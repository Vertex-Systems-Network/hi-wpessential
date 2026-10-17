<?php
/**
 * Auto-renewal, reminders and expiry (daily cron).
 *
 * Renewal orders are normal WooCommerce orders marked with _wpes_renew. When the
 * customer saved a card with a gateway that supports tokens, the gateway charges it
 * through the `wpes_charge_renewal` filter; otherwise the customer gets a pay link.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Renewals.
 */
class Renewals {

	const CRON = 'wpes_server_daily';

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( self::CRON, array( __CLASS__, 'run' ) );
		add_action(
			'init',
			static function () {
				if ( ! wp_next_scheduled( self::CRON ) ) {
					wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON );
				}
			}
		);
	}

	/**
	 * Remove the cron on deactivation.
	 */
	public static function unschedule() {
		wp_clear_scheduled_hook( self::CRON );
	}

	/**
	 * Daily job.
	 *
	 * @return array Counts for logging and tests.
	 */
	public static function run() {
		global $wpdb;
		$table  = $wpdb->prefix . 'wpes_licenses';
		$remind = (int) Plugin::settings( 'remind_days' );
		$counts = array(
			'renewal_orders' => 0,
			'charged'        => 0,
			'reminders'      => 0,
			'expired'        => 0,
		);

		// Licenses ending within the reminder window.
		$due = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
			$wpdb->prepare( "SELECT * FROM {$table} WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at <= %s", gmdate( 'Y-m-d H:i:s', time() + $remind * DAY_IN_SECONDS ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name.
			ARRAY_A
		);
		foreach ( (array) $due as $license ) {
			if ( Licenses::is_expired( $license ) ) {
				Licenses::update( $license['id'], array( 'status' => 'expired' ) );
				self::mail( $license, 'expired' );
				++$counts['expired'];
				continue;
			}
			if ( $license['auto_renew'] ) {
				$result = self::renewal_order( $license );
				if ( $result ) {
					++$counts['renewal_orders'];
					if ( 'charged' === $result ) {
						++$counts['charged'];
					}
				}
			} elseif ( ! get_option( 'wpes_reminded_' . $license['id'] . '_' . $license['expires_at'] ) ) {
				self::mail( $license, 'reminder' );
				update_option( 'wpes_reminded_' . $license['id'] . '_' . $license['expires_at'], 1, false );
				++$counts['reminders'];
			}
		}
		return $counts;
	}

	/**
	 * Pending renewal order for a license (reused if one exists).
	 *
	 * @param array $license License.
	 * @return \WC_Order|null
	 */
	public static function pending_order( $license ) {
		$orders = wc_get_orders(
			array(
				'limit'       => 1,
				'customer_id' => (int) $license['user_id'],
				'status'      => array( 'pending', 'failed', 'on-hold' ),
				'meta_key'    => '_wpes_renewal_of', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Indexed by customer first.
				'meta_value'  => (int) $license['id'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Indexed by customer first.
			)
		);
		return $orders ? $orders[0] : null;
	}

	/**
	 * Create the renewal order and try to charge it.
	 *
	 * @param array $license License.
	 * @return string|false "charged", "invoiced" or false.
	 */
	public static function renewal_order( $license ) {
		if ( self::pending_order( $license ) ) {
			return false;
		}
		$product = wc_get_product( (int) $license['product_id'] );
		if ( ! $product ) {
			return false;
		}
		$last  = $license['order_id'] ? wc_get_order( (int) $license['order_id'] ) : null;
		$order = wc_create_order( array( 'customer_id' => (int) $license['user_id'] ) );
		$item  = new \WC_Order_Item_Product();
		$item->set_product( $product );
		$item->set_quantity( 1 );
		$item->set_subtotal( $product->get_price() );
		$item->set_total( $product->get_price() );
		$item->add_meta_data( '_wpes_renew', (int) $license['id'], true );
		$order->add_item( $item );
		if ( $last ) {
			$order->set_address( $last->get_address( 'billing' ), 'billing' );
			$order->set_payment_method( $last->get_payment_method() );
			$order->set_payment_method_title( $last->get_payment_method_title() );
		} else {
			$customer = new \WC_Customer( (int) $license['user_id'] );
			$order->set_billing_email( $customer->get_email() );
			$order->set_billing_first_name( $customer->get_first_name() );
			$order->set_billing_last_name( $customer->get_last_name() );
		}
		$order->update_meta_data( '_wpes_renewal_of', (int) $license['id'] );
		$order->calculate_totals();
		/* translators: %s: key. */
		$order->add_order_note( sprintf( __( 'Automatic renewal for license %s.', 'wpessential-server' ), $license['license_key'] ) );
		$order->save();

		if ( (float) $order->get_total() <= 0 ) {
			$order->payment_complete();
			return 'charged';
		}

		$token   = self::token( $license, $order->get_payment_method() );
		$charged = $token ? (bool) apply_filters( 'wpes_charge_renewal', false, $order, $token, $license ) : false;
		if ( $charged ) {
			return 'charged';
		}
		$mailer = WC()->mailer()->get_emails();
		if ( isset( $mailer['WC_Email_Customer_Invoice'] ) ) {
			$mailer['WC_Email_Customer_Invoice']->trigger( $order->get_id(), $order );
		}
		return 'invoiced';
	}

	/**
	 * Saved payment token for the customer and gateway.
	 *
	 * @param array  $license License.
	 * @param string $gateway Gateway id.
	 * @return \WC_Payment_Token|null
	 */
	private static function token( $license, $gateway ) {
		if ( ! $gateway || ! class_exists( 'WC_Payment_Tokens' ) ) {
			return null;
		}
		$token = \WC_Payment_Tokens::get_customer_default_token( (int) $license['user_id'] );
		return $token && $token->get_gateway_id() === $gateway ? $token : null;
	}

	/**
	 * Reminder / expiry email.
	 *
	 * @param array  $license License.
	 * @param string $type    reminder|expired.
	 */
	public static function mail( $license, $type ) {
		$user = get_userdata( (int) $license['user_id'] );
		if ( ! $user ) {
			return;
		}
		$link = wc_get_account_endpoint_url( MyAccount::ENDPOINT );
		if ( 'expired' === $type ) {
			/* translators: %s: key. */
			$subject = sprintf( __( 'Your WPEssential license %s has expired', 'wpessential-server' ), $license['license_key'] );
			$body    = __( 'Pro demos and updates are paused on your websites. Renew in one click to continue — your sites keep working either way.', 'wpessential-server' );
		} else {
			/* translators: 1: key, 2: date. */
			$subject = sprintf( __( 'Your WPEssential license %1$s ends on %2$s', 'wpessential-server' ), $license['license_key'], substr( $license['expires_at'], 0, 10 ) );
			$body    = __( 'Automatic renewal is off for this license. Renew now to keep Pro demos, updates and support.', 'wpessential-server' );
		}
		$mailer  = WC()->mailer();
		$message = $mailer->wrap_message( $subject, '<p>' . esc_html( $body ) . '</p><p><a href="' . esc_url( $link ) . '">' . esc_html__( 'Manage your licenses', 'wpessential-server' ) . '</a></p>' );
		$mailer->send( $user->user_email, $subject, $message );
	}
}
