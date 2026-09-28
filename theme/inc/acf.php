<?php
/**
 * ACF — the editing layer.
 *
 * Everything the front end shows is written in the theme as a default and
 * can be overwritten from WordPress with Advanced Custom Fields Pro. The
 * rule is one-way and strict:
 *
 *   a field that is filled in wins · a field left empty falls back to the
 *   theme's own copy, so the site is never blank and never half-built.
 *
 * Field groups are registered in PHP (inc/acf/fields-*.php) rather than
 * created in the admin, so they travel with the theme, can be diffed, and
 * cannot be deleted by accident. Groups the client builds themselves in the
 * UI are saved as JSON into /acf-json and load from there.
 *
 * Without ACF installed the theme still runs: lmf_opt() falls back to the
 * Customizer values, and every content filter below simply does nothing.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is ACF (Pro) actually here?
 */
function lmf_acf() {
	return function_exists( 'get_field' ) && function_exists( 'acf_add_local_field_group' );
}

/**
 * Has ACF Pro (repeaters, options pages)?
 */
function lmf_acf_pro() {
	return lmf_acf() && function_exists( 'acf_add_options_page' );
}

/**
 * A site-wide setting: ACF option first, then the Customizer, then the
 * theme's default. Every template reads its studio details through this, so
 * a site with ACF is edited in one place and a site without it still works.
 *
 * @param string $key     Key without the lmf_ prefix, e.g. 'email'.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function lmf_opt( $key, $default = '' ) {
	$name = 0 === strpos( $key, 'lmf_' ) ? $key : 'lmf_' . $key;

	if ( lmf_acf() ) {
		$v = get_field( $name, 'option' );
		if ( is_array( $v ) ? ! empty( $v ) : ( null !== $v && '' !== $v ) ) {
			return $v;
		}
		// A checkbox/true_false that is deliberately off must not fall back.
		if ( false === $v && function_exists( 'acf_get_field' ) ) {
			$f = acf_get_field( $name );
			if ( $f && 'true_false' === $f['type'] ) {
				return false;
			}
		}
	}

	$mod = get_theme_mod( $name, null );
	if ( null !== $mod && '' !== $mod ) {
		return $mod;
	}
	return $default;
}

/**
 * One field from a post (or the options page), with a fallback.
 *
 * @param string   $name    Field name.
 * @param mixed    $default Fallback when empty or when ACF is missing.
 * @param int|null $post_id Post ID; defaults to the current post.
 * @return mixed
 */
function lmf_field( $name, $default = '', $post_id = null ) {
	if ( ! lmf_acf() ) {
		return $default;
	}
	$v = get_field( $name, null === $post_id ? false : $post_id );
	if ( is_array( $v ) ? ! empty( $v ) : ( null !== $v && '' !== $v ) ) {
		return $v;
	}
	return $default;
}

/**
 * Repeater rows, or the theme's own list when the repeater is empty.
 *
 * @param string $name    Field name.
 * @param array  $default Fallback rows.
 * @param mixed  $post_id 'option' or a post ID.
 * @return array
 */
function lmf_rows( $name, $default = array(), $post_id = 'option' ) {
	$v = lmf_field( $name, null, $post_id );
	return ( is_array( $v ) && $v ) ? $v : $default;
}

/**
 * A row value, whatever ACF handed back for it.
 *
 * @param array  $row     Repeater row.
 * @param string $key     Sub-field name.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function lmf_row( $row, $key, $default = '' ) {
	if ( ! is_array( $row ) || ! isset( $row[ $key ] ) ) {
		return $default;
	}
	$v = $row[ $key ];
	if ( is_array( $v ) ) {
		// image/file arrays → their URL
		if ( isset( $v['url'] ) ) {
			return $v['url'];
		}
		return $v ? $v : $default;
	}
	return ( '' === $v || null === $v ) ? $default : $v;
}

/**
 * An image/file field as a URL, whatever return format it was saved in.
 *
 * @param mixed  $v       Field value.
 * @param string $default Fallback URL.
 * @return string
 */
