# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A WordPress plugin ("AFVData – League Tables & Schedules") that fetches American football league standings and game schedules from a publicly available XML API at `vereine.football-verband.de`, stores them locally, and displays them via shortcodes.

This plugin is independent and not affiliated with the AFVD (American Football Verband Deutschland) or any of its member associations.

## Architecture

```
afvd-data.php              → Bootstrap: constants, requires, activation/deactivation hooks
uninstall.php              → Cleanup: drops tables and deletes options
includes/
  class-afvd-data.php      → Singleton orchestrator (AFVData_Plugin), DB version check, wires all classes
  class-afvd-db.php        → Schema (dbDelta), upsert/query methods, all $wpdb->prepare()
  class-afvd-importer.php  → Fetches XML via wp_remote_get(), parses with simplexml, upserts into DB
  class-afvd-admin.php     → Top-level admin menu "AFVData" (4 tabs: Settings, Leagues, Import, Info), AJAX handlers
  class-afvd-shortcodes.php→ [afvdata_standings] and [afvdata_schedule] shortcodes
  class-afvd-cron.php      → WP-Cron scheduling for automatic imports
admin/
  views/page-settings.php  → Main admin page with tab navigation
  views/partial-leagues.php→ League configuration form
  views/partial-import.php → Import status, raw data viewer, shortcode & attribute reference
  views/partial-info.php   → Disclaimer, contact info, logo
  css/admin.css            → Admin styles
  js/admin.js              → Color picker, league management, import AJAX, raw data viewer
  img/logo.png             → Plugin logo (transparent PNG)
public/
  css/afvd-data.css        → Frontend table styles with CSS custom properties
```

## Data Flow

1. XML API (`vereine.football-verband.de/xmltabelle.php5?Liga=XXX` / `xmlspielplan.php5?Liga=XXX`)
2. `AFVData_Importer` fetches + parses XML, upserts into `{prefix}afvdata_standings` / `{prefix}afvdata_schedule`
3. Shortcodes query `AFVData_DB` methods and render HTML tables

Import uses upsert (`INSERT ... ON DUPLICATE KEY UPDATE`) + stale-row cleanup, so there's no empty-table window during sync.

## Database Tables

- `{prefix}afvdata_standings` — league standings, unique on `(liga_code, gruppe, kuerzel)`
- `{prefix}afvdata_schedule` — game schedule, unique on `(liga_code, game_id)`

Created via `dbDelta()` on activation. Dropped via `uninstall.php`.

## Configuration

All stored in `wp_options`:
- `afvdata_api_base_url` — XML endpoint base URL
- `afvdata_sync_interval` — cron interval (manual/hourly/twicedaily/daily)
- `afvdata_leagues` — serialized array of league configs (slug, label, liga_code, team_name, active)
- `afvdata_color_header_bg` — table header background color
- `afvdata_color_header_text` — table header text color
- `afvdata_color_highlight_bg` — highlight row background color
- `afvdata_last_sync` — timestamp of last full sync
- `afvdata_db_version` — DB schema version for migrations

## Shortcodes

- `[afvdata_standings league="slug"]` — standings table. Attrs: `group`, `highlight`, `class`
- `[afvdata_schedule league="slug"]` — game schedule. Attrs: `group`, `home_only`, `show` (all/upcoming/past), `limit`, `highlight`, `class`

The `league` attribute accepts either a configured slug or a raw liga code. Groups are auto-detected from imported data.

## Naming Conventions

- All PHP classes prefixed with `AFVData_`
- All PHP constants prefixed with `AFVDATA_`
- All options prefixed with `afvdata_`
- All CSS classes prefixed with `afvdata-`
- All CSS custom properties prefixed with `--afvdata-`
- All AJAX actions prefixed with `afvdata_`
- Text domain: `afvdata`
- File names remain `afvd-*.php` (not renamed to avoid breaking WP plugin slug)

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
- To test: drop the folder into `wp-content/plugins/`, activate, configure under AFVData in the admin menu
- The plugin is i18n-ready (text domain: `afvdata`) but no translation files exist yet
- Frontend CSS is only enqueued on pages that actually use a shortcode
- Table colors are configurable in Settings with WordPress color picker; active theme palette colors are offered as presets
- The default table header color is neutral (#333) — users can customize via Settings or theme CSS targeting `.afvdata-league-table th`
- GitHub Actions pipeline builds a ZIP and creates a release on every push
- Version must be bumped in `afvd-data.php` (header + constant) and `readme.txt` (stable tag) before each push
- Contact: Daniel Schmidt-Richert, afvdata@foo.boo
