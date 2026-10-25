<?php
/**
 * Group options for kit elements: typography, spacing, border, shadow, background and
 * normal/hover colours, plus the common "Advanced" options every block gets (Elementor already
 * adds its own Advanced tab to every widget).
 *
 * An element lists its groups in element.json:
 *   "groups": { "titleTypography": { "type": "typography", "label": "Title", "selector": ".wpe-heading__title", "panel": "style" } }
 * The block stores one object per group; Elementor gets the matching native group control with
 * the same selector, so both builders style the same part.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core\Kit;

use WPEssential\Core\Kit;

defined( 'ABSPATH' ) || exit;

/**
 * Group definitions and their CSS.
 */
class Groups {

	/**
	 * Fields of each group type. "prop" is the CSS property; "state" => hover writes :hover.
	 *
	 * @return array
	 */
	public static function fields() {
		$fonts = array( '' => __( 'Default', 'hi-wpessential' ) ) + self::fonts();
		$px    = array(
			'type' => 'range',
			'unit' => 'px',
			'min'  => 0,
			'max'  => 200,
		);
		$side  = static fn( $prop, $label ) => array_merge(
			$px,
			array(
				'label'      => $label,
				'prop'       => $prop,
				'responsive' => true,
				'min'        => -200,
			)
		);
		return array(
			'typography'  => array(
				'family'        => array(
					'type'    => 'select',
					'label'   => __( 'Font', 'hi-wpessential' ),
					'prop'    => 'font-family',
					'options' => $fonts,
					'font'    => true,
				),
				'size'          => array_merge(
					$px,
					array(
						'label'      => __( 'Size', 'hi-wpessential' ),
						'prop'       => 'font-size',
						'responsive' => true,
						'max'        => 200,
					)
				),
				'sizeMin'       => array_merge(
					$px,
					array(
						'label' => __( 'Fluid size: at 360px', 'hi-wpessential' ),
						'prop'  => 'font-size',
						'fluid' => 'min',
						'max'   => 200,
					)
				),
				'sizeMax'       => array_merge(
					$px,
					array(
						'label' => __( 'Fluid size: at 1440px', 'hi-wpessential' ),
						'prop'  => 'font-size',
						'fluid' => 'max',
						'max'   => 240,
					)
				),
				'weight'        => array(
					'type'    => 'select',
					'label'   => __( 'Weight', 'hi-wpessential' ),
					'prop'    => 'font-weight',
					'options' => array(
						''    => __( 'Default', 'hi-wpessential' ),
						'100' => '100',
						'200' => '200',
						'300' => '300',
						'400' => '400',
						'500' => '500',
						'600' => '600',
						'700' => '700',
						'800' => '800',
						'900' => '900',
					),
				),
				'transform'     => array(
					'type'    => 'select',
					'label'   => __( 'Transform', 'hi-wpessential' ),
					'prop'    => 'text-transform',
					'options' => array(
						''           => __( 'Default', 'hi-wpessential' ),
						'uppercase'  => 'UPPERCASE',
						'lowercase'  => 'lowercase',
						'capitalize' => 'Capitalize',
						'none'       => __( 'Normal', 'hi-wpessential' ),
					),
				),
				'style'         => array(
					'type'    => 'select',
					'label'   => __( 'Style', 'hi-wpessential' ),
					'prop'    => 'font-style',
					'options' => array(
						''       => __( 'Default', 'hi-wpessential' ),
						'normal' => __( 'Normal', 'hi-wpessential' ),
						'italic' => __( 'Italic', 'hi-wpessential' ),
					),
				),
				'decoration'    => array(
					'type'    => 'select',
					'label'   => __( 'Decoration', 'hi-wpessential' ),
					'prop'    => 'text-decoration',
					'options' => array(
						''             => __( 'Default', 'hi-wpessential' ),
						'none'         => __( 'None', 'hi-wpessential' ),
						'underline'    => __( 'Underline', 'hi-wpessential' ),
						'line-through' => __( 'Line through', 'hi-wpessential' ),
					),
				),
				'lineHeight'    => array(
					'type'       => 'range',
					'label'      => __( 'Line height', 'hi-wpessential' ),
					'prop'       => 'line-height',
					'unit'       => '',
					'min'        => 0.5,
					'max'        => 3,
					'step'       => 0.05,
					'responsive' => true,
				),
				'letterSpacing' => array(
					'type'       => 'range',
					'label'      => __( 'Letter spacing', 'hi-wpessential' ),
					'prop'       => 'letter-spacing',
					'unit'       => 'px',
					'min'        => -5,
					'max'        => 20,
					'step'       => 0.1,
					'responsive' => true,
				),
				'wordSpacing'   => array(
					'type'       => 'range',
					'label'      => __( 'Word spacing', 'hi-wpessential' ),
					'prop'       => 'word-spacing',
					'unit'       => 'px',
					'min'        => -20,
					'max'        => 50,
					'responsive' => true,
				),
			),
			'spacing'     => array(
				'top'    => $side( '{prop}-top', __( 'Top', 'hi-wpessential' ) ),
				'right'  => $side( '{prop}-right', __( 'Right', 'hi-wpessential' ) ),
				'bottom' => $side( '{prop}-bottom', __( 'Bottom', 'hi-wpessential' ) ),
				'left'   => $side( '{prop}-left', __( 'Left', 'hi-wpessential' ) ),
			),
			'border'      => array(
				'style'      => array(
					'type'    => 'select',
					'label'   => __( 'Border', 'hi-wpessential' ),
					'prop'    => 'border-style',
					'options' => array(
						''       => __( 'Default', 'hi-wpessential' ),
						'none'   => __( 'None', 'hi-wpessential' ),
						'solid'  => __( 'Solid', 'hi-wpessential' ),
						'dashed' => __( 'Dashed', 'hi-wpessential' ),
						'dotted' => __( 'Dotted', 'hi-wpessential' ),
						'double' => __( 'Double', 'hi-wpessential' ),
					),
				),
				'width'      => array_merge(
					$px,
					array(
						'label'      => __( 'Width', 'hi-wpessential' ),
						'prop'       => 'border-width',
						'responsive' => true,
						'max'        => 20,
					)
				),
				'color'      => array(
					'type'  => 'color',
					'label' => __( 'Colour', 'hi-wpessential' ),
					'prop'  => 'border-color',
				),
				'hoverColor' => array(
					'type'  => 'color',
					'label' => __( 'Hover colour', 'hi-wpessential' ),
					'prop'  => 'border-color',
					'state' => 'hover',
				),
				'darkColor'  => array(
					'type'   => 'color',
					'label'  => __( 'Colour in dark mode', 'hi-wpessential' ),
					'prop'   => 'border-color',
					'scheme' => 'dark',
				),
				'radius'     => array_merge(
					$px,
					array(
						'label'      => __( 'Radius', 'hi-wpessential' ),
						'prop'       => 'border-radius',
						'responsive' => true,
						'max'        => 999,
					)
				),
			),
			'shadow'      => array(
				'preset'      => array(
					'type'    => 'select',
					'label'   => __( 'Shadow', 'hi-wpessential' ),
					'prop'    => 'box-shadow',
					'options' => self::shadow_labels(),
				),
				'hoverPreset' => array(
					'type'    => 'select',
					'label'   => __( 'Hover shadow', 'hi-wpessential' ),
					'prop'    => 'box-shadow',
					'state'   => 'hover',
					'options' => self::shadow_labels(),
				),
				'darkPreset'  => array(
					'type'    => 'select',
					'label'   => __( 'Shadow in dark mode', 'hi-wpessential' ),
					'prop'    => 'box-shadow',
					'scheme'  => 'dark',
					'options' => self::shadow_labels(),
				),
				'custom'      => array(
					'type'  => 'text',
					'label' => __( 'Custom shadow (CSS)', 'hi-wpessential' ),
					'prop'  => 'box-shadow',
				),
			),
			'background'  => array(
				'color'      => array(
					'type'  => 'color',
					'label' => __( 'Colour', 'hi-wpessential' ),
					'prop'  => 'background-color',
				),
				'hoverColor' => array(
					'type'  => 'color',
					'label' => __( 'Hover colour', 'hi-wpessential' ),
					'prop'  => 'background-color',
					'state' => 'hover',
				),
				'darkColor'  => array(
					'type'   => 'color',
					'label'  => __( 'Colour in dark mode', 'hi-wpessential' ),
					'prop'   => 'background-color',
					'scheme' => 'dark',
				),
				'darkImage'  => array(
					'type'   => 'url',
					'label'  => __( 'Image URL in dark mode', 'hi-wpessential' ),
					'prop'   => 'background-image',
					'image'  => true,
					'scheme' => 'dark',
				),
				'gradient'   => array(
					'type'    => 'select',
					'label'   => __( 'Gradient', 'hi-wpessential' ),
					'prop'    => 'background-image',
					'options' => self::gradient_labels(),
				),
				'image'      => array(
					'type'  => 'url',
					'label' => __( 'Image URL', 'hi-wpessential' ),
					'prop'  => 'background-image',
					'image' => true,
				),
				'size'       => array(
					'type'       => 'select',
					'label'      => __( 'Image size', 'hi-wpessential' ),
					'prop'       => 'background-size',
					'responsive' => true,
					'options'    => array(
						''        => __( 'Default', 'hi-wpessential' ),
						'cover'   => __( 'Cover', 'hi-wpessential' ),
						'contain' => __( 'Contain', 'hi-wpessential' ),
						'auto'    => __( 'Auto', 'hi-wpessential' ),
					),
				),
				'position'   => array(
					'type'       => 'select',
					'label'      => __( 'Image position', 'hi-wpessential' ),
					'prop'       => 'background-position',
					'responsive' => true,
					'options'    => array(
						''              => __( 'Default', 'hi-wpessential' ),
						'center center' => __( 'Center', 'hi-wpessential' ),
						'center top'    => __( 'Top', 'hi-wpessential' ),
						'center bottom' => __( 'Bottom', 'hi-wpessential' ),
						'left center'   => __( 'Left', 'hi-wpessential' ),
						'right center'  => __( 'Right', 'hi-wpessential' ),
					),
				),
				'repeat'     => array(
					'type'    => 'select',
					'label'   => __( 'Repeat', 'hi-wpessential' ),
					'prop'    => 'background-repeat',
					'options' => array(
						''          => __( 'Default', 'hi-wpessential' ),
						'no-repeat' => __( 'No repeat', 'hi-wpessential' ),
						'repeat'    => __( 'Repeat', 'hi-wpessential' ),
					),
				),
				'attachment' => array(
					'type'    => 'select',
					'label'   => __( 'Attachment', 'hi-wpessential' ),
					'prop'    => 'background-attachment',
					'options' => array(
						''       => __( 'Default', 'hi-wpessential' ),
						'scroll' => __( 'Scroll', 'hi-wpessential' ),
						'fixed'  => __( 'Fixed (parallax)', 'hi-wpessential' ),
					),
				),
			),
			'colors'      => array(
				'normal'    => array(
					'type'  => 'color',
					'label' => __( 'Colour', 'hi-wpessential' ),
					'prop'  => '{prop}',
				),
				'hover'     => array(
					'type'  => 'color',
					'label' => __( 'Hover colour', 'hi-wpessential' ),
					'prop'  => '{prop}',
					'state' => 'hover',
				),
				'focus'     => array(
					'type'  => 'color',
					'label' => __( 'Keyboard focus colour', 'hi-wpessential' ),
					'prop'  => '{prop}',
					'state' => 'focus',
				),
				'active'    => array(
					'type'  => 'color',
					'label' => __( 'Pressed colour', 'hi-wpessential' ),
					'prop'  => '{prop}',
					'state' => 'active',
				),
				'dark'      => array(
					'type'   => 'color',
					'label'  => __( 'Colour in dark mode', 'hi-wpessential' ),
					'prop'   => '{prop}',
					'scheme' => 'dark',
				),
				'darkHover' => array(
					'type'   => 'color',
					'label'  => __( 'Hover colour in dark mode', 'hi-wpessential' ),
					'prop'   => '{prop}',
					'scheme' => 'dark',
					'state'  => 'hover',
				),
			),
			'textEffects' => array(
				'shadow'      => array(
					'type'  => 'text',
					'label' => __( 'Text shadow (e.g. 0 2px 8px rgb(0 0 0 / .3))', 'hi-wpessential' ),
					'prop'  => 'text-shadow',
				),
				'hoverShadow' => array(
					'type'  => 'text',
					'label' => __( 'Text shadow on hover', 'hi-wpessential' ),
					'prop'  => 'text-shadow',
					'state' => 'hover',
				),
				'strokeWidth' => array(
					'type'  => 'range',
					'label' => __( 'Text stroke width', 'hi-wpessential' ),
					'prop'  => '-webkit-text-stroke-width',
					'unit'  => 'px',
					'min'   => 0,
					'max'   => 10,
					'step'  => 0.5,
				),
				'strokeColor' => array(
					'type'  => 'color',
					'label' => __( 'Text stroke colour', 'hi-wpessential' ),
					'prop'  => '-webkit-text-stroke-color',
				),
				'blend'       => array(
					'type'    => 'select',
					'label'   => __( 'Blend mode', 'hi-wpessential' ),
					'prop'    => 'mix-blend-mode',
					'options' => array(
						''            => __( 'Normal', 'hi-wpessential' ),
						'multiply'    => 'Multiply',
						'screen'      => 'Screen',
						'overlay'     => 'Overlay',
						'darken'      => 'Darken',
						'lighten'     => 'Lighten',
						'color-dodge' => 'Color dodge',
						'difference'  => 'Difference',
						'exclusion'   => 'Exclusion',
						'hue'         => 'Hue',
						'luminosity'  => 'Luminosity',
					),
				),
			),
			'filters'     => array(
				'blur'            => array(
					'type'  => 'range',
					'label' => __( 'Blur', 'hi-wpessential' ),
					'fn'    => 'blur',
					'unit'  => 'px',
					'min'   => 0,
					'max'   => 20,
				),
				'brightness'      => array(
					'type'  => 'range',
					'label' => __( 'Brightness', 'hi-wpessential' ),
					'fn'    => 'brightness',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
				),
				'contrast'        => array(
					'type'  => 'range',
					'label' => __( 'Contrast', 'hi-wpessential' ),
					'fn'    => 'contrast',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
				),
				'saturate'        => array(
					'type'  => 'range',
					'label' => __( 'Saturation', 'hi-wpessential' ),
					'fn'    => 'saturate',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
				),
				'hue'             => array(
					'type'  => 'range',
					'label' => __( 'Hue rotate', 'hi-wpessential' ),
					'fn'    => 'hue-rotate',
					'unit'  => 'deg',
					'min'   => 0,
					'max'   => 360,
				),
				'grey'            => array(
					'type'  => 'range',
					'label' => __( 'Greyscale', 'hi-wpessential' ),
					'fn'    => 'grayscale',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 100,
				),
				'hoverBlur'       => array(
					'type'  => 'range',
					'label' => __( 'Blur on hover', 'hi-wpessential' ),
					'fn'    => 'blur',
					'unit'  => 'px',
					'min'   => 0,
					'max'   => 20,
					'state' => 'hover',
				),
				'hoverBrightness' => array(
					'type'  => 'range',
					'label' => __( 'Brightness on hover', 'hi-wpessential' ),
					'fn'    => 'brightness',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
					'state' => 'hover',
				),
				'hoverContrast'   => array(
					'type'  => 'range',
					'label' => __( 'Contrast on hover', 'hi-wpessential' ),
					'fn'    => 'contrast',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
					'state' => 'hover',
				),
				'hoverSaturate'   => array(
					'type'  => 'range',
					'label' => __( 'Saturation on hover', 'hi-wpessential' ),
					'fn'    => 'saturate',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
					'state' => 'hover',
				),
				'hoverHue'        => array(
					'type'  => 'range',
					'label' => __( 'Hue rotate on hover', 'hi-wpessential' ),
					'fn'    => 'hue-rotate',
					'unit'  => 'deg',
					'min'   => 0,
					'max'   => 360,
					'state' => 'hover',
				),
				'hoverGrey'       => array(
					'type'  => 'range',
					'label' => __( 'Greyscale on hover', 'hi-wpessential' ),
					'fn'    => 'grayscale',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 100,
					'state' => 'hover',
				),
				'opacity'         => array(
					'type'  => 'range',
					'label' => __( 'Opacity (%)', 'hi-wpessential' ),
					'prop'  => 'opacity',
					'pct'   => true,
					'min'   => 0,
					'max'   => 100,
				),
				'hoverOpacity'    => array(
					'type'  => 'range',
					'label' => __( 'Opacity on hover (%)', 'hi-wpessential' ),
					'prop'  => 'opacity',
					'pct'   => true,
					'min'   => 0,
					'max'   => 100,
					'state' => 'hover',
				),
			),
		);
	}

