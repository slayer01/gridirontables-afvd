<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
defined('ABSPATH') || exit;
?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="footballdata_save_leagues">
    <?php wp_nonce_field('footballdata_save_leagues', 'footballdata_nonce'); ?>

    <p class="description">
        <?php esc_html_e('Configure the leagues to import. The slug is used in shortcodes, e.g. [footballdata_standings league="mensteam"]. The Liga Code is the league identifier from the XML API (e.g., "olm", "mu19ol").', 'footballdata'); ?>
    </p>

    <table class="widefat footballdata-leagues-table" id="footballdata-leagues-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Slug', 'footballdata'); ?></th>
                <th><?php esc_html_e('Label', 'footballdata'); ?></th>
                <th><?php esc_html_e('Liga Code', 'footballdata'); ?></th>
                <th><?php esc_html_e('Team Name', 'footballdata'); ?></th>
                <th><?php esc_html_e('Active', 'footballdata'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="footballdata-leagues-body">
            <?php if (!empty($leagues)) : ?>
                <?php foreach ($leagues as $i => $league) : ?>
                    <tr class="footballdata-league-row">
                        <td>
                            <input type="text" name="league_slug[]"
                                   value="<?php echo esc_attr($league['slug']); ?>"
                                   class="regular-text" required pattern="[a-z0-9\-]+"
                                   title="<?php esc_attr_e('Lowercase letters, numbers, and hyphens only', 'footballdata'); ?>">
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
                                   placeholder="<?php esc_attr_e('e.g. Wetterau Bulls', 'footballdata'); ?>">
                        </td>
                        <td>
                            <input type="checkbox" name="league_active[<?php echo (int) $i; ?>]"
                                   <?php checked(!empty($league['active'])); ?>>
                        </td>
                        <td>
                            <button type="button" class="button footballdata-remove-league">
                                <?php esc_html_e('Remove', 'footballdata'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p>
        <button type="button" class="button" id="footballdata-add-league">
            <?php esc_html_e('+ Add League', 'footballdata'); ?>
        </button>
    </p>

    <?php submit_button(__('Save Leagues', 'footballdata')); ?>
</form>

<script type="text/html" id="tmpl-footballdata-league-row">
    <tr class="footballdata-league-row">
        <td><input type="text" name="league_slug[]" class="regular-text" required pattern="[a-z0-9\-]+" title="<?php esc_attr_e('Lowercase letters, numbers, and hyphens only', 'footballdata'); ?>"></td>
        <td><input type="text" name="league_label[]" class="regular-text"></td>
        <td><input type="text" name="league_code[]" class="small-text" required></td>
        <td><input type="text" name="league_team_name[]" class="regular-text" placeholder="<?php esc_attr_e('e.g. Wetterau Bulls', 'footballdata'); ?>"></td>
        <td><input type="checkbox" name="league_active[{{INDEX}}]" checked></td>
        <td><button type="button" class="button footballdata-remove-league"><?php esc_html_e('Remove', 'footballdata'); ?></button></td>
    </tr>
</script>
