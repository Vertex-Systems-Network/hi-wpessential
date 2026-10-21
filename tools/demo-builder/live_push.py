"""Push a built demo page to a live site through its WPEssential AI Connect (MCP) endpoint.

The demo builder stays the source of truth: the same block markup that goes into the demo
package is sent to the live site, with {{tokens}} resolved against what already exists there
(media by file name, pages and products by slug, WooCommerce pages, WPForms forms by title).

    WPE_URL=https://wpessential.org WPE_AUTH="Basic …" python3 live_push.py essential home --slug home-v2
    python3 live_push.py essential footer --part        # writes the footer template part

Nothing is deleted. A page is created when the slug does not exist yet, else updated.
"""
import argparse
import json
import os
import re
import sys
import urllib.request

ROOT = os.path.dirname(os.path.abspath(__file__))


def mcp(tool, args=None):
    body = json.dumps({'jsonrpc': '2.0', 'id': 1, 'method': 'tools/call', 'params': {'name': tool, 'arguments': args or {}}}).encode()
    req = urllib.request.Request(os.environ['WPE_URL'].rstrip('/') + '/wp-json/wpessential/v1/mcp', body,
                                 {'Content-Type': 'application/json', 'Authorization': os.environ['WPE_AUTH'], 'User-Agent': 'WPEssential-live-push'})
    res = json.load(urllib.request.urlopen(req, timeout=120))['result']
    text = res['content'][0]['text']
    if res.get('isError'):
        sys.exit('%s failed: %s' % (tool, text))
    return json.loads(text)


def rest(path, method='GET', body=None):
    out = mcp('wp_rest', {'method': method, 'path': path, **({'body': body} if body else {})})
    if out['status'] >= 400:
        sys.exit('%s %s -> %s %s' % (method, path, out['status'], json.dumps(out['data'])[:300]))
    return out['data']


class Live:
    """What exists on the live site, looked up once."""

    def __init__(self, package):
        self.package = package
        self.media = {}
        for m in rest('/wp/v2/media?per_page=100&_fields=id,source_url'):
            self.media[os.path.splitext(m['source_url'].rsplit('/', 1)[-1])[0]] = (m['id'], m['source_url'])
        self.posts = {}
        for kind in ('page', 'post', 'product'):
            for p in mcp('list_content', {'post_type': kind, 'per_page': 200}):
                self.posts[(kind, p['slug'])] = p
        self.woo = mcp('woo_pages') if any('{{woo:' in json.dumps(x) for x in package['content']) else {}
        self.forms = {}
        self.keys = {}  # package key -> (type, slug)
        for item in package['content']:
            self.keys[item['key']] = (item.get('type', 'page'), item.get('slug', ''))
        for item in package.get('products', []):
            self.keys[item['key']] = ('product', item.get('slug', ''))
        self.missing = set()
        self.site = None

    def post(self, key):
        kind, slug = self.keys.get(key, ('page', key))
        return self.posts.get((kind, slug))

    def form_id(self, key):
        if key not in self.forms:
            spec = next((f for f in self.package.get('forms', []) if f['key'] == key), None)
            if not spec:
                self.missing.add('form:' + key)
                return '0'
            # Reuse a form made by an earlier push instead of creating a copy.
            same = [f for f in mcp('list_content', {'post_type': 'wpforms', 'per_page': 200}) if f['title'] == spec['title']]
            if same:
                self.forms[key] = str(same[0]['id'])
                return self.forms[key]
            made = mcp('create_form', {'title': spec['title'], 'fields': spec['fields'], 'submit': spec.get('submit', ''), 'message': spec.get('message', '')})
            self.forms[key] = str(made['id'])
        return self.forms[key]

    def resolve(self, text):
        def sub(m):
            kind, arg = m.group(1), m.group(2)
            if kind in ('media', 'media_id'):
                hit = self.media.get(arg)
                if not hit:
                    self.missing.add('media:' + arg)
                    return '0' if kind == 'media_id' else ''
                return str(hit[0]) if kind == 'media_id' else hit[1]
            if kind in ('page', 'post_id'):
                hit = self.post(arg)
                if not hit:
                    self.missing.add('page:' + arg)
                    return '0' if kind == 'post_id' else os.environ['WPE_URL'].rstrip('/') + '/'
                return str(hit['id']) if kind == 'post_id' else hit['link']
            if kind == 'woo':
                return (self.woo.get(arg) or {}).get('link', '/')
            if kind == 'home':
                return os.environ['WPE_URL'].rstrip('/') + '/' + arg
            if kind == 'form':
                return self.form_id(arg)
            if kind == 'site':
                if self.site is None:
                    self.site = rest('/wp/v2/settings')
                return self.site.get('email' if arg == 'email' else 'title', '')
            return m.group(0)
        return re.sub(r'\{\{(media|media_id|home|page|post_id|woo|form|site):?([a-z0-9/_.-]*)\}\}', sub, text)


