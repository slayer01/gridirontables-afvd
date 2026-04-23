<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
defined('ABSPATH') || exit;
?>
<h2><?php esc_html_e('Import Status', 'afvdata'); ?></h2>

<?php if ($last_sync) : ?>
    <p>
        <?php
        printf(
            /* translators: %s: date/time string */
            esc_html__('Last full sync: %s', 'afvdata'),
            esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $last_sync))
        );
        ?>
    </p>
<?php else : ?>
    <p><?php esc_html_e('No import has been run yet.', 'afvdata'); ?></p>
<?php endif; ?>

<?php if (empty($leagues)) : ?>
    <p><?php esc_html_e('No leagues configured. Go to the Leagues tab to add some.', 'afvdata'); ?></p>
<?php else : ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('League', 'afvdata'); ?></th>
                <th><?php esc_html_e('Liga Code', 'afvdata'); ?></th>
                <th><?php esc_html_e('Active', 'afvdata'); ?></th>
                <th><?php esc_html_e('Standings Rows', 'afvdata'); ?></th>
                <th><?php esc_html_e('Schedule Rows', 'afvdata'); ?></th>
                <th><?php esc_html_e('Raw Data', 'afvdata'); ?></th>
                <th><?php esc_html_e('Action', 'afvdata'); ?></th>
                <th><?php esc_html_e('Status', 'afvdata'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leagues as $league) :
                $counts = AFVData_DB::get_counts($league['liga_code']);
            ?>
                <tr>
                    <td><?php echo esc_html($league['label'] ?: $league['slug']); ?></td>
                    <td><code><?php echo esc_html($league['liga_code']); ?></code></td>
                    <td><?php echo !empty($league['active']) ? '&#10003;' : '&#10007;'; ?></td>
                    <td class="afvdata-count-standings" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        <?php echo (int) $counts['standings']; ?>
                    </td>
                    <td class="afvdata-count-schedule" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        <?php echo (int) $counts['schedule']; ?>
                    </td>
                    <td>
                        <button type="button" class="button button-small afvdata-view-raw"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>" data-type="standings">
                            <?php esc_html_e('Standings', 'afvdata'); ?>
                        </button>
                        <button type="button" class="button button-small afvdata-view-raw"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>" data-type="schedule">
                            <?php esc_html_e('Schedule', 'afvdata'); ?>
                        </button>
                    </td>
                    <td>
                        <button type="button" class="button afvdata-import-league"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                            <?php esc_html_e('Import', 'afvdata'); ?>
                        </button>
                    </td>
                    <td class="afvdata-import-status" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        &mdash;
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 15px;">
        <button type="button" class="button button-primary" id="afvdata-import-all">
            <?php esc_html_e('Import All Active Leagues', 'afvdata'); ?>
        </button>
        <span id="afvdata-import-all-status"></span>
    </p>

    <div id="afvdata-raw-data-wrap" style="display:none; margin-top:20px;">
        <h3 id="afvdata-raw-data-title"></h3>
        <div id="afvdata-raw-data-content" style="overflow-x:auto;"></div>
    </div>

    <h3><?php esc_html_e('Shortcode Reference', 'afvdata'); ?></h3>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Shortcode', 'afvdata'); ?></th>
                <th><?php esc_html_e('Description', 'afvdata'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leagues as $league) : ?>
                <tr>
                    <td><code>[afvdata_standings league="<?php echo esc_attr($league['slug']); ?>"]</code></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %s: league label */
                            esc_html__('Standings table for %s', 'afvdata'),
                            esc_html($league['label'] ?: $league['slug'])
                        );
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><code>[afvdata_schedule league="<?php echo esc_attr($league['slug']); ?>"]</code></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %s: league label */
                            esc_html__('Game schedule for %s', 'afvdata'),
                            esc_html($league['label'] ?: $league['slug'])
                        );
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3><?php esc_html_e('Attribute Reference', 'afvdata'); ?></h3>

    <h4><?php esc_html_e('Standings & Schedule', 'afvdata'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'afvdata'); ?></th>
                <th><?php esc_html_e('Values', 'afvdata'); ?></th>
                <th><?php esc_html_e('Description', 'afvdata'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>league</code></td>
                <td><?php esc_html_e('Slug or Liga Code', 'afvdata'); ?></td>
                <td><?php esc_html_e('Required. The league to display, e.g. league="herren".', 'afvdata'); ?></td>
            </tr>
            <tr>
                <td><code>group</code></td>
                <td><?php esc_html_e('e.g. "A", "B"', 'afvdata'); ?></td>
                <td><?php esc_html_e('Show only a specific group. Without this, all groups from the imported data are shown automatically.', 'afvdata'); ?></td>
            </tr>
            <tr>
                <td><code>highlight</code></td>
                <td><?php esc_html_e('Team name', 'afvdata'); ?></td>
                <td><?php esc_html_e('Override the team name to highlight. Defaults to the team name configured for this league.', 'afvdata'); ?></td>
            </tr>
            <tr>
                <td><code>class</code></td>
                <td><?php esc_html_e('CSS class name', 'afvdata'); ?></td>
                <td><?php esc_html_e('Add a custom CSS class to the wrapper element for styling.', 'afvdata'); ?></td>
            </tr>
        </tbody>
    </table>

    <h4><?php esc_html_e('Schedule only', 'afvdata'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'afvdata'); ?></th>
                <th><?php esc_html_e('Values', 'afvdata'); ?></th>
                <th><?php esc_html_e('Description', 'afvdata'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>home_only</code></td>
                <td><code>"1"</code></td>
                <td><?php esc_html_e('Show only home games of the configured team. Requires a team name set in the league config.', 'afvdata'); ?></td>
            </tr>
            <tr>
                <td><code>show</code></td>
                <td><code>"all"</code>, <code>"upcoming"</code>, <code>"past"</code></td>
                <td><?php esc_html_e('Filter by time. "upcoming" = today and future, "past" = before today. Default: "all".', 'afvdata'); ?></td>
            </tr>
            <tr>
                <td><code>limit</code></td>
                <td><?php esc_html_e('Number', 'afvdata'); ?></td>
                <td><?php esc_html_e('Maximum number of games to show. Useful with show="upcoming" to display the next N games.', 'afvdata'); ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>
