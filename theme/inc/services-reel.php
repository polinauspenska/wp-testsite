<?php
/**
 * SERVICES REEL — data and markup.
 *
 * Где берутся услуги (по порядку):
 *
 *   1. Записи «Services» в админке WordPress. Заголовок → название,
 *      «Отрывок» → короткое описание, «Изображение записи» → кадр,
 *      «Порядок» в атрибутах → место на плёнке. Ссылка — страница услуги.
 *   2. Если записей ещё нет — массив lmf_reel_default_services() ниже.
 *   3. В самом конце — фильтр 'lmf_reel_services': им можно переписать
 *      список из дочерней темы или плагина, не трогая этот файл.
 *
 * Формат одной услуги (одинаковый для всех трёх источников):
 *
 *   array(
 *       'title' => 'Brand Films',                 // название, крупно
 *       'text'  => 'Long-form storytelling…',     // 1–2 предложения
 *       'url'   => 'https://…/services/brand-films/',
 *       'image' => 'https://…/still.jpg',         // 3:2, от 1200×800
 *       'cta'   => 'Discover Service',            // подпись кнопки
 *   )
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Запасной список — тот же набор услуг, что ставит демо-контент.
 * Кадры — стоп-кадры из клипа первого экрана (assets/img/reel/01…09.jpg);
 * у любой услуги с «Изображением записи» кадр заменяется им.
 *
 * Чтобы добавить услугу здесь: скопируйте строку-массив, поменяйте поля,
 * положите картинку 3:2 в assets/img/reel/ и укажите её имя в 'image'.
 *
 * @return array
 */
function lmf_reel_default_services() {
	$img = get_template_directory_uri() . '/assets/img/reel/';
	$svc = home_url( '/services/' );

	return array(
		array(
			'title' => 'Corporate Films',
			'text'  => 'Company stories, leadership profiles and brand films — told cinematically, not corporately.',
			'url'   => $svc . 'corporate-films/',
			'image' => $img . '01.jpg',
		),
		array(
			'title' => 'Ad Films',
			'text'  => 'Commercials and celebrity-led campaigns for television, digital and social.',
			'url'   => $svc . 'ad-films/',
			'image' => $img . '02.jpg',
		),
		array(
			'title' => 'Film & TV Production',
			'text'  => 'Scripted series, short films and original entertainment for broadcasters and platforms.',
			'url'   => $svc . 'film-tv-production/',
			'image' => $img . '03.jpg',
		),
		array(
			'title' => 'Microdramas',
			'text'  => 'Vertical, short-form episodic drama built for mobile-first platforms.',
			'url'   => $svc . 'microdramas/',
			'image' => $img . '04.jpg',
		),
		array(
			'title' => 'Social Media Management',
			'text'  => 'Strategy, monthly content calendars and the production that fills them.',
			'url'   => $svc . 'social-media-management/',
			'image' => $img . '05.jpg',
		),
		array(
			'title' => 'AI Integrated Content',
			'text'  => 'Human-led creative direction with AI-enabled production workflows.',
			'url'   => $svc . 'ai-integrated-content/',
			'image' => $img . '06.jpg',
		),
		array(
			'title' => 'Podcast Production',
			'text'  => 'A produced show — format, multi-camera recording, full post and the short-form cutdowns.',
			'url'   => $svc . 'podcast-production/',
			'image' => $img . '08.jpg',
		),
		array(
			'title' => 'Event Videography',
			'text'  => 'Corporate events and award ceremonies, with reels inside 24–48 hours.',
			'url'   => $svc . 'event-videography/',
			'image' => $img . '07.jpg',
		),
		array(
			'title' => 'Music Productions',
			'text'  => 'Music videos, event anthems and brand campaigns, with the film around them.',
			'url'   => $svc . 'music-productions/',
			'image' => $img . '09.jpg',
		),
	);
}

/**
 * Итоговый список услуг для плёнки.
 *
 * @return array
 */
