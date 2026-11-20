"""WPEssential kit blocks (wpessential/*) with the same helper API as blocks.py.

Demo content built with `import kit as b` uses only our own elements. Theme classes (is-style-*,
has-*-color, ess-*) go into each element's cssClass, so the existing theme CSS keeps styling them.
"""
import re

from blocks import block, cls, join, kebab, media_id  # noqa: F401  (re-exported)

JUSTIFY = {'left': 'start', 'right': 'end', 'center': 'center', 'space-between': 'between', 'flex-start': 'start', 'flex-end': 'end'}
VALIGN = {'top': 'start', 'center': 'center', 'bottom': 'end', 'stretch': 'stretch'}


def _r(value):
    """Responsive attribute value (base breakpoint only)."""
    return {'base': value} if value not in (None, '') else None


def _classes(style=None, className=None, align=None, color=None, size=None, bg=None):
    return cls('is-style-' + style if style else None, className,
               'has-text-align-' + align if align else None,
               'has-%s-color has-text-color' % color if color else None,
               'has-%s-background-color has-background' % bg if bg else None,
               'has-%s-font-size' % kebab(size) if size else None) or None


def el(name, attrs, inner=None):
    return block('wpessential/' + name, {k: v for k, v in attrs.items() if v is not None}, inner)


# Text ---------------------------------------------------------------------------------

def p(text, style=None, color=None, size=None, align=None, className=None):
    return el('text', {'content': '<p>%s</p>' % text, 'cssClass': _classes(style, className, align, color, size)})


def h(text, level=2, size=None, color=None, align=None, className=None, anchor=None):
    return el('heading', {'text': text, 'tag': 'h%d' % level, 'anchor': anchor,
                          'cssClass': _classes(None, className, align, color, size)})


def ul(items, style=None, className=None):
    return el('text', {'content': '<ul>%s</ul>' % ''.join('<li>%s</li>' % i for i in items),
                       'cssClass': _classes(style, className)})


def quote(text, cite=None, className=None):
    return el('text', {'content': '<p>%s</p>%s' % (text, '<cite>%s</cite>' % cite if cite else ''), 'tag': 'blockquote',
                       'cssClass': className})


def details(summary, *inner, style='wpe-flush'):
    return el('accordion', {'items': [{'title': re.sub('<[^>]+>', '', summary), 'content': join(*inner)}],
                            'cssClass': _classes(style)})


def table(head, rows, className=None):
    line = lambda cells: ','.join('"%s"' % str(c).replace('"', '""') for c in cells)  # noqa: E731
    return el('table', {'data': '\n'.join([line(head)] + [line(r) for r in rows]), 'cssClass': className})


def separator(className=None):
    return el('divider', {'cssClass': className})


def spacer(px):
    return el('container', {'minHeight': _r(px), 'cssClass': 'wpe-spacer'}, '')


def shortcode(code):
    """Forms go through the Pro Forms element; other shortcodes are not used in kit demos."""
    m = re.search(r'id="([^"]+)"', code)
    return el('forms', {'formId': m.group(1) if m else '', 'showTitle': False})


def html(markup, className=None):
    return el('code', {'mode': 'html', 'html': markup, 'cssClass': className})


# Actions --------------------------------------------------------------------------------

def button(text, href, style=None, className=None):
    label = re.sub(r'\s*<span class="ess-arrow"[^>]*>.*?</span>', '', text)
    return el('button', {'text': label, 'url': href, 'variant': 'primary',
                          'icon': {'library': 'wpessential', 'value': 'arrow-up-right'} if label != text else {},
                          'cssClass': _classes(style, className)})


def buttons(*items, justify=None, className=None):
    return el('container', {'layout': 'flex', 'direction': _r('row'), 'wrap': _r('wrap'), 'justify': _r(JUSTIFY.get(justify, justify)),
                            'alignItems': _r('center'), 'equalChildren': False, 'contentWidth': 'full',
                            'cssClass': cls('wpe-buttons', className)}, join(*items))


# Media ----------------------------------------------------------------------------------

def image(key, alt, aspect=None, style=None, className=None, href=None, size='full'):
    return el('image', {'image': {'id': media_id(key), 'url': '{{media:%s}}' % key}, 'alt': alt, 'size': size,
                        'ratio': _r(aspect) if aspect in ('1/1', '4/3', '3/2', '16/9', '21/9') else None,
                        'fit': _r('cover') if aspect else None, 'link': href,
                        'cssClass': cls(_classes(style, className), 'wpe-ratio-' + aspect.replace('/', '-') if aspect else None)})


