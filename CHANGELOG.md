# Changelog

All notable changes to **SleekPress Cookies** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-05-12

### Fixed
- **The admin app's data layer now uses `admin-ajax.php` instead of the REST API.** Repeated `403 rest_cookie_invalid_nonce` ("Cookie check failed") on some environments came down to the `/wp-json/` request being processed as *logged-out* (the `wp_<hash>` auth cookie is scoped to `/wp-admin/` and isn't sent to `/wp-json/`, and the `/`-scoped `wp_logged_in_…` cookie wasn't authenticating the REST request there). `admin-ajax.php` lives under `/wp-admin/`, so the auth cookie is always sent. New `SPC_Ajax` handler (`wp_ajax_spc_api`) verifies an admin-ajax nonce + `manage_options`, then dispatches internally to the existing `/spc/v1/*` routes via `rest_do_request()` — zero duplicated controller logic. The public `/spc/v1/observe` ping (used by the front-end banner) still uses REST.

### Changed
- `@sleekpress/ui`: `SleekPress_UI::enqueue_app()` accepts an `ajax_action` arg; when set it exposes `ajaxUrl`/`ajaxAction`/`ajaxNonce` in the boot config. The kit's `createApi()` automatically prefers the admin-ajax transport when that config is present, falling back to a plain REST fetch otherwise.

## [1.1.5] - 2026-05-12

### Changed
- Reverted the `wp.apiFetch` routing introduced in 1.1.4: on sites where the `wp_rest` nonce never verifies (host/cookie mismatch, stale admin-page cache, …) its automatic nonce-refresh-and-retry would loop, flooding the server with requests. The kit's REST client is back to a single plain `fetch()` per request with the injected nonce (sent as `X-WP-Nonce` header + `_wpnonce` query param). Dropped the `wp-api-fetch` script dependency. The "couldn't load" panel now also surfaces the REST error `code`.

## [1.1.4] - 2026-05-12

### Fixed
- Admin REST calls could fail with `rest_cookie_invalid_nonce` ("Cookie check failed"). The kit's REST client (`createApi()`) now routes through `window.wp.apiFetch` when available — WordPress's own REST client, which carries a managed `wp_rest` nonce and auto-refreshes it on expiry. `SleekPress_UI::enqueue_app()` adds `wp-api-fetch` as a script dependency. A plain-`fetch` fallback (with the injected nonce, sent as both `X-WP-Nonce` header and `_wpnonce` query param) is kept for non-wp-admin contexts.

## [1.1.3] - 2026-05-12

### Changed
- The admin "couldn't load" state now surfaces the HTTP status / error message and the REST endpoint it attempted, instead of a generic message — easier to diagnose connectivity / auth problems.

## [1.1.2] - 2026-05-12

### Fixed
- Admin REST requests returned **403 Forbidden** when WordPress's configured site URL didn't exactly match the host/scheme being browsed (very common on Local/dev): the request became cross-origin, the browser dropped the auth cookie, and WP saw a logged-out request. `SleekPress_UI::enqueue_app()` now exposes a path-relative `restBase`/`restRoot` (always same-origin; works with both pretty permalinks and `?rest_route=`), and `createApi()` also appends the nonce as a `_wpnonce` query parameter in addition to the `X-WP-Nonce` header.

## [1.1.1] - 2026-05-12

### Fixed
- The new admin screens failed to load (blank content area): `store.js` created the REST client at module-evaluation time, before `main.js` called `configureApi()`, so it captured an empty `restBase` and every request hit the wrong URL. `createApi()` in `@sleekpress/ui` now resolves `restBase`/`nonce` lazily per request. Also added a "Retry" affordance and a proper loading state to the app shell.

## [1.1.0] - 2026-05-12

### Added
- **SleekPress UI kit** (`vendor/sleekpress-ui/`): a reusable Vue 3 admin component library — design tokens, ~17 components (`SpAppShell`, `SpCard`, `SpFormRow`, `SpButton`, `SpToggle`, `SpSelect`, `SpModal`, `SpTable`, `SpNotice`, `SpBadge`, …), `useApi`/`useToast` composables, an importable Vite base config, and a PHP loader (`SleekPress_UI::enqueue_app()` / `render_root()`). Vendored here, with a README + `bin/sync.sh` for reuse across other SleekPress plugins.
- Live banner preview on the Banner & design screen.
- REST endpoints: `GET/POST /spc/v1/settings` and `GET/POST /spc/v1/cookies`; `SPC_Settings::sanitize()` for unified validation.

