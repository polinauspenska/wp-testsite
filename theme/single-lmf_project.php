<?php
/**
 * Case study — the highest-leverage page on the site.
 *
 * Challenge, direction, process, credits and result. Every project is its own
 * indexable URL carrying VideoObject schema, which is the SEO value the whole
 * competitive set gives away by relying on third-party embeds.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$id       = get_the_ID();
	$code     = get_post_meta( $id, '_lmf_code', true );
	$duration = get_post_meta( $id, '_lmf_duration', true );
	$client   = get_post_meta( $id, '_lmf_client', true );
	$subtitle = get_post_meta( $id, '_lmf_subtitle', true );
	$brief    = get_post_meta( $id, '_lmf_brief', true );
	$video    = get_post_meta( $id, '_lmf_video', true );

	$svc_terms = get_the_terms( $id, 'lmf_service_cat' );
	$ind_terms = get_the_terms( $id, 'lmf_industry_cat' );
	$fmt_terms = get_the_terms( $id, 'lmf_format' );
	?>

	<section class="wrap" style="padding-top:clamp(40px,5vw,80px)">
		<?php
		$kick = array();
		if ( $fmt_terms && ! is_wp_error( $fmt_terms ) ) {
			$kick[] = $fmt_terms[0]->name;
		}
		if ( $ind_terms && ! is_wp_error( $ind_terms ) ) {
			$kick[] = $ind_terms[0]->name;
		}
		lmf_kicker( implode( ' · ', $kick ) );
		?>
		<h1 class="h1" style="max-width:12ch">
			<?php the_title(); ?><?php echo $subtitle ? ' &mdash;<br>' . esc_html( $subtitle ) : ''; ?>
		</h1>
		<?php if ( $brief ) : ?>
			<p class="body-lg" style="margin-top:26px;max-width:50ch"><?php echo esc_html( $brief ); ?></p>
		<?php endif; ?>

		<hr class="rule" style="margin-top:clamp(26px,3vw,44px)">
		<div class="caprow" style="padding-top:14px">
			<span class="meta">
				<?php
				$line = array_filter( array( $client, get_the_date( 'Y' ), ( $svc_terms && ! is_wp_error( $svc_terms ) ) ? $svc_terms[0]->name : '' ) );
				echo esc_html( implode( ' · ', $line ) );
				?>
			</span>
			<span class="meta"><?php echo esc_html( trim( ( $code ? 'LMF / ' . $code : '' ) . ( $code && $duration ? ' · ' : '' ) . $duration ) ); ?></span>
		</div>
	</section>

	<!-- The film. Self-hosted and CDN-delivered — no third-party player chrome. -->
	<section class="wrap" style="padding-top:clamp(24px,3vw,40px)">
		<div class="player">
			<?php if ( $video ) : ?>
				<video controls preload="none" playsinline
					<?php if ( has_post_thumbnail() ) : ?>poster="<?php echo esc_url( get_the_post_thumbnail_url( $id, 'lmf-film' ) ); ?>"<?php endif; ?>
					src="<?php echo esc_url( $video ); ?>"></video>
			<?php else : ?>
				<?php lmf_still( $id, 'lmf-film', 1280, 720 ); ?>
				<button class="playbtn" data-reel aria-label="<?php esc_attr_e( 'Play film', 'lemonmint' ); ?>">
					<svg width="18" height="22" viewBox="0 0 20 24" fill="currentColor" aria-hidden="true"><path d="M0 0l20 12L0 24z"/></svg>
				</button>
				<span class="strip"><span class="meta">00:00</span><span class="bar"><i></i></span><span class="meta"><?php echo esc_html( $duration ); ?></span></span>
			<?php endif; ?>
		</div>
	</section>

	<!-- The three questions, in order: what did they need, what did we make, why does it work. -->
	<section class="sec wrap">
		<?php
		$blocks = array(
			array( '_lmf_challenge', __( 'The challenge', 'lemonmint' ) ),
			array( '_lmf_direction', __( 'Creative direction', 'lemonmint' ) ),
			array( '_lmf_process', __( 'Production', 'lemonmint' ) ),
		);
		$first = true;
		foreach ( $blocks as $b ) {
			$copy = get_post_meta( $id, $b[0], true );
			if ( ! $copy ) {
				continue;
			}
			if ( ! $first ) {
				echo '<hr class="rule">';
			}
			printf(
				'<div class="split" style="margin:%s"><div>%s</div><div><p class="body-lg">%s</p></div></div>',
				$first ? '0 0 clamp(34px,5vw,66px)' : 'clamp(34px,5vw,66px) 0',
				'<span class="kicker">' . esc_html( $b[1] ) . '</span>',
				esc_html( $copy )
			);
			$first = false;
		}
		?>
	</section>

	<?php if ( get_the_content() ) : ?>
		<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'Notes', 'lemonmint' ) ); ?></div>
				<div class="entry-content"><?php the_content(); ?></div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Credits. Also a recruiting signal. -->
	<?php
	$credits = lmf_get_pairs( $id, '_lmf_credits' );
	if ( $credits ) :
		?>
		<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'Credits', 'lemonmint' ) ); ?></div>
				<div>
					<div class="facts">
						<?php foreach ( $credits as $c ) : ?>
							<div>
								<span class="k"><?php echo esc_html( $c['k'] ); ?></span>
								<span class="v" <?php echo $c['v'] ? '' : 'style="color:var(--ink-38)"'; ?>>
									<?php echo esc_html( $c['v'] ? $c['v'] : __( 'to confirm', 'lemonmint' ) ); ?>
								</span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$why = get_post_meta( $id, '_lmf_why', true );
	if ( $why ) :
		?>
		<section class="sec wrap" style="padding-top:0">
			<div class="split">
				<div><?php lmf_kicker( __( 'Why it works', 'lemonmint' ) ); ?></div>
				<div><p class="body-lg"><?php echo esc_html( $why ); ?></p></div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Related — the internal-linking backbone -->
	<?php
	$related = lmf_related_projects( $id, 3 );
	if ( $related ) :
		?>
		<section class="sec wrap" style="padding-top:0">
			<div class="sec-head">
				<div><?php lmf_kicker( __( 'Related', 'lemonmint' ) ); ?><h2 class="h2"><?php esc_html_e( 'More like this', 'lemonmint' ); ?></h2></div>
				<?php if ( $svc_terms && ! is_wp_error( $svc_terms ) ) : ?>
					<a class="link-u" href="<?php echo esc_url( add_query_arg( 'svc', $svc_terms[0]->slug, get_post_type_archive_link( 'lmf_project' ) ) ); ?>">
						<?php echo esc_html( sprintf( /* translators: %s: service name */ __( 'All %s', 'lemonmint' ), $svc_terms[0]->name ) ); ?>
					</a>
				<?php endif; ?>
			</div>
			<div class="grid"><?php foreach ( $related as $r ) { lmf_tile( $r->ID ); } ?></div>
		</section>
	<?php endif; ?>

	<!-- CTA pre-filled with this project's service, so the first reply is about the project -->
	<section class="surface-lemon">
		<div class="wrap" style="padding-top:clamp(44px,6vw,88px);padding-bottom:clamp(44px,6vw,88px)">
			<div class="split" style="align-items:end">
				<div><?php lmf_kicker( __( 'Next', 'lemonmint' ) ); ?></div>
				<div>
					<h2 class="h2" style="max-width:14ch">
						<?php
						echo esc_html(
							$svc_terms && ! is_wp_error( $svc_terms )
								? sprintf( /* translators: %s: service name */ __( 'Enquire about %s', 'lemonmint' ), $svc_terms[0]->name )
								: __( 'Start a production', 'lemonmint' )
						);
						?><span style="color:var(--mint)">.</span>
					</h2>
					<p class="body-lg" style="margin-top:20px;max-width:46ch"><?php esc_html_e( 'The form arrives tagged with this service, so the first reply is about your project rather than about what you meant.', 'lemonmint' ); ?></p>
					<div style="margin-top:28px">
						<a class="btn btn-line" href="<?php echo esc_url( add_query_arg( array( 'i' => 'production', 'svc' => ( $svc_terms && ! is_wp_error( $svc_terms ) ) ? $svc_terms[0]->slug : '' ), home_url( '/contact/' ) ) ); ?>">
							<?php esc_html_e( 'Start a Production', 'lemonmint' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php
endwhile;
get_footer();
