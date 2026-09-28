/* ==========================================================================
   SERVICES · REEL — scroll-driven 35 mm reel (WebGL2, no dependencies)
   --------------------------------------------------------------------------
   Idea (after Immersive Garden's "Cartier in Time"): the services sit as
   frames on a reel of film. Scrolling pushes the camera in to the frame at
   twelve o'clock, then turns the reel frame by frame like a clock hand;
   at the end the camera pulls back and the page carries on.

   How it is built, and why:

   • Pinning is native CSS `position: sticky`, not a JS pin. No pin-spacer,
     no jump on iOS when the address bar shows or hides.
   • The whole reel is ONE full-screen triangle and ONE fragment shader.
     Each pixel is mapped back into reel space (polar coordinates), so the
     strip, perforations, frame corners and edge print are computed rather
     than drawn from meshes — they stay razor sharp at any zoom, from the
     whole reel down to a single frame filling the screen.
   • Stills live in a single texture array, one layer per service, with
     mipmaps; sampling uses analytic gradients so the reel has no seam.
   • Text is real HTML over the canvas: indexable, selectable, readable by
     screen readers. WebGL only paints the picture.
   • The loop sleeps while the section is off screen. On screen but still,
     it redraws only the film grain, 12 times a second.
   • No WebGL2, reduced motion, or a shader that fails to compile → the
     script leaves the section alone and the same HTML renders as a static
     contact sheet (services-reel.css, "static mode").
   ========================================================================== */
