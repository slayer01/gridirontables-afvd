<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
defined('ABSPATH') || exit;
?>
<h2><?php esc_html_e('Import Status', 'dsfooboo_football_data'); ?></h2>

<?php if ($last_sync) : ?>
    <p>
        <?php
        printf(
            /* translators: %s: date/time string */
            esc_html__('Last full sync: %s', 'dsfooboo_football_data'),
            esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $last_sync))
        );
        ?>
    </p>
<?php else : ?>
    <p><?php esc_html_e('No import has been run yet.', 'dsfooboo_football_data'); ?></p>
<?php endif; ?>

<?php if (empty($leagues)) : ?>
    <p><?php esc_html_e('No leagues configured. Go to the Leagues tab to add some.', 'dsfooboo_football_data'); ?></p>
<?php else : ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('League', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Liga Code', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Active', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Standings Rows', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Schedule Rows', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Raw Data', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Action', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Status', 'dsfooboo_football_data'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leagues as $league) :
                $counts = DSFooboo_Football_Data_DB::get_counts($league['liga_code']);
            ?>
                <tr>
                    <td><?php echo esc_html($league['label'] ?: $league['slug']); ?></td>
                    <td><code><?php echo esc_html($league['liga_code']); ?></code></td>
                    <td><?php echo !empty($league['active']) ? '&#10003;' : '&#10007;'; ?></td>
                    <td class="dsfooboo_football_data_count_standings" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        <?php echo (int) $counts['standings']; ?>
                    </td>
                    <td class="dsfooboo_football_data_count_schedule" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        <?php echo (int) $counts['schedule']; ?>
                    </td>
                    <td>
                        <button type="button" class="button button-small dsfooboo_football_data_view_raw"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>" data-type="standings">
                            <?php esc_html_e('Standings', 'dsfooboo_football_data'); ?>
                        </button>
                        <button type="button" class="button button-small dsfooboo_football_data_view_raw"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>" data-type="schedule">
                            <?php esc_html_e('Schedule', 'dsfooboo_football_data'); ?>
                        </button>
                    </td>
                    <td>
                        <button type="button" class="button dsfooboo_football_data_import_league"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                            <?php esc_html_e('Import', 'dsfooboo_football_data'); ?>
                        </button>
                    </td>
                    <td class="dsfooboo_football_data_import_status" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        &mdash;
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 15px;">
        <button type="button" class="button button-primary" id="dsfooboo_football_data_import_all">
            <?php esc_html_e('Import All Active Leagues', 'dsfooboo_football_data'); ?>
        </button>
        <span id="dsfooboo_football_data_import_all_status"></span>
    </p>

    <div id="dsfooboo_football_data_raw_data_wrap" style="display:none; margin-top:20px;">
        <h3 id="dsfooboo_football_data_raw_data_title"></h3>
        <div id="dsfooboo_football_data_raw_data_content" style="overflow-x:auto;"></div>
    </div>

    <h3><?php esc_html_e('Shortcode Reference', 'dsfooboo_football_data'); ?></h3>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Shortcode', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Description', 'dsfooboo_football_data'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leagues as $league) : ?>
                <tr>
                    <td><code>[dsfooboo_football_data_standings league="<?php echo esc_attr($league['slug']); ?>"]</code></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %s: league label */
                            esc_html__('Standings table for %s', 'dsfooboo_football_data'),
                            esc_html($league['label'] ?: $league['slug'])
                        );
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><code>[dsfooboo_football_data_schedule league="<?php echo esc_attr($league['slug']); ?>"]</code></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %s: league label */
                            esc_html__('Game schedule for %s', 'dsfooboo_football_data'),
                            esc_html($league['label'] ?: $league['slug'])
                        );
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="description">
        <?php esc_html_e('Note: the legacy [footballdata_*] and [afvdata_*] shortcodes still work as aliases.', 'dsfooboo_football_data'); ?>
    </p>

    <h3><?php esc_html_e('Attribute Reference', 'dsfooboo_football_data'); ?></h3>

    <h4><?php esc_html_e('Standings & Schedule', 'dsfooboo_football_data'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Values', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Description', 'dsfooboo_football_data'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>league</code></td>
                <td><?php esc_html_e('Slug or Liga Code', 'dsfooboo_football_data'); ?></td>
                <td><?php esc_html_e('Required. The league to display, e.g. league="herren".', 'dsfooboo_football_data'); ?></td>
            </tr>
            <tr>
                <td><code>group</code></td>
                <td><?php esc_html_e('e.g. "A", "B"', 'dsfooboo_football_data'); ?></td>
                <td><?php esc_html_e('Show only a specific group. Without this, all groups from the imported data are shown automatically.', 'dsfooboo_football_data'); ?></td>
            </tr>
            <tr>
                <td><code>highlight</code></td>
                <td><?php esc_html_e('Team name', 'dsfooboo_football_data'); ?></td>
                <td><?php esc_html_e('Override the team name to highlight. Defaults to the team name configured for this league.', 'dsfooboo_football_data'); ?></td>
            </tr>
            <tr>
                <td><code>class</code></td>
                <td><?php esc_html_e('CSS class name', 'dsfooboo_football_data'); ?></td>
                <td><?php esc_html_e('Add a custom CSS class to the wrapper element for styling.', 'dsfooboo_football_data'); ?></td>
            </tr>
        </tbody>
    </table>

    <h4><?php esc_html_e('Schedule only', 'dsfooboo_football_data'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Values', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Description', 'dsfooboo_football_data'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>home_only</code></td>
                <td><code>"1"</code></td>
                <td><?php esc_html_e('Show only home games of the configured team. Requires a team name set in the league config.', 'dsfooboo_football_data'); ?></td>
            </tr>
            <tr>
                <td><code>show</code></td>
                <td><code>"all"</code>, <code>"upcoming"</code>, <code>"past"</code></td>
                <td><?php esc_html_e('Filter by time. "upcoming" = today and future, "past" = before today. Default: "all".', 'dsfooboo_football_data'); ?></td>
            </tr>
            <tr>
                <td><code>limit</code></td>
                <td><?php esc_html_e('Number', 'dsfooboo_football_data'); ?></td>
                <td><?php esc_html_e('Maximum number of games to show. Useful with show="upcoming" to display the next N games.', 'dsfooboo_football_data'); ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>
