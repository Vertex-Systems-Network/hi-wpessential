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
 * Sections and their settings. Each setting: type, label, default '' (= theme default).
 *
 * @return array
 */
function wpessential_site_fields() {
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
		'font_body'       => array(
			'type'    => 'select',
			'label'   => __( 'Body font', 'hi-wpessential' ),
			'choices' => $font_choices,
		),
		'font_heading'    => array(
			'type'    => 'select',
			'label'   => __( 'Heading font', 'hi-wpessential' ),
			'choices' => $font_choices,
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
	) as $slug => $d ) {
		/* translators: %s: heading level. */
		$typography[ 'size_' . $slug . '_min' ] = $px( sprintf( __( '%s size on phones', 'hi-wpessential' ), $d[0] ), 12, 120 );
		/* translators: %s: heading level. */
		$typography[ 'size_' . $slug . '_max' ] = $px( sprintf( __( '%s size on desktop', 'hi-wpessential' ), $d[0] ), 12, 160 );
	}
	return array(
		'wpessential_site_colors' => array(
			'title'       => __( 'Global colours', 'hi-wpessential' ),
			'description' => __( 'The site palette. Every block, kit element and Elementor widget uses these. Leave empty to keep the demo’s colour.', 'hi-wpessential' ),
			'fields'      => $light,
		),
		'wpessential_site_dark'   => array(
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
		'wpessential_site_type'   => array(
			'title'       => __( 'Typography', 'hi-wpessential' ),
			'description' => __( 'Fonts are bundled with the theme (no Google request). Heading sizes scale smoothly between the phone and the desktop value.', 'hi-wpessential' ),
			'fields'      => $typography,
		),
		'wpessential_site_button' => array(
			'title'  => __( 'Buttons', 'hi-wpessential' ),
			'fields' => array(
				'btn_radius'  => $px( __( 'Corner radius', 'hi-wpessential' ), 0, 60 ),
				'btn_pad_y'   => $px( __( 'Padding top / bottom', 'hi-wpessential' ), 4, 32 ),
				'btn_pad_x'   => $px( __( 'Padding left / right', 'hi-wpessential' ), 8, 64 ),
				'btn_size'    => $px( __( 'Text size', 'hi-wpessential' ), 11, 22 ),
				'btn_weight'  => array(
					'type'    => 'select',
					'label'   => __( 'Text weight', 'hi-wpessential' ),
					'choices' => $weights,
				),
				'btn_case'    => array(
					'type'    => 'select',
					'label'   => __( 'Letter case', 'hi-wpessential' ),
					'choices' => array(
						''          => __( 'Theme default', 'hi-wpessential' ),
						'none'      => __( 'As typed', 'hi-wpessential' ),
						'uppercase' => __( 'UPPERCASE', 'hi-wpessential' ),
					),
				),
				'btn_lift'    => $px( __( 'Lift on hover', 'hi-wpessential' ), 0, 8 ),
				'btn_spacing' => $px( __( 'Letter spacing (em)', 'hi-wpessential' ), 0, 0.2, 0.01, 'em' ),
			),
		),
		'wpessential_site_forms'  => array(
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
		'wpessential_site_shape'  => array(
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
		'wpessential_site_layout' => array(
			'title'       => __( 'Layout', 'hi-wpessential' ),
			'description' => __( 'Breakpoints are fixed in the theme (1920, 1440, 1280, 1024, 768, 560, 480, 360, 320) so every demo and element lines up.', 'hi-wpessential' ),
			'fields'      => array(
				'content_width' => $px( __( 'Content width', 'hi-wpessential' ), 480, 1200 ),
				'wide_width'    => $px( __( 'Wide width', 'hi-wpessential' ), 900, 1920 ),
				'section_space' => $px( __( 'Space between sections (desktop)', 'hi-wpessential' ), 24, 200 ),
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
		'wpessential_site_motion' => array(
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
 * One value cleaned for its type.
 *
 * @param array $field Field.
 * @param mixed $value Value.
 * @return mixed
 */
function wpessential_site_clean( array $field, $value ) {
	switch ( $field['type'] ) {
		case 'color':
			return (string) sanitize_hex_color( $value );
		case 'toggle':
			return (bool) $value;
		case 'range':
			return is_numeric( $value ) ? (string) ( 0 + $value ) : '';
		case 'select':
			return array_key_exists( (string) $value, $field['choices'] ) ? (string) $value : '';
	}
	return sanitize_text_field( (string) $value );
}

add_action(
	'customize_register',
	static function ( WP_Customize_Manager $wp ) {
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
				$wp->add_setting(
					$id,
					array(
						'type'              => 'theme_mod',
						'default'           => '',
						'transport'         => 'refresh',
						'sanitize_callback' => static fn( $v ) => wpessential_site_clean( $f, $v ),
					)
				);
				$args = array(
					'label'   => $f['label'],
					'section' => $section,
				);
				if ( 'color' === $f['type'] ) {
					$wp->add_control( new WP_Customize_Color_Control( $wp, $id, $args ) );
					continue;
				}
				if ( 'range' === $f['type'] ) {
					$args['type']        = 'number';
					$args['input_attrs'] = array(
						'min'         => $f['min'],
						'max'         => $f['max'],
						'step'        => $f['step'],
						'placeholder' => __( 'Theme default', 'hi-wpessential' ),
					);
					$args['description'] = $f['unit'] ? sprintf( /* translators: %s: CSS unit. */ __( 'In %s. Empty = theme default.', 'hi-wpessential' ), $f['unit'] ) : __( 'Empty = theme default.', 'hi-wpessential' );
				} elseif ( 'select' === $f['type'] ) {
					$args['type']    = 'select';
					$args['choices'] = $f['choices'];
				} elseif ( 'toggle' === $f['type'] ) {
					$args['type'] = 'checkbox';
				}
				$wp->add_control( $id, $args );
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
							'src'         => array( 'file:./assets/fonts/' . $file ),
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
		if ( ! empty( $families ) ) {
			$settings['typography']['fontFamilies'] = $families;
		}

		// Font sizes (fluid between the phone and the desktop value).
		foreach ( wpessential_site_preset( $settings, array( 'typography', 'fontSizes' ) ) as $i => $s ) {
			$min = wpessential_site_len( $v, 'size_' . $s['slug'] . '_min' );
			$max = wpessential_site_len( $v, 'size_' . $s['slug'] . '_max' );
			if ( 'body' === $s['slug'] && wpessential_site_len( $v, 'size_body' ) ) {
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
			}
		}
		$button['typography']         = array_merge(
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
		$styles['elements']['button'] = $button;

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

// Block themes hide Appearance > Customize; link our settings there.
add_action(
	'admin_menu',
	static function () {
		add_theme_page(
			__( 'Site Settings', 'hi-wpessential' ),
			__( 'Site Settings', 'hi-wpessential' ),
			'edit_theme_options',
			'customize.php?autofocus[panel]=wpessential_site'
		);
	}
);
