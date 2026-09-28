/* ==========================================================================
   CURSOR — a camera viewfinder (see cursor.css)

   • idle     : dot + small viewfinder trailing a touch behind the hand
   • on a link: the viewfinder locks round the element (autofocus)
   • press    : the brackets close in — the shutter
   • labels   : "Play" on reel buttons, "View" on work tiles, or any
                element's data-cursor-label="…"
   • tone     : warm white over anything inside [data-cursor-tone="light"]
                (hero video, mint surface, zoomed-in reel, preloader)

   Mouse / trackpad only, never with reduced motion, and never without JS:
   the system cursor is only hidden once this script has built its own.
   ========================================================================== */
( function () {
	'use strict';

	if ( ! window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) { return; }
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }

	var root = document.documentElement;
	var el = document.createElement( 'div' );
	el.className = 'lmf-cursor';
	el.setAttribute( 'aria-hidden', 'true' );
	el.innerHTML = '<i class="c-dot"></i><span class="c-frame"><b></b><b></b><b></b><b></b></span><em class="c-label"></em>';
	document.body.appendChild( el );
	root.classList.add( 'has-lmf-cursor' );

	var dot = el.querySelector( '.c-dot' );
	var frame = el.querySelector( '.c-frame' );
	var label = el.querySelector( '.c-label' );

	var TARGETS = 'a[href], button, [role="button"], input[type="submit"], input[type="button"], label[for], summary, [data-cursor-label]';
	var FIELDS  = 'input:not([type="submit"]):not([type="button"]):not([type="checkbox"]):not([type="radio"]), textarea, select, [contenteditable="true"]';
	var IDLE = 34, PAD = 8, BIG = 460;

	var mx = -100, my = -100;                 // pointer
	var fx = -100, fy = -100, fw = IDLE, fh = IDLE;   // viewfinder (centre, size)
	var target = null, press = 0, raf = 0, seen = false;

	function labelFor( t ) {
		if ( ! t ) { return ''; }
		if ( t.getAttribute( 'data-cursor-label' ) ) { return t.getAttribute( 'data-cursor-label' ); }
		if ( t.hasAttribute( 'data-reel' ) ) { return 'Play'; }
		if ( t.closest( '.tile' ) ) { return 'View'; }
		return '';
	}

	function toneAt( node ) {
		var z = node && node.closest ? node.closest( '[data-cursor-tone], #lmf-stage' ) : null;
		return !! ( z && ( z.id === 'lmf-stage' || z.getAttribute( 'data-cursor-tone' ) === 'light' ) );
	}

	document.addEventListener( 'mousemove', function ( e ) {
		mx = e.clientX; my = e.clientY;
		if ( ! seen ) { seen = true; fx = mx; fy = my; el.classList.add( 'is-on' ); }

		var node = e.target;
		// typing: step aside for the real caret
		el.classList.toggle( 'is-hidden', !! ( node.closest && node.closest( FIELDS ) ) );

		var t = node.closest ? node.closest( TARGETS ) : null;
		if ( t !== target ) {
			target = t;
			var txt = labelFor( t );
			label.textContent = txt;
			el.classList.toggle( 'has-label', !! txt );
		}
		el.classList.toggle( 'is-light', toneAt( node ) );
		wake();
	}, { passive: true } );

	document.addEventListener( 'mousedown', function () { press = 1; wake(); } );
	document.addEventListener( 'mouseup', function () { press = 0; wake(); } );
	document.addEventListener( 'mouseleave', function () { el.classList.remove( 'is-on' ); seen = false; } );
	window.addEventListener( 'blur', function () { el.classList.remove( 'is-on' ); seen = false; } );

	function wake() { if ( ! raf ) { raf = requestAnimationFrame( tick ); } }

	function tick() {
		raf = 0;
		var tx = mx, ty = my, tw = IDLE, th = IDLE, lock = false;

		if ( target && document.contains( target ) ) {
			var r = target.getBoundingClientRect();
			// lock on to buttons and links; very large targets (a whole tile)
			// keep the small viewfinder and just show the word
			if ( r.width < BIG && r.height < BIG * 0.6 && r.width > 0 ) {
				tx = r.left + r.width / 2;
				ty = r.top + r.height / 2;
				tw = r.width + PAD * 2;
				th = r.height + PAD * 2;
				lock = true;
			}
		}
		if ( press ) { tw *= 0.84; th *= 0.84; }

		var k = lock ? 0.26 : 0.2;
		fx += ( tx - fx ) * k;  fy += ( ty - fy ) * k;
		fw += ( tw - fw ) * 0.24; fh += ( th - fh ) * 0.24;
		el.classList.toggle( 'is-lock', lock );

		dot.style.transform = 'translate3d(' + mx + 'px,' + my + 'px,0)' + ( lock ? ' scale(0)' : '' );
		frame.style.width = fw.toFixed( 1 ) + 'px';
		frame.style.height = fh.toFixed( 1 ) + 'px';
		frame.style.transform = 'translate3d(' + ( fx - fw / 2 ).toFixed( 1 ) + 'px,' + ( fy - fh / 2 ).toFixed( 1 ) + 'px,0)';
		label.style.transform = 'translate3d(' + ( fx - fw / 2 ).toFixed( 1 ) + 'px,' + ( fy + fh / 2 + 10 ).toFixed( 1 ) + 'px,0)';

		var still = Math.abs( tx - fx ) < 0.1 && Math.abs( ty - fy ) < 0.1 && Math.abs( tw - fw ) < 0.1 && Math.abs( th - fh ) < 0.1;
		// keep following while locked: the target itself may be moving (the reel)
		if ( ! still || lock ) { raf = requestAnimationFrame( tick ); }
	}

	/* scrolling moves targets under a still mouse — re-check what is under it */
	window.addEventListener( 'scroll', function () {
		if ( ! seen ) { return; }
		var node = document.elementFromPoint( mx, my );
		if ( ! node ) { return; }
		var t = node.closest( TARGETS );
		if ( t !== target ) {
			target = t;
			var txt = labelFor( t );
			label.textContent = txt;
			el.classList.toggle( 'has-label', !! txt );
		}
		el.classList.toggle( 'is-light', toneAt( node ) );
		wake();
	}, { passive: true } );
} )();
