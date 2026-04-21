<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-afvd-db.php';

AFVD_DB::uninstall();

delete_option('afvd_data_db_version');
delete_option('afvd_data_team_name');
delete_option('afvd_data_api_base_url');
delete_option('afvd_data_sync_interval');
delete_option('afvd_data_leagues');
delete_option('afvd_data_last_sync');
