<?php
/**
 * Custom fields, as native metaboxes.
 *
 * Deliberately no ACF dependency: the field set is small, stable and part of
 * the design system, so it belongs in the theme rather than in a plugin whose
 * licence and export the client would have to maintain.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions, keyed by post type.
 *
 * @return array
 */
function lmf_fields() {
	return array(
		'lmf_project' => array(
			'_lmf_code'     => array( 'Project code', 'text', 'Three digits. Appears as LMF / 024 in every caption row.' ),
			'_lmf_duration' => array( 'Duration', 'text', 'mm:ss — feeds the caption row and VideoObject schema.' ),
			'_lmf_client'   => array( 'Client', 'text', 'Named only where written approval exists; otherwise the sector, e.g. "Property developer, Dubai".' ),
			'_lmf_subtitle' => array( 'Subtitle', 'text', 'A Brand Film / Spot / Documentary — sits under the title.' ),
			'_lmf_brief'    => array( 'Brief', 'textarea', 'One or two sentences. Required before publishing.' ),
			'_lmf_video'    => array( 'Film URL', 'text', 'Self-hosted CDN URL (Mux, Cloudflare Stream, Bunny). Not a Vimeo or YouTube embed — those pass no SEO value back to the domain.' ),
			'_lmf_challenge' => array( 'The challenge', 'textarea', 'What the client needed and why it was hard.' ),
			'_lmf_direction' => array( 'Creative direction', 'textarea', 'The concept and the thinking behind it.' ),
			'_lmf_process'  => array( 'Production', 'textarea', 'Locations, crew, schedule, techniques.' ),
			'_lmf_why'      => array( 'Why it works', 'textarea', 'The result. A metric where one is approved; the craft rationale where it is not.' ),
			'_lmf_credits'  => array( 'Credits', 'textarea', 'One per line, as "Role: Name". Blank names render as "to confirm".' ),
			'_lmf_still'    => array( 'Placeholder still', 'select_kind', 'Only used until a featured image is set.' ),
		),
		'lmf_service' => array(
			'_lmf_keyword'  => array( 'Target keyword', 'text', 'The core keyword of this cluster. One page per cluster — never split.' ),
			'_lmf_cta'      => array( 'CTA label', 'text', 'Matched to this service’s buyer: Start a Production / Book a Discovery Call / Request a Quote / Plan Your Podcast.' ),
			'_lmf_diffs'    => array( 'Differentiators', 'textarea', 'One per line, as "Heading: body copy". Three or four.' ),
			'_lmf_faq'      => array( 'FAQ', 'textarea', 'One per line, as "Question | Answer". Carries FAQPage schema.' ),
			'_lmf_kit'      => array( 'Equipment', 'textarea', 'One per line, as "Category: items". Published because production managers ask first.' ),
			'_lmf_still'    => array( 'Placeholder still', 'select_kind', 'Only used until a featured image is set.' ),
		),
		'lmf_industry' => array(
			'_lmf_needs'    => array( 'What this sector needs', 'textarea', 'Written in that sector’s language, not ours.' ),
			'_lmf_considerations' => array( 'Production considerations', 'textarea', 'Permits, clearances, site access, approval chains.' ),
			'_lmf_still'    => array( 'Placeholder still', 'select_kind', '' ),
		),
		'lmf_person' => array(
			'_lmf_role'     => array( 'Role', 'text', '' ),
			'_lmf_public'   => array( 'Publish a page for this person', 'checkbox', 'Leave off until they have three or more credited projects — a thin director page is as damaging as a thin industry page.' ),
		),
		'lmf_testimonial' => array(
			'_lmf_who'      => array( 'Name or role', 'text', '' ),
			'_lmf_org'      => array( 'Organisation', 'text', '' ),
			'_lmf_project_id' => array( 'Related project ID', 'text', 'Pairs the quote with its project thumbnail rather than leaving it as isolated text.' ),
			'_lmf_approved' => array( 'Written approval on file', 'checkbox', 'Quotes without approval are never rendered.' ),
		),
	);
}

/**
 * Register metaboxes.
 */
