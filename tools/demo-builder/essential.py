"""Essential — theme & demo marketplace demo (Pro), modelled on WPEssential.org.

Direction: the WPEssential.org Figma design (Vertex Systems Network palette: indigo #625BA8,
blue #6188C6, teal #5AC8D6 on ink #07080C; Bricolage Grotesque + Plus Jakarta Sans).
Images are real: demo thumbnails and previews of Base, Maison and Pulse, and screenshots of
the WPEssential dashboard. Products are the demos, the services and the monthly, yearly and lifetime plans.
"""
import json
import os
import re

from urllib.parse import quote

import kit as b

KIT = True  # Gutenberg content uses only WPEssential kit elements (wpessential/*).
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
    ('demo-business-pro', 'Business Pro demo', 'Business Pro consulting demo with a corporate hero'),
    ('demo-saffron', 'Saffron demo', 'Saffron restaurant demo with a menu hero'),
    ('demo-campus', 'Campus demo', 'Campus online course demo with a learning hero'),
    ('shot-library', 'Demo Library', 'WPEssential dashboard showing the Demo Library with Base, Maison and Pulse'),
    ('shot-import', 'Import wizard', 'WPEssential import wizard finishing a demo import with every step logged'),
    ('shot-plugins', 'Plugins step', 'WPEssential import wizard listing the plugins a demo needs'),
]

DEMOS = [
    dict(key='p-base', name='Base', price='0', image='demo-base', site='site-base', niche='Business · Blog', menu_order=3,
         short='A clean multi-purpose site with services, blog and shop. Free with the theme.'),
    dict(key='p-maison', name='Maison', price='59', image='demo-maison', site='site-maison', niche='Fashion · WooCommerce', menu_order=1,
         short='An editorial fashion store with lookbooks, journal and a full WooCommerce shop.'),
    dict(key='p-pulse', name='Pulse', price='59', image='demo-pulse', site='site-pulse', niche='Events · Tickets', menu_order=2,
         short='A conference site with speakers, agenda, ticket passes and an events calendar.'),
    # Sample demos shown in the library (Figma 01/04); their packages ship later.
    dict(key='p-business-pro', name='Business Pro', price='59', image='demo-business-pro', site=None, niche='Consulting · Agency', menu_order=4,
         short='A consulting and corporate services site with case studies, team and a lead form.'),
    dict(key='p-saffron', name='Saffron', price='59', image='demo-saffron', site=None, niche='Restaurant · Orders', menu_order=5,
         short='A restaurant site with menu, reservations and online orders.'),
    dict(key='p-campus', name='Campus', price='59', image='demo-campus', site=None, niche='Education · Courses', menu_order=6,
         short='An online course site with lessons, quizzes and memberships.'),
]

# Same plans as the WPEssential Server (Plans::defaults()): monthly price per tier, yearly is twelve
# months less 20%, lifetime is paid once and covers the major version bought (1.x until 2.0 ships).
# Company details shown on About, Services and Contact.
OFFICE = {'street': 'House 3213/4 8M, Fried Abad, Baron Bohar Gate', 'city': 'Multan, Pakistan',
          'phone': '+92 305 1983930', 'map': 'Bohar Gate, Multan, Pakistan'}

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
    ('fas fa-gauge-high', 'Fast by default', 'Local fonts, no jQuery on the front end and scripts only where a page needs them.'),
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

HOME_DOCS = [  # Figma 01 documentation cards (title, text, article count)
    ('Getting started', 'Install the theme, activate your license and import your first demo.', 12),
    ('Demos & import', 'Choose a builder, what gets imported, re-import and remove a demo.', 18),
    ('Design & styles', 'Colours, fonts, dark mode, hovers, motion and custom CSS.', 24),
    ('Multilingual & SEO', 'WPML, Polylang, RTL languages, translations and SEO plugins.', 9),
    ('Account & billing', 'Licenses, renewals, upgrades, refunds and invoices.', 11),
    ('Developers', 'Hooks, filters, child themes, demo packages and the REST API.', 27),
]
# Sample social proof (Figma 01); editable later in the options below.
HERO_STATS = [['40+', 'ready demos'], ['2', 'native builders'], ['12k+', 'happy customers'], ['4.9/5', 'average rating']]
TESTIMONIALS = [
    ['We rebuilt three client sites in a week. The Gutenberg and Elementor versions match pixel for pixel, so the team just picks one.', 'Ayesha Malik', 'Founder, Northwind Studio'],
    ['The import just works. Pages, products, menus and the dark mode were all there after one click, and the support team answered within hours.', 'Daniel Brooks', 'Freelance developer'],
    ['We imported Maison on Monday and launched the client store on Wednesday. Our team now starts every shop from a WPEssential demo.', 'Sara Ahmed', 'Head of Web, Lumen Agency'],
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
               ('quote', 'Pick Gutenberg for the leanest pages; pick Elementor when your team already edits with it. The design is the same either way.', 'WPEssential team')]),
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

LOGO = ('<a class="wpe-ess-logo" href="{{home:}}" aria-label="WPEssential home"><span class="wpe-ess-logo__mark" aria-hidden="true">W<i></i></span>'
        '<span class="wpe-ess-logo__text"><b>wpessential</b><small>by Vertex Systems Network (Pvt.) Ltd.</small></span></a>')
SOCIAL_ICONS = {'Facebook': 'f', 'Instagram': 'ig', 'X': 'x', 'YouTube': 'yt', 'LinkedIn': 'in', 'GitHub': 'gh'}


def g_header():
    return b.group(b.group(html(LOGO),
                           b.el('menu', {'alignment': {'base': 'center'}}),
                           b.group(b.el('search', {'mode': 'expand', 'buttonStyle': 'icon', 'placeholder': 'Search themes, demos and docs…'}),
                                   b.el('mini-cart', {}),
                                   b.p('<a href="{{woo:myaccount}}">Sign in</a>', className='wpe-ess-signin wpe-signin'),
                                   b.buttons(arrow('Get Pro', '{{page:pricing}}')),
                                   layout='flex', wrap='nowrap', className='wpe-header-actions wpe-ess-actions'),
                           className='wpe-header__inner', layout='flex', wrap='nowrap', justify='space-between'),
                   tag='header', className='wpe-header wpe-ess-header', content='1680px')


def g_footer():
    cols = [('Marketplace', [('Demos', '{{page:demos}}'), ('Plugins', '{{page:plugins}}'), ('Pricing', '{{page:pricing}}'), ('My account', '{{woo:myaccount}}')]),
            ('Resources', [('Documentation', '{{page:docs}}'), ('Blog', '{{page:blog}}'), ('Changelog', '{{page:changelog}}'), ('Roadmap', '{{page:roadmap}}')]),
            ('Company', [('About', '{{page:about}}'), ('Vertex Systems Network (Pvt.) Ltd.', '{{page:about}}'), ('Services', '{{page:services}}'), ('Care plans', '{{page:svc-care-plans}}'), ('Contact', '{{page:contact}}')]),
            ('Support', [('Help center', '{{page:docs}}'), ('Open a ticket', '{{page:support}}'), ('Refund policy', '{{page:refund-policy}}'), ('Billing FAQ', '{{page:billing-faq}}'), ('Licence terms', '{{page:licence-terms}}')])]
    band = b.group(b.group(over('Newsletter', tone='orange'), b.h('New themes and demo drops, monthly.', level=3, className='wpe-ess-band__title'),
                           b.p('One email a month. Release notes, freebies and discounts. Unsubscribe anytime.', className='wpe-ess-band__text'), layout='default'),
                   b.el('forms', {'formId': '{{form:newsletter}}', 'showTitle': False}),
                   layout='flex', wrap='wrap', justify='space-between', valign='center', className='wpe-ess-news')
    social = '<p class="wpe-ess-social">%s</p>' % ''.join('<a href="%s" aria-label="%s" class="wpe-ess-social__%s"></a>' % (u, n, SOCIAL_ICONS[n]) for n, u in SOCIAL)
    brand = b.column(html(LOGO), b.p('Premium WordPress themes and one-click demos for Gutenberg and Elementor.', className='wpe-ess-foot__about'),
                     html(social), b.p('@wpessential on every network', className='wpe-ess-foot__small'), width='38%')
    links = [b.column(b.h(t, level=2, className='wpe-ess-foot__h'), b.ul(['<a href="%s">%s</a>' % (h_, l) for l, h_ in items], className='wpe-ess-foot__list')) for t, items in cols]
    policies = ''.join('<a href="{{page:%s}}">%s</a>' % (k, t) for k, t in [('privacy-policy', 'Privacy'), ('terms', 'Terms'), ('refund-policy', 'Refunds'), ('cookie-policy', 'Cookies')])
    bottom = b.group(b.p('© 2026 WPEssential. A <strong>Vertex Systems Network (Pvt.) Ltd.</strong> company'), html('<p class="wpe-ess-foot__legal">%s</p>' % policies),
                     layout='flex', wrap='wrap', justify='space-between', className='wpe-ess-foot__bottom')
    return b.group(band, b.columns(brand, *links, className='wpe-ess-foot__cols'), bottom, tag='footer', className='wpe-footer wpe-ess-footer', content='1680px')


def g_title(over, title, lead):
    return b.section(b.p(over, style='wpe-overline'), b.h(title, level=1, size='h1'), b.p(lead, style='wpe-lead'),
                     className='wpe-section wpe-section--sm', content='860px')


def g_products(cat, cols=3):
    return b.el('products', {'category': cat, 'count': cols, 'columns': {'base': cols}, 'sort': 'date', 'heading': ''})


PERIOD_SUFFIX = {'monthly': '/ month', 'yearly': '/ year', 'lifetime': 'once'}


def plan_buy(pl):
    return '{{home:}}?add-to-cart={{post_id:plan-%s}}' % pl['plan']


def g_plan_cards(period):
    return b.group(*[plan_card(pl, PERIOD_SUFFIX[period], plan_buy(pl)) for pl in PLANS if pl['period'] == period],
                   layout='grid', columns=3, grid_min='17rem', className='wpe-ess-grid wpe-ess-plans')


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
    """Our Search element, styled as the Figma search bars."""
    return b.el('search', {'placeholder': placeholder, 'buttonText': 'Search', 'buttonStyle': 'text' if button else 'icon',
                           'cssClass': className})


DEMO_CARDS = [  # (product key, name, badge(s), price label, text, image, categories, rating line) in Figma 01/04 order
    ('p-maison', 'Maison', ['PRO', 'NEW'], '$59', 'Editorial fashion store with lookbook, atelier and journal', 'demo-maison', ['shop', 'fashion'], '4.9 · 128 sales'),
    ('p-pulse', 'Pulse', ['PRO', 'NEW'], '$59', 'Conference and events site with agenda, speakers and tickets', 'demo-pulse', ['events'], '5.0 · 64 sales'),
    ('p-base', 'Base', ['FREE', 'NEW'], 'Free', 'Business site with services, pricing, blog and a full shop', 'demo-base', ['business', 'shop', 'blog'], '4.8 · 3.1k downloads'),
    ('p-business-pro', 'Business Pro', ['PRO', 'NEW'], '$59', 'Consulting and corporate services with case studies', 'demo-business-pro', ['business', 'agency'], '4.8 · 412 sales'),
    ('p-saffron', 'Saffron', ['PRO', 'NEW'], '$59', 'Restaurant with menu, reservations and online orders', 'demo-saffron', ['restaurant', 'shop'], '4.9 · 96 sales'),
    ('p-campus', 'Campus', ['PRO', 'NEW'], '$59', 'Online courses with lessons, quizzes and memberships', 'demo-campus', ['education', 'portfolio'], '4.7 · 88 sales'),
]
DEMO_CATS = [('business', 'Business'), ('shop', 'Shop'), ('fashion', 'Fashion'), ('events', 'Events')]