def push_menus(live, package):
    """Write each package menu into the live navigation post of the same name (created when missing)."""
    navs = {n['title']: n for n in mcp('list_content', {'post_type': 'wp_navigation', 'per_page': 50})}
    for menu in package.get('menus', []):
        links = []
        for it in menu['items']:
            hit = live.post(it['page']) if it.get('page') else None
            url = hit['link'] if hit else it.get('url', '/')
            links.append('<!-- wp:navigation-link %s /-->' % json.dumps({'label': it['title'], 'url': url, 'kind': 'post-type' if hit else 'custom',
                                                                          **({'id': hit['id'], 'type': 'page'} if hit else {})}))
        nav = navs.get(menu['name'])
        out = mcp('save_content', {**({'id': nav['id']} if nav else {'post_type': 'wp_navigation'}), 'title': menu['name'], 'status': 'publish', 'content': ''.join(links)})
        print('menu', menu['name'], out['id'])


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('demo')
    ap.add_argument('key', help='content key (home, pricing…) or a template part name with --part')
    ap.add_argument('--slug', help='live slug to write the page to (default: the package slug)')
    ap.add_argument('--part', action='store_true', help='write a template part (header, footer…)')
    ap.add_argument('--status', default='publish')
    ap.add_argument('--all', action='store_true', help='push every page of the package (twice, so new pages link to each other)')
    ap.add_argument('--alias', action='append', default=[], help='package-slug=live-slug, e.g. privacy-policy=privacy-policy-2')
    ap.add_argument('--dry', action='store_true', help='only list what is missing on the live site')
    a = ap.parse_args()
    package = json.load(open(os.path.join(ROOT, '..', '..', 'demos', a.demo, 'gutenberg', 'content.json')))
    live = Live(package)
    if a.dry:
        src = package['settings']['parts'][a.key]['content'] if a.part else next(x for x in package['content'] if x['key'] == a.key)['content']
        live.forms = {f['key']: '?' for f in package.get('forms', [])}
        live.resolve(src)
        print('missing:', ', '.join(sorted(live.missing)) or 'nothing')
        return
    aliases = dict(x.split('=', 1) for x in a.alias)
    if a.all:
        pages = [x for x in package['content'] if x.get('type', 'page') == 'page' and x.get('content')]
        for rnd in (1, 2):
            for item in pages:
                slug = aliases.get(item['slug'], item['slug'])
                existing = live.posts.get(('page', slug))
                out = mcp('save_content', {**({'id': existing['id']} if existing else {'post_type': 'page'}), 'title': item['title'],
                                           'slug': slug, 'status': a.status, 'content': live.resolve(item['content'])})
                if item.get('template'):
                    rest('/wp/v2/pages/%d' % out['id'], 'POST', {'template': item['template']})
                live.posts[('page', slug)] = {'id': out['id'], 'link': out['link'], 'slug': slug}
                live.keys[item['key']] = ('page', slug)
                if rnd == 2:
                    print('page', out['id'], out['link'])
            if rnd == 1:
                live.missing = set()
        push_menus(live, package)
        for part in ('header', 'header-shop', 'header-transparent', 'footer'):
            mcp('block_templates', {'id': 'hi-wpessential//' + part, 'content': live.resolve(package['settings']['parts'][part]['content'])})
            print('part', part)
    elif a.part:
        part = package['settings']['parts'][a.key]['content']
        out = mcp('block_templates', {'id': 'hi-wpessential//' + a.key, 'content': live.resolve(part)})
        print('part', a.key, out.get('status'))
    else:
        item = next(x for x in package['content'] if x['key'] == a.key)
        slug = a.slug or item['slug']
        existing = live.posts.get(('page', slug))
        out = mcp('save_content', {**({'id': existing['id']} if existing else {'post_type': 'page'}), 'title': item['title'],
                                   'slug': slug, 'status': a.status, 'content': live.resolve(item['content'])})
        if item.get('template'):
            rest('/wp/v2/pages/%d' % out['id'], 'POST', {'template': item['template']})
        print('page', out['id'], out['link'])
    if live.missing:
        print('not on the live site yet:', ', '.join(sorted(live.missing)))


if __name__ == '__main__':
    main()
