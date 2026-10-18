"""Essential — theme & demo marketplace demo (Pro), modelled on WPEssential.org.

Direction: the WPEssential.org Figma design (Vertex Systems Network palette: indigo #625BA8,
blue #6188C6, teal #5AC8D6 on ink #07080C; Bricolage Grotesque + Plus Jakarta Sans).
Images are real: demo thumbnails and previews of Base, Maison and Pulse, and screenshots of
the WPEssential dashboard. Products are the demos, the services and the monthly, yearly and lifetime plans.
"""
import re

import blocks as b
from ess_ui import arrow, chip, head, html, ico, over, plan_card
import ess_services as svc
import elementor as e

ID = 'essential'

MEDIA = [
    ('demo-base', 'Base demo', 'Base demo home page with a hero, services and latest posts'),
    ('demo-maison', 'Maison demo', 'Maison fashion store demo with a campaign hero and new arrivals'),
    ('demo-pulse', 'Pulse demo', 'Pulse conference demo with a stage hero and ticket passes'),
    ('site-base', 'Base home page', 'Top of the Base demo home page on desktop'),
    ('site-maison', 'Maison home page', 'Top of the Maison demo home page on desktop'),
    ('site-pulse', 'Pulse home page', 'Top of the Pulse demo home page on desktop'),
    ('mobile-base', 'Base on mobile', 'Base demo home page on a phone'),
    ('mobile-maison', 'Maison on mobile', 'Maison demo home page on a phone'),
    ('mobile-pulse', 'Pulse on mobile', 'Pulse demo home page on a phone'),
    ('shot-library', 'Demo Library', 'WPEssential dashboard showing the Demo Library with Base, Maison and Pulse'),
    ('shot-import', 'Import wizard', 'WPEssential import wizard finishing a demo import with every step logged'),
    ('shot-plugins', 'Plugins step', 'WPEssential import wizard listing the plugins a demo needs'),
]

DEMOS = [
    dict(key='p-base', name='Base', price='0', image='demo-base', site='site-base', niche='Business · Blog', menu_order=1,
         short='A clean multi-purpose site with services, blog and shop. Free with the theme.'),
    dict(key='p-maison', name='Maison', price='59', image='demo-maison', site='site-maison', niche='Fashion · WooCommerce', menu_order=2,
         short='An editorial fashion store with lookbooks, journal and a full WooCommerce shop.'),
    dict(key='p-pulse', name='Pulse', price='59', image='demo-pulse', site='site-pulse', niche='Events · Tickets', menu_order=3,
         short='A conference site with speakers, agenda, ticket passes and an events calendar.'),
]

# Same plans as the WPEssential Server (Plans::defaults()): monthly price per tier, yearly is twelve
# months less 20%, lifetime is paid once and covers the major version bought (1.x until 2.0 ships).
YEARLY_DISCOUNT = 20
TIERS = [
    dict(key='starter', name='Starter', monthly=10, lifetime=239, sites=1, white=0, short='One theme with its demos for one website.',
         inc=['1 theme + its demos', 'Gutenberg & Elementor', '1 website', 'Updates & support']),
    dict(key='pro', name='Pro', monthly=30, lifetime=719, sites=5, white=0, featured=True, short='Every theme and demo for growing studios.',
         inc=['All themes + every demo', 'Early access to new demos', '5 websites', 'Priority support']),
    dict(key='agency', name='Agency', monthly=100, lifetime=2399, sites=0, white=1, short='Unlimited client sites, white label and dedicated help.',
         inc=['Everything in Pro', 'Unlimited websites', 'White-label dashboard', 'Dedicated success manager']),
]
BILLING = [('monthly', 'Monthly', 1), ('yearly', 'Yearly', 12), ('lifetime', 'Lifetime', 0)]


def plan_price(t, period):
    if period == 'lifetime':
        return t['lifetime']
    return round(t['monthly'] * 12 * (100 - YEARLY_DISCOUNT) / 100) if period == 'yearly' else t['monthly']


PLANS = [dict(t, plan='%s_%s' % (t['key'], period), period=period, period_name=label, months=months, price=str(plan_price(t, period)),
              menu_order=i + 1) for i, t in enumerate(TIERS) for period, label, months in BILLING]
BILLING_NOTE = {'monthly': 'Billed monthly. Cancel any time.',
                'yearly': 'Billed yearly — 20% less than paying monthly.',
                'lifetime': 'Pay once. Covers the major version you buy: a 1.x license keeps working, with updates, until version 2.0 is released.'}


FEATURES = [
    ('fas fa-layer-group', 'One theme, every niche', 'Business, fashion, events and more — each demo is a style variation of the same fast block theme.'),
    ('fas fa-wand-magic-sparkles', 'Gutenberg or Elementor', 'Every demo ships a native version for both builders. Pick one in the wizard; they look the same.'),
    ('fas fa-download', 'One-click import', 'Pages, menus, products and images in under two minutes, with each step logged and resumable.'),
    ('fas fa-gauge-high', 'Fast by default', 'Local fonts, no jQuery on the front end and a 95+ PageSpeed score out of the box.'),
    ('fas fa-language', 'Ready for every language', 'WPML and Polylang compatible, RTL ready and translated into 20 languages.'),
    ('fas fa-headset', 'Real support', 'Developers who build the theme answer your tickets, usually within one business day.'),
]

# WordPress services by the WPEssential team. Prices are starting points; the order confirms the scope by email.
SERVICES = [
    ('fas fa-palette', 'Custom demo design', 'A demo designed around your brand, built on Hi WPEssential for Gutenberg or Elementor.', 'from $1,490'),
    ('fas fa-download', 'Setup & content import', 'We install the theme, import your demo, add your logo, pages and products.', 'from $149'),
    ('fas fa-cart-shopping', 'WooCommerce store setup', 'Products, payments, shipping, taxes and emails configured and tested end to end.', 'from $699'),
    ('fas fa-pen-ruler', 'Figma to WordPress', 'Your Figma or Adobe XD design built as a fast, editable WordPress site.', 'from $899'),
    ('fas fa-table-cells-large', 'Page building', 'New pages built in Gutenberg or Elementor to match your site, mobile-ready.', 'from $99 / page'),
    ('fas fa-gauge-high', 'Speed & SEO tuning', 'Core Web Vitals, image optimisation, schema and caching set up for you.', 'from $249'),
    ('fas fa-arrows-rotate', 'Migration', 'Move an existing site to the theme or a new host without losing content or rankings.', 'from $499'),
    ('fas fa-language', 'Multilingual setup', 'WPML or Polylang, translated menus and pages, and right-to-left support.', 'from $299'),
    ('fas fa-shield-halved', 'Security & malware cleanup', 'Hardening, firewall, malware removal and blacklist recovery.', 'from $199'),
    ('fas fa-screwdriver-wrench', 'Bug fixes & support', 'Broken layouts, plugin conflicts, PHP errors and white screens fixed fast.', 'from $79'),
    ('fas fa-server', 'Hosting, domain & email', 'Hosting, SSL, domain DNS and business email set up and connected.', 'from $99'),
    ('fas fa-heart-pulse', 'Care plans', 'Monthly updates, backups, uptime checks and fixes by our team.', 'from $49 / mo'),
    ('fas fa-code', 'Custom development', 'New blocks, plugins, WooCommerce features and API integrations to WordPress standards.', 'Quote'),
]


SERVICE_IMAGES = ['site-maison', 'shot-import', 'site-pulse', 'site-base', 'shot-library', 'shot-plugins'] * 3

STEPS = [('Pick a demo', 'Browse the library and open a live preview of every page.'),
         ('Choose your builder', 'Gutenberg or Elementor — both versions look the same.'),
         ('Import', 'Required plugins install themselves and content imports in about two minutes.'),
         ('Make it yours', 'Change colours, fonts and content in the Site Editor or Elementor.')]

FAQ = [
    ('Can I use a theme on client sites?', 'Yes. Each license activates one live site; the Agency plan is unlimited.'),
    ('What happens when my plan ends?', 'Your sites keep working. You lose updates, new demos and support until you renew.'),
    ('Do demos work with both Gutenberg and Elementor?', 'Yes. Every demo ships native versions for both builders and imports in one click.'),
    ('How does lifetime work?', 'Pay once and keep the version you bought: a 1.x lifetime license gets every 1.x update and demo until version 2.0 is released.'),
    ('Do you offer refunds?', 'Every plan is covered by a 14-day money-back guarantee.'),
    ('Is the theme translated?', 'Hi WPEssential ships with 20 languages and works with WPML and Polylang.'),
]

# Free plugins on WordPress.org by the WPEssential team (links go to wordpress.org).
PLUGINS = [
    ('fas fa-layer-group', 'WPEssential', 'Build complete WordPress themes with Elementor — no code. Sliders, shortcodes and theme parts in one free toolkit.',
     'v2.0 · PHP 8', 'https://wordpress.org/plugins/wpessential/'),
    ('fas fa-icons', 'WPEssential Icons', 'Four extra icon libraries for Elementor: Captain, Elementor, Ion and Type icons, ready in every widget.',
     'Needs WPEssential', 'https://wordpress.org/plugins/wpessential-icons/'),
    ('fas fa-code', 'TCI Ultimate Element Themes', 'Create themes with Elementor dynamic tags, plus lightbox video, breadcrumbs, comments and post navigation widgets.',
     'v1.0 · Elementor', 'https://wordpress.org/plugins/tci-ultimate-element-themes/'),
]

DOCS = [
    ('fas fa-rocket', 'Getting started', 'Install the theme, activate WPEssential and import your first demo.'),
    ('fas fa-download', 'Demo import', 'Gutenberg vs Elementor packages, required plugins and resuming an import.'),
    ('fas fa-palette', 'Customising', 'Style variations, global colours, fonts, header and footer.'),
    ('fas fa-key', 'License & account', 'Activate, move or renew a license and manage your subscription.'),
    ('fas fa-language', 'Translation & RTL', 'WPML, Polylang, 20 bundled languages and right-to-left sites.'),
    ('fas fa-screwdriver-wrench', 'Troubleshooting', 'Update safely, fix common errors and read the system status.'),
]