function lmf_url( $v, $default = '' ) {
	if ( is_array( $v ) && isset( $v['url'] ) ) {
		return $v['url'];
	}
	if ( is_numeric( $v ) ) {
		$u = wp_get_attachment_url( (int) $v );
		return $u ? $u : $default;
	}
	return ( is_string( $v ) && '' !== $v ) ? $v : $default;
}

/* ==========================================================================
   FIELD GROUPS
   ========================================================================== */

/**
 * Small builder so the group files read as content, not as configuration.
 *
 * @param string $type  Field type.
 * @param string $name  Field name (also its key, prefixed).
 * @param string $label Label.
 * @param array  $args  Anything else ACF takes.
 * @return array
 */
function lmf_acf_field( $type, $name, $label, $args = array() ) {
	return array_merge(
		array(
			'key'   => 'field_lmf_' . $name,
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		),
		$args
	);
}

/**
 * A tab, to keep long groups readable in the admin.
 *
 * @param string $name  Unique slug.
 * @param string $label Tab label.
 * @return array
 */
function lmf_acf_tab( $name, $label ) {
	return array(
		'key'       => 'field_lmf_tab_' . $name,
		'name'      => '',
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);
}

/**
 * Register a group with the theme's house settings.
 *
 * @param string $key      Group key suffix.
 * @param string $title    Group title.
 * @param array  $fields   Fields.
 * @param array  $location ACF location rules.
 * @param array  $args     Overrides.
 */
function lmf_acf_group( $key, $title, $fields, $location, $args = array() ) {
	acf_add_local_field_group(
		array_merge(
			array(
				'key'                   => 'group_lmf_' . $key,
				'title'                 => $title,
				'fields'                => $fields,
				'location'              => $location,
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'hide_on_screen'        => array(),
				'active'                => true,
				'show_in_rest'          => false,
			),
			$args
		)
	);
}

/* --------------------------------------------------------------------------
   A location rule of our own: "Lemon Mint page".

   WordPress picks page-contact.php for the page whose slug is "contact" all
   by itself — nobody has to choose a template in Page Attributes, and most
   editors never do. ACF's own "Page Template" rule only sees an explicitly
   chosen template, so the Contact, About and Studio fields would never show
   up. This rule matches the page either way: by its slug, or by the template
   if one was picked.
   -------------------------------------------------------------------------- */

/**
 * The pages this rule knows about: slug => label.
 *
 * @return array
 */
function lmf_acf_rule_pages() {
	return array(
		'contact' => __( 'Contact', 'lemonmint' ),
		'about'   => __( 'About', 'lemonmint' ),
		'studio'  => __( 'Studio', 'lemonmint' ),
	);
}

/**
 * @param array $choices Rule types.
 * @return array
 */
function lmf_acf_rule_type( $choices ) {
	$choices['Lemon Mint']['lmf_page'] = __( 'Lemon Mint page', 'lemonmint' );
	return $choices;
}
add_filter( 'acf/location/rule_types', 'lmf_acf_rule_type' );

/**
 * @return array
 */
function lmf_acf_rule_values() {
	return lmf_acf_rule_pages();
}
add_filter( 'acf/location/rule_values/lmf_page', 'lmf_acf_rule_values' );

/**
 * @param bool  $match   Result so far.
 * @param array $rule    The rule.
 * @param array $options Screen being tested.
 * @return bool
 */
function lmf_acf_rule_match( $match, $rule, $options ) {
	$post_id = isset( $options['post_id'] ) ? (int) $options['post_id'] : 0;
	$post    = $post_id ? get_post( $post_id ) : null;
	if ( ! $post || 'page' !== $post->post_type ) {
		return '!=' === $rule['operator'];
	}
	$slug     = $rule['value'];
	$template = get_page_template_slug( $post_id );
	$is       = ( $post->post_name === $slug ) || ( 'page-' . $slug . '.php' === $template );

	return ( '!=' === $rule['operator'] ) ? ! $is : $is;
}
add_filter( 'acf/location/rule_match/lmf_page', 'lmf_acf_rule_match', 10, 3 );

/**
 * Options pages: one menu, four screens. Everything global lives here.
 */
