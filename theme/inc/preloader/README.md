# Lemon Mint Films — Cinematic Preloader

The brand ident as a WordPress component. The camera rolls in from the left,
the citrus reels turn, the projector exposes the wordmark, and it locks to
the supplied logo **exactly** — verified at 0 of 3,781,312 pixels differing
from the source SVG, across six viewport / pixel-density / staging
combinations.

Built on **logo v2.0**. The previous artwork ships alongside it as
`assets/img/lmf-logo-v1.svg`; point `lmf_preloader_logo_path` at it to go
back.

It is a real preloader, not a splash screen: it plays to a hold point, waits
for the hero media to be genuinely ready, then hands over.

---

## Install — pick one

This folder runs either way from the same code. **Do not install both**; if
you do, nothing breaks (the class guards against a double load) but you are
maintaining two copies.

### As a plugin
1. Copy `lmf-preloader/` into `wp-content/plugins/`
2. Plugins → activate **Lemon Mint Films — Cinematic Preloader**

Survives a theme switch. Best if a client needs to toggle it without touching
theme code.

### Inside the theme
1. Copy `lmf-preloader/` into your theme, e.g. `inc/preloader/`
2. Add one line to `functions.php`:

```php
require_once get_theme_file_path( 'inc/preloader/lmf-preloader.php' );
```

Asset URLs resolve from the folder's own position relative to `wp-content`,
so plugin, theme, child theme and mu-plugin installs all work unconfigured.

**Requirements:** WordPress 5.2+ (for `wp_body_open`), PHP 7.0+. No build
step, no dependencies, no page builder, no ACF. Themes that never call
`wp_body_open` still work — it falls back to `wp_footer`.

---

## Swap in your own hero video

The engine finds the hero on its own. By default it looks for
`.hero, #hero, [data-lmf-hero], .wp-block-cover` and watches the first
`<video>` inside, plus the video's poster and up to four images.

Mark your hero and it will be found:

```html
<section class="hero" data-lmf-hero>
  <video id="hero-video" muted playsinline loop preload="auto" poster="…">
    <source src="…/showreel.webm" type="video/webm">
    <source src="…/showreel.mp4"  type="video/mp4">
  </video>
</section>
```

Or point at it explicitly:

```php
add_filter( 'lmf_preloader_settings', function ( $s ) {
	$s['videoSelector'] = '#hero-video';
	return $s;
} );
```

---

## Settings screen

**Settings → LMF Preloader.** Everything below is editable there, with a
**Preview it now** link that opens the homepage and replays the ident,
ignoring the once-per-visit rule so you can watch it as often as you like.

| | |
|---|---|
| **Enable / Pages** | On or off; homepage only or every page |
| **Repeat** | Once per visit, or on every internal link |
| **Staging** | Darkened room (the signed-off version) or Light first (Ed.02) |
| **Progress colour** | Blank uses the staging's own accent |
| **Opening colour** | What the stage opens on |
| **Resting colour** | What it settles to — match this to your hero for a seamless handover |
| **Progress bar** | Show or hide |
| **Logo** | Choose a different SVG from the media library |
| **Hold for media** | Wait for the hero video and images |
| **Give up after** | Capped at 20s, because a stalled CDN must not block the page |
| **Hero / video selector** | Where to look for the media |
| **Stacking order** | Raise only if something in your theme sits over it |

Colour changes reach the **shader**, not only the CSS — the WebGL canvas
paints over the stage background, so a CSS-only override would be invisible.

Two things are validated at save time rather than failing silently on the
front page: a colour that is not a valid hex falls back to the staging
default with a notice, and a replacement logo is rejected unless it carries
the four group ids the animation moves (`lmf-camera`, `lmf-reel-large`,
`lmf-reel-small`, `lmf-wordmark`).

## Settings in code

Filters run **after** the stored options, so code always wins. These are the
defaults:

