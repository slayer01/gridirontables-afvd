<?php
/**
 * Plugin Name:  FootballData – League Tables & Schedules
 * Plugin URI:   https://slayer01.github.io/afvdata/
 * Description:  Display American football league standings and schedules from publicly available XML data on your WordPress site.
 * Version:      2.4.0
 * Requires at least: 5.9
 * Requires PHP:  7.4
 * Author:       Daniel Schmidt-Richert
 * Author URI:   https://foo.boo
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  footballdata
 */

defined('ABSPATH') || exit;

define('FOOTBALLDATA_VERSION', '2.4.0');
define('FOOTBALLDATA_DB_VERSION', '1.1');
define('FOOTBALLDATA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FOOTBALLDATA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FOOTBALLDATA_PLUGIN_FILE', __FILE__);

require_once FOOTBALLDATA_PLUGIN_DIR . 'includes/class-footballdata-db.php';
require_once FOOTBALLDATA_PLUGIN_DIR . 'includes/class-footballdata-importer.php';
require_once FOOTBALLDATA_PLUGIN_DIR . 'includes/class-footballdata-cron.php';
require_once FOOTBALLDATA_PLUGIN_DIR . 'includes/class-footballdata-admin.php';
require_once FOOTBALLDATA_PLUGIN_DIR . 'includes/class-footballdata-shortcodes.php';
require_once FOOTBALLDATA_PLUGIN_DIR . 'includes/class-footballdata-plugin.php';

register_activation_hook(__FILE__, ['FootballData_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['FootballData_Plugin', 'deactivate']);

add_action('plugins_loaded', function () {
    FootballData_Plugin::get_instance();
});
