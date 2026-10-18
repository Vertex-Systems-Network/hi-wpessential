"""Maison — fashion store demo (Pro). Research and direction: docs/demos/maison.md."""
import blocks as b
import demo_forms as forms
import elementor as e

ID = 'maison'

MEDIA = [
    ('hero-campaign', 'Autumn campaign', 'Model in an ivory linen suit walking through a sunlit stone courtyard'),
    ('lookbook-1', 'Look 01 — Camel and cream', 'Model in a camel wool coat and cream trousers against a terracotta wall'),
    ('lookbook-2', 'Look 02 — Espresso silk', 'Model in an espresso silk shirt and wide ivory trousers in a sunlit studio'),
    ('lookbook-3', 'Look 03 — Terracotta linen', 'Model in a terracotta linen maxi dress walking through dry grass at sunset'),
    ('atelier-hands', 'Hand finishing', 'Tailor hand-stitching ivory linen at a wooden workbench'),
    ('atelier-studio', 'The studio', 'Bright atelier with arched windows, dress forms and rails of neutral garments'),
    ('cat-dresses', 'Dresses', 'Model in a sand linen midi dress on a cream background'),
    ('cat-knitwear', 'Knitwear', 'Two folded knit sweaters on a travertine bench'),
    ('cat-outerwear', 'Outerwear', 'Model in a camel double-breasted coat walking on a Paris street in autumn'),
    ('cat-accessories', 'Accessories', 'Tan leather tote with a printed silk scarf on warm stone'),
    ('p-linen-dress', 'Linen Midi Dress', 'Model wearing the terracotta linen midi dress, front view'),
    ('p-linen-dress-alt', 'Linen Midi Dress — back', 'Model wearing the terracotta linen midi dress, back view'),
    ('p-wool-coat', 'Belted Wool Coat', 'Model wearing the camel belted wool coat over a white turtleneck'),
    ('p-cashmere-knit', 'Cashmere Crew Knit', 'Model wearing the oat cashmere crew knit with ivory trousers'),
    ('p-silk-shirt', 'Silk Shirt', 'Model wearing the espresso silk shirt with ivory wide-leg trousers'),
    ('p-leather-tote', 'Leather Tote', 'Tan leather tote bag on a cream background'),
    ('p-wide-trousers', 'Wide-Leg Trousers', 'Model wearing ivory wide-leg trousers with a white T-shirt'),
    ('p-loafers', 'Suede Penny Loafers', 'Pair of chocolate suede penny loafers'),
    ('journal-1', 'Natural dyes', 'Linen swatches in natural dye colours with bowls of pigment'),
    ('journal-2', 'Lisbon in linen', 'Woman reading on a balcony above the rooftops of Lisbon'),
    ('journal-3', 'A smaller wardrobe', 'Wardrobe rail with linen shirts and wool coats in neutral tones'),
    ('store-paris', 'Paris store', 'Interior of the Maison store in Paris with an arched window and travertine table'),
]

TERMS = [
    ('pc-dresses', 'product_cat', 'Dresses', 'dresses', 'cat-dresses'),
    ('pc-knitwear', 'product_cat', 'Knitwear', 'knitwear', 'cat-knitwear'),
    ('pc-outerwear', 'product_cat', 'Outerwear', 'outerwear', 'cat-outerwear'),
    ('pc-tailoring', 'product_cat', 'Shirts & Trousers', 'shirts-trousers', 'p-silk-shirt'),
    ('pc-accessories', 'product_cat', 'Bags & Shoes', 'bags-shoes', 'cat-accessories'),
    ('cat-atelier', 'category', 'Atelier', 'atelier', None),
    ('cat-style', 'category', 'Style notes', 'style-notes', None),
    ('cat-travel', 'category', 'Travel', 'travel', None),
]

SIZES = {'name': 'Size', 'options': ['XS', 'S', 'M', 'L']}
CARE = '<p><strong>Care.</strong> Cool wash or dry clean. Dry flat, steam to release creases.</p><p><strong>Made in</strong> our partner atelier in Porto, Portugal. Every piece is numbered inside the collar.</p>'

