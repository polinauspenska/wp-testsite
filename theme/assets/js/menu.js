/* ==========================================================================
   MENU — open / close, focus, scroll lock
   ========================================================================== */
( function () {
	'use strict';

	var btn  = document.getElementById( 'burger' );
	var menu = document.getElementById( 'lmf-menu' );
	if ( ! btn || ! menu ) { return; }

	var root  = document.documentElement;
	var word  = btn.querySelector( '.burger-word' );
	var links = [].slice.call( menu.querySelectorAll( 'a, button' ) );
	var open  = false, lastFocus = null, closeTimer = 0;

	// mark the page we are on
	var here = location.href.replace( /[?#].*$/, '' ).replace( /\/$/, '' );
	menu.querySelectorAll( '.menu-link' ).forEach( function ( a ) {
		var href = a.href.replace( /[?#].*$/, '' ).replace( /\/$/, '' );
		if ( href && href === here ) { a.setAttribute( 'aria-current', 'page' ); }
	} );

	function setOpen( v ) {
		if ( v === open ) { return; }
		open = v;
		clearTimeout( closeTimer );
		btn.setAttribute( 'aria-expanded', v ? 'true' : 'false' );
		if ( word ) { word.textContent = word.getAttribute( v ? 'data-close' : 'data-open' ); }

		if ( v ) {
			lastFocus = document.activeElement;
			// keep the page from shifting when the scrollbar disappears
			var sb = window.innerWidth - root.clientWidth;
			if ( sb > 0 ) {
				document.body.style.paddingRight = sb + 'px';
				var nav = document.getElementById( 'nav' );
				if ( nav ) { nav.style.paddingRight = sb + 'px'; }
			}
			menu.hidden = false;
			root.classList.add( 'menu-open' );
			// next frame, so the transitions have a start state
			requestAnimationFrame( function () {
				requestAnimationFrame( function () {
					menu.classList.add( 'is-open' );
					var first = menu.querySelector( '.menu-link' );
					if ( first ) { first.focus( { preventScroll: true } ); }
				} );
			} );
		} else {
			menu.classList.remove( 'is-open' );
			root.classList.remove( 'menu-open' );
			closeTimer = setTimeout( function () {
				menu.hidden = true;
				document.body.style.paddingRight = '';
				var nav = document.getElementById( 'nav' );
				if ( nav ) { nav.style.paddingRight = ''; }
			}, 600 );
			if ( lastFocus && lastFocus.focus ) { lastFocus.focus( { preventScroll: true } ); }
		}
	}

	btn.addEventListener( 'click', function () { setOpen( ! open ); } );

	// a link inside closes the menu (same-page anchors included)
	menu.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( 'a' ) ) { setOpen( false ); }
		else if ( e.target.classList.contains( 'menu-veil' ) ) { setOpen( false ); }
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( ! open ) { return; }
		if ( e.key === 'Escape' ) { e.preventDefault(); setOpen( false ); btn.focus(); return; }
		if ( e.key !== 'Tab' ) { return; }
		// keep focus inside: the menu's links plus the close button
		var ring = links.concat( [ btn ] );
		var i = ring.indexOf( document.activeElement );
		if ( e.shiftKey && ( i <= 0 ) ) { e.preventDefault(); ring[ ring.length - 1 ].focus(); }
		else if ( ! e.shiftKey && i === ring.length - 1 ) { e.preventDefault(); ring[ 0 ].focus(); }
	} );

	// a resize to desktop or back never leaves a stuck lock
	window.addEventListener( 'pagehide', function () { setOpen( false ); } );
} )();