```php
add_filter( 'lmf_preloader_settings', function ( $s ) {
	$s['staging']        = 'room';    // 'room' | 'light'  — see below
	$s['hold']           = true;      // wait for the hero media
	$s['maxWait']        = 6000;      // stop waiting after this, ms
	$s['minFill']        = 900;       // bar never fills faster than this
	$s['videoSeconds']   = 4;         // buffered seconds that count as ready
	$s['oncePerSession'] = true;      // not on every internal link
	$s['heroSelector']   = '.hero, #hero, [data-lmf-hero], .wp-block-cover';
	$s['videoSelector']  = '';        // explicit override
	$s['watchVideo']     = true;
	$s['watchImages']    = true;
	$s['fadeMs']         = 620;
	$s['failsafeMs']     = 12000;
	$s['flight']         = true;      // hand the mark over to the header
	$s['flightMs']       = 900;
	$s['logoTarget']     = '[data-lmf-logo-target]';
	return $s;
} );
```

Where it appears — front page only by default:

```php
add_filter( 'lmf_preloader_display', function ( $show ) {
	return is_front_page() || is_page( 'showreel' );
} );
```

Other filters: `lmf_preloader_logo_path` (absolute path to a different SVG —
it must carry the same four required group ids) and `lmf_preloader_template`
(absolute path to your own markup).

## The handover

When the sequence finishes, the mark does not dissolve where it stands — it
travels to wherever the page already shows the logo, and the real one takes
over there. Mark the target and it is found:

```html
<a class="lockup-link" href="/">
  <svg class="lockup" data-lmf-logo-target> … </svg>
</a>
```

Without the attribute it looks for `header .lockup, .site-brand svg,
.site-brand img`. Point it somewhere else with
`$s['logoTarget'] = '#my-logo';`, set the length with `$s['flightMs']`
(default 900), or turn it off with `$s['flight'] = false`.

**The target has to be the same artwork, inline.** The flight is a translate
and a uniform scale, so anything else would visibly stretch on arrival — and
a raster target would show the swap even at the right size. That is why the
theme prints the logo as inline SVG rather than an `<img>`. The engine checks
before committing: if the target is missing, hidden, collapsed, or its aspect
ratio is more than 6% off the preloader's, it falls back to the plain fade.
So does reduced motion, and so does anything that throws — the flight is a
flourish and is never allowed to be the reason the page fails to come back.

Measured on the shipped theme, the mark lands within **0.07px** of the header
logo at every viewport tested, so the moment the overlay is removed there is
nothing to see.

An event fires on `document.documentElement` when the overlay leaves, which
is where to start your hero video:

```js
document.documentElement.addEventListener('lmf:preloader-done', function (e) {
	document.getElementById('hero-video').play();   // e.detail.reason
});
```

---

## The two stagings

**`room` (default)** is the staging the ident was signed off on: the lamp
strikes, the camera crosses a dim screen, and the frame develops up to paper.
It is the default because it is what was approved — but it does put the mark
on near-black for about two seconds, which Ed. 02 forbids, so it needs a
written exception before launch.

**`light`** follows Brand Guidelines Ed. 02, the 1A system. Warm
white `#F7F5EF` is the ground and stays the ground; the projection reads as a
pool of light warming toward lemon across the sheet, with a local penumbra
around it rather than a global dim. The mark is never on a black panel, so
the misuse rule holds.

Switch between them in Settings, or with
`add_filter('lmf_preloader_settings', fn($s) => $s + ['staging' => 'light']);`

The `room` staging is verified **pixel-identical** to the approved standalone
file — 0 differing pixels at every sampled frame of the sequence.

---

## What the progress bar measures

Not a timer. It shows whichever is **further behind**: how far the sequence
has run, or how much of the hero is genuinely loaded — the document's own
ready state, the poster and hero images, and the video's `readyState` and
buffered ranges. Both inputs only ever increase, so the minimum of them can
never run backwards, and the bar **cannot reach 100% until the page really is
ready**.

