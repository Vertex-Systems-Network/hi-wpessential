<?php
/**
 * WPEssential license API.
 *
 * POST {endpoint}/activate|deactivate|check with license, domain, product.
 * Response: { "success": bool, "status": "active|expired|invalid", "expires": "Y-m-d|lifetime", "plan": "...", "message": "..." }.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core\License;

defined( 'ABSPATH' ) || exit;

/**
 * Custom API adapter.
 */
class Custom extends Adapter {

	/**
	 * Call one action.
	 *
	 * @param string $action Action.
	 * @param string $key    Key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	private function call( $action, $key, $domain ) {
		$body = $this->request(
			'POST',
			trailingslashit( $this->config['endpoint'] ) . $action,
			array(
				'license' => $key,
				'domain'  => $domain,
				'product' => $this->config['product_id'],
			)
		);
		if ( is_wp_error( $body ) ) {
			return $body;
		}
		if ( empty( $body['success'] ) ) {
			return new \WP_Error( 'wpe_license_' . ( $body['status'] ?? 'invalid' ), $body['message'] ?? __( 'This license key is not valid.', 'hi-wpessential' ) );
		}
		return $this->result( $body['status'] ?? 'active', $body['expires'] ?? '', $body['plan'] ?? '', $body['message'] ?? '' ) + array(
			'pro'         => ! empty( $body['pro'] ),
			'white_label' => ! empty( $body['white_label'] ),
			'sites_used'  => (int) ( $body['sites_used'] ?? 0 ),
			'sites_limit' => (int) ( $body['sites_limit'] ?? 0 ),
		);
	}

	/**
	 * Activate the key for a domain.
	 *
	 * @param string $key    License key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	public function activate( $key, $domain ) {
		return $this->call( 'activate', $key, $domain );
	}

	/**
	 * Release the domain.
	 *
	 * @param string $key    License key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	public function deactivate( $key, $domain ) {
		return $this->call( 'deactivate', $key, $domain );
	}

	/**
	 * Check the key.
	 *
	 * @param string $key    License key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	public function check( $key, $domain ) {
		return $this->call( 'check', $key, $domain );
	}
}
