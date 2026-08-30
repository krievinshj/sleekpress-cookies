=== SleekPress Cookies ===
Contributors: sleekpress
Tags: cookie consent, gdpr, google consent mode, cookie banner, cookie scanner
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, self-hosted cookie consent banner with a cookie scanner, optional AI categorisation, and Google Consent Mode v2 — no per-visit fees.

== Description ==

SleekPress Cookies is a free, self-hosted alternative to per-visit cookie-consent SaaS. It shows a consent banner, scans your site for the cookies it actually sets, lets visitors enable or disable cookie categories, and wires up Google Consent Mode v2 so your Google tags behave correctly with or without consent.

**Banner & preferences**

* Banner with **Accept**, **Decline** and **Adjust** buttons.
* Positions: bottom-left box, bottom-right box, or full-width bottom bar.
* "Adjust" opens a preferences modal with per-category toggles: Necessary, Functional, Analytics, Advertisement, Others.
* Each category lists the individual cookies it covers, with provider, lifetime and description.
* Floating "cookie settings" badge so visitors can change their mind later. Also available via the `[sleekpress_cookie_settings]` shortcode or any link with `class="spc-open-prefs"`.

**Cookie scanner**

* Fetches your home page and recent pages and detects well-known third-party scripts from a built-in database: Google Analytics 4, Google Tag Manager, Google Ads, Meta (Facebook) Pixel, YouTube, Vimeo, Hotjar, LinkedIn, TikTok, HubSpot, Stripe, Cloudflare, WordPress core, and more.
* Also reports cookies actually observed in real visitors' browsers (a tiny REST ping), so dynamically-set cookies show up too.
* Review the results, edit them, and add them to your visitor-facing cookie list with one click.

**AI categorisation (optional)**

* Add an OpenAI API key in Settings and the scanner can auto-assign a category and write a clear, policy-ready description for each discovered cookie.

**Customisation**

* Title, intro message and all button labels.
* Colours for the banner background, text, the Accept button, and the Decline/Adjust buttons; corner radius; light/dark theme.
* Privacy-policy link auto-fills from **Settings → Privacy** when you don't set one explicitly.

**Google Consent Mode v2**

* Emits `gtag('consent', 'default', { ... })` with everything denied except `security_storage` **before** any tag loads, so Google tags send only cookieless modelling pings until consent is given.
* Supports `url_passthrough` and `ads_data_redaction`, a configurable `wait_for_update` delay, and per-category default states.
* On every visitor choice it sends a `gtag('consent', 'update', …)` and pushes an `spc_consent_update` event to the dataLayer — handy as a Google Tag Manager trigger.

**Tags**

* Optionally enter a GTM container ID and the plugin prints the GTM snippet for you (after the consent defaults), or enter a GA4 Measurement ID to load gtag.js.
* Leave both empty if you already manage your tags (e.g. in GTM you installed yourself) — Consent Mode signalling still works.

== Installation ==

1. Upload the `sleekpress-cookies` folder to `/wp-content/plugins/`, or install the ZIP via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin.
3. Go to **Cookies** in the admin menu.
4. Run a scan on the **Cookie scanner** tab and add the detected cookies to your list.
5. Adjust appearance on **Banner & design**, review **Consent Mode**, and enter any GTM/GA4 ID or OpenAI key on **Settings**.

== Frequently Asked Questions ==

= Does Consent Mode work if my tags are in Google Tag Manager? =

Yes. The plugin pushes the `gtag('consent', …)` calls and a `spc_consent_update` dataLayer event regardless of how your tags are loaded. You can leave the GTM/GA4 fields empty.

= My theme hard-codes the GTM snippet. Will consent defaults still come first? =

Only if the GTM snippet runs after `wp_head()`. The plugin outputs its consent block at `wp_head` priority 0, so it beats anything added through `wp_head`. If your theme prints GTM directly in `header.php` above the `wp_head()` call, move it below that call, or paste your GTM ID into the plugin's Settings tab and let the plugin emit it.

= Is the scanner a full crawler? =

No. It does not execute JavaScript. It relies on a built-in signature database plus cookies reported by real visitors. That covers the vast majority of sites; for anything unusual you can add cookies manually.

= Where is the visitor's consent stored? =

In the visitor's browser only, in the `spc_consent` cookie.

== Changelog ==

= 1.3.1 =
* The cookie category labels (Necessary, Functional, Analytics, Advertisement, Others) and their descriptions also follow the Banner language setting now — visible both in the visitor's preferences modal and on the admin's Cookie list / Consent Mode screens. Latvian and Russian translations added.

