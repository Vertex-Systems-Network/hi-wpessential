<?php
/**
 * One-time rename of old theme class names (ess-, px-, nw-, mx-, bx-) to their wpe- versions in
 * content that was imported before the rename, so existing sites keep their look.
 *
 * @package hi-wpessential
 */

defined( 'ABSPATH' ) || exit;

/**
 * Old class token → new one; leaves cursor values like nw-resize alone.
 *
 * @param string $text Post content or Elementor JSON.
 * @return string
 */
function wpessential_prefix_classes( $text ) {
	return (string) preg_replace( '/(?<![A-Za-z0-9_-])(ess|px|nw|mx|bx)-(?=[a-z])(?!resize)/', 'wpe-$1-', $text );
}

/**
 * Runs once per site (option flag), on the first admin or front request after the update.
 *
 * @return void
 */
function wpessential_migrate_class_prefix() {
	if ( get_option( 'wpessential_class_prefix_v1' ) ) {
		return;
	}
	global $wpdb;
	// Only rows that can hold an old class; LIKE is a cheap prefilter, the regex decides.
	$like  = "(%s LIKE '%%ess-%%' OR %s LIKE '%%px-%%' OR %s LIKE '%%nw-%%' OR %s LIKE '%%mx-%%' OR %s LIKE '%%bx-%%')";
	$posts = $wpdb->get_results( "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_status <> 'auto-draft' AND " . str_replace( '%s', 'post_content', $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared -- static SQL, one-time migration.
	foreach ( $posts as $row ) {
		$new = wpessential_prefix_classes( $row->post_content );
		if ( $new !== $row->post_content ) {
			$wpdb->update( $wpdb->posts, array( 'post_content' => $new ), array( 'ID' => $row->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( (int) $row->ID );
		}
	}
	$metas = $wpdb->get_results( "SELECT meta_id, post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND " . str_replace( '%s', 'meta_value', $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared -- static SQL, one-time migration.
	foreach ( $metas as $row ) {
		$new = wpessential_prefix_classes( $row->meta_value );
		if ( $new !== $row->meta_value ) {
			$wpdb->update( $wpdb->postmeta, array( 'meta_value' => $new ), array( 'meta_id' => $row->meta_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.SlowDBQuery.slow_query_meta_value
			wp_cache_delete( (int) $row->post_id, 'post_meta' );
			delete_post_meta( (int) $row->post_id, '_elementor_css' );
		}
	}
	update_option( 'wpessential_class_prefix_v1', 1, false );
}
add_action( 'init', 'wpessential_migrate_class_prefix', 20 );
