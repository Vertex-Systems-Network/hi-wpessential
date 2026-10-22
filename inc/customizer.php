<?php
/**
 * Site Settings in the Customizer (like Elementor's Site Settings, with more options).
 *
 * Every value is a theme token: it is written into theme.json through the
 * `wp_theme_json_data_theme` filter, so the front end, the block editor and Elementor
 * (which syncs the palette and fonts from theme.json) all get the same value. An empty
 * value keeps the theme / style-variation default. Changes made in the Site Editor's
 * Styles panel still win, as in WordPress itself.
 *
 * WordPress sections that never change (Site Identity, Homepage Settings, Menus, Widgets,
 * Additional CSS) stay. Classic-theme sections that do not apply to a block theme
 * (Colors, Header Image, Background Image) are removed and replaced by ours.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bundled fonts: family name => [ CSS stack, files ].
 *
 * @return array
 */
function wpessential_site_fonts() {
	return array(
		'Inter'               => array( 'Inter, system-ui, sans-serif', array( 'inter-var.woff2', 'inter-var-italic.woff2' ) ),
		'Plus Jakarta Sans'   => array( '"Plus Jakarta Sans", Inter, system-ui, sans-serif', array( 'plus-jakarta-sans-var.woff2' ) ),
		'DM Sans'             => array( '"DM Sans", system-ui, sans-serif', array( 'dm-sans-var.woff2' ) ),
		'Manrope'             => array( 'Manrope, system-ui, sans-serif', array( 'manrope-var.woff2' ) ),
		'Space Grotesk'       => array( '"Space Grotesk", system-ui, sans-serif', array( 'space-grotesk-var.woff2' ) ),
		'Bricolage Grotesque' => array( '"Bricolage Grotesque", system-ui, sans-serif', array( 'bricolage-grotesque-var.woff2' ) ),
		'Cormorant Garamond'  => array( '"Cormorant Garamond", Georgia, serif', array( 'cormorant-garamond-var.woff2', 'cormorant-garamond-var-italic.woff2' ) ),
		'System UI'           => array( 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif', array() ),
		'Georgia (serif)'     => array( 'Georgia, "Times New Roman", serif', array() ),
	);
}

/**
 * Sections and their settings, with the Elementor-style controls applied.
 *
 * @return array
 */
function wpessential_site_fields() {
	static $fields = null;
	if ( null === $fields ) {
		$fields = wpessential_site_fields_upgrade( wpessential_site_fields_base() );
	}
	return $fields;
}

/**
 * Sections and their settings. Each setting: type, label, default '' (= theme default).
 *
 * @return array
 */
function wpessential_site_fields_base() {
	$colours = array(
		'primary'       => __( 'Primary (brand)', 'hi-wpessential' ),
		'primary-hover' => __( 'Primary on hover', 'hi-wpessential' ),
		'secondary'     => __( 'Secondary', 'hi-wpessential' ),
		'accent'        => __( 'Accent', 'hi-wpessential' ),
		'ink'           => __( 'Ink (dark sections, headings)', 'hi-wpessential' ),
		'text'          => __( 'Text', 'hi-wpessential' ),
		'text-muted'    => __( 'Muted text', 'hi-wpessential' ),
		'surface'       => __( 'Page background', 'hi-wpessential' ),
		'surface-alt'   => __( 'Alternate background', 'hi-wpessential' ),
		'border'        => __( 'Borders', 'hi-wpessential' ),
		'on-primary'    => __( 'Text on primary', 'hi-wpessential' ),
		'success'       => __( 'Success', 'hi-wpessential' ),
		'warning'       => __( 'Warning', 'hi-wpessential' ),
		'danger'        => __( 'Error', 'hi-wpessential' ),
		'primary-soft'  => __( 'Primary tint (soft backgrounds)', 'hi-wpessential' ),
		'on-ink'        => __( 'Text on ink', 'hi-wpessential' ),
		'on-ink-muted'  => __( 'Muted text on ink', 'hi-wpessential' ),
		'pro'           => __( 'Pro badge', 'hi-wpessential' ),
		'star'          => __( 'Rating stars', 'hi-wpessential' ),
	);
	$light   = array();
	$dark    = array();
	foreach ( $colours as $slug => $label ) {
		$light[ 'color_' . $slug ] = array(
			'type'  => 'color',
			'label' => $label,
		);
		$dark[ 'dark_' . $slug ]   = array(
			'type'  => 'color',
			'label' => $label,
		);
	}
	$font_choices = array( '' => __( 'Theme default', 'hi-wpessential' ) ) + array_combine( array_keys( wpessential_site_fonts() ), array_keys( wpessential_site_fonts() ) );
	$weights      = array(
		''    => __( 'Theme default', 'hi-wpessential' ),
		'400' => '400 · Regular',
		'500' => '500 · Medium',
		'600' => '600 · Semibold',
		'700' => '700 · Bold',
		'800' => '800 · Extra bold',
	);
	$px           = static fn( $label, $min, $max, $step = 1, $unit = 'px' ) => array(
		'type'  => 'range',
		'label' => $label,
		'min'   => $min,
		'max'   => $max,
		'step'  => $step,
		'unit'  => $unit,
	);
	$typography   = array(
		'fonts_delivery'  => array(
			'type'    => 'select',
			'label'   => __( 'Google Fonts are served', 'hi-wpessential' ),
			'choices' => array(
				''       => __( 'From this site (downloaded on save, privacy-friendly)', 'hi-wpessential' ),
				'google' => __( 'From Google servers', 'hi-wpessential' ),
			),
		),
		'font_body'       => array(
			'type'    => 'font',
			'label'   => __( 'Body font', 'hi-wpessential' ),
			'choices' => $font_choices,
		),
		'font_heading'    => array(
			'type'    => 'font',
			'label'   => __( 'Heading font', 'hi-wpessential' ),
			'choices' => $font_choices,
		),
		'font_mono'       => array(
			'type'    => 'font',
			'label'   => __( 'Code font', 'hi-wpessential' ),
			'choices' => $font_choices,
		),
		'fonts_extra'     => array(
			'type'  => 'fonts',
			'label' => __( 'More fonts for the editors', 'hi-wpessential' ),
			'help'  => __( 'Google Fonts added here can be picked in any block, kit element and Elementor widget.', 'hi-wpessential' ),
		),
		'size_body'       => $px( __( 'Body text size', 'hi-wpessential' ), 12, 22 ),
		'line_body'       => $px( __( 'Body line height', 'hi-wpessential' ), 1, 2.2, 0.05, '' ),
		'heading_weight'  => array(
			'type'    => 'select',
			'label'   => __( 'Heading weight', 'hi-wpessential' ),
			'choices' => $weights,
		),
		'heading_spacing' => $px( __( 'Heading letter spacing (em)', 'hi-wpessential' ), -0.08, 0.1, 0.005, 'em' ),
		'heading_line'    => $px( __( 'Heading line height', 'hi-wpessential' ), 0.9, 1.6, 0.05, '' ),
		'heading_case'    => array(
			'type'    => 'select',
			'label'   => __( 'Heading letter case', 'hi-wpessential' ),
			'choices' => array(
				''           => __( 'Theme default', 'hi-wpessential' ),
				'none'       => __( 'As typed', 'hi-wpessential' ),
				'uppercase'  => __( 'UPPERCASE', 'hi-wpessential' ),
				'capitalize' => __( 'Capitalize', 'hi-wpessential' ),
			),
		),
	);
	foreach ( array(
		'display' => array( 'Display', 40, 64 ),
		'h1'      => array( 'H1', 34, 48 ),
		'h2'      => array( 'H2', 28, 38 ),
		'h3'      => array( 'H3', 22, 28 ),
		'h4'      => array( 'H4', 18, 20 ),
		'body-l'  => array( __( 'Large body text', 'hi-wpessential' ), 16, 18 ),
	) as $slug => $d ) {
		/* translators: %s: heading level. */
		$typography[ 'size_' . $slug . '_min' ] = $px( sprintf( __( '%s size on phones', 'hi-wpessential' ), $d[0] ), 12, 120 );
		/* translators: %s: heading level. */
		$typography[ 'size_' . $slug . '_max' ] = $px( sprintf( __( '%s size on desktop', 'hi-wpessential' ), $d[0] ), 12, 160 );
	}
	$colour                        = static fn( $label ) => array(
		'type'  => 'color',
		'label' => $label,
	);
	$typography['size_h5']         = $px( __( 'H5 size', 'hi-wpessential' ), 12, 40 );
	$typography['size_h6']         = $px( __( 'H6 size', 'hi-wpessential' ), 10, 32 );
	$typography['size_small']      = $px( __( 'Small text size', 'hi-wpessential' ), 10, 18 );
	$typography['body_weight']     = array(
		'type'    => 'select',
		'label'   => __( 'Body text weight', 'hi-wpessential' ),
		'choices' => array(
			''    => __( 'Theme default', 'hi-wpessential' ),
			'300' => '300 · Light',
		) + array_slice( $weights, 1, 3, true ),
	);
	$typography['paragraph_space'] = $px( __( 'Space between paragraphs (em)', 'hi-wpessential' ), 0, 3, 0.05, 'em' );
	$typography['heading_color']   = $colour( __( 'Heading colour', 'hi-wpessential' ) );
	$typography['selection_bg']    = $colour( __( 'Selected text background', 'hi-wpessential' ) );
	$typography['selection_text']  = $colour( __( 'Selected text colour', 'hi-wpessential' ) );
	$gradient_type                 = array(
		'type'    => 'select',
		'label'   => __( 'Type', 'hi-wpessential' ),
		'choices' => array(
			''       => __( 'Linear (theme default)', 'hi-wpessential' ),
			'radial' => __( 'Radial', 'hi-wpessential' ),
		),
	);
	return array(
		'wpessential_site_colors'    => array(
			'title'       => __( 'Global colours', 'hi-wpessential' ),
			'description' => __( 'The site palette. Every block, kit element and Elementor widget uses these. Leave empty to keep the demo’s colour.', 'hi-wpessential' ),
			'fields'      => $light,
		),
		'wpessential_site_dark'      => array(
			'title'       => __( 'Dark mode', 'hi-wpessential' ),
			'description' => __( 'The colours visitors see in dark mode, and how dark mode works.', 'hi-wpessential' ),
			'fields'      => array(
				'scheme_toggle' => array(
					'type'    => 'select',
					'label'   => __( 'Light / dark switch for visitors', 'hi-wpessential' ),
					'choices' => array(
						''    => __( 'Show (theme default)', 'hi-wpessential' ),
						'off' => __( 'Hide, always use the default', 'hi-wpessential' ),
					),
				),
				'scheme_follow' => array(
					'type'    => 'select',
					'label'   => __( 'First visit', 'hi-wpessential' ),
					'choices' => array(
						''      => __( 'Follow the visitor’s device', 'hi-wpessential' ),
						'fixed' => __( 'Always start with the site default', 'hi-wpessential' ),
					),
				),
			) + $dark,
		),
		'wpessential_site_type'      => array(
			'title'       => __( 'Typography', 'hi-wpessential' ),
			'description' => __( 'Pick from the theme fonts or 1,700+ Google Fonts. Chosen Google Fonts are saved on your site, so visitors never load them from Google (unless you choose so below). Heading sizes scale smoothly between the phone and the desktop value.', 'hi-wpessential' ),
			'fields'      => $typography,
		),
		'wpessential_site_button'    => array(
			'title'  => __( 'Buttons', 'hi-wpessential' ),
			'fields' => array(
				'btn_radius'     => $px( __( 'Corner radius', 'hi-wpessential' ), 0, 60 ),
				'btn_pad_y'      => $px( __( 'Padding top / bottom', 'hi-wpessential' ), 4, 32 ),
				'btn_pad_x'      => $px( __( 'Padding left / right', 'hi-wpessential' ), 8, 64 ),
				'btn_size'       => $px( __( 'Text size', 'hi-wpessential' ), 11, 22 ),
				'btn_weight'     => array(
					'type'    => 'select',
					'label'   => __( 'Text weight', 'hi-wpessential' ),
					'choices' => $weights,
				),
				'btn_case'       => array(
					'type'    => 'select',
					'label'   => __( 'Letter case', 'hi-wpessential' ),
					'choices' => array(
						''          => __( 'Theme default', 'hi-wpessential' ),
						'none'      => __( 'As typed', 'hi-wpessential' ),
						'uppercase' => __( 'UPPERCASE', 'hi-wpessential' ),
					),
				),
				'btn_lift'       => $px( __( 'Lift on hover', 'hi-wpessential' ), 0, 8 ),
				'btn_spacing'    => $px( __( 'Letter spacing (em)', 'hi-wpessential' ), 0, 0.2, 0.01, 'em' ),
				'btn_bg'         => $colour( __( 'Background', 'hi-wpessential' ) ),
				'btn_text'       => $colour( __( 'Text colour', 'hi-wpessential' ) ),
				'btn_hover_bg'   => $colour( __( 'Background on hover', 'hi-wpessential' ) ),
				'btn_hover_text' => $colour( __( 'Text colour on hover', 'hi-wpessential' ) ),
				'btn_border_w'   => $px( __( 'Border width', 'hi-wpessential' ), 0, 6 ),
				'btn_border'     => $colour( __( 'Border colour', 'hi-wpessential' ) ),
				'btn_shadow'     => array(
					'type'    => 'select',
					'label'   => __( 'Shadow', 'hi-wpessential' ),
					'choices' => array(
						''     => __( 'Theme default', 'hi-wpessential' ),
						'none' => __( 'None', 'hi-wpessential' ),
						'sm'   => __( 'Small', 'hi-wpessential' ),
						'md'   => __( 'Medium', 'hi-wpessential' ),
						'lg'   => __( 'Large', 'hi-wpessential' ),
					),
				),
			),
		),
		'wpessential_site_forms'     => array(
			'title'  => __( 'Form fields', 'hi-wpessential' ),
			'fields' => array(
				'field_radius' => $px( __( 'Corner radius', 'hi-wpessential' ), 0, 30 ),
				'field_height' => $px( __( 'Height', 'hi-wpessential' ), 32, 64 ),
				'field_border' => array(
					'type'  => 'color',
					'label' => __( 'Border colour', 'hi-wpessential' ),
				),
				'field_bg'     => array(
					'type'  => 'color',
					'label' => __( 'Background', 'hi-wpessential' ),
				),
				'field_focus'  => array(
					'type'  => 'color',
					'label' => __( 'Focus ring colour', 'hi-wpessential' ),
				),
			),
		),
		'wpessential_site_shape'     => array(
			'title'       => __( 'Corners & images', 'hi-wpessential' ),
			'description' => __( 'The radius scale every card, image and element uses.', 'hi-wpessential' ),
			'fields'      => array(
				'radius_sm'   => $px( __( 'Small radius', 'hi-wpessential' ), 0, 24 ),
				'radius_md'   => $px( __( 'Medium radius', 'hi-wpessential' ), 0, 32 ),
				'radius_lg'   => $px( __( 'Large radius', 'hi-wpessential' ), 0, 48 ),
				'radius_xl'   => $px( __( 'Extra large radius', 'hi-wpessential' ), 0, 64 ),
				'image_zoom'  => $px( __( 'Image zoom on hover', 'hi-wpessential' ), 1, 1.2, 0.01, '' ),
				'image_round' => array(
					'type'  => 'toggle',
					'label' => __( 'Round the corners of images in content', 'hi-wpessential' ),
				),
			),
		),
		'wpessential_site_layout'    => array(
			'title'       => __( 'Layout', 'hi-wpessential' ),
			'description' => __( 'Breakpoints are fixed in the theme (1920, 1440, 1280, 1024, 768, 560, 480, 360, 320) so every demo and element lines up.', 'hi-wpessential' ),
			'fields'      => array(
				'content_width' => $px( __( 'Content width', 'hi-wpessential' ), 480, 1200 ),
				'wide_width'    => $px( __( 'Wide width', 'hi-wpessential' ), 900, 1920 ),
				'section_space' => $px( __( 'Space between sections', 'hi-wpessential' ), 24, 200 ),
				'gutter'        => $px( __( 'Side space', 'hi-wpessential' ), 0, 120 ),
				'gutter_mobile' => $px( __( 'Side space on phones', 'hi-wpessential' ), 0, 48 ),
				'block_gap'     => $px( __( 'Space between blocks', 'hi-wpessential' ), 0, 80 ),
				'header_height' => $px( __( 'Header height', 'hi-wpessential' ), 48, 120 ),
				'sticky_header' => array(
					'type'    => 'select',
					'label'   => __( 'Sticky header', 'hi-wpessential' ),
					'choices' => array(
						''    => __( 'On (theme default)', 'hi-wpessential' ),
						'off' => __( 'Off', 'hi-wpessential' ),
					),
				),
				'back_to_top'   => array(
					'type'    => 'select',
					'label'   => __( 'Back-to-top button', 'hi-wpessential' ),
					'choices' => array(
						''    => __( 'Show (theme default)', 'hi-wpessential' ),
						'off' => __( 'Hide', 'hi-wpessential' ),
					),
				),
			),
		),
		'wpessential_site_gradients' => array(
			'title'       => __( 'Gradients', 'hi-wpessential' ),
			'description' => __( 'Brand gradient (gradient buttons, accent words, highlights), Night (dark sections) and the image fade.', 'hi-wpessential' ),
			'fields'      => array(
				'grad_brand_auto'  => array(
					'type'  => 'toggle',
					'label' => __( 'Brand gradient follows Primary → Secondary', 'hi-wpessential' ),
				),
				'grad_brand_from'  => $colour( __( 'Brand gradient: start', 'hi-wpessential' ) ),
				'grad_brand_to'    => $colour( __( 'Brand gradient: end', 'hi-wpessential' ) ),
				'grad_brand_angle' => $px( __( 'Brand gradient: angle', 'hi-wpessential' ), 0, 360, 5, 'deg' ),
				'grad_brand_type'  => $gradient_type,
				'grad_night_from'  => $colour( __( 'Night gradient: start', 'hi-wpessential' ) ),
				'grad_night_to'    => $colour( __( 'Night gradient: end', 'hi-wpessential' ) ),
				'grad_night_angle' => $px( __( 'Night gradient: angle', 'hi-wpessential' ), 0, 360, 5, 'deg' ),
				'grad_fade'        => $colour( __( 'Image fade colour', 'hi-wpessential' ) ),
				'grad_fade_amount' => $px( __( 'Image fade strength (%)', 'hi-wpessential' ), 0, 100, 5, '%' ),
			),
		),
		'wpessential_site_links'     => array(
			'title'  => __( 'Links', 'hi-wpessential' ),
			'fields' => array(
				'link_color'     => $colour( __( 'Link colour', 'hi-wpessential' ) ),
				'link_hover'     => $colour( __( 'Link hover colour', 'hi-wpessential' ) ),
				'link_underline' => array(
					'type'    => 'select',
					'label'   => __( 'Underline', 'hi-wpessential' ),
					'choices' => array(
						''       => __( 'On hover (theme default)', 'hi-wpessential' ),
						'always' => __( 'Always', 'hi-wpessential' ),
						'never'  => __( 'Never', 'hi-wpessential' ),
						'text'   => __( 'Always inside text, on hover elsewhere', 'hi-wpessential' ),
					),
				),
				'link_thickness' => $px( __( 'Underline thickness', 'hi-wpessential' ), 1, 4 ),
				'link_offset'    => $px( __( 'Underline distance', 'hi-wpessential' ), 0, 10 ),
				'link_weight'    => array(
					'type'    => 'select',
					'label'   => __( 'Link weight', 'hi-wpessential' ),
					'choices' => $weights,
				),
			),
		),
		'wpessential_site_shadows'   => array(
			'title'       => __( 'Shadows', 'hi-wpessential' ),
			'description' => __( 'The small, medium and large shadows every card and element uses.', 'hi-wpessential' ),
			'fields'      => array(
				'shadow_color'    => $colour( __( 'Shadow colour', 'hi-wpessential' ) ),
				'shadow_strength' => $px( __( 'Shadow strength (%)', 'hi-wpessential' ), 0, 300, 10, '%' ),
				'shadow_sm'       => array(
					'type'  => 'text',
					'label' => __( 'Small shadow (CSS, optional)', 'hi-wpessential' ),
				),
				'shadow_md'       => array(
					'type'  => 'text',
					'label' => __( 'Medium shadow (CSS, optional)', 'hi-wpessential' ),
				),
				'shadow_lg'       => array(
					'type'  => 'text',
					'label' => __( 'Large shadow (CSS, optional)', 'hi-wpessential' ),
				),
			),
		),
		'wpessential_site_header'    => array(
			'title'  => __( 'Header & footer', 'hi-wpessential' ),
			'fields' => array(
				'header_bg'         => $colour( __( 'Header background', 'hi-wpessential' ) ),
				'header_text'       => $colour( __( 'Header text and menu', 'hi-wpessential' ) ),
				'header_border'     => $colour( __( 'Header bottom line', 'hi-wpessential' ) ),
				'header_glass'      => array(
					'type'  => 'toggle',
					'label' => __( 'Glass header (see-through with blur)', 'hi-wpessential' ),
				),
				'header_shadow'     => array(
					'type'    => 'select',
					'label'   => __( 'Shadow when scrolling', 'hi-wpessential' ),
					'choices' => array(
						''    => __( 'On (theme default)', 'hi-wpessential' ),
						'off' => __( 'Off', 'hi-wpessential' ),
					),
				),
				'menu_size'         => $px( __( 'Menu text size', 'hi-wpessential' ), 11, 22 ),
				'menu_weight'       => array(
					'type'    => 'select',
					'label'   => __( 'Menu text weight', 'hi-wpessential' ),
					'choices' => $weights,
				),
				'menu_gap'          => $px( __( 'Space between menu items', 'hi-wpessential' ), 0, 64 ),
				'footer_bg'         => $colour( __( 'Footer background', 'hi-wpessential' ) ),
				'footer_text'       => $colour( __( 'Footer text', 'hi-wpessential' ) ),
				'footer_heading'    => $colour( __( 'Footer headings', 'hi-wpessential' ) ),
				'footer_link'       => $colour( __( 'Footer links', 'hi-wpessential' ) ),
				'footer_link_hover' => $colour( __( 'Footer links on hover', 'hi-wpessential' ) ),
			),
		),
		'wpessential_site_a11y'      => array(
			'title'       => __( 'Accessibility', 'hi-wpessential' ),
			'description' => __( 'Keyboard focus and readability for every visitor.', 'hi-wpessential' ),
			'fields'      => array(
				'focus_color' => $colour( __( 'Keyboard focus ring colour', 'hi-wpessential' ) ),
				'focus_width' => $px( __( 'Focus ring width', 'hi-wpessential' ), 1, 6 ),
				'skip_link'   => array(
					'type'    => 'select',
					'label'   => __( '"Skip to content" link', 'hi-wpessential' ),
					'choices' => array(
						''     => __( 'Show on keyboard focus (recommended)', 'hi-wpessential' ),
						'show' => __( 'Always visible', 'hi-wpessential' ),
					),
				),
				'min_text'    => $px( __( 'Smallest text size allowed', 'hi-wpessential' ), 10, 16 ),
			),
		),
		'wpessential_site_motion'    => array(
			'title'       => __( 'Hover & motion', 'hi-wpessential' ),
			'description' => __( 'Visitors who ask their device for less motion always get less motion.', 'hi-wpessential' ),
			'fields'      => array(
				'hover_duration' => $px( __( 'Hover speed (ms)', 'hi-wpessential' ), 0, 800, 10, 'ms' ),
				'hover_ease'     => array(
					'type'    => 'select',
					'label'   => __( 'Hover easing', 'hi-wpessential' ),
					'choices' => array(
						''                             => __( 'Theme default', 'hi-wpessential' ),
						'ease'                         => __( 'Ease', 'hi-wpessential' ),
						'linear'                       => __( 'Linear', 'hi-wpessential' ),
						'cubic-bezier(.2,.8,.2,1)'     => __( 'Smooth out', 'hi-wpessential' ),
						'cubic-bezier(.34,1.56,.64,1)' => __( 'Springy', 'hi-wpessential' ),
					),
				),
				'hover_lift'     => $px( __( 'Card lift on hover', 'hi-wpessential' ), 0, 16 ),
				'motion_off'     => array(
					'type'  => 'toggle',
					'label' => __( 'Turn off all animations', 'hi-wpessential' ),
				),
			),
		),
	);
}

/**
 * Saved values (only the ones that were set).
 *
 * @return array
 */
function wpessential_site_values() {
	return array_filter(
		(array) get_theme_mod( 'wpessential_site', array() ),
		static fn( $v ) => '' !== $v && null !== $v && false !== $v
	);
}

/**
 * A colour: #rgb, #rrggbb, #rrggbbaa (with opacity), rgb()/rgba(), hsl()/hsla() or a var().
 *
 * @param mixed $value Value.
 * @return string
 */
function wpessential_site_color( $value ) {
	$value = trim( (string) $value );
	if ( preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value ) ) {
		return $value;
	}
	if ( preg_match( '/^(rgba?|hsla?)\(\s*[0-9.%,\s\/-]+\)$/i', $value ) || preg_match( '/^var\(--[a-z0-9-]+\)$/i', $value ) ) {
		return $value;
	}
	return '';
}

