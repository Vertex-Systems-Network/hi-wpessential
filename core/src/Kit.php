<?php
/**
 * WPEssential kit: our own elements, each defined once in core/kit/<name>/element.json and
 * shipped as a Gutenberg block (wpessential/<name>) and an Elementor widget (wpe-<name>) with the
 * same options, the same render (core/kit/<name>/render.php) and the theme's breakpoints.
 *
 * Option types in element.json ("attributes"):
 *   text, textarea, url, select, toggle, range, color (theme palette slug).
 *   "responsive": true stores one value per breakpoint: { "base": …, "1920": …, "1280": …, … }.
 *   "css": "--var" turns the value into a CSS custom property on the element (with "unit");
 *   "map": { value: "--a:x;--b:y" } sets several properties for one choice.
 * "groups" add typography, spacing, border, shadow, background and colour groups (Kit\Groups);
 * every block also gets the common Advanced options (Elementor has its own Advanced tab).
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core;

use WPEssential\Core\Kit\Groups;

defined( 'ABSPATH' ) || exit;

/**
 * Kit loader and shared render helpers.
 */
class Kit {

	/**
	 * Breakpoints of the theme (assets/css/responsive.css): key => media query. "base" is 1440.
	 */
	const BREAKPOINTS = array(
		'1920' => '(min-width: 1920px)',
		'1280' => '(max-width: 1439px)',
		'1024' => '(max-width: 1279px)',
		'768'  => '(max-width: 1023px)',
		'560'  => '(max-width: 767px)',
		'480'  => '(max-width: 559px)',
		'360'  => '(max-width: 479px)',
		'320'  => '(max-width: 359px)',
	);

	/**
	 * Elementor device => our breakpoint key. Elementor has at most seven devices, so 360 and 320
	 * follow 480 there; Gutenberg has all of them.
	 */
	const ELEMENTOR_DEVICES = array(
		'widescreen'   => '1920',
		'laptop'       => '1280',
		'tablet_extra' => '1024',
		'tablet'       => '768',
		'mobile_extra' => '560',
		'mobile'       => '480',
	);

	/**
	 * Loaded definitions.
	 *
	 * @var array|null
	 */
	private static $defs = null;

	/**
	 * Units offered for every length option (px options get all of them).
	 */
	const LENGTH_UNITS = array( 'px', 'em', 'rem', '%', 'vw', 'vh' );

	/**
	 * Icon set (core/kit/icons.json): name => SVG body.
	 *
	 * @var array|null
	 */
	private static $icons = null;

