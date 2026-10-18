"""Core block markup that matches what the block editor saves (WordPress 6.5+).

Every helper returns serialized block HTML. Only core blocks are produced, so pages stay
editable without plugins. Images reference package media with {{media:key}} and
{{media_id:key}} tokens that the importer resolves.
"""
import json
import re

_ID = re.compile(r'"__ID_([a-z0-9_.-]+)__"')


def _attrs(attrs):
    attrs = {k: v for k, v in attrs.items() if v is not None and v != {} and v != ''}
    if not attrs:
        return ''
    out = json.dumps(attrs, separators=(',', ':'), ensure_ascii=False)
    out = out.replace('--', '\\u002d\\u002d').replace('<', '\\u003c').replace('>', '\\u003e').replace('&', '\\u0026')
    # Attachment IDs are numbers, resolved from the media map at import time.
    return ' ' + _ID.sub(r'{{media_id:\1}}', out)


def block(name, attrs=None, html=None):
    a = _attrs(attrs or {})
    if html is None:
        return '<!-- wp:%s%s /-->' % (name, a)
    return '<!-- wp:%s%s -->\n%s\n<!-- /wp:%s -->' % (name, a, html, name)


def kebab(slug):
    # lodash kebabCase, as the editor uses for preset classes ("h2" -> "h-2").
    return re.sub(r'(?<=[a-z])(?=[0-9])|(?<=[0-9])(?=[a-z])', '-', slug)


def cls(*parts):
    return ' '.join(p for p in parts if p)


def colors(text=None, bg=None):
    out = []
    if text:
        out += ['has-%s-color' % text, 'has-text-color']
    if bg:
        out += ['has-%s-background-color' % bg, 'has-background']
    return ' '.join(out)


def media_id(key):
    return '__ID_%s__' % key


def join(*blocks):
    return '\n\n'.join(b for b in blocks if b)


# Text ---------------------------------------------------------------------------------

def p(text, style=None, color=None, size=None, align=None, className=None):
    c = cls('is-style-' + style if style else None, className, 'has-text-align-' + align if align else None,
            colors(color), 'has-%s-font-size' % kebab(size) if size else None)
    attrs = {'align': align, 'className': cls('is-style-' + style if style else None, className) or None,
             'textColor': color, 'fontSize': size}
    return block('paragraph', attrs, '<p%s>%s</p>' % (' class="%s"' % c if c else '', text))


def h(text, level=2, size=None, color=None, align=None, className=None):
    c = cls('wp-block-heading', 'has-text-align-' + align if align else None, className, colors(color),
            'has-%s-font-size' % kebab(size) if size else None)
    attrs = {'textAlign': align, 'level': level if level != 2 else None, 'className': className,
             'textColor': color, 'fontSize': size}
    return block('heading', attrs, '<h%d class="%s">%s</h%d>' % (level, c, text, level))


def ul(items, style=None, className=None):
    c = cls('wp-block-list', 'is-style-' + style if style else None, className)
    inner = ''.join(block('list-item', None, '<li>%s</li>' % i) for i in items)
    attrs = {'className': cls('is-style-' + style if style else None, className) or None}
    return block('list', attrs, '<ul class="%s">%s</ul>' % (c, inner))


def quote(text, cite=None, className=None):
    c = cls('wp-block-quote', className)
    inner = p(text) + ('<cite>%s</cite>' % cite if cite else '')
    return block('quote', {'className': className}, '<blockquote class="%s">%s</blockquote>' % (c, inner))


def details(summary, *inner, style='wpe-flush'):
    c = cls('wp-block-details', 'is-style-' + style if style else None)
    return block('details', {'className': 'is-style-' + style if style else None},
                 '<details class="%s"><summary>%s</summary>%s</details>' % (c, summary, join(*inner)))


def table(head, rows, className=None):
    c = cls('wp-block-table', className)
    th = ''.join('<th>%s</th>' % x for x in head)
    body = ''.join('<tr>%s</tr>' % ''.join('<td>%s</td>' % x for x in r) for r in rows)
    return block('table', {'className': className},
                 '<figure class="%s"><table class="has-fixed-layout"><thead><tr>%s</tr></thead><tbody>%s</tbody></table></figure>' % (c, th, body))


def separator(className=None):
    c = cls('wp-block-separator', 'has-alpha-channel-opacity', className)
    return block('separator', {'className': className}, '<hr class="%s"/>' % c)


def spacer(px):
    return block('spacer', {'height': '%dpx' % px},
                 '<div style="height:%dpx" aria-hidden="true" class="wp-block-spacer"></div>' % px)


