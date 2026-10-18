"""Maison — fashion store demo (Pro). Research and direction: docs/demos/maison.md."""
import blocks as b
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
        b.group(b.p('Free shipping over €150 · Returns within 30 days'),
                b.p('<a href="{{page:help}}">Shipping &amp; returns</a> · <a href="{{page:contact}}">Stores</a>', className='wpe-topbar__links'),
                className='wpe-topbar__inner', layout='flex', wrap='nowrap', justify='space-between'),
        className='wpe-topbar', content='1200px')
    return topbar + '\n\n<!-- wp:pattern {"slug":"wpessential/header-centered"} /-->'


def g_footer():
    return b.footer('Linen, wool and cashmere made slowly in Porto and Florence. Repairs for life.',
                    [('Shop', [('All products', '{{woo:shop}}'), ('Dresses', '{{term_link:pc-dresses}}'), ('Knitwear', '{{term_link:pc-knitwear}}'), ('Outerwear', '{{term_link:pc-outerwear}}')]),
                     ('Maison', [('Atelier', '{{page:atelier}}'), ('Lookbook', '{{page:lookbook}}'), ('Journal', '{{page:journal}}'), ('Stores', '{{page:contact}}')]),
                     ('Help', [('Shipping & returns', '{{page:help}}'), ('My account', '{{woo:myaccount}}'), ('Cart', '{{woo:cart}}'), ('Contact', '{{page:contact}}')])],
                    ('© Maison Atelier. All rights reserved.', 'Designed &amp; built with <a href="https://wpessential.org/">Hi WPEssential</a>'),
                    band=('Letters from the atelier', 'New collections and repair days, once a month.', 'Join the list', '{{page:contact}}'))


def g_home():
    hero = b.cover('hero-campaign',
                   b.p('Autumn / Winter 2026', style='wpe-overline', color='on-ink'),
                   b.h('The quiet season', level=1, size='display', color='on-ink'),
                   b.p('Linen, wool and cashmere cut to be worn for years. Made in small runs in Porto and Florence.', color='on-ink', size='body-l'),
                   b.buttons(b.button('Shop the collection', '{{woo:shop}}', style='wpe-light'),
                             b.button('View the lookbook', '{{page:lookbook}}', style='wpe-ghost')),
                   dim=45, min_height=760, position='bottom left', className='wpe-hero-cover')
    cats = b.section(
        b.group(b.group(b.p('Shop by category', style='wpe-overline'), b.h('Pieces for every day', size='h2'), layout='default'),
                b.buttons(b.button('All products', '{{woo:shop}}', style='wpe-outline')),
                layout='flex', wrap='wrap', justify='space-between', valign='bottom'),
        b.group(*[b.group(b.image(img, label, aspect='3/4', href='{{term_link:%s}}' % term),
                          b.h('<a href="{{term_link:%s}}">%s</a>' % (term, label), level=3, size='h4'), layout='default')
                  for term, img, label in CATEGORIES], layout='grid', grid_min='14rem'))
    arrivals = b.section(b.p('Just in', style='wpe-overline'), b.h('New arrivals', size='h2'),
                         b.shortcode('[products limit="4" columns="4" orderby="date" order="DESC"]'), className='wpe-section wpe-section--sm')
    atelier = b.section(b.columns(
        b.column(b.image('atelier-hands', 'Tailor hand-stitching ivory linen', aspect='4/5'), width='50%'),
        b.column(b.p('The atelier', style='wpe-overline'), b.h('Made slowly, by hand', size='h2'),
                 b.p('Every Maison piece passes through the hands of one maker, from first cut to final press. We work in small runs, so nothing is made that will not be worn.', style='wpe-lead'),
                 b.ul(['Natural fibres only: linen, wool, silk and cashmere', 'Family ateliers in Porto and Florence', 'Numbered and signed inside the collar'], style='wpe-checklist'),
                 b.buttons(b.button('Visit the atelier', '{{page:atelier}}')), valign='center'),
        valign='center', className='wpe-gap-lg'), bg='surface-alt')
    best = b.section(b.p('Most loved', style='wpe-overline'), b.h('Best sellers', size='h2'),
                     b.shortcode('[products limit="4" columns="4" visibility="featured"]'), className='wpe-section wpe-section--sm')
    band = b.cover('lookbook-3', b.h('Fewer, better pieces.', size='display', color='on-ink', align='center'),
                   b.p('The Autumn / Winter lookbook: twelve looks, one palette.', color='on-ink', align='center', size='body-l'),
                   b.buttons(b.button('Open the lookbook', '{{page:lookbook}}', style='wpe-light'), justify='center'),
                   dim=40, min_height=620, position='center center')
    promises = b.section(b.group(*[b.group(b.h(t, level=3, size='h4'), b.p(d, color='text-muted'), layout='default') for t, d in PROMISES],
                                 layout='grid', grid_min='14rem'), className='wpe-section wpe-section--sm')
    journal = b.section(b.group(b.group(b.p('Journal', style='wpe-overline'), b.h('Notes from the studio', size='h2'), layout='default'),
                                b.buttons(b.button('Read the journal', '{{page:journal}}', style='wpe-outline')),
                                layout='flex', wrap='wrap', justify='space-between', valign='bottom'),
                        b.latest_posts(3), bg='surface-alt')
    letters = b.section(b.h('Letters from the atelier', size='h2', align='center'),
                        b.p('New collections, studio visits and repair days, once a month. No spam, ever.', align='center', color='text-muted'),
                        b.buttons(b.button('Join the list', '{{page:contact}}'), justify='center'), content='720px')
    return b.join(hero, cats, arrivals, atelier, best, band, promises, journal)


