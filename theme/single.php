<?php
/**
 * Single post — journal article.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="wrap" style="padding-top:clamp(40px,5vw,80px)">
		<?php lmf_kicker( get_the_date( 'j F Y' ) ); ?>
		<h1 class="h1" style="max-width:16ch"><?php the_title(); ?></h1>
		<div class="hero-still"><?php lmf_still( get_the_ID(), 'lmf-hero', 1680, 720 ); ?></div>

		<div class="sec split" style="padding-bottom:0">
			<div><?php lmf_kicker( __( 'Article', 'lemonmint' ) ); ?></div>
			<div class="entry-content"><?php the_content(); ?></div>
		</div>
	</article>
	<?php
endwhile;

lmf_closing_cta();
get_footer();
