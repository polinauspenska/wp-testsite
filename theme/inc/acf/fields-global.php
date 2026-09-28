<?php
/**
 * ACF — site-wide screens: studio details, header/menu/footer, brief popup.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   STUDIO DETAILS — every phone number, address and line the site repeats
   ========================================================================== */
lmf_acf_group(
	'studio_details',
	__( 'Studio details', 'lemonmint' ),
	array(
		lmf_acf_tab( 'contactdetails', __( 'Contact', 'lemonmint' ) ),
		lmf_acf_field( 'email', 'lmf_email', __( 'Enquiries email', 'lemonmint' ), array( 'default_value' => 'info@lemonmintfilms.com', 'instructions' => __( 'Where every brief and enquiry is sent.', 'lemonmint' ) ) ),
		lmf_acf_field( 'text', 'lmf_phone', __( 'Phone', 'lemonmint' ), array( 'default_value' => '+971 4 332 3054' ) ),
		lmf_acf_field( 'text', 'lmf_whatsapp', __( 'WhatsApp number', 'lemonmint' ), array( 'instructions' => __( 'Digits only, with the country code — a mobile, e.g. 9715xxxxxxx. A landline cannot receive WhatsApp. Leave empty and the WhatsApp links disappear.', 'lemonmint' ) ) ),
		lmf_acf_field( 'text', 'lmf_address', __( 'Studio address', 'lemonmint' ), array( 'default_value' => 'Warehouse 28, Al Quoz Industrial Third' ) ),
		lmf_acf_field( 'text', 'lmf_city', __( 'City and country', 'lemonmint' ), array( 'default_value' => 'Dubai, United Arab Emirates' ) ),
		lmf_acf_field( 'text', 'lmf_hours', __( 'Studio hours', 'lemonmint' ), array( 'default_value' => 'Mon–Fri · 09:00–18:00', 'instructions' => __( 'Shown on the contact page. The live Dubai clock beside it assumes Mon–Fri, 09:00–18:00.', 'lemonmint' ) ) ),
		lmf_acf_field( 'text', 'lmf_response', __( 'Reply-time promise', 'lemonmint' ), array( 'default_value' => 'Under 4 working hours', 'instructions' => __( 'Used on the contact page and in the brief popup.', 'lemonmint' ) ) ),

		lmf_acf_tab( 'mapsettings', __( 'Map', 'lemonmint' ) ),
		lmf_acf_field( 'true_false', 'lmf_map_embed', __( 'Show the live Google map', 'lemonmint' ), array( 'default_value' => 1, 'ui' => 1, 'instructions' => __( 'Off: the contact page keeps the drawn map in the brand colours. "Get directions" opens the real map either way.', 'lemonmint' ) ) ),
		lmf_acf_field( 'text', 'lmf_map_query', __( 'Map search text', 'lemonmint' ), array( 'instructions' => __( 'Optional. What the map should search for, if the plain address does not find the building. Example: "Lemon Mint Films, Al Quoz Industrial Third".', 'lemonmint' ) ) ),

		lmf_acf_tab( 'videosettings', __( 'Video', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'lmf_hero_video', __( 'Homepage hero video (mp4)', 'lemonmint' ), array( 'instructions' => __( 'A muted, looping clip. Paste a CDN URL (Mux, Cloudflare Stream, Bunny) or upload and paste the file URL.', 'lemonmint' ) ) ),
		lmf_acf_field( 'image', 'lmf_hero_poster', __( 'Hero poster frame', 'lemonmint' ), array( 'return_format' => 'url', 'preview_size' => 'medium', 'instructions' => __( 'The still shown while the video loads.', 'lemonmint' ) ) ),
		lmf_acf_field( 'text', 'lmf_hero_line', __( 'Homepage headline', 'lemonmint' ), array( 'default_value' => 'Built for the screen, not the scroll' ) ),
		lmf_acf_field( 'text', 'lmf_reel_url', __( 'Showreel URL', 'lemonmint' ), array( 'instructions' => __( 'Opens in the full-screen player. Self-hosted or CDN, not a YouTube page.', 'lemonmint' ) ) ),
		lmf_acf_field( 'text', 'lmf_studio_line', __( 'Studio page headline', 'lemonmint' ), array( 'default_value' => 'We make films for people who have something specific to say, and forty seconds to say it.' ) ),
	),
	array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'lmf-studio' ) ) )
);

/* ==========================================================================
   HEADER, MENU & FOOTER
   ========================================================================== */
lmf_acf_group(
	'elements',
	__( 'Header, menu & footer', 'lemonmint' ),
	array(
		lmf_acf_tab( 'headerbits', __( 'Header', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'lmf_nav_cta', __( 'Header button', 'lemonmint' ), array( 'default_value' => 'Start a Production', 'instructions' => __( 'Opens the brief popup.', 'lemonmint' ) ) ),

		lmf_acf_tab( 'reelband', __( 'Showreel band (homepage)', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'reel_kicker', __( 'Kicker', 'lemonmint' ), array( 'default_value' => 'Showreel 2026' ) ),
		lmf_acf_field( 'text', 'reel_headline', __( 'Headline', 'lemonmint' ), array( 'default_value' => 'Ninety seconds of what we do' ) ),
		lmf_acf_field( 'textarea', 'reel_text', __( 'Sentence under it', 'lemonmint' ), array( 'rows' => 2 ) ),
		lmf_acf_field( 'text', 'reel_button', __( 'Button', 'lemonmint' ), array( 'default_value' => 'Watch the reel' ) ),

		lmf_acf_tab( 'footerbits', __( 'Footer', 'lemonmint' ) ),
		lmf_acf_field( 'textarea', 'lmf_footer_note', __( 'Footer line', 'lemonmint' ), array( 'rows' => 2, 'instructions' => __( 'Optional. Sits under the address block.', 'lemonmint' ) ) ),
		lmf_acf_field(
			'repeater',
			'lmf_socials',
			__( 'Social links', 'lemonmint' ),
			array(
				'layout'       => 'table',
				'button_label' => __( 'Add link', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'label', __( 'Label', 'lemonmint' ) ),
					lmf_acf_field( 'url', 'social_url', __( 'URL', 'lemonmint' ) ),
				),
			)
		),
	),
	array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'lmf-elements' ) ) )
);