PRODUCTS = [
    dict(key='p-linen-dress', name='Linen Midi Dress', price='245', kind='variable', attributes=[SIZES, {'name': 'Colour', 'options': ['Terracotta']}],
         categories=['pc-dresses'], image='p-linen-dress', gallery=['p-linen-dress-alt', 'lookbook-3'], featured=True,
         short='Washed European linen with a gathered skirt and wide straps. Falls just below the knee.',
         description='<p>Cut from 210 g washed linen that softens with every wear. Square neckline, fitted bodice and an easy gathered skirt with side pockets.</p>' + CARE),
    dict(key='p-sundress', name='Washed Linen Sundress', price='210', categories=['pc-dresses'], image='cat-dresses', gallery=['p-linen-dress'],
         short='Sand linen sundress with a square neck and a full, swinging skirt.',
         description='<p>A summer staple in undyed flax. Fully lined bodice, adjustable straps and a skirt cut on the bias so it moves when you walk.</p>' + CARE),
    dict(key='p-maxi', name='Terracotta Maxi Dress', price='295', sale='249', categories=['pc-dresses'], image='lookbook-3', gallery=['p-linen-dress'],
         short='Tiered maxi in garment-dyed ramie with balloon sleeves and a tie waist.',
         description='<p>Dyed with madder root for a warm, uneven terracotta. Tiered skirt, tasselled waist tie and buttoned cuffs.</p>' + CARE),
    dict(key='p-wool-coat', name='Belted Wool Coat', price='590', kind='variable', attributes=[SIZES], categories=['pc-outerwear'],
         image='p-wool-coat', gallery=['lookbook-1', 'cat-outerwear'], featured=True,
         short='Double-faced camel wool with a notch lapel and a self-tie belt.',
         description='<p>Unlined double-faced wool, finished by hand so every seam is clean on both sides. Deep patch pockets and a removable belt.</p>' + CARE),
    dict(key='p-camel-coat', name='Double-Breasted Camel Coat', price='640', categories=['pc-outerwear'], image='cat-outerwear', gallery=['p-wool-coat'],
         stock='out', short='Long double-breasted coat in brushed camel hair. Back in stock in November.',
         description='<p>Brushed camel hair from Biella, horn buttons and a half-belt at the back. A coat for the next twenty winters.</p>' + CARE),
    dict(key='p-cashmere', name='Cashmere Crew Knit', price='260', kind='variable', attributes=[SIZES, {'name': 'Colour', 'options': ['Oat', 'Ivory']}],
         categories=['pc-knitwear'], image='p-cashmere-knit', gallery=['cat-knitwear'], featured=True,
         short='Relaxed crew neck in 2-ply Mongolian cashmere.',
         description='<p>Knitted from 2-ply grade-A cashmere with fully fashioned shoulders and ribbed hems. Soft, warm and light enough to layer.</p>' + CARE),
    dict(key='p-ribbed', name='Ribbed Wool Jumper', price='180', categories=['pc-knitwear'], image='cat-knitwear', gallery=['p-cashmere-knit'], stock=18,
         short='Chunky rib in undyed Merino. Wear it oversized.',
         description='<p>Undyed Merino from a small mill in the Pyrenees, knitted in a deep English rib.</p>' + CARE),
    dict(key='p-silk-shirt', name='Silk Shirt', price='220', kind='variable', attributes=[SIZES], categories=['pc-tailoring'],
         image='p-silk-shirt', gallery=['lookbook-2'], short='Fluid espresso silk with mother-of-pearl buttons.',
         description='<p>Sand-washed silk crepe de chine with a relaxed collar and long cuffs. Tuck it in or wear it open over a slip dress.</p>' + CARE),
    dict(key='p-trousers', name='Wide-Leg Trousers', price='195', kind='variable', attributes=[SIZES], categories=['pc-tailoring'],
         image='p-wide-trousers', gallery=['lookbook-2', 'p-silk-shirt'], featured=True,
         short='High-waisted ivory trousers with front pleats and a pressed crease.',
         description='<p>Linen-wool twill with a high rise, two front pleats and a full-length leg. Side zip and slanted pockets.</p>' + CARE),
    dict(key='p-blazer', name='Linen Suit Blazer', price='340', categories=['pc-tailoring'], image='hero-campaign', gallery=['p-wide-trousers'], stock=6,
         short='Unstructured single-breasted blazer in ivory linen.',
         description='<p>Half-lined so it breathes in summer, with patch pockets and a soft shoulder.</p>' + CARE),
    dict(key='p-tote', name='Leather Tote', price='380', categories=['pc-accessories'], image='p-leather-tote', gallery=['cat-accessories'],
         featured=True, stock=12, short='Vegetable-tanned leather tote that fits a 14" laptop.',
         description='<p>Vegetable-tanned calf from Tuscany, raw edges burnished by hand and an unlined interior with one zip pocket. It darkens beautifully with use.</p><p><strong>Care.</strong> Wipe with a dry cloth; condition twice a year.</p>'),
    dict(key='p-loafers', name='Suede Penny Loafers', price='290', kind='variable', attributes=[{'name': 'Size', 'options': ['37', '38', '39', '40', '41']}],
         categories=['pc-accessories'], image='p-loafers', short='Chocolate suede loafers on a stacked leather sole.',
         description='<p>Blake-stitched in Spain from water-repellent suede with a leather lining and a stacked heel.</p><p><strong>Care.</strong> Brush after wear; use a suede protector before the first rain.</p>'),
]

POSTS = [
    dict(key='post-dyes', title='A field guide to natural dyes', slug='natural-dyes', image='journal-1', cat='cat-atelier', date='2026-09-04 09:00:00',
         excerpt='Madder, weld and walnut: how our dye house gets warm, living colour without synthetic fixers.',
         body=[('p', 'Every terracotta in this collection started as a root. Natural dyes never give the same colour twice, and that is the point: each batch has its own depth, and it softens instead of fading.'),
               ('h', 'The three plants we use most'),
               ('ul', ['<strong>Madder root</strong> for terracotta and rose.', '<strong>Weld</strong> for straw yellows and, over indigo, olive.', '<strong>Walnut hulls</strong> for sand, taupe and espresso.']),
               ('img', 'journal-1', 'Linen swatches in natural dye colours'),
               ('h', 'How to care for naturally dyed linen'),
               ('p', 'Wash cool with a pH-neutral detergent, turn the garment inside out and dry it in the shade. Expect the colour to mellow over the first few washes.'),
               ('quote', 'Synthetic colour sits on the fibre. Natural colour lives inside it.', 'Inês Moreira, head of dyeing')]),
    dict(key='post-lisbon', title='Lisbon in linen: a weekend wardrobe', slug='lisbon-in-linen', image='journal-2', cat='cat-travel', date='2026-09-18 09:00:00',
         excerpt='Seven pieces, one carry-on and three days of tiled streets, long lunches and evening light.',
         body=[('p', 'Lisbon is a city of hills, so pack for walking. We spent a long weekend in Alfama with one carry-on and came back with more ideas than photos.'),
               ('h', 'What we packed'),
               ('ul', ['Linen Suit Blazer and Wide-Leg Trousers — worn together and apart.', 'Silk Shirt for dinner at Prado.', 'Ribbed Wool Jumper for the miradouros after dark.', 'Suede Penny Loafers, broken in before the trip.']),
               ('img', 'journal-2', 'Reading on a balcony above Lisbon'),
               ('p', 'The trick is a palette that always works: ivory, sand and one warm accent. Everything goes with everything, so seven pieces become a dozen outfits.')]),
    dict(key='post-wardrobe', title='The 30-piece wardrobe', slug='thirty-piece-wardrobe', image='journal-3', cat='cat-style', date='2026-10-02 09:00:00',
         excerpt='Why owning fewer clothes makes getting dressed easier, and the pieces we would keep.',
         body=[('p', 'Most of us wear a fifth of what we own. A smaller wardrobe is not about rules; it is about keeping only the pieces you reach for first.'),
               ('h', 'Start with what you already love'),
               ('p', 'Pull out everything you wore last month. Look for repeats: the shape of trousers you trust, the colour you always pick. Those are your foundations.'),
               ('img', 'journal-3', 'A rail of neutral linen and wool pieces'),
               ('h', 'Then buy slowly'),
               ('p', 'Add one piece a season, in a fibre that will last. Natural fibres age well, can be repaired and do not shed microplastics in the wash.'),
               ('quote', 'Buy less, choose well, make it last.', 'Vivienne Westwood')]),
]


