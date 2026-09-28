<?php
/**
 * ABOUT — who Lemon Mint is.
 *
 *   1. hero            — full-bleed frame, the headline registers in
 *   2. manifesto       — the story; words light up as you read down
 *   3. since 2023      — four milestones on a timeline
 *   4. people          — the crew, portraits in film frames
 *   5. why us          — the Studio block from the homepage, reused
 *   6. closing CTA
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();
$c = lmf_about_content();

lmf_page_hero(
	array(
		'kicker' => __( 'About', 'lemonmint' ),
		'title'  => __( 'A production house built like a film crew', 'lemonmint' ),
		'lede'   => __( 'Founded in Dubai in 2023. Brand films, commercials, documentary, series and podcasts — one team, from the first call to the final master.', 'lemonmint' ),
		'image'  => lmf_page_img( 'about' ),
	)
);
?>

<!-- 2. MANIFESTO -->
<section class="pg-manifesto" data-lmf-light>
	<div class="wrap">
		<span class="kicker"><?php esc_html_e( 'Why we exist', 'lemonmint' ); ?></span>
		<p class="pg-manifesto-text">
			<?php
			foreach ( preg_split( '/\s+/', trim( $c['manifesto'] ) ) as $i => $w ) {
				echo ( $i ? ' ' : '' ) . '<span>' . esc_html( $w ) . '</span>';
			}
			?>
		</p>
	</div>
</section>

<!-- 3. SINCE 2023 -->
<section class="pg-timeline">
	<div class="wrap">
		<div class="pg-head">
			<span class="kicker"><?php esc_html_e( 'Since 2023', 'lemonmint' ); ?></span>
			<h2 class="h2"><?php esc_html_e( 'The story so far', 'lemonmint' ); ?></h2>
		</div>
		<ol class="pg-years">
			<?php foreach ( $c['timeline'] as $t ) : ?>
				<li>
					<span class="pg-year"><?php echo esc_html( $t[0] ); ?></span>
					<h3 class="pg-year-t"><?php echo esc_html( $t[1] ); ?></h3>
					<p><?php echo esc_html( $t[2] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- 4. PEOPLE -->
<section class="pg-people">
	<div class="wrap">
		<div class="pg-head">
			<span class="kicker"><?php esc_html_e( 'The crew', 'lemonmint' ); ?></span>
			<h2 class="h2"><?php esc_html_e( 'Who you’ll work with', 'lemonmint' ); ?></h2>
		</div>
		<ul class="pg-people-grid">
			<?php foreach ( lmf_about_people() as $p ) : ?>
				<li class="pg-person">
					<?php if ( $p[3] ) : ?><a href="<?php echo esc_url( $p[3] ); ?>"><?php endif; ?>
					<figure class="pg-frame">
						<?php if ( $p[2] ) : ?>
							<img src="<?php echo esc_url( $p[2] ); ?>" alt="" loading="lazy" decoding="async">
						<?php else : ?>
							<span class="pg-frame-empty"><span class="meta"><?php esc_html_e( 'Portrait to come', 'lemonmint' ); ?></span></span>
						<?php endif; ?>
					</figure>
					<span class="pg-person-n"><?php echo esc_html( $p[0] ); ?></span>
					<span class="meta"><?php echo esc_html( $p[1] ); ?></span>
					<?php if ( $p[3] ) : ?></a><?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php
// 5. Why us — the same block as the homepage.
lmf_studio_section();

lmf_closing_cta();
get_footer();
