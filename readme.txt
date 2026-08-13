=== Cookie Consent Video Embed - CookieScript ===
Contributors: benervine
Tags: cookiescript, cookiebot, youtube, consent, gdpr, video
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display YouTube video thumbnails with a consent overlay until marketing consent is granted via CookieScript or Cookiebot.

== Description ==

Cookie Consent Video Embed blocks YouTube iframes until consent is given, then lets the active consent manager (CookieScript or Cookiebot) load the real player.

When consent is missing, visitors see:

* A thumbnail image (YouTube thumbnail by default)
* A dark overlay for legibility
* Centered consent message text
* A configurable consent CTA link/button

Plugin settings are available under Settings > Cookie Video Consent.

== Features ==

* Choose the active consent manager — CookieScript or Cookiebot — from the settings page
* Converts YouTube embeds to youtube-nocookie URLs
* Writes the correct blocking attributes for the selected manager (`data-src`/`data-cookiecategory` for CookieScript, `data-cookieblock-src`/`data-cookieconsent` for Cookiebot)
* Shows consent placeholder before cookie approval
* Optional global default background image that overrides YouTube thumbnails
* Customisable consent text with safe default fallback
* Customisable consent link label and URL
* If no link URL is set, clicking the CTA opens the active consent manager's preferences popup (`CookieScript.show()` or `Cookiebot.renew()`)

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin in WordPress admin.
3. Go to Settings > Cookie Video Consent.
4. Configure consent text, link, and optional default background image.

== GitHub Updates ==

This plugin is configured to use the Plugin Update Checker library for GitHub-based updates.

To enable admin updates from GitHub:

1. Push the full plugin to a GitHub repository, including the `vendor/` folder.
2. Ensure the repository URL in the main plugin file matches your repo.
3. Create GitHub Releases and attach the plugin zip as a release asset.
4. Keep the plugin version header higher than the installed version for each release.

== Frequently Asked Questions ==

= What videos are supported? =

Current support targets YouTube embeds.

= Which consent managers are supported? =

CookieScript and Cookiebot. Pick the one active on your site under Settings > Cookie Video Consent > Consent manager. Only one can be active at a time — this setting must match whichever CMP is actually installed on the site, or blocked videos will never unlock.

= What if I leave Consent Link URL empty? =

The button tries to open the active consent manager's settings popup directly.

= Can I force one placeholder image for all videos? =

Yes. Set Default video background image URL in plugin settings.

== Changelog ==

= 2.3.2 =
* Fixed the Update URI header and GitHub repository constant, which pointed at the wrong org (The-Bonsai-Digital-Collective instead of Bonsai-Systems) — update checks were silently pointed at a repo with no releases, so this plugin could never see new versions in wp-admin.

= 2.3.1 =
* Fixed a fatal error ("Cannot declare class ComposerAutoloaderInit...") on sites also running another Bonsai plugin that bundles the same Composer library (e.g. Bonsai Code Injector). Both plugins previously shipped an identical composer.json, which made Composer generate the same autoloader class name in both — activating both together crashed the site. This plugin's composer.json now declares a unique package name so its autoloader class no longer collides.

= 2.3.0 =
* Added a Consent Manager setting (CookieScript / Cookiebot) so one plugin install can support either platform.
* Blocking attributes and the "reopen preferences" call now switch based on the selected consent manager.
* Generalised admin page copy and readme so it no longer assumes CookieScript specifically.

= 2.2.1 =
* Fixed the "Update cookie preferences" fallback CTA (shown when no Consent Link URL is set) — it previously did nothing on click; it now correctly opens the CookieScript preferences popup.
* Added uninstall cleanup to remove plugin settings when the plugin is deleted.

= 2.2.0 =
* Renamed plugin to Bonsai Cookie Consent - CookieScript and updated branding/URIs.
* Added branded admin settings header.

= 2.1.0 =
* Added GitHub-based update checking (Plugin Update Checker)
* Added composer dependency and autoload bootstrap for updater
* Added Update URI and release-asset update flow support

= 2.0.0 =
* Added full settings page under Settings > Cookie Video Consent
* Added consent placeholder UI (thumbnail, dark overlay, centered text, CTA)
* Added global default background image override
* Added custom consent text with fallback default
* Added custom consent link URL and label
* Refactored inline footer script into enqueued JS/CSS assets
* Improved plugin structure and sanitisation/escaping

= 1.7 =
* Initial YouTube CookieScript attribute handling
