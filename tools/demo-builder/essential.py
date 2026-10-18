"""Essential — theme & demo marketplace demo (Pro), modelled on WPEssential.org.

Direction: the WPEssential.org Figma design (Vertex Systems Network palette: indigo #625BA8,
blue #6188C6, teal #5AC8D6 on ink #07080C; Bricolage Grotesque + Plus Jakarta Sans).
Images are real: demo thumbnails and previews of Base, Maison and Pulse, and screenshots of
the WPEssential dashboard. Products are the demos, the services and the monthly, yearly and lifetime plans.
"""
import re

import blocks as b
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

def g_header():
    return b.group(b.group(b.group(b.block('site-title'), layout='flex', wrap='nowrap'),
                           b.block('navigation', {'overlayMenu': 'mobile', 'layout': {'type': 'flex', 'justifyContent': 'center'}}),
                           b.buttons(b.button('Get Pro', '{{page:pricing}}')),
                           className='wpe-header__inner', layout='flex', wrap='nowrap', justify='space-between'),
                   tag='header', className='wpe-header', content='1200px')


def g_footer():
    return b.footer('Premium WordPress themes and one-click demos for Gutenberg and Elementor. @wpessential on every network.',
                    [('Marketplace', [('Demos', '{{page:demos}}'), ('Plugins', '{{page:plugins}}'), ('Pricing', '{{page:pricing}}'), ('Services', '{{page:services}}'), ('My account', '{{woo:myaccount}}')]),
                     ('Company', [('About', '{{page:about}}'), ('Services', '{{page:services}}'), ('Blog', '{{page:blog}}'), ('Contact', '{{page:contact}}')]),
                     ('Resources', [('Documentation', '{{page:docs}}'), ('Plugins', '{{page:plugins}}'), ('FAQ', '{{page:pricing}}'), ('Support', '{{page:contact}}')]),
                     ('Follow', SOCIAL)],
                    ('© WPEssential. A Vertex Systems Network (Pvt.) Ltd. company.', 'Built with <a href="https://wpessential.org/">Hi WPEssential</a>'),
                    band=('New themes and demo drops, monthly.', 'One email a month. Release notes, freebies and discounts.', 'See pricing', '{{page:pricing}}'))


def g_title(over, title, lead):
    return b.section(b.p(over, style='wpe-overline'), b.h(title, level=1, size='h1'), b.p(lead, style='wpe-lead'),
                     className='wpe-section wpe-section--sm', content='860px')


def g_products(cat, cols=3):
    return b.shortcode('[products category="%s" columns="%d" limit="%d" orderby="menu_order" order="ASC"]' % (cat, cols, cols))


def g_cards(items, icon_title=True):
    return b.group(*[b.group(b.h(t, level=3, size='h4'), b.p(d, color='text-muted'), *([b.p('<strong>%s</strong>' % x[3], color='primary')] if len(x) > 3 else []),
                             layout='default', className='wpe-card wpe-card--pad')
                     for x in items for t, d in [(x[1], x[2])]], layout='grid', grid_min='16rem')


def g_plugin_cards():
    return b.group(*[b.group(b.p('Free · WordPress.org', style='wpe-overline'), b.h(t, level=3, size='h4'), b.p(d, color='text-muted'),
                             b.p(meta, color='text-muted', size='small'), b.buttons(b.button('Download', url, style='wpe-outline')),
                             layout='default', className='wpe-card wpe-card--pad') for _, t, d, meta, url in PLUGINS],
                   layout='grid', grid_min='16rem')


def g_plugins():
    return b.join(g_title('Plugins', 'Free WordPress plugins', 'Made by the WPEssential team and free on WordPress.org. Install them from Plugins → Add New in your dashboard.'),
                  b.section(g_plugin_cards(), className='wpe-section wpe-section--sm'),
                  b.section(b.h('Want everything in one place?', size='h3', align='center', color='on-ink'),
                            b.p('Hi WPEssential and its demo library work with all three plugins out of the box.', align='center', color='on-ink'),
                            b.buttons(b.button('Get the theme', '{{page:pricing}}'), justify='center'), bg='ink', color='on-ink', content='720px'))