SOCIAL = [('Facebook', 'https://www.facebook.com/wpessential'), ('Instagram', 'https://www.instagram.com/wpessential'),
          ('X', 'https://x.com/wpessential'), ('YouTube', 'https://www.youtube.com/@wpessential'),
          ('LinkedIn', 'https://www.linkedin.com/company/wpessential'), ('GitHub', 'https://github.com/wpessential')]

POSTS = [
    dict(key='post-import', title='Import a demo in one click', slug='import-a-demo', image='shot-import', date='2026-09-02 09:00:00', cat='cat-guides',
         excerpt='Pick a demo, choose Gutenberg or Elementor and import pages, menus and products in about two minutes.',
         body=[('p', 'Every demo ships a Gutenberg and an Elementor version. The importer installs the plugins the demo needs, brings in pages, products, menus and images, and logs each step so you can resume if anything stops.'),
               ('h', '1. Open the Demo Library'), ('p', 'Go to WPEssential → Demo Library in wp-admin and pick a demo.'),
               ('img', 'shot-library', 'The Demo Library in the WPEssential dashboard'),
               ('h', '2. Choose your builder'), ('p', 'Select Gutenberg or Elementor. Both versions look the same.'),
               ('h', '3. Install required plugins'), ('p', 'The wizard lists the plugins and installs them for you.'),
               ('img', 'shot-plugins', 'The plugins step of the import wizard'),
               ('h', '4. Import'), ('p', 'Keep the tab open. A typical demo imports in under two minutes.')]),
    dict(key='post-builders', title='Gutenberg or Elementor: which package?', slug='gutenberg-or-elementor', image='site-maison', date='2026-09-20 09:00:00', cat='cat-guides',
         excerpt='Both versions look the same. Here is how to choose the right one for your site.',
         body=[('p', 'Each demo is built twice, natively: once with core blocks and once with Elementor widgets. Neither is a converted copy of the other.'),
               ('ul', ['Choose Gutenberg for the fastest pages and no page-builder plugin.', 'Choose Elementor if your team already edits with it.', 'Switching later means importing the other package on a fresh site.']),
               ('img', 'site-maison', 'The Maison demo home page'),
               ('quote', 'We moved three client sites to the Gutenberg packages and every one went above 95 on PageSpeed.', 'Agency customer')]),
    dict(key='post-pulse', title='New demo: Pulse for conferences and events', slug='new-demo-pulse', image='site-pulse', date='2026-10-08 09:00:00', cat='cat-news',
         excerpt='Speakers, a two-day agenda, ticket passes and The Events Calendar, ready to import.',
         body=[('p', 'Pulse is our second Pro demo. It is built for conferences, meetups and anyone who sells tickets.'),
               ('ul', ['Speaker grid and profiles', 'Two-day agenda with tracks and rooms', 'Ticket passes sold through WooCommerce', 'Events calendar with venues and organisers']),
               ('img', 'mobile-pulse', 'Pulse on a phone'),
               ('p', 'Pulse is included in the Pro and Agency plans, or you can buy it on its own.')]),
]


def post_html(body):
    out = []
    for item in body:
        if item[0] == 'p':
            out.append(b.p(item[1]))
        elif item[0] == 'h':
            out.append(b.h(item[1]))
        elif item[0] == 'ul':
            out.append(b.ul(item[1]))
        elif item[0] == 'img':
            out.append(b.image(item[1], item[2]))
        elif item[0] == 'quote':
            out.append(b.quote(item[1], item[2]))
    return b.join(*out)


# Gutenberg -----------------------------------------------------------------------------

LOGO = ('<a class="ess-logo" href="{{home:}}" aria-label="WPEssential home"><span class="ess-logo__mark" aria-hidden="true">W<i></i></span>'
        '<span class="ess-logo__text"><b>wpessential</b><small>by Vertex Systems Network (Pvt.) Ltd.</small></span></a>')
SOCIAL_ICONS = {'Facebook': 'f', 'Instagram': 'ig', 'X': 'x', 'YouTube': 'yt', 'LinkedIn': 'in', 'GitHub': 'gh'}


def g_header():
    return b.group(b.group(html(LOGO),
                           b.block('navigation', {'overlayMenu': 'mobile', 'layout': {'type': 'flex', 'justifyContent': 'center'}}),
                           b.group(b.block('search', {'label': 'Search', 'showLabel': False, 'buttonText': 'Search', 'buttonPosition': 'button-only',
                                                      'buttonUseIcon': True, 'isSearchFieldHidden': True}),
                                   b.block('woocommerce/mini-cart'),
                                   b.p('<a href="{{woo:myaccount}}">Sign in</a>', className='ess-signin'),
                                   b.buttons(arrow('Get Pro', '{{page:pricing}}')),
                                   layout='flex', wrap='nowrap', className='wpe-header-actions ess-actions'),
                           className='wpe-header__inner', layout='flex', wrap='nowrap', justify='space-between'),
                   tag='header', className='wpe-header ess-header', content='1200px')


def g_footer():
    cols = [('Marketplace', [('Demos', '{{page:demos}}'), ('Plugins', '{{page:plugins}}'), ('Pricing', '{{page:pricing}}'), ('My account', '{{woo:myaccount}}')]),
            ('Resources', [('Documentation', '{{page:docs}}'), ('Blog', '{{page:blog}}'), ('FAQ', '{{page:pricing}}')]),
            ('Company', [('About', '{{page:about}}'), ('Vertex Systems Network (Pvt.) Ltd.', '{{page:about}}'), ('Services', '{{page:services}}'), ('Contact', '{{page:contact}}')]),
            ('Support', [('Help center', '{{page:docs}}'), ('Open a ticket', '{{page:contact}}'), ('Refund policy', '{{page:refund-policy}}')])]
    band = b.group(b.group(over('Newsletter', tone='orange'), b.h('New themes and demo drops, monthly.', level=3, className='ess-band__title'),
                           b.p('One email a month. Release notes, freebies and discounts. Unsubscribe anytime.', className='ess-band__text'), layout='default'),
                   b.shortcode('[wpforms id="{{form:newsletter}}" title="false"]'),
                   layout='flex', wrap='wrap', justify='space-between', valign='center', className='ess-news')
    social = '<p class="ess-social">%s</p>' % ''.join('<a href="%s" aria-label="%s" class="ess-social__%s"></a>' % (u, n, SOCIAL_ICONS[n]) for n, u in SOCIAL)
    brand = b.column(html(LOGO), b.p('Premium WordPress themes and one-click demos for Gutenberg and Elementor.', className='ess-foot__about'),
                     html(social), b.p('@wpessential on every network', className='ess-foot__small'), width='38%')
    links = [b.column(b.h(t, level=2, className='ess-foot__h'), b.ul(['<a href="%s">%s</a>' % (h_, l) for l, h_ in items], className='ess-foot__list')) for t, items in cols]
    policies = ''.join('<a href="{{page:%s}}">%s</a>' % (k, t) for k, t in [('privacy-policy', 'Privacy'), ('terms', 'Terms'), ('refund-policy', 'Refunds'), ('cookie-policy', 'Cookies')])
    bottom = b.group(b.p('© 2026 WPEssential. A <strong>Vertex Systems Network (Pvt.) Ltd.</strong> company'), html('<p class="ess-foot__legal">%s</p>' % policies),
                     layout='flex', wrap='wrap', justify='space-between', className='ess-foot__bottom')
    return b.group(band, b.columns(brand, *links, className='ess-foot__cols'), bottom, tag='footer', className='wpe-footer ess-footer', content='1200px')


def g_title(over, title, lead):
    return b.section(b.p(over, style='wpe-overline'), b.h(title, level=1, size='h1'), b.p(lead, style='wpe-lead'),
                     className='wpe-section wpe-section--sm', content='860px')


def g_products(cat, cols=3):
    return b.shortcode('[products category="%s" columns="%d" limit="%d" orderby="menu_order" order="ASC"]' % (cat, cols, cols))


PERIOD_SUFFIX = {'monthly': '/ month', 'yearly': '/ year', 'lifetime': 'once'}


def plan_buy(pl):
    return '{{home:}}?add-to-cart={{post_id:plan-%s}}' % pl['plan']


def g_plan_cards(period):
    return b.group(*[plan_card(pl, PERIOD_SUFFIX[period], plan_buy(pl)) for pl in PLANS if pl['period'] == period],
                   layout='grid', columns=3, grid_min='17rem', className='ess-grid ess-plans')


def e_plan_cards(period):
    cards = []
    for pl in [x for x in PLANS if x['period'] == period]:
        feat = pl.get('featured')
        cards.append(e.container(
            e.heading(pl['name'], 3, size_px=22, color_slug='on-ink' if feat else 'text'),
            e.heading('$%s %s' % ('{:,}'.format(int(pl['price'])), PERIOD_SUFFIX[period]), 4, size_px=34, color_slug='on-ink' if feat else 'text'),
            e.text(pl['short'], color_slug='on-ink-muted' if feat else 'text-muted'),
            e.icon_list(pl['inc'], color_slug='on-ink' if feat else 'text'),
            e.button('Choose ' + pl['name'], plan_buy(pl), 'primary' if feat else 'outline'),
            inner=True, pad=32, radius=24, border=None if feat else 'border', bg='ink' if feat else 'surface'))
    return e.grid(*cards, columns=3)


def g_cards(items, icon_title=True):
    return b.group(*[b.group(b.h(t, level=3, size='h4'), b.p(d, color='text-muted'), *([b.p('<strong>%s</strong>' % x[3], color='primary')] if len(x) > 3 else []),
                             layout='default', className='wpe-card wpe-card--pad')
                     for x in items for t, d in [(x[1], x[2])]], layout='grid', columns=3, grid_min='16rem')


