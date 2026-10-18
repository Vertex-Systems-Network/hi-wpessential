"""Pulse — conference / events demo (Pro). Research and direction: docs/demos/pulse.md.

Companies and people are fictional. Event dates are fixed (13–14 May 2027) so the copy,
the agenda and The Events Calendar always agree.
"""
import blocks as b
import elementor as e

ID = 'pulse'
DATES = '13–14 May 2027'

MEDIA = [
    ('hero-stage', 'Main stage', 'Speaker on a large stage lit in lime and magenta in front of a full audience'),
    ('crowd', 'Audience', 'Conference audience applauding under coloured stage lights'),
    ('workshop', 'Workshop', 'Small team working around a table covered in sticky notes during a workshop'),
    ('venue', 'Lisbon Congress Centre', 'Glass congress centre lit in lime and magenta, reflected in the river at dusk'),
    ('networking', 'Networking', 'Four attendees with lanyards chatting over coffee in a bright foyer'),
    ('sp-1', 'Amara Okafor', 'Portrait of Amara Okafor'),
    ('sp-2', 'Rohan Mehta', 'Portrait of Rohan Mehta'),
    ('sp-3', 'Mei Lin Chen', 'Portrait of Mei Lin Chen'),
    ('sp-4', 'Daniel Hart', 'Portrait of Daniel Hart'),
    ('sp-5', 'Lucía Fernández', 'Portrait of Lucía Fernández'),
    ('sp-6', 'Omar Haddad', 'Portrait of Omar Haddad'),
    ('sp-7', 'Siobhan Kelly', 'Portrait of Siobhan Kelly'),
    ('sp-8', 'Jordan Brooks', 'Portrait of Jordan Brooks'),
    ('blog-1', 'Breakout session', 'Speaker presenting in a breakout room to a seated audience'),
    ('blog-2', 'Pulse app', 'Laptop and tablet showing the Pulse event app on a dark table'),
    ('blog-3', 'Panel', 'Four panellists on stools talking on a neon-lit stage'),
]

SPEAKERS = [
    ('sp-1', 'Amara Okafor', 'VP Design', 'Northwind', 'Design systems at scale', 'Design'),
    ('sp-2', 'Rohan Mehta', 'Principal Engineer', 'Edgeway', 'The edge-first web', 'Engineering'),
    ('sp-3', 'Mei Lin Chen', 'AI Research Lead', 'Fieldnote', 'Designing with AI agents', 'AI'),
    ('sp-4', 'Daniel Hart', 'Founder & CEO', 'Lumen', 'From side project to Series B', 'Product'),
    ('sp-5', 'Lucía Fernández', 'Creative Director', 'Studio Norte', 'Brand in motion', 'Design'),
    ('sp-6', 'Omar Haddad', 'UX Research Lead', 'Paperplane', 'Research that ships', 'Product'),
    ('sp-7', 'Siobhan Kelly', 'Engineering Manager', 'Orbit Labs', 'Calm teams ship faster', 'Engineering'),
    ('sp-8', 'Jordan Brooks', 'Developer Advocate', 'Kite', 'Open source in 2027', 'Engineering'),
]

DAY1 = [
    ('09:00', '30 min', 'Keynote', 'Main Stage', 'Opening keynote: the next decade of the web', [0, 2]),
    ('09:45', '40 min', 'Design', 'Main Stage', 'Design systems at scale: lessons from 10,000 components', [0]),
    ('10:30', '40 min', 'Engineering', 'Hall B', 'The edge-first web: shipping in every region', [1]),
    ('11:15', '90 min', 'AI', 'Workshop 1', 'Designing with AI agents (hands-on)', [2]),
    ('13:30', '40 min', 'Product', 'Hall B', 'From side project to Series B in 30 months', [3]),
    ('14:15', '45 min', 'Design', 'Main Stage', 'Panel: brand in motion', [4, 5, 7]),
    ('15:30', '40 min', 'Engineering', 'Hall B', 'Calm teams ship faster', [6]),
    ('19:30', '4 h', 'Social', 'LX Factory', 'Pulse after-party', []),
]
DAY2 = [
    ('09:30', '30 min', 'Keynote', 'Main Stage', 'Research that ships: closing the loop with users', [5]),
    ('10:15', '40 min', 'Engineering', 'Main Stage', 'Open source in 2027: who pays, who maintains', [7]),
    ('11:00', '40 min', 'AI', 'Hall B', 'Evaluating AI features before your users do', [2]),
    ('13:30', '40 min', 'Design', 'Main Stage', 'Motion that means something', [4]),
    ('14:15', '40 min', 'Product', 'Hall B', 'Pricing pages that convert', [3]),
    ('15:30', '45 min', 'Keynote', 'Main Stage', 'Closing keynote: build less, ship better', [0, 6]),
]

PASSES = [
    dict(key='pass-online', name='Online Pass', price='79', image='blog-2', menu_order=1,
         short='Watch every talk live from both stages and on demand for 12 months.',
         inc=['Live stream of both stages', 'Recordings for 12 months', 'Online community access']),
    dict(key='pass-conference', name='Conference Pass', price='599', sale='449', image='hero-stage', menu_order=2, featured=True,
         short='Two days in Lisbon with every talk, lunch, coffee and the evening party.',
         inc=['Both days, all talks', 'Lunch, coffee and the after-party', 'Recordings for 12 months', 'Networking app']),
    dict(key='pass-workshop', name='Conference + Workshop Pass', price='899', sale='749', image='workshop', menu_order=3,
         short='Everything in Conference plus one four-hour workshop on 12 May.',
         inc=['Everything in Conference', 'One 4-hour workshop', 'Workshop certificate', 'Priority seating']),
    dict(key='pass-team', name='Team Pass (5+)', price='399', image='networking', menu_order=4,
         short='Per person for teams of five or more. Add five or more to your cart.',
         inc=['Everything in Conference', 'Named invoice', 'Dedicated team host']),
]