def g_home():
    hero = b.section(
        b.p('Hi WPEssential · by Vertex Systems Network (Pvt.) Ltd.', style='wpe-overline', color='accent', align='center'),
        b.h('Launch a beautiful WordPress site in minutes', level=1, size='display', color='on-ink', align='center'),
        b.p('Premium themes and one-click demos — native for Gutenberg and Elementor. Pick a demo, import it, make it yours.', color='on-ink', size='body-l', align='center'),
        b.buttons(b.button('Browse demos', '{{page:demos}}'), b.button('See pricing', '{{page:pricing}}', style='wpe-light'), justify='center'),
        b.image('shot-library', 'The WPEssential Demo Library', style='wpe-shadow'),
        bg='ink', color='on-ink', content='1100px')
    stats = b.section(b.group(*[b.group(b.p(n, className='wpe-stat-number'), b.p(t, color='text-muted'), layout='default', className='wpe-card wpe-card--pad')
                                for n, t in [('3', 'ready-made demos'), ('2', 'native builders'), ('20', 'languages'), ('95+', 'PageSpeed score')]],
                              layout='grid', grid_min='12rem'), className='wpe-section wpe-section--sm')
    demos = b.section(
        b.group(b.group(b.p('Demos', style='wpe-overline'), b.h('Start from a finished site', size='h2'), layout='default'),
                b.buttons(b.button('All demos', '{{page:demos}}', style='wpe-outline')), layout='flex', wrap='wrap', justify='space-between', valign='bottom'),
        g_products('demos'), bg='surface-alt')
    features = b.section(b.p('The theme', style='wpe-overline'), b.h('One theme. Every niche. Demos that do the heavy lifting.', size='h2'),
                         g_cards(FEATURES))
    how = b.section(b.columns(
        b.column(b.p('How it works', style='wpe-overline'), b.h('From zero to launch in four steps', size='h2'),
                 *[b.group(b.h('%d. %s' % (i + 1, t), level=3, size='h4'), b.p(d, color='text-muted'), layout='default') for i, (t, d) in enumerate(STEPS)],
                 width='45%', valign='center'),
        b.column(b.image('shot-import', 'A finished import in the WPEssential wizard', style='wpe-shadow'),
                 b.p('Real WPEssential import · every step logged · 0 errors', color='text-muted', size='small', align='center'), width='55%', valign='center'),
        valign='center', className='wpe-gap-lg'), bg='surface-alt')
    plugins = b.section(
        b.group(b.group(b.p('Free plugins', style='wpe-overline'), b.h('Free on WordPress.org', size='h2'), layout='default'),
                b.buttons(b.button('All plugins', '{{page:plugins}}', style='wpe-outline')), layout='flex', wrap='wrap', justify='space-between', valign='bottom'),
        g_plugin_cards())
    pricing = b.section(b.p('Pricing', style='wpe-overline', align='center'), b.h('One subscription. Every theme and demo.', size='h2', align='center'),
                        b.p('From $10 a month. Save 20% yearly, or pay once for lifetime.', align='center', color='text-muted'), g_products('plans-monthly'),
                        b.buttons(b.button('Yearly & lifetime prices', '{{page:pricing}}', style='wpe-outline'), justify='center'))
    news = b.section(b.group(b.group(b.p('Blog', style='wpe-overline'), b.h('Guides and release notes', size='h2'), layout='default'),
                             b.buttons(b.button('All posts', '{{page:blog}}', style='wpe-outline')), layout='flex', wrap='wrap', justify='space-between', valign='bottom'),
                     b.latest_posts(3), bg='surface-alt')
    cta = b.section(b.group(b.h('Ready to launch?', size='h2', color='on-ink', align='center'),
                            b.p('Get every theme and demo with Pro. Cancel any time.', color='on-ink', align='center'),
                            b.buttons(b.button('Get Pro — $30/month', '{{page:pricing}}', style='wpe-light'), justify='center'),
                            className='wpe-card wpe-cta-gradient', bg='primary', color='on-primary'))
    return b.join(hero, stats, demos, features, plugins, how, pricing, news, cta)


