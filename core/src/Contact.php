<?php
/**
 * Contact form: [wpessential_contact] shortcode used by demo contact pages.
 *
 * Sends the message to the site's admin email with wp_mail(). Nonce, a honeypot field and a
 * one-minute per-visitor limit keep casual spam out.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Contact form.
 */
class Contact {

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_shortcode( 'wpessential_contact', array( __CLASS__, 'render' ) );
		add_shortcode( 'wpessential_newsletter', array( __CLASS__, 'newsletter' ) );
		add_action( 'admin_post_nopriv_wpessential_contact', array( __CLASS__, 'send' ) );
		add_action( 'admin_post_wpessential_contact', array( __CLASS__, 'send' ) );
	}

	/**
	 * Topics offered in the form.
	 *
	 * @return string[]
	 */
	private static function topics() {
		return (array) apply_filters(
			'wpessential_contact_topics',
			array(
				__( 'General question', 'hi-wpessential' ),
				__( 'Support', 'hi-wpessential' ),
				__( 'Sales & licensing', 'hi-wpessential' ),
				__( 'WordPress service', 'hi-wpessential' ),
			)
		);
	}

	/**
	 * Form markup.
	 *
	 * @return string
	 */
	public static function render() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only status flag.
		$status = isset( $_GET['wpe-contact'] ) ? sanitize_key( wp_unslash( $_GET['wpe-contact'] ) ) : '';
		$notice = '';
		if ( 'sent' === $status ) {
			$notice = '<p class="wpe-contact-notice" role="status">' . esc_html__( 'Thanks — your message is on its way. We reply within one business day.', 'hi-wpessential' ) . '</p>';
		} elseif ( $status ) {
			$notice = '<p class="wpe-contact-notice is-error" role="alert">' . esc_html__( 'Your message could not be sent. Check the fields and try again, or email us directly.', 'hi-wpessential' ) . '</p>';
		}
		$privacy = get_privacy_policy_url();
		ob_start();
		?>
		<form class="wpe-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>
			<input type="hidden" name="action" value="wpessential_contact">
			<input type="hidden" name="back" value="<?php echo esc_url( get_permalink() ); ?>">
			<?php wp_nonce_field( 'wpessential_contact', '_wpe_contact' ); ?>
			<label for="wpe-c-name"><?php esc_html_e( 'Name', 'hi-wpessential' ); ?></label>
			<input id="wpe-c-name" name="name" type="text" required autocomplete="name">
			<label for="wpe-c-email"><?php esc_html_e( 'Email', 'hi-wpessential' ); ?></label>
			<input id="wpe-c-email" name="email" type="email" required autocomplete="email">
			<label for="wpe-c-topic"><?php esc_html_e( 'Topic', 'hi-wpessential' ); ?></label>
			<select id="wpe-c-topic" name="topic">
				<?php foreach ( self::topics() as $topic ) : ?>
					<option><?php echo esc_html( $topic ); ?></option>
				<?php endforeach; ?>
			</select>
			<label for="wpe-c-message"><?php esc_html_e( 'Message', 'hi-wpessential' ); ?></label>
			<textarea id="wpe-c-message" name="message" rows="4" required></textarea>
			<p class="wpe-contact-form__hp" aria-hidden="true"><label for="wpe-c-site"><?php esc_html_e( 'Leave this empty', 'hi-wpessential' ); ?></label><input id="wpe-c-site" name="website" type="text" tabindex="-1" autocomplete="off"></p>
			<button type="submit"><?php esc_html_e( 'Send message', 'hi-wpessential' ); ?> ↗</button>
			<p class="wpe-contact-form__note">
				<?php
				if ( $privacy ) {
					/* translators: %s: privacy policy link. */
					printf( esc_html__( 'We use your details only to reply. See our %s.', 'hi-wpessential' ), '<a href="' . esc_url( $privacy ) . '">' . esc_html__( 'Privacy Policy', 'hi-wpessential' ) . '</a>' );
				} else {
					esc_html_e( 'We use your details only to reply.', 'hi-wpessential' );
				}
				?>
			</p>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * One-field newsletter sign-up: sends the address to the admin email like a contact message.
	 *
	 * @return string
	 */
	public static function newsletter() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only status flag.
		$done = isset( $_GET['wpe-contact'] ) && 'sent' === sanitize_key( wp_unslash( $_GET['wpe-contact'] ) );
		ob_start();
		?>
		<form class="wpe-newsletter" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wpessential_contact">
			<input type="hidden" name="newsletter" value="1">
			<input type="hidden" name="back" value="<?php echo esc_url( home_url( add_query_arg( array() ) ) ); ?>">
			<?php wp_nonce_field( 'wpessential_contact', '_wpe_contact' ); ?>
			<label class="screen-reader-text" for="wpe-n-email"><?php esc_html_e( 'Email address', 'hi-wpessential' ); ?></label>
			<input id="wpe-n-email" name="email" type="email" required placeholder="<?php esc_attr_e( 'you@company.com', 'hi-wpessential' ); ?>" autocomplete="email">
			<span class="wpe-contact-form__hp" aria-hidden="true"><input name="website" type="text" tabindex="-1" autocomplete="off"></span>
			<button type="submit"><?php echo $done ? esc_html__( 'Subscribed', 'hi-wpessential' ) : esc_html__( 'Subscribe', 'hi-wpessential' ) . ' ↗'; ?></button>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Handle a submission.
	 */
	public static function send() {
		$back = isset( $_POST['back'] ) ? esc_url_raw( wp_unslash( $_POST['back'] ) ) : home_url( '/' );
		$back = wp_validate_redirect( $back, home_url( '/' ) );
		$ok   = isset( $_POST['_wpe_contact'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpe_contact'] ) ), 'wpessential_contact' );
		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$mail = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$msg  = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$tpc  = isset( $_POST['topic'] ) ? sanitize_text_field( wp_unslash( $_POST['topic'] ) ) : '';
		$key  = 'wpe_contact_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
		if ( ! empty( $_POST['newsletter'] ) ) {
			$name = $mail;
			$tpc  = __( 'Newsletter', 'hi-wpessential' );
			$msg  = __( 'Please add this address to the newsletter.', 'hi-wpessential' );
		}
		if ( ! empty( $_POST['website'] ) ) {
			wp_safe_redirect( add_query_arg( 'wpe-contact', 'sent', $back ) ); // Honeypot: pretend it worked.
			exit;
		}
		if ( ! $ok || ! $name || ! is_email( $mail ) || ! $msg || get_transient( $key ) ) {
			wp_safe_redirect( add_query_arg( 'wpe-contact', 'error', $back ) );
			exit;
		}
		set_transient( $key, 1, MINUTE_IN_SECONDS );
		/* translators: 1: topic, 2: name. */
		$subject = sprintf( __( '[%1$s] %2$s via the contact form', 'hi-wpessential' ), $tpc, $name );
		$sent    = wp_mail( get_option( 'admin_email' ), $subject, $msg . "\n\n— " . $name . ' <' . $mail . '>', array( 'Reply-To: ' . $name . ' <' . $mail . '>' ) );
		wp_safe_redirect( add_query_arg( 'wpe-contact', $sent ? 'sent' : 'error', $back ) );
		exit;
	}
}