/**
 * One value cleaned for its type.
 *
 * @param array $field Field.
 * @param mixed $value Value.
 * @return mixed
 */
function wpessential_site_clean( array $field, $value ) {
	switch ( $field['type'] ) {
		case 'font':
			$value = (string) $value;
			if ( 0 === strpos( $value, 'google:' ) ) {
				return isset( wpessential_google_fonts()[ substr( $value, 7 ) ] ) ? $value : '';
			}
			return isset( wpessential_site_fonts()[ $value ] ) ? $value : '';
		case 'fonts':
			return implode( '|', array_filter( array_map( 'trim', explode( '|', (string) $value ) ), static fn( $f ) => isset( wpessential_google_fonts()[ $f ] ) ) );
		case 'color':
			return wpessential_site_color( $value );
		case 'toggle':
			return (bool) $value;
		case 'range':
			if ( is_string( $value ) && '' !== $value && '{' === $value[0] ) {
				$tree = wpessential_site_clean_tree( wpessential_site_json( $value ) );
				return $tree ? wp_json_encode( $tree ) : '';
			}
			return is_numeric( $value ) ? (string) ( 0 + $value ) : '';
		case 'select':
		case 'choose':
			return array_key_exists( (string) $value, $field['choices'] ) ? (string) $value : '';
		case 'breakpoints':
			$on = implode( '|', array_intersect( array_keys( wpessential_site_breakpoints() ), explode( '|', (string) $value ) ) );
			// "-" keeps "desktop only" apart from "" (the theme default).
			return '' === $on && '' !== (string) $value ? '-' : $on;
		case 'dimensions':
		case 'typography':
		case 'shadow':
		case 'gradient':
		case 'background':
			$tree = wpessential_site_clean_tree( wpessential_site_json( $value ) );
			return $tree ? wp_json_encode( $tree ) : '';
	}
	if ( ! empty( $field['responsive'] ) && is_string( $value ) && '' !== $value && '{' === $value[0] ) {
		$tree = wpessential_site_clean_tree( wpessential_site_json( $value ) );
		return $tree ? wp_json_encode( $tree ) : '';
	}
	return sanitize_text_field( (string) $value );
}

