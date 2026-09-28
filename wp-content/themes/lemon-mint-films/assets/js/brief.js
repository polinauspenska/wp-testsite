/* ==========================================================================
   THE BRIEF — "Start a production" popup
   Opens from any link to /contact/?i=production (or [data-brief]).
   Four scenes; the slate on the left fills in as they answer; the clapper
   clacks between scenes and shuts when the brief is sent.
   ========================================================================== */
( function () {
	'use strict';

	var root = document.getElementById( 'lmf-brief' );
	if ( ! root ) { return; }

	var html    = document.documentElement;
	var form    = root.querySelector( '.bf-form' );
	var done    = root.querySelector( '.bf-done' );
	var slate   = root.querySelector( '.bf-slate' );
	var scenes  = [].slice.call( root.querySelectorAll( '.bf-scene' ) );
	var bars    = [].slice.call( root.querySelectorAll( '.bf-bars i' ) );
	var no      = root.querySelector( '.bf-no' );
	var back    = root.querySelector( '.bf-back' );
	var next    = root.querySelector( '.bf-next' );
	var send    = root.querySelector( '.bf-send' );
	var skip    = root.querySelector( '.bf-skip' );
	var err     = root.querySelector( '.bf-error' );
	var sendT   = send.querySelector( '.bf-send-t' );
	var endpoint = form.getAttribute( 'data-endpoint' ) || '';
	var reduce  = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	var N = scenes.length, cur = 0, isOpen = false, lastFocus = null, closeTimer = 0, sent = false;

	function pad( n ) { return ( n < 10 ? '0' : '' ) + n; }
	function slot( k ) { return root.querySelector( '[data-slate="' + k + '"]' ); }

	// today's date on the slate, the way a clapper loader writes it
	var d = new Date();
	slot( 'date' ).textContent = pad( d.getDate() ) + '.' + pad( d.getMonth() + 1 ) + '.' + String( d.getFullYear() ).slice( -2 );

	/* ---- the slate ---------------------------------------------------- */
	function write( k, val ) {
		var el = slot( k );
		if ( ! el ) { return; }
		var empty = el.getAttribute( 'data-empty' ) || '';
		var v = val || empty;
		if ( el.textContent === v ) { return; }
		el.textContent = v;
		el.classList.toggle( 'is-empty', ! val );
		el.classList.remove( 'is-new' );
		if ( val ) { void el.offsetWidth; el.classList.add( 'is-new' ); }
	}
	function picked( name ) {
		var i = form.querySelector( 'input[name="' + name + '"]:checked' );
		return i ? i.getAttribute( 'data-label' ) : '';
	}
	function field( name ) {
		var i = form.querySelector( '[name="' + name + '"]' );
		return i ? i.value.trim() : '';
	}
	function refreshSlate() {
		write( 'service', picked( 'lmf_service' ) );
		write( 'when', shortWhen( form.querySelector( 'input[name="lmf_when"]:checked' ) ) );
		write( 'budget', shortBudget( form.querySelector( 'input[name="lmf_budget"]:checked' ) ) );
		write( 'client', field( 'lmf_company' ) || field( 'lmf_name' ) );
	}
	// the board is small — shorter words for it
	function shortWhen( i ) {
		if ( ! i ) { return ''; }
		return ( { asap: 'ASAP', month: '< 1 month', quarter: '2–3 months', later: 'Later this year', unsure: 'Open' } )[ i.value ] || i.getAttribute( 'data-label' );
	}
	function shortBudget( i ) {
		if ( ! i ) { return ''; }
		return ( { unsure: 'Open', u50: '< 50K', '50-150': '50–150K', '150-400': '150–400K', '400+': '400K +' } )[ i.value ] || i.getAttribute( 'data-label' );
	}
	[ 'service', 'when', 'budget', 'client' ].forEach( function ( k ) { slot( k ).classList.add( 'is-empty' ); } );

	function clack() {
		if ( reduce ) { return; }
		slate.classList.remove( 'is-clack' );
		void slate.offsetWidth;
		slate.classList.add( 'is-clack' );
	}

	/* ---- scenes ------------------------------------------------------- */
	function show( i, dir ) {
		i = Math.max( 0, Math.min( N - 1, i ) );
		scenes.forEach( function ( s, j ) {
			s.hidden = j !== i;
			s.classList.remove( 'is-in', 'is-in-back' );
		} );
		var s = scenes[ i ];
		if ( dir && ! reduce ) { void s.offsetWidth; s.classList.add( dir < 0 ? 'is-in-back' : 'is-in' ); }
		if ( i !== cur ) { clack(); }
		cur = i;

		no.textContent = pad( i + 1 );
		slot( 'scene' ).textContent = pad( i + 1 );
		bars.forEach( function ( b, j ) { b.classList.toggle( 'is-on', j <= i ); } );
		back.hidden = i === 0;
		next.hidden = i === N - 1;
		send.hidden = i !== N - 1;
		skip.hidden = ! ( i === 1 || i === 2 );
		err.hidden = true;
		root.querySelector( '.bf-scenes' ).scrollTop = 0;

		// focus the first thing to answer, once the slide has started
		setTimeout( function () {
			var f = s.querySelector( 'input:checked, textarea, input[type="text"], input[type="radio"]' );
			if ( f && isOpen ) { f.focus( { preventScroll: true } ); }
		}, dir ? 80 : 0 );
	}

	next.addEventListener( 'click', function () {
		if ( cur === 0 && ! picked( 'lmf_service' ) ) {
			fail( next.getAttribute( 'data-need' ) || 'Pick what we’re making — or “Something else”.' );
			return;
		}
		show( cur + 1, 1 );
	} );
	back.addEventListener( 'click', function () { show( cur - 1, -1 ); } );

	// scene 1 is one choice: pick it and the next scene rolls in
	var autoT = 0;
	form.addEventListener( 'change', function ( e ) {
		refreshSlate();
		if ( e.target.name === 'lmf_service' ) { err.hidden = true; }
		if ( e.target.name === 'lmf_service' && cur === 0 ) {
			clearTimeout( autoT );
			autoT = setTimeout( function () { if ( cur === 0 ) { show( 1, 1 ); } }, 420 );
		}
	} );
	form.addEventListener( 'input', function ( e ) {
		if ( e.target.name === 'lmf_company' || e.target.name === 'lmf_name' ) { refreshSlate(); }
		if ( e.target.name === 'lmf_message' ) {
			root.querySelector( '.bf-len span' ).textContent = e.target.value.length;
		}
		var f = e.target.closest( '.bf-f' );
		if ( f ) {
			f.classList.remove( 'is-bad' );
			if ( ! form.querySelector( '.bf-f.is-bad' ) ) { err.hidden = true; }
		}
	} );
	// Enter in a text field moves on instead of submitting early
	form.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== 'Enter' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'BUTTON' ) { return; }
		if ( cur < N - 1 ) { e.preventDefault(); next.click(); }
	} );

	function fail( msg ) {
		err.textContent = msg;
		err.hidden = false;
	}

	/* ---- send --------------------------------------------------------- */
	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		if ( sent ) { return; }
		var name  = form.querySelector( '[name="lmf_name"]' );
		var email = form.querySelector( '[name="lmf_email"]' );
		var bad = [];
		if ( ! name.value.trim() ) { bad.push( name ); }
		if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email.value.trim() ) ) { bad.push( email ); }
		if ( bad.length ) {
			bad.forEach( function ( b ) { b.closest( '.bf-f' ).classList.add( 'is-bad' ); } );
			bad[ 0 ].focus();
			fail( 'We need your name and a working email to write back.' );
			return;
		}

		form.querySelector( '[name="source"]' ).value = location.href;
		send.classList.add( 'is-busy' );
		var label = sendT.textContent;
		sendT.textContent = sendT.getAttribute( 'data-busy' ) || label;
		err.hidden = true;

		var go;
		if ( ! endpoint ) {
			// preview without WordPress: pretend it went
			go = new Promise( function ( r ) { setTimeout( function () { r( { success: true } ); }, 700 ); } );
		} else {
			var fd = new FormData( form );
			fd.append( 'ajax', '1' );
			go = fetch( endpoint, { method: 'POST', body: fd, credentials: 'same-origin' } )
				.then( function ( r ) { return r.json().catch( function () { return { success: r.ok }; } ); } );
		}

		go.then( function ( res ) {
			if ( ! res || ! res.success ) {
				throw new Error( res && res.data && res.data.message ? res.data.message : '' );
			}
			sent = true;
			wrap( name.value.trim().split( /\s+/ )[ 0 ] );
		} ).catch( function ( x ) {
			fail( ( x && x.message ) || 'That didn’t send. Please try again, or email us directly.' );
		} ).then( function () {
			send.classList.remove( 'is-busy' );
			sendT.textContent = label;
		} );
	} );

	function wrap( first ) {
		root.classList.add( 'is-done' );
		slot( 'scene' ).textContent = 'Wrap';
		root.querySelector( '.bf-done-name' ).textContent = first ? ', ' + first : '';
		form.hidden = true;
		done.hidden = false;
		if ( ! reduce ) { done.classList.add( 'is-in' ); }
		var b = done.querySelector( 'button' );
		if ( b ) { setTimeout( function () { b.focus( { preventScroll: true } ); }, 400 ); }
	}

	/* ---- open / close ------------------------------------------------- */
	function open( trigger ) {
		if ( isOpen ) { return; }
		isOpen = true;
		clearTimeout( closeTimer );
		lastFocus = trigger || document.activeElement;

		// a service picked on the page it came from (e.g. ?svc=brand-films)
		if ( trigger && trigger.href ) {
			var m = trigger.href.match( /[?&]svc=([^&#]+)/ );
			if ( m ) {
				var r = form.querySelector( 'input[name="lmf_service"][value="' + decodeURIComponent( m[ 1 ] ).replace( /"/g, '' ) + '"]' );
				if ( r ) { r.checked = true; refreshSlate(); }
			}
		}
		if ( sent ) { reset(); }

		var sb = window.innerWidth - html.clientWidth;
		if ( sb > 0 ) { document.body.style.paddingRight = sb + 'px'; }
		html.classList.add( 'bf-open' );
		root.hidden = false;
		show( cur, 0 );
		requestAnimationFrame( function () { requestAnimationFrame( function () { root.classList.add( 'is-open' ); } ); } );
	}
	function close() {
		if ( ! isOpen ) { return; }
		isOpen = false;
		root.classList.remove( 'is-open' );
		closeTimer = setTimeout( function () {
			root.hidden = true;
			html.classList.remove( 'bf-open' );
			document.body.style.paddingRight = '';
			if ( sent ) { reset(); }
		}, reduce ? 0 : 480 );
		if ( lastFocus && lastFocus.focus ) { lastFocus.focus( { preventScroll: true } ); }
	}
	function reset() {
		sent = false;
		form.reset();
		root.classList.remove( 'is-done' );
		done.hidden = true;
		done.classList.remove( 'is-in' );
		form.hidden = false;
		[ 'service', 'when', 'budget', 'client' ].forEach( function ( k ) { write( k, '' ); } );
		root.querySelector( '.bf-len span' ).textContent = '0';
		cur = 0;
		show( 0, 0 );
	}

	root.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '[data-bf-close]' ) ) { e.preventDefault(); close(); }
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( ! isOpen ) { return; }
		if ( e.key === 'Escape' ) { e.preventDefault(); close(); return; }
		if ( e.key !== 'Tab' ) { return; }
		// keep focus inside the dialog
		var f = [].slice.call( root.querySelectorAll( 'button, a[href], input, textarea, select' ) ).filter( function ( el ) {
			return ! el.disabled && el.tabIndex !== -1 && el.offsetParent !== null && ! ( el.type === 'radio' && ! el.checked && form.querySelector( 'input[name="' + el.name + '"]:checked' ) );
		} );
		if ( ! f.length ) { return; }
		var i = f.indexOf( document.activeElement );
		if ( e.shiftKey && i <= 0 ) { e.preventDefault(); f[ f.length - 1 ].focus(); }
		else if ( ! e.shiftKey && i === f.length - 1 ) { e.preventDefault(); f[ 0 ].focus(); }
	} );

	/* ---- triggers ----------------------------------------------------- */
	function isTrigger( a ) {
		if ( a.hasAttribute( 'data-brief' ) ) { return true; }
		var h = a.getAttribute( 'href' ) || '';
		return /contact(?:\/|\.html)?\?(?:[^#]*&)?i=production\b/.test( h );
	}
	document.addEventListener( 'click', function ( e ) {
		if ( e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey ) { return; }
		var a = e.target.closest && e.target.closest( 'a[href], [data-brief]' );
		if ( ! a || root.contains( a ) || ! isTrigger( a ) ) { return; }
		e.preventDefault();
		// from inside the menu: let it close first, then roll in
		var menuOpen = !! a.closest( '#lmf-menu' );
		setTimeout( function () { open( a ); }, menuOpen ? 260 : 0 );
	} );

	// a shared link straight to the brief: /#brief
	if ( location.hash === '#brief' ) { open( null ); }
} )();
