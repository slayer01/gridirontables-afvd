<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- nonce checked in handler
defined('ABSPATH') || exit;
$active_tab       = sanitize_key($_GET['tab'] ?? 'settings');
$base_url         = get_option('afvdata_api_base_url', 'http://vereine.football-verband.de/');
$interval         = get_option('afvdata_sync_interval', 'manual');
$color_header_bg  = get_option('afvdata_color_header_bg', '#333333');
$color_header_txt = get_option('afvdata_color_header_text', '#ffffff');
$color_highlight  = get_option('afvdata_color_highlight_bg', '');
$leagues    = get_option('afvdata_leagues', []);
$last_sync  = get_option('afvdata_last_sync', 0);
?>
<div class="wrap">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
        <img src="<?php echo esc_url(AFVDATA_PLUGIN_URL . 'admin/img/logo.png'); ?>" alt="" style="height:64px;">
        <h1 style="margin:0;"><?php esc_html_e('AFVData', 'afvdata'); ?></h1>
    </div>

    <?php if (!empty($_GET['updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved.', 'afvdata'); ?></p>
        </div>
    <?php endif; ?>

    <nav class="nav-tab-wrapper">
        <a href="<?php echo esc_url(add_query_arg(['page' => 'afvdata', 'tab' => 'settings'], admin_url('admin.php'))); ?>"
           class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Settings', 'afvdata'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(['page' => 'afvdata', 'tab' => 'leagues'], admin_url('admin.php'))); ?>"
           class="nav-tab <?php echo 'leagues' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Leagues', 'afvdata'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(['page' => 'afvdata', 'tab' => 'import'], admin_url('admin.php'))); ?>"
           class="nav-tab <?php echo 'import' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Import', 'afvdata'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(['page' => 'afvdata', 'tab' => 'info'], admin_url('admin.php'))); ?>"
           class="nav-tab <?php echo 'info' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Info', 'afvdata'); ?>
        </a>
    </nav>

    <div class="afvdata-tab-content">
        <?php
        switch ($active_tab) {
            case 'leagues':
                include AFVDATA_PLUGIN_DIR . 'admin/views/partial-leagues.php';
                break;
            case 'import':
                include AFVDATA_PLUGIN_DIR . 'admin/views/partial-import.php';
                break;
            case 'info':
                include AFVDATA_PLUGIN_DIR . 'admin/views/partial-info.php';
                break;
            default:
                ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="afvdata_save_settings">
                    <?php wp_nonce_field('afvdata_save_settings', 'afvdata_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="api_base_url"><?php esc_html_e('API Base URL', 'afvdata'); ?></label>
                            </th>
                            <td>
                                <input type="url" id="api_base_url" name="api_base_url"
                                       value="<?php echo esc_attr($base_url); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('AFVD XML data endpoint. Usually no need to change this.', 'afvdata'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sync_interval"><?php esc_html_e('Auto Sync', 'afvdata'); ?></label>
                            </th>
                            <td>
                                <select id="sync_interval" name="sync_interval">
                                    <option value="manual" <?php selected($interval, 'manual'); ?>>
                                        <?php esc_html_e('Manual only', 'afvdata'); ?>
                                    </option>
                                    <option value="hourly" <?php selected($interval, 'hourly'); ?>>
                                        <?php esc_html_e('Every hour', 'afvdata'); ?>
                                    </option>
                                    <option value="twicedaily" <?php selected($interval, 'twicedaily'); ?>>
                                        <?php esc_html_e('Twice daily', 'afvdata'); ?>
                                    </option>
                                    <option value="daily" <?php selected($interval, 'daily'); ?>>
                                        <?php esc_html_e('Daily', 'afvdata'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('How often to automatically fetch data from AFVD. Note: WP-Cron depends on site traffic. For reliable scheduling, set up a server cron job that calls wp-cron.php.', 'afvdata'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Header Background', 'afvdata'); ?></th>
                            <td>
                                <input type="text" name="color_header_bg" value="<?php echo esc_attr($color_header_bg); ?>" class="afvdata-color-picker" data-default-color="#333333">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Header Text', 'afvdata'); ?></th>
                            <td>
                                <input type="text" name="color_header_text" value="<?php echo esc_attr($color_header_txt); ?>" class="afvdata-color-picker" data-default-color="#ffffff">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Highlight Background', 'afvdata'); ?></th>
                            <td>
                                <input type="text" name="color_highlight_bg" value="<?php echo esc_attr($color_highlight); ?>" class="afvdata-color-picker" data-default-color="">
                                <p class="description">
                                    <?php esc_html_e('Background color for highlighted team rows. Leave empty for no background.', 'afvdata'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(); ?>
                </form>
                <?php
                break;
        }
        ?>
    </div>
</div>