	/**
	 * Shadow presets (theme tokens where the theme has them).
	 *
	 * @return array
	 */
	public static function shadows() {
		return array(
			'none'  => 'none',
			'sm'    => '0 1px 2px rgba(11,26,61,.06), 0 1px 3px rgba(11,26,61,.08)',
			'md'    => '0 4px 12px rgba(11,26,61,.08), 0 12px 32px rgba(11,26,61,.08)',
			'lg'    => '0 18px 40px rgba(11,26,61,.14)',
			'xl'    => '0 30px 70px rgba(11,26,61,.22)',
			'glow'  => '0 0 0 4px color-mix(in srgb, var(--wp--preset--color--primary) 25%, transparent), 0 12px 32px color-mix(in srgb, var(--wp--preset--color--primary) 40%, transparent)',
			'inner' => 'inset 0 2px 6px rgba(11,26,61,.12)',
		);
	}

	/**
	 * Labels for the shadow select.
	 *
	 * @return array
	 */
	private static function shadow_labels() {
		return array(
			''      => __( 'Default', 'hi-wpessential' ),
			'none'  => __( 'None', 'hi-wpessential' ),
			'sm'    => __( 'Small', 'hi-wpessential' ),
			'md'    => __( 'Medium', 'hi-wpessential' ),
			'lg'    => __( 'Large', 'hi-wpessential' ),
			'xl'    => __( 'Extra large', 'hi-wpessential' ),
			'glow'  => __( 'Brand glow', 'hi-wpessential' ),
			'inner' => __( 'Inner', 'hi-wpessential' ),
		);
	}

