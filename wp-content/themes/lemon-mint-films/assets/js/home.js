/* ==========================================================================
   HOMEPAGE — after the reel
   The timeline fills.
   (The opening line and the figures: promise.js.)
   ========================================================================== */
( function () {
	'use strict';

	var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var root = document.documentElement;
	if ( ! reduce ) { root.classList.add( 'js-hp' ); }

	var vh = window.innerHeight;
	var clamp = function ( v ) { return v < 0 ? 0 : v > 1 ? 1 : v; };
	var jobs = [];
	var ticking = false;
	function frame() {
		ticking = false;
		for ( var i = 0; i < jobs.length; i++ ) { jobs[ i ](); }
	}
	function request() { if ( ! ticking ) { ticking = true; requestAnimationFrame( frame ); } }
	window.addEventListener( 'scroll', request, { passive: true } );
	window.addEventListener( 'resize', function () { vh = window.innerHeight; request(); } );

	var seen = function ( els, cb, margin ) {
		if ( ! ( 'IntersectionObserver' in window ) ) { els.forEach( cb ); return; }
		var io = new IntersectionObserver( function ( en ) {
			en.forEach( function ( e ) { if ( e.isIntersecting ) { cb( e.target ); io.unobserve( e.target ); } } );
		}, { rootMargin: margin || '0px 0px -18% 0px' } );
		els.forEach( function ( el ) { io.observe( el ); } );
	};
	var all = function ( s ) { return [].slice.call( document.querySelectorAll( s ) ); };

	/* 5. the timeline fills as it is read */
	all( '[data-hp-process]' ).forEach( function ( sec ) {
		var ol = sec.querySelector( '.hp-steps' );
		var steps = [].slice.call( ol.querySelectorAll( '.hp-step' ) );
		var mark = function ( p ) {
			ol.style.setProperty( '--fill', p.toFixed( 3 ) );
			var vertical = getComputedStyle( ol ).gridTemplateColumns.split( ' ' ).length < 2;
			var box = ol.getBoundingClientRect();
			steps.forEach( function ( s ) {
				var b = s.getBoundingClientRect();
				var at = vertical ? ( b.top - box.top ) / box.height : ( b.left - box.left ) / box.width;
				s.classList.toggle( 'is-on', p >= at - 0.001 );
			} );
		};
		if ( reduce ) { mark( 1 ); return; }
		jobs.push( function () {
			var r = ol.getBoundingClientRect();
			mark( clamp( ( vh * 0.8 - r.top ) / ( vh * 0.45 + r.height * 0.5 ) ) );
		} );
	} );


	/* the sectors: two rolls running against each other; scrolling pushes
	   them faster, and the citrus wheels turn with the type */
	var rows = all( '[data-sx]' ).map( function ( r ) {
		return { el: r, dir: +r.getAttribute( 'data-sx' ), run: r.querySelector( '.sx-run' ), x: 0, ics: [].slice.call( r.querySelectorAll( '.sx-ic' ) ) };
	} );
	if ( rows.length && ! reduce ) {
		var sxOn = false, sxRaf = 0, lastY = window.scrollY, boost = 0, lastT = 0;
		var tick = function ( t ) {
			sxRaf = 0;
			var dt = lastT ? Math.min( 50, t - lastT ) : 16;
			lastT = t;
			var y = window.scrollY;
			boost = boost * 0.92 + Math.min( 60, Math.abs( y - lastY ) ) * 0.08;
			lastY = y;
			var v = ( 0.045 + boost * 0.02 ) * dt;
			rows.forEach( function ( r ) {
				var w = r.run.offsetWidth || 1;
				r.x -= v * r.dir;
				if ( r.x <= -w ) { r.x += w; }
				if ( r.x > 0 ) { r.x -= w; }
				r.el.style.transform = 'translate3d(' + r.x.toFixed( 1 ) + 'px,0,0)';
				var rot = ( r.x * 0.9 ) % 360;
				r.ics.forEach( function ( ic ) { ic.style.setProperty( '--rot', rot.toFixed( 1 ) + 'deg' ); } );
			} );
			if ( sxOn ) { sxRaf = requestAnimationFrame( tick ); }
		};
		rows.forEach( function ( r ) { if ( r.dir < 0 ) { r.x = -( r.run.offsetWidth || 0 ) / 2; } } );
		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( function ( en ) {
				sxOn = en.some( function ( e ) { return e.isIntersecting; } ) || rows.some( function ( r ) { return r.el.getBoundingClientRect().bottom > 0 && r.el.getBoundingClientRect().top < window.innerHeight; } );
				if ( sxOn && ! sxRaf ) { lastT = 0; sxRaf = requestAnimationFrame( tick ); }
			} ).observe( rows[ 0 ].el.parentNode );
		}
	}

	request();
	if ( document.fonts && document.fonts.ready ) { document.fonts.ready.then( request ); }
} )();
