<?php
/**
 * Fallback template — journal listing and anything without a more specific file.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="wrap" style="padding-top:clamp(44px,6vw,90px)">
	<?php lmf_kicker( is_search() ? __( 'Search', 'lemonmint' ) : __( 'Journal', 'lemonmint' ) ); ?>
	<h1 class="h1" style="max-width:14ch">
		<?php
		if ( is_search() ) {
			/* translators: %s: search term */
			printf( esc_html__( 'Results for %s', 'lemonmint' ), esc_html( get_search_query() ) );
		} elseif ( is_archive() ) {
			echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
		} else {
			esc_html_e( 'Journal', 'lemonmint' );
		}
		?>
	</h1>
</section>

<section class="sec wrap" style="padding-top:clamp(30px,4vw,56px)">
	<?php if ( have_posts() ) : ?>
		<div class="grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<a class="tile" href="<?php the_permalink(); ?>">
					<?php lmf_still( get_the_ID(), 'lmf-tile' ); ?>
					<span class="caprow">
						<span class="t"><?php the_title(); ?></span>
						<span class="meta"><?php echo esc_html( get_the_date( 'M Y' ) ); ?></span>
					</span>
					<span class="sub"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></span>
				</a>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'class' => 'pagination', 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) ); ?>
	<?php else : ?>
		<p class="body-lg"><?php esc_html_e( 'Nothing here yet.', 'lemonmint' ); ?></p>
	<?php endif; ?>
</section>

<?php lmf_closing_cta(); ?>
<?php get_footer(); ?>