/* ==========================================================================
   THE BRIEF POPUP
   ========================================================================== */
lmf_acf_group(
	'brief',
	__( 'Brief popup', 'lemonmint' ),
	array(
		lmf_acf_tab( 'briefwords', __( 'The questions', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'brief_q1', __( 'Scene 1 — question', 'lemonmint' ), array( 'default_value' => 'What are we making?' ) ),
		lmf_acf_field( 'text', 'brief_hint1', __( 'Scene 1 — hint', 'lemonmint' ), array( 'default_value' => 'Pick the closest — we’ll shape it together.' ) ),
		lmf_acf_field( 'text', 'brief_q2', __( 'Scene 2 — question', 'lemonmint' ), array( 'default_value' => 'When do we roll?' ) ),
		lmf_acf_field( 'text', 'brief_q2b', __( 'Scene 2 — budget question', 'lemonmint' ), array( 'default_value' => 'And the budget?' ) ),
		lmf_acf_field( 'text', 'brief_q3', __( 'Scene 3 — question', 'lemonmint' ), array( 'default_value' => 'Tell us the story.' ) ),
		lmf_acf_field( 'text', 'brief_hint3', __( 'Scene 3 — hint', 'lemonmint' ), array( 'default_value' => 'What it’s for, who it’s for, anything you already have. A few lines is plenty.' ) ),
		lmf_acf_field( 'text', 'brief_q4', __( 'Scene 4 — question', 'lemonmint' ), array( 'default_value' => 'Who’s calling action?' ) ),
		lmf_acf_field( 'text', 'brief_next', __( 'Next button', 'lemonmint' ), array( 'default_value' => 'Next scene' ) ),
		lmf_acf_field( 'text', 'brief_send', __( 'Send button', 'lemonmint' ), array( 'default_value' => 'Roll camera' ) ),
		lmf_acf_field( 'text', 'brief_skip', __( '"Optional" note', 'lemonmint' ), array( 'default_value' => 'Optional — skip if unsure' ) ),
		lmf_acf_field( 'text', 'brief_side', __( 'Line under the clapperboard', 'lemonmint' ), array( 'default_value' => 'A producer reads every brief. %s.', 'instructions' => __( '%s is replaced with the reply-time promise from Studio details.', 'lemonmint' ) ) ),

		lmf_acf_tab( 'briefdone', __( 'After sending', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'brief_done_kicker', __( 'Kicker', 'lemonmint' ), array( 'default_value' => 'Brief received' ) ),
		lmf_acf_field( 'text', 'brief_done_title', __( 'Headline', 'lemonmint' ), array( 'default_value' => 'That’s a wrap', 'instructions' => __( 'The sender’s first name is added after it.', 'lemonmint' ) ) ),
		lmf_acf_field( 'textarea', 'brief_done_text', __( 'Message', 'lemonmint' ), array( 'rows' => 3, 'default_value' => 'Your brief is with a producer. %s — usually sooner. Keep an eye on your inbox.', 'instructions' => __( '%s is replaced with the reply-time promise.', 'lemonmint' ) ) ),

		lmf_acf_tab( 'briefchoices', __( 'The choices', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'brief_other_label', __( 'Last service option', 'lemonmint' ), array( 'default_value' => 'Something else', 'instructions' => __( 'The services themselves come from the Services posts.', 'lemonmint' ) ) ),
		lmf_acf_field(
			'repeater',
			'brief_when',
			__( 'Timing options', 'lemonmint' ),
			array(
				'layout'       => 'table',
				'button_label' => __( 'Add option', 'lemonmint' ),
				'instructions' => __( 'Leave empty to keep the theme’s list (ASAP → no fixed date).', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'when_label', __( 'Shown to the client', 'lemonmint' ), array( 'name' => 'label' ) ),
					lmf_acf_field( 'text', 'when_key', __( 'Code (optional)', 'lemonmint' ), array( 'name' => 'key' ) ),
				),
			)
		),
		lmf_acf_field(
			'repeater',
			'brief_budget',
			__( 'Budget options', 'lemonmint' ),
			array(
				'layout'       => 'table',
				'button_label' => __( 'Add option', 'lemonmint' ),
				'instructions' => __( 'Leave empty to keep the theme’s bands (under AED 50k → over AED 400k).', 'lemonmint' ),
				'sub_fields'   => array(
					lmf_acf_field( 'text', 'budget_label', __( 'Shown to the client', 'lemonmint' ), array( 'name' => 'label' ) ),
					lmf_acf_field( 'text', 'budget_key', __( 'Code (optional)', 'lemonmint' ), array( 'name' => 'key' ) ),
				),
			)
		),
	),
	array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'lmf-brief' ) ) )
);
