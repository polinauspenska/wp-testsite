<?php
/**
 * ACF — the pages: Contact, About, Studio, Services.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   CONTACT — one screen: the lines, the map, the action
   ========================================================================== */
lmf_acf_group(
	'page_contact',
	__( 'Contact page', 'lemonmint' ),
	array(
		lmf_acf_field( 'text', 'cx_kicker', __( 'Kicker', 'lemonmint' ), array( 'default_value' => 'Contact us' ) ),
		lmf_acf_field( 'text', 'cx_line1', __( 'Headline — first line', 'lemonmint' ), array( 'default_value' => 'Let’s shoot', 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'text', 'cx_line2', __( 'Headline — second line', 'lemonmint' ), array( 'default_value' => 'something.', 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'textarea', 'cx_lede', __( 'Sentence under it', 'lemonmint' ), array( 'rows' => 2, 'default_value' => 'A producer — not a form robot — reads every brief and writes back.' ) ),
		lmf_acf_field( 'text', 'cx_label_write', __( 'Label: email', 'lemonmint' ), array( 'default_value' => 'Write', 'wrapper' => array( 'width' => 25 ) ) ),
		lmf_acf_field( 'text', 'cx_label_call', __( 'Label: phone', 'lemonmint' ), array( 'default_value' => 'Call', 'wrapper' => array( 'width' => 25 ) ) ),
		lmf_acf_field( 'text', 'cx_label_studio', __( 'Label: address', 'lemonmint' ), array( 'default_value' => 'Studio', 'wrapper' => array( 'width' => 25 ) ) ),
		lmf_acf_field( 'text', 'cx_label_hours', __( 'Label: hours', 'lemonmint' ), array( 'default_value' => 'Hours', 'wrapper' => array( 'width' => 25 ) ) ),
		lmf_acf_field( 'text', 'cx_whatsapp_label', __( 'WhatsApp link', 'lemonmint' ), array( 'default_value' => 'WhatsApp a producer', 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'text', 'cx_directions_label', __( 'Directions link', 'lemonmint' ), array( 'default_value' => 'Get directions', 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'text', 'cx_cta', __( 'Button', 'lemonmint' ), array( 'default_value' => 'Start a production', 'instructions' => __( 'Opens the brief popup.', 'lemonmint' ), 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'text', 'cx_card_label', __( 'Map card label', 'lemonmint' ), array( 'default_value' => 'The studio', 'wrapper' => array( 'width' => 50 ) ) ),
	),
	array( array( array( 'param' => 'lmf_page', 'operator' => '==', 'value' => 'contact' ) ) )
);

/* ==========================================================================
   ABOUT
   ========================================================================== */
lmf_acf_group(
	'page_about',
	__( 'About page', 'lemonmint' ),
	array(
		lmf_acf_field( 'textarea', 'about_manifesto', __( 'The story', 'lemonmint' ), array( 'rows' => 5, 'instructions' => __( 'Written the way the founder would say it out loud.', 'lemonmint' ) ) ),
		lmf_acf_field(
			'repeater',
			'about_timeline',
			__( 'Milestones', 'lemonmint' ),
			array(
				'layout'       => 'table',
				'button_label' => __( 'Add milestone', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'tl_year', __( 'Year', 'lemonmint' ), array( 'name' => 'year', 'wrapper' => array( 'width' => 15 ) ) ),
					lmf_acf_field( 'text', 'tl_title', __( 'What happened', 'lemonmint' ), array( 'name' => 'title', 'wrapper' => array( 'width' => 30 ) ) ),
					lmf_acf_field( 'text', 'tl_text', __( 'A line about it', 'lemonmint' ), array( 'name' => 'text', 'wrapper' => array( 'width' => 55 ) ) ),
				),
			)
		),
		lmf_acf_field(
			'message',
			'about_people_note',
			__( 'The team', 'lemonmint' ),
			array(
				'name'    => '',
				'message' => __( 'The people shown on this page come from the <strong>People</strong> posts — add them there, with a photo and a role, and they appear here in that order.', 'lemonmint' ),
			)
		),
	),
	array( array( array( 'param' => 'lmf_page', 'operator' => '==', 'value' => 'about' ) ) )
);

/* ==========================================================================
   STUDIO
   ========================================================================== */
lmf_acf_group(
	'page_studio',
	__( 'Studio page', 'lemonmint' ),
	array(
		lmf_acf_field(
			'repeater',
			'studio_spaces',
			__( 'The spaces', 'lemonmint' ),
			array(
				'layout'       => 'block',
				'button_label' => __( 'Add a space', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'sp_title', __( 'Name', 'lemonmint' ), array( 'name' => 'title', 'wrapper' => array( 'width' => 50 ) ) ),
					lmf_acf_field( 'text', 'sp_meta', __( 'Floor and size', 'lemonmint' ), array( 'name' => 'meta', 'placeholder' => 'Floor 1 · 120 m²', 'wrapper' => array( 'width' => 50 ) ) ),
					lmf_acf_field( 'textarea', 'sp_text', __( 'What it is for', 'lemonmint' ), array( 'name' => 'text', 'rows' => 3 ) ),
					lmf_acf_field( 'image', 'sp_image', __( 'Photograph', 'lemonmint' ), array( 'name' => 'image', 'return_format' => 'url', 'preview_size' => 'medium' ) ),
				),
			)
		),
		lmf_acf_field(
			'repeater',
			'studio_kit',
			__( 'The kit', 'lemonmint' ),
			array(
				'layout'       => 'table',
				'button_label' => __( 'Add a line', 'lemonmint' ),
				'instructions' => __( 'Production managers read this first, so keep it specific.', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'kit_title', __( 'Category', 'lemonmint' ), array( 'name' => 'title' ) ),
					lmf_acf_field( 'text', 'kit_a', __( 'Main', 'lemonmint' ), array( 'name' => 'a' ) ),
					lmf_acf_field( 'text', 'kit_b', __( 'Also', 'lemonmint' ), array( 'name' => 'b' ) ),
				),
			)
		),
	),
	array( array( array( 'param' => 'lmf_page', 'operator' => '==', 'value' => 'studio' ) ) )
);

/* ==========================================================================
   SERVICES PAGE — the process strip and the general FAQ
   ========================================================================== */
lmf_acf_group(
	'page_services',
	__( 'Services page', 'lemonmint' ),
	array(
		lmf_acf_field(
			'repeater',
			'svc_process',
			__( 'How a film gets made', 'lemonmint' ),
			array(
				'layout'       => 'table',
				'button_label' => __( 'Add step', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'svcp_tc', __( 'Timecode', 'lemonmint' ), array( 'name' => 'tc', 'placeholder' => '00:48', 'wrapper' => array( 'width' => 15 ) ) ),
					lmf_acf_field( 'text', 'svcp_title', __( 'Step', 'lemonmint' ), array( 'name' => 'title', 'wrapper' => array( 'width' => 25 ) ) ),
					lmf_acf_field( 'textarea', 'svcp_text', __( 'What happens', 'lemonmint' ), array( 'name' => 'text', 'rows' => 2, 'wrapper' => array( 'width' => 60 ) ) ),
				),
			)
		),
		lmf_acf_field(
			'repeater',
			'svc_faq',
			__( 'Questions people ask', 'lemonmint' ),
			array(
				'layout'       => 'block',
				'button_label' => __( 'Add question', 'lemonmint' ),
				'instructions' => __( 'These carry FAQ markup for Google, so answer plainly and give real numbers where you can.', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'faq_q', __( 'Question', 'lemonmint' ), array( 'name' => 'q' ) ),
					lmf_acf_field( 'textarea', 'faq_a', __( 'Answer', 'lemonmint' ), array( 'name' => 'a', 'rows' => 3 ) ),
				),
			)
		),
	),
	array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'lmf-services' ) ) )
);