add_action(
	'customize_register',
	static function ( WP_Customize_Manager $wp ) {
		require_once WPESSENTIAL_DIR . '/inc/class-wpessential-customize-control.php';
		// Classic-theme sections a block theme does not use; ours replace them.
		foreach ( array( 'colors', 'header_image', 'background_image' ) as $section ) {
			$wp->remove_section( $section );
		}
		$wp->add_panel(
			'wpessential_site',
			array(
				'title'       => __( 'WPEssential Site Settings', 'hi-wpessential' ),
				'description' => __( 'Global colours, fonts, buttons, forms, layout and motion for the whole site, in Gutenberg and Elementor.', 'hi-wpessential' ),
				'priority'    => 25,
			)
		);
		foreach ( wpessential_site_fields() as $section => $s ) {
			$wp->add_section(
				$section,
				array(
					'title'       => $s['title'],
					'description' => $s['description'] ?? '',
					'panel'       => 'wpessential_site',
				)
			);
			foreach ( $s['fields'] as $key => $f ) {
				$id = 'wpessential_site[' . $key . ']';
				if ( 'heading' === $f['type'] ) {
					$wp->add_control(
						new WPEssential_Customize_Control(
							$wp,
							'wpessential_site_' . $key,
							array(
								'label'    => $f['label'],
								'section'  => $section,
								'settings' => array(),
								'field'    => $f,
							)
						)
					);
					continue;
				}
				$wp->add_setting(
					$id,
					array(
						'type'              => 'theme_mod',
						'default'           => '',
						'transport'         => 'refresh',
						'sanitize_callback' => static fn( $v ) => wpessential_site_clean( $f, $v ),
					)
				);
				$wp->add_control(
					new WPEssential_Customize_Control(
						$wp,
						$id,
						array(
							'label'   => $f['label'],
							'section' => $section,
							'field'   => $f,
						)
					)
				);
			}
		}
	}
);