def g_lookbook():
    intro = b.section(b.p('Autumn / Winter 2026', style='wpe-overline', align='center'),
                      b.h('Lookbook', level=1, size='display', align='center'),
                      b.p('Twelve looks shot between Provence and Lisbon in late summer light. Every piece is available now.', align='center', style='wpe-lead'),
                      content='760px')
    looks = []
    for n, (img, title, text, pids) in enumerate([
        ('lookbook-1', 'Look 01 — Camel and cream', 'The Belted Wool Coat over the Cashmere Crew Knit. Undyed, unfussy, warm.', ['p-wool-coat', 'p-cashmere', 'p-trousers']),
        ('lookbook-2', 'Look 02 — Espresso silk', 'Sand-washed silk with ivory wide-leg trousers. Dinner, then a late walk home.', ['p-silk-shirt', 'p-trousers', 'p-loafers']),
        ('lookbook-3', 'Look 03 — Terracotta linen', 'Madder-dyed ramie that moves with the wind. Wear it barefoot.', ['p-maxi', 'p-linen-dress', 'p-tote']),
    ]):
        img_col = b.column(b.image(img, title, aspect='4/5'), width='58%')
        txt_col = b.column(b.p('%02d' % (n + 1), style='wpe-overline'), b.h(title, size='h2'), b.p(text, style='wpe-lead'),
                           b.shortcode('[products ids="%s" columns="3" limit="3"]' % ','.join('{{post_id:%s}}' % k for k in pids)), valign='center')
        cols = (img_col, txt_col) if n % 2 == 0 else (txt_col, img_col)
        looks.append(b.section(b.columns(*cols, valign='center', className='wpe-gap-lg'), className='wpe-section wpe-section--sm'))
    end = b.section(b.h('Shop every look', size='h2', align='center'),
                    b.buttons(b.button('Go to the shop', '{{woo:shop}}'), justify='center'), bg='surface-alt', content='720px')
    return b.join(intro, *looks, end)