def shortcode(code):
    return block('shortcode', None, code)


# Actions --------------------------------------------------------------------------------

def button(text, href, style=None, className=None):
    c = cls('wp-block-button', 'is-style-' + style if style else None, className)
    attrs = {'className': cls('is-style-' + style if style else None, className) or None}
    return block('button', attrs,
                 '<div class="%s"><a class="wp-block-button__link wp-element-button" href="%s">%s</a></div>' % (c, href, text))


def buttons(*items, justify=None, className=None):
    layout = {'type': 'flex', 'justifyContent': justify} if justify else None
    c = cls('wp-block-buttons', className)
    return block('buttons', {'className': className, 'layout': layout}, '<div class="%s">%s</div>' % (c, join(*items)))


# Media ----------------------------------------------------------------------------------

def image(key, alt, aspect=None, style=None, className=None, href=None, size='full'):
    c = cls('wp-block-image', 'size-' + size, 'is-style-' + style if style else None, className)
    attrs = {'id': media_id(key), 'aspectRatio': aspect, 'scale': 'cover' if aspect else None, 'sizeSlug': size,
             'linkDestination': 'custom' if href else 'none',
             'className': cls('is-style-' + style if style else None, className) or None}
    img_style = ' style="aspect-ratio:%s;object-fit:cover"' % aspect if aspect else ''
    img = '<img src="{{media:%s}}" alt="%s" class="wp-image-%s"%s/>' % (key, alt, '{{media_id:%s}}' % key, img_style)
    if href:
        img = '<a href="%s">%s</a>' % (href, img)
    return block('image', attrs, '<figure class="%s">%s</figure>' % (c, img))


def cover(key, *inner, dim=40, overlay='ink', min_height=640, position=None, align='full', className=None,
          layout='constrained'):
    pos_cls = 'has-custom-content-position is-position-%s' % position.replace(' ', '-') if position else None
    c = cls('wp-block-cover', 'align' + align if align else None, className, pos_cls)
    attrs = {'url': '{{media:%s}}' % key, 'id': media_id(key), 'dimRatio': dim, 'overlayColor': overlay,
             'isUserOverlayColor': True, 'minHeight': min_height, 'minHeightUnit': 'px',
             'contentPosition': position, 'align': align, 'className': className,
             'layout': {'type': layout}}
    # The editor rounds the dim class to tens (dimRatioToClass).
    bg = '<span aria-hidden="true" class="wp-block-cover__background has-%s-background-color has-background-dim-%d has-background-dim"></span>' % (overlay, int(dim / 10.0 + 0.5) * 10)
    if dim == 0:
        bg = '<span aria-hidden="true" class="wp-block-cover__background has-%s-background-color has-background-dim-0 has-background-dim"></span>' % overlay
    img = '<img class="wp-block-cover__image-background wp-image-%s" alt="" src="{{media:%s}}" data-object-fit="cover"/>' % ('{{media_id:%s}}' % key, key)
    html = '<div class="%s" style="min-height:%dpx">%s%s<div class="wp-block-cover__inner-container">%s</div></div>' % (
        c, min_height, bg, img, join(*inner))
    return block('cover', attrs, html)


# Layout ---------------------------------------------------------------------------------

def group(*inner, tag='div', align=None, className=None, layout='constrained', content=None, bg=None, color=None,
          justify=None, valign=None, wrap=None, grid_min=None, columns=None):
    if layout == 'flex':
        lay = {'type': 'flex', 'flexWrap': wrap, 'justifyContent': justify, 'verticalAlignment': valign}
        lay = {k: v for k, v in lay.items() if v}
    elif layout == 'stack':
        lay = {'type': 'flex', 'orientation': 'vertical', 'justifyContent': justify}
        lay = {k: v for k, v in lay.items() if v}
    elif layout == 'grid':
        lay = {'type': 'grid', 'columnCount': columns, 'minimumColumnWidth': grid_min}
        lay = {k: v for k, v in lay.items() if v}
    elif layout == 'constrained':
        lay = {'type': 'constrained', 'contentSize': content} if content else {'type': 'constrained'}
    else:
        lay = {'type': 'default'}
    c = cls('wp-block-group', 'align' + align if align else None, className, colors(color, bg))
    attrs = {'tagName': tag if tag != 'div' else None, 'align': align, 'className': className,
             'backgroundColor': bg, 'textColor': color, 'layout': lay}
    return block('group', attrs, '<%s class="%s">%s</%s>' % (tag, c, join(*inner), tag))


