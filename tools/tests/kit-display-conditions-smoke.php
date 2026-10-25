<?php
/** Conditional display gates security regression. @package WPEssential\Core */
use WPEssential\Core\Kit;
$check = static function ( $pass, $label ) { if ( ! $pass ) { throw new RuntimeException( 'FAIL: ' . $label ); } echo 'PASS: ' . $label . "\n"; };
$render = static function ( array $attrs = array() ) { return Kit::render( 'icon', array_merge( array( 'label' => 'Kit gated marker' ), $attrs ) ); };
foreach ( array( 'conditionLogin','conditionRole','conditionCart','conditionPurchasedProduct','conditionStart','conditionEnd','conditionPath','conditionQueryKey','conditionQueryValue','conditionReferrer','conditionDevice' ) as $key ) {
	$check( isset( Kit::attributes( Kit::defs()['icon'] )[ $key ] ), 'Shared condition control ' . $key );
}
$saved_user = get_current_user_id();
$saved_get = $_GET;
$saved_path = $_SERVER['REQUEST_URI'] ?? null;
$saved_ref = $_SERVER['HTTP_REFERER'] ?? null;
$user_id = 0;
try {
	wp_set_current_user( 0 );
	$check( false !== strpos( $render(), 'Kit gated marker' ), 'Unrestricted output retained' );
	$check( false !== strpos( $render( array( 'conditionLogin' => 'out' ) ), 'Kit gated marker' ), 'Logged-out member rule allows guest' );
	foreach ( array(
		array( 'conditionLogin' => 'in' ),
		array( 'conditionRole' => 'administrator' ),
		array( 'conditionCart' => 'nonempty' ),
		array( 'conditionPurchasedProduct' => '123' ),
		array( 'conditionStart' => '2026-02-31 99:99' ),
		array( 'conditionLogin' => 'invalid' ),
		array( 'conditionRole' => '<script>administrator</script>' ),
		array( 'conditionPurchasedProduct' => '123 OR 1=1' ),
	) as $condition ) {
		$check( '' === $render( $condition ), 'Fail closed and suppress all markup: ' . wp_json_encode( $condition ) );
	}
	$user_id = wp_create_user( 'wpe_gate_' . wp_rand( 10000, 99999 ), wp_generate_password( 30 ), 'gate' . wp_rand( 10000, 99999 ) . '@example.test' );
	$check( ! is_wp_error( $user_id ), 'Temporary customer created' );
	$user = new WP_User( $user_id );
	$user->set_role( 'subscriber' );
	wp_set_current_user( $user_id );
	$check( false !== strpos( $render( array( 'conditionLogin' => 'in', 'conditionRole' => 'subscriber' ) ), 'Kit gated marker' ), 'Login and role gates ANDed' );
	$check( '' === $render( array( 'conditionLogin' => 'out' ) ), 'Member denied guest-only element' );
	$check( '' === $render( array( 'conditionRole' => 'administrator' ) ), 'Member denied wrong role' );
	$now = current_datetime();
	$start = $now->modify( '-15 minutes' )->format( 'Y-m-d H:i' );
	$end = $now->modify( '+15 minutes' )->format( 'Y-m-d H:i' );
	$check( false !== strpos( $render( array( 'conditionStart' => $start, 'conditionEnd' => $end ) ), 'Kit gated marker' ), 'Valid site-time schedule' );
	$check( '' === $render( array( 'conditionStart' => $end ) ), 'Future schedule hidden' );
	$check( '' === $render( array( 'conditionEnd' => $start ) ), 'Expired schedule hidden' );
	$_SERVER['REQUEST_URI'] = '/promos/?campaign=summer';
	$_SERVER['HTTP_REFERER'] = 'https://partners.example.test/catalog';
	$_GET = array( 'campaign' => 'summer' );
	$attrs = array( 'conditionPath' => '/promos/', 'conditionQueryKey' => 'campaign', 'conditionQueryValue' => 'summer', 'conditionReferrer' => 'partners.example.test' );
	$check( false !== strpos( $render( $attrs ), 'Kit gated marker' ), 'URL path/query/referrer ANDed' );
	$check( '' === $render( array_merge( $attrs, array( 'conditionQueryValue' => 'winter' ) ) ), 'Query mismatch rejects render' );
	$check( '' === $render( array( 'conditionQueryValue' => 'summer' ) ), 'Missing query key rejects value-only rule' );
	$check( '' === $render( array( 'conditionPath' => '/elsewhere/' ) ), 'Path mismatch denies' );
	$check( '' === $render( array( 'conditionReferrer' => 'elsewhere.example.test' ) ), 'Exact referrer host required' );
	$_GET['campaign'] = array( 'injection' );
	$check( '' === $render( $attrs ), 'Array query parameters rejected' );
	$device = $render( array( 'conditionDevice' => 'mobile' ) );
	$check( false !== strpos( $device, 'wpek-device-mobile' ) && false !== strpos( $device, 'Kit gated marker' ), 'Device CSS is presentation only, not access control' );
	$mapped = Kit::from_elementor( Kit::defs()['icon'], array( 'conditionLogin' => 'in', 'conditionRole' => 'subscriber', 'conditionDevice' => 'desktop' ) );
	$check( false !== strpos( $render( $mapped ), 'wpek-device-desktop' ), 'Elementor global conditions map correctly' );
} finally {
	wp_set_current_user( $saved_user );
	$_GET = $saved_get;
	if ( null === $saved_path ) { unset( $_SERVER['REQUEST_URI'] ); } else { $_SERVER['REQUEST_URI'] = $saved_path; }
	if ( null === $saved_ref ) { unset( $_SERVER['HTTP_REFERER'] ); } else { $_SERVER['HTTP_REFERER'] = $saved_ref; }
	if ( $user_id && ! is_wp_error( $user_id ) ) { wp_delete_user( $user_id ); }
}
echo "Kit conditional display smoke tests passed.\n";