	/**
	 * Gradient presets from the palette (they follow light/dark).
	 *
	 * @return array
	 */
	public static function gradients() {
		$c = static fn( $s ) => 'var(--wp--preset--color--' . $s . ')';
		return array(
			'brand'   => 'linear-gradient(110deg, #e8701a, #f7a23e)',
			'primary' => 'linear-gradient(135deg, ' . $c( 'primary' ) . ', ' . $c( 'secondary' ) . ')',
			'ink'     => 'linear-gradient(160deg, ' . $c( 'ink' ) . ', color-mix(in srgb, ' . $c( 'ink' ) . ' 70%, ' . $c( 'secondary' ) . '))',
			'soft'    => 'linear-gradient(180deg, ' . $c( 'surface' ) . ', ' . $c( 'surface-alt' ) . ')',
			'fade'    => 'linear-gradient(180deg, transparent, rgba(0,0,0,.6))',
		);
	}

	/**
	 * Labels for the gradient select.
	 *
	 * @return array
	 */
	private static function gradient_labels() {
		return array(
			''        => __( 'None', 'hi-wpessential' ),
			'brand'   => __( 'Brand orange', 'hi-wpessential' ),
			'primary' => __( 'Primary → secondary', 'hi-wpessential' ),
			'ink'     => __( 'Ink', 'hi-wpessential' ),
			'soft'    => __( 'Soft surface', 'hi-wpessential' ),
			'fade'    => __( 'Image fade', 'hi-wpessential' ),
		);
	}

	/**
	 * Theme font families: slug => name.
	 *
	 * @return array
	 */
	public static function fonts() {
		$out = array();
		if ( function_exists( 'wp_get_global_settings' ) ) {
			foreach ( (array) wp_get_global_settings( array( 'typography', 'fontFamilies', 'theme' ) ) as $f ) {
				if ( ! empty( $f['slug'] ) ) {
					$out[ $f['slug'] ] = $f['name'] ?? $f['slug'];
				}
			}
		}
		return $out;
	}

