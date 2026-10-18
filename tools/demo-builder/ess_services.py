"""WPEssential.org services: detail for the 13 service pages, the quote flow and the WPForms forms.

Prices are the "from" prices of SERVICES in essential.py. Larger packages are quoted, so no
price is invented here. Edit timelines and inclusions to match what the team actually offers.
"""

COUNTRIES = ['Pakistan', 'United Arab Emirates', 'Saudi Arabia', 'United Kingdom', 'United States', 'Canada', 'Germany', 'Australia', 'Other']
TOPICS = ['Pre-sales: plans and licensing', 'WordPress services / new project', 'Care plans and maintenance', 'Emergency: site down or hacked',
          'Billing, invoices and refunds', 'Agency, partnership or white label', 'Affiliates', 'Press or something else']
SERVICE_NAMES = ['Custom demo design', 'Setup & content import', 'WooCommerce store setup', 'Figma to WordPress', 'Page building', 'Speed & SEO tuning',
                 'Migration', 'Multilingual setup', 'Security & malware cleanup', 'Bug fixes & support', 'Hosting, domain & email', 'Care plans', 'Custom development']
BUDGETS = ['Under $300', '$300 – $1,000', '$1,000 – $3,000', '$3,000 – $10,000', '$10,000+', 'Not sure yet']
TIMELINES = ['As soon as possible', '2 – 4 weeks', '1 – 3 months', 'Flexible']

# slug, outcome headline, who it is for, included, typical turnaround, revisions/warranty line
DETAIL = {
    'Custom demo design': ('custom-design', 'A demo designed around your brand.', 'Brands that want a site that looks like them, not like a template.',
                           ['Discovery call and moodboard', 'Figma design for home and key page types, desktop and mobile', 'Style variation: colours, fonts, buttons, light and dark',
                            'Built on Hi WPEssential for Gutenberg or Elementor', 'Content entry for your main pages', 'Speed check and basic on-page SEO'], '2 – 4 weeks'),
    'Setup & content import': ('setup-import', 'Your theme, demo and content — live in days.', 'Anyone who bought a plan and wants it set up properly by the people who built it.',
                               ['Theme and Core installed and licensed', 'Demo imported for Gutenberg or Elementor', 'Your logo, colours and fonts applied',
                                'Your text and images on up to 5 pages', 'Menus, footer and legal pages set', 'Contact form connected to your inbox'], '2 – 5 days'),
    'WooCommerce store setup': ('woocommerce-setup', 'A store that takes orders on day one.', 'Shops moving to WooCommerce or launching their first online store.',
                                ['Products, categories and variations', 'Payments: card gateway, bank transfer or COD', 'Shipping zones, rates and taxes',
                                 'Order, invoice and customer emails', 'Checkout tested end to end', 'Training video for managing orders'], '1 – 3 weeks'),
    'Figma to WordPress': ('figma-to-wordpress', 'Your Figma design, built as a fast WordPress site.', 'Teams and designers who already have a design and need it built well.',
                           ['Pixel-close build from your Figma or XD file', 'Responsive at every breakpoint', 'Editable blocks or Elementor sections',
                            'Reusable patterns for future pages', 'Accessibility and speed pass', 'Handover and editing guide'], '1 – 4 weeks'),
    'Page building': ('page-building', 'New pages that match the rest of your site.', 'Sites that need landing pages, campaigns or new sections fast.',
                      ['Page built in Gutenberg or Elementor', 'Mobile and tablet versions', 'Your copy and images placed', 'Forms and buttons connected',
                       'SEO title and description', 'One round of changes'], '2 – 5 days per page'),
    'Speed & SEO tuning': ('speed-seo', 'Faster pages that rank and convert.', 'Sites failing Core Web Vitals or losing visitors to slow pages.',
                           ['Before and after PageSpeed report', 'Image optimisation and lazy loading', 'Caching and asset clean-up',
                            'Core Web Vitals fixes', 'Schema, titles and meta descriptions', 'Search Console checked'], '3 – 7 days'),
    'Migration': ('migration', 'Move your site without losing content or rankings.', 'Sites changing host, theme or domain.',
                  ['Full backup before we start', 'Content, users and orders moved', 'Redirects for changed URLs', 'DNS and SSL switched',
                   'Forms, email and payments re-tested', 'Rankings checked after launch'], '3 – 10 days'),
    'Multilingual setup': ('multilingual', 'One site, every language your customers speak.', 'Businesses selling in more than one language or region.',
                           ['WPML or Polylang installed and configured', 'Translated menus, pages and products', 'Language switcher in the header',
                            'Right-to-left layout for Urdu and Arabic', 'SEO tags per language', 'Guide for adding translations'], '1 – 2 weeks'),
    'Security & malware cleanup': ('security-cleanup', 'A clean, hardened site you can trust again.', 'Hacked sites, blacklisted domains and anyone who wants to stay safe.',
                                   ['Malware scan and removal', 'Backdoors and bad users removed', 'Blacklist and Search Console review',
                                    'Firewall and login hardening', 'Updates and backups scheduled', 'Report of what was found and fixed'], '24 – 72 hours'),
    'Bug fixes & support': ('bug-fixes', 'Broken layouts and errors, fixed fast.', 'Site owners stuck with a white screen, plugin conflict or broken page.',
                            ['Problem reproduced on a staging copy', 'Fix applied and tested', 'Plugin and theme conflicts resolved',
                             'PHP errors and white screens fixed', 'Short note on the cause', 'Free re-fix if it comes back in 14 days'], '1 – 3 days'),
    'Hosting, domain & email': ('hosting-email', 'Hosting, domain and business email, set up right.', 'New sites and businesses moving to a proper host.',
                                ['Host chosen for your traffic and budget', 'Domain and DNS connected', 'Free SSL and HTTPS redirect',
                                 'Business email on your domain', 'Backups and staging site', 'Logins handed over to you'], '1 – 3 days'),
    'Care plans': ('care-plans', 'Updates, backups and fixes every month.', 'Businesses that want their site looked after by the theme team.',
                   ['Weekly updates of WordPress, theme and plugins', 'Daily backups with one-click restore', 'Uptime and security monitoring',
                    'Small content edits each month', 'Monthly report', 'Priority support'], 'Monthly'),
    'Custom development': ('custom-development', 'Custom blocks, plugins and integrations.', 'Projects that need features no plugin offers.',
                           ['Technical scoping and estimate', 'Custom blocks, plugins or WooCommerce features', 'API and payment integrations',
                            'Code to WordPress standards', 'Tests on staging', 'Documentation and handover'], 'By scope'),
}


