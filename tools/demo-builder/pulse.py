"""Pulse — conference / events demo (Pro). Research and direction: docs/demos/pulse.md.

Companies and people are fictional. Event dates are fixed (13–14 May 2027) so the copy,
the agenda and The Events Calendar always agree.
"""
import blocks as b
import demo_forms as forms
from ess_ui import html
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


def g_cta():
    return b.section(b.group(b.group(b.h('Early-bird tickets end 22 March', size='h2', color='on-primary'),
                                     b.p('Save €150 on Conference passes. Prices go up at 23:59 Lisbon time.', color='on-primary'), layout='default'),
                             b.buttons(b.button('Get your ticket →', '{{page:tickets}}', style='wpe-dark')),
                             bg='primary', className='wpe-card wpe-px-cta', layout='flex', wrap='wrap', justify='space-between', valign='center'),
                     className='wpe-section wpe-section--sm')


def g_footer():
    return g_cta() + '\n\n' + b.footer('Two days of design, engineering and AI talks. %s at the Lisbon Congress Centre.' % DATES,
                    [('Event', [('Speakers', '{{page:speakers}}'), ('Agenda', '{{page:agenda}}'), ('Tickets', '{{page:tickets}}'), ('Events calendar', '{{home:events/}}')]),
                     ('Attend', [('Venue & hotels', '{{page:venue}}'), ('FAQ', '{{page:faq}}'), ('My tickets', '{{woo:myaccount}}'), ('Contact', '{{page:contact}}')]),
                     ('Company', [('Sponsors', '{{page:sponsors}}'), ('About', '{{page:about}}'), ('Become a sponsor', '{{page:become-sponsor}}'), ('Call for speakers', '{{page:cfp}}'), ('Code of conduct', '{{page:code-of-conduct}}'), ('Gallery', '{{page:gallery}}'), ('Blog', '{{page:blog}}')])],
                    ('© Pulse Conference Lda. All rights reserved.', 'Designed &amp; built with <a href="https://wpessential.org/">Hi WPEssential</a>'),
                    band=('See you in Lisbon.', 'Early-bird pricing until 22 March.', 'Get tickets', '{{page:tickets}}')).replace('wpe-footer__newsletter', 'wpe-footer__newsletter wpe-px-bigband')


def g_speaker_card(s, topic=True):
    k, name, role, company, talk, track = s
    return b.group(b.image(k, 'Portrait of ' + name, aspect='1/1', style='wpe-shadow'),
                   b.h(name, level=3, size='h5'), b.p('%s · %s' % (role, company), color='text-muted', size='small'),
                   b.p(track, className='wpe-px-tag wpe-px-tag--%s' % track.lower()),
                   b.p(talk, size='small') if topic else '', layout='default', className='wpe-speaker wpe-px-speaker')


def g_session(t):
    time, length, track, room, title, sp = t
    return b.group(
        b.group(b.p('<strong>%s</strong>' % time), b.p(length, color='text-muted', size='small'), layout='default', className='wpe-session__time'),
        b.group(b.p('<span class="wpe-px-tag wpe-px-tag--%s">%s</span> <span class="wpe-px-room">%s</span>' % (track.lower().split()[0], track, room), className='wpe-px-session__tags'),
                b.h(title, level=3, size='h5'),
                b.p(speaker_names(sp), color='text-muted', size='small') if sp else '', layout='default', className='wpe-session__body'),
        layout='flex', wrap='nowrap', valign='top', className='wpe-card wpe-session wpe-px-session')


def g_passes():
    cards = []
    for p in PASSES:
        name = p['name'].replace(' Pass', '')
        price = '<strong>€%s</strong>' % p.get('sale', p['price']) + (' <s>€%s</s>' % p['price'] if p.get('sale') else '')
        top = [b.h(name, level=3, size='h5')] + ([b.p('Most popular', className='wpe-px-badge')] if p.get('featured') else [])
        cards.append(b.group(b.group(*top, layout='flex', justify='space-between', valign='center'),
                             b.p(price, className='wpe-px-pass__price'), b.p(p['short'], size='small', className='wpe-px-pass__desc'),
                             b.ul(p['inc'], style='wpe-checklist', className='wpe-px-pass__inc'),
                             b.buttons(b.button('Get this ticket' if p.get('featured') else 'Choose', '{{home:}}?add-to-cart={{post_id:%s}}' % p['key'],
                                                style=None if p.get('featured') else 'wpe-outline'), className='wpe-px-pass__cta'),
                             layout='default', className='wpe-px-pass' + (' is-featured' if p.get('featured') else '')))
    return b.group(*cards, layout='grid', columns=4, className='wpe-px-passes')


