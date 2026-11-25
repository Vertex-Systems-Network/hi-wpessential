"""Elementor page data built from native widgets and Flexbox containers only.

No custom CSS, no HTML widget and no custom code: colours and fonts point at the Kit
globals (system colours primary/secondary/text/accent and the WPE custom colours that
inc/elementor.php syncs from the theme palette), so a style variation restyles the page.
Control names were checked against the Elementor source (includes/widgets, container).
"""
import hashlib
import itertools
import re

_seq = itertools.count(1)

# Font family names of the demo's style variation. Elementor cannot mix a global font with a
# custom size, so sized headings name the family directly (set by each demo before building).
FONTS = {'primary': 'Plus Jakarta Sans', 'text': 'Inter'}

# Niche hover behaviour (from the demo's research brief), applied with native hover controls only.
# button: Elementor hover animation for buttons; image: hover animation for images/image boxes;
# filter_rest: CSS-filter preset at rest that clears on hover (e.g. greyscale speakers);
# invert: buttons swap to outline on hover.
HOVER = {'button': 'float', 'image': 'grow', 'filter_rest': None, 'invert': False, 'transition': 0.45}


def _id():
    return hashlib.md5(str(next(_seq)).encode()).hexdigest()[:7]


def color(slug):
    """Global colour reference for a theme palette slug."""
    system = {'primary': 'primary', 'secondary': 'secondary', 'text': 'text', 'accent': 'accent'}
    if slug in system:
        return 'globals/colors?id=' + system[slug]
    return 'globals/colors?id=wpe' + hashlib.md5(slug.encode()).hexdigest()[:7]


def font(kind):
    """kind: primary (headings), secondary, text (body), accent."""
    return 'globals/typography?id=' + kind


def box(top, right=None, bottom=None, left=None, unit='px'):
    right = top if right is None else right
    bottom = top if bottom is None else bottom
    left = right if left is None else left
    return {'unit': unit, 'top': str(top), 'right': str(right), 'bottom': str(bottom), 'left': str(left),
            'isLinked': len({top, right, bottom, left}) == 1}


def size(n, unit='px'):
    return {'unit': unit, 'size': n, 'sizes': []}


def media(key):
    return {'url': '{{media:%s}}' % key, 'id': '{{media_id:%s}}' % key, 'alt': '', 'source': 'library'}


# Containers ----------------------------------------------------------------------------