def demo_filter():
    """Niche chips that filter the demo cards on the page; labels and counts come from the live demo products."""
    return b.shortcode('[wpessential_demo_cards chips="1"]')
ICON_FOR = {'Custom demo design': 'palette', 'Migration': 'refresh', 'Speed & SEO tuning': 'bolt', 'Care plans': 'headset'}
DOC_ICONS = ['book', 'layers', 'palette', 'key', 'globe', 'code']


def g_demo_card(key, name, badges, price, text, image, cats=()):
    badge = ''.join('<span class="wpe-ess-badge %s">%s</span>' % ('wpe-ess-badge--orange' if t == 'FREE' else '', t) for t in badges)
    return b.group(
        html('<div class="wpe-ess-demo__shot"><img src="{{media:%s}}" alt="%s demo" loading="lazy"><span class="wpe-ess-demo__badges">%s</span>'
             '<span class="wpe-ess-demo__over"><a href="{{page:%s}}">Live preview <span aria-hidden="true">↗</span></a><a class="is-import" href="{{page:doc-import}}">Import <span aria-hidden="true">↗</span></a></span></div>' % (image, name, badge, key)),
        b.group(b.group(b.h('<a href="{{page:%s}}">%s</a>' % (key, name), level=3, className='wpe-ess-demo__name'), b.p(price, className='wpe-ess-demo__price'),
                        layout='flex', justify='space-between', valign='center'),
                b.p(text, className='wpe-ess-demo__text'),
                html('<div class="wpe-ess-demo__meta"><span>Gutenberg &amp; Elementor</span><span class="wpe-ess-gb" title="Gutenberg">G</span><span class="wpe-ess-gb" title="Elementor">E</span></div>'),
                layout='default', className='wpe-ess-demo__body'),
        layout='default', className=' '.join(['wpe-ess-demo', 'wpe-filter-item', 'wpe-plan-' + ('free' if 'FREE' in badges else 'pro'), 'wpe-builder-gutenberg', 'wpe-builder-elementor']
                                             + ['wpe-cat-' + c for c in cats] + ['wpe-niche-' + c for c in cats]))


def g_demo_grid(limit=0):
    """Demo cards rendered live from the 'demos' products (inc/woocommerce.php, [wpessential_demo_cards])."""
    return b.shortcode('[wpessential_demo_cards limit="%d"]' % limit)


def g_plugin_card(icon, name, text, meta, url, first=False):
    return b.group(
        b.group(ico({'fas fa-layer-group': 'layers', 'fas fa-icons': 'sparkle', 'fas fa-code': 'code'}[icon]), b.p('Free', className='wpe-ess-badge wpe-ess-badge--soft'),
                layout='flex', justify='space-between', valign='top'),
        b.h(name, level=3, className='wpe-ess-card__title'), b.p(text, className='wpe-ess-card__text'),
        b.p('Free · WordPress.org <code>%s</code>' % meta, className='wpe-ess-card__meta'),
        b.group(b.p('<strong>Free</strong>'), b.buttons(arrow('Download', url, style=None if first else 'wpe-outline')),
                layout='flex', justify='space-between', valign='center', className='wpe-ess-card__foot'),
        layout='default', className=b.cls('wpe-ess-card', 'is-active' if first else None))


def g_home():
    hero = b.group(
        html('<p class="wpe-ess-pill"><a href="{{page:p-pulse}}"><b>NEW</b> New demo: Pulse for conferences and events <span aria-hidden="true">→</span></a></p>'),
        b.h('Launch a WordPress site <span class="wpe-ess-grad">in minutes, not weeks.</span>', level=1, align='center', className='wpe-ess-hero__title'),
        b.p('Premium themes and one-click demos — native for Gutenberg and Elementor. Search, preview live, import and launch.', align='center', className='wpe-ess-hero__lead'),
        g_search('Search themes, demos and docs…', 'wpe-ess-hero__search'),
        html('<p class="wpe-ess-hero__popular"><span>Popular:</span>%s</p>' % ''.join(
            chip(t, '{{page:%s}}' % k) for t, k in [('Fashion store', 'p-maison'), ('Events', 'p-pulse'), ('Business', 'p-base'), ('Restaurant', 'p-saffron'), ('Agency', 'p-business-pro'), ('Online course', 'p-campus')])),
        b.shortcode('[wpessential_demo_cards stats="1"]'),
        html('<div class="wpe-ess-hero__shots"><img src="{{media:demo-pulse}}" alt="Pulse demo" class="is-left"><img src="{{media:demo-maison}}" alt="Maison demo" class="is-mid"><img src="{{media:demo-base}}" alt="Base demo" class="is-right"></div>'),
        tag='section', align='full', className='wpe-ess-hero', layout='constrained', content='1680px')
    cats = b.group(demo_filter(),
        tag='section', align='full', className='wpe-ess-section wpe-ess-section--cats', layout='constrained', content='1680px')
    demos = b.group(
        head('Demo library', 'Pick a demo. Preview it live. Import it in one click.',
             'Every demo ships for Gutenberg and Elementor, with real content, images, hovers and dark mode.',
             action=arrow('Browse all demos', '{{page:demos}}', style='wpe-outline')),
        g_demo_grid(),
        tag='section', align='full', className='wpe-ess-section', layout='constrained', content='1680px')
    theme = b.group(
        head('Theme & plugins', 'One theme. Every niche. Demos that do the heavy lifting.',
             'Hi WPEssential is a fast block theme with a style for every demo. Our free plugins on WordPress.org add theme building, icons and Elementor dynamic widgets.'),
        b.group(b.columns(
            b.column(html('<p class="wpe-ess-badges"><span class="wpe-ess-badge wpe-ess-badge--orange">Popular</span><span class="wpe-ess-badge wpe-ess-badge--soft">Free</span></p>'),
                     b.h('Hi WPEssential theme', level=3, className='wpe-ess-feature__title'),
                     b.p('A lightweight block theme with 8 breakpoints, light &amp; dark, 20 languages and RTL, built-in SEO and a dashboard that imports any demo in one click.', className='wpe-ess-feature__text'),
                     b.ul(['Gutenberg-native and Elementor-native demos', 'WPML &amp; Polylang ready, 20 languages', 'Lighthouse-ready performance and SEO'], className='wpe-ess-check wpe-ess-check--light'),
                     b.buttons(arrow('Get the theme', '{{page:pricing}}'), b.button('Live demo', '{{page:demos}}', style='wpe-ghost-light')),
                     width='44%', valign='center'),
            b.column(b.image('shot-library', 'The WPEssential Demo Library', className='wpe-ess-feature__shot'), width='56%', valign='center'),
            valign='center', className='wpe-ess-feature__cols'), className='wpe-ess-feature', layout='default'),
        b.group(*[g_plugin_card(*pl, first=(i == 0)) for i, pl in enumerate(PLUGINS)], layout='grid', columns=3, grid_min='18rem', className='wpe-ess-grid wpe-ess-grid--3'),
        tag='section', align='full', className='wpe-ess-section wpe-ess-section--alt', layout='constrained', content='1680px')
    steps = [('search', 'Find your demo', 'Search the library by niche, builder or style, and click through the live preview.'),
             ('layers', 'Choose your builder', 'Pick Gutenberg or Elementor. Required plugins install themselves.'),
             ('download', 'Import in one click', 'Pages, products, menus, images and styles arrive in about two minutes. Undo anytime.')]
    how = b.group(
        head('How it works', 'From zero to a launched site in three steps.', align='center'),
        b.columns(
            b.column(*[b.group(ico(i_, 'soft'), b.group(b.h('<span>0%d</span> %s' % (n + 1, t), level=3, className='wpe-ess-step__title'), b.p(d, className='wpe-ess-step__text'), layout='default'),
                               layout='flex', valign='top', className=b.cls('wpe-ess-step', 'is-active' if n == 2 else None)) for n, (i_, t, d) in enumerate(steps)],
                     width='58%', valign='center'),
            b.column(b.group(b.image('shot-import', 'A finished import in the WPEssential wizard', className='wpe-ess-panel__shot'),
                             b.p('Real WPEssential import · every step logged · 0 errors', className='wpe-ess-panel__note'), className='wpe-ess-panel', layout='default'),
                     width='42%', valign='center'),
            valign='center', className='wpe-ess-gap'),
        tag='section', align='full', className='wpe-ess-section', layout='constrained', content='1680px')
    svc = [x for x in SERVICES if x[1] in ICON_FOR]
    services = b.group(
        head('Services by Vertex Systems Network (Pvt.) Ltd.', 'Need it done for you? Our team builds, moves and maintains WordPress sites.',
             tone='orange', action=arrow('Book a free call', '{{page:contact}}'), title_color='on-ink'),
        b.group(*[b.group(ico(ICON_FOR[t]), b.h(t, level=3, className='wpe-ess-card__title'), b.p(d, className='wpe-ess-card__text'),
                          html('<a class="wpe-ess-card__price" href="{{page:services}}"><code>%s</code><span aria-hidden="true">↗</span></a>' % price),
                          layout='default', className='wpe-ess-card wpe-ess-card--dark') for _, t, d, price in svc],
                layout='grid', columns=4, grid_min='14rem', className='wpe-ess-grid'),
        tag='section', align='full', className='wpe-ess-section wpe-ess-section--ink', layout='constrained', content='1680px')
    pricing = b.group(
        head('Pricing', 'Simple plans. Every update included.', 'From $10 a month. Save 20% yearly, or pay once for lifetime.', align='center'),
        g_toggle(),
        g_periods(),
        tag='section', align='full', className='wpe-ess-section', layout='constrained', content='1680px')
    docs = b.group(
        b.group(b.group(over('Documentation'), b.h('Answers in seconds, not tickets.', size='h1', className='wpe-ess-h2'), layout='default'),
                g_search('Search the docs…', 'wpe-ess-docs__search', button=False),
                layout='flex', wrap='wrap', justify='space-between', valign='bottom', className='wpe-ess-head'),
        b.group(*[b.group(ico(DOC_ICONS[i], 'soft'), b.h(t, level=3, className='wpe-ess-card__title'), b.p(d, className='wpe-ess-card__text'),
                          b.p('<a href="{{page:docs}}">%d articles</a>' % n, className='wpe-ess-card__link wpe-ess-card__count'),
                          layout='default', className='wpe-ess-card') for i, (t, d, n) in enumerate(HOME_DOCS)],
                layout='grid', columns=3, grid_min='18rem', className='wpe-ess-grid wpe-ess-grid--3'),
        tag='section', align='full', className='wpe-ess-section wpe-ess-section--alt', layout='constrained', content='1680px')
    news = b.group(
        head('Loved by builders', 'Agencies and creators ship faster with WPEssential.'),
        b.shortcode('[wpessential_testimonials]'),
        tag='section', align='full', className='wpe-ess-section', layout='constrained', content='1680px')
    return b.join(hero, cats, demos, theme, how, services, pricing, docs, news)


