<?php
/**
 * Table integration smoke tests — disposable WordPress with Elementor.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $condition, $description ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo "PASS: " . $description . "\n";
};
$wpe_block = static function ( array $attrs ) {
	return do_blocks( '<!-- wp:wpessential/table ' . wp_json_encode( (object) $attrs ) . ' /-->' );
};

$wpe_check( class_exists( Kit::class ), 'Kit loads' );
$wpe_check( isset( Kit::defs()['table'] ), 'Table definition loads' );
$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/table' ), 'Gutenberg table registered' );
$wpe_check( wp_style_is( 'wpessential-kit-table', 'registered' ), 'Table CSS registered' );
$wpe_check( wp_script_is( 'wpessential-kit-table', 'registered' ), 'Table sorting/search script registered' );

$wpe_default = $wpe_block( array() );
$wpe_check( false !== strpos( $wpe_default, 'Compare our plans' ), 'Default caption renders' );
$wpe_check( 4 === preg_match_all( '/<th scope="col"/', $wpe_default ), 'Four accessible column headings' );
$wpe_check( 3 === preg_match_all( '/<tr data-wpe-row="1">/', $wpe_default ), 'Three default rows render' );
$wpe_check( false !== strpos( $wpe_default, '<button class="wpe-table__sort"' ), 'Sortable heading buttons render' );
$wpe_check( false !== strpos( $wpe_default, 'type="search"' ), 'Search control renders' );
$wpe_check( false !== strpos( $wpe_default, 'role="status"' ), 'Screen-reader search result announcements available' );
$wpe_check( false !== strpos( $wpe_default, 'data-row-singular=' ), 'Search result announcement localized in markup' );
$wpe_check( false !== strpos( $wpe_default, 'tabindex="0"' ), 'Scrollable data region keyboard focusable' );

$wpe_quoted = $wpe_block(
	array(
		'data' => "Item,Detail,Price\n\"Book, Large\",\"Line one\nLine two\",\"$1,299\"\nStandard,Simple,50",
		'caption' => 'Catalogue prices',
		'searchable' => false,
		'sortable' => false,
		'captionPosition' => 'bottom',
		'layout' => 'stack',
		'delimiter' => 'comma',
	)
);
$wpe_check( 2 === preg_match_all( '/<tr data-wpe-row="1">/', $wpe_quoted ), 'Quoted embedded line break remains one CSV row' );
$wpe_check( false !== strpos( $wpe_quoted, 'Book, Large' ), 'Quoted comma preserved as one cell' );
$wpe_check( false !== strpos( $wpe_quoted, 'Line one' ) && false !== strpos( $wpe_quoted, 'Line two' ), 'Multiline CSV field renders' );
$wpe_check( false !== strpos( $wpe_quoted, '$1,299' ), 'Currency strings preserved' );
$wpe_check( false !== strpos( $wpe_quoted, 'data-label="Price"' ), 'Mobile stacked table has per-cell headings' );
$wpe_check( false !== strpos( $wpe_quoted, 'is-stack' ), 'Stacked responsive mode selected' );
$wpe_check( false !== strpos( $wpe_quoted, 'is-bottom' ), 'Caption moved below table' );
$wpe_check( false === strpos( $wpe_quoted, 'wpe-table__search' ), 'Search toggled off' );
$wpe_check( false === strpos( $wpe_quoted, 'wpe-table__sort"' ), 'Sorting toggled off' );

$wpe_tsv = $wpe_block( array( 'delimiter' => 'tab', 'data' => "Colour\tQty\nBlue\t12\nGreen\t9" ) );
$wpe_check( 2 === preg_match_all( '/<th scope="col"/', $wpe_tsv ), 'Tab-separated columns supported' );
$wpe_check( false !== strpos( $wpe_tsv, 'data-label="Qty">12</td>' ), 'Tab-separated values correctly paired with headings' );

$wpe_missing = $wpe_block( array( 'data' => "Name,Points,Rank\nJane,90\nAlex,75,2,Ignored", 'headerRow' => true ) );
$wpe_check( false !== strpos( $wpe_missing, 'data-label="Rank"></td>' ), 'Missing cells render as empty cells' );
$wpe_check( false === strpos( $wpe_missing, '>Ignored</td>' ), 'Surplus cells truncated to header count is not assumed' );

$wpe_no_header = $wpe_block( array( 'data' => "Acme,10\nOther,5", 'headerRow' => false ) );
$wpe_check( false !== strpos( $wpe_no_header, 'Column 1' ) && false !== strpos( $wpe_no_header, 'Column 2' ), 'Accessible generated headers when first row is data' );
$wpe_check( 2 === preg_match_all( '/<tr data-wpe-row="1">/', $wpe_no_header ), 'No-header mode retains first row as data' );
$wpe_check( false === strpos( $wpe_no_header, 'wpe-table__sort"' ), 'No-header mode avoids meaningless sorting controls' );

$wpe_unsafe = $wpe_block( array( 'data' => "Title,Contents\n\"<img src=x onerror=alert(1)>\",\"<script>alert(2)</script> safe\"", 'caption' => 'Title" onclick="alert(3)' ) );
$wpe_check( false === strpos( $wpe_unsafe, '<img src=x' ), 'Cell HTML cannot become executable markup' );
$wpe_check( false === strpos( $wpe_unsafe, '<script>' ), 'Script tags not rendered in cells' );
$wpe_check( false === strpos( $wpe_unsafe, ' onclick="' ), 'Caption cannot inject attributes' );
$wpe_check( false !== strpos( $wpe_unsafe, 'safe' ), 'Safe text remains visible' );

$wpe_empty = $wpe_block( array( 'data' => '' ) );
$wpe_check( false === strpos( $wpe_empty, '<table' ), 'Empty table does not emit broken structure' );

$wpe_widget = class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-table' ) : null;
$wpe_check( is_object( $wpe_widget ) && 'wpe-table' === $wpe_widget->get_name(), 'Elementor Table registered' );
$wpe_check( in_array( 'wpessential-kit-table', $wpe_widget->get_script_depends(), true ), 'Elementor loads Table sorting JS' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['table'], array(
	'data' => "Product,Count\nExample,25",
	'caption' => 'Elementor table',
	'sortable' => 'yes',
	'searchable' => 'yes',
	'cellPadding' => array( 'size' => 16, 'unit' => 'px' ),
	'cellPadding_mobile' => array( 'size' => 10, 'unit' => 'px' ),
) );
$wpe_check( true === $wpe_mapped['sortable'], 'Elementor sorting switcher maps to boolean' );
$wpe_check( 16 === $wpe_mapped['cellPadding']['base'] && 10 === $wpe_mapped['cellPadding']['480'], 'Elementor responsive cell padding maps correctly' );
$wpe_elementor = Kit::render( 'table', $wpe_mapped );
$wpe_check( false !== strpos( $wpe_elementor, 'Elementor table' ) && false !== strpos( $wpe_elementor, 'Example' ), 'Elementor renders through shared Table template' );
$wpe_check( false !== strpos( $wpe_elementor, '--wpek-cell-padding:16px' ), 'Elementor responsive size CSS generated' );

echo "Table Gutenberg and Elementor integration smoke tests passed.\n";
