<?php
/**
 * Plugin Name:  AFVData – League Tables & Schedules
 * Plugin URI:   https://github.com/slayer01/afvdata
 * Description:  Display American football league standings and schedules from the AFVD (American Football Verband Deutschland) on your WordPress site.
 * Version:      2.3.0
 * Requires at least: 5.9
 * Requires PHP:  7.4
 * Author:       Daniel Schmidt-Richert
 * Author URI:   https://foo.boo
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  afvdata
 */

defined('ABSPATH') || exit;

define('AFVDATA_VERSION', '2.3.0');
define('AFVDATA_DB_VERSION', '1.0');
define('AFVDATA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AFVDATA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AFVDATA_PLUGIN_FILE', __FILE__);

require_once AFVDATA_PLUGIN_DIR . 'includes/class-afvd-db.php';
require_once AFVDATA_PLUGIN_DIR . 'includes/class-afvd-importer.php';
require_once AFVDATA_PLUGIN_DIR . 'includes/class-afvd-cron.php';
require_once AFVDATA_PLUGIN_DIR . 'includes/class-afvd-admin.php';
require_once AFVDATA_PLUGIN_DIR . 'includes/class-afvd-shortcodes.php';
require_once AFVDATA_PLUGIN_DIR . 'includes/class-afvd-data.php';

register_activation_hook(__FILE__, ['AFVData_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['AFVData_Plugin', 'deactivate']);

add_action('plugins_loaded', function () {
    AFVData_Plugin::get_instance();
});
