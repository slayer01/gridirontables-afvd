<?php
defined('ABSPATH') || exit;

class Gridirontables_AFVD_Plugin {

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

        new Gridirontables_AFVD_Cron();

        if (is_admin()) {
            new Gridirontables_AFVD_Admin();
        }

        new Gridirontables_AFVD_Shortcodes();
    }

    private function check_db_version() {
        $installed_version = get_option('gridirontables_afvd_db_version', '0');
        if (version_compare($installed_version, GRIDIRONTABLES_AFVD_DB_VERSION, '<')) {
            Gridirontables_AFVD_DB::install();
        }
    }

    /**
     * One-time migration from earlier prefixes (afvdata_*, footballdata_*, dsfooboo_football_data_*) to gridirontables_afvd_*.
     * Renames DB tables, copies options, reschedules cron.
     */
    private function maybe_migrate_legacy() {
        if (get_option('gridirontables_afvd_legacy_migrated')) {
            return;
        }
        Gridirontables_AFVD_DB::migrate_from_legacy();
        self::migrate_legacy_options();
        self::migrate_legacy_cron();
        update_option('gridirontables_afvd_legacy_migrated', 1);
    }

    /**
     * Map of new option name => list of legacy option names to copy from (in order of priority).
     */
    private static function legacy_option_map() {
        return [
            'gridirontables_afvd_db_version'         => ['dsfooboo_football_data_db_version', 'footballdata_db_version', 'afvdata_db_version'],
            'gridirontables_afvd_api_base_url'       => ['dsfooboo_football_data_api_base_url', 'footballdata_api_base_url', 'afvdata_api_base_url'],
            'gridirontables_afvd_sync_interval'      => ['dsfooboo_football_data_sync_interval', 'footballdata_sync_interval', 'afvdata_sync_interval'],
            'gridirontables_afvd_leagues'            => ['dsfooboo_football_data_leagues', 'footballdata_leagues', 'afvdata_leagues'],
            'gridirontables_afvd_last_sync'          => ['dsfooboo_football_data_last_sync', 'footballdata_last_sync', 'afvdata_last_sync'],
            'gridirontables_afvd_color_header_bg'    => ['dsfooboo_football_data_color_header_bg', 'footballdata_color_header_bg', 'afvdata_color_header_bg'],
            'gridirontables_afvd_color_header_text'  => ['dsfooboo_football_data_color_header_text', 'footballdata_color_header_text', 'afvdata_color_header_text'],
            'gridirontables_afvd_color_highlight_bg' => ['dsfooboo_football_data_color_highlight_bg', 'footballdata_color_highlight_bg', 'afvdata_color_highlight_bg'],
        ];
    }

    private static function migrate_legacy_options() {
        foreach (self::legacy_option_map() as $new => $olds) {
            if (false !== get_option($new, false)) {
                // already set, just clean up legacy keys
                foreach ($olds as $old) {
                    delete_option($old);
                }
                continue;
            }
            foreach ($olds as $old) {
                $value = get_option($old, null);
                if (null !== $value) {
                    update_option($new, $value);
                    break;
                }
            }
            foreach ($olds as $old) {
                delete_option($old);
            }
        }
        // Old migration flags from previous renames are now redundant.
        delete_option('dsfooboo_football_data_legacy_migrated');
        delete_option('footballdata_legacy_migrated');
    }

    private static function migrate_legacy_cron() {
        foreach (['dsfooboo_football_data_sync', 'footballdata_sync', 'afvdata_sync'] as $legacy_hook) {
            $timestamp = wp_next_scheduled($legacy_hook);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $legacy_hook);
            }
        }
        Gridirontables_AFVD_Cron::schedule();
    }

    public static function activate() {
        Gridirontables_AFVD_DB::migrate_from_legacy();
        self::migrate_legacy_options();
        self::migrate_legacy_cron();
        update_option('gridirontables_afvd_legacy_migrated', 1);

        Gridirontables_AFVD_DB::install();
        Gridirontables_AFVD_Cron::schedule();
    }

    public static function deactivate() {
        Gridirontables_AFVD_Cron::unschedule();
    }
}