def g_atelier():
    hero = b.cover('atelier-studio', b.p('Since 1998', style='wpe-overline', color='on-ink'),
                   b.h('The atelier', level=1, size='display', color='on-ink'),
                   b.p('A small studio with a long view: clothes made to be repaired, handed down and worn again.', color='on-ink', size='body-l'),
                   dim=40, min_height=620, position='bottom left')
    story = b.section(b.columns(
        b.column(b.p('Our story', style='wpe-overline'), b.h('Three generations of tailors', size='h2'), width='40%'),
        b.column(b.p('Maison began as a two-person alteration shop in the Marais. Today forty-two makers work with us across two family ateliers, but the rule is the same: one garment, one pair of hands.', style='wpe-lead'),
                 b.p('We design two collections a year and keep the best pieces in production for as long as people want them. Fabrics come from mills we have visited, within a day\'s drive of the workshop.'))),
        className='wpe-section')
    numbers = b.section(b.group(*[b.group(b.p(n, className='wpe-stat-number'), b.p(t, color='text-muted'), layout='default')
                                  for n, t in [('1998', 'Founded in Paris'), ('42', 'Makers in Porto and Florence'), ('100%', 'Natural fibres'), ('1,900', 'Repairs last year')]],
                                layout='grid', grid_min='12rem'), bg='surface-alt', className='wpe-section wpe-section--sm')
    process = b.section(b.columns(
        b.column(b.image('atelier-hands', 'Hand finishing a linen garment', aspect='4/5')),
        b.column(b.p('How we work', style='wpe-overline'), b.h('Slow by design', size='h2'),
                 b.ul(['Fabric rests for 48 hours before cutting so it never shrinks later.', 'Seams are French-finished or bound — no raw edges inside.', 'Buttons are sewn with a thread shank so they last.', 'Every piece is pressed and checked by the person who made it.'], style='wpe-checklist'),
                 valign='center'), valign='center', className='wpe-gap-lg'))
    stores = b.section(b.columns(
        b.column(b.p('Visit us', style='wpe-overline'), b.h('Try it on in Paris or Lisbon', size='h2'),
                 b.p('Book a fitting and we will have your sizes ready, with a tailor on hand for small alterations.'),
                 b.buttons(b.button('Find a store', '{{page:contact}}'))),
        b.column(b.image('store-paris', 'The Maison store in Paris', aspect='3/2')), valign='center', className='wpe-gap-lg'), bg='surface-alt')
    return b.join(hero, story, numbers, process, stores)


def g_contact():
    head = b.section(b.p('Stores &amp; contact', style='wpe-overline'), b.h('Come and see us', level=1, size='h1'),
                     b.p('Questions about sizing, an order or a repair? We answer every email within one working day.', style='wpe-lead'),
                     className='wpe-section wpe-section--sm', content='760px')
    stores = b.section(b.columns(*[b.column(b.h(city, level=3, size='h3'), b.p(addr), b.p(hours, color='text-muted'),
                                            b.p('<a href="tel:%s">%s</a>' % (tel.replace(' ', ''), tel)), style='wpe-card-flat')
                                   for city, addr, hours, tel in STORES]), className='wpe-section wpe-section--sm')
    care = b.section(b.columns(
        b.column(b.image('store-paris', 'Inside the Paris store', aspect='4/3')),
        b.column(b.h('Customer care', size='h2'),
                 b.ul(['Email: <a href="mailto:care@maison-atelier.com">care@maison-atelier.com</a>', 'Phone: +33 1 42 71 18 40 (Mon–Fri, 9:00–18:00 CET)', 'Press &amp; wholesale: <a href="mailto:studio@maison-atelier.com">studio@maison-atelier.com</a>']),
                 b.buttons(b.button('Shipping &amp; returns', '{{page:help}}', style='wpe-outline')), valign='center'), valign='center', className='wpe-gap-lg'), bg='surface-alt')
    return b.join(head, stores, care)


