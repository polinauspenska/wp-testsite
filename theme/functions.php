<?php
/**
 * Lemon Mint Films — theme setup.
 *
 * Built to Brand Guidelines Edition 02 (2026), the 1A system. The design
 * system lives in style.css; this file wires up the content model the site
 * architecture needs: projects (case studies), services, industries and
 * people, each a real post type with its own template — so every project is
 * an indexable URL rather than an embedded video with a title.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

define( 'LMF_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/cpt.php';
require_once get_template_directory() . '/inc/nav-walker.php';
require_once get_template_directory() . '/inc/meta.php';
require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/demo-content.php';
require_once get_template_directory() . '/inc/services-reel.php';
require_once get_template_directory() . '/inc/studio.php';
require_once get_template_directory() . '/inc/home.php';
require_once get_template_directory() . '/inc/pages.php';
require_once get_template_directory() . '/inc/contact.php';
require_once get_template_directory() . '/inc/brief.php';
require_once get_template_directory() . '/inc/acf.php';

/*
 * Cinematic preloader. Ships as a self-contained folder, so it can equally
 * be installed as a plugin (wp-content/plugins/lmf-preloader) — but do not
 * do both. It hands the mark over to the header lockup when it finishes.
 */
require_once get_template_directory() . '/inc/preloader/lmf-preloader.php';

/**
 * Theme supports and menus.
 */
function lmf_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );

	/*
	 * The brand has exactly four colours and the guideline forbids gradients.
	 * Locking the editor palette is the cheapest way to stop a fifth colour
	 * arriving later through the block editor.
	 */
	add_theme_support(
		'editor-color-palette',
		array(
			array(
				'name'  => __( 'Warm White', 'lemonmint' ),
				'slug'  => 'warm-white',
				'color' => '#F7F5EF',
			),
			array(
				'name'  => __( 'Lemon', 'lemonmint' ),
				'slug'  => 'lemon',
				'color' => '#F5B82E',
			),
			array(
				'name'  => __( 'Mint', 'lemonmint' ),
				'slug'  => 'mint',
				'color' => '#17805C',
			),
			array(
				'name'  => __( 'Film Black', 'lemonmint' ),
				'slug'  => 'film-black',
				'color' => '#111111',
			),
		)
	);
	add_theme_support( 'disable-custom-colors' );
	add_theme_support( 'disable-custom-gradients' );
	add_theme_support( 'editor-gradient-presets', array() );

	// Image sizes match the aspect ratios the templates actually use.
	add_image_size( 'lmf-hero', 1680, 720, true );  // 21:9 poster still.
	add_image_size( 'lmf-tile', 900, 600, true );   // 3:2 work grid.
	add_image_size( 'lmf-film', 1280, 720, true );  // 16:9 player poster.

	register_nav_menus(
		array(
			'primary' => __( 'Primary navigation', 'lemonmint' ),
			'footer'  => __( 'Footer — studio column', 'lemonmint' ),
		)
	);
}
add_action( 'after_setup_theme', 'lmf_setup' );

/**
 * Styles and scripts.
 *
 * Anton and Montserrat are the only two faces in the system, and they ship
 * with the theme (assets/fonts). Раньше они приезжали с Google Fonts — из-за
 * этого набор на разных устройствах выглядел по-разному: пока ответ от Google
 * не пришёл (а в корпоративной сети, за блокировщиком или в офлайн-превью он
 * не приходит вовсе), Anton подменялся на Arial, и заголовок терял лицо.
 * Свои файлы снимают и это, и вопрос с GDPR.
 */
