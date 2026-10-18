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
PLANS = [('Starter', '$29', ['1 website', 'All free demos', 'Community support']),
         ('Pro', '$79', ['3 websites', 'All Pro demos', 'Priority support', 'Header & footer builder']),
         ('Agency', '$199', ['Unlimited websites', 'White label', 'Dedicated support'])]


def home():
    return [
        e.container(e.overline('Multi-niche WordPress theme', 'on-ink'), e.heading('Build any website in minutes', 1, 'on-ink', size_px=72),
                    e.text('Import a ready-made demo for Gutenberg or Elementor with one click, then make it yours.', 'on-ink-muted', size_px=18),
                    e.row(e.button('Get started', '{{page:pricing}}'), e.button('Learn more', '{{page:about}}', 'light'), gap=12, mobile_direction='row', wrap=True),
                    e.image('hero', 'A WPEssential site on a laptop and phone', radius=16),
                    bg='ink', pad=(120, 24, 96, 24), align='center', gap=24),
        e.container(e.overline('Why WPEssential'), e.heading('Everything you need, nothing you don\'t'),
                    e.grid(*[e.icon_box(i, t, d) for i, t, d in [('fas fa-download', 'One-click demos', 'Import a complete site with pages, products and menus.'),
                                                                 ('fas fa-layer-group', 'Two builders', 'Every demo ships for Gutenberg and Elementor.'),
                                                                 ('fas fa-bolt', 'Fast by default', 'No jQuery, local fonts and tiny CSS.')]], columns=3), pad=(96, 24, 96, 24)),
        e.container(e.overline('Services'), e.heading('What we do'), e.grid(*[e.icon_box(i, t, d) for i, t, d in SERVICES], columns=4), bg='surface-alt', pad=(96, 24, 96, 24)),
        e.container(e.grid(e.counter(12, 'Demos', '+'), e.counter(98, 'Performance score'), e.counter(4500, 'Happy customers', '+'), e.counter(24, 'Support', '/7'), columns=4),
                    pad=(72, 24, 72, 24)),
        e.container(e.overline('Testimonials'), e.heading('Loved by teams'),
                    e.grid(e.testimonial('We launched our new site in a weekend. The demo import did all the heavy lifting.', 'Ayesha Khan', 'Design Lead', 'team-1'),
                           e.testimonial('Switching between Gutenberg and Elementor without a redesign is a game changer.', 'Daniel Reyes', 'Developer', 'team-2'),
                           e.testimonial('Fast, clean and easy for our editors. Exactly what we needed.', 'Sara Malik', 'Marketing Manager', 'team-3'), columns=3),
                    bg='surface-alt', pad=(96, 24, 96, 24)),
        e.container(e.overline('Blog'), e.heading('Latest articles'),
                    e.grid(*[e.image_box(img, t, x, link='{{page:%s}}' % k, height=240) for k, img, t, x in [
                        ('article-1', 'post-1', 'Building a design system your whole team can use', 'Turn colours, type and spacing into shared tokens.'),
                        ('article-2', 'post-2', 'A launch checklist for small business websites', 'Twelve checks we run before every site goes live.'),
                        ('article-3', 'post-3', 'Gutenberg or Elementor? Choosing the right builder', 'Both are first-class in WPEssential.')]], columns=3), pad=(96, 24, 96, 24)),
        e.container(e.heading('Ready to build your website?', 2, 'on-ink', align='center'), e.button('Get started', '{{page:pricing}}', align='center'),
                    bg='ink', pad=(96, 24, 96, 24), align='center'),
    ]


def title(over, t, lead):
    return e.container(e.overline(over), e.heading(t, 1, size_px=56), e.text(lead, size_px=18), pad=(96, 24, 40, 24))


def about():
    return [title('About us', 'A small team that ships', 'We design and build WordPress websites for growing businesses.'),
            e.container(e.row(e.col(e.image_fixed('demo-business', 'Business website on a laptop', 440, radius=16), width=50),
                              e.col(e.heading('Our story'), e.text('Started in 2016 as two freelancers, we now help hundreds of businesses launch faster with reusable, well-made demos.'),
                                    e.icon_list(['Design-first, always', 'Performance budgets on every page', 'Plain-language support']), width=50, justify='center'), gap=56),
                        pad=(40, 24, 96, 24)),
            e.container(e.heading('The team'), e.grid(*[e.image_box('team-%d' % i, n, r, height=300) for i, (n, r) in enumerate(
                [('Ayesha Khan', 'Design Lead'), ('Daniel Reyes', 'Developer'), ('Sara Malik', 'Marketing'), ('James Carter', 'Support')], 1)], columns=4),
                        bg='surface-alt', pad=(96, 24, 96, 24))]


def services():
    return [title('Services', 'How we can help', 'From the first sketch to the hundredth blog post.'),
            e.container(e.grid(*[e.icon_box(i, t, d) for i, t, d in SERVICES], columns=2), pad=(24, 24, 96, 24))]


def pricing():
    return [title('Pricing', 'Simple, honest pricing', 'Every plan includes updates for a year and a 14-day refund.'),
            e.container(e.grid(*[e.container(e.heading(n, 3), e.heading(p, 2, 'primary'), e.icon_list(items), e.button('Choose ' + n, '{{page:contact}}'),
                                             inner=True, pad=32, radius=16, border='border', bg='surface') for n, p, items in PLANS], columns=3), pad=(24, 24, 96, 24))]


def contact():
    return [title('Contact', 'Let\'s talk about your project', 'We reply within one working day.'),
            e.container(e.grid(e.icon_box('fas fa-envelope', 'Email', 'hello@wpessential.org'), e.icon_box('fas fa-phone', 'Phone', '+1 (555) 014-2290'),
                               e.icon_box('fas fa-location-dot', 'Office', '120 Market Street, San Francisco, CA'), columns=3), pad=(24, 24, 96, 24))]


def faq():
    return [title('FAQ', 'Frequently asked questions', 'Can\'t find an answer? Contact us.'), e.container(e.accordion(FAQ), pad=(24, 24, 96, 24))]


def package():
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
