"""Build the elements bank and the options bank from plugin source code.

Every Elementor widget (Elementor and third-party addons) and every Gutenberg block (core and
block plugins) is read from its source, with all of its controls / attributes, so the bank is
complete and can be refreshed when a plugin updates.

    python3 tools/banks/extract.py --src <dir with unzipped plugins> --wp <wordpress dir>

Writes:
    banks/elements/<source>/<name>.json   one file per widget or block
    banks/options/index.json              every option (control type / group / attribute) and who has it
    banks/summary.json                    counts per source
"""
import argparse
import glob
import json
import os
import re

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
OUT = os.path.join(ROOT, 'banks')

# Elementor sources: folder name in --src -> (bank id, label).
ELEMENTOR = {
    'elementor': ('elementor', 'Elementor'),
    'essential-addons-for-elementor-lite': ('essential-addons', 'Essential Addons Lite'),
    'elementskit-lite': ('elementskit', 'ElementsKit Lite'),
    'premium-addons-for-elementor': ('premium-addons', 'Premium Addons'),
    'happy-elementor-addons': ('happy-addons', 'Happy Addons'),
    'header-footer-elementor': ('ultimate-addons-hfe', 'Ultimate Addons (Header Footer Elementor)'),
}
BLOCKS = {
    'kadence-blocks': ('kadence', 'Kadence Blocks'),
    'ultimate-addons-for-gutenberg': ('spectra', 'Spectra'),
    'generateblocks': ('generateblocks', 'GenerateBlocks'),
}

# Elementor documents and settings panels that parse like widgets but are not elements.
NOT_WIDGETS = {'editor-preferences', 'page-settings', 'styleguide', 'common-base', 'common', 'common-optimized', 'kit'}

STR = r"""(?:'((?:[^'\\]|\\.)*)'|"((?:[^"\\]|\\.)*)")"""


def first_str(text):
    m = re.search(STR, text)
    return (m.group(1) if m.group(1) is not None else m.group(2)) if m else ''


def label_of(chunk):
    m = re.search(r"'label'\s*=>\s*(?:esc_html__|__|esc_html_x|_x|esc_attr__)?\s*\(?\s*" + STR, chunk)
    if not m:
        return ''
    return m.group(1) if m.group(1) is not None else m.group(2)


def key_of(chunk, key):
    m = re.search(r"'%s'\s*=>\s*([^,\n\]]+)" % key, chunk)
    return m.group(1).strip() if m else ''


def options_of(chunk):
    m = re.search(r"'options'\s*=>\s*\[(.*?)\]\s*,", chunk, re.S)
    if not m:
        return []
    return re.findall(r"'([a-z0-9_\-]+)'\s*=>", m.group(1))[:40]


def php_method(src, name):
    m = re.search(r'function\s+%s\s*\(\s*\)\s*(?::\s*\w+\s*)?\{(.*?)\n\s*\}' % name, src, re.S)
    return m.group(1) if m else ''


