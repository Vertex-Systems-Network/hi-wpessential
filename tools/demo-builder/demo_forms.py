"""WPForms Lite forms for the Base, Maison and Pulse demos (same format as ess_services.FORMS).

The importer creates each form with Core's Forms::create() and swaps {{form:key}} for its ID.
Fields follow what each niche is actually asked: a business enquiry, fashion client care and
conference questions.
"""

CONSENT = {'type': 'checkbox', 'label': 'Consent', 'required': True,
           'choices': ['I agree to the Privacy Policy and to being contacted about my request.']}


def base_contact():
    return {'key': 'contact', 'title': 'Contact — project enquiry', 'submit': 'Send message',
            'message': 'Thanks — we have your message and reply within one working day.',
            'settings': {'subject': 'New enquiry from {field_id="1"}'},
            'fields': [
                {'type': 'name', 'label': 'Full name', 'required': True, 'size': 'medium'},
                {'type': 'email', 'label': 'Email', 'required': True, 'size': 'medium'},
                {'type': 'phone', 'label': 'Phone', 'size': 'medium'},
                {'type': 'text', 'label': 'Company', 'size': 'medium'},
                {'type': 'select', 'label': 'What do you need?', 'required': True,
                 'choices': ['Strategy & consulting', 'Website design', 'Development', 'Marketing & SEO', 'Ongoing support', 'Something else']},
                {'type': 'select', 'label': 'Budget', 'choices': ['Under $2,000', '$2,000 – $5,000', '$5,000 – $15,000', '$15,000+', 'Not sure yet'], 'size': 'medium'},
                {'type': 'select', 'label': 'When do you want to start?', 'choices': ['As soon as possible', 'Within a month', 'In 1–3 months', 'Just exploring'], 'size': 'medium'},
                {'type': 'textarea', 'label': 'Message', 'required': True, 'placeholder': 'Tell us about your business and what you want to achieve.'},
                CONSENT,
            ]}


def maison_care():
    return {'key': 'care', 'title': 'Client care — Maison', 'submit': 'Send to client care',
            'message': 'Thank you — our client care team replies within one working day.',
            'settings': {'subject': 'Client care: {field_id="1"}'},
            'fields': [
                {'type': 'name', 'label': 'Full name', 'required': True, 'size': 'medium'},
                {'type': 'email', 'label': 'Email', 'required': True, 'size': 'medium'},
                {'type': 'select', 'label': 'How can we help?', 'required': True,
                 'choices': ['Sizing & fit advice', 'My order & delivery', 'Returns & exchanges', 'Repairs & aftercare',
                             'Book an atelier appointment', 'Press & wholesale']},
                {'type': 'text', 'label': 'Order number', 'placeholder': 'e.g. MA-104 233', 'size': 'medium'},
                {'type': 'select', 'label': 'Preferred store', 'choices': ['Paris — Le Marais', 'Milan — Brera', 'London — Marylebone', 'Online only'], 'size': 'medium'},
                {'type': 'textarea', 'label': 'Message', 'required': True, 'placeholder': 'Tell us the piece, size and what you need.'},
                CONSENT,
            ]}


def maison_newsletter():
    return {'key': 'newsletter', 'title': 'Newsletter — Maison', 'submit': 'Subscribe',
            'message': 'Welcome — the next letter arrives with the new collection.',
            'settings': {'subject': 'New newsletter sign-up'},
            'fields': [{'type': 'email', 'label': 'Email', 'required': True, 'placeholder': 'Your email address'}]}


