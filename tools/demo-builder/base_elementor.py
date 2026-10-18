"""Base demo — Elementor package. Same content and media keys as core/demos/base/gutenberg."""
import json
import os

import elementor as e

HERE = os.path.dirname(os.path.abspath(__file__))
GUTENBERG = os.path.join(os.path.dirname(os.path.dirname(HERE)), 'core', 'demos', 'base', 'gutenberg', 'content.json')

SERVICES = [('fas fa-pen-ruler', 'Brand & web design', 'Identity, design systems and websites that look premium on every screen.'),
            ('fas fa-code', 'WordPress development', 'Fast block themes and Elementor builds, tested and documented.'),
            ('fas fa-cart-shopping', 'Online stores', 'WooCommerce shops with checkout flows that convert.'),
            ('fas fa-chart-line', 'Growth & SEO', 'Search, speed and analytics so the site keeps working after launch.')]
FAQ = [('How long does a website take?', 'Most business sites launch in four to six weeks from the first workshop.'),
       ('Can I edit the site myself?', 'Yes. Every page is built with Gutenberg or Elementor and comes with a short training session.'),
       ('Do you offer support after launch?', 'Every plan includes 30 days of support; monthly care plans are available after that.'),
       ('Which builder should I choose?', 'Both are first-class. Choose Gutenberg for speed and Elementor if your team prefers visual editing.')]
PLANS = [('Free', '$0', 'For personal sites getting started.', 'Download free', ['12 free demos', 'Gutenberg + Elementor', 'Core blocks & patterns', 'Community support']),
         ('Pro', '$59', 'For freelancers and growing businesses.', 'Get Pro', ['All 40+ demos', 'Premium blocks & widgets', 'WooCommerce demos', '1 year of updates', 'Priority email support']),
         ('Agency', '$199', 'For teams building client sites.', 'Get Agency', ['Everything in Pro', 'Use on unlimited sites', 'White-label dashboard', 'Client handoff kit'])]
PLAN_FAQ = [('Can I switch between Gutenberg and Elementor later?', 'Yes. Every demo exists in both versions, and your colors and fonts carry over automatically.'),
            ('What happens when my license expires?', 'Your site keeps working. You just stop receiving updates and new Pro demo imports until you renew.'),
            ('Do you offer refunds?', 'Yes — a full refund within 14 days, no questions asked.')]
VALUES = [('fas fa-bolt', 'Performance first', 'Every demo has a performance budget and is tested on a mid-range phone.'),
          ('fas fa-circle-check', 'Accessible by default', 'WCAG 2.1 AA colors, focus states and semantic markup out of the box.'),
          ('fas fa-layer-group', 'No lock-in', 'Content lives in core blocks or Elementor — switch themes any time.'),
          ('fas fa-user', 'Human support', 'Real developers answer your tickets, usually within a day.')]
SERVICE_CARDS = [('fas fa-download', 'Demo setup', 'We import your chosen demo, connect your domain and configure plugins.', 'From $149'),
                 ('fas fa-bars', 'Brand customization', 'Your colors, fonts, logo and copy applied across every template.', 'From $399'),
                 ('fas fa-cart-shopping', 'WooCommerce launch', 'Products, payments, shipping zones and emails configured and tested.', 'From $699'),
                 ('fas fa-bolt', 'Speed optimization', 'Caching, image compression and Core Web Vitals tuning.', 'From $199'),
                 ('fas fa-lock', 'Care plan', 'Monthly updates, backups, uptime monitoring and security scans.', '$49 / month'),
                 ('fas fa-clock', 'Priority support', 'Same-day answers and screen-share sessions with a developer.', '$29 / month')]
STEPS = [('01', 'Pick a demo', 'Browse 40+ demos and preview them live.'), ('02', 'Choose a builder', 'Gutenberg or Elementor — same design.'),
         ('03', 'Import in one click', 'Plugins, content and settings are set up for you.'), ('04', 'Make it yours', 'Edit text, swap images and publish.')]


