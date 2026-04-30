<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
defined('ABSPATH') || exit;
?>
<h2><?php esc_html_e('Import Status', 'footballdata'); ?></h2>

<?php if ($last_sync) : ?>
    <p>
        <?php
        printf(
            /* translators: %s: date/time string */
            esc_html__('Last full sync: %s', 'footballdata'),
            esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $last_sync))
        );
        ?>
    </p>
<?php else : ?>
    <p><?php esc_html_e('No import has been run yet.', 'footballdata'); ?></p>
<?php endif; ?>

<?php if (empty($leagues)) : ?>
    <p><?php esc_html_e('No leagues configured. Go to the Leagues tab to add some.', 'footballdata'); ?></p>
<?php else : ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('League', 'footballdata'); ?></th>
                <th><?php esc_html_e('Liga Code', 'footballdata'); ?></th>
                <th><?php esc_html_e('Active', 'footballdata'); ?></th>
                <th><?php esc_html_e('Standings Rows', 'footballdata'); ?></th>
                <th><?php esc_html_e('Schedule Rows', 'footballdata'); ?></th>
                <th><?php esc_html_e('Raw Data', 'footballdata'); ?></th>
                <th><?php esc_html_e('Action', 'footballdata'); ?></th>
                <th><?php esc_html_e('Status', 'footballdata'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leagues as $league) :
                $counts = FootballData_DB::get_counts($league['liga_code']);
            ?>
                <tr>
                    <td><?php echo esc_html($league['label'] ?: $league['slug']); ?></td>
                    <td><code><?php echo esc_html($league['liga_code']); ?></code></td>
                    <td><?php echo !empty($league['active']) ? '&#10003;' : '&#10007;'; ?></td>
                    <td class="footballdata-count-standings" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        <?php echo (int) $counts['standings']; ?>
                    </td>
                    <td class="footballdata-count-schedule" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        <?php echo (int) $counts['schedule']; ?>
                    </td>
                    <td>
                        <button type="button" class="button button-small footballdata-view-raw"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>" data-type="standings">
                            <?php esc_html_e('Standings', 'footballdata'); ?>
                        </button>
                        <button type="button" class="button button-small footballdata-view-raw"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>" data-type="schedule">
                            <?php esc_html_e('Schedule', 'footballdata'); ?>
                        </button>
                    </td>
                    <td>
                        <button type="button" class="button footballdata-import-league"
                                data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                            <?php esc_html_e('Import', 'footballdata'); ?>
                        </button>
                    </td>
                    <td class="footballdata-import-status" data-liga="<?php echo esc_attr($league['liga_code']); ?>">
                        &mdash;
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 15px;">
        <button type="button" class="button button-primary" id="footballdata-import-all">
            <?php esc_html_e('Import All Active Leagues', 'footballdata'); ?>
        </button>
        <span id="footballdata-import-all-status"></span>
    </p>

    <div id="footballdata-raw-data-wrap" style="display:none; margin-top:20px;">
        <h3 id="footballdata-raw-data-title"></h3>
        <div id="footballdata-raw-data-content" style="overflow-x:auto;"></div>
    </div>

    <h3><?php esc_html_e('Shortcode Reference', 'footballdata'); ?></h3>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Shortcode', 'footballdata'); ?></th>
                <th><?php esc_html_e('Description', 'footballdata'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leagues as $league) : ?>
                <tr>
                    <td><code>[footballdata_standings league="<?php echo esc_attr($league['slug']); ?>"]</code></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %s: league label */
                            esc_html__('Standings table for %s', 'footballdata'),
                            esc_html($league['label'] ?: $league['slug'])
                        );
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><code>[footballdata_schedule league="<?php echo esc_attr($league['slug']); ?>"]</code></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %s: league label */
                            esc_html__('Game schedule for %s', 'footballdata'),
                            esc_html($league['label'] ?: $league['slug'])
                        );
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="description">
        <?php esc_html_e('Note: the legacy [afvdata_standings] and [afvdata_schedule] shortcodes still work as aliases.', 'footballdata'); ?>
    </p>

    <h3><?php esc_html_e('Attribute Reference', 'footballdata'); ?></h3>

    <h4><?php esc_html_e('Standings & Schedule', 'footballdata'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'footballdata'); ?></th>
                <th><?php esc_html_e('Values', 'footballdata'); ?></th>
                <th><?php esc_html_e('Description', 'footballdata'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>league</code></td>
                <td><?php esc_html_e('Slug or Liga Code', 'footballdata'); ?></td>
                <td><?php esc_html_e('Required. The league to display, e.g. league="herren".', 'footballdata'); ?></td>
            </tr>
            <tr>
                <td><code>group</code></td>
                <td><?php esc_html_e('e.g. "A", "B"', 'footballdata'); ?></td>
                <td><?php esc_html_e('Show only a specific group. Without this, all groups from the imported data are shown automatically.', 'footballdata'); ?></td>
            </tr>
            <tr>
                <td><code>highlight</code></td>
                <td><?php esc_html_e('Team name', 'footballdata'); ?></td>
                <td><?php esc_html_e('Override the team name to highlight. Defaults to the team name configured for this league.', 'footballdata'); ?></td>
            </tr>
            <tr>
                <td><code>class</code></td>
                <td><?php esc_html_e('CSS class name', 'footballdata'); ?></td>
                <td><?php esc_html_e('Add a custom CSS class to the wrapper element for styling.', 'footballdata'); ?></td>
            </tr>
        </tbody>
    </table>

    <h4><?php esc_html_e('Schedule only', 'footballdata'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'footballdata'); ?></th>
                <th><?php esc_html_e('Values', 'footballdata'); ?></th>
                <th><?php esc_html_e('Description', 'footballdata'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>home_only</code></td>
                <td><code>"1"</code></td>
                <td><?php esc_html_e('Show only home games of the configured team. Requires a team name set in the league config.', 'footballdata'); ?></td>
            </tr>
            <tr>
                <td><code>show</code></td>
                <td><code>"all"</code>, <code>"upcoming"</code>, <code>"past"</code></td>
                <td><?php esc_html_e('Filter by time. "upcoming" = today and future, "past" = before today. Default: "all".', 'footballdata'); ?></td>
            </tr>
            <tr>
                <td><code>limit</code></td>
                <td><?php esc_html_e('Number', 'footballdata'); ?></td>
                <td><?php esc_html_e('Maximum number of games to show. Useful with show="upcoming" to display the next N games.', 'footballdata'); ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>
