<?php
/**
 * Director / leadership page.
 *
 * Published only where someone has real credited work behind them — a thin
 * director page is as damaging as a thin industry page.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$id   = get_the_ID();
	$role = get_post_meta( $id, '_lmf_role', true );

	$credited = get_posts(
		array(
			'post_type'      => 'lmf_project',
			'posts_per_page' => 6,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_lmf_credits',
					'value'   => get_the_title(),
					'compare' => 'LIKE',
				),
			),
		)
	);
	?>
	<section class="wrap" style="padding-top:clamp(40px,5vw,80px)">
		<?php lmf_kicker( $role ? $role : __( 'Studio', 'lemonmint' ) ); ?>
		<h1 class="h1" style="max-width:12ch"><?php the_title(); ?></h1>
		<div class="hero-still"><?php lmf_still( $id, 'lmf-hero', 1680, 720 ); ?></div>
	</section>

	<section class="sec wrap">
		<div class="split">
			<div><?php lmf_kicker( __( 'Biography', 'lemonmint' ) ); ?></div>
			<div class="entry-content"><?php the_content(); ?></div>
		</div>
	</section>

	<?php if ( $credited ) : ?>
		<section class="sec wrap" style="padding-top:0">
			<div class="sec-head"><div><?php lmf_kicker( __( 'Credits', 'lemonmint' ) ); ?><h2 class="h2"><?php esc_html_e( 'Selected work', 'lemonmint' ); ?></h2></div></div>
			<div class="grid"><?php foreach ( $credited as $c ) { lmf_tile( $c->ID ); } ?></div>
		</section>
	<?php endif; ?>

	<?php lmf_closing_cta(); ?>
	<?php
endwhile;
get_footer();
