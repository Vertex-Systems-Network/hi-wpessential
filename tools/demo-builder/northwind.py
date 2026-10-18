"""Base demo: Northwind Advisory, a business consulting firm.

Built to the Figma "Northwind Advisory" frames on the Base Demo page; research in docs/demos/base-research.md.
Images ship with the theme (assets/images), so the package keeps the existing Base media keys.
Gutenberg sections use the nw-* classes in assets/css/demos.css; Elementor uses native widgets.
"""
import blocks as b
import elementor as e
from ess_ui import html

PHONE, EMAIL = '+1 (555) 014-2290', 'hello@northwind.co'

SERVICES = [
    ('strategy', '◎', 'fas fa-chess', 'Business strategy', 'Market, positioning and a 3-year plan your team can actually run.', 'From $4,900',
     ['Market and competitor review', 'Positioning and offer design', '3-year plan with owners and budget', 'Quarterly strategy reviews'], ('+31%', 'revenue growth in year one')),
    ('growth', '↗', 'fas fa-arrow-trend-up', 'Growth & marketing', 'Acquisition channels, pricing and funnels that pay back.', 'From $3,500 / mo',
     ['Channel and funnel audit', 'Pricing and packaging review', '90-day growth experiments plan', 'Weekly working session', 'Monthly board-ready report'], ('+48%', 'qualified pipeline')),
    ('operations', '⚙', 'fas fa-gears', 'Operations', 'Processes, KPIs and tooling that remove bottlenecks.', 'From $5,500',
     ['Process mapping', 'KPI tree and dashboard', 'Tooling and automation plan', 'Team rituals that stick'], ('−34%', 'lead time')),
    ('finance', '$', 'fas fa-sack-dollar', 'Finance & fundraising', 'Models, investor decks and due-diligence support.', 'From $6,000',
     ['Three-statement financial model', 'Investor deck and story', 'Investor list and outreach', 'Due-diligence data room'], ('$1.2B', 'raised for clients')),
    ('digital', '☰', 'fas fa-microchip', 'Digital transformation', 'CRM, automation and data you can trust.', 'From $8,000',
     ['CRM selection and rollout', 'Workflow automation', 'Reporting and data model', 'Team training'], ('−60%', 'manual work')),
    ('coaching', '♟', 'fas fa-user-tie', 'Leadership coaching', 'Coaching for founders and new management teams.', 'From $1,200 / mo',
     ['Fortnightly 1:1 coaching', '360° feedback', 'Leadership team offsite', 'Hiring and org design support'], ('92%', 'say they lead better')),
]
CASES = [('post-3', 'Retail · Growth', 'Fieldhouse Goods doubled online revenue in 9 months', '+118% revenue'),
         ('hero', 'SaaS · Finance', 'Orbitly closed a $14M Series A', '$14M raised'),
         ('post-1', 'Manufacturing · Operations', 'Meridian cut delivery times by a third', '−34% lead time'),
         ('post-2', 'Services · Strategy', 'Lumen Studio entered two new markets', '+2 markets'),
         ('post-3', 'Health · Digital', 'A clinic group moved 40k patients to online booking', '−60% no-shows'),
         ('hero', 'Logistics · Operations', 'Kitecraft saves $1.8M a year in fleet costs', '$1.8M saved')]
PEOPLE = [('team-1', 'Ayesha Malik', 'Managing Partner', 'Former CFO, 20 years in retail and consumer brands.'),
          ('team-2', 'Bilal Khan', 'Partner, Finance', 'Led 30+ fundraises worth $900M for SaaS and fintech.'),
          ('team-3', 'Sara Ahmed', 'Partner, Operations', 'Ex-COO; supply chain and service delivery.'),
          ('team-4', 'Usman Tariq', 'Partner, Growth', 'Built growth teams at three high-growth startups.')]
QUOTES = [('team-1', 'Northwind found the margin problem our board had missed for two years. Profit was up 22% the next quarter.', 'Ayesha Malik', 'CEO, Fieldhouse Goods'),
          ('team-2', 'Their fundraising model and deck got us to term sheets in six weeks.', 'Bilal Khan', 'Founder, Orbitly'),
          ('team-3', 'Practical, honest and always available. It felt like hiring a COO.', 'Sara Ahmed', 'COO, Lumen Studio')]
STEPS = [('Discovery', 'A free 45-minute call to understand goals and constraints.'), ('Diagnosis', 'Two weeks of interviews and data to find the real problem.'),
         ('Plan', 'A clear roadmap with owners, budget and KPIs.'), ('Execution', 'We work alongside your team until targets are hit.')]
PLANS = [('Strategy sprint', '$4,900', 'one-off · 2 weeks', 'For founders who need a clear plan fast.', ['Discovery workshop', 'Market & competitor review', '90-day action plan', '1 follow-up session'], False),
         ('Growth partner', '$7,500', 'per month', 'A senior consultant inside your team.', ['Dedicated senior consultant', 'Weekly working sessions', 'KPI dashboard & reporting', 'Board-meeting support', 'Cancel with 30 days notice'], True),
         ('Transformation', 'Custom', 'fixed quote', 'Multi-team programmes and turnarounds.', ['Multi-team programme', 'On-site workshops', 'Change management', 'Post-launch review'], False)]
FAQ = [('How quickly can you start?', 'Usually within two weeks of the first call.'),
       ('Do you work with small businesses?', 'Yes — most clients have between 10 and 500 people.'),
       ('How do you price projects?', 'Fixed fees agreed before we start. No hourly billing.'),
       ('Can you work remotely with our team?', 'Yes. We work on-site or remotely, whichever suits you.'),
       ('Is the first consultation free?', 'Yes, the 45-minute discovery call is free and without obligation.')]