def g_home():
    collage = html('<div class="wpe-px-collage"><span class="wpe-px-live">● Live in Lisbon</span>'
                   '<img src="{{media:sp-3}}" alt="Mei Lin Chen" class="wpe-px-collage__a"><img src="{{media:sp-1}}" alt="Amara Okafor" class="wpe-px-collage__b">'
                   '<img src="{{media:sp-2}}" alt="Rohan Mehta" class="wpe-px-collage__c"><span class="wpe-px-more">60+ speakers</span></div>')
    countdown = html('<div class="wpe-px-count" data-date="2027-05-13T09:00:00+01:00"><span><b>208</b>days</span><span><b>08</b>hours</span>'
                     '<span><b>41</b>min</span><span><b>17</b>sec</span></div>')
    hero = b.section(b.columns(
        b.column(b.p('%s · Lisbon Congress Centre' % DATES, className='wpe-px-chip'),
                 b.h('Design.<br>Code.<br><mark>Ship.</mark>', level=1, size='display', className='wpe-px-hero__title'),
                 b.p('Two days, 60 speakers and 3 stages for people who design and build the web. Talks, workshops and the best after-party in Europe.', color='text-muted'),
                 b.buttons(b.button('Get tickets — from €79', '{{page:tickets}}'), b.button('Watch 2026 recap', '{{page:blog}}', style='wpe-outline')),
                 countdown, width='55%', valign='center'),
        b.column(collage, width='45%', valign='center'), className='wpe-gap-lg'), className='wpe-section wpe-px-hero')
    stats = b.section(b.group(*[b.group(b.p(n, className='wpe-px-stat__n'), b.p(t, color='text-muted', size='small'), layout='default', className='wpe-px-stat')
                                for n, t in [('2', 'days of talks'), ('60+', 'speakers'), ('3', 'stages + workshops'), ('2,400', 'attendees')]],
                              layout='grid', columns=4), className='wpe-section wpe-section--sm')
    logos = b.group(b.p('Backed by teams who build the web', align='center', size='small', className='wpe-px-logos__label'),
                    b.p(''.join('<span>%s</span>' % n for _, names in SPONSORS for n in names), className='wpe-px-logos__row'),
                    align='full', bg='ink', color='on-ink', className='wpe-px-logos', layout='default')
    def head(over, title, label, href):
        return b.group(b.group(b.p(over, className='wpe-px-chip'), b.h(title, size='h2'), layout='default'),
                       b.buttons(b.button(label + ' →', href, style='wpe-outline')), layout='flex', wrap='wrap', justify='space-between', valign='bottom', className='wpe-px-head')
    speakers = b.section(head('Speakers', 'Learn from the people building what\'s next', 'All 60 speakers', '{{page:speakers}}'),
                         b.group(*[g_speaker_card(s, False) for s in SPEAKERS], layout='grid', columns=4))
    agenda = b.section(head('Agenda', 'Day 1 · Thursday 13 May', 'Full agenda', '{{page:agenda}}'),
                       b.p('<span class="is-active">Day 1 · 13 May</span><a href="{{page:agenda}}">Day 2 · 14 May</a><a href="{{page:agenda}}">Workshops · 12 May</a>', className='wpe-px-tabs'),
                       *[g_session(t) for t in DAY1[:4]], bg='surface-alt')
    tickets = b.section(b.p('Tickets', className='wpe-px-chip', align='center'), b.h('Pick your pass', size='h2', align='center'),
                        b.p('All prices include VAT. Student and diversity tickets are available on request.', align='center', color='text-muted'),
                        g_passes())
    venue = b.section(b.columns(
        b.column(b.image('venue', 'Lisbon Congress Centre at dusk', aspect='4/3', style='wpe-shadow'), width='55%'),
        b.column(b.p('Venue', className='wpe-px-chip'), b.h('Lisbon Congress Centre, on the river', size='h2'),
                 b.p('Ten minutes from the airport on the metro, with step-free access throughout and a terrace over the Tagus.', color='text-muted'),
                 b.ul(['Praça das Indústrias, 1300-307 Lisboa', 'Doors 08:00 · Talks 09:00 – 18:00', 'Hearing loops, live captions, quiet rooms'], style='wpe-checklist'),
                 b.buttons(b.button('Plan your trip →', '{{page:venue}}', style='wpe-outline')), valign='center'), valign='center', className='wpe-gap-lg'))
    quotes = b.section(b.group(b.p('2026 in their words', className='wpe-px-chip'), b.h('“The best two days of my year.”', size='h2'), layout='default'),
                       b.columns(*[b.column(b.image(img, alt, aspect='3/2'), b.p('“', className='wpe-px-qmark'), b.p(q), b.p('<strong>%s</strong>' % who, size='small'), style='wpe-card')
                                   for img, alt, q, who in [
                                       ('crowd', 'Audience at Pulse 2026', 'I left with three new collaborators and a notebook full of ideas I shipped the following month.', 'Priya S., Product Designer'),
                                       ('workshop', 'Workshop at Pulse 2026', 'The workshops were hands-on and small. I learned more in four hours than in a semester.', 'Marco R., Frontend Lead'),
                                       ('networking', 'Attendees networking', 'Tight talks, zero fluff, and the after-party was unreal. Already bought my 2027 ticket.', 'Hannah W., CTO')]]))
    return b.join(hero, stats, logos, speakers, agenda, tickets, venue, quotes)


def g_title(over, title, lead):
    return b.section(b.group(b.p('<a href="{{home:}}">Home</a> › %s' % over, className='wpe-px-crumb'), b.p(over, className='wpe-px-chip'),
                             b.h(title, level=1, size='display', className='wpe-px-title'), b.p(lead, color='text-muted'), layout='default'),
                     className='wpe-section wpe-section--sm wpe-px-page-head', content='1200px')


def g_speakers():
    return b.join(g_title('Speakers', '60 speakers, one stage-worthy idea each', 'Designers, engineers and founders who have shipped the things they talk about.'),
                  b.section(b.group(*[g_speaker_card(s) for s in SPEAKERS], layout='grid', columns=4), className='wpe-section wpe-section--sm'),
                  b.section(b.h('Want to speak in 2028?', size='h3', align='center'),
                            b.p('The call for proposals opens in September. Send us a short pitch and a past talk.', align='center', color='text-muted'),
                            b.buttons(b.button('Submit a proposal', '{{page:cfp}}', style='wpe-outline'), justify='center'), bg='surface-alt', content='720px'))


def g_agenda():
    tabs = b.p('<a class="is-active" href="#day-1">Day 1 · Thu 13</a><a href="#day-2">Day 2 · Fri 14</a><a href="{{home:events/}}">Workshops · Wed 12</a>', className='wpe-px-tabs')
    legend = b.p('Tracks: <span class="wpe-px-tag wpe-px-tag--design">Design</span> <span class="wpe-px-tag wpe-px-tag--engineering">Engineering</span> '
                 '<span class="wpe-px-tag wpe-px-tag--ai">AI</span> <span class="wpe-px-tag wpe-px-tag--product">Product</span>', className='wpe-px-legend')
    return b.join(g_title('Agenda', 'Two days, three stages', 'Times are Lisbon time (WEST, UTC+1). Workshops run on 12 May at LX Factory.'),
                  b.section(tabs, legend, b.h('Day 1 · Thursday 13 May', size='h3', className='wpe-px-day', anchor='day-1'), *[g_session(t) for t in DAY1],
                            b.h('Day 2 · Friday 14 May', size='h3', className='wpe-px-day', anchor='day-2'), *[g_session(t) for t in DAY2],
                            b.buttons(b.button('Add to calendar', '{{home:events/}}', style='wpe-outline'), b.button('Get tickets', '{{page:tickets}}')),
                            className='wpe-section wpe-section--sm'))


