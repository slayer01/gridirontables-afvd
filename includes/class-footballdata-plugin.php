<?php
defined('ABSPATH') || exit;

class FootballData_Plugin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->maybe_migrate_legacy();
        $this->check_db_version();

        new FootballData_Cron();

        if (is_admin()) {
            new FootballData_Admin();
        }

        new FootballData_Shortcodes();
    }

    private function check_db_version() {
        $installed_version = get_option('footballdata_db_version', '0');
        if (version_compare($installed_version, FOOTBALLDATA_DB_VERSION, '<')) {
            FootballData_DB::install();
        }
    }

    /**
     * One-time migration from the old afvdata_* prefix to footballdata_*.
     * Renames DB tables, copies options, reschedules cron.
     */
    private function maybe_migrate_legacy() {
        if (get_option('footballdata_legacy_migrated')) {
            return;
        }
        FootballData_DB::migrate_from_legacy();
        self::migrate_legacy_options();
        self::migrate_legacy_cron();
        update_option('footballdata_legacy_migrated', 1);
    }

    private static function migrate_legacy_options() {
        $map = [
            'afvdata_db_version'         => 'footballdata_db_version',
            'afvdata_api_base_url'       => 'footballdata_api_base_url',
            'afvdata_sync_interval'      => 'footballdata_sync_interval',
            'afvdata_leagues'            => 'footballdata_leagues',
            'afvdata_last_sync'          => 'footballdata_last_sync',
            'afvdata_color_header_bg'    => 'footballdata_color_header_bg',
            'afvdata_color_header_text'  => 'footballdata_color_header_text',
            'afvdata_color_highlight_bg' => 'footballdata_color_highlight_bg',
        ];
        foreach ($map as $old => $new) {
            $value = get_option($old, null);
            if (null === $value) {
                continue;
            }
            if (false === get_option($new, false)) {
                update_option($new, $value);
            }
            delete_option($old);
        }
    }

    private static function migrate_legacy_cron() {
        $legacy_hook = 'afvdata_sync';
        $timestamp = wp_next_scheduled($legacy_hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $legacy_hook);
        }
        FootballData_Cron::schedule();
    }

    public static function activate() {
        FootballData_DB::migrate_from_legacy();
        self::migrate_legacy_options();
        self::migrate_legacy_cron();
        update_option('footballdata_legacy_migrated', 1);

        FootballData_DB::install();
        FootballData_Cron::schedule();
    }

    public static function deactivate() {
        FootballData_Cron::unschedule();
    }
}
