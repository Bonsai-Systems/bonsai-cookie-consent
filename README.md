# Bonsai Cookie Consent - CookieScript

A WordPress plugin by [The Bonsai Digital Collective](https://thebonsaidigitalcollective.co.uk) that replaces YouTube and Vimeo embeds with a consent-safe placeholder until marketing consent is granted. Works with **CookieScript** or **Cookiebot**, chosen per site in the plugin settings.

> **Status:** Stable
> **Requires:** WordPress 6.4+ • PHP 7.4+

## What It Does

- Rewrites YouTube and Vimeo iframes server-side (full-page output buffer, `includes/iframe-blocker.php`) so the browser never receives a live `src` — nothing loads before consent, including iframes echoed directly by themes or ACF templates
- Converts YouTube embeds to `youtube-nocookie`
- Adds `dnt=1` to Vimeo embeds (Vimeo's do-not-track mode) and fetches the placeholder thumbnail via Vimeo oEmbed
- Adds the blocking attributes for the selected consent manager:

  | Consent manager | Source attribute | Category attribute |
  |---|---|---|
  | CookieScript | `data-src` | `data-cookiecategory` |
  | Cookiebot | `data-cookieblock-src` | `data-cookieconsent` |

- Shows a consent placeholder before consent:
  - Thumbnail image
  - Darkened overlay
  - Centred consent message
  - Centred CTA link/button
- Automatically reveals the iframe when the consent manager loads the `src`
- Front-end script still handles iframes injected after page load (modals, load-more) as a fallback

### Disabling server-side blocking

Skipped automatically in wp-admin, AJAX, REST, feeds and page builder previews (Elementor, Divi, Beaver Builder, Bricks, Oxygen, WPBakery). To turn it off on a site, fall back to the script only:

```php
add_filter( 'ccve_cookiescript_block_iframes', '__return_false' );
```

## Admin Settings

Path: **Settings > Cookie Video Consent**

- **Consent manager** — CookieScript (default) or Cookiebot. Controls which blocking attributes are written and which preferences popup the CTA opens.
- **Cookie category key** — Consent category assigned to video embeds, used for both managers (default: `marketing`). On Cookiebot sites this must be one of Cookiebot's categories: `marketing`, `statistics` or `preferences`.
- **Default video background image URL** — If set, this overrides all YouTube and Vimeo thumbnails.
- **Consent text** — Overlay message shown before consent. If left empty, the plugin default is used.
- **Consent link URL (optional)** — If provided, CTA links to this URL. If empty, CTA opens the selected manager's preferences popup (CookieScript's popup, or `Cookiebot.renew()`), falling back to the other manager's API if the selected one isn't on the page.
- **Consent link label** — Text shown on the CTA button.

## Requirements

- WordPress 6.4+
- PHP 7.4+
- CookieScript or Cookiebot installed and active on the site

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

This is the distribution repo that live sites' update checks pull from — it is intentionally separate from the `origin` dev remote (`Bonsai-Systems/bonsai-cookie-consent`) used for day-to-day development, matching the same split used by `bonsai-code-injector`. If the distribution repository is ever moved, update `CCVE_COOKIESCRIPT_GITHUB_REPOSITORY` in the main plugin file and the `Update URI` plugin header to match.

## Release Notes

See [CHANGELOG.md](CHANGELOG.md).

## Author

Ben Ervine / [The Bonsai Digital Collective](https://thebonsaidigitalcollective.co.uk)

## License

GPL-2.0+
