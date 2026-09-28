<?php
/**
 * Post types and taxonomies.
 *
 * The architecture needs four content types and three taxonomies. Slugs are
 * keyword-mapped: /work/{project}, /services/{service}, /industries/{industry}.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register post types.
 */
function lmf_register_post_types() {

	// PROJECT — the case study. The highest-leverage page on the site.
	register_post_type(
		'lmf_project',
		array(
			'labels'        => array(
				'name'               => __( 'Work', 'lemonmint' ),
				'singular_name'      => __( 'Project', 'lemonmint' ),
				'add_new_item'       => __( 'Add project', 'lemonmint' ),
				'edit_item'          => __( 'Edit project', 'lemonmint' ),
				'search_items'       => __( 'Search work', 'lemonmint' ),
				'not_found'          => __( 'No projects yet', 'lemonmint' ),
			),
			'public'        => true,
			'has_archive'   => 'work',
			'rewrite'       => array(
				'slug'       => 'work',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-video-alt2',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
			'show_in_rest'  => true,
		)
	);

	// SERVICE — one page per keyword cluster.
	register_post_type(
		'lmf_service',
		array(
			'labels'        => array(
				'name'          => __( 'Services', 'lemonmint' ),
				'singular_name' => __( 'Service', 'lemonmint' ),
				'add_new_item'  => __( 'Add service', 'lemonmint' ),
			),
			'public'        => true,
			'has_archive'   => 'services',
			'rewrite'       => array(
				'slug'       => 'services',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-format-gallery',
			'menu_position' => 21,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
			'show_in_rest'  => true,
		)
	);

	// INDUSTRY — how agencies and government buyers actually search.
	register_post_type(
		'lmf_industry',
		array(
			'labels'        => array(
				'name'          => __( 'Industries', 'lemonmint' ),
				'singular_name' => __( 'Industry', 'lemonmint' ),
				'add_new_item'  => __( 'Add industry', 'lemonmint' ),
			),
			'public'        => true,
			'has_archive'   => 'industries',
			'rewrite'       => array(
				'slug'       => 'industries',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-building',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	// PERSON — publish a page only where a director has real credited work.
	register_post_type(
		'lmf_person',
		array(
			'labels'        => array(
				'name'          => __( 'People', 'lemonmint' ),
				'singular_name' => __( 'Person', 'lemonmint' ),
				'add_new_item'  => __( 'Add person', 'lemonmint' ),
			),
			'public'        => true,
			'has_archive'   => false,
			// 'studio' alone would capture every child of the Studio page.
			'rewrite'       => array(
				'slug'       => 'studio/people',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-groups',
			'menu_position' => 23,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	// TESTIMONIAL — never public on its own; rendered into blocks.
	register_post_type(
		'lmf_testimonial',
		array(
			'labels'        => array(
				'name'          => __( 'Testimonials', 'lemonmint' ),
				'singular_name' => __( 'Testimonial', 'lemonmint' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-format-quote',
			'menu_position' => 24,
			'supports'      => array( 'title', 'editor', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'lmf_register_post_types' );

/**
 * Register taxonomies. These are the three filter axes on the Work hub and the
 * mechanism that pulls proof onto service and industry pages automatically.
 */
function lmf_register_taxonomies() {

	register_taxonomy(
		'lmf_service_cat',
		array( 'lmf_project' ),
		array(
			'labels'            => array(
				'name'          => __( 'Service', 'lemonmint' ),
				'singular_name' => __( 'Service', 'lemonmint' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'work/service' ),
		)
	);

	// NOTE: must NOT be 'lmf_industry' — that is the post type name, and a
	// taxonomy sharing it hijacks the same query var, breaking every
	// /industries/{slug}/ URL.
	register_taxonomy(
		'lmf_industry_cat',
		array( 'lmf_project' ),
		array(
			'labels'            => array(
				'name'          => __( 'Industry', 'lemonmint' ),
				'singular_name' => __( 'Industry', 'lemonmint' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'work/industry' ),
		)
	);

	register_taxonomy(
		'lmf_format',
		array( 'lmf_project' ),
		array(
			'labels'            => array(
				'name'          => __( 'Format', 'lemonmint' ),
				'singular_name' => __( 'Format', 'lemonmint' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'work/format' ),
		)
	);
}
add_action( 'init', 'lmf_register_taxonomies' );

/**
 * Flush rewrites once after activation so /work/ and /services/ resolve
 * without the user having to visit Settings → Permalinks.
 */
function lmf_flush_rewrites() {
	lmf_register_post_types();
	lmf_register_taxonomies();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'lmf_flush_rewrites' );

/**
 * Publication gate.
 *
 * The single most consistent failure across the competitive set is a project
 * published as a bare video with a title. This blocks that at the CMS level:
 * a project cannot go live without a brief, a service, an industry and a
 * poster image. Editors get told exactly what is missing.
 *
 * @param string $new    New status.
 * @param string $old    Old status.
 * @param WP_Post $post  The post.
 */
function lmf_require_fields_before_publish( $new, $old, $post ) {
	if ( 'lmf_project' !== $post->post_type || 'publish' !== $new || 'publish' === $old ) {
		return;
	}

	$missing = array();
	if ( ! get_post_meta( $post->ID, '_lmf_brief', true ) ) {
		$missing[] = __( 'a one-line brief', 'lemonmint' );
	}
	if ( ! has_term( '', 'lmf_service_cat', $post ) ) {
		$missing[] = __( 'a service', 'lemonmint' );
	}
	if ( ! has_term( '', 'lmf_industry_cat', $post ) ) {
		$missing[] = __( 'an industry', 'lemonmint' );
	}
	if ( ! has_post_thumbnail( $post->ID ) ) {
		$missing[] = __( 'a poster image', 'lemonmint' );
	}

	if ( $missing ) {
		remove_action( 'transition_post_status', 'lmf_require_fields_before_publish', 10 );
		wp_update_post(
			array(
				'ID'          => $post->ID,
				'post_status' => 'draft',
			)
		);
		add_action( 'transition_post_status', 'lmf_require_fields_before_publish', 10, 3 );

		set_transient( 'lmf_publish_block_' . $post->ID, $missing, 60 );
	}
}
add_action( 'transition_post_status', 'lmf_require_fields_before_publish', 10, 3 );

/**
 * A person only gets a public page once someone ticks "Publish a page for this
 * person". A thin director page is as damaging as a thin industry page, so the
 * field is enforced rather than merely described.
 */
function lmf_gate_person_pages() {
	if ( ! is_singular( 'lmf_person' ) ) {
		return;
	}
	if ( ! get_post_meta( get_queried_object_id(), '_lmf_public', true ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'lmf_gate_person_pages' );

/**
 * Show the editor why publication was held.
 */
function lmf_publish_block_notice() {
	global $post;
	if ( ! $post ) {
		return;
	}
	$missing = get_transient( 'lmf_publish_block_' . $post->ID );
	if ( ! $missing ) {
		return;
	}
	delete_transient( 'lmf_publish_block_' . $post->ID );
	printf(
		'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
		esc_html__( 'Held as a draft.', 'lemonmint' ),
		esc_html(
			sprintf(
				/* translators: %s: comma separated list of missing fields. */
				__( 'A project needs %s before it can publish — this is what stops the site filling up with bare videos.', 'lemonmint' ),
				implode( ', ', $missing )
			)
		)
	);
}
add_action( 'admin_notices', 'lmf_publish_block_notice' );
