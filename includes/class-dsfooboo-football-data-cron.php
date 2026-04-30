<?php
defined('ABSPATH') || exit;

class DSFooboo_Football_Data_Cron {

    const HOOK = 'dsfooboo_football_data_sync';

    public function __construct() {
        add_action(self::HOOK, [$this, 'run']);
    }

    public function run() {
        $importer = new DSFooboo_Football_Data_Importer();
        $importer->import_all_active();
    }

    public static function schedule() {
        $interval = get_option('dsfooboo_football_data_sync_interval', 'manual');

        if ('manual' === $interval) {
            return;
        }

        if (!wp_next_scheduled(self::HOOK)) {
            wp_schedule_event(time(), $interval, self::HOOK);
        }
    }

    public static function unschedule() {
        $timestamp = wp_next_scheduled(self::HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::HOOK);
        }
    }
}
