<?php
/**
 * Plugin Name:  DSFOOBOO Football Data – League tables & schedules - data provided by AFVD
 * Plugin URI:   https://slayer01.github.io/dsfooboo-football-data/
 * Description:  Display American football league standings and schedules from publicly available XML data on your WordPress site.
 * Version:      2.5.2
 * Requires at least: 5.9
 * Requires PHP:  7.4
 * Author:       Daniel Schmidt-Richert
 * Author URI:   https://foo.boo
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  dsfooboo_football_data
 */

defined('ABSPATH') || exit;

define('DSFOOBOO_FOOTBALL_DATA_VERSION', '2.5.2');
define('DSFOOBOO_FOOTBALL_DATA_DB_VERSION', '1.2');
define('DSFOOBOO_FOOTBALL_DATA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DSFOOBOO_FOOTBALL_DATA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DSFOOBOO_FOOTBALL_DATA_PLUGIN_FILE', __FILE__);

require_once DSFOOBOO_FOOTBALL_DATA_PLUGIN_DIR . 'includes/class-dsfooboo-football-data-db.php';
require_once DSFOOBOO_FOOTBALL_DATA_PLUGIN_DIR . 'includes/class-dsfooboo-football-data-importer.php';
require_once DSFOOBOO_FOOTBALL_DATA_PLUGIN_DIR . 'includes/class-dsfooboo-football-data-cron.php';
require_once DSFOOBOO_FOOTBALL_DATA_PLUGIN_DIR . 'includes/class-dsfooboo-football-data-admin.php';
require_once DSFOOBOO_FOOTBALL_DATA_PLUGIN_DIR . 'includes/class-dsfooboo-football-data-shortcodes.php';
require_once DSFOOBOO_FOOTBALL_DATA_PLUGIN_DIR . 'includes/class-dsfooboo-football-data-plugin.php';

register_activation_hook(__FILE__, ['DSFooboo_Football_Data_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['DSFooboo_Football_Data_Plugin', 'deactivate']);

add_action('plugins_loaded', function () {
    DSFooboo_Football_Data_Plugin::get_instance();
});