def post_html(body):
    out = []
    for item in body:
        kind = item[0]
        if kind == 'p':
            out.append(b.p(item[1]))
        elif kind == 'h':
            out.append(b.h(item[1]))
        elif kind == 'ul':
            out.append(b.ul(item[1]))
        elif kind == 'img':
            out.append(b.image(item[1], item[2]))
        elif kind == 'quote':
            out.append(b.quote(item[1], item[2]))
    return b.join(*out)


CATEGORIES = [('pc-dresses', 'cat-dresses', 'Dresses'), ('pc-knitwear', 'cat-knitwear', 'Knitwear'),
              ('pc-outerwear', 'cat-outerwear', 'Outerwear'), ('pc-accessories', 'cat-accessories', 'Bags & Shoes')]

PROMISES = [('Free shipping over €150', 'Delivered in 2–4 working days across the EU and UK.'),
            ('30-day returns', 'Unworn pieces come back free, no questions asked.'),
            ('Made in Europe', 'Cut and sewn in family ateliers in Portugal and Italy.'),
            ('Repairs for life', 'Send any Maison piece back and we will mend it.')]

FAQ = [
    ('How long does delivery take?', 'Orders placed before 2 pm ship the same day. EU and UK delivery takes 2–4 working days; the rest of the world 5–8.'),
    ('Is shipping free?', 'Yes, on orders over €150. Below that, standard delivery is €8 and express is €15.'),
    ('How do returns work?', 'You have 30 days to return unworn pieces with their tags. Start a return from My Account and print the prepaid label.'),
    ('When will I get my refund?', 'Within 5 working days of the parcel reaching our studio, to the original payment method.'),
    ('How do I find my size?', 'Each product page shows the garment measurements. Between sizes? Take the larger for knitwear and the smaller for tailoring.'),
    ('Do you repair Maison pieces?', 'Always. Email care@maison-atelier.com with a photo and we will send a free repair label.'),
]

STORES = [('Paris', '14 Rue de Saintonge, 75003 Paris', 'Tue–Sat 11:00–19:00', '+33 1 42 71 18 40'),
          ('Lisbon', 'Rua da Escola Politécnica 42, 1250-102 Lisboa', 'Mon–Sat 10:00–19:00', '+351 21 340 1180')]


# Gutenberg -----------------------------------------------------------------------------

def g_header():
    topbar = b.group(
        b.group(b.p('Complimentary shipping over €150 · Free returns within 30 days · Gift wrapping on request'),
                className='wpe-topbar__inner mx-topbar', layout='flex', wrap='nowrap', justify='center'),
        className='wpe-topbar', content='1200px')
    left = b.block('navigation', {'overlayMenu': 'mobile', 'className': 'mx-nav', 'layout': {'type': 'flex', 'justifyContent': 'left'}},
                   '\n'.join(b.block('navigation-link', {'label': l, 'url': u, 'kind': 'custom'}) for l, u in
                             [('New in', '{{woo:shop}}?orderby=date'), ('Dresses', '{{term_link:pc-dresses}}'),
                              ('Knitwear', '{{term_link:pc-knitwear}}'), ('Accessories', '{{term_link:pc-accessories}}')]))
    right = b.group(b.block('navigation', {'overlayMenu': 'never', 'className': 'mx-nav mx-nav--right', 'layout': {'type': 'flex', 'justifyContent': 'right'}},
                            '\n'.join(b.block('navigation-link', {'label': l, 'url': u, 'kind': 'custom'}) for l, u in
                                      [('Lookbook', '{{page:lookbook}}'), ('Journal', '{{page:journal}}')])),
                    b.block('search', {'label': 'Search', 'showLabel': False, 'buttonText': 'Search', 'buttonPosition': 'button-only',
                                       'buttonUseIcon': True, 'isSearchFieldHidden': True, 'query': {'post_type': 'product'}}),
                    b.block('woocommerce/customer-account', {'displayStyle': 'icon_only', 'iconStyle': 'line', 'iconClass': 'wc-block-customer-account__account-icon'}),
                    b.block('woocommerce/mini-cart'),
                    className='wpe-header-actions mx-actions', layout='flex', wrap='nowrap', justify='right')
    inner = b.group(left, b.block('site-title', {'className': 'mx-logo'}), right, className='wpe-header__inner mx-header__inner', layout='default')
    return b.join(topbar, b.group(inner, tag='header', className='wpe-header mx-header', content='1200px'))