def section(*inner, className='wpe-section', content='1200px', bg=None, color=None, align='full'):
    return group(*inner, tag='section', align=align, className=className, content=content, bg=bg, color=color)


def columns(*cols, valign=None, className=None, stack=True):
    c = cls('wp-block-columns', 'are-vertically-aligned-' + valign if valign else None, className,
            None if stack else 'is-not-stacked-on-mobile')
    attrs = {'verticalAlignment': valign, 'isStackedOnMobile': None if stack else False, 'className': className}
    return block('columns', attrs, '<div class="%s">%s</div>' % (c, join(*cols)))


def column(*inner, width=None, valign=None, className=None, style=None):
    c = cls('wp-block-column', 'is-vertically-aligned-' + valign if valign else None,
            'is-style-' + style if style else None, className)
    attrs = {'verticalAlignment': valign, 'width': width,
             'className': cls('is-style-' + style if style else None, className) or None}
    st = ' style="flex-basis:%s"' % width if width else ''
    return block('column', attrs, '<div class="%s"%s>%s</div>' % (c, st, join(*inner)))


# Dynamic --------------------------------------------------------------------------------

# The theme's post card (patterns/post-card.php), inlined: a pattern block inside a query loop
# renders empty on some WordPress versions.
POST_CARD = ('<!-- wp:group {"className":"wpe-card","layout":{"type":"default"}} -->\n<div class="wp-block-group wpe-card">'
             '<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/10","sizeSlug":"wpessential-card"} /-->\n'
             '<!-- wp:group {"className":"wpe-card__body","layout":{"type":"flex","orientation":"vertical"}} -->\n<div class="wp-block-group wpe-card__body">'
             '<!-- wp:post-terms {"term":"category","className":"is-style-wpe-overline"} /-->\n'
             '<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"h4"} /-->\n'
             '<!-- wp:post-excerpt {"excerptLength":18} /-->\n'
             '<!-- wp:group {"className":"wpe-post-meta","layout":{"type":"flex","flexWrap":"wrap"}} -->\n<div class="wp-block-group wpe-post-meta">'
             '<!-- wp:post-author-name /-->\n<!-- wp:post-date /--></div>\n<!-- /wp:group --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->')


def latest_posts(count=3, post_type='post'):
    """Latest posts as theme post cards (wpessential/post-card), sticky posts excluded."""
    q = {'queryId': 7, 'query': {'perPage': count, 'pages': 0, 'offset': 0, 'postType': post_type, 'order': 'desc',
                                 'orderBy': 'date', 'sticky': 'exclude', 'inherit': False}}
    tpl = block('post-template', {'layout': {'type': 'grid', 'columnCount': count, 'minimumColumnWidth': '280px'}},
                POST_CARD)
    return block('query', q, '<div class="wp-block-query">%s</div>' % tpl)


def nav(links):
    inner = '\n'.join(block('navigation-link', {'label': label, 'url': href, 'kind': 'custom'}) for label, href in links)
    return block('navigation', {'overlayMenu': 'never', 'layout': {'type': 'flex', 'orientation': 'vertical'}, 'fontSize': 'small'}, inner)


def footer(about, cols, legal, band=None):
    """Site footer with the theme's footer classes. cols: [(title, [(label, href)])]."""
    top = ''
    if band:
        title, text, label, href = band
        top = group(columns(column(h(title, level=3, color='on-ink'), p(text, color='on-ink'), width='66%', valign='center'),
                            column(buttons(button(label, href, style='wpe-light'), justify='right'), width='34%', valign='center'),
                            valign='center', className='wpe-section--sm'), className='wpe-footer__newsletter', content='1200px')
    brand = column(block('site-title'), p(about, size='small'), width='40%')
    links = [column(group(h(t, level=2), nav(items), layout='stack')) for t, items in cols]
    policies = ' · '.join('<a href="{{page:%s}}">%s</a>' % (k, t) for k, t in
                          [('privacy-policy', 'Privacy'), ('terms', 'Terms'), ('refund-policy', 'Refunds'), ('cookie-policy', 'Cookies')])
    bottom = group(p(legal[0]), p(policies), p(legal[1]), className='wpe-footer__bottom', layout='flex', wrap='wrap', justify='space-between')
    return group(top, columns(brand, *links, className='wpe-footer__cols wpe-section--sm'), bottom, tag='footer',
                 className='wpe-footer wpe-section--md', content='1200px')
