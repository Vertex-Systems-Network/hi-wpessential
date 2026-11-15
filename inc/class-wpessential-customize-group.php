<?php
/**
 * A heading row in the Customizer's top-level list ("Colours", "Typography" …). It holds no
 * controls; the sections that follow it (by priority) belong to its group.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

/**
 * Group heading.
 */
class WPEssential_Customize_Group extends WP_Customize_Section {

	/**
	 * Section type (for the JS template).
	 *
	 * @var string
	 */
	public $type = 'wpessential_group';

	/**
	 * A plain heading instead of a section that opens.
	 */
	protected function render_template() {
		?>
		<li id="accordion-section-{{ data.id }}" class="accordion-section control-section wpessential-group" aria-hidden="false">
			<h3 class="wpessential-group__title">{{ data.title }}</h3>
		</li>
		<?php
	}
}