def container(*elements, direction='column', boxed=True, width=None, gap=24, pad=None, pad_mobile=None, bg=None,
              image=None, overlay=None, overlay_opacity=0.45, min_height=None, justify=None, align=None,
              wrap=False, mobile_direction='column', inner=False, tag=None, radius=None, border=None, grid=None):
    s = {'content_width': 'boxed' if boxed and not inner else 'full', 'flex_direction': direction,
         'flex_gap': {'unit': 'px', 'size': gap, 'column': str(gap), 'row': str(gap), 'isLinked': True}}
    if grid:
        s.update({'container_type': 'grid', 'grid_columns_grid': size(grid, 'fr'),
                  'grid_columns_grid_tablet': size(2, 'fr'), 'grid_columns_grid_mobile': size(1, 'fr'),
                  'grid_gaps': {'unit': 'px', 'column': str(gap), 'row': str(gap), 'isLinked': True}})
        s.pop('flex_direction')
        s.pop('flex_gap')
    if boxed and not inner:
        s['boxed_width'] = size(1200)
    if direction == 'row' and not grid:
        s['flex_direction_mobile'] = mobile_direction
        if wrap:
            s['flex_wrap'] = 'wrap'
    if width:
        s['width'] = size(width, '%')
        s['width_mobile'] = size(100, '%')
    if pad is not None:
        s['padding'] = box(*pad) if isinstance(pad, tuple) else box(pad)
        mob = pad_mobile if pad_mobile is not None else (
            (max(pad[0] // 2, 24), 20, max(pad[2] // 2, 24), 20) if isinstance(pad, tuple) else max(pad // 2, 16))
        s['padding_mobile'] = box(*mob) if isinstance(mob, tuple) else box(mob)
    if bg or image:
        s['background_background'] = 'classic'
        if bg:
            s['__globals__'] = {'background_color': color(bg)}
        if image:
            s['background_image'] = media(image)
            s['background_position'] = 'center center'
            s['background_size'] = 'cover'
    if overlay:
        s['background_overlay_background'] = 'classic'
        s.setdefault('__globals__', {})['background_overlay_color'] = color(overlay)
        s['background_overlay_opacity'] = size(overlay_opacity, 'px')
    if min_height:
        s['min_height'] = size(min_height, 'vh' if min_height <= 100 else 'px')
    if justify:
        s['flex_justify_content'] = justify
    if align:
        s['flex_align_items'] = align
    if tag:
        s['html_tag'] = tag
    if radius is not None:
        s['border_radius'] = box(radius)
    if border:
        s['border_border'] = 'solid'
        s['border_width'] = box(1)
        s.setdefault('__globals__', {})['border_color'] = color(border)
    return {'id': _id(), 'elType': 'container', 'settings': s, 'elements': list(elements), 'isInner': inner}


def row(*elements, gap=32, **kw):
    return container(*elements, direction='row', inner=True, gap=gap, **kw)


def col(*elements, width=None, gap=16, **kw):
    return container(*elements, direction='column', inner=True, width=width, gap=gap, **kw)


def grid(*elements, columns=3, gap=24, **kw):
    return container(*elements, inner=True, gap=gap, grid=columns, **kw)


# Widgets -------------------------------------------------------------------------------

def widget(kind, settings):
    return {'id': _id(), 'elType': 'widget', 'widgetType': kind, 'settings': settings, 'elements': []}


def heading(text, level=2, color_slug='text', align=None, size_px=None, kind='primary'):
    s = {'title': text, 'header_size': 'h%d' % level,
         '__globals__': {'title_color': color(color_slug), 'typography_typography': font(kind)}}
    if align:
        s['align'] = align
    if size_px:
        s['typography_typography'] = 'custom'
        s['typography_font_family'] = FONTS['primary' if kind in ('primary', 'secondary') else 'text']
        s['typography_font_weight'] = FONTS.get('weight', '600')
        s['typography_line_height'] = size(1.05, 'em')
        s['typography_font_size'] = size(size_px)
        s['typography_font_size_tablet'] = size(round(size_px * 0.8))
        s['typography_font_size_mobile'] = size(round(size_px * 0.55))
        s['__globals__'].pop('typography_typography')
    return widget('heading', s)


def text(html, color_slug='text-muted', align=None, size_px=None):
    s = {'editor': html if html.startswith('<') else '<p>%s</p>' % html,
         '__globals__': {'text_color': color(color_slug), 'typography_typography': font('text')}}
    if align:
        s['align'] = align
    if size_px:
        s['typography_typography'] = 'custom'
        s['typography_font_family'] = FONTS['text']
        s['typography_font_size'] = size(size_px)
        s['__globals__'].pop('typography_typography')
    return widget('text-editor', s)


def overline(label, color_slug='accent', align=None):
    s = {'title': label, 'header_size': 'p', 'typography_typography': 'custom', 'typography_font_size': size(13),
         'typography_font_weight': '600', 'typography_text_transform': 'uppercase',
         'typography_letter_spacing': size(1.6), 'typography_font_family': FONTS['text'],
         '__globals__': {'title_color': color(color_slug)}}
    if align:
        s['align'] = align
    return widget('heading', s)


def button(label, href, kind='primary', align=None, full_mobile=False):
    """kind: primary (theme button), dark, outline, light."""
    s = {'text': label, 'link': {'url': href, 'is_external': '', 'nofollow': ''}, 'size': 'md',
         '__globals__': {'typography_typography': font('accent')}}
    if kind == 'dark':
        s['background_background'] = 'classic'
        s['__globals__'].update({'background_color': color('ink'), 'button_text_color': color('on-ink')})
    elif kind == 'outline':
        s['background_background'] = 'classic'
        s['background_color'] = 'rgba(0,0,0,0)'
        s['border_border'] = 'solid'
        s['border_width'] = box(1)
        s['__globals__'].update({'button_text_color': color('text'), 'border_color': color('text')})
    elif kind == 'light':
        s['background_background'] = 'classic'
        s['__globals__'].update({'background_color': color('surface'), 'button_text_color': color('text')})
    if align:
        s['align'] = align
    if full_mobile:
        s['align_mobile'] = 'justify'
    if HOVER.get('button'):
        s['hover_animation'] = HOVER['button']
    s['button_hover_transition_duration'] = size(HOVER.get('transition', 0.3), 's')
    if HOVER.get('invert') and kind in ('primary', 'dark'):
        s['button_background_hover_background'] = 'classic'
        s['button_background_hover_color'] = 'rgba(0,0,0,0)'
        s['button_hover_border_color'] = 'currentColor'
        s.setdefault('border_border', 'solid')
        s.setdefault('border_width', box(1))
        s['__globals__']['hover_color'] = color('text' if kind == 'primary' else 'ink')
        s['__globals__']['button_hover_border_color'] = color('text' if kind == 'primary' else 'ink')
    elif kind == 'outline':
        s['button_background_hover_background'] = 'classic'
        s['__globals__'].update({'button_background_hover_color': color('text'), 'hover_color': color('surface')})
    return widget('button', s)


def image(key, alt='', radius=None, link=None):
    s = {'image': dict(media(key), alt=alt), 'image_size': 'full', 'width': size(100, '%')}
    if radius is not None:
        s['image_border_radius'] = box(radius)
    if link:
        s['link_to'] = 'custom'
        s['link'] = {'url': link, 'is_external': '', 'nofollow': ''}
    _image_hover(s)
    return widget('image', s)


def _image_hover(s, people=False):
    if HOVER.get('image'):
        s['hover_animation'] = HOVER['image']
    s['background_hover_transition'] = size(HOVER.get('transition', 0.3), 's')
    if people and HOVER.get('filter_rest') == 'grayscale':
        s['css_filters_css_filter'] = 'custom'
        s['css_filters_saturate'] = size(0)
        s['css_filters_hover_css_filter'] = 'custom'
        s['css_filters_hover_saturate'] = size(100)


def image_fixed(key, alt, height, radius=None, link=None):
    """Image cropped to a fixed height with object-fit: cover (native control)."""
    s = {'image': dict(media(key), alt=alt), 'image_size': 'full', 'width': size(100, '%'),
         'height': size(height), 'height_tablet': size(round(height * 0.8)), 'height_mobile': size(round(height * 0.7)),
         'object-fit': 'cover', 'object-position': 'center center'}
    if radius is not None:
        s['image_border_radius'] = box(radius)
    if link:
        s['link_to'] = 'custom'
        s['link'] = {'url': link, 'is_external': '', 'nofollow': ''}
    _image_hover(s)
    return widget('image', s)


def image_box(key, title, desc, link=None, height=None, align='left', people=False):
    s = {'image': media(key), 'title_text': title, 'description_text': desc, 'position': 'top', 'text_align': align,
         'title_size': 'h3', 'image_size': size(100, '%'),
         '__globals__': {'title_color': color('text'), 'description_color': color('text-muted'),
                         'title_typography_typography': font('primary'),
                         'description_typography_typography': font('text')}}
    if height:
        s['image_height'] = size(height)
        s['image_object_fit'] = 'cover'
    if link:
        s['link'] = {'url': link, 'is_external': '', 'nofollow': ''}
    _image_hover(s, people)
    s['__globals__']['hover_title_color'] = color('accent')
    return widget('image-box', s)


def icon_box(icon, title, desc, align='left'):
    s = {'selected_icon': {'value': icon, 'library': 'fa-solid'}, 'title_text': title, 'description_text': desc,
         'position': 'top', 'text_align': align, 'title_size': 'h3', 'icon_size': size(28),
         '__globals__': {'primary_color': color('accent'), 'title_color': color('text'),
                         'description_color': color('text-muted'), 'title_typography_typography': font('primary'),
                         'description_typography_typography': font('text')}}
    s['hover_animation'] = HOVER.get('icon', 'float') or ''
    return widget('icon-box', s)


def icon_list(items, icon='fas fa-check', color_slug='text'):
    s = {'icon_list': [{'_id': _id(), 'text': t, 'selected_icon': {'value': icon, 'library': 'fa-solid'}} for t in items],
         'icon_size': size(14), 'text_indent': size(8), 'space_between': size(8),
         '__globals__': {'icon_color': color('accent'), 'text_color': color(color_slug),
                         'icon_typography_typography': font('text')}}
    return widget('icon-list', s)


def accordion(pairs):
    s = {'tabs': [{'_id': _id(), 'tab_title': q, 'tab_content': '<p>%s</p>' % a} for q, a in pairs],
         'faq_schema': 'yes', 'title_html_tag': 'h3',
         '__globals__': {'title_color': color('text'), 'tab_active_color': color('accent'),
                         'content_color': color('text-muted'), 'border_color': color('border'),
                         'title_typography_typography': font('primary'),
                         'content_typography_typography': font('text')}}
    return widget('accordion', s)


def testimonial(quote_text, name, job, key=None):
    s = {'testimonial_content': quote_text, 'testimonial_name': name, 'testimonial_job': job,
         'testimonial_alignment': 'left',
         '__globals__': {'content_content_color': color('text'), 'name_text_color': color('text'),
                         'job_text_color': color('text-muted'), 'content_typography_typography': font('secondary'),
                         'name_typography_typography': font('accent')}}
    if key:
        s['testimonial_image'] = media(key)
        s['testimonial_image_position'] = 'aside'
    return widget('testimonial', s)


def counter(number, title, suffix='', prefix=''):
    s = {'starting_number': 0, 'ending_number': number, 'suffix': suffix, 'prefix': prefix, 'title': title,
         'thousand_separator': 'yes',
         '__globals__': {'number_color': color('text'), 'title_color': color('text-muted'),
                         'typography_number_typography': font('primary'), 'typography_title_typography': font('text')}}
    return widget('counter', s)


def shortcode(code):
    return widget('shortcode', {'shortcode': code})


def divider(color_slug='border'):
    return widget('divider', {'__globals__': {'color': color(color_slug)}})


def spacer(px):
    return widget('spacer', {'space': size(px), 'space_mobile': size(max(px // 2, 8))})


def map_widget(address, height=420):
    return widget('google_maps', {'address': address, 'zoom': size(14), 'height': size(height)})


def kit(container_width=1200, button_radius=10, field_radius=10, button_padding=(14, 24)):
    """Kit layout/theme-style settings. Colours and fonts come from the theme palette sync."""
    return {
        'container_width': size(container_width),
        'container_padding': box(0, 24),
        'space_between_widgets': {'unit': 'px', 'column': '20', 'row': '20', 'isLinked': True, 'size': 20},
        'body_background_background': 'classic',
        'button_border_radius': box(button_radius),
        'button_padding': box(button_padding[0], button_padding[1]),
        'button_background_background': 'classic',
        'form_field_border_radius': box(field_radius),
        'form_field_padding': box(14, 16),
        'image_border_radius': box(0),
        '__globals__': {
            'body_background_color': color('surface'),
            'body_color': color('text'),
            'body_typography_typography': font('text'),
            'link_normal_color': color('accent'),
            'link_hover_color': color('text'),
            'h1_color': color('text'), 'h2_color': color('text'), 'h3_color': color('text'),
            'h1_typography_typography': font('primary'), 'h2_typography_typography': font('primary'),
            'h3_typography_typography': font('primary'),
            'button_typography_typography': font('accent'),
            'button_text_color': color('on-primary'),
            'button_background_color': color('primary'),
            'button_hover_text_color': color('on-primary'),
            'button_hover_background_color': color('primary-hover'),
            'form_field_text_color': color('text'),
            'form_field_background_color': color('surface'),
            'form_field_border_color': color('border'),
        },
    }


def _top_blocks(markup):
    """Split serialized block markup into its top-level blocks."""
    out, depth, start = [], 0, None
    for m in re.finditer(r'<!--\s+(/?)wp:([a-z0-9/-]+)(?:\s+(\{.*?\}))?\s*(/?)-->', markup, re.S):
        closing, selfclose = m.group(1) == '/', m.group(4) == '/'
        if not closing and depth == 0:
            start = m.start()
        if selfclose and not closing:
            if depth == 0:
                out.append(markup[start:m.end()])
            continue
        depth += -1 if closing else 1
        if depth == 0 and start is not None:
            out.append(markup[start:m.end()])
            start = None
    return out


def from_blocks(markup):
    """Elementor version of a kit page: each top-level section sits in a full-width container as a
    Block widget, so it renders the same kit markup as the Gutenberg page (and the same Figma design)."""
    return [{'id': _id(), 'elType': 'container', 'isInner': False, 'elements': [widget('wp-widget-block', {'wp': {'content': blk}})],
             'settings': {'content_width': 'full', 'padding': box(0), 'flex_gap': {'unit': 'px', 'size': 0, 'column': '0', 'row': '0', 'isLinked': True}}}
            for blk in _top_blocks(markup)]