FAQ = [
    ('Can I get a refund?', 'Yes. Full refund until 14 April 2027 and 50% until 7 May. After that you can transfer your pass to a colleague for free.'),
    ('Can I transfer my ticket?', 'Name changes are free until 7 May. Email tickets@pulseconf.io with the order number and the new attendee name.'),
    ('Is there a student discount?', 'Students and under-represented groups in tech can apply for 50% off Conference passes. Places are limited.'),
    ('Are talks recorded?', 'Every talk on both stages is recorded and available to Online and Conference pass holders for 12 months.'),
    ('Is the venue accessible?', 'Yes. Step-free access, hearing loops in both halls, live captions on every talk and a quiet room on each floor.'),
    ('Do you offer visa letters?', 'Yes. After you buy a pass, request a letter from your account and we will email it within two working days.'),
]

POSTS = [
    dict(key='post-recap', title='What we learned running Pulse 2026', slug='pulse-2026-recap', image='blog-3', date='2026-07-02 09:00:00', cat='cat-news',
         excerpt='2,400 people, 58 talks and one very long coffee queue. Here is what we are changing for 2027.',
         body=[('p', 'Pulse 2026 sold out three weeks early. We read every one of the 1,100 feedback forms; these are the changes they asked for.'),
               ('h', 'Shorter talks, longer breaks'), ('p', 'Most talks move from 45 to 40 minutes, and every break is now 30 minutes. The hallway track is half the reason people come.'),
               ('img', 'blog-3', 'Panellists on stage at Pulse 2026'),
               ('h', 'A second stage for workshops'), ('p', 'Workshops sold out in a day, so 12 May becomes a full workshop day at LX Factory with eight rooms.'),
               ('quote', 'The best conference I have been to in years — and I finally met the people behind the tools I use every day.', 'Attendee survey, 2026')]),
    dict(key='post-guide', title='How to get the most out of a two-day conference', slug='conference-guide', image='blog-1', date='2026-09-10 09:00:00', cat='cat-guides',
         excerpt='Pick two talks a day, plan one conversation, and leave room for the unplanned.',
         body=[('p', 'Two days goes fast. The people who get the most out of Pulse plan a little and leave room for a lot.'),
               ('ul', ['Mark two must-see talks per day in the app; let the rest find you.', 'Write down one question you want answered by Friday.', 'Sit somewhere new at lunch — every table is mixed on purpose.', 'Use the quiet rooms. Nobody does their best thinking in a crowd.']),
               ('img', 'blog-1', 'Breakout session at Pulse'),
               ('p', 'And go to the after-party, even for an hour. Half of next year\'s speakers were in the audience the year before.')]),
    dict(key='post-workshops', title='Announcing the 2027 workshop programme', slug='workshops-2027', image='blog-2', date='2026-10-08 09:00:00', cat='cat-news',
         excerpt='Eight hands-on workshops on 12 May, from AI agents to design tokens. Seats are capped at 24.',
         body=[('p', 'Workshops are four hours, hands-on and capped at 24 seats so every attendee gets time with the instructor.'),
               ('ul', ['Designing with AI agents — Mei Lin Chen', 'Design tokens from Figma to code — Amara Okafor', 'Edge functions in practice — Rohan Mehta', 'Running a research sprint — Omar Haddad']),
               ('img', 'blog-2', 'The Pulse app on a laptop and tablet'),
               ('p', 'Workshop seats come with the Conference + Workshop pass. Pick your workshop after checkout.')]),
]

SPONSORS = [('Platinum', ['Northwind', 'Edgeway']), ('Gold', ['Fieldnote', 'Lumen', 'Orbit Labs', 'Paperplane']),
            ('Community', ['Studio Norte', 'Kite', 'Monolith', 'Tessera', 'Halcyon', 'Driftwood'])]

PACKAGES = [('Community', '€4,000', ['Logo on the website and stage screens', 'Two Conference passes', 'Booth in the community area']),
            ('Gold', '€12,000', ['Everything in Community', '10 m² booth in the main foyer', 'Six Conference passes', 'Sponsored coffee break']),
            ('Platinum', '€30,000', ['Everything in Gold', 'Ten-minute stage slot', 'Hosted workshop room on 12 May', 'Twelve Conference passes'])]


def speaker_names(idx):
    return ', '.join(SPEAKERS[i][1] for i in idx)


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
    bar = b.group(b.p('<strong>Pulse 2027 · Lisbon · %s</strong> — Early-bird pricing until 22 March' % DATES, align='center', size='small'),
                  className='wpe-topbar', bg='primary', color='on-primary', content='1200px')
    head = b.group(b.group(b.group(b.block('site-title'), layout='flex', wrap='nowrap'),
                           b.block('navigation', {'overlayMenu': 'mobile', 'layout': {'type': 'flex', 'justifyContent': 'center'}}),
                           b.buttons(b.button('Get tickets', '{{page:tickets}}')),
                           className='wpe-header__inner', layout='flex', wrap='nowrap', justify='space-between'),
                   tag='header', className='wpe-header', content='1200px')
    return b.join(bar, head)


