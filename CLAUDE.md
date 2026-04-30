# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A WordPress plugin ("FootballData – League Tables & Schedules") that fetches American football league standings and game schedules from a publicly available XML API at `vereine.football-verband.de`, stores them locally, and displays them via shortcodes.

This plugin is independent and not affiliated with the AFVD (American Football Verband Deutschland) or any of its member associations.

The plugin was previously named "AFVData". As of 2.4.0 it was renamed to "FootballData"; the old `afvdata_*` prefix lives on only as deprecated shortcode aliases and as the source side of a one-time activation/load-time migration.

## Architecture

```
football-data.php                       → Bootstrap: constants, requires, activation/deactivation hooks
uninstall.php                           → Cleanup: drops tables (new + legacy) and deletes options
includes/
  class-footballdata-plugin.php         → Singleton orchestrator (FootballData_Plugin), DB version + legacy migration, wires all classes
  class-footballdata-db.php             → Schema (dbDelta), upsert/query methods, all $wpdb->prepare(), legacy table rename
  class-footballdata-importer.php       → Fetches XML via wp_remote_get(), parses with simplexml, upserts into DB
  class-footballdata-admin.php          → Top-level admin menu "FootballData" (4 tabs: Settings, Leagues, Import, Info), AJAX handlers
  class-footballdata-shortcodes.php     → [footballdata_standings]/[footballdata_schedule] shortcodes + legacy [afvdata_*] aliases
  class-footballdata-cron.php           → WP-Cron scheduling for automatic imports
admin/
  views/page-settings.php               → Main admin page with tab navigation
  views/partial-leagues.php             → League configuration form
  views/partial-import.php              → Import status, raw data viewer, shortcode & attribute reference
  views/partial-info.php                → Disclaimer, contact info, logo
  css/admin.css                         → Admin styles
  js/admin.js                           → Color picker, league management, import AJAX, raw data viewer
  img/logo.png                          → Plugin logo (transparent PNG)
public/
  css/football-data.css                 → Frontend table styles with CSS custom properties
```

## Data Flow

1. XML API (`vereine.football-verband.de/xmltabelle.php5?Liga=XXX` / `xmlspielplan.php5?Liga=XXX`)
2. `FootballData_Importer` fetches + parses XML, upserts into `{prefix}footballdata_standings` / `{prefix}footballdata_schedule`
3. Shortcodes query `FootballData_DB` methods and render HTML tables

Import uses upsert (`INSERT ... ON DUPLICATE KEY UPDATE`) + stale-row cleanup, so there's no empty-table window during sync.

## Database Tables

- `{prefix}footballdata_standings` — league standings, unique on `(liga_code, gruppe, kuerzel)`
- `{prefix}footballdata_schedule` — game schedule, unique on `(liga_code, game_id)`

Created via `dbDelta()` on activation. Dropped via `uninstall.php`. On first load after upgrade from a pre-2.4.0 install, `FootballData_DB::migrate_from_legacy()` renames the old `{prefix}afvdata_*` tables in place; the option `footballdata_legacy_migrated` is set to skip the migration on subsequent loads.

## Configuration

All stored in `wp_options`:
- `footballdata_api_base_url` — XML endpoint base URL
- `footballdata_sync_interval` — cron interval (manual/hourly/twicedaily/daily)
- `footballdata_leagues` — serialized array of league configs (slug, label, liga_code, team_name, active)
- `footballdata_color_header_bg` — table header background color
- `footballdata_color_header_text` — table header text color
- `footballdata_color_highlight_bg` — highlight row background color
- `footballdata_last_sync` — timestamp of last full sync
- `footballdata_db_version` — DB schema version for migrations
- `footballdata_legacy_migrated` — set to `1` once the one-time afvdata→footballdata migration has run

## Shortcodes

- `[footballdata_standings league="slug"]` — standings table. Attrs: `group`, `highlight`, `class`
- `[footballdata_schedule league="slug"]` — game schedule. Attrs: `group`, `home_only`, `show` (all/upcoming/past), `limit`, `highlight`, `class`

The legacy `[afvdata_standings]` and `[afvdata_schedule]` shortcodes are registered as aliases for backwards compatibility.

The `league` attribute accepts either a configured slug or a raw liga code. Groups are auto-detected from imported data.

## Naming Conventions

- All PHP classes prefixed with `FootballData_`
- All PHP constants prefixed with `FOOTBALLDATA_`
- All options prefixed with `footballdata_`
- All CSS classes prefixed with `footballdata-`
- All CSS custom properties prefixed with `--footballdata-`
- All AJAX actions prefixed with `footballdata_`
- Cron hook: `footballdata_sync` (legacy `afvdata_sync` is unscheduled during migration)
- Text domain: `footballdata`
- Admin page slug: `footballdata`
- File names use `class-footballdata-*.php` and `football-data.php`/`football-data.css`
- The repository folder and GitHub Pages slug remain `afvdata` for now (Plugin URI still points to slayer01.github.io/afvdata)

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
- To test: drop the folder into `wp-content/plugins/`, activate, configure under FootballData in the admin menu
- The plugin is i18n-ready (text domain: `footballdata`) but no translation files exist yet
- Frontend CSS is only enqueued on pages that actually use a shortcode
- Table colors are configurable in Settings with WordPress color picker; active theme palette colors are offered as presets
- The default table header color is neutral (#333) — users can customize via Settings or theme CSS targeting `.footballdata-league-table th`
- GitHub Actions pipeline builds a ZIP and creates a release on every push
- Version must be bumped in `football-data.php` (header + constant) and `readme.txt` (stable tag) before each push
- Contact: Daniel Schmidt-Richert, afvdata@foo.boo
