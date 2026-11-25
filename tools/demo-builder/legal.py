"""Legal pages every demo ships: Privacy Policy, Terms & Conditions, Refund Policy, Cookie Policy.

build.py appends them to each demo package (Gutenberg and Elementor) and the shared footer
links to them. The text is a sensible starting point written for the demo's own business;
site owners must review it with their own legal adviser before going live.
"""
import blocks as b
import elementor as e

UPDATED = '1 October 2026'

# key, title, slug — the slugs match the links in the theme's footer patterns.
PAGES = [('privacy-policy', 'Privacy Policy', 'privacy-policy'), ('terms', 'Terms & Conditions', 'terms'),
         ('refund-policy', 'Refund Policy', 'refund-policy'), ('cookie-policy', 'Cookie Policy', 'cookie-policy')]

LINKS = [('Privacy', '{{page:privacy-policy}}'), ('Terms', '{{page:terms}}'), ('Refunds', '{{page:refund-policy}}'), ('Cookies', '{{page:cookie-policy}}')]


def _sections(key, brand, email, sells):
    """Section list [(heading, [paragraphs or ('ul', items)])] for one page."""
    what = sells or 'our products and services'
    if key == 'privacy-policy':
        return [
            ('Who we are', ['%s runs this website. This policy explains what personal data we collect when you visit it, buy %s or contact us, and how we use it.' % (brand, what)]),
            ('What we collect', [('ul', ['Details you give us: name, email, phone, billing and delivery address when you order or get in touch.',
                                         'Order details: what you bought, when and how you paid. Card numbers are handled by our payment provider and never stored by us.',
                                         'Usage data: pages visited, device and browser, collected through cookies (see our Cookie Policy).'])]),
            ('How we use it', [('ul', ['To process orders, deliver what you bought and send receipts.',
                                      'To answer questions and give support.',
                                      'To send news or offers, only if you opted in. Every email has an unsubscribe link.',
                                      'To keep the site secure and improve it.'])]),
            ('Sharing', ['We share data only with services that help us run the business — payment, hosting, email and delivery providers — and only what they need. We never sell your data.']),
            ('How long we keep it', ['Order records are kept for as long as tax law requires. Other data is deleted when it is no longer needed or when you ask us to.']),
            ('Your rights', ['You can ask to see, correct, export or delete your data, or object to how we use it. Email %s and we will reply within 30 days.' % email]),
            ('Changes', ['If this policy changes we update the date at the top of the page.']),
        ]
    if key == 'terms':
        return [
            ('About these terms', ['These terms apply when you use this website or buy %s from %s. By placing an order you agree to them.' % (what, brand)]),
            ('Orders and prices', ['Prices include the taxes shown at checkout. An order is confirmed when you receive our confirmation email. We may cancel an order if an item is unavailable or a price was shown in error, and refund you in full.']),
            ('Payment', ['Payment is taken at checkout through our payment provider. Subscriptions renew automatically until you cancel them in your account.']),
            ('Using the website', [('ul', ['Do not misuse the site, try to break its security or copy its content for commercial use without permission.',
                                          'Accounts are personal; keep your password safe.'])]),
            ('Intellectual property', ['Text, images, logos and designs on this site belong to %s or its licensors.' % brand]),
            ('Liability', ['We do our best to keep the site accurate and available but cannot guarantee it is always error-free. Nothing in these terms limits rights you have by law as a consumer.']),
            ('Contact', ['Questions about these terms: %s.' % email]),
        ]
    if key == 'refund-policy':
        return [
            ('Our promise', ['If something is not right with your order, tell us and we will put it right.']),
            ('Returns and refunds', [('ul', ['Physical items: return within 30 days in original condition for a full refund.',
                                            'Digital products and subscriptions: full refund within 14 days of purchase if you are not happy.',
                                            'Services and bookings: cancel up to 7 days before the start date for a full refund.'])]),
            ('How to ask for a refund', ['Email %s with your order number. Refunds go back to the original payment method within 5–10 working days.' % email]),
            ('Exceptions', ['Personalised items, used gift cards and services already delivered cannot be refunded unless they were faulty.']),
        ]
    return [
        ('What cookies are', ['Cookies are small files saved by your browser. They help the site work and tell us how it is used.']),
        ('Cookies we use', [('ul', ['Essential: keep you signed in, remember your cart and keep checkout secure. These cannot be turned off.',
                                   'Preferences: remember choices such as language or dark mode.',
                                   'Analytics: count visits and see which pages are useful, only with your consent.',
                                   'Marketing: measure campaigns, only with your consent.'])]),
        ('Your choices', ['You can accept or reject non-essential cookies in the cookie banner and change your choice at any time. You can also delete cookies in your browser settings.']),
        ('Contact', ['Questions about cookies: %s.' % email]),
    ]