def g_footer():
    return b.footer('Two days of design, engineering and AI talks. %s at the Lisbon Congress Centre.' % DATES,
                    [('Event', [('Speakers', '{{page:speakers}}'), ('Agenda', '{{page:agenda}}'), ('Tickets', '{{page:tickets}}'), ('Events calendar', '{{home:events/}}')]),
                     ('Attend', [('Venue & hotels', '{{page:venue}}'), ('FAQ', '{{page:faq}}'), ('My tickets', '{{woo:myaccount}}'), ('Contact', '{{page:contact}}')]),
                     ('Company', [('Sponsors', '{{page:sponsors}}'), ('Become a sponsor', '{{page:become-sponsor}}'), ('Blog', '{{page:blog}}')])],
                    ('© Pulse Conference Lda. All rights reserved.', 'Designed &amp; built with <a href="https://wpessential.org/">WPEssential</a>'),
                    band=('See you in Lisbon.', 'Early-bird pricing until 22 March.', 'Get tickets', '{{page:tickets}}'))


def g_speaker_card(s, topic=True):
    k, name, role, company, talk, track = s
    return b.group(b.image(k, 'Portrait of ' + name, aspect='4/5', style='wpe-shadow'),
                   b.h(name, level=3, size='h4'), b.p('%s · %s' % (role, company), color='text-muted', size='small'),
                   b.p(talk, size='small') if topic else '', layout='default', className='wpe-speaker')


def g_session(t):
    time, length, track, room, title, sp = t
    return b.group(
        b.group(b.p('<strong>%s</strong>' % time), b.p(length, color='text-muted', size='small'), layout='default', className='wpe-session__time'),
        b.group(b.p('%s · %s' % (track, room), style='wpe-overline'), b.h(title, level=3, size='h4'),
                b.p(speaker_names(sp), color='text-muted', size='small') if sp else '', layout='default', className='wpe-session__body'),
        layout='flex', wrap='nowrap', valign='top', className='wpe-card wpe-session')


def g_passes():
    return b.shortcode('[products category="passes" columns="4" limit="4" orderby="menu_order" order="ASC"]')


def g_home():
    hero = b.cover('hero-stage',
                   b.p('%s · Lisbon Congress Centre' % DATES, style='wpe-overline', color='primary'),
                   b.h('Design. Code. Ship.', level=1, size='display', color='on-ink'),
                   b.p('Two days, 60 speakers and three stages for people who design and build the web. Talks, workshops and the best after-party in Europe.', color='on-ink', size='body-l'),
                   b.buttons(b.button('Get tickets — from €79', '{{page:tickets}}'), b.button('See the agenda', '{{page:agenda}}', style='wpe-light')),
                   dim=60, min_height=760, position='bottom left')
    stats = b.section(b.group(*[b.group(b.p(n, className='wpe-stat-number'), b.p(t, color='text-muted'), layout='default', className='wpe-card')
                                for n, t in [('2', 'days of talks'), ('60+', 'speakers'), ('3', 'stages + workshops'), ('2,400', 'attendees')]],
                              layout='grid', grid_min='12rem'), className='wpe-section wpe-section--sm')
    sponsors = b.section(b.p('Backed by teams who ship', align='center', color='text-muted', size='small'),
                         b.p(' · '.join(n for _, names in SPONSORS[:2] for n in names), align='center', size='h4'),
                         className='wpe-section wpe-section--sm', content='960px')
    speakers = b.section(
        b.group(b.group(b.p('Speakers', style='wpe-overline'), b.h('Learn from the people building what\'s next', size='h2'), layout='default'),
                b.buttons(b.button('All speakers', '{{page:speakers}}', style='wpe-outline')), layout='flex', wrap='wrap', justify='space-between', valign='bottom'),
        b.group(*[g_speaker_card(s, False) for s in SPEAKERS], layout='grid', grid_min='10rem'))
    agenda = b.section(
        b.group(b.group(b.p('Agenda', style='wpe-overline'), b.h('Day 1 · Thursday 13 May', size='h2'), layout='default'),
                b.buttons(b.button('Full agenda', '{{page:agenda}}', style='wpe-outline')), layout='flex', wrap='wrap', justify='space-between', valign='bottom'),
        *[g_session(t) for t in DAY1[:4]], bg='surface-alt')
    tickets = b.section(b.p('Tickets', style='wpe-overline', align='center'), b.h('Pick your pass', size='h2', align='center'),
                        b.p('All prices include VAT. Student and diversity tickets are available on request.', align='center', color='text-muted'),
                        g_passes())
    venue = b.section(b.columns(
        b.column(b.image('venue', 'Lisbon Congress Centre at dusk', aspect='4/3', style='wpe-shadow'), width='55%'),
        b.column(b.p('Venue', style='wpe-overline'), b.h('Lisbon Congress Centre, on the river', size='h2'),
                 b.p('Ten minutes from the airport on the metro, with step-free access throughout and a terrace over the Tagus.', color='text-muted'),
                 b.ul(['Praça das Indústrias, 1300-307 Lisboa', 'Doors 08:00 · Talks 09:00 – 18:00', 'Hearing loops, live captions, quiet rooms'], style='wpe-checklist'),
                 b.buttons(b.button('Plan your trip', '{{page:venue}}', style='wpe-outline')), valign='center'), valign='center', className='wpe-gap-lg'), bg='surface-alt')
    quotes = b.section(b.p('2026 in their words', style='wpe-overline'), b.h('“The best two days of my year.”', size='h2'),
                       b.columns(*[b.column(b.image(img, alt, aspect='3/2'), b.p(q), b.p('<strong>%s</strong>' % who, size='small'), style='wpe-card')
                                   for img, alt, q, who in [
                                       ('crowd', 'Audience at Pulse 2026', 'I left with three new collaborators and a notebook full of ideas I shipped the following month.', 'Priya S., Product Designer'),
                                       ('workshop', 'Workshop at Pulse 2026', 'The workshops were hands-on and small. I learned more in four hours than in a semester.', 'Marco R., Frontend Lead'),
                                       ('networking', 'Attendees networking', 'Tight talks, zero fluff, and the after-party was unreal. Already bought my 2027 ticket.', 'Hannah W., CTO')]]))
    news = b.section(b.group(b.group(b.p('News', style='wpe-overline'), b.h('From the Pulse blog', size='h2'), layout='default'),
                             b.buttons(b.button('All posts', '{{page:blog}}', style='wpe-outline')), layout='flex', wrap='wrap', justify='space-between', valign='bottom'),
                     b.latest_posts(3), bg='surface-alt')
    cta = b.section(b.group(b.h('Early-bird tickets end 22 March', size='h2', color='on-primary'),
                            b.p('Save €150 on Conference passes. Prices go up at 23:59 Lisbon time.', color='on-primary'),
                            b.buttons(b.button('Get your ticket', '{{page:tickets}}', style='wpe-dark')), bg='primary', className='wpe-card', layout='constrained'),
                    className='wpe-section wpe-section--sm')
    return b.join(hero, stats, sponsors, speakers, agenda, tickets, venue, quotes, news, cta)


