<?php
/**
 * HOMEPAGE — everything after the Services reel.
 *
 * The page after the reel is a sales argument, in the order a buyer asks
 * their questions:
 *
 *   1. PROMISE   — what we do for you, in one line, with three proof figures
 *                  and the sectors we work in.               lmf_home_promise()
 *   2. PROOF     — the wheel: eight wedges, eight films; scroll turns it
 *                  project by project, then dives into one.  lmf_home_cases()
 *   3. (mint)    — the showreel.                              front-page.php
 *   4. PROCESS   — six weeks from first call to master, a fixed quote after
 *                  the treatment, and the one lemon action.    lmf_home_process()
 *   5. VOICES    — what clients said, one at a time.           lmf_home_voices()
 *   6. (lemon)   — the closing call to action.                 lmf_closing_cta()
 *
 * All copy and numbers live in lmf_home_content() and can be replaced whole
 * through the 'lmf_home_content' filter. EVERY figure, result and quote
 * below is a PLACEHOLDER written from the demo projects — confirm with the
 * client before launch.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Content.
 *
 * @return array
 */
function lmf_home_content() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$img     = get_template_directory_uri() . '/assets/img/';
	$vid     = get_template_directory_uri() . '/assets/video/work/';
	$contact = home_url( '/contact/' );

	$c = array(

		'promise' => array(
			'kicker'   => 'Why brands hire us',
			'headline' => 'Most brand content gets scrolled past. We make the films people [watch to the end].',
			'figures_kicker' => 'Lemon Mint, in numbers',
			'text'     => 'Lemon Mint is a Dubai production house for brands that need more than footage. We write the idea, shoot it with our own crew and kit, and finish it in-house — so you get one team, one invoice and a film that does its job on every screen it lands on.',
			'figures'  => array(
				array( 'value' => '120', 'suffix' => '+', 'label' => 'Films delivered since 2023' ),
				array( 'value' => '96', 'suffix' => '%', 'label' => 'Delivered on the first agreed date' ),
				array( 'value' => '6', 'suffix' => '', 'label' => 'GCC countries filmed' ),
			),
			'sectors_label' => 'Made for teams in',
			'sectors'  => array( 'Real estate', 'Hospitality', 'Government', 'Automotive', 'Finance', 'Retail & F&B', 'Healthcare', 'Technology' ),
		),

		'cases' => array(
			'kicker'   => 'Selected work — and what it did',
			'headline' => 'Proof, not promises.',
			'more'     => array( 'See all work', get_post_type_archive_link( 'lmf_project' ) ),
			/*
			 * The wheel. Eight wedges, eight films. Footage comes from ONE
			 * atlas video (4 × 2 tiles, one per wedge) so the browser decodes
			 * a single stream instead of eight. An item may instead carry its
			 * own 'video' URL (then it plays from its own file). The last
			 * item is the one the camera dives into: 'dive' is its
			 * full-frame clip.
			 */
			'atlas'    => array(
				'mp4'    => $vid . 'atlas.mp4',
				'webm'   => $vid . 'atlas.webm',
				'poster' => $vid . 'atlas.jpg',
				'cols'   => 4,
				'rows'   => 2,
			),
			'dive'     => array(
				'mp4'    => $vid . 'dive.mp4',
				'webm'   => $vid . 'dive.webm',
				'poster' => $vid . 'dive.jpg',
			),
			'items'    => array(
				array(
					'code'    => 'LMF / 021 · 06:20',
					'client'  => 'Financial institution, UAE',
					'type'    => 'Corporate film',
					'title'   => 'Union Hour',
					'result'  => 'No. 1',
					'outcome' => 'Highest engagement of any communication in an eleven-thousand-person merger.',
					'url'     => home_url( '/work/union-hour/' ),
				),
				array(
					'code'    => 'LMF / 033 · 28:00',
					'client'  => 'Investment group, Dubai',
					'type'    => 'Podcast series',
					'title'   => 'The Founders Room',
					'result'  => '12 / 12',
					'outcome' => 'Episodes published on schedule, after two earlier attempts never shipped one.',
					'url'     => home_url( '/work/the-founders-room/' ),
				),
				array(
					'code'    => 'LMF / 028 · 01:12',
					'client'  => 'Automotive brand, GCC',
					'type'    => 'Campaign',
					'title'   => 'Chrome Season',
					'result'  => '1 shoot',
					'outcome' => 'Film, print and social — the whole campaign from four shooting days.',
					'url'     => home_url( '/work/chrome-season/' ),
				),
				array(
					'code'    => 'LMF / 019 · 00:45',
					'client'  => 'Hospitality group, Dubai',
					'type'    => 'Commercial',
					'title'   => 'Sunroom',
					'result'  => '3 lengths',
					'outcome' => 'Storyboarded at every duration before the shoot, so no cutdown is a trim.',
					'url'     => home_url( '/work/sunroom/' ),
				),
				array(
					'code'    => 'LMF / 031 · 01:38',
					'client'  => 'Property developer, Dubai',
					'type'    => 'Launch film',
					'title'   => 'Meridian',
					'result'  => '6 weeks',
					'outcome' => 'Brief to master for an off-plan launch — now the template for the masterplan.',
					'url'     => home_url( '/work/meridian/' ),
				),
				array(
					'code'    => 'LMF / 024 · 02:14',
					'client'  => 'Wellness brand, Dubai',
					'type'    => 'Brand film',
					'title'   => 'Nafas',
					'result'  => '1 + 6',
					'outcome' => 'The full film and six verticals, cut from the same grade.',
					'url'     => home_url( '/work/nafas/' ),
				),
				array(
					'code'    => 'LMF / 022 · 01:00',
					'client'  => 'F&B group, GCC',
					'type'    => 'Commercial',
					'title'   => 'Night Shift',
					'result'  => '14 h → 60 s',
					'outcome' => 'Two nights of real service in an operating kitchen, cut to one minute.',
					'url'     => home_url( '/work/night-shift/' ),
				),
				array(
					'code'    => 'LMF / 017 · 48:00',
					'client'  => 'Cultural institution, UAE',
					'type'    => 'Documentary',
					'title'   => 'The Long Sand',
					'result'  => '3 rooms',
					'outcome' => 'One film that plays to a ministry, a broadcaster and a school without a recut.',
					'url'     => home_url( '/work/the-long-sand/' ),
				),
			),
		),

		'process' => array(
			'kicker'   => 'How it works',
			'headline' => 'From first call to final master in six weeks.',
			'steps'    => array(
				array( 'Day 1', 'A free call', '30 minutes on what the film has to do, for whom and by when.' ),
				array( 'Week 1', 'Treatment & fixed quote', 'The film on paper — idea, look, cast, locations — and a price that holds.' ),
				array( 'Weeks 2–4', 'Pre-production & shoot', 'Crew, permits and schedule handled. A producer on set who answers to you.' ),
				array( 'Weeks 5–6', 'Post & delivery', 'Edit, grade and sound in-house. Two revision rounds included. Every format delivered.' ),
			),
			'promise'  => 'Fixed quote after the treatment · Two revision rounds included · You own the footage',
			'cta'      => array( 'Book a free call', add_query_arg( 'i', 'call', $contact ) ),
		),

		'voices' => array(
			'kicker'   => 'Clients',
			'headline' => 'In their words.',
			// quote, who, organisation, project, still (a frame from that film)
			'items'  => array(
				array( 'They asked what we needed the audience to feel before they asked what we wanted to shoot. That conversation changed the brief.', 'Marketing Director', 'Hospitality group, Dubai', 'Vertical City', $img . 'reel/09.jpg' ),
				array( 'We had tried this twice with other partners and never published. This time we shipped twelve episodes on schedule.', 'Head of Communications', 'Investment group, Dubai', 'The Founders Room', $img . 'reel/04.jpg' ),
				array( 'Four approval rounds on a merger film is unheard of internally. They got the structure right before anyone picked up a camera.', 'Internal Communications Lead', 'Financial institution, UAE', 'Union Hour', $img . 'reel/05.jpg' ),
			),
		),
	);

	/*
	 * Approved testimonials, when the site has them, replace the sample
	 * quotes.
	 */
	$quotes = get_posts(
		array(
			'post_type'   => 'lmf_testimonial',
			'numberposts' => 5,
			'meta_key'    => '_lmf_approved',
			'meta_value'  => '1',
		)
	);
	if ( $quotes ) {
		$c['voices']['items'] = array();
		foreach ( $quotes as $q ) {
			$rel = (int) get_post_meta( $q->ID, '_lmf_project_id', true );
			$c['voices']['items'][] = array(
				wp_strip_all_tags( $q->post_content ),
				get_post_meta( $q->ID, '_lmf_who', true ),
				get_post_meta( $q->ID, '_lmf_org', true ),
				$rel ? get_the_title( $rel ) : '',
				$rel && get_the_post_thumbnail_url( $rel, 'large' ) ? get_the_post_thumbnail_url( $rel, 'large' ) : get_template_directory_uri() . '/assets/img/reel/0' . ( 4 + count( $c['voices']['items'] ) % 5 ) . '.jpg',
				$rel ? get_permalink( $rel ) : '',
			);
		}
	}

	$cache = apply_filters( 'lmf_home_content', $c );
	return $cache;
}

