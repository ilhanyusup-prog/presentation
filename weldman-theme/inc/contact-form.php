<?php
/**
 * Contact form handling.
 *
 * Per the project brief, one lightweight form plugin is allowed. The theme
 * is built to work with Contact Form 7 (shortcode dropped into the ACF
 * "Contact form" layout / Options Page), but ships a tiny native fallback
 * using wp_mail() so the "Kirjuta meile!" section still works out of the
 * box on a fresh install before any plugin is activated.
 *
 * @package Weldman
 */

/**
 * Render the contact form markup.
 *
 * If Contact Form 7 is active and a form ID/shortcode has been configured in
 * the "Contact form" ACF layout, that shortcode is used. Otherwise a small
 * native form (POST to admin-post.php) is rendered.
 *
 * @param array $args {
 *     @type string $title Optional heading override.
 * }
 */
function weldman_render_contact_form( $args = array() ) {
	$cf7_shortcode = weldman_field( 'cf7_shortcode' );

	if ( $cf7_shortcode && shortcode_exists( 'contact-form-7' ) ) {
		echo do_shortcode( $cf7_shortcode );
		return;
	}

	weldman_native_contact_form();
}

/**
 * Minimal, dependency-free contact form (name / email / message) that posts
 * to admin-post.php and sends mail via wp_mail(). Includes a honeypot field
 * and a simple time-check as basic spam mitigation.
 */
function weldman_native_contact_form() {
	$sent  = isset( $_GET['weldman_contact'] ) && 'sent' === $_GET['weldman_contact'];
	$error = isset( $_GET['weldman_contact'] ) && 'error' === $_GET['weldman_contact'];
	?>
	<?php if ( $sent ) : ?>
		<p class="form-notice form-notice--success" role="status">
			<?php esc_html_e( 'Thank you! Your message has been sent.', 'weldman' ); ?>
		</p>
	<?php elseif ( $error ) : ?>
		<p class="form-notice form-notice--error" role="alert">
			<?php esc_html_e( 'Something went wrong, please try again or email us directly.', 'weldman' ); ?>
		</p>
	<?php endif; ?>
	<form class="contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="weldman_contact_form" />
		<?php wp_nonce_field( 'weldman_contact_form', 'weldman_contact_nonce' ); ?>
		<input type="text" name="weldman_hp" class="form-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true" />
		<input type="hidden" name="weldman_ts" value="<?php echo esc_attr( time() ); ?>" />

		<div class="form-row">
			<label class="screen-reader-text" for="weldman-name"><?php esc_html_e( 'Name', 'weldman' ); ?></label>
			<input type="text" id="weldman-name" name="name" placeholder="<?php esc_attr_e( 'Nimi', 'weldman' ); ?>" required />
		</div>
		<div class="form-row">
			<label class="screen-reader-text" for="weldman-email"><?php esc_html_e( 'Email', 'weldman' ); ?></label>
			<input type="email" id="weldman-email" name="email" placeholder="<?php esc_attr_e( 'E-post', 'weldman' ); ?>" required />
		</div>
		<div class="form-row">
			<label class="screen-reader-text" for="weldman-message"><?php esc_html_e( 'Message', 'weldman' ); ?></label>
			<textarea id="weldman-message" name="message" rows="5" placeholder="<?php esc_attr_e( 'Sõnum', 'weldman' ); ?>" required></textarea>
		</div>
		<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Saada', 'weldman' ); ?></button>
	</form>
	<?php
}

/**
 * Handle the native contact form submission (logged-in and logged-out).
 */
function weldman_handle_contact_form() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	if (
		! isset( $_POST['weldman_contact_nonce'] ) ||
		! wp_verify_nonce( $_POST['weldman_contact_nonce'], 'weldman_contact_form' )
	) {
		wp_safe_redirect( add_query_arg( 'weldman_contact', 'error', $redirect ) );
		exit;
	}

	// Honeypot: bots tend to fill every field.
	if ( ! empty( $_POST['weldman_hp'] ) ) {
		wp_safe_redirect( add_query_arg( 'weldman_contact', 'sent', $redirect ) );
		exit;
	}

	// Time-trap: real users take at least a couple of seconds to fill the form.
	$submitted_at = isset( $_POST['weldman_ts'] ) ? (int) $_POST['weldman_ts'] : 0;
	if ( $submitted_at && ( time() - $submitted_at ) < 2 ) {
		wp_safe_redirect( add_query_arg( 'weldman_contact', 'error', $redirect ) );
		exit;
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( ! $name || ! is_email( $email ) || ! $message ) {
		wp_safe_redirect( add_query_arg( 'weldman_contact', 'error', $redirect ) );
		exit;
	}

	$to      = weldman_option( 'email' ) ? weldman_option( 'email' ) : get_option( 'admin_email' );
	$subject = sprintf( /* translators: %s: site name. */ __( 'New contact form message — %s', 'weldman' ), get_bloginfo( 'name' ) );
	$body    = sprintf(
		"%s: %s\n%s: %s\n\n%s:\n%s",
		__( 'Name', 'weldman' ),
		$name,
		__( 'Email', 'weldman' ),
		$email,
		__( 'Message', 'weldman' ),
		$message
	);
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	$sent = wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'weldman_contact', $sent ? 'sent' : 'error', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_weldman_contact_form', 'weldman_handle_contact_form' );
add_action( 'admin_post_weldman_contact_form', 'weldman_handle_contact_form' );