def g_title(over, title, lead):
    return b.section(b.p(over, style='wpe-overline'), b.h(title, level=1, size='h1'), b.p(lead, style='wpe-lead'),
                     className='wpe-section wpe-section--sm', content='860px')


def g_speakers():
    return b.join(g_title('Speakers', '60 speakers, one stage-worthy idea each', 'Designers, engineers and founders who have shipped the things they talk about.'),
                  b.section(b.group(*[g_speaker_card(s) for s in SPEAKERS], layout='grid', grid_min='10rem'), className='wpe-section wpe-section--sm'),
                  b.section(b.h('Want to speak in 2028?', size='h3', align='center'),
                            b.p('The call for proposals opens in September. Send us a short pitch and a past talk.', align='center', color='text-muted'),
                            b.buttons(b.button('Get in touch', '{{page:contact}}', style='wpe-outline'), justify='center'), bg='surface-alt', content='720px'))


def g_agenda():
    return b.join(g_title('Agenda', 'Two days, three stages', 'All times are Lisbon time (WEST, UTC+1). Workshops run on 12 May at LX Factory.'),
                  b.section(b.h('Day 1 · Thursday 13 May', size='h2'), *[g_session(t) for t in DAY1], className='wpe-section wpe-section--sm'),
                  b.section(b.h('Day 2 · Friday 14 May', size='h2'), *[g_session(t) for t in DAY2], bg='surface-alt'),
                  b.section(b.h('Add Pulse to your calendar', size='h3', align='center'),
                            b.buttons(b.button('Browse all events', '{{home:events/}}'), b.button('Get tickets', '{{page:tickets}}', style='wpe-outline'), justify='center'),
                            content='720px', className='wpe-section wpe-section--sm'))


def g_tickets():
    rows = [['Live stream of both stages', '✓', '✓', '✓', '✓'], ['In-person talks, both days', '—', '✓', '✓', '✓'],
            ['Lunch, coffee and after-party', '—', '✓', '✓', '✓'], ['Recordings for 12 months', '✓', '✓', '✓', '✓'],
            ['Four-hour workshop', '—', '—', '✓', 'Add-on'], ['Named invoice and team host', '—', '—', '—', '✓']]
    return b.join(g_title('Tickets', 'Choose your pass', 'Early-bird pricing until 22 March. Group discounts apply automatically from five passes.'),
                  b.section(g_passes(), className='wpe-section wpe-section--sm'),
                  b.section(b.h('What is included', size='h2'), b.table(['', 'Online', 'Conference', '+ Workshop', 'Team'], rows), bg='surface-alt'),
                  b.section(b.h('Ticket questions', size='h2'), *[b.details(q, b.p(a)) for q, a in FAQ[:3]], content='860px'))


def g_venue():
    return b.join(
        b.cover('venue', b.p('Venue', style='wpe-overline', color='primary'), b.h('Lisbon Congress Centre', level=1, size='display', color='on-ink'),
                b.p('On the Tagus riverfront, ten minutes from the airport. Step-free access throughout.', color='on-ink', size='body-l'),
                dim=50, min_height=600, position='bottom left'),
        b.section(b.h('Getting there', size='h2'), b.columns(*[b.column(b.h(t, level=3, size='h4'), b.p(d, color='text-muted'), style='wpe-card')
                                                             for t, d in [('From the airport', 'Red metro line to Oriente, change to the green line for Cais do Sodré. About 25 minutes, €1.80.'),
                                                                          ('From the city centre', 'Tram 15 to Belém, then a five-minute walk along the river.'),
                                                                          ('By bike', 'Gira bike stations at the main entrance. Secure bike parking inside.')]])),
        b.section(b.h('Stay close, pay less', size='h2'), b.p('Use the code PULSE27 for these rates. Rooms are held until 10 April.', color='text-muted'),
                  b.table(['Hotel', 'Walk', 'From'], [['Myriad by Sana', '2 min · river view', '€189'], ['Tivoli Oriente', '5 min · rooftop pool', '€149'], ['Moxy Lisboa Oriente', '8 min · budget friendly', '€99']]),
                  bg='surface-alt'),
        b.section(b.columns(b.column(b.image('networking', 'Attendees in the foyer', aspect='4/3')),
                            b.column(b.h('Workshops and after-party: LX Factory', size='h2'),
                                     b.p('Rua Rodrigues de Faria 103, 1300-501 Lisboa. A converted textile mill with eight workshop rooms and a courtyard for the party.', color='text-muted'),
                                     b.buttons(b.button('Open in Maps', 'https://maps.google.com/?q=LX+Factory+Lisboa', style='wpe-outline')), valign='center'),
                            valign='center', className='wpe-gap-lg')))