/**
 * Anton headline split into words, for the scroll-lit effect.
 *
 * @param string $text Headline.
 */
function lmf_home_words( $text ) {
	// [words in brackets] get the lemon marker stroke
	$words = preg_split( '/\s+/', trim( (string) $text ) );
	$open  = false;
	foreach ( $words as $i => $w ) {
		$start = 0 === strpos( $w, '[' );
		$end   = false !== strpos( $w, ']' );
		$w     = str_replace( array( '[', ']' ), '', $w );
		echo $i ? ' ' : '';
		if ( $start ) { echo '<span class="hw-mark">'; $open = true; }
		echo '<span class="hw">' . esc_html( $w ) . '</span>';
		if ( $end && $open ) { echo '</span>'; $open = false; }
	}
	if ( $open ) { echo '</span>'; }
}

/**
 * A number set as a counter: every digit is a strip 0–9 that rolls into
 * place, like a camera's frame counter. The real value is in the text for
 * screen readers; the strips are decoration.
 *
 * @param string $value Digits.
 */
function lmf_home_odometer( $value ) {
	$value = preg_replace( '/\D/', '', (string) $value );
	echo '<span class="pm-sr">' . esc_html( $value ) . '</span>';
	echo '<span class="pm-odo" aria-hidden="true" data-value="' . esc_attr( $value ) . '">';
	foreach ( str_split( $value ) as $d ) {
		echo '<span class="pm-d" data-d="' . esc_attr( $d ) . '"><span class="pm-col" style="--p:' . (int) $d . '">';
		for ( $k = 0; $k <= 10; $k++ ) {
			echo '<span>' . ( $k % 10 ) . '</span>';
		}
		echo '</span></span>';
	}
	echo '</span>';
}

