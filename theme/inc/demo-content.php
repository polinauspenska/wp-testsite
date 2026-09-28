<?php
/**
 * One-click demo content.
 *
 * Seeds the site with the same structure the clickable prototype used, so the
 * client can see the theme working before a single real asset has been
 * cleared. Every title, client and credit below is a PLACEHOLDER. The only
 * substantive claim seeded is the streaming/broadcast pedigree, which comes
 * from the strategy document and still needs written sign-off on its exact
 * wording before publication. NAFAS and
 * SUNROOM are the two work examples printed in the brand guidelines; the rest
 * exist only to exercise the templates.
 *
 * Tools → Lemon Mint demo content. Safe to run once; it skips anything that
 * already exists, and there is a Remove button for a clean handover.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * The default text of the two legal pages. PLACEHOLDERS: they describe how
 * the studio and this site actually work, but they are not legal advice and
 * need a lawyer's eye — and the company's legal name, licence number and
 * registered address — before launch.
 */
const LMF_PRIVACY_COPY = 'PLACEHOLDER — this notice describes how the site and the studio actually handle information, but it must be read by a lawyer, and the company\'s legal name, licence number and registered address added, before the site goes live.

<h2>Who we are</h2>
Lemon Mint Films is a film production company based in Dubai, United Arab Emirates. If you have any question about this notice, write to info@lemonmintfilms.com.

<h2>What we collect, and why</h2>
When you send us a brief or an enquiry, we ask for your name, your email address and — if you choose to give them — your company, your phone number, the kind of film you are planning, your timing, your budget range and whatever you tell us about the project. We use it for one thing: to reply to you and to prepare a proposal. We do not sell it, rent it, or pass it to anyone for their own marketing.

Our web host records standard server logs, including IP addresses, for security and troubleshooting.

<h2>Cookies</h2>
This site sets no advertising or tracking cookies. If analytics or a chat tool is added later, this notice will say so before it is switched on.

<h2>Who else sees it</h2>
Only the people who need to: our producers, our email provider and our web host. Where a supplier is outside the UAE, we use providers who commit to appropriate safeguards for international transfers.

<h2>How long we keep it</h2>
Enquiries are kept while we are talking and for two years afterwards, so we can pick a conversation back up. Project records are kept for as long as the law and our contracts require. You can ask us to delete yours sooner.

<h2>Your rights</h2>
You can ask what we hold about you, ask us to correct it, ask us to delete it, or object to how we use it. Write to info@lemonmintfilms.com and we will answer within thirty days.

<h2>Changes</h2>
If this notice changes, the new version appears on this page with the date it took effect.';

const LMF_TERMS_COPY = 'PLACEHOLDER — these terms describe how the studio normally works, but they are not legal advice and must be reviewed and completed by a lawyer, with the company\'s legal name, licence number and jurisdiction, before the site goes live.

<h2>About these terms</h2>
They cover the use of this website and the way we quote for and carry out production work. A signed proposal or contract for a specific project always takes precedence over anything written here.

<h2>Quotes and bookings</h2>
A banded estimate is given after the first conversation; a fixed quote follows the treatment. A quote is valid for thirty days. A project is booked when the quote is accepted in writing and the deposit is received, and crew, kit and locations are only held once it is.

<h2>Payment</h2>
Unless a proposal says otherwise, invoices are payable within thirty days of issue. Prices are exclusive of VAT and of third-party costs — permits, talent, licensed music, travel — which are listed separately.

<h2>Revisions</h2>
Two rounds of revisions on the edit and one on the grade and mix are included, as set out in the quote. Further rounds, or changes to an approved brief, are quoted before the work is done.

<h2>Footage and rights</h2>
On full payment, you own the delivered films and the rights agreed in the proposal. Raw footage, project files and archives stay with us unless the proposal says otherwise. We license music, stock and talent for the territories, media and term written into the quote; using a film beyond them needs a new licence. Unless you ask us not to, we may show the finished work in our portfolio and showreel.