def g_sponsors():
    tiers = [b.section(b.h(tier, size='h3'), b.group(*[b.group(b.p('<strong>%s</strong>' % n, align='center', size='h4'), className='wpe-card', layout='constrained') for n in names],
                                                      layout='grid', grid_min='12rem'), className='wpe-section wpe-section--sm') for tier, names in SPONSORS]
    return b.join(g_title('Sponsors', 'The teams that make Pulse possible', 'Our sponsors keep ticket prices low and fund 200 diversity tickets every year.'), *tiers,
                  b.section(b.h('Put your team in front of 2,400 builders', size='h2', align='center'),
                            b.buttons(b.button('Become a sponsor', '{{page:become-sponsor}}'), justify='center'), bg='surface-alt', content='760px'))


def g_become():
    return b.join(g_title('Partner with Pulse', 'Become a sponsor', 'Meet the designers, engineers and founders choosing the tools of the next five years.'),
                  b.section(b.columns(*[b.column(b.h(n, level=3, size='h3'), b.p('<strong>%s</strong>' % price, size='h2'), b.ul(items, style='wpe-checklist'), style='wpe-card')
                                        for n, price, items in PACKAGES]), className='wpe-section wpe-section--sm'),
                  b.section(b.columns(b.column(b.image('crowd', 'Pulse audience', aspect='4/3')),
                                      b.column(b.h('Let\'s talk', size='h2'), b.p('Tell us what you want to achieve and we will build a package around it.'),
                                               b.ul(['Email: <a href="mailto:partners@pulseconf.io">partners@pulseconf.io</a>', 'Phone: +351 21 340 2290', 'Deck and audience data on request']),
                                               valign='center'), valign='center', className='wpe-gap-lg'), bg='surface-alt'))


def g_faq():
    return b.join(g_title('FAQ', 'Questions, answered', 'Tickets, travel, accessibility and everything in between.'),
                  b.section(*[b.details(q, b.p(a)) for q, a in FAQ], content='860px', className='wpe-section wpe-section--sm'))


def g_contact():
    return b.join(g_title('Contact', 'Talk to the Pulse team', 'We reply within one working day, Monday to Friday.'),
                  b.section(b.columns(*[b.column(b.h(t, level=3, size='h4'), b.p(d), style='wpe-card') for t, d in [
                      ('Tickets', '<a href="mailto:tickets@pulseconf.io">tickets@pulseconf.io</a>'),
                      ('Sponsors', '<a href="mailto:partners@pulseconf.io">partners@pulseconf.io</a>'),
                      ('Press', '<a href="mailto:press@pulseconf.io">press@pulseconf.io</a>'),
                      ('Office', 'Rossio 42, 1100-200 Lisboa<br>+351 21 340 2290')]]), className='wpe-section wpe-section--sm'))


# Elementor ------------------------------------------------------------------------------

def e_speaker(s, topic=True):
    k, name, role, company, talk, track = s
    return e.image_box(k, name, '%s · %s%s' % (role, company, ('<br>' + talk) if topic else ''), height=320)


def e_session(t):
    time, length, track, room, title, sp = t
    return e.row(e.col(e.heading(time, 3, size_px=22), e.text(length), width=15),
                 e.col(e.overline('%s · %s' % (track, room)), e.heading(title, 3, size_px=22), e.text(speaker_names(sp)) if sp else e.spacer(1), width=85),
                 gap=24, pad=24, bg='surface', radius=20, border='border', mobile_direction='column')


def e_title(over, title, lead):
    return e.container(e.overline(over), e.heading(title, 1, size_px=64), e.text(lead, size_px=18), pad=(80, 24, 32, 24))


def e_home():
    return [
        e.container(e.overline('%s · Lisbon Congress Centre' % DATES, 'primary'), e.heading('Design. Code. Ship.', 1, 'on-ink', size_px=112),
                    e.text('Two days, 60 speakers and three stages for people who design and build the web. Talks, workshops and the best after-party in Europe.', 'on-ink', size_px=18),
                    e.row(e.button('Get tickets — from €79', '{{page:tickets}}'), e.button('See the agenda', '{{page:agenda}}', 'light'), gap=12, wrap=True, mobile_direction='row'),
                    image='hero-stage', overlay='ink', overlay_opacity=0.6, min_height=760, justify='flex-end', pad=(96, 24, 96, 24)),
        e.container(e.grid(e.counter(2, 'days of talks'), e.counter(60, 'speakers', '+'), e.counter(3, 'stages + workshops'), e.counter(2400, 'attendees'), columns=4),
                    pad=(56, 24, 56, 24)),
        e.container(e.text('Backed by teams who ship', align='center'), e.heading(' · '.join(n for _, names in SPONSORS[:2] for n in names), 3, 'text-muted', align='center'), pad=(16, 24, 48, 24)),
        e.container(e.overline('Speakers'), e.heading("Learn from the people building what's next"),
                    e.grid(*[e_speaker(s, False) for s in SPEAKERS], columns=4), e.button('All speakers', '{{page:speakers}}', 'outline'), pad=(80, 24, 80, 24)),
        e.container(e.overline('Agenda'), e.heading('Day 1 · Thursday 13 May'), *[e_session(t) for t in DAY1[:4]], e.button('Full agenda', '{{page:agenda}}', 'outline'),
                    bg='surface-alt', pad=(80, 24, 80, 24), gap=16),
        e.container(e.overline('Tickets', align='center'), e.heading('Pick your pass', align='center'),
                    e.text('All prices include VAT. Student and diversity tickets are available on request.', align='center'),
                    e.shortcode('[products category="passes" columns="4" limit="4" orderby="menu_order" order="ASC"]'), pad=(80, 24, 80, 24)),
        e.container(e.row(e.col(e.image_fixed('venue', 'Lisbon Congress Centre at dusk', 460, radius=24), width=55),
                          e.col(e.overline('Venue'), e.heading('Lisbon Congress Centre, on the river'),
                                e.text('Ten minutes from the airport on the metro, with step-free access throughout and a terrace over the Tagus.'),
                                e.icon_list(['Praça das Indústrias, 1300-307 Lisboa', 'Doors 08:00 · Talks 09:00 – 18:00', 'Hearing loops, live captions, quiet rooms']),
                                e.button('Plan your trip', '{{page:venue}}', 'outline'), width=45, justify='center'), gap=56),
                    bg='surface-alt', pad=(80, 24, 80, 24)),
        e.container(e.overline('2026 in their words'), e.heading('“The best two days of my year.”'),
                    e.grid(e.testimonial('I left with three new collaborators and a notebook full of ideas I shipped the following month.', 'Priya S.', 'Product Designer'),
                           e.testimonial('The workshops were hands-on and small. I learned more in four hours than in a semester.', 'Marco R.', 'Frontend Lead'),
                           e.testimonial('Tight talks, zero fluff, and the after-party was unreal. Already bought my 2027 ticket.', 'Hannah W.', 'CTO'), columns=3),
                    pad=(80, 24, 80, 24)),
        e.container(e.overline('News'), e.heading('From the Pulse blog'),
                    e.grid(*[e.image_box(p['image'], p['title'], p['excerpt'], link='{{page:%s}}' % p['key'], height=240) for p in POSTS], columns=3),
                    bg='surface-alt', pad=(80, 24, 80, 24)),
        e.container(e.container(e.heading('Early-bird tickets end 22 March', 2, 'on-primary'), e.text('Save €150 on Conference passes. Prices go up at 23:59 Lisbon time.', 'on-primary'),
                                e.button('Get your ticket', '{{page:tickets}}', 'dark'), inner=True, bg='primary', radius=28, pad=56),
                    pad=(48, 24, 80, 24)),
    ]


