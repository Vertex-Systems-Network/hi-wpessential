<?php
/**
 * Admin: licenses, settings.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Admin screens.
 */
class Admin {

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_wpes_license_save', array( __CLASS__, 'save_license' ) );
		add_action( 'admin_post_wpes_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_wpes_run_cron', array( __CLASS__, 'run_cron' ) );
	}

	/**
	 * Menu.
	 */
	public static function menu() {
		add_menu_page( __( 'WPEssential Server', 'wpessential-server' ), __( 'WPE Server', 'wpessential-server' ), 'manage_woocommerce', 'wpes-server', array( __CLASS__, 'licenses' ), 'dashicons-admin-network', 56 );
		add_submenu_page( 'wpes-server', __( 'Licenses', 'wpessential-server' ), __( 'Licenses', 'wpessential-server' ), 'manage_woocommerce', 'wpes-server', array( __CLASS__, 'licenses' ) );
		add_submenu_page( 'wpes-server', __( 'Settings', 'wpessential-server' ), __( 'Settings', 'wpessential-server' ), 'manage_options', 'wpes-settings', array( __CLASS__, 'settings' ) );
	}

	/**
	 * Licenses list / edit.
	 */
	public static function licenses() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only filters.
		if ( ! empty( $_GET['license'] ) ) {
			self::edit( absint( $_GET['license'] ) );
			return;
		}
		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$page   = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		// phpcs:enable
		$result = Licenses::query(
			array(
				'status'   => $status,
				'search'   => $search,
				'page'     => $page,
				'per_page' => 25,
			)
		);
		echo '<div class="wrap"><h1 class="wp-heading-inline">' . esc_html__( 'Licenses', 'wpessential-server' ) . '</h1>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">' . wp_nonce_field( 'wpes_run_cron', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="wpes_run_cron"><button class="page-title-action">' . esc_html__( 'Run renewals now', 'wpessential-server' ) . '</button></form>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce markup.
		echo '<hr class="wp-header-end">';
		echo '<form method="get"><input type="hidden" name="page" value="wpes-server"><p class="search-box"><select name="status"><option value="">' . esc_html__( 'All statuses', 'wpessential-server' ) . '</option>';
		foreach ( array( 'active', 'expired', 'cancelled', 'revoked' ) as $s ) {
			echo '<option value="' . esc_attr( $s ) . '"' . selected( $status, $s, false ) . '>' . esc_html( ucfirst( $s ) ) . '</option>';
		}
		echo '</select> <input type="search" name="s" value="' . esc_attr( $search ) . '" placeholder="' . esc_attr__( 'Key, email or domain', 'wpessential-server' ) . '"> <button class="button">' . esc_html__( 'Filter', 'wpessential-server' ) . '</button></p></form>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Key', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Customer', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Plan', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Status', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Websites', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Expires', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Auto-renew', 'wpessential-server' ) . '</th></tr></thead><tbody>';
		if ( ! $result['rows'] ) {
			echo '<tr><td colspan="7">' . esc_html__( 'No licenses found.', 'wpessential-server' ) . '</td></tr>';
		}
		foreach ( $result['rows'] as $row ) {
			$url = add_query_arg(
				array(
					'page'    => 'wpes-server',
					'license' => $row['id'],
				),
				admin_url( 'admin.php' )
			);
			printf(
				'<tr><td><a href="%1$s"><code>%2$s</code></a></td><td>%3$s</td><td>%4$s</td><td>%5$s</td><td>%6$s</td><td>%7$s</td><td>%8$s</td></tr>',
				esc_url( $url ),
				esc_html( $row['license_key'] ),
				esc_html( (string) $row['user_email'] ),
				esc_html( ucwords( str_replace( '_', ' ', $row['plan'] ) ) ),
				esc_html( ucfirst( $row['status'] ) ),
				esc_html( Licenses::used_sites( $row['id'] ) . ' / ' . ( $row['sites_limit'] ? $row['sites_limit'] : '∞' ) ),
				esc_html( $row['expires_at'] ? substr( $row['expires_at'], 0, 10 ) : __( 'Never', 'wpessential-server' ) ),
				esc_html( $row['auto_renew'] ? __( 'On', 'wpessential-server' ) : __( 'Off', 'wpessential-server' ) )
			);
		}
		echo '</tbody></table>';
		$pages = (int) ceil( $result['total'] / 25 );
		if ( $pages > 1 ) {
			echo '<p>' . wp_kses_post(
				paginate_links(
					array(
						'base'    => add_query_arg( 'paged', '%#%' ),
						'current' => $page,
						'total'   => $pages,
					)
				)
			) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Edit one license.
	 *
	 * @param int $id License.
	 */
	private static function edit( $id ) {
		$license = Licenses::get( $id );
		if ( ! $license ) {
			echo '<div class="wrap"><p>' . esc_html__( 'License not found.', 'wpessential-server' ) . '</p></div>';
			return;
		}
		$user = get_userdata( (int) $license['user_id'] );
		echo '<div class="wrap"><h1><code>' . esc_html( $license['license_key'] ) . '</code></h1>';
		echo '<p>' . esc_html( $user ? $user->display_name . ' · ' . $user->user_email : '' ) . ( $license['order_id'] ? ' · <a href="' . esc_url( admin_url( 'post.php?post=' . (int) $license['order_id'] . '&action=edit' ) ) . '">' . esc_html( sprintf( /* translators: %d: order. */ __( 'Order #%d', 'wpessential-server' ), $license['order_id'] ) ) . '</a>' : '' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . wp_nonce_field( 'wpes_license_save', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="wpes_license_save"><input type="hidden" name="id" value="' . (int) $id . '"><table class="form-table">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce markup.
		echo '<tr><th>' . esc_html__( 'Status', 'wpessential-server' ) . '</th><td><select name="status">';
		foreach ( array( 'active', 'expired', 'cancelled', 'revoked' ) as $s ) {
			echo '<option value="' . esc_attr( $s ) . '"' . selected( $license['status'], $s, false ) . '>' . esc_html( ucfirst( $s ) ) . '</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>' . esc_html__( 'Expires (UTC)', 'wpessential-server' ) . '</th><td><input type="date" name="expires" value="' . esc_attr( $license['expires_at'] ? substr( $license['expires_at'], 0, 10 ) : '' ) . '"> <span class="description">' . esc_html__( 'Empty = never.', 'wpessential-server' ) . '</span></td></tr>';
		echo '<tr><th>' . esc_html__( 'Websites', 'wpessential-server' ) . '</th><td><input type="number" min="0" name="sites" value="' . (int) $license['sites_limit'] . '"> <span class="description">' . esc_html__( '0 = unlimited.', 'wpessential-server' ) . '</span></td></tr>';
		echo '<tr><th>' . esc_html__( 'Auto-renew', 'wpessential-server' ) . '</th><td><label><input type="checkbox" name="auto_renew" value="1"' . checked( $license['auto_renew'], 1, false ) . '> ' . esc_html__( 'On', 'wpessential-server' ) . '</label></td></tr>';
		echo '<tr><th>' . esc_html__( 'White label', 'wpessential-server' ) . '</th><td><label><input type="checkbox" name="white_label" value="1"' . checked( $license['white_label'], 1, false ) . '> ' . esc_html__( 'On', 'wpessential-server' ) . '</label></td></tr>';
		echo '<tr><th>' . esc_html__( 'Activated websites', 'wpessential-server' ) . '</th><td>';
		$activations = Licenses::activations( $id );
		if ( ! $activations ) {
			esc_html_e( 'None yet.', 'wpessential-server' );
		}
		foreach ( $activations as $a ) {
			echo '<label style="display:block"><input type="checkbox" name="remove[]" value="' . esc_attr( $a['domain'] ) . '"> ' . esc_html__( 'Remove', 'wpessential-server' ) . ' <strong>' . esc_html( $a['domain'] ) . '</strong>' . ( $a['is_staging'] ? ' (' . esc_html__( 'staging', 'wpessential-server' ) . ')' : '' ) . ' · ' . esc_html( sprintf( /* translators: %s: date. */ __( 'last seen %s', 'wpessential-server' ), $a['last_check'] ) ) . '</label>';
		}
		echo '</td></tr></table>';
		submit_button();
		echo '</form></div>';
	}

	/**
	 * Save license edits.
	 */
	public static function save_license() {
		check_admin_referer( 'wpes_license_save' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ) );
		}
		$id      = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$status  = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'active';
		$expires = isset( $_POST['expires'] ) ? sanitize_text_field( wp_unslash( $_POST['expires'] ) ) : '';
		Licenses::update(
			$id,
			array(
				'status'      => in_array( $status, array( 'active', 'expired', 'cancelled', 'revoked' ), true ) ? $status : 'active',
				'expires_at'  => $expires ? gmdate( 'Y-m-d 23:59:59', strtotime( $expires ) ) : null,
				'sites_limit' => isset( $_POST['sites'] ) ? max( 0, (int) $_POST['sites'] ) : 1,
				'auto_renew'  => empty( $_POST['auto_renew'] ) ? 0 : 1,
				'white_label' => empty( $_POST['white_label'] ) ? 0 : 1,
			)
		);
		foreach ( isset( $_POST['remove'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['remove'] ) ) : array() as $domain ) {
			Licenses::deactivate( $id, $domain );
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'wpes-server', 'license' => $id, 'updated' => 1 ), admin_url( 'admin.php' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound -- Short redirect args.
		exit;
	}

	/**
	 * Settings screen.
	 */
	public static function settings() {
		$s = Plugin::settings();
		echo '<div class="wrap"><h1>' . esc_html__( 'WPEssential Server settings', 'wpessential-server' ) . '</h1>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . wp_nonce_field( 'wpes_settings', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="wpes_settings"><table class="form-table">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce markup.
		$rows = array(
			'grace_days'    => array( __( 'Grace period (days)', 'wpessential-server' ), __( 'Licenses keep working this long after the end date while a renewal is paid.', 'wpessential-server' ) ),
			'remind_days'   => array( __( 'Renew / remind before (days)', 'wpessential-server' ), __( 'Renewal orders are created (or reminders sent) this many days before the end date.', 'wpessential-server' ) ),
			'token_days'    => array( __( 'Dashboard sign-in lasts (days)', 'wpessential-server' ), '' ),
			'download_mins' => array( __( 'Download links last (minutes)', 'wpessential-server' ), '' ),
		);
		foreach ( $rows as $key => $row ) {
			echo '<tr><th>' . esc_html( $row[0] ) . '</th><td><input type="number" min="1" name="' . esc_attr( $key ) . '" value="' . (int) $s[ $key ] . '">' . ( $row[1] ? '<p class="description">' . esc_html( $row[1] ) . '</p>' : '' ) . '</td></tr>';
		}
		echo '<tr><th>' . esc_html__( 'Free staging sites', 'wpessential-server' ) . '</th><td><label><input type="checkbox" name="free_staging" value="1"' . checked( $s['free_staging'], 1, false ) . '> ' . esc_html__( 'Do not count localhost, *.local, *.test and staging.* domains against the website limit.', 'wpessential-server' ) . '</label></td></tr>';
		echo '<tr><th>' . esc_html__( 'Theme endpoint', 'wpessential-server' ) . '</th><td><code>' . esc_html( rest_url( Api::NS . '/license' ) ) . '</code><p class="description">' . esc_html__( 'Use this as the license endpoint and the demos manifest URL below in the theme.', 'wpessential-server' ) . '</p><code>' . esc_html( rest_url( Api::NS . '/demos' ) ) . '</code></td></tr>';
		echo '</table>';
		submit_button();
		echo '</form></div>';
	}

	/**
	 * Save settings.
	 */
	public static function save_settings() {
		check_admin_referer( 'wpes_settings' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ) );
		}
		$out = array();
		foreach ( array( 'grace_days', 'remind_days', 'token_days', 'download_mins' ) as $key ) {
			$out[ $key ] = isset( $_POST[ $key ] ) ? max( 1, (int) $_POST[ $key ] ) : Plugin::settings( $key );
		}
		$out['free_staging'] = empty( $_POST['free_staging'] ) ? 0 : 1;
		update_option( 'wpes_server_settings', $out );
		wp_safe_redirect( admin_url( 'admin.php?page=wpes-settings&updated=1' ) );
		exit;
	}

	/**
	 * Manual cron run.
	 */
	public static function run_cron() {
		check_admin_referer( 'wpes_run_cron' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ) );
		}
		Renewals::run();
		wp_safe_redirect( admin_url( 'admin.php?page=wpes-server' ) );
		exit;
	}
}
