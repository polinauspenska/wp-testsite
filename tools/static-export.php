<?php
/**
 * Static export — the WordPress theme, rendered to flat HTML for GitHub Pages.
 *
 * The theme itself is not modified: this file stands in for WordPress. It
 * provides the handful of core functions the templates call, a small in-memory
 * content store built from the theme's own demo data (inc/demo-content.php),
 * and a URL map that turns permalinks into .html files sitting next to each
 * other, so the whole site works from a project subfolder such as
 * /wp-testsite/ with no server behind it.
 *
 * usage: php tools/static-export.php [build-dir]
 *
 * @package LemonMintFilms
 */

$ROOT  = dirname( __DIR__ );
$THEME = $ROOT . '/theme';
$BUILD = isset( $argv[1] ) ? $argv[1] : $ROOT . '/build';

define( 'ABSPATH', $BUILD . '/' );
define( 'WP_CONTENT_DIR', $BUILD . '/wp-content' );
define( 'WP_CONTENT_URL', './wp-content' );
define( 'WPINC', 'wp-includes' );

/* ==========================================================================
   THE CONTENT STORE
   ========================================================================== */

$GLOBALS['POSTS']  = array();   // id => object
$GLOBALS['META']   = array();   // id => array
$GLOBALS['LOOP']   = array();   // the current query's ids
$GLOBALS['LOOP_I'] = -1;
$GLOBALS['CURRENT'] = 0;
$GLOBALS['PAGE']   = 'home';
$GLOBALS['a']      = array();   // actions
$GLOBALS['f']      = array();   // filters
$GLOBALS['css']    = array();
$GLOBALS['js']     = array();
$GLOBALS['inline'] = array();
$GLOBALS['head']   = array();
$GLOBALS['opt']    = array();

/**
 * Add a post to the store.
 */
function lmf_x_post( $args, $meta = array() ) {
	static $id = 100;
	$id++;
	$p = (object) array_merge(
		array(
			'ID'           => $id,
			'post_type'    => 'page',
			'post_title'   => '',
			'post_name'    => '',
			'post_content' => '',
			'post_excerpt' => '',
			'post_status'  => 'publish',
			'menu_order'   => 0,
		),
		$args
	);
	$p->ID                  = $id;
	$GLOBALS['POSTS'][ $id ] = $p;
	$GLOBALS['META'][ $id ]  = $meta;
	return $id;
}

/* ==========================================================================
   WORDPRESS, ENOUGH OF IT
   ========================================================================== */

// -- hooks -----------------------------------------------------------------
function add_action( $h, $c, $p = 10, $n = 1 ) { $GLOBALS['a'][ $h ][ $p ][] = $c; }
function remove_action( $h, $c, $p = 10 ) {
	if ( empty( $GLOBALS['a'][ $h ][ $p ] ) ) { return; }
	$GLOBALS['a'][ $h ][ $p ] = array_values( array_filter( $GLOBALS['a'][ $h ][ $p ], function ( $x ) use ( $c ) { return $x !== $c; } ) );
}
function do_action( $h, ...$args ) {
	if ( empty( $GLOBALS['a'][ $h ] ) ) { return; }
	ksort( $GLOBALS['a'][ $h ] );
	foreach ( $GLOBALS['a'][ $h ] as $cs ) { foreach ( $cs as $c ) { call_user_func_array( $c, $args ); } }
}
function add_filter( $h, $c, $p = 10, $n = 1 ) { $GLOBALS['f'][ $h ][ $p ][] = $c; }
function remove_filter( $h, $c, $p = 10 ) {
	if ( empty( $GLOBALS['f'][ $h ][ $p ] ) ) { return; }
	$GLOBALS['f'][ $h ][ $p ] = array_values( array_filter( $GLOBALS['f'][ $h ][ $p ], function ( $x ) use ( $c ) { return $x !== $c; } ) );
}
function has_filter( $h, $c = false ) { return ! empty( $GLOBALS['f'][ $h ] ); }
function apply_filters( $h, $v, ...$args ) {
	if ( empty( $GLOBALS['f'][ $h ] ) ) { return $v; }
	ksort( $GLOBALS['f'][ $h ] );
	foreach ( $GLOBALS['f'][ $h ] as $cs ) { foreach ( $cs as $c ) { $v = call_user_func_array( $c, array_merge( array( $v ), $args ) ); } }
	return $v;
}
function add_theme_support() {} function add_image_size() {} function register_nav_menus() {}
function load_theme_textdomain() {} function add_editor_style() {} function add_post_type_support() {}
function register_post_type() {} function register_taxonomy() {} function flush_rewrite_rules() {}
function add_meta_box() {} function add_management_page() {} function add_submenu_page() {} function add_options_page() {}
function wp_enqueue_script( $h, $src = '', $d = array(), $v = '', $foot = false ) {
	if ( ! isset( $GLOBALS['js'][ $h ] ) ) { $GLOBALS['js'][ $h ] = array( 'src' => '', 'before' => array(), 'after' => array() ); }
	if ( $src ) { $GLOBALS['js'][ $h ]['src'] = $src; }
}
function wp_enqueue_style( $h, $src = '', $d = array(), $v = '' ) { if ( $src ) { $GLOBALS['css'][ $h ] = $src; } }
function wp_register_style( $h, $src = '', $d = array(), $v = '' ) {}
function wp_register_script( $h, $src = '', $d = array(), $v = '' ) {}
function wp_add_inline_style( $h, $css ) { $GLOBALS['inline'][] = $css; }
function wp_add_inline_script( $h, $js, $pos = 'after' ) {
	if ( ! isset( $GLOBALS['js'][ $h ] ) ) { $GLOBALS['js'][ $h ] = array( 'src' => '', 'before' => array(), 'after' => array() ); }
	$GLOBALS['js'][ $h ][ 'before' === $pos ? 'before' : 'after' ][] = $js;
}
function wp_script_add_data() {} function wp_style_add_data() {} function wp_dequeue_script() {} function wp_localize_script() {}