= 1.3.0 =
* New "Banner language" setting (Auto / English / Latvian / Russian). It translates the modal's built-in strings ("Always active", "Show cookies", etc.), and tells the OpenAI cookie-description prompt which language to write in. On the Banner & design tab there's a "Load <Language> defaults" button that fills the editable text fields with translated defaults you can then tweak. Multilingual sites should leave this on Auto and rely on TranslatePress / WPML / Polylang for output translation as before.

= 1.2.4 =
* Inputs now have a smaller 0.5rem corner radius (new `--sp-control-radius` token).
* Colour inputs accept CSS variable references like `var(--theme-palette-color-1)` in addition to hex — handy for binding to your theme's design tokens. The swatch shows the resolved colour from the cascade, and the value passes through cleanly to the front-end banner.

= 1.2.3 =
* Admin layout: the content container is now ~1300px wide (was ~1024px). On the Banner & design screen the preview sidebar grew to 30rem, the preview banner uses the configured banner width, and its typography, padding and buttons now mirror the real front-end banner pixel-for-pixel — no more shrunken approximation.

= 1.2.2 =
* Added WooCommerce (woocommerce_cart_hash, woocommerce_items_in_cart, wp_woocommerce_session_*, woocommerce_recently_viewed, tk_ni) and Automattic Tracks (tk_ai, tk_qs, tk_lr, tk_or, tk_r3d, tk_tc) to the built-in scanner database. Scans now categorise the WooCommerce cart cookies as Necessary and the Tracks analytics cookies as Analytics with proper descriptions.

= 1.2.1 =
* Added Sourcebuster.js (the marketing-attribution library bundled by Fluent Forms, FluentCRM and others) to the built-in scanner database — sbjs_first, sbjs_first_add, sbjs_current, sbjs_current_add, sbjs_session, sbjs_udata, sbjs_migrations. Scans now categorise them as Advertisement with proper descriptions.

= 1.2.0 =
* Fixed (for real): the admin app's data calls now go through admin-ajax.php instead of /wp-json/. admin-ajax lives under /wp-admin/, so the login cookie is always sent to it — on some setups (Local/dev, certain host/proxy configs) the REST API was seeing the request as logged-out, which surfaced as "Cookie check failed". The handler just proxies internally to the same REST routes, so behaviour is unchanged. (The public consent ping still uses REST.)

= 1.1.5 =
* Reverted the wp.apiFetch routing from 1.1.4: its automatic nonce-refresh-and-retry could spin into a request loop on sites where the REST nonce never verifies. Back to a single plain fetch with the injected nonce; the "couldn't load" panel now also shows the error code.

= 1.1.4 =
* Fixed: admin REST calls could fail with "Cookie check failed" (invalid nonce). The admin app now routes requests through WordPress's own wp.apiFetch, which manages the REST nonce — including refreshing it automatically when it expires.

= 1.1.3 =
* The admin's "couldn't load" screen now shows the actual error and the endpoint it tried, to make diagnosing connectivity issues easier.

= 1.1.2 =
* Fixed: admin REST calls returned 403 when the site's configured URL didn't exactly match the URL being browsed (common on local dev) — the cross-origin request dropped the auth cookie. The admin app now uses a same-origin (relative) REST URL and also sends the nonce as a query parameter.

= 1.1.1 =
* Fixed: the new admin screens didn't load because the REST client captured an empty API base before configuration ran (module load order). The client now resolves its config per request.

= 1.1.0 =
* New admin: rebuilt as a single Vue 3 app (vue-router, scoped components) on a shared "SleekPress UI" kit for a modern, consistent look across SleekPress plugins. Settings and the cookie list are now saved over the REST API with inline toasts; the Banner & design screen has a live preview.
* Build: the admin app now ships pre-built in assets/admin/dist; developers build it with `npm install && npm run build` (Vite). Front-end banner is unchanged.

= 1.0.4 =
* Links pointing to #cookies-settings (and a few aliases) now open the preferences modal — handy for a "Cookie settings" link in the footer when the floating badge is disabled. Also works when loading a page with that hash already in the URL.

= 1.0.3 =
* Banner heading enlarged to 1.25rem.
* Revisit badge now uses a cookie SVG icon (inherits the Accept-button colour) instead of an emoji.

= 1.0.2 =
* Banner now sizes from a "Banner width" setting (default 26.25rem ≈ 420px) on the Banner & design tab; applies to the box layouts.
* All front-end and admin CSS now uses rem/em units instead of px.

= 1.0.1 =
* Fixed: banner button/background colours set in the admin were ignored due to a CSS specificity clash; the generated inline styles now correctly override the defaults.

= 1.0.0 =
* Initial release: consent banner (Accept/Decline/Adjust), preferences modal with category toggles, cookie scanner with built-in service database and live cookie reporting, optional OpenAI categorisation, banner customisation, Google Consent Mode v2, optional GTM/GA4 injection, reopen-preferences shortcode.

== Upgrade Notice ==

= 1.0.0 =
First public release.