LOGOS = ['Kitecraft', 'Fieldhouse', 'Lumen Studio', 'Orbitly', 'Mosaic &amp; Co', 'Meridian Bank']
JOBS = [('Senior Consultant, Growth', 'Chicago or remote', 'Full-time'), ('Financial Analyst', 'London', 'Full-time'),
        ('Operations Consultant', 'Remote (US)', 'Full-time'), ('Marketing Associate', 'Remote', 'Contract')]


# Gutenberg ---------------------------------------------------------------------------------

def sec(*inner, alt=False, dark=False, cls=None):
    return b.section(*inner, className=b.cls('wpe-section nw-sec', 'nw-sec--alt' if alt else None, 'nw-sec--dark' if dark else None, cls),
                     bg='ink' if dark else ('surface-alt' if alt else None))


def head(over, title, lead=None, center=False, action=None):
    al = 'center' if center else None
    left = b.group(b.p(over, style='wpe-overline', align=al), b.h(title, size='h2', align=al),
                   *([b.p(lead, color='text-muted', align=al, className='nw-lead')] if lead else []), layout='default', className='nw-head')
    if not action:
        return left
    return b.group(left, b.buttons(b.button(*action, style='wpe-outline')), layout='flex', wrap='wrap', justify='space-between', valign='bottom', className='nw-head-row')


def page_hero(crumb, title, lead):
    return b.section(b.p('<a href="{{home:}}">Home</a> / ' + crumb, className='nw-crumb'), b.h(title, level=1, size='h1'), b.p(lead, color='text-muted', size='body-l'),
                     className='wpe-section wpe-section--sm nw-page-hero', bg='surface-alt')


def svc_cards(link=True):
    return b.group(*[b.group(html('<span class="nw-ico" aria-hidden="true">%s</span>' % c), b.h(t, level=3, size='h4'), b.p(d, color='text-muted'),
                             b.p('<span>%s</span> <a href="{{page:svc-%s}}">Learn more →</a>' % (pr, k) if link else pr, className='nw-card__foot'),
                             layout='default', className='nw-card') for k, c, _i, t, d, pr, _inc, _o in SERVICES], layout='grid', columns=3, className='nw-grid')


def case_cards(items):
    return b.group(*[b.group(b.image(img, t, aspect='16/10'), b.group(b.p(tag, style='wpe-overline'), b.h('<a href="{{page:case-fieldhouse}}">%s</a>' % t, level=3, size='h4'),
                                                                      b.p(r, className='nw-result'), layout='default', className='nw-case__body'),
                             layout='default', className='nw-case') for img, tag, t, r in items], layout='grid', columns=3, className='nw-grid')


def quotes():
    return b.group(*[b.group(b.p('★★★★★', className='nw-stars'), b.p('“%s”' % q, size='body-l'),
                             b.group(b.image(img, n, className='nw-avatar'), b.group(b.p('<strong>%s</strong>' % n), b.p(r, color='text-muted', size='small'), layout='default'),
                                     layout='flex', wrap='nowrap', valign='center'), layout='default', className='nw-card') for img, q, n, r in QUOTES],
                   layout='grid', columns=3, className='nw-grid')


def people(items):
    return b.group(*[b.group(b.image(img, n, aspect='9/10', className='nw-person__img'), b.h(n, level=3, size='h4'), b.p(r, className='nw-role'),
                             *([b.p(bio, color='text-muted', size='small')] if bio else []), layout='default', className='nw-person')
                     for img, n, r, bio in items], layout='grid', columns=4, className='nw-grid')


def steps():
    return b.group(*[b.group(b.p('0%d' % (i + 1), className='nw-num'), b.h(t, level=3, size='h4'), b.p(d, color='text-muted'), layout='default', className='nw-card nw-card--alt')
                     for i, (t, d) in enumerate(STEPS)], layout='grid', columns=4, className='nw-grid')


def plans():
    out = []
    for n, pr, per, d, inc, feat in PLANS:
        out.append(b.group(*([b.p('Most popular', className='nw-badge')] if feat else []), b.h(n, level=3, size='h4'),
                           b.p('<strong>%s</strong> <small>%s</small>' % (pr, per), className='nw-price'), b.p(d, className='nw-muted'),
                           b.ul(inc, style='wpe-checklist'), b.buttons(b.button('Start now' if feat else 'Get in touch', '{{page:contact}}', style=None if feat else 'wpe-outline')),
                           layout='default', className=b.cls('nw-card nw-plan', 'is-featured' if feat else None)))
    return b.group(*out, layout='grid', columns=3, className='nw-grid')


def faq(items=FAQ):
    return b.group(*[b.details(q, b.p(a)) for q, a in items], layout='default', className='nw-faq')


def cta():
    return sec(b.group(b.group(b.h('Ready to grow with a senior team behind you?', size='h2', color='on-primary'),
                               b.p('Book a free 45-minute consultation. No obligation, no sales pitch.', color='on-primary'), layout='default'),
                       b.buttons(b.button('Book a consultation', '{{page:contact}}', style='wpe-light')),
                       layout='flex', wrap='wrap', justify='space-between', valign='center', className='nw-cta', bg='primary'))


