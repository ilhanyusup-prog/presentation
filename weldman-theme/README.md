# Weldman WordPress Theme

Lightweight, custom classic (non-block/non-FSE) WordPress theme for
[weldman.ee](https://weldman.ee) — a welding training center in Tallinn,
Estonia. Built to replace the current Elementor-based site with clean,
version-controlled code while keeping content editing simple through the
standard WordPress admin (Pages + ACF fields), no page builder involved.

## Stack

- **Theme:** custom classic PHP theme (based on the `_s` / underscores
  structure), no build step required — plain CSS/JS.
- **Flexible content:** [Advanced Custom Fields](https://www.advancedcustomfields.com/)
  (free or Pro). Pro is needed for the Flexible Content field used on the
  homepage/`template-sections.php`.
- **Multilingual:** [Polylang](https://polylang.pro/) (ET / RU / EN).
- **Contact form:** built-in lightweight form (name/email/message via
  `wp_mail()`, with a honeypot + timing check for spam) or, optionally,
  [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) — paste a
  CF7 shortcode into the "Contact Form 7 shortcode" field on the relevant
  section/page and it takes over automatically.
- **SEO:** handled manually by the theme at first (title tag, meta
  description, semantic HTML, `LocalBusiness` JSON-LD in the footer). Only
  once the site is live and stable, install Yoast SEO or Rank Math on top —
  see "Order of work" below.

## Required plugins

| Plugin | Required | Notes |
|---|---|---|
| Advanced Custom Fields (free or Pro) | **Yes** | Pro needed for the homepage Flexible Content field. |
| Polylang | Yes, for multilingual (ET/RU/EN) | Free version is sufficient. |
| Contact Form 7 | Optional | Only if you don't want the built-in native form. |
| Yoast SEO / Rank Math | Optional, install **last** | See step 13 below. |

The theme fails gracefully (no fatal errors) if ACF is missing, and shows an
admin notice prompting you to install it.

## Folder structure

```
weldman-theme/
├── style.css                    # Theme header (see assets/css for real styles)
├── functions.php                # Enqueues, menus, thumbnails, cleanup, ACF options page
├── header.php / footer.php
├── comments.php / searchform.php / 404.php / search.php
├── index.php / page.php / front-page.php
├── archive.php / single.php
├── page-templates/
│   ├── template-sections.php    # Reusable "Flexible sections" page (e.g. Innovatsioon)
│   └── template-contact.php     # Kontakt page (intro, address/phone/email, map, form)
├── inc/
│   ├── acf-fields.php           # ACF field group registration (code-based, versioned)
│   ├── customizer.php           # Brand color theme_mod's
│   ├── seo.php                  # Meta description + LocalBusiness JSON-LD
│   ├── contact-form.php         # Native contact form handler + CF7 passthrough
│   └── template-functions.php   # weldman_field(), weldman_option(), weldman_image(), ...
├── template-parts/
│   ├── section-hero.php
│   ├── section-text-block.php   # Reusable: Missioon / Innovatsioon / Kvaliteet
│   ├── section-partners.php
│   ├── section-contact-form.php
│   └── content-post.php
├── assets/
│   ├── css/ (reset.css, style.css, components.css, responsive.css)
│   ├── js/main.js                # Mobile menu toggle, no dependencies
│   └── images/
└── acf-json/                    # Auto-sync target for ACF UI edits (see acf-json/README.md)
```

### A note on `template-parts/section-*.php` vs. the brief

The ACF "Text block with image" layout is explicitly reusable across
Missioon / Innovatsioon / Kvaliteet (same fields: title, text, image, image
position). Rather than duplicating near-identical markup into three files
(`section-mission.php`, `section-innovation.php`, `section-quality.php`),
the theme implements **one** partial, `template-parts/section-text-block.php`,
rendered once per Flexible Content row. This keeps the three homepage blocks
byte-for-byte consistent and easier to restyle later. If distinct markup per
block is ever needed, split this file — the ACF field names stay the same.

## ACF field groups (registered in `inc/acf-fields.php`)

1. **Page sections** (`page_sections`, Flexible Content) — attached to the
   site's Front Page and to any page using the *Flexible sections* page
   template. Layouts: `Hero`, `Text block with image`, `Partners / Logos`,
   `Contact form`.
2. **Contacts & Social** (ACF Options Page, *Site Options* in the admin
   menu) — `company_address`, `phone_numbers` (repeater), `email`,
   `social_links` (repeater: platform + URL). Used by the footer, the
   Kontakt page, and the JSON-LD schema.
3. **Contact page** — attached to the *Contact page* page template:
   `contact_intro` (WYSIWYG), `contact_map_embed` (Google Maps embed URL),
   `contact_form_title`, `cf7_shortcode`.
4. **SEO** — `meta_description` on Posts and Pages (sidebar meta box).

Fields are registered in PHP (`acf_add_local_field_group`) so they're
correct and versioned from the first deploy with zero manual export step.
If an editor later tweaks a group from **wp-admin → Custom Fields**, ACF
will write the change into `acf-json/` automatically (already wired up via
`acf/settings/save_json` in `functions.php`) — commit that file and it will
take precedence over the PHP-registered version on the next deploy.

## Pages to create after activating the theme

| Page | Template | Notes |
|---|---|---|
| Avaleht (home) | *Default template* | Set as the static front page in **Settings → Reading**. Add "Page sections" rows: Hero, Text block ×3 (Missioon/Innovatsioon/Kvaliteet), Partners/Logos, Contact form. |
| Innovatsioon | *Flexible sections* | Slug `/ecosystem/` to keep the current URL. |
| Kontakt | *Contact page* | Slug `/contact/`. |
| Õppimine | — | Not a real page — add a **Custom Link** menu item pointing to the external `wkk.ee` URL. |
| Blog | — | Set as the "Posts page" in **Settings → Reading**, or link to `/blog/`. |

Register the **Primary Menu** location (Appearance → Menus) with: Avaleht,
Innovatsioon, Õppimine (custom link to wkk.ee), Kontakt, Blog. The language
switcher (ET/RU/EN) appears automatically at the end of the primary
navigation once Polylang is configured.

## Performance & SEO notes

- Emoji scripts/styles, oEmbed discovery links, RSD/WLW/generator meta tags
  and the unused block-library CSS are all stripped in `functions.php`.
- Google Fonts are preloaded with `preconnect` + `font-display: swap` and
  never block rendering.
- All content images use `wp_get_attachment_image()` / `the_post_thumbnail()`
  (automatic `srcset`/`sizes`) and `loading="lazy"`, except the hero image
  which is eager-loaded since it's the page's LCP element.
- A `LocalBusiness` JSON-LD block is printed in the footer using the Site
  Options data (address/phone/email/social links).
- No SEO plugin is required to ship the site; `inc/seo.php` provides a
  manual `<meta name="description">` and delegates the `<title>` tag to
  core's `add_theme_support( 'title-tag' )`.
- Recommend a caching plugin (WP Super Cache, W3 Total Cache, or WP Rocket)
  separately — this is intentionally not the theme's responsibility.

## Suggested order of work

0. **Before touching anything:** export the current site content via
   **Tools → Export** (WXR file) and separately download the entire
   `/wp-content/uploads/` folder. Store both outside of the working
   environment.
1. Install this theme and activate it.
2. Install & activate ACF (Pro if you need the Flexible Content layouts) and
   Polylang.
3. Create the pages listed above, assign templates, set the front page in
   Settings → Reading, and build the Primary Menu.
4. Fill in **Site Options** (address, phone numbers, email, social links).
5. Add the homepage's Flexible Content sections and the Kontakt page fields;
   re-import text/images from the WXR backup as needed.
6. Configure Polylang and duplicate content into ET/RU/EN.
7. Run Lighthouse/PageSpeed Insights and test on real devices; adjust
   `assets/css/responsive.css` as needed.
8. Only **after** the site is stable, install Yoast SEO or Rank Math and
   configure the XML sitemap on top of the finished theme.
