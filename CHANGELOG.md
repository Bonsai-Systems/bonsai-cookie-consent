# Changelog

All notable changes to this plugin are documented in this file.
This project adheres to [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [2.3.1] - 2026-07-23

### Fixed
- [composer.json] Fatal error (`Cannot declare class ComposerAutoloaderInit88bd1240a06e12371573341aa3549092, because the name is already in use`) when this plugin was active alongside another Bonsai plugin bundling the same version of `yahnis-elsts/plugin-update-checker` (e.g. Bonsai Code Injector). Both plugins shipped a byte-identical `composer.json`, so Composer generated the same autoloader class name in both `vendor/` directories — the second plugin to load fataled on class redeclaration.
- Added a unique `name` field to `composer.json` and regenerated `vendor/` from a clean install, giving this plugin's Composer autoloader class a distinct name (`ComposerAutoloaderInit059726867b99d530d594054e5e01c204`).

### Security note for other Bonsai plugins
This is a structural risk, not unique to this plugin — any two Bonsai plugins that vendor the same library via an unmodified/identical `composer.json` will collide the same way if ever active together on one site. `bonsai-code-injector` (and any other plugin bundling `yahnis-elsts/plugin-update-checker` without a unique `composer.json` `name`) should get the same fix.

---

## [2.3.0] - 2026-07-23

### Added
- [cookie-consent-video-embed-CookieScript.php] "Consent manager" setting (CookieScript / Cookiebot) so one plugin install can support either platform on a per-site basis.
- [assets/js/ccve-cookiescript.js] Blocking attributes now switch at runtime: `data-src`/`data-cookiecategory` for CookieScript, `data-cookieblock-src`/`data-cookieconsent` for Cookiebot.
- [assets/js/ccve-cookiescript.js] `openCookiebotSettings()` calling `Cookiebot.renew()`; the fallback CTA now routes to the correct manager's reopen call based on the setting, with a same-page fallback to the other manager's API if the primary one isn't present.

### Changed
- [cookie-consent-video-embed-CookieScript.php] Admin page title, header, and field descriptions generalised — no longer assume CookieScript is the only supported platform.
- [readme.txt] Description, features list, and FAQ updated for dual consent-manager support; Stable tag synced to `2.3.0`.

---

## [2.2.1] - 2026-07-23

### Fixed
- [assets/js/ccve-cookiescript.js] Fallback "Update cookie preferences" CTA (shown when no Consent Link URL is configured) is now wired to actually open the CookieScript preferences popup — previously the click handler was never attached, so the button did nothing.

### Added
- [cookie-consent-video-embed-CookieScript.php] Uninstall hook to delete `ccve_cookiescript_options` when the plugin is removed.

### Changed
- [readme.txt] Stable tag synced to `2.2.1` (was out of date at `2.1.0`).

---

## [2.2.0] - 2026-06-24

### Changed
- Plugin renamed to **Bonsai Cookie Consent - CookieScript** to align with Bonsai Digital Collective branding.
- Plugin URI and Author URI updated to `https://thebonsaidigitalcollective.co.uk`.
- Update URI aligned with `The-Bonsai-Digital-Collective` GitHub org.
- `Requires at least` bumped to `6.4` (reflects actual use of `NodeList.forEach`, `MutationObserver`, `aspect-ratio`, and `inset` CSS).
- Admin settings page now shows a branded Bonsai header bar (`#ee4367`).
- Consent overlay CTA hover state updated to use Bonsai brand colour (`#ee4367`).

---

## [2.1.0] - 2026-06-24

### Added
- GitHub-based update checks via Plugin Update Checker (`yahnis-elsts/plugin-update-checker` v5.7).
- Composer setup for updater dependency (`composer.json`, `composer.lock`, `vendor/`).

### Changed
- Added `Update URI` header and updater bootstrap to main plugin file.

---

## [2.0.0] - 2026-06-24

### Added
- WordPress settings page at **Settings > Cookie Video Consent**.
- Configurable cookie category key (default: `marketing`).
- Configurable default background image URL to override YouTube thumbnails.
- Configurable consent text with fallback default.
- Configurable consent CTA URL and label.
- Consent placeholder UI with dark overlay and centered CTA.
- Frontend CSS and JS enqueued as versioned assets via `wp_enqueue_scripts`.
- Plugin settings passed to JS via `wp_localize_script`.

### Changed
- Refactored plugin from inline footer script to proper enqueued JS and CSS assets.
- Upgraded plugin header metadata and internal option handling.

### Security
- All settings fields sanitised on save and escaped on output.
- Settings page gated behind `manage_options` capability check.

---

## [1.7] - 2025-01-01

### Added
- Initial YouTube iframe CookieScript compatibility logic.

[2.3.1]: https://github.com/The-Bonsai-Digital-Collective/bonsai-cookie-consent/compare/v2.3.0...v2.3.1
[2.3.0]: https://github.com/The-Bonsai-Digital-Collective/bonsai-cookie-consent/compare/v2.2.1...v2.3.0
[2.2.1]: https://github.com/The-Bonsai-Digital-Collective/bonsai-cookie-consent/compare/v2.2.0...v2.2.1
[2.2.0]: https://github.com/The-Bonsai-Digital-Collective/bonsai-cookie-consent/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/The-Bonsai-Digital-Collective/bonsai-cookie-consent/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/The-Bonsai-Digital-Collective/bonsai-cookie-consent/compare/v1.7...v2.0.0
[1.7]: https://github.com/The-Bonsai-Digital-Collective/bonsai-cookie-consent/releases/tag/v1.7
