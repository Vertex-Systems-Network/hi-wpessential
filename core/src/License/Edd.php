<?php
/**
 * Easy Digital Downloads Software Licensing.
 *
 * Config: endpoint = store URL, product_id = download ID.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core\License;

defined( 'ABSPATH' ) || exit;

/**
 * EDD adapter.
 */
class Edd extends Adapter {

	/**
	 * Messages for EDD error codes.
	 *
	 * @param string $code EDD code.
	 * @return string
	 */
	private function message( $code ) {
		$messages = array(
			'expired'             => __( 'This license has expired. Renew it to keep receiving demos and updates.', 'wpessential' ),
			'disabled'            => __( 'This license has been disabled.', 'wpessential' ),
			'revoked'             => __( 'This license has been revoked.', 'wpessential' ),
			'missing'             => __( 'This license key does not exist.', 'wpessential' ),
			'invalid'             => __( 'This license key is not valid.', 'wpessential' ),
			'site_inactive'       => __( 'This license is not active for this domain.', 'wpessential' ),
			'item_name_mismatch'  => __( 'This key belongs to a different product.', 'wpessential' ),
			'no_activations_left' => __( 'This license has reached its activation limit. Deactivate it on another site first.', 'wpessential' ),
		);
		return $messages[ $code ] ?? $messages['invalid'];
	}

	/**
	 * Call EDD.
	 *
	 * @param string $action EDD action.
	 * @param string $key    Key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	private function call( $action, $key, $domain ) {
		$body = $this->request(
			'POST',
			$this->config['endpoint'],
			array(
				'edd_action' => $action,
				'license'    => $key,
				'item_id'    => $this->config['product_id'],
				'url'        => $domain,
			)
		);
		if ( is_wp_error( $body ) ) {
			return $body;
		}
		$status = $body['license'] ?? 'invalid';
		if ( 'deactivate_license' === $action ) {
			return $this->result( 'inactive' );
		}
		if ( empty( $body['success'] ) && 'valid' !== $status ) {
			$code = $body['error'] ?? $status;
			return new \WP_Error( 'wpe_license_' . $code, $this->message( $code ) );
		}
		return $this->result( 'valid' === $status ? 'active' : $status, $body['expires'] ?? '', $body['price_id'] ?? '' );
	}

	/**
	 * Activate the key for a domain.
	 *
	 * @param string $key    License key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	public function activate( $key, $domain ) {
		return $this->call( 'activate_license', $key, $domain );
	}

	/**
	 * Release the domain.
	 *
	 * @param string $key    License key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	public function deactivate( $key, $domain ) {
		return $this->call( 'deactivate_license', $key, $domain );
	}

	/**
	 * Check the key.
	 *
	 * @param string $key    License key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	public function check( $key, $domain ) {
		return $this->call( 'check_license', $key, $domain );
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
		$body = $this->request(
			'POST',
			$this->config['endpoint'],
			array(
				'edd_action' => 'get_version',
				'license'    => $key,
				'item_id'    => $this->config['product_id'],
				'url'        => $domain,
			)
		);
		if ( is_wp_error( $body ) ) {
			return $body;
		}
		$url = $body['download_link'] ?? ( $body['package'] ?? '' );
		$url = apply_filters( 'wpessential_core_edd_package_url', $url, $item, $context, $body );
		return $url ? esc_url_raw( $url ) : new \WP_Error( 'wpe_package', __( 'The store did not return a download link for this license.', 'wpessential' ) );
	}
}