// -- escaping and i18n -----------------------------------------------------
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return esc_html( $s ); }
function esc_url( $s ) { return esc_html( $s ); }
function esc_url_raw( $s ) { return $s; }
function esc_textarea( $s ) { return esc_html( $s ); }
function esc_js( $s ) { return esc_html( $s ); }
function wp_kses( $s ) { return $s; }
function wp_kses_post( $s ) { return $s; }
function __( $s, $d = '' ) { return $s; }
function _x( $s, $c = '', $d = '' ) { return $s; }
function _n( $s, $p, $n, $d = '' ) { return $n > 1 ? $p : $s; }
function esc_html__( $s, $d = '' ) { return esc_html( $s ); }
function esc_attr__( $s, $d = '' ) { return esc_attr( $s ); }
function esc_html_e( $s, $d = '' ) { echo esc_html( $s ); }
function esc_attr_e( $s, $d = '' ) { echo esc_attr( $s ); }
function _e( $s, $d = '' ) { echo esc_html( $s ); }
function wp_strip_all_tags( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_textarea_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_title( $s ) { return strtolower( preg_replace( '/[^a-z0-9]+/i', '-', trim( (string) $s ) ) ); }
function sanitize_key( $s ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $s ) ); }
function sanitize_email( $s ) { return $s; }
function sanitize_file_name( $s ) { return $s; }
function wp_unslash( $s ) { return $s; }
function absint( $n ) { return abs( (int) $n ); }
function wp_json_encode( $v, $flags = 0 ) { return json_encode( $v, $flags | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); }
function wp_normalize_path( $p ) { return str_replace( '\\', '/', $p ); }
function wp_rand( $a = 0, $b = 0 ) { return random_int( $a, $b ? $b : PHP_INT_MAX ); }
function is_wp_error( $x ) { return false; }
function wp_parse_args( $a, $d = array() ) { return array_merge( $d, (array) $a ); }
function selected( $a, $b, $e = true ) { $r = ( (string) $a === (string) $b ) ? ' selected="selected"' : ''; if ( $e ) { echo $r; } return $r; }
function checked( $a, $b = true, $e = true ) { $r = ( (string) $a === (string) $b ) ? ' checked="checked"' : ''; if ( $e ) { echo $r; } return $r; }
function disabled( $a, $b = true, $e = true ) { return ''; }

// -- urls ------------------------------------------------------------------
/**
 * Every permalink becomes a flat file next to the others.
 */
