/* ==========================================================================
   HERO · FILM EDGE + REGISTRATION
   --------------------------------------------------------------------------
   Делает ровно две вещи и ничего больше:

     1. вставляет полосу перфорации в левое поле секции (абсолютом — поток
        не трогается, ни один элемент не сдвигается);
     2. разбивает заголовок на строки и запускает вход «сведение красок».

   Разметку секции не переписывает: заголовок восстанавливается из исходного
   HTML при каждом пересчёте, поэтому правка текста в WordPress работает как
   обычно.

   Запуск ждёт прелоадер: начинается, когда он поднимает занавес. Порядок
   экрана — кадр с первого плана → заголовок построчно → кнопки → полоса.
   ========================================================================== */
( function () {
	'use strict';

	var hero = document.querySelector( '.hero' );
	if ( ! hero ) { return; }

	var h1 = hero.querySelector( '.h1' );
	var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var started = false;

	/* Полоса живёт в той же колонке, что и набор: когда секция отдана кадру
	   на всю ширину, поля --pad держит внутренний .wrap, а не сама секция.
	   Ставим полосу туда же — иначе на экранах шире 1560px она отъедет от
	   текста на половину лишнего поля. */
	var mount = hero.querySelector( '.hero-in' ) || hero;
	if ( mount !== hero ) { mount.style.position = 'relative'; }

	/* ----------------------------------------------------------------------
	   1. Полоса перфорации
	   ---------------------------------------------------------------------- */
	var edgeEl = null;

	// The perforation strip beside the headline has been taken off the hero
	// at the client's request; flip SHOW_EDGE back to true to restore it.
	var SHOW_EDGE = false;

	function buildEdge() {
		if ( ! SHOW_EDGE || edgeEl ) { return; }
		edgeEl = document.createElement( 'div' );
		edgeEl.className = 'film-edge';
		edgeEl.setAttribute( 'aria-hidden', 'true' );   // декор, не контент
		edgeEl.innerHTML =
			'<span class="film-edge__tape"></span>' +
			'<span class="film-edge__flicker"></span>';
		mount.appendChild( edgeEl );
		fitEdge();
	}

	/* Полоса стоит ровно напротив заголовка, а не во всю секцию.
	   Высота h1 зависит от ширины экрана и от того, загрузился ли Anton,
	   поэтому меряем её по факту и пересчитываем на ресайзе и после
	   готовности шрифтов — иначе полоса встанет не по набору. */
	function fitEdge() {
		if ( ! edgeEl || ! h1 ) { return; }
		var hb = mount.getBoundingClientRect();
		var tb = h1.getBoundingClientRect();
		edgeEl.style.setProperty( '--edge-top', ( tb.top - hb.top ).toFixed( 1 ) + 'px' );
		edgeEl.style.setProperty( '--edge-height', tb.height.toFixed( 1 ) + 'px' );
	}

	/* Шрифт меняет и высоту заголовка, и то, где он переносится. Пришёл
	   Anton — пересобираем строки заново и ставим полосу по новой высоте.
	   Анимацию не переигрываем: она к этому моменту уже идёт или прошла. */
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( function () {
			if ( started ) { layout(); }
			fitEdge();
		} );
	}

	/* ----------------------------------------------------------------------
	   2. Заголовок построчно

	   Режем по фактическим строкам, а не по словам: перенос зависит от
	   ширины экрана и от шрифта, поэтому строки считаем по offsetTop уже
	   отрисованных слов. Разделитель перед словом запоминаем — мятная точка
	   стоит вплотную к «scroll», и склейка через пробел её отрывает.
	   ---------------------------------------------------------------------- */
	var originalHTML = h1 ? h1.innerHTML : '';

	function splitLines() {
		if ( ! h1 ) { return 0; }
		h1.innerHTML = originalHTML;
		var words = [];

		( function walk( el ) {
			Array.prototype.slice.call( el.childNodes ).forEach( function ( n ) {
				if ( n.nodeType === 3 ) {
					var parts = n.textContent.split( /(\s+)/ );
					var frag = document.createDocumentFragment();
					var pending = '';
					parts.forEach( function ( part ) {
						if ( ! part ) { return; }
						if ( /^\s+$/.test( part ) ) {
							pending = ' ';
							frag.appendChild( document.createTextNode( part ) );
							return;
						}
						var w = document.createElement( 'span' );
						w.textContent = part;
						frag.appendChild( w );
						words.push( { el: w, sep: pending } );
						pending = '';
					} );
					el.replaceChild( frag, n );
				} else if ( n.nodeType === 1 ) {
					if ( n.childNodes.length && n.textContent.trim() && n.children.length ) {
						walk( n );
					} else {
						var prev = n.previousSibling;
						words.push( {
							el: n,
							sep: ( prev && prev.nodeType === 3 && /\s$/.test( prev.textContent ) ) ? ' ' : ''
						} );
					}
				}
			} );
		} )( h1 );

		var lines = [], last = null;
		words.forEach( function ( w ) {
			var top = w.el.offsetTop;
			if ( last === null || Math.abs( top - last ) > 4 ) { lines.push( [] ); last = top; }
			lines[ lines.length - 1 ].push( w );
		} );

		var out = document.createDocumentFragment();
		lines.forEach( function ( ws ) {
			var line = document.createElement( 'span' );
			line.className = 'fl';
			var inner = document.createElement( 'i' );
			ws.forEach( function ( w, i ) {
				if ( i && w.sep ) { inner.appendChild( document.createTextNode( w.sep ) ); }
				inner.appendChild( w.el );
			} );
			line.appendChild( inner );
			out.appendChild( line );
		} );

		h1.innerHTML = '';
		h1.appendChild( out );
		return lines.length;
	}

	/* Задержки ставим после каждой пересборки строк: строк могло стать
	   больше или меньше, и старые значения на новые строки не лягут. */
	function stagger() {
		h1.querySelectorAll( '.fl > i' ).forEach( function ( el, i ) {
			el.style.setProperty( '--fd', ( 0.12 + i * 0.13 ).toFixed( 2 ) + 's' );
		} );
	}

	/* Разбивка строк и вход — разные вещи. is-film ставится один раз и
	   больше не снимается: это раскладка. Анимацию несёт is-enter. */
	function layout() {
		splitLines();
		stagger();
		hero.classList.add( 'is-film' );
	}

	var enterTimer;

	function play() {
		clearTimeout( enterTimer );
		hero.classList.remove( 'is-enter' );
		void hero.offsetWidth;                       // сброс, чтобы анимация пошла заново
		var n = h1 ? h1.querySelectorAll( '.fl' ).length : 0;
		layout();

		/* Кадр начинается с начала — вместе с заголовком. Под прелоадером
		   клип уже шёл, и занавес поднимался посреди чужого плана; теперь
		   первым планом открывается экран, а не случайным. */
		var vid = hero.querySelector( 'video' );
		if ( vid && ! reduced ) {
			try { vid.currentTime = 0; } catch ( e ) {}
			var pr = vid.play();
			if ( pr && typeof pr.catch === 'function' ) { pr.catch( function () {} ); }
		}

		document.documentElement.classList.remove( 'hero-wait' );
		if ( reduced ) { return; }

		/* кнопки встают сразу за последней строкой, полоса — за кнопками */
		var count = h1.querySelectorAll( '.fl' ).length || n || 1;
		var lastLine = 0.12 + ( count - 1 ) * 0.13;
		hero.style.setProperty( '--foot-delay', ( lastLine + 0.42 ).toFixed( 2 ) + 's' );
		hero.style.setProperty( '--edge-delay', ( lastLine + 0.8 ).toFixed( 2 ) + 's' );
		hero.classList.add( 'is-enter' );

		/* Отыграв, вход себя снимает. Это не косметика: строки пересобираются
		   на каждом ресайзе, и если бы класс остался, каждая новая строка
		   заводила бы анимацию заново — заголовок мигал бы при любом повороте
		   экрана или перетаскивании окна. Конечное состояние анимации и так
		   совпадает с обычным, поэтому снятие класса ничего не меняет на вид. */
		/* снимаем, когда доиграло последнее — полоса и подсказка */
		enterTimer = setTimeout( function () {
			hero.classList.remove( 'is-enter' );
		}, ( lastLine + 0.8 + 0.9 ) * 1000 + 150 );
	}

	function start() {
		if ( started ) { return; }
		started = true;
		buildEdge();

		/* Строки режутся по фактическому переносу, а перенос зависит от
		   гарнитуры: посчитать их на подстановочном Arial и оставить так —
		   значит получить чужую разбивку, когда приедет Anton. Ждём шрифты,
		   но не бесконечно: если они почему-то не пришли, играем как есть и
		   пересчитаем позже, в обработчике fonts.ready ниже. */
		var fired = false;
		function go() {
			if ( fired ) { return; }
			fired = true;
			play();
			fitEdge();            // после разбивки на строки высота h1 итоговая
		}

		if ( document.fonts && document.fonts.ready && 'loaded' !== document.fonts.status ) {
			document.fonts.ready.then( go );
			setTimeout( go, 1200 );
		} else {
			go();
		}
	}

	/* строки зависят от ширины — пересобираем разбивку, но заново не играем:
	   повтор анимации на ресайзе выглядит как сбой, а не как приём */
	var t;
	window.addEventListener( 'resize', function () {
		if ( ! started ) { return; }
		clearTimeout( t );
		t = setTimeout( function () {
			layout();             // is-film остаётся: строки должны остаться строками
			fitEdge();            // строк стало больше или меньше — полоса следом
		}, 200 );
	} );

	/* ----------------------------------------------------------------------
	   3. Момент запуска
	   ---------------------------------------------------------------------- */
	var stage = document.getElementById( 'lmf-stage' );
	if ( ! stage ) { start(); return; }

	/* Старт — момент, когда прелоадер поднимает занавес (он ставит на <html>
	   класс lmf-ready), а не когда логотип уже сел в шапку. Иначе целую
	   секунду полёта экран стоял пустым кадром, и заголовок появлялся
	   отдельно, после. Теперь марка летит наверх, а заголовок в это время
	   сводится внизу — одно движение, а не два. Короткая пауза — чтобы
	   оверлей успел заметно поредеть. */
	var root = document.documentElement;
	function onReady() { setTimeout( start, 260 ); }
	if ( root.classList.contains( 'lmf-ready' ) ) {
		onReady();
	} else if ( window.MutationObserver ) {
		var ro = new MutationObserver( function () {
			if ( root.classList.contains( 'lmf-ready' ) ) { ro.disconnect(); onReady(); }
		} );
		ro.observe( root, { attributes: true, attributeFilter: [ 'class' ] } );
	}

	root.addEventListener( 'lmf:preloader-done', start, { once: true } );

	/* На повторном визите прелоадер не играет: снимает оверлей сразу и события
	   не шлёт. Без этого секция дождалась бы страховки и переанимировалась
	   через несколько секунд — уже на глазах, что хуже, чем не анимироваться. */
	if ( window.MutationObserver ) {
		var mo = new MutationObserver( function () {
			if ( ! document.getElementById( 'lmf-stage' ) ) { mo.disconnect(); start(); }
		} );
		mo.observe( document.body, { childList: true, subtree: true } );
	}

	/* последняя страховка — если прелоадер завис и не ушёл сам */
	setTimeout( start, 14000 );
} )();
