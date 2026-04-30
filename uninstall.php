<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-footballdata-db.php';

FootballData_DB::uninstall();

$options = [
    'footballdata_db_version',
    'footballdata_api_base_url',
    'footballdata_sync_interval',
    'footballdata_leagues',
    'footballdata_last_sync',
    'footballdata_color_header_bg',
    'footballdata_color_header_text',
    'footballdata_color_highlight_bg',
    'footballdata_legacy_migrated',
    // Legacy options from the afvdata_* era — clean these up too in case
    // a user uninstalls without ever reactivating after the rename.
    'afvdata_db_version',
    'afvdata_team_name',
    'afvdata_api_base_url',
    'afvdata_sync_interval',
    'afvdata_leagues',
    'afvdata_last_sync',
    'afvdata_color_header_bg',
    'afvdata_color_header_text',
    'afvdata_color_highlight_bg',
];

foreach ($options as $option) {
    delete_option($option);
}
