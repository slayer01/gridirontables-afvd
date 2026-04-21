<?php
defined('ABSPATH') || exit;

class AFVD_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_post_afvd_save_settings', [$this, 'handle_save_settings']);
        add_action('admin_post_afvd_save_leagues', [$this, 'handle_save_leagues']);
        add_action('wp_ajax_afvd_import', [$this, 'ajax_import']);
        add_action('wp_ajax_afvd_import_all', [$this, 'ajax_import_all']);
    }

    public function add_menu_page() {
        add_menu_page(
            __('AFVD Data', 'afvd-data'),
            __('AFVD Data', 'afvd-data'),
            'manage_options',
            'afvd-data',
            [$this, 'render_page'],
            'dashicons-football',
            30
        );
    }

    public function enqueue_assets($hook) {
        if ('toplevel_page_afvd-data' !== $hook) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_enqueue_style(
            'afvd-data-admin',
            AFVD_DATA_PLUGIN_URL . 'admin/css/admin.css',
            [],
            AFVD_DATA_VERSION
        );

        wp_enqueue_script(
            'afvd-data-admin',
            AFVD_DATA_PLUGIN_URL . 'admin/js/admin.js',
            ['jquery', 'wp-color-picker'],
            AFVD_DATA_VERSION,
            true
        );

        wp_localize_script('afvd-data-admin', 'afvdData', [
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('afvd_data_import'),
            'themePalette'  => self::get_theme_palette(),
            'i18n'          => [
                'importing'  => __('Importing...', 'afvd-data'),
                'success'    => __('Import successful', 'afvd-data'),
                'error'      => __('Import failed', 'afvd-data'),
                'confirm'    => __('Import all active leagues now?', 'afvd-data'),
            ],
        ]);
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include AFVD_DATA_PLUGIN_DIR . 'admin/views/page-settings.php';
    }

    public function handle_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'afvd-data'));
        }

        check_admin_referer('afvd_save_settings', 'afvd_nonce');

        update_option('afvd_data_api_base_url', esc_url_raw($_POST['api_base_url'] ?? 'http://vereine.football-verband.de/'));

        update_option('afvd_data_color_header_bg', sanitize_hex_color($_POST['color_header_bg'] ?? '#333333'));
        update_option('afvd_data_color_header_text', sanitize_hex_color($_POST['color_header_text'] ?? '#ffffff'));
        update_option('afvd_data_color_highlight_bg', sanitize_hex_color($_POST['color_highlight_bg'] ?? ''));

        $allowed_intervals = ['manual', 'hourly', 'twicedaily', 'daily'];
        $interval = sanitize_key($_POST['sync_interval'] ?? 'manual');
        if (!in_array($interval, $allowed_intervals, true)) {
            $interval = 'manual';
        }

        $old_interval = get_option('afvd_data_sync_interval', 'manual');
        update_option('afvd_data_sync_interval', $interval);

        if ($old_interval !== $interval) {
            AFVD_Cron::unschedule();
            AFVD_Cron::schedule();
        }

        wp_redirect(add_query_arg([
            'page'    => 'afvd-data',
            'tab'     => 'settings',
            'updated' => '1',
        ], admin_url('admin.php')));
        exit;
    }

    public function handle_save_leagues() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'afvd-data'));
        }

        check_admin_referer('afvd_save_leagues', 'afvd_nonce');

        $leagues    = [];
        $slugs      = $_POST['league_slug'] ?? [];
        $labels     = $_POST['league_label'] ?? [];
        $codes      = $_POST['league_code'] ?? [];
        $groups     = $_POST['league_groups'] ?? [];
        $team_names = $_POST['league_team_name'] ?? [];
        $actives    = $_POST['league_active'] ?? [];

        foreach ($slugs as $i => $slug) {
            $slug = sanitize_key($slug);
            $code = sanitize_text_field($codes[$i] ?? '');
            if ('' === $slug || '' === $code) {
                continue;
            }

            $leagues[] = [
                'slug'      => $slug,
                'label'     => sanitize_text_field($labels[$i] ?? $slug),
                'liga_code' => $code,
                'groups'    => sanitize_text_field($groups[$i] ?? ''),
                'team_name' => sanitize_text_field($team_names[$i] ?? ''),
                'active'    => isset($actives[$i]),
            ];
        }

        update_option('afvd_data_leagues', $leagues);

        wp_redirect(add_query_arg([
            'page'    => 'afvd-data',
            'tab'     => 'leagues',
            'updated' => '1',
        ], admin_url('admin.php')));
        exit;
    }

    public function ajax_import() {
        check_ajax_referer('afvd_data_import', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'afvd-data'));
        }

        $liga_code = sanitize_text_field($_POST['liga_code'] ?? '');
        if ('' === $liga_code) {
            wp_send_json_error(__('No league specified', 'afvd-data'));
        }

        $importer = new AFVD_Importer();
        $result   = $importer->import_league($liga_code);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success($result);
    }

    public function ajax_import_all() {
        check_ajax_referer('afvd_data_import', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'afvd-data'));
        }

        $importer = new AFVD_Importer();
        $results  = $importer->import_all_active();

        wp_send_json_success($results);
    }

    /**
     * Get the active theme's color palette as a flat array of hex values.
     */
    public static function get_theme_palette() {
        $colors = [];

        // Block themes (WP 5.9+): read from theme.json global settings
        if (function_exists('wp_get_global_settings')) {
            $palette = wp_get_global_settings(['color', 'palette', 'theme']);
            if (!empty($palette) && is_array($palette)) {
                foreach ($palette as $entry) {
                    if (!empty($entry['color'])) {
                        $colors[] = sanitize_hex_color($entry['color']);
                    }
                }
            }
        }

        // Classic themes: read from editor-color-palette theme support
        if (empty($colors)) {
            $support = get_theme_support('editor-color-palette');
            if (!empty($support[0]) && is_array($support[0])) {
                foreach ($support[0] as $entry) {
                    if (!empty($entry['color'])) {
                        $colors[] = sanitize_hex_color($entry['color']);
                    }
                }
            }
        }

        return array_values(array_filter($colors));
    }

    /**
     * Helper to get leagues config.
     */
    public static function get_leagues() {
        return get_option('afvd_data_leagues', []);
    }

    /**
     * Find a league config by its slug.
     */
    public static function get_league_by_slug($slug) {
        $leagues = self::get_leagues();
        foreach ($leagues as $league) {
            if ($league['slug'] === $slug) {
                return $league;
            }
        }
        return null;
    }
}