def g_tickets():
    rows = [['Live stream of both stages', '✓', '✓', '✓', '✓'], ['In-person talks, both days', '—', '✓', '✓', '✓'],
            ['Lunch, coffee and after-party', '—', '✓', '✓', '✓'], ['Recordings for 12 months', '✓', '✓', '✓', '✓'],
            ['Four-hour workshop', '—', '—', '✓', 'Add-on'], ['Named invoice and team host', '—', '—', '—', '✓']]
    return b.join(g_title('Tickets', 'Choose your pass', 'Early-bird pricing until 22 March. Group discounts apply automatically from five passes.'),
                  b.section(g_passes(), className='wpe-section wpe-section--sm'),
                  b.section(b.h('What is included', size='h2'), b.table(['', 'Online', 'Conference', '+ Workshop', 'Team'], rows), bg='surface-alt'),
                  b.section(b.h('Ticket questions', size='h2'), *[b.details(q, b.p(a)) for q, a in FAQ[:3]], content='860px'))


HOTELS = [('Myriad by SANA', '2 min walk · river view', '€189'), ('Tivoli Oriente', '6 min walk · rooftop pool', '€149'), ('Moxy Lisboa Oriente', '8 min walk · budget friendly', '€119')]


def g_venue():
    routes = [('From the airport', 'Red metro line to Oriente, change to the green line — 18 minutes, €1.80.'),
              ('From the city centre', 'Tram 15 to Belém, then a five-minute walk along the river.'),
              ('By bike', 'Gira bike stations at the main entrance. Secure bike parking inside.')]
    return b.join(
        g_title('Venue &amp; travel', 'Lisbon Congress Centre', 'On the Tagus riverfront, 10 minutes from the airport. Step-free access throughout.'),
        b.section(b.image('venue', 'Lisbon Congress Centre at dusk', aspect='2/1', className='wpe-px-rounded'), className='wpe-section wpe-section--sm'),
        b.section(b.group(b.p('Getting here', className='wpe-px-chip'), b.h('Metro, tram or bike', size='h2'), layout='default'),
                  b.group(*[b.group(b.p('●', className='wpe-px-dot'), b.h(t, level=3, size='h5'), b.p(d, color='text-muted', size='small'), layout='default', className='wpe-px-tile')
                            for t, d in routes], layout='grid', columns=3), className='wpe-section wpe-section--sm'),
        b.section(b.group(b.p('Partner hotels', className='wpe-px-chip'), b.h('Stay close, pay less', size='h2'),
                          b.p('Use code PULSE27 for the rates below. Rooms held until 10 April.', color='text-muted'), layout='default'),
                  *[b.group(b.group(b.h(n, level=3, size='h5'), b.p(d, color='text-muted', size='small'), layout='default'),
                            b.p('<span class="wpe-px-badge">from %s</span> <a class="wpe-px-mini-btn" href="{{page:contact}}">Book →</a>' % p, className='wpe-px-row__end'),
                            layout='flex', wrap='nowrap', justify='space-between', valign='center', className='wpe-px-row') for n, d, p in HOTELS],
                  className='wpe-section wpe-section--sm'))


def g_sponsors():
    labels = {'Platinum': 'Headline partners', 'Gold': 'Gold partners', 'Community': 'Community partners'}
    cols = {'Platinum': 2, 'Gold': 4, 'Community': 6}
    tiers = [b.group(b.p(labels.get(tier, tier), className='wpe-px-chip'),
                     b.group(*[b.p(n, className='wpe-px-logo wpe-px-logo--%s' % tier.lower()) for n in names], layout='grid', columns=cols.get(tier, 4), className='wpe-px-logos-grid'),
                     layout='default', className='wpe-px-tier') for tier, names in SPONSORS]
    return b.join(g_title('Sponsors', 'The teams making Pulse possible', 'Pulse is independent. Our partners fund scholarships, live captions and free recordings.'),
                  b.section(*tiers, className='wpe-section wpe-section--sm'))


def g_become():
    hero = b.section(b.columns(
        b.column(b.p('Partner with us', className='wpe-px-chip'), b.h('Put your brand in front of 2,400 builders', level=1, size='display', className='wpe-px-title'),
                 b.p('72% of attendees influence tooling decisions at their company. Booths, workshops and talk slots available.', color='text-muted'), valign='center'),
        b.column(b.image('networking', 'Attendees talking at Pulse', aspect='3/2', className='wpe-px-rounded'), valign='center'), className='wpe-gap-lg'),
        className='wpe-section wpe-px-page-head')
    packs = b.section(b.p('Packages', className='wpe-px-chip'), b.h('Three ways to partner', size='h2'),
                      b.group(*[b.group(b.h(n, level=3, size='h5'), b.p(price, className='wpe-px-pass__price'), b.ul(items, style='wpe-checklist', className='wpe-px-pass__inc'),
                                        layout='default', className='wpe-px-pass') for n, price, items in PACKAGES], layout='grid', columns=3), bg='surface-alt')
    form = b.section(b.p('Get in touch', className='wpe-px-chip'), b.h('Request the sponsor deck', size='h2'),
                     b.shortcode('[wpforms id="{{form:sponsor}}" title="false"]'), className='wpe-section wpe-px-sponsor-form')
    return b.join(hero, packs, form)


def g_faq():
    return b.join(g_title('FAQ', 'Questions, answered', 'Still stuck? Write to tickets@pulseconf.io and we reply within a day.'),
                  b.section(*[b.details(q, b.p(a)) for q, a in FAQ], className='wpe-section wpe-section--sm wpe-px-faq'))


