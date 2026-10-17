# WPEssential Theme ↔ Core Plugin Contract

Theme sirf data/hooks deti hai. Demo import, preview, plugin manager aur license **WPEssential Core** plugin mein hain (WP.org guideline: themes plugin install / content import nahi kar saktin).

## Theme jo deti hai
| Hook / function | Kaam |
|---|---|
| `wpessential_theme_supports()` / filter `wpessential_theme_supports` | Theme version, builders, features |
| `wpessential_demo_manifest_url()` / filter `wpessential_demos_manifest_url` | Remote `demos.json` URL |
| action `wpessential_demo_imported( $demo_id, $builder )` | Core import ke baad fire kare; theme Elementor Kit ko theme.json tokens se re-sync karti hai |
| `wpessential_sync_elementor_kit()` | Manual Kit sync |
| Constant `WPESSENTIAL_CORE_VERSION` | Core define kare to theme ka "install Core" notice band |

## Demo manifest
Schema: `docs/demo-manifest.schema.json`, example: `docs/demos.example.json`.
Har demo ke `builders.gutenberg` aur/ya `builders.elementor` packages — same demo, dono builders.

## Plugin auto-management (Core)
User builder select kare → us package ki `plugins` list resolve:
1. `required` + missing → install (`Plugin_Upgrader`, source `wporg` via `plugins_api`, `zip` via URL, `licensed` via signed URL) → activate.
2. `required` + inactive → activate.
3. `conflicts` + active → deactivate (e.g. Gutenberg demo par Elementor).
4. **Delete** sirf user ke explicit checkbox/confirm par, kabhi silently nahi.
5. Har step AJAX/REST queue mein, progress UI (Figma: Dashboard — Import Wizard). Capability: `install_plugins`, `activate_plugins`, nonce.

## Preview
Card par "Preview" → modal mein `preview_url` iframe + Desktop/Tablet/Mobile width toggle + "Import this demo" (Figma: Demo Preview Modal).

## License (premium)
**Type:** license key + per-domain activation (site limit e.g. 1 / 5 / unlimited), yearly expiry; expire hone par purane imported demos chalte rahen, sirf naye Pro imports + updates band.

Core mein adapter interface, provider settings se select:

```php
interface WPEssential_License_Provider {
    public function activate( string $key, string $domain ): array;   // ['status','expires','sites_used','sites_max']
    public function deactivate( string $key, string $domain ): bool;
    public function check( string $key, string $domain ): array;      // daily cron
    public function get_package_url( string $key, string $demo_id, string $builder ): string; // short-lived signed URL
}
```

Implementations:
- **Custom REST** — `POST /v1/license/{activate|deactivate|check}`, `GET /v1/demos/{id}/{builder}/download` → HMAC-signed URL, 10 min TTL.
- **EDD Software Licensing** — `edd_action=activate_license|deactivate_license|check_license`, `item_id`, `url`.
- **WooCommerce** (License Manager for WooCommerce) — `/wp-json/lmfwc/v2/licenses/{activate|deactivate|validate}/{key}`.

Key `wp_options` mein store (autoload off), status transient 24h cache, kabhi bhi frontend ya REST response mein expose na ho.

## Layout hooks

| Hook | Type | Purpose |
|---|---|---|
| `wpessential_layout_areas` | filter | Map of area => template part slugs the theme lets Core replace (default `header`, `footer`). |
| `wpessential_layout_html` | filter | Return HTML for `( $area, $slug )` to replace the theme's header/footer part. Return `null` to keep the theme part. |
| `wpessential_layout_rendered` | action | Fires after Core/Elementor output replaced an area. |
| `wpessential_free_shipping_threshold` | filter | Override the free-shipping amount used by the cart progress bar. |
| `wpessential_back_to_top` | filter | Return `false` to disable the back-to-top button. |
| `wpessential_scheme_toggle` | filter | Return `false` to remove the visitor light/dark toggle. |
| `wpessential_scheme_follow_os` | filter | Return `false` to ignore the device colour preference on first visit. |


## Bundled Core

The Core module now ships in `core/` and is loaded by the theme when the standalone plugin is absent.
See [`core/README.md`](../core/README.md) for the importer, package format, license adapters and hooks,
and for the steps to extract it into its own plugin.