def _g_page(title, sections, b=b, width='760px', over='Legal', sub=None, plain=False):
    body = []
    for h, items in sections:
        body.append(b.h(h, size='h4'))
        for it in items:
            body.append(b.ul(it[1]) if isinstance(it, tuple) else b.p(it))
    chips = [] if plain else [b.p(' '.join('<a class="wpe-legal-chip%s" href="%s">%s</a>' % (' is-current' if t == title.split()[0] or (t == 'Refunds' and 'Refund' in title) or (t == 'Cookies' and 'Cookie' in title) else '', u, t)
                                           for t, u in LINKS), className='wpe-legal-chips')]
    cls = 'wpe-section wpe-section--sm' + (' wpe-legal-plain' if plain else '')
    return b.join(b.section(b.p(('— ' + over) if plain else over, style='wpe-overline'), b.h(title, level=1, size='h1'),
                            b.p(sub or 'Last updated %s' % UPDATED, color='text-muted', size='small'), *chips,
                            className=cls, content=width),
                  b.section(*body, className=cls, content=width))


def _e_page(title, sections, over='Legal', sub=None):
    html = ''
    for h, items in sections:
        html += '<h3>%s</h3>' % h
        for it in items:
            html += '<ul>%s</ul>' % ''.join('<li>%s</li>' % x for x in it[1]) if isinstance(it, tuple) else '<p>%s</p>' % it
    return [e.container(e.overline(over), e.heading(title, 1, size_px=56), e.text(sub or 'Last updated %s' % UPDATED, size_px=14), pad=(80, 24, 24, 24)),
            e.container(e.text(html, 'text', size_px=16), pad=(16, 24, 96, 24))]


def pages(builder, brand, email, sells=None, kit=False, width='760px', extra=None):
    """Content items for the four legal pages, plus a demo's own policy pages.
    extra: {key: (title, slug, overline, subtitle, sections)} replaces a legal page by key or adds one."""
    extra = dict(extra or {})
    out = []
    rows = [(k, t, s, 'Legal', None, None, False) for k, t, s in PAGES]
    for i, r in enumerate(rows):
        if r[0] in (extra or {}):
            t, sl, ov, sub, secs = extra.pop(r[0])
            rows[i] = (r[0], t, sl, ov, sub, secs, True)
    rows += [(k, t, sl, ov, sub, secs, True) for k, (t, sl, ov, sub, secs) in extra.items()]
    for key, title, slug, over, sub, secs, plain in rows:
        item = {'key': key, 'type': 'page', 'title': title, 'slug': slug, 'template': 'page-full-width',
                'meta': {'_wpe_seo_description': '%s of %s.' % (title, brand)}}
        secs = secs or _sections(key, brand, email, sells)
        if builder == 'elementor':
            item['content'] = ''
            item['elementor'] = _e_page(title, secs, over, sub)
        else:
            item['content'] = _g_page(title, secs, *([__import__('kit')] if kit else [b]), width='1680px' if plain else width, over=over, sub=sub, plain=plain)
        out.append(item)
    return out


def add(package, builder, brand, email, sells=None, kit=False, width='760px', extra=None):
    """Add the legal pages to a package, replacing copies built for another builder."""
    fresh = {p['key']: p for p in pages(builder, brand, email, sells, kit, width, extra)}
    package['content'] = [fresh.pop(c.get('key'), c) for c in package.get('content', [])] + list(fresh.values())
    return package