function lmf_x_url( $path ) {
	$path  = (string) $path;
	$query = '';
	if ( false !== strpos( $path, '?' ) ) {
		list( $path, $query ) = explode( '?', $path, 2 );
		$query = '?' . $query;
	}
	$hash = '';
	if ( false !== strpos( $path, '#' ) ) {
		list( $path, $hash ) = explode( '#', $path, 2 );
		$hash = '#' . $hash;
	}
	$p = trim( str_replace( 'http://lmf.local', '', $path ), '/' );

	if ( '' === $p ) { return './index.html' . $query . $hash; }

	$map = array(
		'about'          => 'about.html',
		'studio'         => 'studio.html',
		'contact'        => 'contact.html',
		'privacy-policy' => 'privacy-policy.html',
		'terms'          => 'terms.html',
		'work'           => 'work.html',
		'services'       => 'services.html',
		'industries'     => 'industries.html',
	);
	if ( isset( $map[ $p ] ) ) { return './' . $map[ $p ] . $query . $hash; }

	$parts = explode( '/', $p );
	if ( count( $parts ) > 1 && isset( $map[ $parts[0] ] ) ) {
		// /work/nafas/ → the project page, when it was exported
		$file = $parts[0] . '-' . $parts[1] . '.html';
		if ( in_array( $parts[0], array( 'work', 'services', 'industries' ), true ) && file_exists( $GLOBALS['BUILD'] . '/' . $file ) ) {
			return './' . $file . $query . $hash;
		}
		return './' . $map[ $parts[0] ] . $query . $hash;
	}
	return './index.html' . $query . $hash;
}
function home_url( $p = '/' ) { return lmf_x_url( $p ); }
function site_url( $p = '/' ) { return lmf_x_url( $p ); }
function get_home_url( $b = null, $p = '/' ) { return lmf_x_url( $p ); }
function admin_url( $p = '' ) { return ''; }
function content_url( $p = '' ) { return WP_CONTENT_URL . $p; }
function plugins_url( $rel = '', $file = '' ) { return WP_CONTENT_URL . '/plugins/lmf-preloader/' . ltrim( $rel, '/' ); }
function includes_url( $p = '' ) { return './wp-includes/' . ltrim( $p, '/' ); }
function get_template_directory() { return $GLOBALS['THEME']; }
function get_stylesheet_directory() { return $GLOBALS['THEME']; }
function get_template_directory_uri() { return WP_CONTENT_URL . '/themes/lemon-mint-films'; }
function get_stylesheet_directory_uri() { return get_template_directory_uri(); }
function get_stylesheet_uri() { return get_template_directory_uri() . '/style.css'; }
function get_theme_file_path( $f = '' ) { return $GLOBALS['THEME'] . '/' . ltrim( $f, '/' ); }
function get_theme_file_uri( $f = '' ) { return get_template_directory_uri() . '/' . ltrim( $f, '/' ); }
function add_query_arg( $k, $v = null, $url = '' ) {
	if ( is_array( $k ) ) { $url = $v; $args = $k; } else { $args = array( $k => $v ); }
	$sep = ( false === strpos( (string) $url, '?' ) ) ? '?' : '&';
	$out = '';
	foreach ( $args as $key => $val ) { $out .= ( '' === $out ? $sep : '&' ) . rawurlencode( $key ) . '=' . rawurlencode( $val ); }
	return $url . $out;
}
function remove_query_arg( $k, $url = '' ) { return preg_replace( '/[?&]' . preg_quote( $k, '/' ) . '=[^&]*/', '', (string) $url ); }
function wp_safe_redirect( $u ) {} function wp_redirect( $u ) {} function wp_get_referer() { return ''; }
function wp_create_nonce( $a = '' ) { return 'static'; }
function wp_verify_nonce( $n, $a = '' ) { return true; }
function wp_nonce_field( $a = '', $n = '', $r = true, $e = true ) {}
function check_admin_referer( $a = '' ) { return true; }
function current_user_can( $c ) { return false; }
function is_user_logged_in() { return false; }
function is_admin() { return false; }
function wp_doing_ajax() { return false; }
function wp_using_themes() { return true; }

// -- options and theme mods -------------------------------------------------
function get_option( $k, $d = false ) { return isset( $GLOBALS['opt'][ $k ] ) ? $GLOBALS['opt'][ $k ] : $d; }
function update_option( $k, $v ) { $GLOBALS['opt'][ $k ] = $v; return true; }
function delete_option( $k ) { unset( $GLOBALS['opt'][ $k ] ); return true; }
function get_theme_mod( $k, $d = false ) { return $d; }
function set_theme_mod( $k, $v ) {}
function get_transient( $k ) { return false; }
function set_transient( $k, $v, $t = 0 ) { return true; }
function delete_transient( $k ) { return true; }
function wp_cache_get( $k, $g = '' ) { return false; }
function wp_cache_set( $k, $v, $g = '', $e = 0 ) { return true; }

