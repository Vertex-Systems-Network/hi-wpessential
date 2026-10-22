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
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'elementor_category' ) );
		add_filter( 'block_categories_all', array( __CLASS__, 'block_category' ) );
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
		wp_register_script( 'wpessential-kit-editor', wpessential_core_url( 'assets/kit-editor.js' ), array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ), WPESSENTIAL_CORE_ASSET_VERSION, true );
		wp_register_style( 'wpessential-kit-common', wpessential_core_url( 'kit/common.css' ), array(), WPESSENTIAL_CORE_ASSET_VERSION );
		$editor = array();
		foreach ( self::defs() as $name => $def ) {
			$handle = 'wpessential-kit-' . $name;
			wp_register_style( $handle, wpessential_core_url( 'kit/' . $name . '/style.css' ), array( 'wpessential-kit-common' ), WPESSENTIAL_CORE_ASSET_VERSION );
			$attrs = array();
			foreach ( self::attributes( $def ) as $key => $a ) {
				$attrs[ $key ] = ! empty( $a['responsive'] ) || 'breakpoints' === $a['type'] ? array( 'type' => 'object' ) : array( 'type' => 'toggle' === $a['type'] ? 'boolean' : ( 'range' === $a['type'] ? 'number' : 'string' ) );
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
		foreach ( array_keys( self::defs() ) as $name ) {
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
		$def = $defs[ $name ];
		$a   = array();
		foreach ( self::defaults( $def ) as $key => $default ) {
			$value     = $attributes[ $key ] ?? $default;
			$a[ $key ] = is_array( $default ) ? array_replace( $default, array_filter( (array) $value, static fn( $v ) => '' !== $v && null !== $v ) ) : $value;
		}
		wp_enqueue_style( 'wpessential-kit-' . $name );
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
		if ( ! empty( $a['animation'] ) && isset( Groups::animations()[ $a['animation'] ] ) ) {
			$classes[] = 'wpek-anim';
			$classes[] = 'wpek-anim-' . sanitize_html_class( $a['animation'] );
		}
		$attrs = '';
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
		return $out;
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
		return preg_replace( '/[^a-z0-9#%.,() \-]/i', '', (string) $value );
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
		return $out;
	}
}
