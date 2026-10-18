<?php
/**
 * Base class for license providers.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core\License;

defined( 'ABSPATH' ) || exit;

/**
 * Every adapter returns array{status, expires, plan, message} or a WP_Error.
 * Error code "wpe_http" means the server could not be reached.
 */
abstract class Adapter {

	/**
	 * Provider config.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param array $config Config.
	 */
	public function __construct( array $config ) {
		$this->config = $config;
	}

	/**
	 * Activate.
	 *
	 * @param string $key    Key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	abstract public function activate( $key, $domain );

	/**
	 * Deactivate.
	 *
	 * @param string $key    Key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	abstract public function deactivate( $key, $domain );

	/**
	 * Check.
	 *
	 * @param string $key    Key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error
	 */
	abstract public function check( $key, $domain );

	/**
	 * Download URL for a licensed package. Default: the custom API pattern.
	 *
	 * @param string $key     Key.
	 * @param string $domain  Domain.
	 * @param string $item    Item.
	 * @param string $context Context.
	 * @return string|\WP_Error
	 */
	public function package_url( $key, $domain, $item, $context ) {
		$body = $this->request(
			'POST',
			trailingslashit( $this->config['endpoint'] ) . 'package',
			array(
				'license' => $key,
				'domain'  => $domain,
				'item'    => $item,
				'context' => $context,
			)
		);
		if ( is_wp_error( $body ) ) {
			return $body;
		}
		return empty( $body['url'] ) ? new \WP_Error( 'wpe_package', __( 'The license server did not return a download link.', 'wpessential' ) ) : esc_url_raw( $body['url'] );
	}

	/**
	 * HTTP helper returning decoded JSON.
	 *
	 * @param string $method Method.
	 * @param string $url    URL.
	 * @param array  $args   Body or query.
	 * @return array|\WP_Error
	 */
	protected function request( $method, $url, array $args ) {
		$params = array(
			'timeout' => 15,
			'headers' => array( 'Accept' => 'application/json' ),
		);
		if ( 'GET' === $method ) {
			$response = wp_safe_remote_get( add_query_arg( $args, $url ), $params );
		} else {
			$response = wp_safe_remote_post( $url, $params + array( 'body' => $args ) );
		}
		if ( is_wp_error( $response ) ) {
			/* translators: %s: error. */
			return new \WP_Error( 'wpe_http', sprintf( __( 'The license server could not be reached: %s', 'wpessential' ), $response->get_error_message() ) );
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( $code >= 500 || ! is_array( $body ) ) {
			/* translators: %d: HTTP status. */
			return new \WP_Error( 'wpe_http', sprintf( __( 'The license server answered with an unexpected response (HTTP %d). Try again in a few minutes.', 'wpessential' ), $code ) );
		}
		return $body;
	}

	/**
	 * Normalised success array.
	 *
	 * @param string $status  Status.
	 * @param string $expires Expiry date.
	 * @param string $plan    Plan name.
	 * @param string $message Message.
	 * @return array
	 */
	protected function result( $status, $expires = '', $plan = '', $message = '' ) {
		return array(
			'status'  => $status,
			'expires' => $expires && 'lifetime' !== $expires ? gmdate( 'Y-m-d', is_numeric( $expires ) ? (int) $expires : strtotime( $expires ) ) : (string) $expires,
			'plan'    => (string) $plan,
			'message' => (string) $message,
		);
	}
}
