<?php
/**
 * Generic page — Studio, Contact and anything else the client adds.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="wrap" style="padding-top:clamp(40px,5vw,80px)">
		<?php lmf_kicker( get_bloginfo( 'name' ) ); ?>
		<h1 class="h1" style="max-width:14ch"><?php the_title(); ?><span style="color:var(--mint)">.</span></h1>
	</section>

	<section class="sec wrap" style="padding-top:clamp(28px,4vw,52px)">
		<div class="split">
			<div><?php lmf_kicker( lmf_is_legal_page() ? __( 'Legal', 'lemonmint' ) : __( 'Overview', 'lemonmint' ) ); ?></div>
			<div class="entry-content"><?php the_content(); ?></div>
		</div>
	</section>
	<?php
endwhile;

// A privacy notice is not a place to sell a production.
if ( ! lmf_is_legal_page() ) {
	lmf_closing_cta();
}
get_footer();