<h2>Your material</h2>
You confirm that any logo, footage, music or other material you give us is yours to use for the project.

<h2>Cancellation</h2>
If a confirmed shoot is cancelled, costs already committed — crew, kit, locations, permits, travel — remain payable, as does work already done.

<h2>Permits and compliance</h2>
We film under the permits required in the emirate concerned and follow the conditions attached to them. Where your site or your client sets additional rules, tell us in advance so the schedule can allow for them.

<h2>Liability</h2>
Nothing here limits liability that cannot be limited by law. Otherwise our liability for a project is limited to the fees paid for it, and we are not liable for indirect or consequential loss.

<h2>Governing law</h2>
These terms are governed by the laws of the United Arab Emirates as applied in the Emirate of Dubai, and the Dubai courts have jurisdiction.

<h2>Contact</h2>
info@lemonmintfilms.com';

/**
 * Admin page.
 */
function lmf_demo_menu() {
	add_management_page(
		__( 'Lemon Mint demo content', 'lemonmint' ),
		__( 'Lemon Mint demo content', 'lemonmint' ),
		'manage_options',
		'lmf-demo',
		'lmf_demo_page'
	);
}
add_action( 'admin_menu', 'lmf_demo_menu' );

/**
 * Render the admin page.
 */
function lmf_demo_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['lmf_demo_action'] ) && check_admin_referer( 'lmf_demo' ) ) {
		$action = sanitize_key( wp_unslash( $_POST['lmf_demo_action'] ) );
		if ( 'install' === $action ) {
			$n = lmf_demo_install();
			printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html( sprintf( /* translators: %d: number of items */ __( 'Seeded %d items. Visit the homepage.', 'lemonmint' ), $n ) ) );
		} elseif ( 'remove' === $action ) {
			$n = lmf_demo_remove();
			printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html( sprintf( /* translators: %d: number of items */ __( 'Removed %d demo items.', 'lemonmint' ), $n ) ) );
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Lemon Mint demo content', 'lemonmint' ); ?></h1>
		<p style="max-width:70ch">
			<?php esc_html_e( 'Seeds nine services, five industries, nine projects and three testimonials, plus the Studio and Contact pages and the primary menu. Everything is placeholder copy — no real client, award or credential is asserted. Replace it as real content is cleared, or remove it before handover.', 'lemonmint' ); ?>
		</p>
		<form method="post" style="display:flex;gap:12px;margin-top:20px">
			<?php wp_nonce_field( 'lmf_demo' ); ?>
			<button class="button button-primary" name="lmf_demo_action" value="install"><?php esc_html_e( 'Install demo content', 'lemonmint' ); ?></button>
			<button class="button" name="lmf_demo_action" value="remove"><?php esc_html_e( 'Remove demo content', 'lemonmint' ); ?></button>
		</form>
	</div>
	<?php
}

/**
 * The seed data.
 *
 * @return array
 */