def g_hero_light(crumb, title, lead, *extra, over_text=None, parent=('Home', '{{home:}}'), show_over=True):
    """Light page hero (Figma: Demos, Plugins, About, Contact, Blog)."""
    # Figma 10/11/19: breadcrumb, then the overline; 04/12: overline only.
    top = ([html('<p class="wpe-ess-crumb"><a href="%s">%s</a> / %s</p>' % (parent[1], parent[0], crumb))] + ([over(crumb)] if show_over else [])) if crumb else [over(over_text)]
    return b.group(*top, b.h(title, level=1, className='wpe-ess-page__title'), b.p(lead, className='wpe-ess-lead'), *extra,
                   tag='section', align='full', className='wpe-ess-page-hero', layout='constrained', content='1680px')


def g_hero_dark(overline, title, lead, *extra, cls=None):
    """Navy page hero (Figma: Pricing, Services, Docs)."""
    return b.group(over(overline, align='center', tone='orange'), b.h(title, level=1, align='center', className='wpe-ess-page__title wpe-ess-page__title--light'),
                   *([b.p(lead, align='center', className='wpe-ess-hero__lead')] if lead else []), *extra,
                   tag='section', align='full', className=b.cls('wpe-ess-page-hero wpe-ess-page-hero--dark', cls), layout='constrained', content='1680px')


def g_band(title, text, btn, href, tone='blue'):
    return b.group(b.group(b.h(title, level=3, className='wpe-ess-band__title'), b.p(text, className='wpe-ess-band__text'), layout='default'),
                   b.buttons(arrow(btn, href)), layout='flex', wrap='wrap', justify='space-between', valign='center',
                   className='wpe-ess-band wpe-ess-band--%s' % tone)


def section(*inner, alt=False, ink=False):
    return b.group(*inner, tag='section', align='full', layout='constrained', content='1680px',
                   className=b.cls('wpe-ess-section', 'wpe-ess-section--alt' if alt else None, 'wpe-ess-section--ink' if ink else None))


def demo_facets():
    """Figma 04: filter sidebar, result bar and card grid, all built from the live 'demos' products.
    Filtering runs in the browser (assets/js/components.js, [data-wpe-facets])."""
    return b.shortcode('[wpessential_demo_cards facets="1"]')


def g_demos():
    return b.join(
        g_hero_light(None, 'Pick a demo. Preview it live. Import it in one click.',
                     'Hi WPEssential is free. Every demo ships for Gutenberg and Elementor. Free demos import with the free plugin; Pro demos unlock with a Pro licence.',
                     b.p('<span aria-hidden="true">▷</span> Live demos run in your browser (WordPress Playground) — nothing to install.', className='wpe-ess-note-pill'), over_text='Themes & demos'),
        section(demo_facets(), alt=True),
        section(b.group(b.h('Unlock every Pro demo', level=2, align='center', className='wpe-ess-band__title'),
                        b.p('Pro demos come with any paid plan: Starter, Pro or Agency. No separate theme purchase.', align='center', className='wpe-ess-band__text'),
                        b.buttons(arrow('See plans', '{{page:pricing}}'), justify='center'),
                        layout='default', className='wpe-ess-band wpe-ess-band--ink wpe-ess-band--center')))


def g_periods():
    """All three billing periods; the toggle (assets/js/components.js) shows one at a time, monthly first."""
    return b.group(*[b.group(g_plan_cards(period), b.p(BILLING_NOTE[period], align='center', className='wpe-ess-note'),
                             layout='default', className='wpe-ess-period wpe-tab-panel').replace('"cssClass":"wpe-ess-period wpe-tab-panel"', '"anchor":"%s","cssClass":"wpe-ess-period wpe-tab-panel"' % period, 1)
                     for period, _, _ in BILLING], layout='default', className='wpe-ess-periods')


def g_toggle(dark=False):
    return html('<p class="wpe-ess-toggle%s" data-wpe-tabs>%s</p>' % (' wpe-ess-toggle--dark' if dark else '', ''.join(
        '<a href="#%s"%s>%s</a>' % (k, ' class="is-on"' if k == 'monthly' else '', t) for k, t in [('monthly', 'Monthly'), ('yearly', 'Yearly · save 20%'), ('lifetime', 'Lifetime')])))


PRO_MODULES = [('Content & data', 'blue', ['Fields', 'Relations', 'Custom Tables', 'Query', 'Listings', 'Admin Columns', 'Status']),
               ('Design & build', 'orange', ['Site Kit — colours, fonts, dark mode, SEO', 'Pro Kit — countdown, modal, pricing, chart…', 'Builder Widgets']),
               ('Users & access', 'purple', ['Roles', 'Profiles', 'Membership']),
               ('Admin', 'ink', ['Dashboard', 'Dashboard Widgets', 'Admin Menu', 'Settings']),
               ('Automation', 'teal', ['Forms & Workflows', 'Emails', 'Notifications', 'Chat', 'Cron']),
               ('Site ops', 'green', ['Backup'])]
PRO_ADDONS = [['Setup &amp; demo import', '$99', 'One-time'], ['Priority support upgrade', '$15', 'Monthly'], ['Plugin customisation', 'from $249', 'One-time'],
              ['New module', 'from $499', 'One-time'], ['Custom demo', 'from $1,490', 'One-time']]


def g_pricing():
    rows = [['Hi WPEssential theme', 'Yes', 'Yes', 'Yes', 'Yes'], ['Custom Post Types &amp; Taxonomies', 'Yes', 'Yes', 'Yes', 'Yes'],
            ['Free demos', 'Yes', 'Yes', 'Yes', 'Yes'], ['Pro demos', '—', 'Yes', 'Yes', 'Yes'], ['22 Pro modules', '—', 'Yes', 'Yes', 'Yes'],
            ['Sites', 'Unlimited', '1', '5', 'Unlimited'], ['Support', 'Forum', 'Standard', 'Priority', 'Dedicated'], ['White-label', '—', '—', '—', 'Yes']]
    modules = b.group(*[b.group(b.p(name, className='wpe-ess-badge wpe-ess-badge--%s' % tone),
                                b.h('%d module%s' % (len(items), '' if len(items) == 1 else 's'), level=3, className='wpe-ess-card__title'),
                                b.ul(items, className='wpe-ess-plain'), layout='default', className='wpe-ess-card wpe-ess-card--alt')
                        for name, tone, items in PRO_MODULES], layout='grid', columns=3, grid_min='18rem', className='wpe-ess-grid wpe-ess-grid--3')
    return b.join(
        g_hero_dark('Pricing · WPEssential Pro', 'Free to start. <span class="wpe-ess-grad">Pro when you grow.</span>',
                    'The Hi WPEssential theme, the WPEssential plugin and free demos cost nothing. WPEssential Pro adds 22 modules and the Pro demos.',
                    b.buttons(arrow('Compare plans', '#compare'), b.button('Download free', '{{page:plugins}}', style='wpe-ghost-light'), justify='center')),
        section(g_toggle(), g_periods(), alt=True),
        section(head('What you get', '22 Pro modules in every plan', 'Plans differ only by number of sites and support level.'), modules),
        section(head('Compare', 'Free vs Pro'), b.table(['Feature', 'Free', 'Starter', 'Pro', 'Agency'], rows, className='wpe-ess-table'), alt=True)
        .replace('"cssClass":"wpe-ess-section', '"anchor":"compare","cssClass":"wpe-ess-section', 1),
        section(head('Pro add-ons', 'Extras for a Pro licence', 'Bought on the same checkout. Each one needs an active Pro licence.'),
                b.table(['Add-on', 'Price', 'Billing'], PRO_ADDONS, className='wpe-ess-table'),
                b.p('Custom design, development, SEO or hosting is a service, quoted and invoiced separately. <a href="{{page:services}}">→ Services</a>', className='wpe-ess-note-box')),
        section(b.group(b.h('Start free. Upgrade when you need it.', level=2, align='center', className='wpe-ess-band__title'),
                        b.p('The theme and plugin stay free forever.', align='center', className='wpe-ess-band__text'),
                        b.buttons(arrow('Get Pro', '#compare'), b.button('Download free', '{{page:plugins}}', style='wpe-ghost-light'), justify='center'),
                        layout='default', className='wpe-ess-band wpe-ess-band--blue wpe-ess-band--center')))


SERVICE_ICONS = ['palette', 'download', 'cart', 'figma', 'grid', 'bolt', 'refresh', 'globe', 'shield', 'wrench', 'server', 'heart', 'code']


SERVICE_GROUPS = [('Custom design', 'Figma or web design for your brand.', 'from $1,490'), ('Figma / HTML → WordPress', 'Pixel-perfect builds on WPEssential.', 'from $499'),
                  ('Custom development', 'Plugins, integrations, WooCommerce.', 'Quote'), ('Speed &amp; SEO', 'Core Web Vitals and technical SEO.', 'from $249'),
                  ('Hosting &amp; care plans', 'Managed hosting, updates, backups.', 'from $49/mo'), ('Branding &amp; social', 'Logo, brand kit, social media.', 'Quote')]


def g_services():
    steps = [['1. Request a quote', 'Tell us about the project'], ['2. Proposal', 'Scope, timeline and fixed price'],
             ['3. Invoice', 'Pay by Payoneer, PayPal, Stripe or bank transfer'], ['4. Delivery', 'Work, review rounds, handover']]
    return b.join(
        g_hero_dark('Services · by Vertex Systems Network', 'WordPress work, <span class="wpe-ess-grad">done for you.</span>',
                    'Design, development, speed, SEO and hosting. Quoted per project and invoiced by Vertex Systems Network (Pvt.) Ltd.',
                    b.buttons(arrow('Request a quote', '{{page:quote}}'), b.button('See Pro add-ons', '{{page:pricing}}', style='wpe-ghost-light'), justify='center')),
        section(b.group(*[b.group(b.h(t, level=3, className='wpe-ess-card__title'), b.p(d, className='wpe-ess-card__text'), b.p(price, className='wpe-ess-plan__price'),
                                  b.buttons(b.button('Request a quote', '{{page:quote}}', style='wpe-outline')), layout='default', className='wpe-ess-card wpe-ess-card--svc')
                          for t, d, price in SERVICE_GROUPS], layout='grid', columns=3, grid_min='18rem', className='wpe-ess-grid wpe-ess-grid--3')),
        section(head('How it works', 'Quote → invoice → delivery'), b.table(['Step', 'What happens'], steps, className='wpe-ess-table'),
                b.p('Already on WPEssential Pro? Setup, customisation and new modules are Pro add-ons on the normal checkout. <a href="{{page:pricing}}">→ Pricing</a>', className='wpe-ess-note-box'), alt=True),
        section(b.group(b.h('Tell us what you need.', level=2, align='center', className='wpe-ess-band__title'),
                        b.p('We reply with a fixed quote within one business day.', align='center', className='wpe-ess-band__text'),
                        b.buttons(arrow('Request a quote', '{{page:quote}}'), b.button('Pricing', '{{page:pricing}}', style='wpe-ghost-light'), justify='center'),
                        layout='default', className='wpe-ess-band wpe-ess-band--blue wpe-ess-band--center')))


