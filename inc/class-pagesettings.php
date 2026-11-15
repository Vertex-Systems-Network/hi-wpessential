<?php
/**
 * Page settings: per page / post options in both builders.
 *
 * Gutenberg: a "Page settings (WPEssential)" panel in the document sidebar (post meta).
 * Elementor: the same options in the page's settings panel (Elementor page settings).
 * One definition (fields()) drives both, and one place applies them on the front end.
 *
 * @package Hi_WPEssential
 */

namespace WPEssential\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Page settings.
 */
class PageSettings {

	/**
	 * Post meta key (one object).
	 */
	const META = '_wpessential_page';

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'init', array( __CLASS__, 'register_meta' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'editor' ) );
		add_action( 'elementor/documents/register_controls', array( __CLASS__, 'elementor_controls' ) );
		add_action( 'wp', array( __CLASS__, 'apply' ) );
	}

	/**
	 * Every option, grouped by panel.
	 *
	 * @return array
	 */
	public static function fields() {
		$parts   = static function ( $area ) {
			$out = array();
			foreach ( (array) glob( get_template_directory() . '/parts/' . $area . '*.html' ) as $file ) {
				$slug         = basename( $file, '.html' );
				$out[ $slug ] = ucwords( str_replace( '-', ' ', $slug ) );
			}
			return $out;
		};
		$palette = array();
		foreach ( (array) wp_get_global_settings( array( 'color', 'palette', 'theme' ) ) as $wpe_c ) {
			$palette[ $wpe_c['slug'] ] = $wpe_c['name'];
		}
		return array(
			'hideTitle'     => array(
				'type'  => 'toggle',
				'group' => 'layout',
				'label' => __( 'Hide page title', 'hi-wpessential' ),
			),
			'hideFeatured'  => array(
				'type'  => 'toggle',
				'group' => 'layout',
				'label' => __( 'Hide featured image', 'hi-wpessential' ),
			),
			'contentWidth'  => array(
				'type'    => 'select',
				'group'   => 'layout',
				'label'   => __( 'Content width', 'hi-wpessential' ),
				'options' => array(
					''       => __( 'Theme default', 'hi-wpessential' ),
					'narrow' => __( 'Narrow (640px, for reading)', 'hi-wpessential' ),
					'wide'   => __( 'Wide (1200px)', 'hi-wpessential' ),
					'full'   => __( 'Full width (edge to edge)', 'hi-wpessential' ),
					'custom' => __( 'Custom', 'hi-wpessential' ),
				),
			),
			'contentCustom' => array(
				'type'  => 'length',
				'group' => 'layout',
				'label' => __( 'Custom content width', 'hi-wpessential' ),
				'show'  => array( 'contentWidth', 'custom' ),
			),
			'paddingTop'    => array(
				'type'  => 'length',
				'group' => 'layout',
				'label' => __( 'Space above content', 'hi-wpessential' ),
			),
			'paddingBottom' => array(
				'type'  => 'length',
				'group' => 'layout',
				'label' => __( 'Space below content', 'hi-wpessential' ),
			),
			'header'        => array(
				'type'    => 'select',
				'group'   => 'header',
				'label'   => __( 'Header', 'hi-wpessential' ),
				'options' => array(
					''     => __( 'Site default', 'hi-wpessential' ),
					'none' => __( 'Hide header', 'hi-wpessential' ),
				) + $parts( 'header' ),
			),
			'sticky'        => array(
				'type'    => 'select',
				'group'   => 'header',
				'label'   => __( 'Sticky header', 'hi-wpessential' ),
				'options' => array(
					''    => __( 'Site default', 'hi-wpessential' ),
					'on'  => __( 'Sticky', 'hi-wpessential' ),
					'off' => __( 'Not sticky', 'hi-wpessential' ),
				),
			),
			'footer'        => array(
				'type'    => 'select',
				'group'   => 'header',
				'label'   => __( 'Footer', 'hi-wpessential' ),
				'options' => array(
					''     => __( 'Site default', 'hi-wpessential' ),
					'none' => __( 'Hide footer', 'hi-wpessential' ),
				) + $parts( 'footer' ),
			),
			'scheme'        => array(
				'type'    => 'select',
				'group'   => 'colours',
				'label'   => __( 'Colour mode', 'hi-wpessential' ),
				'options' => array(
					''      => __( 'Visitor chooses (site default)', 'hi-wpessential' ),
					'light' => __( 'Always light', 'hi-wpessential' ),
					'dark'  => __( 'Always dark', 'hi-wpessential' ),
				),
			),
			'accent'        => array(
				'type'    => 'color',
				'group'   => 'colours',
				'label'   => __( 'Brand colour on this page', 'hi-wpessential' ),
				'palette' => $palette,
			),
			'bgColor'       => array(
				'type'    => 'color',
				'group'   => 'colours',
				'label'   => __( 'Page background', 'hi-wpessential' ),
				'palette' => $palette,
			),
			'bgGradient'    => array(
				'type'    => 'select',
				'group'   => 'colours',
				'label'   => __( 'Background gradient', 'hi-wpessential' ),
				'options' => array( '' => __( 'None', 'hi-wpessential' ) ) + array_combine( array_keys( Kit\Groups::gradients() ), array_map( 'ucfirst', array_keys( Kit\Groups::gradients() ) ) ),
			),
			'bgImage'       => array(
				'type'  => 'image',
				'group' => 'colours',
				'label' => __( 'Background image', 'hi-wpessential' ),
			),
			'darkBgColor'   => array(
				'type'    => 'color',
				'group'   => 'colours',
				'label'   => __( 'Page background in dark mode', 'hi-wpessential' ),
				'palette' => $palette,
			),
			'bodyClass'     => array(
				'type'  => 'text',
				'group' => 'advanced',
				'label' => __( 'Extra body classes', 'hi-wpessential' ),
			),
			'customCss'     => array(
				'type'  => 'code',
				'group' => 'advanced',
				'label' => __( 'Custom CSS for this page ("selector" = this page)', 'hi-wpessential' ),
			),
		);
	}

	/**
	 * Panel names.
	 *
	 * @return array
	 */
	public static function groups() {
		return array(
			'layout'   => __( 'Layout', 'hi-wpessential' ),
			'header'   => __( 'Header & footer', 'hi-wpessential' ),
			'colours'  => __( 'Colours & background', 'hi-wpessential' ),
			'advanced' => __( 'Advanced', 'hi-wpessential' ),
		);
	}

	/**
	 * Post types that get page settings.
	 *
	 * @return string[]
	 */
	private static function post_types() {
		$types = array_values( array_diff( get_post_types( array( 'public' => true ) ), array( 'attachment' ) ) );
		return (array) apply_filters( 'wpessential_page_settings_post_types', $types );
	}

	/**
	 * Register the meta for the block editor (REST).
	 */
	public static function register_meta() {
		$props = array();
		foreach ( self::fields() as $key => $f ) {
			$props[ $key ] = 'toggle' === $f['type'] ? array( 'type' => 'boolean' ) : ( 'image' === $f['type'] ? array(
				'type'       => 'object',
				'properties' => array(
					'id'  => array( 'type' => 'integer' ),
					'url' => array( 'type' => 'string' ),
				),
			) : array( 'type' => 'string' ) );
		}
		foreach ( self::post_types() as $type ) {
			register_post_meta(
				$type,
				self::META,
				array(
					'type'              => 'object',
					'single'            => true,
					'default'           => array(),
					'show_in_rest'      => array(
						'schema' => array(
							'type'                 => 'object',
							'properties'           => $props,
							'additionalProperties' => false,
						),
					),
					'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
					'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				)
			);
		}
	}

	/**
	 * Clean stored values.
	 *
	 * @param mixed $value Value.
	 * @return array
	 */
	public static function sanitize( $value ) {
		$out = array();
		foreach ( self::fields() as $key => $f ) {
			if ( ! isset( $value[ $key ] ) ) {
				continue;
			}
			$v = $value[ $key ];
			switch ( $f['type'] ) {
				case 'toggle':
					$out[ $key ] = (bool) $v;
					break;
				case 'image':
					$out[ $key ] = array(
						'id'  => (int) ( $v['id'] ?? 0 ),
						'url' => esc_url_raw( (string) ( $v['url'] ?? '' ) ),
					);
					break;
				case 'select':
					$out[ $key ] = array_key_exists( (string) $v, $f['options'] ) ? (string) $v : '';
					break;
				case 'code':
					$out[ $key ] = self::clean_css( (string) $v );
					break;
				case 'length':
				case 'color':
					$out[ $key ] = preg_replace( '/[^a-z0-9#%.,()\/ \-]/i', '', (string) $v );
					break;
				default:
					$out[ $key ] = sanitize_text_field( (string) $v );
			}
		}
		return $out;
	}

	/**
	 * Custom CSS without markup or remote imports.
	 *
	 * @param string $css CSS.
	 * @return string
	 */
	private static function clean_css( $css ) {
		return trim( str_ireplace( array( '</', '<', '@import', 'expression(', 'javascript:' ), '', wp_strip_all_tags( $css ) ) );
	}

	/**
	 * Block editor panel.
	 */
	public static function editor() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->post_type, self::post_types(), true ) ) {
			return;
		}
		wp_enqueue_script( 'wpessential-page-settings', get_theme_file_uri( 'assets/js/page-settings.js' ), array( 'wp-plugins', 'wp-editor', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data', 'wp-block-editor', 'wp-i18n' ), WPESSENTIAL_ASSET_VERSION, true );
		wp_add_inline_script(
			'wpessential-page-settings',
			'window.wpessentialPage = ' . wp_json_encode(
				array(
					'meta'   => self::META,
					'fields' => self::fields(),
					'groups' => self::groups(),
					'units'  => Kit::LENGTH_UNITS,
				)
			) . ';',
			'before'
		);
	}

	/**
	 * The same options in Elementor's page settings.
	 *
	 * @param \Elementor\Core\Base\Document $document Document.
	 */
	public static function elementor_controls( $document ) {
		// Controls are registered once per document type, so check the type, not the post.
		if ( ! $document instanceof \Elementor\Core\DocumentTypes\PageBase ) {
			return;
		}
		foreach ( self::groups() as $group => $label ) {
			$document->start_controls_section(
				'wpe_page_' . $group,
				array(
					/* translators: %s: panel name. */
					'label' => sprintf( __( 'WPEssential: %s', 'hi-wpessential' ), $label ),
					'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
				)
			);
			foreach ( self::fields() as $key => $f ) {
				if ( $f['group'] !== $group ) {
					continue;
				}
				$args = array(
					'label' => $f['label'],
					'type'  => array(
						'toggle' => \Elementor\Controls_Manager::SWITCHER,
						'select' => \Elementor\Controls_Manager::SELECT,
						'color'  => \Elementor\Controls_Manager::COLOR,
						'image'  => \Elementor\Controls_Manager::MEDIA,
						'code'   => \Elementor\Controls_Manager::CODE,
						'length' => \Elementor\Controls_Manager::TEXT,
					)[ $f['type'] ] ?? \Elementor\Controls_Manager::TEXT,
				);
				if ( 'select' === $f['type'] ) {
					$args['options'] = $f['options'];
				}
				if ( 'length' === $f['type'] ) {
					$args['placeholder'] = '80px, 5rem, 10vh…';
				}
				if ( 'code' === $f['type'] ) {
					$args['language'] = 'css';
				}
				if ( ! empty( $f['show'] ) ) {
					$args['condition'] = array( 'wpe_' . $f['show'][0] => $f['show'][1] );
				}
				$document->add_control( 'wpe_' . $key, $args );
			}
			$document->end_controls_section();
		}
	}

	/**
	 * Settings of a post: Gutenberg meta, with Elementor page settings on top when the page is built with Elementor.
	 *
	 * @param int $post_id Post.
	 * @return array
	 */
	public static function get( $post_id ) {
		$s = (array) get_post_meta( $post_id, self::META, true );
		if ( 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
			foreach ( (array) get_post_meta( $post_id, '_elementor_page_settings', true ) as $k => $v ) {
				if ( 0 === strpos( (string) $k, 'wpe_' ) && '' !== $v && null !== $v ) {
					$key = substr( $k, 4 );
					$f   = self::fields()[ $key ] ?? null;
					if ( ! $f ) {
						continue;
					}
					$s[ $key ] = 'toggle' === $f['type'] ? 'yes' === $v : $v;
				}
			}
			$s = self::sanitize( $s );
		}
		return $s;
	}

	/**
	 * Apply the settings of the page being viewed.
	 */
	public static function apply() {
		if ( is_admin() || ! is_singular() ) {
			return;
		}
		$id = get_queried_object_id();
		$s  = self::get( $id );
		if ( ! array_filter( $s ) ) {
			return;
		}
		$on = static fn( $k ) => ! empty( $s[ $k ] );

		if ( $on( 'hideTitle' ) || $on( 'hideFeatured' ) ) {
			add_filter(
				'render_block',
				static function ( $html, $block, $instance ) use ( $s, $id ) {
					$hide = ( ! empty( $s['hideTitle'] ) && 'core/post-title' === $block['blockName'] ) || ( ! empty( $s['hideFeatured'] ) && 'core/post-featured-image' === $block['blockName'] );
					// Only this page's own title/image, not the ones of posts listed in a query loop.
					$post = $instance instanceof \WP_Block && isset( $instance->context['postId'] ) ? (int) $instance->context['postId'] : get_the_ID();
					return $hide && $post === $id ? '' : $html;
				},
				10,
				3
			);
		}
		// Header / footer: hide or swap the template part.
		foreach ( array( 'header', 'footer' ) as $area ) {
			$choice = (string) ( $s[ $area ] ?? '' );
			if ( '' === $choice ) {
				continue;
			}
			add_filter(
				'render_block_data',
				static function ( $block ) use ( $area, $choice ) {
					if ( 'core/template-part' === $block['blockName'] && isset( $block['attrs']['slug'] ) && self::area( $block['attrs']['slug'] ) === $area && 'none' !== $choice ) {
						$block['attrs']['slug'] = $choice;
					}
					return $block;
				}
			);
			if ( 'none' === $choice ) {
				add_filter(
					'wpessential_layout_html',
					static fn( $html, $a ) => $a === $area ? '' : $html,
					5,
					2
				);
			}
		}
		if ( ! empty( $s['scheme'] ) ) {
			add_filter( 'wpessential_scheme_toggle', '__return_false', 20 );
			add_filter(
				'language_attributes',
				static fn( $out ) => preg_replace( '/ data-wpe-scheme="[^"]*"/', '', $out ) . ' data-wpe-scheme="' . esc_attr( $s['scheme'] ) . '"',
				20
			);
		}
		add_filter(
			'body_class',
			static function ( $classes ) use ( $s ) {
				$classes[] = 'wpe-has-page-settings';
				if ( 'off' === ( $s['sticky'] ?? '' ) ) {
					$classes[] = 'wpe-header-static';
				}
				if ( 'full' === ( $s['contentWidth'] ?? '' ) ) {
					$classes[] = 'wpe-content-full';
				}
				foreach ( preg_split( '/\s+/', (string) ( $s['bodyClass'] ?? '' ) ) as $c ) {
					if ( '' !== $c ) {
						$classes[] = sanitize_html_class( $c );
					}
				}
				return $classes;
			}
		);
		add_action(
			'wp_enqueue_scripts',
			static function () use ( $s ) {
				$css = self::css( $s );
				if ( '' !== $css ) {
					wp_register_style( 'wpessential-page', false, array(), WPESSENTIAL_ASSET_VERSION );
					wp_enqueue_style( 'wpessential-page' );
					wp_add_inline_style( 'wpessential-page', $css );
				}
			},
			30
		);
	}

	/**
	 * CSS for the settings.
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	public static function css( array $s ) {
		$color = static function ( $v ) {
			$v = (string) $v;
			return preg_match( '/^[a-z0-9-]+$/', $v ) && ! preg_match( '/^[0-9a-f]{3,8}$/i', $v ) ? 'var(--wp--preset--color--' . $v . ')' : $v;
		};
		$body  = 'body.wpe-has-page-settings';
		$rules = array();
		$width = array(
			'narrow' => '640px',
			'wide'   => '1200px',
			'full'   => '100%',
			'custom' => $s['contentCustom'] ?? '',
		)[ $s['contentWidth'] ?? '' ] ?? '';
		if ( '' !== $width ) {
			$rules[] = $body . ' .wpe-main{--wp--style--global--content-size:' . $width . ';--wp--style--global--wide-size:' . ( 'full' === $s['contentWidth'] ? '100%' : 'max(' . $width . ',1200px)' ) . '}';
		}
		if ( 'full' === ( $s['contentWidth'] ?? '' ) ) {
			$rules[] = $body . ' .wpe-main .wpe-section{padding-inline:0}';
		}
		foreach ( array(
			'paddingTop'    => 'padding-top',
			'paddingBottom' => 'padding-bottom',
		) as $k => $prop ) {
			if ( ! empty( $s[ $k ] ) ) {
				$rules[] = $body . ' .wpe-main{' . $prop . ':' . $s[ $k ] . '}';
			}
		}
		if ( 'on' === ( $s['sticky'] ?? '' ) ) {
			$rules[] = $body . ' .wp-block-template-part:has(> .wpe-header){position:sticky;top:0;z-index:100}';
		}
		if ( 'off' === ( $s['sticky'] ?? '' ) ) {
			$rules[] = $body . ' .wp-block-template-part:has(> .wpe-header),' . $body . ' .wpe-header{position:relative;top:auto}';
		}
		if ( ! empty( $s['accent'] ) ) {
			$rules[] = $body . '{--wp--preset--color--primary:' . $color( $s['accent'] ) . '}';
		}
		$bg = array();
		if ( ! empty( $s['bgColor'] ) ) {
			$bg[] = 'background-color:' . $color( $s['bgColor'] );
		}
		$layers = array();
		if ( ! empty( $s['bgGradient'] ) && isset( Kit\Groups::gradients()[ $s['bgGradient'] ] ) ) {
			$layers[] = Kit\Groups::gradients()[ $s['bgGradient'] ];
		}
		if ( ! empty( $s['bgImage']['url'] ) ) {
			$layers[] = 'url("' . esc_url_raw( $s['bgImage']['url'] ) . '")';
			$bg[]     = 'background-size:cover;background-position:center;background-attachment:fixed';
		}
		if ( $layers ) {
			$bg[] = 'background-image:' . implode( ',', $layers );
		}
		if ( $bg ) {
			$rules[] = $body . '{' . implode( ';', $bg ) . '}';
		}
		if ( ! empty( $s['darkBgColor'] ) ) {
			$rules[] = ':root[data-wpe-scheme="dark"] ' . $body . '{background-color:' . $color( $s['darkBgColor'] ) . '}';
		}
		$custom = trim( (string) ( $s['customCss'] ?? '' ) );
		if ( '' !== $custom ) {
			$custom  = preg_replace( '/\bselector\b/', $body, self::clean_css( $custom ) );
			$rules[] = false === strpos( $custom, '{' ) ? $body . '{' . $custom . '}' : $custom;
		}
		return implode( "\n", $rules );
	}

	/**
	 * Header/footer area of a template-part slug (theme helper when present).
	 *
	 * @param string $slug Slug.
	 * @return string
	 */
	private static function area( $slug ) {
		if ( function_exists( 'wpessential_layout_area' ) ) {
			return wpessential_layout_area( $slug );
		}
		foreach ( array( 'header', 'footer' ) as $area ) {
			if ( $slug === $area || 0 === strpos( $slug, $area . '-' ) ) {
				return $area;
			}
		}
		return '';
	}
}
