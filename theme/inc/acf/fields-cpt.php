<?php
/**
 * ACF — the post types: Projects, Services, Industries, People, Testimonials.
 *
 * These replace the theme's own meta boxes when ACF is active, and they write
 * to the SAME meta keys the templates already read (_lmf_code, _lmf_client …).
 * So nothing is migrated, nothing is duplicated, and switching ACF off leaves
 * the content exactly where it was.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every ACF field named lmf_x on these post types is mirrored to _lmf_x.
 *
 * @return array
 */
function lmf_acf_legacy_keys() {
	return array(
		'lmf_code', 'lmf_duration', 'lmf_client', 'lmf_subtitle', 'lmf_brief', 'lmf_video',
		'lmf_challenge', 'lmf_direction', 'lmf_process', 'lmf_why', 'lmf_credits', 'lmf_still',
		'lmf_keyword', 'lmf_cta', 'lmf_diffs', 'lmf_faq', 'lmf_kit',
		'lmf_needs', 'lmf_considerations',
		'lmf_role', 'lmf_public',
		'lmf_who', 'lmf_org', 'lmf_project_id', 'lmf_approved',
	);
}

/**
 * Read: a field the client has not touched yet still shows whatever the old
 * meta box saved.
 *
 * @param mixed $value   Value.
 * @param mixed $post_id Post.
 * @param array $field   Field.
 * @return mixed
 */
function lmf_acf_load_legacy( $value, $post_id, $field ) {
	if ( ( '' !== $value && null !== $value && array() !== $value ) || ! is_numeric( $post_id ) ) {
		return $value;
	}
	if ( ! in_array( $field['name'], lmf_acf_legacy_keys(), true ) ) {
		return $value;
	}
	$legacy = get_post_meta( (int) $post_id, '_' . $field['name'], true );
	return ( '' === $legacy ) ? $value : $legacy;
}
add_filter( 'acf/load_value', 'lmf_acf_load_legacy', 10, 3 );

/**
 * Write: keep the underscored key in step, because the templates, the
 * schema output and the publish checks all read that one.
 *
 * @param int|string $post_id Post.
 */
function lmf_acf_save_legacy( $post_id ) {
	if ( ! is_numeric( $post_id ) ) {
		return;
	}
	foreach ( lmf_acf_legacy_keys() as $name ) {
		$v = get_field( $name, $post_id );
		if ( null === $v ) {
			continue;
		}
		if ( is_array( $v ) ) {
			$v = isset( $v['url'] ) ? $v['url'] : ( isset( $v['ID'] ) ? $v['ID'] : wp_json_encode( $v ) );
		}
		if ( is_bool( $v ) ) {
			$v = $v ? '1' : '';
		}
		update_post_meta( (int) $post_id, '_' . $name, $v );
	}
}
add_action( 'acf/save_post', 'lmf_acf_save_legacy', 20 );

/* ==========================================================================
   PROJECT
   ========================================================================== */
lmf_acf_group(
	'cpt_project',
	__( 'Project', 'lemonmint' ),
	array(
		lmf_acf_tab( 'projectfacts', __( 'The facts', 'lemonmint' ) ),
		lmf_acf_field( 'text', 'lmf_code', __( 'Project code', 'lemonmint' ), array( 'instructions' => __( 'Three digits. Shown as LMF / 024 in the caption rows.', 'lemonmint' ), 'wrapper' => array( 'width' => 25 ) ) ),
		lmf_acf_field( 'text', 'lmf_duration', __( 'Duration', 'lemonmint' ), array( 'placeholder' => '01:38', 'wrapper' => array( 'width' => 25 ) ) ),
		lmf_acf_field( 'text', 'lmf_client', __( 'Client', 'lemonmint' ), array( 'instructions' => __( 'Name them only where you have written approval; otherwise the sector, e.g. "Property developer, Dubai".', 'lemonmint' ), 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'text', 'lmf_subtitle', __( 'Subtitle', 'lemonmint' ), array( 'placeholder' => 'A Brand Film' ) ),
		lmf_acf_field( 'textarea', 'lmf_brief', __( 'Brief', 'lemonmint' ), array( 'rows' => 3, 'required' => 1, 'instructions' => __( 'One or two sentences. Needed before the project can be published.', 'lemonmint' ) ) ),
		lmf_acf_field( 'text', 'lmf_video', __( 'Film URL', 'lemonmint' ), array( 'instructions' => __( 'A self-hosted or CDN file (Mux, Cloudflare Stream, Bunny) — not a YouTube or Vimeo page.', 'lemonmint' ) ) ),

		lmf_acf_tab( 'projectstory', __( 'The story', 'lemonmint' ) ),
		lmf_acf_field( 'textarea', 'lmf_challenge', __( 'The challenge', 'lemonmint' ), array( 'rows' => 3 ) ),
		lmf_acf_field( 'textarea', 'lmf_direction', __( 'Creative direction', 'lemonmint' ), array( 'rows' => 3 ) ),
		lmf_acf_field( 'textarea', 'lmf_process', __( 'Production', 'lemonmint' ), array( 'rows' => 3 ) ),
		lmf_acf_field( 'textarea', 'lmf_why', __( 'Why it works', 'lemonmint' ), array( 'rows' => 3, 'instructions' => __( 'The result: a number where one is approved, the craft reason where it is not.', 'lemonmint' ) ) ),
		lmf_acf_field( 'textarea', 'lmf_credits', __( 'Credits', 'lemonmint' ), array( 'rows' => 5, 'instructions' => __( 'One per line, as "Role: Name".', 'lemonmint' ) ) ),
	),
	array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'lmf_project' ) ) )
);