def g_plugin_cards():
    return b.group(*[b.group(b.p('Free · WordPress.org', style='wpe-overline'), b.h(t, level=3, size='h4'), b.p(d, color='text-muted'),
                             b.p(meta, color='text-muted', size='small'), b.buttons(b.button('Download', url, style='wpe-outline')),
                             layout='default', className='wpe-card wpe-card--pad') for _, t, d, meta, url in PLUGINS],
                   layout='grid', columns=3, grid_min='16rem')


def g_search(placeholder, className, button=True):
    """A core Search block (front end search, styled as the Figma search bars)."""
    attrs = {'label': 'Search', 'showLabel': False, 'placeholder': placeholder, 'buttonText': 'Search',
             'buttonPosition': 'button-inside' if button else 'no-button', 'className': className}
    return b.block('search', attrs)


DEMO_CARDS = [  # (product key, name, badge(s), price label, text, image)
    ('p-maison', 'Maison', ['PRO', 'NEW'], '$59', 'Editorial fashion store with lookbook, atelier and journal', 'demo-maison'),
    ('p-pulse', 'Pulse', ['PRO', 'NEW'], '$59', 'Conference and events site with agenda, speakers and tickets', 'demo-pulse'),
    ('p-base', 'Base', ['FREE'], 'Free', 'Business site with services, pricing, blog and a full shop', 'demo-base'),
]
ICON_FOR = {'Custom demo design': 'palette', 'Migration': 'refresh', 'Speed & SEO tuning': 'bolt', 'Care plans': 'headset'}
DOC_ICONS = ['book', 'layers', 'palette', 'key', 'globe', 'code']


def g_demo_card(key, name, badges, price, text, image):
    badge = ''.join('<span class="ess-badge %s">%s</span>' % ('ess-badge--orange' if t == 'FREE' else '', t) for t in badges)
    return b.group(
        html('<a class="ess-demo__shot" href="{{page:%s}}"><img src="{{media:%s}}" alt="%s demo" loading="lazy"><span class="ess-demo__badges">%s</span></a>' % (key, image, name, badge)),
        b.group(b.group(b.h('<a href="{{page:%s}}">%s</a>' % (key, name), level=3, className='ess-demo__name'), b.p(price, className='ess-demo__price'),
                        layout='flex', justify='space-between', valign='center'),
                b.p(text, className='ess-demo__text'),
                html('<div class="ess-demo__meta"><span>Gutenberg &amp; Elementor</span><span class="ess-gb" title="Gutenberg">G</span><span class="ess-gb" title="Elementor">E</span></div>'),
                layout='default', className='ess-demo__body'),
        layout='default', className='ess-demo')


def g_plugin_card(icon, name, text, meta, url, first=False):
    return b.group(
        b.group(ico({'fas fa-layer-group': 'layers', 'fas fa-icons': 'sparkle', 'fas fa-code': 'code'}[icon]), b.p('Free', className='ess-badge ess-badge--soft'),
                layout='flex', justify='space-between', valign='top'),
        b.h(name, level=3, className='ess-card__title'), b.p(text, className='ess-card__text'),
        b.p('Free · WordPress.org <code>%s</code>' % meta, className='ess-card__meta'),
        b.group(b.p('<strong>Free</strong>'), b.buttons(arrow('Download', url, style=None if first else 'wpe-outline')),
                layout='flex', justify='space-between', valign='center', className='ess-card__foot'),
        layout='default', className=b.cls('ess-card', 'is-active' if first else None))


def g_home():
    hero = b.group(
        html('<p class="ess-pill"><a href="{{page:p-pulse}}"><b>NEW</b> New demo: Pulse for conferences and events <span aria-hidden="true">→</span></a></p>'),
        b.h('Launch a WordPress site <span class="ess-grad">in minutes, not weeks.</span>', level=1, align='center', className='ess-hero__title'),
        b.p('Premium themes and one-click demos — native for Gutenberg and Elementor. Search, preview live, import and launch.', align='center', className='ess-hero__lead'),
        g_search('Search themes, demos and docs…', 'ess-hero__search'),
        html('<p class="ess-hero__popular"><span>Popular:</span>%s</p>' % ''.join(
            chip(t, '{{page:%s}}' % k) for t, k in [('Fashion store', 'p-maison'), ('Events', 'p-pulse'), ('Business', 'p-base'), ('Pricing', 'pricing'), ('Services', 'services'), ('Docs', 'docs')])),
        html('<ul class="ess-hero__stats">%s</ul>' % ''.join('<li><b>%s</b><span>%s</span></li>' % x for x in
                                                            [('3', 'ready demos'), ('2', 'native builders'), ('20', 'languages'), ('95+', 'PageSpeed score')])),
        html('<div class="ess-hero__shots"><img src="{{media:demo-pulse}}" alt="Pulse demo" class="is-left"><img src="{{media:demo-maison}}" alt="Maison demo" class="is-mid"><img src="{{media:demo-base}}" alt="Base demo" class="is-right"></div>'),
        tag='section', align='full', className='ess-hero', layout='constrained', content='1200px')
    cats = b.group(html('<p class="ess-cats">%s</p>' % ''.join(
        [chip('All <i>3</i>', '{{page:demos}}', 'is-on')] + [chip('%s <i>1</i>' % t, '{{page:%s}}' % k) for t, k in
                                                             [('Business', 'p-base'), ('Shop', 'p-maison'), ('Fashion', 'p-maison'), ('Events', 'p-pulse')]])),
        tag='section', align='full', className='ess-section ess-section--cats', layout='constrained', content='1200px')
    demos = b.group(
        head('Demo library', 'Pick a demo. Preview it live. Import it in one click.',
             'Every demo ships for Gutenberg and Elementor, with real content, images, hovers and dark mode.',
             action=arrow('Browse all demos', '{{page:demos}}', style='wpe-outline')),
        b.group(*[g_demo_card(*d) for d in DEMO_CARDS], layout='grid', columns=3, grid_min='18rem', className='ess-grid'),
        tag='section', align='full', className='ess-section', layout='constrained', content='1200px')
    theme = b.group(
        head('Theme & plugins', 'One theme. Every niche. Demos that do the heavy lifting.',
             'Hi WPEssential is a fast block theme with a style for every demo. Our free plugins on WordPress.org add theme building, icons and Elementor dynamic widgets.'),
        b.group(b.columns(
            b.column(html('<p class="ess-badges"><span class="ess-badge ess-badge--orange">Popular</span><span class="ess-badge ess-badge--soft">Free</span></p>'),
                     b.h('Hi WPEssential theme', level=3, className='ess-feature__title'),
                     b.p('A lightweight block theme with 8 breakpoints, light &amp; dark, 20 languages and RTL, built-in SEO and a dashboard that imports any demo in one click.', className='ess-feature__text'),
                     b.ul(['Gutenberg-native and Elementor-native demos', 'WPML &amp; Polylang ready, 20 languages', 'Lighthouse-ready performance and SEO'], className='ess-check ess-check--light'),
                     b.buttons(arrow('Get the theme', '{{page:pricing}}'), b.button('Live demo', '{{page:demos}}', style='wpe-ghost-light')),
                     width='44%', valign='center'),
            b.column(b.image('shot-library', 'The WPEssential Demo Library', className='ess-feature__shot'), width='56%', valign='center'),
            valign='center', className='ess-feature__cols'), className='ess-feature', layout='default'),
        b.group(*[g_plugin_card(*pl, first=(i == 0)) for i, pl in enumerate(PLUGINS)], layout='grid', columns=3, grid_min='18rem', className='ess-grid'),
        tag='section', align='full', className='ess-section ess-section--alt', layout='constrained', content='1200px')
    steps = [('search', 'Find your demo', 'Search the library by niche, builder or style, and click through the live preview.'),
             ('layers', 'Choose your builder', 'Pick Gutenberg or Elementor. Required plugins install themselves.'),
             ('download', 'Import in one click', 'Pages, products, menus, images and styles arrive in about two minutes. Undo anytime.')]
    how = b.group(
        head('How it works', 'From zero to a launched site in three steps.', align='center'),
        b.columns(
            b.column(*[b.group(ico(i_, 'soft'), b.group(b.h('<span>0%d</span> %s' % (n + 1, t), level=3, className='ess-step__title'), b.p(d, className='ess-step__text'), layout='default'),
                               layout='flex', valign='top', className=b.cls('ess-step', 'is-active' if n == 2 else None)) for n, (i_, t, d) in enumerate(steps)],
                     width='42%', valign='center'),
            b.column(b.group(b.image('shot-import', 'A finished import in the WPEssential wizard', className='ess-panel__shot'),
                             b.p('Real WPEssential import · every step logged · 0 errors', className='ess-panel__note'), className='ess-panel', layout='default'),
                     width='58%', valign='center'),
            valign='center', className='ess-gap'),
        tag='section', align='full', className='ess-section', layout='constrained', content='1200px')
    svc = [x for x in SERVICES if x[1] in ICON_FOR]
    services = b.group(
        head('Services by Vertex Systems Network (Pvt.) Ltd.', 'Need it done for you? Our team builds, moves and maintains WordPress sites.',
             tone='orange', action=arrow('Book a free call', '{{page:contact}}'), title_color='on-ink'),
        b.group(*[b.group(ico(ICON_FOR[t]), b.h(t, level=3, className='ess-card__title'), b.p(d, className='ess-card__text'),
                          html('<a class="ess-card__price" href="{{page:services}}"><code>%s</code><span aria-hidden="true">↗</span></a>' % price),
                          layout='default', className='ess-card ess-card--dark') for _, t, d, price in svc],
                layout='grid', columns=4, grid_min='14rem', className='ess-grid'),
        tag='section', align='full', className='ess-section ess-section--ink', layout='constrained', content='1200px')
    pricing = b.group(
        head('Pricing', 'Simple plans. Every update included.', 'From $10 a month. Save 20% yearly, or pay once for lifetime.', align='center'),
        html('<p class="ess-toggle"><a class="is-on" href="{{page:pricing}}">Monthly</a><a href="{{page:pricing}}">Yearly · save 20%</a><a href="{{page:pricing}}">Lifetime</a></p>'),
        g_plan_cards('monthly'),
        tag='section', align='full', className='ess-section', layout='constrained', content='1200px')
    docs = b.group(
        b.group(b.group(over('Documentation'), b.h('Answers in seconds, not tickets.', size='h1', className='ess-h2'), layout='default'),
                g_search('Search the docs…', 'ess-docs__search', button=False),
                layout='flex', wrap='wrap', justify='space-between', valign='bottom', className='ess-head'),
        b.group(*[b.group(ico(DOC_ICONS[i], 'soft'), b.h(t, level=3, className='ess-card__title'), b.p(d, className='ess-card__text'),
                          b.p('<a href="{{page:docs}}">Read the guide <span aria-hidden="true">→</span></a>', className='ess-card__link'),
                          layout='default', className='ess-card') for i, (_, t, d) in enumerate(DOCS)],
                layout='grid', columns=3, grid_min='18rem', className='ess-grid'),
        tag='section', align='full', className='ess-section ess-section--alt', layout='constrained', content='1200px')
    news = b.group(
        head('From the blog', 'Guides and release notes.', align='center'),
        b.latest_posts(3),
        b.buttons(arrow('All posts', '{{page:blog}}', style='wpe-outline'), justify='center'),
        tag='section', align='full', className='ess-section', layout='constrained', content='1200px')
    return b.join(hero, cats, demos, theme, how, services, pricing, docs, news)