def stats(items, dark=False):
    return b.group(*[b.group(b.p(n, className='nw-stat__n'), b.p(t, className='nw-stat__t'), layout='default', className='nw-stat') for n, t in items],
                   layout='grid', columns=len(items), className=b.cls('nw-grid nw-stats', 'is-dark' if dark else None))


def g_home():
    hero = sec(b.columns(
        b.column(b.p('★ 4.9 · rated by 300+ clients', className='nw-pill'), b.h('Strategy and growth advice for ambitious businesses', level=1, size='display'),
                 b.p('Northwind Advisory helps founders and leadership teams grow revenue, fix operations and raise capital — with a senior consultant on every project.',
                     color='text-muted', size='body-l'),
                 b.buttons(b.button('Book a free consultation', '{{page:contact}}'), b.button('See our work', '{{page:case-studies}}', style='wpe-outline')),
                 stats([('18 yrs', 'in business'), ('420+', 'projects delivered'), ('$1.2B', 'capital raised')]), width='52%', valign='center'),
        b.column(b.image('post-2', 'Consultant working with a client', className='nw-hero__img'), width='48%', valign='center'), className='wpe-gap-lg'),
        alt=True, cls='nw-hero')
    logos = b.section(b.p('Trusted by leadership teams at', color='text-muted', size='small'),
                      b.p(''.join('<span>%s</span>' % n for n in LOGOS), className='nw-logos'), className='wpe-section wpe-section--sm')
    about = sec(b.columns(b.column(b.image('post-1', 'Strategy session', className='nw-rounded'), width='45%', valign='center'),
                          b.column(head('About Northwind', 'Senior advisors, not slide decks',
                                        'Founded in 2008, we are a team of 40 former operators, CFOs and marketers. Every engagement is led by a partner and measured against numbers we agree up front.'),
                                   stats([('98%', 'client retention'), ('3.4×', 'average ROI'), ('40', 'senior consultants'), ('12', 'countries served')]),
                                   b.buttons(b.button('About us', '{{page:about}}', style='wpe-outline')), valign='center'), className='wpe-gap-lg'), alt=True)
    return b.join(hero, logos,
                  sec(head('What we do', 'Consulting services that move the numbers', action=('All services', '{{page:services}}')), svc_cards()),
                  about,
                  sec(head('How we work', 'From first call to measurable results', center=True), steps()),
                  sec(head('Case studies', 'Results our clients can measure', action=('All case studies', '{{page:case-studies}}')), case_cards(CASES[:3]), dark=True),
                  sec(head('Testimonials', 'What our clients say', center=True), quotes()),
                  sec(head('Our partners', 'Meet the people who lead your project', action=('Meet the team', '{{page:team}}')), people([p[:3] + ('',) for p in PEOPLE]), alt=True),
                  sec(head('Engagements', 'Simple ways to work with us', 'Fixed fees agreed before we start. No hourly surprises.', center=True), plans()),
                  sec(b.columns(b.column(head('FAQ', 'Questions before you book'), faq()),
                                b.column(head('Insights', 'Latest from our blog'), b.latest_posts(3)), className='wpe-gap-lg'), alt=True),
                  cta())


def g_about():
    story = sec(b.columns(b.column(head('Our story', 'Built by operators who have run the businesses we advise',
                                        'Northwind started in 2008 when three former CFOs and COOs decided advice should come from people who have hit payroll and missed targets themselves. Today 40 senior consultants work with founders and boards in 12 countries.'), valign='center'),
                          b.column(b.image('post-2', 'Our team at work', className='nw-rounded'), valign='center'), className='wpe-gap-lg'))
    values = [('◎', 'Numbers first', 'We agree the targets before we start and report against them every week.'), ('♟', 'Senior people only', 'A partner leads every engagement.'),
              ('⚖', 'Honest advice', 'If you do not need us, we will say so on the first call.'), ('↗', 'Built to last', 'We leave your team able to run the system without us.')]
    timeline = [('2008', 'Founded in Chicago by three former operators'), ('2013', 'Opened London office; first $100M fundraise'),
                ('2019', 'Launched the growth-partner retainer'), ('2024', '40 consultants across 12 countries')]
    return b.join(page_hero('About', 'We help ambitious businesses grow — profitably', 'Strategy, growth and operations consulting led by people who have run companies themselves.'),
                  story,
                  sec(stats([('18', 'years in business'), ('420+', 'projects delivered'), ('98%', 'client retention'), ('$1.2B', 'capital raised for clients')], dark=True), dark=True),
                  sec(head('What we believe', 'Our principles', center=True),
                      b.group(*[b.group(html('<span class="nw-ico">%s</span>' % c), b.h(t, level=3, size='h4'), b.p(d, color='text-muted'), layout='default', className='nw-card')
                                for c, t, d in values], layout='grid', columns=4, className='nw-grid'), alt=True),
                  sec(head('Milestones', 'How we got here'),
                      b.group(*[b.group(b.p(y, className='nw-year'), b.p(t, color='text-muted'), layout='default', className='nw-mile') for y, t in timeline],
                              layout='grid', columns=4, className='nw-grid')),
                  sec(head('Leadership', 'The partners'), people([p[:3] + ('',) for p in PEOPLE]), alt=True),
                  b.section(b.p(''.join('<span>🏆 %s</span>' % a for a in ['Forbes Top Consulting Firms 2025', 'Financial Times — Leading Consultants', 'Clutch Top B2B Company', 'Great Place to Work']),
                                className='nw-logos nw-awards'), className='wpe-section wpe-section--sm'),
                  cta())