function lmf_reel_services() {
	$fallback = lmf_reel_default_services();
	$out      = array();

	$posts = get_posts(
		array(
			'post_type'   => 'lmf_service',
			'numberposts' => 24,          // больше на плёнку не помещается осмысленно
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		)
	);

	foreach ( $posts as $i => $p ) {
		$image = get_the_post_thumbnail_url( $p->ID, 'large' );
		if ( ! $image ) {
			// кадра нет — берём стоп-кадр по кругу, чтобы плёнка не была пустой
			$image = $fallback[ $i % count( $fallback ) ]['image'];
		}
		$out[] = array(
			'title' => get_the_title( $p ),
			'text'  => wp_strip_all_tags( get_the_excerpt( $p ) ),
			'url'   => get_permalink( $p ),
			'image' => $image,
		);
	}

	if ( ! $out ) {
		$out = $fallback;
	}

	/**
	 * Последнее слово — за фильтром.
	 *
	 * @param array $out Список услуг в формате, описанном в шапке файла.
	 */
	$out = apply_filters( 'lmf_reel_services', $out );

	// Нормализуем: пустые поля не должны ломать разметку.
	$clean = array();
	foreach ( (array) $out as $s ) {
		if ( empty( $s['title'] ) ) {
			continue;
		}
		$clean[] = array(
			'title' => (string) $s['title'],
			'text'  => isset( $s['text'] ) ? (string) $s['text'] : '',
			'url'   => isset( $s['url'] ) ? (string) $s['url'] : '',
			'image' => isset( $s['image'] ) ? (string) $s['image'] : '',
			'cta'   => ! empty( $s['cta'] ) ? (string) $s['cta'] : __( 'Discover Service', 'lemonmint' ),
		);
	}
	return $clean;
}

/**
 * Разметка секции.
 *
 * Всё содержимое — обычный HTML: названия, описания и ссылки индексируются
 * и читаются скринридером. WebGL рисует только картинку поверх — плёнку,
 * кадры, перфорацию. Без WebGL или при reduce-motion остаётся этот же HTML,
 * свёрстанный как лента контактов (см. services-reel.css, «статичный режим»).
 */
function lmf_services_reel() {
	$services = lmf_reel_services();
	if ( ! $services ) {
		return;
	}
	$total = count( $services );
	?>
	<section class="reel" data-lmf-reel aria-labelledby="reel-title">
		<div class="reel-stage">
			<canvas class="reel-gl" aria-hidden="true"></canvas>

			<!-- центр бобины: видно, пока плёнка целиком в кадре -->
			<div class="reel-hub">
				<?php lmf_kicker( __( 'Services', 'lemonmint' ) ); ?>
				<h2 class="h2" id="reel-title"><?php esc_html_e( 'What we make', 'lemonmint' ); ?></h2>
				<a class="link-u reel-hub__all" href="<?php echo esc_url( get_post_type_archive_link( 'lmf_service' ) ); ?>"><?php esc_html_e( 'All services', 'lemonmint' ); ?></a>
			</div>

			<ol class="reel-list">
				<?php foreach ( $services as $i => $s ) : ?>
					<li class="reel-item" data-img="<?php echo esc_url( $s['image'] ); ?>">
						<figure class="reel-frame">
							<?php if ( $s['image'] ) : ?>
								<img src="<?php echo esc_url( $s['image'] ); ?>" alt="" width="1200" height="800" loading="lazy" decoding="async">
							<?php endif; ?>
						</figure>
						<div class="reel-copy">
							<span class="meta reel-num"><?php echo esc_html( sprintf( '%02d / %02d', $i + 1, $total ) ); ?></span>
							<h3 class="reel-title"><?php echo esc_html( $s['title'] ); ?></h3>
							<?php if ( $s['text'] ) : ?>
								<p class="reel-text"><?php echo esc_html( $s['text'] ); ?></p>
							<?php endif; ?>
							<?php if ( $s['url'] ) : ?>
								<a class="btn btn-lemon reel-cta" href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['cta'] ); ?> <span aria-hidden="true">&rarr;</span></a>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>
	<?php
}
