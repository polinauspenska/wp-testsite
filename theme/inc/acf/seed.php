<?php
/**
 * ACF — first fill.
 *
 * The fields fall back to the theme's copy when they are empty, so the site
 * always looks right. The admin, though, would look half-built: every list
 * (the counters, the sectors, the eight wedges, the steps, the quotes) would
 * open as an empty repeater with no hint of what is on the page.
 *
 * So the first time ACF Pro is active, every field is written once with
 * exactly what the site is showing. From then on the client edits real
 * sentences instead of blank boxes, and nothing on the front end moves.
 *
 * It runs once. Fields already filled are never overwritten — including on
 * the manual re-run from Lemon Mint → Content, which only tops up what is
 * still empty.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * The version of the seed. Bump it to hand a new section's copy to sites
 * that were set up before that section existed.
 */
const LMF_SEED = '1.0';

/**
 * Write one field only if it is still empty.
 *
 * @param string $name  Field name.
 * @param mixed  $value Value.
 * @param mixed  $where 'option' or a post ID.
 * @param bool   $force Overwrite a filled field.
 */
function lmf_seed_field( $name, $value, $where = 'option', $force = false ) {
	if ( '' === $value || null === $value || array() === $value ) {
		return;
	}
	if ( ! $force ) {
		$current = get_field( $name, $where );
		if ( is_array( $current ) ? ! empty( $current ) : ( '' !== $current && null !== $current ) ) {
			return;
		}
	}
	update_field( $name, $value, $where );
}

/**
 * Fill everything.
 *
 * @param bool $force Overwrite fields that already have content.
 */
