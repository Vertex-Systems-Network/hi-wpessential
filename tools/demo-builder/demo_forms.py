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


PLUGIN = {'slug': 'wpforms-lite', 'name': 'WPForms Lite', 'source': 'wporg', 'state': 'required'}
