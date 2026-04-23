# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A WordPress plugin ("AFVData") that fetches American football league standings and game schedules from the AFVD (American Football Verband Deutschland) XML API, stores them locally, and displays them via shortcodes.

## Architecture

```
afvd-data.php              → Bootstrap: constants, requires, activation/deactivation hooks
includes/
  class-afvd-data.php      → Singleton orchestrator (AFVData_Plugin), DB version check, wires all classes
  class-afvd-db.php        → Schema (dbDelta), upsert/query methods, all $wpdb->prepare()
  class-afvd-importer.php  → Fetches XML via wp_remote_get(), parses with simplexml, upserts into DB
  class-afvd-admin.php     → Settings page under AFVData (4 tabs), AJAX handlers
  class-afvd-shortcodes.php→ [afvdata_standings] and [afvdata_schedule] shortcodes
  class-afvd-cron.php      → WP-Cron scheduling for automatic imports
```

## Data Flow

1. AFVD XML API (`vereine.football-verband.de/xmltabelle.php5?Liga=XXX` / `xmlspielplan.php5?Liga=XXX`)
2. `AFVData_Importer` fetches + parses XML, upserts into `{prefix}afvdata_standings` / `{prefix}afvdata_schedule`
3. Shortcodes query `AFVData_DB` methods and render HTML tables

Import uses upsert (`INSERT ... ON DUPLICATE KEY UPDATE`) + stale-row cleanup, so there's no empty-table window during sync.

## Database Tables

- `{prefix}afvdata_standings` — league standings, unique on `(liga_code, gruppe, kuerzel)`
- `{prefix}afvdata_schedule` — game schedule, unique on `(liga_code, game_id)`

Created via `dbDelta()` on activation. Dropped via `uninstall.php`.

## Configuration

All stored in `wp_options`:
- `afvdata_api_base_url` — AFVD XML endpoint base URL
- `afvdata_sync_interval` — cron interval (manual/hourly/twicedaily/daily)
- `afvdata_leagues` — serialized array of league configs (slug, label, liga_code, team_name, active)

## Shortcodes

- `[afvdata_standings league="slug"]` — standings table. Attrs: `group`, `highlight`, `class`
- `[afvdata_schedule league="slug"]` — game schedule. Attrs: `group`, `home_only`, `show` (all/upcoming/past), `limit`, `highlight`, `class`

The `league` attribute accepts either a configured slug or a raw AFVD liga code.

## Security Conventions

- All DB queries via `$wpdb->prepare()`
- All admin forms use nonces (`wp_nonce_field` / `wp_verify_nonce`)
- All admin handlers check `current_user_can('manage_options')`
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`
- All PHP files guarded with `defined('ABSPATH') || exit`
- AJAX endpoints use `check_ajax_referer()`

## Development Notes

- This is a standalone WordPress plugin — no build step, no npm, no composer
- To test: drop the folder into `wp-content/plugins/`, activate, configure under AFVData in the admin menu
- The plugin is i18n-ready (text domain: `afvdata`) but no translation files exist yet
- Frontend CSS is only enqueued on pages that actually use a shortcode
- The default table header color is neutral (#333) — users should customize via their theme CSS targeting `.afvdata-league-table th`