function lmf_acf_options_pages() {
	if ( ! lmf_acf_pro() ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'Lemon Mint', 'lemonmint' ),
			'menu_title' => __( 'Lemon Mint', 'lemonmint' ),
			'menu_slug'  => 'lmf-settings',
			'capability' => 'edit_theme_options',
			'icon_url'   => 'dashicons-format-video',
			'position'   => 3,
			'redirect'   => true,
			'autoload'   => true,
		)
	);

	$pages = array(
		'lmf-studio'   => __( 'Studio details', 'lemonmint' ),
		'lmf-home'     => __( 'Homepage', 'lemonmint' ),
		'lmf-services' => __( 'Services page', 'lemonmint' ),
		'lmf-brief'    => __( 'Brief popup', 'lemonmint' ),
		'lmf-elements' => __( 'Header, menu & footer', 'lemonmint' ),
	);
	foreach ( $pages as $slug => $title ) {
		acf_add_options_sub_page(
			array(
				'page_title'  => $title,
				'menu_title'  => $title,
				'menu_slug'   => $slug,
				'parent_slug' => 'lmf-settings',
				'capability'  => 'edit_theme_options',
				'autoload'    => true,
			)
		);
	}
}
add_action( 'acf/init', 'lmf_acf_options_pages' );

/**
 * The groups themselves.
 */
function lmf_acf_register_fields() {
	if ( ! lmf_acf() ) {
		return;
	}
	require_once get_template_directory() . '/inc/acf/fields-global.php';
	require_once get_template_directory() . '/inc/acf/fields-home.php';
	require_once get_template_directory() . '/inc/acf/fields-pages.php';
	require_once get_template_directory() . '/inc/acf/fields-cpt.php';
	require_once get_template_directory() . '/inc/acf/seed.php';
}
add_action( 'acf/init', 'lmf_acf_register_fields' );

/**
 * Field groups the client builds in the admin are saved into the theme as
 * JSON, and loaded from there on every environment.
 */
function lmf_acf_json_save( $path ) {
	$dir = get_stylesheet_directory() . '/acf-json';
	return is_dir( $dir ) ? $dir : $path;
}
add_filter( 'acf/settings/save_json', 'lmf_acf_json_save' );

/**
 * @param array $paths Load paths.
 * @return array
 */
function lmf_acf_json_load( $paths ) {
	$paths[] = get_template_directory() . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'lmf_acf_json_load' );

/* ==========================================================================
   THE OVERLAY — ACF values over the theme's defaults
   ========================================================================== */

/**
 * Homepage.
 *
 * @param array $c Content.
 * @return array
 */
