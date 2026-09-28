<?php
/**
 * ACF — the homepage, section by section, in the order a visitor meets them.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

lmf_acf_group(
	'home',
	__( 'Homepage', 'lemonmint' ),
	array(

		/* ---- 1. why brands hire us ---- */
		lmf_acf_tab( 'promise', __( 'Why brands hire us', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'promise_kicker', __( 'Kicker', 'lemonmint' ), array( 'default_value' => 'Why brands hire us' ) ),
		lmf_acf_field(
			'textarea',
			'promise_headline',
			__( 'Headline', 'lemonmint' ),
			array(
				'rows'          => 2,
				'default_value' => 'Most brand content gets scrolled past. We make the films people [watch to the end].',
				'instructions'  => __( 'The words in [square brackets] get the lemon marker underneath them as the line lights up.', 'lemonmint' ),
			)
		),
		lmf_acf_field( 'text', 'promise_figures_kicker', __( 'Kicker above the numbers', 'lemonmint' ), array( 'default_value' => 'Lemon Mint, in numbers' ) ),
		lmf_acf_field(
			'repeater',
			'promise_figures',
			__( 'The three numbers', 'lemonmint' ),
			array(
				'layout'       => 'table',
				'min'          => 0,
				'max'          => 3,
				'button_label' => __( 'Add number', 'lemonmint' ),
				'instructions' => __( 'Digits only in the first box — they roll into place like a frame counter. Put %, + or anything else in the suffix.', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'fig_value', __( 'Number', 'lemonmint' ), array( 'name' => 'value' ) ),
					lmf_acf_field( 'text', 'fig_suffix', __( 'Suffix', 'lemonmint' ), array( 'name' => 'suffix' ) ),
					lmf_acf_field( 'text', 'fig_label', __( 'What it counts', 'lemonmint' ), array( 'name' => 'label' ) ),
				),
			)
		),
		lmf_acf_field( 'textarea', 'promise_text', __( 'Paragraph under the numbers', 'lemonmint' ), array( 'rows' => 4 ) ),
		lmf_acf_field( 'text', 'sectors_label', __( 'Sectors — kicker', 'lemonmint' ), array( 'default_value' => 'Made for teams in' ) ),
		lmf_acf_field(
			'repeater',
			'sectors',
			__( 'Sectors', 'lemonmint' ),
			array(
				'layout'       => 'table',
				'button_label' => __( 'Add sector', 'lemonmint' ),
				'instructions' => __( 'They run past in two rows, one each way. Six to ten reads best.', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'sector_name', __( 'Sector', 'lemonmint' ), array( 'name' => 'name' ) ),
				),
			)
		),

		/* ---- 2. the work wheel ---- */
		lmf_acf_tab( 'cases', __( 'Selected work (the wheel)', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'cases_kicker', __( 'Kicker', 'lemonmint' ), array( 'default_value' => 'Selected work — and what it did' ) ),
		lmf_acf_field( 'text', 'cases_headline', __( 'Headline', 'lemonmint' ), array( 'default_value' => 'Proof, not promises.' ) ),
		lmf_acf_field( 'text', 'cases_more_label', __( 'Link under the wheel', 'lemonmint' ), array( 'default_value' => 'See all work' ) ),
		lmf_acf_field( 'url', 'cases_more_url', __( 'Its address', 'lemonmint' ), array( 'instructions' => __( 'Leave empty for the Work archive.', 'lemonmint' ) ) ),
		lmf_acf_field(
			'message',
			'cases_note',
			__( 'How the footage works', 'lemonmint' ),
			array(
				'message' => __( 'The wedges play from ONE video file laid out as a grid of tiles — four across and two down by default, one tile per wedge, in the same order as the list below. That keeps the page to a single video stream instead of eight. The "dive" clip is the film that fills the screen when the wheel opens up.', 'lemonmint' ),
				'name'    => '',
			)
		),
		lmf_acf_field( 'text', 'atlas_mp4', __( 'Wedge video — mp4', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'atlas_webm', __( 'Wedge video — webm', 'lemonmint' ), array( 'instructions' => __( 'Optional, but smaller and sharper in Chrome and Firefox.', 'lemonmint' ) ) ),
		lmf_acf_field( 'image', 'atlas_poster', __( 'Wedge video — poster', 'lemonmint' ), array( 'return_format' => 'url', 'preview_size' => 'medium' ) ),
		lmf_acf_field( 'number', 'atlas_cols', __( 'Tiles across', 'lemonmint' ), array( 'default_value' => 4, 'min' => 1, 'max' => 8, 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'number', 'atlas_rows', __( 'Tiles down', 'lemonmint' ), array( 'default_value' => 2, 'min' => 1, 'max' => 8, 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'text', 'dive_mp4', __( 'Dive clip — mp4', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'dive_webm', __( 'Dive clip — webm', 'lemonmint' ) ),
		lmf_acf_field( 'image', 'dive_poster', __( 'Dive clip — poster', 'lemonmint' ), array( 'return_format' => 'url', 'preview_size' => 'medium' ) ),
		lmf_acf_field(
			'repeater',
			'cases_items',
			__( 'The wedges', 'lemonmint' ),
			array(
				'layout'       => 'block',
				'max'          => 8,
				'button_label' => __( 'Add a wedge', 'lemonmint' ),
				'instructions' => __( 'Eight wedges, in the same order as the tiles in the video. Pick a project and the title and link fill themselves in.', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field(
						'post_object',
						'case_project',
						__( 'Project', 'lemonmint' ),
						array(
							'name'          => 'project',
							'post_type'     => array( 'lmf_project' ),
							'return_format' => 'id',
							'allow_null'    => 1,
							'ui'            => 1,
							'wrapper'       => array( 'width' => 40 ),
						)
					),
					lmf_acf_field( 'text', 'case_title', __( 'Title', 'lemonmint' ), array( 'name' => 'title', 'instructions' => __( 'Only if it should differ from the project’s own title.', 'lemonmint' ), 'wrapper' => array( 'width' => 30 ) ) ),
					lmf_acf_field( 'text', 'case_code', __( 'Caption code', 'lemonmint' ), array( 'name' => 'code', 'placeholder' => 'LMF / 021 · 06:20', 'wrapper' => array( 'width' => 30 ) ) ),
					lmf_acf_field( 'text', 'case_client', __( 'Client', 'lemonmint' ), array( 'name' => 'client', 'wrapper' => array( 'width' => 50 ) ) ),
					lmf_acf_field( 'text', 'case_type', __( 'Kind of film', 'lemonmint' ), array( 'name' => 'type', 'wrapper' => array( 'width' => 50 ) ) ),
					lmf_acf_field( 'text', 'case_result', __( 'The result, in a word or two', 'lemonmint' ), array( 'name' => 'result', 'placeholder' => '6 weeks', 'wrapper' => array( 'width' => 30 ) ) ),
					lmf_acf_field( 'text', 'case_outcome', __( 'What happened', 'lemonmint' ), array( 'name' => 'outcome', 'wrapper' => array( 'width' => 70 ) ) ),
					lmf_acf_field( 'url', 'case_url', __( 'Link', 'lemonmint' ), array( 'name' => 'url', 'instructions' => __( 'Only if it should not go to the project above.', 'lemonmint' ), 'wrapper' => array( 'width' => 50 ) ) ),
					lmf_acf_field( 'text', 'case_video', __( 'Its own clip (optional)', 'lemonmint' ), array( 'name' => 'video', 'instructions' => __( 'A separate file for this wedge instead of its tile in the grid.', 'lemonmint' ), 'wrapper' => array( 'width' => 50 ) ) ),
				),
			)
		),

		/* ---- 3. process ---- */
		lmf_acf_tab( 'process', __( 'How it works', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'process_kicker', __( 'Kicker', 'lemonmint' ), array( 'default_value' => 'How it works' ) ),
		lmf_acf_field( 'text', 'process_headline', __( 'Headline', 'lemonmint' ), array( 'default_value' => 'From first call to final master in six weeks.' ) ),
		lmf_acf_field(
			'repeater',
			'process_steps',
			__( 'The steps', 'lemonmint' ),
			array(
				'layout'       => 'table',
				'button_label' => __( 'Add step', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'step_when', __( 'When', 'lemonmint' ), array( 'name' => 'when', 'placeholder' => 'Week 1' ) ),
					lmf_acf_field( 'text', 'step_title', __( 'Step', 'lemonmint' ), array( 'name' => 'title' ) ),
					lmf_acf_field( 'textarea', 'step_text', __( 'What happens', 'lemonmint' ), array( 'name' => 'text', 'rows' => 2 ) ),
				),
			)
		),
		lmf_acf_field( 'text', 'process_promise', __( 'The guarantee line', 'lemonmint' ), array( 'instructions' => __( 'Separate the promises with " · ".', 'lemonmint' ) ) ),
		lmf_acf_field( 'text', 'process_cta_label', __( 'Button', 'lemonmint' ), array( 'default_value' => 'Book a free call', 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'url', 'process_cta_url', __( 'Its address', 'lemonmint' ), array( 'wrapper' => array( 'width' => 50 ) ) ),

		/* ---- 4. voices ---- */
		lmf_acf_tab( 'voices', __( 'In their words', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'voices_kicker', __( 'Kicker', 'lemonmint' ), array( 'default_value' => 'Clients' ) ),
		lmf_acf_field( 'text', 'voices_headline', __( 'Headline', 'lemonmint' ), array( 'default_value' => 'In their words.' ) ),
		lmf_acf_field(
			'repeater',
			'voices_items',
			__( 'The quotes', 'lemonmint' ),
			array(
				'layout'       => 'block',
				'button_label' => __( 'Add a quote', 'lemonmint' ),
				'instructions' => __( 'Only quotes you have written approval for. Approved Testimonial posts, if you use them, take priority over this list.', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'textarea', 'voice_quote', __( 'The quote', 'lemonmint' ), array( 'name' => 'quote', 'rows' => 3, 'instructions' => __( 'It appears as subtitles, so 25–40 words is the sweet spot.', 'lemonmint' ) ) ),
					lmf_acf_field( 'text', 'voice_who', __( 'Who said it', 'lemonmint' ), array( 'name' => 'who', 'placeholder' => 'Marketing Director', 'wrapper' => array( 'width' => 33 ) ) ),
					lmf_acf_field( 'text', 'voice_org', __( 'Where they work', 'lemonmint' ), array( 'name' => 'org', 'wrapper' => array( 'width' => 34 ) ) ),
					lmf_acf_field( 'text', 'voice_project', __( 'The film', 'lemonmint' ), array( 'name' => 'project', 'wrapper' => array( 'width' => 33 ) ) ),
					lmf_acf_field( 'image', 'voice_image', __( 'A frame from that film', 'lemonmint' ), array( 'name' => 'image', 'return_format' => 'url', 'preview_size' => 'medium', 'wrapper' => array( 'width' => 50 ) ) ),
					lmf_acf_field( 'url', 'voice_url', __( 'Link to the project', 'lemonmint' ), array( 'name' => 'url', 'wrapper' => array( 'width' => 50 ) ) ),
				),
			)
		),

		/* ---- 5. the closing countdown ---- */
		lmf_acf_tab( 'cta', __( 'Closing call to action', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'cta_kicker', __( 'Kicker', 'lemonmint' ), array( 'default_value' => 'Contact' ) ),
		lmf_acf_field( 'text', 'cta_headline', __( 'Headline', 'lemonmint' ), array( 'default_value' => 'Let’s shoot something' ) ),
		lmf_acf_field( 'text', 'cta_caption', __( 'Caption under the countdown', 'lemonmint' ) ),
		lmf_acf_field(
			'repeater',
			'cta_routes',
			__( 'The three ways in', 'lemonmint' ),
			array(
				'layout'       => 'table',
				'max'          => 3,
				'button_label' => __( 'Add a route', 'lemonmint' ),
				'instructions' => __( 'They are numbered like a film leader: the last one counts down to 1 and is the main action.', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'route_label', __( 'What they do', 'lemonmint' ), array( 'name' => 'label' ) ),
					lmf_acf_field( 'text', 'route_line', __( 'When it fits them', 'lemonmint' ), array( 'name' => 'line' ) ),
					lmf_acf_field(
						'select',
						'route_intent',
						__( 'Opens the brief as', 'lemonmint' ),
						array(
							'name'    => 'intent',
							'choices' => array(
								'production' => __( 'Start a production', 'lemonmint' ),
								'call'       => __( 'Book a call', 'lemonmint' ),
								'quote'      => __( 'Get a quote', 'lemonmint' ),
							),
						)
					),
				),
			)
		),
	),
	array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'lmf-home' ) ) )
);