def g_demos():
    showcase = [b.section(b.columns(
        b.column(b.image(d['site'], '%s demo home page' % d['name'], style='wpe-shadow'), width='60%'),
        b.column(b.p(d['niche'], style='wpe-overline'), b.h(d['name'], size='h2'), b.p(d['short'], color='text-muted'),
                 b.p('<strong>%s</strong>' % ('Free with the theme' if d['price'] == '0' else '$%s or included in Pro' % d['price'])),
                 b.buttons(b.button('View demo', '{{product:%s}}' % d['key']), b.button('Pricing', '{{page:pricing}}', style='wpe-outline')),
                 width='40%', valign='center'), valign='center', className='wpe-gap-lg'), bg='surface-alt' if i % 2 else None)
        for i, d in enumerate(DEMOS)]
    return b.join(g_title('Demos', 'Start from a finished site', 'Every demo imports in one click and ships native Gutenberg and Elementor versions.'),
                  b.section(g_products('demos'), className='wpe-section wpe-section--sm'), *showcase)


def g_pricing():
    rows = [['Themes', '1', 'All', 'All'], ['Demos (Gutenberg + Elementor)', 'Theme demos', 'All', 'All'], ['Site licenses', '1', '5', 'Unlimited'],
            ['Support', 'Standard', 'Priority', 'Dedicated'], ['Updates', 'While subscribed · lifetime: one major version', 'Same', 'Same'], ['White-label dashboard', '—', '—', '✓']]
    return b.join(g_title('Pricing', 'One subscription. Every theme and demo.', 'Monthly, yearly (save 20%) or lifetime. Every plan has a 14-day money-back guarantee.'),
                  *[b.section(b.h(label + (' · save 20%' if period == 'yearly' else ''), size='h3'), b.p(BILLING_NOTE[period], color='text-muted'),
                              g_products('plans-' + period), className='wpe-section wpe-section--sm', bg='surface-alt' if period == 'yearly' else None)
                    for period, label, _ in BILLING],
                  b.section(b.h('Compare plans', size='h2'), b.table(['Feature', 'Starter', 'Pro', 'Agency'], rows), bg='surface-alt'),
                  b.section(b.h('Frequently asked questions', size='h2'), *[b.details(q, b.p(a)) for q, a in FAQ], content='860px'))


def g_services():
    return b.join(g_title('Services', 'Your site, launched by the people who build the theme.', 'Design, setup, speed and care from the WPEssential team at Vertex Systems Network (Pvt.) Ltd.'),
                  b.section(b.shortcode('[products category="services" columns="3" limit="12" orderby="menu_order" order="ASC"]'), className='wpe-section wpe-section--sm'),
                  b.section(b.h('How a project runs', size='h2'),
                            b.group(*[b.group(b.p('0%d' % (i + 1), className='wpe-stat-number'), b.h(t, level=3, size='h4'), b.p(d, color='text-muted'), layout='default')
                                      for i, (t, d) in enumerate([('Discovery call', '30 minutes on goals, content and budget.'), ('Proposal', 'Fixed price, scope and timeline within 48 hours.'),
                                                                  ('Build', 'Weekly previews on a staging site you can click through.'), ('Launch & care', 'We go live, train your team and stay on call.')])],
                                    layout='grid', grid_min='12rem'), bg='surface-alt'),
                  b.section(b.h('Tell us about your project', size='h3', align='center'), b.p('Free 30-minute call. Fixed quote within 48 hours.', align='center', color='text-muted'),
                            b.buttons(b.button('Book a free call', '{{page:contact}}'), justify='center'), content='720px'))


