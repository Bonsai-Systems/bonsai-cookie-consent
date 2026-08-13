# Bonsai Cookie Consent - CookieScript

A WordPress plugin by [The Bonsai Digital Collective](https://thebonsaidigitalcollective.co.uk) that replaces YouTube embeds with a consent-safe placeholder until CookieScript marketing consent is granted.

> **Status:** Stable
> **Requires:** WordPress 6.4+ • PHP 7.4+

## What It Does

- Converts YouTube embeds to `youtube-nocookie`
- Adds CookieScript-compatible attributes:
  - `data-src`
  - `data-cookiecategory`
- Shows a consent placeholder before consent:
  - Thumbnail image
  - Darkened overlay
  - Centred consent message
  - Centred CTA link/button
- Automatically reveals the iframe when CookieScript loads the `src`

## Admin Settings

Path: **Settings > Cookie Video Consent**

- **Cookie category key** — CookieScript category key to assign to video embeds (default: `marketing`).
- **Default video background image URL** — If set, this overrides all YouTube thumbnails.
- **Consent text** — Overlay message shown before consent. If left empty, the plugin default is used.
- **Consent link URL (optional)** — If provided, CTA links to this URL. If empty, CTA attempts to open the CookieScript preferences popup.
- **Consent link label** — Text shown on the CTA button.

## Requirements

- WordPress 6.4+
- PHP 7.4+
- CookieScript installed and active on the site

## GitHub Updates

This plugin uses [`yahnis-elsts/plugin-update-checker`](https://github.com/YahnisElsts/plugin-update-checker) to deliver updates via GitHub Releases.

- Updater bootstrap is in the main plugin file.
- Composer dependency is in `composer.json`.
- Autoload path is `vendor/autoload.php`.

### Release Workflow

1. Commit plugin changes, including `vendor/` and `composer.lock`.
2. Bump `Version` in the main plugin file and `CCVE_COOKIESCRIPT_VERSION` constant.
3. Create a GitHub Release tagged `vX.X.X`.
4. Attach a release zip of the plugin folder.
5. WordPress admin update checks will detect the newer release automatically.

### Repository

```
https://github.com/Bonsai-Systems/bonsai-cookie-consent
```

This is the distribution repo that live sites' update checks pull from — it is intentionally separate from the `origin` dev remote (`gakdesign/bonsai-cookie-consent`) used for day-to-day development, matching the same split used by `bonsai-code-injector`. If the distribution repository is ever moved, update `CCVE_COOKIESCRIPT_GITHUB_REPOSITORY` in the main plugin file and the `Update URI` plugin header to match.

## Release Notes

See [CHANGELOG.md](CHANGELOG.md).

## Author

Ben Ervine / [The Bonsai Digital Collective](https://thebonsaidigitalcollective.co.uk)

## License

GPL-2.0+
