<?php
/**
 * Template tags — the components the design system is made of.
 *
 * Every helper here maps to a named element in the brand guidelines, so a
 * developer adding a page has a vocabulary rather than a blank stylesheet.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * The mark — the client's supplied artwork, used as-is.
 *
 * Never redraw, stretch, rotate, recolour the wedges, or place it on a lemon
 * surface (the wedges disappear into the ground). The only sanctioned
 * alternative is the reversed lockup on mint, which is a separate file rather
 * than a CSS filter so the mint accents can become lemon and stay visible.
 *
 * @param string $class    Extra classes on the wrapper.
 * @param bool   $reversed True on a mint surface only.
 */
function lmf_mark( $class = '', $reversed = false ) {
	printf(
		'<span class="mkw %s"><img class="mk" src="%s" alt="" width="420" height="425" loading="lazy" decoding="async"></span>',
		esc_attr( $class ),
		esc_url( get_template_directory_uri() . '/assets/img/' . ( $reversed ? 'mark-reversed.png' : 'mark.png' ) )
	);
}

/**
 * The supplied logo as inline SVG, read once per request.
 *
 * Inline rather than <img> for one reason: the preloader hands the mark over
 * to the header when it finishes, and a flight that lands on a raster would
 * show the swap. Same file, same viewBox, both vector — so the arrival is
 * invisible.
 *
 * @return string SVG markup, or '' if the file is missing.
 */
function lmf_logo_svg() {
	static $svg = null;
	if ( null !== $svg ) {
		return $svg;
	}

	$path = get_theme_file_path( 'assets/img/lmf-logo.svg' );
	if ( ! is_readable( $path ) ) {
		$svg = '';
		return $svg;
	}

	$markup = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	// Ships with the theme, but strip anything executable regardless.
	$markup = preg_replace( '#<\?xml[^>]*\?>#i', '', $markup );
	$markup = preg_replace( '#<(script|foreignObject)\b[^>]*>.*?</\1>#is', '', $markup );
	$markup = preg_replace( '#\son[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)#is', '', $markup );

	/*
	 * Классы для реверса.
	 *
	 * Поставленный reversed-логотип устроен так: чёрное становится тёплым
	 * белым, мятное — лимонным, лимонное не меняется (проверено по
	 * lockup-reversed.png: зелёных пикселей 0, лимонных ровно на столько
	 * больше, сколько было мятных). Повторить это фильтром нельзя, а второго
	 * файла для v2 нет — поэтому размечаем сами заливки, не трогая их
	 * значений. В покое SVG остаётся байт-в-байт тем же, что и был:
	 * добавляется только атрибут class, который без .is-rev ничего не делает.
	 */
	$markup = preg_replace_callback(
		'/fill="rgb\(\s*([\d.]+)%\s*,\s*([\d.]+)%\s*,\s*([\d.]+)%\s*\)"/i',
		function ( $m ) {
			$r = (float) $m[1];
			$g = (float) $m[2];
			$b = (float) $m[3];

			if ( max( $r, $g, $b ) < 25 ) {
				$class = 'i';   // чёрная краска
			} elseif ( $g > $r ) {
				$class = 'm';   // мята
			} else {
				$class = 'l';   // лимон
			}

			return $m[0] . ' class="' . $class . '"';
		},
		$markup
	);

	// Контуры колёс — чёрный stroke тоже должен уходить в тёплый белый.
	$markup = preg_replace_callback(
		'/stroke="rgb\(\s*([\d.]+)%\s*,\s*([\d.]+)%\s*,\s*([\d.]+)%\s*\)"/i',
		function ( $m ) {
			if ( max( (float) $m[1], (float) $m[2], (float) $m[3] ) < 25 ) {
				return $m[0] . ' class="si"';
			}
			return $m[0];
		},
		$markup
	);

	$svg = trim( $markup );
	return $svg;
}

/**
 * The horizontal lockup — mark plus wordmark, one supplied asset.
 *
 * @param bool  $reversed True on a mint surface only. Never on black or lemon.
 * @param array $attrs    Extra attributes for the root element, e.g. the
 *                        preloader's flight target marker.
 */
