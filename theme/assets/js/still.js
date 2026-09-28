/* Lemon Mint Films - halftone still engine.
   Draws a black dot screen on warm white as a stand-in for photography that
   has not been shot or cleared yet. Every generated frame stays inside the
   four-colour palette, so a page full of placeholders is still on-brand.
   Delete this file once real stills are in the media library; the templates
   fall back to the featured image whenever one is set. */
(function(){
"use strict";
function rng(seed){var s=seed>>>0;return function(){s=(s+0x6D2B79F5)>>>0;var t=s;t=Math.imul(t^t>>>15,t|1);t^=t+Math.imul(t^t>>>7,t|61);return((t^t>>>14)>>>0)/4294967296;};}
function hashStr(s){var h=2166136261;for(var i=0;i<s.length;i++){h^=s.charCodeAt(i);h=Math.imul(h,16777619);}return h>>>0;}
function clamp(v,a,b){return v<a?a:v>b?b:v;}
function smooth(e0,e1,x){var t=clamp((x-e0)/(e1-e0),0,1);return t*t*(3-2*t);}

function paintStill(cv,seedStr,kind){
  var r=rng(hashStr(seedStr)), ctx=cv.getContext("2d"), w=cv.width, h=cv.height;
  ctx.fillStyle="#F7F5EF";ctx.fillRect(0,0,w,h);

  var P={
    hz:0.56+r()*0.16,
    lx:0.28+r()*0.46, ly:0.24+r()*0.24,
    lw:0.10+r()*0.16,
    sx:0.20+r()*0.58, sy:0.68+r()*0.20, sw:0.13+r()*0.13, sh:0.30+r()*0.24,
    two:r()>0.55, sx2:0.12+r()*0.76, sw2:0.09+r()*0.07, sh2:0.20+r()*0.16,
    band:r()>0.42, by:0.40+r()*0.30, bh:0.020+r()*0.030,
    blocks:[]
  };
  var bx=-0.06;
  while(bx<1.06){var bw2=0.05+r()*0.13;P.blocks.push([bx,bw2,0.10+r()*0.30]);bx+=bw2+0.004+r()*0.03;}

  function field(u,v){
    var d=0.21+v*0.15;                                       /* gentle vertical gradation */
    d+=smooth(P.hz,1.06,v)*0.28;                             /* ground falls into shadow */
    var dx=(u-P.lx)/P.lw, dy=(v-P.ly)/(P.lw*1.35);
    d-=Math.exp(-(dx*dx+dy*dy)*0.85)*0.52;                   /* key light opens to paper white */
    d+=Math.pow(Math.abs(u-0.5)*2,3.4)*0.22;                 /* vignette */
    d+=Math.pow(Math.abs(v-0.5)*2,3.4)*0.16;
    if(kind==="portrait"||kind==="interior"){
      var qx=(u-P.sx)/P.sw, qy=(v-P.sy)/P.sh, q=Math.sqrt(qx*qx+qy*qy);
      d+=smooth(1.24,0.52,q)*0.66;                           /* subject in silhouette */
      d-=smooth(1.30,1.02,q)*0.16;                           /* rim light along its edge */
      if(P.two){                                             /* a second figure, further back */
        var rx=(u-P.sx2)/P.sw2, ry=(v-(P.sy+0.06))/P.sh2, q2=Math.sqrt(rx*rx+ry*ry);
        d+=smooth(1.24,0.55,q2)*0.40;
      }
      if(P.band)d+=smooth(P.bh,0,Math.abs(v-P.by))*0.30;     /* a horizontal edge: table, wall, sill */
      if(kind==="interior")d+=smooth(0.016,0,Math.abs(v-(P.hz-0.02)))*0.22;
    } else {
      for(var i=0;i<P.blocks.length;i++){
        var b=P.blocks[i],top=P.hz-b[2];
        if(u<b[0]-0.014||u>b[0]+b[1]+0.014||v>P.hz+0.008)continue;
        var fx=smooth(-0.014,0.006,u-b[0])*smooth(-0.014,0.006,(b[0]+b[1])-u);
        var fy=smooth(-0.008,0.012,v-top);
        d+=0.56*fx*fy;                                       /* feathered skyline */
      }
      d+=smooth(0.010,0,Math.abs(v-P.hz))*0.30;
    }
    return clamp(d,0,1);
  }

  /* rotated dot screen, classic halftone angle */
  var sp=Math.max(3.1,w/210), ang=-0.26, ca=Math.cos(ang), sa=Math.sin(ang);
  var diag=Math.sqrt(w*w+h*h);
  ctx.fillStyle="#111111";
  for(var a=-diag*0.6;a<diag*0.7;a+=sp){
    for(var b2=-diag*0.6;b2<diag*0.7;b2+=sp){
      var x=a*ca-b2*sa+w*0.5, y=a*sa+b2*ca+h*0.5;
      if(x<-sp||y<-sp||x>w+sp||y>h+sp)continue;
      var d=field(x/w,y/h);
      if(d<=0.02)continue;
      var rad=sp*0.60*Math.pow(d,0.60);
      if(rad<0.26)continue;
      ctx.beginPath();ctx.arc(x,y,rad,0,6.2832);ctx.fill();
    }
  }
}

window.LMFStill = paintStill;

function hydrate(root){
  (root||document).querySelectorAll("canvas[data-seed]").forEach(function(cv){
    if(cv.dataset.done)return;
    cv.dataset.done="1";
    paintStill(cv,cv.dataset.seed,cv.dataset.kind||"wide");
  });
}
if(document.readyState!=="loading")hydrate(document);
else document.addEventListener("DOMContentLoaded",function(){hydrate(document);});
window.LMFHydrate=hydrate;
})();
