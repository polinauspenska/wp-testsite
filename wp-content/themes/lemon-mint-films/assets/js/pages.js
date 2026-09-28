/* ==========================================================================
   LANDING PAGES — the one scroll idea each page has
   All four behaviours share one scroll listener and do nothing on pages
   that don't carry them. No library.
   ========================================================================== */
( function () {
	'use strict';

	var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var fine    = window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches;
	var jobs    = [];

	function clamp01( x ) { return x < 0 ? 0 : x > 1 ? 1 : x; }
	function vh() { return window.innerHeight; }

	/* ABOUT · manifesto — words light up as the paragraph passes the
	   middle of the screen */
	var man = document.querySelector( '[data-lmf-light]' );
	if ( man && ! reduced ) {
		var words = [].slice.call( man.querySelectorAll( '.pg-manifesto-text span' ) );
		var text = man.querySelector( '.pg-manifesto-text' );
		man.classList.add( 'is-live' );
		var litN = -1;
		jobs.push( function () {
			var r = text.getBoundingClientRect();
			// 0 when the paragraph's top reaches 80 % of the screen, 1 when its bottom reaches 45 %
			var p = clamp01( ( vh() * 0.8 - r.top ) / ( r.height + vh() * 0.35 ) );
			var n = Math.round( p * words.length );
			if ( n === litN ) { return; }
			litN = n;
			words.forEach( function ( w, i ) { w.classList.toggle( 'is-lit', i < n ); } );
		} );
	}

	/* SERVICES · process — the playhead follows the scroll across the steps */
	var proc = document.querySelector( '[data-lmf-playhead]' );
	if ( proc && ! reduced ) {
		var track = proc.querySelector( '.pg-track' );
		var steps = [].slice.call( proc.querySelectorAll( '.pg-step' ) );
		proc.classList.add( 'is-live' );
		jobs.push( function () {
			var r = track.getBoundingClientRect();
			var p = clamp01( ( vh() * 0.75 - r.top ) / ( vh() * 0.55 ) );
			proc.style.setProperty( '--ph', ( p * 100 ).toFixed( 2 ) + '%' );
			var past = Math.ceil( p * steps.length - 0.15 );
			steps.forEach( function ( s, i ) { s.classList.toggle( 'is-past', i < Math.max( 1, past ) ); } );
		} );
	}

	/* INDUSTRIES · the projector — the line under the cursor, or without a
	   mouse the line nearest the middle, chooses the still */
	var ix = document.querySelector( '[data-lmf-index]' );
	if ( ix ) {
		var rows = [].slice.call( ix.querySelectorAll( '.pg-ix' ) );
		var imgs = [].slice.call( ix.querySelectorAll( '.pg-projector-frame img' ) );
		var cap  = ix.querySelector( '.pg-projector-name' );
		var cur = -1, hover = -1;
		ix.classList.add( 'is-live' );
		function show( i ) {
			if ( i === cur || i < 0 ) { return; }
			cur = i;
			rows.forEach( function ( r, k ) { r.classList.toggle( 'is-on', k === i ); } );
			imgs.forEach( function ( im, k ) { im.classList.toggle( 'is-on', k === i ); } );
			if ( cap ) { cap.textContent = rows[ i ].querySelector( '.pg-ix-t' ).textContent; }
		}
		if ( fine ) {
			rows.forEach( function ( r, i ) {
				r.addEventListener( 'mouseenter', function () { hover = i; show( i ); } );
				r.addEventListener( 'focusin', function () { hover = i; show( i ); } );
			} );
			ix.querySelector( 'ol' ).addEventListener( 'mouseleave', function () { hover = -1; } );
		}
		jobs.push( function () {
			if ( hover > -1 ) { return; }
			var mid = vh() * 0.5, best = 0, d = 1e9;
			rows.forEach( function ( r, i ) {
				var b = r.getBoundingClientRect();
				var dd = Math.abs( b.top + b.height / 2 - mid );
				if ( dd < d ) { d = dd; best = i; }
			} );
			show( best );
		} );
	}

	/* STUDIO · the rooms — the room nearest the middle takes the picture */
	var sp = document.querySelector( '[data-lmf-spaces]' );
	if ( sp ) {
		var rooms = [].slice.call( sp.querySelectorAll( '.pg-space' ) );
		var pics  = [].slice.call( sp.querySelectorAll( '.pg-spaces-frame img' ) );
		var num   = sp.querySelector( '.pg-spaces-i' );
		var on = -1;
		sp.classList.add( 'is-live' );
		jobs.push( function () {
			var mid = vh() * 0.5, best = 0, d = 1e9;
			rooms.forEach( function ( r, i ) {
				var b = r.getBoundingClientRect();
				var dd = Math.abs( b.top + Math.min( b.height, vh() * 0.4 ) / 2 - mid );
				if ( dd < d ) { d = dd; best = i; }
			} );
			if ( best === on ) { return; }
			on = best;
			rooms.forEach( function ( r, i ) { r.classList.toggle( 'is-on', i === best ); } );
			pics.forEach( function ( p, i ) { p.classList.toggle( 'is-on', i === best ); } );
			if ( num ) { num.textContent = ( best + 1 < 10 ? '0' : '' ) + ( best + 1 ); }
		} );
	}

	if ( ! jobs.length ) { return; }
	var ticking = false;
	function run() { ticking = false; jobs.forEach( function ( j ) { j(); } ); }
	function onScroll() { if ( ! ticking ) { ticking = true; requestAnimationFrame( run ); } }
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll );
	run();
} )();