def e_speakers():
    return [e_title('Speakers', '60 speakers, one stage-worthy idea each', 'Designers, engineers and founders who have shipped the things they talk about.'),
            e.container(e.grid(*[e_speaker(s) for s in SPEAKERS], columns=4), pad=(24, 24, 80, 24)),
            e.container(e.heading('Want to speak in 2028?', 3, align='center'), e.text('The call for proposals opens in September. Send us a short pitch and a past talk.', align='center'),
                        e.button('Get in touch', '{{page:contact}}', 'outline', align='center'), bg='surface-alt', pad=(72, 24, 72, 24))]


def e_agenda():
    return [e_title('Agenda', 'Two days, three stages', 'All times are Lisbon time (WEST, UTC+1). Workshops run on 12 May at LX Factory.'),
            e.container(e.heading('Day 1 · Thursday 13 May'), *[e_session(t) for t in DAY1], pad=(24, 24, 64, 24), gap=16),
            e.container(e.heading('Day 2 · Friday 14 May'), *[e_session(t) for t in DAY2], bg='surface-alt', pad=(64, 24, 64, 24), gap=16),
            e.container(e.heading('Add Pulse to your calendar', 3, align='center'),
                        e.row(e.button('Browse all events', '{{home:events/}}'), e.button('Get tickets', '{{page:tickets}}', 'outline'), gap=12, justify='center', mobile_direction='row', wrap=True),
                        pad=(64, 24, 64, 24), align='center')]


def e_tickets():
    return [e_title('Tickets', 'Choose your pass', 'Early-bird pricing until 22 March. Group discounts apply automatically from five passes.'),
            e.container(e.shortcode('[products category="passes" columns="4" limit="4" orderby="menu_order" order="ASC"]'), pad=(24, 24, 56, 24)),
            e.container(e.heading('What is included'), e.grid(*[e.container(e.heading(p['name'], 3, size_px=22), e.icon_list(p['inc']), inner=True, pad=28, radius=20, border='border', bg='surface')
                                                               for p in PASSES], columns=4), bg='surface-alt', pad=(72, 24, 72, 24)),
            e.container(e.heading('Ticket questions'), e.accordion(FAQ[:3]), pad=(72, 24, 72, 24))]


def e_venue():
    return [e.container(e.overline('Venue', 'primary'), e.heading('Lisbon Congress Centre', 1, 'on-ink', size_px=96),
                        e.text('On the Tagus riverfront, ten minutes from the airport. Step-free access throughout.', 'on-ink', size_px=18),
                        image='venue', overlay='ink', overlay_opacity=0.5, min_height=600, justify='flex-end', pad=(96, 24, 96, 24)),
            e.container(e.heading('Getting there'), e.grid(*[e.icon_box(i, t, d) for i, t, d in [
                ('fas fa-plane-arrival', 'From the airport', 'Red metro line to Oriente, change to the green line for Cais do Sodré. About 25 minutes, €1.80.'),
                ('fas fa-train-tram', 'From the city centre', 'Tram 15 to Belém, then a five-minute walk along the river.'),
                ('fas fa-bicycle', 'By bike', 'Gira bike stations at the main entrance. Secure bike parking inside.')]], columns=3), pad=(80, 24, 80, 24)),
            e.container(e.heading('Stay close, pay less'), e.text('Use the code PULSE27 for these rates. Rooms are held until 10 April.'),
                        e.icon_list(['Myriad by Sana — 2 min, river view — from €189', 'Tivoli Oriente — 5 min, rooftop pool — from €149', 'Moxy Lisboa Oriente — 8 min, budget friendly — from €99'], icon='fas fa-bed'),
                        bg='surface-alt', pad=(72, 24, 72, 24)),
            e.container(e.map_widget('Praça das Indústrias, 1300-307 Lisboa'), pad=0, boxed=False)]