	/**
	 * Option holding the elements switched off on the Elements screen. Every element is on by default.
	 */
	const OFF_OPTION = 'wpessential_kit_disabled';

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_filter( 'elementor/icons_manager/additional_tabs', array( __CLASS__, 'elementor_icons' ) );
		add_action(
			'enqueue_block_editor_assets',
			static function () {
				wp_enqueue_style( 'wpessential-kit-editor', wpessential_core_url( 'assets/kit-editor.css' ), array(), WPESSENTIAL_CORE_ASSET_VERSION );
			}
		);
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'elementor_category' ) );
		add_filter( 'block_categories_all', array( __CLASS__, 'block_category' ) );
	}

	/**
	 * Names of the elements switched off.
	 *
	 * @return string[]
	 */
	public static function disabled() {
		return array_values( array_filter( array_map( 'sanitize_key', (array) get_option( self::OFF_OPTION, array() ) ) ) );
	}

	/**
	 * Switch elements on or off.
	 *
	 * @param array $states Name => bool.
	 */
	public static function set_enabled( array $states ) {
		$off = array_fill_keys( self::disabled(), true );
		foreach ( $states as $name => $on ) {
			$name = sanitize_key( $name );
			if ( ! isset( self::defs()[ $name ] ) ) {
				continue;
			}
			if ( $on ) {
				unset( $off[ $name ] );
			} else {
				$off[ $name ] = true;
			}
		}
		update_option( self::OFF_OPTION, array_keys( $off ), false );
	}

	/**
	 * Definitions of the elements that are switched on.
	 *
	 * @return array
	 */
	public static function enabled_defs() {
		return array_diff_key( self::defs(), array_flip( self::disabled() ) );
	}

	/**
	 * Elements for the Elements screen.
	 *
	 * @return array
	 */
	public static function catalogue() {
		$off = self::disabled();
		$out = array();
		foreach ( self::defs() as $name => $def ) {
			$out[] = array(
				'name'        => $name,
				'title'       => trim( str_replace( '(WPEssential)', '', (string) $def['title'] ) ),
				'description' => (string) ( $def['description'] ?? '' ),
				'category'    => (string) ( $def['category'] ?? 'basic' ),
				'icon'        => (string) ( $def['icon'] ?? 'star-filled' ),
				'options'     => count( self::attributes( $def ) ) + count( self::groups( $def ) ),
				'enabled'     => ! in_array( $name, $off, true ),
			);
		}
		return $out;
	}

	/**
	 * The WPEssential icon set.
	 *
	 * @return array
	 */
	public static function icons() {
		if ( null === self::$icons ) {
			self::$icons = (array) json_decode( (string) file_get_contents( WPESSENTIAL_CORE_DIR . '/kit/icons.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
		}
		return self::$icons;
	}

	/**
	 * Units a range option offers: all length units when its unit is a length, else its own unit.
	 *
	 * @param array $opt Option.
	 * @return string[]
	 */
	public static function units( array $opt ) {
		$unit = $opt['unit'] ?? 'px';
		if ( isset( $opt['units'] ) ) {
			return (array) $opt['units'];
		}
		if ( ! empty( $opt['fluid'] ) || ! in_array( $unit, array_merge( self::LENGTH_UNITS, array( 'ch' ) ), true ) ) {
			return array( $unit );
		}
		return array_values( array_unique( array_merge( array( $unit ), self::LENGTH_UNITS ) ) );
	}

	/**
	 * Our icon set as a tab in Elementor's icon library.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public static function elementor_icons( $tabs ) {
		$tabs['wpessential'] = array(
			'name'          => 'wpessential',
			'label'         => __( 'WPEssential', 'hi-wpessential' ),
			'url'           => wpessential_core_url( 'kit/icons.css' ),
			'enqueue'       => array(),
			'prefix'        => 'wpei-',
			'displayPrefix' => 'wpei',
			'labelIcon'     => 'wpei wpei-spark',
			'ver'           => WPESSENTIAL_CORE_ASSET_VERSION,
			'fetchJson'     => wpessential_core_url( 'kit/icons-elementor.json' ),
			'native'        => false,
		);
		return $tabs;
	}

	/**
	 * Icon markup for an icon value: { library, value } from either builder.
	 * Our set and Elementor's "wpei" tab print inline SVG; uploaded SVG/images print <img>;
	 * other Elementor libraries (Font Awesome…) go through Elementor.
	 *
	 * @param mixed  $icon      Value.
	 * @param string $css_class CSS class.
	 * @return string
	 */
	public static function icon_html( $icon, $css_class = 'wpe-icon' ) {
		if ( is_string( $icon ) ) {
			$legacy = array(
				'arrow' => 'arrow-up-right',
				'right' => 'arrow-right',
			);
			$icon   = array(
				'library' => 'wpessential',
				'value'   => $legacy[ $icon ] ?? $icon,
			);
		}
		if ( ! is_array( $icon ) || empty( $icon['value'] ) ) {
			return '';
		}
		$lib   = (string) ( $icon['library'] ?? 'wpessential' );
		$value = $icon['value'];
		if ( 'svg' === $lib || 'upload' === $lib ) {
			$url = is_array( $value ) ? ( $value['url'] ?? '' ) : (string) $value;
			return $url ? '<img class="' . esc_attr( $css_class ) . ' is-upload" src="' . esc_url( $url ) . '" alt="" aria-hidden="true" width="24" height="24">' : '';
		}
		$name = is_string( $value ) ? preg_replace( '/^wpei\s+wpei-/', '', $value ) : '';
		if ( 'wpessential' === $lib && isset( self::icons()[ $name ] ) ) {
			return '<svg class="' . esc_attr( $css_class ) . '" viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . self::icons()[ $name ] . '</svg>';
		}
		if ( class_exists( '\Elementor\Icons_Manager' ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon(
				$icon,
				array(
					'aria-hidden' => 'true',
					'class'       => $css_class,
				)
			);
			return (string) ob_get_clean();
		}
		return '';
	}

	/**
	 * Every element definition, keyed by name.
	 *
	 * @return array
	 */
	public static function defs() {
		if ( null === self::$defs ) {
			self::$defs = array();
			foreach ( (array) glob( WPESSENTIAL_CORE_DIR . '/kit/*/element.json' ) as $file ) {
				$def = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
				if ( is_array( $def ) && ! empty( $def['name'] ) ) {
					$def['dir']                 = dirname( $file );
					self::$defs[ $def['name'] ] = $def;
				}
			}
		}
		return self::$defs;
	}

	/**
	 * Default value of every attribute.
	 *
	 * @param array $def Definition.
	 * @return array
	 */
	public static function defaults( array $def ) {
		$out = array();
		foreach ( self::groups( $def ) as $key => $g ) {
			$out[ $key ] = array();
		}
		foreach ( self::attributes( $def ) as $key => $a ) {
			$value       = $a['default'] ?? ( 'toggle' === $a['type'] ? false : '' );
			$out[ $key ] = ! empty( $a['responsive'] ) ? array( 'base' => $value ) : $value;
		}
		return $out;
	}

	/**
	 * Element options plus the common Advanced options.
	 *
	 * @param array $def Definition.
	 * @return array
	 */
	public static function attributes( array $def ) {
		return $def['attributes'] + Groups::common()[0];
	}

	/**
	 * Element groups plus the common Advanced groups.
	 *
	 * @param array $def Definition.
	 * @return array
	 */
	public static function groups( array $def ) {
		return ( $def['groups'] ?? array() ) + Groups::common()[1];
	}

	/**
	 * Gutenberg category.
	 *
	 * @param array $cats Categories.
	 * @return array
	 */
	public static function block_category( $cats ) {
		array_unshift(
			$cats,
			array(
				'slug'  => 'wpessential',
				'title' => __( 'WPEssential', 'hi-wpessential' ),
			)
		);
		return $cats;
	}

	/**
	 * Elementor category.
	 *
	 * @param \Elementor\Elements_Manager $manager Manager.
	 */
	public static function elementor_category( $manager ) {
		$manager->add_category(
			'wpessential',
			array(
				'title' => __( 'WPEssential', 'hi-wpessential' ),
				'icon'  => 'eicon-star',
			)
		);
	}

	/**
	 * Register every element as a block, plus the shared editor script and styles.
	 */
	public static function register_blocks() {
		wp_register_script( 'wpessential-kit-editor', wpessential_core_url( 'assets/kit-editor.js' ), array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n', 'wp-data' ), WPESSENTIAL_CORE_ASSET_VERSION, true );
		wp_register_style( 'wpessential-kit-common', wpessential_core_url( 'kit/common.css' ), array(), WPESSENTIAL_CORE_ASSET_VERSION );
		$editor = array();
		foreach ( self::enabled_defs() as $name => $def ) {
			$handle = 'wpessential-kit-' . $name;
			wp_register_style( $handle, wpessential_core_url( 'kit/' . $name . '/style.css' ), array( 'wpessential-kit-common' ), WPESSENTIAL_CORE_ASSET_VERSION );
			// Optional behaviour: kit/<name>/script.js, loaded only on pages that use the element.
			if ( file_exists( $def['dir'] . '/script.js' ) ) {
				wp_register_script( $handle, wpessential_core_url( 'kit/' . $name . '/script.js' ), array(), WPESSENTIAL_CORE_ASSET_VERSION, true );
			}
			$attrs = array();
			foreach ( self::attributes( $def ) as $key => $a ) {
				$attrs[ $key ] = ! empty( $a['responsive'] ) || 'repeater' === $a['type'] ? array( 'type' => 'array' ) : ( in_array( $a['type'], array( 'breakpoints', 'image', 'icon' ), true ) ? array( 'type' => 'object' ) : array( 'type' => 'toggle' === $a['type'] ? 'boolean' : ( 'range' === $a['type'] ? array( 'number', 'string' ) : 'string' ) ) );
			}
			foreach ( self::attributes( $def ) as $key => $a ) {
				// Defaults in the block too, so the sidebar shows the value the page really uses.
				if ( isset( $a['default'] ) && '' !== $a['default'] && array() !== $a['default'] && empty( $a['responsive'] ) ) {
					$attrs[ $key ]['default'] = $a['default'];
				}
			}
			foreach ( self::groups( $def ) as $key => $g ) {
				$attrs[ $key ] = array( 'type' => 'object' );
			}
			register_block_type(
				'wpessential/' . $name,
				array(
					'api_version'     => 3,
					'title'           => $def['title'],
					'category'        => 'wpessential',
					'icon'            => $def['icon'] ?? 'star-filled',
					'keywords'        => $def['keywords'] ?? array(),
					'attributes'      => $attrs,
					'supports'        => array(
						'anchor' => true,
						'html'   => false,
					),
					'style_handles'   => array( $handle ),
					'editor_script'   => 'wpessential-kit-editor',
					'render_callback' => static function ( $attributes ) use ( $name ) {
						return self::render( $name, (array) $attributes );
					},
				)
			);
			$editor[ $name ] = array_merge(
				array_intersect_key( $def, array_flip( array( 'name', 'title', 'icon' ) ) ),
				array(
					'attributes' => self::attributes( $def ),
					'groups'     => self::groups( $def ),
					'panels'     => ( $def['panels'] ?? array( 'content' => __( 'Content', 'hi-wpessential' ) ) ) + array( 'advanced' => __( 'Layout & effects', 'hi-wpessential' ) ),
				)
			);
		}
		wp_add_inline_script(
			'wpessential-kit-editor',
			'window.wpessentialKit = ' . wp_json_encode(
				array(
					'elements'    => $editor,
					'breakpoints' => array_merge( array( 'base' ), array_map( 'strval', array_keys( self::BREAKPOINTS ) ) ),
					'palette'     => self::palette(),
					'groupFields' => Groups::fields(),
					'icons'       => self::icons(),
					'lengthUnits' => self::LENGTH_UNITS,
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Theme palette slugs and names (colour options use slugs, so light/dark both work).
	 *
	 * @return array
	 */
	public static function palette() {
		$out = array();
		if ( function_exists( 'wp_get_global_settings' ) ) {
			foreach ( (array) wp_get_global_settings( array( 'color', 'palette', 'theme' ) ) as $c ) {
				$out[ $c['slug'] ] = $c['name'];
			}
		}
		return $out;
	}

	/**
	 * Register every element as an Elementor widget.
	 *
	 * @param \Elementor\Widgets_Manager $manager Manager.
	 */
	public static function register_widgets( $manager ) {
		foreach ( array_keys( self::enabled_defs() ) as $name ) {
			$class = __NAMESPACE__ . '\\Kit\\Elements\\' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $name ) ) );
			if ( class_exists( $class ) ) {
				$manager->register( new $class() );
			}
		}
	}

	/**
	 * Render an element from saved attributes (block) or mapped settings (widget).
	 *
	 * @param string $name       Element.
	 * @param array  $attributes Values.
	 * @return string
	 */
	public static function render( $name, array $attributes ) {
		$defs = self::defs();
		if ( empty( $defs[ $name ] ) ) {
			return '';
		}
		$def   = $defs[ $name ];
		$a     = array();
		$attrs = self::attributes( $def );
		foreach ( self::defaults( $def ) as $key => $default ) {
			$value = $attributes[ $key ] ?? $default;
			// A list of items is taken as saved: merging it with the default items would bring them back.
			if ( 'repeater' === ( $attrs[ $key ]['type'] ?? '' ) ) {
				$a[ $key ] = is_array( $value ) ? array_values( array_filter( $value, 'is_array' ) ) : array();
				continue;
			}
			$a[ $key ] = is_array( $default ) ? array_replace( $default, array_filter( (array) $value, static fn( $v ) => '' !== $v && null !== $v ) ) : $value;
		}
		wp_enqueue_style( 'wpessential-kit-' . $name );
		if ( wp_script_is( 'wpessential-kit-' . $name, 'registered' ) ) {
			wp_enqueue_script( 'wpessential-kit-' . $name );
		}
		$id   = 'wpe-' . $name . '-' . substr( md5( wp_json_encode( $a ) . wp_rand() ), 0, 8 );
		$css  = self::css( $def, $a, $id );
		$html = self::template( $def['dir'] . '/render.php', $a, $id );
		return ( $css ? '<style>' . $css . '</style>' : '' ) . self::root_extras( $html, $id, $a );
	}

	/**
	 * Common options that live on the root tag: extra classes, custom attributes, transform and
	 * entrance animation classes.
	 *
	 * @param string $html Markup.
	 * @param string $id   Root id.
	 * @param array  $a    Values.
	 * @return string
	 */
	private static function root_extras( $html, $id, array $a ) {
		$classes = array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', (string) ( $a['cssClass'] ?? '' ) ) ) );
		foreach ( array( 'rotate', 'scale', 'moveX', 'moveY', 'hoverRotate', 'hoverScale', 'hoverMoveY' ) as $t ) {
			if ( isset( $a[ $t ] ) && '' !== $a[ $t ] && null !== $a[ $t ] ) {
				$classes[] = 'wpek-tf';
				break;
			}
		}
		foreach ( array( 'transition', 'transitionEase', 'transitionDelay' ) as $t ) {
			if ( ! empty( $a[ $t ] ) ) {
				$classes[] = 'wpek-tr';
				break;
			}
		}
		if ( ! empty( $a['hoverPreset'] ) ) {
			$classes[] = 'wpek-hp-' . sanitize_html_class( $a['hoverPreset'] );
		}
		if ( ! empty( $a['animation'] ) && isset( Groups::animations()[ $a['animation'] ] ) ) {
			$classes[] = 'wpek-anim';
			$classes[] = 'wpek-anim-' . sanitize_html_class( $a['animation'] );
		}
		$attrs = '';
		$aria  = array(
			'role'        => (string) ( $a['rootRole'] ?? '' ),
			// A label on a plain div means nothing to screen readers, so it needs a role.
			'aria-label'  => empty( $a['rootRole'] ) ? '' : (string) ( $a['rootAriaLabel'] ?? '' ),
			'aria-hidden' => empty( $a['rootAriaHidden'] ) ? '' : 'true',
			'aria-live'   => (string) ( $a['rootAriaLive'] ?? '' ),
		);
		foreach ( array_filter( $aria ) as $k => $v ) {
			$attrs .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}
		foreach ( preg_split( '/\r?\n/', (string) ( $a['attributes'] ?? '' ) ) as $line ) {
			$pair = array_map( 'trim', explode( '|', $line, 2 ) );
			$key  = strtolower( preg_replace( '/[^a-zA-Z0-9_\-:]/', '', $pair[0] ) );
			if ( '' === $key || 0 === strpos( $key, 'on' ) || in_array( $key, array( 'id', 'class', 'style' ), true ) ) {
				continue; // No event handlers or overrides of what the element owns.
			}
			$attrs .= ' ' . $key . '="' . esc_attr( $pair[1] ?? '' ) . '"';
		}
		if ( ! $classes && '' === $attrs ) {
			return $html;
		}
		$pos = strpos( $html, 'id="' . $id . '"' );
		if ( false === $pos ) {
			return $html;
		}
		$tag_end = strpos( $html, '>', $pos );
		$tag     = substr( $html, $pos, $tag_end - $pos );
		if ( $classes ) {
			$tag = false !== strpos( $tag, 'class="' ) ? preg_replace( '/class="/', 'class="' . esc_attr( implode( ' ', $classes ) ) . ' ', $tag, 1 ) : $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
		}
		return substr( $html, 0, $pos ) . $tag . $attrs . substr( $html, $tag_end );
	}

	/**
	 * Run an element's render.php with its values ($a) and element id ($id) in scope.
	 *
	 * @param string $file Template.
	 * @param array  $a    Values.
	 * @param string $id   Element id.
	 * @return string
	 */
	private static function template( $file, array $a, $id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Used by the included template.
		ob_start();
		include $file;
		return (string) ob_get_clean();
	}

	/**
	 * CSS custom properties per breakpoint for options with "css".
	 *
	 * @param array  $def Definition.
	 * @param array  $a   Values.
	 * @param string $id  Element id.
	 * @return string
	 */
	private static function css( array $def, array $a, $id ) {
		$root  = '#' . $id;
		$rules = array( 'base' => array() );
		foreach ( self::attributes( $def ) as $key => $opt ) {
			if ( 'breakpoints' === $opt['type'] ) {
				foreach ( (array) ( $a[ $key ] ?? array() ) as $bp => $on ) {
					if ( $on ) {
						$rules[ (string) $bp ][ $root ][] = 'display:none';
					}
				}
				continue;
			}
			if ( empty( $opt['css'] ) ) {
				continue;
			}
			$sel    = empty( $opt['state'] ) ? $root : $root . ':hover,' . $root . ':focus-within';
			$values = ! empty( $opt['responsive'] ) ? (array) $a[ $key ] : array( 'base' => $a[ $key ] );
			foreach ( $values as $bp => $value ) {
				if ( '' === $value || null === $value || false === $value ) {
					continue;
				}
				// "map" turns one choice into several declarations, e.g. center => text-align + auto margins.
				$rules[ (string) $bp ][ $sel ][] = isset( $opt['map'][ $value ] ) ? preg_replace( '/[^a-z0-9:;%#.,() \-]/i', '', $opt['map'][ $value ] ) : $opt['css'] . ':' . self::css_value( $opt, $value );
			}
		}
		foreach ( self::groups( $def ) as $key => $group ) {
			if ( ! empty( $a[ $key ] ) && is_array( $a[ $key ] ) ) {
				Groups::css( $group, $a[ $key ], $root, $rules );
			}
		}
		$out = '';
		// Custom CSS: "selector" means this element, like Elementor Pro.
		$custom = trim( (string) ( $a['customCss'] ?? '' ) );
		if ( '' !== $custom ) {
			$custom = str_ireplace( array( '</', '<', '@import', 'expression(', 'javascript:' ), '', wp_strip_all_tags( $custom ) );
			$custom = preg_replace( '/\bselector\b/', $root, $custom );
		}
		foreach ( array( 'base' => '' ) + self::BREAKPOINTS as $bp => $query ) {
			if ( empty( $rules[ (string) $bp ] ) ) {
				continue;
			}
			$block = '';
			foreach ( $rules[ (string) $bp ] as $sel => $decls ) {
				$block .= $sel . '{' . implode( ';', $decls ) . '}';
			}
			$out .= 'base' === $bp ? $block : '@media ' . $query . '{' . $block . '}';
		}
		return $out . ( '' !== $custom && false === strpos( $custom, '{' ) ? $root . '{' . $custom . '}' : $custom );
	}

	/**
	 * One CSS value: palette slug -> preset variable, numbers get their unit.
	 *
	 * @param array $opt   Option.
	 * @param mixed $value Value.
	 * @return string
	 */
	private static function css_value( array $opt, $value ) {
		if ( 'color' === $opt['type'] ) {
			return 'var(--wp--preset--color--' . sanitize_key( $value ) . ')';
		}
		if ( is_numeric( $value ) ) {
			return ( 0 + $value ) . ( $opt['unit'] ?? '' );
		}
		return preg_replace( '/[^a-z0-9#%.,()\/ \-]/i', '', (string) $value );
	}

	/**
	 * Elementor settings -> our attribute shape (responsive values per breakpoint).
	 *
	 * @param array $def      Definition.
	 * @param array $settings Widget settings.
	 * @return array
	 */
	public static function from_elementor( array $def, array $settings ) {
		$out = array();
		foreach ( $def['attributes'] as $key => $opt ) {
			$get = static function ( $k ) use ( $settings, $opt ) {
				$v = $settings[ $k ] ?? '';
				if ( 'icon' === $opt['type'] ) {
					return is_array( $v ) && ! empty( $v['value'] ) ? $v : array();
				}
				if ( 'repeater' === $opt['type'] ) {
					return array_map(
						static fn( $item ) => self::from_elementor( array( 'attributes' => $opt['fields'] ), (array) $item ),
						is_array( $v ) ? array_values( $v ) : array()
					);
				}
				if ( 'range' === $opt['type'] && is_array( $v ) ) {
					if ( ! isset( $v['size'] ) || '' === $v['size'] || null === $v['size'] ) {
						return '';
					}
					$unit = (string) ( $v['unit'] ?? '' );
					return '' === $unit || ( $opt['unit'] ?? 'px' ) === $unit ? $v['size'] : $v['size'] . $unit;
				}
				if ( 'image' === $opt['type'] ) {
					return is_array( $v ) ? array(
						'id'  => (int) ( $v['id'] ?? 0 ),
						'url' => (string) ( $v['url'] ?? '' ),
					) : array();
				}
				if ( is_array( $v ) && array_key_exists( 'size', $v ) ) {
					$v = $v['size'];
				}
				if ( is_array( $v ) && array_key_exists( 'url', $v ) ) {
					$v = $v['url'];
				}
				return 'toggle' === $opt['type'] ? ( 'yes' === $v ) : $v;
			};
			if ( ! empty( $opt['responsive'] ) ) {
				$out[ $key ] = array( 'base' => $get( $key ) );
				foreach ( self::ELEMENTOR_DEVICES as $device => $bp ) {
					$out[ $key ][ $bp ] = $get( $key . '_' . $device );
				}
			} else {
				$out[ $key ] = $get( $key );
			}
		}
		if ( ! empty( $settings['wpe_custom_css'] ) ) {
			$out['customCss'] = (string) $settings['wpe_custom_css'];
		}
		return $out;
	}
}