	/**
	 * Common options of every block, in the "Advanced" panel (Elementor has its own).
	 *
	 * @return array array( attributes, groups )
	 */
	public static function common() {
		$px   = array(
			'type'       => 'range',
			'unit'       => 'px',
			'min'        => -500,
			'max'        => 2000,
			'responsive' => true,
			'panel'      => 'advanced',
			'common'     => true,
		);
		$attr = array(
			'customCss'           => array(
				'type'  => 'textarea',
				'panel' => 'advanced',
				'label' => __( 'Custom CSS (write "selector" for this element)', 'hi-wpessential' ),
				'code'  => true,
			),
			'cssClass'            => array(
				'type'   => 'text',
				'label'  => __( 'CSS classes', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'attributes'          => array(
				'type'   => 'textarea',
				'label'  => __( 'Custom attributes (key|value, one per line)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'width'               => array(
				'type'       => 'select',
				'label'      => __( 'Width', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'responsive' => true,
				'css'        => 'width',
				'raw'        => true,
				'options'    => array(
					''            => __( 'Default', 'hi-wpessential' ),
					'100%'        => __( 'Full width', 'hi-wpessential' ),
					'auto'        => __( 'Auto', 'hi-wpessential' ),
					'fit-content' => __( 'Fit content', 'hi-wpessential' ),
				),
			),
			'maxWidth'            => array_merge(
				$px,
				array(
					'label' => __( 'Max width', 'hi-wpessential' ),
					'css'   => 'max-width',
					'raw'   => true,
					'min'   => 0,
				)
			),
			'alignSelf'           => array(
				'type'       => 'select',
				'label'      => __( 'Align self (in flex/grid)', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'responsive' => true,
				'css'        => 'align-self',
				'raw'        => true,
				'options'    => array(
					''           => __( 'Default', 'hi-wpessential' ),
					'flex-start' => __( 'Start', 'hi-wpessential' ),
					'center'     => __( 'Center', 'hi-wpessential' ),
					'flex-end'   => __( 'End', 'hi-wpessential' ),
					'stretch'    => __( 'Stretch', 'hi-wpessential' ),
				),
			),
			'order'               => array(
				'type'       => 'range',
				'label'      => __( 'Order', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'responsive' => true,
				'css'        => 'order',
				'raw'        => true,
				'unit'       => '',
				'min'        => -10,
				'max'        => 10,
			),
			'position'            => array(
				'type'       => 'select',
				'label'      => __( 'Position', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'responsive' => true,
				'css'        => 'position',
				'raw'        => true,
				'options'    => array(
					''         => __( 'Default', 'hi-wpessential' ),
					'relative' => __( 'Relative', 'hi-wpessential' ),
					'absolute' => __( 'Absolute', 'hi-wpessential' ),
					'fixed'    => __( 'Fixed', 'hi-wpessential' ),
					'sticky'   => __( 'Sticky', 'hi-wpessential' ),
				),
			),
			'top'                 => array_merge(
				$px,
				array(
					'label' => __( 'Offset top', 'hi-wpessential' ),
					'css'   => 'top',
					'raw'   => true,
				)
			),
			'right'               => array_merge(
				$px,
				array(
					'label' => __( 'Offset right', 'hi-wpessential' ),
					'css'   => 'right',
					'raw'   => true,
				)
			),
			'bottom'              => array_merge(
				$px,
				array(
					'label' => __( 'Offset bottom', 'hi-wpessential' ),
					'css'   => 'bottom',
					'raw'   => true,
				)
			),
			'left'                => array_merge(
				$px,
				array(
					'label' => __( 'Offset left', 'hi-wpessential' ),
					'css'   => 'left',
					'raw'   => true,
				)
			),
			// Logical inline spacing respects direction: start is left in LTR, right in RTL.
			'logicalMarginStart'  => array_merge(
				$px,
				array(
					'label'     => __( 'Margin inline start (RTL-aware)', 'hi-wpessential' ),
					'css'       => 'margin-inline-start',
					'raw'       => true,
					'important' => true,
				)
			),
			'logicalMarginEnd'    => array_merge(
				$px,
				array(
					'label'     => __( 'Margin inline end (RTL-aware)', 'hi-wpessential' ),
					'css'       => 'margin-inline-end',
					'raw'       => true,
					'important' => true,
				)
			),
			'logicalPaddingStart' => array_merge(
				$px,
				array(
					'label'     => __( 'Padding inline start (RTL-aware)', 'hi-wpessential' ),
					'css'       => 'padding-inline-start',
					'raw'       => true,
					'important' => true,
					'min'       => 0,
				)
			),
			'logicalPaddingEnd'   => array_merge(
				$px,
				array(
					'label'     => __( 'Padding inline end (RTL-aware)', 'hi-wpessential' ),
					'css'       => 'padding-inline-end',
					'raw'       => true,
					'important' => true,
					'min'       => 0,
				)
			),
			'zIndex'              => array(
				'type'   => 'range',
				'label'  => __( 'Z-index', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => 'z-index',
				'raw'    => true,
				'unit'   => '',
				'min'    => -10,
				'max'    => 999,
			),
			'opacity'             => array(
				'type'   => 'range',
				'label'  => __( 'Opacity', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => 'opacity',
				'raw'    => true,
				'unit'   => '',
				'min'    => 0,
				'max'    => 1,
				'step'   => 0.05,
			),
			'hoverOpacity'        => array(
				'type'   => 'range',
				'label'  => __( 'Hover opacity', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => 'opacity',
				'raw'    => true,
				'state'  => 'hover',
				'unit'   => '',
				'min'    => 0,
				'max'    => 1,
				'step'   => 0.05,
			),
			'rotate'              => array(
				'type'   => 'range',
				'label'  => __( 'Rotate', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-rot',
				'unit'   => 'deg',
				'min'    => -180,
				'max'    => 180,
			),
			'scale'               => array(
				'type'   => 'range',
				'label'  => __( 'Scale', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-sc',
				'unit'   => '',
				'min'    => 0,
				'max'    => 3,
				'step'   => 0.05,
			),
			'moveX'               => array(
				'type'   => 'range',
				'label'  => __( 'Move X', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-tx',
				'unit'   => 'px',
				'min'    => -300,
				'max'    => 300,
			),
			'moveY'               => array(
				'type'   => 'range',
				'label'  => __( 'Move Y', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-ty',
				'unit'   => 'px',
				'min'    => -300,
				'max'    => 300,
			),
			'hoverRotate'         => array(
				'type'   => 'range',
				'label'  => __( 'Hover rotate', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-h-rot',
				'unit'   => 'deg',
				'min'    => -180,
				'max'    => 180,
			),
			'hoverScale'          => array(
				'type'   => 'range',
				'label'  => __( 'Hover scale', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-h-sc',
				'unit'   => '',
				'min'    => 0,
				'max'    => 3,
				'step'   => 0.05,
			),
			'hoverMoveY'          => array(
				'type'   => 'range',
				'label'  => __( 'Hover move Y', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-h-ty',
				'unit'   => 'px',
				'min'    => -100,
				'max'    => 100,
			),
			'transition'          => array(
				'type'   => 'range',
				'label'  => __( 'Transition (ms)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-dur',
				'unit'   => 'ms',
				'min'    => 0,
				'max'    => 3000,
				'step'   => 50,
			),
			'transitionEase'      => array(
				'type'    => 'select',
				'label'   => __( 'Transition easing', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'css'     => '--wpek-ease',
				'raw'     => true,
				'options' => array(
					''                             => __( 'Theme default', 'hi-wpessential' ),
					'ease'                         => 'Ease',
					'linear'                       => __( 'Linear', 'hi-wpessential' ),
					'cubic-bezier(.2,.8,.2,1)'     => __( 'Smooth out', 'hi-wpessential' ),
					'cubic-bezier(.65,0,.35,1)'    => __( 'In-out', 'hi-wpessential' ),
					'cubic-bezier(.34,1.56,.64,1)' => __( 'Spring', 'hi-wpessential' ),
				),
			),
			'transitionDelay'     => array(
				'type'   => 'range',
				'label'  => __( 'Transition delay (ms)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-tdelay',
				'unit'   => 'ms',
				'min'    => 0,
				'max'    => 2000,
				'step'   => 50,
			),
			'hoverPreset'         => array(
				'type'    => 'select',
				'label'   => __( 'Hover preset (from the demo)', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''       => __( 'None', 'hi-wpessential' ),
					'lift'   => __( 'Lift', 'hi-wpessential' ),
					'zoom'   => __( 'Zoom', 'hi-wpessential' ),
					'glow'   => __( 'Glow', 'hi-wpessential' ),
					'shadow' => __( 'Shadow', 'hi-wpessential' ),
					'dim'    => __( 'Dim others', 'hi-wpessential' ),
					'tilt'   => __( 'Tilt', 'hi-wpessential' ),
				),
			),
			'rootAriaLabel'       => array(
				'type'   => 'text',
				'label'  => __( 'Wrapper ARIA label (needs a role)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'rootRole'            => array(
				'type'    => 'select',
				'label'   => __( 'Wrapper ARIA role', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''              => __( 'Default', 'hi-wpessential' ),
					'region'        => 'region',
					'banner'        => 'banner',
					'complementary' => 'complementary',
					'note'          => 'note',
					'presentation'  => 'presentation',
					'group'         => 'group',
				),
			),
			'rootAriaHidden'      => array(
				'type'   => 'toggle',
				'label'  => __( 'Decorative: hide from screen readers', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'rootAriaLive'        => array(
				'type'    => 'select',
				'label'   => __( 'Announce changes (aria-live)', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''          => __( 'Off', 'hi-wpessential' ),
					'polite'    => __( 'Polite', 'hi-wpessential' ),
					'assertive' => __( 'Assertive', 'hi-wpessential' ),
				),
			),
			'animation'           => array(
				'type'    => 'select',
				'label'   => __( 'Entrance animation', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'default' => '',
				'options' => self::animations(),
			),
			'animDuration'        => array(
				'type'   => 'range',
				'label'  => __( 'Animation duration (ms)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-adur',
				'unit'   => 'ms',
				'min'    => 100,
				'max'    => 3000,
				'step'   => 50,
			),
			'animDelay'           => array(
				'type'   => 'range',
				'label'  => __( 'Animation delay (ms)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-adelay',
				'unit'   => 'ms',
				'min'    => 0,
				'max'    => 3000,
				'step'   => 50,
			),
			'animTrigger'         => array(
				'type'    => 'select',
				'label'   => __( 'Entrance trigger', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''       => __( 'Default scroll (CSS)', 'hi-wpessential' ),
					'scroll' => __( 'Scroll into view', 'hi-wpessential' ),
					'hover'  => __( 'Hover or keyboard focus', 'hi-wpessential' ),
					'click'  => __( 'Click or tap', 'hi-wpessential' ),
					'load'   => __( 'Page load', 'hi-wpessential' ),
				),
			),
			'animReduced'         => array(
				'type'    => 'select',
				'label'   => __( 'Reduced-motion alternative', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''     => __( 'No animation', 'hi-wpessential' ),
					'fade' => __( 'Gentle fade (no movement)', 'hi-wpessential' ),
				),
			),
			'staggerChildren'     => array(
				'type'   => 'toggle',
				'label'  => __( 'Stagger direct child elements', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'staggerStep'         => array(
				'type'   => 'range',
				'label'  => __( 'Time between children (ms)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-stagger-step',
				'unit'   => 'ms',
				'min'    => 0,
				'max'    => 500,
				'step'   => 25,
			),
			'stickyEnabled'       => array(
				'type'   => 'toggle',
				'label'  => __( 'Sticky within parent container', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'stickyOffset'        => array(
				'type'       => 'range',
				'label'      => __( 'Sticky top offset (px)', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-sticky-offset',
				'unit'       => 'px',
				'min'        => 0,
				'max'        => 300,
				'responsive' => true,
			),
			'stickyFrom'          => array(
				'type'    => 'select',
				'label'   => __( 'Sticky enabled from screen width', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''        => __( 'Every breakpoint', 'hi-wpessential' ),
					'tablet'  => __( 'Tablet and desktop (768px+)', 'hi-wpessential' ),
					'desktop' => __( 'Desktop only (1024px+)', 'hi-wpessential' ),
				),
			),
			'parallaxEnabled'     => array(
				'type'   => 'toggle',
				'label'  => __( 'Parallax: enable direct-child layers', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'parallaxAxis'        => array(
				'type'    => 'select',
				'label'   => __( 'Parallax axis', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''  => __( 'Vertical', 'hi-wpessential' ),
					'x' => __( 'Horizontal', 'hi-wpessential' ),
				),
			),
			'parallaxLayer1'      => array(
				'type'       => 'range',
				'label'      => __( 'Parallax layer 1 travel (px, 0 disables)', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-parallax-layer-1',
				'unit'       => 'px',
				'units'      => array( 'px' ),
				'min'        => -240,
				'max'        => 240,
				'step'       => 5,
				'responsive' => true,
			),
			'parallaxLayer2'      => array(
				'type'       => 'range',
				'label'      => __( 'Parallax layer 2 travel (px, 0 disables)', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-parallax-layer-2',
				'unit'       => 'px',
				'units'      => array( 'px' ),
				'min'        => -240,
				'max'        => 240,
				'step'       => 5,
				'responsive' => true,
			),
			'parallaxLayer3'      => array(
				'type'       => 'range',
				'label'      => __( 'Parallax layer 3 travel (px, 0 disables)', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-parallax-layer-3',
				'unit'       => 'px',
				'units'      => array( 'px' ),
				'min'        => -240,
				'max'        => 240,
				'step'       => 5,
				'responsive' => true,
			),
			'parallaxLayer4'      => array(
				'type'       => 'range',
				'label'      => __( 'Parallax layer 4 travel (px, 0 disables)', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-parallax-layer-4',
				'unit'       => 'px',
				'units'      => array( 'px' ),
				'min'        => -240,
				'max'        => 240,
				'step'       => 5,
				'responsive' => true,
			),
			'stickyStopId'        => array(
				'type'   => 'text',
				'label'  => __( 'Sticky stop at element ID (same page)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'focusRingStyle'      => array(
				'type'    => 'select',
				'label'   => __( 'Keyboard focus outline style', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'css'     => '--wpek-focus-style',
				'options' => array(
					''       => __( 'Theme default', 'hi-wpessential' ),
					'solid'  => __( 'Solid', 'hi-wpessential' ),
					'dashed' => __( 'Dashed', 'hi-wpessential' ),
					'double' => __( 'Double', 'hi-wpessential' ),
				),
			),
			'focusRingColor'      => array(
				'type'   => 'color',
				'label'  => __( 'Keyboard focus outline colour', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-focus-color',
			),
			'focusRingWidth'      => array(
				'type'       => 'range',
				'label'      => __( 'Keyboard focus outline width', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-focus-width',
				'unit'       => 'px',
				'min'        => 1,
				'max'        => 9,
				'step'       => 1,
				'responsive' => true,
			),
			'focusRingOffset'     => array(
				'type'       => 'range',
				'label'      => __( 'Keyboard focus outline offset', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-focus-offset',
				'unit'       => 'px',
				'min'        => 0,
				'max'        => 15,
				'step'       => 1,
				'responsive' => true,
			),
			'glass'               => array(
				'type'   => 'toggle',
				'label'  => __( 'Glass / translucent surface', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'glassBlur'           => array(
				'type'       => 'range',
				'label'      => __( 'Glass backdrop blur', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-glass-blur',
				'unit'       => 'px',
				'min'        => 0,
				'max'        => 60,
				'step'       => 1,
				'responsive' => true,
			),
			'glassOpacity'        => array(
				'type'       => 'range',
				'label'      => __( 'Glass surface opacity', 'hi-wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-glass-opacity',
				'unit'       => '%',
				'min'        => 30,
				'max'        => 100,
				'step'       => 1,
				'responsive' => true,
			),
			'hidePrint'           => array(
				'type'   => 'toggle',
				'label'  => __( 'Hide this element when printing', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'hide'                => array(
				'type'   => 'breakpoints',
				'label'  => __( 'Hide on', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
		);

		// Advanced responsive foundations shared by Gutenberg and Elementor.
		$attr += array(
			'containerQuerySource'    => array(
				'type'   => 'toggle',
				'label'  => __( 'Act as a CSS size query container', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'containerQueryHideBelow' => array(
				'type'   => 'range',
				'label'  => __( 'Hide below nearest container width', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 160,
				'max'    => 1920,
				'step'   => 1,
			),
			'containerQueryHideAbove' => array(
				'type'   => 'range',
				'label'  => __( 'Hide above nearest container width', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 160,
				'max'    => 1920,
				'step'   => 1,
			),
			'fluidPaddingMin'         => array(
				'type'   => 'range',
				'label'  => __( 'Fluid padding at viewport 360px', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 0,
				'max'    => 260,
				'step'   => 1,
			),
			'fluidPaddingMax'         => array(
				'type'   => 'range',
				'label'  => __( 'Fluid padding at viewport 1440px', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 0,
				'max'    => 260,
				'step'   => 1,
			),
			'fluidGapMin'             => array(
				'type'   => 'range',
				'label'  => __( 'Fluid gap at viewport 360px', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 0,
				'max'    => 260,
				'step'   => 1,
			),
			'fluidGapMax'             => array(
				'type'   => 'range',
				'label'  => __( 'Fluid gap at viewport 1440px', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 0,
				'max'    => 260,
				'step'   => 1,
			),
			'paddingToken'            => array(
				'type'      => 'select',
				'label'     => __( 'Theme padding token', 'hi-wpessential' ),
				'panel'     => 'advanced',
				'common'    => true,
				'css'       => 'padding',
				'important' => true,
				'map'       => array(
					'sm' => 'padding:var(--wp--preset--spacing--20,8px)',
					'md' => 'padding:var(--wp--preset--spacing--40,16px)',
					'lg' => 'padding:var(--wp--preset--spacing--50,24px)',
					'xl' => 'padding:var(--wp--preset--spacing--70,48px)',
				),
				'options'   => array(
					''   => __( 'Default', 'hi-wpessential' ),
					'sm' => 'Small (8px)',
					'md' => 'Medium (16px)',
					'lg' => 'Large (24px)',
					'xl' => 'Extra large (48px)',
				),
			),
			'radiusToken'             => array(
				'type'      => 'select',
				'label'     => __( 'Theme radius token', 'hi-wpessential' ),
				'panel'     => 'advanced',
				'common'    => true,
				'css'       => 'border-radius',
				'important' => true,
				'map'       => array(
					'sm' => 'border-radius:var(--wp--custom--radius--sm,6px)',
					'md' => 'border-radius:var(--wp--custom--radius--md,10px)',
					'lg' => 'border-radius:var(--wp--custom--radius--lg,16px)',
					'xl' => 'border-radius:var(--wp--custom--radius--xl,24px)',
				),
				'options'   => array(
					''   => __( 'Default', 'hi-wpessential' ),
					'sm' => 'Small',
					'md' => 'Medium',
					'lg' => 'Large',
					'xl' => 'Extra large',
				),
			),
		);

		// Conditional display rules are evaluated before rendering content.
		$attr  += array(
			'conditionLogin'            => array(
				'type'    => 'select',
				'label'   => __( 'Visibility: visitor login state', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''    => __( 'Everyone', 'hi-wpessential' ),
					'in'  => __( 'Logged-in visitors only', 'hi-wpessential' ),
					'out' => __( 'Logged-out visitors only', 'hi-wpessential' ),
				),
			),
			'conditionRole'             => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: required user role slug (single role)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionCart'             => array(
				'type'    => 'select',
				'label'   => __( 'Visibility: WooCommerce cart', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''         => __( 'Any cart', 'hi-wpessential' ),
					'nonempty' => __( 'Cart must contain items', 'hi-wpessential' ),
					'empty'    => __( 'Cart must be empty', 'hi-wpessential' ),
				),
			),
			'conditionPurchasedProduct' => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: purchased WooCommerce product ID', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionStart'            => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: start date (YYYY-MM-DD HH:mm, site time)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionEnd'              => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: end date (YYYY-MM-DD HH:mm, site time)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionPath'             => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: exact URL path (e.g. /offers/)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionQueryKey'         => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: URL query parameter name', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionQueryValue'       => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: required URL query value (optional)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionReferrer'         => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: exact referrer domain (not secure)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionDevice'           => array(
				'type'    => 'select',
				'label'   => __( 'Visibility: device viewport (CSS only)', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''        => __( 'Any screen', 'hi-wpessential' ),
					'mobile'  => __( 'Mobile under 768px', 'hi-wpessential' ),
					'tablet'  => __( 'Tablet 768–1023px', 'hi-wpessential' ),
					'desktop' => __( 'Desktop 1024px and wider', 'hi-wpessential' ),
				),
			),
		);
		$groups = array(
			'advMargin'     => array(
				'type'     => 'spacing',
				'label'    => __( 'Margin', 'hi-wpessential' ),
				'prop'     => 'margin',
				'selector' => '',
				'panel'    => 'advanced',
				'common'   => true,
			),
			'advPadding'    => array(
				'type'     => 'spacing',
				'label'    => __( 'Padding', 'hi-wpessential' ),
				'prop'     => 'padding',
				'selector' => '',
				'panel'    => 'advanced',
				'common'   => true,
			),
			'advBackground' => array(
				'type'     => 'background',
				'label'    => __( 'Background', 'hi-wpessential' ),
				'selector' => '',
				'panel'    => 'advanced',
				'common'   => true,
			),
			'advBorder'     => array(
				'type'     => 'border',
				'label'    => __( 'Border', 'hi-wpessential' ),
				'selector' => '',
				'panel'    => 'advanced',
				'common'   => true,
			),
			'advShadow'     => array(
				'type'     => 'shadow',
				'label'    => __( 'Box shadow', 'hi-wpessential' ),
				'selector' => '',
				'panel'    => 'advanced',
				'common'   => true,
			),
		);
		return array( $attr, $groups );
	}

	/**
	 * Entrance animations (core/kit/common.css).
	 *
	 * @return array
	 */
	public static function animations() {
		return array(
			''           => __( 'None', 'hi-wpessential' ),
			'fade'       => __( 'Fade in', 'hi-wpessential' ),
			'fade-up'    => __( 'Fade up', 'hi-wpessential' ),
			'fade-down'  => __( 'Fade down', 'hi-wpessential' ),
			'fade-left'  => __( 'Fade from right', 'hi-wpessential' ),
			'fade-right' => __( 'Fade from left', 'hi-wpessential' ),
			'zoom-in'    => __( 'Zoom in', 'hi-wpessential' ),
			'zoom-out'   => __( 'Zoom out', 'hi-wpessential' ),
			'slide-up'   => __( 'Slide up', 'hi-wpessential' ),
			'flip-x'     => __( 'Flip', 'hi-wpessential' ),
			'blur-in'    => __( 'Blur in', 'hi-wpessential' ),
			'reveal'     => __( 'Reveal (clip)', 'hi-wpessential' ),
			'rotate-in'  => __( 'Rotate in', 'hi-wpessential' ),
		);
	}

	/**
	 * CSS declarations of one group: [ breakpoint ][ selector ][] = declaration.
	 *
	 * @param array  $group Group definition.
	 * @param array  $value Stored values.
	 * @param string $root  Root selector (#id).
	 * @param array  $rules Rules to add to.
	 */
	public static function css( array $group, array $value, $root, array &$rules ) {
		$fields = self::fields()[ $group['type'] ] ?? array();
		$base   = trim( $root . ' ' . ( $group['selector'] ?? '' ) );
		$parts  = array_map( 'trim', explode( ',', $base ) );
		// Fluid font size: one clamp() between the 360px and the 1440px value.
		if ( 'typography' === $group['type'] && is_numeric( $value['sizeMin'] ?? '' ) && is_numeric( $value['sizeMax'] ?? '' ) ) {
			$min                      = (float) $value['sizeMin'];
			$max                      = (float) $value['sizeMax'];
			$slope                    = ( $max - $min ) / ( 1440 - 360 );
			$rules['base'][ $base ][] = sprintf( 'font-size:clamp(%1$spx, calc(%2$spx + %3$svw), %4$spx)', min( $min, $max ), round( $min - $slope * 360, 3 ), round( $slope * 100, 4 ), max( $min, $max ) );
		}
		$states = array(
			'hover'  => array( ':hover', ':focus-visible' ),
			'focus'  => array( ':focus-visible' ),
			'active' => array( ':active' ),
		);
		// CSS filters: the functions of one state become one filter declaration.
		foreach ( array(
			''      => $parts,
			'hover' => null,
		) as $state => $sels ) {
			$fns = array();
			foreach ( $fields as $key => $f ) {
				if ( ! empty( $f['fn'] ) && ( $f['state'] ?? '' ) === $state && is_numeric( $value[ $key ] ?? '' ) ) {
					$fns[] = $f['fn'] . '(' . ( 0 + $value[ $key ] ) . $f['unit'] . ')';
				}
			}
			if ( $fns ) {
				$sels                                     = $sels ?? array_merge( ...array_map( static fn( $x ) => array( $x . ':hover', $x . ':focus-visible' ), $parts ) );
				$rules['base'][ implode( ',', $sels ) ][] = 'filter:' . implode( ' ', $fns );
			}
		}
		foreach ( $fields as $key => $f ) {
			if ( ! isset( $value[ $key ] ) || ! empty( $f['fluid'] ) || ! empty( $f['fn'] ) ) {
				continue;
			}
			$values = ! empty( $f['responsive'] ) && is_array( $value[ $key ] ) ? $value[ $key ] : array( 'base' => $value[ $key ] );
			$prop   = str_replace( '{prop}', $group['prop'] ?? 'color', $f['prop'] );
			$sels   = $parts;
			if ( ! empty( $f['state'] ) ) {
				$sels = array();
				foreach ( $parts as $part ) {
					foreach ( $states[ $f['state'] ] as $pseudo ) {
						$sels[] = $part . $pseudo;
					}
				}
			}
			if ( ! empty( $f['scheme'] ) ) {
				// The theme's visitor toggle sets data-wpe-scheme on <html>.
				$sels = array_map( static fn( $x ) => ':root[data-wpe-scheme="dark"] ' . $x, $sels );
			}
			$sel = implode( ',', $sels );
			foreach ( $values as $bp => $v ) {
				if ( '' === $v || null === $v ) {
					continue;
				}
				$css = self::value( $f, $v );
				if ( '' !== $css ) {
					$rules[ (string) $bp ][ $sel ][] = $prop . ':' . $css;
				}
			}
		}
	}

	/**
	 * One field value as CSS.
	 *
	 * @param array $f Field.
	 * @param mixed $v Value.
	 * @return string
	 */
	private static function value( array $f, $v ) {
		if ( 'color' === $f['type'] ) {
			return 'var(--wp--preset--color--' . sanitize_key( $v ) . ')';
		}
		if ( ! empty( $f['font'] ) ) {
			return 'var(--wp--preset--font-family--' . sanitize_key( $v ) . ')';
		}
		if ( 'box-shadow' === $f['prop'] && 'text' !== $f['type'] ) {
			return self::shadows()[ $v ] ?? '';
		}
		if ( 'background-image' === $f['prop'] ) {
			return ! empty( $f['image'] ) ? 'url("' . esc_url_raw( $v ) . '")' : ( self::gradients()[ $v ] ?? '' );
		}
		if ( ! empty( $f['pct'] ) && is_numeric( $v ) ) {
			return (string) ( ( 0 + $v ) / 100 );
		}
		if ( 'range' === $f['type'] && is_numeric( $v ) ) {
			return ( 0 + $v ) . ( $f['unit'] ?? '' );
		}
		return preg_replace( '/[^a-z0-9#%.,()\/ \-]/i', '', (string) $v );
	}

	/**
	 * Native Elementor controls for one group.
	 *
	 * @param \Elementor\Widget_Base $w     Widget.
	 * @param string                 $key   Group key.
	 * @param array                  $group Group.
	 */
	public static function elementor( $w, $key, array $group ) {
		$sel   = '{{WRAPPER}} ' . ( $group['selector'] ?? '' );
		$hover = implode( ',', array_map( static fn( $s ) => '{{WRAPPER}} ' . trim( $s ) . ':hover', explode( ',', $group['selector'] ?? '' ) ) );
		$label = $group['label'] ?? $key;
		switch ( $group['type'] ) {
			case 'typography':
				$w->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'selector' => $sel,
					)
				);
				break;
			case 'spacing':
				$w->add_responsive_control(
					$key,
					array(
						'label'      => $label,
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', 'em', 'rem', '%', 'vw' ),
						'selectors'  => array( $sel => ( $group['prop'] ?? 'padding' ) . ': {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
					)
				);
				break;
			case 'border':
				$w->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'selector' => $sel,
					)
				);
				$w->add_responsive_control(
					$key . '_radius',
					array(
						'label'      => __( 'Border radius', 'hi-wpessential' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', '%', 'em' ),
						'selectors'  => array( $sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
					)
				);
				$w->add_control(
					$key . '_hover_color',
					array(
						'label'     => __( 'Hover border colour', 'hi-wpessential' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => array( $hover => 'border-color: {{VALUE}};' ),
					)
				);
				break;
			case 'shadow':
				$w->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'selector' => $sel,
					)
				);
				$w->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					array(
						'name'     => $key . '_hover',
						'label'    => __( 'Hover shadow', 'hi-wpessential' ),
						'selector' => $hover,
					)
				);
				break;
			case 'background':
				$w->add_group_control(
					\Elementor\Group_Control_Background::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'types'    => array( 'classic', 'gradient' ),
						'selector' => $sel,
					)
				);
				$w->add_group_control(
					\Elementor\Group_Control_Background::get_type(),
					array(
						'name'     => $key . '_hover',
						'label'    => __( 'Hover background', 'hi-wpessential' ),
						'types'    => array( 'classic', 'gradient' ),
						'selector' => $hover,
					)
				);
				break;
			case 'textEffects':
				$w->add_group_control(
					\Elementor\Group_Control_Text_Shadow::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'selector' => $sel,
					)
				);
				$w->add_group_control(
					\Elementor\Group_Control_Text_Shadow::get_type(),
					array(
						'name'     => $key . '_hover',
						'label'    => __( 'Text shadow on hover', 'hi-wpessential' ),
						'selector' => $hover,
					)
				);
				$w->add_group_control(
					\Elementor\Group_Control_Text_Stroke::get_type(),
					array(
						'name'     => $key . '_stroke',
						'selector' => $sel,
					)
				);
				$w->add_control(
					$key . '_blend',
					array(
						'label'     => __( 'Blend mode', 'hi-wpessential' ),
						'type'      => \Elementor\Controls_Manager::SELECT,
						'options'   => self::fields()['textEffects']['blend']['options'],
						'selectors' => array( $sel => 'mix-blend-mode: {{VALUE}};' ),
					)
				);
				break;
			case 'filters':
				$w->add_group_control(
					\Elementor\Group_Control_Css_Filter::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'selector' => $sel,
					)
				);
				$w->add_group_control(
					\Elementor\Group_Control_Css_Filter::get_type(),
					array(
						'name'     => $key . '_hover',
						'label'    => __( 'CSS filters on hover', 'hi-wpessential' ),
						'selector' => $hover,
					)
				);
				foreach ( array(
					''       => array( __( 'Opacity', 'hi-wpessential' ), $sel ),
					'_hover' => array( __( 'Opacity on hover', 'hi-wpessential' ), $hover ),
				) as $suffix => $o ) {
					$w->add_control(
						$key . '_opacity' . $suffix,
						array(
							'label'     => $o[0],
							'type'      => \Elementor\Controls_Manager::SLIDER,
							'range'     => array(
								'px' => array(
									'min'  => 0,
									'max'  => 1,
									'step' => 0.01,
								),
							),
							'selectors' => array( $o[1] => 'opacity: {{SIZE}};' ),
						)
					);
				}
				break;
			case 'colors':
				$w->add_control(
					$key,
					array(
						'label'     => $label,
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => array( $sel => ( $group['prop'] ?? 'color' ) . ': {{VALUE}};' ),
					)
				);
				$w->add_control(
					$key . '_hover',
					array(
						/* translators: %s: option label. */
						'label'     => sprintf( __( '%s (hover)', 'hi-wpessential' ), $label ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => array( $hover => ( $group['prop'] ?? 'color' ) . ': {{VALUE}};' ),
					)
				);
				$each  = static fn( $suffix ) => implode( ',', array_map( static fn( $x ) => '{{WRAPPER}} ' . trim( $x ) . $suffix, explode( ',', $group['selector'] ?? '' ) ) );
				$dark  = ':root[data-wpe-scheme="dark"] ';
				$extra = array(
					'_focus'      => array( /* translators: %s: option label. */ __( '%s (keyboard focus)', 'hi-wpessential' ), $each( ':focus-visible' ) ),
					'_active'     => array( /* translators: %s: option label. */ __( '%s (pressed)', 'hi-wpessential' ), $each( ':active' ) ),
					'_dark'       => array( /* translators: %s: option label. */ __( '%s (dark mode)', 'hi-wpessential' ), implode( ',', array_map( static fn( $x ) => $dark . $x, explode( ',', $each( '' ) ) ) ) ),
					'_dark_hover' => array( /* translators: %s: option label. */ __( '%s (dark mode, hover)', 'hi-wpessential' ), implode( ',', array_map( static fn( $x ) => $dark . $x, explode( ',', $each( ':hover' ) ) ) ) ),
				);
				foreach ( $extra as $suffix => $def ) {
					$w->add_control(
						$key . $suffix,
						array(
							'label'     => sprintf( $def[0], $label ),
							'type'      => \Elementor\Controls_Manager::COLOR,
							'selectors' => array( $def[1] => ( $group['prop'] ?? 'color' ) . ': {{VALUE}};' ),
						)
					);
				}
				break;
		}
	}
}
