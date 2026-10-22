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
			'typography' => array(
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
			'spacing'    => array(
				'top'    => $side( '{prop}-top', __( 'Top', 'hi-wpessential' ) ),
				'right'  => $side( '{prop}-right', __( 'Right', 'hi-wpessential' ) ),
				'bottom' => $side( '{prop}-bottom', __( 'Bottom', 'hi-wpessential' ) ),
				'left'   => $side( '{prop}-left', __( 'Left', 'hi-wpessential' ) ),
			),
			'border'     => array(
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
			'shadow'     => array(
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
				'custom'      => array(
					'type'  => 'text',
					'label' => __( 'Custom shadow (CSS)', 'hi-wpessential' ),
					'prop'  => 'box-shadow',
				),
			),
			'background' => array(
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
			'colors'     => array(
				'normal' => array(
					'type'  => 'color',
					'label' => __( 'Colour', 'hi-wpessential' ),
					'prop'  => '{prop}',
				),
				'hover'  => array(
					'type'  => 'color',
					'label' => __( 'Hover colour', 'hi-wpessential' ),
					'prop'  => '{prop}',
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
		$px     = array(
			'type'       => 'range',
			'unit'       => 'px',
			'min'        => -500,
			'max'        => 2000,
			'responsive' => true,
			'panel'      => 'advanced',
			'common'     => true,
		);
		$attr   = array(
			'cssClass'     => array(
				'type'   => 'text',
				'label'  => __( 'CSS classes', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'attributes'   => array(
				'type'   => 'textarea',
				'label'  => __( 'Custom attributes (key|value, one per line)', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'width'        => array(
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
			'maxWidth'     => array_merge(
				$px,
				array(
					'label' => __( 'Max width', 'hi-wpessential' ),
					'css'   => 'max-width',
					'raw'   => true,
					'min'   => 0,
				)
			),
			'alignSelf'    => array(
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
			'order'        => array(
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
			'position'     => array(
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
			'top'          => array_merge(
				$px,
				array(
					'label' => __( 'Offset top', 'hi-wpessential' ),
					'css'   => 'top',
					'raw'   => true,
				)
			),
			'right'        => array_merge(
				$px,
				array(
					'label' => __( 'Offset right', 'hi-wpessential' ),
					'css'   => 'right',
					'raw'   => true,
				)
			),
			'bottom'       => array_merge(
				$px,
				array(
					'label' => __( 'Offset bottom', 'hi-wpessential' ),
					'css'   => 'bottom',
					'raw'   => true,
				)
			),
			'left'         => array_merge(
				$px,
				array(
					'label' => __( 'Offset left', 'hi-wpessential' ),
					'css'   => 'left',
					'raw'   => true,
				)
			),
			'zIndex'       => array(
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
			'opacity'      => array(
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
			'hoverOpacity' => array(
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
			'rotate'       => array(
				'type'   => 'range',
				'label'  => __( 'Rotate', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-rot',
				'unit'   => 'deg',
				'min'    => -180,
				'max'    => 180,
			),
			'scale'        => array(
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
			'moveX'        => array(
				'type'   => 'range',
				'label'  => __( 'Move X', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-tx',
				'unit'   => 'px',
				'min'    => -300,
				'max'    => 300,
			),
			'moveY'        => array(
				'type'   => 'range',
				'label'  => __( 'Move Y', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-ty',
				'unit'   => 'px',
				'min'    => -300,
				'max'    => 300,
			),
			'hoverRotate'  => array(
				'type'   => 'range',
				'label'  => __( 'Hover rotate', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-h-rot',
				'unit'   => 'deg',
				'min'    => -180,
				'max'    => 180,
			),
			'hoverScale'   => array(
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
			'hoverMoveY'   => array(
				'type'   => 'range',
				'label'  => __( 'Hover move Y', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-h-ty',
				'unit'   => 'px',
				'min'    => -100,
				'max'    => 100,
			),
			'transition'   => array(
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
			'animation'    => array(
				'type'    => 'select',
				'label'   => __( 'Entrance animation', 'hi-wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'default' => '',
				'options' => self::animations(),
			),
			'animDuration' => array(
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
			'animDelay'    => array(
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
			'hide'         => array(
				'type'   => 'breakpoints',
				'label'  => __( 'Hide on', 'hi-wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
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
		foreach ( $fields as $key => $f ) {
			if ( ! isset( $value[ $key ] ) ) {
				continue;
			}
			$values = ! empty( $f['responsive'] ) && is_array( $value[ $key ] ) ? $value[ $key ] : array( 'base' => $value[ $key ] );
			$prop   = str_replace( '{prop}', $group['prop'] ?? 'color', $f['prop'] );
			$sel    = trim( $root . ' ' . ( $group['selector'] ?? '' ) );
			if ( ! empty( $f['state'] ) ) {
				$sel = $root . ( ( $group['selector'] ?? '' ) ? ' ' . $group['selector'] : '' );
				$sel = implode( ',', array_map( static fn( $s ) => trim( $s ) . ':hover', explode( ',', $sel ) ) ) . ',' . implode( ',', array_map( static fn( $s ) => trim( $s ) . ':focus-visible', explode( ',', $sel ) ) );
			}
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
		if ( 'range' === $f['type'] && is_numeric( $v ) ) {
			return ( 0 + $v ) . ( $f['unit'] ?? '' );
		}
		return preg_replace( '/[^a-z0-9#%.,() \-]/i', '', (string) $v );
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
				break;
		}
	}
}
