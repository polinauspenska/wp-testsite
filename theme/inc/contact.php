<?php
/**
 * CONTACT — the enquiry, sent.
 *
 * The contact page's form works on its own: it posts to admin-post.php, and
 * this handler emails the enquiry to the address set in Customizer →
 * "Lemon Mint — studio details" → Enquiries email. No plugin needed.
 *
 * If the client prefers a form plugin (Gravity Forms, WPForms, Fluent…),
 * return its shortcode from the 'lmf_contact_form_shortcode' filter and the
 * page prints that instead of this form.
 *
 * Spam: a nonce, a hidden honeypot field, a minimum fill time and one
 * enquiry per visitor per minute.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * The choices the sentence offers. One place, used by the form and the email.
 *
 * @return array
 */
function lmf_contact_choices() {
	$services = array();
	foreach ( lmf_reel_services() as $s ) {
		$services[ sanitize_title( $s['title'] ) ] = $s['title'];
	}
	$services['other'] = __( 'something else', 'lemonmint' );

	return apply_filters(
		'lmf_contact_choices',
		array(
			'intent'   => array(
				'production' => array( __( 'Start a production', 'lemonmint' ), __( 'We need', 'lemonmint' ) ),
				'call'       => array( __( 'Book a call', 'lemonmint' ), __( 'We’re thinking about', 'lemonmint' ) ),
				'quote'      => array( __( 'Get a quote', 'lemonmint' ), __( 'We need a quote for', 'lemonmint' ) ),
			),
			'services' => $services,
			'when'     => array(
				'asap'    => __( 'as soon as possible', 'lemonmint' ),
				'month'   => __( 'within a month', 'lemonmint' ),
				'quarter' => __( 'in the next 2–3 months', 'lemonmint' ),
				'later'   => __( 'later this year', 'lemonmint' ),
				'unsure'  => __( 'with no fixed date yet', 'lemonmint' ),
			),
			'budget'   => array(
				'unsure' => __( 'still open', 'lemonmint' ),
				'u50'    => __( 'under AED 50k', 'lemonmint' ),
				'50-150' => __( 'AED 50–150k', 'lemonmint' ),
				'150-400'=> __( 'AED 150–400k', 'lemonmint' ),
				'400+'   => __( 'over AED 400k', 'lemonmint' ),
			),
		)
	);
}

/**
 * Handle the post.
 */
function lmf_handle_enquiry() {
	$ajax = ! empty( $_POST['ajax'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	$fail = function ( $msg, $code = 400 ) use ( $ajax ) {
		if ( $ajax ) {
			wp_send_json_error( array( 'message' => $msg ), $code );
		}
		wp_safe_redirect( add_query_arg( 'sent', '0', wp_get_referer() ? wp_get_referer() : home_url( '/contact/' ) ) );
		exit;
	};

	if ( ! isset( $_POST['lmf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lmf_nonce'] ) ), 'lmf_enquiry' ) ) {
		$fail( __( 'The form expired. Please reload the page and send it again.', 'lemonmint' ), 403 );
	}

	// honeypot: people never see this field, bots fill it
	if ( ! empty( $_POST['website'] ) ) {
		$fail( __( 'Something went wrong.', 'lemonmint' ) );
	}
	// a human takes more than three seconds to write a sentence
	$started = isset( $_POST['t'] ) ? (int) $_POST['t'] : 0;
	if ( $started && ( time() - $started ) < 3 ) {
		$fail( __( 'Something went wrong.', 'lemonmint' ) );
	}
	// one enquiry per visitor per minute
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'lmf_enq_' . md5( $ip );
	if ( get_transient( $key ) ) {
		$fail( __( 'We have your message already — give us a minute before sending another.', 'lemonmint' ), 429 );
	}

	$c     = lmf_contact_choices();
	$pick  = function ( $field, $set ) {
		$v = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return isset( $set[ $v ] ) ? $set[ $v ] : '';
	};
	$text  = function ( $field ) {
		return isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	};

	$name    = $text( 'lmf_name' );
	$email   = isset( $_POST['lmf_email'] ) ? sanitize_email( wp_unslash( $_POST['lmf_email'] ) ) : '';
	$company = $text( 'lmf_company' );
	$phone   = $text( 'lmf_phone' );
	$idea    = isset( $_POST['lmf_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lmf_message'] ) ) : '';
	$intent  = $pick( 'lmf_intent', $c['intent'] );
	$service = $pick( 'lmf_service', $c['services'] );
	$when    = $pick( 'lmf_when', $c['when'] );
	$budget  = $pick( 'lmf_budget', $c['budget'] );
	$source  = isset( $_POST['source'] ) ? esc_url_raw( wp_unslash( $_POST['source'] ) ) : '';

	if ( '' === $name || ! is_email( $email ) ) {
		$fail( __( 'We need your name and a working email to write back.', 'lemonmint' ) );
	}

	$to      = lmf_opt( 'email', 'info@lemonmintfilms.com' );
	$subject = sprintf( '[%s] %s — %s', $intent ? $intent[0] : 'Enquiry', $name, $company ? $company : $email );
	$lines   = array(
		'Intent:   ' . ( $intent ? $intent[0] : '—' ),
		'Name:     ' . $name,
		'Company:  ' . ( $company ? $company : '—' ),
		'Email:    ' . $email,
		'Phone:    ' . ( $phone ? $phone : '—' ),
		'Service:  ' . ( $service ? $service : '—' ),
		'When:     ' . ( $when ? $when : '—' ),
		'Budget:   ' . ( $budget ? $budget : '—' ),
		'',
		'The idea:',
		$idea ? $idea : '—',
		'',
		'Sent from: ' . ( $source ? $source : home_url( '/contact/' ) ),
	);
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	$ok = wp_mail( $to, $subject, implode( "\n", $lines ), $headers );

	/**
	 * Fires after an enquiry is handled — hook a CRM here.
	 *
	 * @param array $data The enquiry.
	 * @param bool  $ok   Whether the email went out.
	 */
	do_action( 'lmf_enquiry_sent', compact( 'name', 'email', 'company', 'phone', 'idea', 'intent', 'service', 'when', 'budget', 'source' ), $ok );

	if ( ! $ok ) {
		$fail( sprintf( /* translators: %s: email */ __( 'The message didn’t go through. Please write to %s directly.', 'lemonmint' ), $to ), 500 );
	}

	set_transient( $key, 1, MINUTE_IN_SECONDS );

	if ( $ajax ) {
		wp_send_json_success( array( 'name' => $name ) );
	}
	wp_safe_redirect( add_query_arg( 'sent', '1', wp_get_referer() ? wp_get_referer() : home_url( '/contact/' ) ) . '#sheet' );
	exit;
}
add_action( 'admin_post_lmf_enquiry', 'lmf_handle_enquiry' );
add_action( 'admin_post_nopriv_lmf_enquiry', 'lmf_handle_enquiry' );

