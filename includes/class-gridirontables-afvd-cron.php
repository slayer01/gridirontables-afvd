<?php
defined('ABSPATH') || exit;

class Gridirontables_AFVD_Cron {

    const HOOK = 'gridirontables_afvd_sync';

    public function __construct() {
        add_action(self::HOOK, [$this, 'run']);
    }

    public function run() {
        $importer = new Gridirontables_AFVD_Importer();
        $importer->import_all_active();
    }

    public static function schedule() {
        $interval = get_option('gridirontables_afvd_sync_interval', 'manual');

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