// -- posts ------------------------------------------------------------------
function get_post( $id = null ) {
	$id = $id ? ( is_object( $id ) ? $id->ID : (int) $id ) : $GLOBALS['CURRENT'];
	return isset( $GLOBALS['POSTS'][ $id ] ) ? $GLOBALS['POSTS'][ $id ] : null;
}
function get_posts( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array( 'post_type' => 'post', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC', 'meta_key' => '', 'meta_value' => '' )
	);
	$types = (array) $args['post_type'];
	$out   = array();
	foreach ( $GLOBALS['POSTS'] as $p ) {
		if ( ! in_array( $p->post_type, $types, true ) || 'publish' !== $p->post_status ) { continue; }
		if ( $args['meta_key'] ) {
			$m = get_post_meta( $p->ID, $args['meta_key'], true );
			if ( (string) $m !== (string) $args['meta_value'] ) { continue; }
		}
		$out[] = $p;
	}
	usort( $out, function ( $a, $b ) { return $a->menu_order <=> $b->menu_order; } );
	$n = (int) $args['numberposts'];
	return ( $n > 0 ) ? array_slice( $out, 0, $n ) : $out;
}
function get_page_by_path( $slug, $output = OBJECT, $type = 'page' ) {
	foreach ( $GLOBALS['POSTS'] as $p ) {
		if ( $p->post_name === $slug && in_array( $p->post_type, (array) $type, true ) ) { return $p; }
	}
	return null;
}
function get_post_status( $id ) { $p = get_post( $id ); return $p ? $p->post_status : false; }
function get_post_meta( $id, $key = '', $single = false ) {
	$id = is_object( $id ) ? $id->ID : (int) $id;
	if ( '' === $key ) { return isset( $GLOBALS['META'][ $id ] ) ? $GLOBALS['META'][ $id ] : array(); }
	$v = isset( $GLOBALS['META'][ $id ][ $key ] ) ? $GLOBALS['META'][ $id ][ $key ] : '';
	return $single ? $v : ( '' === $v ? array() : array( $v ) );
}
function update_post_meta( $id, $k, $v ) { $GLOBALS['META'][ (int) $id ][ $k ] = $v; return true; }
function get_the_title( $p = 0 ) { $o = get_post( $p ); return $o ? $o->post_title : ''; }
function get_the_ID() { return $GLOBALS['CURRENT']; }
function get_permalink( $p = 0 ) {
	$o = get_post( $p );
	if ( ! $o ) { return lmf_x_url( '/' ); }
	$base = array( 'lmf_project' => 'work', 'lmf_service' => 'services', 'lmf_industry' => 'industries', 'lmf_person' => 'people' );
	if ( isset( $base[ $o->post_type ] ) ) { return lmf_x_url( '/' . $base[ $o->post_type ] . '/' . $o->post_name . '/' ); }
	return lmf_x_url( '/' . $o->post_name . '/' );
}
function get_the_permalink( $p = 0 ) { return get_permalink( $p ); }
function get_the_excerpt( $p = 0 ) { $o = get_post( $p ); return $o ? ( $o->post_excerpt ? $o->post_excerpt : wp_trim_words( $o->post_content, 30 ) ) : ''; }
function wp_trim_words( $t, $n = 55, $more = '…' ) {
	$w = preg_split( '/\s+/', wp_strip_all_tags( $t ) );
	return count( $w ) > $n ? implode( ' ', array_slice( $w, 0, $n ) ) . $more : implode( ' ', $w );
}
function get_the_content( $more = null, $strip = false, $p = 0 ) { $o = get_post( $p ); return $o ? $o->post_content : ''; }
function the_content() { $o = get_post(); echo $o ? wpautop( $o->post_content ) : ''; }
function the_title( $b = '', $a = '', $echo = true ) { $t = $b . get_the_title() . $a; if ( $echo ) { echo $t; } return $t; }
function the_ID() { echo get_the_ID(); }
function wpautop( $t ) {
	$t = trim( (string) $t );
	if ( '' === $t ) { return ''; }
	$blocks = preg_split( '/\n\s*\n/', $t );
	$out    = '';
	foreach ( $blocks as $b ) {
		$b = trim( $b );
		if ( '' === $b ) { continue; }
		$out .= preg_match( '/^<(h\d|ul|ol|p|div|blockquote)/i', $b ) ? $b : '<p>' . nl2br( $b ) . '</p>';
		$out .= "\n";
	}
	return $out;
}
function has_post_thumbnail( $p = 0 ) { return false; }
function get_the_post_thumbnail_url( $p = 0, $size = '' ) { return false; }
function get_the_post_thumbnail( $p = 0, $size = '' ) { return ''; }
function wp_get_attachment_url( $id ) { return ''; }
function wp_get_attachment_image_url( $id, $size = '' ) { return ''; }
function attachment_url_to_postid( $u ) { return 0; }
function get_post_type( $p = 0 ) { $o = get_post( $p ); return $o ? $o->post_type : ''; }
function get_post_type_archive_link( $type ) {
	$map = array( 'lmf_project' => '/work/', 'lmf_service' => '/services/', 'lmf_industry' => '/industries/' );
	return lmf_x_url( isset( $map[ $type ] ) ? $map[ $type ] : '/' );
}
function get_post_type_object( $t ) { return (object) array( 'labels' => (object) array( 'name' => ucfirst( $t ) ) ); }
function has_term() { return true; }
function get_the_terms() { return array(); }
function get_terms() { return array(); }
function get_term_link( $t ) { return lmf_x_url( '/' ); }
function wp_get_post_terms() { return array(); }
function wp_insert_post( $a ) { return 0; }
function wp_update_post( $a ) { return 0; }
function wp_set_object_terms() {}
function get_post_thumbnail_id() { return 0; }
function get_the_date( $f = '', $p = 0 ) { return gmdate( $f ? $f : 'Y-m-d' ); }
function get_the_modified_date( $f = '', $p = 0 ) { return gmdate( $f ? $f : 'Y-m-d' ); }
function get_page_template_slug( $p = 0 ) { return ''; }
function get_queried_object_id() { return $GLOBALS['CURRENT']; }
function get_queried_object() { return get_post( $GLOBALS['CURRENT'] ); }