def g_contact():
    cards = [('General', 'tickets@pulseconf.io'), ('Speaking', 'speakers@pulseconf.io'), ('Sponsorship', 'partners@pulseconf.io'), ('Phone', '+351 21 340 2290')]
    side = [b.group(b.p('●', className='wpe-px-dot'), b.group(b.p('<strong>%s</strong>' % t), b.p(d, color='text-muted', size='small'), layout='default'),
                    layout='flex', wrap='nowrap', valign='center', className='wpe-px-tile wpe-px-tile--row') for t, d in cards]
    return b.join(g_title('Contact', 'Talk to the Pulse team', 'Tickets, press, speaking or sponsorship — pick a topic and we route it to the right person.'),
                  b.section(b.columns(b.column(b.shortcode('[wpforms id="{{form:contact}}" title="false"]'), width='62%', className='wpe-px-form-card'),
                                      b.column(*side, width='38%'), className='wpe-gap-lg'), className='wpe-section wpe-section--sm'),
                  b.section(html('<div class="demo-map"><iframe title="Lisbon Congress Centre on Google Maps" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=Lisbon%20Congress%20Centre&amp;z=15&amp;output=embed"></iframe></div>'), className='wpe-section wpe-section--sm'))


# Elementor ------------------------------------------------------------------------------

def e_speaker(s, topic=True):
    k, name, role, company, talk, track = s
    return e.image_box(k, name, '%s · %s%s' % (role, company, ('<br>' + talk) if topic else ''), height=320, people=True)


def e_session(t):
    time, length, track, room, title, sp = t
    return e.row(e.col(e.heading(time, 3, size_px=20), e.text(length, size_px=13), width=12),
                 e.col(e.text('<span class="wpe-px-tag wpe-px-tag--%s">%s</span> <span class="wpe-px-room">%s</span>' % (track.lower().split()[0], track, room), size_px=12),
                       e.heading(title, 3, size_px=20), e.text(speaker_names(sp), size_px=13) if sp else e.spacer(1), width=88, gap=6),
                 gap=24, pad=20, bg='surface', radius=16, border='border', mobile_direction='row')


def e_title(over, title, lead):
    return e.container(e.text('Home › ' + over, size_px=12), e.overline(over), e.heading(title, 1, size_px=72), e.text(lead, size_px=16), pad=(72, 24, 32, 24), gap=12)


def e_passes():
    cards = []
    for p in PASSES:
        feat = p.get('featured')
        price = '€%s' % p.get('sale', p['price'])
        cards.append(e.col(e.heading(p['name'].replace(' Pass', ''), 3, 'on-ink' if feat else 'text', size_px=20),
                           e.heading(price, 4, 'on-ink' if feat else 'text', size_px=40),
                           e.text(p['short'], 'on-ink-muted' if feat else 'text-muted', size_px=14),
                           e.icon_list(p['inc'], color_slug='on-ink' if feat else 'text'),
                           e.button('Get this ticket' if feat else 'Choose', '{{home:}}?add-to-cart={{post_id:%s}}' % p['key'], 'primary' if feat else 'outline'),
                           gap=12, pad=24, radius=20, border=None if feat else 'border', bg='ink' if feat else 'surface'))
    return e.grid(*cards, columns=4)


def e_home():
    return [
        e.container(e.row(
            e.col(e.overline('%s · Lisbon Congress Centre' % DATES), e.heading('Design.<br>Code.<br><mark>Ship.</mark>', 1, size_px=120),
                  e.text('Two days, 60 speakers and 3 stages for people who design and build the web. Talks, workshops and the best after-party in Europe.', size_px=17),
                  e.row(e.button('Get tickets — from €79', '{{page:tickets}}'), e.button('Watch 2026 recap', '{{page:blog}}', 'outline'), gap=12, wrap=True, mobile_direction='row'),
                  width=55, justify='center'),
            e.col(e.image_fixed('sp-3', 'Mei Lin Chen on stage', 300, radius=18), e.row(e.image_fixed('sp-1', 'Amara Okafor', 200, radius=18), e.image_fixed('sp-2', 'Rohan Mehta', 200, radius=18), gap=16, mobile_direction='row'),
                  width=45, justify='center'), gap=48), pad=(72, 24, 56, 24)),
        e.container(e.grid(e.counter(2, 'days of talks'), e.counter(60, 'speakers', '+'), e.counter(3, 'stages + workshops'), e.counter(2400, 'attendees'), columns=4),
                    pad=(56, 24, 56, 24)),
        e.container(e.text('Backed by teams who build the web', 'on-ink-muted', align='center'), e.heading('   '.join(n for _, names in SPONSORS for n in names), 3, 'on-ink', align='center', size_px=22), bg='ink', pad=(28, 24, 28, 24)),
        e.container(e.overline('Speakers'), e.heading("Learn from the people building what's next"),
                    e.grid(*[e_speaker(s, False) for s in SPEAKERS], columns=4), e.button('All speakers', '{{page:speakers}}', 'outline'), pad=(80, 24, 80, 24)),
        e.container(e.overline('Agenda'), e.heading('Day 1 · Thursday 13 May'), *[e_session(t) for t in DAY1[:4]], e.button('Full agenda', '{{page:agenda}}', 'outline'),
                    bg='surface-alt', pad=(80, 24, 80, 24), gap=16),
        e.container(e.overline('Tickets', align='center'), e.heading('Pick your pass', align='center'),
                    e.text('All prices include VAT. Student and diversity tickets are available on request.', align='center'),
                    e_passes(), pad=(80, 24, 80, 24)),
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
    ]


def e_speakers():
    return [e_title('Speakers', '60 speakers, one stage-worthy idea each', 'Designers, engineers and founders who have shipped the things they talk about.'),
            e.container(e.grid(*[e_speaker(s) for s in SPEAKERS], columns=4), pad=(24, 24, 80, 24)),
            e.container(e.heading('Want to speak in 2028?', 3, align='center'), e.text('The call for proposals opens in September. Send us a short pitch and a past talk.', align='center'),
                        e.button('Get in touch', '{{page:contact}}', 'outline', align='center'), bg='surface-alt', pad=(72, 24, 72, 24))]