( function () {
	'use strict';

	var sec = document.querySelector( '[data-lmf-reel]' );
	if ( ! sec ) { return; }

	var stage  = sec.querySelector( '.reel-stage' );
	var canvas = sec.querySelector( '.reel-gl' );
	var hub    = sec.querySelector( '.reel-hub' );
	var items  = [].slice.call( sec.querySelectorAll( '.reel-item' ) );
	var N      = items.length;
	if ( ! stage || ! canvas || ! N ) { return; }

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }

	var gl = null;
	try {
		gl = canvas.getContext( 'webgl2', {
			alpha: false, antialias: false, depth: false, stencil: false,
			premultipliedAlpha: false, powerPreference: 'high-performance'
		} );
	} catch ( e ) { gl = null; }
	if ( ! gl ) { return; }

	/* ----------------------------------------------------------------------
	   1. TIMELINE — lengths in screens of scroll
	   ---------------------------------------------------------------------- */
	var ZOOM_IN  = 1.0;   // whole reel → first frame
	var STEP     = 0.8;   // one frame → the next
	var HOLD_END = 0.5;   // lingering on the last frame
	var ZOOM_OUT = 0.9;   // back out to the whole reel
	var SWING    = 1.2;   // how far the reel swings in / out, in frames
	var T = ZOOM_IN + ( N - 1 ) * STEP + HOLD_END + ZOOM_OUT;

	/* ----------------------------------------------------------------------
	   2. REEL GEOMETRY — world units, one unit = frame height
	   ---------------------------------------------------------------------- */
	var AP_W  = 1.5;                 // 3:2 aperture
	var GAP   = 0.1;                 // frame line between frames
	var PITCH = AP_W + GAP;
	var BAND  = 0.8;                 // half-width of the strip (perfs + edge print)
	var REPS  = Math.max( 2, Math.ceil( 18 / N ) );   // wind the services round
	var M     = N * REPS;            // slots on the reel (≥ 18 keeps the curve gentle)
	var R     = M * PITCH / ( 2 * Math.PI );
	var MAXN  = 24;                  // uniform array size in the shader

	/* ----------------------------------------------------------------------
	   3. SHADER
	   ---------------------------------------------------------------------- */
	var VS = '#version 300 es\n' +
		'void main(){vec2 p=vec2((gl_VertexID<<1)&2,gl_VertexID&2);gl_Position=vec4(p*2.-1.,0.,1.);}';

	var FS = [
		'#version 300 es',
		'precision highp float;',
		'precision highp sampler2DArray;',
		'uniform vec2 uRes;uniform float uDpr;uniform float uS;uniform vec2 uP;uniform vec2 uW;',
		'uniform float uF;uniform float uM;uniform float uN;uniform float uR;uniform float uPitch;uniform float uAp;uniform float uBand;',
		'uniform float uVel;uniform float uTime;uniform float uZoom;uniform float uScrim;',
		'uniform sampler2DArray uTex;uniform sampler2D uEdge;uniform float uEdgeOn;',
		'uniform float uReady[' + MAXN + '];',
		'out vec4 o;',
		'const vec3 PAPER=vec3(.969,.961,.937);const vec3 INK=vec3(.067);const vec3 LEMON=vec3(.961,.722,.180);',
		'float hash(vec2 p){p=fract(p*vec2(123.34,456.21));p+=dot(p,p+45.32);return fract(p.x*p.y);}',
		'float sdBox(vec2 p,vec2 b,float r){vec2 q=abs(p)-b+r;return length(max(q,0.))+min(max(q.x,q.y),0.)-r;}',
		'void main(){',
		'  vec2 px=vec2(gl_FragCoord.x,uRes.y-gl_FragCoord.y);',
		'  vec2 w=uW+(px-uP)/uS*vec2(1.,-1.);',            // pixel → reel space
		'  float aa=1./uS;',                                  // one device pixel, in world units
		'  float r=length(w);',
		'  float ang=atan(w.x,w.y);',                         // clockwise from twelve o'clock
		'  float sf=ang/(6.2831853/uM)+uF;',                  // continuous slot coordinate
		'  float k=floor(sf+.5);',
		'  float u=(sf-k)*uPitch;',                           // along the strip
		'  float v=r-uR;',                                    // across it, outward positive
		'  vec3 col=PAPER;float grain=.016;',
		'  float band=1.-smoothstep(-aa,aa,abs(v)-uBand);',
		'  if(band>0.){',
		'    vec3 film=INK;',
		'    float pp=uPitch*.25;',                           // four perforations per frame
		'    float uu=mod(u+pp*.5,pp)-pp*.5;',
		'    float dPerf=sdBox(vec2(uu,abs(v)-.622),vec2(.064,.046),.02);',   // 35 mm proportions
		'    film=mix(film,PAPER,1.-smoothstep(-aa,aa,dPerf));',
		'    float slot=mod(k,uM);float layer=mod(slot,uN);',
		'    float ey=(v-.69)/.068;float ex=(u+uAp*.5)/.952;', // edge print, outside the perfs
		'    if(uEdgeOn>0.&&ey>0.&&ey<1.&&ex>0.&&ex<1.){',
		'      float t=textureLod(uEdge,vec2(ex,(layer+1.-ey)/uN),0.).a;',
		'      float legible=smoothstep(4.,9.,.068*uS/uDpr);',
		'      film=mix(film,LEMON,t*.82*legible);',
		'    }',
		'    float dAp=sdBox(vec2(u,v),vec2(uAp*.5,.5),.035);',
		'    float ap=1.-smoothstep(-aa,aa,dAp);',
		'    if(ap>0.){',
		'      vec2 uv=vec2(u/uAp+.5,.5-v);',
		'      uv=(uv-.5)/1.08+.5;',                          // overscan for the parallax
		'      float off=k-uF;',
		'      uv.x+=clamp(-off*.035,-.035,.035);',
		'      float g=aa/1.08;',
		'      vec2 dx=vec2(g/uAp,0.),dy=vec2(0.,g);',          // analytic gradients: no seam at six o'clock
		'      float du=clamp(uVel,-8.,8.)*.0026;',              // colour split with speed
		'      vec3 img;',
		'      img.r=textureGrad(uTex,vec3(uv+vec2(du,0.),layer),dx,dy).r;',
		'      img.g=textureGrad(uTex,vec3(uv,layer),dx,dy).g;',
		'      img.b=textureGrad(uTex,vec3(uv-vec2(du,0.),layer),dx,dy).b;',
		'      img=mix(vec3(.13),img,uReady[int(layer)]);',   // develops in when loaded
		'      float lit=mix(1.,.2,clamp(abs(off),0.,1.)*uZoom);', // only the gate is lit
		'      img*=lit;',
		'      img*=mix(1.,.34,smoothstep(.28,1.,uv.y)*uScrim*uZoom);', // under the caption
		'      img*=mix(1.,.8,smoothstep(.55,0.,uv.x)*uScrim*uZoom);',
		'      film=mix(film,img,ap);',
		'    }',
		'    col=mix(col,film,band);grain=mix(grain,.045,band);',
		'  }',
		'  float n=hash(floor(px/uDpr)+floor(uTime*12.)*vec2(17.,31.))-.5;',
		'  o=vec4(col+n*grain,1.);',
		'}'
	].join( '\n' );

	function compile( type, src ) {
		var s = gl.createShader( type );
		gl.shaderSource( s, src );
		gl.compileShader( s );
		if ( ! gl.getShaderParameter( s, gl.COMPILE_STATUS ) ) {
			if ( window.console ) { console.warn( '[lmf-reel]', gl.getShaderInfoLog( s ) ); }
			return null;
		}
		return s;
	}
	var vs = compile( gl.VERTEX_SHADER, VS ), fs = compile( gl.FRAGMENT_SHADER, FS );
	if ( ! vs || ! fs ) { return; }
	var prog = gl.createProgram();
	gl.attachShader( prog, vs );
	gl.attachShader( prog, fs );
	gl.linkProgram( prog );
	if ( ! gl.getProgramParameter( prog, gl.LINK_STATUS ) ) { return; }
	gl.useProgram( prog );

	var U = {};
	[ 'uRes', 'uDpr', 'uS', 'uP', 'uW', 'uF', 'uM', 'uN', 'uR', 'uPitch', 'uAp', 'uBand',
	  'uVel', 'uTime', 'uZoom', 'uScrim', 'uTex', 'uEdge', 'uEdgeOn', 'uReady' ].forEach( function ( n ) {
		U[ n ] = gl.getUniformLocation( prog, n );
	} );
	gl.uniform1f( U.uM, M );
	gl.uniform1f( U.uN, N );
	gl.uniform1f( U.uR, R );
	gl.uniform1f( U.uPitch, PITCH );
	gl.uniform1f( U.uAp, AP_W );
	gl.uniform1f( U.uBand, BAND );
	gl.uniform1i( U.uTex, 0 );
	gl.uniform1i( U.uEdge, 1 );
	gl.bindVertexArray( gl.createVertexArray() );

	/* From here on the section is live. */
	sec.classList.add( 'is-live' );
	sec.style.setProperty( '--reel-len', T.toFixed( 3 ) );

	/* ----------------------------------------------------------------------
	   4. STILLS — one texture array layer per service, loaded on approach
	   ---------------------------------------------------------------------- */
	var coarse = window.matchMedia( '(pointer: coarse)' ).matches;
	var TW = coarse ? 768 : 1200, TH = TW * 2 / 3;
	var tex = gl.createTexture();
	gl.activeTexture( gl.TEXTURE0 );
	gl.bindTexture( gl.TEXTURE_2D_ARRAY, tex );
	var levels = Math.floor( Math.log2( TW ) ) + 1;
	gl.texStorage3D( gl.TEXTURE_2D_ARRAY, levels, gl.RGBA8, TW, TH, N );
	gl.texParameteri( gl.TEXTURE_2D_ARRAY, gl.TEXTURE_MIN_FILTER, gl.LINEAR_MIPMAP_LINEAR );
	gl.texParameteri( gl.TEXTURE_2D_ARRAY, gl.TEXTURE_MAG_FILTER, gl.LINEAR );
	gl.texParameteri( gl.TEXTURE_2D_ARRAY, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE );
	gl.texParameteri( gl.TEXTURE_2D_ARRAY, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE );

	var ready = new Float32Array( MAXN );      // 0 → 1 as each still "develops"
	var target = new Float32Array( MAXN );
	var scratch = document.createElement( 'canvas' );
	scratch.width = TW; scratch.height = TH;
	var sctx = scratch.getContext( '2d' );
	var loading = false;

	function loadStills() {
		if ( loading ) { return; }
		loading = true;
		items.forEach( function ( li, i ) {
			var src = li.getAttribute( 'data-img' );
			if ( ! src || i >= MAXN ) { return; }
			var img = new Image();
			img.decoding = 'async';
			/* Картинка с другого домена (CDN, медиатека на поддомене) без
			   CORS «заражает» холст, и WebGL откажется её брать. Просим CORS
			   явно; если сервер не разрешит — кадр останется пустым, а не
			   сломает всю плёнку. */
			try {
				if ( new URL( src, location.href ).origin !== location.origin && src.indexOf( 'data:' ) !== 0 ) {
					img.crossOrigin = 'anonymous';
				}
			} catch ( e ) {}
			img.onload = function () {
				// cover-crop to exactly 3:2
				var s = Math.max( TW / img.naturalWidth, TH / img.naturalHeight );
				var w = img.naturalWidth * s, h = img.naturalHeight * s;
				sctx.clearRect( 0, 0, TW, TH );
				sctx.drawImage( img, ( TW - w ) / 2, ( TH - h ) / 2, w, h );
				gl.activeTexture( gl.TEXTURE0 );
				gl.bindTexture( gl.TEXTURE_2D_ARRAY, tex );
				try {
					gl.texSubImage3D( gl.TEXTURE_2D_ARRAY, 0, 0, 0, i, TW, TH, 1, gl.RGBA, gl.UNSIGNED_BYTE, scratch );
					gl.generateMipmap( gl.TEXTURE_2D_ARRAY );
					target[ i ] = 1;
				} catch ( e ) {
					if ( window.console ) { console.warn( '[lmf-reel] still not usable:', src ); }
				}
				wake();
			};
			img.src = src;
		} );
	}

	/* ----------------------------------------------------------------------
	   5. EDGE PRINT — "LMF 5219 ▸ 01 · BRAND FILMS", lemon, on the outer edge
	   ---------------------------------------------------------------------- */
	var edge = gl.createTexture();
	var edgeOn = 0;
	function buildEdge() {
		var cw = 896, ch = 66;
		var c = document.createElement( 'canvas' );
		c.width = cw; c.height = ch * N;
		var x = c.getContext( '2d' );
		x.fillStyle = '#fff';
		x.textBaseline = 'middle';
		items.forEach( function ( li, i ) {
			var t = li.querySelector( '.reel-title' );
			var label = 'LMF 5219  ▸ ' + ( i + 1 < 10 ? '0' : '' ) + ( i + 1 ) +
				'   ' + ( t ? t.textContent.trim().toUpperCase() : '' );
			var size = 40;
			x.font = '500 ' + size + 'px Montserrat, Arial, sans-serif';
			var wdt = x.measureText( label ).width;
			if ( wdt > cw - 8 ) {
				size = Math.floor( size * ( cw - 8 ) / wdt );
				x.font = '500 ' + size + 'px Montserrat, Arial, sans-serif';
			}
			x.fillText( label, 4, i * ch + ch / 2 );
		} );
		gl.activeTexture( gl.TEXTURE1 );
		gl.bindTexture( gl.TEXTURE_2D, edge );
		gl.texImage2D( gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, c );
		gl.texParameteri( gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR );
		gl.texParameteri( gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR );
		gl.texParameteri( gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE );
		gl.texParameteri( gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE );
		edgeOn = 1;
		wake();
	}
	// Montserrat must be in before the text is baked into a texture
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( buildEdge );
	} else {
		buildEdge();
	}

	/* ----------------------------------------------------------------------
	   6. LAYOUT — camera at both ends of the zoom, caption placement
	   ---------------------------------------------------------------------- */
	var L = {};          // current layout, CSS px
	var dpr = 1;
	var snaps = [];

	function navH() {
		return parseFloat( getComputedStyle( document.documentElement ).getPropertyValue( '--nav-h' ) ) || 72;
	}
	function layout() {
		var vw = stage.clientWidth, vh = stage.clientHeight, nh = navH();
		var mob = vw < 900 && vh > vw * 1.15;           // portrait phone / tablet
		L = { vw: vw, vh: vh, mob: mob };

		// whole reel: fits the smaller side, centred below the header;
		// on a phone it runs a touch past the edges — bigger, more physical
		L.S0 = ( mob ? 0.53 * vw : 0.43 * Math.min( vw, vh - nh * 0.5 ) ) / ( R + BAND );
		L.C0 = [ vw / 2, ( vh + nh ) / 2 ];

		if ( mob ) {
			// frame on top, caption under the strip on the reel's paper centre;
			// the pair is centred in the space below the header
			var capH = 250, gapC = 22;                     // caption ≈ 250 px
			var room = vh - nh - 32 - capH - gapC;
			L.S1 = Math.min( 0.92 * vw / AP_W, room / ( 2 * BAND ) );
			var block = 2 * BAND * L.S1 + gapC + capH;
			var top0 = nh + Math.max( 16, ( vh - nh - block ) / 2 );
			L.F1 = [ vw / 2, top0 + BAND * L.S1 ];
			sec.style.setProperty( '--copy-top', ( L.F1[ 1 ] + BAND * L.S1 + gapC ).toFixed( 1 ) + 'px' );
		} else {
			// frame large and central; perforations stay in view top and bottom
			L.S1 = Math.min( 0.62 * vw / AP_W, ( vh - nh - 32 ) / ( 2 * BAND ) );
			L.F1 = [ vw / 2, nh + ( vh - nh ) / 2 ];
			var fw = AP_W * L.S1, fh = L.S1;
			sec.style.setProperty( '--copy-l', ( L.F1[ 0 ] - fw / 2 + fw * 0.055 ).toFixed( 1 ) + 'px' );
			sec.style.setProperty( '--copy-b', ( vh - ( L.F1[ 1 ] + fh / 2 ) + fh * 0.08 ).toFixed( 1 ) + 'px' );
			sec.style.setProperty( '--copy-w', ( fw * 0.6 ).toFixed( 1 ) + 'px' );
			// a short screen (phone on its side) has no room for the description
			// inside the frame: number, name and the button only
			var shortFrame = fh < 330;
			sec.classList.toggle( 'is-short', shortFrame );
			sec.style.setProperty( '--title-fs', ( shortFrame ? Math.max( 20, fh * 0.16 ) : Math.max( 26, fh * 0.13 ) ).toFixed( 1 ) + 'px' );
			if ( shortFrame ) {
				sec.style.setProperty( '--copy-w', ( fw * 0.8 ).toFixed( 1 ) + 'px' );
			}
		}
		if ( mob ) { sec.classList.remove( 'is-short' ); }
		sec.classList.toggle( 'is-mob', mob );
		// captions turn about the frame's centre
		sec.style.setProperty( '--fx', L.F1[ 0 ].toFixed( 1 ) + 'px' );
		sec.style.setProperty( '--fy', L.F1[ 1 ].toFixed( 1 ) + 'px' );

		// the reel's paper centre, for the heading in the middle
		sec.style.setProperty( '--hub-w', Math.max( 180, 1.7 * ( R - BAND ) * L.S0 ).toFixed( 0 ) + 'px' );

		// canvas
		dpr = Math.min( window.devicePixelRatio || 1, coarse ? 1.75 : 2 );
		canvas.width = Math.round( vw * dpr );
		canvas.height = Math.round( vh * dpr );
		gl.viewport( 0, 0, canvas.width, canvas.height );

		// touch: settle points at each frame's hold
		snaps.forEach( function ( s ) { s.parentNode && s.parentNode.removeChild( s ); } );
		snaps = [];
		if ( coarse ) {
			for ( var i = 0; i < N; i++ ) {
				var at = ZOOM_IN + i * STEP + ( i < N - 1 ? 0.12 * STEP : 0.2 * HOLD_END );
				var m = document.createElement( 'span' );
				m.className = 'reel-snap';
				m.setAttribute( 'aria-hidden', 'true' );
				m.style.top = ( at * vh ).toFixed( 0 ) + 'px';
				sec.appendChild( m );
				snaps.push( m );
			}
			document.documentElement.classList.add( 'reel-snapping' );
		}
		wake();
	}

	/* ----------------------------------------------------------------------
	   7. STATE — where the camera and the reel are for a given scroll
	   ---------------------------------------------------------------------- */
	function clamp01( x ) { return x < 0 ? 0 : x > 1 ? 1 : x; }
	function ease( x ) { return x < 0.5 ? 4 * x * x * x : 1 - Math.pow( -2 * x + 2, 3 ) / 2; }
	function smooth( a, b, x ) { var t = clamp01( ( x - a ) / ( b - a ) ); return t * t * ( 3 - 2 * t ); }

	function state( s ) {
		var z, f;
		var runEnd = ZOOM_IN + ( N - 1 ) * STEP;
		if ( s < ZOOM_IN ) {
			z = ease( s / ZOOM_IN );
			f = -SWING * ( 1 - z );                       // the reel swings in like a clock hand
		} else if ( s < runEnd ) {
			var local = ( s - ZOOM_IN ) / STEP;
			var i = Math.floor( local ), t = local - i;
			z = 1;
			f = i + ease( smooth( 0.3, 1, t ) );         // hold on a frame, then advance
		} else if ( s < runEnd + HOLD_END ) {
			z = 1; f = N - 1;
		} else {
			var e = clamp01( ( s - runEnd - HOLD_END ) / ZOOM_OUT );
			z = 1 - ease( e );
			f = N - 1 + SWING * e * e;                   // and swings on as it pulls back
		}
		return { z: z, f: f };
	}

	/* ----------------------------------------------------------------------
	   8. LOOP
	   ---------------------------------------------------------------------- */
	var cur = -1, fPrev = 0, vel = 0, last = 0, raf = 0, visible = false, lastGrain = 0;

	function targetS() {
		var top = sec.getBoundingClientRect().top;
		return Math.min( T, Math.max( 0, -top / ( L.vh || 1 ) ) );
	}

	function frame( now ) {
		raf = 0;
		var dt = last ? Math.min( 0.1, ( now - last ) / 1000 ) : 1 / 60;
		last = now;

		var tgt = targetS();
		if ( cur < 0 ) { cur = tgt; }
		cur += ( tgt - cur ) * ( 1 - Math.exp( -dt / 0.085 ) );   // scrubbed, not jerky
		if ( Math.abs( tgt - cur ) < 1e-4 ) { cur = tgt; }

		var st = state( cur );
		var v = ( st.f - fPrev ) / dt;
		fPrev = st.f;
		vel += ( v - vel ) * 0.25;

		var developing = false;
		for ( var i = 0; i < N && i < MAXN; i++ ) {
			if ( ready[ i ] < target[ i ] ) {
				ready[ i ] = Math.min( 1, ready[ i ] + dt / 0.9 );
				developing = true;
			}
		}

		draw( st, now );
		captions( st );

		var moving = cur !== tgt || Math.abs( vel ) > 0.01 || developing;
		if ( visible ) {
			if ( moving ) {
				raf = requestAnimationFrame( frame );
			} else {
				vel = 0;
				// still: only the grain moves, 12 fps
				setTimeout( function () { if ( visible && ! raf ) { raf = requestAnimationFrame( frame ); } }, 83 );
			}
		}
	}

	function wake() {
		if ( visible && ! raf ) { last = 0; raf = requestAnimationFrame( frame ); }
	}

	function draw( st, now ) {
		var z = st.z;
		// scale is interpolated logarithmically — a zoom that feels even
		var S = Math.exp( Math.log( L.S0 ) * ( 1 - z ) + Math.log( L.S1 ) * z );
		// the frame at twelve o'clock, world (0, R), travels from its place on
		// the whole reel to the focal point
		var p0 = [ L.C0[ 0 ], L.C0[ 1 ] - R * L.S0 ];
		var pz = [ p0[ 0 ] + ( L.F1[ 0 ] - p0[ 0 ] ) * z, p0[ 1 ] + ( L.F1[ 1 ] - p0[ 1 ] ) * z ];

		gl.uniform2f( U.uRes, canvas.width, canvas.height );
		gl.uniform1f( U.uDpr, dpr );
		gl.uniform1f( U.uS, S * dpr );
		gl.uniform2f( U.uP, pz[ 0 ] * dpr, pz[ 1 ] * dpr );
		gl.uniform2f( U.uW, 0, R );
		gl.uniform1f( U.uF, st.f );
		gl.uniform1f( U.uVel, vel );
		gl.uniform1f( U.uTime, now / 1000 );
		gl.uniform1f( U.uZoom, z );
		gl.uniform1f( U.uScrim, L.mob ? 0 : 1 );
		gl.uniform1f( U.uEdgeOn, edgeOn );
		gl.uniform1fv( U.uReady, ready );
		gl.activeTexture( gl.TEXTURE0 );
		gl.bindTexture( gl.TEXTURE_2D_ARRAY, tex );
		gl.activeTexture( gl.TEXTURE1 );
		gl.bindTexture( gl.TEXTURE_2D, edge );
		gl.drawArrays( gl.TRIANGLES, 0, 3 );
	}

	/* The caption rides on its frame: the same turn about the reel's centre,
	   expressed in screen space. On a phone the caption sits under the strip,
	   so only the sideways part of the movement is kept, and softened. */
	function ride( slots ) {
		var a = slots * 2 * Math.PI / M;
		var rpx = R * L.S1;
		var dx = rpx * Math.sin( a ), dy = rpx * ( 1 - Math.cos( a ) );
		if ( L.mob ) {
			return 'translate3d(' + ( dx * 0.45 ).toFixed( 1 ) + 'px,0,0)';
		}
		return 'translate3d(' + dx.toFixed( 1 ) + 'px,' + dy.toFixed( 1 ) + 'px,0) rotate(' + a.toFixed( 4 ) + 'rad)';
	}

	var lastOn = -1, lastTone = '';
	function captions( st ) {
		var z = st.z;
		// the cursor turns warm white once the camera is in on the dark film
		var tone = z > 0.5 && ! L.mob ? 'light' : 'dark';
		if ( tone !== lastTone ) { lastTone = tone; sec.setAttribute( 'data-cursor-tone', tone ); }
		// heading in the reel's centre: there while the whole reel is in view
		var h = 1 - smooth( 0, 0.3, z );
		hub.style.opacity = h.toFixed( 3 );
		hub.style.transform = 'translate(-50%,-50%) scale(' + ( 1 - 0.08 * z ).toFixed( 4 ) + ')';
		hub.style.visibility = h < 0.01 ? 'hidden' : 'visible';

		var zIn = smooth( 0.82, 1, z );
		var on = -1;
		for ( var i = 0; i < N; i++ ) {
			var d = st.f - i;
			var a = clamp01( 1 - Math.abs( d ) * 2.4 ) * zIn;
			var li = items[ i ];
			if ( a > 0.01 ) {
				li.classList.add( 'is-on' );
				li.style.opacity = a.toFixed( 3 );
				li.style.transform = ride( -d );
				if ( a > 0.5 ) { on = i; }
			} else if ( li.classList.contains( 'is-on' ) ) {
				li.classList.remove( 'is-on' );
				li.style.opacity = '0';
			}
		}
		if ( on !== lastOn ) {
			lastOn = on;
			sec.setAttribute( 'data-active', on );
		}
	}

	/* ----------------------------------------------------------------------
	   9. WIRING
	   ---------------------------------------------------------------------- */
	var ro;
	if ( window.ResizeObserver ) {
		ro = new ResizeObserver( function () { layout(); } );
		ro.observe( stage );
	} else {
		window.addEventListener( 'resize', layout );
	}
	layout();

	// load stills when the section is a screen and a half away
	if ( window.IntersectionObserver ) {
		new IntersectionObserver( function ( es ) {
			if ( es[ 0 ].isIntersecting ) { loadStills(); }
		}, { rootMargin: '150% 0px' } ).observe( sec );

		new IntersectionObserver( function ( es ) {
			visible = es[ 0 ].isIntersecting;
			// the header drops to 98.5 % only while it is over the WebGL reel
			// (see hero-video.css); everywhere else it stays fully opaque
			document.body.classList.toggle( 'over-gl', visible );
			if ( visible ) { wake(); }
		}, { rootMargin: '10% 0px' } ).observe( sec );
	} else {
		loadStills();
		visible = true;
		wake();
	}

	window.addEventListener( 'scroll', wake, { passive: true } );

	// a lost context (GPU reset, backgrounded tab on mobile) → reload cleanly
	canvas.addEventListener( 'webglcontextlost', function ( e ) { e.preventDefault(); }, false );
	canvas.addEventListener( 'webglcontextrestored', function () { window.location.reload(); }, false );
} )();