def g_letter():
    return b.section(b.p('The Maison letter', style='wpe-overline', align='center'), b.h('Quiet notes on new collections', size='h2', align='center'),
                     b.p('One letter a month: new pieces, atelier stories and private sales. No noise.', align='center', color='text-muted'),
                     b.shortcode('[wpforms id="{{form:newsletter}}" title="false"]'), content='560px', bg='surface-alt', className='wpe-section mx-letter')


def g_footer():
    return g_letter() + '\n\n' + b.footer('Considered clothing, made slowly in small European ateliers since 2014.',
                    [('Shop', [('New in', '{{woo:shop}}?orderby=date'), ('Dresses', '{{term_link:pc-dresses}}'), ('Knitwear', '{{term_link:pc-knitwear}}'), ('Outerwear', '{{term_link:pc-outerwear}}'), ('Accessories', '{{term_link:pc-accessories}}')]),
                     ('Maison', [('Atelier', '{{page:atelier}}'), ('Lookbook', '{{page:lookbook}}'), ('Journal', '{{page:journal}}'), ('Stores', '{{page:contact}}')]),
                     ('Help', [('Shipping & returns', '{{page:help}}'), ('My account', '{{woo:myaccount}}'), ('Bag', '{{woo:cart}}'), ('Contact', '{{page:contact}}')]),
                     ('Visit', [('14 Rue de Saintonge, Paris', '{{page:contact}}'), ('Tue – Sat 11:00 – 19:00', '{{page:contact}}'), ('+33 1 42 71 18 40', 'tel:+33142711840'), ('care@maison-atelier.com', 'mailto:care@maison-atelier.com')])],
                    ('© Maison Atelier. All rights reserved.', 'Designed &amp; built with <a href="https://wpessential.org/">Hi WPEssential</a>'))


LOOKS = [('lookbook-1', 'Look 01 — Camel coat, cream trousers'), ('lookbook-2', 'Look 02 — Espresso silk, wide ivory linen'),
         ('lookbook-3', 'Look 03 — Terracotta linen maxi')]
STATS = [('6', 'partner ateliers'), ('100%', 'natural fibres'), ('Lifetime', 'free repairs')]


def g_home():
    hero = b.group(b.columns(
        b.column(b.p('Autumn · Winter 2026', style='wpe-overline'),
                 b.h('The Quiet Season', level=1, size='display', className='mx-hero__title'),
                 b.p('<em>linen, silk &amp; undyed wool</em>', className='mx-hero__sub'),
                 b.p('Cut in small runs in Porto and Florence. Pieces made to soften with every wear and last for decades.', color='text-muted'),
                 b.buttons(b.button('Shop the collection', '{{woo:shop}}'), b.button('View lookbook', '{{page:lookbook}}', style='wpe-outline')),
                 b.p('01 / 03 <span>— Look 01: linen suit, natural canvas tote</span>', className='mx-hero__count'),
                 width='50%', valign='center', className='mx-hero__text'),
        b.column(b.image('hero-campaign', 'Model in an ivory linen suit in a sunlit stone courtyard', className='mx-hero__img'), width='50%', className='mx-hero__media'),
        className='mx-hero__cols', stack=True), align='full', className='mx-hero', layout='default')
    def head(over, title, link, href, center=False):
        if center:
            return b.group(b.p(over, style='wpe-overline', align='center'), b.h(title, size='h2', align='center'), layout='default')
        return b.group(b.group(b.p(over, style='wpe-overline'), b.h(title, size='h2'), layout='default'),
                       b.p('<a href="%s">%s</a>' % (href, link), className='mx-link'),
                       layout='flex', wrap='wrap', justify='space-between', valign='bottom', className='mx-head')
    cats = b.section(head('Shop by category', 'Wardrobe foundations', 'All categories', '{{woo:shop}}'),
                     b.group(*[b.group(b.image(img, label, aspect='3/4', href='{{term_link:%s}}' % term),
                                       b.h('<a href="{{term_link:%s}}">%s</a>' % (term, label), level=3, size='h4'), layout='default', className='mx-cat')
                               for term, img, label in CATEGORIES], layout='grid', grid_min='14rem'))
    arrivals = b.section(head('New in', 'The first pieces of the season', 'Shop new in', '{{woo:shop}}?orderby=date'),
                         b.shortcode('[products limit="8" columns="4" orderby="date" order="DESC"]'), className='wpe-section wpe-section--sm')
    atelier = b.group(b.columns(
        b.column(b.image('atelier-hands', 'Tailor hand-stitching ivory linen', className='mx-split__img'), width='50%', className='mx-split__media'),
        b.column(b.p('The atelier', style='wpe-overline'), b.h('Made slowly,<br>worn for years', size='h1'),
                 b.p('Every Maison piece is cut and sewn by a family workshop we know by name. We pay above the regional wage, use natural fibres only and repair anything we make, free, for life.', color='text-muted'),
                 b.group(*[b.group(b.p(n, className='mx-stat__n'), b.p(l, className='mx-stat__l'), layout='default') for n, l in STATS],
                         layout='flex', wrap='wrap', className='mx-stats'),
                 b.p('<a href="{{page:atelier}}">Discover the atelier</a>', className='mx-link'),
                 width='50%', valign='center', className='mx-split__text'),
        className='mx-split', stack=True), align='full', bg='surface-alt', className='mx-split-wrap', layout='default')
    looks = b.section(head('Lookbook', 'Three ways to wear linen', '', '', center=True),
                      b.p('Styled in Provence by Inès Moreau', align='center', color='text-muted'),
                      b.group(*[b.group(b.image(k, t, aspect='3/4', href='{{page:lookbook}}'), b.p(t, className='mx-caption'), layout='default') for k, t in LOOKS],
                              layout='grid', columns=3, className='mx-looks'))
    promises = b.section(b.group(*[b.group(b.p('<strong>%s</strong>' % t), b.p(d, color='text-muted', size='small'), layout='default') for t, d in PROMISES],
                                 layout='grid', grid_min='14rem', className='mx-promises'), className='wpe-section wpe-section--sm mx-promise-band')
    journal = b.section(head('Journal', 'Notes from the studio', 'Read the journal', '{{page:journal}}'), b.latest_posts(3))
    visit = b.group(b.columns(
        b.column(b.image('store-paris', 'Inside the Paris store', className='mx-split__img'), width='50%', className='mx-split__media'),
        b.column(b.p('Visit us', style='wpe-overline', color='on-ink'), b.h('14 Rue de<br>Saintonge, Paris', size='h1', color='on-ink'),
                 b.p('Tuesday – Saturday, 11:00 – 19:00. Private fittings with our tailors by appointment.', color='on-ink'),
                 b.buttons(b.button('Book a fitting', '{{page:contact}}', style='wpe-light')),
                 width='50%', valign='center', className='mx-split__text'),
        className='mx-split', stack=True), align='full', bg='ink', className='mx-split-wrap mx-visit', layout='default')
    return b.join(hero, cats, arrivals, atelier, looks, promises, journal, visit)


