<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-afvd-db.php';

AFVData_DB::uninstall();

delete_option('afvdata_db_version');
delete_option('afvdata_team_name');
delete_option('afvdata_api_base_url');
delete_option('afvdata_sync_interval');
delete_option('afvdata_leagues');
delete_option('afvdata_last_sync');
delete_option('afvdata_color_header_bg');
delete_option('afvdata_color_header_text');
delete_option('afvdata_color_highlight_bg');
