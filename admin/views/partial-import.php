<?php
defined('ABSPATH') || exit;
?>
<h2><?php esc_html_e('Import Status', 'afvd-data'); ?></h2>

<?php if ($last_sync) : ?>
    <p>
        <?php
        printf(
            /* translators: %s: date/time string */
            esc_html__('Last full sync: %s', 'afvd-data'),
            esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $last_sync))
        );
        ?>
    </p>
<?php else : ?>
    <p><?php esc_html_e('No import has been run yet.', 'afvd-data'); ?></p>
<?php endif; ?>

<?php if (empty($leagues)) : ?>
    <p><?php esc_html_e('No leagues configured. Go to the Leagues tab to add some.', 'afvd-data'); ?></p>
<?php else : ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('League', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Liga Code', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Active', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Standings Rows', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Schedule Rows', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Raw Data', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Action', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Status', 'afvd-data'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leagues as $league) :
                $counts = AFVD_DB::get_counts($league['liga_code']);
            ?>
                <tr>
                    <td><?php echo esc_html($league['label'] ?: $league['slug']); ?></td>
                    <td><code><?php echo esc_html($league['liga_code']); ?></code></td>
                    <td><?php echo !empty($league['active']) ? '&#10003;' : '&#10007;'; ?></td>
                    <td class="afvd-count-standings" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        <?php echo (int) $counts['standings']; ?>
                    </td>
                    <td class="afvd-count-schedule" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        <?php echo (int) $counts['schedule']; ?>
                    </td>
                    <td>
                        <button type="button" class="button button-small afvd-view-raw"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>" data-type="standings">
                            <?php esc_html_e('Standings', 'afvd-data'); ?>
                        </button>
                        <button type="button" class="button button-small afvd-view-raw"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>" data-type="schedule">
                            <?php esc_html_e('Schedule', 'afvd-data'); ?>
                        </button>
                    </td>
                    <td>
                        <button type="button" class="button afvd-import-league"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                            <?php esc_html_e('Import', 'afvd-data'); ?>
                        </button>
                    </td>
                    <td class="afvd-import-status" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        &mdash;
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 15px;">
        <button type="button" class="button button-primary" id="afvd-import-all">
            <?php esc_html_e('Import All Active Leagues', 'afvd-data'); ?>
        </button>
        <span id="afvd-import-all-status"></span>
    </p>

    <div id="afvd-raw-data-wrap" style="display:none; margin-top:20px;">
        <h3 id="afvd-raw-data-title"></h3>
        <div id="afvd-raw-data-content" style="overflow-x:auto;"></div>
    </div>

    <h3><?php esc_html_e('Shortcode Reference', 'afvd-data'); ?></h3>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Shortcode', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Description', 'afvd-data'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leagues as $league) : ?>
                <tr>
                    <td><code>[afvd_standings league="<?php echo esc_attr($league['slug']); ?>"]</code></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %s: league label */
                            esc_html__('Standings table for %s', 'afvd-data'),
                            esc_html($league['label'] ?: $league['slug'])
                        );
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><code>[afvd_schedule league="<?php echo esc_attr($league['slug']); ?>"]</code></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %s: league label */
                            esc_html__('Game schedule for %s', 'afvd-data'),
                            esc_html($league['label'] ?: $league['slug'])
                        );
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3><?php esc_html_e('Attribute Reference', 'afvd-data'); ?></h3>

    <h4><?php esc_html_e('Standings & Schedule', 'afvd-data'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Values', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Description', 'afvd-data'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>league</code></td>
                <td><?php esc_html_e('Slug or Liga Code', 'afvd-data'); ?></td>
                <td><?php esc_html_e('Required. The league to display, e.g. league="herren".', 'afvd-data'); ?></td>
            </tr>
            <tr>
                <td><code>group</code></td>
                <td><?php esc_html_e('e.g. "A", "B"', 'afvd-data'); ?></td>
                <td><?php esc_html_e('Show only a specific group. Without this, all groups from the imported data are shown automatically.', 'afvd-data'); ?></td>
            </tr>
            <tr>
                <td><code>highlight</code></td>
                <td><?php esc_html_e('Team name', 'afvd-data'); ?></td>
                <td><?php esc_html_e('Override the team name to highlight. Defaults to the team name configured for this league.', 'afvd-data'); ?></td>
            </tr>
            <tr>
                <td><code>class</code></td>
                <td><?php esc_html_e('CSS class name', 'afvd-data'); ?></td>
                <td><?php esc_html_e('Add a custom CSS class to the wrapper element for styling.', 'afvd-data'); ?></td>
            </tr>
        </tbody>
    </table>

    <h4><?php esc_html_e('Schedule only', 'afvd-data'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Values', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Description', 'afvd-data'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>home_only</code></td>
                <td><code>"1"</code></td>
                <td><?php esc_html_e('Show only home games of the configured team. Requires a team name set in the league config.', 'afvd-data'); ?></td>
            </tr>
            <tr>
                <td><code>show</code></td>
                <td><code>"all"</code>, <code>"upcoming"</code>, <code>"past"</code></td>
                <td><?php esc_html_e('Filter by time. "upcoming" = today and future, "past" = before today. Default: "all".', 'afvd-data'); ?></td>
            </tr>
            <tr>
                <td><code>limit</code></td>
                <td><?php esc_html_e('Number', 'afvd-data'); ?></td>
                <td><?php esc_html_e('Maximum number of games to show. Useful with show="upcoming" to display the next N games.', 'afvd-data'); ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>