def e_sponsors():
    return [e_title('Sponsors', 'The teams that make Pulse possible', 'Our sponsors keep ticket prices low and fund 200 diversity tickets every year.')] + [
        e.container(e.heading(tier, 3), e.grid(*[e.container(e.heading(n, 4, align='center', size_px=22), inner=True, pad=32, radius=20, border='border', bg='surface', justify='center')
                                                for n in names], columns=4), pad=(32, 24, 32, 24)) for tier, names in SPONSORS] + [
        e.container(e.heading('Put your team in front of 2,400 builders', align='center'), e.button('Become a sponsor', '{{page:become-sponsor}}', align='center'),
                    bg='surface-alt', pad=(72, 24, 72, 24))]


def e_become():
    return [e_title('Partner with Pulse', 'Become a sponsor', 'Meet the designers, engineers and founders choosing the tools of the next five years.'),
            e.container(e.grid(*[e.container(e.heading(n, 3), e.heading(price, 2, 'accent'), e.icon_list(items), inner=True, pad=32, radius=24, border='border', bg='surface')
                                 for n, price, items in PACKAGES], columns=3), pad=(24, 24, 72, 24)),
            e.container(e.row(e.col(e.image_fixed('crowd', 'Pulse audience', 420, radius=24), width=50),
                              e.col(e.heading("Let's talk"), e.text('Tell us what you want to achieve and we will build a package around it.'),
                                    e.icon_list(['partners@pulseconf.io', '+351 21 340 2290', 'Deck and audience data on request'], icon='fas fa-envelope'), width=50, justify='center'), gap=56),
                        bg='surface-alt', pad=(72, 24, 72, 24))]


def e_faq():
    return [e_title('FAQ', 'Questions, answered', 'Tickets, travel, accessibility and everything in between.'), e.container(e.accordion(FAQ), pad=(24, 24, 80, 24))]


def e_contact():
    return [e_title('Contact', 'Talk to the Pulse team', 'We reply within one working day, Monday to Friday.'),
            e.container(e.grid(*[e.icon_box(i, t, d) for i, t, d in [('fas fa-ticket', 'Tickets', 'tickets@pulseconf.io'), ('fas fa-handshake', 'Sponsors', 'partners@pulseconf.io'),
                                                                     ('fas fa-newspaper', 'Press', 'press@pulseconf.io'), ('fas fa-location-dot', 'Office', 'Rossio 42, 1100-200 Lisboa · +351 21 340 2290')]], columns=4),
                        pad=(24, 24, 56, 24)),
            e.container(e.map_widget('Rossio 42, 1100-200 Lisboa'), pad=0, boxed=False)]


# Package --------------------------------------------------------------------------------