function lmf_acf_seed( $force = false ) {
	if ( ! lmf_acf_pro() || ! function_exists( 'update_field' ) ) {
		return;
	}

	/* ---------------------------------------------------------------
	   Studio details — from the Customizer if the site has them, from
	   the theme's defaults otherwise.
	   --------------------------------------------------------------- */
	$studio = array(
		'lmf_email'       => 'info@lemonmintfilms.com',
		'lmf_phone'       => '+971 4 332 3054',
		'lmf_whatsapp'    => '',
		'lmf_address'     => 'Warehouse 28, Al Quoz Industrial Third',
		'lmf_city'        => 'Dubai, United Arab Emirates',
		'lmf_hours'       => 'Mon–Fri · 09:00–18:00',
		'lmf_response'    => 'Under 4 working hours',
		'lmf_hero_line'   => 'Built for the screen, not the scroll',
		'lmf_studio_line' => 'We make films for people who have something specific to say, and forty seconds to say it.',
		'lmf_reel_url'    => '',
		'lmf_nav_cta'     => 'Start a Production',
	);
	foreach ( $studio as $key => $default ) {
		lmf_seed_field( $key, get_theme_mod( $key, $default ), 'option', $force );
	}
	if ( $force || null === get_field( 'lmf_map_embed', 'option' ) ) {
		update_field( 'lmf_map_embed', true, 'option' );
	}

	/* ---------------------------------------------------------------
	   Homepage
	   --------------------------------------------------------------- */
	/*
	 * The content functions themselves: with every field still empty the
	 * ACF overlay hands back the theme's own words, so this is exactly what
	 * the page is showing right now.
	 */
	$c = lmf_home_content();

	$p = $c['promise'];
	lmf_seed_field( 'promise_kicker', $p['kicker'], 'option', $force );
	lmf_seed_field( 'promise_headline', $p['headline'], 'option', $force );
	lmf_seed_field( 'promise_text', $p['text'], 'option', $force );
	lmf_seed_field( 'promise_figures_kicker', $p['figures_kicker'], 'option', $force );
	lmf_seed_field( 'sectors_label', $p['sectors_label'], 'option', $force );

	$figures = array();
	foreach ( $p['figures'] as $f ) {
		$figures[] = array( 'value' => $f['value'], 'suffix' => $f['suffix'], 'label' => $f['label'] );
	}
	lmf_seed_field( 'promise_figures', $figures, 'option', $force );

	$sectors = array();
	foreach ( $p['sectors'] as $s ) {
		$sectors[] = array( 'name' => $s );
	}
	lmf_seed_field( 'sectors', $sectors, 'option', $force );

	$w = $c['cases'];
	lmf_seed_field( 'cases_kicker', $w['kicker'], 'option', $force );
	lmf_seed_field( 'cases_headline', $w['headline'], 'option', $force );
	lmf_seed_field( 'cases_more_label', isset( $w['more'][0] ) ? $w['more'][0] : '', 'option', $force );
	lmf_seed_field( 'cases_more_url', isset( $w['more'][1] ) ? $w['more'][1] : '', 'option', $force );
	foreach ( array( 'mp4', 'webm', 'poster' ) as $k ) {
		lmf_seed_field( 'atlas_' . $k, isset( $w['atlas'][ $k ] ) ? $w['atlas'][ $k ] : '', 'option', $force );
		lmf_seed_field( 'dive_' . $k, isset( $w['dive'][ $k ] ) ? $w['dive'][ $k ] : '', 'option', $force );
	}
	lmf_seed_field( 'atlas_cols', isset( $w['atlas']['cols'] ) ? $w['atlas']['cols'] : 4, 'option', $force );
	lmf_seed_field( 'atlas_rows', isset( $w['atlas']['rows'] ) ? $w['atlas']['rows'] : 2, 'option', $force );

	$wedges = array();
	foreach ( $w['items'] as $i ) {
		$wedges[] = array(
			'project' => '',
			'title'   => isset( $i['title'] ) ? $i['title'] : '',
			'code'    => isset( $i['code'] ) ? $i['code'] : '',
			'client'  => isset( $i['client'] ) ? $i['client'] : '',
			'type'    => isset( $i['type'] ) ? $i['type'] : '',
			'result'  => isset( $i['result'] ) ? $i['result'] : '',
			'outcome' => isset( $i['outcome'] ) ? $i['outcome'] : '',
			'url'     => isset( $i['url'] ) ? $i['url'] : '',
			'video'   => isset( $i['video'] ) ? $i['video'] : '',
		);
	}
	lmf_seed_field( 'cases_items', $wedges, 'option', $force );

	$pr = $c['process'];
	lmf_seed_field( 'process_kicker', $pr['kicker'], 'option', $force );
	lmf_seed_field( 'process_headline', $pr['headline'], 'option', $force );
	lmf_seed_field( 'process_promise', $pr['promise'], 'option', $force );
	lmf_seed_field( 'process_cta_label', isset( $pr['cta'][0] ) ? $pr['cta'][0] : '', 'option', $force );
	lmf_seed_field( 'process_cta_url', isset( $pr['cta'][1] ) ? $pr['cta'][1] : '', 'option', $force );

	$steps = array();
	foreach ( $pr['steps'] as $s ) {
		$steps[] = array( 'when' => $s[0], 'title' => $s[1], 'text' => $s[2] );
	}
	lmf_seed_field( 'process_steps', $steps, 'option', $force );

	$v = $c['voices'];
	lmf_seed_field( 'voices_kicker', $v['kicker'], 'option', $force );
	lmf_seed_field( 'voices_headline', $v['headline'], 'option', $force );

	$quotes = array();
	foreach ( $v['items'] as $q ) {
		$quotes[] = array(
			'quote'   => isset( $q[0] ) ? $q[0] : '',
			'who'     => isset( $q[1] ) ? $q[1] : '',
			'org'     => isset( $q[2] ) ? $q[2] : '',
			'project' => isset( $q[3] ) ? $q[3] : '',
			'image'   => isset( $q[4] ) ? lmf_seed_attachment( $q[4] ) : '',
			'url'     => isset( $q[5] ) ? $q[5] : '',
		);
	}
	lmf_seed_field( 'voices_items', $quotes, 'option', $force );

	/* ---- the closing countdown and the mint band ---- */
	lmf_seed_field( 'cta_kicker', __( 'Contact', 'lemonmint' ), 'option', $force );
	lmf_seed_field( 'cta_headline', __( 'Let’s shoot something', 'lemonmint' ), 'option', $force );
	lmf_seed_field( 'cta_caption', __( 'Roll camera', 'lemonmint' ), 'option', $force );
	lmf_seed_field(
		'cta_routes',
		array(
			array( 'label' => __( 'Request a quote', 'lemonmint' ), 'line' => __( 'You need a number for the budget.', 'lemonmint' ), 'intent' => 'quote' ),
			array( 'label' => __( 'Book a discovery call', 'lemonmint' ), 'line' => __( 'The idea is still taking shape.', 'lemonmint' ), 'intent' => 'call' ),
			array( 'label' => __( 'Start a production', 'lemonmint' ), 'line' => __( 'You have a brief and a date.', 'lemonmint' ), 'intent' => 'production' ),
		),
		'option',
		$force
	);
	lmf_seed_field( 'reel_kicker', __( 'Showreel 2026', 'lemonmint' ), 'option', $force );
	lmf_seed_field( 'reel_headline', __( 'Ninety seconds of the last year.', 'lemonmint' ), 'option', $force );
	lmf_seed_field( 'reel_text', __( 'Brand films, commercials, documentary and series — cut from work shot and finished in-house.', 'lemonmint' ), 'option', $force );
	lmf_seed_field( 'reel_button', __( 'Watch the reel', 'lemonmint' ), 'option', $force );

	/* ---------------------------------------------------------------
	   The brief popup
	   --------------------------------------------------------------- */
	$t = lmf_brief_copy();
	foreach ( $t as $k => $line ) {
		lmf_seed_field( 'brief_' . $k, $line, 'option', $force );
	}
	lmf_seed_field( 'brief_other_label', __( 'something else', 'lemonmint' ), 'option', $force );

	$choices = lmf_contact_choices();
	foreach ( array( 'when', 'budget' ) as $set ) {
		$rows = array();
		foreach ( $choices[ $set ] as $key => $label ) {
			$rows[] = array( 'label' => $label, 'key' => $key );
		}
		lmf_seed_field( 'brief_' . $set, $rows, 'option', $force );
	}

	/* ---------------------------------------------------------------
	   Services page
	   --------------------------------------------------------------- */
	$svc = lmf_process_steps();
	$rows = array();
	foreach ( $svc as $s ) {
		$rows[] = array( 'tc' => $s[0], 'title' => $s[1], 'text' => $s[2] );
	}
	lmf_seed_field( 'svc_process', $rows, 'option', $force );

	$faq  = lmf_services_faq();
	$rows = array();
	foreach ( $faq as $q ) {
		$rows[] = array( 'q' => $q[0], 'a' => $q[1] );
	}
	lmf_seed_field( 'svc_faq', $rows, 'option', $force );

	/* ---------------------------------------------------------------
	   Pages
	   --------------------------------------------------------------- */
	// A page that does not exist yet (the site may be seeded before the demo
	// content is installed) leaves the seed unfinished, so it runs again on
	// the next admin load rather than marking itself done.
	$pending = false;

	$about = lmf_acf_page_id( 'about' );
	if ( $about ) {
		$a = lmf_about_content();
		lmf_seed_field( 'about_manifesto', $a['manifesto'], $about, $force );
		$rows = array();
		foreach ( $a['timeline'] as $r ) {
			$rows[] = array( 'year' => $r[0], 'title' => $r[1], 'text' => $r[2] );
		}
		lmf_seed_field( 'about_timeline', $rows, $about, $force );
	} else {
		$pending = true;
	}

	$studio_page = lmf_acf_page_id( 'studio' );
	if ( $studio_page ) {
		$s    = lmf_studio_page_content();
		$rows = array();
		foreach ( $s['spaces'] as $r ) {
			$rows[] = array( 'title' => $r[0], 'meta' => $r[1], 'text' => $r[2], 'image' => lmf_seed_attachment( $r[3] ) );
		}
		lmf_seed_field( 'studio_spaces', $rows, $studio_page, $force );

		$rows = array();
		foreach ( $s['kit'] as $r ) {
			$rows[] = array( 'title' => $r[0], 'a' => $r[1], 'b' => $r[2] );
		}
		lmf_seed_field( 'studio_kit', $rows, $studio_page, $force );
	} else {
		$pending = true;
	}

	$contact = lmf_acf_page_id( 'contact' );
	if ( $contact ) {
		$cx = array(
			'cx_kicker'           => __( 'Contact us', 'lemonmint' ),
			'cx_line1'            => __( 'Let’s shoot', 'lemonmint' ),
			'cx_line2'            => __( 'something.', 'lemonmint' ),
			'cx_lede'             => __( 'A producer — not a form robot — reads every brief and writes back.', 'lemonmint' ),
			'cx_label_write'      => __( 'Write', 'lemonmint' ),
			'cx_label_call'       => __( 'Call', 'lemonmint' ),
			'cx_label_studio'     => __( 'Studio', 'lemonmint' ),
			'cx_label_hours'      => __( 'Hours', 'lemonmint' ),
			'cx_whatsapp_label'   => __( 'WhatsApp a producer', 'lemonmint' ),
			'cx_directions_label' => __( 'Get directions', 'lemonmint' ),
			'cx_cta'              => __( 'Start a production', 'lemonmint' ),
			'cx_card_label'       => __( 'The studio', 'lemonmint' ),
		);
		foreach ( $cx as $k => $line ) {
			lmf_seed_field( $k, $line, $contact, $force );
		}
	} else {
		$pending = true;
	}

	update_option( 'lmf_acf_seeded', $pending ? 'partial' : LMF_SEED );
}

