<?php
/**
 * Five-element batch: WordPress, Gutenberg and Elementor smoke tests.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $ok, $description ) {
	if ( ! $ok ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo 'PASS: ' . $description . "\n";
};
$wpe_block = static function ( $name, array $attributes = array() ) {
	return do_blocks( '<!-- wp:wpessential/' . $name . ' ' . wp_json_encode( (object) $attributes ) . ' /-->' );
};
$wpe_elements = array( 'countdown', 'svg', 'team-member', 'site-logo', 'divider' );
foreach ( $wpe_elements as $wpe_name ) {
	$wpe_check( isset( Kit::defs()[ $wpe_name ] ), $wpe_name . ': Kit definition loaded' );
	$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/' . $wpe_name ), $wpe_name . ': Gutenberg registered' );
	$wpe_check( wp_style_is( 'wpessential-kit-' . $wpe_name, 'registered' ), $wpe_name . ': Styles registered' );
	$wpe_widget = class_exists( '\Elementor\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-' . $wpe_name ) : null;
	$wpe_check( is_object( $wpe_widget ) && 'wpe-' . $wpe_name === $wpe_widget->get_name(), $wpe_name . ': Elementor widget registered' );
}

// Countdown: timezone-correct, no-script fallback, expired state.
$wpe_old_timezone = get_option( 'timezone_string' );
try {
	update_option( 'timezone_string', 'Asia/Karachi' );
	$wpe_count = $wpe_block( 'countdown', array(
		'target' => '2030-01-01 00:00:00',
		'heading' => '<script>Unsafe</script>Sale begins',
		'expiredText' => '<img src=x onerror=alert(1)>',
	) );
	$wpe_expected_epoch = ( new DateTimeImmutable( '2030-01-01 00:00:00', new DateTimeZone( 'Asia/Karachi' ) ) )->getTimestamp() * 1000;
	$wpe_check( false !== strpos( $wpe_count, 'data-end="' . $wpe_expected_epoch . '"' ), 'Countdown: target uses site timezone (not visitor timezone)' );
	$wpe_check( false !== strpos( $wpe_count, '<time datetime="2030-01-01T00:00:00+05:00"' ), 'Countdown: target date visible without JS' );
	$wpe_check( 4 === preg_match_all( '/data-unit=/', $wpe_count ), 'Countdown: all four time units render' );
	$wpe_check( false === strpos( $wpe_count, '<script>' ) && false === strpos( $wpe_count, '<img src=x' ), 'Countdown: user content escaped' );
	$wpe_check( false !== strpos( $wpe_count, 'role="status"' ), 'Countdown: completion announcement accessible' );
	$wpe_invalid = $wpe_block( 'countdown', array( 'target' => '2027-02-30 00:00:00' ) );
	$wpe_check( false === strpos( $wpe_invalid, 'data-wpe-countdown' ), 'Countdown: impossible dates rejected' );
	$wpe_invalid_markup = $wpe_block( 'countdown', array( 'target' => '<script>alert(2)</script>' ) );
	$wpe_check( false === strpos( $wpe_invalid_markup, '<section' ), 'Countdown: malformed date produces no frontend widget' );
	$wpe_mapped = Kit::from_elementor( Kit::defs()['countdown'], array(
		'target' => '2030-01-01 00:00:00',
		'showSeconds' => '',
		'gap' => array( 'size' => 26, 'unit' => 'px' ),
	) );
	$wpe_check( false === $wpe_mapped['showSeconds'], 'Countdown: Elementor seconds switcher maps correctly' );
	$wpe_check( 26 === $wpe_mapped['gap']['base'], 'Countdown: Elementor responsive gap maps correctly' );
	$wpe_html = Kit::render( 'countdown', $wpe_mapped );
	echo 'DEBUG countdown rendered unit count: ' . preg_match_all( '/data-unit=/', $wpe_html ) . '; has-seconds=' . ( false !== strpos( $wpe_html, 'data-unit="seconds"' ) ? 'yes' : 'no' ) . '; markup=' . substr( $wpe_html, 0, 1800 ) . "\n";
$wpe_check( 3 === preg_match_all( '/data-unit=/', $wpe_html ), 'Countdown: disabled seconds absent from Elementor render' );
} finally {
	update_option( 'timezone_string', $wpe_old_timezone );
}

// SVG: safe built-in drawing paths, decorative semantics, and animation configuration.
$wpe_svg = $wpe_block( 'svg', array( 'shape' => 'heart', 'animation' => 'draw', 'title' => '" onclick="alert(1)', 'caption' => '<svg onload=alert(2)>' ) );
$wpe_check( false !== strpos( $wpe_svg, '<svg ' ) && false !== strpos( $wpe_svg, 'pathLength="100"' ), 'SVG: bundled path renders' );
$wpe_check( false !== strpos( $wpe_svg, 'class="wpe-svg is-draw"' ), 'SVG: motion mode renders' );
$wpe_check( false === strpos( $wpe_svg, ' onclick="' ) && false === strpos( $wpe_svg, '<svg onload=' ), 'SVG: labels and captions escaped' );
$wpe_check( false !== strpos( $wpe_svg, 'role="img"' ), 'SVG: meaningful icon has image role' );
$wpe_check( false !== strpos( $wpe_svg, 'data-wpe-svg="1"' ), 'SVG: JS enhancement hook present' );
$wpe_static = $wpe_block( 'svg', array( 'shape' => '<script>', 'decorative' => true, 'animation' => 'none' ) );
$wpe_check( false !== strpos( $wpe_static, 'aria-hidden="true"' ), 'SVG: decorative icon hidden from AT' );
$wpe_check( false === strpos( $wpe_static, '<script>' ), 'SVG: malicious shape never interpolated into path' );
$wpe_check( wp_script_is( 'wpessential-kit-svg', 'registered' ), 'SVG: viewport JS registered' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['svg'], array( 'shape' => 'star', 'size' => array( 'size' => 96, 'unit' => 'px' ), 'repeat' => 'yes' ) );
$wpe_check( true === $wpe_mapped['repeat'], 'SVG: Elementor repeat switcher maps to boolean' );
$wpe_check( false !== strpos( Kit::render( 'svg', $wpe_mapped ), '--wpek-svg-size:96px' ), 'SVG: responsive width CSS generated' );

// Team member: names/URLs escaped, invalid links discarded, optional bio hidden.
$wpe_team = $wpe_block( 'team-member', array(
	'name' => '<img src=x onerror=alert(3)>Jane',
	'role' => 'Engineer',
	'email' => 'jane@example.com',
	'profileUrl' => 'https://example.org/jane',
	'newTab' => true,
	'socials' => array(
		array( 'label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/' ),
		array( 'label' => 'Unsafe', 'url' => 'javascript:alert(3)' ),
	),
) );
$wpe_check( false === strpos( $wpe_team, '<img src=x' ), 'Team: name markup escaped' );
$wpe_check( false !== strpos( $wpe_team, 'jane@example.com' ), 'Team: safe email shown' );
$wpe_check( false !== strpos( $wpe_team, 'href="https://example.org/jane"' ), 'Team: profile link preserved' );
$wpe_check( false !== strpos( $wpe_team, 'rel="noopener noreferrer"' ), 'Team: external new-tab links protected' );
$wpe_check( false === strpos( $wpe_team, 'javascript:' ), 'Team: dangerous social links rejected' );
$wpe_check( false !== strpos( $wpe_team, '>LinkedIn</a>' ), 'Team: allowed socials render' );
$wpe_no_bio = $wpe_block( 'team-member', array( 'showBio' => false ) );
$wpe_check( false === strpos( $wpe_no_bio, 'wpe-team-member__bio' ), 'Team: biography toggle hides bio' );

$wpe_file = wp_upload_bits( 'wpe-kit-batch-smoke.png', null, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jMwAAAABJRU5ErkJggg==' ) );
$wpe_check( empty( $wpe_file['error'] ), 'Fixtures: disposable PNG uploaded' );
$wpe_attachment = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => 'Kit smoke image', 'post_status' => 'inherit' ), $wpe_file['file'] );
$wpe_check( $wpe_attachment && ! is_wp_error( $wpe_attachment ), 'Fixtures: image attachment created' );
$wpe_old_logo = get_theme_mod( 'custom_logo' );
$wpe_old_site_name = get_option( 'blogname' );
$wpe_old_tagline = get_option( 'blogdescription' );
try {
	$wpe_with_photo = $wpe_block( 'team-member', array( 'photo' => array( 'id' => $wpe_attachment ), 'name' => 'Taylor' ) );
	$wpe_check( false !== strpos( $wpe_with_photo, 'alt="Taylor"' ), 'Team: attachment image alt uses member name' );
	$wpe_check( false !== strpos( $wpe_with_photo, 'wpe-kit-batch-smoke.png' ), 'Team: WordPress attachment URL rendered' );
	$wpe_mapped = Kit::from_elementor( Kit::defs()['team-member'], array(
		'name' => 'Elementor colleague', 'socials' => array( array( 'label' => 'Portfolio', 'url' => 'https://example.com/' ) ), 'photoWidth' => array( 'size' => 240, 'unit' => 'px' ),
	) );
	$wpe_check( false !== strpos( Kit::render( 'team-member', $wpe_mapped ), 'Elementor colleague' ), 'Team: Elementor uses shared renderer' );

	// Dynamic site identity and overridden logo.
	update_option( 'blogname', 'Smoke Brand' );
	update_option( 'blogdescription', 'Example tagline' );
	set_theme_mod( 'custom_logo', $wpe_attachment );
	$wpe_logo = $wpe_block( 'site-logo', array( 'showTagline' => true ) );
	$wpe_check( false !== strpos( $wpe_logo, 'Smoke Brand' ), 'Site Logo: dynamic site title rendered' );
	$wpe_check( false !== strpos( $wpe_logo, 'Example tagline' ), 'Site Logo: dynamic tagline rendered' );
	$wpe_check( false !== strpos( $wpe_logo, 'wpe-kit-batch-smoke.png' ), 'Site Logo: dynamic theme attachment rendered' );
	$wpe_title = $wpe_block( 'site-logo', array( 'display' => 'title', 'siteTitle' => 'Personal brand', 'url' => 'javascript:alert(4)' ) );
	$wpe_check( false !== strpos( $wpe_title, 'Personal brand' ), 'Site Logo: override site title rendered' );
	$wpe_check( false === strpos( $wpe_title, 'javascript:' ), 'Site Logo: dangerous home override discarded' );
	$wpe_check( false === strpos( $wpe_title, 'wpe-site-logo__image' ), 'Site Logo: title-only option hides logo' );
	$wpe_mapped = Kit::from_elementor( Kit::defs()['site-logo'], array( 'showTagline' => 'yes', 'logoWidth' => array( 'size' => 140, 'unit' => 'px' ) ) );
	$wpe_check( true === $wpe_mapped['showTagline'], 'Site Logo: Elementor tagline switcher maps correctly' );
	$wpe_check( false !== strpos( Kit::render( 'site-logo', $wpe_mapped ), '--wpek-brand-width:140px' ), 'Site Logo: responsive width CSS emitted' );
} finally {
	set_theme_mod( 'custom_logo', $wpe_old_logo );
	update_option( 'blogname', $wpe_old_site_name );
	update_option( 'blogdescription', $wpe_old_tagline );
	wp_delete_attachment( $wpe_attachment, true );
}

// Divider: semantic separator, labelled rule, decorative symbol and spacer.
$wpe_line = $wpe_block( 'divider', array() );
$wpe_check( false !== strpos( $wpe_line, '<hr ' ), 'Divider: horizontal rule uses semantic hr' );
$wpe_text = $wpe_block( 'divider', array( 'mode' => 'text', 'text' => '<script>X</script>Section label', 'lineStyle' => 'dashed' ) );
$wpe_check( false !== strpos( $wpe_text, 'role="separator"' ), 'Divider: labelled rule has separator role' );
$wpe_check( false !== strpos( $wpe_text, 'is-dashed' ), 'Divider: dashed style supported' );
$wpe_check( false === strpos( $wpe_text, '<script>' ), 'Divider: label HTML escaped' );
$wpe_symbol = $wpe_block( 'divider', array( 'mode' => 'symbol', 'symbol' => 'diamond' ) );
$wpe_check( false !== strpos( $wpe_symbol, '◆' ), 'Divider: safe icon preset renders' );
$wpe_space = $wpe_block( 'divider', array( 'mode' => 'space', 'height' => array( 'base' => 80 ) ) );
$wpe_check( false !== strpos( $wpe_space, 'aria-hidden="true"' ), 'Divider: spacer hidden from assistive tech' );
$wpe_check( false === strpos( $wpe_space, 'role="separator"' ), 'Divider: spacer not announced as content' );
$wpe_check( false !== strpos( $wpe_space, '--wpek-spacer-height:80px' ), 'Divider: responsive spacer height emitted' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['divider'], array( 'mode' => 'space', 'height' => array( 'size' => 65, 'unit' => 'px' ) ) );
$wpe_check( false !== strpos( Kit::render( 'divider', $wpe_mapped ), '--wpek-spacer-height:65px' ), 'Divider: Elementor height maps to shared renderer' );

echo "Five Kit elements WordPress + Elementor smoke tests passed.\n";