def g_hero_light(crumb, title, lead, *extra, over_text=None):
    """Light page hero (Figma: Demos, Plugins, About, Contact, Blog)."""
    top = html('<p class="ess-crumb"><a href="{{home:}}">Home</a> <span>/</span> %s</p>' % crumb) if crumb else over(over_text)
    return b.group(top, b.h(title, level=1, className='ess-page__title'), b.p(lead, className='ess-lead'), *extra,
                   tag='section', align='full', className='ess-page-hero', layout='constrained', content='1200px')


def g_hero_dark(overline, title, lead, *extra):
    """Navy page hero (Figma: Pricing, Services, Docs)."""
    return b.group(over(overline, align='center', tone='orange'), b.h(title, level=1, align='center', className='ess-page__title ess-page__title--light'),
                   *([b.p(lead, align='center', className='ess-hero__lead')] if lead else []), *extra,
                   tag='section', align='full', className='ess-page-hero ess-page-hero--dark', layout='constrained', content='1200px')


def g_band(title, text, btn, href, tone='blue'):
    return b.group(b.group(b.h(title, level=3, className='ess-band__title'), b.p(text, className='ess-band__text'), layout='default'),
                   b.buttons(arrow(btn, href)), layout='flex', wrap='wrap', justify='space-between', valign='center',
                   className='ess-band ess-band--%s' % tone)


def section(*inner, alt=False, ink=False):
    return b.group(*inner, tag='section', align='full', layout='constrained', content='1200px',
                   className=b.cls('ess-section', 'ess-section--alt' if alt else None, 'ess-section--ink' if ink else None))


def g_demos():
    return b.join(
        g_hero_light('Demos', 'Demo library', 'Complete websites for Gutenberg and Elementor. Preview any demo live, then import it in one click.',
                     g_search('Search demos…', 'ess-hero__search ess-hero__search--light')),
        section(html('<p class="ess-cats ess-cats--left">%s</p>' % ''.join(
                    [chip('All <i>3</i>', '{{page:demos}}', 'is-on')] + [chip('%s <i>1</i>' % t, '{{page:%s}}' % k) for t, k in
                                                                         [('Business', 'p-base'), ('Fashion', 'p-maison'), ('Events', 'p-pulse')]])),
                b.group(*[g_demo_card(*d) for d in DEMO_CARDS], layout='grid', columns=3, grid_min='18rem', className='ess-grid'),
                g_band('Every demo, every builder.', 'Pro includes all demos, early access to new ones and 5 site licenses.', 'See plans', '{{page:pricing}}', tone='ink')))


def g_pricing():
    rows = [['Themes', '1', 'All', 'All'], ['Demos (Gutenberg + Elementor)', 'Theme demos', 'All', 'All'], ['Site licenses', '1', '5', 'Unlimited'],
            ['Updates &amp; support', 'Standard', 'Priority', 'Dedicated'], ['White-label dashboard', '—', '—', '✓']]
    toggle = html('<p class="ess-toggle ess-toggle--dark">%s</p>' % ''.join(
        '<a href="#%s">%s</a>' % (k, t) for k, t in [('monthly', 'Monthly'), ('yearly', 'Yearly · save 20%'), ('lifetime', 'Lifetime')]))
    periods = b.group(*[b.group(g_plan_cards(period), b.p(BILLING_NOTE[period], align='center', className='ess-note'),
                                layout='default', className='ess-period').replace('{"className":"ess-period"', '{"anchor":"%s","className":"ess-period"' % period, 1).replace('class="wp-block-group ess-period"', 'id="%s" class="wp-block-group ess-period"' % period, 1)
                        for period, _, _ in BILLING], layout='default', className='ess-periods')
    return b.join(
        g_hero_dark('Pricing', 'One subscription. Every theme and demo.',
                    'From $10 a month. Save 20% yearly, or pay once for lifetime — it covers the major version you buy (1.x until 2.0 ships).', toggle),
        b.group(periods, tag='section', align='full', layout='constrained', content='1200px', className='ess-section ess-section--pull'),
        section(b.h('Compare plans', size='h1', className='ess-h2'), b.table(['Feature', 'Starter', 'Pro', 'Agency'], rows, className='ess-table')),
        section(g_band('14-day money-back guarantee', 'Not the right fit? Email us within 14 days and get a full refund. No questions.', 'Talk to sales', '{{page:contact}}')),
        section(b.h('Frequently asked questions', size='h1', className='ess-h2'), *[b.details(q, b.p(a), style=None) for q, a in FAQ]))


SERVICE_ICONS = ['palette', 'download', 'cart', 'figma', 'grid', 'bolt', 'refresh', 'globe', 'shield', 'wrench', 'server', 'heart', 'code']


def g_services():
    steps = [('Discovery call', '30 minutes on goals, content and budget.'), ('Proposal', 'Fixed price, scope and timeline within 48 hours.'),
             ('Build', 'Weekly previews on a staging site you can click through.'), ('Launch & care', 'We go live, train your team and stay on call.')]
    return b.join(
        g_hero_dark('Services', 'Your site, launched by the people who build the theme.',
                    'Design, setup, speed and care from the WPEssential team at Vertex Systems Network (Pvt.) Ltd.',
                    b.buttons(arrow('Book a free call', '{{page:contact}}'), b.button('See pricing', '{{page:pricing}}', style='wpe-ghost-light'), justify='center')),
        section(b.h('What we do', size='h1', className='ess-h2'),
                b.group(*[b.group(ico(SERVICE_ICONS[i], 'soft'), b.h(t, level=3, className='ess-card__title'), b.p(d, className='ess-card__text'),
                                  b.p('<a href="%s">%s →</a>' % (svc_link(t), price), className='ess-card__from'), layout='default', className='ess-card')
                          for i, (_, t, d, price) in enumerate(SERVICES)], layout='grid', columns=3, grid_min='17rem', className='ess-grid')),
        section(b.h('How a project runs', size='h1', className='ess-h2'),
                b.group(*[b.group(b.p('0%d' % (i + 1), className='ess-num'), b.h(t, level=3, className='ess-card__title'), b.p(d, className='ess-card__text'), layout='default', className='ess-run')
                          for i, (t, d) in enumerate(steps)], layout='grid', columns=4, grid_min='12rem', className='ess-grid'), alt=True),
        section(g_band('Tell us about your project', 'Free 30-minute call. Fixed quote within 48 hours.', 'Book a free call', '{{page:contact}}')))


def g_docs():
    return b.join(
        g_hero_dark('Documentation', 'How can we help?', None, g_search('Search the docs…', 'ess-hero__search'),
                    html('<p class="ess-hero__popular"><span>Popular:</span>%s</p>' % ''.join(chip(t, '{{page:%s}}' % k) for t, k in
                         [('Import a demo', 'post-import'), ('Gutenberg or Elementor', 'post-builders'), ('Pricing', 'pricing'), ('Contact', 'contact')]))),
        section(b.h('Browse by topic', size='h1', className='ess-h2'),
                b.group(*[b.group(ico(DOC_ICONS[i], 'orange'), b.h(t, level=3, className='ess-card__title'), b.p(d, className='ess-card__text'),
                                  b.p('<a href="{{page:blog}}">Read guides <span aria-hidden="true">→</span></a>', className='ess-card__link ess-card__link--blue'),
                                  layout='default', className='ess-card') for i, (_, t, d) in enumerate(DOCS)],
                        layout='grid', columns=3, grid_min='18rem', className='ess-grid')),
        section(b.h('Popular articles', size='h1', className='ess-h2'), b.latest_posts(3), alt=True),
        section(b.group(ico('chat'), b.group(b.h('Still stuck?', level=3, className='ess-band__title'),
                                            b.p('Open a ticket and a WPEssential developer replies within one business day.', className='ess-band__text'), layout='default'),
                        b.buttons(arrow('Open a ticket', '{{page:contact}}')), layout='flex', wrap='wrap', valign='center', className='ess-band ess-band--soft')))


def g_plugins():
    return b.join(
        g_hero_light('Plugins', 'Free WordPress plugins', 'Made by the WPEssential team and free on WordPress.org. Install them from Plugins → Add New in your dashboard.'),
        section(b.p('<strong>3 plugins</strong> <span>All free · Elementor add-ons</span>', className='ess-count'),
                b.group(*[g_plugin_card(*pl) for pl in PLUGINS], layout='grid', columns=3, grid_min='18rem', className='ess-grid'),
                g_band('Want everything in one place?', 'Hi WPEssential and its demo library work with all three plugins out of the box.', 'Get the theme', '{{page:pricing}}', tone='ink')))