/**
 * Number with its unit.
 *
 * @param array  $v    Values.
 * @param string $key  Key.
 * @param string $unit Unit.
 * @return string
 */
function wpessential_site_len( array $v, $key, $unit = 'px' ) {
	if ( isset( $v[ $key ] ) && ! is_numeric( $v[ $key ] ) ) {
		// Responsive value: the desktop size (other devices are written as media queries).
		$d = wpessential_site_json( $v[ $key ] );
		return isset( $d['desktop'] ) ? wpessential_site_size( $d['desktop'], $unit ) : '';
	}
	return isset( $v[ $key ] ) && is_numeric( $v[ $key ] ) ? ( 0 + $v[ $key ] ) . $unit : '';
}

/**
 * A preset list (palette, fonts, sizes) from the user layer (demo style variation / Site Editor),
 * falling back to the theme's own list.
 *
 * @param array $settings User settings.
 * @param array $path     Path, e.g. [ 'color', 'palette' ].
 * @return array
 */
function wpessential_site_preset( array $settings, array $path ) {
	$list = _wp_array_get( $settings, $path, array() );
	if ( isset( $list['custom'] ) || isset( $list['theme'] ) ) {
		$list = $list['custom'] ?? $list['theme'];
	}
	if ( ! $list ) {
		$theme = WP_Theme_JSON_Resolver::get_theme_data()->get_settings();
		$list  = _wp_array_get( $theme, $path, array() );
		$list  = $list['theme'] ?? $list;
	}
	return array_values( (array) $list );
}

