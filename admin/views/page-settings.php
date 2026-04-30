<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- nonce checked in handler
defined('ABSPATH') || exit;
$active_tab       = sanitize_key($_GET['tab'] ?? 'settings');
$base_url         = get_option('dsfooboo_football_data_api_base_url', 'http://vereine.football-verband.de/');
$interval         = get_option('dsfooboo_football_data_sync_interval', 'manual');
$color_header_bg  = get_option('dsfooboo_football_data_color_header_bg', '#333333');
$color_header_txt = get_option('dsfooboo_football_data_color_header_text', '#ffffff');
$color_highlight  = get_option('dsfooboo_football_data_color_highlight_bg', '');
$leagues    = get_option('dsfooboo_football_data_leagues', []);
$last_sync  = get_option('dsfooboo_football_data_last_sync', 0);
?>
<div class="wrap">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
        <img src="<?php echo esc_url(DSFOOBOO_FOOTBALL_DATA_PLUGIN_URL . 'admin/img/logo.png'); ?>" alt="" style="height:64px;">
        <h1 style="margin:0;"><?php esc_html_e('DSFOOBOO Football Data', 'dsfooboo_football_data'); ?></h1>
    </div>

    <?php if (!empty($_GET['updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved.', 'dsfooboo_football_data'); ?></p>
        </div>
    <?php endif; ?>

    <nav class="nav-tab-wrapper">
        <a href="<?php echo esc_url(add_query_arg(['page' => 'dsfooboo_football_data', 'tab' => 'settings'], admin_url('admin.php'))); ?>"
           class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Settings', 'dsfooboo_football_data'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(['page' => 'dsfooboo_football_data', 'tab' => 'leagues'], admin_url('admin.php'))); ?>"
           class="nav-tab <?php echo 'leagues' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Leagues', 'dsfooboo_football_data'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(['page' => 'dsfooboo_football_data', 'tab' => 'import'], admin_url('admin.php'))); ?>"
           class="nav-tab <?php echo 'import' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Import', 'dsfooboo_football_data'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(['page' => 'dsfooboo_football_data', 'tab' => 'info'], admin_url('admin.php'))); ?>"
           class="nav-tab <?php echo 'info' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Info', 'dsfooboo_football_data'); ?>
        </a>
    </nav>

    <div class="dsfooboo_football_data_tab_content">
        <?php
        switch ($active_tab) {
            case 'leagues':
                include DSFOOBOO_FOOTBALL_DATA_PLUGIN_DIR . 'admin/views/partial-leagues.php';
                break;
            case 'import':
                include DSFOOBOO_FOOTBALL_DATA_PLUGIN_DIR . 'admin/views/partial-import.php';
                break;
            case 'info':
                include DSFOOBOO_FOOTBALL_DATA_PLUGIN_DIR . 'admin/views/partial-info.php';
                break;
            default:
                ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="dsfooboo_football_data_save_settings">
                    <?php wp_nonce_field('dsfooboo_football_data_save_settings', 'dsfooboo_football_data_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="api_base_url"><?php esc_html_e('API Base URL', 'dsfooboo_football_data'); ?></label>
                            </th>
                            <td>
                                <input type="url" id="api_base_url" name="api_base_url"
                                       value="<?php echo esc_attr($base_url); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('XML data endpoint. Usually no need to change this.', 'dsfooboo_football_data'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sync_interval"><?php esc_html_e('Auto Sync', 'dsfooboo_football_data'); ?></label>
                            </th>
                            <td>
                                <select id="sync_interval" name="sync_interval">
                                    <option value="manual" <?php selected($interval, 'manual'); ?>>
                                        <?php esc_html_e('Manual only', 'dsfooboo_football_data'); ?>
                                    </option>
                                    <option value="hourly" <?php selected($interval, 'hourly'); ?>>
                                        <?php esc_html_e('Every hour', 'dsfooboo_football_data'); ?>
                                    </option>
                                    <option value="twicedaily" <?php selected($interval, 'twicedaily'); ?>>
                                        <?php esc_html_e('Twice daily', 'dsfooboo_football_data'); ?>
                                    </option>
                                    <option value="daily" <?php selected($interval, 'daily'); ?>>
                                        <?php esc_html_e('Daily', 'dsfooboo_football_data'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('How often to automatically fetch data. Note: WP-Cron depends on site traffic. For reliable scheduling, set up a server cron job that calls wp-cron.php.', 'dsfooboo_football_data'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Header Background', 'dsfooboo_football_data'); ?></th>
                            <td>
                                <input type="text" name="color_header_bg" value="<?php echo esc_attr($color_header_bg); ?>" class="dsfooboo_football_data_color_picker" data-default-color="#333333">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Header Text', 'dsfooboo_football_data'); ?></th>
                            <td>
                                <input type="text" name="color_header_text" value="<?php echo esc_attr($color_header_txt); ?>" class="dsfooboo_football_data_color_picker" data-default-color="#ffffff">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Highlight Background', 'dsfooboo_football_data'); ?></th>
                            <td>
                                <input type="text" name="color_highlight_bg" value="<?php echo esc_attr($color_highlight); ?>" class="dsfooboo_football_data_color_picker" data-default-color="">
                                <p class="description">
                                    <?php esc_html_e('Background color for highlighted team rows. Leave empty for no background.', 'dsfooboo_football_data'); ?>
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
