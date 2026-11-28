<?php
/**
 * Site Settings: the Elementor-style controls (responsive sliders, dimensions, choose,
 * typography, box shadow, gradient, background, normal/hover tabs, headings) and the CSS
 * they write. The control UI lives in assets/js/wpe-controls.js; docs/wiki/Controls.md
 * explains how to reuse each control.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

/**
 * The theme breakpoints, in order from widest to narrowest: key => [ label, media query, preview width ].
 * "desktop" is the 1440 base. Old values saved as "tablet" / "mobile" read as 768 / 560.
 *
 * @return array
 */
function wpessential_site_breakpoints() {
	$model = wpessential_site_kit_breakpoint_model();
	if ( $model ) {
		return array_map(
			static fn( $row ) => array( $row['label'], $row['media'], $row['width'] ),
			$model
		);
	}
	return array(
		'1920'    => array( __( 'Wide screen (1920 and up)', 'hi-wpessential' ), '@media (min-width: 1920px)', 1920 ),
		'desktop' => array( __( 'Desktop (1440, base)', 'hi-wpessential' ), '', 0 ),
		'1280'    => array( __( 'Laptop (1280 to 1439)', 'hi-wpessential' ), '@media (max-width: 1439px)', 1280 ),
		'1024'    => array( __( 'Tablet landscape (1024 to 1279)', 'hi-wpessential' ), '@media (max-width: 1279px)', 1024 ),
		'768'     => array( __( 'Tablet (768 to 1023)', 'hi-wpessential' ), '@media (max-width: 1023px)', 768 ),
		'560'     => array( __( 'Mobile landscape (560 to 767)', 'hi-wpessential' ), '@media (max-width: 767px)', 560 ),
		'480'     => array( __( 'Mobile (480 to 559)', 'hi-wpessential' ), '@media (max-width: 559px)', 480 ),
		'360'     => array( __( 'Small mobile (360 to 479)', 'hi-wpessential' ), '@media (max-width: 479px)', 360 ),
		'320'     => array( __( 'Smallest (320 to 359)', 'hi-wpessential' ), '@media (max-width: 359px)', 320 ),
	);
}

/**
 * Canonical breakpoint bridge, provided only after explicit plugin handoff.
 *
 * @return array
 */
function wpessential_site_kit_breakpoint_model() {
	$model = apply_filters( 'wpessential_site_kit_legacy_breakpoint_model', array() );
	return is_array( $model ) && isset( $model['desktop'] ) ? $model : array();
}

/**
 * Breakpoints switched on in Site Settings → Layout (desktop is always on).
 *
 * @return string[]
 */
function wpessential_site_breakpoints_on() {
	$model = wpessential_site_kit_breakpoint_model();
	if ( $model ) {
		return array_keys( array_filter( $model, static fn( $row ) => $row['on'] ) );
	}
	$v   = function_exists( 'wpessential_site_values' ) ? (array) get_theme_mod( 'wpessential_site', array() ) : array();
	$raw = (string) ( $v['breakpoints'] ?? '' );
	$on  = '' === $raw ? array( '768', '560' ) : array_filter( explode( '|', $raw ) );
	return array_values( array_intersect( array_keys( wpessential_site_breakpoints() ), array_merge( array( 'desktop' ), $on ) ) );
}

/**
 * Per-breakpoint values with the old device names mapped.
 *
 * @param array $val Value { desktop, 768, … }.
 * @return array
 */
function wpessential_site_by_bp( array $val ) {
	foreach ( array(
		'tablet' => '768',
		'mobile' => '560',
	) as $old => $new ) {
		if ( isset( $val[ $old ] ) && ! isset( $val[ $new ] ) ) {
			$val[ $new ] = $val[ $old ];
		}
		unset( $val[ $old ] );
	}
	return $val;
}

/**
 * Selectors the responsive and composite settings write to.
 *
 * @return array key => [ selector, property ]
 */
