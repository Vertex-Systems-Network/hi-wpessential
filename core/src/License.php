<?php
/**
 * License key + domain activation.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Stores the license and talks to the configured provider through an adapter.
 */
class License {

	const OPTION = 'wpessential_core_license';
	const CRON   = 'wpessential_core_license_check';

	/**
	 * Provider settings. Sellers override them with the filter.
	 *
	 * @return array{provider: string, endpoint: string, product_id: string}
	 */
	public static function config() {
		return wp_parse_args(
			apply_filters( 'wpessential_core_license_config', array() ),
			array(
				'provider'   => 'custom',
				'api'        => 'https://wpessential.org/wp-json/wpessential-server/v1',
				'endpoint'   => 'https://wpessential.org/wp-json/wpessential-server/v1/license',
				'product_id' => 'wpessential-theme',
			)
		);
	}

	/**
	 * Adapter for the configured provider.
	 *
	 * @return License\Adapter
	 */
	public static function adapter() {
		$config = self::config();
		$map    = apply_filters(
			'wpessential_core_license_adapters',
			array(
				'custom' => License\Custom::class,
				'edd'    => License\Edd::class,
				'woo'    => License\Woo::class,
			)
		);
		$class  = $map[ $config['provider'] ] ?? License\Custom::class;
		return new $class( $config );
	}

	/**
	 * Saved license data.
	 *
	 * @return array
	 */
	public static function data() {
		return wp_parse_args(
			(array) get_option( self::OPTION, array() ),
			array(
				'key'         => '',
				'status'      => 'inactive',
				'expires'     => '',
				'plan'        => '',
				'checked'     => 0,
				'message'     => '',
				'pro'         => false,
				'white_label' => false,
				'sites_used'  => 0,
				'sites_limit' => 0,
			)
		);
	}

	/**
	 * Data safe to send to the browser (masked key).
	 *
	 * @return array
	 */
	public static function public_data() {
		$data        = self::data();
		$data['key'] = $data['key'] ? substr( $data['key'], 0, 4 ) . str_repeat( '•', 8 ) . substr( $data['key'], -4 ) : '';
		$config      = self::config();
		$labels      = array(
			'custom' => 'WPEssential',
			'edd'    => 'Easy Digital Downloads',
			'woo'    => 'WooCommerce API Manager',
		);
		return $data + array(
			'domain'   => self::domain(),
			'provider' => $labels[ $config['provider'] ] ?? $config['provider'],
		);
	}

	/**
	 * Whether a valid license is stored.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return 'active' === self::data()['status'];
	}

	/**
	 * Active license that unlocks Pro demos. Licenses from EDD/Woo adapters count as Pro.
	 *
	 * @return bool
	 */
	public static function has_pro() {
		$data = self::data();
		return self::is_active() && ( ! empty( $data['pro'] ) || 'custom' !== self::config()['provider'] );
	}

	/**
	 * Site domain the license is bound to.
	 *
	 * @return string
	 */
	public static function domain() {
		return (string) wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Activate a key for this domain.
	 *
	 * @param string $key License key.
	 * @return array|\WP_Error
	 */
	public static function activate( $key ) {
		$key = trim( sanitize_text_field( $key ) );
		if ( strlen( $key ) < 8 ) {
			return new \WP_Error( 'wpe_key', __( 'That license key looks too short. Copy it again from your purchase email.', 'hi-wpessential' ) );
		}
		$result = self::adapter()->activate( $key, self::domain() );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		update_option(
			self::OPTION,
			array_merge(
				$result,
				array(
					'key'     => $key,
					'checked' => time(),
				)
			),
			false
		);
		return self::public_data();
	}

	/**
	 * Release this domain.
	 *
	 * @return array|\WP_Error
	 */
	public static function deactivate() {
		$data = self::data();
		if ( $data['key'] ) {
			$result = self::adapter()->deactivate( $data['key'], self::domain() );
			if ( is_wp_error( $result ) && 'wpe_http' !== $result->get_error_code() ) {
				return $result;
			}
		}
		delete_option( self::OPTION );
		return self::public_data();
	}

	/**
	 * Re-check the stored key (daily cron).
	 */
	public static function refresh() {
		$data = self::data();
		if ( ! $data['key'] ) {
			return;
		}
		$result = self::adapter()->check( $data['key'], self::domain() );
		if ( is_wp_error( $result ) ) {
			// Keep the last known state when the server is unreachable.
			if ( 'wpe_http' !== $result->get_error_code() ) {
				$data['status']  = 'invalid';
				$data['message'] = $result->get_error_message();
			}
		} else {
			$data = array_merge( $data, $result );
		}
		$data['checked'] = time();
		update_option( self::OPTION, $data, false );
	}

	/**
	 * Signed download URL for a licensed package.
	 *
	 * @param string $item    Demo id or plugin slug.
	 * @param string $context Builder key or "plugin".
	 * @return string|\WP_Error
	 */
	public static function package_url( $item, $context ) {
		if ( ! self::is_active() ) {
			return new \WP_Error( 'wpe_license', __( 'This is a Pro item. Activate your license on the License tab to unlock it.', 'hi-wpessential' ) );
		}
		return self::adapter()->package_url( self::data()['key'], self::domain(), $item, $context );
	}

	/**
	 * Daily check.
	 */
	public static function schedule() {
		add_action( self::CRON, array( __CLASS__, 'refresh' ) );
		add_action(
			'admin_init',
			static function () {
				if ( ! wp_next_scheduled( self::CRON ) ) {
					wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', self::CRON );
				}
			}
		);
		add_action(
			'switch_theme',
			static function () {
				wp_clear_scheduled_hook( self::CRON );
			}
		);
	}
}