def g_docs():
    return b.join(g_title('Documentation', 'How can we help?', 'Guides for the theme, the demos and your account.'),
                  b.section(b.block('search', {'label': 'Search the docs', 'showLabel': False, 'placeholder': 'Search the docs…', 'buttonText': 'Search'}),
                            className='wpe-section wpe-section--sm', content='720px'),
                  b.section(b.h('Browse by topic', size='h2'), g_cards(DOCS)),
                  b.section(b.h('Popular articles', size='h2'), b.latest_posts(3), bg='surface-alt'),
                  b.section(b.h('Still stuck?', size='h3', align='center'), b.p('Open a ticket and a WPEssential developer replies within one business day.', align='center', color='text-muted'),
                            b.buttons(b.button('Open a ticket', '{{page:contact}}'), justify='center'), content='720px'))


ABOUT_VALUES = [('fas fa-bolt', 'Fast by default', 'Lean code, local fonts and no page-builder bloat unless you ask for it.'),
                ('fas fa-universal-access', 'Built for everyone', 'Accessible markup, keyboard support, RTL and 20 languages out of the box.'),
                ('fas fa-handshake', 'Honest pricing', 'Clear plans, a 14-day money-back guarantee and no hidden renewals.'),
                ('fas fa-life-ring', 'Real support', 'The developers who build the theme answer your tickets.')]


def g_about():
    return b.join(g_title('About us', 'We build the WordPress tools we wanted to use.',
                          'WPEssential is the WordPress product team of Vertex Systems Network (Pvt.) Ltd. We make Hi WPEssential, its demo library, free plugins and the services around them.'),
                  b.section(b.columns(b.column(b.image('shot-library', 'The WPEssential Demo Library', style='wpe-shadow'), width='55%'),
                                      b.column(b.p('Our story', style='wpe-overline'), b.h('From client work to products', size='h2'),
                                               b.p('We spent years building WordPress sites for clients and kept solving the same problems: slow themes, demos that broke on import and builders that locked people in.', color='text-muted'),
                                               b.p('So we built one fast theme with a style for every niche, demos that import in one click for both Gutenberg and Elementor, and a dashboard that logs every step.', color='text-muted'),
                                               width='45%', valign='center'), valign='center', className='wpe-gap-lg'), className='wpe-section wpe-section--sm'),
                  b.section(b.p('What we stand for', style='wpe-overline'), b.h('Our values', size='h2'), g_cards(ABOUT_VALUES), bg='surface-alt'),
                  b.section(b.group(*[b.group(b.p(n, className='wpe-stat-number'), b.p(t, color='text-muted'), layout='default', className='wpe-card wpe-card--pad')
                                      for n, t in [('3', 'free plugins on WordPress.org'), ('20', 'languages'), ('2', 'native builders'), ('1', 'theme for every niche')]],
                                    layout='grid', grid_min='12rem')),
                  b.section(b.h('Work with us', size='h3', align='center', color='on-ink'),
                            b.p('Need a site built, moved or fixed? Our team takes on WordPress projects of every size.', align='center', color='on-ink'),
                            b.buttons(b.button('See services', '{{page:services}}'), b.button('Contact us', '{{page:contact}}', style='wpe-light'), justify='center'),
                            bg='ink', color='on-ink', content='720px'))


