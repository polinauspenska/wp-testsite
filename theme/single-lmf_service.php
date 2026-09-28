<?php
/**
 * Service page — one per keyword cluster. Never split into thin sub-pages.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$id      = get_the_ID();
	$keyword = get_post_meta( $id, '_lmf_keyword', true );
	$cta     = get_post_meta( $id, '_lmf_cta', true );
	$cta     = $cta ? $cta : __( 'Start a Production', 'lemonmint' );
	$diffs   = lmf_get_pairs( $id, '_lmf_diffs' );
	$kit     = lmf_get_pairs( $id, '_lmf_kit' );
	$faq     = lmf_get_faq( $id );
	$work    = lmf_service_projects( $id, 3 );
	$index   = 0;

	foreach ( get_posts( array( 'post_type' => 'lmf_service', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $i => $s ) {
		if ( (int) $s->ID === (int) $id ) {
			$index = $i + 1;
		}
	}
	?>

	<section class="wrap" style="padding-top:clamp(40px,5vw,80px)">
		<?php lmf_kicker( sprintf( '%02d — %s', $index, __( 'Service · Dubai & GCC', 'lemonmint' ) ) ); ?>
		<h1 class="h1" style="max-width:12ch"><?php the_title(); ?></h1>
		<p class="body-lg" style="margin-top:24px;max-width:48ch"><?php echo esc_html( get_the_excerpt() ); ?></p>

		<div style="margin-top:32px;display:flex;gap:26px;align-items:center;flex-wrap:wrap">
			<a class="btn btn-lemon" href="<?php echo esc_url( add_query_arg( array( 'i' => 'production', 'svc' => get_post_field( 'post_name', $id ) ), home_url( '/contact/' ) ) ); ?>"><?php echo esc_html( $cta ); ?></a>
			<button class="link-u" data-reel><?php esc_html_e( 'Watch the reel', 'lemonmint' ); ?></button>
		</div>

		<hr class="rule" style="margin-top:clamp(26px,3vw,44px)">
		<div class="caprow" style="padding-top:14px">
			<span class="meta"><?php echo $keyword ? esc_html( 'Target — ' . $keyword ) : ''; ?></span>
			<span class="meta"><?php echo esc_html( 'LMF / ' . strtoupper( substr( get_post_field( 'post_name', $id ), 0, 3 ) ) ); ?></span>
		</div>

		<div class="hero-still"><?php lmf_still( $id, 'lmf-hero', 1680, 720 ); ?></div>
	</section>

	<?php if ( $diffs ) : ?>
		<section class="sec wrap">
			<div class="split">
				<div><?php lmf_kicker( __( 'Why us', 'lemonmint' ) ); ?></div>
				<div>
					<h2 class="h2" style="max-width:16ch;margin-bottom:32px"><?php esc_html_e( 'Why brands choose this', 'lemonmint' ); ?></h2>
					<div class="numlist">
						<?php foreach ( $diffs as $i => $d ) : ?>
							<div>
								<span class="n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
								<span><h4><?php echo esc_html( $d['k'] ); ?></h4><p><?php echo esc_html( $d['v'] ); ?></p></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( get_the_content() ) : ?>
		<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'The offer', 'lemonmint' ) ); ?></div>
				<div class="entry-content"><?php the_content(); ?></div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $work ) : ?>
		<section class="sec wrap" style="padding-top:0">
			<div class="sec-head">
				<div><?php lmf_kicker( __( 'Proof', 'lemonmint' ) ); ?><h2 class="h2"><?php esc_html_e( 'Selected work', 'lemonmint' ); ?></h2></div>
				<a class="link-u" href="<?php echo esc_url( add_query_arg( 'svc', get_post_field( 'post_name', $id ), get_post_type_archive_link( 'lmf_project' ) ) ); ?>"><?php esc_html_e( 'Filter all work', 'lemonmint' ); ?></a>
			</div>
			<div class="grid"><?php foreach ( $work as $w ) { lmf_tile( $w->ID ); } ?></div>
		</section>
	<?php endif; ?>

	<?php if ( $kit ) : ?>
		<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div>
					<?php lmf_kicker( __( 'Equipment', 'lemonmint' ) ); ?>
					<p class="xs" style="margin-top:12px;max-width:26ch"><?php esc_html_e( 'Published because production managers ask first and no brand-facing competitor answers.', 'lemonmint' ); ?></p>
				</div>
				<div>
					<div class="facts">
						<?php foreach ( $kit as $k ) : ?>
							<div><span class="k"><?php echo esc_html( $k['k'] ); ?></span><span class="v"><?php echo esc_html( $k['v'] ); ?></span></div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $faq ) : ?>
		<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'Questions', 'lemonmint' ) ); ?></div>
				<div>
					<div class="faq">
						<?php foreach ( $faq as $i => $f ) : ?>
							<details <?php echo 0 === $i ? 'open' : ''; ?>>
								<summary><?php echo esc_html( $f['q'] ); ?></summary>
								<div class="a"><?php echo esc_html( $f['a'] ); ?></div>
							</details>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php lmf_closing_cta(); ?>
	<?php
endwhile;
get_footer();