def parse_widget(path):
    src = open(path, encoding='utf-8', errors='ignore').read()
    if not re.search(r"function\s+_?register_(?:content_|style_)?controls\s*\(", src) or 'extends' not in src or re.search(r'abstract\s+class', src):
        return None
    body = php_method(src, 'get_name')
    name = first_str(body) if re.search(r'return\s*[\'"]', body) else ''
    if not name:
        # Dynamic names (ElementsKit, Happy Addons): the widget's folder or file name.
        base = os.path.basename(path)[:-4]
        name = os.path.basename(os.path.dirname(path)) if base in ('widget', 'index') or base == os.path.basename(os.path.dirname(path)) else base
        name = re.sub(r'[^a-z0-9]+', '-', name.lower())
    title = ''
    for text in [php_method(src, 'get_title')] + [open(h, encoding='utf-8', errors='ignore').read() for h in glob.glob(os.path.join(os.path.dirname(path), '*handler*.php'))]:
        t = first_str(php_method(text, 'get_title') if text.lstrip().startswith('<?php') else text)
        if t and '://' not in t and not re.search(r'return\s+\w+::', php_method(text, 'get_title') if text.lstrip().startswith('<?php') else text):
            title = t
            break
    title = title or name.replace('-', ' ').title()
    cats = re.findall(r"'([a-z0-9\-_]+)'", php_method(src, 'get_categories'))
    keywords = re.findall(r"'([^']+)'", php_method(src, 'get_keywords'))[:20]
    sections, cur = [], None
    # Walk every control call in source order.
    pattern = re.compile(r'(start_controls_section|add_control|add_responsive_control|add_group_control|start_controls_tab|start_popover)\s*\(')
    for m in pattern.finditer(src):
        kind = m.group(1)
        chunk = src[m.end(): m.end() + 1800]
        stop = re.search(r'\$this->|\$repeater->|\$element->', chunk)
        chunk = chunk[: stop.start()] if stop else chunk
        if kind == 'start_controls_section':
            cur = {'id': first_str(chunk), 'label': label_of(chunk), 'tab': key_of(chunk, 'tab').replace('Controls_Manager::', '').replace('\\Elementor\\', ''), 'controls': []}
            sections.append(cur)
            continue
        if cur is None:
            cur = {'id': 'content', 'label': '', 'tab': '', 'controls': []}
            sections.append(cur)
        if kind == 'start_controls_tab':
            cur['controls'].append({'name': first_str(chunk), 'label': label_of(chunk), 'type': 'TAB'})
            continue
        if kind == 'start_popover':
            continue
        if kind == 'add_group_control':
            g = re.match(r'\s*(?:\\?Elementor\\)?(\w+)::get_type\(\)', chunk)
            cur['controls'].append({'name': key_of(chunk, 'name').strip("'\""), 'label': label_of(chunk), 'type': 'GROUP', 'group': g.group(1) if g else ''})
            continue
        ctl = {'name': first_str(chunk), 'label': label_of(chunk),
               'type': key_of(chunk, 'type').replace('Controls_Manager::', '').replace('\\Elementor\\', '').strip("'\"")}
        if kind == 'add_responsive_control':
            ctl['responsive'] = True
        if "'selectors'" in chunk:
            ctl['styles'] = True
        opts = options_of(chunk)
        if opts:
            ctl['options'] = opts
        cur['controls'].append(ctl)
    sections = [s for s in sections if s['controls']]
    if not sections:
        return None
    return {'kind': 'elementor-widget', 'name': name, 'title': title, 'categories': cats, 'keywords': keywords,
            'file': os.path.relpath(path).split('/', 1)[-1], 'sections': sections,
            'counts': {'sections': len(sections), 'controls': sum(len(s['controls']) for s in sections),
                       'responsive': sum(1 for s in sections for c in s['controls'] if c.get('responsive'))}}


def parse_block(path):
    try:
        data = json.load(open(path, encoding='utf-8'))
    except (ValueError, OSError):
        return None
    if not isinstance(data, dict) or 'name' not in data:
        return None
    attrs = {}
    for k, v in (data.get('attributes') or {}).items():
        if isinstance(v, dict):
            attrs[k] = {x: v[x] for x in ('type', 'default', 'enum') if x in v}
    return {'kind': 'block', 'name': data['name'], 'title': data.get('title', data['name']), 'categories': [data.get('category', '')],
            'keywords': data.get('keywords', []), 'description': data.get('description', ''),
            'file': os.path.relpath(path).split('/', 1)[-1], 'attributes': attrs, 'supports': data.get('supports', {}),
            'counts': {'attributes': len(attrs), 'supports': len(data.get('supports', {}) or {})}}


def parse_php_attributes(path, prefix):
    """Spectra keeps each block's attributes in includes/blocks/<name>/attributes.php (key => default)."""
    src = open(path, encoding='utf-8', errors='ignore').read()
    attrs = {}
    for k, v in re.findall(r"^\s*'([A-Za-z0-9_]+)'\s*=>\s*([^\n]+?),?\s*$", src, re.M):
        v = v.strip().rstrip(',')
        t = 'boolean' if v in ('true', 'false') else 'number' if re.fullmatch(r'-?\d+(\.\d+)?', v) else 'array' if v.startswith('array') else 'string'
        attrs[k] = {'type': t, 'default': v.strip("'\"")}
    name = os.path.basename(os.path.dirname(path))
    return {'kind': 'block', 'name': prefix + name, 'title': name.replace('-', ' ').title(), 'categories': [], 'keywords': [], 'description': '',
            'file': os.path.relpath(path).split('/', 1)[-1], 'attributes': attrs, 'supports': {},
            'counts': {'attributes': len(attrs), 'supports': 0}}