LOOKS_FULL = [
    ('lookbook-1', 'Belted Wool Coat · Cashmere Crew Knit', ['p-wool-coat', 'p-cashmere']),
    ('lookbook-2', 'Silk Shirt · Wide-Leg Trousers', ['p-silk-shirt', 'p-trousers']),
    ('lookbook-3', 'Linen Midi Dress · Suede Penny Loafers', ['p-linen-dress', 'p-loafers']),
    ('hero-campaign', 'Linen Suit Blazer · Leather Tote', ['p-blazer', 'p-tote']),
]
STEPS = [('Fibre', 'Undyed linen from Normandy flax, cashmere and merino from certified herds.'),
         ('Pattern', 'Hélène drafts every pattern by hand and fits it on three body types.'),
         ('Atelier', 'Small teams cut and sew each piece; one person finishes it from start to end.'),
         ('Finish', 'Garment washing, hand-pressing and a signed label from the maker.')]
CARE_CARDS = [('fa-truck', 'Delivery', 'Free over €150 in the EU, UK and Switzerland. 2–4 working days, carbon-neutral.'),
              ('fa-rotate-left', 'Returns', '30 days, free collection from your door. Refunds within 5 days of arrival.'),
              ('fa-ruler', 'Sizing', 'Each product lists garment measurements. Book a video fitting with our tailors.')]
SHIPPING = [('France', '1–2 working days', 'Free over €150 · €8'), ('European Union', '2–4 working days', 'Free over €150 · €12'),
            ('United Kingdom & Switzerland', '3–5 working days', 'Free over €250 · €18'), ('United States & Canada', '4–6 working days', 'Free over €300 · €25')]


def page_head(crumb, over, title, lead=None, center=False):
    a = 'center' if center else None
    inner = [b.p('<a href="{{home:}}">Home</a> / %s' % crumb, className='mx-crumb')] if not center else []
    inner += [b.p(over, style='wpe-overline', align=a), b.h(title, level=1, size='display', align=a, className='mx-page-title')]
    if lead:
        inner.append(b.p(lead, color='text-muted', align=a))
    return b.section(*inner, className='wpe-section wpe-section--sm mx-page-head', content='760px' if center else '1200px')


def g_lookbook():
    intro = page_head('Lookbook', 'Lookbook · AW26', 'A summer in Provence',
                      'Photographed over five days in the Luberon by Inès Moreau. Every piece is available to shop below each look.', center=True)
    rows = []
    for n, (img, title, pids) in enumerate(LOOKS_FULL):
        img_col = b.column(b.image(img, title, aspect='7/5'), width='56%')
        txt_col = b.column(b.p('Look %02d' % (n + 1), className='mx-look__n'), b.h(title, size='h2'),
                           b.p('<a href="{{page:%s}}">Shop this look</a>' % pids[0], className='mx-link'), width='44%', valign='center')
        cols = (img_col, txt_col) if n % 2 == 0 else (txt_col, img_col)
        rows.append(b.columns(*cols, valign='center', className='wpe-gap-lg mx-look'))
    return b.join(intro, b.section(*rows, className='wpe-section wpe-section--sm'))