function lmf_lockup( $reversed = false, $attrs = array() ) {
	$svg = lmf_logo_svg();

	/*
	 * Реверс — не фильтр: краски переназначаются поимённо (см. lmf_logo_svg),
	 * ровно по правилу поставленного lockup-reversed. Пока векторного v2 не
	 * было, здесь стоял растр v1 — из-за него на мятной плашке жил старый
	 * логотип. Теперь обе версии — один и тот же файл.
	 */
	if ( '' === $svg && $reversed ) {
		printf(
			'<img class="lockup" src="%s" alt="%s" width="1000" height="267" decoding="async">',
			esc_url( get_template_directory_uri() . '/assets/img/lockup-reversed.png' ),
			esc_attr( get_bloginfo( 'name' ) )
		);
		return;
	}

	if ( '' === $svg ) {
		printf(
			'<img class="lockup" src="%s" alt="%s" width="1000" height="267" decoding="async">',
			esc_url( get_template_directory_uri() . '/assets/img/lockup.png' ),
			esc_attr( get_bloginfo( 'name' ) )
		);
		return;
	}

	/*
	 * The mark is printed more than once per page — header, footer, and the
	 * preloader inlines the same file again. An SVG carries ids for its
	 * clipPath, so every copy needs its own suffix: without it the markup is
	 * invalid and the later copies' url(#…) references resolve against the
	 * FIRST copy's defs.
	 */
	static $instance = 0;
	$instance++;
	$suffix = '-' . $instance;

	$svg = preg_replace( '/id="(lmf-[^"]+)"/', 'id="$1' . $suffix . '"', $svg );
	$svg = preg_replace( '/url\(#(lmf-[^)]+)\)/', 'url(#$1' . $suffix . ')', $svg );
	$svg = preg_replace( '/(xlink:href|href)="#(lmf-[^"]+)"/', '$1="#$2' . $suffix . '"', $svg );

	$extra = 'class="lockup' . ( $reversed ? ' is-rev' : '' ) . '" role="img" aria-label="' . esc_attr( get_bloginfo( 'name' ) ) . '"';
	foreach ( $attrs as $k => $v ) {
		$extra .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	}
	$svg = preg_replace( '/<svg\b/', '<svg ' . $extra, $svg, 1 );

	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Kicker — mint, uppercase, 0.28em tracking. Always above the headline.
 *
 * @param string $text Kicker text.
 */
function lmf_kicker( $text ) {
	printf( '<span class="kicker">%s</span>', esc_html( $text ) );
}

/**
 * A still. Uses the featured image where one exists; otherwise draws a
 * halftone placeholder so an unfinished page still reads as designed rather
 * than broken — and stays inside the four-colour palette either way.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Registered image size.
 * @param int    $w       Placeholder canvas width.
 * @param int    $h       Placeholder canvas height.
 */
function lmf_still( $post_id = 0, $size = 'lmf-tile', $w = 900, $h = 600 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	echo '<div class="still">';
	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, $size, array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		$kind = get_post_meta( $post_id, '_lmf_still', true );
		$kind = $kind ? $kind : 'wide';
		printf(
			'<canvas data-seed="%s" data-kind="%s" width="%d" height="%d"></canvas><span class="tagph">%s</span>',
			esc_attr( 'p-' . $post_id . '-' . get_post_field( 'post_name', $post_id ) ),
			esc_attr( $kind ),
			(int) $w,
			(int) $h,
			esc_html__( 'still — placeholder', 'lemonmint' )
		);
	}
	echo '</div>';
}

/**
 * The caption row from the guideline's work page: title left, LMF code and
 * duration right, tabular figures so codes align down the column.
 *
 * @param int $post_id Post ID.
 */
function lmf_caption_row( $post_id = 0 ) {
	$post_id  = $post_id ? $post_id : get_the_ID();
	$code     = get_post_meta( $post_id, '_lmf_code', true );
	$duration = get_post_meta( $post_id, '_lmf_duration', true );
	$subtitle = get_post_meta( $post_id, '_lmf_subtitle', true );
	$client   = get_post_meta( $post_id, '_lmf_client', true );

	echo '<span class="caprow">';
	printf( '<span class="t">%s</span>', esc_html( get_the_title( $post_id ) ) );
	if ( $code || $duration ) {
		printf(
			'<span class="meta">%s</span>',
			esc_html( trim( ( $code ? 'LMF / ' . $code : '' ) . ( $code && $duration ? ' · ' : '' ) . $duration ) )
		);
	}
	echo '</span>';

	$sub = array_filter( array( $subtitle, $client ) );
	if ( $sub ) {
		printf( '<span class="sub">%s</span>', esc_html( implode( ' · ', $sub ) ) );
	}
}

/**
 * One project tile.
 *
 * @param int $post_id Post ID.
 */