def home():
    return [
        e.container(e.overline('New · 40+ demos for Gutenberg & Elementor', 'on-ink'), e.heading('Launch a premium WordPress site in minutes, not weeks', 1, 'on-ink', size_px=64),
                    e.text('Import a ready-made demo for Gutenberg or Elementor with one click, then make it yours.', 'on-ink-muted', size_px=18),
                    e.row(e.button('Browse demos', '{{page:services}}'), e.button('Watch 2-min tour', '{{page:about}}', 'light'), gap=12, mobile_direction='row', wrap=True),
                    e.image('hero', 'A Hi WPEssential site on a laptop and phone', radius=16),
                    bg='ink', pad=(120, 24, 96, 24), align='center', gap=24),
        e.container(e.overline('Why Hi WPEssential'), e.heading('Everything a modern site needs, nothing it doesn\'t'),
                    e.grid(*[e.icon_box(i, t, d) for i, t, d in [('fas fa-download', 'One-click demos', 'Import a complete site with pages, products and menus.'),
                                                                 ('fas fa-layer-group', 'Two builders', 'Every demo ships for Gutenberg and Elementor.'),
                                                                 ('fas fa-bolt', 'Fast by default', 'No jQuery, local fonts and tiny CSS.')]], columns=3), pad=(96, 24, 96, 24)),
        e.container(e.overline('Demo library'), e.heading('Start from a site that already works'),
                    e.grid(*[e.image_box(k, t, b_, height=300) for k, t, b_ in [('demo-business', 'Business', 'Free'), ('demo-maison', 'Maison — Fashion shop', 'Pro'),
                                                                               ('demo-pulse', 'Pulse — Conference', 'Pro')]], columns=3), pad=(96, 24, 96, 24)),
        e.container(e.row(e.col(e.overline('Your builder, your choice', 'on-ink'), e.heading('Same design. Gutenberg or Elementor.', 2, 'on-ink'),
                                e.text('Switch builders without losing your brand. Colors, fonts and spacing come from one theme.json and sync into Elementor\'s global kit automatically.', 'on-ink-muted'),
                                e.icon_list(['Shared design tokens across both builders', 'Elementor Pro Theme Builder locations supported', 'Full Site Editing templates and patterns'], color_slug='on-ink'),
                                width=50, justify='center'),
                          e.col(e.image('hero', 'A Hi WPEssential site being edited', radius=16), width=50), gap=56),
                    bg='ink', pad=(96, 24, 96, 24)),
        e.container(e.overline('Loved by builders'), e.heading('What our customers say'),
                    e.grid(e.testimonial('We launched our new site in a weekend. The demo import did all the heavy lifting.', 'Ayesha Khan', 'Design Lead', 'team-1'),
                           e.testimonial('Switching between Gutenberg and Elementor without a redesign is a game changer.', 'Daniel Reyes', 'Developer', 'team-2'),
                           e.testimonial('Fast, clean and easy for our editors. Exactly what we needed.', 'Sara Malik', 'Marketing Manager', 'team-3'), columns=3),
                    bg='surface-alt', pad=(96, 24, 96, 24)),
        e.container(e.overline('From the blog'), e.heading('Guides for faster, better WordPress sites'),
                    e.grid(*[e.image_box(img, t, x, link='{{page:%s}}' % k, height=240) for k, img, t, x in [
                        ('article-1', 'post-1', 'Building a design system your whole team can use', 'Turn colours, type and spacing into shared tokens.'),
                        ('article-2', 'post-2', 'A launch checklist for small business websites', 'Twelve checks we run before every site goes live.'),
                        ('article-3', 'post-3', 'Gutenberg or Elementor? Choosing the right builder', 'Both are first-class in Hi WPEssential.')]], columns=3), pad=(96, 24, 96, 24)),
        e.container(e.heading('Ready to launch your next site?', 2, 'on-ink', align='center'), e.button('Get started', '{{page:pricing}}', align='center'),
                    bg='ink', pad=(96, 24, 96, 24), align='center'),
    ]


def title(over, t, lead):
    return e.container(e.overline(over), e.heading(t, 1, size_px=56), e.text(lead, size_px=18), pad=(96, 24, 40, 24))


def about():
    return [title('About', 'We build WordPress themes people actually enjoy using',
                  'Our team has shipped sites for agencies, stores and publishers since 2014. Hi WPEssential is everything we learned, packaged.'),
            e.container(e.row(e.col(e.image_fixed('post-1', 'A designer building a site at her desk', 440, radius=16), width=46),
                              e.col(e.overline('Our story'), e.heading('Small team, big standards'),
                                    e.text('We started as an agency rebuilding the same sites over and over. Hi WPEssential replaces that repetition with demos you can trust: accessible markup, real performance budgets and no lock-in.'),
                                    e.grid(e.counter(12, 'Building for WordPress', ' yrs'), e.counter(18, 'Active installs', 'k+'), e.counter(24, 'Support response', 'h'), columns=3),
                                    width=54, justify='center'), gap=56), pad=(72, 24, 96, 24)),
            e.container(e.overline('What we value', align='center'), e.heading('Principles behind every release', align='center'),
                        e.grid(*[e.icon_box(i, t, d) for i, t, d in VALUES], columns=4), bg='surface-alt', pad=(96, 24, 96, 24)),
            e.container(e.overline('The team', align='center'), e.heading('People behind Hi WPEssential', align='center'),
                        e.grid(*[e.image_box('team-%d' % i, n, r, height=300) for i, (n, r) in enumerate(
                            [('Ayesha Malik', 'Founder & Lead Designer'), ('Bilal Khan', 'Head of Engineering'), ('Sara Ahmed', 'Customer Success'), ('Usman Tariq', 'WooCommerce Specialist')], 1)], columns=4),
                        pad=(96, 24, 96, 24))]