def g_help():
    head = b.section(b.p('Help', style='wpe-overline'), b.h('Shipping &amp; returns', level=1, size='h1'),
                     b.p('Everything about delivery, returns, sizing and repairs.', style='wpe-lead'), className='wpe-section wpe-section--sm', content='760px')
    faq = b.section(*[b.details(q, b.p(a)) for q, a in FAQ], className='wpe-section wpe-section--sm', content='760px')
    more = b.section(b.h('Still need help?', size='h3', align='center'),
                     b.buttons(b.button('Contact customer care', '{{page:contact}}'), justify='center'), bg='surface-alt', content='760px',
                     className='wpe-section wpe-section--sm')
    return b.join(head, faq, more)


# Elementor ------------------------------------------------------------------------------

def e_home():
    hero = e.container(e.overline('Autumn / Winter 2026', color_slug='on-ink'),
                       e.heading('The quiet season', 1, 'on-ink', size_px=96),
                       e.text('Linen, wool and cashmere cut to be worn for years. Made in small runs in Porto and Florence.', 'on-ink', size_px=18),
                       e.row(e.button('Shop the collection', '{{woo:shop}}', 'light'), e.button('View the lookbook', '{{page:lookbook}}', 'outline'), gap=12, wrap=True, mobile_direction='row'),
                       image='hero-campaign', overlay='ink', overlay_opacity=0.3, min_height=760, justify='flex-end', pad=(96, 24, 96, 24), gap=20)
    cats = e.container(e.overline('Shop by category'), e.heading('Pieces for every day'),
                       e.grid(*[e.image_box(img, label, '', link='{{term_link:%s}}' % term, height=420) for term, img, label in CATEGORIES], columns=4),
                       pad=(96, 24, 48, 24))
    arrivals = e.container(e.overline('Just in'), e.heading('New arrivals'), e.shortcode('[products limit="4" columns="4" orderby="date" order="DESC"]'), pad=(48, 24, 48, 24))
    atelier = e.container(
        e.row(e.col(e.image_fixed('atelier-hands', 'Tailor hand-stitching ivory linen', 620), width=50),
              e.col(e.overline('The atelier'), e.heading('Made slowly, by hand'),
                    e.text('Every Maison piece passes through the hands of one maker, from first cut to final press. We work in small runs, so nothing is made that will not be worn.', size_px=18),
                    e.icon_list(['Natural fibres only: linen, wool, silk and cashmere', 'Family ateliers in Porto and Florence', 'Numbered and signed inside the collar']),
                    e.button('Visit the atelier', '{{page:atelier}}'), width=50, justify='center'), gap=64),
        bg='surface-alt', pad=(96, 24, 96, 24))
    best = e.container(e.overline('Most loved'), e.heading('Best sellers'), e.shortcode('[products limit="4" columns="4" visibility="featured"]'), pad=(48, 24, 48, 24))
    band = e.container(e.heading('Fewer, better pieces.', 2, 'on-ink', align='center', size_px=80),
                       e.text('The Autumn / Winter lookbook: twelve looks, one palette.', 'on-ink', align='center', size_px=18),
                       e.button('Open the lookbook', '{{page:lookbook}}', 'light', align='center'),
                       image='lookbook-3', overlay='ink', overlay_opacity=0.4, min_height=620, justify='center', align='center', pad=(96, 24, 96, 24))
    promises = e.container(e.grid(*[e.icon_box(icon, t, d) for (t, d), icon in zip(PROMISES, ['fas fa-truck', 'fas fa-rotate-left', 'fas fa-location-dot', 'fas fa-scissors'])], columns=4),
                           pad=(72, 24, 72, 24))
    journal = e.container(e.overline('Journal'), e.heading('Notes from the studio'),
                          e.grid(*[e.image_box(p['image'], p['title'], p['excerpt'], link='{{page:%s}}' % p['key'], height=280) for p in POSTS], columns=3),
                          bg='surface-alt', pad=(96, 24, 96, 24))
    letters = e.container(e.heading('Letters from the atelier', align='center'),
                          e.text('New collections, studio visits and repair days, once a month. No spam, ever.', align='center'),
                          e.button('Join the list', '{{page:contact}}', align='center'), pad=(96, 24, 96, 24), align='center')
    return [hero, cats, arrivals, atelier, best, band, promises, journal]