ABOUT_VALUES = [('bolt', 'Fast by default', 'Lean code, local fonts and no page-builder bloat.'),
                ('globe', 'Built for everyone', 'Accessible, RTL and 20 languages out of the box.'),
                ('shield', 'Honest pricing', 'Clear plans and a 14-day money-back guarantee.'),
                ('headset', 'Real support', 'The developers who build the theme answer your tickets.')]


def g_about():
    stat = lambda n, t: b.group(b.p(n, className='ess-stat'), b.p(t, className='ess-card__text'), layout='default', className='ess-card')
    return b.join(
        g_hero_light(None, 'We build the WordPress tools we wanted to use.',
                     'WPEssential is the WordPress product team of Vertex Systems Network (Pvt.) Ltd. We make Hi WPEssential, its demo library, free plugins and the services around them.',
                     over_text='About us'),
        section(b.columns(b.column(b.image('shot-library', 'The WPEssential Demo Library', className='ess-shot'), width='52%', valign='center'),
                          b.column(over('Our story'), b.h('From client work to products', size='h1', className='ess-h2'),
                                   b.p('We spent years building WordPress sites for clients and kept solving the same problems: slow themes, demos that broke on import and builders that locked people in.', className='ess-lead'),
                                   b.p('So we built one fast theme with a style for every niche, demos that import in one click for both Gutenberg and Elementor, and a dashboard that logs every step.', className='ess-lead'),
                                   width='48%', valign='center'), valign='center', className='ess-gap')),
        section(head('What we stand for', 'Our values'),
                b.group(*[b.group(ico(i_, 'soft'), b.h(t, level=3, className='ess-card__title'), b.p(d, className='ess-card__text'), layout='default', className='ess-card')
                          for i_, t, d in ABOUT_VALUES], layout='grid', columns=4, grid_min='14rem', className='ess-grid'), alt=True),
        section(b.group(stat('3', 'free plugins on WordPress.org'), stat('20', 'languages'), stat('2', 'native builders'), stat('1', 'theme for every niche'),
                        layout='grid', columns=4, grid_min='12rem', className='ess-grid')),
        section(b.h('Work with us', size='h1', align='center', className='ess-h2', color='on-ink'),
                b.p('Need a site built, moved or fixed? Our team takes on WordPress projects of every size.', align='center', className='ess-hero__lead'),
                b.buttons(arrow('See services', '{{page:services}}'), b.button('Contact us', '{{page:contact}}', style='wpe-light'), justify='center'), ink=True))


def wpform(key):
    return b.shortcode('[wpforms id="{{form:%s}}" title="false"]' % key)


def svc_link(name):
    return '{{page:svc-%s}}' % svc.DETAIL[name][0]


def g_contact():
    routes = [('card', 'Plans & licensing', 'Which plan fits, lifetime licences, agencies and invoices.', 'Talk to sales', '#contact-form'),
              ('palette', 'Start a project', 'Design, setup, WooCommerce, migration, speed or care plans.', 'Request a quote', '{{page:quote}}'),
              ('headset', 'Already a customer?', 'Bugs, demo import or licence questions go to our ticket desk.', 'Open a support ticket', '{{woo:myaccount}}')]
    side = [('Response times', [('Sales & pre-sales', 'Within one business day'), ('Projects & quotes', 'Scoped estimate within two business days'),
                                ('Support', 'Through the ticket desk, by licence')]),
            ('Email us directly', [('<a href="mailto:sales@wpessential.org">sales@wpessential.org</a>', 'Plans, licences, agencies'),
                                   ('<a href="mailto:services@wpessential.org">services@wpessential.org</a>', 'Projects and care plans'),
                                   ('<a href="mailto:support@wpessential.org">support@wpessential.org</a>', 'Customers with a licence')]),
            ('Office hours', [('Monday – Friday', 'Pakistan time (PKT, UTC+5)'), ('Vertex Systems Network (Pvt.) Ltd.', 'Registered company, Pakistan')])]
    side_cards = [b.group(b.h(t, level=3, className='ess-side__title'),
                          *[b.group(b.p('<strong>%s</strong>' % a_), b.p(d, className='ess-side__muted'), layout='default', className='ess-side__row') for a_, d in rows],
                          layout='default', className='ess-card ess-side') for t, rows in side]
    side_cards.append(b.group(b.h('Already a customer?', level=3, className='ess-side__title'),
                              b.p('<a href="{{page:docs}}">Documentation →</a><br><a href="{{woo:myaccount}}">Open a ticket →</a><br><a href="{{woo:myaccount}}">My account &amp; licences →</a>'),
                              layout='default', className='ess-card ess-card--ink ess-side'))
    side_cards.append(b.group(b.h('Prefer to talk?', level=3, className='ess-side__title'), b.p('Book a free 30-minute call about your project.', className='ess-side__muted'),
                              b.buttons(arrow('Book a call', '{{page:quote}}')), layout='default', className='ess-card ess-card--soft ess-side'))
    steps = [('We reply', 'A real person answers within one business day.'), ('Discovery call', '30 minutes on goals, content, budget and deadlines.'),
             ('Fixed quote', 'Scope, price and timeline in writing — no surprises.'), ('Kickoff', 'We start on the agreed date and share weekly previews.')]
    faq = [('Do you offer pre-sales help?', 'Yes — email sales@wpessential.org or use the form and we will help you pick a plan or demo.'),
           ('Which plan do I need?', 'Starter for one site, Pro for up to five, Agency for unlimited sites and white label.'),
           ('Is there a refund policy?', 'Every plan has a 14-day money-back guarantee.'),
           ('Does lifetime include future major versions?', 'A lifetime licence covers the major version you buy, e.g. 1.x until 2.0 is released.'),
           ('Do you work on sites not built with Hi WPEssential?', 'Yes. Most services work on any WordPress site.'),
           ('How are services priced?', 'We send a fixed quote from your scope; the prices shown are starting points.'),
           ('What do you need for a quote?', 'Your goals, examples you like, page count, content status and deadline.'),
           ('Do you sign NDAs?', 'Yes, on request before you share project details.'),
           ('Which time zones do you cover?', 'We work Pakistan time (UTC+5) and overlap with Europe, the Gulf and US mornings.'),
           ('How do I get technical support?', 'Customers with a licence open a ticket from My Account.'),
           ('Do you white-label for agencies?', 'Yes — choose “Agency, partnership or white label” as the topic.'),
           ('Which payment methods do you accept?', 'The methods shown at checkout, and invoices for agencies.')]
    trust = [('3 free plugins on WordPress.org', 'WPEssential, WPEssential Icons, TCI Ultimate Element Themes'), ('14-day money-back', 'On every plan, no questions asked'),
             ('Registered company', 'Vertex Systems Network (Pvt.) Ltd.'), ('Secure checkout', 'Payments by WooCommerce and trusted gateways')]
    return b.join(
        b.group(over('Contact'), b.h('How can we help?', level=1, className='ess-page__title'),
                b.p('Pre-sales questions, new projects or an existing order — pick a route and the right team replies, usually within one business day.', className='ess-lead'),
                b.group(*[b.group(ico(i_, 'soft'), b.h(t, level=3, className='ess-card__title'), b.p(d, className='ess-card__text'),
                                  b.p('<a href="%s">%s <span aria-hidden="true">→</span></a>' % (h_, l), className='ess-card__link ess-card__link--blue'),
                                  layout='default', className='ess-card') for i_, t, d, l, h_ in routes],
                        layout='grid', columns=3, grid_min='16rem', className='ess-grid'),
                tag='section', align='full', className='ess-page-hero', layout='constrained', content='1200px'),
        section(b.group(*[b.group(b.p(n, className='ess-proof__n'), b.p(l, className='ess-side__muted'), layout='default', className='ess-proof')
                          for n, l in [('1,200+', 'WordPress sites delivered'), ('4.9 / 5', 'from 640 client reviews'), ('&lt; 1 day', 'average first reply'), ('12 yrs', 'building for WordPress')]],
                        layout='grid', columns=4, grid_min='12rem', className='ess-grid'),
                b.p('<span>Trusted by agencies, stores and publishers</span>' + ''.join('<b>%s</b>' % n for n in ['Northwind', 'Kitecraft', 'Fieldhouse', 'Lumen Studio', 'Orbitly', 'Mosaic &amp; Co']),
                    className='ess-clients')),
        section(b.columns(
            b.column(b.group(html('<span id="contact-form"></span>'), b.h('Send us a message', level=2, className='ess-form__title'),
                             b.p('Fields marked * are required. We use your details only to reply.', className='ess-side__muted'),
                             wpform('contact'), layout='default', className='ess-card ess-form'), width='66%'),
            b.column(*side_cards, width='34%'), className='ess-gap ess-contact-cols')),
        section(head('What happens next', 'From message to kickoff in four steps.'),
                b.group(*[b.group(b.p('0%d' % (i + 1), className='ess-num'), b.h(t, level=3, className='ess-card__title'), b.p(d, className='ess-card__text'),
                                  layout='default', className='ess-card') for i, (t, d) in enumerate(steps)],
                        layout='grid', columns=4, grid_min='13rem', className='ess-grid'), alt=True),
        section(head('Recent work', 'Sites we built for teams like yours.', 'Every project ships on Hi WPEssential, so you can keep editing it yourself after launch.'),
                b.group(*[b.group(b.image(img, alt, aspect='16/9'), b.group(b.p(tag, className='ess-over'), b.h(t, level=3, className='ess-card__title'),
                                                                              b.p('✓ ' + r, className='ess-work__result'), layout='default', className='ess-work__body'),
                                  layout='default', className='ess-card ess-work') for img, alt, tag, t, r in [
                    ('site-base', 'Synergy Advisors business site', 'Business · Custom design', 'Synergy Advisors: new site and lead funnel in 3 weeks', '+62% enquiries in the first quarter'),
                    ('site-maison', 'Maison fashion store', 'WooCommerce store', 'Maison: fashion store with lookbook and 12 variable products', '2.1s → 0.9s load time on mobile'),
                    ('site-pulse', 'Pulse conference site', 'Events · Migration', 'Pulse Conference: agenda, tickets and Events Calendar', '2,400 tickets sold online')]],
                        layout='grid', columns=3, grid_min='16rem', className='ess-grid'),
                b.group(b.image('team-1', 'Ayesha Malik', className='ess-quote__avatar'),
                        b.group(b.p('“They scoped it in two days, launched in three weeks and still answer our tickets the same day. It feels like having our own WordPress team.”', className='ess-quote__text'),
                                b.p('Ayesha Malik · Marketing Lead, Fieldhouse Goods · ★★★★★', className='ess-quote__by'), layout='default'),
                        layout='flex', wrap='nowrap', valign='center', className='ess-card ess-card--ink ess-quote')),
        section(b.group(b.group(over('Services'), b.h('Not sure what you need? Start here.', size='h1', className='ess-h2'), layout='default'),
                        b.buttons(arrow('All services', '{{page:services}}', style='wpe-outline')),
                        layout='flex', wrap='wrap', justify='space-between', valign='bottom', className='ess-head'),
                b.group(*[b.group(ico(SERVICE_ICONS[i], 'soft'), b.group(b.p('<a href="%s"><strong>%s</strong></a>' % (svc_link(n), n)), b.p(price, className='ess-mono'), layout='default'),
                                  layout='flex', wrap='nowrap', valign='center', className='ess-card ess-mini') for i, (_, n, _d, price) in enumerate(SERVICES[:10])],
                        layout='grid', columns=5, grid_min='12rem', className='ess-grid')),
        section(b.columns(
            b.column(head('Who you will talk to', 'Real people, one team.', 'No call centre and no outsourcing — the people who build the theme run your project.'),
                     b.group(*[b.group(b.image('team-%d' % i, n, aspect='7/8', className='ess-team__img'), b.p('<strong>%s</strong>' % n), b.p(r, className='ess-side__muted'), layout='default', className='ess-team')
                               for i, (n, r) in enumerate([('Ayesha Malik', 'Project lead'), ('Bilal Khan', 'Lead developer'), ('Sara Ahmed', 'Client success'), ('Usman Tariq', 'WooCommerce')], 1)],
                             layout='grid', columns=4, grid_min='7rem', className='ess-grid'), width='55%'),
            b.column(html('<div class="ess-map"><iframe title="Office location on Google Maps" src="https://maps.google.com/maps?q=Lahore%2C%20Pakistan&amp;z=12&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'
                          '<div class="ess-map__card"><strong>Vertex Systems Network (Pvt.) Ltd.</strong><span>Lahore, Pakistan · PKT (UTC+5)</span>'
                          '<a href="https://maps.google.com/?q=Lahore%2C%20Pakistan" target="_blank" rel="noopener">Get directions ↗</a></div></div>'), width='45%'),
            className='ess-gap', valign='center')),
        section(head('FAQ', 'Questions we hear before every project.'),
                b.columns(*[b.column(*[b.details(q, b.p(a_), style=None) for q, a_ in half]) for half in (faq[:6], faq[6:])], className='ess-gap'), alt=True),
        section(b.group(*[b.group(b.p('<strong>%s</strong>' % t), b.p(d, className='ess-side__muted'), layout='default', className='ess-card ess-trust') for t, d in trust],
                        layout='grid', columns=4, grid_min='13rem', className='ess-grid'),
                g_band('Have a project in mind?', 'Free 30-minute call and a fixed quote within two business days.', 'Request a quote', '{{page:quote}}')))


