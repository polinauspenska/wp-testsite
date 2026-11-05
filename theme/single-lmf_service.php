<?php
/**
 * Service page — one per keyword cluster. Never split into thin sub-pages.
 *
 * All nine run the same seven blocks in the same order. A block with no
 * content yet keeps its place and says what belongs there, so the pages
 * stay comparable and the gap is visible rather than quietly dropped.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$id      = get_the_ID();
	$keyword = get_post_meta( $id, '_lmf_keyword', true );
	$cta     = get_post_meta( $id, '_lmf_cta', true );
	$cta     = $cta ? $cta : __( 'Let’s create together', 'lemonmint' );
	$slug    = get_post_field( 'post_name', $id );
	$info    = lmf_service_info( $slug );
	$diffs   = lmf_get_pairs( $id, '_lmf_diffs' );
	$faq     = lmf_get_faq( $id );
	$work    = lmf_service_projects( $id, 6 );

	/*
	 * Questions: this service's own if the document gave them, then whatever
	 * is set on the post, then the studio's general six. A service page is
	 * never left without the answers a buyer asks for.
	 */
	if ( $info['faq'] ) {
		$faq = array();
		foreach ( $info['faq'] as $pair ) {
			$faq[] = array( 'q' => $pair[0], 'a' => $pair[1] );
		}
	} elseif ( ! $faq && function_exists( 'lmf_services_faq' ) ) {
		foreach ( lmf_services_faq() as $pair ) {
			$faq[] = array( 'q' => $pair[0], 'a' => $pair[1] );
		}
	}
	// The detail: the same six rows on every service page.
	$facts = lmf_service_facts( $slug );

	?>

	<section class="wrap" style="padding-top:clamp(40px,5vw,80px)">
		<?php lmf_kicker( sprintf( '%02d — %s', $index, __( 'Service · Dubai & GCC', 'lemonmint' ) ) ); ?>
		<h1 class="h1" style="max-width:12ch"><?php the_title(); ?></h1>
		<p class="body-lg" style="margin-top:24px;max-width:52ch"><?php echo esc_html( $info['lede'] ? $info['lede'] : get_the_excerpt() ); ?></p>
		<?php if ( $info['audience'] ) : ?>
			<p class="sm" style="margin-top:14px;max-width:52ch"><?php esc_html_e( 'For', 'lemonmint' ); ?> <?php echo esc_html( $info['audience'] ); ?></p>
		<?php endif; ?>

		<?php // One action per surface. The film is on the page already, so it needs no button of its own. ?>
		<div style="margin-top:32px">
			<a class="btn btn-lemon" href="<?php echo esc_url( add_query_arg( array( 'i' => 'production', 'svc' => $slug ), home_url( '/contact/' ) ) ); ?>"><?php echo esc_html( $cta ); ?></a>
		</div>

		<hr class="rule" style="margin-top:clamp(26px,3vw,44px)">
		<div class="caprow" style="padding-top:14px">
			<span class="meta"><?php echo $keyword ? esc_html( 'Target — ' . $keyword ) : ''; ?></span>
			<span class="meta"><?php echo esc_html( 'LMF / ' . strtoupper( substr( get_post_field( 'post_name', $id ), 0, 3 ) ) ); ?></span>
		</div>

		<div class="hero-still<?php echo get_post_meta( $id, '_lmf_video', true ) ? ' player' : ''; ?>"><?php lmf_film( $id, 'lmf-hero', 1680, 720 ); ?></div>
	</section>

	<!-- ===============================================================
		 Every service page runs the same order:
		   what we make · what it solves · how it works · the detail ·
		   selected work · questions · what comes with it
		 A block with nothing behind it is left out, never improvised.
		 =============================================================== -->

	<section class="sec wrap">
		<div class="split">
			<div><?php lmf_kicker( __( 'What we make', 'lemonmint' ) ); ?></div>
			<div>
				<h2 class="h2" style="max-width:14ch;margin-bottom:30px"><?php esc_html_e( 'The work this covers', 'lemonmint' ); ?></h2>
				<?php if ( $info['types'] ) : ?>
					<ul class="svc-list">
						<?php foreach ( $info['types'] as $t ) : ?>
							<li><?php echo esc_html( $t ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<?php lmf_svc_slot( __( 'The project types this service covers, as the service document lists them.', 'lemonmint' ) ); ?>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'What it solves', 'lemonmint' ) ); ?></div>
				<div>
					<?php if ( ! $info['problems'] && ! $info['uvp'] ) : ?>
						<?php lmf_svc_slot( __( 'The client problems this service solves, and the one line on why it is ours.', 'lemonmint' ) ); ?>
					<?php endif; ?>
					<?php if ( $info['problems'] ) : ?>
						<div class="numlist">
							<?php foreach ( $info['problems'] as $i => $pr ) : ?>
								<div>
									<span class="n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
									<span><h4><?php echo esc_html( $pr ); ?></h4></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<?php if ( $info['uvp'] ) : ?>
						<p class="body-lg" style="margin-top:34px;max-width:56ch"><?php echo esc_html( $info['uvp'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</section>

	<section class="sec wrap" style="padding-top:0">
		<div class="split">
			<div><?php lmf_kicker( __( 'How it works', 'lemonmint' ) ); ?></div>
			<div>
				<?php if ( $info['process'] ) : ?>
					<div class="numlist">
						<?php foreach ( $info['process'] as $i => $st ) : ?>
							<div>
								<span class="n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
								<span><h4><?php echo esc_html( $st[0] ); ?></h4><p><?php echo esc_html( $st[1] ); ?></p></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<?php lmf_svc_slot( __( 'This service’s own production process, step by step, with its turnaround.', 'lemonmint' ) ); ?>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( $diffs && ! $info['problems'] ) : ?>
		<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'Why us', 'lemonmint' ) ); ?></div>
				<div>
					<h2 class="h2" style="max-width:16ch;margin-bottom:32px"><?php esc_html_e( 'Why brands choose this', 'lemonmint' ); ?></h2>
					<div class="numlist">
						<?php foreach ( $diffs as $i => $d ) : ?>
							<div>
								<span class="n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
								<span><h4><?php echo esc_html( $d['k'] ); ?></h4><p><?php echo esc_html( $d['v'] ); ?></p></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div>
					<?php lmf_kicker( __( 'The detail', 'lemonmint' ) ); ?>
					<p class="sm" style="margin-top:12px;max-width:26ch"><?php esc_html_e( 'Published because production managers ask first and no brand-facing competitor answers.', 'lemonmint' ); ?></p>
				</div>
				<div>
					<div class="facts">
						<?php foreach ( $facts as $row ) : ?>
							<div>
								<span class="k"><?php echo esc_html( $row[0] ); ?></span>
								<?php if ( $row[1] ) : ?>
									<span class="v"><?php echo esc_html( $row[1] ); ?></span>
								<?php else : ?>
									<span class="v is-pending"><?php esc_html_e( 'To come', 'lemonmint' ); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>

	<section class="sec wrap" style="padding-top:0">
		<div class="sec-head">
			<div><?php lmf_kicker( __( 'Proof', 'lemonmint' ) ); ?><h2 class="h2"><?php esc_html_e( 'Selected work', 'lemonmint' ); ?></h2></div>
			<?php if ( $work ) : ?>
				<a class="link-u" href="<?php echo esc_url( add_query_arg( 'svc', $slug, get_post_type_archive_link( 'lmf_project' ) ) ); ?>"><?php esc_html_e( 'Filter all work', 'lemonmint' ); ?></a>
			<?php endif; ?>
		</div>
		<?php if ( $work ) : ?>
			<div class="grid"><?php foreach ( $work as $w ) { lmf_tile( $w->ID ); } ?></div>
		<?php else : ?>
			<?php lmf_svc_slot( __( 'Projects filed under this service appear here — add one in Work and tag it.', 'lemonmint' ) ); ?>
		<?php endif; ?>
	</section>

	<?php if ( get_the_content() ) : ?>
		<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'The offer', 'lemonmint' ) ); ?></div>
				<div class="entry-content"><?php the_content(); ?></div>
			</div>
		</section>
	<?php endif; ?>

	<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'Questions', 'lemonmint' ) ); ?></div>
				<div>
					<?php if ( ! $faq ) : ?>
						<?php lmf_svc_slot( __( 'The six questions clients ask about this service, with the studio’s answers.', 'lemonmint' ) ); ?>
					<?php endif; ?>
					<div class="faq">
						<?php foreach ( $faq as $i => $f ) : ?>
							<details <?php echo 0 === $i ? 'open' : ''; ?>>
								<summary><?php echo esc_html( $f['q'] ); ?></summary>
								<div class="a"><?php echo esc_html( $f['a'] ); ?></div>
							</details>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>

	<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'Usually with it', 'lemonmint' ) ); ?></div>
				<div class="svc-related">
					<?php if ( ! $info['related'] ) : ?>
						<?php lmf_svc_slot( __( 'The services that usually come with this one.', 'lemonmint' ) ); ?>
					<?php endif; ?>
					<?php
					foreach ( $info['related'] as $rel ) :
						$rel_title = lmf_service_title( $rel );
						if ( ! $rel_title ) {
							continue;
						}
						?>
						<a href="<?php echo esc_url( home_url( '/services/' . $rel . '/' ) ); ?>"><?php echo esc_html( $rel_title ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

	<?php lmf_closing_cta(); ?>
	<?php
endwhile;
get_footer();
