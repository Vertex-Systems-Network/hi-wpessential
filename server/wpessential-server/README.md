# WPEssential Server

Runs on the seller site (wpessential.org) with WooCommerce. It is the online demo library, license server and subscription system for every site that uses the WPEssential theme. It is **not** shipped inside the theme.

## What it does

| Area | Details |
| --- | --- |
| Plans | Free, Monthly, Quarterly, Yearly, Lifetime, Enterprise, White Label — created as hidden virtual WooCommerce products on activation. Price, billing period, website limit, Pro access and white label are editable on each product (General tab). |
| Licenses | Created when a plan order is paid; key `WPE-XXXX-XXXX-XXXX-XXXX`. Status active / expired / cancelled / revoked. Refunds revoke. |
| Domain limits | Each plan allows N production websites (0 = unlimited). localhost, `*.local`, `*.test`, `staging.*` and common host staging domains are free. |
| Auto-renewal | Daily cron creates a renewal order `remind_days` before the end date. A gateway with saved cards charges it through the `wpes_charge_renewal` filter; otherwise the customer gets the WooCommerce invoice with a pay link. Paid renewals extend from the old end date. A grace period keeps sites working while the renewal is paid. |
| Reminders / expiry | Customers with auto-renew off get one reminder; licenses past end + grace expire and the customer is emailed. |
| Upgrades | Upgrade to any higher plan; the unused part of the current plan is credited at checkout. |
| Customer actions | My Account → Licenses: copy key, see websites, remove a website, renew now, auto-renew on/off (cancel), upgrade, new key. |
| Dashboard sign-in | Theme dashboards sign up / sign in with the REST API, get a token (30 days), list licenses, use a license on the site, toggle auto-renew and open a one-time signed-in checkout link. New accounts receive a Free license. |
| Demo library | Demos post type (WPE Server → Demos) with niches, pages, highlights, preview URL, thumbnail and a private zip per builder. Free packages download directly; Pro packages need an active Pro license on an activated domain and get a 15-minute signed link. Premium plugin zips use the same private folder (`plugin-<slug>.zip`). |

## REST API (`/wp-json/wpessential-server/v1`)

| Route | Auth | Purpose |
| --- | --- | --- |
| `POST license/activate` `license, domain` | — | Activate a domain |
| `POST license/deactivate` | — | Release a domain |
| `POST license/check` | — | Daily check; fails when the domain was removed in the account |
| `POST license/package` `license, domain, item, context` | — | Signed download URL (demo builder or `plugin`) |
| `GET demos` | — | Manifest read by the theme |
| `GET download` | signed | Package stream |
| `GET plans` | — | Plans for pricing tables |
| `POST account/register` `name, email, password, site` | — | New customer + Free license, returns token |
| `POST account/login` `login, password, site` | — | Returns token (rate-limited 10 / 15 min / IP) |
| `GET account/me` | Bearer | User, licenses (with keys and domains), plans |
| `POST account/license` `license_id, action, domain` | Bearer | `auto_renew_on`, `auto_renew_off`, `cancel`, `deactivate_domain`, `regenerate` |
| `POST account/checkout` `plan, license_id, mode` | Bearer | One-time link that signs in and opens checkout (`buy`, `renew`, `upgrade`) |
| `POST account/logout` | Bearer | Revoke the token |

The theme points at this server with `wpessential_core_license_config` (`api`, `endpoint`) and `wpessential_core_manifest_url`.

## Hooks

`wpes_charge_renewal` (filter, return true when the gateway charged the token), `wpes_license_created`, `wpes_license_activated`, `wpes_license_action` (actions), `wpes_staging_patterns`, `wpes_rate_limit` (filters).