def services():
    return [title('Services', 'Services that take you from idea to launch', 'Use Hi WPEssential yourself, or let our team set up, customize and maintain your site.'),
            e.container(e.grid(*[e.icon_box(i, t, d + '<br><strong>' + p + '</strong>') for i, t, d, p in SERVICE_CARDS], columns=3), pad=(48, 24, 96, 24)),
            e.container(e.overline('How it works', 'on-ink', align='center'), e.heading('Live in four simple steps', 2, 'on-ink', align='center'),
                        e.grid(*[e.col(e.heading(n, 3, 'on-ink', size_px=36), e.heading(t, 4, 'on-ink', size_px=18), e.text(d, 'on-ink-muted'), gap=8, pad=24, radius=16, border='border')
                                 for n, t, d in STEPS], columns=4), bg='ink', pad=(96, 24, 96, 24))]


def pricing():
    return [title('Pricing', 'Simple pricing. Every plan includes both builders.', 'Start free and upgrade when you need Pro demos, premium blocks and priority support.'),
            e.container(e.grid(*[e.container(e.heading(n, 3), e.heading(p, 2, 'on-ink' if n == 'Pro' else 'primary'), e.text(sub, 'on-ink-muted' if n == 'Pro' else 'text-muted'),
                                             e.button(cta, '{{page:contact}}', 'primary' if n == 'Pro' else 'outline'),
                                             e.icon_list(items, color_slug='on-ink' if n == 'Pro' else 'text'),
                                             inner=True, pad=32, radius=16, border=None if n == 'Pro' else 'border', bg='ink' if n == 'Pro' else 'surface')
                                 for n, p, sub, cta, items in PLANS], columns=3), pad=(48, 24, 72, 24)),
            e.container(e.overline('FAQ', align='center'), e.heading('Questions about plans', align='center'), e.accordion(PLAN_FAQ), pad=(48, 24, 96, 24), bg='surface-alt')]


def contact():
    return [title('Contact', 'Let\'s talk about your project', 'We reply within one working day.'),
            e.container(e.grid(e.icon_box('fas fa-envelope', 'Email', 'hello@wpessential.org'), e.icon_box('fas fa-phone', 'Phone', '+1 (555) 014-2290'),
                               e.icon_box('fas fa-location-dot', 'Office', '120 Market Street, San Francisco, CA'), columns=3), pad=(24, 24, 48, 24)),
            e.container(e.heading('Send us a message'), e.text('Tell us what you need and we reply within one working day.'),
                        e.shortcode('[wpforms id="{{form:contact}}" title="false"]'), bg='surface-alt', pad=(72, 24, 96, 24))]


def faq():
    return [title('FAQ', 'Frequently asked questions', 'Can\'t find an answer? Contact us.'), e.container(e.accordion(FAQ), pad=(24, 24, 96, 24))]


def package():
    e.HOVER = {'button': 'float', 'image': 'grow', 'icon': 'float', 'filter_rest': None, 'invert': False, 'transition': 0.45}
    e.FONTS = {'primary': 'Plus Jakarta Sans', 'text': 'Inter', 'weight': '800'}
    data = json.load(open(GUTENBERG))
    builders = {'home': home, 'about': about, 'services': services, 'pricing': pricing, 'contact': contact, 'faq': faq}
    for item in data['content']:
        if item['key'] in builders:
            item['content'] = ''
            item['elementor'] = builders[item['key']]()
            item['template'] = 'page-full-width'
    data['settings']['elementor_kit'] = e.kit(container_width=1200, button_radius=10, field_radius=10)
    return data

# Brand, contact email and what the site sells, used for the legal pages (tools/demo-builder/legal.py).
LEGAL = ('{{site:name}}', '{{site:email}}', 'our products and services')
