=== AFVD Data ===
Contributors: slayer01
Tags: american football, afvd, standings, schedule, sports
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display American football league standings and schedules from the AFVD (American Football Verband Deutschland) on your WordPress site.

== Description ==

AFVD Data fetches league standings and game schedules from the AFVD XML API, stores them locally, and displays them via shortcodes.

= Features =

* Import standings and schedules for any AFVD league
* Automatic sync via WP-Cron (hourly, twice daily, daily, or manual)
* Per-league team name for highlighting
* Configurable table colors (header, highlight) with theme palette support
* Groups are auto-detected from imported data
* Responsive schedule table for mobile

= Shortcodes =

* `[afvd_standings league="slug"]` — League standings table
* `[afvd_schedule league="slug"]` — Game schedule table

= Schedule Attributes =

* `home_only="1"` — Show only home games
* `show="upcoming"` — Show only upcoming games
* `show="past"` — Show only past games
* `limit="5"` — Limit number of games shown

= Shared Attributes =

* `group="A"` — Show only a specific group
* `highlight="Team Name"` — Override team name for highlighting
* `class="my-class"` — Add custom CSS class

== Installation ==

1. Upload the `afvd-data` folder to `/wp-content/plugins/`
2. Activate the plugin through the Plugins menu
3. Go to AFVD Data in the admin menu to configure leagues
4. Import data and use shortcodes on your pages

== Changelog ==

= 2.1.7 =
* Added readme.txt for WordPress plugin directory compatibility

= 2.1.0 =
* Fixed schedule showing only own team's games
* Per-league team name configuration
* Configurable table colors with theme palette
* Top-level admin menu with logo
* Raw data viewer on import tab
* Auto-detect groups from imported data
* German date format for schedule

= 2.0.0 =
* Initial release
