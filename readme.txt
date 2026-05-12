=== SleekPress Cookies ===
Contributors: sleekpress
Tags: cookie consent, gdpr, google consent mode, cookie banner, cookie scanner
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.0.0
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

= 1.0.0 =
* Initial release: consent banner (Accept/Decline/Adjust), preferences modal with category toggles, cookie scanner with built-in service database and live cookie reporting, optional OpenAI categorisation, banner customisation, Google Consent Mode v2, optional GTM/GA4 injection, reopen-preferences shortcode.

== Upgrade Notice ==

= 1.0.0 =
First public release.