function wpessential_site_targets() {
	$button = '.wp-element-button,.wp-block-button__link,.wpe-btn';
	$field  = 'input:is([type="text"],[type="email"],[type="url"],[type="password"],[type="search"],[type="number"],[type="tel"],[type="date"]),select,textarea,.wpe-input';
	return array(
		'size_body'     => array( 'body', 'font-size' ),
		'section_space' => array( ':root', '--wp--preset--spacing--90' ),
		'header_height' => array( ':root', '--wp--custom--header--height' ),
		'menu_size'     => array( '.wpe-header .wp-block-navigation', 'font-size' ),
		'menu_gap'      => array( '.wpe-header .wp-block-navigation', 'gap' ),
		'btn_size'      => array( $button, 'font-size' ),
		'btn_padding'   => array( $button, 'padding' ),
		'field_height'  => array( $field, 'min-height' ),
		'field_padding' => array( $field, 'padding' ),
		'gutter'        => array( ':root', '--wp--style--root--padding-left|--wp--style--root--padding-right' ),
		'typo_body'     => array( 'body', '' ),
		'typo_h1'       => array( 'h1,.wp-block-post-title', '' ),
		'typo_h2'       => array( 'h2', '' ),
		'typo_h3'       => array( 'h3', '' ),
		'typo_h4'       => array( 'h4', '' ),
		'typo_h5'       => array( 'h5', '' ),
		'typo_h6'       => array( 'h6', '' ),
		'typo_button'   => array( $button, '' ),
		'typo_menu'     => array( '.wpe-header .wp-block-navigation-item__content', '' ),
		'typo_small'    => array( 'small,.has-small-font-size,figcaption', '' ),
		'site_bg'       => array( 'body', '' ),
		'site_bg_dark'  => array( ':root[data-wpe-scheme="dark"] body', '' ),
	);
}

/**
 * Apply the new control types to the base field list.
 *
 * @param array $sections Sections.
 * @return array
 */
function wpessential_site_fields_upgrade( array $sections ) {
	$case_icons = array(
		''           => array( 'Theme default', '–' ),
		'none'       => array( 'As typed', 'Aa' ),
		'uppercase'  => array( 'UPPERCASE', 'AA' ),
		'capitalize' => array( 'Capitalize', 'Ab' ),
	);
	// Responsive sliders (desktop / tablet / mobile, like Elementor).
	foreach ( array( 'size_body', 'section_space', 'header_height', 'menu_size', 'menu_gap', 'btn_size', 'field_height', 'gutter' ) as $key ) {
		foreach ( $sections as $sid => $sec ) {
			if ( isset( $sec['fields'][ $key ] ) ) {
				$sections[ $sid ]['fields'][ $key ]['responsive'] = true;
			}
		}
	}
	unset( $sections['wpessential_site_layout']['fields']['gutter_mobile'] );
	$sections['wpessential_site_layout']['fields'] = array(
		'breakpoints' => array(
			'type'    => 'breakpoints',
			'label'   => __( 'Breakpoints you edit', 'hi-wpessential' ),
			'help'    => __( 'Switch on the screen sizes you want to set. Every responsive option shows these, each with its own icon. Desktop (1440) is always on.', 'hi-wpessential' ),
			'choices' => array_map( static fn( $b ) => $b[0], wpessential_site_breakpoints() ),
		),
	) + $sections['wpessential_site_layout']['fields'];

	// Letter case as icon buttons.
	foreach ( array( array( 'wpessential_site_type', 'heading_case' ), array( 'wpessential_site_button', 'btn_case' ) ) as $p ) {
		if ( isset( $sections[ $p[0] ]['fields'][ $p[1] ] ) ) {
			$f            = &$sections[ $p[0] ]['fields'][ $p[1] ];
			$f['type']    = 'choose';
			$f['choices'] = array_intersect_key( $case_icons, $f['choices'] );
			unset( $f );
		}
	}

	// Buttons: padding as dimensions, colours in Normal / Hover tabs.
	$btn = &$sections['wpessential_site_button']['fields'];
	$btn = wpessential_site_insert(
		$btn,
		'btn_pad_x',
		array(
			'btn_padding' => array(
				'type'       => 'dimensions',
				'label'      => __( 'Padding', 'hi-wpessential' ),
				'responsive' => true,
				'units'      => array( 'px', 'em', 'rem' ),
			),
		)
	);
	unset( $btn['btn_pad_y'], $btn['btn_pad_x'] );
	foreach ( array( 'btn_bg', 'btn_text', 'btn_border' ) as $key ) {
		$btn[ $key ]['tab'] = 'normal';
	}
	foreach ( array( 'btn_hover_bg', 'btn_hover_text' ) as $key ) {
		$btn[ $key ]['tab'] = 'hover';
	}
	$btn['btn_hover_border'] = array(
		'type'  => 'color',
		'label' => __( 'Border colour on hover', 'hi-wpessential' ),
		'tab'   => 'hover',
	);
	unset( $btn );

	// Forms: padding.
	$sections['wpessential_site_forms']['fields'] = wpessential_site_insert(
		$sections['wpessential_site_forms']['fields'],
		'field_height',
		array(
			'field_padding' => array(
				'type'       => 'dimensions',
				'label'      => __( 'Padding', 'hi-wpessential' ),
				'responsive' => true,
				'units'      => array( 'px', 'em', 'rem' ),
			),
		)
	);

	// Shadows: box-shadow controls.
	foreach ( array( 'sm', 'md', 'lg' ) as $size ) {
		$sections['wpessential_site_shadows']['fields'][ 'shadow_' . $size ]['type'] = 'shadow';
	}

	// Gradients: one gradient control each.
	$g = $sections['wpessential_site_gradients']['fields'];
	$sections['wpessential_site_gradients']['fields'] = array(
		'grad_brand_auto'  => $g['grad_brand_auto'],
		'grad_brand'       => array(
			'type'  => 'gradient',
			'label' => __( 'Brand gradient', 'hi-wpessential' ),
		),
		'grad_night'       => array(
			'type'  => 'gradient',
			'label' => __( 'Night gradient (dark sections)', 'hi-wpessential' ),
		),
		'grad_fade'        => $g['grad_fade'],
		'grad_fade_amount' => $g['grad_fade_amount'],
	);

	// Header & footer: headings between the two groups.
	$hf = $sections['wpessential_site_header']['fields'];
	$sections['wpessential_site_header']['fields'] = array(
		'h_header' => array(
			'type'  => 'heading',
			'label' => __( 'Header', 'hi-wpessential' ),
		),
	) + $hf;
	$sections['wpessential_site_header']['fields'] = wpessential_site_insert(
		$sections['wpessential_site_header']['fields'],
		'menu_gap',
		array(
			'h_footer' => array(
				'type'  => 'heading',
				'label' => __( 'Footer', 'hi-wpessential' ),
			),
		)
	);

	// Global typography (Elementor's Global Fonts, with more).
	$typo = array();
	foreach ( array(
		'typo_body'   => __( 'Body text', 'hi-wpessential' ),
		'typo_h1'     => 'H1',
		'typo_h2'     => 'H2',
		'typo_h3'     => 'H3',
		'typo_h4'     => 'H4',
		'typo_h5'     => 'H5',
		'typo_h6'     => 'H6',
		'typo_button' => __( 'Buttons', 'hi-wpessential' ),
		'typo_menu'   => __( 'Menu', 'hi-wpessential' ),
		'typo_small'  => __( 'Small text and captions', 'hi-wpessential' ),
	) as $key => $label ) {
		$typo[ $key ] = array(
			'type'  => 'typography',
			'label' => $label,
		);
	}
	$sections = wpessential_site_insert(
		$sections,
		'wpessential_site_type',
		array(
			'wpessential_site_gtype' => array(
				'title'       => __( 'Global typography', 'hi-wpessential' ),
				'description' => __( 'One typography set per text style: font, size per device, weight, case, style, decoration, line height and letter spacing. Click the pencil to open a set.', 'hi-wpessential' ),
				'fields'      => $typo,
			),
			'wpessential_site_bg'    => array(
				'title'  => __( 'Site background', 'hi-wpessential' ),
				'fields' => array(
					'site_bg'      => array(
						'type'  => 'background',
						'label' => __( 'Background', 'hi-wpessential' ),
					),
					'site_bg_dark' => array(
						'type'  => 'background',
						'label' => __( 'Background in dark mode', 'hi-wpessential' ),
					),
				),
			),
		)
	);
	return $sections;
}

