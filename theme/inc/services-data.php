<?php
/**
 * SERVICE PAGES — what each one says, in the same order every time.
 *
 * Nine services, one shape. Every page answers the same questions in the
 * same sequence, so a buyer comparing two of them is comparing like with
 * like, and so a page that is missing a part simply drops that block
 * instead of improvising a different layout.
 *
 *   lede      one sentence under the title
 *   audience  who it is for — the document's "Main Target Clients"
 *   types     what we make — "Typical Project Types"
 *   problems  what it solves — "Common Client Problems"
 *   uvp       why it is ours — "Unique Value Proposition"
 *   process   how it runs — "Typical Production Process"
 *   facts     turnaround, languages, locations, crew, kit, pricing
 *   faq       the studio's own client questions, with their answers
 *   related   the services that usually come with it
 *
 * Everything here comes from Lemon Mint's service document. A field we do
 * not have stays empty: the page then leaves that block out rather than
 * showing invented copy. ACF overrides any of it per service.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * The shape every service is filled into.
 *
 * @return array
 */
function lmf_service_info_blank() {
	return array(
		'lede'     => '',
		'audience' => '',
		'types'    => array(),
		'problems' => array(),
		'uvp'      => '',
		'process'  => array(),
		'facts'    => array(),
		'faq'      => array(),
		'related'  => array(),
	);
}

/**
 * The nine services.
 *
 * @return array[] slug => info
 */
function lmf_service_info_all() {
	$all = array(

		/* ---------------------------------------------------------------
		   01 — CORPORATE FILMS
		   --------------------------------------------------------------- */
		'corporate-films' => array(
			'lede'     => 'Corporate storytelling told cinematically: the concept, the narrative, the production and the finished film.',
			'audience' => 'Corporations, real estate developers, government entities, entrepreneurs, business leaders and professional organisations.',
			'types'    => array(
				'Corporate profile films',
				'Company documentaries',
				'Leadership and executive films',
				'Corporate brand stories',
				'Business achievement films',
				'Internal communication videos',
				'Corporate award and recognition films',
				'Real estate and development films',
			),
			'problems' => array(
				'Communicating a company’s vision and achievements',
				'Building brand credibility',
				'Presenting leadership and business milestones',
				'Simplifying complex business information',
				'Creating compelling content for stakeholders, investors and customers',
			),
			'uvp'      => 'We approach corporate filmmaking through cinematic storytelling rather than conventional corporate communication: films that carry the business objective and still connect emotionally with the people watching.',
			'process'  => array(
				array( 'Briefing', 'Client briefing and objective identification.' ),
				array( 'Concept', 'Creative concept and narrative development.' ),
				array( 'Pre-production', 'Planning, scheduling, locations and permissions.' ),
				array( 'Filming', 'Direction, cinematography, lighting and sound.' ),
				array( 'Delivery', 'Editing, finishing and final delivery.' ),
			),
			'facts'    => array(
				'Turnaround' => '7–14 days',
				'Languages'  => 'English, Hindi, Arabic',
				'Locations'  => 'Our studio, or the client’s office or warehouse',
				'Crew'       => 'Director, producer, director of photography, production team, writer, post-production team',
				'Equipment'  => 'Cinematic cameras and lights, professional editing',
				'Pricing'    => 'Custom quotation',
			),
			'faq'      => array(
				array(
					'How long does a corporate film take to produce?',
					'A typical corporate film takes approximately 2–4 weeks, depending on the complexity of the concept, the number of filming locations, production requirements and post-production scope. Larger productions may require additional time, while simpler projects can be delivered on an accelerated schedule.',
				),
				array(
					'Can you develop the script and creative concept?',
					'Yes. We can support the creative process from initial concept development to scripting and storytelling. We work closely with clients to understand their brand, objectives and target audience before developing a narrative that communicates their message effectively.',
				),
				array(
					'Can you film at our offices or multiple locations?',
					'Yes. We can plan productions at corporate offices, commercial facilities, outdoor locations and multiple filming sites. Location requirements, permissions and logistics are discussed during pre-production to ensure efficient filming.',
				),
				array(
					'Do you provide shorter versions for social media?',
					'Yes. Corporate films can be adapted into shorter versions for LinkedIn, Instagram, YouTube and other digital platforms — promotional edits, executive highlights, vertical reels and short-form campaign content, depending on the agreed deliverables.',
				),
				array(
					'How many revisions are included?',
					'The number of revisions depends on the project’s scope and agreed quotation. We establish the revision structure before production begins so clients have clarity on the feedback and approval process.',
				),
				array(
					'Can you accommodate executive interviews?',
					'Yes. Executive interviews and leadership storytelling can be built into corporate films. We can help structure the questions, plan the filming setup and integrate the interview into the overall narrative.',
				),
			),
			'related'  => array( 'ad-films', 'event-videography', 'social-media-management' ),
		),

		/* ---------------------------------------------------------------
		   The remaining eight are filled from the same document, field for
		   field. Until then each page shows the parts it has.
		   --------------------------------------------------------------- */
		'ad-films'               => array(),
		'film-tv-production'     => array(),
		'microdramas'            => array(),
		'social-media-management' => array(),
		'ai-integrated-content'  => array(),
		'podcast-production'     => array(),
		'event-videography'      => array(),
		'music-productions'      => array(),
	);

	return apply_filters( 'lmf_service_info_all', $all );
}

/**
 * One service, always in the full shape.
 *
 * @param string $slug Service slug.
 * @return array
 */
function lmf_service_info( $slug ) {
	$all  = lmf_service_info_all();
	$info = isset( $all[ $slug ] ) ? $all[ $slug ] : array();

	return apply_filters( 'lmf_service_info', array_merge( lmf_service_info_blank(), $info ), $slug );
}

/**
 * A service's title by slug, for the "related services" row.
 *
 * @param string $slug Service slug.
 * @return string
 */
function lmf_service_title( $slug ) {
	$post = get_page_by_path( $slug, OBJECT, 'lmf_service' );
	return $post ? get_the_title( $post ) : '';
}
