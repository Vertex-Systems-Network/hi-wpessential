<?php
/**
 * WooCommerce API Manager.
 *
 * Config: endpoint = store URL, product_id = API Manager product ID.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core\License;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce adapter.
 */
class Woo extends Adapter {

	/**
	 * Stable instance id for this site.
	 *
	 * @return string
	 */
	private function instance_id() {
		$id = get_option( 'wpessential_core_instance' );
		if ( ! $id ) {
			$id = wp_generate_password( 20, false );
			update_option( 'wpessential_core_instance', $id, false );
		}
		return $id;
	}

	/**
	 * Call the API Manager.
	 *
	 * @param string $action Action.
	 * @param string $key    Key.
	 * @param string $domain Domain.
	 * @param array  $extra  Extra args.
	 * @return array|\WP_Error
	 */
	private function call( $action, $key, $domain, array $extra = array() ) {
		$body = $this->request(
			'GET',
			add_query_arg( 'wc-api', 'wc-am-api', trailingslashit( $this->config['endpoint'] ) ),
			array(
				'wc_am_action' => $action,
				'api_key'      => $key,
				'product_id'   => $this->config['product_id'],
				'instance'     => $this->instance_id(),
				'object'       => $domain,
			) + $extra
		);
		if ( is_wp_error( $body ) ) {
			return $body;
		}
		if ( empty( $body['success'] ) ) {
			return new \WP_Error( 'wpe_license_invalid', $body['error'] ?? __( 'This license key is not valid.', 'wpessential' ) );
		}
		return $body;
	}

	/**
	 * Activate the key for a domain.
	 *
	 * @param string $key    License key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	public function activate( $key, $domain ) {
		$body = $this->call( 'activate', $key, $domain );
		return is_wp_error( $body ) ? $body : $this->result( 'active', '', '', $body['message'] ?? '' );
	}

	/**
	 * Release the domain.
	 *
	 * @param string $key    License key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	public function deactivate( $key, $domain ) {
		$body = $this->call( 'deactivate', $key, $domain );
		return is_wp_error( $body ) ? $body : $this->result( 'inactive' );
	}

	/**
	 * Check the key.
	 *
	 * @param string $key    License key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	public function check( $key, $domain ) {
		$body = $this->call( 'status', $key, $domain );
		if ( is_wp_error( $body ) ) {
			return $body;
		}
		$active = ! empty( $body['status_check'] ) && 'active' === $body['status_check'];
		return $this->result( $active ? 'active' : 'expired' );
	}

	/**
	 * Signed download URL.
	 *
	 * @param string $key     License key.
	 * @param string $domain  Domain.
	 * @param string $item    Demo id or plugin slug.
	 * @param string $context Builder key or "plugin".
	 * @return string|\WP_Error
	 */
	public function package_url( $key, $domain, $item, $context ) {
		$body = $this->call( 'update', $key, $domain, array( 'plugin_name' => $item ) );
		if ( is_wp_error( $body ) ) {
			return $body;
		}
		$url = $body['data']['package']['package'] ?? '';
		return $url ? esc_url_raw( $url ) : new \WP_Error( 'wpe_package', __( 'The store did not return a download link for this license.', 'wpessential' ) );
	}
}