def g_services():
    inds = ['Retail &amp; e-commerce', 'SaaS &amp; technology', 'Manufacturing', 'Professional services', 'Healthcare', 'Financial services', 'Logistics', 'Hospitality']
    return b.join(page_hero('Services', 'Consulting services that move the numbers', 'Six practices, one senior team. Pick a single project or a monthly partnership.'),
                  sec(svc_cards()),
                  sec(head('Industries', 'Sectors we know inside out'), b.p(''.join('<span>%s</span>' % i for i in inds), className='nw-chips'), alt=True),
                  sec(head('How we work', 'From first call to measurable results', center=True), steps()),
                  cta())


def g_service(i):
    key, _c, _i, name, desc, price, inc, (onum, otext) = SERVICES[i]
    hero = sec(b.columns(
        b.column(b.p('<a href="{{home:}}">Home</a> / <a href="{{page:services}}">Services</a> / ' + name, className='nw-crumb'),
                 b.h(name, level=1, size='h1', color='on-ink'), b.p(desc, color='on-ink-muted', size='body-l'),
                 stats([(price.replace('From ', ''), 'starting price'), ('6–12 weeks', 'to first results'), (onum, otext)], dark=True),
                 b.buttons(b.button('Book a consultation', '{{page:contact}}'), b.button('See pricing', '{{page:pricing}}', style='wpe-light')), valign='center'),
        b.column(b.image('post-3', name, className='nw-rounded'), valign='center'), className='wpe-gap-lg'), dark=True, cls='nw-hero')
    return b.join(hero,
                  sec(b.columns(b.column(head('Who it is for', 'Companies with a product that sells — but growth has stalled',
                                              'Usually $2M–$50M revenue and a leadership team ready to act on what we find.'), valign='center'),
                                b.column(b.group(b.h('What is included', level=3, size='h4'), b.ul(inc, style='wpe-checklist'), layout='default', className='nw-card nw-card--alt')),
                      className='wpe-gap-lg')),
                  sec(head('Outcomes', 'What clients typically see'), stats([(onum, otext), ('3.4×', 'average ROI'), ('98%', 'would hire us again')]), alt=True),
                  sec(b.columns(b.column(b.image('post-3', 'Fieldhouse Goods', className='nw-rounded'), valign='center'),
                                b.column(b.p('Case study · Retail', style='wpe-overline'), b.h('Fieldhouse Goods doubled online revenue in 9 months', size='h2'),
                                         b.p('+118% revenue · −27% CAC · 3 new channels', className='nw-result'),
                                         b.buttons(b.button('Read the case study', '{{page:case-fieldhouse}}', style='wpe-outline')), valign='center'), className='wpe-gap-lg')),
                  sec(b.columns(b.column(head('FAQ', 'Questions about ' + name.lower()), faq(FAQ[:4])),
                                b.column(b.group(b.h('Get a proposal', level=3, size='h3'), b.shortcode('[wpforms id="{{form:contact}}" title="false"]'), layout='default', className='nw-card')),
                                className='wpe-gap-lg'), alt=True))


def g_cases():
    return b.join(page_hero('Case studies', 'Results our clients can measure', 'Every engagement ends with numbers. Here are some of them.'),
                  sec(case_cards(CASES)), cta())


def g_case():
    meta = ''.join('<span><small>%s</small><strong>%s</strong></span>' % kv for kv in
                   [('Client', 'Fieldhouse Goods'), ('Industry', 'Retail &amp; e-commerce'), ('Service', 'Growth &amp; marketing'), ('Duration', '9 months')])
    body = [('The challenge', 'Fieldhouse had strong products and loyal customers, but online growth had stalled at 4% a year while ad costs kept rising.'),
            ('Our approach', 'We rebuilt pricing and bundles, moved 40% of spend from paid social to search and email, and set up a weekly growth meeting with clear owners.'),
            ('The result', 'Online revenue more than doubled in nine months and the team now runs the experiments without us.')]
    return b.join(b.section(b.p('<a href="{{home:}}">Home</a> / <a href="{{page:case-studies}}">Case studies</a> / Fieldhouse Goods', className='nw-crumb'),
                            b.p('Retail · Growth', style='wpe-overline'), b.h('How Fieldhouse Goods doubled online revenue in 9 months', level=1, size='h1'),
                            b.p(meta, className='nw-meta'), className='wpe-section wpe-section--sm nw-page-hero', bg='surface-alt'),
                  b.section(b.image('post-3', 'Fieldhouse Goods team', className='nw-rounded nw-wide'), className='wpe-section wpe-section--sm'),
                  sec(stats([('+118%', 'online revenue'), ('−27%', 'customer acquisition cost'), ('3', 'new profitable channels')], dark=True), dark=True),
                  b.section(*[x for h, t in body for x in (b.h(h, size='h3'), b.p(t, size='body-l', color='text-muted'))],
                            b.group(b.p('“%s”' % QUOTES[0][1], size='h3'), b.p('<strong>%s</strong> · %s' % QUOTES[0][2:]), layout='default', className='nw-card nw-card--alt nw-quote'),
                            className='wpe-section', content='860px'),
                  cta())


