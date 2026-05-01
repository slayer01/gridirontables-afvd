=== Gridirontables AFVD – League tables & schedules - data provided by AFVD ===
Contributors: slayer01
Tags: american football, standings, schedule, sports, germany
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 3.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display American football league standings and game schedules on your WordPress site using publicly available XML data from vereine.football-verband.de.

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

The legacy `[dsfooboo_football_data_*]`, `[footballdata_*]` and `[afvdata_*]` shortcodes still work as aliases.

= Schedule Attributes =

* `home_only="1"` — Show only home games of the configured team
* `show="upcoming"` — Show only upcoming games
* `show="past"` — Show only past games
* `limit="5"` — Limit number of games shown

= Shared Attributes =

* `group="A"` — Show only a specific group
* `highlight="Team Name"` — Override team name for highlighting
* `class="my-class"` — Add custom CSS class

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the Plugins menu
3. Go to **Gridirontables AFVD** in the admin menu to configure leagues
4. Import data and use shortcodes on your pages

== Changelog ==

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
