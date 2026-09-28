/* ==========================================================================
   SELECTED WORK — the wheel (Canvas 2D, one video stream, no dependencies)
   --------------------------------------------------------------------------
   The citrus wheel from the mark: a lemon rind, eight lemon wedges, pale
   spokes and centre — as drawn on the logo's reels.
   Scroll (the section pins with CSS sticky):

     FILL   the wedges fill with film, clockwise from twelve, like a hand
            sweeping a dial — each wedge its own film;
     TURN   the wheel turns a wedge at a time; the wedge at twelve is lit
            and its case is set beside it (client, the result);
     DIVE   the camera flies into the last wedge and its film opens to
            full frame through an iris — then the page carries on.

   • All eight films come from ONE atlas video (4 × 2 tiles): one decoder,
     one network request. Each wedge draws its tile, rotated with the wedge.
   • Wedge outlines are computed (straight spokes of even width, rounded
     corners, an outer edge on the rind circle), so they stay crisp at any
     zoom, all the way into the dive.
   • Text is real HTML (captions, index) over the canvas.
   • The loop sleeps off screen; the videos play only while they can be seen.
   • Reduced motion or no canvas → the script does nothing and the section
     stays a still list of the eight cases (work-wheel.css base styles).
   ========================================================================== */
( function () {
	'use strict';

	var sec = document.querySelector( '[data-lmf-wheel]' );
	if ( ! sec ) { return; }
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }

	var canvas = sec.querySelector( '.wk-gl' );
	var ctx = canvas && canvas.getContext ? canvas.getContext( '2d' ) : null;
	if ( ! ctx ) { return; }

	var stage  = sec.querySelector( '.wk-stage' );
	var src    = sec.querySelector( '.wk-src' );
	var head   = sec.querySelector( '.wk-head' );
	var capsEl = sec.querySelector( '.wk-caps' );
	var caps   = [].slice.call( sec.querySelectorAll( '.wk-cap' ) );
	var idx    = [].slice.call( sec.querySelectorAll( '.wk-index li' ) );
	var index  = sec.querySelector( '.wk-index' );
	var dive   = sec.querySelector( '.wk-dive' );
	var diveV  = sec.querySelector( '.wk-dive-v' );
	var N      = caps.length;
	if ( N < 3 ) { return; }
	var COLS = +sec.getAttribute( 'data-cols' ) || 4;
	var ROWS = +sec.getAttribute( 'data-rows' ) || 2;
	var urls = caps.map( function ( c ) { var a = c.querySelector( 'a' ); return a ? a.href : ''; } );

	sec.classList.add( 'is-live' );

	/* ----------------------------------------------------------------------
	   1. TIMELINE — lengths in screens of scroll
	   ---------------------------------------------------------------------- */
	var FILL = 1.1, HOLD1 = 0.25, STEP = 0.45, HOLD2 = 0.35, DIVE = 1.2, HOLD3 = 0.55;
	var B0 = FILL + HOLD1;
	var D0 = B0 + ( N - 1 ) * STEP + HOLD2;
	var T  = D0 + DIVE + HOLD3;
	sec.style.setProperty( '--wk-len', T.toFixed( 3 ) );

	/* ----------------------------------------------------------------------
	   2. GEOMETRY — the wheel in units of its radius, wedge pointing up
	   ---------------------------------------------------------------------- */
	var LEMON = '#F8BE1B';                  // the wheels' own yellow in the mark
	var TAU = Math.PI * 2, STEPA = TAU / N, TH = Math.PI / N;
	// drawn after the citrus wheels in the mark: a lemon rind line, a thin
	// pale gap, eight wedges split by narrow pale spokes and a small pale
	// centre — no dark disc
	var RING = 0.965, RINGW = 0.07;        // lemon rind line
	var R1 = 0.86;                          // outer edge of the wedges
	var S  = 0.062;                         // spoke width
	var RC = 0.075, RT = 0.035;             // corner and tip rounding

	var u   = [ Math.sin( TH ), -Math.cos( TH ) ];
	var nIn = [ -Math.cos( TH ), -Math.sin( TH ) ];
	function at( t, off ) { return [ t * u[ 0 ] + off * nIn[ 0 ], t * u[ 1 ] + off * nIn[ 1 ] ]; }
	function mx( p ) { return [ -p[ 0 ], p[ 1 ] ]; }
	function ang( p, c ) { return Math.atan2( p[ 1 ] - c[ 1 ], p[ 0 ] - c[ 0 ] ); }

	var tc = Math.sqrt( ( R1 - RC ) * ( R1 - RC ) - ( S / 2 + RC ) * ( S / 2 + RC ) );
	var cR = at( tc, S / 2 + RC ), LR = at( tc, S / 2 );
	var lc = Math.hypot( cR[ 0 ], cR[ 1 ] );
	var KR = [ cR[ 0 ] * R1 / lc, cR[ 1 ] * R1 / lc ];
	var tt = ( S / 2 + RT ) * Math.cos( TH ) / Math.sin( TH );
	var cT = at( tt, S / 2 + RT ), TR = at( tt, S / 2 );
	var cL = mx( cR ), LL = mx( LR ), KL = mx( KR ), TL = mx( TR );
	cT = [ 0, cT[ 1 ] ];

	var wedge = new Path2D();
	wedge.moveTo( TR[ 0 ], TR[ 1 ] );
	wedge.lineTo( LR[ 0 ], LR[ 1 ] );
	wedge.arc( cR[ 0 ], cR[ 1 ], RC, ang( LR, cR ), ang( KR, cR ), true );
	wedge.arc( 0, 0, R1, ang( KR, [ 0, 0 ] ), ang( KL, [ 0, 0 ] ), true );
	wedge.arc( cL[ 0 ], cL[ 1 ], RC, ang( KL, cL ), ang( LL, cL ), true );
	wedge.lineTo( TL[ 0 ], TL[ 1 ] );
	wedge.arc( cT[ 0 ], cT[ 1 ], RT, ang( TL, cT ), ang( TR, cT ), true );
	wedge.closePath();

	// the box a wedge's film is fitted into
	var BX = Math.max( LR[ 0 ], KR[ 0 ] ) + 0.01;
	var BY0 = -R1 - 0.01, BY1 = cT[ 1 ] + RT;
	var TGT = [ 0, ( BY0 + BY1 ) / 2 ];       // where the camera dives to

	/* ----------------------------------------------------------------------
	   3. FOOTAGE
	   ---------------------------------------------------------------------- */
	var poster = new Image();
	poster.decoding = 'async';
	poster.src = src ? src.getAttribute( 'poster' ) : '';
	function source() {
		if ( src && src.readyState >= 2 && src.videoWidth ) { return [ src, src.videoWidth, src.videoHeight ]; }
		if ( poster.complete && poster.naturalWidth ) { return [ poster, poster.naturalWidth, poster.naturalHeight ]; }
		return null;
	}
	function drawTile( i, s ) {
		var sw = s[ 1 ] / COLS, sh = s[ 2 ] / ROWS;
		var sx = ( i % COLS ) * sw, sy = Math.floor( i / COLS ) * sh;
		var bw = BX * 2, bh = BY1 - BY0;
		var k = Math.max( bw / sw, bh / sh );
		var dw = sw * k, dh = sh * k;
		ctx.drawImage( s[ 0 ], sx, sy, sw, sh, -dw / 2, BY0 + ( bh - dh ) * 0.35, dw, dh );
	}

	/* ----------------------------------------------------------------------
	   4. LAYOUT
	   ---------------------------------------------------------------------- */
	var W = 0, H = 0, dpr = 1, R = 100, CX = 0, CY = 0, mobile = false;
	function layout() {
		W = stage.clientWidth;
		H = stage.clientHeight;
		dpr = Math.min( 2, window.devicePixelRatio || 1 );
		canvas.width = Math.round( W * dpr );
		canvas.height = Math.round( H * dpr );
		mobile = W <= 900;
		if ( mobile ) {
			var top = head.getBoundingClientRect().bottom - stage.getBoundingClientRect().top + 14;
			var bot = H - capsEl.offsetHeight - 34;
			R = Math.max( 90, Math.min( W * 0.44, ( bot - top ) / 2 ) );
			CX = W / 2;
			CY = top + ( bot - top ) / 2;
		} else {
			// left column: heading and caption; the wheel takes the rest;
			// the index gets the far right only when the wheel keeps its size
			var pad  = parseFloat( getComputedStyle( head ).paddingLeft ) || 60;
			var capW = Math.min( W * 0.26, 380 );
			var Lx   = pad + capW + 56;
			var Rmax = H * 0.4;
			var withIdx = index && ( W - pad - 250 - Lx ) / 2 >= Rmax * 0.92;
			var Rx   = W - pad - ( withIdx ? 250 : 0 );
			R  = Math.min( Rmax, ( Rx - Lx ) / 2 );
			CX = ( Lx + Rx ) / 2;
			CY = H * 0.53;
			sec.style.setProperty( '--wk-cap', capW.toFixed( 0 ) + 'px' );
			sec.classList.toggle( 'has-index', !! withIdx );
		}
		dirty = true;
	}

	/* ----------------------------------------------------------------------
	   5. DRAW
	   ---------------------------------------------------------------------- */
	var clamp = function ( v ) { return v < 0 ? 0 : v > 1 ? 1 : v; };
	var smooth = function ( t ) { return t * t * ( 3 - 2 * t ); };
	var io3 = function ( t ) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow( -2 * t + 2, 3 ) / 2; };
	var wrap = function ( v ) { v = ( ( v % N ) + N ) % N; return v > N / 2 ? v - N : v; };

	var P = 0, target = 0, hover = -1, shown = -2, cam = null, dirty = true;

	function frame() {
		// ---- where we are on the timeline
		var a    = clamp( P / FILL );
		var grow = smooth( clamp( P / 0.6 ) );
		var sweep = io3( a );
		var q = Math.max( 0, Math.min( N - 1, ( P - B0 ) / STEP ) );
		var k = Math.floor( q ), f = q - k;
		var pos = Math.min( N - 1, k + smooth( clamp( ( f - 0.2 ) / 0.6 ) ) );
		var bAmt = smooth( clamp( ( P - FILL + 0.1 ) / ( HOLD1 + 0.2 ) ) );
		var d  = clamp( ( P - D0 ) / DIVE );
		var e  = io3( d );
		var rho = -( 1 - smooth( a ) ) * 0.6 - pos * STEPA;

		// ---- camera
		var Smax = Math.max( W, H ) / ( BX * 2 * R ) * 1.6;
		var s  = ( 0.84 + 0.16 * grow ) * Math.exp( Math.log( Smax ) * e );
		var ox = CX + ( W / 2 - CX ) * e, oy = CY + ( H / 2 - CY ) * e;
		var tx = ox - s * e * TGT[ 0 ] * R, ty = oy - s * e * TGT[ 1 ] * R;
		cam = { s: s, tx: tx, ty: ty, rho: rho, live: bAmt > 0.5 && d < 0.05 };

		ctx.setTransform( 1, 0, 0, 1, 0, 0 );
		ctx.clearRect( 0, 0, canvas.width, canvas.height );
		ctx.setTransform( dpr * s * R, 0, 0, dpr * s * R, dpr * tx, dpr * ty );

		// the pale flesh and the rind
		ctx.fillStyle = '#FFFFFF';
		ctx.beginPath(); ctx.arc( 0, 0, RING, 0, TAU ); ctx.fill();
		ctx.strokeStyle = LEMON;
		ctx.lineWidth = RINGW;
		ctx.beginPath(); ctx.arc( 0, 0, RING, 0, TAU ); ctx.stroke();

		var img = source();
		var sig = sweep * TAU;
		for ( var i = 0; i < N; i++ ) {
			var dist = Math.abs( wrap( i - pos ) );
			var lit  = bAmt * Math.max( 0, 1 - dist );
			if ( i === hover && cam.live ) { lit = Math.max( lit, 0.85 ); }
			var dim  = bAmt * 0.58 * ( 1 - lit ) * ( 1 - e );

			ctx.save();
			ctx.rotate( rho + i * STEPA );
			ctx.translate( 0, -0.035 * lit * ( 1 - e ) );

			ctx.fillStyle = LEMON;
			ctx.fill( wedge );

			if ( img && sig > 0 ) {
				ctx.save();
				ctx.clip( wedge );
				if ( sig < TAU - 1e-4 ) {
					var a0 = -Math.PI / 2 - i * STEPA;
					ctx.beginPath();
					ctx.moveTo( 0, 0 );
					ctx.arc( 0, 0, 2, a0, a0 + sig, false );
					ctx.closePath();
					ctx.clip();
				}
				drawTile( i, img );
				if ( dim > 0.001 ) {
					ctx.fillStyle = 'rgba(17,17,17,' + dim.toFixed( 3 ) + ')';
					ctx.fillRect( -1, -1.1, 2, 1.2 );
				}
				ctx.restore();
			}
			if ( lit > 0.01 && e < 0.99 ) {
				ctx.strokeStyle = 'rgba(17,17,17,' + ( lit * ( 1 - e ) ).toFixed( 3 ) + ')';
				ctx.lineWidth = 0.012;
				ctx.stroke( wedge );
			}
			ctx.restore();
		}

		// the gate at twelve o'clock: which wedge is "on"
		var g = bAmt * ( 1 - clamp( d * 4 ) );
		if ( g > 0.01 ) {
			ctx.fillStyle = 'rgba(245,184,46,' + g.toFixed( 3 ) + ')';
			ctx.beginPath();
			ctx.moveTo( -0.045, -1.12 );
			ctx.lineTo( 0.045, -1.12 );
			ctx.lineTo( 0, -1.05 );
			ctx.closePath();
			ctx.fill();
		}

		// ---- the HTML around it
		var ui = 1 - clamp( d / 0.3 );
		head.style.opacity = ui.toFixed( 3 );
		var capA = ui * clamp( ( bAmt - 0.4 ) / 0.4 );
		capsEl.style.opacity = capA.toFixed( 3 );
		capsEl.style.pointerEvents = capA > 0.5 ? '' : 'none';
		if ( index ) {
			index.style.opacity = ( ui * ( 0.25 + 0.75 * bAmt ) ).toFixed( 3 );
			index.style.pointerEvents = ui > 0.5 ? '' : 'none';
		}
		var on = hover >= 0 && cam.live ? hover : Math.round( pos );
		if ( on !== shown ) {
			shown = on;
			caps.forEach( function ( c, j ) { c.classList.toggle( 'is-on', j === on ); } );
			idx.forEach( function ( l, j ) { l.classList.toggle( 'is-on', j === on ); } );
		}

		// ---- the iris
		var ir = clamp( ( d - 0.5 ) / 0.42 );
		if ( ir > 0 ) {
			dive.classList.add( 'is-on' );
			dive.style.setProperty( '--dr', ( io3( ir ) * Math.hypot( W, H ) * 0.55 ).toFixed( 1 ) + 'px' );
			dive.style.setProperty( '--dt', clamp( ( d - 0.86 ) / 0.14 ).toFixed( 3 ) );
			dive.querySelectorAll( 'a' ).forEach( function ( l ) { l.tabIndex = d > 0.9 ? 0 : -1; } );
		} else if ( dive.classList.contains( 'is-on' ) ) {
			dive.classList.remove( 'is-on' );
			dive.style.setProperty( '--dr', '0px' );
		}

		// ---- the films play only while they can be seen
		play( src, a > 0 && ir < 1 );
		play( diveV, d > 0.35 );
	}

	function play( v, want ) {
		if ( ! v ) { return; }
		if ( want ) {
			if ( v.preload !== 'auto' ) { v.preload = 'auto'; }
			if ( v.paused ) { var p = v.play(); if ( p && p.catch ) { p.catch( function () {} ); } }
		} else if ( ! v.paused ) { v.pause(); }
	}

	/* ----------------------------------------------------------------------
	   6. LOOP — scroll sets a target, the wheel eases toward it
	   ---------------------------------------------------------------------- */
	var visible = false, raf = 0;
	function measure() {
		var r = sec.getBoundingClientRect();
		target = Math.max( 0, Math.min( T, -r.top / ( H || window.innerHeight ) ) );
	}
	function loop() {
		raf = 0;
		if ( ! visible ) { return; }
		measure();
		var dP = target - P;
		P = Math.abs( dP ) < 0.0005 ? target : P + dP * 0.14;
		frame();
		raf = requestAnimationFrame( loop );
	}
	function wake() { if ( ! raf && visible ) { raf = requestAnimationFrame( loop ); } }

	if ( 'IntersectionObserver' in window ) {
		new IntersectionObserver( function ( en ) {
			visible = en[ 0 ].isIntersecting;
			if ( visible ) {
				wake();
			} else {
				play( src, false );
				play( diveV, false );
			}
		}, { rootMargin: '200px 0px' } ).observe( sec );
	} else {
		visible = true;
	}

	/* ----------------------------------------------------------------------
	   7. POINTER — hover lights a wedge, a click opens its case
	   ---------------------------------------------------------------------- */
	function hit( ev ) {
		if ( ! cam || ! cam.live ) { return -1; }
		var b = canvas.getBoundingClientRect();
		var x = ( ev.clientX - b.left - cam.tx ) / ( cam.s * R );
		var y = ( ev.clientY - b.top - cam.ty ) / ( cam.s * R );
		var r = Math.hypot( x, y );
		if ( r < 0.1 || r > R1 + 0.02 ) { return -1; }
		var t = Math.atan2( x, -y ) - cam.rho;
		return ( ( Math.round( t / STEPA ) % N ) + N ) % N;
	}
	canvas.addEventListener( 'pointermove', function ( ev ) {
		if ( ev.pointerType !== 'mouse' ) { return; }
		var h = hit( ev );
		if ( h !== hover ) {
			hover = h;
			canvas.style.cursor = h >= 0 ? 'pointer' : '';
			if ( h >= 0 ) { canvas.setAttribute( 'data-cursor-label', 'View' ); } else { canvas.removeAttribute( 'data-cursor-label' ); }
		}
	} );
	canvas.addEventListener( 'pointerleave', function () { hover = -1; canvas.removeAttribute( 'data-cursor-label' ); } );
	canvas.addEventListener( 'click', function ( ev ) {
		var h = hit( ev );
		if ( h >= 0 && urls[ h ] ) { window.location.href = urls[ h ]; }
	} );

	/* ----------------------------------------------------------------------
	   8. GO
	   ---------------------------------------------------------------------- */
	layout();
	if ( 'ResizeObserver' in window ) { new ResizeObserver( layout ).observe( stage ); } else { window.addEventListener( 'resize', layout ); }
	if ( document.fonts && document.fonts.ready ) { document.fonts.ready.then( layout ); }
	poster.onload = function () { dirty = true; };
	window.addEventListener( 'scroll', wake, { passive: true } );
	measure();
	P = target;
	wake();
} )();