def g_team():
    staff = PEOPLE + [('team-2', 'Daniel Reyes', 'Senior Consultant', 'Pricing and revenue operations.'), ('team-1', 'Hina Aslam', 'Senior Consultant', 'Change management and coaching.'),
                      ('team-4', 'James Carter', 'Senior Consultant', 'Data, CRM and automation.'), ('team-3', 'Maryam Ali', 'Consultant', 'Financial modelling and due diligence.')]
    return b.join(page_hero('Team', 'Senior people on every project', 'Partners and consultants who have run finance, operations and growth at real companies.'),
                  sec(people(staff)),
                  sec(b.group(head('Careers', 'Want to work with us?', 'We hire senior operators who love solving hard business problems.'),
                              b.buttons(b.button('See open roles', '{{page:careers}}')), layout='flex', wrap='wrap', justify='space-between', valign='center'), alt=True),
                  cta())


def g_pricing():
    rows = [['Senior consultant', '✓', '✓', '✓'], ['Weekly sessions', '—', '✓', '✓'], ['KPI dashboard', '—', '✓', '✓'],
            ['On-site workshops', 'Optional', 'Optional', '✓'], ['Board support', '—', '✓', '✓']]
    return b.join(page_hero('Pricing', 'Fixed fees. Agreed before we start.', 'No hourly billing and no surprises. Pick a project or a monthly partnership.'),
                  sec(plans()),
                  sec(head('Compare', 'What each engagement includes'), b.table(['', 'Sprint', 'Partner', 'Transformation'], rows, className='nw-table'), alt=True),
                  sec(head('FAQ', 'Pricing questions'), faq([('Are there any extra costs?', 'Only travel for on-site work, agreed in advance.'),
                                                              ('Can we pause a retainer?', 'Yes, with 30 days notice.'),
                                                              ('Do you offer a guarantee?', 'If we miss the agreed targets, the next month is free.'),
                                                              ('How do you invoice?', 'Monthly in advance, or 50/50 for projects.')])),
                  cta())


def g_careers():
    perks = [('Senior team', 'Work only with experienced partners and clients.'), ('Remote-first', 'Offices in Chicago and London, work from anywhere.'),
             ('Learning budget', '$3,000 a year for courses and conferences.'), ('Real impact', 'See your work move a company’s numbers.')]
    return b.join(page_hero('Careers', 'Join a team of senior operators', 'We are growing in Chicago, London and remote.'),
                  sec(head('Why Northwind', 'Do the best work of your career'),
                      b.group(*[b.group(b.h(t, level=3, size='h4'), b.p(d, color='text-muted'), layout='default', className='nw-card nw-card--alt') for t, d in perks],
                              layout='grid', columns=4, className='nw-grid')),
                  sec(head('Open roles', '%d positions open' % len(JOBS)),
                      *[b.group(b.group(b.h(t, level=3, size='h4'), b.p('%s · %s' % (loc, ty), color='text-muted', size='small'), layout='default'),
                                b.buttons(b.button('Apply', '#apply', style='wpe-outline')), layout='flex', wrap='nowrap', justify='space-between', valign='center', className='nw-card nw-job')
                        for t, loc, ty in JOBS], alt=True),
                  sec(b.columns(b.column(html('<span id="apply"></span>'), head('Apply', 'Send us your application', 'Do not see your role? Send an open application.')),
                                b.column(b.group(b.shortcode('[wpforms id="{{form:careers}}" title="false"]'), layout='default', className='nw-card')), className='wpe-gap-lg')))


def g_contact():
    offices = [('Chicago (HQ)', '200 W Madison St, Suite 1800<br>Chicago, IL 60606<br>' + PHONE), ('London', '30 Finsbury Square<br>London EC2A 1AG<br>+44 20 7946 0321'),
               ('Hours', 'Mon–Fri 9:00–18:00<br>Reply within one working day'), ('Email', '<a href="mailto:%s">%s</a><br>careers@northwind.co' % (EMAIL, EMAIL))]
    return b.join(page_hero('Contact', 'Let’s talk about your business', 'A free 45-minute consultation with a partner. No obligation.'),
                  sec(b.columns(
                      b.column(b.group(b.h('Book a free consultation', level=2, size='h3'), b.p('Tell us about your business. A partner replies within one working day.', color='text-muted'),
                                       b.shortcode('[wpforms id="{{form:contact}}" title="false"]'), layout='default', className='nw-card'), width='62%'),
                      b.column(*[b.group(b.h(t, level=3, size='h4'), b.p(d, color='text-muted'), layout='default', className='nw-card nw-card--alt') for t, d in offices], width='38%'),
                      className='wpe-gap-lg')),
                  html('<div class="nw-map"><iframe title="Northwind Advisory on Google Maps" loading="lazy" src="https://maps.google.com/maps?q=200%20W%20Madison%20St%20Chicago&amp;z=14&amp;output=embed"></iframe></div>'),
                  sec(head('FAQ', 'Before you book'), faq([FAQ[4], FAQ[0], ('Do you sign NDAs?', 'Yes, before any detailed discussion.'),
                                                          ('Do you work outside the US and UK?', 'Yes — we have clients in 12 countries.')]), alt=True))


# Elementor -----------------------------------------------------------------------------------

def e_hero(title, lead, crumb):
    return e.container(e.text('Home / ' + crumb, size_px=13), e.heading(title, 1, size_px=56), e.text(lead, size_px=18), bg='surface-alt', pad=(72, 24, 56, 24), gap=14)


def e_head(over, title, lead=None, center=False):
    al = 'center' if center else None
    return [e.overline(over, align=al), e.heading(title, align=al)] + ([e.text(lead, align=al)] if lead else [])


def e_svc():
    return e.grid(*[e.icon_box(i, t, d + '<br><strong>%s</strong> · <a href="{{page:svc-%s}}">Learn more →</a>' % (pr, k)) for k, _c, i, t, d, pr, _inc, _o in SERVICES], columns=3)


