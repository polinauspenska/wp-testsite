<?php
/**
 * LANDING PAGES — shared parts and content.
 *
 * Every landing page (About, Services, Industries, Studio, Contact) opens
 * the way the homepage does: a full-bleed frame with the header lying over
 * it in reverse, the headline registering in line by line, the perforated
 * film edge beside it. They reuse the homepage's hero files, so a change to
 * the hero there reaches every page.
 *
 * All copy below marked PLACEHOLDER is drafted from the strategy document
 * and must be confirmed by the client. Every block can be replaced whole
 * through its filter, or edited in place here.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is this one of the pages that open with a full-bleed frame?
 *
 * @return bool
 */
function lmf_is_cine_page() {
	// Contact is one screen without a hero frame, so its header stays solid.
	return is_front_page()
		|| is_page( array( 'about', 'studio' ) )
		|| is_post_type_archive( array( 'lmf_service', 'lmf_industry' ) );
}

/**
 * The page hero.
 *
 * @param array $a {
 *     @type string $kicker Small line above the title.
 *     @type string $title  The headline. A mint full stop is added.
 *     @type string $lede   One or two sentences under it. Optional.
 *     @type string $image  Background still (16:9 or wider).
 *     @type array  $cta    array( label, url ). Optional.
 *     @type string $size   'tall' (default) or 'short'.
 * }
 */
function lmf_page_hero( $a ) {
	$a = wp_parse_args(
		$a,
		array(
			'kicker' => '',
			'title'  => get_the_title(),
			'lede'   => '',
			'image'  => '',
			'cta'    => array(),
			'size'   => 'tall',
		)
	);
	?>
	<script>document.documentElement.classList.add('hero-wait');</script>
	<section class="hero hero-video hero-page is-<?php echo esc_attr( $a['size'] ); ?>" data-cursor-tone="light">
		<div class="hero-bg" aria-hidden="true"<?php echo $a['image'] ? ' style="background-image:url(\'' . esc_url( $a['image'] ) . '\')"' : ''; ?>></div>
		<div class="wrap hero-in">
			<?php if ( $a['kicker'] ) : ?>
				<span class="kicker hero-page-kicker"><?php echo esc_html( $a['kicker'] ); ?></span>
			<?php endif; ?>
			<h1 class="h1"><?php echo esc_html( $a['title'] ); ?><span class="h1-dot">.</span></h1>
			<?php if ( $a['lede'] || $a['cta'] ) : ?>
				<div class="hero-foot is-solo hero-page-foot">
					<?php if ( $a['lede'] ) : ?>
						<p class="hero-page-lede"><?php echo esc_html( $a['lede'] ); ?></p>
					<?php endif; ?>
					<?php if ( $a['cta'] ) : ?>
						<div><a class="btn btn-lemon" href="<?php echo esc_url( $a['cta'][1] ); ?>"><?php echo esc_html( $a['cta'][0] ); ?></a></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<span class="hero-cue" aria-hidden="true"><b><?php esc_html_e( 'Scroll', 'lemonmint' ); ?></b><i></i></span>
	</section>
	<?php
}

/**
 * A still from the theme's page folder.
 *
 * @param string $name File stem in assets/img/pages or assets/img/reel.
 * @return string URL.
 */
function lmf_page_img( $name ) {
	$dir = ( strlen( $name ) === 2 && ctype_digit( $name ) ) ? 'reel' : 'pages';
	return get_template_directory_uri() . '/assets/img/' . $dir . '/' . $name . '.jpg';
}

/* ==========================================================================
   ABOUT
   ========================================================================== */

/**
 * @return array
 */
function lmf_about_content() {
	return apply_filters(
		'lmf_about_content',
		array(
			// PLACEHOLDER — the company story in the founder's own words.
			'manifesto' => 'We started Lemon Mint because good ideas kept getting lost between the agency, the crew and the edit suite. So we built one team that writes it, shoots it, cuts it, grades it and mixes it — in one building, with one person answering the phone when you call.',
			// PLACEHOLDER — milestones to be confirmed.
			'timeline'  => array(
				array( '2023', 'Founded in Dubai', 'A production house with streaming and broadcast credits on the leadership team.' ),
				array( '2024', 'Post moves in-house', 'Edit, grade and sound under the same roof as the shoot.' ),
				array( '2025', 'Across the GCC', 'Shoots in six countries, permits and all.' ),
				array( '2026', 'Original series', 'Formats and docuseries in development for commissioners.' ),
			),
			// PLACEHOLDER — used only until People are added in WordPress.
			// No stand-in photos here on purpose: a stranger's face under a
			// real name would read as that person. Empty frames until then.
			'people'    => array(
				array( 'Mohammad Faizan', 'Founder & CEO', '' ),
				array( 'Name to confirm', 'Head of Production', '' ),
				array( 'Name to confirm', 'Director of Photography', '' ),
				array( 'Name to confirm', 'Head of Post', '' ),
			),
		)
	);
}