DOC_POPULAR = [('Import a demo in one click', 'Pick a demo, choose Gutenberg or Elementor and import pages, menus and products.', 'Demo import · 4 min read', '{{page:doc-import}}'),
               ('Gutenberg or Elementor: which package?', 'Both versions look the same. Here is how to choose the right one for your site.', 'Demo import · 3 min read', '{{page:post-builders}}'),
               ('Activate your license key', 'Connect your site to unlock Pro demos, updates and support.', 'License · 2 min read', '{{page:docs}}'),
               ('Translate your site with WPML', 'Translate pages, menus and theme strings with WPML or Polylang.', 'Translation · 6 min read', '{{page:docs}}')]


def g_docs():
    return b.join(
        g_hero_dark('Documentation', 'How can we help?', None, g_search('Search the docs…', 'wpe-ess-hero__search'),
                    html('<p class="wpe-ess-hero__popular"><span>Popular:</span>%s</p>' % ''.join(chip(t, '{{page:%s}}' % k) for t, k in
                         [('Import a demo', 'doc-import'), ('Gutenberg or Elementor', 'post-builders'), ('Pricing', 'pricing'), ('Contact', 'contact')])), cls='wpe-ess-page-hero--docs'),
        section(b.h('Browse by topic', size='h1', className='wpe-ess-h2'),
                b.group(*[b.group(ico(DOC_ICONS[i], 'orange'), b.h(t, level=3, className='wpe-ess-card__title'), b.p(d, className='wpe-ess-card__text'),
                                  b.p('<a href="{{page:blog}}">Read guides <span aria-hidden="true">→</span></a>', className='wpe-ess-card__link wpe-ess-card__link--blue'),
                                  layout='default', className='wpe-ess-card wpe-ess-card--doc') for i, (_, t, d) in enumerate(DOCS)],
                        layout='grid', columns=3, grid_min='18rem', className='wpe-ess-grid wpe-ess-grid--3')),
        section(b.h('Popular articles', size='h1', className='wpe-ess-h2'),
                b.group(*[b.group(ico('book', 'soft'), b.h('<a href="%s">%s</a>' % (u, t), level=3, className='wpe-ess-card__title'), b.p(d, className='wpe-ess-card__text'),
                                  b.p('<a href="%s">%s <span aria-hidden="true">→</span></a>' % (u, m), className='wpe-ess-card__link'),
                                  layout='default', className='wpe-ess-card wpe-ess-card--article') for t, d, m, u in DOC_POPULAR],
                        layout='grid', columns=2, grid_min='20rem', className='wpe-ess-grid wpe-ess-grid--articles'), alt=True),
        section(b.group(ico('chat'), b.group(b.h('Still stuck?', level=3, className='wpe-ess-band__title'),
                                            b.p('Open a ticket and a WPEssential developer replies within one business day.', className='wpe-ess-band__text'), layout='default'),
                        b.buttons(arrow('Open a ticket', '{{page:support}}')), layout='flex', wrap='nowrap', valign='center', className='wpe-ess-band wpe-ess-band--soft wpe-ess-band--row')))


DOC_NAV = [('Getting started', ['Install the theme', 'Activate WPEssential']),
           ('Demo import', ['Import a demo in one click', 'Gutenberg or Elementor?', 'Required plugins', 'Resume a stopped import']),
           ('Customising', ['Style variations', 'Fonts &amp; colours'])]


def g_doc_article():
    """Doc article (Figma 09): docs nav, the article, and an on-this-page list."""
    nav = b.group(g_search('Search the docs…', 'wpe-ess-docnav__search', button=False),
                  *[b.group(b.p(sec, className='wpe-ess-docnav__head'),
                            html('<ul class="wpe-ess-docnav__list">%s</ul>' % ''.join(
                                '<li%s><a href="%s">%s</a></li>' % (' class="is-current"' if t == 'Import a demo in one click' else '',
                                                                   '{{page:doc-import}}' if t == 'Import a demo in one click' else '{{page:docs}}', t) for t in items)),
                            layout='default') for sec, items in DOC_NAV], layout='default', className='wpe-ess-docnav')
    steps = [('Open the Demo Library', 'Go to WPEssential → Demo Library in wp-admin and pick a demo.'),
             ('Choose your builder', 'Select Gutenberg or Elementor. Both versions look the same.'),
             ('Install required plugins', 'The wizard lists the plugins and installs them for you.'),
             ('Import', 'Keep the tab open. A typical demo imports in under two minutes.')]
    article = b.group(html('<p class="wpe-ess-doc__crumbs"><a href="{{page:docs}}">Docs</a> / <a href="{{page:docs}}">Demo import</a> / Import a demo in one click</p>'),
                      b.h('Import a demo in one click', level=1, size='h1', className='wpe-ess-doc__title'),
                      b.p('Updated 12 Oct 2026 · 4 min read', className='wpe-ess-doc__meta'),
                      b.p('Every Hi WPEssential demo ships a Gutenberg and an Elementor version. The importer installs the plugins the demo needs, brings in pages, products, menus and images, and logs each step so you can resume if anything stops.'),
                      *[x for n, (t, d) in enumerate(steps) for x in (b.h('%d. %s' % (n + 1, t), level=2, size='h4'), b.p(d))],
                      b.el('alert', {'type': 'warning', 'title': ' ', 'content': 'Tip: import on a fresh site. Existing pages are kept, but menus and the front page are replaced.', 'dismissible': False}),
                      b.el('code', {'mode': 'code', 'code': 'wp wpessential import maison --builder=gutenberg', 'language': 'bash'}),
                      html('<p class="wpe-ess-doc__helpful"><span>Was this article helpful?</span> <a href="{{page:docs}}">Yes</a> <a href="{{page:support}}">No</a></p>'),
                      layout='default', className='wpe-ess-doc')
    toc = b.group(b.el('table-of-contents', {'heading': 'On this page', 'levels': ['h2']}), layout='default', className='wpe-ess-doc__toc')
    return b.group(b.columns(b.column(nav, width='15%'), b.column(article, width='65%'), b.column(toc, width='20%'), className='wpe-ess-docgrid'),
                   tag='section', align='full', layout='constrained', content='1680px', className='wpe-ess-section wpe-ess-section--doc')


def g_module_cards():
    return b.group(*[b.group(b.p(name, className='wpe-ess-badge wpe-ess-badge--%s' % tone),
                             b.h('%d module%s' % (len(items), '' if len(items) == 1 else 's'), level=3, className='wpe-ess-card__title'),
                             b.ul(items, className='wpe-ess-plain'), layout='default', className='wpe-ess-card')
                     for name, tone, items in PRO_MODULES], layout='grid', columns=3, grid_min='18rem', className='wpe-ess-grid wpe-ess-grid--3')


def g_plugins():
    def product(badge, tone, name, sub, inc, btn, featured=False):
        return b.group(b.p(badge, className='wpe-ess-badge wpe-ess-badge--%s' % tone), b.h(name, level=3, className='wpe-ess-card__title'),
                       b.p(sub, className='wpe-ess-card__text'), b.ul(inc, className='wpe-ess-check'), btn,
                       layout='default', className=b.cls('wpe-ess-plan', 'is-featured' if featured else None))
    rows = [['Custom Post Types &amp; Taxonomies', 'Yes', 'Yes'], ['Demo Library', 'Free demos', 'Free + Pro demos'], ['Content &amp; data modules', '—', '7 modules'],
            ['Site Kit, Pro Kit', '—', 'Yes'], ['Roles, Profiles, Membership', '—', 'Yes'], ['Automation &amp; Backup', '—', 'Yes'],
            ['Updates', 'WordPress.org', 'Licence key'], ['Support', 'Forum', 'Ticket']]
    small = [(n, t) for _, n, t, *_ in PLUGINS[1:]]
    return b.join(
        g_hero_dark('Plugins', 'Free core on WordPress.org. <span class="wpe-ess-grad">Pro extension from us.</span>',
                    'WPEssential is free forever. WPEssential Pro needs it and adds 22 modules plus the Pro demos.',
                    b.buttons(arrow('Get Pro', '{{page:pricing}}'), b.button('Download free', 'https://wordpress.org/plugins/wpessential/', style='wpe-ghost-light'), justify='center')),
        section(b.group(product('Free · WordPress.org', 'green', 'WPEssential', 'The free core plugin.',
                                ['Custom Post Type builder', 'Custom Taxonomy builder', 'Modules dashboard &amp; System Status', 'Demo Library with free demos', 'AI Connect (MCP, optional)'],
                                b.buttons(b.button('Download free', 'https://wordpress.org/plugins/wpessential/', style='wpe-outline'))),
                        product('Pro · wpessential.org', 'orange', 'WPEssential Pro', 'Extension for the free plugin.',
                                ['Fields, Relations, Custom Tables, Query', 'Site Kit + Pro Kit elements', 'Roles, Profiles, Membership', 'Forms, Emails, Notifications, Backup', 'Pro demos for Hi WPEssential'],
                                b.buttons(arrow('Get Pro', '{{page:pricing}}')), featured=True),
                        layout='grid', columns=2, grid_min='20rem', className='wpe-ess-grid'),
                b.group(*[b.group(b.p('Free', className='wpe-ess-badge wpe-ess-badge--green'), b.h(n, level=3, className='wpe-ess-card__title'), b.p(t, className='wpe-ess-card__text'),
                                  layout='default', className='wpe-ess-card') for n, t in small],
                        layout='grid', columns=2, grid_min='20rem', className='wpe-ess-grid')),
        section(head('Pro modules', 'Everything WPEssential Pro adds'), g_module_cards(), alt=True),
        section(head('Free vs Pro', 'What changes when you add Pro'), b.table(['✓', 'WPEssential (Free)', 'WPEssential Pro'], rows, className='wpe-ess-table')),
        section(b.group(b.h('Start free. Add Pro later.', level=2, align='center', className='wpe-ess-band__title'),
                        b.p('The free plugin stays free.', align='center', className='wpe-ess-band__text'),
                        b.buttons(arrow('Get Pro', '{{page:pricing}}'), b.button('Download free', 'https://wordpress.org/plugins/wpessential/', style='wpe-ghost-light'), justify='center'),
                        layout='default', className='wpe-ess-band wpe-ess-band--blue wpe-ess-band--center')))


ABOUT_VALUES = [('bolt', 'Fast by default', 'Lean code, local fonts and no page-builder bloat.'),
                ('globe', 'Built for everyone', 'Accessible, RTL and 20 languages out of the box.'),
                ('shield', 'Honest pricing', 'Clear plans and a 14-day money-back guarantee.'),
                ('headset', 'Real support', 'The developers who build the theme answer your tickets.')]