def e_case_cards(items):
    return e.grid(*[e.image_box(img, t, tag + '<br><strong>' + r + '</strong>', link='{{page:case-fieldhouse}}', height=220) for img, tag, t, r in items], columns=3)


def e_people(items):
    return e.grid(*[e.image_box(img, n, r + ('<br>' + bio if bio else ''), height=300, people=True) for img, n, r, bio in items], columns=4)


def e_stats(items, color='text'):
    return e.grid(*[e.col(e.heading(n, 3, color, size_px=40), e.text(t, size_px=14), gap=4) for n, t in items], columns=len(items))


def e_steps():
    return e.grid(*[e.col(e.heading('0%d' % (i + 1), 3, 'primary', size_px=36), e.heading(t, 3, size_px=20), e.text(d), gap=8, pad=24, radius=16, bg='surface-alt')
                    for i, (t, d) in enumerate(STEPS)], columns=4)


def e_plans():
    return e.grid(*[e.col(e.heading(n, 3, 'on-ink' if f else 'text', size_px=20), e.heading(pr, 2, 'on-ink' if f else 'text', size_px=44), e.text(per + ' · ' + d, 'on-ink-muted' if f else 'text-muted'),
                          e.icon_list(inc, color_slug='on-ink' if f else 'text'), e.button('Start now' if f else 'Get in touch', '{{page:contact}}', 'primary' if f else 'outline'),
                          gap=14, pad=32, radius=16, border=None if f else 'border', bg='ink' if f else 'surface') for n, pr, per, d, inc, f in PLANS], columns=3)


def e_quotes():
    return e.grid(*[e.testimonial(q, n, r, img) for img, q, n, r in QUOTES], columns=3)


def e_cta():
    return e.container(e.container(e.heading('Ready to grow with a senior team behind you?', 2, 'on-primary'),
                                   e.text('Book a free 45-minute consultation. No obligation, no sales pitch.', 'on-primary'),
                                   e.button('Book a consultation', '{{page:contact}}', 'light'), inner=True, bg='primary', radius=24, pad=56), pad=(48, 24, 80, 24))


def e_home():
    return [
        e.container(e.row(e.col(e.text('★ 4.9 · rated by 300+ clients', size_px=13), e.heading('Strategy and growth advice for ambitious businesses', 1, size_px=60),
                                e.text('Northwind Advisory helps founders and leadership teams grow revenue, fix operations and raise capital — with a senior consultant on every project.', size_px=18),
                                e.row(e.button('Book a free consultation', '{{page:contact}}'), e.button('See our work', '{{page:case-studies}}', 'outline'), gap=12, wrap=True, mobile_direction='row'),
                                e_stats([('18 yrs', 'in business'), ('420+', 'projects delivered'), ('$1.2B', 'capital raised')]), width=52, justify='center'),
                          e.col(e.image_fixed('post-2', 'Consultant working with a client', 520, radius=24), width=48), gap=56), bg='surface-alt', pad=(88, 24, 88, 24)),
        e.container(e.text('Trusted by leadership teams at', size_px=13), e.heading(' · '.join(LOGOS).replace('&amp;', '&'), 3, 'text-muted', size_px=22), pad=(40, 24, 40, 24)),
        e.container(*e_head('What we do', 'Consulting services that move the numbers'), e_svc(), e.button('All services', '{{page:services}}', 'outline'), pad=(96, 24, 96, 24)),
        e.container(e.row(e.col(e.image_fixed('post-1', 'Strategy session', 460, radius=24), width=45),
                          e.col(*e_head('About Northwind', 'Senior advisors, not slide decks', 'Founded in 2008, we are a team of 40 former operators, CFOs and marketers.'),
                                e_stats([('98%', 'client retention'), ('3.4×', 'average ROI'), ('40', 'consultants'), ('12', 'countries')], 'primary'),
                                e.button('About us', '{{page:about}}', 'outline'), width=55, justify='center'), gap=56), bg='surface-alt', pad=(96, 24, 96, 24)),
        e.container(*e_head('How we work', 'From first call to measurable results', center=True), e_steps(), pad=(96, 24, 96, 24)),
        e.container(e.overline('Case studies', 'on-ink'), e.heading('Results our clients can measure', 2, 'on-ink'), e_case_cards(CASES[:3]),
                    e.button('All case studies', '{{page:case-studies}}', 'light'), bg='ink', pad=(96, 24, 96, 24)),
        e.container(*e_head('Testimonials', 'What our clients say', center=True), e_quotes(), pad=(96, 24, 96, 24)),
        e.container(*e_head('Our partners', 'Meet the people who lead your project'), e_people([p[:3] + ('',) for p in PEOPLE]), bg='surface-alt', pad=(96, 24, 96, 24)),
        e.container(*e_head('Engagements', 'Simple ways to work with us', 'Fixed fees agreed before we start.', center=True), e_plans(), pad=(96, 24, 96, 24)),
        e.container(e.row(e.col(*e_head('FAQ', 'Questions before you book'), e.accordion(FAQ), width=50),
                          e.col(*e_head('Insights', 'Latest from our blog'), e.widget('posts', {'posts_per_page': 3, 'classic_columns': '1', 'classic_thumbnail': 'left'}), width=50), gap=56),
                    bg='surface-alt', pad=(96, 24, 96, 24)),
        e_cta()]