function lmf_acf_home_content( $c ) {
	if ( ! lmf_acf() ) {
		return $c;
	}
	$o = 'option';

	/* ---- promise ---- */
	$c['promise']['kicker']         = lmf_field( 'promise_kicker', $c['promise']['kicker'], $o );
	$c['promise']['headline']       = lmf_field( 'promise_headline', $c['promise']['headline'], $o );
	$c['promise']['text']           = lmf_field( 'promise_text', $c['promise']['text'], $o );
	$c['promise']['figures_kicker'] = lmf_field( 'promise_figures_kicker', $c['promise']['figures_kicker'], $o );
	$c['promise']['sectors_label']  = lmf_field( 'sectors_label', $c['promise']['sectors_label'], $o );

	$figures = lmf_rows( 'promise_figures', array(), $o );
	if ( $figures ) {
		$c['promise']['figures'] = array();
		foreach ( $figures as $f ) {
			$c['promise']['figures'][] = array(
				'value'  => lmf_row( $f, 'value' ),
				'suffix' => lmf_row( $f, 'suffix' ),
				'label'  => lmf_row( $f, 'label' ),
			);
		}
	}

	$sectors = lmf_rows( 'sectors', array(), $o );
	if ( $sectors ) {
		$c['promise']['sectors'] = array();
		foreach ( $sectors as $s ) {
			$name = lmf_row( $s, 'name' );
			if ( $name ) {
				$c['promise']['sectors'][] = $name;
			}
		}
	}

	/* ---- the work wheel ---- */
	$c['cases']['kicker']   = lmf_field( 'cases_kicker', $c['cases']['kicker'], $o );
	$c['cases']['headline'] = lmf_field( 'cases_headline', $c['cases']['headline'], $o );
	$more_label             = lmf_field( 'cases_more_label', '', $o );
	$more_url               = lmf_field( 'cases_more_url', '', $o );
	if ( $more_label ) {
		$c['cases']['more'] = array( $more_label, $more_url ? $more_url : $c['cases']['more'][1] );
	}

	foreach ( array( 'atlas', 'dive' ) as $set ) {
		foreach ( array( 'mp4', 'webm', 'poster' ) as $k ) {
			$v = lmf_url( lmf_field( $set . '_' . $k, '', $o ), '' );
			if ( $v ) {
				$c['cases'][ $set ][ $k ] = $v;
			}
		}
	}
	foreach ( array( 'cols', 'rows' ) as $k ) {
		$v = (int) lmf_field( 'atlas_' . $k, 0, $o );
		if ( $v > 0 ) {
			$c['cases']['atlas'][ $k ] = $v;
		}
	}

	$items = lmf_rows( 'cases_items', array(), $o );
	if ( $items ) {
		$c['cases']['items'] = array();
		foreach ( $items as $i ) {
			$project = lmf_row( $i, 'project', 0 );
			$project = is_array( $project ) ? ( isset( $project['ID'] ) ? (int) $project['ID'] : 0 ) : (int) $project;
			$c['cases']['items'][] = array(
				'code'    => lmf_row( $i, 'code' ),
				'client'  => lmf_row( $i, 'client' ),
				'type'    => lmf_row( $i, 'type' ),
				'title'   => lmf_row( $i, 'title', $project ? get_the_title( $project ) : '' ),
				'result'  => lmf_row( $i, 'result' ),
				'outcome' => lmf_row( $i, 'outcome' ),
				'url'     => lmf_row( $i, 'url', $project ? get_permalink( $project ) : '' ),
				'video'   => lmf_url( lmf_row( $i, 'video', '' ), '' ),
			);
		}
	}

	/* ---- process ---- */
	$c['process']['kicker']   = lmf_field( 'process_kicker', $c['process']['kicker'], $o );
	$c['process']['headline'] = lmf_field( 'process_headline', $c['process']['headline'], $o );
	$c['process']['promise']  = lmf_field( 'process_promise', $c['process']['promise'], $o );

	$steps = lmf_rows( 'process_steps', array(), $o );
	if ( $steps ) {
		$c['process']['steps'] = array();
		foreach ( $steps as $s ) {
			$c['process']['steps'][] = array( lmf_row( $s, 'when' ), lmf_row( $s, 'title' ), lmf_row( $s, 'text' ) );
		}
	}
	$cta_label = lmf_field( 'process_cta_label', '', $o );
	$cta_url   = lmf_field( 'process_cta_url', '', $o );
	if ( $cta_label || $cta_url ) {
		$c['process']['cta'] = array(
			$cta_label ? $cta_label : $c['process']['cta'][0],
			$cta_url ? $cta_url : $c['process']['cta'][1],
		);
	}

	/* ---- voices ---- */
	$c['voices']['kicker']   = lmf_field( 'voices_kicker', $c['voices']['kicker'], $o );
	$c['voices']['headline'] = lmf_field( 'voices_headline', $c['voices']['headline'], $o );

	$quotes = lmf_rows( 'voices_items', array(), $o );
	if ( $quotes ) {
		$c['voices']['items'] = array();
		foreach ( $quotes as $q ) {
			$c['voices']['items'][] = array(
				lmf_row( $q, 'quote' ),
				lmf_row( $q, 'who' ),
				lmf_row( $q, 'org' ),
				lmf_row( $q, 'project' ),
				lmf_url( lmf_row( $q, 'image', '' ), '' ),
				lmf_row( $q, 'url', '' ),
			);
		}
	}

	return $c;
}
add_filter( 'lmf_home_content', 'lmf_acf_home_content', 20 );

/**
 * The closing countdown.
 *
 * @param array $routes number, label, line, intent.
 * @return array
 */
