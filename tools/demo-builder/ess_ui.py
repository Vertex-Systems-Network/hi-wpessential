"""WPEssential.org sections built to the Figma file (page "WPEssential.org", frames at 1440).

Core blocks only, styled by the .ess-* rules in assets/css/theme.css. Icons are CSS masks
(.wpe-ess-ico--<name>), so the markup stays plain and editable.
"""
import kit as b


def over(text, align=None, tone=None):
    return b.p('— ' + text, className=b.cls('wpe-ess-over', 'wpe-ess-over--' + tone if tone else None), align=align)


def ico(name, tone='blue'):
    return b.group(className='wpe-ess-ico wpe-ess-ico--%s wpe-ess-ico--%s' % (name, tone), layout='default')


def chip(text, href, cls_extra=None):
    return '<a class="%s" href="%s">%s</a>' % (b.cls('wpe-ess-chip', cls_extra), href, text)


def html(markup):
    return b.html(markup)


def head(overline, title, lead=None, align=None, tone=None, action=None, title_color=None):
    """Section heading block: overline, h2, optional lead and an action button on the right."""
    left = b.group(over(overline, align=align, tone=tone), b.h(title, size='h1', align=align, color=title_color, className='wpe-ess-h2'),
                   *([b.p(lead, className='wpe-ess-lead', align=align)] if lead else []), layout='default', className='wpe-ess-head__text')
    if not action:
        return b.group(left, className=b.cls('wpe-ess-head', 'wpe-ess-head--center' if align == 'center' else None), layout='default')
    return b.group(left, b.buttons(action), className='wpe-ess-head wpe-ess-head--split', layout='flex', wrap='wrap', justify='space-between', valign='bottom')


def arrow(label, href, style=None):
    return b.button(label + ' <span class="wpe-ess-arrow" aria-hidden="true">↗</span>', href, style=style)


def plan_card(pl, period_suffix, buy):
    feat = pl.get('featured')
    top = b.group(b.h(pl['name'], level=3, className='wpe-ess-plan__name'),
                  *([b.p('Popular', className='wpe-ess-badge wpe-ess-badge--orange')] if feat else []),
                  layout='flex', justify='space-between', valign='center', className='wpe-ess-plan__top')
    alt = {'monthly': 'or yearly −20%, or lifetime', 'yearly': 'or monthly, or lifetime', 'lifetime': 'or monthly, or yearly −20%'}.get(pl.get('period'), '')
    return b.group(top,
                   *([b.p(pl['who'], className='wpe-ess-plan__who')] if pl.get('who') else []),
                   b.p('$%s <small>%s</small>' % ('{:,}'.format(int(pl['price'])), period_suffix), className='wpe-ess-plan__price'),
                   *([b.p(alt, className='wpe-ess-plan__alt')] if alt else []),
                   b.p(pl['short'], className='wpe-ess-plan__desc'),
                   b.buttons(arrow(('Get ' if feat else 'Choose ') + pl['name'], buy), className='wpe-ess-plan__cta'),
                   b.ul(pl['inc'], className='wpe-ess-check'),
                   layout='default', className=b.cls('wpe-ess-plan', 'is-featured' if feat else None))
