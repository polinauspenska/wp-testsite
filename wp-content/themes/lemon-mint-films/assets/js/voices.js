/* ==========================================================================
   VOICES — clients in their words, as subtitles on their own film
   Pinned. Per scene: the subtitle comes in word by word as you scroll →
   it holds → the film pulls down to the next frame through the gate, the
   perforations running with it and the timecode ticking. Scrubbed by
   scroll (eased), so it plays backwards too.
   ========================================================================== */
( function () {
	'use strict';

	var sec = document.querySelector( '[data-tv]' );
	if ( ! sec ) { return; }
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }

	var frames = [].slice.call( sec.querySelectorAll( '.tv-frame' ) );
	var N = frames.length;
	if ( ! N ) { return; }

	var STEP = 1.0, HOLD = 0.25;
	var T = ( N - 1 ) * STEP + 0.62 + HOLD;       // the last scene doesn't pull down
	sec.style.setProperty( '--tv-len', T.toFixed( 3 ) );
	sec.classList.add( 'is-live' );

	var track = sec.querySelector( '.tv-track' );
	var gate  = sec.querySelector( '.tv-gate' );
	var strip = sec.querySelector( '.tv-strip' );
	var perfs = [].slice.call( sec.querySelectorAll( '.tv-perf' ) );
	var no    = sec.querySelector( '.tv-no' );
	var crs   = [].slice.call( sec.querySelectorAll( '.tv-cr' ) );
	var bars  = [].slice.call( sec.querySelectorAll( '.tv-bars i' ) );
	var tc    = sec.querySelector( '.tv-tc' );
	var scenes = frames.map( function ( f ) {
		return { el: f, words: [].slice.call( f.querySelectorAll( '.tw' ) ), lit: -1 };
	} );

	var clamp = function ( v ) { return v < 0 ? 0 : v > 1 ? 1 : v; };
	var span  = function ( p, a, b ) { return clamp( ( p - a ) / ( b - a ) ); };
	var ease  = function ( t ) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow( -2 * t + 2, 3 ) / 2; };
	var pad   = function ( n ) { return ( n < 10 ? '0' : '' ) + n; };

	var shown = -1;
	function draw( P ) {
		var gh = gate.clientHeight, gap = parseFloat( getComputedStyle( strip ).rowGap ) || 12;
		var pos = 0;
		for ( var i = 0; i < N; i++ ) {
			var t = P - i * STEP;
			var s = scenes[ i ];
			var n = Math.round( span( t, 0.02, 0.55 ) * s.words.length );
			if ( n !== s.lit ) {
				s.lit = n;
				for ( var w = 0; w < s.words.length; w++ ) { s.words[ w ].classList.toggle( 'is-lit', w < n ); }
			}
			if ( bars[ i ] ) { bars[ i ].style.setProperty( '--sp', span( t, 0, i < N - 1 ? 1 : 0.62 ).toFixed( 3 ) ); }
			if ( i < N - 1 ) { pos += ease( span( t, 0.78, 1 ) ); }
		}
		var off = pos * ( gh + gap );
		strip.style.transform = 'translate3d(0,' + ( -off ).toFixed( 1 ) + 'px,0)';
		perfs.forEach( function ( p ) { p.style.setProperty( '--off', ( -off ).toFixed( 1 ) + 'px' ); } );

		var on = Math.min( N - 1, Math.round( pos ) );
		if ( on !== shown ) {
			shown = on;
			if ( no ) { no.textContent = pad( on + 1 ); }
			frames.forEach( function ( f, j ) { f.classList.toggle( 'is-on', j === on ); } );
			crs.forEach( function ( c, j ) {
				c.classList.toggle( 'is-on', j === on );
				var a = c.querySelector( 'a' );
				if ( a ) { a.tabIndex = j === on ? 0 : -1; }
			} );
		}
		if ( tc ) {
			var f = Math.floor( P * 9 * 25 );                 // ~9 seconds of film a screen, 25 fps
			tc.textContent = 'TC 01:' + pad( Math.floor( f / 1500 ) % 60 ) + ':' + pad( Math.floor( f / 25 ) % 60 ) + ':' + pad( f % 25 );
		}
	}

	var target = 0, P = -1, raf = 0;
	function measure() {
		var r = track.getBoundingClientRect();
		var dist = track.offsetHeight - window.innerHeight;
		target = dist > 0 ? Math.max( 0, Math.min( T, -r.top / window.innerHeight ) ) : 0;
	}
	function loop() {
		raf = 0;
		measure();
		if ( P < 0 ) { P = target; }
		var d = target - P;
		P = Math.abs( d ) < 0.0005 ? target : P + d * 0.15;
		draw( P );
		if ( P !== target ) { raf = requestAnimationFrame( loop ); }
	}
	function wake() { if ( ! raf ) { raf = requestAnimationFrame( loop ); } }
	window.addEventListener( 'scroll', wake, { passive: true } );
	window.addEventListener( 'resize', wake );
	if ( document.fonts && document.fonts.ready ) { document.fonts.ready.then( wake ); }
	wake();
} )();
