/*!
 * Lemon Mint Films — cinematic preloader
 * Hybrid vector + WebGL. The supplied logo is never tessellated, re-pathed
 * or rasterised: it stays live SVG, so the resting frame IS the artwork.
 * WebGL runs on two canvases around it and supplies only what vector
 * cannot — light, dust, grain and the exposure.
 *
 * No dependencies. Exposes window.LMFPreloader.
 */
(function (window, document) {
"use strict";

const LMFPreloader = (function () {

  /* ---------------- sequence ----------------
     Your artwork is near-black on transparent, so it needs a lit ground
     to read against. That is the staging idea: a dark room, the lamp
     strikes and washes a screen, the camera rolls across it as a
     silhouette, then its own lens throws the beam that exposes the
     wordmark. Nothing about the logo changes — only what is behind it. */
  const SEQ = {
    lamp:     { in: 120,  out: 1150 },   // lamp strikes, screen washes in
    bar:      { in: 300,  out: 900  },   // progress bar fades up
    camera:   { in: 420,  out: 2100 },   // assembly rolls in — 1.68s
    beam:     { in: 1450 },              // lens fires, stays lit through the hold
    beamHead: { in: 1550, out: 2650 },   // the light travelling right
    reveal:   { in: 1950, out: 3500 },   // wordmark exposure — 1.55s
    develop:  { in: 2600 },              // screen lifts toward paper

    holdAt:   3600,      // everything above has played; wait here for load
    tail:     460,       // settle after the reels have stopped

    brakeNominal: 1700,  // preferred deceleration length, solved per reel

    cameraBlurMax: 16,   // px of directional smear at peak velocity
    revealBlurMax: 5,    // optical softness as the type resolves
    focusBlurMax:  0,    // NO defocus — the artwork is sharp from frame one
    focusPush:     1.010,// the depth adjustment is scale only

    idleGrain: 0.045,
    peakGrain: 0.115
  };

  /* ---------------- staging ----------------
     Two ways to light the same sequence, sharing one shader.

     'light'  Brand Guidelines Ed.02, the 1A system: warm white #F7F5EF is
              the ground and stays the ground. The projection reads as a
              POOL of light on the sheet — the lit area holds paper white
              and warms, everything outside it eases down a few percent —
              which is how light on paper actually behaves. The mark never
              sits on a black panel, so the misuse rule holds.

     'room'   The earlier staging: a darkened room, the lamp strikes, the
              screen develops up to paper. More cinematic contrast, but it
              puts the mark on near-black for roughly two seconds. */
  const STAGING = {
    light: {
      room:    [0.969, 0.961, 0.937],   // #F7F5EF from frame one
      ambient: [0.969, 0.961, 0.937],
      paper:   [0.969, 0.961, 0.937],
      light:   [1.000, 0.945, 0.792],   // lemon-warm #F5B82E family
      dimFall:   1.00,    // paper stays paper — no ambient falloff at all
      litFall:   1.00,
      tint:      1.00,    // the pool warms rather than brightens
      pool:      0.10,    // penumbra depth, applied only around the pool
      beamGain:  0.62,
      lensGain:  0.30,
      vigOpen:   0.02,   // all but flat: the ground is the brand's 70%
      vigRest:   0.00,
      dustDark:  1,       // motes must be darker than paper to register
      flash:     0.30
    },
    room: {
      room:    [0.030, 0.027, 0.024],
      ambient: [0.180, 0.166, 0.146],
      paper:   [0.965, 0.953, 0.925],
      light:   [1.000, 0.958, 0.876],
      dimFall:   0.68,    // a lamp does not light a screen evenly
      litFall:   0.74,
      tint:      0.0,
      pool:      0.0,
      beamGain:  0.50,
      lensGain:  0.20,
      vigOpen:   0.50,
      vigRest:   0.035,
      dustDark:  0,
      flash:     0.40
    }
  };

  /* Reel motion is specified as an angular VELOCITY in degrees per
     millisecond, then integrated. That is what lets the hold work: the
     reels can coast for an unknown time and the brake still solves to a
     whole turn. */
  const REELS = {
    large: { start: 800, spinUp: 1500, v0: 0.48 },   // ~1.33 rev/s — slower
    small: { start: 800, spinUp: 1350, v0: 0.72 }    // ~2.0  rev/s — faster
  };

  const E = {
    outQuart:  t => 1 - Math.pow(1 - t, 4),
    outExpo:   t => t >= 1 ? 1 : 1 - Math.pow(2, -10 * t),
    inOutCubic:t => t < .5 ? 4*t*t*t : 1 - Math.pow(-2*t + 2, 3) / 2,
    inOutSine: t => -(Math.cos(Math.PI * t) - 1) / 2,
    smooth:    t => t*t*(3 - 2*t)
  };
  const clamp = (v,a,b) => v < a ? a : v > b ? b : v;
  const seg = (t,s) => clamp((t - s.in) / (s.out - s.in), 0, 1);

  /* ---- reel integrals -------------------------------------------
     Velocity ramps up as smoothstep, coasts flat, then brakes as its
     mirror. Both integrals are closed form, so the motion stays exact
     and — unlike a piecewise position curve — has no velocity step
     anywhere, which is what makes it read as smooth. */
  const upInt    = x => x*x*x - x*x*x*x*0.5;          // ∫ smoothstep
  const brakeInt = x => x - x*x*x + x*x*x*x*0.5;      // ∫ (1 - smoothstep)

  function coastAngle(c, effT, holdMs){
    if (effT <= c.start) return 0;
    const upEnd = c.start + c.spinUp;
    if (effT < upEnd) return c.v0 * c.spinUp * upInt((effT - c.start)/c.spinUp);
    const t = Math.min(effT, SEQ.holdAt);
    return c.v0 * c.spinUp * 0.5
         + c.v0 * (t - upEnd)
         + (effT >= SEQ.holdAt ? c.v0 * holdMs : 0);
  }

  /* Pick the deceleration that lands on an exact multiple of 360 while
     staying closest to the preferred length. The SHAPE never changes —
     only its duration — so the brake always enters at exactly the coast
     speed and always arrives at zero, whatever the hold length was. */
  function solveBrake(c, A0){
    const rest = ((A0 % 360) + 360) % 360;
    const D0 = (360 - rest) % 360;
    let best = null;
    for (let k = 0; k < 10; k++){
      const D = D0 + k*360;
      if (D < 150) continue;                 // too short reads as a stall
      const B = 2*D / c.v0;                  // ∫brake = 0.5  ->  D = v0*B/2
      if (B < 850) continue;
      const cost = Math.abs(B - SEQ.brakeNominal);
      if (!best || cost < best.cost) best = { D, B, cost };
    }
    return best || { D: 360, B: 2*360/c.v0 };
  }

  function reelAngle(c, solve, effT, holdMs){
    if (effT <= SEQ.holdAt || !solve) return coastAngle(c, effT, holdMs);
    const A0 = coastAngle(c, SEQ.holdAt, holdMs);
    const x = clamp((effT - SEQ.holdAt) / solve.B, 0, 1);
    return A0 + c.v0 * solve.B * brakeInt(x);
  }

  /* ================================================================
     GLSL
     ================================================================ */
  const VERT_QUAD = `#version 300 es
  in vec2 aPos;
  void main(){ gl_Position = vec4(aPos, 0.0, 1.0); }`;

  const NOISE = `
  float hash(vec2 p){
    p = fract(p * vec2(443.897, 441.423));
    p += dot(p, p + 19.19);
    return fract((p.x + p.y) * p.x);
  }
  float vnoise(vec2 p){
    vec2 i = floor(p), f = fract(p);
    vec2 u = f*f*(3.0-2.0*f);
    return mix(mix(hash(i), hash(i+vec2(1,0)), u.x),
               mix(hash(i+vec2(0,1)), hash(i+vec2(1,1)), u.x), u.y);
  }
  float fbm(vec2 p){
    float v = 0.0, a = 0.5;
    for(int i=0;i<4;i++){ v += a*vnoise(p); p *= 2.03; a *= 0.5; }
    return v;
  }
  float beam(vec2 P, vec2 O, vec2 D, float halfW0, float spread,
             float reach, float time, float striate){
    vec2 v = P - O;
    float t = dot(v, D);
    if (t < 0.0) return 0.0;
    float perp = abs(v.x*D.y - v.y*D.x);
    float w = halfW0 + t * spread;
    float radial = pow(1.0 - smoothstep(0.0, w, perp), 1.85);
    float head = 1.0 - smoothstep(reach*0.74, reach, t);
    float near = smoothstep(0.0, halfW0*1.1, t);
    float falloff = 1.0 / (1.0 + t*0.0014);
    float n = mix(1.0, 0.58 + 0.72*fbm(vec2(t*0.011 - time*0.16, perp*0.017 + time*0.038)), striate);
    return radial * head * near * falloff * n;
  }`;

  const FRAG_BACK = `#version 300 es
  precision highp float;
  out vec4 outColor;
  uniform vec2  uRes;
  uniform float uDpr, uTime;
  uniform vec2  uOrigin, uDir;
  uniform float uReach, uBeamI, uHalfW, uSpread, uFlicker;
  uniform float uLamp, uDevelop, uGate, uVig, uFlat;
  uniform float uPool, uBeamGain, uLensGain, uDimFall, uLitFall, uTint;
  uniform vec3  uPaper, uRoom, uAmbient, uLight;
  ${NOISE}
  void main(){
    vec2 P  = vec2(gl_FragCoord.x/uDpr, uRes.y - gl_FragCoord.y/uDpr);
    vec2 uv = P / uRes;
    vec2 q  = (uv - vec2(0.5, 0.48)) * vec2(uRes.x/uRes.y, 1.0);
    float d = length(q);

    float ill = 1.0 - smoothstep(0.18, 1.02, d * 1.22);

    /* Two distinct states, not one lerp to white: the lamp strikes and
       gives a dim WARM screen (a projector running with no film), then
       the exposure develops that up to full paper. Straight black to
       paper passes through a dead neutral grey that reads as a loading
       screen rather than a room. The falloff flattens as it settles, so
       the resting ground is genuinely flat paper. */
    /* The falloff depth is a staging choice, not a constant. In the room
       it is the lamp's uneven throw across a screen; on paper there is no
       lamp and no screen, so it flattens to 1.0 and the ground stays the
       warm white the guideline specifies. Leaving it at 0.68 was what
       turned #F7F5EF into a grey vignette. */
    vec3 dim = uAmbient * mix(uDimFall, 1.0, ill);
    vec3 lit = uPaper   * mix(mix(uLitFall, 1.0, ill), 1.0, uFlat);
    vec3 col = mix(uRoom, dim, clamp(uLamp * uGate, 0.0, 1.0));
    col = mix(col, lit, uDevelop);

    /* airborne haze belongs to the darkened room; on paper there is no
       air to light, and it only muddies the ground */
    float haze = fbm(uv * vec2(3.1, 2.0) + vec2(uTime*0.011, uTime*0.006));
    col += vec3(0.026,0.025,0.022) * haze * (1.0 - uDevelop) * (1.0 - uTint);

    float b    = beam(P, uOrigin, uDir, uHalfW, uSpread, uReach, uTime, 1.0);
    float lens = exp(-length(P - uOrigin) * 0.018);
    float core = b * uBeamI * uFlicker;
    float glow = lens * uBeamI * uFlicker;

    /* Room: plain addition, because there is darkness to lift.
       Paper: brightness has nowhere to go above #F7F5EF, so the pool
       carries HUE instead — it warms toward lemon, which is both visible
       on white and the accent the guideline already allots. */
    col += uLight * core * uBeamGain * (1.0 - uTint);
    col += uLight * glow * uLensGain * (1.0 - uTint);
    col  = mix(col, uLight, clamp((core*uBeamGain + glow*uLensGain) * uTint, 0.0, 1.0));

    /* A LOCAL penumbra, not a global dim. Sampling a wider cone and
       subtracting the core leaves a ring that eases down just around the
       pool; distant paper is untouched, so the ground stays the warm
       white the 1A system asks for instead of drifting grey. */
    if (uPool > 0.0){
      float halo = beam(P, uOrigin, uDir, uHalfW*2.6, uSpread*1.9, uReach*1.08, uTime, 0.0);
      col *= 1.0 - clamp((halo - b) * uPool * uBeamI * uFlicker, 0.0, 0.5);
    }

    col *= 1.0 - smoothstep(0.40, 1.06, d) * uVig;
    outColor = vec4(col, 1.0);
  }`;

  const FRAG_FRONT = `#version 300 es
  precision highp float;
  out vec4 outColor;
  uniform vec2 uRes; uniform float uDpr, uTime, uGrain;
  float hash(vec2 p){
    p = fract(p * vec2(443.897, 441.423));
    p += dot(p, p + 19.19);
    return fract((p.x + p.y) * p.x);
  }
  void main(){
    vec2 P = vec2(gl_FragCoord.x/uDpr, uRes.y - gl_FragCoord.y/uDpr);
    float n = hash(floor(P * 1.25) + vec2(uTime*61.7, uTime*37.3));
    outColor = vec4(vec3(0.5 + (n - 0.5) * uGrain), 1.0);
  }`;

  const VERT_DUST = `#version 300 es
  in float aSeed;
  uniform vec2 uRes, uOrigin, uDir;
  uniform float uReach, uTime, uDpr, uHalfW, uSpread;
  out float vA;
  float h11(float p){ p = fract(p*0.1031); p *= p + 33.33; p *= p + p; return fract(p); }
  void main(){
    float s=aSeed, r1=h11(s*1.7), r2=h11(s*3.1+7.0), r3=h11(s*5.9+13.0);
    float t = fract(r1 + uTime * (0.014 + r2*0.032)) * uReach;
    float w = uHalfW + t * uSpread;
    float lat = (r3*2.0 - 1.0) * w * 0.86 + sin(uTime*(0.4+r2*0.8) + r1*20.0) * w * 0.09;
    vec2 perp = vec2(-uDir.y, uDir.x);
    vec2 pos  = uOrigin + uDir*t + perp*lat;
    vA = clamp(1.0 - abs(lat)/max(w,1.0), 0.0, 1.0)
       * (1.0 - smoothstep(uReach*0.62, uReach, t))
       * smoothstep(0.0, uReach*0.10, t) * (0.30 + r2*0.70);
    vec2 c = (pos/uRes)*2.0 - 1.0; c.y = -c.y;
    gl_Position = vec4(c, 0.0, 1.0);
    gl_PointSize = (0.9 + r2*2.0) * uDpr;
  }`;
  const FRAG_DUST = `#version 300 es
  precision mediump float;
  in float vA; out vec4 outColor;
  uniform float uBeamI, uFlicker, uDustDark; uniform vec3 uLight;
  void main(){
    float a = (1.0 - smoothstep(0.12, 0.5, length(gl_PointCoord - 0.5))) * vA * uBeamI * uFlicker;
    /* bright motes vanish on paper, so on the light staging they invert to
       a warm grey and are drawn with normal alpha rather than additively */
    vec3 c = mix(uLight, vec3(0.44, 0.40, 0.34), uDustDark);
    outColor = vec4(c * a, a * mix(1.0, 0.55, uDustDark));
  }`;

  function compile(gl, type, src){
    const s = gl.createShader(type);
    gl.shaderSource(s, src); gl.compileShader(s);
    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS))
      throw new Error(gl.getShaderInfoLog(s) || 'compile failed');
    return s;
  }
  function program(gl, vs, fs){
    const p = gl.createProgram();
    gl.attachShader(p, compile(gl, gl.VERTEX_SHADER, vs));
    gl.attachShader(p, compile(gl, gl.FRAGMENT_SHADER, fs));
    gl.linkProgram(p);
    if (!gl.getProgramParameter(p, gl.LINK_STATUS))
      throw new Error(gl.getProgramInfoLog(p) || 'link failed');
    return p;
  }
  const uniforms = (gl,p,n) => n.reduce((o,k)=>(o[k]=gl.getUniformLocation(p,k),o),{});

  /* ================================================================
     SVG RIG — wraps your groups, never edits their contents
     ================================================================ */
  const DEFAULT_MAP = {
    camera:'#lmf-camera', reelLarge:'#lmf-reel-large', reelSmall:'#lmf-reel-small',
    /* Optional. Artwork whose wheels overlap has to paint part of one wheel
       behind the other; that part lives in its own group so the paint order
       survives, and it turns locked to the wheel it belongs to. */
    reelSmallBack:'#lmf-reel-small-back',
    wordmark:'#lmf-wordmark', accents:'#lmf-accents', lens:'#lmf-lens'
  };
  const NS = 'http://www.w3.org/2000/svg';
  const el = (n,a) => { const e=document.createElementNS(NS,n); for(const k in a) e.setAttribute(k,a[k]); return e; };
  const wrap = (node,a) => { const g=el('g',a||{}); node.parentNode.insertBefore(g,node); g.appendChild(node); return g; };

  function rigSvg(svg, map){
    const parts = {};
    for (const r in DEFAULT_MAP) parts[r] = map[r] ? svg.querySelector(map[r]) : null;
    if (!parts.camera || !parts.wordmark) throw new Error('missing-groups');

    let defs = svg.querySelector('defs');
    if (!defs){ defs = el('defs'); svg.insertBefore(defs, svg.firstChild); }

    const vb = (svg.getAttribute('viewBox')||'0 0 1000 300').split(/[\s,]+/).map(Number);
    const VBX=vb[0]||0, VBY=vb[1]||0, VBW=vb[2]||1000, VBH=vb[3]||300;

    const fMotion = el('filter', { id:'lmfMotion', x:'-70%', y:'-30%', width:'240%', height:'160%' });
    const blurM = el('feGaussianBlur', { stdDeviation:'0 0', edgeMode:'none' });
    fMotion.appendChild(blurM); defs.appendChild(fMotion);

    const fSoft = el('filter', { id:'lmfSoft', x:'-25%', y:'-60%', width:'150%', height:'220%' });
    const blurS = el('feGaussianBlur', { stdDeviation:'0' });
    fSoft.appendChild(blurS); defs.appendChild(fSoft);

    const wm = parts.wordmark.getBBox();
    const grad = el('linearGradient', { id:'lmfRevealGrad', gradientUnits:'userSpaceOnUse',
                                        x1: wm.x, y1: 0, x2: wm.x + 1, y2: 0 });
    grad.appendChild(el('stop', { offset:'0',    'stop-color':'#fff' }));
    grad.appendChild(el('stop', { offset:'0.55', 'stop-color':'#fff' }));
    grad.appendChild(el('stop', { offset:'1',    'stop-color':'#000' }));
    defs.appendChild(grad);
    const mask = el('mask', { id:'lmfReveal', maskUnits:'userSpaceOnUse',
                              x:VBX-VBW, y:VBY-VBH, width:VBW*3, height:VBH*3 });
    mask.appendChild(el('rect', { x:VBX-VBW, y:VBY-VBH, width:VBW*3, height:VBH*3,
                                  fill:'url(#lmfRevealGrad)' }));
    defs.appendChild(mask);

    const camMove  = wrap(parts.camera, { 'data-lmf':'camera-move' });
    const camBlur  = wrap(parts.camera, { filter:'url(#lmfMotion)' });
    const wordMask = wrap(parts.wordmark, { mask:'url(#lmfReveal)' });
    const wordSoft = wrap(parts.wordmark, { filter:'url(#lmfSoft)' });
    const accMask  = parts.accents ? wrap(parts.accents, { mask:'url(#lmfReveal)' }) : null;

    function pivot(node){
      if (!node) return null;
      const move = wrap(node, { 'data-lmf':'reel-move' });
      const b = node.getBBox();
      return { node, move, cx: b.x + b.width/2, cy: b.y + b.height/2, b };
    }
    const reelL  = pivot(parts.reelLarge);
    const reelS  = pivot(parts.reelSmall);
    const reelSB = pivot(parts.reelSmallBack);
    /* every rotor, so the sites below never have to name them one by one */
    const rotors = [reelL, reelS, reelSB].filter(Boolean);

    const rigOrigin = { x: VBX + VBW*0.15, y: VBY + VBH*0.60 };
    const movers = [camMove].concat(rotors.map(r => r.move));

    const cb = parts.camera.getBBox();
    let ax0=cb.x, ax1=cb.x+cb.width;
    rotors.forEach(r => {
      ax0=Math.min(ax0,r.b.x); ax1=Math.max(ax1,r.b.x+r.b.width); });
    const assembly = { x:ax0, width:ax1-ax0 };

    let lens;
    if (parts.lens){ const b=parts.lens.getBBox(); lens={ x:b.x+b.width/2, y:b.y+b.height/2 }; }
    else lens = { x: cb.x + cb.width*0.98, y: cb.y + cb.height*0.46 };

    return { svg, parts, camMove, camBlur, blurM, blurS, grad, movers, rigOrigin,
             wordMask, wordSoft, accMask, reelL, reelS, reelSB, rotors, lens, assembly,
             wm, VBW, VBH, VBX, VBY,
             toScreen(pt){
               const r = svg.getBoundingClientRect();
               return { x: r.left + (pt.x - VBX)/VBW * r.width,
                        y: r.top  + (pt.y - VBY)/VBH * r.height,
                        scale: r.width / VBW };
             } };
  }

  /* ================================================================
     INSTANCE
     ================================================================ */
  function mount(opts){
    opts = opts || {};
    const stage=opts.stage, logoEl=opts.logo, svg=opts.svg, flash=opts.flash;
    const barEl=opts.bar, fillEl=opts.barFill;
    const map = Object.assign({}, DEFAULT_MAP, opts.map || {});
    const minFill = opts.minFill != null ? opts.minFill : 900;
    const shouldHold = opts.hold !== false;
    const maxWait = opts.maxWait != null ? opts.maxWait : 6000;
    /* Colour overrides from the settings screen land on top of the staging's
       own values. They have to reach the shader, not just the CSS, because
       the GL canvas paints over the stage background. */
    const ST = Object.assign({}, STAGING[opts.staging] || STAGING.light, opts.palette || {});
    const reduced = () => opts.forceReduced ||
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let effT = 0, holdMs = 0;
    let rig;
    try { rig = rigSvg(svg, map); }
    catch(e){ return { error:e.message, destroy(){}, play(){}, seek(){}, resize(){}, setProgress(){} }; }

    /* ---------------- progress ----------------
       The source reports { p, done, parts }. p is what is genuinely
       loaded; done is whether everything the page needs is actually
       usable. They are separate on purpose: p can sit at 0.9 for a while
       and the bar must NOT read 100% until done is true, or the bar is
       telling the visitor something that is not so. */
    let manual = null;          // set via setProgress()
    let progT = 0, progD = 0, srcDone = false, lastParts = null;

    function readSource(){
      if (manual != null) return { p: clamp(manual,0,1), done: manual >= 1 };
      if (opts.source){
        const r = opts.source() || {};
        lastParts = r.parts || null;
        return { p: clamp(r.p||0,0,1), done: !!r.done };
      }
      if (opts.progress){                        // legacy plain-number source
        const v = clamp(opts.progress(),0,1);
        return { p: v, done: v >= 1 };
      }
      const c = document.readyState === 'complete';
      return { p: c ? 1 : document.readyState === 'interactive' ? 0.65 : 0.25, done: c };
    }

    const timedOut = () => shouldHold && holdMs >= maxWait;

    function updateProgress(dt){
      const r = readSource();
      srcDone = r.done;
      /* the bar may only read 100% when the page really is ready */
      progT = (r.done || timedOut()) ? 1 : Math.min(r.p, 0.99);
      const k = 1 - Math.exp(-dt/380);
      let next = progD + (progT - progD) * k;
      next = Math.min(next, progD + dt/minFill);
      progD = clamp(Math.max(progD, next), 0, progT);
      /* an exponential approaches 1 without arriving, which would stall
         the hold forever — close the last sliver outright */
      if (progT - progD < 0.005) progD = progT;
    }
    /* Never trap a visitor behind a preloader. If the media has not
       arrived within maxWait we let go anyway and the hero shows its
       poster — a stalled CDN must not mean a permanently blocked page. */
    const ready = () => !shouldHold || srcDone || timedOut();

    /* ---------------- GL ---------------- */
    let gl=null, gf=null, vaoBack, vaoFront, vaoDust;
    let progBeam,uBeam, progDust,uDust, progFront,uFront;
    const dustN = 210;
    let glOK = false;

    if (!opts.forceNoGL && !reduced()){
      try {
        const A = { alpha:true, antialias:false, depth:false, stencil:false,
                    premultipliedAlpha:true, powerPreference:'high-performance' };
        gl = opts.canvasBack.getContext('webgl2', A);
        gf = opts.canvasFront.getContext('webgl2', A);
        if (!gl || !gf) throw new Error('no-webgl2');
        const quad = (ctx, prog) => {
          const v=ctx.createVertexArray(); ctx.bindVertexArray(v);
          const bb=ctx.createBuffer(); ctx.bindBuffer(ctx.ARRAY_BUFFER,bb);
          ctx.bufferData(ctx.ARRAY_BUFFER,new Float32Array([-1,-1,3,-1,-1,3]),ctx.STATIC_DRAW);
          const l=ctx.getAttribLocation(prog,'aPos');
          ctx.enableVertexAttribArray(l); ctx.vertexAttribPointer(l,2,ctx.FLOAT,false,0,0);
          ctx.bindVertexArray(null); return v;
        };
        progBeam = program(gl, VERT_QUAD, FRAG_BACK);
        uBeam = uniforms(gl, progBeam, ['uRes','uDpr','uTime','uOrigin','uDir','uReach',
                 'uBeamI','uHalfW','uSpread','uFlicker','uLamp','uDevelop','uGate','uVig',
                 'uFlat','uPool','uBeamGain','uLensGain','uDimFall','uLitFall','uTint',
                 'uPaper','uRoom','uAmbient','uLight']);
        vaoBack = quad(gl, progBeam);

        progDust = program(gl, VERT_DUST, FRAG_DUST);
        uDust = uniforms(gl, progDust, ['uRes','uOrigin','uDir','uReach','uTime','uDpr',
                 'uHalfW','uSpread','uBeamI','uFlicker','uLight','uDustDark']);
        vaoDust = gl.createVertexArray(); gl.bindVertexArray(vaoDust);
        const db=gl.createBuffer(), seeds=new Float32Array(dustN);
        for(let i=0;i<dustN;i++) seeds[i]=i*1.6180339887+0.37;
        gl.bindBuffer(gl.ARRAY_BUFFER,db); gl.bufferData(gl.ARRAY_BUFFER,seeds,gl.STATIC_DRAW);
        const sl=gl.getAttribLocation(progDust,'aSeed');
        gl.enableVertexAttribArray(sl); gl.vertexAttribPointer(sl,1,gl.FLOAT,false,0,0);
        gl.bindVertexArray(null);

        progFront = program(gf, VERT_QUAD, FRAG_FRONT);
        uFront = uniforms(gf, progFront, ['uRes','uDpr','uTime','uGrain']);
        vaoFront = quad(gf, progFront);
        glOK = true;
      } catch(e){ glOK = false; }
    }
    if (!glOK){ opts.canvasBack.style.display='none'; opts.canvasFront.style.display='none'; }

    /* ---------------- sizing + dynamic travel ---------------- */
    let W=0,H=0,DPR=1,travelUser=0;
    function resize(){
      const r = stage.getBoundingClientRect();
      W=Math.max(1,r.width); H=Math.max(1,r.height);
      DPR=Math.min(window.devicePixelRatio||1, 2);
      const sr = rig.svg.getBoundingClientRect();
      const sc = sr.width / rig.VBW;
      if (sc > 0){
        /* start the assembly just past the viewport edge whatever the
           viewport is — a fixed fraction of the viewBox strands it
           on-screen at wide sizes */
        const rightPx = sr.left + (rig.assembly.x + rig.assembly.width - rig.VBX) * sc;
        travelUser = -(rightPx + W * 0.10) / sc;
      }
      if (!glOK) return;
      [[opts.canvasBack, gl],[opts.canvasFront, gf]].forEach(([c,ctx])=>{
        const w=Math.round(W*DPR), h=Math.round(H*DPR);
        if (c.width!==w||c.height!==h){ c.width=w; c.height=h; }
        ctx.viewport(0,0,w,h);
      });
    }
    const ro = new ResizeObserver(resize); ro.observe(stage); resize();

    /* ---------------- brake solve (memoised per hold length) ------ */
    let solveKey = null, solveL = null, solveS = null, brakeEnd = SEQ.brakeNominal;
    function ensureSolve(holdMs){
      if (solveKey === holdMs) return;
      solveKey = holdMs;
      solveL = rig.reelL ? solveBrake(REELS.large, coastAngle(REELS.large, SEQ.holdAt, holdMs)) : null;
      solveS = rig.reelS ? solveBrake(REELS.small, coastAngle(REELS.small, SEQ.holdAt, holdMs)) : null;
      brakeEnd = Math.max(solveL ? solveL.B : 0, solveS ? solveS.B : 0) || SEQ.brakeNominal;
    }
    const totalFor = holdMs => { ensureSolve(holdMs); return SEQ.holdAt + brakeEnd + SEQ.tail; };

    /* ---------------- filters / transforms on and off -------------
       Even fully revealed, a mask or filter routes the artwork through
       an offscreen buffer, and a transform — even an identity one —
       keeps it on the transformed raster path. Both shift edge
       antialiasing. Detaching them at lockup returns the vectors to the
       exact path the browser would use for the untouched file. */
    function armFilters(on){
      const set=(n,a,v)=>{ if(!n) return; on ? n.setAttribute(a,v) : n.removeAttribute(a); };
      set(rig.camBlur,'filter','url(#lmfMotion)');
      set(rig.wordSoft,'filter','url(#lmfSoft)');
      set(rig.wordMask,'mask','url(#lmfReveal)');
      set(rig.accMask,'mask','url(#lmfReveal)');
      logoEl.style.willChange = on ? 'transform, filter' : 'auto';
    }
    function clearTransforms(){
      const wipe = n => { if(!n) return;
        n.style.transform=''; n.style.transformBox=''; n.style.transformOrigin=''; };
      rig.movers.forEach(wipe); wipe(rig.wordMask);
      rig.rotors.forEach(r => wipe(r.node));
    }
    function repivot(){
      rig.rotors.forEach(o => {
        o.node.style.transformBox='view-box';
        o.node.style.transformOrigin=`${o.cx}px ${o.cy}px`; });
      rig.movers.forEach(m => { m.style.transformBox='view-box';
        m.style.transformOrigin=`${rig.rigOrigin.x}px ${rig.rigOrigin.y}px`; });
    }

    /* ---------------- frame ---------------- */
    /* declared up here because the progress helpers close over holdMs */
    let raf=0, playing=false, held=false;
    let startNow=0, lastNow=0, lastX=null, lastT=null;

    function applyFrame(t, hold){
      ensureSolve(hold);
      const total = SEQ.holdAt + brakeEnd + SEQ.tail;
      const tau = Math.max(0, t - SEQ.holdAt);      // time into the outro
      const secs = (t + hold)/1000;
      if (t < total) repivot();

      /* --- assembly rolls in ------------------------------------- */
      const pc = seg(t, SEQ.camera), ec = E.outQuart(pc);
      const camX = (1-ec) * travelUser;
      const camScale = 1 + (1-ec)*0.024;
      const camY = (1-ec) * rig.VBH * 0.008;
      const body = `translate(${camX}px, ${camY}px) scale(${camScale})`;
      for (let i=0;i<rig.movers.length;i++) rig.movers[i].style.transform = body;

      let vel = 0;
      if (lastX!==null && lastT!==null && t>lastT) vel = Math.abs(camX-lastX)/(t-lastT);
      lastX=camX; lastT=t;
      rig.blurM.setAttribute('stdDeviation',
        `${(clamp(vel*4.4,0,SEQ.cameraBlurMax)*(pc<1?1:0)).toFixed(2)} 0`);

      /* --- reels: integrated velocity, brake solved to a whole turn -- */
      if (rig.reelL) rig.reelL.node.style.transform =
        `rotate(${reelAngle(REELS.large, solveL, t, hold).toFixed(3)}deg)`;
      if (rig.reelS || rig.reelSB){
        /* one angle, both halves of the small wheel — they are one wheel */
        const aS = `rotate(${reelAngle(REELS.small, solveS, t, hold).toFixed(3)}deg)`;
        if (rig.reelS)  rig.reelS.node.style.transform  = aS;
        if (rig.reelSB) rig.reelSB.node.style.transform = aS;
      }

      /* --- beam --------------------------------------------------- */
      const pbh = E.outExpo(seg(t, SEQ.beamHead));
      let beamI = 0;
      if (t >= SEQ.beam.in){
        const rise = clamp((t - SEQ.beam.in)/380, 0, 1);
        const fadeFrom = SEQ.holdAt + brakeEnd*0.18, fadeTo = SEQ.holdAt + brakeEnd*0.85;
        const fall = 1 - clamp((t - fadeFrom)/(fadeTo - fadeFrom), 0, 1);
        beamI = E.smooth(rise) * fall;
      }
      /* arc instability. Gaussian dips, not stepped multipliers — the
         steps were the other thing reading as a jolt. */
      let flick = 1;
      if (beamI > 0){
        flick = 0.945 + 0.055*Math.sin(secs*39.0)*Math.sin(secs*11.3);
        const since = t - SEQ.beam.in;
        flick *= 1 - 0.50*Math.exp(-Math.pow((since-40)/46, 2))
                   - 0.26*Math.exp(-Math.pow((since-150)/62, 2));
      }

      /* --- wordmark exposure -------------------------------------- */
      const pr = seg(t, SEQ.reveal), er = E.inOutCubic(pr);
      const feather = Math.max(rig.wm.width*0.16, rig.VBW*0.02);
      const x1 = rig.wm.x - feather + er*(rig.wm.width + feather*2.2);
      rig.grad.setAttribute('x1', x1.toFixed(2));
      rig.grad.setAttribute('x2', (x1+feather).toFixed(2));
      /* the type resolves well before the edge finishes travelling, so
         nothing sits soft on screen for long */
      rig.blurS.setAttribute('stdDeviation',
        ((1 - E.outQuart(clamp(pr/0.6,0,1))) * SEQ.revealBlurMax * (rig.VBH/160)).toFixed(2));

      /* --- depth: scale only, no defocus at any point -------------- */
      const settle = clamp(tau/Math.max(brakeEnd,1), 0, 1);
      const push = 1 + (SEQ.focusPush - 1) * (1 - E.outQuart(settle));
      const gb = SEQ.focusBlurMax * (1 - E.outQuart(settle));

      if (t >= total){
        logoEl.style.filter='none'; logoEl.style.transform='none';
        rig.grad.setAttribute('x1',(rig.VBX+rig.VBW*4).toFixed(2));
        rig.grad.setAttribute('x2',(rig.VBX+rig.VBW*4+1).toFixed(2));
        rig.blurS.setAttribute('stdDeviation','0');
        rig.blurM.setAttribute('stdDeviation','0 0');
        clearTransforms();
      } else {
        logoEl.style.filter = gb > 0.02 ? `blur(${gb.toFixed(2)}px)` : 'none';
        logoEl.style.transform = `scale(${push.toFixed(5)})`;
      }

      /* --- progress bar -------------------------------------------
         The bar shows whichever is FURTHER BEHIND: the sequence or the
         actual loading. Reporting load alone was wrong — on a warm cache
         it is already 1, so the bar raced to 100% in under a second and
         then sat there for the rest of the animation. Gating it by the
         timeline means a loaded page fills the bar smoothly across the
         intro and arrives at 100% exactly at the hold point, while a slow
         page is held back by the real figure instead. Both inputs only
         ever increase, so the minimum of them never runs backwards. */
      if (barEl){
        const fadeIn  = E.inOutSine(seg(t, SEQ.bar));
        const fadeOut = 1 - clamp((tau - brakeEnd*0.12)/(brakeEnd*0.42), 0, 1);
        barEl.style.opacity = (fadeIn * fadeOut).toFixed(3);

        const timelineP = clamp((t - SEQ.bar.in)/(SEQ.holdAt - SEQ.bar.in), 0, 1);
        const shown = t >= SEQ.holdAt
          ? (ready() ? 1 : progD)          // holding: the real figure drives
          : Math.min(timelineP, progD);    // intro: whichever lags
        if (fillEl) fillEl.style.width = (clamp(shown,0,1)*100).toFixed(2) + '%';
        barEl.setAttribute('aria-valuenow', Math.round(clamp(shown,0,1)*100));
      }

      /* --- shutter ------------------------------------------------ */
      let fl = 0;
      const flashAt = brakeEnd - 90, flashDur = 110;
      if (tau >= flashAt && tau <= flashAt + flashDur + 190){
        const p = (tau - flashAt)/flashDur;
        fl = p <= 1 ? Math.pow(Math.sin(clamp(p,0,1)*Math.PI), 0.8)*ST.flash
                    : Math.max(0, 1 - (tau - flashAt - flashDur)/190)*0.06;
      }
      if (flash) flash.style.opacity = fl.toFixed(3);

      /* --- GL ----------------------------------------------------- */
      if (glOK){
        const o  = rig.toScreen(rig.lens);
        const sr = stage.getBoundingClientRect();
        const ox = o.x - sr.left, oy = o.y - sr.top;
        const tgt = rig.toScreen({ x: rig.wm.x + rig.wm.width, y: rig.wm.y + rig.wm.height/2 });
        let dx=(tgt.x-sr.left)-ox, dy=(tgt.y-sr.top)-oy;
        const dl=Math.hypot(dx,dy)||1; dx/=dl; dy/=dl;

        const reach  = dl*(0.26 + 0.80*pbh);
        const halfW  = Math.max(8, (rig.parts.camera.getBBox().height*0.42) * o.scale);
        const spread = 0.17;

        const lamp    = E.inOutSine(seg(t, SEQ.lamp));
        /* the screen keeps developing through the outro */
        const devEnd  = SEQ.holdAt + brakeEnd*0.55;
        const develop = E.inOutCubic(clamp((t - SEQ.develop.in)/(devEnd - SEQ.develop.in), 0, 1));
        const flat    = E.outQuart(clamp((tau - brakeEnd*0.5)/(brakeEnd*0.5 + SEQ.tail), 0, 1));
        const gate    = 1 + (Math.sin(secs*27.0)*0.008 + Math.sin(secs*6.1)*0.012)*(1-develop);
        const vig     = ST.vigOpen*(1-develop) + ST.vigRest*develop*(1-flat);
        const grain   = SEQ.idleGrain + (SEQ.peakGrain-SEQ.idleGrain)*(1-flat);

        gl.useProgram(progBeam);
        gl.uniform2f(uBeam.uRes,W,H); gl.uniform1f(uBeam.uDpr,DPR);
        gl.uniform1f(uBeam.uTime,secs);
        gl.uniform2f(uBeam.uOrigin,ox,oy); gl.uniform2f(uBeam.uDir,dx,dy);
        gl.uniform1f(uBeam.uReach,reach); gl.uniform1f(uBeam.uBeamI,beamI);
        gl.uniform1f(uBeam.uHalfW,halfW); gl.uniform1f(uBeam.uSpread,spread);
        gl.uniform1f(uBeam.uFlicker,flick);
        gl.uniform1f(uBeam.uLamp,lamp); gl.uniform1f(uBeam.uDevelop,develop);
        gl.uniform1f(uBeam.uGate,gate); gl.uniform1f(uBeam.uVig,vig);
        gl.uniform1f(uBeam.uFlat,flat);
        gl.uniform1f(uBeam.uPool,ST.pool);
        gl.uniform1f(uBeam.uBeamGain,ST.beamGain);
        gl.uniform1f(uBeam.uLensGain,ST.lensGain);
        gl.uniform1f(uBeam.uDimFall,ST.dimFall);
        gl.uniform1f(uBeam.uLitFall,ST.litFall);
        gl.uniform1f(uBeam.uTint,ST.tint);
        gl.uniform3fv(uBeam.uPaper,ST.paper); gl.uniform3fv(uBeam.uRoom,ST.room);
        gl.uniform3fv(uBeam.uAmbient,ST.ambient); gl.uniform3fv(uBeam.uLight,ST.light);
        gl.bindVertexArray(vaoBack); gl.disable(gl.BLEND);
        gl.drawArrays(gl.TRIANGLES,0,3);

        if (beamI > 0.01){
          gl.useProgram(progDust);
          gl.uniform2f(uDust.uRes,W,H); gl.uniform1f(uDust.uDpr,DPR);
          gl.uniform1f(uDust.uTime,secs);
          gl.uniform2f(uDust.uOrigin,ox,oy); gl.uniform2f(uDust.uDir,dx,dy);
          gl.uniform1f(uDust.uReach,reach); gl.uniform1f(uDust.uHalfW,halfW);
          gl.uniform1f(uDust.uSpread,spread); gl.uniform1f(uDust.uBeamI,beamI);
          gl.uniform1f(uDust.uFlicker,flick); gl.uniform3fv(uDust.uLight,ST.light);
          gl.uniform1f(uDust.uDustDark, ST.dustDark);
          gl.enable(gl.BLEND);
          /* additive motes on a dark ground, ordinary alpha on paper */
          if (ST.dustDark) gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
          else             gl.blendFunc(gl.SRC_ALPHA, gl.ONE);
          gl.bindVertexArray(vaoDust); gl.drawArrays(gl.POINTS,0,dustN);
          gl.disable(gl.BLEND);
        }

        gf.useProgram(progFront);
        gf.uniform2f(uFront.uRes,W,H); gf.uniform1f(uFront.uDpr,DPR);
        gf.uniform1f(uFront.uTime,secs); gf.uniform1f(uFront.uGrain,grain);
        gf.bindVertexArray(vaoFront);
        gf.drawArrays(gf.TRIANGLES,0,3);
      }
    }

    function lock(){
      applyFrame(totalFor(holdMs), holdMs);
      armFilters(false);
      if (flash) flash.style.opacity='0';
      if (barEl) barEl.style.opacity='0';
      stage.classList.remove('is-armed');
      startIdle();
    }

    /* ---------------- idle parallax — almost imperceptible --------- */
    let idleRaf=0,mx=0,my=0,cx=0,cy=0,idleOn=false;
    const onMove = e => {
      const r=stage.getBoundingClientRect();
      mx=((e.clientX-r.left)/r.width-0.5)*2;
      my=((e.clientY-r.top)/r.height-0.5)*2;
    };
    function idleLoop(){
      cx+=(mx-cx)*0.04; cy+=(my-cy)*0.04;
      const u=rig.VBW/1000;
      const nudge=(n,ax,ay)=>{ if(!n) return;
        n.style.transformBox='view-box';
        n.style.transform=`translate(${(cx*ax*u).toFixed(3)}px, ${(cy*ay*u).toFixed(3)}px)`; };
      nudge(rig.camMove,2.4,1.4);
      /* the small wheel's two halves share one depth, or they would part */
      nudge(rig.reelS &&rig.reelS.move, 4.8,2.7);
      nudge(rig.reelSB&&rig.reelSB.move,4.8,2.7);
      nudge(rig.reelL &&rig.reelL.move, 3.8,2.1);
      nudge(rig.wordMask,1.1,0.6);
      idleRaf=requestAnimationFrame(idleLoop);
    }
    function startIdle(){
      if (idleOn || reduced() || window.matchMedia('(hover: none)').matches) return;
      idleOn=true;
      window.addEventListener('pointermove',onMove,{passive:true});
      idleRaf=requestAnimationFrame(idleLoop);
    }
    function stopIdle(){
      if(!idleOn) return; idleOn=false;
      window.removeEventListener('pointermove',onMove);
      cancelAnimationFrame(idleRaf);
    }

    const onDone = opts.onComplete || function(){};

    function frame(now){
      if (!playing) return;
      if (!startNow) startNow = now;
      const dt = clamp(now - (lastNow || now), 0, 120);
      lastNow = now;
      updateProgress(dt);          // fresh readiness before we decide to hold

      /* Absolute clock, not an accumulator. Summing clamped deltas makes
         the whole sequence run in slow motion on a device that drops
         frames; deriving the time from the start stamp and pushing only
         the hold into an offset keeps it honest under any frame rate. */
      let t = now - startNow - holdMs;
      if (t >= SEQ.holdAt && !ready()){
        holdMs += (t - SEQ.holdAt);        // absorb the overshoot into the hold
        t = SEQ.holdAt;
      }
      effT = t;

      const total = totalFor(holdMs);
      if (effT >= total){ effT = total; playing=false; lock(); onDone(); return; }
      applyFrame(effT, holdMs);
      raf = requestAnimationFrame(frame);
    }

    /* reduced motion: still a preloader — hold the static lockup until
       the page is actually ready, then hand over */
    function reducedRun(){
      lock();
      const step = now => {
        const dt = clamp(now - (lastNow||now), 0, 50); lastNow = now;
        updateProgress(dt);
        if (ready()){ onDone(); return; }
        raf = requestAnimationFrame(step);
      };
      raf = requestAnimationFrame(step);
    }

    const api = {
      SEQ, REELS, rig,
      get glOK(){ return glOK; },
      get staging(){ return ST; },
      get time(){ return effT; },
      get playing(){ return playing; },
      get progress(){ return progD; },
      get progressTarget(){ return progT; },
      get holdMs(){ return holdMs; },
      get waiting(){ return effT >= SEQ.holdAt && !ready(); },
      get parts(){ return lastParts; },
      duration(){ return totalFor(0); },
      setProgress(v){ manual = v == null ? null : clamp(v,0,1); return api; },
      play(){
        stopIdle();
        effT=0; holdMs=0; progD=0; progT=0; srcDone=false; startNow=0; lastNow=0;
        lastX=null; lastT=null; solveKey=null;
        if (reduced()){ reducedRun(); return api; }
        stage.classList.add('is-armed'); armFilters(true);
        cancelAnimationFrame(raf);
        playing=true;
        raf=requestAnimationFrame(frame);
        return api;
      },
      pause(){ playing=false; cancelAnimationFrame(raf); held=true; return api; },
      resume(){ if(!held) return api; held=false; playing=true;
                lastNow=0; startNow=0;
                /* re-anchor so the absolute clock resumes where it paused */
                const anchor = effT + holdMs;
                raf=requestAnimationFrame(n=>{ startNow = n - anchor; frame(n); });
                return api; },
      seek(ms){
        playing=false; cancelAnimationFrame(raf); stopIdle();
        stage.classList.add('is-armed'); lastX=null; lastT=null;
        holdMs = 0;                            /* scrubbing is hold-free */
        const total = totalFor(0);
        effT = clamp(ms, 0, total);
        progD = progT = clamp(effT / (SEQ.holdAt*0.92), 0, 1);
        if (effT >= total) lock();
        else { armFilters(true); applyFrame(effT, 0); }
        return api;
      },
      /* park the logo dead still at its exact resting position — the
         cursor parallax is a per-layer offset, so anything comparing the
         lockup against the source has to freeze it first */
      freeze(){ stopIdle(); mx=my=cx=cy=0; clearTransforms(); return api; },
      lock, resize,
      destroy(){
        playing=false; cancelAnimationFrame(raf); stopIdle(); ro.disconnect();
        if (glOK) [gl,gf].forEach(c=>{ const x=c&&c.getExtension('WEBGL_lose_context'); if(x) x.loseContext(); });
        gl=gf=null;
      }
    };
    return api;
  }

  /* ================================================================
     trackMedia — a progress source that measures the things a visitor
     is actually waiting for. Returns () => { p, done, parts }.

     The video is scored from its own readyState and buffered ranges, so
     this is the browser's real view of the download, not a guess. It is
     deliberately NOT "has the whole file arrived": a hero loop only has
     to reach HAVE_FUTURE_DATA to start playing, and waiting for the last
     byte would leave the visitor staring at a preloader for no reason.
     ================================================================ */
  function trackMedia(cfg){
    cfg = cfg || {};
    const video  = cfg.video || null;
    const images = (cfg.images || []).filter(Boolean);
    const secs   = cfg.videoSeconds || 4;      // enough buffered to play out
    const W = Object.assign({ document:1, images:2, video:5 }, cfg.weights || {});
    const extra = cfg.extra || [];   // [{ key, weight, get:()=>({p,done,note}) }]

    return function(){
      const parts = [];
      const rs = document.readyState;
      parts.push({ key:'document', w:W.document,
                   p: rs==='complete' ? 1 : rs==='interactive' ? 0.65 : 0.25,
                   done: rs==='complete', note: rs });

      if (images.length){
        let settled=0, ok=0;
        images.forEach(i => { if (i.complete){ settled++; if (i.naturalWidth>0) ok++; } });
        parts.push({ key:'images', w:W.images, p: settled/images.length,
                     done: settled === images.length,
                     note: `${ok}/${images.length}` });
      }

      if (video){
        const broken = !!video.error;
        const state  = [0, 0.18, 0.40, 0.85, 1][video.readyState] || 0;
        let buf = 0, bufSec = 0, target = secs;
        try {
          if (video.buffered.length && isFinite(video.duration) && video.duration > 0){
            target = Math.min(video.duration, secs);
            bufSec = video.buffered.end(video.buffered.length - 1);
            buf = Math.min(1, bufSec / target);
          }
        } catch(e){}
        /* a broken source counts as settled — never hold for a 404 */
        parts.push({ key:'video', w:W.video,
                     p: broken ? 1 : Math.max(state, buf*0.92),
                     done: broken || video.readyState >= 3,
                     note: broken ? 'error'
                          : `rs${video.readyState} · ${bufSec.toFixed(1)}/${target.toFixed(1)}s` });
      }

      extra.forEach(e => {
        const r = e.get() || {};
        parts.push({ key:e.key, w:e.weight||1, p:clamp(r.p||0,0,1), done:!!r.done, note:r.note||'' });
      });

      const tw = parts.reduce((s,x)=>s+x.w, 0) || 1;
      return {
        p: parts.reduce((s,x)=>s + x.w*x.p, 0) / tw,
        done: parts.every(x => x.done),
        parts
      };
    };
  }

  /* ================================================================
     download — byte-accurate loading for a hero asset.

     Streaming a <video> gives only coarse progress: readyState steps and
     whatever buffered ranges the browser chose to report. Fetching the
     file yourself gives the exact byte count, which is what makes a
     progress bar move smoothly and honestly. The cost is that playback
     cannot begin until the file is down, so this suits a short hero loop
     rather than a long film. Falls back to the plain URL if fetch or
     Content-Length is unavailable, and never rejects — a failed asset
     resolves as done so the preloader still lets go.
     ================================================================ */
  function download(url, o){
    o = o || {};
    const st = { p:0, done:false, bytes:0, total:0, url:null, error:null };
    st.promise = (async () => {
      try {
        if (!window.fetch || !window.ReadableStream) throw new Error('no-stream');
        const res = await fetch(url);
        if (!res.ok || !res.body) throw new Error('bad-response');
        /* A data: URI carries no Content-Length, and plenty of real
           servers omit it on compressed or chunked responses. Accept an
           explicit o.total, otherwise fall back to an indeterminate mode
           that still advances rather than sitting at zero. */
        const len = +(o.total || res.headers.get('content-length') || 0);
        st.total = len;
        st.indeterminate = !len;
        const reader = res.body.getReader();
        const chunks = [];
        for(;;){
          const { done, value } = await reader.read();
          if (done) break;
          chunks.push(value);
          st.bytes += value.length;
          st.p = len ? Math.min(0.99, st.bytes/len)
                     /* unknown size: ease toward 0.9 on bytes seen so far,
                        so the bar keeps moving without ever claiming to
                        know how much is left */
                     : Math.min(0.9, 1 - Math.exp(-st.bytes/450000));
        }

        /* DEMO ONLY. A local file cannot be throttled for real — a
           33KB data: URI arrives in a single chunk — so when asked, we
           replay the arrival of the bytes we already hold across a fixed
           window. The tracker's arithmetic is untouched; only the timing
           is staged. Production never passes throttleMs and the loop
           above is the whole story. */
        if (o.throttleMs){
          const totalBytes = st.bytes || st.total || 1;
          const t0 = performance.now();
          st.simulated = true;
          await new Promise(res2 => {
            const step = () => {
              const f = Math.min(1, (performance.now() - t0)/o.throttleMs);
              st.bytes = Math.round(totalBytes * f);
              st.p = Math.min(0.99, f);
              if (f >= 1) res2(); else requestAnimationFrame(step);
            };
            step();
          });
        }

        st.url = URL.createObjectURL(new Blob(chunks, o.type ? { type:o.type } : undefined));
        st.p = 1;
      } catch(e){
        st.error = e; st.url = url; st.p = 1;      // fall back to the plain URL
      }
      st.done = true;
      return st.url;
    })();
    return st;
  }


  /* ================================================================
     boot — what WordPress calls. Reads a config object printed by PHP,
     finds the hero media on its own, and guarantees the overlay leaves.

     The guarantee matters more than anything else here: a preloader is
     the one component that can take a whole site down by doing nothing.
     Every exit path below removes the stage from the DOM rather than
     fading it, because a position:fixed element at opacity 0 still eats
     clicks and traps the keyboard.
     ================================================================ */
  function boot(cfg){
    cfg = cfg || window.LMF_PRELOADER || {};
    const root  = document.documentElement;
    const stage = document.getElementById(cfg.ids && cfg.ids.stage || 'lmf-stage');
    if (!stage) return null;

    const store = {
      get(k){ try { return window.sessionStorage.getItem(k); } catch(e){ return null; } },
      set(k,v){ try { window.sessionStorage.setItem(k,v); } catch(e){} }
    };

    let inst = null, failsafe = 0, gone = false, logoEl = null;

    /* ---------------------------------------------------------------
       The handover.

       Rather than dissolving on the spot, the locked mark travels to
       wherever the page already shows the logo — the header — and the
       real one takes its place there. It only works if both are the
       same artwork at the same aspect: the flight is a translate and a
       uniform scale, so anything else would visibly stretch. That is
       why the header prints the same SVG rather than a raster.

       Everything here is optional. No target, reduced motion, or a
       target that is hidden or a different shape, and it falls back to
       the plain fade — which is also what happens if any of it throws.
       --------------------------------------------------------------- */
    const FLIGHT_SEL = '[data-lmf-logo-target]';

    function findTarget(){
      const sel = cfg.logoTarget || FLIGHT_SEL;
      let t = null;
      try { t = document.querySelector(sel); } catch(e){ return null; }
      if (!t && !cfg.logoTarget) {
        t = document.querySelector('header .lockup, .site-brand svg, .site-brand img');
      }
      if (!t) return null;
      const r = t.getBoundingClientRect();
      if (r.width < 8 || r.height < 4) return null;          // hidden or collapsed
      const cs = window.getComputedStyle(t);
      if (cs.visibility === 'hidden' || cs.display === 'none') return null;
      return t;
    }

    function flyToHeader(done){
      const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (cfg.flight === false || reduced || !logoEl) return false;

      const svg = logoEl.querySelector('svg') || logoEl;
      const target = findTarget();
      if (!target) return false;

      const from = svg.getBoundingClientRect();
      const to   = target.getBoundingClientRect();
      if (!from.width || !to.width) return false;

      /* a translate-and-scale can only land cleanly on the same shape */
      const ar = (from.width / from.height) / (to.width / to.height);
      if (!isFinite(ar) || ar < 0.94 || ar > 1.06) return false;

      const s  = to.width / from.width;
      const dx = to.left - from.left;
      const dy = to.top  - from.top;
      const ms = cfg.flightMs != null ? cfg.flightMs : 900;

      /* the page has to be visible for the mark to arrive onto it, so the
         stage sheds its atmosphere first and its ground fades under the
         travelling logo — fading the stage itself would fade the logo too */
      const rest = (window.getComputedStyle(stage).getPropertyValue('--lmf-rest') || '').trim();
      if (rest) stage.style.background = rest;
      stage.classList.add('is-handing');

      target.style.visibility = 'hidden';       // the real one waits its turn

      const to1 = 'translate(' + dx.toFixed(2) + 'px, ' + dy.toFixed(2) + 'px) scale(' + s.toFixed(5) + ')';
      logoEl.style.transformOrigin = 'top left';
      logoEl.style.willChange = 'transform';

      /* the ground goes first, and faster, so the page is already there
         when the mark arrives rather than materialising under it */
      stage.style.transition = 'background-color ' + Math.round(ms * 0.66) + 'ms ease';
      stage.style.background = 'transparent';

      let landed = false;
      const land = function(){
        if (landed) return; landed = true;
        target.style.visibility = '';
        done();
      };

      /* Web Animations rather than a CSS transition. transitionend is not
         dependable here: on a page still busy behind the overlay the
         transition can start a frame or two late, and removing the element
         cancels the event outright — which stranded the mark short of the
         header, visibly, on slower viewports. An animation object reports
         its own completion and cannot be missed. */
      if (typeof logoEl.animate === 'function') {
        let anim;
        try {
          anim = logoEl.animate(
            [ { transform: 'none' }, { transform: to1 } ],
            { duration: ms, easing: 'cubic-bezier(.62,.02,.16,1)', fill: 'forwards' }
          );
        } catch(e){ anim = null; }
        if (anim) {
          if (anim.finished && anim.finished.then) {
            anim.finished.then(land, land);
          } else {
            anim.onfinish = land;
          }
          /* a hard stop well clear of the animation's own length, in case
             the tab is backgrounded and it never gets to run */
          window.setTimeout(land, ms + 1200);
          return true;
        }
      }

      /* no Web Animations: fall back to the transition, and give the event
         real room before the timer overrules it */
      void logoEl.offsetWidth;                  // commit the start state
      logoEl.style.transition = 'transform ' + ms + 'ms cubic-bezier(.62,.02,.16,1)';
      logoEl.style.transform = to1;
      logoEl.addEventListener('transitionend', function h(e){
        if (e.propertyName !== 'transform') return;
        logoEl.removeEventListener('transitionend', h); land();
      });
      window.setTimeout(land, ms + 1200);
      return true;
    }

    function release(reason){
      if (gone) return; gone = true;
      clearTimeout(failsafe);
      /* the head guard fires only if we never got here */
      if (window.__lmfFailsafe) { clearTimeout(window.__lmfFailsafe); window.__lmfFailsafe = 0; }
      root.classList.remove('lmf-locked');
      root.classList.add('lmf-ready');
      if (cfg.oncePerSession !== false) store.set(cfg.sessionKey || 'lmfPreloaderSeen', '1');

      const finish = function(){
        try { if (inst) inst.destroy(); } catch(e){}
        if (stage.parentNode) stage.parentNode.removeChild(stage);
        root.dispatchEvent(new CustomEvent('lmf:preloader-done', { detail:{ reason:reason } }));
      };

      /* the flight is a flourish; it must never be the reason the page
         fails to come back, so anything it throws falls through to the fade */
      let flying = false;
      if (reason === 'complete') {
        try { flying = flyToHeader(finish); } catch(e){ flying = false; }
      }
      if (flying) return;

      stage.classList.add('is-leaving');
      window.setTimeout(finish, cfg.fadeMs != null ? cfg.fadeMs : 620);
    }

    /* already seen this session — never make a returning visitor sit
       through it twice on an internal link */
    if (cfg.oncePerSession !== false && store.get(cfg.sessionKey || 'lmfPreloaderSeen')){
      root.classList.remove('lmf-locked');
      root.classList.add('lmf-ready');
      if (stage.parentNode) stage.parentNode.removeChild(stage);
      return null;
    }

    /* last line of defence: if anything below throws, stalls, or the
       browser simply never fires the events we are waiting on, the site
       still comes back */
    failsafe = window.setTimeout(function(){ release('failsafe'); },
                                 cfg.failsafeMs != null ? cfg.failsafeMs : 12000);

    try {
      const id = k => document.getElementById((cfg.ids && cfg.ids[k]) || 'lmf-' + k);
      const logo = id('logo'), svg = id('svg');
      if (!logo || !svg) { release('no-logo'); return null; }
      logoEl = logo;                    // the flight needs it at release time

      /* ---- find what is worth waiting for ---- */
      const heroSel = cfg.heroSelector || '.hero, #hero, [data-lmf-hero], .wp-block-cover';
      const heroEl  = document.querySelector(heroSel) || document.querySelector('main') || document.body;
      let video = null;
      if (cfg.videoSelector) {
        const v = document.querySelector(cfg.videoSelector);
        if (v && v.tagName === 'VIDEO') video = v;
      }
      if (!video && cfg.watchVideo !== false) video = heroEl.querySelector('video') || null;

      const images = [];
      if (cfg.watchImages !== false){
        if (video && video.getAttribute('poster')){
          const p = new Image(); p.src = video.getAttribute('poster'); images.push(p);
        }
        Array.prototype.slice.call(heroEl.querySelectorAll('img'), 0, 4)
          .forEach(function(i){ images.push(i); });
      }

      inst = mount({
        stage: stage,
        logo: logo,
        svg: svg,
        flash: id('flash'),
        bar: id('progress'),
        barFill: id('progress-fill'),
        canvasBack: id('gl-back'),
        canvasFront: id('gl-front'),
        staging: cfg.staging || 'room',
        palette: cfg.palette || null,
        hold: cfg.hold !== false,
        maxWait: cfg.maxWait != null ? cfg.maxWait : 6000,
        minFill: cfg.minFill != null ? cfg.minFill : 900,
        source: trackMedia({
          video: video,
          images: images,
          videoSeconds: cfg.videoSeconds || 4
        }),
        onComplete: function(){ release('complete'); }
      });

      if (inst.error){ release('rig-error:' + inst.error); return null; }
      inst.play();
      window.LMFPreloaderInstance = inst;
      return inst;
    } catch(err){
      if (window.console && console.warn) console.warn('[lmf-preloader]', err);
      release('exception');
      return null;
    }
  }

  return { mount, trackMedia, download, boot, SEQ, REELS, STAGING,
           DEFAULT_MAP, solveBrake, coastAngle };
})();
window.LMFPreloader = LMFPreloader;

/* Auto-start when PHP printed a config. Waits for DOMContentLoaded only if
   the document has not parsed yet — the script is footer-enqueued, so it
   normally runs immediately and the overlay is already painted by CSS. */
if (window.LMF_PRELOADER && window.LMF_PRELOADER.autoBoot !== false) {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { LMFPreloader.boot(); });
  } else {
    LMFPreloader.boot();
  }
}

})(window, document);
