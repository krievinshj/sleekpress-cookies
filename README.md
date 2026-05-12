# SleekPress Cookies

A lightweight, self-hosted cookie-consent plugin for WordPress — a free alternative to per-visit SaaS banners.

## What it does

- **Consent banner** (bottom-left box, bottom-right box, or full-width bar) with **Accept / Decline / Adjust** buttons.
- **Preferences modal** ("Adjust") with per-category toggles (Necessary, Functional, Analytics, Advertisement, Others) and an expandable list of the cookies in each category.
- **Cookie scanner** (admin → *Cookies → Cookie scanner*): fetches your home page + recent pages, detects known third-party scripts (Google Analytics, GTM, Google Ads, Meta Pixel, YouTube, Vimeo, Hotjar, LinkedIn, TikTok, HubSpot, Stripe, Cloudflare …) from a built-in database, and also lists cookies actually seen in real visitors' browsers (reported via a tiny REST ping). One click to add them to your cookie list.
- **AI categorisation** (optional): give it an OpenAI API key in Settings and the scanner can auto-assign a category and write a plain-English description for each discovered cookie.
- **Banner customisation**: title, intro text, button labels, colours (background, text, Accept button, Decline/Adjust button), corner radius, light/dark, position, a floating "cookie settings" badge, and a privacy-policy link that auto-fills from *Settings → Privacy* if you don't set one.
- **Google Consent Mode v2**: pushes `gtag('consent', 'default', …)` (everything denied except `security_storage`) **before any tag loads**, plus `url_passthrough` and `ads_data_redaction`, and a `consent` `update` the moment the visitor chooses. Configurable per-category defaults and `wait_for_update`. Also pushes a `spc_consent_update` dataLayer event you can use as a GTM trigger.
- **Tags**: optional GTM container ID (plugin prints the snippet for you, after the consent defaults) or GA4 Measurement ID (plugin loads gtag.js). Leave both empty if you already manage tags yourself — Consent Mode still works.

## Notes / limitations

- Consent Mode signals only work correctly if the consent code runs **before** Google's tag. The plugin prints its `<head>` block at `wp_head` priority 0. If your theme hard-codes the GTM snippet *above* `wp_head()` in `header.php`, move it below `wp_head()` or paste the GTM ID into the plugin's Settings tab instead.
- The scanner does not execute JavaScript, so it relies on (a) the built-in signature database and (b) cookies reported by real visitors. It is not a full headless-browser crawler.
- Reopen the preferences modal anywhere with the `[sleekpress_cookie_settings]` shortcode or a link with `class="spc-open-prefs"`.

## Development (admin app)

The wp-admin UI is a Vue 3 single-page app in [`admin-app/`](admin-app/), built
on the shared **SleekPress UI** kit vendored at
[`vendor/sleekpress-ui/`](vendor/sleekpress-ui/) (see its README). The front-end
consent banner is plain JS and has no build step.

```bash
npm install        # once
npm run dev        # Vite dev server (for working on admin-app/)
npm run build      # → assets/admin/dist/admin.js + admin.css  (commit these)
```

The PHP side just registers the menu page and calls
`SleekPress_UI::enqueue_app()` / `::render_root()`; all admin data flows through
the `spc/v1` REST routes (`/settings`, `/cookies`, `/scan`, `/ai-categorize`,
`/merge`, `/observe`).

## Data stored

- `spc_settings` — all settings (incl. the OpenAI key).
- `spc_cookies` — the cookie list shown to visitors.
- `spc_observed_cookies` — cookie names seen in visitors' browsers (capped, non-autoloaded).
- `spc_last_scan` — metadata about the last scan.
- Visitor consent is stored in their browser only, in the `spc_consent` cookie.