def package(builder):
    e.FONTS = {'primary': 'Space Grotesk', 'text': 'DM Sans', 'weight': '700'}
    media = [{'key': k, 'file': k + '.webp', 'title': t, 'alt': a} for k, t, a in MEDIA]
    terms = [
        {'key': 'pc-passes', 'taxonomy': 'product_cat', 'name': 'Passes', 'slug': 'passes', 'meta': {'thumbnail_id': '{{media_id:hero-stage}}'}},
        {'key': 'cat-news', 'taxonomy': 'category', 'name': 'News', 'slug': 'news'},
        {'key': 'cat-guides', 'taxonomy': 'category', 'name': 'Guides', 'slug': 'guides'},
        {'key': 'ev-conference', 'taxonomy': 'tribe_events_cat', 'name': 'Conference', 'slug': 'conference'},
        {'key': 'ev-workshops', 'taxonomy': 'tribe_events_cat', 'name': 'Workshops', 'slug': 'workshops'},
        {'key': 'ev-community', 'taxonomy': 'tribe_events_cat', 'name': 'Community', 'slug': 'community'},
    ]
    pages = [('home', 'Home', 'home', g_home, e_home), ('speakers', 'Speakers', 'speakers', g_speakers, e_speakers),
             ('agenda', 'Agenda', 'agenda', g_agenda, e_agenda), ('tickets', 'Tickets', 'tickets', g_tickets, e_tickets),
             ('venue', 'Venue', 'venue', g_venue, e_venue), ('sponsors', 'Sponsors', 'sponsors', g_sponsors, e_sponsors),
             ('become-sponsor', 'Become a Sponsor', 'become-a-sponsor', g_become, e_become), ('faq', 'FAQ', 'faq', g_faq, e_faq),
             ('contact', 'Contact', 'contact', g_contact, e_contact)]
    content = [
        # The Events Calendar: venues and organizer first, events link to them.
        {'key': 'v-lcc', 'type': 'tribe_venue', 'title': 'Lisbon Congress Centre', 'slug': 'lisbon-congress-centre',
         'content': b.p('On the Tagus riverfront, ten minutes from the airport. Step-free access throughout.'),
         'venue': {'Address': 'Praça das Indústrias', 'City': 'Lisboa', 'Zip': '1300-307', 'Country': 'Portugal', 'Phone': '+351 21 360 1500', 'ShowMap': True, 'ShowMapLink': True}},
        {'key': 'v-lx', 'type': 'tribe_venue', 'title': 'LX Factory', 'slug': 'lx-factory',
         'content': b.p('A converted textile mill with eight workshop rooms and a courtyard.'),
         'venue': {'Address': 'Rua Rodrigues de Faria 103', 'City': 'Lisboa', 'Zip': '1300-501', 'Country': 'Portugal', 'ShowMap': True, 'ShowMapLink': True}},
        {'key': 'o-pulse', 'type': 'tribe_organizer', 'title': 'Pulse Conference', 'slug': 'pulse-conference',
         'content': b.p('The team behind Pulse, Lisbon\'s conference for people who design and build the web.'),
         'organizer': {'Email': 'hello@pulseconf.io', 'Phone': '+351 21 340 2290', 'Website': '{{home:}}'}},
    ]
    events = [
        ('ev-meetup', 'Design Systems Meetup (pre-Pulse)', '2027-04-22', 0, '18:30', '21:30', 'Free', 'v-lx', 'ev-community', 'crowd',
         'An evening of lightning talks on design tokens and component libraries. Free, but register so we can order enough pastéis de nata.'),
        ('ev-workshops', 'Pulse Workshop Day', '2027-05-12', 0, '09:00', '13:00', '€300 add-on', 'v-lx', 'ev-workshops', 'workshop',
         'Eight hands-on workshops capped at 24 seats each. Included with the Conference + Workshop pass.'),
        ('ev-day1', 'Pulse 2027 · Day 1', '2027-05-13', 0, '09:00', '18:00', 'From €79', 'v-lcc', 'ev-conference', 'hero-stage',
         'Opening keynote, design systems, the edge-first web and an afternoon panel on brand in motion.'),
        ('ev-party', 'Pulse After-party', '2027-05-13', 0, '19:30', '23:30', 'Included with Conference passes', 'v-lx', 'ev-community', 'networking',
         'Food trucks, a DJ in the courtyard and the best conversations of the week.'),
        ('ev-day2', 'Pulse 2027 · Day 2', '2027-05-14', 0, '09:30', '17:00', 'From €79', 'v-lcc', 'ev-conference', 'crowd',
         'Research, open source, AI evaluation and the closing keynote.'),
    ]
    for key, title, date, days, start, end, cost, venue, cat, img, desc in events:
        content.append({'key': key, 'type': 'tribe_events', 'title': title, 'slug': key.replace('ev-', 'pulse-'), 'featured': img,
                        'content': b.join(b.p(desc), b.buttons(b.button('Get tickets', '{{page:tickets}}'))), 'terms': {'tribe_events_cat': [cat]},
                        'event': {'date': date, 'days': days, 'start': start, 'end': end, 'cost': cost, 'venue': venue, 'organizers': ['o-pulse'],
                                  'url': '{{page:tickets}}'}})
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
    for pr in PASSES:
        item = {'key': pr['key'], 'name': pr['name'], 'slug': pr['name'].lower().replace(' + ', '-').replace(' ', '-').replace('(', '').replace(')', ''),
                'price': pr['price'], 'categories': ['pc-passes'], 'image': pr['image'], 'virtual': True, 'menu_order': pr['menu_order'],
                'featured': pr.get('featured', False), 'short': pr['short'],
                'description': '<p>%s</p><ul>%s</ul><p>Passes are personal and can be transferred for free until 7 May 2027. Prices include 23%% Portuguese VAT.</p>' % (
                    pr['short'], ''.join('<li>%s</li>' % i for i in pr['inc']))}
        if pr.get('sale'):
            item['sale'] = pr['sale']
        products.append(item)
    menus = [{'name': 'Pulse menu', 'location': 'primary', 'items': [
        {'title': 'Speakers', 'page': 'speakers'}, {'title': 'Agenda', 'page': 'agenda'}, {'title': 'Tickets', 'page': 'tickets'},
        {'title': 'Venue', 'page': 'venue'}, {'title': 'Sponsors', 'page': 'sponsors'}, {'title': 'Events', 'url': '{{home:events/}}'},
        {'title': 'Blog', 'page': 'blog'}]}]
    settings = {'front_page': 'home', 'blog_page': 'blog', 'style': 'pulse', 'motion': 'dynamic',
                'parts': {'header': {'content': g_header()}, 'header-shop': {'content': g_header()}, 'header-transparent': {'content': g_header()}, 'footer': {'content': g_footer()}},
                'options': {'posts_per_page': 9, 'blogname': 'Pulse', 'blogdescription': 'The conference for people who design and build the web.'},
                'woocommerce': {'options': {'woocommerce_currency': 'EUR', 'woocommerce_currency_pos': 'left', 'woocommerce_price_num_decimals': '0',
                                            'woocommerce_coming_soon': 'no', 'woocommerce_store_pages_only': 'no',
                                            'woocommerce_enable_guest_checkout': 'yes',
                                            'woocommerce_cod_settings': {'enabled': 'yes', 'title': 'Pay by bank transfer on invoice'}}}}
    if builder == 'elementor':
        settings['elementor_kit'] = e.kit(container_width=1200, button_radius=999, field_radius=14, button_padding=(14, 24))
    return {'version': 1, 'media': media, 'terms': terms, 'content': content, 'products': products, 'menus': menus, 'settings': settings}


DEMO = {
    'id': ID, 'title': 'Pulse', 'tagline': 'A conference site with speakers, agenda, ticket sales and an events calendar.',
    'niche': ['events', 'conference', 'meetup', 'tickets'], 'type': 'pro',
    'thumbnail': 'demo:thumbnail.webp',
    'preview': {'desktop': 'demo:preview/desktop.webp', 'tablet': 'demo:preview/tablet.webp', 'mobile': 'demo:preview/mobile.webp'},
    'pages': ['Home', 'Speakers', 'Agenda', 'Tickets', 'Venue', 'Sponsors', 'Become a Sponsor', 'Events', 'Blog', 'FAQ', 'Contact'],
    'highlights': ['5 calendar events', '4 ticket passes', '8 speakers', 'Light & dark'],
    'motion': 'dynamic',
    'plugins': [{'slug': 'the-events-calendar', 'name': 'The Events Calendar', 'source': 'wporg', 'state': 'required'},
                {'slug': 'woocommerce', 'name': 'WooCommerce', 'source': 'wporg', 'state': 'required'}],
}