/**
 * A gradient preset rebuilt from the settings, or '' to keep it.
 *
 * @param string $slug Gradient slug.
 * @param array  $v    Values.
 * @return string
 */
function wpessential_site_gradient( $slug, array $v ) {
	if ( 'fade-bottom' === $slug && ! empty( $v['grad_fade'] ) ) {
		$amount = isset( $v['grad_fade_amount'] ) && is_numeric( $v['grad_fade_amount'] ) ? (int) $v['grad_fade_amount'] : 85;
		return 'linear-gradient(180deg, color-mix(in srgb, ' . $v['grad_fade'] . ' 0%, transparent) 0%, color-mix(in srgb, ' . $v['grad_fade'] . ' ' . $amount . '%, transparent) 100%)';
	}
	if ( ! in_array( $slug, array( 'brand', 'night' ), true ) ) {
		return '';
	}
	$own = wpessential_site_json( $v[ 'grad_' . $slug ] ?? '' );
	if ( $own ) {
		return wpessential_site_gradient_css( $own );
	}
	$from = $v[ 'grad_' . $slug . '_from' ] ?? '';
	$to   = $v[ 'grad_' . $slug . '_to' ] ?? '';
	if ( 'brand' === $slug && ! empty( $v['grad_brand_auto'] ) ) {
		$from = $from ? $from : 'var(--wp--preset--color--primary)';
		$to   = $to ? $to : 'var(--wp--preset--color--secondary)';
	}
	if ( ! $from && ! $to ) {
		return '';
	}
	$from  = $from ? $from : $to;
	$to    = $to ? $to : $from;
	$angle = isset( $v[ 'grad_' . $slug . '_angle' ] ) && is_numeric( $v[ 'grad_' . $slug . '_angle' ] ) ? (int) $v[ 'grad_' . $slug . '_angle' ] : ( 'brand' === $slug ? 120 : 180 );
	if ( 'radial' === ( $v[ 'grad_' . $slug . '_type' ] ?? '' ) ) {
		return 'radial-gradient(circle at 30% 20%, ' . $from . ' 0%, ' . $to . ' 100%)';
	}
	return 'linear-gradient(' . $angle . 'deg, ' . $from . ' 0%, ' . $to . ' 100%)';
}

/**
 * A shadow preset rebuilt from the settings, or '' to keep it.
 *
 * @param array $preset Preset (slug, shadow).
 * @param array $v      Values.
 * @return string
 */
function wpessential_site_shadow( array $preset, array $v ) {
	$box = wpessential_site_json( $v[ 'shadow_' . $preset['slug'] ] ?? '' );
	if ( $box ) {
		return wpessential_site_shadow_css( $box );
	}
	$own = trim( (string) ( $v[ 'shadow_' . $preset['slug'] ] ?? '' ) );
	if ( '' !== $own ) {
		return preg_replace( '/[^a-z0-9#%.,() \-]/i', '', $own );
	}
	if ( empty( $v['shadow_color'] ) && ! isset( $v['shadow_strength'] ) ) {
		return '';
	}
	$hex      = ltrim( (string) ( $v['shadow_color'] ?? '#0a0f21' ), '#' );
	$strength = isset( $v['shadow_strength'] ) && is_numeric( $v['shadow_strength'] ) ? $v['shadow_strength'] / 100 : 1;
	if ( 6 !== strlen( $hex ) ) {
		return '';
	}
	$rgb = implode( ',', array_map( 'hexdec', str_split( $hex, 2 ) ) );
	// Same offsets as the theme, new colour and opacity.
	return preg_replace_callback(
		'/rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*,\s*([\d.]+)\s*\)/',
		static fn( $m ) => 'rgba(' . $rgb . ',' . round( min( 1, (float) $m[1] * $strength ), 3 ) . ')',
		(string) $preset['shadow']
	);
}