def g_atelier():
    head = page_head('Atelier', '', 'We make fewer things,<br>more carefully')
    wide = b.image('atelier-studio', 'The Maison studio with arched windows and rails of linen', className='mx-wide')
    story = b.section(b.columns(
        b.column(b.h('Founded in 2014 by Hélène Rousseau after a decade in Paris couture houses.', size='h2', className='mx-serif-lead')),
        b.column(b.p('Hélène wanted the craft of couture without its waste. Maison works with six family ateliers in Portugal and Italy, ordering in runs of 80 to 200 pieces so nothing ends in a landfill.', color='text-muted'),
                 b.p('We publish the cost of every garment, pay our partners above the regional living wage and keep a repair workshop open for anything we have ever sold.', color='text-muted')),
        className='wpe-gap-lg'))
    steps = b.section(b.p('How a piece is made', style='wpe-overline'), b.h('From bolt to wardrobe in five weeks', size='h2'),
                      b.group(*[b.group(b.p('%02d' % (i + 1), className='mx-step__n'), b.h(t, level=3, size='h5'), b.p(d, color='text-muted', size='small'),
                                        layout='default', className='mx-step') for i, (t, d) in enumerate(STEPS)], layout='grid', columns=4),
                      bg='surface-alt')
    maker = b.group(b.columns(
        b.column(b.image('atelier-hands', 'Rui Almeida hand-finishing a linen shirt', className='mx-split__img'), width='50%', className='mx-split__media'),
        b.column(b.p('Meet the makers', style='wpe-overline'),
                 b.h('“A seam should outlive the person who sewed it.”', size='h2', className='mx-quote'),
                 b.p('Rui Almeida, master tailor, Porto', color='text-muted', size='small'), width='50%', valign='center', className='mx-split__text'),
        className='mx-split', stack=True), align='full', className='mx-split-wrap', layout='default')
    return b.join(head, wide, story, steps, maker)


def g_contact():
    head = page_head('Contact', 'Stores &amp; contact', 'We would love to hear from you', 'Our client care team answers within one working day, Monday to Saturday.')
    stores = [b.group(b.h(city, level=3, size='h3'),
                      b.ul([addr, hours, '<a href="tel:%s">%s</a>' % (tel.replace(' ', ''), tel)], className='mx-store__list'), layout='default', className='mx-store')
              for city, addr, hours, tel in STORES]
    body = b.section(b.columns(
        b.column(b.shortcode('[wpforms id="{{form:care}}" title="false"]'), width='55%'),
        b.column(b.image('store-paris', 'Inside the Maison store in Paris', aspect='5/3'), *stores, width='45%'),
        className='wpe-gap-lg'), className='wpe-section wpe-section--sm')
    return b.join(head, body)


def g_help():
    head = page_head('Help', 'Client care', 'Shipping, returns &amp; FAQ', 'Everything about delivery, sizing and caring for your pieces.')
    cards = b.group(*[b.group(b.h(t, level=3, size='h3'), b.p(d, color='text-muted', size='small'),
                              layout='default', className='mx-card') for i, t, d in CARE_CARDS], layout='grid', columns=3)
    table = b.table(['Destination', 'Delivery time', 'Cost'], [list(r) for r in SHIPPING], className='mx-table')
    faq = b.group(b.p('FAQ', style='wpe-overline'), b.h('Frequently asked', size='h2'), *[b.details(q, b.p(a)) for q, a in FAQ], layout='default')
    return b.join(head, b.section(cards, table, faq, className='wpe-section wpe-section--sm'))


def e_home():
    hero = e.container(
        e.row(e.col(e.overline('Autumn · Winter 2026'), e.heading('The Quiet Season', 1, size_px=88),
                    e.heading('linen, silk & undyed wool', 3, 'text-muted', size_px=28),
                    e.text('Cut in small runs in Porto and Florence. Pieces made to soften with every wear and last for decades.', size_px=16),
                    e.row(e.button('Shop the collection', '{{woo:shop}}'), e.button('View lookbook', '{{page:lookbook}}', 'outline'), gap=12, wrap=True, mobile_direction='row'),
                    width=50, justify='center', pad=(64, 64, 64, 24)),
              e.col(e.image_fixed('hero-campaign', 'Model in an ivory linen suit in a sunlit stone courtyard', 720), width=50), gap=0),
        pad=(0, 0, 0, 0))
    cats = e.container(e.overline('Shop by category'), e.heading('Wardrobe foundations'),
                       e.grid(*[e.image_box(img, label, '', link='{{term_link:%s}}' % term, height=420) for term, img, label in CATEGORIES], columns=4),
                       pad=(96, 24, 48, 24))
    arrivals = e.container(e.overline('New in'), e.heading('The first pieces of the season'),
                           e.shortcode('[products limit="8" columns="4" orderby="date" order="DESC"]'), pad=(48, 24, 72, 24))
    atelier = e.container(
        e.row(e.col(e.image_fixed('atelier-hands', 'Tailor hand-stitching ivory linen', 620), width=50),
              e.col(e.overline('The atelier'), e.heading('Made slowly, worn for years', size_px=48),
                    e.text('Every Maison piece is cut and sewn by a family workshop we know by name. We pay above the regional wage, use natural fibres only and repair anything we make, free, for life.'),
                    e.grid(*[e.col(e.heading(n, 3, size_px=32), e.text(l, size_px=13), gap=4) for n, l in STATS], columns=3),
                    e.button('Discover the atelier', '{{page:atelier}}', 'outline'), width=50, justify='center', pad=(48, 24, 48, 48)), gap=0),
        bg='surface-alt', pad=(0, 0, 0, 0))
    looks = e.container(e.overline('Lookbook', align='center'), e.heading('Three ways to wear linen', align='center'),
                        e.text('Styled in Provence by Inès Moreau', align='center'),
                        e.grid(*[e.image_box(k, '', t, link='{{page:lookbook}}', height=520) for k, t in LOOKS], columns=3), pad=(96, 24, 72, 24))
    promises = e.container(e.grid(*[e.icon_box(icon, t, d) for (t, d), icon in zip(PROMISES, ['fas fa-truck', 'fas fa-rotate-left', 'fas fa-location-dot', 'fas fa-scissors'])], columns=4),
                           pad=(56, 24, 56, 24), border='border')
    journal = e.container(e.overline('Journal'), e.heading('Notes from the studio'),
                          e.grid(*[e.image_box(p['image'], p['title'], p['excerpt'], link='{{page:%s}}' % p['key'], height=280) for p in POSTS], columns=3),
                          pad=(96, 24, 96, 24))
    visit = e.container(
        e.row(e.col(e.image_fixed('store-paris', 'Inside the Paris store', 560), width=50),
              e.col(e.overline('Visit us', 'on-ink-muted'), e.heading('14 Rue de Saintonge, Paris', 2, 'on-ink', size_px=48),
                    e.text('Tuesday – Saturday, 11:00 – 19:00. Private fittings with our tailors by appointment.', 'on-ink'),
                    e.button('Book a fitting', '{{page:contact}}', 'light'), width=50, justify='center', pad=(48, 24, 48, 64)), gap=0),
        bg='ink', pad=(0, 0, 0, 0))
    return [hero, cats, arrivals, atelier, looks, promises, journal, visit]


