# Lemon Mint Films — WordPress theme

A custom theme built to **Brand Guidelines Edition 02 · 2026** (the 1A system) and to the
site architecture derived from the Digital Experience Strategy.

---

## Install

1. WordPress admin → **Appearance → Themes → Add New → Upload Theme** → choose `lemon-mint-films.zip` → **Activate**.
2. **Tools → Lemon Mint demo content → Install demo content.** This seeds nine services,
   five industries, nine projects, three testimonials, the Studio and Contact pages, a
   static homepage and the primary menu, so you can see the theme working immediately.
3. **Settings → Permalinks → Save** (only if `/work/` or `/services/` 404 — the theme
   flushes rewrites on activation, but some hosts cache).
4. **Appearance → Customise → Lemon Mint — studio details** for the address, phone,
   WhatsApp number, response-time commitment, showreel URL and homepage headline.

Requires WordPress 6.4+ and PHP 8.0+. No plugins are required.

---

## The rules this theme enforces

The guideline's competitive argument is that this site should not look or behave like the
templated builds every competitor in the market runs. That argument is only true if these
hold, so the theme enforces them rather than trusting them:

| Rule | Where it lives |
|---|---|
| Four colours only — warm white `#F7F5EF`, lemon `#F5B82E`, mint `#17805C`, film black `#111111` | `style.css` tokens; the block editor palette is locked and custom colours are disabled |
| No gradients | `add_theme_support( 'editor-gradient-presets', array() )` and `disable-custom-gradients` |
| No black backgrounds | There is no dark surface class in the stylesheet |
| Tints are alpha of the same four values | `--ink-70`, `--rule`, `--mint-12`, `--lemon-14` — no new hues |
| Anton for headlines only, uppercase, 0.02em | `.h1 .h2 .h3-anton` — Anton is never applied to a body class |
| Montserrat 400/500/600 for everything else | `body`, `.body-lg`, `.sm`, `.xs` |
| Kickers are mint, uppercase, 0.28em, always above the headline | `lmf_kicker()` — call it before the `<h1>`/`<h2>`, never after |
| Tabular figures so codes and timecodes align | `font-variant-numeric: tabular-nums` on `body` |
| One lemon action per surface | `.btn-lemon` appears once per template; secondary routes use `.link-u` |
| Margins are 1/12 of the sheet | `--pad: clamp(20px, 8.333vw, 120px)` |
| The mark is never redrawn, recoloured or skewed | `assets/img/mark.png` and `lockup.png` are your supplied artwork, used as-is by `lmf_mark()` / `lmf_lockup()` |
| The mark never sits on a lemon surface | `.surface-lemon .lockup, .surface-lemon .mk { display:none }` — the wedges vanish into a lemon ground |

**Do not install a page builder.** Elementor, WPBakery or a bundled theme framework
reintroduces exactly the template fingerprint the whole positioning depends on avoiding,
and will break the Core Web Vitals budget the poster hero is built around.

---

## Content model

Four public post types, one private, three taxonomies.

| Post type | URL | Template |
|---|---|---|
| `lmf_project` | `/work/{slug}/` | `single-lmf_project.php`, `archive-lmf_project.php` |
| `lmf_service` | `/services/{slug}/` | `single-lmf_service.php`, `archive-lmf_service.php` |
| `lmf_industry` | `/industries/{slug}/` | `single-lmf_industry.php`, `archive-lmf_industry.php` |
| `lmf_person` | `/studio/people/{slug}/` | `single-lmf_person.php` |
| `lmf_testimonial` | not public | rendered into homepage blocks |

Taxonomies on projects: `lmf_service_cat`, `lmf_industry_cat`, `lmf_format`. (The industry
taxonomy is deliberately *not* named `lmf_industry` — that is the post type name, and a
taxonomy sharing it hijacks the same query var and breaks every `/industries/` URL.) These are the
three filter axes on the Work hub and the mechanism that pulls proof onto service and
industry pages automatically — an editor never has to remember to attach it.

Custom fields are native metaboxes in `inc/meta.php`. **There is no ACF dependency** —
the field set is small, stable and part of the design system, so it belongs in the theme
rather than in a plugin whose licence and field export the client has to maintain.

### Publication gate

A project **cannot be published** without a brief, a service, an industry and a poster
image. This is deliberate: the single most consistent failure across the thirteen
competitors reviewed is a project published as a bare embedded video with a title.
The editor is told exactly which field is missing. See `lmf_require_fields_before_publish()`.

---

## Placeholder stills

Until real photography is shot and cleared, `lmf_still()` draws a **black halftone screen
on warm white** in a `<canvas>` — so an unfinished page still reads as designed rather
than broken, and never leaves the four-colour palette.

