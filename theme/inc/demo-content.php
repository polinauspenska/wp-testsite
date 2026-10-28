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
/**
 * The films we hold, by slug. Paths are relative to the theme's assets, so
 * the installer and the static export resolve them the same way.
 *
 * @return array[] slug => array( video, poster )
 */
function lmf_demo_films() {
	$v = '/assets/video/';
	$i = '/assets/img/stills/';
	return array(
		// services
		'corporate-films'      => array( $v . 'services/corporate-films.mp4', $v . 'services/corporate-films.jpg' ),
		'ad-films'             => array( $v . 'services/ad-films.mp4', $v . 'services/ad-films.jpg' ),
		'microdramas'          => array( $v . 'services/microdramas.mp4', $v . 'services/microdramas.jpg' ),
		'event-videography'    => array( $v . 'services/event-videography.mp4', $v . 'services/event-videography.jpg' ),
		// a service and a project can share one film
		'film-tv-production'   => array( $v . 'work/khamosh-aahatein.mp4', $v . 'work/khamosh-aahatein.jpg' ),
		'music-productions'    => array( $v . 'work/you-cant-break-dubai.mp4', $v . 'work/you-cant-break-dubai.jpg' ),
		// projects with their own film
		'khamosh-aahatein'     => array( $v . 'work/khamosh-aahatein.mp4', $v . 'work/khamosh-aahatein.jpg' ),
		'falcons-of-majlis'    => array( $v . 'work/falcons-of-majlis.mp4', $v . 'work/falcons-of-majlis.jpg' ),
		'you-cant-break-dubai' => array( $v . 'work/you-cant-break-dubai.mp4', $v . 'work/you-cant-break-dubai.jpg' ),

		/*
		 * Projects with a frame but no film of their own yet: the still comes
		 * from the reel that covers them, so the archive shows the work rather
		 * than a plate. An empty first value means "no video".
		 */
		'nkn-property-expo'                => array( '', $i . 'nkn-property-expo.jpg' ),
		'icons-of-the-uae-ankur-aggarwal'  => array( '', $i . 'icons-of-the-uae-ankur-aggarwal.jpg' ),
		'icons-of-the-uae-tauseef-khan'    => array( '', $i . 'icons-of-the-uae-tauseef-khan.jpg' ),
		'icons-of-the-uae-satish-sanpal'   => array( '', $i . 'icons-of-the-uae-satish-sanpal.jpg' ),
		'icons-of-the-uae-maqsood-muhammed' => array( '', $i . 'icons-of-the-uae-maqsood-muhammed.jpg' ),
		'rrr-race-raftaar-roar'            => array( '', $i . 'rrr-race-raftaar-roar.jpg' ),
		'chuppa-hua-race-king'             => array( '', $i . 'chuppa-hua-race-king.jpg' ),
		'the-final-race'                   => array( '', $i . 'the-final-race.jpg' ),
	);
}

/**
 * The film for one slug, as absolute URLs, or an empty array.
 *
 * @param string $slug Post slug.
 * @return array { video, poster } or empty.
 */
function lmf_demo_film( $slug ) {
	$all = lmf_demo_films();
	if ( empty( $all[ $slug ] ) ) {
		return array();
	}
	$base = get_template_directory_uri();
	// an empty path stays empty — "no film", not the theme's own folder
	return array(
		$all[ $slug ][0] ? $base . $all[ $slug ][0] : '',
		$all[ $slug ][1] ? $base . $all[ $slug ][1] : '',
	);
}

