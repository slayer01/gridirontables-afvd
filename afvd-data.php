<?php
/**
 * Plugin Name:  AFVD Data
 * Plugin URI:   https://github.com/slayer01/afvdata
 * Description:  Display American football league standings and schedules from the AFVD (American Football Verband Deutschland) on your WordPress site.
 * Version:      2.0.9
 * Author:       Daniel Schmidt-Richert
 * Author URI:   https://foo.boo
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  afvd-data
 */

defined('ABSPATH') || exit;

define('AFVD_DATA_VERSION', '2.0.9');
define('AFVD_DATA_DB_VERSION', '1.0');
define('AFVD_DATA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AFVD_DATA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AFVD_DATA_PLUGIN_FILE', __FILE__);

require_once AFVD_DATA_PLUGIN_DIR . 'includes/class-afvd-db.php';
require_once AFVD_DATA_PLUGIN_DIR . 'includes/class-afvd-importer.php';
require_once AFVD_DATA_PLUGIN_DIR . 'includes/class-afvd-cron.php';
require_once AFVD_DATA_PLUGIN_DIR . 'includes/class-afvd-admin.php';
require_once AFVD_DATA_PLUGIN_DIR . 'includes/class-afvd-shortcodes.php';
require_once AFVD_DATA_PLUGIN_DIR . 'includes/class-afvd-data.php';

register_activation_hook(__FILE__, ['AFVD_Data', 'activate']);
register_deactivation_hook(__FILE__, ['AFVD_Data', 'deactivate']);

add_action('plugins_loaded', function () {
    AFVD_Data::get_instance();
});