def g_about():
    stat = lambda n, t: b.group(b.p(n, className='wpe-ess-stat'), b.p(t, className='wpe-ess-card__text'), layout='default', className='wpe-ess-card')
    return b.join(
        g_hero_light(None, 'We build the WordPress tools we wanted to use.',
                     'WPEssential is the WordPress product team of Vertex Systems Network (Pvt.) Ltd. We make Hi WPEssential, its demo library, free plugins and the services around them.',
                     over_text='About us'),
        section(b.columns(b.column(b.image('shot-library', 'The WPEssential Demo Library', className='wpe-ess-shot'), width='52%', valign='center'),
                          b.column(over('Our story'), b.h('From client work to products', size='h1', className='wpe-ess-h2'),
                                   b.p('We spent years building WordPress sites for clients and kept solving the same problems: slow themes, demos that broke on import and builders that locked people in.', className='wpe-ess-lead'),
                                   b.p('So we built one fast theme with a style for every niche, demos that import in one click for both Gutenberg and Elementor, and a dashboard that logs every step.', className='wpe-ess-lead'),
                                   width='48%', valign='center'), valign='center', className='wpe-ess-gap')),
        section(head('What we stand for', 'Our values'),
                b.group(*[b.group(ico(i_, 'soft'), b.h(t, level=3, className='wpe-ess-card__title'), b.p(d, className='wpe-ess-card__text'), layout='default', className='wpe-ess-card')
                          for i_, t, d in ABOUT_VALUES], layout='grid', columns=4, grid_min='14rem', className='wpe-ess-grid'), alt=True),
        section(b.group(stat('3', 'free plugins on WordPress.org'), stat('20', 'languages'), stat('2', 'native builders'), stat('1', 'theme for every niche'),
                        layout='grid', columns=4, grid_min='12rem', className='wpe-ess-grid')),
        section(b.h('Work with us', size='h1', align='center', className='wpe-ess-h2', color='on-ink'),
                b.p('Need a site built, moved or fixed? Our team takes on WordPress projects of every size.', align='center', className='wpe-ess-hero__lead'),
                b.buttons(arrow('See services', '{{page:services}}'), b.button('Contact us', '{{page:contact}}', style='wpe-light'), justify='center'), ink=True))


def wpform(key):
    return b.el('forms', {'formId': '{{form:%s}}' % key, 'showTitle': False})


def svc_link(name):
    return '{{page:svc-%s}}' % svc.DETAIL[name][0]


def g_work():
    # Our own demos, without invented results or reviews.
    return section(head('Built on Hi WPEssential', 'Sites you can open and try today.', 'Every project ships on Hi WPEssential, so you can keep editing it yourself after launch.'),
            b.group(*[b.group(b.image(img, alt, aspect='16/9'), b.group(b.p(tag, className='wpe-ess-over'), b.h(t, level=3, className='wpe-ess-card__title'),
                                                                          b.p('<a href="{{page:%s}}">See the demo <span aria-hidden="true">→</span></a>' % key, className='wpe-ess-card__link'),
                                                                          layout='default', className='wpe-ess-work__body'),
                              layout='default', className='wpe-ess-card wpe-ess-work') for img, alt, tag, t, key in [
                ('site-base', 'Base demo home page', 'Business', 'Base: services, pricing, blog and a small shop', 'p-base'),
                ('site-maison', 'Maison demo home page', 'WooCommerce store', 'Maison: fashion store with lookbook and atelier', 'p-maison'),
                ('site-pulse', 'Pulse demo home page', 'Events', 'Pulse: conference site with agenda, speakers and tickets', 'p-pulse')]],
                    layout='grid', columns=3, grid_min='16rem', className='wpe-ess-grid wpe-ess-grid--3'))


def g_team():
    return section(html('<div class="wpe-ess-map"><iframe title="Office location on Google Maps" src="https://maps.google.com/maps?q=%s&amp;z=15&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'
                        '<div class="wpe-ess-map__card"><strong>Vertex Systems Network (Pvt.) Ltd.</strong><span>%s<br>%s</span><span><a href="tel:%s">%s</a> · PKT (UTC+5)</span>'
                        '<a href="https://maps.google.com/?q=%s" target="_blank" rel="noopener">Get directions ↗</a></div></div>' % (
                            quote(OFFICE['map']), OFFICE['street'], OFFICE['city'], OFFICE['phone'].replace(' ', ''), OFFICE['phone'], quote(OFFICE['map']))))


def g_contact():
    routes = [('card', 'Plans & licensing', 'Which plan fits, lifetime licences, agencies and invoices.', 'Talk to sales', '#contact-form'),
              ('palette', 'Start a project', 'Design, setup, WooCommerce, migration, speed or care plans.', 'Request a quote', '{{page:quote}}'),
              ('headset', 'Already a customer?', 'Bugs, demo import or licence questions go to our ticket desk.', 'Open a support ticket', '{{page:support}}')]
    side = [('Response times', [('Sales & pre-sales', 'Within one business day'), ('Projects & quotes', 'Scoped estimate within two business days'),
                                ('Support', 'Through the ticket desk, by licence')]),
            ('Email us directly', [('<a href="mailto:sales@wpessential.org">sales@wpessential.org</a>', 'Plans, licences, agencies'),
                                   ('<a href="mailto:services@wpessential.org">services@wpessential.org</a>', 'Projects and care plans'),
                                   ('<a href="mailto:support@wpessential.org">support@wpessential.org</a>', 'Customers with a licence')]),
            ('Office', [(OFFICE['street'], OFFICE['city']), ('<a href="tel:%s">%s</a>' % (OFFICE['phone'].replace(' ', ''), OFFICE['phone']), 'Monday – Friday, 9:00 – 18:00 PKT (UTC+5)'),
                        ('Vertex Systems Network (Pvt.) Ltd.', 'Registered company, Pakistan')])]
    side_cards = [b.group(b.h(t, level=3, className='wpe-ess-side__title'),
                          *[b.group(b.p('<strong>%s</strong>' % a_), b.p(d, className='wpe-ess-side__muted'), layout='default', className='wpe-ess-side__row') for a_, d in rows],
                          layout='default', className='wpe-ess-card wpe-ess-side') for t, rows in side]
    side_cards.append(b.group(b.h('Already a customer?', level=3, className='wpe-ess-side__title'),
                              b.p('<a href="{{page:docs}}">Documentation →</a><br><a href="{{page:support}}">Open a ticket →</a><br><a href="{{woo:myaccount}}">My account &amp; licences →</a>'),
                              layout='default', className='wpe-ess-card wpe-ess-card--ink wpe-ess-side'))
    side_cards.append(b.group(b.h('Prefer to talk?', level=3, className='wpe-ess-side__title'), b.p('Book a free 30-minute call about your project.', className='wpe-ess-side__muted'),
                              b.buttons(arrow('Book a call', '{{page:quote}}')), layout='default', className='wpe-ess-card wpe-ess-card--soft wpe-ess-side'))
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
        b.group(over('Contact'), b.h('How can we help?', level=1, className='wpe-ess-page__title'),
                b.p('Pre-sales questions, new projects or an existing order — pick a route and the right team replies, usually within one business day.', className='wpe-ess-lead'),
                b.group(*[b.group(ico(i_, 'soft'), b.h(t, level=3, className='wpe-ess-card__title'), b.p(d, className='wpe-ess-card__text'),
                                  b.p('<a href="%s">%s <span aria-hidden="true">→</span></a>' % (h_, l), className='wpe-ess-card__link wpe-ess-card__link--blue'),
                                  layout='default', className='wpe-ess-card') for i_, t, d, l, h_ in routes],
                        layout='grid', columns=3, grid_min='16rem', className='wpe-ess-grid wpe-ess-grid--3'),
                tag='section', align='full', className='wpe-ess-page-hero', layout='constrained', content='1680px'),
        section(b.columns(
            b.column(b.group(html('<span id="contact-form"></span>'), b.h('Send us a message', level=2, className='wpe-ess-form__title'),
                             b.p('Fields marked * are required. We use your details only to reply.', className='wpe-ess-side__muted'),
                             wpform('contact'), layout='default', className='wpe-ess-card wpe-ess-form'), width='66%'),
            b.column(*side_cards, width='34%'), className='wpe-ess-gap wpe-ess-contact-cols')),
        section(head('What happens next', 'From message to kickoff in four steps.'),
                b.group(*[b.group(b.p('0%d' % (i + 1), className='wpe-ess-num'), b.h(t, level=3, className='wpe-ess-card__title'), b.p(d, className='wpe-ess-card__text'),
                                  layout='default', className='wpe-ess-card') for i, (t, d) in enumerate(steps)],
                        layout='grid', columns=4, grid_min='13rem', className='wpe-ess-grid'), alt=True),
        g_work(),
        section(b.group(b.group(over('Services'), b.h('Not sure what you need? Start here.', size='h1', className='wpe-ess-h2'), layout='default'),
                        b.buttons(arrow('All services', '{{page:services}}', style='wpe-outline')),
                        layout='flex', wrap='wrap', justify='space-between', valign='bottom', className='wpe-ess-head'),
                b.group(*[b.group(ico(SERVICE_ICONS[i], 'soft'), b.group(b.p('<a href="%s"><strong>%s</strong></a>' % (svc_link(n), n)), b.p(price, className='wpe-ess-mono'), layout='default'),
                                  layout='flex', wrap='nowrap', valign='center', className='wpe-ess-card wpe-ess-mini') for i, (_, n, _d, price) in enumerate(SERVICES[:10])],
                        layout='grid', columns=5, grid_min='12rem', className='wpe-ess-grid')),
        g_team(),
        section(head('FAQ', 'Questions we hear before every project.'),
                b.columns(*[b.column(b.faq(half)) for half in (faq[:6], faq[6:])], className='wpe-ess-gap'), alt=True),
        section(b.group(*[b.group(b.p('<strong>%s</strong>' % t), b.p(d, className='wpe-ess-side__muted'), layout='default', className='wpe-ess-card wpe-ess-trust') for t, d in trust],
                        layout='grid', columns=4, grid_min='13rem', className='wpe-ess-grid'),
                g_band('Have a project in mind?', 'Free 30-minute call and a fixed quote within two business days.', 'Request a quote', '{{page:quote}}')))


CARE_PLANS = [('Basic', '49', ['Weekly updates & backups', 'Uptime monitoring', 'Monthly report', '1h of fixes'], False),
              ('Growth', '129', ['Everything in Basic', 'Daily backups, 90-day history', 'Malware scans & firewall', '3h of fixes & small edits', 'Speed check every month'], True),
              ('Business', '299', ['Everything in Growth', 'Staging site for updates', 'WooCommerce order checks', '8h of development', 'Same-day priority support'], False)]
