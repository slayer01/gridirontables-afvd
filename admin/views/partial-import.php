<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
defined('ABSPATH') || exit;
?>
<h2><?php esc_html_e('Import Status', 'gridirontables-afvd'); ?></h2>

<?php if ($last_sync) : ?>
    <p>
        <?php
        printf(
            /* translators: %s: date/time string */
            esc_html__('Last full sync: %s', 'gridirontables-afvd'),
            esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $last_sync))
        );
        ?>
    </p>
<?php else : ?>
    <p><?php esc_html_e('No import has been run yet.', 'gridirontables-afvd'); ?></p>
<?php endif; ?>

<?php if (empty($leagues)) : ?>
    <p><?php esc_html_e('No leagues configured. Go to the Leagues tab to add some.', 'gridirontables-afvd'); ?></p>
<?php else : ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('League', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Liga Code', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Saison', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Active', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Standings Rows', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Schedule Rows', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Raw Data', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Action', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Status', 'gridirontables-afvd'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leagues as $league) :
                $row_saison = $league['saison'] ?? '';
                $counts = Gridirontables_AFVD_DB::get_counts($league['liga_code'], $row_saison);
            ?>
                <tr>
                    <td><?php echo esc_html($league['label'] ?: $league['slug']); ?> <span style="color:#888;">(<?php echo esc_html($league['slug']); ?>)</span></td>
                    <td><code><?php echo esc_html($league['liga_code']); ?></code></td>
                    <td><?php echo '' === $row_saison ? '<em style="color:#888;">' . esc_html__('current', 'gridirontables-afvd') . '</em>' : esc_html($row_saison); ?></td>
                    <td><?php echo !empty($league['active']) ? '&#10003;' : '&#10007;'; ?></td>
                    <td class="gridirontables_afvd_count_standings" data-slug="<?php echo esc_attr($league['slug']); ?>">
                        <?php echo (int) $counts['standings']; ?>
                    </td>
                    <td class="gridirontables_afvd_count_schedule" data-slug="<?php echo esc_attr($league['slug']); ?>">
                        <?php echo (int) $counts['schedule']; ?>
                    </td>
                    <td>
                        <button type="button" class="button button-small gridirontables_afvd_view_raw"
                                data-slug="<?php echo esc_attr($league['slug']); ?>" data-type="standings">
                            <?php esc_html_e('Standings', 'gridirontables-afvd'); ?>
                        </button>
                        <button type="button" class="button button-small gridirontables_afvd_view_raw"
                                data-slug="<?php echo esc_attr($league['slug']); ?>" data-type="schedule">
                            <?php esc_html_e('Schedule', 'gridirontables-afvd'); ?>
                        </button>
                    </td>
                    <td>
                        <button type="button" class="button gridirontables_afvd_import_league"
                                data-slug="<?php echo esc_attr($league['slug']); ?>">
                            <?php esc_html_e('Import', 'gridirontables-afvd'); ?>
                        </button>
                    </td>
                    <td class="gridirontables_afvd_import_status" data-slug="<?php echo esc_attr($league['slug']); ?>">
                        &mdash;
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 15px;">
        <button type="button" class="button button-primary" id="gridirontables_afvd_import_all">
            <?php esc_html_e('Import All Active Leagues', 'gridirontables-afvd'); ?>
        </button>
        <span id="gridirontables_afvd_import_all_status"></span>
    </p>

    <div id="gridirontables_afvd_raw_data_wrap" style="display:none; margin-top:20px;">
        <h3 id="gridirontables_afvd_raw_data_title"></h3>
        <div id="gridirontables_afvd_raw_data_content" style="overflow-x:auto;"></div>
    </div>

    <p class="description" style="margin-top:15px;">
        <?php esc_html_e('Tip: to display archive data alongside current data, create separate league entries with the same Liga Code but different Saison values (e.g. one with Saison empty for the current year and another with Saison "2025" for the archive). Each entry needs its own unique slug.', 'gridirontables-afvd'); ?>
    </p>

    <h3><?php esc_html_e('Shortcode Reference', 'gridirontables-afvd'); ?></h3>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Shortcode', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Description', 'gridirontables-afvd'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leagues as $league) : ?>
                <tr>
                    <td><code>[gridirontables_afvd_standings league="<?php echo esc_attr($league['slug']); ?>"]</code></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %s: league label */
                            esc_html__('Standings table for %s', 'gridirontables-afvd'),
                            esc_html($league['label'] ?: $league['slug'])
                        );
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><code>[gridirontables_afvd_schedule league="<?php echo esc_attr($league['slug']); ?>"]</code></td>
                    <td>
                        <?php
                        printf(
                            /* translators: %s: league label */
                            esc_html__('Game schedule for %s', 'gridirontables-afvd'),
                            esc_html($league['label'] ?: $league['slug'])
                        );
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3><?php esc_html_e('Attribute Reference', 'gridirontables-afvd'); ?></h3>

    <h4><?php esc_html_e('Standings & Schedule', 'gridirontables-afvd'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Values', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Description', 'gridirontables-afvd'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>league</code></td>
                <td><?php esc_html_e('Slug or Liga Code', 'gridirontables-afvd'); ?></td>
                <td><?php esc_html_e('Required. The league to display, e.g. league="herren".', 'gridirontables-afvd'); ?></td>
            </tr>
            <tr>
                <td><code>group</code></td>
                <td><?php esc_html_e('e.g. "A", "B"', 'gridirontables-afvd'); ?></td>
                <td><?php esc_html_e('Show only a specific group. Without this, all groups from the imported data are shown automatically.', 'gridirontables-afvd'); ?></td>
            </tr>
            <tr>
                <td><code>highlight</code></td>
                <td><?php esc_html_e('Team name', 'gridirontables-afvd'); ?></td>
                <td><?php esc_html_e('Override the team name to highlight. Defaults to the team name configured for this league.', 'gridirontables-afvd'); ?></td>
            </tr>
            <tr>
                <td><code>class</code></td>
                <td><?php esc_html_e('CSS class name', 'gridirontables-afvd'); ?></td>
                <td><?php esc_html_e('Add a custom CSS class to the wrapper element for styling.', 'gridirontables-afvd'); ?></td>
            </tr>
            <tr>
                <td><code>saison</code></td>
                <td><?php esc_html_e('Year, e.g. "2026"', 'gridirontables-afvd'); ?></td>
                <td><?php esc_html_e('Override the season label shown in the heading. Defaults to the season configured for this league.', 'gridirontables-afvd'); ?></td>
            </tr>
        </tbody>
    </table>

    <h4><?php esc_html_e('Standings only', 'gridirontables-afvd'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Values', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Description', 'gridirontables-afvd'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>format</code></td>
                <td><code>"wins"</code>, <code>"points"</code></td>
                <td><?php esc_html_e('"wins" (default) shows W-L (Quotient) and Home/Away records per BSO. "points" shows the legacy P+ / P- / TD+ / TD- layout for archive seasons. Overrides the league setting.', 'gridirontables-afvd'); ?></td>
            </tr>
        </tbody>
    </table>

    <h4><?php esc_html_e('Schedule only', 'gridirontables-afvd'); ?></h4>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Attribute', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Values', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Description', 'gridirontables-afvd'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>home_only</code></td>
                <td><code>"1"</code></td>
                <td><?php esc_html_e('Show only home games of the configured team. Requires a team name set in the league config.', 'gridirontables-afvd'); ?></td>
            </tr>
            <tr>
                <td><code>show</code></td>
                <td><code>"all"</code>, <code>"upcoming"</code>, <code>"past"</code></td>
                <td><?php esc_html_e('Filter by time. "upcoming" = today and future, "past" = before today. Default: "all".', 'gridirontables-afvd'); ?></td>
            </tr>
            <tr>
                <td><code>limit</code></td>
                <td><?php esc_html_e('Number', 'gridirontables-afvd'); ?></td>
                <td><?php esc_html_e('Maximum number of games to show. Useful with show="upcoming" to display the next N games.', 'gridirontables-afvd'); ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>
