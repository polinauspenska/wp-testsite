<?php
/**
 * STUDIO — the building and the kit.
 *
 *   1. hero       — full-bleed frame
 *   2. the spaces — scrollytelling: the picture on the left stays put and
 *                   changes as you read down the rooms on the right
 *   3. the kit    — laid out as a camera report sheet
 *   4. visit      — address, directions, a lemon button to book a visit
 *   5. closing CTA
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

$c       = lmf_studio_page_content();
$address = lmf_opt( 'address', 'Warehouse 28, Al Quoz Industrial Third' );

lmf_page_hero(
	array(
		'kicker' => __( 'Studio', 'lemonmint' ),
		'title'  => __( 'One building. Every stage of the film', 'lemonmint' ),
		'lede'   => __( 'The stage, the edit suites, the grade and the mix are all down one corridor — so nothing about your film is ever handed to a stranger.', 'lemonmint' ),
		'image'  => lmf_page_img( 'studio' ),
	)
);
?>

<!-- 2. THE SPACES -->
<section class="pg-spaces" data-lmf-spaces>
	<div class="wrap pg-spaces-in">
		<div class="pg-spaces-pic" aria-hidden="true">
			<div class="pg-spaces-frame">
				<?php foreach ( $c['spaces'] as $i => $s ) : ?>
					<img src="<?php echo esc_url( $s[3] ); ?>" alt="" loading="lazy" decoding="async" class="<?php echo 0 === $i ? 'is-on' : ''; ?>">
				<?php endforeach; ?>
			</div>
			<span class="meta pg-spaces-count"><span class="pg-spaces-i">01</span> / <?php echo esc_html( sprintf( '%02d', count( $c['spaces'] ) ) ); ?></span>
		</div>
		<div class="pg-spaces-list">
			<div class="pg-head">
				<span class="kicker"><?php esc_html_e( 'Inside', 'lemonmint' ); ?></span>
				<h2 class="h2"><?php esc_html_e( 'The rooms', 'lemonmint' ); ?></h2>
			</div>
			<?php foreach ( $c['spaces'] as $i => $s ) : ?>
				<article class="pg-space" data-i="<?php echo (int) $i; ?>">
					<!-- on phones the picture sits with its room -->
					<img class="pg-space-img" src="<?php echo esc_url( $s[3] ); ?>" alt="" loading="lazy" decoding="async">
					<span class="meta"><?php echo esc_html( $s[1] ); ?></span>
					<h3 class="pg-space-t"><?php echo esc_html( $s[0] ); ?></h3>
					<p><?php echo esc_html( $s[2] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- 3. THE KIT — a camera report -->
<section class="pg-kit">
	<div class="wrap">
		<div class="pg-head">
			<span class="kicker"><?php esc_html_e( 'Kit', 'lemonmint' ); ?></span>
			<h2 class="h2"><?php esc_html_e( 'Camera report', 'lemonmint' ); ?></h2>
		</div>
		<div class="pg-report" role="table" aria-label="<?php esc_attr_e( 'Equipment', 'lemonmint' ); ?>">
			<div class="pg-report-head" role="row">
				<span role="columnheader"><?php esc_html_e( 'Roll', 'lemonmint' ); ?></span>
				<span role="columnheader"><?php esc_html_e( 'Department', 'lemonmint' ); ?></span>
				<span role="columnheader"><?php esc_html_e( 'Package', 'lemonmint' ); ?></span>
				<span role="columnheader"><?php esc_html_e( 'Notes', 'lemonmint' ); ?></span>
			</div>
			<?php foreach ( $c['kit'] as $i => $k ) : ?>
				<div class="pg-report-row" role="row">
					<span role="cell" class="pg-roll">A<?php echo esc_html( sprintf( '%03d', $i + 1 ) ); ?></span>
					<span role="cell" class="pg-dept"><?php echo esc_html( $k[0] ); ?></span>
					<span role="cell"><?php echo esc_html( $k[1] ); ?></span>
					<span role="cell" class="pg-note"><?php echo esc_html( $k[2] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- 4. VISIT -->
<section class="pg-visit">
	<div class="wrap">
		<div class="split">
			<div><span class="kicker"><?php esc_html_e( 'Visit', 'lemonmint' ); ?></span></div>
			<div>
				<p class="pg-visit-addr"><?php echo esc_html( $address ); ?></p>
				<div class="pg-visit-actions">
					<a class="btn btn-lemon" href="<?php echo esc_url( home_url( '/contact/?i=call' ) ); ?>"><?php esc_html_e( 'Book a studio visit', 'lemonmint' ); ?></a>
					<a class="link-u" href="<?php echo esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $address . ', Dubai' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get directions', 'lemonmint' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
lmf_closing_cta();
get_footer();