/**
 * Insert items after a key.
 *
 * @param array  $items_in List.
 * @param string $after    Key.
 * @param array  $items    Items.
 * @return array
 */
function wpessential_site_insert( array $items_in, $after, array $items ) {
	$pos = array_search( $after, array_keys( $items_in ), true );
	if ( false === $pos ) {
		return $items_in + $items;
	}
	return array_slice( $items_in, 0, $pos + 1, true ) + $items + array_slice( $items_in, $pos + 1, null, true );
}

/**
 * A stored composite / responsive value as an array.
 *
 * @param mixed $value Value.
 * @return array
 */
function wpessential_site_json( $value ) {
	if ( is_array( $value ) ) {
		return $value;
	}
	$d = is_string( $value ) && '' !== $value && '{' === $value[0] ? json_decode( $value, true ) : null;
	return is_array( $d ) ? $d : array();
}

/**
 * Clean a composite value: plain CSS-safe strings and numbers only, URLs escaped.
 *
 * @param mixed $value Value.
 * @param int   $depth Nesting.
 * @return mixed
 */
function wpessential_site_clean_tree( $value, $depth = 0 ) {
	if ( is_array( $value ) ) {
		if ( $depth > 3 ) {
			return array();
		}
		$out = array();
		foreach ( $value as $k => $v ) {
			$k = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $k );
			if ( '' === $k ) {
				continue;
			}
			$out[ $k ] = 'url' === $k ? esc_url_raw( (string) $v ) : wpessential_site_clean_tree( $v, $depth + 1 );
		}
		return array_filter( $out, static fn( $x ) => '' !== $x && null !== $x && array() !== $x );
	}
	if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
		return $value;
	}
	return preg_replace( '/[^a-zA-Z0-9#%.,()\/ :_\-]/', '', (string) $value );
}

