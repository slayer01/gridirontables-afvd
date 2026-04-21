<?php
defined('ABSPATH') || exit;

$active_tab = sanitize_key($_GET['tab'] ?? 'settings');
$team_name  = get_option('afvd_data_team_name', '');
$base_url   = get_option('afvd_data_api_base_url', 'http://vereine.football-verband.de/');
$interval   = get_option('afvd_data_sync_interval', 'manual');
$leagues    = get_option('afvd_data_leagues', []);
$last_sync  = get_option('afvd_data_last_sync', 0);
?>
<div class="wrap">
    <h1><?php esc_html_e('AFVD Data', 'afvd-data'); ?></h1>

    <?php if (!empty($_GET['updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved.', 'afvd-data'); ?></p>
        </div>
    <?php endif; ?>

    <nav class="nav-tab-wrapper">
        <a href="<?php echo esc_url(add_query_arg(['page' => 'afvd-data', 'tab' => 'settings'], admin_url('options-general.php'))); ?>"
           class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Settings', 'afvd-data'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(['page' => 'afvd-data', 'tab' => 'leagues'], admin_url('options-general.php'))); ?>"
           class="nav-tab <?php echo 'leagues' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Leagues', 'afvd-data'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg(['page' => 'afvd-data', 'tab' => 'import'], admin_url('options-general.php'))); ?>"
           class="nav-tab <?php echo 'import' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Import', 'afvd-data'); ?>
        </a>
    </nav>

    <div class="afvd-tab-content">
        <?php
        switch ($active_tab) {
            case 'leagues':
                include AFVD_DATA_PLUGIN_DIR . 'admin/views/partial-leagues.php';
                break;
            case 'import':
                include AFVD_DATA_PLUGIN_DIR . 'admin/views/partial-import.php';
                break;
            default:
                ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="afvd_save_settings">
                    <?php wp_nonce_field('afvd_save_settings', 'afvd_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="team_name"><?php esc_html_e('Team Name', 'afvd-data'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="team_name" name="team_name"
                                       value="<?php echo esc_attr($team_name); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Your team name as it appears in the AFVD data (e.g., "Wetterau Bulls"). Used to highlight your games.', 'afvd-data'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_base_url"><?php esc_html_e('API Base URL', 'afvd-data'); ?></label>
                            </th>
                            <td>
                                <input type="url" id="api_base_url" name="api_base_url"
                                       value="<?php echo esc_attr($base_url); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('AFVD XML data endpoint. Usually no need to change this.', 'afvd-data'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sync_interval"><?php esc_html_e('Auto Sync', 'afvd-data'); ?></label>
                            </th>
                            <td>
                                <select id="sync_interval" name="sync_interval">
                                    <option value="manual" <?php selected($interval, 'manual'); ?>>
                                        <?php esc_html_e('Manual only', 'afvd-data'); ?>
                                    </option>
                                    <option value="hourly" <?php selected($interval, 'hourly'); ?>>
                                        <?php esc_html_e('Every hour', 'afvd-data'); ?>
                                    </option>
                                    <option value="twicedaily" <?php selected($interval, 'twicedaily'); ?>>
                                        <?php esc_html_e('Twice daily', 'afvd-data'); ?>
                                    </option>
                                    <option value="daily" <?php selected($interval, 'daily'); ?>>
                                        <?php esc_html_e('Daily', 'afvd-data'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('How often to automatically fetch data from AFVD. Note: WP-Cron depends on site traffic. For reliable scheduling, set up a server cron job that calls wp-cron.php.', 'afvd-data'); ?>
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