function lmf_acf_cta_routes( $routes ) {
	$rows = lmf_rows( 'cta_routes', array(), 'option' );
	if ( ! $rows ) {
		return $routes;
	}
	$out = array();
	$n   = count( $rows );
	foreach ( $rows as $i => $r ) {
		$out[] = array(
			(string) ( $n - $i ),           // the leader counts down to 1
			lmf_row( $r, 'label' ),
			lmf_row( $r, 'line' ),
			lmf_row( $r, 'intent', 'production' ),
		);
	}
	return $out;
}
add_filter( 'lmf_cta_routes', 'lmf_acf_cta_routes' );

/**
 * The closing section's own words.
 *
 * @param array $copy kicker, headline, caption.
 * @return array
 */
function lmf_acf_cta_copy( $copy ) {
	$copy['kicker']   = lmf_field( 'cta_kicker', $copy['kicker'], 'option' );
	$copy['headline'] = lmf_field( 'cta_headline', $copy['headline'], 'option' );
	$copy['caption']  = lmf_field( 'cta_caption', $copy['caption'], 'option' );
	return $copy;
}
add_filter( 'lmf_cta_copy', 'lmf_acf_cta_copy' );

/**
 * The showreel band on the homepage.
 *
 * @param array $copy kicker, headline, button, url.
 * @return array
 */
function lmf_acf_reel_copy( $copy ) {
	$copy['kicker']   = lmf_field( 'reel_kicker', $copy['kicker'], 'option' );
	$copy['headline'] = lmf_field( 'reel_headline', $copy['headline'], 'option' );
	$copy['text']     = lmf_field( 'reel_text', $copy['text'], 'option' );
	$copy['button']   = lmf_field( 'reel_button', $copy['button'], 'option' );
	return $copy;
}
add_filter( 'lmf_reel_copy', 'lmf_acf_reel_copy' );

/**
 * The brief popup: its words and its choices.
 *
 * @param array $copy Defaults.
 * @return array
 */
function lmf_acf_brief_copy( $copy ) {
	$map = array(
		'q1', 'hint1', 'q2', 'q2b', 'q3', 'hint3', 'q4',
		'next', 'send', 'skip', 'side', 'done_kicker', 'done_title', 'done_text',
	);
	foreach ( $map as $k ) {
		$copy[ $k ] = lmf_field( 'brief_' . $k, $copy[ $k ], 'option' );
	}
	return $copy;
}
add_filter( 'lmf_brief_copy', 'lmf_acf_brief_copy' );

/**
 * The choices the brief (and the contact handler) offer.
 *
 * @param array $c Choices.
 * @return array
 */
function lmf_acf_contact_choices( $c ) {
	if ( ! lmf_acf() ) {
		return $c;
	}
	foreach ( array( 'when', 'budget' ) as $set ) {
		$rows = lmf_rows( 'brief_' . $set, array(), 'option' );
		if ( ! $rows ) {
			continue;
		}
		$c[ $set ] = array();
		foreach ( $rows as $r ) {
			$label = lmf_row( $r, 'label' );
			if ( '' === $label ) {
				continue;
			}
			$c[ $set ][ lmf_choice_key( lmf_row( $r, 'key', $label ) ) ] = $label;
		}
	}
	$other = lmf_field( 'brief_other_label', '', 'option' );
	if ( $other && isset( $c['services']['other'] ) ) {
		$c['services']['other'] = $other;
	}
	return $c;
}
add_filter( 'lmf_contact_choices', 'lmf_acf_contact_choices', 20 );

/**
 * The code a choice travels under, in the form and in the email. Kept as
 * written where it is already safe — "400+" and "50-150" mean something to
 * whoever reads the enquiry — and slugged only when it is not.
 *
 * @param string $key Key or label.
 * @return string
 */
function lmf_choice_key( $key ) {
	$key = trim( (string) $key );
	if ( preg_match( '/^[A-Za-z0-9_+\-]{1,32}$/', $key ) ) {
		return $key;
	}
	$slug = sanitize_title( $key );
	return $slug ? $slug : 'opt';
}

/**
 * About page.
 *
 * @param array $a Content.
 * @return array
 */