function lmf_add_meta_boxes() {
	// ACF owns these screens when it is installed; the fields write to the
	// same meta keys, so the two never disagree.
	if ( function_exists( 'lmf_acf' ) && lmf_acf() ) {
		return;
	}
	foreach ( lmf_fields() as $type => $fields ) {
		add_meta_box(
			'lmf_meta_' . $type,
			__( 'Lemon Mint — details', 'lemonmint' ),
			'lmf_render_meta_box',
			$type,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'lmf_add_meta_boxes' );

/**
 * Render a metabox.
 *
 * @param WP_Post $post Current post.
 */
function lmf_render_meta_box( $post ) {
	$all = lmf_fields();
	if ( empty( $all[ $post->post_type ] ) ) {
		return;
	}
	wp_nonce_field( 'lmf_save_meta', 'lmf_meta_nonce' );

	echo '<style>.lmf-f{margin:0 0 18px}.lmf-f label{display:block;font-weight:600;margin-bottom:4px}
	.lmf-f .d{display:block;color:#666;font-size:12px;margin-bottom:6px;max-width:70ch}
	.lmf-f input[type=text],.lmf-f textarea,.lmf-f select{width:100%;max-width:680px}
	.lmf-f textarea{min-height:76px}</style>';

	foreach ( $all[ $post->post_type ] as $key => $conf ) {
		list( $label, $type, $desc ) = $conf;
		$value = get_post_meta( $post->ID, $key, true );

		echo '<div class="lmf-f">';
		printf( '<label for="%s">%s</label>', esc_attr( $key ), esc_html( $label ) );
		if ( $desc ) {
			printf( '<span class="d">%s</span>', esc_html( $desc ) );
		}

		switch ( $type ) {
			case 'textarea':
				printf(
					'<textarea id="%s" name="%s">%s</textarea>',
					esc_attr( $key ),
					esc_attr( $key ),
					esc_textarea( $value )
				);
				break;

			case 'checkbox':
				printf(
					'<input type="checkbox" id="%s" name="%s" value="1" %s>',
					esc_attr( $key ),
					esc_attr( $key ),
					checked( $value, '1', false )
				);
				break;

			case 'select_kind':
				$kinds = array(
					'wide'     => __( 'Wide — skyline / landscape', 'lemonmint' ),
					'portrait' => __( 'Portrait — a subject', 'lemonmint' ),
					'interior' => __( 'Interior — a lit room', 'lemonmint' ),
				);
				printf( '<select id="%s" name="%s">', esc_attr( $key ), esc_attr( $key ) );
				foreach ( $kinds as $k => $l ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $value, $k, false ), esc_html( $l ) );
				}
				echo '</select>';
				break;

			default:
				printf(
					'<input type="text" id="%s" name="%s" value="%s">',
					esc_attr( $key ),
					esc_attr( $key ),
					esc_attr( $value )
				);
		}
		echo '</div>';
	}
}

/**
 * Save metabox values.
 *
 * @param int $post_id Post ID.
 */
function lmf_save_meta( $post_id ) {
	if ( ! isset( $_POST['lmf_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['lmf_meta_nonce'] ) ), 'lmf_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$all  = lmf_fields();
	$type = get_post_type( $post_id );
	if ( empty( $all[ $type ] ) ) {
		return;
	}

	foreach ( $all[ $type ] as $key => $conf ) {
		if ( 'checkbox' === $conf[1] ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '' );
			continue;
		}
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		$val = ( 'textarea' === $conf[1] ) ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		update_post_meta( $post_id, $key, $val );
	}
}
add_action( 'save_post', 'lmf_save_meta' );

/**
 * Parse the FAQ field into question/answer pairs.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function lmf_get_faq( $post_id ) {
	$raw = (string) get_post_meta( $post_id, '_lmf_faq', true );
	if ( ! $raw ) {
		return array();
	}
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		if ( false === strpos( $line, '|' ) ) {
			continue;
		}
		list( $q, $a ) = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( $q && $a ) {
			$out[] = array(
				'q' => $q,
				'a' => $a,
			);
		}
	}
	return $out;
}

/**
 * Parse a "Label: body" list field into pairs.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @return array
 */
function lmf_get_pairs( $post_id, $key ) {
	$raw = (string) get_post_meta( $post_id, $key, true );
	if ( ! $raw ) {
		return array();
	}
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( false !== strpos( $line, ':' ) ) {
			list( $k, $v ) = array_map( 'trim', explode( ':', $line, 2 ) );
		} else {
			$k = $line;
			$v = '';
		}
		$out[] = array(
			'k' => $k,
			'v' => $v,
		);
	}
	return $out;
}