/**
 * One length from a number or { size, unit }.
 *
 * @param mixed  $v    Value.
 * @param string $unit Default unit.
 * @return string
 */
function wpessential_site_size( $v, $unit = 'px' ) {
	if ( is_array( $v ) ) {
		$unit = (string) ( $v['unit'] ?? $unit );
		$v    = $v['size'] ?? '';
	}
	return is_numeric( $v ) ? ( 0 + $v ) . $unit : '';
}

/**
 * Dimensions { top, right, bottom, left, unit } as a CSS value.
 *
 * @param array $d Dimensions.
 * @return string
 */
function wpessential_site_dims( array $d ) {
	$unit  = (string) ( $d['unit'] ?? 'px' );
	$sides = array();
	foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
		if ( ! isset( $d[ $side ] ) || ! is_numeric( $d[ $side ] ) ) {
			return '';
		}
		$sides[] = ( 0 + $d[ $side ] ) . $unit;
	}
	return implode( ' ', $sides );
}

/**
 * Typography { family, size{desktop,tablet,mobile}, unit, weight, transform, style, decoration,
 * lineHeight, letterSpacing } as declarations per device.
 *
 * @param array $t Typography.
 * @return array device => declarations
 */
function wpessential_site_typo( array $t ) {
	$out    = array_fill_keys( array_keys( wpessential_site_breakpoints() ), array() );
	$family = (string) ( $t['family'] ?? '' );
	if ( '' !== $family ) {
		$out['desktop']['font-family'] = 0 === strpos( $family, 'google:' ) ? wpessential_google_stack( substr( $family, 7 ) ) : ( wpessential_site_fonts()[ $family ][0] ?? '' );
	}
	foreach ( array(
		'size'          => 'font-size',
		'lineHeight'    => 'line-height',
		'letterSpacing' => 'letter-spacing',
	) as $key => $prop ) {
		$val = wpessential_site_by_bp( wpessential_site_json( $t[ $key ] ?? array() ) );
		foreach ( array_keys( wpessential_site_breakpoints() ) as $device ) {
			$css = wpessential_site_size( $val[ $device ] ?? '', 'lineHeight' === $key ? '' : 'px' );
			if ( '' !== $css ) {
				$out[ $device ][ $prop ] = $css;
			}
		}
	}
	foreach ( array(
		'weight'     => 'font-weight',
		'transform'  => 'text-transform',
		'style'      => 'font-style',
		'decoration' => 'text-decoration',
	) as $key => $prop ) {
		if ( ! empty( $t[ $key ] ) ) {
			$out['desktop'][ $prop ] = (string) $t[ $key ];
		}
	}
	return $out;
}

/**
 * Gradient { type, angle, from, fromStop, to, toStop } as CSS.
 *
 * @param array $g Gradient.
 * @return string
 */
function wpessential_site_gradient_css( array $g ) {
	if ( empty( $g['from'] ) && empty( $g['to'] ) ) {
		return '';
	}
	$from = (string) ( $g['from'] ?? $g['to'] );
	$to   = (string) ( $g['to'] ?? $g['from'] );
	$a    = is_numeric( $g['fromStop'] ?? '' ) ? (int) $g['fromStop'] : 0;
	$b    = is_numeric( $g['toStop'] ?? '' ) ? (int) $g['toStop'] : 100;
	if ( 'radial' === ( $g['type'] ?? '' ) ) {
		return 'radial-gradient(circle at ' . preg_replace( '/[^a-z ]/', '', (string) ( $g['position'] ?? 'center center' ) ) . ', ' . $from . ' ' . $a . '%, ' . $to . ' ' . $b . '%)';
	}
	$angle = is_numeric( $g['angle'] ?? '' ) ? (int) $g['angle'] : 180;
	return 'linear-gradient(' . $angle . 'deg, ' . $from . ' ' . $a . '%, ' . $to . ' ' . $b . '%)';
}

/**
 * Box shadow { x, y, blur, spread, color, inset } as CSS.
 *
 * @param array $s Shadow.
 * @return string
 */
