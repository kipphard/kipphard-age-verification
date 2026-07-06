=== Kipphard Age Verification ===
Contributors: kipphard
Tags: age verification, age gate, woocommerce, gdpr, altersverifikation
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

GDPR-compliant full-screen age gate for WordPress and WooCommerce. Date-of-birth check runs entirely in the browser — no personal data sent to the server.

== Description ==

**Kipphard Age Verification** protects your WordPress site or specific pages from access by underage visitors. A full-screen overlay appears before any content is visible. The visitor confirms their minimum age by clicking a button or entering their date of birth.

**What this plugin does:**

* Full-screen fail-safe overlay — visible by default via CSS; JavaScript removes it only when a valid confirmation cookie exists or the visitor confirms
* Two verification modes: simple confirmation (Yes / No) or date-of-birth entry
* Date-of-birth calculation runs entirely in the visitor's browser — no personal data is ever sent to the server
* Configurable scope: entire website or specific pages by ID
* Customisable overlay background colour, accent colour (buttons), and optional logo
* Configurable decline action: show an inline message or redirect to a URL
* Functional cookie (`kipphard_age_verification_ok=1`) stores confirmation for 1–3650 days (no tracking, no profiling, no PII)
* Optional branding notice in the overlay footer — can be switched off in the settings
* No external dependencies, no CDN, no third-party requests

**What this plugin does NOT do:**

This overlay is a confirmation barrier — it honestly communicates an age restriction and makes access harder for underage visitors. It is **not** a hard identity verification. A genuine age check requires ID-based methods (document scan, PostIdent, or equivalent). For legally critical content regulated by youth-protection law, consult a qualified lawyer. Pro-roadmap items include ID-based verification integrations.

**Free vs. Pro:**

The free version gates the entire site or individual pages, with full control over text, design, and cookie duration. **Age Verification Pro** adds:

* WooCommerce integration: gate only products in specific categories


*Hinweis (DE): Dieses Plugin zeigt einen DSGVO-konformen Vollbild-Overlay (Age Gate), bevor Besucher auf deine Inhalte zugreifen können. Die Altersberechnung erfolgt ausschließlich im Browser – keine personenbezogenen Daten werden an den Server übertragen. Die Benutzeroberfläche ist auf Deutsch verfügbar.*

== Installation ==

1. Upload the `kipphard-age-verification` folder to `/wp-content/plugins/`, or install it from the Plugins screen in your WordPress admin.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to **Altersverifikation** in the admin menu and configure your settings.
4. Save — the overlay is active immediately.

== Frequently Asked Questions ==

= Is this a legally binding age check? =

No. This overlay is a confirmation barrier that makes it harder for underage visitors to access content and provides an honest notice. It is not a hard identity verification. A genuine check requires ID-based methods (document scan, PostIdent, or equivalent). For content regulated by youth-protection law, get qualified legal advice.

= Is any personal data stored or transmitted? =

No. When using the "date of birth" mode, the date is calculated entirely in the visitor's browser and never sent to the server. After confirmation, only an anonymous functional cookie (`kipphard_age_verification_ok=1`) is set.

= What happens if JavaScript is disabled? =

The overlay stays visible (fail-safe). Content is inaccessible without JavaScript.

= Does the plugin work with WooCommerce? =

The free version gates the entire site or specific pages. The Pro version enables gating at the WooCommerce product-category level.

= Can I customise the design? =

Yes. You can choose the overlay background colour, accent colour, and an optional logo directly in the settings, and you can switch off the branding notice in the overlay footer.

= Where and how is the cookie set? =

The cookie `kipphard_age_verification_ok=1` is set by JavaScript after confirmation — no server request is involved. It applies to path `/`, has a configurable lifetime (1–3650 days), and is marked `SameSite=Lax`.

== Screenshots ==

1. The age gate overlay — full-screen, customisable heading, message, and buttons.
2. Date-of-birth entry mode with day / month / year fields.
3. The admin settings page: scope, verification mode, text, design, and cookie duration.
4. A note about the WooCommerce Pro add-on for site owners who need category-level gating.

== Changelog ==

= 0.4.0 =
* Renamed to Kipphard Age Verification. Unique plugin-specific prefixes for all options, nonces, admin-post actions, the cookie name, and filter hooks. The branding-notice toggle is now a free setting (previously gated behind a non-functional license check). Removed the geo-targeting and custom CSS fields, which were unimplemented placeholders never gated by any real Pro mechanism. The only remaining Pro feature is genuine: WooCommerce product-category gating, shipped as a separate add-on class.

= 0.3.1 =
* Shared Kipphard design system (kip-ui) for a consistent, theme-safe look across all Kipphard plugins.

= 0.3.0 =
* Shared design system integration; admin UI polish.

= 0.2.0 =
* English source baseline with a German (de_DE) translation.

= 0.1.0 =
* Initial release.
* Full-screen fail-safe overlay (CSS visible by default; JS removes it on valid cookie or confirmation).
* Two modes: simple confirmation (Yes / No) and date-of-birth entry.
* Configurable scope: entire site or specific pages by ID.
* Customisable overlay colour, accent colour, logo URL, and button labels.
* Configurable decline action: show message or redirect to URL.
* Functional cookie with configurable duration (no PII, no tracking).
* Admin settings page with Pro upgrade teaser.
* Pro hooks: WooCommerce category gating, geo-targeting, white-label, custom CSS.
* No external dependencies, no CDN, no third-party requests.

== Upgrade Notice ==

= 0.4.0 =
Renamed to Kipphard Age Verification; unique prefixes for all options/hooks; no license-gated code remains in the free build.