def e_about():
    return [e_hero('We help ambitious businesses grow — profitably', 'Strategy, growth and operations consulting led by people who have run companies themselves.', 'About'),
            e.container(e.row(e.col(*e_head('Our story', 'Built by operators who have run the businesses we advise',
                                            'Northwind started in 2008 when three former CFOs and COOs decided advice should come from people who have hit payroll themselves. Today 40 senior consultants work in 12 countries.'), width=55, justify='center'),
                              e.col(e.image_fixed('post-2', 'Our team at work', 420, radius=24), width=45), gap=56), pad=(96, 24, 96, 24)),
            e.container(e_stats([('18', 'years in business'), ('420+', 'projects'), ('98%', 'client retention'), ('$1.2B', 'capital raised')], 'on-ink'), bg='ink', pad=(64, 24, 64, 24)),
            e.container(*e_head('What we believe', 'Our principles', center=True),
                        e.grid(*[e.icon_box(i, t, d) for i, t, d in [('fas fa-chart-line', 'Numbers first', 'We agree targets before we start.'), ('fas fa-user-tie', 'Senior people only', 'A partner leads every engagement.'),
                                                                     ('fas fa-scale-balanced', 'Honest advice', 'If you do not need us, we will say so.'), ('fas fa-seedling', 'Built to last', 'Your team runs it without us.')]], columns=4),
                        bg='surface-alt', pad=(96, 24, 96, 24)),
            e.container(*e_head('Leadership', 'The partners'), e_people([p[:3] + ('',) for p in PEOPLE]), pad=(96, 24, 96, 24)),
            e_cta()]


def e_services():
    return [e_hero('Consulting services that move the numbers', 'Six practices, one senior team.', 'Services'),
            e.container(e_svc(), pad=(72, 24, 72, 24)),
            e.container(*e_head('How we work', 'From first call to measurable results', center=True), e_steps(), bg='surface-alt', pad=(96, 24, 96, 24)),
            e_cta()]


def e_service(i):
    key, _c, icon, name, desc, price, inc, (onum, otext) = SERVICES[i]
    return [e.container(e.row(e.col(e.text('Home / Services / ' + name, 'on-ink-muted', size_px=13), e.heading(name, 1, 'on-ink', size_px=56), e.text(desc, 'on-ink-muted', size_px=18),
                                    e_stats([(price.replace('From ', ''), 'starting price'), ('6–12 weeks', 'to first results'), (onum, otext)], 'on-ink'),
                                    e.row(e.button('Book a consultation', '{{page:contact}}'), e.button('See pricing', '{{page:pricing}}', 'light'), gap=12, mobile_direction='row', wrap=True),
                                    width=55, justify='center'),
                              e.col(e.image_fixed('post-3', name, 420, radius=24), width=45), gap=56), bg='ink', pad=(80, 24, 80, 24)),
            e.container(e.row(e.col(*e_head('Who it is for', 'Companies ready to grow — with a team ready to act'), width=50),
                              e.col(e.heading('What is included', 3, size_px=20), e.icon_list(inc), width=50, pad=28, radius=16, bg='surface-alt'), gap=48), pad=(96, 24, 96, 24)),
            e.container(*e_head('Case study', 'Fieldhouse Goods doubled online revenue in 9 months'), e.text('<strong>+118% revenue · −27% CAC · 3 new channels</strong>'),
                        e.button('Read the case study', '{{page:case-fieldhouse}}', 'outline'), bg='surface-alt', pad=(80, 24, 80, 24)),
            e.container(e.row(e.col(*e_head('FAQ', 'Questions about ' + name.lower()), e.accordion(FAQ[:4]), width=45),
                              e.col(e.heading('Get a proposal', 3, size_px=24), e.shortcode('[wpforms id="{{form:contact}}" title="false"]'), width=55, pad=32, radius=16, border='border'), gap=48),
                        pad=(96, 24, 96, 24))]


def e_cases():
    return [e_hero('Results our clients can measure', 'Every engagement ends with numbers.', 'Case studies'), e.container(e_case_cards(CASES), pad=(72, 24, 72, 24)), e_cta()]


def e_case():
    return [e_hero('How Fieldhouse Goods doubled online revenue in 9 months', 'Client: Fieldhouse Goods · Retail · Growth & marketing · 9 months', 'Case studies / Fieldhouse Goods'),
            e.container(e.image_fixed('post-3', 'Fieldhouse Goods team', 480, radius=24), pad=(40, 24, 40, 24)),
            e.container(e_stats([('+118%', 'online revenue'), ('−27%', 'acquisition cost'), ('3', 'new channels')], 'on-ink'), bg='ink', pad=(56, 24, 56, 24)),
            e.container(e.heading('The challenge', 3), e.text('Online growth had stalled at 4% a year while ad costs kept rising.'),
                        e.heading('Our approach', 3), e.text('New pricing and bundles, 40% of spend moved to search and email, and a weekly growth meeting.'),
                        e.heading('The result', 3), e.text('Online revenue more than doubled in nine months.'), e.testimonial(*QUOTES[0][1:], key=QUOTES[0][0]), pad=(80, 24, 80, 24)),
            e_cta()]


def e_team():
    return [e_hero('Senior people on every project', 'Partners and consultants who have run real companies.', 'Team'),
            e.container(e_people(PEOPLE), pad=(72, 24, 72, 24)),
            e.container(e.heading('Want to work with us?', 3), e.button('See open roles', '{{page:careers}}'), bg='surface-alt', pad=(64, 24, 64, 24)), e_cta()]


