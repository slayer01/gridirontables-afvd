<?php
defined('ABSPATH') || exit;

class AFVData_Plugin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->check_db_version();

        new AFVData_Cron();

        if (is_admin()) {
            new AFVData_Admin();
        }

        new AFVData_Shortcodes();
    }

    private function check_db_version() {
        $installed_version = get_option('afvdata_db_version', '0');
        if (version_compare($installed_version, AFVDATA_DB_VERSION, '<')) {
            AFVData_DB::install();
        }
    }

    public static function activate() {
        AFVData_DB::install();
        AFVData_Cron::schedule();
    }

    public static function deactivate() {
        AFVData_Cron::unschedule();
    }
}
