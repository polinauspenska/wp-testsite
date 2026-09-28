/* ==========================================================================
   CONTACT — the sentence form, the live call sheet, the wrap
   ========================================================================== */
( function () {
	'use strict';

	/* ----------------------------------------------------------------------
	   1. The call sheet clock — the time in Dubai, and whether we're in
	   ---------------------------------------------------------------------- */
	var clock = document.querySelector( '[data-lmf-clock]' );
	if ( clock && window.Intl ) {
		var days = ( clock.getAttribute( 'data-days' ) || '1,2,3,4,5' ).split( ',' ).map( Number );
		var from = +clock.getAttribute( 'data-from' ) || 9;
		var to   = +clock.getAttribute( 'data-to' ) || 18;
		var tEl  = clock.querySelector( '.ct-time' );
		var sEl  = clock.querySelector( '.ct-state' );
		var fmt  = new Intl.DateTimeFormat( 'en-GB', { timeZone: 'Asia/Dubai', hour: '2-digit', minute: '2-digit', weekday: 'short', hour12: false } );
		var dayIx = { Sun: 0, Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6 };
		var tick = function () {
			var parts = {};
			fmt.formatToParts( new Date() ).forEach( function ( p ) { parts[ p.type ] = p.value; } );
			var h = +parts.hour, m = +parts.minute, d = dayIx[ parts.weekday ];
			var open = days.indexOf( d ) > -1 && h >= from && h < to;
			tEl.textContent = parts.hour + ':' + parts.minute;
			sEl.textContent = open
				? 'in Dubai — the studio is open'
				: 'in Dubai — closed, we’ll reply first thing';
			clock.classList.toggle( 'is-open', open );
			clock.classList.toggle( 'is-closed', ! open );
		};
		tick();
		setInterval( tick, 20000 );
	}

	/* ----------------------------------------------------------------------
	   2. The sentence
	   ---------------------------------------------------------------------- */
	var form = document.querySelector( '.sf' );
	if ( ! form ) { return; }
	var done = document.querySelector( '.sf-done' );
	var lead = form.querySelector( '.sf-lead' );
	var err  = form.querySelector( '.sf-error' );
	var btn  = form.querySelector( '.sf-btn' );
	var src  = form.querySelector( '[name="source"]' );
	if ( src ) { src.value = location.href; }

	// the first words of the second sentence follow the choice above
	form.querySelectorAll( '[name="lmf_intent"]' ).forEach( function ( r ) {
		r.addEventListener( 'change', function () {
			if ( ! lead ) { return; }
			lead.classList.add( 'is-swap' );
			setTimeout( function () {
				lead.textContent = r.getAttribute( 'data-lead' );
				lead.classList.remove( 'is-swap' );
			}, 180 );
		} );
	} );

	// blanks grow with what's typed, selects fit what's chosen — the line
	// should read as a sentence, not as a row of boxes
	var meter = document.createElement( 'span' );
	meter.style.cssText = 'position:absolute;visibility:hidden;white-space:pre;left:-9999px;top:0';
	document.body.appendChild( meter );
	function fit( el ) {
		var cs = getComputedStyle( el );
		// the shorthand `font` often reads back empty — copy each part
		[ 'fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'letterSpacing', 'textTransform' ].forEach( function ( k ) { meter.style[ k ] = cs[ k ]; } );
		var txt = el.tagName === 'SELECT' ? el.options[ el.selectedIndex ].text : ( el.value || el.placeholder );
		meter.textContent = txt;
		var pad = el.tagName === 'SELECT' ? parseFloat( cs.fontSize ) * 1.25 : parseFloat( cs.fontSize ) * 0.35;
		// never wider than the line it sits on
		var max = el.closest( '.sf-sentence' ) ? el.closest( '.sf-sentence' ).clientWidth : 9999;
		el.style.width = Math.min( max, Math.ceil( meter.getBoundingClientRect().width + pad ) ) + 'px';
	}
	var fields = [].slice.call( form.querySelectorAll( '.sf-f input, .sf-f select' ) );
	fields.forEach( function ( el ) {
		fit( el );
		el.addEventListener( el.tagName === 'SELECT' ? 'change' : 'input', function () { fit( el ); el.classList.remove( 'is-bad' ); } );
	} );
	window.addEventListener( 'resize', function () { fields.forEach( fit ); } );
	if ( document.fonts && document.fonts.ready ) { document.fonts.ready.then( function () { fields.forEach( fit ); } ); }

	// the idea box grows instead of scrolling
	var ta = form.querySelector( 'textarea' );
	if ( ta ) {
		var grow = function () { ta.style.height = 'auto'; ta.style.height = ta.scrollHeight + 'px'; };
		ta.addEventListener( 'input', grow );
		grow();
	}

	/* ----------------------------------------------------------------------
	   3. Sending
	   ---------------------------------------------------------------------- */
	function bad( el, msg ) {
		el.classList.add( 'is-bad' );
		err.textContent = msg;
		err.hidden = false;
		el.focus();
		return false;
	}
	function valid() {
		var name = form.querySelector( '[name="lmf_name"]' );
		var mail = form.querySelector( '[name="lmf_email"]' );
		if ( ! name.value.trim() ) { return bad( name, 'Tell us your name so we know who to write to.' ); }
		if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( mail.value.trim() ) ) { return bad( mail, 'That email doesn’t look right — we need it to write back.' ); }
		err.hidden = true;
		return true;
	}

	function wrap( name ) {
		form.hidden = true;
		if ( done ) {
			var n = done.querySelector( '.sf-done-name' );
			if ( n && name ) { n.textContent = ', ' + name.split( ' ' )[ 0 ]; }
			done.hidden = false;
			done.setAttribute( 'tabindex', '-1' );
			done.focus( { preventScroll: true } );
			var top = done.getBoundingClientRect().top + window.scrollY - 140;
			window.scrollTo( { top: Math.max( 0, top ), behavior: 'smooth' } );
		}
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		if ( ! valid() ) { return; }
		var name = form.querySelector( '[name="lmf_name"]' ).value.trim();
		var endpoint = form.getAttribute( 'data-endpoint' );

		// preview copies of the page have nowhere to send to — show the wrap
		if ( ! endpoint || endpoint === '#' ) { wrap( name ); return; }

		btn.disabled = true;
		var fd = new FormData( form );
		fd.append( 'ajax', '1' );
		fetch( endpoint, { method: 'POST', body: fd, credentials: 'same-origin' } )
			.then( function ( r ) { return r.json().catch( function () { return { success: false }; } ); } )
			.then( function ( j ) {
				btn.disabled = false;
				if ( j && j.success ) { wrap( name ); return; }
				err.textContent = ( j && j.data && j.data.message ) || 'That didn’t send. Please try again, or email us directly.';
				err.hidden = false;
			} )
			.catch( function () {
				btn.disabled = false;
				err.textContent = 'That didn’t send — check your connection, or email us directly.';
				err.hidden = false;
			} );
	} );
} )();

/* ==========================================================================
   CONTACT (one screen) — fade the live map in once it has actually loaded,
   so a blocked or slow embed leaves the drawn plate on screen.
   ========================================================================== */
( function () {
	'use strict';
	var map = document.querySelector( '.cx-map' );
	var fr  = map && map.querySelector( '.cx-embed' );
	if ( ! fr ) { return; }
	var live = function () { map.classList.add( 'is-live' ); };
	if ( fr.complete ) { live(); }
	fr.addEventListener( 'load', live );
} )();