def slug(text):
    return re.sub(r'[^a-z0-9]+', '-', text.lower()).strip('-') or 'item'


def save(source, label, item, index):
    d = os.path.join(OUT, 'elements', source)
    os.makedirs(d, exist_ok=True)
    item = dict(item, source=source, source_label=label)
    fname = slug(item['name'].split('/')[-1] if item['kind'] == 'block' and item['name'].startswith(('core/',)) else item['name']) + '.json'
    path = os.path.join(d, fname)
    n = 2
    while os.path.exists(path):
        path = os.path.join(d, fname[:-5] + '-%d.json' % n)
        n += 1
    json.dump(item, open(path, 'w'), indent=1, ensure_ascii=False)
    ref = '%s/%s' % (source, os.path.basename(path)[:-5])
    # Options bank: what each element offers, grouped by option kind.
    if item['kind'] == 'elementor-widget':
        for s in item['sections']:
            for c in s['controls']:
                key = 'group:' + c['group'] if c['type'] == 'GROUP' else 'control:' + (c['type'] or 'UNKNOWN')
                index.setdefault(key, set()).add(ref)
                if c.get('responsive'):
                    index.setdefault('feature:responsive', set()).add(ref)
                if re.search(r'hover', c.get('name', '') + c.get('label', ''), re.I):
                    index.setdefault('feature:hover', set()).add(ref)
    else:
        for k in item['supports']:
            index.setdefault('supports:' + k, set()).add(ref)
        for a, v in item['attributes'].items():
            index.setdefault('attribute-type:' + str(v.get('type', 'unknown')), set()).add(ref)
    return item


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--src', required=True)
    ap.add_argument('--wp', required=True)
    a = ap.parse_args()
    index, summary = {}, {}
    for folder, (source, label) in ELEMENTOR.items():
        base = os.path.join(a.src, folder)
        files = [f for f in glob.glob(base + '/**/*.php', recursive=True)
                 if '/vendor/' not in f and '/node_modules/' not in f and '/base/' not in f and 'widget-base' not in f]
        names = set()
        for f in sorted(files):
            w = parse_widget(f)
            if w and w['name'] in NOT_WIDGETS:
                continue
            if w and w['name'] not in names:
                names.add(w['name'])
                save(source, label, w, index)
        summary[source] = {'label': label, 'kind': 'elementor', 'elements': len(names)}
    for folder, (source, label) in [('wordpress', ('gutenberg-core', 'Gutenberg core'))] + list(BLOCKS.items()):
        base = os.path.join(a.wp, 'wp-includes', 'blocks') if folder == 'wordpress' else os.path.join(a.src, folder)
        names = set()
        if folder == 'ultimate-addons-for-gutenberg':
            for f in sorted(glob.glob(base + '/**/includes/blocks/*/attributes.php', recursive=True)):
                blk = parse_php_attributes(f, 'uagb/')
                if blk['name'] not in names and blk['attributes']:
                    names.add(blk['name'])
                    save(source, label, blk, index)
        for f in sorted(glob.glob(base + '/**/block.json', recursive=True)):
            if '/node_modules/' in f or '/src/' in f and glob.glob(os.path.join(base, '**', 'build', '**', 'block.json'), recursive=True):
                continue
            blk = parse_block(f)
            if blk and blk['name'] not in names:
                names.add(blk['name'])
                save(source, label, blk, index)
        summary[source] = {'label': label, 'kind': 'gutenberg', 'elements': len(names)}
    os.makedirs(os.path.join(OUT, 'options'), exist_ok=True)
    json.dump({k: sorted(v) for k, v in sorted(index.items(), key=lambda kv: -len(kv[1]))},
              open(os.path.join(OUT, 'options', 'index.json'), 'w'), indent=1)
    json.dump(summary, open(os.path.join(OUT, 'summary.json'), 'w'), indent=1)
    print(json.dumps(summary, indent=1))


if __name__ == '__main__':
    main()
