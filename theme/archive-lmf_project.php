<?php
/**
 * Work hub — filterable. Three axes: service, industry, format.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="wrap" style="padding-top:clamp(44px,6vw,90px);padding-bottom:clamp(28px,4vw,52px)">
	<?php lmf_kicker( __( 'Work', 'lemonmint' ) ); ?>
	<h1 class="h1" style="max-width:13ch"><?php esc_html_e( 'Every project is a page', 'lemonmint' ); ?><span style="color:var(--mint)">.</span></h1>
	<p class="body-lg" style="margin-top:24px;max-width:52ch"><?php esc_html_e( 'Challenge, direction, process and credits — the context that turns a finished film into something a buyer can judge.', 'lemonmint' ); ?></p>
</section>

<section class="wrap" style="padding-bottom:clamp(56px,8vw,120px)">
	<div class="filters">
		<?php
		lmf_filter_group( 'svc', 'lmf_service_cat', __( 'Service', 'lemonmint' ) );
		lmf_filter_group( 'ind', 'lmf_industry_cat', __( 'Industry', 'lemonmint' ) );
		lmf_filter_group( 'fmt', 'lmf_format', __( 'Format', 'lemonmint' ) );

		if ( ! empty( $_GET['svc'] ) || ! empty( $_GET['ind'] ) || ! empty( $_GET['fmt'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			printf(
				'<a class="fchip" href="%s">%s &#10005;</a>',
				esc_url( get_post_type_archive_link( 'lmf_project' ) ),
				esc_html__( 'Clear', 'lemonmint' )
			);
		}

		global $wp_query;
		printf( '<span class="fcount">%02d</span>', (int) $wp_query->found_posts );
		?>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="grid">
			<?php
			while ( have_posts() ) {
				the_post();
				lmf_tile( get_the_ID() );
			}
			?>
		</div>
		<?php
		the_posts_pagination(
			array(
				'class'     => 'pagination',
				'mid_size'  => 2,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
			)
		);
		?>
	<?php else : ?>
		<p class="body-lg" style="padding:50px 0">
			<?php esc_html_e( 'Nothing matches that combination.', 'lemonmint' ); ?>
			<a class="link-u" href="<?php echo esc_url( get_post_type_archive_link( 'lmf_project' ) ); ?>"><?php esc_html_e( 'Clear filters', 'lemonmint' ); ?></a>
		</p>
	<?php endif; ?>
</section>

<?php lmf_closing_cta(); ?>
<?php get_footer(); ?>