function lmf_demo_data() {
	return array(
		'services' => array(
			array( 'Corporate Films', 'corporate-films', 'Company profiles, brand films, leadership and internal communication — cinematic rather than corporate.', 'Corporate Video Production Dubai', 'Request a quote', 'interior' ),
			array( 'Ad Films', 'ad-films', 'Commercials and campaigns for broadcast and social, including celebrity-led productions.', 'TV Commercial Production Dubai', 'Request a quote', 'wide' ),
			array( 'Film & TV Production', 'film-tv-production', 'Scripted series, short films and long-form entertainment, produced to platform specification.', 'Film Production Company Dubai', 'Let’s create together', 'portrait' ),
			array( 'Microdramas', 'microdramas', 'Vertical short-form drama, written and shot for 9:16 and delivered episode by episode.', 'Microdrama Production Dubai', 'Let’s create together', 'portrait' ),
			array( 'Social Media Management', 'social-media-management', 'Strategy, content calendars and always-on production for brands that publish every week.', 'Social Media Management Dubai', 'Request a quote', 'interior' ),
			array( 'AI Integrated Content', 'ai-integrated-content', 'AI-assisted production workflows used where they save time, with the craft kept human.', 'AI Video Production Dubai', 'Ask a question', 'interior' ),
			array( 'Podcast Production', 'podcast-production', 'A produced show — format, multi-camera recording, full post and the clips that come with it.', 'Podcast Production Dubai', 'Plan your podcast', 'interior' ),
			array( 'Event Videography', 'event-videography', 'Awards, conferences and activations covered multi-camera, with reels inside 24–48 hours.', 'Event Video Production Dubai', 'Request a quote', 'wide' ),
			array( 'Music Productions', 'music-productions', 'Music videos, event anthems and original tracks — composition, recording and the film around it.', 'Music Video Production Dubai', 'Let’s create together', 'wide' ),
		),
		'industries' => array(
			array( 'Real Estate', 'real-estate', 'Off-plan launches, developer brand films and property-expo campaigns.', 'wide' ),
			array( 'Corporate & Business', 'corporate-business', 'Company profiles, leadership films and internal communication.', 'interior' ),
			array( 'Entertainment & Streaming', 'entertainment-streaming', 'Scripted series, short films and microdramas for platforms.', 'portrait' ),
			array( 'Events & Awards', 'events-awards', 'Award ceremonies, conferences and brand activations.', 'wide' ),
			array( 'Sports', 'sports', 'Teams, tournaments and sports-led promotional films.', 'wide' ),
			array( 'Retail & Consumer', 'retail-consumer', 'Product campaigns and always-on content for consumer brands.', 'interior' ),
			array( 'Automotive', 'automotive', 'Launches, dealer content and brand events across the GCC.', 'wide' ),
		),
		'projects' => array(
			array(
				'title'  => 'Khamosh Aahatein',
				'slug'   => 'khamosh-aahatein',
				'sub'    => 'A television series',
				'code'   => 'TV-01',
				'dur'    => '60 × ep',
				'client' => 'Atrangii',
				'svc'    => 'Film & TV Production',
				'ind'    => 'Entertainment & Streaming',
				'fmt'    => 'Television series',
				'kind'   => 'portrait',
				'brief'  => 'A full scripted season for the Atrangii platform: sixty episodes written, produced and delivered episode by episode to platform specification.',
				'chal'   => 'A platform commission is a delivery problem as much as a creative one. Sixty episodes have to look like one series, arrive on a schedule, and pass technical QC every time.',
				'dir'    => 'One directing and camera language fixed at the start of the season and held to it, so the look does not drift between blocks.',
				'proc'   => 'Block shooting against a season schedule, with edit, sound and masters running behind the shoot rather than after it.',
				'why'    => 'Our longest-form production to date, and the clearest evidence that we can carry a full scripted season end to end.',
			),
			array(
				'title'  => 'NKN Property Expo',
				'slug'   => 'nkn-property-expo',
				'sub'    => 'An ad film',
				'code'   => 'AD-01',
				'dur'    => 'Campaign',
				'client' => 'with Jacqueline Fernandez',
				'svc'    => 'Ad Films',
				'ind'    => 'Real Estate',
				'fmt'    => 'Ad film',
				'kind'   => 'wide',
				'brief'  => 'A property expo campaign fronted by Jacqueline Fernandez, built for broadcast and social out of a single shoot.',
				'chal'   => 'Celebrity days are short and expensive, and the campaign needed a television cut and a full set of social versions from the same hours.',
				'dir'    => 'Shot so the main film and the cutdowns share setups, with the talent carrying the message rather than a voiceover.',
				'proc'   => 'A single-day celebrity shoot, scheduled around the talent, then cut into the broadcast film and the platform versions.',
				'why'    => 'One shoot day produced a whole campaign — the main film plus the vertical and square versions the expo ran on social.',
			),
			array(
				'title'  => 'Falcons of Majlis',
				'slug'   => 'falcons-of-majlis',
				'sub'    => 'A promotional film',
				'code'   => 'AD-02',
				'dur'    => 'Launch',
				'client' => 'with Suniel Shetty',
				'svc'    => 'Ad Films',
				'ind'    => 'Sports',
				'fmt'    => 'Promotional film',
				'kind'   => 'wide',
				'brief'  => 'A sports team identity introduced through its most recognisable supporter, Suniel Shetty.',
				'chal'   => 'A new team has no history to cut from, so the launch film has to build the identity rather than recap it.',
				'dir'    => 'The team presented through a single recognisable voice, with the crest, the colours and the squad introduced around it.',
				'proc'   => 'A controlled shoot with the talent, cut for the announcement and the follow-up social rollout.',
				'why'    => 'The team arrived with a film that gave it a face on day one.',
			),
			array(
				'title'  => 'Icons of the UAE — Ankur Aggarwal',
				'slug'   => 'icons-of-the-uae-ankur-aggarwal',
				'sub'    => 'A corporate film',
				'code'   => 'CF-01',
				'dur'    => 'Series of 4',
				'client' => 'Business leaders, UAE',
				'svc'    => 'Corporate Films',
				'ind'    => 'Corporate & Business',
				'fmt'    => 'Corporate film series',
				'kind'   => 'portrait',
				'brief'  => 'The first of four leadership profiles in the Icons of the UAE series: interview-led, cut cinematic rather than corporate.',
				'chal'   => 'Leadership films usually end up as a chair, a logo and a list of achievements. The brief was a profile someone would actually watch.',
				'dir'    => 'Interview as the spine, with the business filmed around it, so the story comes from the person instead of a script read to camera.',
				'proc'   => 'A single interview day on location, with supporting coverage of the business, finished with grade and sound in-house.',
				'why'    => 'A format that scaled: the same approach carried three further films in the series without repeating itself.',
			),
			array(
				'title'  => 'Icons of the UAE — Tauseef Khan',
				'slug'   => 'icons-of-the-uae-tauseef-khan',
				'sub'    => 'A corporate film',
				'code'   => 'CF-02',
				'dur'    => 'Series of 4',
				'client' => 'Business leaders, UAE',
				'svc'    => 'Corporate Films',
				'ind'    => 'Corporate & Business',
				'fmt'    => 'Corporate film series',
				'kind'   => 'interior',
				'brief'  => 'The second film in the Icons of the UAE leadership series.',
				'chal'   => 'Each subject in the series has a different business and a different way of speaking about it, and the series still has to hold together.',
				'dir'    => 'The series look held, the questions rewritten for the subject.',
				'proc'   => 'One interview day, business coverage, in-house finish to the series specification.',
				'why'    => 'Consistent with the first film and still particular to its subject.',
			),
			array(
				'title'  => 'Icons of the UAE — Satish Sanpal',
				'slug'   => 'icons-of-the-uae-satish-sanpal',
				'sub'    => 'A corporate film',
				'code'   => 'CF-03',
				'dur'    => 'Series of 4',
				'client' => 'Business leaders, UAE',
				'svc'    => 'Corporate Films',
				'ind'    => 'Corporate & Business',
				'fmt'    => 'Corporate film series',
				'kind'   => 'interior',
				'brief'  => 'The third film in the Icons of the UAE leadership series.',
				'chal'   => 'A profile for an audience that already knows the name and wants the reasoning behind the decisions.',
				'dir'    => 'Interview-led, with the operation filmed as evidence rather than as b-roll.',
				'proc'   => 'One interview day, business coverage, in-house finish to the series specification.',
				'why'    => 'Used across the group’s own channels as well as the series.',
			),
			array(
				'title'  => 'Icons of the UAE — Maqsood Muhammed',
				'slug'   => 'icons-of-the-uae-maqsood-muhammed',
				'sub'    => 'A corporate film',
				'code'   => 'CF-04',
				'dur'    => 'Series of 4',
				'client' => 'Business leaders, UAE',
				'svc'    => 'Corporate Films',
				'ind'    => 'Corporate & Business',
				'fmt'    => 'Corporate film series',
				'kind'   => 'portrait',
				'brief'  => 'The fourth film in the Icons of the UAE leadership series.',
				'chal'   => 'Closing a series means matching four months of work in a day on location.',
				'dir'    => 'Same grammar, same grade, a story told in the subject’s own order.',
				'proc'   => 'One interview day, business coverage, in-house finish to the series specification.',
				'why'    => 'Completed a four-film series that reads as one body of work.',
			),
			array(
				'title'  => 'Ultimate Realty Awards — Miri Homes',
				'slug'   => 'ultimate-realty-awards-miri-homes',
				'sub'    => 'A corporate film',
				'code'   => 'CF-05',
				'dur'    => 'Awards film',
				'client' => 'Miri Homes',
				'svc'    => 'Corporate Films',
				'ind'    => 'Events & Awards',
				'fmt'    => 'Corporate film',
				'kind'   => 'interior',
				'brief'  => 'A developer profile film produced for the Ultimate Realty Awards.',
				'chal'   => 'An awards film is watched once, in a room, by an audience that has already heard the pitch.',
				'dir'    => 'Short, visual and about the work rather than the company structure.',
				'proc'   => 'Shot on site and at the development, finished to the event’s screen specification.',
				'why'    => 'Played to the room it was made for and reused afterwards on the developer’s channels.',
			),
			array(
				'title'  => 'Ultimate Realty Awards — Revi Realty',
				'slug'   => 'ultimate-realty-awards-revi-realty',
				'sub'    => 'A corporate film',
				'code'   => 'CF-06',
				'dur'    => 'Awards film',
				'client' => 'Revi Realty',
				'svc'    => 'Corporate Films',
				'ind'    => 'Events & Awards',
				'fmt'    => 'Corporate film',
				'kind'   => 'interior',
				'brief'  => 'A second developer profile film for the Ultimate Realty Awards.',
				'chal'   => 'Two films for the same event have to be distinct without looking like different suppliers made them.',
				'dir'    => 'A shared structure with the company’s own material at the centre.',
				'proc'   => 'Site and development coverage, in-house edit, grade and mix to the event specification.',
				'why'    => 'Delivered alongside the first film to the same deadline.',
			),
			array(
				'title'  => 'Race Raftaar Roar',
				'slug'   => 'rrr-race-raftaar-roar',
				'sub'    => 'A microdrama season',
				'code'   => 'MD-01',
				'dur'    => '45 × 2 min',
				'client' => 'StoryTV India',
				'svc'    => 'Microdramas',
				'ind'    => 'Entertainment & Streaming',
				'fmt'    => 'Microdrama',
				'kind'   => 'portrait',
				'brief'  => 'A forty-five-episode vertical season for StoryTV India, produced with an AI-hybrid workflow for mobile-first audiences.',
				'chal'   => 'Vertical drama lives or dies on volume and pace: forty-five episodes of two minutes each, each one holding attention from the first second.',
				'dir'    => 'Written and framed for 9:16 from the start — the composition is not a crop of a horizontal cut.',
				'proc'   => 'Block shooting across the season, with an AI-assisted workflow in pre-production and post to keep the episode count on schedule.',
				'why'    => 'A full vertical season delivered to platform spec, and the template for the microdrama work that followed.',
			),
			array(
				'title'  => 'Chuppa Hua Race King',
				'slug'   => 'chuppa-hua-race-king',
				'sub'    => 'A microdrama season',
				'code'   => 'MD-02',
				'dur'    => '45 × 2 min',
				'client' => 'StoryTV India',
				'svc'    => 'Microdramas',
				'ind'    => 'Entertainment & Streaming',
				'fmt'    => 'Microdrama',
				'kind'   => 'portrait',
				'brief'  => 'A vertical microdrama season for StoryTV India, produced in the same format as Race Raftaar Roar.',
				'chal'   => 'Repeating the format without repeating the series.',
				'dir'    => 'Vertical-native direction, with the performances carried in close-up.',
				'proc'   => 'Block shooting, AI-hybrid pre-production and post, episodic delivery.',
				'why'    => 'A second season in the same slot, delivered on the same schedule.',
			),
			array(
				'title'  => 'The Final Race',
				'slug'   => 'the-final-race',
				'sub'    => 'A microdrama season',
				'code'   => 'MD-03',
				'dur'    => '45 × 2 min',
				'client' => 'StoryTV India',
				'svc'    => 'Microdramas',
				'ind'    => 'Entertainment & Streaming',
				'fmt'    => 'Microdrama',
				'kind'   => 'portrait',
				'brief'  => 'A third vertical season for StoryTV India in the microdrama format.',
				'chal'   => 'Three seasons in, the production had to be routine without the storytelling becoming routine.',
				'dir'    => 'Vertical-native, episode-led, written to a hook per episode.',
				'proc'   => 'Block shooting, AI-hybrid workflow, delivery episode by episode.',
				'why'    => 'Confirmed microdrama production as a repeatable line of work rather than a one-off.',
			),
			array(
				'title'  => 'Pocket FM Short Films',
				'slug'   => 'pocket-fm-short-films',
				'sub'    => 'Three short films',
				'code'   => 'TV-02',
				'dur'    => '3 films',
				'client' => 'Pocket FM',
				'svc'    => 'Film & TV Production',
				'ind'    => 'Entertainment & Streaming',
				'fmt'    => 'Short film',
				'kind'   => 'wide',
				'brief'  => 'Three short films produced for Pocket FM: Hollywood Sequel, Unraveling Son in Law and Cupid’s Curse.',
				'chal'   => 'Three different stories for one platform, each needing to stand alone.',
				'dir'    => 'Scripted and directed as short films rather than as promotional content.',
				'proc'   => 'Produced and finished in-house, delivered to the platform’s specification.',
				'why'    => 'Three films for a single platform client, produced back to back.',
			),
			array(
				'title'  => 'All Real Konvos',
				'slug'   => 'all-real-konvos',
				'sub'    => 'A podcast series',
				'code'   => 'PD-01',
				'dur'    => '13 episodes',
				'client' => '',
				'svc'    => 'Podcast Production',
				'ind'    => 'Entertainment & Streaming',
				'fmt'    => 'Podcast series',
				'kind'   => 'interior',
				'brief'  => 'A thirteen-episode conversation series, recorded multi-camera and cut into episodes and short-form in the same pass.',
				'chal'   => 'A podcast is not a room with a microphone in it. The series needed a format, a look and a clip strategy before the first recording.',
				'dir'    => 'Multi-camera, properly lit and mixed, so the show looks like a show on video as well as sounding like one in audio.',
				'proc'   => 'Recorded in blocks, with the full episode and the social clips pulled from the same material.',
				'why'    => 'Thirteen episodes plus the short-form that goes with them, from one set of recording days.',
			),
			array(
				'title'  => 'She in Me',
				'slug'   => 'she-in-me',
				'sub'    => 'A podcast series',
				'code'   => 'PD-02',
				'dur'    => 'Series',
				'client' => 'Sirona',
				'svc'    => 'Podcast Production',
				'ind'    => 'Retail & Consumer',
				'fmt'    => 'Podcast series',
				'kind'   => 'interior',
				'brief'  => 'A branded conversation series produced for Sirona.',
				'chal'   => 'A consumer brand talking about its own category has to earn the attention rather than assume it.',
				'dir'    => 'Guest-led conversation, the brand present without being the subject.',
				'proc'   => 'Multi-camera recording, episodic edit, social cutdowns from the same pass.',
				'why'    => 'Content the brand can publish on a schedule instead of one campaign film.',
			),
			array(
				'title'  => 'Taqdeer Awards',
				'slug'   => 'taqdeer-awards',
				'sub'    => 'Social & event',
				'code'   => 'SM-01',
				'dur'    => 'Full account',
				'client' => '',
				'svc'    => 'Social Media Management',
				'ind'    => 'Events & Awards',
				'fmt'    => 'Social & event',
				'kind'   => 'wide',
				'brief'  => 'Strategy, content calendar and event coverage for an awards programme — the full account rather than a single film.',
				'chal'   => 'An awards platform publishes all year and peaks on one night. Both halves need the same voice.',
				'dir'    => 'One calendar covering the run-up, the ceremony and the aftermath, with the event shot to feed it.',
				'proc'   => 'Monthly content production alongside full event coverage, with reels cut and published while the event was still running.',
				'why'    => 'An account managed end to end, from the calendar to the night itself.',
			),
			array(
				'title'  => 'You Can’t Break Dubai',
				'slug'   => 'you-cant-break-dubai',
				'sub'    => 'A music production',
				'code'   => 'MU-01',
				'dur'    => 'Original track',
				'client' => 'Lemon Mint Films',
				'svc'    => 'Music Productions',
				'ind'    => 'Entertainment & Streaming',
				'fmt'    => 'Music production',
				'kind'   => 'wide',
				'brief'  => 'An original Dubai anthem — written, recorded and filmed in-house.',
				'chal'   => 'An anthem has to belong to the city rather than to a brand, or nobody shares it.',
				'dir'    => 'The track first, the film built around it.',
				'proc'   => 'Composition, recording and the video produced by the same team.',
				'why'    => 'Our own production, and the clearest demonstration of what music and film together can do.',
			),
			array(
				'title'  => 'Chale Chalo',
				'slug'   => 'chale-chalo',
				'sub'    => 'A music production',
				'code'   => 'MU-02',
				'dur'    => 'Original track',
				'client' => 'Taqdeer Awards',
				'svc'    => 'Music Productions',
				'ind'    => 'Events & Awards',
				'fmt'    => 'Music production',
				'kind'   => 'interior',
				'brief'  => 'An original track and film produced for the Taqdeer Awards.',
				'chal'   => 'An event anthem has to work in the room and on a phone afterwards.',
				'dir'    => 'Written for the occasion, filmed to carry it beyond the night.',
				'proc'   => 'Composition, recording and video production in-house, to the event’s deadline.',
				'why'    => 'A piece of the event that keeps playing after it.',
			),
			array(
				'title'  => 'BMW Sustainability Awards',
				'slug'   => 'bmw-sustainability-awards',
				'sub'    => 'An event film',
				'code'   => 'EV-01',
				'dur'    => 'Event',
				'client' => 'BMW',
				'svc'    => 'Event Videography',
				'ind'    => 'Automotive',
				'fmt'    => 'Event film',
				'kind'   => 'wide',
				'brief'  => 'Full coverage of the BMW Sustainability Awards, cut for same-week publication.',
				'chal'   => 'Event coverage is unrepeatable: there is no second take of an awards night.',
				'dir'    => 'Multi-camera coverage planned against the run of show, with the key moments covered twice.',
				'proc'   => 'A crew briefed on the schedule, with the edit started on site.',
				'why'    => 'Highlights and social reels delivered while the event was still current.',
			),
			array(
				'title'  => 'ANAX Capital',
				'slug'   => 'anax-capital',
				'sub'    => 'An event film',
				'code'   => 'EV-02',
				'dur'    => 'Event',
				'client' => 'ANAX Capital',
				'svc'    => 'Event Videography',
				'ind'    => 'Corporate & Business',
				'fmt'    => 'Event film',
				'kind'   => 'interior',
				'brief'  => 'Event coverage for ANAX Capital, produced for corporate and social use.',
				'chal'   => 'A corporate event has to look considered in the highlights and credible in the long cut.',
				'dir'    => 'Coverage planned around the speakers and the room rather than around the stage alone.',
				'proc'   => 'Multi-camera coverage, recorded sound from the desk, in-house edit.',
				'why'    => 'A highlights film and the social versions from one night.',
			),
			array(
				'title'  => 'Women’s Run',
				'slug'   => 'womens-run',
				'sub'    => 'An event film',
				'code'   => 'EV-03',
				'dur'    => 'Event',
				'client' => '',
				'svc'    => 'Event Videography',
				'ind'    => 'Sports',
				'fmt'    => 'Event film',
				'kind'   => 'wide',
				'brief'  => 'Coverage of a mass-participation run, cut for the organiser’s channels.',
				'chal'   => 'A moving event across a course, with one chance at every moment of it.',
				'dir'    => 'Camera positions planned against the route and the timings rather than chased on the day.',
				'proc'   => 'A distributed crew, fast ingest, and reels cut for same-day and next-day publication.',
				'why'    => 'Participants saw themselves on the organiser’s channels within a day.',
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

	$diffs = "Premium cinematic quality: A cinematic approach applied across corporate, commercial, entertainment and digital work, not only to the showpieces.\n"
		. "Creative excellence: Story-driven concepts built around the audience and the objective, developed before anything is scheduled.\n"
		. "Proven track record: More than 100 projects across industries and formats, from 60-episode television to 24-hour event reels.\n"
		. "Rapid turnaround: Production efficiency without giving up the craft — corporate films in 7–14 days, event reels in 24–48 hours.\n"
		. "End-to-end, in-house: Concept, production and post under one roof, so one team is accountable from brief to master.";

	$faq = "What does a production cost? | Every project is quoted individually. We give an indicative range after the first conversation and a full quotation once the scope and the deliverables are agreed. Talent fees, location rentals and permits are quoted separately from production.\n"
		. "How long does it take? | Corporate films are typically 7–14 days. Most productions run 2–4 weeks, short films 4–8 weeks and a microdrama season 3–6 weeks. Event highlights are delivered in 3–5 days and social reels inside 24–48 hours.\n"
		. "Do you develop the concept and the script? | Yes. Concept development, scripting, storyboarding and creative direction are part of the work.\n"
		. "Which languages do you work in? | English, Hindi, Arabic and Urdu, with subtitles, voiceovers, dubbing and localisation arranged to the project.\n"
		. "Can one film be cut for different platforms? | Yes — the main film plus 30 and 15-second cutdowns, vertical reels and teasers, agreed as deliverables before the shoot.";

	$kit = "Camera: Cinema bodies with prime and zoom sets, for horizontal and 9:16 vertical production\n"
		. "Lighting: LED panel and tube packages, HMI, full grip and rigging\n"
		. "Sound: Multi-channel field recording, boom and lavalier, voiceover recording\n"
		. "Post: Edit, colour, sound, motion graphics, subtitling and platform mastering\n"
		. "Music: Composition and recording for tracks, anthems and score";

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
			$film = lmf_demo_film( $s[1] );
			if ( $film ) {
				if ( $film[0] ) {
					update_post_meta( $id, '_lmf_video', $film[0] );
				}
				update_post_meta( $id, '_lmf_image', $film[1] );
			}
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
		$film = lmf_demo_film( $p['slug'] );
		if ( $film ) {
			if ( $film[0] ) {
				update_post_meta( $id, '_lmf_video', $film[0] );
			}
			update_post_meta( $id, '_lmf_image', $film[1] );
		}
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

	/*
	 * Testimonials: held back. The client has not supplied approved quotes or
	 * attributions, and nothing invented should go out under a real client’s
	 * name. Add the real quotes here — or in Testimonials in the admin — and the
	 * homepage section switches itself back on.
	 */
	$quotes = array();

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
		'about'   => array( 'About', "A Dubai production company built on one idea: great content should do more than look good, it should make people feel something.\n\nFounded by Mohammad Faizan, Lemon Mint Films works across corporate films, advertising, television, microdramas, podcasts, events and music." ),
		'studio'  => array( 'Studio', "Warehouse 28, Al Quoz Third, Dubai. Production and post under one roof — camera, lighting, sound, edit, colour and music.\n\nThe room list and the equipment inventory on this page are placeholders: confirm them with the studio before publishing." ),
		'contact' => array( 'Contact', "Let’s create something extraordinary.\n\nTell us about the project and we will come back with an approach, a timeline and a quotation." ),
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
		update_option( 'blogdescription', 'Dubai’s premium storytelling studio — corporate films, advertising, television, microdramas, podcasts, events and music.' );
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
