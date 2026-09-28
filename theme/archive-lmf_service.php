<?php
/**
 * SERVICES — landing page.
 *
 *   1. hero      — full-bleed frame
 *   2. the reel  — every service as a frame on the film (same as homepage)
 *   3. process   — how a film gets made, as an edit timeline: a lemon
 *                  playhead runs across the five steps as you scroll
 *   4. questions — the ones every client asks
 *   5. closing CTA
 *
 * Each service still has its own page (single-lmf_service.php); the reel's
 * "Discover Service" buttons lead there.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

lmf_page_hero(
	array(
		'kicker' => __( 'Services', 'lemonmint' ),
		'title'  => __( 'Every format, one crew', 'lemonmint' ),
		'lede'   => __( 'Brand films to original series — written, shot, cut, graded and mixed by the same team. Scroll the reel to see them all.', 'lemonmint' ),
		'image'  => lmf_page_img( 'services' ),
		'cta'    => array( __( 'Start a Production', 'lemonmint' ), home_url( '/contact/?i=production' ) ),
	)
);

// 2. The reel.
lmf_services_reel();
?>

<!-- 3. PROCESS — an edit timeline -->
<section class="pg-process" data-lmf-playhead>
	<div class="wrap">
		<div class="pg-head">
			<span class="kicker"><?php esc_html_e( 'How it works', 'lemonmint' ); ?></span>
			<h2 class="h2"><?php esc_html_e( 'From brief to master', 'lemonmint' ); ?></h2>
		</div>
		<div class="pg-track">
			<span class="pg-playhead" aria-hidden="true"></span>
			<ol class="pg-steps">
				<?php foreach ( lmf_process_steps() as $i => $s ) : ?>
					<li class="pg-step">
						<span class="meta pg-tc">TC <?php echo esc_html( $s[0] ); ?></span>
						<span class="pg-step-n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<h3 class="pg-step-t"><?php echo esc_html( $s[1] ); ?></h3>
						<p><?php echo esc_html( $s[2] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>

<!-- 4. QUESTIONS -->
<section class="pg-faq">
	<div class="wrap">
		<div class="split">
			<div><span class="kicker"><?php esc_html_e( 'Questions', 'lemonmint' ); ?></span></div>
			<div class="faq">
				<?php foreach ( lmf_services_faq() as $q ) : ?>
					<details>
						<summary><?php echo esc_html( $q[0] ); ?></summary>
						<p><?php echo esc_html( $q[1] ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<?php
lmf_closing_cta();
get_footer();