/* ==========================================================================
   SERVICE
   ========================================================================== */
lmf_acf_group(
	'cpt_service',
	__( 'Service', 'lemonmint' ),
	array(
		lmf_acf_field( 'text', 'lmf_keyword', __( 'Target keyword', 'lemonmint' ), array( 'instructions' => __( 'One page per keyword cluster — never split it across two services.', 'lemonmint' ), 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'text', 'lmf_cta', __( 'CTA label', 'lemonmint' ), array( 'instructions' => __( 'Matched to this service’s buyer: Start a Production / Book a Discovery Call / Request a Quote.', 'lemonmint' ), 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'textarea', 'lmf_diffs', __( 'What makes it different', 'lemonmint' ), array( 'rows' => 4, 'instructions' => __( 'One per line, as "Heading: the sentence". Three or four.', 'lemonmint' ) ) ),
		lmf_acf_field( 'textarea', 'lmf_faq', __( 'FAQ', 'lemonmint' ), array( 'rows' => 4, 'instructions' => __( 'One per line, as "Question | Answer". Carries FAQ markup for Google.', 'lemonmint' ) ) ),
		lmf_acf_field( 'textarea', 'lmf_kit', __( 'Equipment', 'lemonmint' ), array( 'rows' => 4, 'instructions' => __( 'One per line, as "Category: items".', 'lemonmint' ) ) ),
	),
	array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'lmf_service' ) ) )
);

/* ==========================================================================
   INDUSTRY
   ========================================================================== */
lmf_acf_group(
	'cpt_industry',
	__( 'Industry', 'lemonmint' ),
	array(
		lmf_acf_field( 'textarea', 'lmf_needs', __( 'What this sector needs', 'lemonmint' ), array( 'rows' => 4, 'instructions' => __( 'In that sector’s language, not ours.', 'lemonmint' ) ) ),
		lmf_acf_field( 'textarea', 'lmf_considerations', __( 'Production considerations', 'lemonmint' ), array( 'rows' => 4, 'instructions' => __( 'Permits, clearances, site access, approval chains.', 'lemonmint' ) ) ),
	),
	array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'lmf_industry' ) ) )
);

/* ==========================================================================
   PERSON
   ========================================================================== */
lmf_acf_group(
	'cpt_person',
	__( 'Person', 'lemonmint' ),
	array(
		lmf_acf_field( 'text', 'lmf_role', __( 'Role', 'lemonmint' ) ),
		lmf_acf_field( 'true_false', 'lmf_public', __( 'Give this person their own page', 'lemonmint' ), array( 'ui' => 1, 'instructions' => __( 'Leave off until they have three or more credited projects — a thin profile page does more harm than good.', 'lemonmint' ) ) ),
	),
	array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'lmf_person' ) ) )
);

/* ==========================================================================
   TESTIMONIAL
   ========================================================================== */
lmf_acf_group(
	'cpt_testimonial',
	__( 'Testimonial', 'lemonmint' ),
	array(
		lmf_acf_field( 'text', 'lmf_who', __( 'Name or role', 'lemonmint' ), array( 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field( 'text', 'lmf_org', __( 'Organisation', 'lemonmint' ), array( 'wrapper' => array( 'width' => 50 ) ) ),
		lmf_acf_field(
			'post_object',
			'lmf_project_id',
			__( 'The film it is about', 'lemonmint' ),
			array(
				'post_type'     => array( 'lmf_project' ),
				'return_format' => 'id',
				'allow_null'    => 1,
				'ui'            => 1,
				'instructions'  => __( 'Pairs the quote with a frame from that film on the homepage.', 'lemonmint' ),
			)
		),
		lmf_acf_field( 'true_false', 'lmf_approved', __( 'Written approval on file', 'lemonmint' ), array( 'ui' => 1, 'instructions' => __( 'Quotes without approval are never shown.', 'lemonmint' ) ) ),
	),
	array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'lmf_testimonial' ) ) )
);
