/* ==========================================================================
   CLOSING CALL — the film leader
   While the section is on screen the dial counts 3 · 2 · 1, a sweep going
   round once a second like an academy leader, then shows ACTION and starts
   again. Hover (or focus) one of the three ways in and the leader jumps to
   its number and keeps sweeping on it. Reduced motion: it rests on 1.
   ========================================================================== */
( function () {
	'use strict';
	var sec = document.querySelector( '[data-cta]' );
	if ( ! sec ) { return; }
	var dial = sec.querySelector( '.cta-dial' );
	var num  = sec.querySelector( '.cta-num' );
	var cap  = sec.querySelector( '.cta-cap-r' );
	var rows = [].slice.call( sec.querySelectorAll( '.cta-r' ) );
	if ( ! dial || ! num ) { return; }
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }

	var labels = { '3': 'Request a quote', '2': 'Book a call', '1': 'Roll camera' };
	rows.forEach( function ( r ) {
		var t = r.querySelector( '.cta-do' );
		if ( t ) { labels[ r.getAttribute( 'data-n' ) ] = t.textContent; }
	} );
	var hold = null, visible = false, raf = 0, t0 = 0, shown = '';

	function set( txt ) {
		if ( txt === shown ) { return; }
		shown = txt;
		num.textContent = txt;
		num.classList.toggle( 'is-word', txt.length > 1 );
		num.style.transform = 'scale(1.08)';
		requestAnimationFrame( function () { num.style.transform = ''; } );
		rows.forEach( function ( r ) { r.classList.toggle( 'is-on', r.getAttribute( 'data-n' ) === txt ); } );
		if ( cap ) { cap.textContent = labels[ txt ] || 'Roll camera'; }
	}

	function tick( t ) {
		raf = 0;
		if ( ! visible ) { return; }
		if ( ! t0 ) { t0 = t; }
		var s = ( t - t0 ) / 1000;
		if ( hold ) {
			set( hold );
			dial.style.setProperty( '--a', ( s % 1 ).toFixed( 3 ) );
		} else {
			var c = s % 4.6;                         // 3 · 2 · 1 · ACTION
			if ( c < 3 ) {
				set( String( 3 - Math.floor( c ) ) );
				dial.style.setProperty( '--a', ( c % 1 ).toFixed( 3 ) );
			} else {
				set( 'ACTION' );
				dial.style.setProperty( '--a', '0' );
			}
		}
		raf = requestAnimationFrame( tick );
	}
	function wake() { if ( ! raf && visible ) { raf = requestAnimationFrame( tick ); } }

	rows.forEach( function ( r ) {
		var on  = function () { hold = r.getAttribute( 'data-n' ); t0 = 0; };
		var off = function () { hold = null; t0 = 0; };
		r.addEventListener( 'mouseenter', on );
		r.addEventListener( 'focus', on );
		r.addEventListener( 'mouseleave', off );
		r.addEventListener( 'blur', off );
	} );
	if ( 'IntersectionObserver' in window ) {
		new IntersectionObserver( function ( en ) { visible = en[ 0 ].isIntersecting; if ( visible ) { t0 = 0; wake(); } } ).observe( sec );
	} else { visible = true; wake(); }
} )();
