<?php
/**
 * 404 — routes back into Work and Services rather than being a dead end.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="wrap" style="padding-top:clamp(60px,10vw,140px);padding-bottom:clamp(60px,10vw,140px)">
	<?php lmf_kicker( __( 'Error 404', 'lemonmint' ) ); ?>
	<h1 class="h1" style="max-width:12ch"><?php esc_html_e( 'That page has been cut', 'lemonmint' ); ?><span style="color:var(--mint)">.</span></h1>
	<p class="body-lg" style="margin-top:24px;max-width:46ch"><?php esc_html_e( 'The link is broken or the page has moved. The work is still where you left it.', 'lemonmint' ); ?></p>
	<div style="margin-top:32px;display:flex;gap:26px;align-items:center;flex-wrap:wrap">
		<a class="btn btn-lemon" href="<?php echo esc_url( get_post_type_archive_link( 'lmf_project' ) ); ?>"><?php esc_html_e( 'See the work', 'lemonmint' ); ?></a>
		<a class="link-u" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the homepage', 'lemonmint' ); ?></a>
	</div>
</section>
<?php get_footer(); ?>