function lmf_demo_data() {
	return array(
		'services' => array(
			array( 'Brand Films', 'brand-films', 'Long-form storytelling for brands and founders. Concept, production, cut.', 'TV Commercial Production Dubai', 'Start a Production', 'portrait' ),
			array( 'Commercials', 'commercials', '30 and 15-second spots for broadcast and social, shot in-house.', 'Corporate Video Production Dubai', 'Start a Production', 'interior' ),
			array( 'Documentary', 'documentary', 'Observational work on location, small crews, natural light.', 'Documentary Production Dubai', 'Book a Discovery Call', 'wide' ),
			array( 'Post & Colour', 'post-colour', 'Edit, grade, sound and delivery in every aspect ratio you need.', 'Video Editing Dubai', 'Request a Quote', 'interior' ),
			array( 'Event Film', 'event-film', 'Launches, conferences and aftermovies, cut fast without looking like it.', 'Event Video Production Dubai', 'Request a Quote', 'wide' ),
			array( 'Animation & Motion', 'animation-motion', '2D, motion graphics and explainer work, designed rather than templated.', 'Animation Studio Dubai', 'Request a Quote', 'interior' ),
			array( 'Original Series', 'original-series', 'Reality formats, docuseries and original IP, developed for commissioners.', 'TV Series Production Company Dubai', 'Book a Discovery Call', 'portrait' ),
			array( 'Podcast Production', 'podcast-production', 'A produced show — format, multi-camera recording, full post. Not a room by the hour.', 'Podcast Production Dubai', 'Plan Your Podcast', 'interior' ),
			array( 'Drone & Aerial', 'drone-aerial', 'Licensed aerial cinematography, and the permit work that happens before it.', 'Drone Videography Dubai', 'Request a Quote', 'wide' ),
		),
		'industries' => array(
			array( 'Real Estate', 'real-estate', 'Off-plan launches, completed handovers and masterplan films.', 'wide' ),
			array( 'Automotive', 'automotive', 'Model-year campaigns, launches and dealer content across the GCC.', 'portrait' ),
			array( 'Government', 'government', 'Ministries, authorities and institutions, with the approval chains that come with them.', 'interior' ),
			array( 'Hospitality', 'hospitality', 'Properties, restaurants and destination films.', 'interior' ),
			array( 'Retail & F&B', 'retail-fb', 'Campaigns and always-on content for brands people meet every day.', 'interior' ),
		),
		'projects' => array(
			array(
				'title' => 'Nafas',
				'slug'  => 'nafas',
				'sub'   => 'A Brand Film',
				'code'  => '024',
				'dur'   => '02:14',
				'client' => 'Wellness brand, Dubai',
				'svc'   => 'Brand Films',
				'ind'   => 'Retail & F&B',
				'fmt'   => 'Brand film',
				'kind'  => 'portrait',
				'brief' => 'Two minutes, one location, natural light. Directed and cut in-house across four shooting days in Dubai Production City.',
				'chal'  => 'The brand had a founder who was good on camera and a story that had only ever been told in a pitch deck. Everything on their channels so far had been shot in fifteen-second pieces, which meant the full idea had never been said out loud anywhere.',
				'dir'   => 'One location, one day in the life, natural light only. No interview setup, no b-roll shopping list. We shot the routine the brand is actually built around and let the founder narrate it afterwards over picture, so the voice sits on the film instead of driving it.',
				'proc'  => 'Four shooting days spread across three weeks to catch the same light. A two-person crew. Grade and mix in-house; the sound design is entirely location audio, no library beds.',
				'why'   => 'It is the first thing the brand has that works at full length and cuts down without falling apart. Six verticals came out of the same grade.',
			),
			array(
				'title' => 'Sunroom',
				'slug'  => 'sunroom',
				'sub'   => 'Spot',
				'code'  => '019',
				'dur'   => '00:45',
				'client' => 'Hospitality group, Dubai',
				'svc'   => 'Commercials',
				'ind'   => 'Hospitality',
				'fmt'   => 'Commercial',
				'kind'  => 'interior',
				'brief' => 'A 45-second spot and a cutdown suite, shot in a single room across one day.',
				'chal'  => 'One room, one day, and a brief that wanted the property to feel bigger than it is. Every option that solves this with a wide lens makes the room look like a showhome.',
				'dir'   => 'We stayed close. The spot is built almost entirely from detail shots at the focal lengths you actually see a room in, and the only wide is the last frame. The space reads generous because you arrive at it rather than start there.',
				'proc'  => 'One shooting day, natural light supplemented with two units. Cut, graded and mixed in four days.',
				'why'   => 'The cutdowns hold because the film was storyboarded at three durations before the shoot, not trimmed afterwards.',
			),
			array(
				'title' => 'Meridian',
				'slug'  => 'meridian',
				'sub'   => 'Launch Film',
				'code'  => '031',
				'dur'   => '01:38',
				'client' => 'Property developer, Dubai',
				'svc'   => 'Brand Films',
				'ind'   => 'Real Estate',
				'fmt'   => 'Brand film',
				'kind'  => 'wide',
				'brief' => 'An off-plan launch film for a waterfront tower, delivered in six weeks from brief to master.',
				'chal'  => 'The building did not exist yet. Every competitor launch film in the category solves this the same way, with a CG flythrough and a voiceover about lifestyle, and the developer had explicitly seen too many of them.',
				'dir'   => 'We shot the site, the water and the light at the hours the building will eventually catch them, and let CG carry only the tower. The film is mostly real footage of a place that is mostly still empty, which is a stranger and more memorable thing to look at than a rendered lobby.',
				'proc'  => 'Six weeks. Two aerial days under permit, one marine unit, three golden-hour ground days. CG integration ran parallel to the edit rather than after it, which is the only reason six weeks was possible.',
				'why'   => 'It does not look like an off-plan film, which was the whole brief. The treatment is now the template for the rest of the masterplan.',
			),
			array(
				'title' => 'The Long Sand',
				'slug'  => 'the-long-sand',
				'sub'   => 'Documentary',
				'code'  => '017',
				'dur'   => '48:00',
				'client' => 'Cultural institution, UAE',
				'svc'   => 'Documentary',
				'ind'   => 'Government',
				'fmt'   => 'Documentary',
				'kind'  => 'wide',
				'brief' => 'A feature documentary on a disappearing craft, shot across four emirates over eleven months.',
				'chal'  => 'The institution had a century of archive and no through-line. Everything worth saying existed as fragments — oral history, photographs, a handful of surviving practitioners — and none of it had been shaped into something a general audience would sit through.',
				'dir'   => 'We built the film around a single practitioner rather than the craft itself. One person, one year, one working season. The archive stopped being the subject and became texture.',
				'proc'  => 'Eleven months, four emirates, a two-person crew for most of it. Long-lens observational coverage with almost no interviews on location; the sit-down was recorded once, at the end, when trust had been built.',
				'why'   => 'The film had a job beyond the screen: to be the thing the institution could hand to a ministry, a broadcaster and a school without recutting it three ways. It plays in all three rooms.',
			),
			array(
				'title' => 'Night Shift',
				'slug'  => 'night-shift',
				'sub'   => 'Spot',
				'code'  => '022',
				'dur'   => '01:00',
				'client' => 'F&B group, GCC',
				'svc'   => 'Commercials',
				'ind'   => 'Retail & F&B',
				'fmt'   => 'Commercial',
				'kind'  => 'interior',
				'brief' => 'A 60-second spot and a cutdown suite, shot over two nights in an operating kitchen.',
				'chal'  => 'The client wanted the energy of a real service and the legal team wanted a closed set. Those are opposite requirements.',
				'dir'   => 'We ran a real service, briefed everyone once, then shot it like documentary coverage with a scripted spine underneath. Nothing in the film is a performance of service; it is service.',
				'proc'  => 'Two nights, three cameras, no second takes on the line. The edit was assembled from fourteen hours of coverage against a sixty-second board.',
				'why'   => 'The cutdowns work because the footage was never cut for one duration. There are six versions and none of them feels like a trim.',
			),
			array(
				'title' => 'Chrome Season',
				'slug'  => 'chrome-season',
				'sub'   => 'Campaign',
				'code'  => '028',
				'dur'   => '01:12',
				'client' => 'Automotive brand, GCC',
				'svc'   => 'Brand Films',
				'ind'   => 'Automotive',
				'fmt'   => 'Commercial',
				'kind'  => 'portrait',
				'brief' => 'A model-year campaign film plus stills, shot across desert and city in four days.',
				'chal'  => 'Regional automotive work has a house style — a car, a dune, a drone. The brand wanted to be recognisably in the category without arriving in the same shot.',
				'dir'   => 'We shot the car almost entirely at rest and put the motion in the world around it: light, weather, traffic, people. The one driving sequence lands harder because it is the only one.',
				'proc'  => 'Four days, tracking vehicle and a full lighting package for the night city work. Stills captured by a dedicated unit on the same setups rather than as a separate shoot.',
				'why'   => 'The campaign ran across film, print and social from a single production, which is where the budget efficiency actually came from.',
			),
			array(
				'title' => 'The Founders Room',
				'slug'  => 'the-founders-room',
				'sub'   => 'Series',
				'code'  => '033',
				'dur'   => '28:00',
				'client' => 'Investment group, Dubai',
				'svc'   => 'Podcast Production',
				'ind'   => 'Government',
				'fmt'   => 'Podcast',
				'kind'  => 'interior',
				'brief' => 'A twelve-episode branded interview series — format, set, recording and full post.',
				'chal'  => 'The group had booked studio time twice before and ended up with twelve hours of footage nobody edited. They did not need a room; they needed a show.',
				'dir'   => 'We built the format first: episode shape, segment structure, a title package and a hard twenty-eight-minute runtime. The set was designed to shoot four episodes a day without relighting.',
				'proc'  => 'Three recording days for twelve episodes, four cameras. Post delivered each episode as a long-form cut, a trailer, six vertical clips and a subtitled version.',
				'why'   => 'They published on schedule for twelve straight weeks. The first two attempts never published at all.',
			),
			array(
				'title' => 'Union Hour',
				'slug'  => 'union-hour',
				'sub'   => 'Corporate Film',
				'code'  => '021',
				'dur'   => '06:20',
				'client' => 'Financial institution, UAE',
				'svc'   => 'Commercials',
				'ind'   => 'Government',
				'fmt'   => 'Corporate film',
				'kind'  => 'portrait',
				'brief' => 'An internal culture film for a merger, screened to eleven thousand staff.',
				'chal'  => 'Two organisations with different histories and a workforce that had been reading rumours for a year. A film that oversold the merger would have been worse than no film at all.',
				'dir'   => 'No executives on camera for the first three minutes. We opened with the people whose jobs were actually changing, in their own words, including the uncertain ones.',
				'proc'  => 'Nineteen interviews across six sites in two weeks. The edit went through four approval rounds, unusually few for the sector, because the structure was agreed before the shoot.',
				'why'   => 'It was screened live in eleven locations simultaneously. Internal comms measured the highest engagement of any communication in the merger programme.',
			),
			array(
				'title' => 'Vertical City',
				'slug'  => 'vertical-city',
				'sub'   => 'Aerial Film',
				'code'  => '026',
				'dur'   => '02:40',
				'client' => 'Hospitality group, Dubai',
				'svc'   => 'Drone & Aerial',
				'ind'   => 'Hospitality',
				'fmt'   => 'Brand film',
				'kind'  => 'wide',
				'brief' => 'An aerial-led property film across three properties, with full permit management.',
				'chal'  => 'Three sites, two of them in restricted airspace, and a delivery date driven by a trade show.',
				'dir'   => 'The aerial work is the spine, but it is cut against handheld interior coverage at the same times of day, so the film reads as one continuous descent from the skyline into a room.',
				'proc'  => 'Permits were filed before the treatment was signed off, the only way the schedule worked. Five flying days across three weeks, weather-dependent, with two contingency days built in.',
				'why'   => 'It made the trade show. Two of the three sites had never been cleared for aerial coverage before.',
			),
		),
	);
}