/**
 * The map plate — a drawn map in brand colours.
 *
 * Warm ground, ink streets, one mint waterway, a lemon pin on the studio.
 * It is the ground the live embed sits on: if the embed is off, blocked or
 * slow, this is what people see, and it is still a map.
 */
function lmf_contact_map_plate() {
	?>
	<svg class="cx-plate" viewBox="0 0 800 900" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
		<!-- ground -->
		<rect width="800" height="900" fill="rgba(17,17,17,.075)"/>
		<!-- the creek -->
		<path d="M-20 720 C 150 660, 250 575, 430 612 C 570 640, 660 565, 820 520" fill="none" stroke="#17805C" stroke-opacity=".5" stroke-width="58" stroke-linecap="round"/>
		<!-- streets: warm, like paper showing through the city -->
		<g stroke="#F7F5EF" fill="none" stroke-linecap="square">
			<path d="M0 232 H800 M0 556 H800 M0 838 H800" stroke-width="16"/>
			<path d="M262 0 V900 M516 0 V900" stroke-width="16"/>
			<path d="M-40 828 L840 250" stroke-width="26"/>
			<path d="M-40 96 L840 430" stroke-width="10"/>
			<path d="M120 0 V232 M660 232 V556 M380 556 V900" stroke-width="7" stroke-opacity=".85"/>
		</g>
		<!-- block edges -->
		<g stroke="rgba(17,17,17,.14)" stroke-width="1" fill="none">
			<path d="M0 224 H800 M0 240 H800 M0 548 H800 M0 564 H800"/>
			<path d="M254 0 V900 M270 0 V900 M508 0 V900 M524 0 V900"/>
		</g>
		<!-- street names -->
		<g fill="rgba(17,17,17,.42)" font-family="Montserrat, Arial, sans-serif" font-size="13" letter-spacing="3" font-weight="500">
			<text x="36" y="226">SHEIKH ZAYED RD</text>
			<text x="36" y="550">AL ASAYEL ST</text>
			<text x="282" y="60" transform="rotate(90 282 60)">UMM SUQEIM ST</text>
		</g>
		<!-- the pin -->
		<g transform="translate(398 452)">
			<circle class="cx-ping" r="16" fill="#F5B82E" fill-opacity=".4"/>
			<circle r="40" fill="none" stroke="rgba(17,17,17,.45)" stroke-width="1.2" stroke-dasharray="3 7"/>
			<circle r="11" fill="#F5B82E" stroke="#111" stroke-width="2.4"/>
		</g>
	</svg>
	<?php
}