def g_contact():
    return b.join(g_title('Contact', 'Talk to the WPEssential team', 'We reply within one business day, Monday to Friday.'),
                  b.section(b.group(*[b.group(b.h(t, level=3, size='h4'), b.p(d), layout='default', className='wpe-card wpe-card--pad') for t, d in [
                      ('Support', '<a href="mailto:support@wpessential.org">support@wpessential.org</a>'),
                      ('Sales', '<a href="mailto:sales@wpessential.org">sales@wpessential.org</a>'),
                      ('Services', '<a href="mailto:services@wpessential.org">services@wpessential.org</a>'),
                      ('Follow', ' · '.join('<a href="%s">%s</a>' % (u, n) for n, u in SOCIAL))]], layout='grid', grid_min='14rem'),
                            className='wpe-section wpe-section--sm'))


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
                    e.text('From $10 a month. Save 20% yearly, or pay once for lifetime.', align='center'), e_products('plans-monthly'),
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
                                           e.row(e.button('View demo', '{{product:%s}}' % d['key']), e.button('Pricing', '{{page:pricing}}', 'outline'), gap=12, wrap=True, mobile_direction='row'),
                                           width=40, justify='center'), gap=56),
                               bg='surface-alt' if i % 2 else None, pad=(72, 24, 72, 24)))
    return out


def e_pricing():
    return [e_title('Pricing', 'One subscription. Every theme and demo.', 'Monthly, yearly (save 20%) or lifetime. Every plan has a 14-day money-back guarantee.'),
            *[e.container(e.heading(label + (' · save 20%' if period == 'yearly' else ''), 3), e.text(BILLING_NOTE[period]), e_products('plans-' + period),
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
             ('contact', 'Contact', 'contact', g_contact, e_contact)]
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
        {'title': 'Demos', 'page': 'demos'}, {'title': 'Plugins', 'page': 'plugins'}, {'title': 'Pricing', 'page': 'pricing'}, {'title': 'Services', 'page': 'services'},
        {'title': 'Docs', 'page': 'docs'}, {'title': 'Blog', 'page': 'blog'}, {'title': 'About', 'page': 'about'}, {'title': 'Contact', 'page': 'contact'}]}]
    settings = {'front_page': 'home', 'blog_page': 'blog', 'style': 'essential', 'motion': 'subtle',
                'parts': {'header': {'content': g_header()}, 'header-shop': {'content': g_header()}, 'header-transparent': {'content': g_header()}, 'footer': {'content': g_footer()}},
                'options': {'posts_per_page': 9, 'blogname': 'WPEssential', 'blogdescription': 'Premium WordPress themes and one-click demos.'},
                'woocommerce': {'options': {'woocommerce_currency': 'USD', 'woocommerce_currency_pos': 'left', 'woocommerce_price_num_decimals': '0',
                                            'woocommerce_coming_soon': 'no', 'woocommerce_store_pages_only': 'no',
                                            'woocommerce_enable_guest_checkout': 'yes',
                                            'woocommerce_cod_settings': {'enabled': 'yes', 'title': 'Pay by invoice'}}}}
    if builder == 'elementor':
        settings['elementor_kit'] = e.kit(container_width=1200, button_radius=999, field_radius=12, button_padding=(14, 24))
    return {'version': 1, 'media': media, 'terms': terms, 'content': content, 'products': products, 'menus': menus, 'settings': settings}


DEMO = {
    'id': ID, 'title': 'Essential', 'tagline': 'A theme and demo marketplace with pricing plans, services and docs — the WPEssential.org site.',
    'niche': ['marketplace', 'software', 'saas', 'digital products'], 'type': 'pro',
    'thumbnail': 'demo:thumbnail.webp',
    'preview': {'desktop': 'demo:preview/desktop.webp', 'tablet': 'demo:preview/tablet.webp', 'mobile': 'demo:preview/mobile.webp'},
    'pages': ['Home', 'Demos', 'Plugins', 'About', 'Pricing', 'Services', 'Docs', 'Blog', 'Contact'],
    'highlights': ['3 demo products', 'Monthly, yearly & lifetime plans', 'Docs & services', 'Light & dark'],
    'motion': 'subtle',
    'plugins': [{'slug': 'woocommerce', 'name': 'WooCommerce', 'source': 'wporg', 'state': 'required'}],
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