def g_quote():
    return b.join(
        g_hero_light('Request a quote', 'Tell us about your project', 'A few questions, then a fixed quote, scope and timeline within two business days — free, no obligation.'),
        section(b.columns(
            b.column(b.group(wpform('quote'), layout='default', className='ess-card ess-form'), width='66%'),
            b.column(b.group(b.h('What happens next', level=3, className='ess-side__title'),
                             b.ul(['We read your brief the same day.', 'A short call if anything is unclear.', 'Fixed quote, scope and timeline within two business days.',
                                   'You approve, we schedule the kickoff.'], className='ess-check'), layout='default', className='ess-card ess-side'),
                     b.group(b.h('Prefer email?', level=3, className='ess-side__title'),
                             b.p('<a href="mailto:services@wpessential.org">services@wpessential.org</a><br>We sign NDAs on request.'),
                             layout='default', className='ess-card ess-card--ink ess-side'), width='34%'),
            className='ess-gap ess-contact-cols')))


def g_service(i):
    _, name, desc, price = SERVICES[i]
    slug, headline, who, included, turnaround = svc.DETAIL[name]
    related = [SERVICES[(i + k) % len(SERVICES)] for k in (1, 2, 3)]
    return b.join(
        b.group(b.columns(
            b.column(html('<p class="ess-crumb ess-crumb--light"><a href="{{page:services}}">Services</a> <span>/</span> %s</p>' % name),
                     over('Service', tone='orange'), b.h(headline, level=1, className='ess-page__title ess-page__title--light'),
                     b.p(desc, className='ess-hero__lead'),
                     html('<ul class="ess-facts"><li><span>Starting at</span><b>%s</b></li><li><span>Typical turnaround</span><b>%s</b></li><li><span>After launch</span><b>Fixes included</b></li></ul>'
                          % (price.replace('from ', ''), turnaround)),
                     b.buttons(arrow('Request a quote', '#service-quote'), b.button('Book a call', '{{page:contact}}', style='wpe-ghost-light')),
                     width='56%', valign='center'),
            b.column(b.image(SERVICE_IMAGES[i], name, className='ess-shot'), width='44%', valign='center'), valign='center', className='ess-gap'),
            tag='section', align='full', className='ess-page-hero ess-page-hero--dark ess-page-hero--left', layout='constrained', content='1200px'),
        section(b.columns(
            b.column(over('Who it is for'), b.h(who, size='h1', className='ess-h2'), width='50%'),
            b.column(b.group(b.h('What is included', level=3, className='ess-side__title'), b.ul(included, className='ess-check'), layout='default', className='ess-card ess-card--alt'), width='50%'),
            className='ess-gap')),
        section(head('Process', 'Brief, estimate, build, review, launch.'),
                b.group(*[b.group(b.p('0%d' % (k + 1), className='ess-num'), b.h(t, level=3, className='ess-card__title'), b.p(d, className='ess-card__text'), layout='default', className='ess-card')
                          for k, (t, d) in enumerate([('Brief', 'Call or form'), ('Estimate', 'Fixed quote in 2 days'), ('Build', 'Staging previews'),
                                                      ('Review & QA', 'Speed, mobile, accessibility'), ('Launch', 'Go live and hand over')])],
                        layout='grid', columns=5, grid_min='11rem', className='ess-grid'), alt=True),
        section(b.columns(
            b.column(over('Good to know'), b.h('What we need from you', level=3, className='ess-side__title'),
                     b.ul(['Admin access to WordPress and hosting', 'Your logo, content and examples you like', 'One person who approves the work'], className='ess-check'),
                     b.h('Not included', level=3, className='ess-side__title'),
                     b.ul(['Paid plugins, stock photos or hosting fees', 'Work outside the agreed scope (we quote it first)'], className='ess-check'), width='50%'),
            b.column(b.group(html('<span id="service-quote"></span>'), b.h('Get a quote for %s' % name, level=3, className='ess-side__title'), wpform('service'),
                             layout='default', className='ess-card ess-form'), width='50%'),
            className='ess-gap')),
        section(head('Related', 'Often booked together'),
                b.group(*[b.group(b.h('<a href="%s">%s</a>' % (svc_link(n), n), level=3, className='ess-card__title'), b.p(d, className='ess-card__text'),
                                  b.p(p_, className='ess-mono'), layout='default', className='ess-card') for _, n, d, p_ in related],
                        layout='grid', columns=3, grid_min='16rem', className='ess-grid'), alt=True))


# Elementor ------------------------------------------------------------------------------

def e_title(over, title, lead):
    return e.container(e.overline(over), e.heading(title, 1, size_px=64), e.text(lead, size_px=18), pad=(80, 24, 32, 24))


def e_cards(items, columns=3):
    return e.grid(*[e.container(e.heading(x[1], 3, size_px=22), e.text(x[2]), *([e.heading(x[3], 4, 'primary', size_px=16)] if len(x) > 3 else []),
                                inner=True, pad=28, radius=20, border='border', bg='surface', gap=12) for x in items], columns=columns)


def e_icon_cards(items, columns=3):
    return e.grid(*[e.icon_box(x[0], x[1], x[2] + ('<br><strong>%s</strong>' % x[3] if len(x) > 3 else '')) for x in items], columns=columns)


def e_products(cat, cols=3):
    return e.shortcode('[products category="%s" columns="%d" limit="%d" orderby="menu_order" order="ASC"]' % (cat, cols, cols))


def e_plugin_cards():
    return e.grid(*[e.container(e.overline('Free · WordPress.org'), e.heading(t, 3, size_px=22), e.text(d), e.text(meta, size_px=14),
                                e.button('Download', url, 'outline'), inner=True, pad=28, radius=20, border='border', bg='surface', gap=12)
                    for _, t, d, meta, url in PLUGINS], columns=3)


def e_plugins():
    return [e_title('Plugins', 'Free WordPress plugins', 'Made by the WPEssential team and free on WordPress.org. Install them from Plugins → Add New in your dashboard.'),
            e.container(e_plugin_cards(), pad=(24, 24, 56, 24)),
            e.container(e.heading('Want everything in one place?', 3, 'on-ink', align='center'),
                        e.text('Hi WPEssential and its demo library work with all three plugins out of the box.', 'on-ink-muted', align='center'),
                        e.button('Get the theme', '{{page:pricing}}', align='center'), bg='ink', pad=(64, 24, 64, 24), align='center')]