def e_pricing():
    return [e_hero('Fixed fees. Agreed before we start.', 'No hourly billing and no surprises.', 'Pricing'), e.container(e_plans(), pad=(72, 24, 72, 24)),
            e.container(*e_head('FAQ', 'Pricing questions'), e.accordion([('Are there any extra costs?', 'Only travel for on-site work, agreed in advance.'),
                                                                        ('Can we pause a retainer?', 'Yes, with 30 days notice.')]), bg='surface-alt', pad=(80, 24, 80, 24)), e_cta()]


def e_careers():
    return [e_hero('Join a team of senior operators', 'We are growing in Chicago, London and remote.', 'Careers'),
            e.container(*e_head('Open roles', '%d positions open' % len(JOBS)),
                        *[e.row(e.col(e.heading(t, 3, size_px=20), e.text('%s · %s' % (loc, ty), size_px=14), gap=2), e.button('Apply', '#apply', 'outline'),
                                gap=16, pad=20, radius=14, border='border', mobile_direction='row') for t, loc, ty in JOBS], pad=(72, 24, 72, 24), gap=12),
            e.container(e.heading('Send us your application'), e.shortcode('[wpforms id="{{form:careers}}" title="false"]'), bg='surface-alt', pad=(72, 24, 96, 24))]


def e_contact():
    return [e_hero('Let’s talk about your business', 'A free 45-minute consultation with a partner. No obligation.', 'Contact'),
            e.container(e.row(e.col(e.heading('Book a free consultation', 2, size_px=28), e.shortcode('[wpforms id="{{form:contact}}" title="false"]'), width=62, pad=32, radius=16, border='border'),
                              e.col(*[e.icon_box(i, t, d) for i, t, d in [('fas fa-building', 'Chicago (HQ)', '200 W Madison St, Suite 1800, Chicago, IL 60606<br>' + PHONE),
                                                                         ('fas fa-location-dot', 'London', '30 Finsbury Square, London EC2A 1AG'),
                                                                         ('fas fa-clock', 'Hours', 'Mon–Fri 9:00–18:00'), ('fas fa-envelope', 'Email', EMAIL)]], width=38, gap=20), gap=40),
                        pad=(72, 24, 72, 24)),
            e.container(e.map_widget('200 W Madison St, Chicago'), pad=0, boxed=False),
            e.container(*e_head('FAQ', 'Before you book'), e.accordion([FAQ[4], FAQ[0]]), bg='surface-alt', pad=(80, 24, 80, 24))]


# Package -------------------------------------------------------------------------------------

PAGES = [('home', 'Home', 'home', g_home, e_home), ('about', 'About', 'about', g_about, e_about), ('services', 'Services', 'services', g_services, e_services),
         ('case-studies', 'Case studies', 'case-studies', g_cases, e_cases), ('case-fieldhouse', 'Fieldhouse Goods', 'fieldhouse-goods', g_case, e_case),
         ('team', 'Team', 'team', g_team, e_team), ('pricing', 'Pricing', 'pricing', g_pricing, e_pricing), ('careers', 'Careers', 'careers', g_careers, e_careers),
         ('contact', 'Contact', 'contact', g_contact, e_contact)]
PAGES += [('svc-' + s[0], s[3], s[0], (lambda i=i: g_service(i)), (lambda i=i: e_service(i))) for i, s in enumerate(SERVICES)]


def careers_form():
    return {'key': 'careers', 'title': 'Job application — Northwind', 'submit': 'Send application',
            'message': 'Thanks — we read every application and reply within a week.',
            'settings': {'subject': 'Application from {field_id="1"}'},
            'fields': [{'type': 'name', 'label': 'Full name', 'required': True, 'size': 'medium'}, {'type': 'email', 'label': 'Email', 'required': True, 'size': 'medium'},
                       {'type': 'select', 'label': 'Role', 'required': True, 'choices': [j[0] for j in JOBS] + ['Open application']},
                       {'type': 'url', 'label': 'LinkedIn or CV link', 'required': True}, {'type': 'textarea', 'label': 'Why Northwind?'}]}


def apply(data, builder):
    """Replace the Base pages, menu and site options in an existing Base package (products, posts and media stay)."""
    keep = [c for c in data['content'] if c['type'] != 'page' or c['key'] == 'blog']
    pages = []
    for key, title, slug, g, el in PAGES:
        item = {'key': key, 'type': 'page', 'title': title, 'slug': slug, 'template': 'page-full-width'}
        if builder == 'gutenberg':
            item['content'] = g()
        else:
            item['content'] = ''
            item['elementor'] = el()
        if key.startswith('svc-'):
            item['parent'] = 'services'
        pages.append(item)
    data['content'] = pages + keep
    data['menus'] = [{'name': 'Main menu', 'location': 'primary', 'items': [
        {'title': 'About', 'page': 'about'}, {'title': 'Services', 'page': 'services'}, {'title': 'Case studies', 'page': 'case-studies'},
        {'title': 'Team', 'page': 'team'}, {'title': 'Pricing', 'page': 'pricing'}, {'title': 'Insights', 'page': 'blog'}, {'title': 'Contact', 'page': 'contact'}]}]
    opts = data['settings'].setdefault('options', {})
    opts.update({'blogname': 'Northwind Advisory', 'blogdescription': 'Strategy, growth and operations consulting'})
    import demo_forms
    data['forms'] = [demo_forms.base_contact(), careers_form()]
    return data


LEGAL = ('{{site:name}}', '{{site:email}}', 'our consulting services')
