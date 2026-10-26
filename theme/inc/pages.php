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
			// The founder's own words, from the company's website brief.
			'manifesto' => 'At Lemon Mint Films, we believe great content should do more than look good. It should make people feel something. Founded by Mohammad Faizan, we bring together cinematic storytelling and modern production to create work for brands, businesses, broadcasters and entertainment platforms — corporate films, celebrity-led campaigns, television series, short films, microdramas, podcasts, music productions and live experiences. Every story has a different purpose: a corporate film has to inspire confidence, an advertisement has to hold attention, a series has to keep an audience coming back. We start with the story, the audience and the objective behind the project.',
			/*
			 * Not a timeline: the five things the company says make it
			 * different. Dates and milestones are PENDING from the client.
			 */
			'timeline'  => array(
				array( '01', 'Premium cinematic quality', 'A cinematic approach across corporate, commercial, entertainment and digital work.' ),
				array( '02', 'Creative excellence', 'Story-driven concepts built around the audience and the client’s objective.' ),
				array( '03', 'Proven track record', 'More than 100 projects across industries and content formats.' ),
				array( '04', 'Rapid turnaround', 'Production efficiency without giving up on the craft.' ),
				array( '05', 'Scalable infrastructure', 'From digital-first content to long-form entertainment and advertising.' ),
			),
			// PLACEHOLDER — only the founder is confirmed; the rest are PENDING.
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
			array( '00:00', 'Brief', 'What the film has to do, who it is for, and when it is needed.' ),
			array( '00:48', 'Concept & script', 'The creative concept, the narrative and the script, developed around that objective.' ),
			array( '02:10', 'Pre-production', 'Planning, scheduling, casting and locations — with permits where the shoot needs them.' ),
			array( '03:36', 'Production', 'Direction, cinematography, lighting and sound, in our studio or on your site.' ),
			array( '05:20', 'Post & delivery', 'Edit, colour, sound and every platform version, including the vertical cutdowns.' ),
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
			array( 'What does a production cost?', 'Every project is quoted individually. We give an indicative range after the first conversation and a full quotation once the scope and the deliverables are agreed. Talent fees, celebrity engagements, location rentals and permits are quoted separately from production so the figure stays readable.' ),
			array( 'How long does a project take?', 'A corporate film is typically 7–14 days. Most productions — commercials, brand films, campaigns — run 2–4 weeks. Short films take 4–8 weeks and a microdrama season 3–6 weeks. Event highlights are delivered in 3–5 days, and social reels inside 24–48 hours.' ),
			array( 'Do you develop the concept and the script?', 'Yes. Concept development, scripting, storyboarding and creative direction are part of the work. We start from the brand, the objective and the audience before anything is written.' ),
			array( 'Which languages do you work in?', 'English, Hindi, Arabic and Urdu, with subtitles, voiceovers, dubbing and localisation arranged to the project’s requirements.' ),
			array( 'Where do you film?', 'Dubai, Abu Dhabi and across the UAE — our own studio, corporate offices, developments, hotels, event venues and outdoor locations. International productions are coordinated on request, subject to permits and logistics.' ),
			array( 'Can one film be cut for different platforms?', 'Yes. A single production can deliver the main film plus 30 and 15-second cutdowns, vertical reels, teasers and platform-specific versions, agreed as deliverables before the shoot.' ),
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
			array( 'Real Estate', 'Off-plan launches, developer brand films and property-expo campaigns.', 'real-estate' ),
			array( 'Corporate & Business', 'Company profiles, leadership films and internal communication.', 'corporate-business' ),
			array( 'Entertainment & Streaming', 'Scripted series, short films and microdramas for platforms.', 'entertainment-streaming' ),
			array( 'Events & Awards', 'Award ceremonies, conferences and brand activations.', 'events-awards' ),
			array( 'Sports', 'Teams, tournaments and sports-led promotional films.', 'sports' ),
			array( 'Retail & Consumer', 'Product campaigns and always-on content for consumer brands.', 'retail-consumer' ),
			array( 'Automotive', 'Launches, dealer content and brand events across the GCC.', 'automotive' ),
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
				array( 'Camera', 'Cinema bodies, prime and zoom sets', 'Horizontal and 9:16 vertical' ),
				array( 'Lighting', 'LED panel and tube packages, HMI', 'Full grip and rigging' ),
				array( 'Sound', 'Multi-channel field recording', 'Boom, lavalier, VO booth' ),
				array( 'Post', 'Offline and online suites', 'Grading, motion, subtitling' ),
				array( 'Music', 'Composition and recording', 'Tracks, anthems and score' ),
			),
		)
	);
}