# Layout ---------------------------------------------------------------------------------

def group(*inner, tag='div', align=None, className=None, layout='constrained', content=None, bg=None, color=None,
          justify=None, valign=None, wrap=None, grid_min=None, columns=None):
    a = {'tag': tag if tag in ('section', 'article', 'aside', 'header', 'footer', 'main', 'nav') else None, 'equalChildren': False,
         'cssClass': cls(_classes(None, className, None, color, None, bg), 'align' + align if align else None)}
    if layout == 'flex':
        a.update(layout='flex', direction=_r('row'), wrap=_r('nowrap' if wrap == 'nowrap' else 'wrap'),
                 justify=_r(JUSTIFY.get(justify, justify)), alignItems=_r(VALIGN.get(valign, valign)), contentWidth='full')
    elif layout == 'grid':
        a.update(layout='grid', columns=_r(columns), contentWidth='full',
                 cssClass=cls(a['cssClass'], 'wpe-grid-min-%s' % grid_min.replace('px', '') if grid_min else None))
    elif layout == 'stack':
        a.update(layout='flex', direction=_r('column'), alignItems=_r(VALIGN.get(justify, 'stretch')), contentWidth='full')
    else:
        a.update(layout='flex', direction=_r('column'), alignItems=_r('stretch'),
                 contentWidth='boxed' if layout == 'constrained' else 'full',
                 maxWidth=_r(int(content.replace('px', ''))) if content else None)
    return el('container', a, join(*inner))


def section(*inner, className='wpe-section', content='1200px', bg=None, color=None, align='full'):
    return group(*inner, tag='section', align=align, className=className, content=content, bg=bg, color=color)


def columns(*cols, valign=None, className=None, stack=True):
    return el('container', {'layout': 'flex', 'direction': _r('row'), 'wrap': _r('nowrap'), 'alignItems': _r(VALIGN.get(valign, 'stretch')),
                            'equalChildren': False, 'contentWidth': 'full',
                            'cssClass': cls('wpe-cols', None if stack else 'wpe-cols--keep', className)}, join(*cols))


def column(*inner, width=None, valign=None, className=None, style=None):
    return el('container', {'layout': 'flex', 'direction': _r('column'), 'justify': _r(VALIGN.get(valign, 'start')),
                            'equalChildren': False, 'contentWidth': 'full',
                            'cssClass': cls('wpe-col', 'wpe-col--w%s' % width.replace('%', '') if width else None, _classes(style, className))},
              join(*inner))


# Dynamic --------------------------------------------------------------------------------

def latest_posts(count=3, post_type='post'):
    return el('posts-grid', {'postType': post_type, 'postsPerPage': count, 'columns': _r(count), 'showExcerpt': True,
                             'showTaxonomy': True, 'showDate': True, 'showAuthor': True, 'titleTag': 'h3'})


def links(items, className=None):
    """A list of links (footer columns, sidebars) as an Icon List without markers."""
    return el('icon-list', {'items': [{'text': label, 'link': href} for label, href in items], 'marker': 'none',
                            'cssClass': className})


def nav(items):
    return links(items, className='wpe-footer__links')


def footer(about, cols, legal, band=None):
    top = ''
    if band:
        title, text, label, href = band
        top = group(columns(column(h(title, level=3, color='on-ink'), p(text, color='on-ink'), width='66%', valign='center'),
                            column(buttons(button(label, href, style='wpe-light'), justify='right'), width='34%', valign='center'),
                            valign='center', className='wpe-section--sm'), className='wpe-footer__newsletter', content='1200px')
    brand = column(el('site-logo', {'display': 'title'}), p(about, size='small'), width='40%')
    cols_ = [column(group(h(t, level=2), nav(items), layout='stack')) for t, items in cols]
    policies = ' · '.join('<a href="{{page:%s}}">%s</a>' % (k, t) for k, t in
                          [('privacy-policy', 'Privacy'), ('terms', 'Terms'), ('refund-policy', 'Refunds'), ('cookie-policy', 'Cookies')])
    bottom = group(p(legal[0]), p(policies), p(legal[1]), className='wpe-footer__bottom', layout='flex', wrap='wrap', justify='space-between')
    return group(top, columns(brand, *cols_, className='wpe-footer__cols wpe-section--sm'), bottom, tag='footer',
                 className='wpe-footer wpe-section--md', content='1200px')
