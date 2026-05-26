=== Gridirontables AFVD – League tables & schedules - data provided by AFVD ===
Contributors: dsfooboo
Donate link: https://buymeacoffee.com/dscr
Tags: american football, standings, schedule, sports, germany
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 3.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display American football league standings and schedules from publicly available XML data on your WordPress site.

== Description ==

Gridirontables AFVD fetches league standings and game schedules from a publicly available XML API, stores them locally, and displays them via shortcodes.

This plugin is an independent project and is not affiliated with, endorsed by, or in any way officially connected to the AFVD (American Football Verband Deutschland) or any of its member associations.

= Features =

* Import standings and schedules for any league available in the XML API
* Automatic sync via WP-Cron (hourly, twice daily, daily, or manual)
* Per-league team name for highlighting
* Configurable table colors (header, highlight) with active theme palette support
* Groups are auto-detected from imported data
* Responsive schedule table for mobile
* Raw data viewer in the admin panel
* German date format for schedules

= Shortcodes =

* `[gridirontables_afvd_standings league="slug"]` — League standings table
* `[gridirontables_afvd_schedule league="slug"]` — Game schedule table

= Schedule Attributes =

* `home_only="1"` — Show only home games of the configured team
* `show="upcoming"` — Show only upcoming games
* `show="past"` — Show only past games
* `limit="5"` — Limit number of games shown

= Shared Attributes =

* `group="A"` — Show only a specific group
* `highlight="Team Name"` — Override team name for highlighting
* `class="my-class"` — Add custom CSS class
* `saison="2026"` — Override the season label shown in the heading

= Standings Attributes =

* `format="wins"` (default) — BSO record layout: W-L (Quotient) / TD / Home-Away
* `format="points"` — Legacy points layout: P+ / P- / TD+ / TD- (for archive data)

== Support development ==

If this plugin saves you time, consider buying me a coffee: https://buymeacoffee.com/dscr

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the Plugins menu
3. Go to **Gridirontables AFVD** in the admin menu to configure leagues
4. Import data and use shortcodes on your pages

== Screenshots ==

1. Frontend standings table with the configured team highlighted
2. Frontend game schedule for a configured league
3. Admin: configure the leagues to import on the Leagues tab
4. Admin: run imports, view raw data, and look up shortcodes on the Import tab

== Changelog ==

= 3.1.4 =
* Added a Buy Me a Coffee link to the plugin's Info tab, the WordPress.org Donate field, the GitHub README, and the GitHub Pages landing page. No functional changes.

= 3.1.3 =
* Fix: post-import row counts in the Import tab reported the wrong saison's count. `import_league()` called `get_counts($liga_code)` without passing the saison, so importing an archive entry would store the right rows in the DB but display the count from the current saison — making two leagues with the same liga_code look identical even though the underlying data was different. Now the count is scoped to the saison that was just imported.
* Migration robustness: replaced `SHOW INDEX … WHERE` with a fetch-all-then-filter-in-PHP approach so the saison-in-unique-key migration runs reliably on all MySQL/MariaDB versions; table and key names are now backtick-quoted.

= 3.1.2 =
* Leagues form: when duplicate slugs or duplicate (liga_code, saison) pairs are submitted, **nothing is saved** and the form re-renders with the user's unsaved input intact plus a red error notice listing the row numbers and what to fix. Previously the duplicate row was silently skipped, which lost the user's typing.

= 3.1.1 =
* Two league entries can now coexist with the same Liga Code but different Saison values — useful for displaying current + archive data side by side. The `saison` column is part of the unique key on both DB tables; DB schema bumped to 1.5 (the key is migrated automatically on upgrade)
* Validation on the Leagues form: duplicate slugs or duplicate (liga_code, saison) pairs are now skipped on save and surfaced in an admin notice — previously the second entry silently overwrote the first
* Import buttons, count cells, and raw-data viewer in the admin Import tab key off the league slug instead of the liga_code so multi-saison setups address the right league entry
* Import tab now shows the per-league Saison column and counts are scoped to that saison