# Only what is really built. 1.0.0 is not tagged yet, so it is listed as in development.
RELEASES = [('1.0.0', 'In development', [('New', 'Base, Maison and Pulse demos for Gutenberg and Elementor'),
                                         ('New', 'One-click demo import with resumable steps and Remove demo'),
                                         ('New', 'AI Connect (MCP) with connected sessions, tracking and revoke'),
                                         ('New', 'Light and dark schemes, 20 languages and right-to-left support'),
                                         ('New', 'Licenses and subscriptions on WooCommerce with a full event log')])]
# From docs/STATUS.md: what is designed or planned, nothing promised beyond that.
ROADMAP = [('Now', ['WPEssential.org service pages', 'Variation swatches everywhere', 'Elementor version of every demo']),  # Figma 11
           ('Next', ['Restaurant and Course demos', 'Header & footer builder', 'Wishlist and compare']),
           ('Later', ['AI demo generator', 'Multisite licence manager', 'Figma plugin for demo kits'])]


def g_care_plans():
    cards = [b.group(b.h(n, level=3, className='wpe-ess-plan__name'), b.p('$%s <small>/ month</small>' % p, className='wpe-ess-plan__price'),
                     b.ul(inc, className='wpe-ess-check'), b.buttons(arrow('Choose ' + n, '{{page:quote}}', style=None if feat else 'wpe-outline'), className='wpe-ess-plan__cta'),
                     layout='default', className=b.cls('wpe-ess-plan', 'is-featured' if feat else None)) for n, p, inc, feat in CARE_PLANS]
    return section(head('Plans', 'Pick a plan — cancel any month.'), b.group(*cards, layout='grid', columns=3, grid_min='16rem', className='wpe-ess-grid wpe-ess-grid--3'))


def g_support():
    side = [('Before you open a ticket', 'Search the <a href="{{page:docs}}">docs</a> — most answers are there.<br>Update the theme and the WPEssential plugin.<br>Try with other plugins switched off.', ''),
            ('Response times', 'Pro: within 1 business day<br>Agency: same day<br>Care plans: priority queue', ''),
            ('Need it done for you?', 'Bug fixes from $79 — <a href="{{page:svc-bug-fixes}}">we fix it, you relax</a>.', 'wpe-ess-card--soft')]
    return b.join(g_hero_light('Support', 'How can we fix it?', 'Customers with an active licence get help from the developers who build the theme.'),
                  section(b.columns(
                      b.column(b.group(b.h('Open a ticket', level=2, className='wpe-ess-form__title'), wpform('support'), layout='default', className='wpe-ess-card wpe-ess-form'), width='62%'),
                      b.column(*[b.group(b.h(t, level=3, className='wpe-ess-side__title'), b.p(d, className='wpe-ess-side__muted'), layout='default', className=b.cls('wpe-ess-card wpe-ess-side', c))
                                 for t, d, c in side], width='38%'), className='wpe-ess-gap wpe-ess-contact-cols')))


def g_changelog():
    out = []
    for v, d, items in RELEASES:
        rows = ''.join('<li><span class="wpe-ess-tag wpe-ess-tag--%s">%s</span> %s</li>' % (t.lower(), t, x) for t, x in items)
        out.append(b.group(b.h('Version %s <small>%s</small>' % (v, d), level=2, className='wpe-ess-release__title'), html('<ul class="wpe-ess-release">%s</ul>' % rows),
                           layout='default', className='wpe-ess-card'))
    return b.join(g_hero_light('Changelog', 'What’s new in Hi WPEssential', 'Every release, with what changed and why.'), section(*out))


def g_roadmap():
    slug = lambda t: re.sub(r'[^a-z0-9]+', '-', t.lower()).strip('-')
    cols = [b.group(b.h(c, level=3, className='wpe-ess-card__title'), *[b.group(b.p('<strong>%s</strong>' % i), b.shortcode('[wpessential_vote id="%s"]' % slug(i)), layout='default', className='wpe-ess-card wpe-ess-roadmap__item')
                                                                    for i in items], layout='default', className='wpe-ess-card wpe-ess-card--alt') for c, items in ROADMAP]
    return b.join(g_hero_light('Roadmap', 'What we are building next', 'Vote for features and see what is in progress. Suggest your own from My Account.'),
                  section(b.group(*cols, layout='grid', columns=3, grid_min='16rem', className='wpe-ess-grid wpe-ess-grid--3')))


def g_quote():
    return b.join(
        g_hero_light('Request a quote', 'Tell us about your project', 'A few questions, then a fixed quote, scope and timeline within two business days — free, no obligation.', parent=('Services', '{{page:services}}'), show_over=False),
        section(b.columns(
            b.column(b.group(wpform('quote'), layout='default', className='wpe-ess-card wpe-ess-form'), width='66%'),
            b.column(b.group(b.h('What happens next', level=3, className='wpe-ess-side__title'),
                             b.ul(['We read your brief the same day.', 'A short call if anything is unclear.', 'Fixed quote, scope and timeline within two business days.',
                                   'You approve, we schedule the kickoff.'], className='wpe-ess-check'), layout='default', className='wpe-ess-card wpe-ess-side'),
                     b.group(b.h('Prefer email?', level=3, className='wpe-ess-side__title'),
                             b.p('<a href="mailto:services@wpessential.org">services@wpessential.org</a><br>We sign NDAs on request.'),
                             layout='default', className='wpe-ess-card wpe-ess-card--ink wpe-ess-side'), width='34%'),
            className='wpe-ess-gap wpe-ess-contact-cols')))



def g_pro():
    """Figma 22: the WPEssential Pro product page; the buy box is live from the plan products (inc/woocommerce.php)."""
    cards = ''.join('<div class="wpe-ess-pro-mod"><span class="wpe-ess-pro-mod__tag is-%s">%s</span><h3>%d %s</h3><ul>%s</ul></div>'
                    % (tone, g, len(m), 'module' if len(m) == 1 else 'modules', ''.join('<li>%s</li>' % x for x in m)) for g, tone, m in PRO_MODULES)
    return b.join(
        section(b.group(
            b.group(html('<div class="wpe-ess-pro__art"><span>WPEssential Pro</span></div><p class="wpe-ess-pro__meta">22 modules · Pro demos · licence key + updates</p>'),
                    layout='default', className='wpe-ess-pro__media'),
            b.group(html('<span class="wpe-ess-pro__badge">Pro plugin</span><h1 class="wpe-ess-pro__title">WPEssential Pro</h1><p class="wpe-ess-pro__lead">Pro extension for the free WPEssential plugin.</p>'),
                    b.shortcode('[wpessential_pro_buy]'), layout='default', className='wpe-ess-pro__buy'),
            layout='default', className='wpe-ess-pro')),
        section(html('<p class="wpe-ess-cart__over">— Included</p><h2 class="wpe-ess-pro__h2">All 22 Pro modules</h2><div class="wpe-ess-pro-mods">%s</div>' % cards)))


def g_cart():
    return b.join(section(html('<p class="wpe-ess-cart__over">— Cart</p><h1 class="wpe-ess-cart__title">Your cart</h1>'), b.el('woo-notices', {}), b.el('woo-cart', {})))


def g_checkout():
    return b.join(section(b.el('woo-notices', {}), b.el('woo-checkout', {})))


def g_account():
    return b.join(section(html('<h1 class="wpe-ess-cart__title wpe-ess-account__title">My account</h1>'), b.el('woo-notices', {}), b.el('woo-account', {})))


def g_service(i):
    _, name, desc, price = SERVICES[i]
    slug, headline, who, included, turnaround = svc.DETAIL[name]
    related = [SERVICES[(i + k) % len(SERVICES)] for k in (1, 2, 3)]
    return b.join(
        b.group(b.columns(
            b.column(html('<p class="wpe-ess-crumb wpe-ess-crumb--light"><a href="{{page:services}}">Services</a> <span>/</span> %s</p>' % name),
                     over('Service', tone='orange'), b.h(headline, level=1, className='wpe-ess-page__title wpe-ess-page__title--light'),
                     b.p(desc, className='wpe-ess-hero__lead'),
                     html('<ul class="wpe-ess-facts"><li><span>Starting at</span><b>%s</b></li><li><span>Typical turnaround</span><b>%s</b></li><li><span>After launch</span><b>Fixes included</b></li></ul>'
                          % (price.replace('from ', ''), turnaround)),
                     b.buttons(arrow('Request a quote', '#service-quote'), b.button('Book a call', '{{page:contact}}', style='wpe-ghost-light')),
                     width='56%', valign='center'),
            b.column(b.image(SERVICE_IMAGES[i], name, className='wpe-ess-shot'), width='44%', valign='center'), valign='center', className='wpe-ess-gap'),
            tag='section', align='full', className='wpe-ess-page-hero wpe-ess-page-hero--dark wpe-ess-page-hero--left', layout='constrained', content='1680px'),
        section(b.columns(
            b.column(over('Who it is for'), b.h(who, size='h1', className='wpe-ess-h2'), width='50%'),
            b.column(b.group(b.h('What is included', level=3, className='wpe-ess-side__title'), b.ul(included, className='wpe-ess-check'), layout='default', className='wpe-ess-card wpe-ess-card--alt'), width='50%'),
            className='wpe-ess-gap')),
        *([g_care_plans()] if slug == 'care-plans' else []),
        g_work(),
        section(head('Process', 'Brief, estimate, build, review, launch.'),
                b.group(*[b.group(b.p('0%d' % (k + 1), className='wpe-ess-num'), b.h(t, level=3, className='wpe-ess-card__title'), b.p(d, className='wpe-ess-card__text'), layout='default', className='wpe-ess-card')
                          for k, (t, d) in enumerate([('Brief', 'Call or form'), ('Estimate', 'Fixed quote in 2 days'), ('Build', 'Staging previews'),
                                                      ('Review & QA', 'Speed, mobile, accessibility'), ('Launch', 'Go live and hand over')])],
                        layout='grid', columns=5, grid_min='11rem', className='wpe-ess-grid'), alt=True),
        section(b.columns(
            b.column(over('Good to know'), b.h('What we need from you', level=3, className='wpe-ess-side__title'),
                     b.ul(['Admin access to WordPress and hosting', 'Your logo, content and examples you like', 'One person who approves the work'], className='wpe-ess-check'),
                     b.h('Not included', level=3, className='wpe-ess-side__title'),
                     b.ul(['Paid plugins, stock photos or hosting fees', 'Work outside the agreed scope (we quote it first)'], className='wpe-ess-check'), width='50%'),
            b.column(b.group(html('<span id="service-quote"></span>'), b.h('Get a quote for %s' % name, level=3, className='wpe-ess-side__title'), wpform('service'),
                             layout='default', className='wpe-ess-card wpe-ess-form'), width='50%'),
            className='wpe-ess-gap')),
        section(head('Related', 'Often booked together'),
                b.group(*[b.group(b.h('<a href="%s">%s</a>' % (svc_link(n), n), level=3, className='wpe-ess-card__title'), b.p(d, className='wpe-ess-card__text'),
                                  b.p(p_, className='wpe-ess-mono'), layout='default', className='wpe-ess-card') for _, n, d, p_ in related],
                        layout='grid', columns=3, grid_min='16rem', className='wpe-ess-grid wpe-ess-grid--3'), alt=True))


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
        e.container(e.shortcode('[wpessential_demo_cards stats="1"]'), bg='ink', pad=(8, 24, 56, 24)),
        e.container(e.overline('Demos'), e.heading('Start from a finished site'), e.shortcode('[wpessential_demo_cards]'), e.button('All demos', '{{page:demos}}', 'outline'),
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
           e.container(e.shortcode('[wpessential_demo_cards facets="1"]'), pad=(24, 24, 56, 24))]
    for i, d in enumerate([x for x in DEMOS if x["site"]]):
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


