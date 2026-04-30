=== FootballData – League Tables & Schedules ===
Contributors: slayer01
Tags: american football, standings, schedule, sports, germany
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display American football league standings and game schedules on your WordPress site using publicly available XML data from vereine.football-verband.de.

== Description ==

FootballData fetches league standings and game schedules from a publicly available XML API, stores them locally, and displays them via shortcodes.

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

* `[footballdata_standings league="slug"]` — League standings table
* `[footballdata_schedule league="slug"]` — Game schedule table

The legacy `[afvdata_standings]` and `[afvdata_schedule]` shortcodes still work as aliases.

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
3. Go to **FootballData** in the admin menu to configure leagues
4. Import data and use shortcodes on your pages

== Changelog ==

= 2.4.1 =
* GitHub repository renamed from `afvdata` to `footballdata`; Plugin URI and documentation links updated accordingly

= 2.4.0 =
* Renamed plugin from "AFVData" to "FootballData"
* New shortcodes `[footballdata_standings]` and `[footballdata_schedule]`; old `[afvdata_*]` shortcodes still work as aliases
* All internal prefixes (classes, options, CSS, AJAX hooks, text domain) renamed from `afvdata` to `footballdata`
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
