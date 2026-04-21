# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A WordPress plugin ("AFVD Data") that fetches American football league standings and game schedules from the AFVD (American Football Verband Deutschland) XML API, stores them locally, and displays them via shortcodes.

## Architecture

```
afvd-data.php              → Bootstrap: constants, requires, activation/deactivation hooks
includes/
  class-afvd-data.php      → Singleton orchestrator, DB version check, wires all classes
  class-afvd-db.php        → Schema (dbDelta), upsert/query methods, all $wpdb->prepare()
  class-afvd-importer.php  → Fetches XML via wp_remote_get(), parses with simplexml, upserts into DB
  class-afvd-admin.php     → Settings page under Settings → AFVD Data (3 tabs), AJAX handlers
  class-afvd-shortcodes.php→ [afvd_standings] and [afvd_schedule] shortcodes
  class-afvd-cron.php      → WP-Cron scheduling for automatic imports
```

## Data Flow

1. AFVD XML API (`vereine.football-verband.de/xmltabelle.php5?Liga=XXX` / `xmlspielplan.php5?Liga=XXX`)
2. `AFVD_Importer` fetches + parses XML, upserts into `{prefix}afvd_standings` / `{prefix}afvd_schedule`
3. Shortcodes query `AFVD_DB` methods and render HTML tables

Import uses upsert (`INSERT ... ON DUPLICATE KEY UPDATE`) + stale-row cleanup, so there's no empty-table window during sync.

## Database Tables

- `{prefix}afvd_standings` — league standings, unique on `(liga_code, gruppe, kuerzel)`
- `{prefix}afvd_schedule` — game schedule, unique on `(liga_code, game_id)`

Created via `dbDelta()` on activation. Dropped via `uninstall.php`.

## Configuration

All stored in `wp_options`:
- `afvd_data_team_name` — team name used for row highlighting
- `afvd_data_api_base_url` — AFVD XML endpoint base URL
- `afvd_data_sync_interval` — cron interval (manual/hourly/twicedaily/daily)
- `afvd_data_leagues` — serialized array of league configs (slug, label, liga_code, groups, active)

## Shortcodes

- `[afvd_standings league="slug"]` — standings table. Attrs: `group`, `highlight`, `class`
- `[afvd_schedule league="slug"]` — game schedule. Attrs: `group`, `home_only`, `show` (all/upcoming/past), `limit`, `highlight`, `class`

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
- To test: drop the folder into `wp-content/plugins/`, activate, configure under Settings → AFVD Data
- The plugin is i18n-ready (text domain: `afvd-data`) but no translation files exist yet
- Frontend CSS is only enqueued on pages that actually use a shortcode
- The default table header color is neutral (#333) — users should customize via their theme CSS targeting `.afvd-league-table th`
