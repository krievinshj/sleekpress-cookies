# Changelog

All notable changes to **SleekPress Cookies** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.0.2]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.0.2
[1.0.1]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.0.1
[1.0.0]: https://github.com/krievinshj/sleekpress-cookies/releases/tag/v1.0.0
