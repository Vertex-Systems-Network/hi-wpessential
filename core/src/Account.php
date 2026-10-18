<?php
/**
 * WPEssential account (sign up / sign in) against the WPEssential Server plugin.
 *
 * The token is stored per site (admins share it) and never sent to the browser.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Account connection.
 */
class Account {

	const OPTION = 'wpessential_core_account';

	/**
	 * Server API base.
	 *
	 * @return string
	 */
	public static function api() {
		return untrailingslashit( (string) License::config()['api'] );
	}

	/**
	 * Call the server.
	 *
	 * @param string $method Method.
	 * @param string $route  Route.
	 * @param array  $body   Body.
	 * @param bool   $auth   Send the token.
	 * @return array|\WP_Error
	 */
	private static function request( $method, $route, array $body = array(), $auth = true ) {
		$args  = array(
			'method'  => $method,
			'timeout' => 20,
			'headers' => array( 'Accept' => 'application/json' ),
		);
		$token = self::token();
		if ( $auth && $token ) {
			$args['headers']['Authorization'] = 'Bearer ' . $token;
		}
		if ( 'GET' !== $method ) {
			$args['body'] = $body;
		}
		$response = wp_safe_remote_request( self::api() . $route, $args );
		if ( is_wp_error( $response ) ) {
			/* translators: %s: error. */
			return new \WP_Error( 'wpe_http', sprintf( __( 'WPEssential could not be reached: %s', 'hi-wpessential' ), $response->get_error_message() ) );
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( 401 === $code && $auth ) {
			delete_option( self::OPTION );
			return new \WP_Error( 'wpe_signed_out', __( 'Your WPEssential session ended. Sign in again.', 'hi-wpessential' ) );
		}
		if ( ! is_array( $data ) ) {
			/* translators: %d: HTTP code. */
			return new \WP_Error( 'wpe_http', sprintf( __( 'WPEssential answered with an unexpected response (HTTP %d).', 'hi-wpessential' ), $code ) );
		}
		if ( isset( $data['success'] ) && ! $data['success'] ) {
			return new \WP_Error( 'wpe_' . ( $data['status'] ?? 'error' ), $data['message'] ?? __( 'Something went wrong.', 'hi-wpessential' ) );
		}
		if ( isset( $data['code'], $data['message'] ) && $code >= 400 ) {
			return new \WP_Error( 'wpe_' . $data['code'], $data['message'] );
		}
		return $data;
	}

	/**
	 * Stored token.
	 *
	 * @return string
	 */
	private static function token() {
		return (string) ( get_option( self::OPTION, array() )['token'] ?? '' );
	}

	/**
	 * Save session from a login/register response.
	 *
	 * @param array $data Response.
	 * @return array
	 */
	private static function store( array $data ) {
		update_option(
			self::OPTION,
			array(
				'token' => $data['token'],
				'user'  => $data['user'],
			),
			false
		);
		return self::shape( $data );
	}

	/**
	 * Browser-safe account data.
	 *
	 * @param array $data Server data.
	 * @return array
	 */
	private static function shape( array $data ) {
		$domain = License::domain();
		$plans  = array_values(
			array_filter(
				(array) ( $data['plans'] ?? array() ),
				static function ( $p ) {
					return 'free' !== $p['key'];
				}
			)
		);
		return array(
			'signed_in' => true,
			'user'      => $data['user'] ?? array(),
			'account'   => $data['account'] ?? '',
			'plans'     => $plans,
			'licenses'  => array_map(
				static function ( $l ) use ( $domain ) {
					$l['here'] = in_array( preg_replace( '/^www\./', '', strtolower( $domain ) ), (array) $l['domains'], true );
					$l['mask'] = substr( $l['key'], 0, 8 ) . '••••' . substr( $l['key'], -4 );
					unset( $l['key'] );
					return $l;
				},
				(array) ( $data['licenses'] ?? array() )
			),
		);
	}

	/**
	 * Current account (refreshes from the server).
	 *
	 * @return array|\WP_Error
	 */
	public static function me() {
		if ( ! self::token() ) {
			return array( 'signed_in' => false );
		}
		$data = self::request( 'GET', '/account/me' );
		return is_wp_error( $data ) ? $data : self::shape( $data );
	}

	/**
	 * Sign in.
	 *
	 * @param string $login    Email or username.
	 * @param string $password Password.
	 * @return array|\WP_Error
	 */
	public static function login( $login, $password ) {
		$data = self::request(
			'POST',
			'/account/login',
			array(
				'login'    => $login,
				'password' => $password,
				'site'     => home_url(),
			),
			false
		);
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		self::store( $data );
		return self::me();
	}

	/**
	 * Create an account (gets the Free plan).
	 *
	 * @param string $name     Name.
	 * @param string $email    Email.
	 * @param string $password Password.
	 * @return array|\WP_Error
	 */
	public static function register( $name, $email, $password ) {
		$data = self::request(
			'POST',
			'/account/register',
			array(
				'name'     => $name,
				'email'    => $email,
				'password' => $password,
				'site'     => home_url(),
			),
			false
		);
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		self::store( $data );
		$me = self::me();
		// New accounts get a Free license: put it on this site straight away.
		if ( ! is_wp_error( $me ) && ! License::is_active() && ! empty( $data['licenses'][0]['key'] ) ) {
			License::activate( $data['licenses'][0]['key'] );
		}
		return $me;
	}

	/**
	 * Sign out.
	 *
	 * @return array
	 */
	public static function logout() {
		if ( self::token() ) {
			self::request( 'POST', '/account/logout' );
		}
		delete_option( self::OPTION );
		return array( 'signed_in' => false );
	}

	/**
	 * Use one of the account's licenses on this site.
	 *
	 * @param int $license_id License.
	 * @return array|\WP_Error License data.
	 */
	public static function use_license( $license_id ) {
		$data = self::request( 'GET', '/account/me' );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		foreach ( (array) $data['licenses'] as $license ) {
			if ( (int) $license['id'] === (int) $license_id ) {
				if ( License::is_active() && License::data()['key'] !== $license['key'] ) {
					License::deactivate();
				}
				return License::activate( $license['key'] );
			}
		}
		return new \WP_Error( 'wpe_license', __( 'That license is not in your account.', 'hi-wpessential' ) );
	}

	/**
	 * Checkout link for buy / renew / upgrade (opens signed in on wpessential.org).
	 *
	 * @param string $plan       Plan key.
	 * @param int    $license_id License for renew/upgrade.
	 * @param string $mode       buy|renew|upgrade.
	 * @return array|\WP_Error
	 */
	public static function checkout( $plan, $license_id = 0, $mode = 'buy' ) {
		return self::request(
			'POST',
			'/account/checkout',
			array(
				'plan'       => $plan,
				'license_id' => $license_id,
				'mode'       => $mode,
			)
		);
	}

	/**
	 * Owner action on a license (auto_renew_on/off, cancel, deactivate_domain).
	 *
	 * @param int    $license_id License.
	 * @param string $action     Action.
	 * @param string $domain     Domain.
	 * @return array|\WP_Error
	 */
	public static function license_action( $license_id, $action, $domain = '' ) {
		if ( ! in_array( $action, array( 'auto_renew_on', 'auto_renew_off', 'cancel', 'deactivate_domain' ), true ) ) {
			return new \WP_Error( 'wpe_action', __( 'Unknown action.', 'hi-wpessential' ) );
		}
		$data = self::request(
			'POST',
			'/account/license',
			array(
				'license_id' => $license_id,
				'action'     => $action,
				'domain'     => $domain,
			)
		);
		return is_wp_error( $data ) ? $data : self::me();
	}
}