def e_home():
    return [
        e.container(e.overline('Hi WPEssential · by Vertex Systems Network (Pvt.) Ltd.', 'accent', align='center'),
                    e.heading('Launch a beautiful WordPress site in minutes', 1, 'on-ink', align='center', size_px=80),
                    e.text('Premium themes and one-click demos — native for Gutenberg and Elementor. Pick a demo, import it, make it yours.', 'on-ink-muted', align='center', size_px=18),
                    e.row(e.button('Browse demos', '{{page:demos}}'), e.button('See pricing', '{{page:pricing}}', 'light'), gap=12, justify='center', wrap=True, mobile_direction='row'),
                    e.image('shot-library', 'The WPEssential Demo Library', radius=20),
                    bg='ink', pad=(112, 24, 96, 24), align='center'),
        e.container(e.grid(e.counter(3, 'ready-made demos'), e.counter(2, 'native builders'), e.counter(20, 'languages'), e.counter(95, 'PageSpeed score', '+'), columns=4),
                    pad=(56, 24, 56, 24)),
        e.container(e.overline('Demos'), e.heading('Start from a finished site'), e_products('demos'), e.button('All demos', '{{page:demos}}', 'outline'),
                    bg='surface-alt', pad=(80, 24, 80, 24)),
        e.container(e.overline('The theme'), e.heading('One theme. Every niche. Demos that do the heavy lifting.'), e_icon_cards(FEATURES), pad=(80, 24, 80, 24)),
        e.container(e.overline('Free plugins'), e.heading('Free on WordPress.org'), e_plugin_cards(), e.button('All plugins', '{{page:plugins}}', 'outline'),
                    pad=(80, 24, 80, 24)),
        e.container(e.row(e.col(e.overline('How it works'), e.heading('From zero to launch in four steps'),
                                *[e.container(e.heading('%d. %s' % (i + 1, t), 3, size_px=22), e.text(d), inner=True, gap=6) for i, (t, d) in enumerate(STEPS)], width=45, justify='center'),
                          e.col(e.image('shot-import', 'A finished import in the WPEssential wizard', radius=20),
                                e.text('Real WPEssential import · every step logged · 0 errors', align='center', size_px=14), width=55, justify='center'), gap=56),
                    bg='surface-alt', pad=(80, 24, 80, 24)),
        e.container(e.overline('Pricing', align='center'), e.heading('One subscription. Every theme and demo.', align='center'),
                    e.text('From $10 a month. Save 20% yearly, or pay once for lifetime.', align='center'), e_plan_cards('monthly'),
                    e.button('Yearly & lifetime prices', '{{page:pricing}}', 'outline', align='center'), pad=(80, 24, 80, 24)),
        e.container(e.overline('Blog'), e.heading('Guides and release notes'),
                    e.grid(*[e.image_box(p['image'], p['title'], p['excerpt'], link='{{page:%s}}' % p['key'], height=220) for p in POSTS], columns=3),
                    bg='surface-alt', pad=(80, 24, 80, 24)),
        e.container(e.container(e.heading('Ready to launch?', 2, 'on-primary', align='center'), e.text('Get every theme and demo with Pro. Cancel any time.', 'on-primary', align='center'),
                                e.button('Get Pro — $30/month', '{{page:pricing}}', 'light', align='center'), inner=True, bg='primary', radius=28, pad=56, align='center'),
                    pad=(48, 24, 80, 24)),
    ]


def e_demos():
    out = [e_title('Demos', 'Start from a finished site', 'Every demo imports in one click and ships native Gutenberg and Elementor versions.'),
           e.container(e_products('demos'), pad=(24, 24, 56, 24))]
    for i, d in enumerate(DEMOS):
        out.append(e.container(e.row(e.col(e.image(d['site'], '%s demo home page' % d['name'], radius=20), width=60),
                                     e.col(e.overline(d['niche']), e.heading(d['name']), e.text(d['short']),
                                           e.heading('Free with the theme' if d['price'] == '0' else '$%s or included in Pro' % d['price'], 4, size_px=18),
                                           e.row(e.button('View demo', '{{page:%s}}' % d['key']), e.button('Pricing', '{{page:pricing}}', 'outline'), gap=12, wrap=True, mobile_direction='row'),
                                           width=40, justify='center'), gap=56),
                               bg='surface-alt' if i % 2 else None, pad=(72, 24, 72, 24)))
    return out


def e_pricing():
    return [e_title('Pricing', 'One subscription. Every theme and demo.', 'Monthly, yearly (save 20%) or lifetime. Every plan has a 14-day money-back guarantee.'),
            *[e.container(e.heading(label + (' · save 20%' if period == 'yearly' else ''), 3), e.text(BILLING_NOTE[period]), e_plan_cards(period),
                          bg='surface-alt' if period == 'yearly' else None, pad=(48, 24, 48, 24)) for period, label, _ in BILLING],
            e.container(e.heading('What is included'), e.grid(*[e.container(e.heading(p['name'], 3, size_px=22), e.icon_list(p['inc']), inner=True, pad=28, radius=20, border='border', bg='surface')
                                                               for p in TIERS], columns=3), bg='surface-alt', pad=(72, 24, 72, 24)),
            e.container(e.heading('Frequently asked questions'), e.accordion(FAQ), pad=(72, 24, 72, 24))]


def e_services():
    return [e_title('Services', 'Your site, launched by the people who build the theme.', 'Design, setup, speed and care from the WPEssential team at Vertex Systems Network (Pvt.) Ltd.'),
            e.container(e.shortcode('[products category="services" columns="3" limit="12" orderby="menu_order" order="ASC"]'), pad=(24, 24, 72, 24)),
            e.container(e.heading('How a project runs'),
                        e.grid(*[e.container(e.heading('0%d' % (i + 1), 3, 'primary', size_px=40), e.heading(t, 3, size_px=22), e.text(d), inner=True, gap=8)
                                 for i, (t, d) in enumerate([('Discovery call', '30 minutes on goals, content and budget.'), ('Proposal', 'Fixed price, scope and timeline within 48 hours.'),
                                                             ('Build', 'Weekly previews on a staging site you can click through.'), ('Launch & care', 'We go live, train your team and stay on call.')])], columns=4),
                        bg='surface-alt', pad=(72, 24, 72, 24)),
            e.container(e.heading('Tell us about your project', 3, align='center'), e.text('Free 30-minute call. Fixed quote within 48 hours.', align='center'),
                        e.button('Book a free call', '{{page:contact}}', align='center'), pad=(72, 24, 72, 24))]


def e_docs():
    return [e_title('Documentation', 'How can we help?', 'Guides for the theme, the demos and your account.'),
            e.container(e.text('<form role="search" method="get" action="{{home:}}" class="wpe-search-form"><input type="search" name="s" placeholder="Search the docs…" aria-label="Search the docs"> <button type="submit">Search</button></form>'), pad=(0, 24, 40, 24)),
            e.container(e.heading('Browse by topic'), e_icon_cards(DOCS), pad=(40, 24, 72, 24)),
            e.container(e.heading('Popular articles'), e.grid(*[e.image_box(p['image'], p['title'], p['excerpt'], link='{{page:%s}}' % p['key'], height=220) for p in POSTS], columns=3),
                        bg='surface-alt', pad=(72, 24, 72, 24)),
            e.container(e.heading('Still stuck?', 3, align='center'), e.text('Open a ticket and a WPEssential developer replies within one business day.', align='center'),
                        e.button('Open a ticket', '{{page:contact}}', align='center'), pad=(72, 24, 72, 24))]


def e_about():
    return [e_title('About us', 'We build the WordPress tools we wanted to use.',
                    'WPEssential is the WordPress product team of Vertex Systems Network (Pvt.) Ltd. We make Hi WPEssential, its demo library, free plugins and the services around them.'),
            e.container(e.row(e.col(e.image('shot-library', 'The WPEssential Demo Library', radius=20), width=55),
                              e.col(e.overline('Our story'), e.heading('From client work to products'),
                                    e.text('We spent years building WordPress sites for clients and kept solving the same problems: slow themes, demos that broke on import and builders that locked people in.'),
                                    e.text('So we built one fast theme with a style for every niche, demos that import in one click for both Gutenberg and Elementor, and a dashboard that logs every step.'),
                                    width=45, justify='center'), gap=56), pad=(24, 24, 72, 24)),
            e.container(e.overline('What we stand for'), e.heading('Our values'), e_icon_cards(ABOUT_VALUES, 4), bg='surface-alt', pad=(72, 24, 72, 24)),
            e.container(e.grid(e.counter(3, 'free plugins on WordPress.org'), e.counter(20, 'languages'), e.counter(2, 'native builders'), e.counter(1, 'theme for every niche'), columns=4),
                        pad=(56, 24, 56, 24)),
            e.container(e.heading('Work with us', 3, 'on-ink', align='center'),
                        e.text('Need a site built, moved or fixed? Our team takes on WordPress projects of every size.', 'on-ink-muted', align='center'),
                        e.row(e.button('See services', '{{page:services}}'), e.button('Contact us', '{{page:contact}}', 'light'), gap=12, justify='center', wrap=True, mobile_direction='row'),
                        bg='ink', pad=(64, 24, 64, 24), align='center')]


def e_contact():
    return [e_title('Contact', 'Talk to the WPEssential team', 'We reply within one business day, Monday to Friday.'),
            e.container(e.grid(*[e.icon_box(i, t, d) for i, t, d in [('fas fa-life-ring', 'Support', 'support@wpessential.org'), ('fas fa-cart-shopping', 'Sales', 'sales@wpessential.org'),
                                                                     ('fas fa-briefcase', 'Services', 'services@wpessential.org')]], columns=3), pad=(24, 24, 40, 24)),
            e.container(e.heading('Follow @wpessential', 3), e.widget('social-icons', {'social_icon_list': [
                {'_id': e._id(), 'social_icon': {'value': 'fab fa-%s' % ('x-twitter' if n == 'X' else n.lower()), 'library': 'fa-brands'},
                 'link': {'url': u, 'is_external': 'on', 'nofollow': ''}} for n, u in SOCIAL], 'shape': 'circle'}), pad=(24, 24, 80, 24))]


