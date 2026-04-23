<?php
defined('ABSPATH') || exit;

class AFVData_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_post_afvdata_save_settings', [$this, 'handle_save_settings']);
        add_action('admin_post_afvdata_save_leagues', [$this, 'handle_save_leagues']);
        add_action('wp_ajax_afvdata_import', [$this, 'ajax_import']);
        add_action('wp_ajax_afvdata_import_all', [$this, 'ajax_import_all']);
        add_action('wp_ajax_afvdata_raw_data', [$this, 'ajax_raw_data']);
    }

    public function add_menu_page() {
        add_menu_page(
            __('AFVData', 'afvdata'),
            __('AFVData', 'afvdata'),
            'manage_options',
            'afvdata',
            [$this, 'render_page'],
            'dashicons-awards',
            30
        );
    }

    public function enqueue_assets($hook) {
        if ('toplevel_page_afvdata' !== $hook) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_enqueue_style(
            'afvdata-admin',
            AFVDATA_PLUGIN_URL . 'admin/css/admin.css',
            [],
            AFVDATA_VERSION
        );

        wp_enqueue_script(
            'afvdata-admin',
            AFVDATA_PLUGIN_URL . 'admin/js/admin.js',
            ['jquery', 'wp-color-picker'],
            AFVDATA_VERSION,
            true
        );

        wp_localize_script('afvdata-admin', 'afvdataConfig', [
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('afvdata_import'),
            'themePalette'  => self::get_theme_palette(),
            'i18n'          => [
                'importing'  => __('Importing...', 'afvdata'),
                'success'    => __('Import successful', 'afvdata'),
                'error'      => __('Import failed', 'afvdata'),
                'confirm'    => __('Import all active leagues now?', 'afvdata'),
            ],
        ]);
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include AFVDATA_PLUGIN_DIR . 'admin/views/page-settings.php';
    }

    public function handle_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized', 'afvdata'));
        }

        check_admin_referer('afvdata_save_settings', 'afvdata_nonce');

        update_option('afvdata_api_base_url', esc_url_raw(wp_unslash($_POST['api_base_url'] ?? 'http://vereine.football-verband.de/')));

        update_option('afvdata_color_header_bg', sanitize_hex_color(wp_unslash($_POST['color_header_bg'] ?? '#333333')));
        update_option('afvdata_color_header_text', sanitize_hex_color(wp_unslash($_POST['color_header_text'] ?? '#ffffff')));
        update_option('afvdata_color_highlight_bg', sanitize_hex_color(wp_unslash($_POST['color_highlight_bg'] ?? '')));

        $allowed_intervals = ['manual', 'hourly', 'twicedaily', 'daily'];
        $interval = sanitize_key(wp_unslash($_POST['sync_interval'] ?? 'manual'));
        if (!in_array($interval, $allowed_intervals, true)) {
            $interval = 'manual';
        }

        $old_interval = get_option('afvdata_sync_interval', 'manual');
        update_option('afvdata_sync_interval', $interval);

        if ($old_interval !== $interval) {
            AFVData_Cron::unschedule();
            AFVData_Cron::schedule();
        }

        wp_safe_redirect(add_query_arg([
            'page'    => 'afvdata',
            'tab'     => 'settings',
            'updated' => '1',
        ], admin_url('admin.php')));
        exit;
    }

    public function handle_save_leagues() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized', 'afvdata'));
        }

        check_admin_referer('afvdata_save_leagues', 'afvdata_nonce');

        $leagues    = [];
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- individual values sanitized in loop below
        $slugs      = isset($_POST['league_slug']) ? wp_unslash($_POST['league_slug']) : [];
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $labels     = isset($_POST['league_label']) ? wp_unslash($_POST['league_label']) : [];
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $codes      = isset($_POST['league_code']) ? wp_unslash($_POST['league_code']) : [];
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $team_names = isset($_POST['league_team_name']) ? wp_unslash($_POST['league_team_name']) : [];
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $actives    = isset($_POST['league_active']) ? wp_unslash($_POST['league_active']) : [];

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
                'team_name' => sanitize_text_field($team_names[$i] ?? ''),
                'active'    => isset($actives[$i]),
            ];
        }

        update_option('afvdata_leagues', $leagues);

        wp_safe_redirect(add_query_arg([
            'page'    => 'afvdata',
            'tab'     => 'leagues',
            'updated' => '1',
        ], admin_url('admin.php')));
        exit;
    }

    public function ajax_import() {
        check_ajax_referer('afvdata_import', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'afvdata'));
        }

        $liga_code = sanitize_text_field(wp_unslash($_POST['liga_code'] ?? ''));
        if ('' === $liga_code) {
            wp_send_json_error(__('No league specified', 'afvdata'));
        }

        $importer = new AFVData_Importer();
        $result   = $importer->import_league($liga_code);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success($result);
    }

    public function ajax_import_all() {
        check_ajax_referer('afvdata_import', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'afvdata'));
        }

        $importer = new AFVData_Importer();
        $results  = $importer->import_all_active();

        wp_send_json_success($results);
    }

    public function ajax_raw_data() {
        check_ajax_referer('afvdata_import', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'afvdata'));
        }

        $liga_code = sanitize_text_field(wp_unslash($_POST['liga_code'] ?? ''));
        $type      = sanitize_key(wp_unslash($_POST['type'] ?? ''));

        if ('' === $liga_code || !in_array($type, ['standings', 'schedule'], true)) {
            wp_send_json_error(__('Invalid request', 'afvdata'));
        }

        if ('standings' === $type) {
            $rows = AFVData_DB::get_standings($liga_code);
        } else {
            $rows = AFVData_DB::get_schedule($liga_code, []);
        }

        wp_send_json_success([
            'type' => $type,
            'rows' => $rows,
        ]);
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
        return get_option('afvdata_leagues', []);
    }

    /**
     * Find a league config by slug or liga_code.
     */
    public static function get_league_by_slug($identifier) {
        $identifier = sanitize_key($identifier);
        $leagues = self::get_leagues();

        // Try slug first
        foreach ($leagues as $league) {
            if ($league['slug'] === $identifier) {
                return $league;
            }
        }

        // Fall back to liga_code
        foreach ($leagues as $league) {
            if ($league['liga_code'] === $identifier) {
                return $league;
            }
        }

        return null;
    }
}
