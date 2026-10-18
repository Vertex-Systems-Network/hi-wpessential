"""WPEssential.org sections built to the Figma file (page "WPEssential.org", frames at 1440).

Core blocks only, styled by the .ess-* rules in assets/css/theme.css. Icons are CSS masks
(.ess-ico--<name>), so the markup stays plain and editable.
"""
import blocks as b


def over(text, align=None, tone=None):
    return b.p('— ' + text, className=b.cls('ess-over', 'ess-over--' + tone if tone else None), align=align)


def ico(name, tone='blue'):
    return b.group(className='ess-ico ess-ico--%s ess-ico--%s' % (name, tone), layout='default')


def chip(text, href, cls_extra=None):
    return '<a class="%s" href="%s">%s</a>' % (b.cls('ess-chip', cls_extra), href, text)


def html(markup):
    return b.block('html', None, markup)


def head(overline, title, lead=None, align=None, tone=None, action=None, title_color=None):
    """Section heading block: overline, h2, optional lead and an action button on the right."""
    left = b.group(over(overline, align=align, tone=tone), b.h(title, size='h1', align=align, color=title_color, className='ess-h2'),
                   *([b.p(lead, className='ess-lead', align=align)] if lead else []), layout='default', className='ess-head__text')
    if not action:
        return b.group(left, className=b.cls('ess-head', 'ess-head--center' if align == 'center' else None), layout='default')
    return b.group(left, b.buttons(action), className='ess-head ess-head--split', layout='flex', wrap='wrap', justify='space-between', valign='bottom')


def arrow(label, href, style=None):
    return b.button(label + ' <span class="ess-arrow" aria-hidden="true">↗</span>', href, style=style)


def plan_card(pl, period_suffix, buy):
    feat = pl.get('featured')
    top = b.group(b.h(pl['name'], level=3, className='ess-plan__name'),
                  *([b.p('Popular', className='ess-badge ess-badge--orange')] if feat else []),
                  layout='flex', justify='space-between', valign='center', className='ess-plan__top')
    return b.group(top,
                   b.p('$%s <small>%s</small>' % ('{:,}'.format(int(pl['price'])), period_suffix), className='ess-plan__price'),
                   b.p(pl['short'], className='ess-plan__desc'),
                   b.buttons(arrow(('Get ' if feat else 'Choose ') + pl['name'], buy), className='ess-plan__cta'),
                   b.ul(pl['inc'], className='ess-check'),
                   layout='default', className=b.cls('ess-plan', 'is-featured' if feat else None))