# Package --------------------------------------------------------------------------------

def package(builder):
    e.HOVER = {'button': 'float', 'image': 'grow', 'icon': 'float', 'filter_rest': None, 'invert': False, 'transition': 0.24}
    e.FONTS = {'primary': 'Bricolage Grotesque', 'text': 'Plus Jakarta Sans', 'weight': '700'}
    media = [{'key': k, 'file': k + '.webp', 'title': t, 'alt': a} for k, t, a in MEDIA]
    terms = [
        {'key': 'pc-demos', 'taxonomy': 'product_cat', 'name': 'Demos', 'slug': 'demos', 'meta': {'thumbnail_id': '{{media_id:demo-maison}}'}},
        {'key': 'pc-plans-monthly', 'taxonomy': 'product_cat', 'name': 'Monthly plans', 'slug': 'plans-monthly', 'meta': {'thumbnail_id': '{{media_id:shot-library}}'}},
        {'key': 'pc-plans-yearly', 'taxonomy': 'product_cat', 'name': 'Yearly plans', 'slug': 'plans-yearly', 'meta': {'thumbnail_id': '{{media_id:shot-library}}'}},
        {'key': 'pc-plans-lifetime', 'taxonomy': 'product_cat', 'name': 'Lifetime plans', 'slug': 'plans-lifetime', 'meta': {'thumbnail_id': '{{media_id:shot-library}}'}},
        {'key': 'pc-services', 'taxonomy': 'product_cat', 'name': 'Services', 'slug': 'services', 'meta': {'thumbnail_id': '{{media_id:shot-import}}'}},
        {'key': 'cat-news', 'taxonomy': 'category', 'name': 'News', 'slug': 'news'},
        {'key': 'cat-guides', 'taxonomy': 'category', 'name': 'Guides', 'slug': 'guides'},
    ]
    pages = [('home', 'Home', 'home', g_home, e_home), ('demos', 'Demos', 'demos', g_demos, e_demos),
             ('plugins', 'Plugins', 'plugins', g_plugins, e_plugins), ('pricing', 'Pricing', 'pricing', g_pricing, e_pricing), ('services', 'Services', 'services', g_services, e_services),
             ('docs', 'Docs', 'docs', g_docs, e_docs), ('about', 'About', 'about', g_about, e_about),
             ('contact', 'Contact', 'contact', g_contact, e_contact), ('quote', 'Request a quote', 'request-a-quote', g_quote, e_contact)]
    pages += [('svc-' + svc.DETAIL[n][0], n, svc.DETAIL[n][0], (lambda i=i: g_service(i)), e_services) for i, (_, n, _d, _p) in enumerate(SERVICES)]
    content = []
    for key, title, slug, g, el in pages:
        item = {'key': key, 'type': 'page', 'title': title, 'slug': slug, 'template': 'page-full-width'}
        if builder == 'gutenberg':
            item['content'] = g()
        else:
            item['content'] = ''
            item['elementor'] = el()
        content.append(item)
    content.append({'key': 'blog', 'type': 'page', 'title': 'Blog', 'slug': 'blog', 'content': ''})
    for p in POSTS:
        content.append({'key': p['key'], 'type': 'post', 'title': p['title'], 'slug': p['slug'], 'featured': p['image'], 'date': p['date'],
                        'terms': {'category': [p['cat']]}, 'excerpt': p['excerpt'], 'content': post_html(p['body'])})
    products = []
    for d in DEMOS:
        products.append({'key': d['key'], 'name': d['name'] + ' demo', 'slug': d['name'].lower() + '-demo', 'price': d['price'], 'categories': ['pc-demos'],
                         'image': d['image'], 'gallery': [d['site'], 'mobile-' + d['name'].lower()], 'virtual': True, 'menu_order': d['menu_order'], 'short': d['short'],
                         'description': '<p>%s</p><ul><li>Native Gutenberg and Elementor versions</li><li>One-click import with required plugins</li><li>Light &amp; dark style variation</li></ul>' % d['short']})
    for pl in PLANS:
        products.append({'key': 'plan-' + pl['plan'], 'name': '%s %s' % (pl['name'], pl['period_name']), 'slug': 'plan-' + pl['plan'].replace('_', '-'),
                         'price': pl['price'], 'categories': ['pc-plans-' + pl['period']], 'image': 'shot-library', 'virtual': True, 'sold_individually': True,
                         'menu_order': pl['menu_order'], 'featured': pl.get('featured', False) and pl['period'] == 'yearly', 'short': pl['short'],
                         # WPEssential Server reads these: the order creates the license, renewals and upgrades follow the plan.
                         'meta': {'_wpes_plan': pl['plan'], '_wpes_months': pl['months'], '_wpes_sites': pl['sites'], '_wpes_white_label': pl['white'], '_wpes_pro': 1},
                         'description': '<p>%s</p><ul>%s</ul><p>%s 14-day money-back guarantee.</p>' % (
                             pl['short'], ''.join('<li>%s</li>' % i for i in pl['inc']), BILLING_NOTE[pl['period']])})
    for i, (icon, name, desc, price) in enumerate(SERVICES):
        amount = (re.findall(r'\$([\d,]+)', price) or [''])[0].replace(',', '')
        products.append({'key': 'svc-%d' % i, 'name': name, 'slug': 'service-' + name.lower().replace(' & ', '-').replace(' ', '-'),
                         'price': amount if amount.isdigit() else '', 'categories': ['pc-services'], 'image': SERVICE_IMAGES[i], 'virtual': True,
                         'menu_order': i + 1, 'short': desc,
                         'description': '<p>%s</p><p>After checkout we email you within one business day to book a kick-off call.</p>' % desc})
    menus = [{'name': 'Essential menu', 'location': 'primary', 'items': [
        {'title': 'Demos', 'page': 'demos'}, {'title': 'Plugins', 'page': 'plugins'}, {'title': 'Docs', 'page': 'docs'}, {'title': 'Services', 'page': 'services'},
        {'title': 'Pricing', 'page': 'pricing'}, {'title': 'Blog', 'page': 'blog'}]}]
    settings = {'front_page': 'home', 'blog_page': 'blog', 'style': 'essential', 'motion': 'subtle',
                'parts': {'header': {'content': g_header()}, 'header-shop': {'content': g_header()}, 'header-transparent': {'content': g_header()}, 'footer': {'content': g_footer()}},
                'options': {'posts_per_page': 9, 'blogname': 'WPEssential', 'blogdescription': 'Premium WordPress themes and one-click demos.'},
                'woocommerce': {'options': {'woocommerce_currency': 'USD', 'woocommerce_currency_pos': 'left', 'woocommerce_price_num_decimals': '0',
                                            'woocommerce_coming_soon': 'no', 'woocommerce_store_pages_only': 'no',
                                            'woocommerce_enable_guest_checkout': 'yes',
                                            'woocommerce_cod_settings': {'enabled': 'yes', 'title': 'Pay by invoice'}}}}
    if builder == 'elementor':
        settings['elementor_kit'] = e.kit(container_width=1200, button_radius=999, field_radius=12, button_padding=(14, 24))
    forms = [f() for f in svc.FORMS]
    return {'version': 1, 'media': media, 'terms': terms, 'forms': forms, 'content': content, 'products': products, 'menus': menus, 'settings': settings}


DEMO = {
    'id': ID, 'title': 'Essential', 'tagline': 'A theme and demo marketplace with pricing plans, services and docs — the WPEssential.org site.',
    'niche': ['marketplace', 'software', 'saas', 'digital products'], 'type': 'pro',
    'thumbnail': 'demo:thumbnail.webp',
    'preview': {'desktop': 'demo:preview/desktop.webp', 'tablet': 'demo:preview/tablet.webp', 'mobile': 'demo:preview/mobile.webp'},
    'pages': ['Home', 'Demos', 'Plugins', 'About', 'Pricing', 'Services', 'Docs', 'Blog', 'Contact'],
    'highlights': ['3 demo products', 'Monthly, yearly & lifetime plans', 'Docs & services', 'Light & dark'],
    'motion': 'subtle',
    'plugins': [{'slug': 'woocommerce', 'name': 'WooCommerce', 'source': 'wporg', 'state': 'required'},
                {'slug': 'wpforms-lite', 'name': 'WPForms Lite', 'source': 'wporg', 'state': 'required'}],
}

SEO = {
    'about': 'About WPEssential, the WordPress product team of Vertex Systems Network (Pvt.) Ltd.: our story, values and the people behind Hi WPEssential.',
    'plugins': 'Free WordPress plugins by WPEssential on WordPress.org: theme building, icon libraries and Elementor dynamic widgets.',
    'home': 'Premium WordPress themes and one-click demos, native for Gutenberg and Elementor. Pick a demo, import it and launch.',
    'demos': 'Browse ready-made WordPress demos for business, fashion and events. Every demo imports in one click.',
    'pricing': 'Plans for every theme and demo from $10 a month: save 20% yearly or pay once for lifetime. 14-day money-back guarantee.',
    'services': 'Custom demo design, setup, speed tuning, migration and care plans from the WPEssential team.',
    'docs': 'Documentation for the Hi WPEssential theme, demo import, customising, licenses and translation.',
    'contact': 'Contact WPEssential support, sales or services. We reply within one business day.',
    'blog': 'Guides, release notes and new demos from the WPEssential team.',
}

# Brand, contact email and what the site sells, used for the legal pages (tools/demo-builder/legal.py).
LEGAL = ('WPEssential, a Vertex Systems Network (Pvt.) Ltd. company', 'support@wpessential.org', 'themes, demos, plans and services')