// -- the loop ---------------------------------------------------------------
function have_posts() { return ( $GLOBALS['LOOP_I'] + 1 ) < count( $GLOBALS['LOOP'] ); }
function the_post() {
	$GLOBALS['LOOP_I']++;
	$GLOBALS['CURRENT'] = $GLOBALS['LOOP'][ $GLOBALS['LOOP_I'] ];
	$GLOBALS['post']    = get_post( $GLOBALS['CURRENT'] );
}
function rewind_posts() { $GLOBALS['LOOP_I'] = -1; }
function wp_reset_postdata() {}
function the_posts_pagination( $a = array() ) {}
function paginate_links( $a = array() ) { return ''; }

// -- conditionals ------------------------------------------------------------
function is_front_page() { return 'home' === $GLOBALS['PAGE']; }
function is_home() { return false; }
function is_page( $p = '' ) {
	$pages = array( 'about', 'studio', 'contact', 'privacy-policy', 'terms' );
	if ( ! in_array( $GLOBALS['PAGE'], $pages, true ) ) { return false; }
	if ( '' === $p ) { return true; }
	return in_array( $GLOBALS['PAGE'], (array) $p, true );
}
function is_post_type_archive( $t = '' ) {
	$m   = array( 'services' => 'lmf_service', 'industries' => 'lmf_industry', 'work' => 'lmf_project' );
	$cur = isset( $m[ $GLOBALS['PAGE'] ] ) ? $m[ $GLOBALS['PAGE'] ] : null;
	if ( ! $cur ) { return false; }
	return '' === $t || in_array( $cur, (array) $t, true );
}
function is_singular( $t = '' ) {
	if ( 0 !== strpos( $GLOBALS['PAGE'], 'single:' ) ) { return false; }
	$type = substr( $GLOBALS['PAGE'], 7 );
	return '' === $t || in_array( $type, (array) $t, true );
}
function is_single( $t = '' ) { return is_singular( $t ); }
function is_archive() { return is_post_type_archive(); }
function is_search() { return false; } function is_404() { return '404' === $GLOBALS['PAGE']; }
function is_feed() { return false; } function is_embed() { return false; } function is_preview() { return false; }
function is_paged() { return false; } function is_category() { return false; } function is_tax() { return false; }
function comments_open() { return false; } function get_comments_number() { return 0; }
function is_active_sidebar() { return false; } function dynamic_sidebar() {}
function has_nav_menu( $l ) { return false; }
function wp_nav_menu( $a = array() ) {}
function get_nav_menu_locations() { return array(); }
function wp_get_nav_menu_items() { return array(); }
function wp_get_nav_menu_object( $n ) { return false; }
function wp_create_nav_menu( $n ) { return 0; }
function wp_update_nav_menu_item() {}
function language_attributes() { echo 'lang="en"'; }
function bloginfo( $k = '' ) { echo get_bloginfo( $k ); }
function get_bloginfo( $k = '' ) {
	$v = array(
		'name'        => 'Lemon Mint Films',
		'description' => 'Film, series & podcast, made in Dubai',
		'charset'     => 'UTF-8',
		'url'         => lmf_x_url( '/' ),
	);
	return isset( $v[ $k ] ) ? $v[ $k ] : '';
}
function body_class( $c = '' ) {
	$classes = array();
	if ( is_front_page() ) { $classes[] = 'home'; } else { $classes[] = 'page'; }
	if ( is_post_type_archive() ) { $classes[] = 'archive'; }
	$classes = apply_filters( 'body_class', $classes );
	echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
}
function post_class( $c = '' ) { echo 'class="' . esc_attr( is_array( $c ) ? implode( ' ', $c ) : $c ) . '"'; }
function wp_body_open() { do_action( 'wp_body_open' ); }
function wp_head() {
	do_action( 'wp_enqueue_scripts' );
	foreach ( $GLOBALS['head'] as $h ) { echo $h; }
	do_action( 'wp_head' );
	foreach ( $GLOBALS['css'] as $h => $src ) {
		printf( '<link rel="stylesheet" href="%s">' . "\n", esc_url( $src ) );
	}
	foreach ( $GLOBALS['inline'] as $css ) { echo "<style>\n" . $css . "\n</style>\n"; }
}
function wp_footer() {
	do_action( 'wp_footer' );
	foreach ( $GLOBALS['js'] as $h => $item ) {
		foreach ( $item['before'] as $js ) { echo "<script>\n" . $js . "\n</script>\n"; }
		if ( $item['src'] ) {
			// The preloader ships as an ES module; WordPress tags it through
			// the script_loader_tag filter, which this export applies here.
			$tag = sprintf( '<script%s src="%s"></script>' . "\n", 'lmf-preloader' === $h ? ' type="module"' : '', esc_url( $item['src'] ) );
			echo apply_filters( 'script_loader_tag', $tag, $h, $item['src'] );
		}
		foreach ( $item['after'] as $js ) { echo "<script>\n" . $js . "\n</script>\n"; }
	}
}
function wp_mail() { return true; }
function wp_send_json_success( $d = null ) {} function wp_send_json_error( $d = null, $c = 0 ) {}
function status_header( $c ) {} function nocache_headers() {} function wp_die( $m = '' ) { die( $m ); }
function get_search_form( $echo = true ) {}
function get_search_query() { return ''; }
function wp_timezone_string() { return 'Asia/Dubai'; }
function current_time( $t = 'timestamp' ) { return time(); }
function human_time_diff( $a, $b = 0 ) { return ''; }
function number_format_i18n( $n, $d = 0 ) { return number_format( $n, $d ); }
function get_avatar() { return ''; }
function settings_fields() {} function do_settings_sections() {} function submit_button( $t = '' ) {}
function register_setting() {} function add_settings_section() {} function add_settings_field() {} function add_settings_error() {}
function get_current_screen() { return null; }
function wp_get_theme() { return (object) array( 'Version' => '1.0.0' ); }
function wp_get_environment_type() { return 'production'; }
function wp_is_json_request() { return false; }
function post_type_exists( $t ) { return true; }
function taxonomy_exists( $t ) { return true; }
function get_field( $n, $p = false ) { return null; }
function the_field( $n, $p = false ) {}
function acf_add_local_field_group( $g ) {}