def contact_form():
    return {'key': 'contact', 'title': 'Contact — WPEssential.org', 'submit': 'Send message',
            'message': 'Thanks — your message is with the right team. We reply within one business day.',
            'settings': {'subject': 'New contact message from {field_id="2"}'},
            'fields': [
                {'type': 'divider', 'label': '1 · About you', 'description': 'So we know who to reply to.'},
                {'type': 'name', 'label': 'Full name', 'required': True, 'size': 'medium'},
                {'type': 'email', 'label': 'Email', 'required': True, 'size': 'medium'},
                {'type': 'text', 'label': 'Company or website name', 'size': 'medium'},
                {'type': 'url', 'label': 'Current website URL', 'size': 'medium'},
                {'type': 'select', 'label': 'Country / time zone', 'choices': COUNTRIES, 'size': 'medium'},
                {'type': 'phone', 'label': 'Phone / WhatsApp', 'size': 'medium'},
                {'type': 'divider', 'label': '2 · Your project', 'description': 'Topic, services, budget and timeline.'},
                {'type': 'select', 'label': 'Topic', 'required': True, 'choices': TOPICS},
                {'type': 'checkbox', 'label': 'Services of interest', 'choices': SERVICE_NAMES, 'description': 'For services and new projects.'},
                {'type': 'select', 'label': 'Budget range', 'choices': BUDGETS, 'size': 'medium'},
                {'type': 'select', 'label': 'Desired timeline', 'choices': TIMELINES, 'size': 'medium'},
                {'type': 'divider', 'label': '3 · Details', 'description': 'The more you tell us, the better the quote.'},
                {'type': 'textarea', 'label': 'Message', 'required': True,
                 'placeholder': 'Tell us about your site, goals and anything you already have (designs, content, hosting).'},
                {'type': 'radio', 'label': 'Preferred contact', 'choices': ['Email', 'Call', 'WhatsApp']},
                {'type': 'checkbox', 'label': 'Consent', 'required': True, 'choices': ['I agree to the Privacy Policy and to being contacted about my request.']},
                {'type': 'checkbox', 'label': 'Newsletter', 'choices': ['Send me new demos and release notes once a month.']},
            ]}


