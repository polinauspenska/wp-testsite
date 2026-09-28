<?php
/**
 * Homepage — built as a printed page: generous margins, one idea per surface,
 * and exactly two accent surfaces (one mint, one lemon).
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

?>

<!-- HERO — первый экран целиком отдан кадру. Заголовок и действия те же,
     что были на бумажном варианте; сменилась только подложка. Шапка над
     этой секцией идёт прозрачной и в реверсе — см. hero-video.css.

     No mark here on purpose. It lives in the header, once: the preloader
     hands it over to that lockup, and repeating it immediately underneath
     would undo the handover and state the identity twice on one screen. -->
<?php
$lmf_video  = lmf_opt( 'hero_video', get_template_directory_uri() . '/assets/video/lmf-hero.mp4' );
$lmf_poster = lmf_opt( 'hero_poster', get_template_directory_uri() . '/assets/video/lmf-hero-poster.jpg' );

/*
 * WebM того же клипа отдаём первым, если он лежит рядом с mp4: VP9 весит
 * меньше, а браузер, который его не понимает, просто возьмёт второй source.
 * Подставленный через кастомайзер файл живёт своей жизнью — тогда WebM не
 * ищем.
 */
$lmf_webm = '';
if ( $lmf_video && preg_match( '/\.mp4$/i', $lmf_video ) ) {
	$lmf_try = preg_replace( '/\.mp4$/i', '.webm', $lmf_video );
	$lmf_rel = str_replace( get_template_directory_uri(), '', $lmf_try );
	if ( $lmf_rel !== $lmf_try && file_exists( get_template_directory() . $lmf_rel ) ) {
		$lmf_webm = $lmf_try;
	}
}
?>
<?php
/*
 * Первый экран собирается по порядку, а не весь сразу: кадр → заголовок →
 * кнопки. Чтобы до этого момента заголовок не стоял готовым (и не исчезал
 * потом ради входа), содержимое прячется ещё до отрисовки — классом на
 * <html>, который ставит эта строка. Без JS класса нет, и экран виден сразу.
 */
?>
<script>document.documentElement.classList.add('hero-wait');</script>
<section class="hero hero-video" data-cursor-tone="light">

	<!-- Подложка. Постер стоит и фоном, и атрибутом видео: если автоплей
	     запрещён (iOS Low Power, экономия трафика, reduce-motion) или файл
	     не дошёл — на экране всё равно кадр, а не чёрная дыра. -->
	<div class="hero-bg" aria-hidden="true" style="background-image:url('<?php echo esc_url( $lmf_poster ); ?>')">
		<?php if ( $lmf_video ) : ?>
		<video
			class="hero-bg__v"
			poster="<?php echo esc_url( $lmf_poster ); ?>"
			autoplay muted loop playsinline disablepictureinpicture
			preload="metadata" tabindex="-1" aria-hidden="true">
			<?php if ( $lmf_webm ) : ?>
			<source src="<?php echo esc_url( $lmf_webm ); ?>" type="video/webm">
			<?php endif; ?>
			<source src="<?php echo esc_url( $lmf_video ); ?>" type="video/mp4">
		</video>
		<?php endif; ?>
	</div>

	<div class="wrap hero-in">
		<h1 class="h1"><?php echo esc_html( lmf_opt( 'hero_line', 'Built for the screen, not the scroll' ) ); ?><span class="h1-dot">.</span></h1>

		<!-- Подзаголовок убран: заголовок говорит сам за себя. Строка сетки
		     осталась одна, поэтому действия выравниваются по левому краю,
		     под заголовком — см. .hero-foot.is-solo в style.css. -->
		<div class="hero-foot is-solo">
			<div style="display:flex;gap:26px;align-items:center;flex-wrap:wrap">
				<a class="btn btn-lemon" href="<?php echo esc_url( home_url( '/contact/?i=production' ) ); ?>"><?php esc_html_e( 'Start a Production', 'lemonmint' ); ?></a>
				<button class="link-u" data-reel><?php esc_html_e( 'Watch the reel', 'lemonmint' ); ?></button>
			</div>
		</div>
	</div>

	<!-- Подсказка «листайте». Экран занят целиком, край страницы не виден,
	     поэтому граница нужна. Убирается одной строкой. -->
	<span class="hero-cue" aria-hidden="true"><b><?php esc_html_e( 'Scroll', 'lemonmint' ); ?></b><i></i></span>
</section>

<!-- SERVICES REEL — вторая секция. Услуги кадрами на бобине: прокрутка
     подводит камеру к кадру и поворачивает плёнку. Список берётся из записей
     «Services», при их отсутствии — из inc/services-reel.php. -->
<?php lmf_services_reel(); ?>

<!-- AFTER THE REEL — the sales argument, in the order a buyer asks:
     what do you do for me → prove it → show me → what do I get → how does
     it work → who says so → let's talk. Content: inc/home.php. -->
<?php lmf_home_promise(); ?>
<?php lmf_home_cases(); ?>

<?php
// The words on the mint band — editable through Lemon Mint → Header, menu & footer.
$lmf_reel = apply_filters(
	'lmf_reel_copy',
	array(
		'kicker'   => __( 'Showreel 2026', 'lemonmint' ),
		'headline' => __( 'Ninety seconds of the last year.', 'lemonmint' ),
		'text'     => __( 'Brand films, commercials, documentary and series — cut from work shot and finished in-house.', 'lemonmint' ),
		'button'   => __( 'Watch the reel', 'lemonmint' ),
	)
);
?>
<!-- ACCENT SURFACE 1 of 2 — mint, with the reversed lockup. Never on black. -->
<section class="surface-mint" data-cursor-tone="light">
	<div class="wrap" style="padding-top:clamp(48px,7vw,110px);padding-bottom:clamp(48px,7vw,110px)">
		<div class="split">
			<div><?php lmf_lockup( true ); ?></div>
			<div>
				<?php lmf_kicker( $lmf_reel['kicker'] ); ?>
				<h2 class="h2" style="max-width:15ch"><?php echo esc_html( $lmf_reel['headline'] ); ?></h2>
				<p class="body-lg" style="margin-top:20px"><?php echo esc_html( $lmf_reel['text'] ); ?></p>
				<div style="margin-top:30px;display:flex;gap:26px;align-items:center;flex-wrap:wrap">
					<button class="btn btn-rev" data-reel>&#9654;&nbsp;&nbsp;<?php echo esc_html( $lmf_reel['button'] ); ?></button>
					<span class="meta" style="color:rgba(247,245,239,.72)">LMF / REEL · 01:30</span>
				</div>
			</div>
		</div>
	</div>
</section>

<?php lmf_home_process(); ?>
<?php lmf_home_voices(); ?>

<?php lmf_closing_cta(); ?>
<?php get_footer(); ?>
