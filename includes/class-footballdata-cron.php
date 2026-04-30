<?php
defined('ABSPATH') || exit;

class FootballData_Cron {

    const HOOK = 'footballdata_sync';

    public function __construct() {
        add_action(self::HOOK, [$this, 'run']);
    }

    /**
     * Cron callback: import all active leagues.
     */
    public function run() {
        $importer = new FootballData_Importer();
        $importer->import_all_active();
    }

    /**
     * Schedule the cron event based on settings.
     */
    public static function schedule() {
        $interval = get_option('footballdata_sync_interval', 'manual');

        if ('manual' === $interval) {
            return;
        }

        if (!wp_next_scheduled(self::HOOK)) {
            wp_schedule_event(time(), $interval, self::HOOK);
        }
    }

    /**
     * Remove the scheduled cron event.
     */
    public static function unschedule() {
        $timestamp = wp_next_scheduled(self::HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::HOOK);
        }
    }
}