def e_agenda():
    tabs = e.text('<a href="#day-1"><strong>Day 1 · Thu 13</strong></a> &nbsp;·&nbsp; <a href="#day-2">Day 2 · Fri 14</a> &nbsp;·&nbsp; <a href="{{home:events/}}">Workshops · Wed 12</a>', size_px=14)
    legend = e.text('Tracks: Design · Engineering · AI · Product', size_px=12)
    return [e_title('Agenda', 'Two days, three stages', 'Times are Lisbon time (WEST, UTC+1). Workshops run on 12 May at LX Factory.'),
            e.container(tabs, legend, e.text('<h3 id="day-1">Day 1 · Thursday 13 May</h3>'), *[e_session(t) for t in DAY1],
                        e.text('<h3 id="day-2">Day 2 · Friday 14 May</h3>'), *[e_session(t) for t in DAY2],
                        e.row(e.button('Add to calendar', '{{home:events/}}', 'outline'), e.button('Get tickets', '{{page:tickets}}'), gap=12, mobile_direction='row', wrap=True),
                        pad=(24, 24, 96, 24), gap=12)]


def e_tickets():
    return [e_title('Tickets', 'Choose your pass', 'Early-bird pricing until 22 March. Group discounts apply automatically from five passes.'),
            e.container(e_passes(), pad=(24, 24, 56, 24)),
            e.container(e.heading('What is included'), e.grid(*[e.container(e.heading(p['name'], 3, size_px=22), e.icon_list(p['inc']), inner=True, pad=28, radius=20, border='border', bg='surface')
                                                               for p in PASSES], columns=4), bg='surface-alt', pad=(72, 24, 72, 24)),
            e.container(e.heading('Ticket questions'), e.accordion(FAQ[:3]), pad=(72, 24, 72, 24))]


def e_venue():
    return [e_title('Venue & travel', 'Lisbon Congress Centre', 'On the Tagus riverfront, 10 minutes from the airport. Step-free access throughout.'),
            e.container(e.image_fixed('venue', 'Lisbon Congress Centre at dusk', 520, radius=24), pad=(8, 24, 48, 24)),
            e.container(e.overline('Getting here'), e.heading('Metro, tram or bike'),
                        e.grid(*[e.icon_box(i, t, d) for i, t, d in [('fas fa-plane', 'From the airport', 'Red metro line to Oriente, change to the green line — 18 minutes, €1.80.'),
                                                                     ('fas fa-train-tram', 'From the city centre', 'Tram 15 to Belém, then a five-minute walk along the river.'),
                                                                     ('fas fa-bicycle', 'By bike', 'Gira bike stations at the main entrance. Secure bike parking inside.')]], columns=3), pad=(48, 24, 48, 24)),
            e.container(e.overline('Partner hotels'), e.heading('Stay close, pay less'), e.text('Use code PULSE27 for the rates below. Rooms held until 10 April.'),
                        *[e.row(e.col(e.heading(n, 3, size_px=18), e.text(d, size_px=13), gap=2), e.button('from %s · Book' % p, '{{page:contact}}', 'outline'),
                                gap=16, pad=16, radius=14, border='border', bg='surface', mobile_direction='row') for n, d, p in HOTELS], pad=(48, 24, 96, 24), gap=12)]


def e_sponsors():
    labels = {'Platinum': 'Headline partners', 'Gold': 'Gold partners', 'Community': 'Community partners'}
    cols = {'Platinum': 2, 'Gold': 4, 'Community': 6}
    out = [e_title('Sponsors', 'The teams making Pulse possible', 'Pulse is independent. Our partners fund scholarships, live captions and free recordings.')]
    for tier, names in SPONSORS:
        out.append(e.container(e.overline(labels.get(tier, tier)),
                               e.grid(*[e.container(e.heading(n, 3, align='center', size_px=32 if tier == 'Platinum' else 20), inner=True, bg='surface-alt', radius=16,
                                                    pad=40 if tier == 'Platinum' else 20, justify='center') for n in names], columns=cols.get(tier, 4)), pad=(24, 24, 24, 24)))
    return out


def e_become():
    return [e.container(e.row(e.col(e.overline('Partner with us'), e.heading('Put your brand in front of 2,400 builders', 1, size_px=64),
                                    e.text('72% of attendees influence tooling decisions at their company. Booths, workshops and talk slots available.'), width=50, justify='center'),
                              e.col(e.image_fixed('networking', 'Attendees talking at Pulse', 400, radius=24), width=50), gap=56), pad=(72, 24, 72, 24)),
            e.container(e.overline('Packages'), e.heading('Three ways to partner'),
                        e.grid(*[e.container(e.heading(n, 3, size_px=20), e.heading(price, 2, size_px=40), e.icon_list(items), inner=True, pad=28, radius=20, bg='surface')
                                 for n, price, items in PACKAGES], columns=3), bg='surface-alt', pad=(72, 24, 72, 24)),
            e.container(e.overline('Get in touch'), e.heading('Request the sponsor deck'), e.shortcode('[wpforms id="{{form:sponsor}}" title="false"]'), pad=(72, 24, 96, 24))]


def e_faq():
    return [e_title('FAQ', 'Questions, answered', 'Still stuck? Write to tickets@pulseconf.io and we reply within a day.'), e.container(e.accordion(FAQ), pad=(24, 24, 96, 24))]


