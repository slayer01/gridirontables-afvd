<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
defined('ABSPATH') || exit;
?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="dsfooboo_football_data_save_leagues">
    <?php wp_nonce_field('dsfooboo_football_data_save_leagues', 'dsfooboo_football_data_nonce'); ?>

    <p class="description">
        <?php esc_html_e('Configure the leagues to import. The slug is used in shortcodes, e.g. [dsfooboo_football_data_standings league="mensteam"]. The Liga Code is the league identifier from the XML API (e.g., "olm", "mu19ol").', 'dsfooboo_football_data'); ?>
    </p>

    <table class="widefat dsfooboo_football_data_leagues_table" id="dsfooboo_football_data_leagues_table">
        <thead>
            <tr>
                <th><?php esc_html_e('Slug', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Label', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Liga Code', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Team Name', 'dsfooboo_football_data'); ?></th>
                <th><?php esc_html_e('Active', 'dsfooboo_football_data'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="dsfooboo_football_data_leagues_body">
            <?php if (!empty($leagues)) : ?>
                <?php foreach ($leagues as $i => $league) : ?>
                    <tr class="dsfooboo_football_data_league_row">
                        <td>
                            <input type="text" name="league_slug[]"
                                   value="<?php echo esc_attr($league['slug']); ?>"
                                   class="regular-text" required pattern="[a-z0-9\-]+"
                                   title="<?php esc_attr_e('Lowercase letters, numbers, and hyphens only', 'dsfooboo_football_data'); ?>">
                        </td>
                        <td>
                            <input type="text" name="league_label[]"
                                   value="<?php echo esc_attr($league['label']); ?>"
                                   class="regular-text">
                        </td>
                        <td>
                            <input type="text" name="league_code[]"
                                   value="<?php echo esc_attr($league['liga_code']); ?>"
                                   class="small-text" required>
                        </td>
                        <td>
                            <input type="text" name="league_team_name[]"
                                   value="<?php echo esc_attr($league['team_name'] ?? ''); ?>"
                                   class="regular-text"
                                   placeholder="<?php esc_attr_e('e.g. Wetterau Bulls', 'dsfooboo_football_data'); ?>">
                        </td>
                        <td>
                            <input type="checkbox" name="league_active[<?php echo (int) $i; ?>]"
                                   <?php checked(!empty($league['active'])); ?>>
                        </td>
                        <td>
                            <button type="button" class="button dsfooboo_football_data_remove_league">
                                <?php esc_html_e('Remove', 'dsfooboo_football_data'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p>
        <button type="button" class="button" id="dsfooboo_football_data_add_league">
            <?php esc_html_e('+ Add League', 'dsfooboo_football_data'); ?>
        </button>
    </p>

    <?php submit_button(__('Save Leagues', 'dsfooboo_football_data')); ?>
</form>

<script type="text/html" id="tmpl-dsfooboo_football_data_league_row">
    <tr class="dsfooboo_football_data_league_row">
        <td><input type="text" name="league_slug[]" class="regular-text" required pattern="[a-z0-9\-]+" title="<?php esc_attr_e('Lowercase letters, numbers, and hyphens only', 'dsfooboo_football_data'); ?>"></td>
        <td><input type="text" name="league_label[]" class="regular-text"></td>
        <td><input type="text" name="league_code[]" class="small-text" required></td>
        <td><input type="text" name="league_team_name[]" class="regular-text" placeholder="<?php esc_attr_e('e.g. Wetterau Bulls', 'dsfooboo_football_data'); ?>"></td>
        <td><input type="checkbox" name="league_active[{{INDEX}}]" checked></td>
        <td><button type="button" class="button dsfooboo_football_data_remove_league"><?php esc_html_e('Remove', 'dsfooboo_football_data'); ?></button></td>
    </tr>
</script>
