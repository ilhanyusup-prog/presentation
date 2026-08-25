# Weldman WordPress Theme

Lightweight classic WordPress theme for [weldman.ee](https://weldman.ee).
It replaces Elementor with clean PHP templates and fields editable through
the standard WordPress admin.

## Free plugin stack

- **Advanced Custom Fields Free** — required. The theme uses only free field
  types: Text, Textarea, WYSIWYG, Image, URL, Select and Tab.
- **Polylang Free** — required for ET / RU / EN.
- **Contact Form 7 Free** — optional. A native `wp_mail()` form is included.
- **Yoast SEO Free or Rank Math Free** — optional and intended to be installed
  only after the site is stable.

No ACF Pro features are used. In particular, the theme has no Flexible
Content, Repeater, Group or Options Page fields.

## Content model

### Homepage

The static front page receives one ACF Free field group named **Homepage
sections**. Its tabs organize fixed fields for:

1. Hero
2. Mission
3. Innovation
4. Quality
5. Partners
6. Contact form

`front-page.php` renders those sections in that fixed order. Mission,
Innovation and Quality share `template-parts/section-text-block.php`, with
separate prefixed fields such as `mission_title`, `innovation_title` and
`quality_title`.

Partners use six fixed slots:

- `partner_1_logo` / `partner_1_link`
- …
- `partner_6_logo` / `partner_6_link`

Empty partner slots are simply omitted.

### Site-wide contact and social data

ACF Free has no Options Page. Site-wide values therefore use WordPress core
Customizer settings under **Appearance → Customize → Weldman Contact &
Social**:

- Company address
- Phone 1 and Phone 2
- Email
- Facebook, Instagram, TikTok, YouTube, LinkedIn and WhatsApp URLs

These settings feed the footer, Kontakt page, contact form recipient and
`LocalBusiness` JSON-LD schema.

### Kontakt and SEO

- The **Contact page** template has ACF Free fields for intro text, optional
  map URL, form title and optional Contact Form 7 shortcode.
- Posts and pages receive a free `meta_description` textarea. If empty, the
  theme falls back to the excerpt/content.

## Folder structure

```text
weldman-theme/
├── style.css
├── functions.php
├── header.php / footer.php
├── front-page.php / page.php
├── archive.php / single.php / index.php / search.php
├── 404.php / comments.php / searchform.php
├── page-templates/
│   ├── template-sections.php    # Standard content page, e.g. Innovatsioon
│   └── template-contact.php
├── inc/
│   ├── acf-fields.php           # ACF Free fields only
│   ├── customizer.php           # Brand, contacts, phones and social URLs
│   ├── contact-form.php
│   ├── seo.php
│   └── template-functions.php
├── template-parts/
│   ├── section-hero.php
│   ├── section-text-block.php
│   ├── section-partners.php
│   ├── section-contact-form.php
│   └── content-post.php
├── assets/css/ and assets/js/
└── acf-json/
```

Field groups are registered in PHP with
`acf_add_local_field_group()`, so a fresh environment needs no manual import.
The `acf-json/` directory remains configured for versioning edits made in the
ACF admin UI.

## Page setup

| Page | Template | Notes |
|---|---|---|
| Avaleht | Default | Set as static front page in **Settings → Reading**, then fill the fixed Homepage sections fields. |
| Innovatsioon | Content page | Keep slug `/ecosystem/`; edit with the standard WordPress editor and featured image. |
| Kontakt | Contact page | Keep slug `/contact/`. |
| Õppimine | Custom menu link | Link directly to `wkk.ee`; no local page needed. |
| Blog | Posts page | Set in **Settings → Reading**. |

Assign Avaleht, Innovatsioon, Õppimine, Kontakt and Blog to the Primary Menu.
The ET/RU/EN switcher appears automatically after Polylang is configured.

## Installation and migration

0. Before migration, export the existing site through **Tools → Export** and
   separately download `/wp-content/uploads/`. Keep both backups outside the
   working environment.
1. Install and activate this theme.
2. Install **Advanced Custom Fields Free** and **Polylang Free**.
3. Create/assign the pages and menu listed above.
4. Fill contacts and social links in **Appearance → Customize → Weldman
   Contact & Social**.
5. Fill the fixed homepage fields and Kontakt fields; restore text and media
   from the backup.
6. Configure and translate content with Polylang.
7. Test responsive layouts and Lighthouse/PageSpeed.
8. Optionally install a free SEO and caching plugin after the site is stable.

## Performance and SEO

- Emoji, oEmbed discovery and unused block-library assets are removed.
- Images use WordPress attachment rendering with `srcset`/`sizes`; non-hero
  images are lazy-loaded.
- Google Fonts load non-blocking with `font-display: swap`.
- WordPress manages document titles and canonical URLs.
- The theme emits a manual meta description and `LocalBusiness` JSON-LD.