def e_docs():
    return [e_title('Documentation', 'How can we help?', 'Guides for the theme, the demos and your account.'),
            e.container(e.text('<form role="search" method="get" action="{{home:}}" class="wpe-search-form"><input type="search" name="s" placeholder="Search the docs…" aria-label="Search the docs"> <button type="submit">Search</button></form>'), pad=(0, 24, 40, 24)),
            e.container(e.heading('Browse by topic'), e_icon_cards(DOCS), pad=(40, 24, 72, 24)),
            e.container(e.heading('Popular articles'), e.grid(*[e.image_box(p['image'], p['title'], p['excerpt'], link='{{page:%s}}' % p['key'], height=220) for p in POSTS], columns=3),
                        bg='surface-alt', pad=(72, 24, 72, 24)),
            e.container(e.heading('Still stuck?', 3, align='center'), e.text('Open a ticket and a WPEssential developer replies within one business day.', align='center'),
                        e.button('Open a ticket', '{{page:support}}', align='center'), pad=(72, 24, 72, 24))]


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


def e_work():
    return e.container(e.overline('Built on Hi WPEssential'), e.heading('Sites you can open and try today.'),
                       e.text('Every project ships on Hi WPEssential, so you can keep editing it yourself after launch.'),
                       e.grid(*[e.image_box(k, t, tag, height=220) for k, tag, t in [
                           ('site-base', 'Business', 'Base: services, pricing, blog and a small shop'),
                           ('site-maison', 'WooCommerce store', 'Maison: fashion store with lookbook and atelier'),
                           ('site-pulse', 'Events', 'Pulse: conference site with agenda, speakers and tickets')]], columns=3),
                       pad=(80, 24, 80, 24))


def e_team():
    return e.container(e.map_widget(OFFICE['map'], height=420), pad=(80, 24, 80, 24))


def e_contact():
    side = [('fas fa-clock', 'Response times', 'Sales: within one business day<br>Projects: quote within two business days<br>Support: through the ticket desk'),
            ('fas fa-envelope', 'Email us directly', 'sales@wpessential.org<br>services@wpessential.org<br>support@wpessential.org'),
            ('fas fa-building', 'Office', '%s<br>%s<br>%s · Mon – Fri, 9:00 – 18:00 PKT<br>Vertex Systems Network (Pvt.) Ltd.' % (OFFICE['street'], OFFICE['city'], OFFICE['phone'])),
            ('fas fa-life-ring', 'Already a customer?', '<a href="{{page:docs}}">Documentation</a> · <a href="{{page:support}}">Open a ticket</a> · <a href="{{woo:myaccount}}">My account</a>')]
    steps = [('We reply', 'A real person answers within one business day.'), ('Discovery call', '30 minutes on goals, content, budget and deadlines.'),
             ('Fixed quote', 'Scope, price and timeline in writing — no surprises.'), ('Kickoff', 'We start on the agreed date and share weekly previews.')]
    return [e_title('Contact', 'How can we help?', 'Pre-sales questions, new projects or an existing order — the right team replies, usually within one business day.'),
            e.container(e_icon_cards([('fas fa-credit-card', 'Plans & licensing', 'Which plan fits, lifetime licences, agencies and invoices.'),
                                      ('fas fa-palette', 'Start a project', 'Design, setup, WooCommerce, migration, speed or care plans. <a href="{{page:quote}}">Request a quote →</a>'),
                                      ('fas fa-headset', 'Already a customer?', 'Bugs, demo import or licence questions. <a href="{{page:support}}">Open a ticket →</a>')]), pad=(8, 24, 8, 24)),
            e.container(e.row(e.col(e.heading('Send us a message', 2, size_px=28), e.shortcode('[wpforms id="{{form:contact}}" title="false"]'), width=64, pad=32, radius=20, border='border', bg='surface'),
                              e.col(*[e.container(e.icon_box(i, t, d), inner=True, pad=20, radius=16, border='border', bg='surface') for i, t, d in side], width=36, gap=16), gap=32),
                        pad=(24, 24, 80, 24)),
            e.container(e.overline('What happens next'), e.heading('From message to kickoff in four steps.'),
                        e.grid(*[e.container(e.heading('0%d' % (k + 1), 3, 'primary', size_px=28), e.heading(t, 3, size_px=20), e.text(d), inner=True, pad=24, radius=16, bg='surface', gap=8)
                                 for k, (t, d) in enumerate(steps)], columns=4), bg='surface-alt', pad=(80, 24, 80, 24)),
            e_work(), e_team(),
            e.container(e.overline('FAQ'), e.heading('Questions we hear before every project.'),
                        e.accordion([('Do you offer pre-sales help?', 'Yes — email sales@wpessential.org or use the form.'), ('Is there a refund policy?', 'Every plan has a 14-day money-back guarantee.'),
                                     ('Do you work on sites not built with Hi WPEssential?', 'Yes. Most services work on any WordPress site.'), ('Do you sign NDAs?', 'Yes, on request.'),
                                     ('Which time zones do you cover?', 'Pakistan time (UTC+5), overlapping with Europe, the Gulf and US mornings.')]), bg='surface-alt', pad=(80, 24, 80, 24))]


def e_services():
    steps = [('Discovery call', '30 minutes on goals, content and budget.'), ('Proposal', 'Fixed price, scope and timeline within 48 hours.'),
             ('Build', 'Weekly previews on a staging site you can click through.'), ('Launch & care', 'We go live, train your team and stay on call.')]
    return [e_title('Services', 'Your site, launched by the people who build the theme.', 'Design, setup, speed and care from the WPEssential team at Vertex Systems Network (Pvt.) Ltd.'),
            e.container(e.overline('What we do'), e.heading('Services'),
                        e_icon_cards([(i, t, d + ' <a href="%s">%s →</a>' % (svc_link(t), p)) for i, t, d, p in SERVICES]), pad=(48, 24, 72, 24)),
            e.container(e.overline('Process'), e.heading('How a project runs'),
                        e.grid(*[e.container(e.heading('0%d' % (k + 1), 3, 'primary', size_px=32), e.heading(t, 3, size_px=20), e.text(d), inner=True, gap=8) for k, (t, d) in enumerate(steps)], columns=4),
                        bg='surface-alt', pad=(72, 24, 72, 24)),
            e_work(), e_team(),
            e.container(e.heading('Tell us about your project', 3, align='center'), e.text('Free 30-minute call. Fixed quote within 48 hours.', align='center'),
                        e.button('Book a free call', '{{page:contact}}', align='center'), pad=(72, 24, 72, 24))]


def e_service(i):
    _, name, desc, price = SERVICES[i]
    slug, headline, who, included, turnaround = svc.DETAIL[name]
    out = [e.container(e.row(e.col(e.text('<a href="{{page:services}}">Services</a> / ' + name, 'on-ink-muted', size_px=13), e.overline(name, 'on-ink'),
                                   e.heading(headline, 1, 'on-ink', size_px=56), e.text(desc, 'on-ink-muted', size_px=17),
                                   e.text('<strong>Starting at %s</strong> · <strong>Typical turnaround %s</strong>' % (price.replace('from ', ''), turnaround), 'on-ink', size_px=15),
                                   e.row(e.button('Request a quote', '{{page:quote}}'), e.button('Book a call', '{{page:contact}}', 'light'), gap=12, mobile_direction='row', wrap=True), width=55, justify='center'),
                             e.col(e.image('site-base', name, radius=16), width=45), gap=48), bg='ink', pad=(80, 24, 80, 24)),
           e.container(e.row(e.col(e.overline('Who it is for'), e.heading(who, 2, size_px=36), width=50),
                             e.col(e.container(e.heading('What is included', 3, size_px=20), e.icon_list(included), inner=True, pad=28, radius=20, bg='surface-alt'), width=50), gap=48),
                       pad=(80, 24, 80, 24))]
    if slug == 'care-plans':
        out.append(e.container(e.overline('Plans'), e.heading('Pick a plan — cancel any month.'),
                               e.grid(*[e.container(e.heading(n, 3, 'on-ink' if feat else 'text', size_px=20), e.heading('$%s / month' % p, 2, 'on-ink' if feat else 'text', size_px=36),
                                                    e.icon_list(inc, color_slug='on-ink' if feat else 'text'), e.button('Choose ' + n, '{{page:quote}}', 'primary' if feat else 'outline'),
                                                    inner=True, pad=28, radius=20, border=None if feat else 'border', bg='ink' if feat else 'surface', gap=14)
                                        for n, p, inc, feat in CARE_PLANS], columns=3), pad=(48, 24, 80, 24)))
    out += [e_work(),
            e.container(e.row(e.col(e.overline('FAQ'), e.heading('Questions about ' + name), width=45),
                              e.col(e.heading('Get a quote for ' + name, 3, size_px=22), e.shortcode('[wpforms id="{{form:service}}" title="false"]'), width=55, pad=28, radius=20, border='border', bg='surface'), gap=48),
                        bg='surface-alt', pad=(80, 24, 80, 24))]
    return out


def e_quote():
    return [e_title('Request a quote', 'Tell us about your project', 'A fixed quote, scope and timeline within two business days. Free 30-minute call included.'),
            e.container(e.row(e.col(e.shortcode('[wpforms id="{{form:quote}}" title="false"]'), width=64, pad=32, radius=20, border='border', bg='surface'),
                              e.col(e.container(e.icon_box('fas fa-list-check', 'What happens next', 'We read your brief the same day, call if anything is unclear, and send a fixed quote within two business days.'),
                                                inner=True, pad=20, radius=16, bg='surface-alt'), width=36), gap=32), pad=(24, 24, 80, 24)),
            e_work()]


def e_support():
    side = [('fas fa-book', 'Before you open a ticket', 'Search the <a href="{{page:docs}}">docs</a>, update the theme and plugin, and try with other plugins switched off.'),
            ('fas fa-clock', 'Response times', 'Pro: within 1 business day<br>Agency: same day<br>Care plans: priority queue'),
            ('fas fa-screwdriver-wrench', 'Need it done for you?', 'Bug fixes from $79 — <a href="{{page:svc-bug-fixes}}">we fix it, you relax</a>.')]
    return [e_title('Support', 'How can we fix it?', 'Customers with an active licence get help from the developers who build the theme.'),
            e.container(e.row(e.col(e.heading('Open a ticket', 2, size_px=28), e.shortcode('[wpforms id="{{form:support}}" title="false"]'), width=62, pad=32, radius=20, border='border', bg='surface'),
                              e.col(*[e.container(e.icon_box(i, t, d), inner=True, pad=20, radius=16, border='border', bg='surface') for i, t, d in side], width=38, gap=16), gap=32),
                        pad=(24, 24, 96, 24))]