// The values go into the user layer of theme.json (above the demo's style variation), so the
// front end, the editor and Elementor share them.
add_filter(
	'wp_theme_json_data_user',
	static function ( $theme_json ) {
		$v = wpessential_site_values();
		if ( ! $v ) {
			return $theme_json;
		}
		$data     = $theme_json->get_data();
		$settings = $data['settings'] ?? array();
		$styles   = $data['styles'] ?? array();

		// Palette.
		$palette  = array();
		$families = array();
		$sizes    = array();
		foreach ( wpessential_site_preset( $settings, array( 'color', 'palette' ) ) as $i => $c ) {
			if ( ! empty( $v[ 'color_' . $c['slug'] ] ) ) {
				$c['color'] = $v[ 'color_' . $c['slug'] ];
			}
			$palette[ $i ] = $c;
		}
		if ( ! empty( $palette ) ) {
			$settings['color']['palette'] = $palette;
		}

		// Fonts.
		$fonts = wpessential_site_fonts();
		foreach ( wpessential_site_preset( $settings, array( 'typography', 'fontFamilies' ) ) as $i => $f ) {
			$pick = $v[ 'font_' . $f['slug'] ] ?? '';
			if ( $pick && 0 === strpos( $pick, 'google:' ) ) {
				$families[ $i ] = wpessential_google_family_entry( $f['slug'], substr( $pick, 7 ) );
				continue;
			}
			if ( $pick && isset( $fonts[ $pick ] ) ) {
				$f = array(
					'slug'       => $f['slug'],
					'name'       => $pick,
					'fontFamily' => $fonts[ $pick ][0],
					'fontFace'   => array_map(
						static fn( $file ) => array(
							'fontFamily'  => $pick,
							'fontStyle'   => false !== strpos( $file, 'italic' ) ? 'italic' : 'normal',
							'fontWeight'  => '100 900',
							'fontDisplay' => 'swap',
							'src'         => array( get_theme_file_uri( 'assets/fonts/' . $file ) ),
						),
						$fonts[ $pick ][1]
					),
				);
				if ( ! $f['fontFace'] ) {
					unset( $f['fontFace'] );
				}
			}
			$families[ $i ] = $f;
		}
		// Extra Google fonts for the editors' font pickers.
		foreach ( array_filter( array_map( 'trim', explode( '|', (string) ( $v['fonts_extra'] ?? '' ) ) ) ) as $family ) {
			if ( isset( wpessential_google_fonts()[ $family ] ) ) {
				$families[] = wpessential_google_family_entry( 'g-' . sanitize_title( $family ), $family );
			}
		}
		if ( ! empty( $families ) ) {
			$settings['typography']['fontFamilies'] = array_values( $families );
		}

		// Font sizes (fluid between the phone and the desktop value).
		foreach ( wpessential_site_preset( $settings, array( 'typography', 'fontSizes' ) ) as $i => $s ) {
			$min = wpessential_site_len( $v, 'size_' . $s['slug'] . '_min' );
			$max = wpessential_site_len( $v, 'size_' . $s['slug'] . '_max' );
			if ( 'small' === $s['slug'] && wpessential_site_len( $v, 'size_small' ) ) {
				$s['size']  = wpessential_site_len( $v, 'size_small' );
				$s['fluid'] = false;
			} elseif ( 'body' === $s['slug'] && wpessential_site_len( $v, 'size_body' ) ) {
				$s['size']  = wpessential_site_len( $v, 'size_body' );
				$s['fluid'] = false;
			} elseif ( $min || $max ) {
				$s['size']  = $max ? $max : $s['size'];
				$s['fluid'] = array(
					'min' => $min ? $min : ( $s['fluid']['min'] ?? $s['size'] ),
					'max' => $max ? $max : ( $s['fluid']['max'] ?? $s['size'] ),
				);
			}
			$sizes[ $i ] = $s;
		}
		if ( ! empty( $sizes ) ) {
			$settings['typography']['fontSizes'] = $sizes;
		}

		// Layout and tokens.
		foreach ( array(
			'content_width' => array( 'layout', 'contentSize' ),
			'wide_width'    => array( 'layout', 'wideSize' ),
		) as $key => $path ) {
			if ( wpessential_site_len( $v, $key ) ) {
				$settings[ $path[0] ][ $path[1] ] = wpessential_site_len( $v, $key );
			}
		}
		foreach ( array( 'sm', 'md', 'lg', 'xl' ) as $r ) {
			if ( wpessential_site_len( $v, 'radius_' . $r ) ) {
				$settings['custom']['radius'][ $r ] = wpessential_site_len( $v, 'radius_' . $r );
			}
		}
		if ( wpessential_site_len( $v, 'header_height' ) ) {
			$settings['custom']['header']['height'] = wpessential_site_len( $v, 'header_height' );
		}
		$hover = array(
			'duration'   => wpessential_site_len( $v, 'hover_duration', 'ms' ),
			'ease'       => $v['hover_ease'] ?? '',
			'lift'       => wpessential_site_len( $v, 'hover_lift' ) ? '-' . wpessential_site_len( $v, 'hover_lift' ) : '',
			'buttonLift' => wpessential_site_len( $v, 'btn_lift' ) ? '-' . wpessential_site_len( $v, 'btn_lift' ) : '',
			'zoom'       => wpessential_site_len( $v, 'image_zoom', '' ),
		);
		foreach ( array_filter( $hover ) as $k => $val ) {
			$settings['custom']['hover'][ $k ] = $val;
		}

		// Headings.
		$heading = array_filter(
			array(
				'fontWeight'    => $v['heading_weight'] ?? '',
				'letterSpacing' => wpessential_site_len( $v, 'heading_spacing', 'em' ),
				'lineHeight'    => wpessential_site_len( $v, 'heading_line', '' ),
				'textTransform' => $v['heading_case'] ?? '',
			)
		);
		if ( $heading ) {
			foreach ( array( 'heading', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $el ) {
				$styles['elements'][ $el ]['typography'] = array_merge( (array) ( $styles['elements'][ $el ]['typography'] ?? array() ), $heading );
			}
		}
		if ( wpessential_site_len( $v, 'line_body', '' ) ) {
			$styles['typography']['lineHeight'] = wpessential_site_len( $v, 'line_body', '' );
		}

		// Buttons.
		$button = (array) ( $styles['elements']['button'] ?? array() );
		if ( wpessential_site_len( $v, 'btn_radius' ) ) {
			$button['border']['radius'] = wpessential_site_len( $v, 'btn_radius' );
		}
		foreach ( array(
			'btn_pad_y' => array( 'top', 'bottom' ),
			'btn_pad_x' => array( 'left', 'right' ),
		) as $key => $sides ) {
			if ( wpessential_site_len( $v, $key ) ) {
				foreach ( $sides as $side ) {
					$button['spacing']['padding'][ $side ] = wpessential_site_len( $v, $key );
				}
				$pad = wpessential_site_json( $v['btn_padding'] ?? '' );
				if ( ! empty( $pad['desktop'] ) && is_array( $pad['desktop'] ) && '' !== wpessential_site_dims( $pad['desktop'] ) ) {
					$sides = explode( ' ', wpessential_site_dims( $pad['desktop'] ) );
					foreach ( array( 'top', 'right', 'bottom', 'left' ) as $n => $side ) {
						$button['spacing']['padding'][ $side ] = $sides[ $n ];
					}
				}
			}
		}
		$button['typography'] = array_merge(
			(array) ( $button['typography'] ?? array() ),
			array_filter(
				array(
					'fontSize'      => wpessential_site_len( $v, 'btn_size' ),
					'fontWeight'    => $v['btn_weight'] ?? '',
					'textTransform' => $v['btn_case'] ?? '',
					'letterSpacing' => wpessential_site_len( $v, 'btn_spacing', 'em' ),
				)
			)
		);
		foreach ( array(
			'btn_bg'   => array( 'color', 'background' ),
			'btn_text' => array( 'color', 'text' ),
		) as $key => $path ) {
			if ( ! empty( $v[ $key ] ) ) {
				$button[ $path[0] ][ $path[1] ] = $v[ $key ];
			}
		}
		foreach ( array(
			'btn_hover_bg'   => 'background',
			'btn_hover_text' => 'text',
		) as $key => $prop ) {
			if ( ! empty( $v[ $key ] ) ) {
				$button[':hover']['color'][ $prop ] = $v[ $key ];
			}
		}
		if ( wpessential_site_len( $v, 'btn_border_w' ) ) {
			$button['border']['width'] = wpessential_site_len( $v, 'btn_border_w' );
			$button['border']['style'] = 'solid';
		}
		if ( ! empty( $v['btn_border'] ) ) {
			$button['border']['color'] = $v['btn_border'];
		}
		if ( ! empty( $v['btn_shadow'] ) ) {
			$button['shadow'] = 'none' === $v['btn_shadow'] ? 'none' : 'var(--wp--preset--shadow--' . $v['btn_shadow'] . ')';
		}
		$styles['elements']['button'] = $button;

		// Gradients.
		$grads = array();
		foreach ( wpessential_site_preset( $settings, array( 'color', 'gradients' ) ) as $g ) {
			$css = wpessential_site_gradient( $g['slug'], $v );
			if ( $css ) {
				$g['gradient'] = $css;
			}
			$grads[] = $g;
		}
		if ( $grads ) {
			$settings['color']['gradients'] = $grads;
		}

		// Shadows.
		$shadows = array();
		foreach ( wpessential_site_preset( $settings, array( 'shadow', 'presets' ) ) as $sh ) {
			$css = wpessential_site_shadow( $sh, $v );
			if ( $css ) {
				$sh['shadow'] = $css;
			}
			$shadows[] = $sh;
		}
		if ( $shadows ) {
			$settings['shadow']['presets'] = $shadows;
		}

		// Links.
		$link = (array) ( $styles['elements']['link'] ?? array() );
		if ( ! empty( $v['link_color'] ) ) {
			$link['color']['text'] = $v['link_color'];
		}
		if ( ! empty( $v['link_hover'] ) ) {
			$link[':hover']['color']['text'] = $v['link_hover'];
		}
		if ( ! empty( $v['link_weight'] ) ) {
			$link['typography']['fontWeight'] = $v['link_weight'];
		}
		if ( 'always' === ( $v['link_underline'] ?? '' ) ) {
			$link['typography']['textDecoration']           = 'underline';
			$link[':hover']['typography']['textDecoration'] = 'underline';
		} elseif ( 'never' === ( $v['link_underline'] ?? '' ) ) {
			$link['typography']['textDecoration']           = 'none';
			$link[':hover']['typography']['textDecoration'] = 'none';
		}
		$styles['elements']['link'] = $link;

		// More type and spacing.
		foreach ( array(
			'h5' => 'size_h5',
			'h6' => 'size_h6',
		) as $el => $key ) {
			if ( wpessential_site_len( $v, $key ) ) {
				$styles['elements'][ $el ]['typography']['fontSize'] = wpessential_site_len( $v, $key );
			}
		}
		if ( ! empty( $v['heading_color'] ) ) {
			$styles['elements']['heading']['color']['text'] = $v['heading_color'];
		}
		if ( ! empty( $v['body_weight'] ) ) {
			$styles['typography']['fontWeight'] = $v['body_weight'];
		}
		if ( wpessential_site_len( $v, 'block_gap' ) ) {
			$styles['spacing']['blockGap'] = wpessential_site_len( $v, 'block_gap' );
		}
		if ( wpessential_site_len( $v, 'gutter' ) ) {
			$side                                  = wpessential_site_len( $v, 'gutter_mobile' ) ? 'clamp(' . wpessential_site_len( $v, 'gutter_mobile' ) . ', 4vw, ' . wpessential_site_len( $v, 'gutter' ) . ')' : wpessential_site_len( $v, 'gutter' );
			$styles['spacing']['padding']['left']  = $side;
			$styles['spacing']['padding']['right'] = $side;
		}

		return $theme_json->update_with(
			array(
				'version'  => 3,
				'settings' => $settings,
				'styles'   => $styles,
			)
		);
	}
);

/**
 * CSS for the settings theme.json cannot express (dark colours, forms, sections, toggles).
 *
 * @return string
 */
function wpessential_site_css() {
	$v   = wpessential_site_values();
	$css = '';
	$dk  = '';
	// Brand colours stay the same in dark mode unless a dark value is set; text and backgrounds do not.
	foreach ( array( 'primary', 'primary-hover', 'secondary', 'accent', 'success', 'warning', 'danger' ) as $slug ) {
		if ( ! empty( $v[ 'color_' . $slug ] ) && empty( $v[ 'dark_' . $slug ] ) ) {
			$v[ 'dark_' . $slug ] = $v[ 'color_' . $slug ];
		}
	}
	foreach ( $v as $key => $val ) {
		if ( 0 === strpos( $key, 'dark_' ) ) {
			$dk .= '--wp--preset--color--' . substr( $key, 5 ) . ':' . $val . ';';
		}
	}
	if ( $dk ) {
		$css .= ':root[data-wpe-scheme="dark"]{' . $dk . '}';
	}
	$field = array_filter(
		array(
			'border-radius'    => wpessential_site_len( $v, 'field_radius' ),
			'min-height'       => wpessential_site_len( $v, 'field_height' ),
			'border-color'     => $v['field_border'] ?? '',
			'background-color' => $v['field_bg'] ?? '',
		)
	);
	if ( $field ) {
		$decl = '';
		foreach ( $field as $p => $val ) {
			$decl .= $p . ':' . $val . ';';
		}
		$css .= 'input:is([type="text"],[type="email"],[type="url"],[type="password"],[type="search"],[type="number"],[type="tel"],[type="date"]),select,.wpe-input{' . $decl . '}textarea{' . str_replace( 'min-height:' . ( $field['min-height'] ?? '' ) . ';', '', $decl ) . '}';
	}
	if ( ! empty( $v['field_focus'] ) ) {
		$css .= 'input:focus,select:focus,textarea:focus,.wpe-input:focus{border-color:' . $v['field_focus'] . ';box-shadow:0 0 0 3px color-mix(in srgb,' . $v['field_focus'] . ' 25%,transparent);outline:0}';
	}
	if ( wpessential_site_len( $v, 'section_space' ) ) {
		$css .= ':root{--wp--preset--spacing--90:clamp(40px,8vw,' . wpessential_site_len( $v, 'section_space' ) . ')}';
	}
	if ( ! empty( $v['image_round'] ) ) {
		$css .= '.wp-block-post-content img,.entry-content img{border-radius:var(--wp--custom--radius--md)}';
	}
	if ( 'off' === ( $v['sticky_header'] ?? '' ) ) {
		$css .= '.wp-block-template-part:has(> .wpe-header),.wpe-header{position:relative;top:auto}';
	}
	if ( 'off' === ( $v['back_to_top'] ?? '' ) ) {
		$css .= '.wpe-back-to-top{display:none!important}';
	}
	$rule = static function ( $sel, array $decls ) {
		$decls = array_filter( $decls, static fn( $x ) => '' !== $x && null !== $x );
		if ( ! $decls ) {
			return '';
		}
		$out = '';
		foreach ( $decls as $p => $x ) {
			$out .= $p . ':' . $x . ';';
		}
		return $sel . '{' . $out . '}';
	};
	if ( wpessential_site_len( $v, 'paragraph_space', 'em' ) ) {
		$css .= '.wp-block-post-content p,.entry-content p{margin-block:0 ' . wpessential_site_len( $v, 'paragraph_space', 'em' ) . '}';
	}
	$css .= $rule(
		'::selection',
		array(
			'background' => $v['selection_bg'] ?? '',
			'color'      => $v['selection_text'] ?? '',
		)
	);
	$css .= $rule(
		'a',
		array(
			'text-decoration-thickness' => wpessential_site_len( $v, 'link_thickness' ),
			'text-underline-offset'     => wpessential_site_len( $v, 'link_offset' ),
		)
	);
	if ( 'text' === ( $v['link_underline'] ?? '' ) ) {
		$css .= '.wp-block-post-content p a,.entry-content p a,.wp-block-post-content li a{text-decoration:underline}';
	}
	$css .= $rule(
		'.wpe-header',
		array(
			'background'   => $v['header_bg'] ?? '',
			'color'        => $v['header_text'] ?? '',
			'border-color' => $v['header_border'] ?? '',
		)
	);
	if ( ! empty( $v['header_text'] ) ) {
		$css .= '.wpe-header a,.wpe-header .wp-block-navigation-item__content,.wpe-header .wp-block-site-title a{color:' . $v['header_text'] . '}';
	}
	if ( ! empty( $v['header_glass'] ) ) {
		$css .= '.wpe-header{background:color-mix(in srgb,' . ( $v['header_bg'] ?? 'var(--wp--preset--color--surface)' ) . ' 72%,transparent);-webkit-backdrop-filter:saturate(1.6) blur(14px);backdrop-filter:saturate(1.6) blur(14px)}';
	}
	if ( 'off' === ( $v['header_shadow'] ?? '' ) ) {
		$css .= '.wpe-header.is-scrolled{box-shadow:none}';
	}
	$css .= $rule(
		'.wpe-header .wp-block-navigation',
		array(
			'font-size'               => wpessential_site_len( $v, 'menu_size' ),
			'font-weight'             => $v['menu_weight'] ?? '',
			'gap'                     => wpessential_site_len( $v, 'menu_gap' ),
			'--navigation-layout-gap' => wpessential_site_len( $v, 'menu_gap' ),
		)
	);
	$css .= $rule(
		'.wpe-footer',
		array(
			'background' => $v['footer_bg'] ?? '',
			'color'      => $v['footer_text'] ?? '',
		)
	);
	$css .= $rule( '.wpe-footer :is(h1,h2,h3,h4,h5,h6,.wp-block-heading)', array( 'color' => $v['footer_heading'] ?? '' ) );
	$css .= $rule( '.wpe-footer a', array( 'color' => $v['footer_link'] ?? '' ) );
	$css .= $rule( '.wpe-footer a:hover,.wpe-footer a:focus-visible', array( 'color' => $v['footer_link_hover'] ?? '' ) );
	if ( ! empty( $v['focus_color'] ) || wpessential_site_len( $v, 'focus_width' ) ) {
		$css .= ':root{--wpe-focus:0 0 0 ' . ( wpessential_site_len( $v, 'focus_width' ) ? wpessential_site_len( $v, 'focus_width' ) : '3px' ) . ' ' . ( $v['focus_color'] ?? 'var(--wp--preset--color--primary)' ) . '}';
	}
	if ( 'show' === ( $v['skip_link'] ?? '' ) ) {
		$css .= '.skip-link.screen-reader-text{position:static!important;clip:auto!important;clip-path:none!important;width:auto!important;height:auto!important;display:block;padding:8px 16px;background:var(--wp--preset--color--ink);color:#fff}';
	}
	if ( wpessential_site_len( $v, 'min_text' ) ) {
		$css .= ':root{--wp--preset--font-size--xs:max(var(--wp--preset--font-size--xs,12px),' . wpessential_site_len( $v, 'min_text' ) . ')}small,.has-xs-font-size,figcaption{font-size:max(1em,' . wpessential_site_len( $v, 'min_text' ) . ')}';
	}
	// Gradients in dark mode too (the dark palette brings its own otherwise).
	$dark_grads = '';
	foreach ( array( 'brand', 'night', 'fade-bottom' ) as $slug ) {
		$g = wpessential_site_gradient( $slug, $v );
		if ( $g ) {
			$dark_grads .= '--wp--preset--gradient--' . $slug . ':' . $g . ';';
		}
	}
	if ( $dark_grads ) {
		$css .= ':root[data-wpe-scheme]{' . $dark_grads . '}';
	}
	$css .= wpessential_site_controls_css( $v );
	if ( ! empty( $v['motion_off'] ) ) {
		$css .= '*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}';
	}
	return $css;
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		$css = wpessential_site_css();
		if ( '' !== $css ) {
			wp_add_inline_style( 'wpessential', $css );
		}
	},
	25
);