If the hero is already cached the bar simply tracks the sequence and arrives
at 100% at the hold point. If the connection is slow it stalls at whatever
fraction is true and the preloader waits.

---

## Why it cannot take the site down

A preloader is the one component that can break a whole site by doing
nothing, so every failure path is covered:

- **No JavaScript** — a `<noscript>` rule hides the overlay outright. The
  page is simply the page.
- **The script 404s, or throws on parse** — an inline guard in `<head>` arms
  a timer that removes the stage regardless. Verified.
- **The video never arrives** — `maxWait` releases the hold and the hero
  shows its poster.
- **A broken video source** — counts as settled rather than blocking.
- **Reduced motion** — skips straight to the resting lockup, still waits for
  media, then hands over.
- **No WebGL** — the vector motion runs without the atmosphere.

The overlay is always *removed from the DOM*, never just faded: a
`position: fixed` element at `opacity: 0` still swallows clicks and traps the
keyboard.

---

## About the artwork

The logo is inlined rather than served as `<img>` or `<object>` because the
engine has to reach its groups in the same document. Nothing inside them is
read or rewritten — they are only wrapped and transformed, and every
transform, mask and filter is detached at lockup so the resting frame goes
back through the browser's ordinary raster path.

That last detail is why it is exact: an identity transform or an attached
mask keeps an element on a different rasterisation path and shifts edge
antialiasing by a fraction of a pixel.

`assets/img/lmf-logo.svg` was extracted from `logo v2.0.ai`. All 46 paths are
copied through verbatim — same `d`, same fill, same stroke, same transform —
and only *wrapped* in the groups the animation moves. Nothing was redrawn,
and this export needed no reconstruction at all: unlike the previous file, it
carries no flattened rasters.

**Framing.** v2.0's artboard carries noticeably more empty margin than the
previous file did — 39.5 units on the left against 24 on the right — so the
mark rendered about a tenth smaller than the approved ident and sat right of
centre. The shipped SVG therefore carries a `viewBox` cropped to the artwork
rather than to the artboard. No path moved: this only changes the window onto
them, and the original artboard is recorded on the root as
`data-lmf-artboard`. Rebuild with `FIT = False` in `build_svg_v2.py` to ship
the artboard exactly as saved.

The wheels are grouped as Illustrator painted them. It paints the small
wheel's outer contour *behind* the large wheel and the rest of that wheel in
front of it, so the two overlap in a sliver about two units wide. Putting the
whole small wheel in one group reordered that sliver and cost four pixels, so
the contour keeps its place in its own group, `lmf-reel-small-back`, and the
engine turns it locked to the wheel it belongs to. Each rotor spins about its
own measured centre rather than a shared one — the contour is drawn a fifth
of a unit off-axis, and a shared centre would make it orbit.

Each wheel's axis is measured from its spoke-and-hub path, which is the part
the eye reads as turning; both come out within 0.003 units of the wheel's own
rings, and neither drifts by a measurable fraction of a pixel at any angle.

---

## Files

```
lmf-preloader/
  lmf-preloader.php              loader — plugin header, hooks, filters
  templates/preloader.php        markup
  assets/css/lmf-preloader.css   the .lmf-* rules
  assets/js/lmf-preloader.js     engine, no dependencies
  assets/img/lmf-logo.svg        the artwork (logo v2.0)
  assets/img/lmf-logo-v1.svg     the previous mark, kept for rollback
  README.md
```

Roughly 50 KB of JavaScript and 3 KB of CSS, both footer/normally enqueued,
plus a small critical block inlined in `<head>` so the stage covers the page
before the stylesheet arrives. No Three.js: the atmosphere is about 9 KB of
hand-written GLSL, and shipping ~650 KB of engine inside the one thing that
must paint before the page would cost more than it adds.
