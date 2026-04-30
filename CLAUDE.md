# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A WordPress plugin ("DSFOOBOO Football Data – League tables & schedules - data provided by AFVD") that fetches American football league standings and game schedules from a publicly available XML API at `vereine.football-verband.de`, stores them locally, and displays them via shortcodes.

This plugin is independent and not affiliated with the AFVD (American Football Verband Deutschland) or any of its member associations.

The plugin has been renamed twice: originally "AFVData" (`afvdata_*` prefix), then briefly "FootballData" (`footballdata_*` prefix in v2.4.x), and from v2.5.0 onward "DSFOOBOO Football Data" (`dsfooboo_football_data_*` prefix). The two prior prefixes live on only as deprecated shortcode aliases and as the source side of a one-time migration.

## Architecture

```
dsfooboo-football-data.php                              → Bootstrap: constants, requires, activation/deactivation hooks
uninstall.php                                           → Cleanup: drops tables (all known prefixes) and deletes options
includes/
  class-dsfooboo-football-data-plugin.php               → Singleton orchestrator, DB version + legacy migration, wires all classes
  class-dsfooboo-football-data-db.php                   → Schema (dbDelta), upsert/query methods, all $wpdb->prepare(), legacy table rename
  class-dsfooboo-football-data-importer.php             → Fetches XML via wp_remote_get(), parses with simplexml, upserts into DB
  class-dsfooboo-football-data-admin.php                → Top-level admin menu (4 tabs: Settings, Leagues, Import, Info), AJAX handlers
  class-dsfooboo-football-data-shortcodes.php           → [dsfooboo_football_data_*] shortcodes + legacy [footballdata_*] / [afvdata_*] aliases
  class-dsfooboo-football-data-cron.php                 → WP-Cron scheduling for automatic imports
admin/
  views/page-settings.php                               → Main admin page with tab navigation
  views/partial-leagues.php                             → League configuration form
  views/partial-import.php                              → Import status, raw data viewer, shortcode & attribute reference
  views/partial-info.php                                → Disclaimer, contact info, logo
  css/admin.css                                         → Admin styles
  js/admin.js                                           → Color picker, league management, import AJAX, raw data viewer
  img/logo.png                                          → Plugin logo (transparent PNG)
public/
  css/dsfooboo-football-data.css                        → Frontend table styles with CSS custom properties
```

## Data Flow

1. XML API (`vereine.football-verband.de/xmltabelle.php5?Liga=XXX` / `xmlspielplan.php5?Liga=XXX`)
2. `DSFooboo_Football_Data_Importer` fetches + parses XML, upserts into `{prefix}dsfooboo_football_data_standings` / `{prefix}dsfooboo_football_data_schedule`
3. Shortcodes query `DSFooboo_Football_Data_DB` methods and render HTML tables

Import uses upsert (`INSERT ... ON DUPLICATE KEY UPDATE`) + stale-row cleanup, so there's no empty-table window during sync.

## Database Tables

- `{prefix}dsfooboo_football_data_standings` — league standings, unique on `(liga_code, gruppe, kuerzel)`
- `{prefix}dsfooboo_football_data_schedule` — game schedule, unique on `(liga_code, game_id)`

Created via `dbDelta()` on activation. Dropped via `uninstall.php`. On first load after upgrade from any earlier version, `DSFooboo_Football_Data_DB::migrate_from_legacy()` renames whichever older table set exists (`{prefix}footballdata_*` or `{prefix}afvdata_*`) in place; the option `dsfooboo_football_data_legacy_migrated` is set to skip the migration on subsequent loads.

## Configuration

All stored in `wp_options` under `dsfooboo_football_data_*`:
- `..._api_base_url` — XML endpoint base URL
- `..._sync_interval` — cron interval (manual/hourly/twicedaily/daily)
- `..._leagues` — serialized array of league configs (slug, label, liga_code, team_name, active)
- `..._color_header_bg` / `..._color_header_text` / `..._color_highlight_bg` — table colors
- `..._last_sync` — timestamp of last full sync
- `..._db_version` — DB schema version for migrations
- `..._legacy_migrated` — set to `1` once the one-time migration from older prefixes has run

## Shortcodes

- `[dsfooboo_football_data_standings league="slug"]` — standings table. Attrs: `group`, `highlight`, `class`
- `[dsfooboo_football_data_schedule league="slug"]` — game schedule. Attrs: `group`, `home_only`, `show` (all/upcoming/past), `limit`, `highlight`, `class`

Deprecated aliases `[footballdata_*]` and `[afvdata_*]` are registered for backwards compatibility.

The `league` attribute accepts either a configured slug or a raw liga code. Groups are auto-detected from imported data.

## Naming Conventions

- All PHP classes: `DSFooboo_Football_Data_*`
- All PHP constants: `DSFOOBOO_FOOTBALL_DATA_*`
- All options, DB tables, AJAX actions, cron hook, text domain, menu page slug, CSS classes, CSS custom properties: `dsfooboo_football_data_*` (snake_case with underscores everywhere — also in CSS classes, deviating from typical hyphenated convention; matches the user's stated preference)
- Cron hook: `dsfooboo_football_data_sync` (legacy `footballdata_sync` and `afvdata_sync` are unscheduled during migration)
- Plugin file and class file names use hyphens (WP convention for plugin slug paths): `dsfooboo-football-data.php`, `class-dsfooboo-football-data-*.php`, `dsfooboo-football-data.css`
- GitHub repo: `slayer01/dsfooboo-football-data`; Pages URL `https://slayer01.github.io/dsfooboo-football-data/`. Local working-copy folder is still named `afvdata` for legacy reasons.

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
- To test: drop the folder into `wp-content/plugins/`, activate, configure under "DSFOOBOO Football Data" in the admin menu
- The plugin is i18n-ready (text domain: `dsfooboo_football_data`) but no translation files exist yet
- Frontend CSS is only enqueued on pages that actually use a shortcode
- Table colors are configurable in Settings with WordPress color picker; active theme palette colors are offered as presets
- The default table header color is neutral (#333) — users can customize via Settings or theme CSS targeting `.dsfooboo_football_data_league_table th`
- GitHub Actions pipeline builds a ZIP and creates a release on every push
- Version must be bumped in `dsfooboo-football-data.php` (header + constant) and `readme.txt` (stable tag) before each push
- Contact: Daniel Schmidt-Richert, afvdata@foo.boo