/**
 * People: the "People" post type if it has entries, the list above if not.
 *
 * @return array[] name, role, image, url
 */
function lmf_about_people() {
	$out   = array();
	$posts = get_posts( array( 'post_type' => 'lmf_person', 'numberposts' => 12, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
	foreach ( $posts as $p ) {
		$out[] = array(
			get_the_title( $p ),
			(string) get_post_meta( $p->ID, '_lmf_role', true ),
			get_the_post_thumbnail_url( $p->ID, 'lmf-tile' ),
			get_post_meta( $p->ID, '_lmf_public', true ) ? get_permalink( $p ) : '',
		);
	}
	if ( ! $out ) {
		foreach ( lmf_about_content()['people'] as $p ) {
			$out[] = array( $p[0], $p[1], $p[2], '' );
		}
	}
	return $out;
}

/**
 * The two legal pages, for the footer.
 *
 * Whatever they are called and wherever they live: WordPress's own privacy
 * page setting first, then the usual slugs. A page that does not exist is
 * left out rather than linked to a 404.
 *
 * @return array[] title, url
 */
function lmf_legal_links() {
	$out = array();

	$privacy = 0;
	if ( function_exists( 'get_option' ) ) {
		$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
	}
	if ( ! $privacy || 'publish' !== get_post_status( $privacy ) ) {
		$privacy = 0;
		foreach ( array( 'privacy-policy', 'privacy', 'privacy-notice' ) as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				$privacy = (int) $page->ID;
				break;
			}
		}
	}
	if ( $privacy ) {
		$out[] = array( get_the_title( $privacy ), get_permalink( $privacy ) );
	}

	foreach ( array( 'terms', 'terms-of-use', 'terms-and-conditions' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$out[] = array( get_the_title( $page ), get_permalink( $page ) );
			break;
		}
	}

	return apply_filters( 'lmf_legal_links', $out );
}

/**
 * Is this one of them? Legal pages skip the closing call to action.
 *
 * @return bool
 */
function lmf_is_legal_page() {
	if ( ! is_page() ) {
		return false;
	}
	$here = get_queried_object_id();
	foreach ( lmf_legal_links() as $l ) {
		if ( untrailingslashit( $l[1] ) === untrailingslashit( get_permalink( $here ) ) ) {
			return true;
		}
	}
	return false;
}

/* ==========================================================================
   SERVICES
   ========================================================================== */

/**
 * How a film gets made. The order is real, so the numbering is too.
 *
 * @return array
 */
function lmf_process_steps() {
	return apply_filters(
		'lmf_process_steps',
		array(
			array( '00:00', 'Brief', 'A first call. We ask what the film has to do, for whom, and by when.' ),
			array( '00:48', 'Treatment', 'You see the film on paper — structure, look, cast, locations — before a camera is booked.' ),
			array( '02:10', 'Pre-production', 'Crew, permits, locations, schedule and a budget that holds.' ),
			array( '03:36', 'Shoot', 'One crew, our own kit, and a producer on set who answers to you.' ),
			array( '05:20', 'Post & delivery', 'Edit, grade, sound and every aspect ratio you need, in-house.' ),
		)
	);
}

/**
 * General questions for the Services page.
 *
 * @return array[] q, a
 */
function lmf_services_faq() {
	return apply_filters(
		'lmf_services_faq',
		array(
			array( 'What does a film cost in Dubai?', 'A single-day brand film with a small crew and in-house post starts in the mid five figures AED. A multi-day commercial with talent and a full lighting package sits well above that. We give a banded estimate after the first conversation and a fixed quote after the treatment.' ),
			array( 'How long from brief to delivered master?', 'Six to eight weeks is comfortable for a single film including two rounds of revisions. Anything under three weeks means something is being cut, and we will tell you what.' ),
			array( 'How many rounds of revisions are included?', 'Two full rounds on the edit and one on the grade and mix, written into the quote. Further rounds are quoted rather than refused.' ),
			array( 'Do you handle filming permits?', 'Yes, including commission permits, location agreements and drone clearances. Permit lead times shape the schedule more than crew availability does, so we file early.' ),
			array( 'Can you work to an agency’s concept?', 'Regularly. We execute agency creative as a production partner and are comfortable white-labelling.' ),
		)
	);
}

/* ==========================================================================
   INDUSTRIES
   ========================================================================== */

/**
 * Industries: the post type if it has entries, the demo list if not.
 *
 * @return array[] title, text, url, image
 */
function lmf_industries_list() {
	$imgs  = array( 'industries', '03', '05', '01', '08', '04', '09', '02', '06' );
	$out   = array();
	$posts = get_posts( array( 'post_type' => 'lmf_industry', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
	foreach ( $posts as $i => $p ) {
		$img   = get_the_post_thumbnail_url( $p->ID, 'large' );
		$out[] = array(
			get_the_title( $p ),
			wp_strip_all_tags( get_the_excerpt( $p ) ),
			get_permalink( $p ),
			$img ? $img : lmf_page_img( $imgs[ $i % count( $imgs ) ] ),
		);
	}
	if ( ! $out ) {
		$fallback = array(
			array( 'Real Estate', 'Off-plan launches, completed handovers and masterplan films.', 'real-estate' ),
			array( 'Automotive', 'Model-year campaigns, launches and dealer content across the GCC.', 'automotive' ),
			array( 'Government', 'Ministries, authorities and institutions, with the approval chains that come with them.', 'government' ),
			array( 'Hospitality', 'Properties, restaurants and destination films.', 'hospitality' ),
			array( 'Retail & F&B', 'Campaigns and always-on content for brands people meet every day.', 'retail-fb' ),
		);
		foreach ( $fallback as $i => $f ) {
			$out[] = array( $f[0], $f[1], home_url( '/industries/' . $f[2] . '/' ), lmf_page_img( $imgs[ $i % count( $imgs ) ] ) );
		}
	}
	return apply_filters( 'lmf_industries_list', $out );
}

/* ==========================================================================
   STUDIO
   ========================================================================== */

/**
 * @return array
 */
function lmf_studio_page_content() {
	return apply_filters(
		'lmf_studio_page_content',
		array(
			// PLACEHOLDER — spaces, sizes and floors to be confirmed on a site visit.
			'spaces' => array(
				array( 'Stage A', 'Floor 1 · 120 m²', 'Our shooting floor: a cyc wall, blackout, rigging grid and three-phase power. Built for commercials, interviews and product work.', lmf_page_img( 'studio' ) ),
				array( 'Edit suites', 'Floor 2 · 3 rooms', 'Offline and online edit, side by side with the producer who ran the shoot. No hand-over, no lost notes.', lmf_page_img( '04' ) ),
				array( 'Grading suite', 'Floor 2 · calibrated', 'A reference monitor and a colourist in a darkened room — the look agreed in the treatment, finished here.', lmf_page_img( '06' ) ),
				array( 'Sound & voice', 'Floor 2 · booth', 'Voiceover booth, mixing and deliverables for broadcast, cinema and social loudness standards.', lmf_page_img( '08' ) ),
				array( 'Podcast set', 'Floor 1 · 4 cameras', 'A dressed, lit, multi-camera set: a produced show, not a room hired by the hour.', lmf_page_img( '02' ) ),
			),
			// PLACEHOLDER — from the strategy document's kit list.
			'kit'    => array(
				array( 'Camera', 'Large-format and Super 35 bodies', 'Prime and zoom sets' ),
				array( 'Lighting', 'LED panel and tube packages, HMI', 'Full grip and rigging' ),
				array( 'Sound', 'Multi-channel field recording', 'Boom, lavalier, VO booth' ),
				array( 'Post', 'Offline and online suites', 'Grading, motion, subtitling' ),
				array( 'Aerial', 'Licensed drone operators', 'Permits, restricted-airspace clearance' ),
			),
		)
	);
}