The moment a featured image is set on a post, the halftone is replaced by the real image
automatically. Once every page has real photography, `assets/js/still.js` can be deleted
and dequeued.

---

## Video

`_lmf_video` takes a **self-hosted, CDN-delivered URL** — Mux, Cloudflare Stream or Bunny.
Do not paste a Vimeo or YouTube embed: third-party players pass no SEO value back to the
domain and bring their own chrome into a page that is otherwise entirely yours. This is
the single technical change that recovers what every competitor is giving away.

---

## Schema

Emitted from `lmf_schema()` and `lmf_breadcrumbs()`:

- `Organization` sitewide
- `VideoObject` on every project (duration is converted from `mm:ss` to ISO 8601)
- `BreadcrumbList` throughout
- `FAQPage` wherever a service page carries an FAQ

---

## Contact form

`page-contact.php` ships the **designed shell** — three intent doors, the direct-channel
block, and the field layout. It does not send mail.

Connect the client's form plugin by filtering:

```php
add_filter( 'lmf_contact_form_shortcode', function () {
    return '[gravityform id="1" title="false"]';
} );
```

Whatever plugin is used **must** accept hidden fields for intent, service and source URL,
or the routing is lost and every enquiry arrives as an untagged "Contact Us" — which is
the competitor behaviour this page exists to beat.

---

## The logo

`assets/img/lockup.png` and `mark.png` are your supplied artwork, trimmed and
scaled — not redrawn. The `-reversed` files are the same artwork with the ink
swapped to warm white **and the mint accents swapped to lemon**, because a mint
dot and a mint full stop are invisible on a mint ground. They are separate
files rather than a CSS filter for exactly that reason.

The mark is hidden on lemon surfaces (`.surface-lemon`): the lemon wedges
disappear into a lemon background, which the guideline's misuse page rules out.

**Please send the SVG.** These are rasters from the PNG you supplied. They are
sharp at every size the site currently uses, but a vector is what you want for
print, for the favicon, and for the 64px floor the guideline specifies. Drop
the SVG into `assets/img/` and change the two filenames in
`inc/template-tags.php` — nothing else needs to move.

---

## Two facts to confirm before launch

The brand guidelines and the strategy document disagree. Neither is asserted anywhere in
the theme; both are placeholders.

1. **Address** — guidelines say *Warehouse 28, Al Quoz Industrial Third*; the strategy document
   says *Meydan Grandstand, Nad Al Sheba*. The Customiser default follows the newer
   guidelines. Confirm and set it.
2. **Founder** — the guidelines' sample business card and the strategy document name
   different people. The demo content leaves every name as "to confirm".

Also outstanding: which client names may be used publicly, which awards are independently
verifiable, and the exact permitted wording for the streaming and broadcast credits.
Until those are signed off, leave the placeholders in place.

---

## Files

```
lemon-mint-films/
├── style.css                    design system + theme header
├── functions.php                setup, assets, schema, customiser
├── header.php  footer.php
├── front-page.php               homepage
├── archive-lmf_project.php      work hub, filterable
├── single-lmf_project.php       case study
├── archive-lmf_service.php      services hub
├── single-lmf_service.php       service page
├── archive-lmf_industry.php     industries hub
├── single-lmf_industry.php      industry page
├── single-lmf_person.php        director page
├── page-contact.php             intent-routed contact
├── page.php  single.php  index.php  404.php  searchform.php
├── inc/
│   ├── cpt.php                  post types, taxonomies, publication gate
│   ├── meta.php                 custom fields
│   ├── template-tags.php        lmf_mark, lmf_still, lmf_tile, lmf_kicker…
│   ├── nav-walker.php           primary nav + services mega panel
│   └── demo-content.php         one-click seeder (Tools menu)
└── assets/
    ├── img/mark.svg             the supplied vector mark — do not edit
    └── js/still.js  site.js
```

All placeholder content is clearly marked. No real client relationship, award or
credential is asserted anywhere in this theme.

## "Start a production" popup (inc/brief.php)

Every link to `/contact/?i=production` — header button, menu, closing CTA, project pages — opens a four-step brief in a popup instead of leaving the page (add `data-brief` to any other link to do the same; `/#brief` opens it on load). It posts to the same handler as the contact page, so enquiries arrive at Customizer → *Enquiries email* with the same spam checks and the `lmf_enquiry_sent` hook. Without JavaScript, and on the contact page itself, the links simply go to the contact page. Turn it off with `add_filter( 'lmf_brief_enabled', '__return_false' );`.

## Contact page (page-contact.php)

