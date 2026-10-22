<?php
/**
 * Elementor widget built from a kit element definition: native controls (responsive where the
 * option is), and the same render as the Gutenberg block.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core\Kit;

use WPEssential\Core\Kit;
use WPEssential\Core\Kit\Groups;

defined( 'ABSPATH' ) || exit;

/**
 * Base widget; each element subclass sets KIT to its folder name.
 */
abstract class Widget extends \Elementor\Widget_Base {

	const KIT = '';

	/**
	 * Definition.
	 *
	 * @return array
	 */
	protected function def() {
		return Kit::defs()[ static::KIT ] ?? array( 'attributes' => array() );
	}

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wpe-' . static::KIT;
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->def()['title'] ?? static::KIT;
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return $this->def()['elementor_icon'] ?? 'eicon-star';
	}

	/**
	 * Category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'wpessential' );
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return $this->def()['keywords'] ?? array();
	}

	/**
	 * Style handles.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'wpessential-kit-' . static::KIT );
	}

	/**
	 * Controls from the definition, one section per panel.
	 */
	protected function register_controls() {
		$def    = $this->def();
		$panels = $def['panels'] ?? array( 'content' => __( 'Content', 'hi-wpessential' ) );
		$types  = array(
			'text'     => \Elementor\Controls_Manager::TEXT,
			'textarea' => \Elementor\Controls_Manager::TEXTAREA,
			'url'      => \Elementor\Controls_Manager::TEXT,
			'image'    => \Elementor\Controls_Manager::MEDIA,
			'html'     => \Elementor\Controls_Manager::WYSIWYG,
			'select'   => \Elementor\Controls_Manager::SELECT,
			'color'    => \Elementor\Controls_Manager::SELECT,
			'toggle'   => \Elementor\Controls_Manager::SWITCHER,
			'range'    => \Elementor\Controls_Manager::SLIDER,
		);
		foreach ( $panels as $panel => $label ) {
			$this->start_controls_section(
				'section_' . $panel,
				array(
					'label' => $label,
					'tab'   => 'style' === $panel ? \Elementor\Controls_Manager::TAB_STYLE : \Elementor\Controls_Manager::TAB_CONTENT,
				)
			);
			foreach ( $def['attributes'] as $key => $a ) {
				if ( ( $a['panel'] ?? 'content' ) !== $panel ) {
					continue;
				}
				$args = array(
					'label' => $a['label'] ?? $key,
					'type'  => $types[ $a['type'] ] ?? \Elementor\Controls_Manager::TEXT,
				);
				if ( 'select' === $a['type'] ) {
					$args['options'] = $a['options'];
				} elseif ( 'color' === $a['type'] ) {
					$args['options'] = array( '' => __( 'Default', 'hi-wpessential' ) ) + Kit::palette();
				} elseif ( 'range' === $a['type'] ) {
					$args['size_units'] = array( $a['unit'] ?? 'px' );
					$args['range']      = array(
						$a['unit'] ?? 'px' => array(
							'min'  => $a['min'] ?? 0,
							'max'  => $a['max'] ?? 200,
							'step' => $a['step'] ?? 1,
						),
					);
				} elseif ( 'textarea' === $a['type'] ) {
					$args['rows'] = 3;
				}
				if ( isset( $a['default'] ) && '' !== $a['default'] ) {
					$args['default'] = 'toggle' === $a['type'] ? ( $a['default'] ? 'yes' : '' ) : ( 'range' === $a['type'] ? array(
						'size' => $a['default'],
						'unit' => $a['unit'] ?? 'px',
					) : $a['default'] );
				}
				if ( ! empty( $a['responsive'] ) ) {
					$this->add_responsive_control( $key, $args );
				} else {
					$this->add_control( $key, $args );
				}
			}
			foreach ( $def['groups'] ?? array() as $key => $group ) {
				if ( ( $group['panel'] ?? 'style' ) === $panel ) {
					Groups::elementor( $this, $key, $group );
				}
			}
			$this->end_controls_section();
		}
	}

	/**
	 * Same output as the block.
	 */
	protected function render() {
		echo Kit::render( static::KIT, Kit::from_elementor( $this->def(), $this->get_settings_for_display() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in render.php.
	}
}