/**
 * A small citrus wheel, after the reels in the mark — the separator in the
 * sectors roll. Decorative.
 */
function lmf_citrus_icon() {
	echo '<svg class="sx-ic" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10.6" fill="none" stroke="currentColor" stroke-width="1.6"/><path fill="currentColor" d="M12.84 9.97L12.59 3.62A8.4 8.4 0 0 1 17.51 5.66Z M14.03 11.16L18.34 6.49A8.4 8.4 0 0 1 20.38 11.41Z M14.03 12.84L20.38 12.59A8.4 8.4 0 0 1 18.34 17.51Z M12.84 14.03L17.51 18.34A8.4 8.4 0 0 1 12.59 20.38Z M11.16 14.03L11.41 20.38A8.4 8.4 0 0 1 6.49 18.34Z M9.97 12.84L5.66 17.51A8.4 8.4 0 0 1 3.62 12.59Z M9.97 11.16L3.62 11.41A8.4 8.4 0 0 1 5.66 6.49Z M11.16 9.97L6.49 5.66A8.4 8.4 0 0 1 11.41 3.62Z"/></svg>';
}

/**
 * 1. PROMISE — pinned for a few screens:
 *    the line, centred, lights up word by word and its last words are
 *    struck with a lemon marker → it steps back and blurs away → three
 *    figures roll in like frame counters, each drawing its rule → the
 *    sentence that explains them. Then the sectors roll past, unpinned.
 *    Reduced motion: the same content, still and in order.
 */