def e_changelog():
    return [e_title('Changelog', 'What’s new in Hi WPEssential', 'Every release, with what changed and why.')] + [
        e.container(e.container(e.heading('Version %s' % v, 2, size_px=28), e.text(d, size_px=13), e.icon_list(['%s — %s' % (t, x) for t, x in items], icon='fas fa-circle-check'),
                                inner=True, pad=28, radius=20, border='border', bg='surface', gap=10), pad=(12, 24, 12, 24)) for v, d, items in RELEASES] + [e.container(e.spacer(48), pad=0)]


def e_roadmap():
    return [e_title('Roadmap', 'What we are building next', 'What is in progress and what comes after it. Suggest a demo or feature through Support.'),
            e.container(e.grid(*[e.container(e.heading(c, 3, size_px=22), e.icon_list(items, icon='fas fa-caret-up'), inner=True, pad=24, radius=20, bg='surface-alt', gap=12)
                                 for c, items in ROADMAP], columns=3), pad=(24, 24, 96, 24))]


# Package --------------------------------------------------------------------------------

def style_data():
    """The essential style variation plus this demo's own CSS (css/essential.css). It travels in the
    package and lands in the site's global styles, so the base theme carries no demo CSS."""
    here = os.path.dirname(os.path.abspath(__file__))
    data = json.load(open(os.path.join(here, '..', '..', 'styles', 'essential.json')))
    data.setdefault('styles', {})['css'] = open(os.path.join(here, 'css', 'essential.css')).read()
    return data


def package(builder):
    e.HOVER = {'button': 'float', 'image': 'grow', 'icon': 'float', 'filter_rest': None, 'invert': False, 'transition': 0.24}
    e.FONTS = {'primary': 'Bricolage Grotesque', 'text': 'Plus Jakarta Sans', 'weight': '700'}
    media = [{'key': k, 'file': k + '.webp', 'title': t, 'alt': a} for k, t, a in MEDIA]
    terms = [
        {'key': 'pc-demos', 'taxonomy': 'product_cat', 'name': 'Demos', 'slug': 'demos', 'meta': {'thumbnail_id': '{{media_id:demo-maison}}'}},
        {'key': 'pc-plans-monthly', 'taxonomy': 'product_cat', 'name': 'Monthly plans', 'slug': 'plans-monthly', 'meta': {'thumbnail_id': '{{media_id:shot-library}}'}},
        {'key': 'pc-plans-yearly', 'taxonomy': 'product_cat', 'name': 'Yearly plans', 'slug': 'plans-yearly', 'meta': {'thumbnail_id': '{{media_id:shot-library}}'}},
        {'key': 'pc-plans-lifetime', 'taxonomy': 'product_cat', 'name': 'Lifetime plans', 'slug': 'plans-lifetime', 'meta': {'thumbnail_id': '{{media_id:shot-library}}'}},
        {'key': 'pc-pro-addons', 'taxonomy': 'product_cat', 'name': 'Pro add-ons', 'slug': 'pro-addons'},
        {'key': 'pc-services', 'taxonomy': 'product_cat', 'name': 'Services', 'slug': 'services', 'meta': {'thumbnail_id': '{{media_id:shot-import}}'}},
        {'key': 'cat-news', 'taxonomy': 'category', 'name': 'News', 'slug': 'news'},
        {'key': 'cat-guides', 'taxonomy': 'category', 'name': 'Guides', 'slug': 'guides'},
    ]
    pages = [('home', 'Home', 'home', g_home, e_home), ('demos', 'Demos', 'demos', g_demos, e_demos),
             ('plugins', 'Plugins', 'plugins', g_plugins, e_plugins), ('pricing', 'Pricing', 'pricing', g_pricing, e_pricing), ('services', 'Services', 'services', g_services, e_services),
             ('docs', 'Docs', 'docs', g_docs, e_docs), ('about', 'About', 'about', g_about, e_about),
             ('contact', 'Contact', 'contact', g_contact, e_contact), ('quote', 'Request a quote', 'request-a-quote', g_quote, e_quote),
             ('support', 'Support', 'support', g_support, e_support),
             ('changelog', 'Changelog', 'changelog', g_changelog, e_changelog), ('roadmap', 'Roadmap', 'roadmap', g_roadmap, e_roadmap)]
    if True:  # Both builders: the Elementor pages reuse the kit markup (elementor.from_blocks).
        pages += [('pro', 'WPEssential Pro', 'wpessential-pro', g_pro, None), ('doc-import', 'Import a demo in one click', 'import-a-demo-in-one-click', g_doc_article, None), ('cart', 'Your cart', 'cart', g_cart, None), ('checkout', 'Checkout', 'checkout', g_checkout, None), ('my-account', 'My account', 'my-account', g_account, None)]
    pages += [('svc-' + svc.DETAIL[n][0], n, svc.DETAIL[n][0], (lambda i=i: g_service(i)), (lambda i=i: e_service(i))) for i, (_, n, _d, _p) in enumerate(SERVICES)]
    content = []
    for key, title, slug, g, el in pages:
        item = {'key': key, 'type': 'page', 'title': title, 'slug': slug, 'template': 'page-full-width'}
        if builder == 'gutenberg':
            item['content'] = g()
        else:
            item['content'] = ''
            item['elementor'] = e.from_blocks(g())
        content.append(item)
    content.append({'key': 'blog', 'type': 'page', 'title': 'Blog', 'slug': 'blog', 'content': ''})
    for p in POSTS:
        content.append({'key': p['key'], 'type': 'post', 'title': p['title'], 'slug': p['slug'], 'featured': p['image'], 'date': p['date'],
                        'terms': {'category': [p['cat']]}, 'excerpt': p['excerpt'], 'content': post_html(p['body'])})
    products = []
    for d in DEMOS:
        c = next(x for x in DEMO_CARDS if x[0] == d['key'])
        products.append({'key': d['key'], 'name': d['name'] + ' demo', 'slug': d['name'].lower() + '-demo', 'price': d['price'], 'categories': ['pc-demos'],
                         'image': d['image'], 'gallery': [d['site'], 'mobile-' + d['name'].lower()] if d['site'] else [d['image']], 'virtual': True, 'menu_order': d['menu_order'], 'short': d['short'],
                         'meta': {'_wpe_niches': ','.join(c[6]), '_wpe_badge': 'NEW' if 'NEW' in c[2] else '', '_wpe_card_text': c[4], '_wpe_card_meta': c[7], '_wpe_includes': ','.join((['woocommerce'] if 'shop' in c[6] else []) + ['dark-mode', 'rtl'])},
                         'description': '<p>%s</p><ul><li>Native Gutenberg and Elementor versions</li><li>One-click import with required plugins</li><li>Light &amp; dark style variation</li></ul>' % d['short']})
    products.append({'key': 'addon-setup', 'name': 'Setup & demo import', 'slug': 'setup-demo-import', 'price': '99', 'categories': ['pc-pro-addons'],
                     'virtual': True, 'sold_individually': True, 'menu_order': 1,
                     'short': 'We install the theme and plugins, activate your licence and import your chosen demo.',
                     'description': '<p>We install the theme and plugins, activate your licence and import your chosen demo. We email you within 1 business day to collect site access.</p>'})
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
    settings = {'front_page': 'home', 'blog_page': 'blog', 'style': 'essential', 'style_data': style_data(), 'motion': 'subtle',
                'parts': {'header': {'content': g_header()}, 'header-shop': {'content': g_header()}, 'header-transparent': {'content': g_header()}, 'header-checkout': {'content': g_header()},
                          'footer': {'content': g_footer()}, 'footer-simple': {'content': g_footer()}},
                'options': {'posts_per_page': 9, 'blogname': 'WPEssential', 'blogdescription': 'Premium WordPress themes and one-click demos.',
                            'wpessential_hero_stats': HERO_STATS, 'wpessential_testimonials': TESTIMONIALS,
                            'wpessential_blog_over': 'Blog', 'wpessential_blog_title': 'Guides and release notes',
                            'wpessential_blog_lead': 'Tutorials for the theme, new demos and WordPress tips from the WPEssential team.',
                            'wpessential_404_title': 'This page took a different demo.', 'wpessential_404_lead': 'The link may be old or mistyped. Search the site or start from one of these.'},
                'woocommerce': {'pages': {'cart': 'cart', 'checkout': 'checkout', 'myaccount': 'my-account'}, 'options': {'woocommerce_enable_myaccount_registration': 'yes', 'woocommerce_registration_generate_username': 'yes', 'woocommerce_registration_generate_password': 'no', 'woocommerce_currency': 'USD', 'woocommerce_currency_pos': 'left', 'woocommerce_price_num_decimals': '0',
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
    'highlights': ['6 demo products', 'Monthly, yearly & lifetime plans', 'Docs & services', 'Light & dark'],
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

LEGAL_WIDTH = '1360px'  # Figma 20
# Figma 49-51: refund policy, licence terms and billing FAQ ({key: (title, slug, overline, subtitle, sections)}).
LEGAL_EXTRA = {
    'refund-policy': ('Refund policy', 'refund-policy', 'Legal', None, [
        ('Pro plugin (via Kelviq)', ['Request a full refund within 14 days of purchase. Kelviq, our merchant of record, processes the refund to your original payment method.']),
        ('Subscriptions', ['Cancel any time. You keep access until the end of the paid period. Renewals can be refunded within 7 days.']),
        ('Pro add-on services', ['Add-on services bought with Pro can be refunded before work begins.']),
        ('Invoiced services', ['Custom services follow the terms in your quote. Completed milestones are non-refundable.']),
        ('How to ask', ['<a href="{{page:support}}">Open a ticket</a> or email support@wpessential.org with your order number.'])]),
    'licence-terms': ('Licence terms', 'licence-terms', 'Legal', 'WPEssential Pro licence', [
        ('What you get', ['A licence key for the number of sites in your plan, with updates and support while active.']),
        ('GPL', ['WPEssential and WPEssential Pro are GPL-licensed. The key gives access to updates, Pro demos and support.']),
        ('Sites', ['Move a licence between sites any time from My Account → Licences.']),
        ('Expiry', ['If a licence expires, Pro keeps working but stops receiving updates and Pro demo imports.'])]),
    'billing-faq': ('Billing FAQ', 'billing-faq', 'Help', 'Answers to common payment questions', [
        ('Who charges my card?', ['Products are sold by Kelviq as merchant of record, so your statement shows Kelviq. Services are invoiced by Vertex Systems Network.']),
        ('Do you charge tax?', ['Kelviq calculates and collects sales tax or VAT where it applies.']),
        ('Which methods are accepted?', ['Products: cards and wallets via Kelviq. Services: Payoneer, PayPal, Stripe or bank transfer.']),
        ('Can I get an invoice with my company details?', ['Yes. Add your company and tax ID under My Account → Addresses before buying.'])]),
}
# Brand, contact email and what the site sells, used for the legal pages (tools/demo-builder/legal.py).
LEGAL = ('WPEssential, a Vertex Systems Network (Pvt.) Ltd. company', 'support@wpessential.org', 'themes, demos, plans and services')