function lmf_assets() {
	wp_enqueue_style( 'lmf-fonts', get_template_directory_uri() . '/assets/css/fonts.css', array(), LMF_VERSION );
	wp_enqueue_style( 'lmf-style', get_stylesheet_uri(), array( 'lmf-fonts' ), LMF_VERSION );

	wp_enqueue_script( 'lmf-still', get_template_directory_uri() . '/assets/js/still.js', array(), LMF_VERSION, true );
	wp_enqueue_script( 'lmf-site', get_template_directory_uri() . '/assets/js/site.js', array( 'lmf-still' ), LMF_VERSION, true );

	/*
	 * Курсор-видоискатель — на всём сайте. Только мышь/трекпад; на тач-экранах,
	 * без JS и при reduce-motion остаётся системный курсор.
	 */
	wp_enqueue_style( 'lmf-cursor', get_template_directory_uri() . '/assets/css/cursor.css', array( 'lmf-style' ), LMF_VERSION );
	wp_enqueue_script( 'lmf-cursor', get_template_directory_uri() . '/assets/js/cursor.js', array(), LMF_VERSION, true );

	// Полноэкранное меню: большие слова на размытой странице. На всех экранах.
	wp_enqueue_style( 'lmf-menu', get_template_directory_uri() . '/assets/css/menu.css', array( 'lmf-style' ), LMF_VERSION );
	wp_enqueue_script( 'lmf-menu', get_template_directory_uri() . '/assets/js/menu.js', array(), LMF_VERSION, true );
	wp_enqueue_script( 'lmf-cta', get_template_directory_uri() . '/assets/js/cta.js', array(), LMF_VERSION, true );

	/*
	 * Первый экран: перфорация в левом поле и вход заголовка. Только на
	 * главной — больше он нигде не нужен. Стили отдельным файлом, чтобы
	 * приём можно было снять одной строкой, не трогая style.css.
	 */
	$uri = get_template_directory_uri();

	/*
	 * Страницы с кадром на первом экране: главная и пять посадочных
	 * (About, Services, Industries, Studio, Contact). Кадр, шапка в реверсе,
	 * вход заголовка и перфорация — одни и те же файлы для всех.
	 */
	if ( lmf_is_cine_page() ) {
		wp_enqueue_style( 'lmf-hero-film', $uri . '/assets/css/hero-film.css', array( 'lmf-style' ), LMF_VERSION );
		wp_enqueue_script( 'lmf-hero-film', $uri . '/assets/js/hero-film.js', array(), LMF_VERSION, true );
		wp_enqueue_style( 'lmf-hero-video', $uri . '/assets/css/hero-video.css', array( 'lmf-hero-film' ), LMF_VERSION );
		wp_enqueue_script( 'lmf-hero-video', $uri . '/assets/js/hero-video.js', array(), LMF_VERSION, true );
	}

	// Посадочные страницы: их собственные секции.
	if ( lmf_is_cine_page() && ! is_front_page() ) {
		wp_enqueue_style( 'lmf-pages', $uri . '/assets/css/pages.css', array( 'lmf-hero-video' ), LMF_VERSION );
		wp_enqueue_script( 'lmf-pages', $uri . '/assets/js/pages.js', array(), LMF_VERSION, true );
	}

	// Бобина с услугами: главная и страница Services.
	if ( is_front_page() || is_post_type_archive( 'lmf_service' ) ) {
		wp_enqueue_style( 'lmf-services-reel', $uri . '/assets/css/services-reel.css', array( 'lmf-style' ), LMF_VERSION );
		wp_enqueue_script( 'lmf-services-reel', $uri . '/assets/js/services-reel.js', array(), LMF_VERSION, true );
	}

	/*
	 * Contact: один экран — строки, карта, действие. Зависит только от
	 * style.css: страница больше не «киношная», pages.css на ней не грузится,
	 * и зависимость от него молча отменяла бы всю таблицу.
	 */
	if ( is_page( 'contact' ) ) {
		wp_enqueue_style( 'lmf-contact', $uri . '/assets/css/contact.css', array( 'lmf-style' ), LMF_VERSION );
		wp_enqueue_script( 'lmf-contact', $uri . '/assets/js/contact.js', array(), LMF_VERSION, true );
	}

	// Главная после бобины: обещание, кейсы, пакет, процесс, отзывы.
	if ( is_front_page() ) {
		wp_enqueue_style( 'lmf-home', $uri . '/assets/css/home.css', array( 'lmf-style' ), LMF_VERSION );
		wp_enqueue_script( 'lmf-home', $uri . '/assets/js/home.js', array(), LMF_VERSION, true );
		wp_enqueue_script( 'lmf-voices', $uri . '/assets/js/voices.js', array(), LMF_VERSION, true );
		wp_enqueue_script( 'lmf-promise', $uri . '/assets/js/promise.js', array(), LMF_VERSION, true );
		wp_enqueue_style( 'lmf-work-wheel', $uri . '/assets/css/work-wheel.css', array( 'lmf-style' ), LMF_VERSION );
		wp_enqueue_script( 'lmf-work-wheel', $uri . '/assets/js/work-wheel.js', array(), LMF_VERSION, true );
	}

	// «The studio» — страница About.
	if ( is_page( 'about' ) ) {
		wp_enqueue_style( 'lmf-studio', $uri . '/assets/css/studio.css', array( 'lmf-style' ), LMF_VERSION );
		wp_enqueue_script( 'lmf-studio', $uri . '/assets/js/studio.js', array(), LMF_VERSION, true );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'lmf_assets' );

/**
 * Две гарнитуры, которые нужны первому экрану, просим заранее.
 *
 * Без этого браузер узнаёт о шрифте только дочитав fonts.css, и заголовок
 * секунду стоит подстановочным. Предзагружаем ровно latin-подмножества
 * Anton 400 и Montserrat 500 — остальные начертания подтянутся по ходу, их
 * на первом экране нет.
 */
function lmf_preload_fonts() {
	$dir = get_template_directory_uri() . '/assets/fonts/';
	foreach ( array( 'anton-latin-400-normal.woff2', 'montserrat-latin-500-normal.woff2' ) as $file ) {
		printf(
			'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>' . "\n",
			esc_url( $dir . $file )
		);
	}
}
add_action( 'wp_head', 'lmf_preload_fonts', 1 );

/**
 * Body classes used by the stylesheet.
 *
 * @param array $classes Existing classes.
 * @return array
 */
function lmf_body_class( $classes ) {
	if ( is_singular( 'lmf_project' ) ) {
		$classes[] = 'is-case-study';
	}
	/*
	 * На страницах с кадром (главная и пять посадочных) шапка уходит из
	 * потока и ложится на кадр. На остальных она sticky и обычная.
	 */
	if ( lmf_is_cine_page() ) {
		$classes[] = 'has-video-hero';
	}
	return $classes;
}
add_filter( 'body_class', 'lmf_body_class' );

/**
 * Work archive: honour the service / industry / format parameters the filter
 * chips write into the URL, so a filtered view is shareable and shows up in
 * analytics rather than living only in front-end state.
 *
 * @param WP_Query $q The query.
 */
function lmf_archive_query( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}

	if ( $q->is_post_type_archive( 'lmf_project' ) ) {
		$q->set( 'posts_per_page', 24 );
		$q->set( 'orderby', 'menu_order date' );
		$q->set( 'order', 'DESC' );

		$tax = array();
		$map = array(
			'svc' => 'lmf_service_cat',
			'ind' => 'lmf_industry_cat',
			'fmt' => 'lmf_format',
		);
		foreach ( $map as $param => $taxonomy ) {
			if ( ! empty( $_GET[ $param ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$tax[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => sanitize_title( wp_unslash( $_GET[ $param ] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				);
			}
		}
		if ( $tax ) {
			$tax['relation'] = 'AND';
			$q->set( 'tax_query', $tax ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}
	}

	if ( $q->is_post_type_archive( 'lmf_service' ) || $q->is_post_type_archive( 'lmf_industry' ) ) {
		$q->set( 'posts_per_page', -1 );
		$q->set( 'orderby', 'menu_order' );
		$q->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'lmf_archive_query' );

/**
 * Structured data.
 *
 * Organization sitewide, VideoObject on every project, BreadcrumbList
 * throughout, and FAQPage wherever a service page actually carries an FAQ —
 * the schema is what turns those answers into a search snippet.
 */
function lmf_schema() {
	$graph = array();

	$graph[] = array(
		'@type'   => 'Organization',
		'@id'     => home_url( '/#org' ),
		'name'    => get_bloginfo( 'name' ),
		'url'     => home_url( '/' ),
		'logo'    => get_template_directory_uri() . '/assets/img/mark.png',
		'address' => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => lmf_opt( 'address', 'Warehouse 28, Al Quoz Industrial Third' ),
			'addressLocality' => 'Dubai',
			'addressCountry'  => 'AE',
		),
		'telephone' => lmf_opt( 'phone', '+971 4 332 3054' ),
		'email'     => lmf_opt( 'email', 'info@lemonmintfilms.com' ),
	);

	if ( is_singular( 'lmf_project' ) ) {
		$id      = get_the_ID();
		$graph[] = array(
			'@type'        => 'VideoObject',
			'name'         => get_the_title(),
			'description'  => wp_strip_all_tags( (string) get_post_meta( $id, '_lmf_brief', true ) ),
			'uploadDate'   => get_the_date( 'c' ),
			'thumbnailUrl' => get_the_post_thumbnail_url( $id, 'lmf-film' ) ? get_the_post_thumbnail_url( $id, 'lmf-film' ) : '',
			'duration'     => lmf_iso_duration( (string) get_post_meta( $id, '_lmf_duration', true ) ),
		);
	}

	if ( is_singular( 'lmf_service' ) ) {
		$faq = lmf_get_faq( get_the_ID() );
		if ( $faq ) {
			$items = array();
			foreach ( $faq as $row ) {
				$items[] = array(
					'@type'          => 'Question',
					'name'           => $row['q'],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $row['a'],
					),
				);
			}
			$graph[] = array(
				'@type'      => 'FAQPage',
				'mainEntity' => $items,
			);
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		)
	) . '</script>' . "\n";
}
add_action( 'wp_head', 'lmf_schema' );

/**
 * Turn "02:14" into "PT2M14S" for VideoObject.
 *
 * @param string $mmss Duration as mm:ss.
 * @return string
 */
function lmf_iso_duration( $mmss ) {
	if ( ! $mmss || ! preg_match( '/^(\d+):(\d{2})$/', trim( $mmss ), $m ) ) {
		return '';
	}
	return sprintf( 'PT%dM%dS', (int) $m[1], (int) $m[2] );
}

/**
 * Trim head output the design never uses — emoji detection and the generator
 * tag. A video-led site spends its performance budget on the hero still.
 */
function lmf_trim_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'lmf_trim_head' );

/**
 * Customizer: the handful of values that genuinely differ per environment.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function lmf_customize( $wp_customize ) {
	$wp_customize->add_section(
		'lmf_studio',
		array(
			'title'    => __( 'Lemon Mint — studio details', 'lemonmint' ),
			'priority' => 30,
		)
	);

	// Each field gets the sanitiser its own type needs — a blanket
	// wp_kses_post() would let markup through into a URL or a phone number.
	$fields = array(
		'lmf_address'   => array( __( 'Studio address', 'lemonmint' ), 'Warehouse 28, Al Quoz Industrial Third', 'sanitize_text_field' ),
		'lmf_phone'     => array( __( 'Phone', 'lemonmint' ), '+971 4 332 3054', 'sanitize_text_field' ),
		'lmf_whatsapp'  => array( __( 'WhatsApp number (digits only, with country code)', 'lemonmint' ), '', 'sanitize_text_field' ),
		'lmf_email'     => array( __( 'Enquiries email', 'lemonmint' ), 'info@lemonmintfilms.com', 'sanitize_email' ),
		'lmf_response'  => array( __( 'Response-time commitment', 'lemonmint' ), 'Under 4 working hours', 'sanitize_text_field' ),
		'lmf_hours'     => array( __( 'Studio hours (Contact page; the live clock assumes Mon–Fri 09–18)', 'lemonmint' ), 'Mon–Fri · 09:00–18:00', 'sanitize_text_field' ),
		'lmf_city'      => array( __( 'City and country (Contact page, under the address)', 'lemonmint' ), 'Dubai, United Arab Emirates', 'sanitize_text_field' ),
		'lmf_reel_url'  => array( __( 'Showreel URL (Mux, Cloudflare Stream, Bunny…)', 'lemonmint' ), '', 'esc_url_raw' ),
		'lmf_hero_line' => array( __( 'Homepage headline', 'lemonmint' ), 'Built for the screen, not the scroll.', 'sanitize_text_field' ),
			// Клип первого экрана и его постер меняются без правки шаблона.
			'lmf_hero_video'  => array( __( 'Hero video (mp4, muted loop)', 'lemonmint' ), get_template_directory_uri() . '/assets/video/lmf-hero.mp4', 'esc_url_raw' ),
			'lmf_hero_poster' => array( __( 'Hero poster frame (jpg)', 'lemonmint' ), get_template_directory_uri() . '/assets/video/lmf-hero-poster.jpg', 'esc_url_raw' ),
	);

	// The Contact page's live map. Switched off, the drawn map plate stays —
	// it is still a map, and "Get directions" still opens the real one.
	$wp_customize->add_setting(
		'lmf_map_embed',
		array(
			'default'           => true,
			'sanitize_callback' => function ( $v ) {
				return (bool) $v;
			},
		)
	);
	$wp_customize->add_control(
		'lmf_map_embed',
		array(
			'label'   => __( 'Contact page: show the live Google map', 'lemonmint' ),
			'section' => 'lmf_studio',
			'type'    => 'checkbox',
		)
	);

	foreach ( $fields as $key => $conf ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $conf[1],
				'sanitize_callback' => $conf[2],
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $conf[0],
				'section' => 'lmf_studio',
				'type'    => 'text',
			)
		);
	}
}
add_action( 'customize_register', 'lmf_customize' );