def e_contact():
    cards = [('fas fa-envelope', 'General', 'tickets@pulseconf.io'), ('fas fa-microphone', 'Speaking', 'speakers@pulseconf.io'),
             ('fas fa-handshake', 'Sponsorship', 'partners@pulseconf.io'), ('fas fa-phone', 'Phone', '+351 21 340 2290')]
    return [e_title('Contact', 'Talk to the Pulse team', 'Tickets, press, speaking or sponsorship — pick a topic and we route it to the right person.'),
            e.container(e.row(e.col(e.shortcode('[wpforms id="{{form:contact}}" title="false"]'), width=62, pad=28, radius=20, border='border', bg='surface'),
                              e.col(*[e.container(e.icon_box(i, t, d), inner=True, bg='surface-alt', radius=16, pad=18) for i, t, d in cards], width=38, gap=12), gap=40),
                        pad=(24, 24, 72, 24)),
            e.container(e.map_widget('Lisbon Congress Centre, Lisbon'), pad=(0, 24, 96, 24))]


# Package --------------------------------------------------------------------------------

# About, gallery, call for speakers and code of conduct (standard on Envato event demos and real conference sites).
EDITIONS = [('2023', '420 attendees', 'First edition in a converted LX Factory warehouse.'),
            ('2024', '900 attendees', 'Moved to the riverfront; added the workshop day.'),
            ('2025', '1,600 attendees', 'Three tracks, 42 speakers and a sold-out after-party.'),
            ('2026', '2,400 attendees', '58 talks, live captions on every stage and our first scholarship fund.')]
NUMBERS = [('2,400', 'attendees in 2026'), ('38', 'countries'), ('4.8/5', 'average talk rating'), ('120', 'scholarship tickets')]
VALUES = [('fas fa-microphone', 'Talks with receipts', 'Every speaker has shipped what they talk about. No sales pitches on stage.'),
          ('fas fa-universal-access', 'Accessible by default', 'Live captions, step-free venues, quiet rooms and a free sign-language interpreter on request.'),
          ('fas fa-hand-holding-heart', 'Open to everyone', '5% of tickets fund scholarships for students and under-represented builders.')]
GALLERY = [('crowd', 'The main stage on Day 1, 2026'), ('hero-stage', 'Opening keynote under the riverfront roof'),
           ('workshop', 'Design-tokens workshop at LX Factory'), ('networking', 'Coffee and conversations between sessions'),
           ('venue', 'The Lisbon Congress Centre at sunset'), ('blog-1', 'Speakers backstage before the panel'),
           ('blog-2', 'The after-party courtyard'), ('blog-3', 'Hallway track in the sponsor hall')]
TOPICS = ['Design systems & tokens', 'Front-end architecture', 'AI in product', 'Accessibility', 'Brand & motion', 'Research & strategy']
CFP_STEPS = [('1', 'Send a pitch', 'Title, a 150-word abstract and a link to any past talk. First-time speakers welcome.'),
             ('2', 'Review', 'An anonymous panel of five reviews every proposal by 15 January.'),
             ('3', 'Coaching', 'Selected speakers get two rehearsal calls with a speaker coach.'),
             ('4', 'On stage', 'Flights, three hotel nights and a €500 honorarium are covered.')]
CONDUCT = [('Be kind and inclusive', 'Pulse is for everyone, regardless of gender, identity, age, disability, appearance, race, religion or background.'),
           ('Harassment is not tolerated', 'This includes offensive comments, deliberate intimidation, stalking, unwanted photography and unwelcome physical contact.'),
           ('It applies everywhere', 'Talks, workshops, the after-party, online chat and social media about the event.'),
           ('What happens if you break it', 'Organisers may warn you, remove you from the event without a refund or report the incident to the police.')]


def g_about():
    hist = b.group(*[b.group(b.p(y, className='wpe-px-year'), b.h(a, level=3, size='h5'), b.p(d, color='text-muted', size='small'), layout='default', className='wpe-px-tile')
                     for y, a, d in EDITIONS], layout='grid', columns=4)
    nums = b.group(*[b.group(b.p(n, className='wpe-px-num'), b.p(t, color='text-muted', size='small'), layout='default') for n, t in NUMBERS], layout='grid', columns=4, className='wpe-px-nums')
    vals = b.group(*[b.group(b.h(t, level=3, size='h5'), b.p(d, color='text-muted', size='small'), layout='default', className='wpe-px-tile') for _i, t, d in VALUES],
                   layout='grid', columns=3)
    story = b.columns(b.column(b.image('crowd', 'The Pulse main stage full of attendees', aspect='4/3', className='wpe-px-rounded')),
                      b.column(b.p('Our story', className='wpe-px-chip'), b.h('Built by people who were tired of sales-pitch conferences', size='h2'),
                               b.p('Pulse started in 2023 as a one-day meetup for 420 designers and engineers in Lisbon. Five editions later it is still independent, still run by the same four people and still has no paid keynote slots.', color='text-muted'),
                               valign='center'), valign='center', className='wpe-gap-lg')
    return b.join(g_title('About', 'The conference for people who design and build the web', 'Independent since 2023, in Lisbon every May.'),
                  b.section(story, nums, className='wpe-section wpe-section--sm'),
                  b.section(b.group(b.p('Five editions', className='wpe-px-chip'), b.h('How Pulse grew', size='h2'), layout='default'), hist, bg='surface-alt'),
                  b.section(b.group(b.p('What we stand for', className='wpe-px-chip'), b.h('Three rules we never break', size='h2'), layout='default'), vals,
                            b.buttons(b.button('Read the code of conduct', '{{page:code-of-conduct}}', style='wpe-outline'))))


def g_gallery():
    tiles = b.group(*[b.image(k, alt, aspect='4/3', className='wpe-px-rounded') for k, alt in GALLERY], layout='grid', columns=4, className='wpe-px-gallery')
    return b.join(g_title('Gallery', 'Pulse 2026 in pictures', 'Two days, 2,400 people and a lot of very good coffee. Photos by Joana Reis.'),
                  b.section(tiles, className='wpe-section wpe-section--sm'),
                  b.section(b.h('Be in the 2027 photos', size='h3', align='center'),
                            b.p('Early-bird Conference passes are on sale until 22 March.', align='center', color='text-muted'),
                            b.buttons(b.button('Get tickets', '{{page:tickets}}'), justify='center'), bg='surface-alt', content='720px'))