function is_customize_preview() { return false; }
function get_post_field( $field, $p = 0 ) { $o = get_post( $p ); return ( $o && isset( $o->$field ) ) ? $o->$field : ''; }
function get_the_author_meta( $f = '', $u = 0 ) { return 'Lemon Mint Films'; }
function get_edit_post_link() { return ''; }
function edit_post_link() {}
function wp_is_mobile() { return false; }
function is_robots() { return false; }
function did_action( $h ) { return 0; }
function doing_action( $h ) { return false; }
function register_activation_hook() {} function register_deactivation_hook() {}
function plugin_basename( $f ) { return basename( $f ); }
function plugin_dir_path( $f ) { return dirname( $f ) . '/'; }
function plugin_dir_url( $f ) { return WP_CONTENT_URL . '/plugins/lmf-preloader/'; }
function wp_style_is( $h, $l = 'enqueued' ) { return isset( $GLOBALS['css'][ $h ] ); }
function wp_script_is( $h, $l = 'enqueued' ) { return isset( $GLOBALS['js'][ $h ] ); }
function do_shortcode( $s ) { return $s; }
function add_shortcode() {} function shortcode_exists() { return false; }
function wp_list_pluck( $l, $f ) { return array_map( function ( $i ) use ( $f ) { return is_object( $i ) ? $i->$f : $i[ $f ]; }, (array) $l ); }
function get_template() { return 'lemon-mint-films'; }
function get_locale() { return 'en_US'; }
function is_rtl() { return false; }
function get_current_user_id() { return 0; }
function wp_get_current_user() { return (object) array( 'ID' => 0, 'display_name' => '' ); }
function wp_upload_dir() { return array( 'basedir' => WP_CONTENT_DIR . '/uploads', 'baseurl' => WP_CONTENT_URL . '/uploads' ); }
function wp_mkdir_p( $d ) { return is_dir( $d ) || mkdir( $d, 0755, true ); }
function size_format( $b, $d = 0 ) { return $b; }
function esc_sql( $s ) { return $s; }
function trailingslashit( $s ) { return rtrim( (string) $s, '/' ) . '/'; }
function untrailingslashit( $s ) { return rtrim( (string) $s, '/' ); }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function wp_remote_get() { return array(); }
function wp_http_validate_url( $u ) { return $u; }
function wp_generate_password( $l = 12 ) { return substr( md5( (string) mt_rand() ), 0, $l ); }
function get_intermediate_image_sizes() { return array(); }
function wp_get_additional_image_sizes() { return array(); }