function lmf_tile( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	printf( '<a class="tile" href="%s">', esc_url( get_permalink( $post_id ) ) );
	lmf_still( $post_id, 'lmf-tile' );
	lmf_caption_row( $post_id );
	echo '</a>';
}

/**
 * Filter chips for the Work hub. Writes state into the URL rather than
 * front-end memory, so a filtered view is shareable and analytics-visible.
 *
 * @param string $param    Query parameter (svc, ind, fmt).
 * @param string $taxonomy Taxonomy name.
 * @param string $label    Group label.
 */
function lmf_filter_group( $param, $taxonomy, $label ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}

	$current = isset( $_GET[ $param ] ) ? sanitize_title( wp_unslash( $_GET[ $param ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$base    = get_post_type_archive_link( 'lmf_project' );
	$keep    = array();
	foreach ( array( 'svc', 'ind', 'fmt' ) as $p ) {
		if ( $p !== $param && ! empty( $_GET[ $p ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$keep[ $p ] = sanitize_title( wp_unslash( $_GET[ $p ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}

	echo '<div class="fgroup">';
	lmf_kicker( $label );
	foreach ( $terms as $term ) {
		$on   = ( $current === $term->slug );
		$args = $keep;
		if ( ! $on ) {
			$args[ $param ] = $term->slug;
		}
		printf(
			'<a class="fchip%s" href="%s">%s</a>',
			$on ? ' on' : '',
			esc_url( $args ? add_query_arg( $args, $base ) : $base ),
			esc_html( $term->name )
		);
	}
	echo '</div>';
}

/**
 * The closing lemon surface. One of exactly two accent surfaces per page —
 * the guideline rations colour at roughly 70 / 14 / 10 / 6.
 */
function lmf_closing_cta() {
	$contact_page = get_page_by_path( 'contact' );
	$contact      = $contact_page ? get_permalink( $contact_page ) : home_url( '/contact/' );
	/*
	 * A film leader counts down 3 · 2 · 1 before the picture starts — the
	 * three ways in are that countdown, and "1" is the one that rolls
	 * camera. Hover a line and the leader jumps to its number.
	 */
	$routes = apply_filters(
		'lmf_cta_routes',
		array(
			array( '3', __( 'Request a quote', 'lemonmint' ), __( 'You need a number for the budget.', 'lemonmint' ), 'quote' ),
			array( '2', __( 'Book a discovery call', 'lemonmint' ), __( 'The idea is still taking shape.', 'lemonmint' ), 'call' ),
			array( '1', __( 'Start a production', 'lemonmint' ), __( 'You have a brief and a date.', 'lemonmint' ), 'production' ),
		)
	);

	// The words around the countdown, so they are editable too.
	$copy = apply_filters(
		'lmf_cta_copy',
		array(
			'kicker'   => __( 'Contact', 'lemonmint' ),
			'headline' => __( 'Let’s shoot something', 'lemonmint' ),
			'caption'  => __( 'Roll camera', 'lemonmint' ),
		)
	);
	?>
	<section class="surface-lemon cta" data-cta>
		<div class="wrap cta-in">
			<div class="cta-left">
				<?php lmf_kicker( $copy['kicker'] ); ?>
				<h2 class="h2 cta-t"><?php echo esc_html( $copy['headline'] ); ?><span style="color:var(--mint)">.</span></h2>

				<ol class="cta-routes">
					<?php foreach ( $routes as $r ) : ?>
						<li>
							<a class="cta-r" data-n="<?php echo esc_attr( $r[0] ); ?>" href="<?php echo esc_url( add_query_arg( 'i', $r[3], $contact ) ); ?>">
								<span class="cta-n" aria-hidden="true"><?php echo esc_html( $r[0] ); ?></span>
								<span class="cta-txt">
									<span class="cta-do"><?php echo esc_html( $r[1] ); ?></span>
									<span class="cta-when"><?php echo esc_html( $r[2] ); ?></span>
								</span>
								<span class="cta-go" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18"><path d="M5 12h13M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8"/></svg></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>

			<!-- the leader -->
			<div class="cta-leader" aria-hidden="true">
				<div class="cta-dial">
					<span class="cta-sweep"></span>
					<span class="cta-ring cta-ring--o"></span>
					<span class="cta-ring cta-ring--i"></span>
					<span class="cta-cross cta-cross--h"></span>
					<span class="cta-cross cta-cross--v"></span>
					<span class="cta-num">1</span>
				</div>
				<p class="meta cta-cap"><span>LMF / LEADER</span><span class="cta-cap-r"><?php echo esc_html( $copy['caption'] ); ?></span></p>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Breadcrumbs — BreadcrumbList schema plus a visible trail.
 */
function lmf_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$items = array( array( home_url( '/' ), __( 'Home', 'lemonmint' ) ) );

	if ( is_singular( 'lmf_project' ) ) {
		$items[] = array( get_post_type_archive_link( 'lmf_project' ), __( 'Work', 'lemonmint' ) );
	} elseif ( is_singular( 'lmf_service' ) ) {
		$items[] = array( get_post_type_archive_link( 'lmf_service' ), __( 'Services', 'lemonmint' ) );
	} elseif ( is_singular( 'lmf_industry' ) ) {
		$items[] = array( get_post_type_archive_link( 'lmf_industry' ), __( 'Industries', 'lemonmint' ) );
	}

	if ( is_singular() ) {
		$items[] = array( get_permalink(), get_the_title() );
	}

	$list = array();
	foreach ( $items as $i => $item ) {
		$list[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $item[1],
			'item'     => $item[0],
		);
	}
	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $list,
		)
	) . '</script>';
}
add_action( 'wp_footer', 'lmf_breadcrumbs' );

/**
 * Related projects by shared service or industry — the internal-linking
 * backbone, not a decorative row.
 *
 * @param int $post_id Post ID.
 * @param int $count   How many.
 * @return WP_Post[]
 */
function lmf_related_projects( $post_id, $count = 3 ) {
	$svc = wp_get_post_terms( $post_id, 'lmf_service_cat', array( 'fields' => 'ids' ) );
	$ind = wp_get_post_terms( $post_id, 'lmf_industry_cat', array( 'fields' => 'ids' ) );

	$tax = array( 'relation' => 'OR' );
	if ( $svc ) {
		$tax[] = array(
			'taxonomy' => 'lmf_service_cat',
			'field'    => 'term_id',
			'terms'    => $svc,
		);
	}
	if ( $ind ) {
		$tax[] = array(
			'taxonomy' => 'lmf_industry_cat',
			'field'    => 'term_id',
			'terms'    => $ind,
		);
	}

	$q = new WP_Query(
		array(
			'post_type'           => 'lmf_project',
			'posts_per_page'      => $count,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
			'tax_query'           => count( $tax ) > 1 ? $tax : array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		)
	);
	return $q->posts;
}

/**
 * Projects for a given service page, pulled automatically by taxonomy so an
 * editor never has to remember to attach proof.
 *
 * @param int $service_id Service post ID.
 * @param int $count      How many.
 * @return WP_Post[]
 */
function lmf_service_projects( $service_id, $count = 3 ) {
	$slug = get_post_field( 'post_name', $service_id );
	$q    = new WP_Query(
		array(
			'post_type'      => 'lmf_project',
			'posts_per_page' => $count,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'lmf_service_cat',
					'field'    => 'slug',
					'terms'    => $slug,
				),
			),
		)
	);
	return $q->posts;
}

/**
 * The five main destinations, in order. One list for the header bar and the
 * full-screen menu, so they can never disagree. A WordPress menu assigned to
 * "Primary navigation" replaces it (top level only).
 *
 * @return array[] { url, label }
 */
function lmf_nav_items() {
	$items = array();

	if ( has_nav_menu( 'primary' ) ) {
		$locations = get_nav_menu_locations();
		$menu      = isset( $locations['primary'] ) ? wp_get_nav_menu_items( $locations['primary'] ) : array();
		foreach ( (array) $menu as $m ) {
			if ( empty( $m->menu_item_parent ) ) {
				$items[] = array( 'url' => $m->url, 'label' => $m->title );
			}
		}
	}

	if ( ! $items ) {
		$items = array(
			array( 'url' => home_url( '/about/' ), 'label' => __( 'About', 'lemonmint' ) ),
			array( 'url' => get_post_type_archive_link( 'lmf_service' ), 'label' => __( 'Services', 'lemonmint' ) ),
			array( 'url' => get_post_type_archive_link( 'lmf_industry' ), 'label' => __( 'Industries', 'lemonmint' ) ),
			array( 'url' => home_url( '/studio/' ), 'label' => __( 'Studio', 'lemonmint' ) ),
			array( 'url' => home_url( '/contact/' ), 'label' => __( 'Contact us', 'lemonmint' ) ),
		);
	}

	return apply_filters( 'lmf_nav_items', $items );
}
