<?php
/**
 * Industry page — exists because agencies and government buyers search by
 * their own sector, not by service category.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$id    = get_the_ID();
	$slug  = get_post_field( 'post_name', $id );
	$needs = get_post_meta( $id, '_lmf_needs', true );
	$cons  = get_post_meta( $id, '_lmf_considerations', true );

	$work = get_posts(
		array(
			'post_type'      => 'lmf_project',
			'posts_per_page' => 3,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'lmf_industry_cat',
					'field'    => 'slug',
					'terms'    => $slug,
				),
			),
		)
	);
	?>
	<section class="wrap" style="padding-top:clamp(40px,5vw,80px)">
		<?php lmf_kicker( __( 'Industry · Dubai & GCC', 'lemonmint' ) ); ?>
		<h1 class="h1" style="max-width:13ch"><?php the_title(); ?></h1>
		<p class="body-lg" style="margin-top:24px;max-width:50ch"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<div class="hero-still"><?php lmf_still( $id, 'lmf-hero', 1680, 720 ); ?></div>
	</section>

	<?php if ( $needs ) : ?>
		<section class="sec wrap">
			<div class="split">
				<div><?php lmf_kicker( __( 'What this sector needs', 'lemonmint' ) ); ?></div>
				<div><p class="body-lg"><?php echo esc_html( $needs ); ?></p></div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $work ) : ?>
		<section class="sec wrap" style="padding-top:0">
			<div class="sec-head">
				<div><?php lmf_kicker( __( 'Proof', 'lemonmint' ) ); ?><h2 class="h2"><?php esc_html_e( 'Selected work', 'lemonmint' ); ?></h2></div>
				<a class="link-u" href="<?php echo esc_url( add_query_arg( 'ind', $slug, get_post_type_archive_link( 'lmf_project' ) ) ); ?>"><?php esc_html_e( 'Filter all work', 'lemonmint' ); ?></a>
			</div>
			<div class="grid"><?php foreach ( $work as $w ) { lmf_tile( $w->ID ); } ?></div>
		</section>
	<?php endif; ?>

	<?php if ( $cons ) : ?>
		<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'Production considerations', 'lemonmint' ) ); ?></div>
				<div><p class="body-lg"><?php echo esc_html( $cons ); ?></p></div>
			</div>
		</section>
	<?php endif; ?>

	<?php lmf_closing_cta(); ?>
	<?php
endwhile;
get_footer();