function wpessential_site_shadow_css( array $s ) {
	if ( ! isset( $s['x'] ) && ! isset( $s['y'] ) && ! isset( $s['blur'] ) ) {
		return '';
	}
	$n = static fn( $k ) => ( is_numeric( $s[ $k ] ?? '' ) ? 0 + $s[ $k ] : 0 ) . 'px';
	return ( ! empty( $s['inset'] ) ? 'inset ' : '' ) . $n( 'x' ) . ' ' . $n( 'y' ) . ' ' . $n( 'blur' ) . ' ' . $n( 'spread' ) . ' ' . ( $s['color'] ?? 'rgba(0,0,0,.15)' );
}

/**
 * Background { type: classic|gradient, color, image{url}, position, size, repeat, attachment, gradient{} }.
 *
 * @param array $b Background.
 * @return array declarations
 */
function wpessential_site_background( array $b ) {
	$d = array();
	if ( 'gradient' === ( $b['type'] ?? '' ) ) {
		$g = wpessential_site_gradient_css( wpessential_site_json( $b['gradient'] ?? array() ) );
		if ( $g ) {
			$d['background-image'] = $g;
		}
		return $d;
	}
	if ( ! empty( $b['color'] ) ) {
		$d['background-color'] = (string) $b['color'];
	}
	if ( ! empty( $b['image']['url'] ) ) {
		$d['background-image'] = 'url("' . esc_url_raw( $b['image']['url'] ) . '")';
		foreach ( array( 'position', 'size', 'repeat', 'attachment' ) as $k ) {
			if ( ! empty( $b[ $k ] ) ) {
				$d[ 'background-' . $k ] = (string) $b[ $k ];
			}
		}
	}
	return $d;
}

/**
 * CSS of the responsive and composite settings.
 *
 * @param array $v Values.
 * @return string
 */
function wpessential_site_controls_css( array $v ) {
	// Base first, so every media query after it can override it.
	$media = array( 'desktop' => '' ) + array_map( static fn( $b ) => $b[1], wpessential_site_breakpoints() );
	$rules = array_fill_keys( array_keys( $media ), array() );
	$on    = array_flip( wpessential_site_breakpoints_on() );
	$add   = static function ( $device, $sel, $prop, $val ) use ( &$rules ) {
		foreach ( explode( '|', $prop ) as $p ) {
			$rules[ $device ][ $sel ][ $p ] = $val;
		}
	};
	foreach ( wpessential_site_targets() as $key => $t ) {
		if ( empty( $v[ $key ] ) ) {
			continue;
		}
		$val = wpessential_site_json( $v[ $key ] );
		if ( 0 === strpos( $key, 'typo_' ) ) {
			foreach ( wpessential_site_typo( $val ) as $device => $decls ) {
				foreach ( $decls as $p => $x ) {
					$add( $device, $t[0], $p, $x );
				}
			}
			continue;
		}
		if ( 0 === strpos( $key, 'site_bg' ) ) {
			foreach ( wpessential_site_background( $val ) as $p => $x ) {
				$add( 'desktop', $t[0], $p, $x );
			}
			continue;
		}
		if ( ! $val ) {
			continue; // A plain (old) number: written through theme.json already.
		}
		$val = wpessential_site_by_bp( $val );
		foreach ( array_keys( $media ) as $device ) {
			if ( ! isset( $val[ $device ] ) ) {
				continue;
			}
			$x = is_array( $val[ $device ] ) && isset( $val[ $device ]['top'] ) ? wpessential_site_dims( $val[ $device ] ) : wpessential_site_size( $val[ $device ] );
			// Desktop values of theme.json-backed settings are written there; repeat them here only for the composite ones.
			if ( '' !== $x && ( 'desktop' !== $device || in_array( $key, array( 'btn_padding', 'field_padding', 'menu_size', 'menu_gap', 'field_height', 'gutter' ), true ) ) ) {
				$add( $device, $t[0], $t[1], $x );
			}
		}
	}
	if ( ! empty( $v['btn_hover_border'] ) ) {
		$add( 'desktop', '.wp-element-button:hover,.wp-block-button__link:hover,.wpe-btn:hover', 'border-color', $v['btn_hover_border'] );
	}
	$css = '';
	foreach ( $rules as $device => $sels ) {
		// Only the breakpoints switched on in Layout are written.
		if ( ! isset( $on[ $device ] ) ) {
			continue;
		}
		$block = '';
		foreach ( $sels as $sel => $decls ) {
			$block .= $sel . '{';
			foreach ( $decls as $p => $x ) {
				$block .= $p . ':' . $x . ';';
			}
			$block .= '}';
		}
		if ( '' !== $block ) {
			$css .= $media[ $device ] ? $media[ $device ] . '{' . $block . '}' : $block;
		}
	}
	return $css;
}
