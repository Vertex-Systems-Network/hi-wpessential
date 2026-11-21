<?php
/**
 * WPEssential controls for the Site Settings in the Customizer.
 *
 * One control class draws every field type (colour swatches, slider with reset, switch,
 * button group, font picker with Google Fonts, font list). The markup is filled in by
 * assets/js/customizer-controls.js, which writes the value to a hidden input linked to the
 * setting, so the Customizer saves and previews it as usual.
 *
 * Loaded from customize_register, after WP_Customize_Control exists.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEssential_Customize_Control' ) ) {
	/**
	 * Site Settings control.
	 */
	class WPEssential_Customize_Control extends WP_Customize_Control {

		/**
		 * Control type.
		 *
		 * @var string
		 */
		public $type = 'wpessential';

		/**
		 * Field definition from wpessential_site_fields().
		 *
		 * @var array
		 */
		public $field = array();

		/**
		 * Markup; the script builds the inside.
		 */
		public function render_content() {
			$f    = $this->field;
			$kind = $f['type'];
			if ( 'select' === $kind && count( $f['choices'] ) <= 3 && max( array_map( 'strlen', $f['choices'] ) ) <= 22 ) {
				$kind = 'buttons';
			}
			$data = array(
				'type'       => $kind,
				'min'        => $f['min'] ?? null,
				'max'        => $f['max'] ?? null,
				'step'       => $f['step'] ?? null,
				'unit'       => $f['unit'] ?? null,
				'choices'    => $f['choices'] ?? null,
				'responsive' => ! empty( $f['responsive'] ) ? true : null,
				'units'      => $f['units'] ?? null,
				'tab'        => $f['tab'] ?? null,
			);
			if ( 'heading' === $kind ) {
				echo '<h3 class="wpe-c-heading">' . esc_html( $this->label ) . '</h3>';
				return;
			}
			?>
			<div class="wpe-c wpe-c--<?php echo esc_attr( $kind ); ?>" data-wpec="<?php echo esc_attr( wp_json_encode( array_filter( $data, static fn( $x ) => null !== $x ) ) ); ?>">
				<div class="wpe-c__head">
					<span class="wpe-c__label"><?php echo esc_html( $this->label ); ?></span>
					<button type="button" class="wpe-c__reset" hidden aria-label="<?php echo esc_attr( sprintf( /* translators: %s: option name. */ __( 'Reset %s to the theme default', 'hi-wpessential' ), $this->label ) ); ?>" title="<?php esc_attr_e( 'Back to the theme default', 'hi-wpessential' ); ?>">
						<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
					</button>
				</div>
				<?php if ( ! empty( $f['help'] ) ) : ?>
					<p class="wpe-c__help"><?php echo esc_html( $f['help'] ); ?></p>
				<?php endif; ?>
				<div class="wpe-c__ui"></div>
				<input type="hidden" class="wpe-c__value" <?php $this->link(); ?> value="<?php echo esc_attr( is_scalar( $this->value() ) ? (string) $this->value() : '' ); ?>">
			</div>
			<?php
		}
	}
}