### Changed
- The entire admin is now a single Vue 3 single-page app (`admin-app/`, hash router) using the kit — modern, consistent, JS-driven. It saves over REST with inline toast feedback instead of full-page form submits.
- Build step added: `package.json` + `vite.config.js`; the app is bundled to `assets/admin/dist/admin.js` + `admin.css` (committed). Run `npm install && npm run build` after changing `admin-app/`.

### Removed
- The old PHP-rendered admin screens (`view_*`, `handle_save`, form helpers in `SPC_Admin`) and `assets/{css,js}/spc-admin.*`.

### Unchanged
- The front-end consent banner, Consent Mode v2 output, and all visitor-facing behaviour.

## [1.0.4] - 2026-05-12

### Added
- Plain anchor links to `#cookies-settings` (and aliases `#cookie-settings`, `#cookies-preferences`, `#cookie-preferences`, `#cookies-consent`, `#cookie-consent`) open the preferences modal — no shortcode or special class needed. Also triggered on page load / `hashchange` when the URL contains one of those fragments, so cross-page links like `/contact/#cookies-settings` work.

## [1.0.3] - 2026-05-12

### Changed
- Banner heading font size increased to `1.25rem`.
- The floating "cookie settings" badge now uses an inline cookie SVG icon (drawn with `currentColor`, inheriting the Accept-button colour via `--spc-primary`) instead of the 🍪 emoji.

## [1.0.2] - 2026-05-12

### Added
- "Banner width" setting on the *Banner & design* tab (default `26.25rem` ≈ 420px), exposed as the `--spc-width` custom property. Applies to the bottom-left / bottom-right box layouts; the full-width bar ignores it.

### Changed
- All front-end and admin CSS (and the dynamically-set widths in the scanner table) now use `rem` / `em` units instead of `px`. The configurable corner-radius value is converted to `rem` on output.

## [1.0.1] - 2026-05-12

### Fixed
- Banner button / background colours from the *Banner & design* tab were ignored because the generated inline CSS targeted `:root` while the bundled stylesheet defines the colour variables on `#spc-root` (higher specificity). The inline overrides now target `#spc-root` as well.

## [1.0.0] - 2026-05-12

### Added
- Consent banner with **Accept / Decline / Adjust** buttons; positions: bottom-left box, bottom-right box, full-width bottom bar.
- Preferences modal with per-category toggles (Necessary, Functional, Analytics, Advertisement, Others) and an expandable list of the cookies in each category.
- Cookie scanner (admin): fetches the home page + recent pages, detects known third-party scripts from a built-in database (Google Analytics 4, Google Tag Manager, Google Ads, Meta Pixel, YouTube, Vimeo, Hotjar, LinkedIn, TikTok, HubSpot, Stripe, Cloudflare, WordPress core), and lists cookies observed in real visitors' browsers via a REST ping. One-click "add to cookie list".
- Optional OpenAI-powered categorisation: auto-assigns a category and writes a plain-English description for discovered cookies.
- Banner customisation: title, intro message, button labels, colours (background, text, Accept button, Decline/Adjust button), corner radius, light/dark theme, floating "cookie settings" badge, optional branding.
- Privacy-policy link that auto-fills from the WordPress *Settings → Privacy* page when no explicit URL is set.
- Google Consent Mode v2: `gtag('consent', 'default', …)` (everything denied except `security_storage`) emitted before tags load, plus `url_passthrough`, `ads_data_redaction`, configurable per-category defaults and `wait_for_update`. Emits a `spc_consent_update` dataLayer event on every choice for use as a GTM trigger.
- Optional Google Tag Manager container ID (plugin prints the snippet after the consent defaults) or GA4 Measurement ID (plugin loads gtag.js). Both optional — Consent Mode works without them.
- `[sleekpress_cookie_settings]` shortcode and `.spc-open-prefs` link hook to reopen the preferences modal.

[1.2.0]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.2.0
[1.1.5]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.1.5
[1.1.4]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.1.4
[1.1.3]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.1.3
[1.1.2]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.1.2
[1.1.1]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.1.1
[1.1.0]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.1.0
[1.0.4]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.0.4
[1.0.3]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.0.3
[1.0.2]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.0.2
[1.0.1]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.0.1
[1.0.0]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.0.0