class Walker { public $tree_type = ''; public $db_fields = array(); }
class Walker_Nav_Menu extends Walker {
	public function start_lvl( &$o, $d = 0, $a = null ) {}
	public function end_lvl( &$o, $d = 0, $a = null ) {}
	public function start_el( &$o, $i, $d = 0, $a = null, $id = 0 ) {}
	public function end_el( &$o, $i, $d = 0, $a = null ) {}
}
/**
 * Enough of WP_Query for the "related work" strips: it answers with real
 * projects from the store instead of an empty result.
 */
class WP_Query {
	public $posts = array();
	public $found_posts = 0;
	public $max_num_pages = 1;

	public function __construct( $args = array() ) {
		if ( ! $args ) { return; }
		$type    = isset( $args['post_type'] ) ? $args['post_type'] : 'post';
		$exclude = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
		$n       = isset( $args['posts_per_page'] ) ? (int) $args['posts_per_page'] : 3;
		$found   = array();
		foreach ( get_posts( array( 'post_type' => $type ) ) as $p ) {
			if ( in_array( $p->ID, $exclude, true ) ) { continue; }
			$found[] = $p;
			if ( $n > 0 && count( $found ) >= $n ) { break; }
		}
		$this->posts       = $found;
		$this->found_posts = count( $found );
	}
	public function set_404() {}
}
class WP_Error {}

define( 'OBJECT', 'OBJECT' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );

/* ==========================================================================
   THE THEME
   ========================================================================== */

$GLOBALS['THEME'] = $THEME;
$GLOBALS['BUILD'] = $BUILD;

require $THEME . '/functions.php';

function get_header( $n = null ) { include $GLOBALS['THEME'] . '/header.php'; }
function get_footer( $n = null ) { include $GLOBALS['THEME'] . '/footer.php'; }
function get_template_part( $slug, $name = null ) {
	$f = $GLOBALS['THEME'] . '/' . $slug . ( $name ? "-$name" : '' ) . '.php';
	if ( file_exists( $f ) ) { include $f; }
}
function get_sidebar( $n = null ) {}
function comments_template() {}

/* ==========================================================================
   CONTENT — the theme's own demo data, as if it had been installed
   ========================================================================== */

$demo = lmf_demo_data();

foreach ( $demo['services'] as $i => $s ) {
	lmf_x_post(
		array( 'post_type' => 'lmf_service', 'post_title' => $s[0], 'post_name' => $s[1], 'post_excerpt' => $s[2], 'post_content' => $s[2], 'menu_order' => $i + 1 ),
		array( '_lmf_keyword' => $s[3], '_lmf_cta' => $s[4], '_lmf_still' => $s[5] )
	);
}
foreach ( $demo['industries'] as $i => $n ) {
	lmf_x_post(
		array( 'post_type' => 'lmf_industry', 'post_title' => $n[0], 'post_name' => $n[1], 'post_excerpt' => $n[2], 'post_content' => $n[2], 'menu_order' => $i + 1 ),
		array( '_lmf_still' => $n[3] )
	);
}
foreach ( $demo['projects'] as $i => $p ) {
	lmf_x_post(
		array( 'post_type' => 'lmf_project', 'post_title' => $p['title'], 'post_name' => $p['slug'], 'post_excerpt' => $p['brief'], 'post_content' => $p['brief'], 'menu_order' => $i + 1 ),
		array(
			'_lmf_code'      => $p['code'],
			'_lmf_duration'  => $p['dur'],
			'_lmf_client'    => $p['client'],
			'_lmf_subtitle'  => $p['sub'],
			'_lmf_brief'     => $p['brief'],
			'_lmf_challenge' => isset( $p['chal'] ) ? $p['chal'] : '',
			'_lmf_direction' => isset( $p['dir'] ) ? $p['dir'] : '',
			'_lmf_process'   => isset( $p['proc'] ) ? $p['proc'] : '',
			'_lmf_why'       => isset( $p['why'] ) ? $p['why'] : '',
			'_lmf_still'     => isset( $p['kind'] ) ? $p['kind'] : 'wide',
		)
	);
}

