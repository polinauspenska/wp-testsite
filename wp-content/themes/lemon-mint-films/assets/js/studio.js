/* ==========================================================================
   THE STUDIO — focus pull, lit index, frame counter, cursor frame
   No library. Nothing is hidden without this script.
   ========================================================================== */
( function () {
	'use strict';

	var sec = document.querySelector( '[data-lmf-studio]' );
	if ( ! sec ) { return; }

	var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var words   = [].slice.call( sec.querySelectorAll( '.studio-title .sw' ) );
	var rows    = [].slice.call( sec.querySelectorAll( '.studio-row' ) );
	var nums    = [].slice.call( sec.querySelectorAll( '.studio-num' ) );
	var title   = sec.querySelector( '.studio-title' );

	sec.classList.add( 'is-live' );

	function clamp01( x ) { return x < 0 ? 0 : x > 1 ? 1 : x; }

	/* ---------------------------------------------------------------------
	   1. Focus pull — scroll-driven, word by word
	   The headline is fully sharp by the time it reaches the upper third of
	   the screen. While soft, each word carries a lemon and a mint ghost,
	   as an out-of-register print would.
	   --------------------------------------------------------------------- */
	var lastP = -1;
	function focus() {
		if ( reduced || ! title ) { return; }
		var r = title.getBoundingClientRect();
		var vh = window.innerHeight;
		// 0 when the headline's top enters at 95 % of the screen, 1 at 40 %
		var p = clamp01( ( vh * 0.95 - r.top ) / ( vh * 0.55 ) );
		if ( Math.abs( p - lastP ) < 0.002 ) { return; }
		lastP = p;
		var n = words.length, span = 5;
		words.forEach( function ( w, i ) {
			// q: 1 = fully soft, 0 = sharp; each word takes a fifth of the pull,
			// overlapping its neighbours, so the focus travels along the line
			var q = 1 - clamp01( ( p * ( n + span ) - i ) / span );
			var blur = ( q * 10 ).toFixed( 2 );
			var off  = ( q * 6 ).toFixed( 1 );
			w.style.filter = q > 0.01 ? 'blur(' + blur + 'px)' : 'none';
			w.style.opacity = ( 1 - q * 0.7 ).toFixed( 3 );
			w.style.textShadow = q > 0.01
				? ( '-' + off + 'px 0 0 rgba(245,184,46,' + ( q * 0.8 ).toFixed( 2 ) + '), ' + off + 'px 0 0 rgba(23,128,92,' + ( q * 0.8 ).toFixed( 2 ) + ')' )
				: 'none';
		} );
	}

	/* ---------------------------------------------------------------------
	   2. Lit index — the row nearest the middle of the screen
	   --------------------------------------------------------------------- */
	var lit = -1;
	function light() {
		var mid = window.innerHeight * 0.5, best = -1, bestD = 1e9;
		rows.forEach( function ( row, i ) {
			var r = row.getBoundingClientRect();
			var d = Math.abs( r.top + r.height / 2 - mid );
			if ( d < bestD ) { bestD = d; best = i; }
		} );
		// nothing lit until the list is actually in view
		var lr = rows.length ? rows[ 0 ].parentNode.getBoundingClientRect() : null;
		if ( lr && ( lr.bottom < 0 || lr.top > window.innerHeight ) ) { best = -1; }
		if ( hoverRow > -1 ) { best = hoverRow; }
		if ( best === lit ) { return; }
		lit = best;
		rows.forEach( function ( row, i ) { row.classList.toggle( 'is-lit', i === best ); } );
	}

	/* ---------------------------------------------------------------------
	   3. Frame counter — digits roll up once, when the figures come in
	   --------------------------------------------------------------------- */
	function buildCounters() {
		nums.forEach( function ( dd ) {
			var el = dd.querySelector( '.studio-digits' );
			var v = ( dd.getAttribute( 'data-value' ) || '' ).split( '' );
			el.textContent = '';
			v.forEach( function ( ch, i ) {
				if ( ! /\d/.test( ch ) ) { el.appendChild( document.createTextNode( ch ) ); return; }
				var col = document.createElement( 'span' );
				col.className = 'studio-digit';
				var strip = document.createElement( 'span' );
				// two full turns, then land: reads as a mechanical counter
				var html = '';
				for ( var k = 0; k < 20; k++ ) { html += '<b>' + ( k % 10 ) + '</b>'; }
				html += '<b>' + ch + '</b>';
				strip.innerHTML = html;
				strip.style.setProperty( '--dd', ( i * 0.09 ).toFixed( 2 ) + 's' );
				strip.setAttribute( 'data-land', 20 );
				col.appendChild( strip );
				col.setAttribute( 'aria-hidden', 'true' );
				el.appendChild( col );
			} );
			// the real figure stays readable for screen readers
			var sr = document.createElement( 'span' );
			sr.className = 'screen-reader-text';
			sr.textContent = dd.getAttribute( 'data-value' );
			el.appendChild( sr );
		} );
	}
	function rollCounters() {
		sec.querySelectorAll( '.studio-digit > span' ).forEach( function ( s ) {
			s.style.transform = 'translateY(-' + s.getAttribute( 'data-land' ) + 'em)';
		} );
	}
	if ( ! reduced && nums.length ) {
		buildCounters();
		var figs = sec.querySelector( '.studio-figures' );
		if ( window.IntersectionObserver && figs ) {
			var io = new IntersectionObserver( function ( es ) {
				if ( es[ 0 ].isIntersecting ) { io.disconnect(); requestAnimationFrame( rollCounters ); }
			}, { threshold: 0.4 } );
			io.observe( figs );
		} else {
			rollCounters();
		}
	}

	/* ---------------------------------------------------------------------
	   4. Cursor frame — desktop only
	   A small perforated frame trails the cursor over the list, showing the
	   row's still; it leans a little with the speed of the hand.
	   --------------------------------------------------------------------- */
	var loupe = sec.querySelector( '.studio-loupe' );
	var hoverRow = -1;
	if ( loupe && window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches && ! reduced ) {
		var limg = loupe.querySelector( 'img' );
		var mx = 0, my = 0, lx = 0, ly = 0, vx = 0, raf = 0, on = false;
		var cache = {};
		rows.forEach( function ( row, i ) {
			var src = row.getAttribute( 'data-img' );
			if ( src ) { var im = new Image(); im.src = src; cache[ i ] = src; }
			row.addEventListener( 'mouseenter', function () {
				hoverRow = i;
				if ( cache[ i ] ) { limg.src = cache[ i ]; }
				on = true;
				loupe.classList.add( 'is-on' );
				light();
				if ( ! raf ) { raf = requestAnimationFrame( follow ); }
			} );
			row.addEventListener( 'mouseleave', function () {
				hoverRow = -1;
				on = false;
				loupe.classList.remove( 'is-on' );
				light();
			} );
		} );
		sec.addEventListener( 'mousemove', function ( e ) {
			mx = e.clientX; my = e.clientY;
			if ( ! lx && ! ly ) { lx = mx; ly = my; }
		}, { passive: true } );

		var follow = function () {
			var nx = lx + ( mx - lx ) * 0.16;
			var ny = ly + ( my - ly ) * 0.16;
			vx += ( ( nx - lx ) - vx ) * 0.2;
			lx = nx; ly = ny;
			var w = loupe.offsetWidth, h = loupe.offsetHeight;
			var rot = Math.max( -8, Math.min( 8, vx * 0.35 ) );
			// sits to the right of the cursor, flips left near the edge
			var x = lx + 28;
			if ( x + w > window.innerWidth - 16 ) { x = lx - w - 28; }
			loupe.style.transform = 'translate3d(' + x.toFixed( 1 ) + 'px,' + ( ly - h / 2 ).toFixed( 1 ) + 'px,0) rotate(' + rot.toFixed( 2 ) + 'deg)';
			if ( on || Math.abs( vx ) > 0.05 ) { raf = requestAnimationFrame( follow ); } else { raf = 0; }
		};
	}

	/* ---------------------------------------------------------------------
	   Wiring
	   --------------------------------------------------------------------- */
	var ticking = false;
	function onScroll() {
		if ( ticking ) { return; }
		ticking = true;
		requestAnimationFrame( function () { ticking = false; focus(); light(); } );
	}
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll );
	if ( document.fonts && document.fonts.ready ) { document.fonts.ready.then( onScroll ); }
	onScroll();
} )();