def e_head(over, title, lead=None, center=False):
    al = 'center' if center else None
    return e.container(e.overline(over, align=al), e.heading(title, 1, align=al, size_px=80),
                       *([e.text(lead, align=al, size_px=16)] if lead else []), pad=(80, 24, 40, 24), align=al)


def e_lookbook():
    out = [e_head('Lookbook · AW26', 'A summer in Provence',
                  'Photographed over five days in the Luberon by Inès Moreau. Every piece is available to shop below each look.', center=True)]
    for n, (img, title, pids) in enumerate(LOOKS_FULL):
        pic = e.col(e.image_fixed(img, title, 460), width=56)
        txt = e.col(e.heading('Look %02d' % (n + 1), 3, 'primary', size_px=22), e.heading(title, 2, size_px=40),
                    e.button('Shop this look', '{{page:%s}}' % pids[0], 'outline'), width=44, justify='center')
        out.append(e.container(e.row(*((pic, txt) if n % 2 == 0 else (txt, pic)), gap=64), pad=(32, 24, 32, 24)))
    return out


def e_atelier():
    return [
        e_head('Atelier', 'We make fewer things, more carefully'),
        e.container(e.image_fixed('atelier-studio', 'The Maison studio with arched windows and rails of linen', 640), pad=0, boxed=False),
        e.container(e.row(e.col(e.heading('Founded in 2014 by Hélène Rousseau after a decade in Paris couture houses.', 2, size_px=36), width=50),
                          e.col(e.text('Hélène wanted the craft of couture without its waste. Maison works with six family ateliers in Portugal and Italy, ordering in runs of 80 to 200 pieces so nothing ends in a landfill.'),
                                e.text('We publish the cost of every garment, pay our partners above the regional living wage and keep a repair workshop open for anything we have ever sold.'), width=50), gap=64),
                    pad=(96, 24, 96, 24)),
        e.container(e.overline('How a piece is made'), e.heading('From bolt to wardrobe in five weeks'),
                    e.grid(*[e.col(e.heading('%02d' % (i + 1), 3, 'primary', size_px=28), e.heading(t, 4, size_px=18), e.text(d, size_px=14), gap=8)
                             for i, (t, d) in enumerate(STEPS)], columns=4), bg='surface-alt', pad=(96, 24, 96, 24)),
        e.container(e.row(e.col(e.image_fixed('atelier-hands', 'Rui Almeida hand-finishing a linen shirt', 560), width=50),
                          e.col(e.overline('Meet the makers'), e.heading('“A seam should outlive the person who sewed it.”', 2, size_px=40),
                                e.text('Rui Almeida, master tailor, Porto', size_px=14), width=50, justify='center', pad=(48, 24, 48, 64)), gap=0), pad=0),
    ]


def e_contact():
    stores = [e.col(e.heading(city, 3, size_px=28), e.icon_list([addr, hours, tel], icon='fas fa-location-dot'), gap=8, border='border', pad=20)
              for city, addr, hours, tel in STORES]
    return [
        e_head('Stores & contact', 'We would love to hear from you', 'Our client care team answers within one working day, Monday to Saturday.'),
        e.container(e.row(e.col(e.shortcode('[wpforms id="{{form:care}}" title="false"]'), width=55),
                          e.col(e.image_fixed('store-paris', 'Inside the Maison store in Paris', 320), *stores, width=45), gap=48), pad=(24, 24, 96, 24)),
    ]


def e_help():
    rows = ''.join('<tr><td>%s</td><td>%s</td><td>%s</td></tr>' % r for r in SHIPPING)
    table = '<table class="mx-table"><thead><tr><th>Destination</th><th>Delivery time</th><th>Cost</th></tr></thead><tbody>%s</tbody></table>' % rows
    return [
        e_head('Client care', 'Shipping, returns & FAQ', 'Everything about delivery, sizing and caring for your pieces.'),
        e.container(e.grid(*[e.icon_box('fas ' + i, t, d) for i, t, d in CARE_CARDS], columns=3), e.text(table), pad=(24, 24, 48, 24)),
        e.container(e.overline('FAQ'), e.heading('Frequently asked'), e.accordion(FAQ), pad=(24, 24, 96, 24)),
    ]


# Package --------------------------------------------------------------------------------

