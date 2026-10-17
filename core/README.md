# WPEssential Core

Demo import, plugin manager and license activation for the WPEssential theme.

It ships inside the theme (`core/`) and loads from `functions.php` only when the standalone
plugin is not active. Nothing in this folder calls theme code, so it can become a plugin unchanged.

## Moving it to a plugin

1. Copy `core/` to `wp-content/plugins/wpessential-core/`. `wpessential-core.php` already has the plugin header.
2. Move `core/demos/` with it (it already lives inside `core/`).
3. Delete `core/` from the theme, or keep it: the plugin loads first, defines `WPESSENTIAL_CORE_VERSION`,
   and the theme skips its bundled copy. Sites can also disable the bundled copy with
   `add_filter( 'wpessential_load_bundled_core', '__return_false' );`.

## Structure

| File | Role |
| --- | --- |
| `wpessential-core.php` | Bootstrap, PSR-4 autoloader, `wpessential_core_url()` |
| `src/Admin.php` | `WPEssential` admin menu, welcome redirect after theme activation, app assets |
| `src/Rest.php` | REST API `wpessential/v1` (all routes need `manage_options` + REST nonce) |
| `src/Demos.php` | Bundled demos (`demos/*/demo.json`) + remote manifest, package download |
| `src/Importer.php` | Step-based importer with logs, resume, crash-skip and rollback |
| `src/Plugins.php` | Install / activate / deactivate / delete (wordpress.org, zip URL, bundled zip, licensed) |
| `src/License.php` + `src/License/*` | Key + domain activation; adapters for the custom API, EDD and WooCommerce API Manager |
| `src/Status.php` | Server checks shared by System Status and the import preflight |
| `assets/admin.js`, `assets/admin.css` | Dashboard app (no build step) |

## How an import runs

`POST /import/start` creates a job (stored in the `wpessential_core_job` option). The browser then calls
`POST /import/run` in a loop; each call works for about 6 seconds (`wpessential_core_import_budget`) and
returns progress plus new log lines (`info`, `success`, `warning`, `error`, each with an optional fix hint).

Steps: preflight → cleanup (optional) → package → plugins → media → terms → content → products → menus → settings → finalize.

- **Resume.** Progress is saved after every item. A closed tab, network drop or timeout resumes at the same item;
  the browser retries with back-off before asking the user.
- **Crash-safe.** An item that kills PHP twice is skipped and logged as an error instead of blocking the import.
- **Fresh request after plugins.** Plugins activated during the import are booted in the next request before content uses them.
- **Rollback.** Every created post, attachment, term and menu is recorded (`wpessential_core_imported`) together with
  the previous value of every option it changes. `POST /import/remove` deletes them and restores the options.

## Demo package (`content.json`)

```json
{
  "media":    [ { "key": "hero", "file": "hero.webp", "source": "theme|package|url", "alt": "…" } ],
  "terms":    [ { "key": "cat-design", "taxonomy": "category", "name": "Design", "slug": "design", "meta": {} } ],
  "content":  [ { "key": "home", "type": "page", "title": "Home", "template": "page-landing", "content": "{{pattern:wpessential/hero-dark}}", "featured": "hero", "terms": { "category": [ "cat-design" ] } } ],
  "products": [ { "key": "p-1", "kind": "simple|variable|grouped", "name": "…", "price": "79", "sale": "", "categories": [], "image": "hero", "attributes": [], "children": [] } ],
  "menus":    [ { "name": "Main menu", "location": "primary", "items": [ { "title": "About", "page": "about" }, { "title": "Shop", "woo": "shop" } ] } ],
  "settings": { "front_page": "home", "blog_page": "blog", "options": {}, "woocommerce": { "free_shipping": 75, "options": {} } }
}
```

Tokens inside strings: `{{pattern:slug}}` (pattern markup, so pages follow theme updates until edited), `{{media:key}}`,
`{{media_id:key}}`, `{{page:key}}`, `{{post_id:key}}`, `{{term_link:key}}`, `{{home}}`, `{{theme:path}}`.
Only options on the allow-list (`wpessential_core_import_options`) and `woocommerce_*` options are written.

## License providers

```php
add_filter( 'wpessential_core_license_config', function () {
	return array(
		'provider'   => 'edd',                       // custom | edd | woo
		'endpoint'   => 'https://wpessential.org/',  // store URL or API base
		'product_id' => '123',
	);
} );
```

Add a provider with `wpessential_core_license_adapters` (map of key => class extending `WPEssential\Core\License\Adapter`).
The key is re-checked daily; when the server is unreachable the last known state is kept.

## Hooks

| Hook | Type | Purpose |
| --- | --- | --- |
| `wpessential_load_bundled_core` | filter | Return false to skip the copy inside the theme |
| `wpessential_core_manifest_url` | filter | Remote demo manifest URL |
| `wpessential_core_demos` | filter | Final demo list |
| `wpessential_core_plugin_package` | filter | Download URL for a plugin |
| `wpessential_core_import_budget` | filter | Seconds of work per request (default 6) |
| `wpessential_core_import_options` | filter | Options a demo may set |
| `wpessential_core_license_config` | filter | License provider settings |
| `wpessential_core_license_adapters` | filter | Provider adapters |
| `wpessential_core_edd_package_url` | filter | Package URL from an EDD response |
| `wpessential_demo_imported` | action | After an import: `( $demo_id, $builder, $map )` |