One screen: the studio's direct lines on the left, the map on the right, one lemon action that opens the production brief popup. Address, phone, WhatsApp, email, hours and the reply-time line all come from Customizer → *Lemon Mint — studio details*; the clock next to the hours shows the current time in Dubai and whether the studio is open.

The map is drawn in the brand's own colours (SVG, no API key, no tracking) and the live Google map is laid over it once it loads. Untick *Contact page: show the live Google map* in the Customizer to keep the drawn map only — "Get directions" opens the real map in a new tab either way.

## Setting the site up (once)

1. **Install the theme and activate it.**
2. **Install ACF Pro** and activate it.
3. **Tools → Lemon Mint demo content → Install.** This builds the whole structure: the Home page (and sets it as the front page), the About, Studio and Contact pages with their templates, the primary menu, nine Services, five Industries, nine Projects and three Testimonials. Every word of it is placeholder copy.
4. **Settings → Permalinks → Save** once, so the Work / Services / Industries addresses resolve.
5. Open the admin. The Lemon Mint screens are now filled with the text the site is showing — start replacing it with the client's own.
6. Replace the placeholders, then **Tools → Lemon Mint demo content → Remove** before handover if any demo projects are left.

Pages do not have to be built by hand or with a page builder: each one has its own template in the theme, chosen automatically by the page slug (`about`, `studio`, `contact`). Creating those pages manually works too — the fields appear on them either way.

## Editing the site — Advanced Custom Fields

The theme needs **ACF Pro** (repeaters and options pages). Install and activate it; the field groups come with the theme and appear by themselves. Nothing has to be built in the admin.

**The rule:** a field you fill in wins, a field you leave empty falls back to the theme's own copy. The site is never blank, and anything you are unsure about can simply be left alone. Switching ACF off puts every default back; nothing is lost.

**Everything arrives pre-filled.** The first time an editor opens the admin with ACF Pro active, every field and every list — the three counters, the sectors, the eight wedges, the process steps, the quotes, the timeline, the kit, the FAQ, the popup's questions and options — is written once with exactly the text the site is already showing. Nothing on the page moves; you are simply editing real sentences instead of blank boxes. It runs once and never overwrites anything anyone has typed. If a field is emptied and the original wording is wanted back, or a theme update adds a new section, *Lemon Mint → Content* tops up whatever is still empty.

Two things stay empty on purpose: pictures that live inside the theme (they are not in the Media Library, so a reference would be broken — the page keeps showing them until a real image is uploaded), and the Project link on each wedge, since those projects may not exist on the site yet.

### Where things are edited

**Lemon Mint → Studio details** — email, phone, WhatsApp, address, city, hours, the reply-time promise, the hero video and its poster frame, the homepage headline, the showreel URL, and whether the contact page shows the live Google map.

**Lemon Mint → Homepage** — five tabs, in the order the page reads:

- *Why brands hire us*: kicker, headline (words in `[square brackets]` get the lemon marker), the three counters, the paragraph, and the sector words that run past in two rows.
- *Selected work (the wheel)*: kicker, headline, the wedge video (one file laid out as a grid of tiles, four across and two down) and the dive clip, then the eight wedges — pick a Project and the title and link fill themselves in.
- *How it works*: the timeline steps, the guarantee line and the button.
- *In their words*: the quotes, who said them, and a frame from each film. Approved **Testimonial** posts, if you use them, take priority over this list.
- *Closing call to action*: the headline and the three countdown routes.

**Lemon Mint → Services page** — the "how a film gets made" strip and the general FAQ (which carries FAQ markup for Google).

**Lemon Mint → Brief popup** — every question, hint and button in the "Start a production" popup, the wording after it sends, and the timing and budget options. The services offered come from the Services posts.

**Lemon Mint → Header, menu & footer** — the header button, the mint showreel band on the homepage, an optional footer line and the social links.

**Pages** — Contact, About and Studio each carry their own fields on the page itself (edit the page, scroll below the editor).

**Posts** — Projects, Services, Industries, People and Testimonials have ACF fields on their edit screens. They write to the same fields the theme has always used, so existing content carries over untouched and the old boxes simply step aside while ACF is active.

### For developers

Field groups are registered in PHP in `inc/acf/` — version-controlled, diffable and impossible to delete by accident. Groups created in the admin are saved to `/acf-json` and load from there. The templates never call `get_field()` directly: they read `lmf_opt()`, `lmf_field()` and `lmf_rows()` (in `inc/acf.php`), which apply the fallback rule, and the page content itself is assembled through the existing `lmf_home_content`, `lmf_contact_choices`, `lmf_cta_routes` and related filters.
