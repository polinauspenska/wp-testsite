/* ==========================================================================
   HERO · КАДР НА ВЕСЬ ЭКРАН — поведение
   --------------------------------------------------------------------------
   Три вещи:

     1. включает реверс шапки после того, как прелоадер отдал марку —
        иначе логотип сменил бы краску прямо в момент посадки;
     2. переключает шапку между «на кадре» и «своей обычной», когда первый
        экран уезжает;
     3. следит за видео: при reduce-motion и при отказе автоплея оставляет
        постер, а не чёрный прямоугольник.

   Раскладку не трогает: только классы на <body>.
   ========================================================================== */
( function () {
	'use strict';

	var body = document.body;
	if ( ! body || ! body.classList.contains( 'has-video-hero' ) ) { return; }

	var hero = document.querySelector( '.hero-video' );
	if ( ! hero ) { return; }

	var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ----------------------------------------------------------------------
	   1. Видео
	   ---------------------------------------------------------------------- */
	var vid = hero.querySelector( '.hero-bg__v' );

	if ( vid ) {
		if ( reduced ) {
			/* Движение — украшение. Постер уже стоит фоном .hero-bg,
			   поэтому достаточно убрать кадр с экрана. */
			vid.removeAttribute( 'autoplay' );
			vid.pause();
			vid.style.display = 'none';
		} else {
			/* Safari на «Экономии энергии» и часть мобильных браузеров
			   отказывают в автоплее молча: обещание падает, видео стоит на
			   первом кадре. Постер под ним уже есть — просто снимаем
			   пустой чёрный слой. */
			var playing = vid.play();
			if ( playing && typeof playing.catch === 'function' ) {
				playing.catch( function () { vid.style.display = 'none'; } );
			}
			vid.addEventListener( 'error', function () { vid.style.display = 'none'; } );

			/* Кадр за экраном не должен крутиться впустую. */
			if ( window.IntersectionObserver ) {
				new IntersectionObserver( function ( es ) {
					es.forEach( function ( e ) {
						if ( e.isIntersecting ) {
							var p = vid.play();
							if ( p && typeof p.catch === 'function' ) { p.catch( function () {} ); }
						} else {
							vid.pause();
						}
					} );
				}, { threshold: 0.01 } ).observe( hero );
			}
		}
	}

	/* ----------------------------------------------------------------------
	   2. Шапка: на кадре / своя обычная
	   Порог — низ первого экрана минус высота шапки: краска меняется ровно
	   тогда, когда шапка сходит с кадра на бумагу.
	   ---------------------------------------------------------------------- */
	var navH = parseFloat(
		getComputedStyle( document.documentElement ).getPropertyValue( '--nav-h' )
	) || 72;

	var ticking = false;

	function syncNav() {
		ticking = false;
		var passed = window.scrollY > ( hero.offsetHeight - navH - 2 );
		body.classList.toggle( 'nav-solid', passed );
	}

	window.addEventListener( 'scroll', function () {
		if ( ticking ) { return; }
		ticking = true;
		window.requestAnimationFrame( syncNav );
	}, { passive: true } );

	window.addEventListener( 'resize', syncNav );
	syncNav();

	/* ----------------------------------------------------------------------
	   3. Реверс логотипа — после прелоадера
	   Та же логика ожидания, что и у hero-film.js: событие, наблюдатель за
	   снятием оверлея (на повторном визите прелоадер уходит молча) и
	   страховка по времени.
	   ---------------------------------------------------------------------- */
	var lit = false;

	function light() {
		if ( lit ) { return; }
		lit = true;
		body.classList.add( 'hero-live' );
	}

	var stage = document.getElementById( 'lmf-stage' );
	if ( ! stage ) { light(); return; }

	document.documentElement.addEventListener( 'lmf:preloader-done', light, { once: true } );

	if ( window.MutationObserver ) {
		var mo = new MutationObserver( function () {
			if ( ! document.getElementById( 'lmf-stage' ) ) { mo.disconnect(); light(); }
		} );
		mo.observe( body, { childList: true, subtree: true } );
	}

	setTimeout( light, 14000 );
} )();