// Dark-mode switch settings.
add_filter(
	'wpessential_scheme_toggle',
	static fn( $on ) => 'off' === ( wpessential_site_values()['scheme_toggle'] ?? '' ) ? false : $on
);
add_filter(
	'wpessential_scheme_follow_os',
	static fn( $on ) => 'fixed' === ( wpessential_site_values()['scheme_follow'] ?? '' ) ? false : $on
);

// After saving: drop the cached theme.json and let Elementor pick up the new palette and fonts.
add_action(
	'customize_save_after',
	static function () {
		if ( function_exists( 'wp_clean_theme_json_cache' ) ) {
			wp_clean_theme_json_cache();
		}
		if ( function_exists( 'wpessential_sync_elementor_kit' ) ) {
			wpessential_sync_elementor_kit();
		}
	}
);

// One place for the site: the WordPress sections this theme uses go into the Site Settings panel;
// the ones a block theme does not use are removed. Menus stays its own panel (WordPress needs that).
add_action(
	'customize_register',
	static function ( WP_Customize_Manager $wp ) {
		$move = array(
			'title_tagline'               => 1,
			'static_front_page'           => 2,
			'woocommerce_store_notice'    => 900,
			'woocommerce_product_catalog' => 901,
			'woocommerce_product_images'  => 902,
			'woocommerce_checkout'        => 903,
			'custom_css'                  => 999,
		);
		foreach ( $move as $id => $priority ) {
			$section = $wp->get_section( $id );
			if ( $section ) {
				$section->panel    = 'wpessential_site';
				$section->priority = $priority;
			}
		}
		// The WooCommerce panel is empty now; widgets and the classic options are not used by a block theme.
		$wp->remove_panel( 'woocommerce' );
		$wp->remove_panel( 'widgets' );
		foreach ( array( 'colors', 'header_image', 'background_image' ) as $id ) {
			$wp->remove_section( $id );
		}
		// Keep our panel at the top, Menus right after it.
		$panel = $wp->get_panel( 'wpessential_site' );
		if ( $panel ) {
			$panel->priority = 1;
		}
		$menus = $wp->get_panel( 'nav_menus' );
		if ( $menus ) {
			$menus->priority = 2;
		}
	},
	1000
);

