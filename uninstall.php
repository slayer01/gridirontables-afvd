<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-dsfooboo-football-data-db.php';

DSFooboo_Football_Data_DB::uninstall();

$options = [
    // Current prefix
    'dsfooboo_football_data_db_version',
    'dsfooboo_football_data_api_base_url',
    'dsfooboo_football_data_sync_interval',
    'dsfooboo_football_data_leagues',
    'dsfooboo_football_data_last_sync',
    'dsfooboo_football_data_color_header_bg',
    'dsfooboo_football_data_color_header_text',
    'dsfooboo_football_data_color_highlight_bg',
    'dsfooboo_football_data_legacy_migrated',
    // Legacy from the footballdata_* era
    'footballdata_db_version',
    'footballdata_api_base_url',
    'footballdata_sync_interval',
    'footballdata_leagues',
    'footballdata_last_sync',
    'footballdata_color_header_bg',
    'footballdata_color_header_text',
    'footballdata_color_highlight_bg',
    'footballdata_legacy_migrated',
    // Legacy from the afvdata_* era
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