def quote_form():
    return {'key': 'quote', 'title': 'Request a quote — WPEssential.org', 'submit': 'Send my brief',
            'message': 'Thanks — we will send a fixed quote, scope and timeline within two business days.',
            'settings': {'subject': 'New quote request from {field_id="1"}'},
            'fields': [
                {'type': 'name', 'label': 'Full name', 'required': True, 'size': 'medium'},
                {'type': 'email', 'label': 'Email', 'required': True, 'size': 'medium'},
                {'type': 'text', 'label': 'Company', 'size': 'medium'},
                {'type': 'phone', 'label': 'Phone / WhatsApp', 'size': 'medium'},
                {'type': 'select', 'label': 'Country / time zone', 'choices': COUNTRIES, 'size': 'medium'},
                {'type': 'url', 'label': 'Website URL', 'size': 'medium'},
                {'type': 'checkbox', 'label': 'Service(s)', 'required': True, 'choices': SERVICE_NAMES},
                {'type': 'radio', 'label': 'Project type', 'required': True, 'choices': ['New site', 'Redesign', 'Fix or improve', 'Ongoing work']},
                {'type': 'radio', 'label': 'Uses Hi WPEssential?', 'choices': ['Yes', 'No', 'Not decided']},
                {'type': 'select', 'label': 'Page builder', 'choices': ['Gutenberg', 'Elementor', 'Other', 'Not sure'], 'size': 'medium'},
                {'type': 'select', 'label': 'Number of pages', 'choices': ['1 – 5', '6 – 10', '11 – 25', '25+'], 'size': 'medium'},
                {'type': 'text', 'label': 'WooCommerce products (if any)', 'size': 'medium'},
                {'type': 'text', 'label': 'Languages', 'size': 'medium'},
                {'type': 'textarea', 'label': 'Reference sites', 'placeholder': 'Links to sites you like, one per line.'},
                {'type': 'url', 'label': 'Figma or design file link'},
                {'type': 'textarea', 'label': 'Describe the project', 'required': True, 'placeholder': 'Goals, must-have features, integrations, anything we should know.'},
                {'type': 'radio', 'label': 'Content ready?', 'choices': ['Ready', 'Partly ready', 'Need help with content']},
                {'type': 'radio', 'label': 'Hosting', 'choices': ['I have hosting', 'I need hosting']},
                {'type': 'select', 'label': 'Budget range', 'required': True, 'choices': BUDGETS, 'size': 'medium'},
                {'type': 'select', 'label': 'Timeline', 'choices': TIMELINES, 'size': 'medium'},
                {'type': 'checkbox', 'label': 'Extras', 'choices': ['I need an NDA before sharing details', 'Rush delivery']},
                {'type': 'radio', 'label': 'Preferred contact', 'choices': ['Email', 'Call', 'WhatsApp']},
                {'type': 'checkbox', 'label': 'Consent', 'required': True, 'choices': ['I agree to the Privacy Policy and to being contacted about my request.']},
            ]}


def service_form():
    return {'key': 'service', 'title': 'Service quote — WPEssential.org', 'submit': 'Send request',
            'message': 'Thanks — we will reply with a quote within two business days.',
            'fields': [
                {'type': 'name', 'label': 'Name', 'required': True},
                {'type': 'email', 'label': 'Email', 'required': True},
                {'type': 'select', 'label': 'Service', 'choices': SERVICE_NAMES},
                {'type': 'select', 'label': 'Budget', 'choices': BUDGETS},
                {'type': 'textarea', 'label': 'Tell us about the project', 'required': True},
                {'type': 'checkbox', 'label': 'Consent', 'required': True, 'choices': ['I agree to the Privacy Policy.']},
            ]}


def newsletter_form():
    return {'key': 'newsletter', 'title': 'Newsletter — WPEssential.org', 'submit': 'Subscribe',
            'message': 'Thanks — you are on the list. One email a month, unsubscribe any time.',
            'settings': {'subject': 'Newsletter sign-up'},
            'fields': [{'type': 'email', 'label': 'Email', 'required': True, 'placeholder': 'you@company.com'}]}


FORMS = [contact_form, quote_form, service_form, newsletter_form]