def maison_appointment():
    return {'key': 'appointment', 'title': 'Atelier appointment — Maison', 'submit': 'Request appointment',
            'message': 'Thank you — we will confirm your appointment by email within a few hours.',
            'settings': {'subject': 'Appointment request: {field_id="1"}'},
            'fields': [
                {'type': 'name', 'label': 'Full name', 'required': True, 'size': 'medium'},
                {'type': 'email', 'label': 'Email', 'required': True, 'size': 'medium'},
                {'type': 'phone', 'label': 'Phone', 'size': 'medium'},
                {'type': 'select', 'label': 'Appointment', 'required': True, 'size': 'medium',
                 'choices': ['Personal styling (60 min)', 'Made-to-measure fitting (45 min)', 'Alterations & repairs (20 min)']},
                {'type': 'select', 'label': 'Store', 'required': True, 'size': 'medium', 'choices': ['Paris — Le Marais', 'Milan — Brera', 'London — Marylebone']},
                {'type': 'text', 'label': 'Preferred date and time', 'placeholder': 'e.g. Saturday 14 Nov, morning', 'size': 'medium'},
                {'type': 'textarea', 'label': 'Anything we should prepare?', 'placeholder': 'Sizes, pieces you want to try or the item to repair.'},
                CONSENT,
            ]}


def pulse_contact():
    return {'key': 'contact', 'title': 'Contact — Pulse', 'submit': 'Send message',
            'message': 'Thanks — the Pulse team replies within one working day.',
            'settings': {'subject': 'Pulse: {field_id="1"}'},
            'fields': [
                {'type': 'name', 'label': 'Full name', 'required': True, 'size': 'medium'},
                {'type': 'email', 'label': 'Email', 'required': True, 'size': 'medium'},
                {'type': 'text', 'label': 'Company', 'size': 'medium'},
                {'type': 'select', 'label': 'Topic', 'required': True, 'size': 'medium',
                 'choices': ['Tickets & invoices', 'Group tickets (10+)', 'Speaking', 'Sponsorship', 'Press & media', 'Accessibility', 'Volunteering']},
                {'type': 'text', 'label': 'Ticket order number', 'placeholder': 'If your question is about a booking', 'size': 'medium'},
                {'type': 'textarea', 'label': 'Message', 'required': True},
                CONSENT,
            ]}


def pulse_sponsor():
    return {'key': 'sponsor', 'title': 'Sponsor deck request — Pulse', 'submit': 'Send request',
            'message': 'Thanks — the sponsor deck and audience data are on their way to your inbox.',
            'settings': {'subject': 'Sponsor deck request from {field_id="1"}'},
            'fields': [
                {'type': 'name', 'label': 'Name', 'required': True, 'size': 'medium'},
                {'type': 'text', 'label': 'Company', 'required': True, 'size': 'medium'},
                {'type': 'email', 'label': 'Work email', 'required': True},
                {'type': 'select', 'label': 'Package', 'choices': ['Community · €4,000', 'Gold · €12,000', 'Headline · €30,000', 'Not sure yet']},
                CONSENT,
            ]}


def pulse_cfp():
    return {'key': 'cfp', 'title': 'Call for speakers — Pulse', 'submit': 'Submit proposal',
            'message': 'Thanks — your proposal is in. The review panel replies to everyone by 15 January.',
            'settings': {'subject': 'Talk proposal from {field_id="1"}'},
            'fields': [
                {'type': 'name', 'label': 'Full name', 'required': True, 'size': 'medium'},
                {'type': 'email', 'label': 'Email', 'required': True, 'size': 'medium'},
                {'type': 'text', 'label': 'Role and company', 'size': 'medium'},
                {'type': 'select', 'label': 'Track', 'required': True, 'size': 'medium',
                 'choices': ['Design systems & tokens', 'Front-end architecture', 'AI in product', 'Accessibility', 'Brand & motion', 'Research & strategy']},
                {'type': 'text', 'label': 'Talk title', 'required': True},
                {'type': 'textarea', 'label': 'Abstract (about 150 words)', 'required': True},
                {'type': 'url', 'label': 'Link to a past talk or slides', 'placeholder': 'Optional — first-time speakers welcome'},
                CONSENT,
            ]}


PLUGIN = {'slug': 'wpforms-lite', 'name': 'WPForms Lite', 'source': 'wporg', 'state': 'required'}