function lmf_home_promise() {
	$c = lmf_home_content();
	$p = $c['promise'];
	?>
	<section class="pm" aria-labelledby="hp-promise-t" data-pm>
		<div class="pm-track">
			<div class="pm-stage">
				<div class="pm-a wrap">
					<?php lmf_kicker( $p['kicker'] ); ?>
					<h2 class="pm-lit" id="hp-promise-t"><?php lmf_home_words( $p['headline'] ); ?></h2>
				</div>

				<div class="pm-b wrap">
					<?php lmf_kicker( $p['figures_kicker'] ); ?>
					<dl class="pm-figs">
						<?php foreach ( $p['figures'] as $i => $f ) : ?>
							<div class="pm-fig" style="--i:<?php echo (int) $i; ?>">
								<dt class="pm-label"><?php echo esc_html( $f['label'] ); ?></dt>
								<dd class="pm-num"><?php lmf_home_odometer( $f['value'] ); ?><?php echo $f['suffix'] ? '<span class="pm-suffix">' . esc_html( $f['suffix'] ) . '</span>' : ''; ?></dd>
								<span class="pm-rule" aria-hidden="true"></span>
							</div>
						<?php endforeach; ?>
					</dl>
					<p class="pm-text"><?php echo esc_html( $p['text'] ); ?></p>
				</div>
			</div>
		</div>

		<!-- the sectors: two rolls of type running against each other, filled
		     and outlined in turn, a citrus wheel turning between them; they
		     speed up with the scroll -->
		<div class="sx" aria-label="<?php echo esc_attr( $p['sectors_label'] . ': ' . implode( ', ', $p['sectors'] ) ); ?>">
			<div class="sx-head wrap">
				<?php lmf_kicker( $p['sectors_label'] ); ?>
			</div>
			<?php
			$rows = array( $p['sectors'], array_reverse( $p['sectors'] ) );
			foreach ( $rows as $r => $list ) :
				?>
				<div class="sx-row<?php echo $r ? ' sx-row--b' : ''; ?>" data-sx="<?php echo $r ? '-1' : '1'; ?>" aria-hidden="true">
					<?php for ( $k = 0; $k < 2; $k++ ) : ?>
						<div class="sx-run">
							<?php foreach ( $list as $j => $sct ) : ?>
								<span class="sx-w<?php echo ( $j + $r ) % 2 ? ' is-o' : ''; ?>"><?php echo esc_html( $sct ); ?></span><?php lmf_citrus_icon(); ?>
							<?php endforeach; ?>
						</div>
					<?php endfor; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

/**
 * 2. PROOF — the wheel.
 *
 * A citrus wheel from the mark: eight lemon wedges, pale spokes, inside the
 * lemon rind. Scrolling (the section pins, like the reel):
 *   1. the wedges fill with film, clockwise, like a hand sweeping a dial;
 *   2. the wheel turns wedge by wedge — the one at twelve o'clock is lit and
 *      its case (client, result) is set beside it;
 *   3. the camera dives into the last wedge until its film fills the screen.
 * Everything is also real HTML — the index of eight projects and their
 * captions — so it reads without JS, for search engines and screen readers,
 * and in reduced-motion mode as a still wheel and a list.
 * Drawing: assets/js/work-wheel.js (Canvas 2D, one video stream).
 */
function lmf_home_cases() {
	$c     = lmf_home_content();
	$w     = $c['cases'];
	$items = array_values( $w['items'] );
	$n     = count( $items );
	$at    = $w['atlas'];
	?>
	<section class="wk" data-lmf-wheel data-cols="<?php echo (int) $at['cols']; ?>" data-rows="<?php echo (int) $at['rows']; ?>" aria-labelledby="wk-t">
		<div class="wk-stage">
			<canvas class="wk-gl" aria-hidden="true"></canvas>

			<!-- the footage: one atlas stream, drawn into the wedges -->
			<video class="wk-src" muted loop playsinline preload="none" disablepictureinpicture aria-hidden="true" tabindex="-1" poster="<?php echo esc_url( $at['poster'] ); ?>">
				<source src="<?php echo esc_url( $at['webm'] ); ?>" type="video/webm">
				<source src="<?php echo esc_url( $at['mp4'] ); ?>" type="video/mp4">
			</video>

			<div class="wk-head">
				<?php lmf_kicker( $w['kicker'] ); ?>
				<h2 class="h2" id="wk-t"><?php echo esc_html( $w['headline'] ); ?></h2>
			</div>

			<!-- one caption per wedge; the lit wedge's caption is shown -->
			<div class="wk-caps" aria-live="polite">
				<?php foreach ( $items as $i => $it ) : ?>
					<article class="wk-cap<?php echo 0 === $i ? ' is-on' : ''; ?>" data-i="<?php echo (int) $i; ?>" style="--c:<?php echo (int) ( $i % $at['cols'] ); ?>;--r:<?php echo (int) floor( $i / $at['cols'] ); ?>">
						<span class="wk-thumb" aria-hidden="true" style="background-image:url('<?php echo esc_url( $at['poster'] ); ?>')"></span>
						<span class="meta wk-n"><?php echo esc_html( sprintf( '%02d / %02d', $i + 1, $n ) ); ?> · <?php echo esc_html( $it['type'] ); ?></span>
						<h3 class="wk-title"><a href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $it['title'] ); ?></a></h3>
						<span class="wk-client"><?php echo esc_html( $it['client'] ); ?></span>
						<div class="wk-res">
							<strong class="wk-big"><?php echo esc_html( $it['result'] ); ?></strong>
							<span class="wk-out"><?php echo esc_html( $it['outcome'] ); ?></span>
						</div>
						<a class="wk-go" href="<?php echo esc_url( $it['url'] ); ?>" tabindex="-1"><?php esc_html_e( 'Read the case', 'lemonmint' ); ?> <span aria-hidden="true">&rarr;</span></a>
					</article>
				<?php endforeach; ?>
			</div>

			<!-- the index: all eight, the lit one marked -->
			<nav class="wk-index" aria-label="<?php esc_attr_e( 'Selected projects', 'lemonmint' ); ?>">
				<ol>
					<?php foreach ( $items as $i => $it ) : ?>
						<li data-i="<?php echo (int) $i; ?>"><a href="<?php echo esc_url( $it['url'] ); ?>"><span class="meta"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span> <?php echo esc_html( $it['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ol>
				<?php if ( ! empty( $w['more'][1] ) ) : ?>
					<a class="link-u wk-more" href="<?php echo esc_url( $w['more'][1] ); ?>"><?php echo esc_html( $w['more'][0] ); ?></a>
				<?php endif; ?>
			</nav>

			<!-- the dive: the last wedge's film, full frame -->
			<?php $last = $items[ $n - 1 ]; ?>
			<div class="wk-dive" data-cursor-tone="light" aria-hidden="true">
				<video class="wk-dive-v" muted loop playsinline preload="none" disablepictureinpicture tabindex="-1" poster="<?php echo esc_url( $w['dive']['poster'] ); ?>">
					<source src="<?php echo esc_url( $w['dive']['webm'] ); ?>" type="video/webm">
					<source src="<?php echo esc_url( $w['dive']['mp4'] ); ?>" type="video/mp4">
				</video>
				<div class="wk-dive-in wrap">
					<span class="kicker"><?php echo esc_html( $last['client'] . ' · ' . $last['type'] ); ?></span>
					<p class="wk-dive-t"><?php echo esc_html( $last['title'] ); ?></p>
					<p class="wk-dive-o"><b><?php echo esc_html( $last['result'] ); ?></b> <?php echo esc_html( $last['outcome'] ); ?></p>
					<div class="wk-dive-a">
						<a class="btn btn-rev" href="<?php echo esc_url( $last['url'] ); ?>" tabindex="-1"><?php esc_html_e( 'Read the case', 'lemonmint' ); ?></a>
						<?php if ( ! empty( $w['more'][1] ) ) : ?>
							<a class="link-u wk-dive-more" href="<?php echo esc_url( $w['more'][1] ); ?>" tabindex="-1"><?php echo esc_html( $w['more'][0] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * 5. PROCESS — with the page's one lemon action.
 */
function lmf_home_process() {
	$c = lmf_home_content();
	$p = $c['process'];
	?>
	<section class="hp hp-process" aria-labelledby="hp-process-t" data-hp-process>
		<div class="wrap">
			<?php lmf_kicker( $p['kicker'] ); ?>
			<h2 class="h2 hp-process-t" id="hp-process-t"><?php echo esc_html( $p['headline'] ); ?></h2>

			<ol class="hp-steps">
				<?php foreach ( $p['steps'] as $i => $s ) : ?>
					<li class="hp-step" style="--i:<?php echo (int) $i; ?>">
						<span class="hp-step-dot" aria-hidden="true"></span>
						<span class="meta"><?php echo esc_html( $s[0] ); ?></span>
						<h3 class="hp-step-t"><?php echo esc_html( $s[1] ); ?></h3>
						<p><?php echo esc_html( $s[2] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>

			<div class="hp-process-foot">
				<a class="btn btn-lemon" href="<?php echo esc_url( $p['cta'][1] ); ?>"><?php echo esc_html( $p['cta'][0] ); ?></a>
				<p class="hp-guarantee">
					<?php foreach ( explode( '·', $p['promise'] ) as $g ) : ?>
						<span><?php echo esc_html( trim( $g ) ); ?></span>
					<?php endforeach; ?>
				</p>
			</div>
		</div>
	</section>
	<?php
}

/**
 * 6. VOICES — one quote at a time.
 */
function lmf_home_voices() {
	$c = lmf_home_content();
	$v = $c['voices'];
	if ( empty( $v['items'] ) ) {
		return;
	}
	$items = array_values( $v['items'] );
	$n     = count( $items );
	foreach ( $items as $i => $q ) {
		$items[ $i ]['url'] = ! empty( $q[5] ) ? $q[5] : ( ! empty( $q[3] ) ? home_url( '/work/' . sanitize_title( $q[3] ) . '/' ) : '' );
	}
	?>
	<section class="tv" aria-labelledby="tv-t" data-tv>
		<div class="tv-track">
			<div class="tv-stage">
				<div class="tv-in">

					<!-- left: the title card and the credit of the scene on screen -->
					<div class="tv-side">
						<?php lmf_kicker( $v['kicker'] ); ?>
						<h2 class="tv-h" id="tv-t"><?php echo esc_html( $v['headline'] ); ?></h2>

						<div class="tv-count" aria-hidden="true">
							<span class="tv-no">01</span><span class="tv-of">/ <?php echo esc_html( sprintf( '%02d', $n ) ); ?></span>
						</div>
						<div class="tv-bars" aria-hidden="true">
							<?php for ( $k = 0; $k < $n; $k++ ) : ?><i style="--k:<?php echo (int) $k; ?>"></i><?php endfor; ?>
						</div>

						<div class="tv-credits">
							<?php foreach ( $items as $i => $q ) : ?>
								<div class="tv-cr<?php echo 0 === $i ? ' is-on' : ''; ?>">
									<b><?php echo esc_html( $q[1] ); ?></b>
									<span><?php echo esc_html( $q[2] ); ?></span>
									<?php if ( $q['url'] ) : ?>
										<a class="tv-from" href="<?php echo esc_url( $q['url'] ); ?>" tabindex="-1"><?php esc_html_e( 'From', 'lemonmint' ); ?> <em><?php echo esc_html( $q[3] ); ?></em> <span aria-hidden="true">&rarr;</span></a>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- right: the screen, one frame per client, subtitled -->
					<div class="tv-main">
						<div class="tv-screen">
							<span class="tv-perf tv-perf-l" aria-hidden="true"></span>
							<span class="tv-perf tv-perf-r" aria-hidden="true"></span>
							<div class="tv-gate">
								<ol class="tv-strip">
									<?php foreach ( $items as $i => $q ) : ?>
										<li class="tv-frame<?php echo 0 === $i ? ' is-on' : ''; ?>">
											<figure>
												<span class="tv-img"><img src="<?php echo esc_url( isset( $q[4] ) ? $q[4] : '' ); ?>" alt="" loading="lazy" decoding="async"></span>
												<blockquote class="tv-sub">
													<p><?php
													foreach ( preg_split( '/\s+/', trim( $q[0] ) ) as $k => $w ) {
														echo ( $k ? ' ' : '' ) . '<span class="tw">' . esc_html( $w ) . '</span>';
													}
													?></p>
												</blockquote>
												<figcaption class="tv-credit">
													<b><?php echo esc_html( $q[1] ); ?></b> · <?php echo esc_html( $q[2] ); ?>
													<?php if ( $q['url'] ) : ?>
														· <a href="<?php echo esc_url( $q['url'] ); ?>"><?php echo esc_html( $q[3] ); ?></a>
													<?php endif; ?>
												</figcaption>
											</figure>
										</li>
									<?php endforeach; ?>
								</ol>
							</div>
						</div>
						<p class="meta tv-foot" aria-hidden="true"><span><?php esc_html_e( 'In their own words, subtitled', 'lemonmint' ); ?></span><span class="tv-tc">TC 01:00:00:00</span></p>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php
}