def e_lookbook():
    out = [e.container(e.overline('Autumn / Winter 2026', align='center'), e.heading('Lookbook', 1, align='center', size_px=96),
                       e.text('Twelve looks shot between Provence and Lisbon in late summer light. Every piece is available now.', align='center', size_px=18),
                       pad=(96, 24, 48, 24), align='center')]
    for n, (img, title, text, pids) in enumerate([
        ('lookbook-1', 'Look 01 — Camel and cream', 'The Belted Wool Coat over the Cashmere Crew Knit. Undyed, unfussy, warm.', ['p-wool-coat', 'p-cashmere', 'p-trousers']),
        ('lookbook-2', 'Look 02 — Espresso silk', 'Sand-washed silk with ivory wide-leg trousers. Dinner, then a late walk home.', ['p-silk-shirt', 'p-trousers', 'p-loafers']),
        ('lookbook-3', 'Look 03 — Terracotta linen', 'Madder-dyed ramie that moves with the wind. Wear it barefoot.', ['p-maxi', 'p-linen-dress', 'p-tote'])]):
        img_col = e.col(e.image_fixed(img, title, 720), width=58)
        txt_col = e.col(e.overline('%02d' % (n + 1)), e.heading(title), e.text(text, size_px=18),
                        e.shortcode('[products ids="%s" columns="3" limit="3"]' % ','.join('{{post_id:%s}}' % k for k in pids)), width=42, justify='center')
        out.append(e.container(e.row(*((img_col, txt_col) if n % 2 == 0 else (txt_col, img_col)), gap=56), pad=(48, 24, 48, 24)))
    out.append(e.container(e.heading('Shop every look', align='center'), e.button('Go to the shop', '{{woo:shop}}', align='center'), bg='surface-alt', pad=(80, 24, 80, 24)))
    return out


def e_atelier():
    return [
        e.container(e.overline('Since 1998', color_slug='on-ink'), e.heading('The atelier', 1, 'on-ink', size_px=96),
                    e.text('A small studio with a long view: clothes made to be repaired, handed down and worn again.', 'on-ink', size_px=18),
                    image='atelier-studio', overlay='ink', overlay_opacity=0.4, min_height=620, justify='flex-end', pad=(96, 24, 96, 24)),
        e.container(e.row(e.col(e.overline('Our story'), e.heading('Three generations of tailors'), width=40),
                          e.col(e.text('Maison began as a two-person alteration shop in the Marais. Today forty-two makers work with us across two family ateliers, but the rule is the same: one garment, one pair of hands.', 'text', size_px=20),
                                e.text("We design two collections a year and keep the best pieces in production for as long as people want them. Fabrics come from mills we have visited, within a day's drive of the workshop."), width=60), gap=64),
                    pad=(96, 24, 96, 24)),
        e.container(e.grid(e.counter(1998, 'Founded in Paris'), e.counter(42, 'Makers in Porto and Florence'), e.counter(100, 'Natural fibres', '%'),
                           e.counter(1900, 'Repairs last year'), columns=4), bg='surface-alt', pad=(72, 24, 72, 24)),
        e.container(e.row(e.col(e.image_fixed('atelier-hands', 'Hand finishing a linen garment', 620), width=50),
                          e.col(e.overline('How we work'), e.heading('Slow by design'),
                                e.icon_list(['Fabric rests for 48 hours before cutting so it never shrinks later.', 'Seams are French-finished or bound — no raw edges inside.',
                                             'Buttons are sewn with a thread shank so they last.', 'Every piece is pressed and checked by the person who made it.']),
                                width=50, justify='center'), gap=64), pad=(96, 24, 96, 24)),
        e.container(e.row(e.col(e.overline('Visit us'), e.heading('Try it on in Paris or Lisbon'),
                                e.text('Book a fitting and we will have your sizes ready, with a tailor on hand for small alterations.'),
                                e.button('Find a store', '{{page:contact}}'), width=45, justify='center'),
                          e.col(e.image_fixed('store-paris', 'The Maison store in Paris', 480), width=55), gap=64), bg='surface-alt', pad=(96, 24, 96, 24)),
    ]