function lmf_acf_about_content( $a ) {
	$page = lmf_acf_page_id( 'about' );
	if ( ! $page ) {
		return $a;
	}
	$a['manifesto'] = lmf_field( 'about_manifesto', $a['manifesto'], $page );

	$rows = lmf_rows( 'about_timeline', array(), $page );
	if ( $rows ) {
		$a['timeline'] = array();
		foreach ( $rows as $r ) {
			$a['timeline'][] = array( lmf_row( $r, 'year' ), lmf_row( $r, 'title' ), lmf_row( $r, 'text' ) );
		}
	}
	return $a;
}
add_filter( 'lmf_about_content', 'lmf_acf_about_content' );

/**
 * Studio page.
 *
 * @param array $s Content.
 * @return array
 */
function lmf_acf_studio_content( $s ) {
	$page = lmf_acf_page_id( 'studio' );
	if ( ! $page ) {
		return $s;
	}
	$spaces = lmf_rows( 'studio_spaces', array(), $page );
	if ( $spaces ) {
		$s['spaces'] = array();
		foreach ( $spaces as $r ) {
			$s['spaces'][] = array(
				lmf_row( $r, 'title' ),
				lmf_row( $r, 'meta' ),
				lmf_row( $r, 'text' ),
				lmf_url( lmf_row( $r, 'image', '' ), lmf_page_img( 'studio' ) ),
			);
		}
	}
	$kit = lmf_rows( 'studio_kit', array(), $page );
	if ( $kit ) {
		$s['kit'] = array();
		foreach ( $kit as $r ) {
			$s['kit'][] = array( lmf_row( $r, 'title' ), lmf_row( $r, 'a' ), lmf_row( $r, 'b' ) );
		}
	}
	return $s;
}
add_filter( 'lmf_studio_page_content', 'lmf_acf_studio_content' );

/**
 * Services page: the process strip and the FAQ.
 *
 * @param array $steps Steps.
 * @return array
 */
function lmf_acf_process_steps( $steps ) {
	$rows = lmf_rows( 'svc_process', array(), 'option' );
	if ( ! $rows ) {
		return $steps;
	}
	$out = array();
	foreach ( $rows as $r ) {
		$out[] = array( lmf_row( $r, 'tc' ), lmf_row( $r, 'title' ), lmf_row( $r, 'text' ) );
	}
	return $out;
}
add_filter( 'lmf_process_steps', 'lmf_acf_process_steps' );

/**
 * @param array $faq Questions.
 * @return array
 */
function lmf_acf_services_faq( $faq ) {
	$rows = lmf_rows( 'svc_faq', array(), 'option' );
	if ( ! $rows ) {
		return $faq;
	}
	$out = array();
	foreach ( $rows as $r ) {
		$out[] = array( lmf_row( $r, 'q' ), lmf_row( $r, 'a' ) );
	}
	return $out;
}
add_filter( 'lmf_services_faq', 'lmf_acf_services_faq' );

/**
 * The page a template group is attached to, by slug.
 *
 * @param string $slug Page slug.
 * @return int
 */
function lmf_acf_page_id( $slug ) {
	if ( ! lmf_acf() ) {
		return 0;
	}
	static $cache = array();
	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}
	$page            = get_page_by_path( $slug );
	$cache[ $slug ]  = $page ? (int) $page->ID : 0;
	return $cache[ $slug ];
}

/* ==========================================================================
   ADMIN
   ========================================================================== */

/**
 * The theme runs without ACF, but the client cannot edit anything — say so
 * once, on the plugins and dashboard screens, and nowhere else.
 */
function lmf_acf_notice() {
	if ( lmf_acf_pro() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && ! in_array( $screen->id, array( 'dashboard', 'plugins', 'themes' ), true ) ) {
		return;
	}
	$msg = lmf_acf()
		? __( 'Lemon Mint: ACF is active, but the theme needs ACF <strong>Pro</strong> for the Lemon Mint settings screens (repeaters and options pages). Content is showing the theme defaults meanwhile.', 'lemonmint' )
		: __( 'Lemon Mint: install and activate <strong>Advanced Custom Fields Pro</strong> to edit the homepage, the contact details and the brief popup. The site works without it — it just shows the theme defaults.', 'lemonmint' );
	echo '<div class="notice notice-warning"><p>' . wp_kses( $msg, array( 'strong' => array() ) ) . '</p></div>';
}
add_action( 'admin_notices', 'lmf_acf_notice' );