def g_cfp():
    steps = b.group(*[b.group(b.p(n, className='wpe-px-num'), b.h(t, level=3, size='h5'), b.p(d, color='text-muted', size='small'), layout='default', className='wpe-px-tile')
                      for n, t, d in CFP_STEPS], layout='grid', columns=4)
    topics = b.p(''.join('<span class="wpe-px-tag">%s</span> ' % t for t in TOPICS), className='wpe-px-topics')
    return b.join(g_title('Call for speakers', 'Speak at Pulse 2028', 'Proposals are open until 30 November. We especially want first-time speakers.'),
                  b.section(b.group(b.p('How it works', className='wpe-px-chip'), b.h('From pitch to stage', size='h2'), layout='default'), steps,
                            b.h('Topics we are looking for', level=3, size='h4'), topics, className='wpe-section wpe-section--sm'),
                  b.section(b.columns(b.column(b.h('Send your proposal', size='h2'), b.p('It takes about ten minutes. We reply to every proposal.', color='text-muted'),
                                               b.image('hero-stage', 'A speaker on the Pulse main stage', aspect='4/3', className='wpe-px-rounded')),
                                      b.column(b.shortcode('[wpforms id="{{form:cfp}}" title="false"]'), className='wpe-px-form-card'), className='wpe-gap-lg'), bg='surface-alt'))


def g_conduct():
    items = [b.group(b.h(t, level=2, size='h4'), b.p(d, color='text-muted'), layout='default', className='wpe-px-tile') for t, d in CONDUCT]
    report = b.group(b.h('Need help or want to report something?', level=2, size='h4'),
                     b.p('Find anyone in a pink Pulse T-shirt, call our safety line on <a href="tel:+351912000270">+351 912 000 270</a> or email <a href="mailto:safety@pulseconf.io">safety@pulseconf.io</a>. Reports are confidential.'),
                     layout='default', className='wpe-px-tile wpe-px-tile--accent')
    return b.join(g_title('Code of conduct', 'A safe, welcoming event for everyone', 'All attendees, speakers, sponsors and volunteers agree to this code.'),
                  b.section(*items, report, className='wpe-section wpe-section--sm wpe-px-conduct', content='820px'))


def e_about():
    return [e_title('About', 'The conference for people who design and build the web', 'Independent since 2023, in Lisbon every May.'),
            e.container(e.row(e.col(e.image_fixed('crowd', 'The Pulse main stage full of attendees', 420), width=50),
                              e.col(e.overline('Our story'), e.heading('Built by people who were tired of sales-pitch conferences', 2, size_px=40),
                                    e.text('Pulse started in 2023 as a one-day meetup for 420 designers and engineers in Lisbon. Five editions later it is still independent, still run by the same four people and still has no paid keynote slots.'),
                                    width=50, justify='center'), gap=56),
                        e.grid(*[e.col(e.heading(n, 3, 'primary', size_px=44), e.text(t, size_px=14), gap=4) for n, t in NUMBERS], columns=4), pad=(24, 24, 72, 24)),
            e.container(e.overline('Five editions'), e.heading('How Pulse grew'),
                        e.grid(*[e.col(e.heading(y, 3, 'primary', size_px=28), e.heading(a, 4, size_px=18), e.text(d, size_px=14), gap=6, pad=20, radius=16, bg='surface')
                                 for y, a, d in EDITIONS], columns=4), bg='surface-alt', pad=(88, 24, 88, 24)),
            e.container(e.overline('What we stand for'), e.heading('Three rules we never break'),
                        e.grid(*[e.icon_box(i, t, d) for i, t, d in VALUES], columns=3),
                        e.button('Read the code of conduct', '{{page:code-of-conduct}}', 'outline'), pad=(88, 24, 96, 24))]


def e_gallery():
    return [e_title('Gallery', 'Pulse 2026 in pictures', 'Two days, 2,400 people and a lot of very good coffee. Photos by Joana Reis.'),
            e.container(e.grid(*[e.image_fixed(k, alt, 240) for k, alt in GALLERY], columns=4), pad=(24, 24, 72, 24)),
            e.container(e.heading('Be in the 2027 photos', 3, align='center', size_px=32), e.text('Early-bird Conference passes are on sale until 22 March.', align='center'),
                        e.button('Get tickets', '{{page:tickets}}'), bg='surface-alt', align='center', pad=(72, 24, 72, 24))]


def e_cfp():
    return [e_title('Call for speakers', 'Speak at Pulse 2028', 'Proposals are open until 30 November. We especially want first-time speakers.'),
            e.container(e.overline('How it works'), e.heading('From pitch to stage'),
                        e.grid(*[e.col(e.heading(n, 3, 'primary', size_px=36), e.heading(t, 4, size_px=18), e.text(d, size_px=14), gap=6, pad=20, radius=16, bg='surface-alt')
                                 for n, t, d in CFP_STEPS], columns=4),
                        e.heading('Topics we are looking for', 3, size_px=22), e.text(''.join('<span class="wpe-px-tag">%s</span> ' % t for t in TOPICS)), pad=(24, 24, 72, 24)),
            e.container(e.row(e.col(e.heading('Send your proposal', 2, size_px=40), e.text('It takes about ten minutes. We reply to every proposal.'),
                                    e.image_fixed('hero-stage', 'A speaker on the Pulse main stage', 320), width=45),
                              e.col(e.shortcode('[wpforms id="{{form:cfp}}" title="false"]'), width=55, pad=28, radius=20, border='border', bg='surface'), gap=48),
                        bg='surface-alt', pad=(88, 24, 96, 24))]


def e_conduct():
    return [e_title('Code of conduct', 'A safe, welcoming event for everyone', 'All attendees, speakers, sponsors and volunteers agree to this code.'),
            e.container(*[e.col(e.heading(t, 2, size_px=24), e.text(d), gap=6, pad=24, radius=16, bg='surface-alt') for t, d in CONDUCT],
                        e.col(e.heading('Need help or want to report something?', 2, size_px=24),
                              e.text('Find anyone in a pink Pulse T-shirt, call our safety line on +351 912 000 270 or email safety@pulseconf.io. Reports are confidential.'),
                              gap=6, pad=24, radius=16, border='primary'), gap=16, pad=(24, 24, 96, 24))]


