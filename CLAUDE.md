# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A WordPress plugin ("Gridirontables AFVD – League tables & schedules - data provided by AFVD") that fetches American football league standings and game schedules from a publicly available XML API at `vereine.football-verband.de`, stores them locally, and displays them via shortcodes.

This plugin is independent and not affiliated with the AFVD (American Football Verband Deutschland) or any of its member associations.

The plugin has been renamed three times: originally "AFVData" (`afvdata_*` prefix), then "FootballData" (`footballdata_*` prefix in v2.4.x), then "DSFOOBOO Football Data" (`dsfooboo_football_data_*` prefix in v2.5.x), and from v3.0.0 onward "Gridirontables AFVD" (`gridirontables_afvd_*` snake-case prefix; `gridirontables-afvd` hyphenated text domain to satisfy the WordPress.org plugin slug requirement). The three prior prefixes live on only as the source side of a one-time options/tables migration — the deprecated shortcode aliases were removed in v3.0.2 to satisfy the WordPress.org Plugin Check prefix rule, so pages still using `[dsfooboo_football_data_*]`, `[footballdata_*]` or `[afvdata_*]` must be updated to `[gridirontables_afvd_*]`.

## Architecture

```
gridirontables-afvd.php                                 → Bootstrap: constants, requires, activation/deactivation hooks
uninstall.php                                           → Cleanup: drops tables (all known prefixes) and deletes options
includes/
  class-gridirontables-afvd-plugin.php                  → Singleton orchestrator, DB version + legacy migration, wires all classes
  class-gridirontables-afvd-db.php                      → Schema (dbDelta), upsert/query methods, all $wpdb->prepare(), legacy table rename
  class-gridirontables-afvd-importer.php                → Fetches XML via wp_remote_get(), parses with simplexml, upserts into DB
  class-gridirontables-afvd-admin.php                   → Top-level admin menu (4 tabs: Settings, Leagues, Import, Info), AJAX handlers
  class-gridirontables-afvd-shortcodes.php              → [gridirontables_afvd_*] shortcodes (legacy aliases removed in v3.0.2)
  class-gridirontables-afvd-cron.php                    → WP-Cron scheduling for automatic imports
admin/
  views/page-settings.php                               → Main admin page with tab navigation
  views/partial-leagues.php                             → League configuration form
  views/partial-import.php                              → Import status, raw data viewer, shortcode & attribute reference
  views/partial-info.php                                → Disclaimer, contact info, logo
  css/admin.css                                         → Admin styles
  js/admin.js                                           → Color picker, league management, import AJAX, raw data viewer
  img/logo.png                                          → Plugin logo (transparent PNG)
public/
  css/gridirontables-afvd.css                           → Frontend table styles with CSS custom properties
```

## Data Flow

1. XML API (`vereine.football-verband.de/xmltabelle.php5?Liga=XXX` / `xmlspielplan.php5?Liga=XXX`)
2. `Gridirontables_AFVD_Importer` fetches + parses XML, upserts into `{prefix}gridirontables_afvd_standings` / `{prefix}gridirontables_afvd_schedule`
3. Shortcodes query `Gridirontables_AFVD_DB` methods and render HTML tables

Import uses upsert (`INSERT ... ON DUPLICATE KEY UPDATE`) + stale-row cleanup, so there's no empty-table window during sync.

## Database Tables

- `{prefix}gridirontables_afvd_standings` — league standings, unique on `(liga_code, gruppe, kuerzel)`
- `{prefix}gridirontables_afvd_schedule` — game schedule, unique on `(liga_code, game_id)`

Created via `dbDelta()` on activation. Dropped via `uninstall.php`. On first load after upgrade from any earlier version, `Gridirontables_AFVD_DB::migrate_from_legacy()` renames whichever older table set exists (`{prefix}dsfooboo_football_data_*`, `{prefix}footballdata_*`, or `{prefix}afvdata_*`) in place; the option `gridirontables_afvd_legacy_migrated` is set to skip the migration on subsequent loads.

## Configuration

All stored in `wp_options` under `gridirontables_afvd_*`:
- `..._api_base_url` — XML endpoint base URL
- `..._sync_interval` — cron interval (manual/hourly/twicedaily/daily)
- `..._leagues` — serialized array of league configs (slug, label, liga_code, team_name, active)
- `..._color_header_bg` / `..._color_header_text` / `..._color_highlight_bg` — table colors
- `..._last_sync` — timestamp of last full sync
- `..._db_version` — DB schema version for migrations
- `..._legacy_migrated` — set to `1` once the one-time migration from older prefixes has run

## Shortcodes

- `[gridirontables_afvd_standings league="slug"]` — standings table. Attrs: `group`, `highlight`, `class`
- `[gridirontables_afvd_schedule league="slug"]` — game schedule. Attrs: `group`, `home_only`, `show` (all/upcoming/past), `limit`, `highlight`, `class`

The `league` attribute accepts either a configured slug or a raw liga code. Groups are auto-detected from imported data.

## Naming Conventions

- All PHP classes: `Gridirontables_AFVD_*`
- All PHP constants: `GRIDIRONTABLES_AFVD_*`
- All options, DB tables, AJAX actions, cron hook, menu page slug, CSS classes, CSS custom properties: `gridirontables_afvd_*` (snake_case with underscores everywhere — also in CSS classes, deviating from typical hyphenated convention; matches the user's stated preference)
- **Text domain is the one exception**: `gridirontables-afvd` (hyphens) — WordPress.org's automated plugin scan requires the text domain header to be lowercase letters, numbers, and hyphens only, and to match the plugin slug. So `__('...', 'gridirontables-afvd')` not `'gridirontables_afvd'`.
- Cron hook: `gridirontables_afvd_sync` (legacy `dsfooboo_football_data_sync`, `footballdata_sync`, and `afvdata_sync` are unscheduled during migration)
- Plugin file and class file names use hyphens (WP convention for plugin slug paths): `gridirontables-afvd.php`, `class-gridirontables-afvd-*.php`, `gridirontables-afvd.css`
- GitHub repo: `slayer01/gridirontables-afvd`; Pages URL `https://slayer01.github.io/gridirontables-afvd/`. Local working-copy folder is still named `afvdata` for legacy reasons.

## Security Conventions

- All DB queries via `$wpdb->prepare()`
- All admin forms use nonces (`wp_nonce_field` / `check_admin_referer`)
- All admin handlers check `current_user_can('manage_options')`
- All `$_POST` / `$_GET` access via `wp_unslash()` + sanitization
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`
- All PHP files guarded with `defined('ABSPATH') || exit`
- AJAX endpoints use `check_ajax_referer()`
- Redirects use `wp_safe_redirect()`

## Development Notes

- This is a standalone WordPress plugin — no build step, no npm, no composer
- To test: drop the folder into `wp-content/plugins/`, activate, configure under "Gridirontables AFVD" in the admin menu
- The plugin is i18n-ready (text domain: `gridirontables-afvd`) but no translation files exist yet
- Frontend CSS is only enqueued on pages that actually use a shortcode
- Table colors are configurable in Settings with WordPress color picker; active theme palette colors are offered as presets
- The default table header color is neutral (#333) — users can customize via Settings or theme CSS targeting `.gridirontables_afvd_league_table th`
- GitHub Actions pipeline builds a ZIP and creates a release on every push
- Version must be bumped in `gridirontables-afvd.php` (header + constant) and `readme.txt` (stable tag) before each push
- Contact: Daniel Schmidt-Richert, afvdata@foo.boo