// The pages, including the two legal ones with the theme's own default text.
$pages = array(
	'home'           => array( 'Home', '' ),
	'about'          => array( 'About', '' ),
	'studio'         => array( 'Studio', '' ),
	'contact'        => array( 'Contact', '' ),
	'privacy-policy' => array( 'Privacy Policy', defined( 'LMF_PRIVACY_COPY' ) ? LMF_PRIVACY_COPY : '' ),
	'terms'          => array( 'Terms of Use', defined( 'LMF_TERMS_COPY' ) ? LMF_TERMS_COPY : '' ),
);
$page_ids = array();
foreach ( $pages as $slug => $p ) {
	$page_ids[ $slug ] = lmf_x_post( array( 'post_type' => 'page', 'post_title' => $p[0], 'post_name' => $slug, 'post_content' => $p[1] ) );
}
update_option( 'wp_page_for_privacy_policy', $page_ids['privacy-policy'] );

/* ==========================================================================
   RENDER
   ========================================================================== */

/**
 * Render one page to a file.
 *
 * @param string $page     Which page (drives the is_* conditionals).
 * @param string $template Theme template file.
 * @param string $out      Output file name.
 * @param int    $current  The post the template is "on".
 * @param array  $loop     Post ids for the main loop.
 */
function lmf_x_render( $page, $template, $out, $current = 0, $loop = array() ) {
	$GLOBALS['PAGE']    = $page;
	$GLOBALS['CURRENT'] = $current;
	$GLOBALS['post']    = $current ? get_post( $current ) : null;
	$GLOBALS['LOOP']    = $loop;
	$GLOBALS['LOOP_I']  = -1;
	$GLOBALS['css']     = array();
	$GLOBALS['js']      = array();
	$GLOBALS['inline']  = array();
	$GLOBALS['inline_js'] = array();
	$GLOBALS['wp_query'] = (object) array( 'found_posts' => count( $loop ), 'max_num_pages' => 1 );

	ob_start();
	include $GLOBALS['THEME'] . '/' . $template;
	$html = ob_get_clean();

	file_put_contents( $GLOBALS['BUILD'] . '/' . $out, $html );
	printf( "  %-26s %6.1f KB\n", $out, strlen( $html ) / 1024 );
}

// build folder
if ( ! is_dir( $BUILD ) ) { mkdir( $BUILD, 0755, true ); }

echo "Rendering:\n";
lmf_x_render( 'home', 'front-page.php', 'index.html', $page_ids['home'] );
lmf_x_render( 'about', 'page-about.php', 'about.html', $page_ids['about'] );
lmf_x_render( 'studio', 'page-studio.php', 'studio.html', $page_ids['studio'] );
lmf_x_render( 'contact', 'page-contact.php', 'contact.html', $page_ids['contact'] );
lmf_x_render( 'services', 'archive-lmf_service.php', 'services.html' );
lmf_x_render( 'industries', 'archive-lmf_industry.php', 'industries.html' );

$projects = array_map( function ( $p ) { return $p->ID; }, get_posts( array( 'post_type' => 'lmf_project' ) ) );
lmf_x_render( 'work', 'archive-lmf_project.php', 'work.html', 0, $projects );

foreach ( $projects as $id ) {
	$p = get_post( $id );
	lmf_x_render( 'single:lmf_project', 'single-lmf_project.php', 'work-' . $p->post_name . '.html', $id, array( $id ) );
}
foreach ( get_posts( array( 'post_type' => 'lmf_service' ) ) as $p ) {
	lmf_x_render( 'single:lmf_service', 'single-lmf_service.php', 'services-' . $p->post_name . '.html', $p->ID, array( $p->ID ) );
}
foreach ( get_posts( array( 'post_type' => 'lmf_industry' ) ) as $p ) {
	lmf_x_render( 'single:lmf_industry', 'single-lmf_industry.php', 'industries-' . $p->post_name . '.html', $p->ID, array( $p->ID ) );
}

lmf_x_render( 'privacy-policy', 'page.php', 'privacy-policy.html', $page_ids['privacy-policy'], array( $page_ids['privacy-policy'] ) );
lmf_x_render( 'terms', 'page.php', 'terms.html', $page_ids['terms'], array( $page_ids['terms'] ) );
lmf_x_render( '404', '404.php', '404.html' );

echo "Done.\n";
