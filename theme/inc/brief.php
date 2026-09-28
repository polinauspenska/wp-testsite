<?php
/**
 * THE BRIEF — "Start a production" as a popup.
 *
 * Every "Start a production" link on the site (header, menu, closing CTA,
 * project pages — anything pointing at /contact/?i=production, or carrying
 * data-brief) opens this dialog instead of leaving the page. Without JS the
 * links still go to the contact page, so nothing is lost.
 *
 * Left: a clapperboard slate that fills itself in as the client answers.
 * Right: four short scenes — what, when & budget, the story, who.
 * It posts to the same handler as the contact page (inc/contact.php), so the
 * email, the spam checks and the 'lmf_enquiry_sent' hook are shared.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the popup runs on this request. The contact page's CTA opens it
 * too — that page is one screen of details, the brief lives here.
 *
 * @return bool
 */
function lmf_brief_enabled() {
	if ( '' !== apply_filters( 'lmf_contact_form_shortcode', '' ) ) {
		return false; // the client uses a form plugin — send people to it
	}
	return (bool) apply_filters( 'lmf_brief_enabled', true );
}

/**
 * Every word in the popup, in one place. Filtered, so ACF (Lemon Mint →
 * Brief popup) can rewrite any of it without touching the markup.
 *
 * @return array
 */
function lmf_brief_copy() {
	return apply_filters(
		'lmf_brief_copy',
		array(
			'q1'          => __( 'What are we making?', 'lemonmint' ),
			'hint1'       => __( 'Pick the closest — we’ll shape it together.', 'lemonmint' ),
			'q2'          => __( 'When do we roll?', 'lemonmint' ),
			'q2b'         => __( 'And the budget?', 'lemonmint' ),
			'q3'          => __( 'Tell us the story.', 'lemonmint' ),
			'hint3'       => __( 'What it’s for, who it’s for, anything you already have. A few lines is plenty.', 'lemonmint' ),
			'q4'          => __( 'Who’s calling action?', 'lemonmint' ),
			'next'        => __( 'Next scene', 'lemonmint' ),
			'send'        => __( 'Roll camera', 'lemonmint' ),
			'skip'        => __( 'Optional — skip if unsure', 'lemonmint' ),
			/* translators: %s: reply-time promise */
			'side'        => __( 'A producer reads every brief. %s.', 'lemonmint' ),
			'done_kicker' => __( 'Brief received', 'lemonmint' ),
			'done_title'  => __( 'That’s a wrap', 'lemonmint' ),
			/* translators: %s: reply-time promise */
			'done_text'   => __( 'Your brief is with a producer. %s — usually sooner. Keep an eye on your inbox.', 'lemonmint' ),
		)
	);
}

/**
 * Print the dialog (hidden) at the end of the page.
 */
