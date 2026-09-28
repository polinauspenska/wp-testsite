<?php
/**
 * INDUSTRIES — landing page.
 *
 *   1. hero      — full-bleed frame
 *   2. the index — each sector as a big line; on desktop a projected still
 *                  on the right changes with the line under the cursor (or,
 *                  without a mouse, the line in the middle of the screen)
 *   3. closing CTA
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

$list = lmf_industries_list();

lmf_page_hero(
	array(
		'kicker' => __( 'Industries', 'lemonmint' ),
		'title'  => __( 'Who we make it for', 'lemonmint' ),
		'lede'   => __( 'Every sector has its own approvals, its own audience and its own clichés to avoid. These are the ones we know from the inside.', 'lemonmint' ),
		'image'  => lmf_page_img( 'industries' ),
	)
);
?>

<!-- 2. THE INDEX -->
<section class="pg-index" data-lmf-index>
	<div class="wrap pg-index-in">
		<div class="pg-index-list">
			<div class="pg-head">
				<span class="kicker"><?php echo esc_html( sprintf( _n( '%d sector', '%d sectors', count( $list ), 'lemonmint' ), count( $list ) ) ); ?></span>
			</div>
			<ol>
				<?php foreach ( $list as $i => $it ) : ?>
					<li class="pg-ix" data-img="<?php echo esc_url( $it[3] ); ?>">
						<a href="<?php echo esc_url( $it[2] ); ?>">
							<span class="meta pg-ix-n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
							<span class="pg-ix-t"><?php echo esc_html( $it[0] ); ?></span>
							<span class="pg-ix-d"><?php echo esc_html( $it[1] ); ?></span>
							<span class="pg-ix-go" aria-hidden="true">&rarr;</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
		<!-- the projector: a still that follows the line you are on -->
		<figure class="pg-projector" aria-hidden="true">
			<div class="pg-projector-frame">
				<?php foreach ( $list as $i => $it ) : ?>
					<img src="<?php echo esc_url( $it[3] ); ?>" alt="" loading="lazy" decoding="async" class="<?php echo 0 === $i ? 'is-on' : ''; ?>">
				<?php endforeach; ?>
			</div>
			<figcaption class="meta pg-projector-cap"><span class="pg-projector-name"><?php echo esc_html( $list ? $list[0][0] : '' ); ?></span></figcaption>
		</figure>
	</div>
</section>

<?php
lmf_closing_cta();
get_footer();