/**
 * Seed the content.
 *
 * @return int Number of items created.
 */
function lmf_demo_install() {
	$data  = lmf_demo_data();
	$count = 0;

	$diffs = "Streaming pedigree: Netflix-original and top-rated-television credits on the leadership team. Rare in this market, and it shows in how films are structured.\n"
		. "End-to-end, in-house: Production and post under one roof. Fewer handoffs, one point of accountability, and no vendor blaming another in the final week.\n"
		. "GCC production experience: Permits, locations, crew and compliance across the UAE and the wider Gulf, including government approval chains.\n"
		. "Written before it is shot: You see the film as a treatment before a camera is booked. Most overruns are scoping failures rather than creative ones.";

	$faq = "What does a film cost in Dubai? | A single-day brand film with a small crew and in-house post starts in the mid five figures AED. A multi-day commercial with talent, a full lighting package and licensed music sits well above that. We give a banded estimate after the first conversation and a fixed quote after the treatment.\n"
		. "How long from brief to delivered master? | Six to eight weeks is comfortable for a single film including two rounds of revisions. Anything under three weeks means something is being cut, and we will tell you what.\n"
		. "How many rounds of revisions are included? | Two full rounds on the edit and one on the grade and mix, written into the quote. Further rounds are quoted rather than refused.\n"
		. "Do you handle filming permits? | Yes, including commission permits, location agreements and drone clearances. Permit lead times shape the schedule more than crew availability does, so we file early.\n"
		. "Can you work to an agency's concept? | Regularly. We execute agency creative as a production partner and are comfortable white-labelling.";

	$kit = "Camera: Large-format and Super 35 bodies, prime and zoom sets\n"
		. "Lighting: LED panel and tube packages, HMI, full grip and rigging\n"
		. "Sound: Multi-channel field recording, boom and lavalier, voiceover booth\n"
		. "Post: Offline and online suites, grading suite, motion graphics, subtitling\n"
		. "Aerial: Licensed operators, permit management, restricted-airspace clearance";

	// Services.
	foreach ( $data['services'] as $i => $s ) {
		if ( get_page_by_path( $s[1], OBJECT, 'lmf_service' ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'lmf_service',
				'post_status'  => 'publish',
				'post_title'   => $s[0],
				'post_name'    => $s[1],
				'post_excerpt' => $s[2],
				'menu_order'   => $i + 1,
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_lmf_keyword', $s[3] );
			update_post_meta( $id, '_lmf_cta', $s[4] );
			update_post_meta( $id, '_lmf_still', $s[5] );
			update_post_meta( $id, '_lmf_diffs', $diffs );
			update_post_meta( $id, '_lmf_faq', $faq );
			update_post_meta( $id, '_lmf_kit', $kit );
			$count++;
		}
	}

	// Industries.
	foreach ( $data['industries'] as $i => $n ) {
		if ( get_page_by_path( $n[1], OBJECT, 'lmf_industry' ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'lmf_industry',
				'post_status'  => 'publish',
				'post_title'   => $n[0],
				'post_name'    => $n[1],
				'post_excerpt' => $n[2],
				'menu_order'   => $i + 1,
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_lmf_still', $n[3] );
			update_post_meta( $id, '_lmf_needs', 'Written in this sector’s language rather than ours — replace with the real audience needs at content stage.' );
			update_post_meta( $id, '_lmf_considerations', 'Permits, clearances, site access and the approval chain this sector actually runs on.' );
			$count++;
		}
	}

	// Projects.
	foreach ( $data['projects'] as $i => $p ) {
		if ( get_page_by_path( $p['slug'], OBJECT, 'lmf_project' ) ) {
			continue;
		}
		// Insert as a draft: the publication gate fires on transition to publish
		// and the brief, terms and poster do not exist yet. Published at the end.
		$id = wp_insert_post(
			array(
				'post_type'   => 'lmf_project',
				'post_status' => 'draft',
				'post_title'  => $p['title'],
				'post_name'   => $p['slug'],
				'menu_order'  => $i + 1,
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}

		wp_set_object_terms( $id, $p['svc'], 'lmf_service_cat' );
		wp_set_object_terms( $id, $p['ind'], 'lmf_industry_cat' );
		wp_set_object_terms( $id, $p['fmt'], 'lmf_format' );

		update_post_meta( $id, '_lmf_code', $p['code'] );
		update_post_meta( $id, '_lmf_duration', $p['dur'] );
		update_post_meta( $id, '_lmf_client', $p['client'] );
		update_post_meta( $id, '_lmf_subtitle', $p['sub'] );
		update_post_meta( $id, '_lmf_brief', $p['brief'] );
		update_post_meta( $id, '_lmf_challenge', $p['chal'] );
		update_post_meta( $id, '_lmf_direction', $p['dir'] );
		update_post_meta( $id, '_lmf_process', $p['proc'] );
		update_post_meta( $id, '_lmf_why', $p['why'] );
		update_post_meta( $id, '_lmf_still', $p['kind'] );
		update_post_meta(
			$id,
			'_lmf_credits',
			"Director:\nDirector of Photography:\nProducer:\nEditor:\nColourist:\nSound:\nProduction Design:\nAgency:"
		);

		// Now that the required fields exist, publish. The gate still applies —
		// if a field were missing the project would correctly stay a draft.
		wp_update_post(
			array(
				'ID'          => $id,
				'post_status' => 'publish',
			)
		);
		$count++;
	}

	// Testimonials — sample copy, marked approved so the homepage block renders.
	$quotes = array(
		array( 'They asked what we needed the audience to feel before they asked what we wanted to shoot. That conversation changed the brief.', 'Marketing Director', 'Hospitality group, Dubai', 'vertical-city' ),
		array( 'We had tried this twice with other partners and never published. This time we shipped twelve episodes on schedule.', 'Head of Communications', 'Investment group, Dubai', 'the-founders-room' ),
		array( 'Four approval rounds on a merger film is unheard of internally. They got the structure right before anyone picked up a camera.', 'Internal Communications Lead', 'Financial institution, UAE', 'union-hour' ),
	);
	foreach ( $quotes as $i => $q ) {
		$title = 'Quote ' . ( $i + 1 );
		// get_page_by_title() is deprecated in WP 6.3; query by title instead.
		$exists = get_posts(
			array(
				'post_type'      => 'lmf_testimonial',
				'title'          => $title,
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'fields'         => 'ids',
			)
		);
		if ( $exists ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'lmf_testimonial',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_content' => $q[0],
				'menu_order'   => $i + 1,
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_lmf_who', $q[1] );
			update_post_meta( $id, '_lmf_org', $q[2] );
			update_post_meta( $id, '_lmf_approved', '1' );
			$rel = get_page_by_path( $q[3], OBJECT, 'lmf_project' );
			if ( $rel ) {
				update_post_meta( $id, '_lmf_project_id', $rel->ID );
			}
			$count++;
		}
	}

	// Pages.
	$pages = array(
		'privacy-policy' => array( 'Privacy Policy', LMF_PRIVACY_COPY ),
		'terms'          => array( 'Terms of Use', LMF_TERMS_COPY ),
		'about'   => array( 'About', "One team that writes it, shoots it, cuts it, grades it and mixes it.\n\nReplace this copy once the company story is confirmed." ),
		'studio'  => array( 'Studio', "Founded in Dubai in 2023. Brand films, commercials, documentary, original series and podcasts — produced and finished by the same team.\n\nReplace this copy once the company story is confirmed. Note that the brand guidelines and the strategy document give different addresses and different founder names; confirm which is current before publishing." ),
		'contact' => array( 'Contact', "Tell us what the audience needs to feel.\n\nWhatsApp and phone sit above the form: the fastest first response wins the meeting." ),
	);
	foreach ( $pages as $slug => $p ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $p[0],
				'post_name'    => $slug,
				'post_content' => $p[1],
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			// The template also follows the slug by itself; setting it makes
			// the choice visible in Page Attributes rather than implied.
			if ( file_exists( get_template_directory() . '/page-' . $slug . '.php' ) ) {
				update_post_meta( $id, '_wp_page_template', 'page-' . $slug . '.php' );
			}
			// WordPress keeps its own pointer to the privacy notice.
			if ( 'privacy-policy' === $slug ) {
				update_option( 'wp_page_for_privacy_policy', $id );
			}
			$count++;
		}
	}

	// Homepage: a static front page, since front-page.php is the designed one.
	$home = get_page_by_path( 'home' );
	if ( ! $home ) {
		$home_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Home',
				'post_name'   => 'home',
			)
		);
		if ( $home_id && ! is_wp_error( $home_id ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_id );
			$count++;
		}
	}

	// Primary menu.
	if ( ! wp_get_nav_menu_object( 'Primary' ) ) {
		$menu_id = wp_create_nav_menu( 'Primary' );
		if ( ! is_wp_error( $menu_id ) ) {
			$items = array(
				array( __( 'Work', 'lemonmint' ), get_post_type_archive_link( 'lmf_project' ) ),
				array( __( 'Services', 'lemonmint' ), get_post_type_archive_link( 'lmf_service' ) ),
				array( __( 'Industries', 'lemonmint' ), get_post_type_archive_link( 'lmf_industry' ) ),
				array( __( 'Studio', 'lemonmint' ), home_url( '/studio/' ) ),
				array( __( 'About', 'lemonmint' ), home_url( '/about/' ) ),
				array( __( 'Contact', 'lemonmint' ), home_url( '/contact/' ) ),
			);
			$parents = array();
			foreach ( $items as $it ) {
				$parents[ $it[0] ] = wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'  => $it[0],
						'menu-item-url'    => $it[1],
						'menu-item-status' => 'publish',
					)
				);
			}
			// Services children, so the mega panel has something to show.
			foreach ( get_posts( array( 'post_type' => 'lmf_service', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $s ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'       => get_the_title( $s ),
						'menu-item-url'         => get_permalink( $s ),
						'menu-item-description' => get_the_excerpt( $s ),
						'menu-item-parent-id'   => $parents[ __( 'Services', 'lemonmint' ) ],
						'menu-item-status'      => 'publish',
					)
				);
			}
			set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu_id ) );
		}
	}

	// Only set the tagline while it is still the WordPress default.
	if ( in_array( get_option( 'blogdescription' ), array( '', 'Just another WordPress site' ), true ) ) {
		update_option( 'blogdescription', 'A production house in Dubai making cinematic content for brands, organisations, agencies and creators — from first idea to final cut.' );
	}
	flush_rewrite_rules();

	return $count;
}

/**
 * Remove everything the seeder created.
 *
 * @return int Number removed.
 */
function lmf_demo_remove() {
	$count = 0;
	foreach ( array( 'lmf_project', 'lmf_service', 'lmf_industry', 'lmf_person', 'lmf_testimonial' ) as $type ) {
		foreach ( get_posts( array( 'post_type' => $type, 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) {
			wp_delete_post( $p->ID, true );
			$count++;
		}
	}
	foreach ( array( 'lmf_service_cat', 'lmf_industry_cat', 'lmf_format' ) as $tx ) {
		foreach ( get_terms( array( 'taxonomy' => $tx, 'hide_empty' => false ) ) as $term ) {
			if ( ! is_wp_error( $term ) ) {
				wp_delete_term( $term->term_id, $tx );
			}
		}
	}
	foreach ( array( 'about', 'studio', 'contact', 'privacy-policy', 'terms', 'home' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			wp_delete_post( $page->ID, true );
			$count++;
		}
	}
	update_option( 'show_on_front', 'posts' );
	delete_option( 'page_on_front' );
	$menu = wp_get_nav_menu_object( 'Primary' );
	if ( $menu ) {
		wp_delete_nav_menu( $menu->term_id );
	}
	flush_rewrite_rules();
	return $count;
}