function lmf_brief_dialog() {
	if ( ! lmf_brief_enabled() ) {
		return;
	}
	$c        = lmf_contact_choices();
	$t        = lmf_brief_copy();
	$response = lmf_opt( 'response', 'Under 4 working hours' );
	$endpoint = admin_url( 'admin-post.php' );
	$scenes   = 4;
	?>
	<div class="bf" id="lmf-brief" role="dialog" aria-modal="true" aria-labelledby="bf-title" hidden>
		<div class="bf-veil" data-bf-close></div>

		<div class="bf-panel">
			<button class="bf-x" type="button" data-bf-close aria-label="<?php esc_attr_e( 'Close', 'lemonmint' ); ?>">
				<span class="bf-x-t"><?php esc_html_e( 'Close', 'lemonmint' ); ?></span>
				<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M4 4l12 12M16 4L4 16" stroke="currentColor" stroke-width="1.6" fill="none"/></svg>
			</button>

			<!-- the slate: fills itself in as they answer -->
			<aside class="bf-side surface-mint" data-cursor-tone="light" aria-hidden="true">
				<span class="bf-side-k"><?php esc_html_e( 'Production slate', 'lemonmint' ); ?></span>

				<div class="bf-slate">
					<div class="bf-stick"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
					<div class="bf-hinge"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
					<div class="bf-board">
						<div class="bf-row bf-row--wide">
							<b><?php esc_html_e( 'Production', 'lemonmint' ); ?></b>
							<span data-slate="service" data-empty="—"><?php echo '—'; ?></span>
						</div>
						<div class="bf-row bf-row--3">
							<div><b><?php esc_html_e( 'Scene', 'lemonmint' ); ?></b><span data-slate="scene">01</span></div>
							<div><b><?php esc_html_e( 'Take', 'lemonmint' ); ?></b><span>01</span></div>
							<div><b><?php esc_html_e( 'Roll', 'lemonmint' ); ?></b><span data-slate="roll">A</span></div>
						</div>
						<div class="bf-row bf-row--2">
							<div><b><?php esc_html_e( 'Shoot', 'lemonmint' ); ?></b><span data-slate="when" data-empty="TBC">TBC</span></div>
							<div><b><?php esc_html_e( 'Budget', 'lemonmint' ); ?></b><span data-slate="budget" data-empty="TBC">TBC</span></div>
						</div>
						<div class="bf-row bf-row--wide">
							<b><?php esc_html_e( 'Client', 'lemonmint' ); ?></b>
							<span data-slate="client" data-empty="—">—</span>
						</div>
						<div class="bf-row bf-row--foot">
							<span><?php bloginfo( 'name' ); ?></span>
							<span data-slate="date"></span>
						</div>
					</div>
				</div>

				<p class="bf-side-p"><?php echo esc_html( sprintf( $t['side'], $response ) ); ?></p>
			</aside>

			<!-- the form: four scenes -->
			<div class="bf-main">
				<form class="bf-form" method="post" action="<?php echo esc_url( $endpoint ); ?>" data-endpoint="<?php echo esc_url( $endpoint ); ?>" novalidate>
					<input type="hidden" name="action" value="lmf_enquiry">
					<input type="hidden" name="lmf_nonce" value="<?php echo esc_attr( wp_create_nonce( 'lmf_enquiry' ) ); ?>">
					<input type="hidden" name="t" value="<?php echo esc_attr( time() ); ?>">
					<input type="hidden" name="source" value="">
					<input type="hidden" name="lmf_intent" value="production">
					<div class="bf-hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

					<header class="bf-head">
						<span class="meta bf-count"><?php esc_html_e( 'Scene', 'lemonmint' ); ?> <span class="bf-no">01</span> / <?php echo esc_html( sprintf( '%02d', $scenes ) ); ?></span>
						<span class="bf-bars" aria-hidden="true"><?php for ( $i = 0; $i < $scenes; $i++ ) : ?><i></i><?php endfor; ?></span>
					</header>

					<div class="bf-scenes">
						<!-- 1 · what -->
						<fieldset class="bf-scene is-on" data-scene="1">
							<legend class="bf-q" id="bf-title"><?php echo esc_html( $t['q1'] ); ?></legend>
							<p class="bf-hint"><?php echo esc_html( $t['hint1'] ); ?></p>
							<div class="bf-chips bf-chips--svc">
								<?php foreach ( $c['services'] as $key => $label ) : ?>
									<label class="bf-chip"><input type="radio" name="lmf_service" value="<?php echo esc_attr( $key ); ?>" data-label="<?php echo esc_attr( 'other' === $key ? __( 'Something else', 'lemonmint' ) : $label ); ?>"><span><?php echo esc_html( 'other' === $key ? __( 'Something else', 'lemonmint' ) : $label ); ?></span></label>
								<?php endforeach; ?>
							</div>
						</fieldset>

						<!-- 2 · when & budget -->
						<fieldset class="bf-scene" data-scene="2" hidden>
							<legend class="bf-q"><?php echo esc_html( $t['q2'] ); ?></legend>
							<div class="bf-chips">
								<?php foreach ( $c['when'] as $key => $label ) : ?>
									<label class="bf-chip"><input type="radio" name="lmf_when" value="<?php echo esc_attr( $key ); ?>" data-label="<?php echo esc_attr( ucfirst( $label ) ); ?>"><span><?php echo esc_html( ucfirst( $label ) ); ?></span></label>
								<?php endforeach; ?>
							</div>
							<p class="bf-q bf-q--2"><?php echo esc_html( $t['q2b'] ); ?></p>
							<div class="bf-chips" role="radiogroup" aria-label="<?php esc_attr_e( 'Budget', 'lemonmint' ); ?>">
								<?php foreach ( $c['budget'] as $key => $label ) : ?>
									<label class="bf-chip"><input type="radio" name="lmf_budget" value="<?php echo esc_attr( $key ); ?>" data-label="<?php echo esc_attr( ucfirst( $label ) ); ?>"><span><?php echo esc_html( ucfirst( $label ) ); ?></span></label>
								<?php endforeach; ?>
							</div>
						</fieldset>

						<!-- 3 · the story -->
						<fieldset class="bf-scene" data-scene="3" hidden>
							<legend class="bf-q"><?php echo esc_html( $t['q3'] ); ?></legend>
							<p class="bf-hint"><?php echo esc_html( $t['hint3'] ); ?></p>
							<label class="bf-area">
								<span class="screen-reader-text"><?php esc_html_e( 'Your idea', 'lemonmint' ); ?></span>
								<textarea name="lmf_message" rows="5" maxlength="2000" placeholder="<?php esc_attr_e( 'We’re launching…', 'lemonmint' ); ?>"></textarea>
								<span class="bf-len meta"><span>0</span> / 2000</span>
							</label>
						</fieldset>

						<!-- 4 · who -->
						<fieldset class="bf-scene" data-scene="4" hidden>
							<legend class="bf-q"><?php echo esc_html( $t['q4'] ); ?></legend>
							<div class="bf-fields">
								<label class="bf-f"><span class="bf-l"><?php esc_html_e( 'Your name', 'lemonmint' ); ?> <em>*</em></span><input type="text" name="lmf_name" autocomplete="name" required></label>
								<label class="bf-f"><span class="bf-l"><?php esc_html_e( 'Company', 'lemonmint' ); ?></span><input type="text" name="lmf_company" autocomplete="organization"></label>
								<label class="bf-f"><span class="bf-l"><?php esc_html_e( 'Email', 'lemonmint' ); ?> <em>*</em></span><input type="email" name="lmf_email" autocomplete="email" required></label>
								<label class="bf-f"><span class="bf-l"><?php esc_html_e( 'Phone / WhatsApp', 'lemonmint' ); ?></span><input type="tel" name="lmf_phone" autocomplete="tel" placeholder="+971"></label>
							</div>
						</fieldset>
					</div>

					<p class="bf-error" role="alert" hidden></p>

					<footer class="bf-foot">
						<button class="bf-back link-u" type="button" hidden><span aria-hidden="true">&larr;</span> <?php esc_html_e( 'Back', 'lemonmint' ); ?></button>
						<span class="bf-skip meta" hidden><?php echo esc_html( $t['skip'] ); ?></span>
						<button class="btn btn-lemon bf-next" type="button"><span><?php echo esc_html( $t['next'] ); ?></span> <span aria-hidden="true">&rarr;</span></button>
						<button class="btn btn-lemon bf-send" type="submit" hidden><span class="bf-send-t" data-busy="<?php esc_attr_e( 'Rolling…', 'lemonmint' ); ?>"><?php echo esc_html( $t['send'] ); ?></span> <span aria-hidden="true">&rarr;</span></button>
					</footer>
				</form>

				<div class="bf-done" role="status" aria-live="polite" hidden>
					<span class="kicker"><?php echo esc_html( $t['done_kicker'] ); ?></span>
					<p class="bf-done-t"><?php echo esc_html( $t['done_title'] ); ?><span class="bf-done-name"></span><span class="bf-dot">.</span></p>
					<p class="bf-done-p"><?php echo esc_html( sprintf( $t['done_text'], $response ) ); ?></p>
					<div class="bf-done-next">
						<button class="btn btn-line" type="button" data-bf-close><?php esc_html_e( 'Back to the site', 'lemonmint' ); ?></button>
						<a class="link-u" href="<?php echo esc_url( get_post_type_archive_link( 'lmf_project' ) ); ?>"><?php esc_html_e( 'Meanwhile, see our work', 'lemonmint' ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'lmf_brief_dialog', 5 );

/**
 * Assets — everywhere the dialog is printed.
 */
function lmf_brief_assets() {
	if ( ! lmf_brief_enabled() ) {
		return;
	}
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'lmf-brief', $uri . '/assets/css/brief.css', array( 'lmf-style' ), LMF_VERSION );
	wp_enqueue_script( 'lmf-brief', $uri . '/assets/js/brief.js', array(), LMF_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'lmf_brief_assets', 20 );
