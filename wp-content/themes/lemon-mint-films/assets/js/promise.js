/* ==========================================================================
   PROMISE — the pinned opening after the reel
   The line lights word by word (each word lifts out of a blur) and its last
   words are struck with a lemon marker → the line steps back and blurs away
   → three figures roll in like camera frame counters, one after another,
   each drawing its mint rule → the sentence that explains them.
   All of it is scrubbed by scroll (eased), so it runs backwards too.
   ========================================================================== */
( function () {
	'use strict';

	var sec = document.querySelector( '[data-pm]' );
	if ( ! sec ) { return; }
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }

	var LEN = 4.2; // screens of scroll the stage stays pinned for (+1)
	sec.style.setProperty( '--pm-len', LEN );
	sec.classList.add( 'is-live' );

	var track = sec.querySelector( '.pm-track' );
	var A     = sec.querySelector( '.pm-a' );
	var B     = sec.querySelector( '.pm-b' );
	var words = [].slice.call( sec.querySelectorAll( '.pm-lit .hw' ) );
	var mark  = sec.querySelector( '.hw-mark' );
	var text  = sec.querySelector( '.pm-text' );
	var figs  = [].slice.call( sec.querySelectorAll( '.pm-fig' ) ).map( function ( f ) {
		var odo = f.querySelector( '.pm-odo' );
		return {
			el: f,
			value: odo ? +odo.getAttribute( 'data-value' ) : 0,
			cols: odo ? [].slice.call( odo.querySelectorAll( '.pm-col' ) ).reverse() : []   // units first
		};
	} );

	var clamp = function ( v ) { return v < 0 ? 0 : v > 1 ? 1 : v; };
	var span  = function ( p, a, b ) { return clamp( ( p - a ) / ( b - a ) ); };
	var ease  = function ( t ) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow( -2 * t + 2, 3 ) / 2; };
	var out   = function ( t ) { return 1 - Math.pow( 1 - t, 3 ); };

	// a counter showing v (fractional): the units column turns freely, every
	// higher column only turns while the one below it passes 9 → 0
	function roll( f, v ) {
		for ( var k = 0; k < f.cols.length; k++ ) {
			var pk = Math.pow( 10, k ), pos;
			if ( k === 0 ) {
				pos = v % 10;
			} else {
				var base = Math.floor( v / pk );
				var rem  = v - base * pk;
				pos = ( base % 10 ) + Math.max( 0, rem - ( pk - 1 ) );
			}
			f.cols[ k ].style.setProperty( '--p', pos.toFixed( 3 ) );
		}
	}

	var lit = -1;
	function draw( p ) {
		// 1. the line, word by word
		var n = Math.round( span( p, 0.0, 0.3 ) * words.length );
		if ( n !== lit ) {
			lit = n;
			for ( var i = 0; i < words.length; i++ ) { words[ i ].classList.toggle( 'is-lit', i < n ); }
		}
		if ( mark ) { mark.style.setProperty( '--mk', ease( span( p, 0.3, 0.4 ) ).toFixed( 3 ) ); }

		// 2. it steps back and blurs away
		var o = ease( span( p, 0.44, 0.58 ) );
		A.style.opacity = ( 1 - o ).toFixed( 3 );
		A.style.transform = 'translate3d(0,' + ( -6 * o ).toFixed( 2 ) + 'vh,0) scale(' + ( 1 - 0.1 * o ).toFixed( 4 ) + ')';
		A.style.filter = o > 0.001 ? 'blur(' + ( 10 * o ).toFixed( 1 ) + 'px)' : '';
		A.style.visibility = o > 0.999 ? 'hidden' : '';

		// 3. the figures roll in
		var b = out( span( p, 0.5, 0.62 ) );
		B.style.opacity = b.toFixed( 3 );
		B.style.transform = 'translate3d(0,' + ( 6 * ( 1 - b ) ).toFixed( 2 ) + 'vh,0)';
		B.style.visibility = b < 0.001 ? 'hidden' : '';
		figs.forEach( function ( f, j ) {
			f.el.style.setProperty( '--fa', out( span( p, 0.54 + j * 0.06, 0.64 + j * 0.06 ) ).toFixed( 3 ) );
			var t = ease( span( p, 0.56 + j * 0.06, 0.76 + j * 0.06 ) );
			f.el.style.setProperty( '--r', t.toFixed( 3 ) );
			roll( f, f.value * t );
		} );
		if ( text ) {
			var x = out( span( p, 0.84, 0.92 ) );
			text.style.opacity = x.toFixed( 3 );
			text.style.transform = 'translate3d(0,' + ( 14 * ( 1 - x ) ).toFixed( 1 ) + 'px,0)';
		}
	}

	// scroll sets a target; the stage eases toward it
	var target = 0, P = -1, raf = 0, visible = true;
	function measure() {
		var r = track.getBoundingClientRect();
		var dist = track.offsetHeight - window.innerHeight;
		target = dist > 0 ? clamp( -r.top / dist ) : 0;
	}
	function loop() {
		raf = 0;
		measure();
		if ( P < 0 ) { P = target; }
		var d = target - P;
		P = Math.abs( d ) < 0.0004 ? target : P + d * 0.16;
		draw( P );
		if ( P !== target && visible ) { raf = requestAnimationFrame( loop ); }
	}
	function wake() { if ( ! raf ) { raf = requestAnimationFrame( loop ); } }

	if ( 'IntersectionObserver' in window ) {
		new IntersectionObserver( function ( en ) { visible = en[ 0 ].isIntersecting; if ( visible ) { wake(); } } ).observe( sec );
	}
	window.addEventListener( 'scroll', wake, { passive: true } );
	window.addEventListener( 'resize', wake );
	if ( document.fonts && document.fonts.ready ) { document.fonts.ready.then( wake ); }
	wake();
} )();
