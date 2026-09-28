<?php
/**
 * Footer.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="site">
	<div class="wrap">
		<div class="fgrid">
			<div>
				<span style="display:inline-block;margin-bottom:18px"><?php lmf_lockup(); ?></span>
				<p class="sm" style="max-width:34ch"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
				<p class="xs" style="margin-top:16px">
					<?php echo esc_html( lmf_opt( 'address', 'Warehouse 28, Al Quoz Industrial Third' ) ); ?><br>
					<?php echo esc_html( lmf_opt( 'email', 'info@lemonmintfilms.com' ) ); ?> ·
					<?php echo esc_html( lmf_opt( 'phone', '+971 4 332 3054' ) ); ?>
				</p>
				<?php
				// An optional line and the social links — Lemon Mint → Header, menu & footer.
				$lmf_note = lmf_opt( 'footer_note', '' );
				if ( $lmf_note ) :
					?>
					<p class="xs" style="margin-top:12px;max-width:34ch"><?php echo esc_html( $lmf_note ); ?></p>
					<?php
				endif;
				$lmf_social = lmf_opt( 'socials', array() );
				if ( is_array( $lmf_social ) && $lmf_social ) :
					?>
					<p class="xs" style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px 18px">
						<?php foreach ( $lmf_social as $lmf_s ) : ?>
							<?php if ( ! empty( $lmf_s['label'] ) && ! empty( $lmf_s['social_url'] ) ) : ?>
								<a href="<?php echo esc_url( $lmf_s['social_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $lmf_s['label'] ); ?></a>
							<?php endif; ?>
						<?php endforeach; ?>
					</p>
					<?php
				endif;
				?>
			</div>

			<div>
				<h5><?php esc_html_e( 'Services', 'lemonmint' ); ?></h5>
				<?php
				foreach ( get_posts( array( 'post_type' => 'lmf_service', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $s ) {
					printf( '<a href="%s">%s</a>', esc_url( get_permalink( $s ) ), esc_html( get_the_title( $s ) ) );
				}
				?>
			</div>

			<div>
				<h5><?php esc_html_e( 'Industries', 'lemonmint' ); ?></h5>
				<?php
				foreach ( get_posts( array( 'post_type' => 'lmf_industry', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $i ) {
					printf( '<a href="%s">%s</a>', esc_url( get_permalink( $i ) ), esc_html( get_the_title( $i ) ) );
				}
				?>
			</div>

			<div>
				<h5><?php esc_html_e( 'Studio', 'lemonmint' ); ?></h5>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1 ) );
				} else {
					printf( '<a href="%s">%s</a>', esc_url( home_url( '/studio/' ) ), esc_html__( 'About', 'lemonmint' ) );
					printf( '<a href="%s">%s</a>', esc_url( get_post_type_archive_link( 'lmf_project' ) ), esc_html__( 'Work', 'lemonmint' ) );
					printf( '<a href="%s">%s</a>', esc_url( home_url( '/contact/' ) ), esc_html__( 'Contact', 'lemonmint' ) );
				}
				?>
				<a href="<?php echo esc_url( home_url( '/contact/?i=quote' ) ); ?>" style="margin-top:12px"><?php esc_html_e( 'Request a Quote', 'lemonmint' ); ?></a>
			</div>
		</div>

		<div class="fbot">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
			<span class="flegal">
				<?php
				$lmf_legal = function_exists( 'lmf_legal_links' ) ? lmf_legal_links() : array();
				foreach ( $lmf_legal as $lmf_i => $lmf_l ) {
					printf(
						'%s<a href="%s">%s</a>',
						$lmf_i ? '<i aria-hidden="true">·</i>' : '',
						esc_url( $lmf_l[1] ),
						esc_html( $lmf_l[0] )
					);
				}
				?>
			</span>
		</div>
	</div>
</footer>

<div id="reel" aria-hidden="true" data-src="<?php echo esc_url( lmf_opt( 'reel_url', '' ) ); ?>">
	<div class="top">
		<span class="meta"><?php esc_html_e( 'Showreel · LMF / REEL', 'lemonmint' ); ?></span>
		<button class="x" id="reelX" aria-label="<?php esc_attr_e( 'Close', 'lemonmint' ); ?>">&#10005;</button>
	</div>
	<div class="stage"><div class="inner" id="reelInner"></div></div>
	<div class="ctrl">
		<span class="meta" id="reelT">00:00</span>
		<span class="bar"><i id="reelBar"></i></span>
		<span class="meta">01:30</span>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
