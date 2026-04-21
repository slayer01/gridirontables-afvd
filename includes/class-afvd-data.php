<?php
defined('ABSPATH') || exit;

class AFVD_Data {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->check_db_version();

        new AFVD_Cron();

        if (is_admin()) {
            new AFVD_Admin();
        }

        new AFVD_Shortcodes();
    }

    private function check_db_version() {
        $installed_version = get_option('afvd_data_db_version', '0');
        if (version_compare($installed_version, AFVD_DATA_DB_VERSION, '<')) {
            AFVD_DB::install();
        }
    }

    public static function activate() {
        AFVD_DB::install();
        AFVD_Cron::schedule();
    }

    public static function deactivate() {
        AFVD_Cron::unschedule();
    }
}