// Our controls: script and styles for the Customizer pane.
add_action(
	'customize_controls_enqueue_scripts',
	static function () {
		$ver = wp_get_theme()->get( 'Version' ) . '.' . (int) filemtime( WPESSENTIAL_DIR . '/assets/js/customizer-controls.js' );
		wp_enqueue_style( 'wpessential-customizer', get_theme_file_uri( 'assets/css/customizer-controls.css' ), array(), $ver );
		wp_enqueue_media();
		wp_enqueue_style( 'wpessential-pickr', get_theme_file_uri( 'assets/vendor/pickr/nano.min.css' ), array(), '1.9.1' );
		wp_enqueue_script( 'wpessential-pickr', get_theme_file_uri( 'assets/vendor/pickr/pickr.min.js' ), array(), '1.9.1', true );
		wp_enqueue_script( 'wpessential-controls', get_theme_file_uri( 'assets/js/wpe-controls.js' ), array( 'wp-i18n', 'wpessential-pickr' ), $ver, true );
		wp_enqueue_script( 'wpessential-customizer', get_theme_file_uri( 'assets/js/customizer-controls.js' ), array( 'customize-controls', 'wpessential-controls' ), $ver, true );
		// The site's own palette (the demo's when it has one), each colour once.
		$origins = (array) wp_get_global_settings( array( 'color', 'palette' ) );
		$palette = array();
		$seen    = array();
		foreach ( (array) ( $origins['custom'] ?? $origins['theme'] ?? array() ) as $c ) {
			if ( isset( $c['slug'], $c['color'] ) && preg_match( '/^#[0-9a-f]{3,8}$/i', $c['color'] ) && ! isset( $seen[ strtolower( $c['color'] ) ] ) ) {
				$seen[ strtolower( $c['color'] ) ] = true;
				$palette[ $c['slug'] ]             = array( $c['name'] ?? $c['slug'], $c['color'] );
			}
		}
		wp_localize_script(
			'wpessential-controls',
			'wpessentialControls',
			array(
				'palette'       => $palette,
				'themeFonts'    => array_keys( wpessential_site_fonts() ),
				'themeStacks'   => array_map( static fn( $f ) => $f[0], wpessential_site_fonts() ),
				'fontFiles'     => array_map( static fn( $f ) => $f[1] ? get_theme_file_uri( 'assets/fonts/' . $f[1][0] ) : '', wpessential_site_fonts() ),
				'googleList'    => get_theme_file_uri( 'assets/data/google-fonts.json' ),
				'breakpoints'   => array_map(
					static fn( $b ) => array(
						'label' => $b[0],
						'width' => $b[2],
					),
					wpessential_site_breakpoints()
				),
				'breakpointsOn' => wpessential_site_breakpoints_on(),
			)
		);
	}
);

// Block themes hide Appearance > Customize; link our settings there.
add_action(
	'admin_menu',
	static function () {
		// With the Core plugin, Site Settings sits in the WPEssential menu; otherwise under Appearance.
		if ( class_exists( '\\WPEssential\\Core\\Admin' ) ) {
			return;
		}
		add_theme_page(
			__( 'Site Settings', 'hi-wpessential' ),
			__( 'Site Settings', 'hi-wpessential' ),
			'edit_theme_options',
			'customize.php?autofocus[panel]=wpessential_site'
		);
	}
);