/**
 * An image field wants an attachment, not a URL. Theme files are not in the
 * media library, so a picture that lives in the theme is left empty rather
 * than saved as a broken reference — the front end keeps showing it either
 * way, and the client replaces it with their own frame when they have one.
 *
 * @param string $url Image URL.
 * @return int|string
 */
function lmf_seed_attachment( $url ) {
	if ( ! $url || 0 === strpos( $url, get_template_directory_uri() ) ) {
		return '';
	}
	$id = attachment_url_to_postid( $url );
	return $id ? $id : '';
}

/**
 * Run it once, quietly, the first time an editor opens the admin with ACF
 * Pro active.
 */
function lmf_acf_seed_once() {
	if ( ! is_admin() || wp_doing_ajax() || ! lmf_acf_pro() ) {
		return;
	}
	if ( get_option( 'lmf_acf_seeded' ) === LMF_SEED ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	lmf_acf_seed( false );
}
add_action( 'admin_init', 'lmf_acf_seed_once', 20 );

/**
 * "Fill empty fields with the site's current text" — for a field the client
 * cleared and wants back, or after a theme update adds a section.
 */
function lmf_acf_seed_button() {
	if ( ! lmf_acf_pro() ) {
		return;
	}
	add_submenu_page(
		'lmf-settings',
		__( 'Content', 'lemonmint' ),
		__( 'Content', 'lemonmint' ),
		'edit_theme_options',
		'lmf-content',
		'lmf_acf_seed_screen'
	);
}
add_action( 'admin_menu', 'lmf_acf_seed_button', 99 );

/**
 * That screen.
 */
function lmf_acf_seed_screen() {
	$done = isset( $_GET['filled'] ) ? sanitize_key( wp_unslash( $_GET['filled'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Lemon Mint — content', 'lemonmint' ); ?></h1>
		<?php if ( $done ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Filled. Anything you had already written was left as it was.', 'lemonmint' ); ?></p></div>
		<?php endif; ?>
		<p style="max-width:44em">
			<?php esc_html_e( 'Every field on the Lemon Mint screens starts out holding the text the site is showing, so you edit real sentences instead of blank boxes. If you have emptied a field and want the original wording back — or a theme update has added a new section — this fills whatever is still empty. It never overwrites anything you have written.', 'lemonmint' ); ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'lmf_seed' ); ?>
			<input type="hidden" name="action" value="lmf_seed">
			<?php submit_button( __( 'Fill empty fields', 'lemonmint' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Handle it.
 */
function lmf_acf_seed_run() {
	if ( ! current_user_can( 'edit_theme_options' ) || ! check_admin_referer( 'lmf_seed' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'lemonmint' ) );
	}
	lmf_acf_seed( false );
	wp_safe_redirect( add_query_arg( array( 'page' => 'lmf-content', 'filled' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_lmf_seed', 'lmf_acf_seed_run' );