def e_contact():
    return [
        e.container(e.overline('Stores & contact'), e.heading('Come and see us', 1, size_px=64),
                    e.text('Questions about sizing, an order or a repair? We answer every email within one working day.', size_px=18), pad=(96, 24, 48, 24)),
        e.container(e.row(*[e.col(e.heading(city, 3), e.text('<p>%s</p><p>%s</p><p><a href="tel:%s">%s</a></p>' % (addr, hours, tel.replace(' ', ''), tel)),
                                  width=50, pad=32, border='border') for city, addr, hours, tel in STORES], gap=24), pad=(24, 24, 72, 24)),
        e.container(e.row(e.col(e.image_fixed('store-paris', 'Inside the Paris store', 460), width=50),
                          e.col(e.heading('Customer care'),
                                e.icon_list(['care@maison-atelier.com', '+33 1 42 71 18 40 (Mon–Fri, 9:00–18:00 CET)', 'Press & wholesale: studio@maison-atelier.com'], icon='fas fa-envelope'),
                                e.button('Shipping & returns', '{{page:help}}', 'outline'), width=50, justify='center'), gap=64),
                    bg='surface-alt', pad=(96, 24, 96, 24)),
        e.container(e.map_widget('14 Rue de Saintonge, 75003 Paris'), pad=0, boxed=False),
    ]


def e_help():
    return [
        e.container(e.overline('Help'), e.heading('Shipping & returns', 1, size_px=64), e.text('Everything about delivery, returns, sizing and repairs.', size_px=18),
                    pad=(96, 24, 32, 24)),
        e.container(e.accordion(FAQ), pad=(24, 24, 72, 24)),
        e.container(e.heading('Still need help?', 3, align='center'), e.button('Contact customer care', '{{page:contact}}', align='center'), bg='surface-alt', pad=(64, 24, 64, 24)),
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
                'options': {'posts_per_page': 9, 'blogname': 'Maison', 'blogdescription': 'Clothes made slowly, to be worn for years.'},
                'woocommerce': {'free_shipping': 150, 'options': {'woocommerce_currency': 'EUR', 'woocommerce_currency_pos': 'left',
                                                                  'woocommerce_price_thousand_sep': '.', 'woocommerce_price_decimal_sep': ',',
                                                                  'woocommerce_price_num_decimals': '0', 'woocommerce_coming_soon': 'no',
                                                                  'woocommerce_store_pages_only': 'no',
                                                                  'woocommerce_cod_settings': {'enabled': 'yes', 'title': 'Pay in store'}}}}
    if builder == 'elementor':
        settings['elementor_kit'] = e.kit(container_width=1200, button_radius=0, field_radius=0, button_padding=(16, 28))
    return {'version': 1, 'media': media, 'terms': terms, 'content': content, 'products': products, 'menus': menus, 'settings': settings}


DEMO = {
    'id': ID, 'title': 'Maison', 'tagline': 'An editorial fashion store with a lookbook, atelier story and journal.',
    'niche': ['fashion', 'shop', 'clothing', 'boutique'], 'type': 'pro',
    'thumbnail': 'demo:thumbnail.webp',
    'preview': {'desktop': 'demo:preview/desktop.webp', 'tablet': 'demo:preview/tablet.webp', 'mobile': 'demo:preview/mobile.webp'},
    'pages': ['Home', 'Shop', 'Lookbook', 'Atelier', 'Journal', 'Stores & Contact', 'Shipping & Returns'],
    'highlights': ['12 products', 'Variable sizes & colours', 'Lookbook', 'Light & dark'],
    'motion': 'editorial',
    'plugins': [{'slug': 'woocommerce', 'name': 'WooCommerce', 'source': 'wporg', 'state': 'required'}],
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