def package(builder):
    e.HOVER = {'button': '', 'image': '', 'icon': '', 'filter_rest': None, 'invert': True, 'transition': 0.9}
    e.FONTS = {'primary': 'Cormorant Garamond', 'text': 'Manrope', 'weight': '500'}
    media = [{'key': k, 'file': k + '.webp', 'title': t, 'alt': a} for k, t, a in MEDIA]
    terms = []
    for key, tax, name, slug, img in TERMS:
        t = {'key': key, 'taxonomy': tax, 'name': name, 'slug': slug}
        if img:
            t['meta'] = {'thumbnail_id': '{{media_id:%s}}' % img}
        terms.append(t)
    FW = 'page-full-width'
    pages = [
        ('home', 'Home', 'home', g_home, e_home),
        ('lookbook', 'Lookbook', 'lookbook', g_lookbook, e_lookbook),
        ('atelier', 'Atelier', 'atelier', g_atelier, e_atelier),
        ('contact', 'Stores & Contact', 'contact', g_contact, e_contact),
        ('help', 'Shipping & Returns', 'shipping-returns', g_help, e_help),
    ]
    content = []
    for key, title, slug, g, el in pages:
        item = {'key': key, 'type': 'page', 'title': title, 'slug': slug, 'template': FW}
        if builder == 'gutenberg':
            item['content'] = g()
        else:
            item['content'] = ''
            item['elementor'] = el()
        content.append(item)
    content.append({'key': 'journal', 'type': 'page', 'title': 'Journal', 'slug': 'journal', 'content': ''})
    for p in POSTS:
        content.append({'key': p['key'], 'type': 'post', 'title': p['title'], 'slug': p['slug'], 'featured': p['image'], 'date': p['date'],
                        'terms': {'category': [p['cat']]}, 'excerpt': p['excerpt'], 'content': post_html(p['body'])})
    products = []
    for i, pr in enumerate(PRODUCTS):
        pr = dict(pr)
        pr['slug'] = pr['name'].lower().replace(' ', '-').replace('&', 'and')
        pr['menu_order'] = i
        products.append(pr)
    menus = [{'name': 'Maison menu', 'location': 'primary', 'items': [
        {'title': 'Shop', 'woo': 'shop'}, {'title': 'Lookbook', 'page': 'lookbook'}, {'title': 'Atelier', 'page': 'atelier'},
        {'title': 'Journal', 'page': 'journal'}, {'title': 'Stores', 'page': 'contact'}]}]
    settings = {'front_page': 'home', 'blog_page': 'journal', 'style': 'maison', 'motion': 'editorial',
                'parts': {'header': {'content': g_header()}, 'header-shop': {'content': g_header()}, 'header-transparent': {'content': g_header()}, 'footer': {'content': g_footer()}},
                'options': {'posts_per_page': 9, 'blogname': 'Maison', 'blogdescription': 'Clothes made slowly, to be worn for years.',
                            'wpessential_shop_title': 'All pieces', 'wpessential_shop_lead': 'Linen, wool, silk and leather — made in small runs.', 'wpessential_cart_title': 'Your bag'},
                'woocommerce': {'free_shipping': 150, 'options': {'woocommerce_currency': 'EUR', 'woocommerce_currency_pos': 'left',
                                                                  'woocommerce_price_thousand_sep': '.', 'woocommerce_price_decimal_sep': ',',
                                                                  'woocommerce_price_num_decimals': '0', 'woocommerce_coming_soon': 'no',
                                                                  'woocommerce_store_pages_only': 'no',
                                                                  'woocommerce_cod_settings': {'enabled': 'yes', 'title': 'Pay in store'}}}}
    if builder == 'elementor':
        settings['elementor_kit'] = e.kit(container_width=1200, button_radius=0, field_radius=0, button_padding=(16, 28))
    return {'version': 1, 'media': media, 'terms': terms, 'forms': [forms.maison_care(), forms.maison_newsletter()], 'content': content, 'products': products, 'menus': menus, 'settings': settings}


DEMO = {
    'id': ID, 'title': 'Maison', 'tagline': 'An editorial fashion store with a lookbook, atelier story and journal.',
    'niche': ['fashion', 'shop', 'clothing', 'boutique'], 'type': 'pro',
    'thumbnail': 'demo:thumbnail.webp',
    'preview': {'desktop': 'demo:preview/desktop.webp', 'tablet': 'demo:preview/tablet.webp', 'mobile': 'demo:preview/mobile.webp'},
    'pages': ['Home', 'Shop', 'Lookbook', 'Atelier', 'Journal', 'Stores & Contact', 'Shipping & Returns'],
    'highlights': ['12 products', 'Variable sizes & colours', 'Lookbook', 'Light & dark'],
    'motion': 'editorial',
    'plugins': [{'slug': 'woocommerce', 'name': 'WooCommerce', 'source': 'wporg', 'state': 'required'}, forms.PLUGIN],
}

# Unique meta description per page (playbook §1.13), stored as _wpe_seo_description.
SEO = {
    'home': 'Maison is an editorial fashion house: seasonal collections, a lookbook and atelier tailoring, shipped worldwide with free returns.',
    'lookbook': 'The Maison lookbook: the season\'s collection styled in full, with every piece linked to the shop.',
    'atelier': 'Inside the Maison atelier: how our tailors cut, fit and finish every garment, and how to book a fitting.',
    'contact': 'Visit a Maison store or contact our client care team for sizing, orders and atelier appointments.',
    'help': 'Maison shipping and returns: delivery times, costs, free 30-day returns and how to send an order back.',
    'journal': 'The Maison journal: styling notes, atelier stories and the ideas behind each collection.',
}

# Brand, contact email and what the site sells, used for the legal pages (tools/demo-builder/legal.py).
LEGAL = ('Maison', 'care@maison-atelier.com', 'our clothing and accessories')
