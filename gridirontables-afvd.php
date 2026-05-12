<?php
/**
 * Plugin Name:  Gridirontables AFVD – League tables & schedules - data provided by AFVD
 * Plugin URI:   https://slayer01.github.io/gridirontables-afvd/
 * Description:  Display American football league standings and schedules from publicly available XML data on your WordPress site.
 * Version:      3.0.2
 * Requires at least: 5.9
 * Requires PHP:  7.4
 * Author:       Daniel Schmidt-Richert
 * Author URI:   https://foo.boo
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  gridirontables-afvd
 */

defined('ABSPATH') || exit;

define('GRIDIRONTABLES_AFVD_VERSION', '3.0.2');
define('GRIDIRONTABLES_AFVD_DB_VERSION', '1.3');
define('GRIDIRONTABLES_AFVD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GRIDIRONTABLES_AFVD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GRIDIRONTABLES_AFVD_PLUGIN_FILE', __FILE__);

require_once GRIDIRONTABLES_AFVD_PLUGIN_DIR . 'includes/class-gridirontables-afvd-db.php';
require_once GRIDIRONTABLES_AFVD_PLUGIN_DIR . 'includes/class-gridirontables-afvd-importer.php';
require_once GRIDIRONTABLES_AFVD_PLUGIN_DIR . 'includes/class-gridirontables-afvd-cron.php';
require_once GRIDIRONTABLES_AFVD_PLUGIN_DIR . 'includes/class-gridirontables-afvd-admin.php';
require_once GRIDIRONTABLES_AFVD_PLUGIN_DIR . 'includes/class-gridirontables-afvd-shortcodes.php';
require_once GRIDIRONTABLES_AFVD_PLUGIN_DIR . 'includes/class-gridirontables-afvd-plugin.php';

register_activation_hook(__FILE__, ['Gridirontables_AFVD_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['Gridirontables_AFVD_Plugin', 'deactivate']);

add_action('plugins_loaded', function () {
    Gridirontables_AFVD_Plugin::get_instance();
});