= 3.1.0 =
* New BSO-style standings layout (default): `Rank | Team | Record (W-L (Quotient)) | TD (TD+:TD-) | Home/Away`, matching the official footballverband.de output
* Legacy `P+ / P- / TD+ / TD-` layout remains available via `format="points"` on the shortcode or per league config (for archive seasons that still use the old points system)
* New per-league **Saison** field — when set, gets appended to the table heading and passed to the XML API as the `Saison` parameter so archive seasons can be imported
* Shortcode attributes `format` and `saison` for ad-hoc overrides
* Importer now captures `Gameswin`, `Gamesloose`, `Gamestied`, `Quotient`, Home/Away splits and overtime scores (`OTHeim` / `OTGast`) — DB schema bumped to 1.4
* **Important after upgrade:** run a fresh import (Gridirontables AFVD → Import) to populate the new columns; until you do, standings fall back to the legacy `points` layout

= 3.0.2 =
* Corrected the `Contributors` field in `readme.txt` to the WordPress.org account that owns the plugin (`dsfooboo`)
* Removed the deprecated `[dsfooboo_football_data_*]`, `[footballdata_*]` and `[afvdata_*]` shortcode aliases to satisfy the WordPress.org Plugin Check prefix requirement — only `[gridirontables_afvd_*]` is registered now (existing pages using the old tags must be updated)

= 3.0.1 =
* Trim short description to fit the 150-character readme limit
* Suppress remaining `WordPress.DB.PreparedSQL.InterpolatedNotPrepared` warnings on the legacy-table RENAME / DROP queries (table names cannot be passed as prepared placeholders)
* Scope `uninstall.php` cleanup loop in a closure so its variables are no longer flagged as unprefixed globals

= 3.0.0 =
* Renamed plugin to "Gridirontables AFVD" (third rename) to match the WordPress.org plugin slug `gridirontables-afvd`
* Text domain changed to `gridirontables-afvd` (hyphenated, as required by WordPress.org)
* New shortcodes `[gridirontables_afvd_standings]` and `[gridirontables_afvd_schedule]`; previous `[dsfooboo_football_data_*]`, `[footballdata_*]` and `[afvdata_*]` shortcodes still work as aliases
* All internal prefixes (classes, options, CSS, AJAX hooks, cron hook) renamed to `gridirontables_afvd_*`
* Migration on activation/load handles tables and options from all three prior prefixes (`dsfooboo_football_data_*`, `footballdata_*`, `afvdata_*`)
* DB schema bumped to 1.3

= 2.5.2 =
* Updated tagline to "League tables & schedules - data provided by AFVD"

= 2.5.1 =
* GitHub repository renamed from `footballdata` to `dsfooboo-football-data`; Plugin URI and documentation links updated accordingly

= 2.5.0 =
* Renamed plugin to "DSFOOBOO Football Data"
* New shortcodes `[dsfooboo_football_data_standings]` and `[dsfooboo_football_data_schedule]`; previous `[footballdata_*]` and `[afvdata_*]` shortcodes still work as aliases
* All internal prefixes (classes, options, CSS, AJAX hooks, text domain, cron hook) renamed to `dsfooboo_football_data_*`
* Migration on activation/load handles tables and options from both prior prefixes (`footballdata_*` and `afvdata_*`)
* DB schema bumped to 1.2

= 2.4.1 =
* GitHub repository renamed from `afvdata` to `footballdata`; Plugin URI and documentation links updated accordingly

= 2.4.0 =
* Renamed plugin from "AFVData" to "FootballData"
* New shortcodes `[footballdata_standings]` and `[footballdata_schedule]`; old `[afvdata_*]` shortcodes still work as aliases
* All internal prefixes renamed from `afvdata` to `footballdata`
* Automatic one-time migration on activation: renames database tables and copies options from the old prefix
* DB schema bumped to 1.1

= 2.3.0 =
* Complete rename of all internal prefixes to afvdata
* Shortcodes are now `[afvdata_standings]` and `[afvdata_schedule]`
* Text domain changed to `afvdata`
* Updated all CSS classes, option names, and hook names
* Added Info tab with disclaimer and contact information
* Added plugin logo

= 2.1.0 =
* Fixed schedule showing only own team's games
* Per-league team name configuration
* Configurable table colors with theme palette
* Top-level admin menu
* Raw data viewer on import tab
* Auto-detect groups from imported data
* German date format for schedule

= 2.0.0 =
* Initial release