def package(builder):
    e.HOVER = {'button': 'float', 'image': 'grow', 'icon': 'float', 'filter_rest': 'grayscale', 'invert': False, 'transition': 0.26}
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
             ('contact', 'Contact', 'contact', g_contact, e_contact), ('about', 'About', 'about', g_about, e_about),
             ('gallery', 'Gallery', 'gallery', g_gallery, e_gallery), ('cfp', 'Call for Speakers', 'call-for-speakers', g_cfp, e_cfp),
             ('code-of-conduct', 'Code of Conduct', 'code-of-conduct', g_conduct, e_conduct)]
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
        {'title': 'About', 'page': 'about'}, {'title': 'Speakers', 'page': 'speakers'}, {'title': 'Agenda', 'page': 'agenda'}, {'title': 'Tickets', 'page': 'tickets'},
        {'title': 'Venue', 'page': 'venue'}, {'title': 'Sponsors', 'page': 'sponsors'}, {'title': 'Gallery', 'page': 'gallery'},
        {'title': 'Events', 'url': '{{home:events/}}'}, {'title': 'Blog', 'page': 'blog'}]}]
    settings = {'front_page': 'home', 'blog_page': 'blog', 'style': 'pulse', 'motion': 'dynamic',
                'parts': {'header': {'content': g_header()}, 'header-shop': {'content': g_header()}, 'header-transparent': {'content': g_header()}, 'footer': {'content': g_footer()}},
                'options': {'posts_per_page': 9, 'blogname': 'Pulse', 'blogdescription': 'The conference for people who design and build the web.',
                            'wpessential_shop_title': 'Passes & tickets', 'wpessential_shop_lead': 'All prices include VAT. Group discounts apply automatically from five passes.'},
                'woocommerce': {'options': {'woocommerce_enable_myaccount_registration': 'yes', 'woocommerce_currency': 'EUR', 'woocommerce_currency_pos': 'left', 'woocommerce_price_num_decimals': '0',
                                            'woocommerce_coming_soon': 'no', 'woocommerce_store_pages_only': 'no',
                                            'woocommerce_enable_guest_checkout': 'yes',
                                            'woocommerce_cod_settings': {'enabled': 'yes', 'title': 'Pay by bank transfer on invoice'}}}}
    if builder == 'elementor':
        settings['elementor_kit'] = e.kit(container_width=1200, button_radius=999, field_radius=14, button_padding=(14, 24))
    return {'version': 1, 'media': media, 'terms': terms, 'forms': [forms.pulse_contact(), forms.pulse_sponsor(), forms.pulse_cfp()], 'content': content, 'products': products, 'menus': menus, 'settings': settings}


DEMO = {
    'id': ID, 'title': 'Pulse', 'tagline': 'A conference site with speakers, agenda, ticket sales and an events calendar.',
    'niche': ['events', 'conference', 'meetup', 'tickets'], 'type': 'pro',
    'thumbnail': 'demo:thumbnail.webp',
    'preview': {'desktop': 'demo:preview/desktop.webp', 'tablet': 'demo:preview/tablet.webp', 'mobile': 'demo:preview/mobile.webp'},
    'pages': ['Home', 'Speakers', 'Agenda', 'Tickets', 'Venue', 'Sponsors', 'Become a Sponsor', 'Events', 'Blog', 'FAQ', 'Contact', 'About', 'Gallery', 'Call for Speakers', 'Code of Conduct'],
    'highlights': ['5 calendar events', '4 ticket passes', '8 speakers', 'Gallery & call for speakers', 'Light & dark'],
    'motion': 'dynamic',
    'plugins': [{'slug': 'the-events-calendar', 'name': 'The Events Calendar', 'source': 'wporg', 'state': 'required'},
                {'slug': 'woocommerce', 'name': 'WooCommerce', 'source': 'wporg', 'state': 'required'}, forms.PLUGIN],
}

# Unique meta description per page (playbook §1.13), stored as _wpe_seo_description.
SEO = {
    'home': 'Pulse 2027 in Lisbon, 13–14 May: two days, 60 speakers and three stages for people who design and build the web.',
    'speakers': 'Meet the 60 Pulse 2027 speakers: designers, engineers and researchers shaping how the web is built.',
    'agenda': 'The full Pulse 2027 agenda: keynotes, talks, workshops and the after-party across three stages in Lisbon.',
    'tickets': 'Pulse 2027 tickets: conference, workshop and team passes, with early-bird prices and group discounts.',
    'venue': 'Getting to Pulse 2027 at the Lisbon Congress Centre: travel, accessibility and partner hotels nearby.',
    'sponsors': 'The companies sponsoring Pulse 2027 and supporting the community of people who build the web.',
    'become-sponsor': 'Sponsor Pulse 2027: packages, audience numbers and how to put your brand in front of 2,000 web makers.',
    'about': 'About Pulse: an independent design and engineering conference in Lisbon since 2023, with five editions and 2,400 attendees.',
    'gallery': 'Photos from Pulse 2026 in Lisbon: the main stage, workshops, networking and the after-party.',
    'cfp': 'Speak at Pulse 2028: how the call for proposals works, topics we want and the form to send your talk.',
    'code-of-conduct': 'The Pulse code of conduct: what we expect from everyone at the event and how to report a problem.',
    'faq': 'Answers to common Pulse 2027 questions: tickets, refunds, accessibility, code of conduct and travel.',
    'contact': 'Contact the Pulse team about tickets, speaking, sponsorship or press for Pulse 2027 in Lisbon.',
    'blog': 'News from Pulse 2027: speaker announcements, workshop programmes and stories from the community.',
}

# Brand, contact email and what the site sells, used for the legal pages (tools/demo-builder/legal.py).
LEGAL = ('Pulse Conference', 'tickets@pulseconf.io', 'conference passes and tickets')
