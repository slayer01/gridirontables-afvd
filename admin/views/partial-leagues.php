<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
defined('ABSPATH') || exit;
?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="afvdata_save_leagues">
    <?php wp_nonce_field('afvdata_save_leagues', 'afvdata_nonce'); ?>

    <p class="description">
        <?php esc_html_e('Configure the leagues to import. The slug is used in shortcodes, e.g. [afvdata_standings league="mensteam"]. The Liga Code is the AFVD identifier (e.g., "olm", "mu19ol").', 'afvdata'); ?>
    </p>

    <table class="widefat afvdata-leagues-table" id="afvdata-leagues-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Slug', 'afvdata'); ?></th>
                <th><?php esc_html_e('Label', 'afvdata'); ?></th>
                <th><?php esc_html_e('Liga Code', 'afvdata'); ?></th>
                <th><?php esc_html_e('Team Name', 'afvdata'); ?></th>
                <th><?php esc_html_e('Active', 'afvdata'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="afvdata-leagues-body">
            <?php if (!empty($leagues)) : ?>
                <?php foreach ($leagues as $i => $league) : ?>
                    <tr class="afvdata-league-row">
                        <td>
                            <input type="text" name="league_slug[]"
                                   value="<?php echo esc_attr($league['slug']); ?>"
                                   class="regular-text" required pattern="[a-z0-9\-]+"
                                   title="<?php esc_attr_e('Lowercase letters, numbers, and hyphens only', 'afvdata'); ?>">
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
                                   placeholder="<?php esc_attr_e('e.g. Wetterau Bulls', 'afvdata'); ?>">
                        </td>
                        <td>
                            <input type="checkbox" name="league_active[<?php echo (int) $i; ?>]"
                                   <?php checked(!empty($league['active'])); ?>>
                        </td>
                        <td>
                            <button type="button" class="button afvdata-remove-league">
                                <?php esc_html_e('Remove', 'afvdata'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p>
        <button type="button" class="button" id="afvdata-add-league">
            <?php esc_html_e('+ Add League', 'afvdata'); ?>
        </button>
    </p>

    <?php submit_button(__('Save Leagues', 'afvdata')); ?>
</form>

<script type="text/html" id="tmpl-afvdata-league-row">
    <tr class="afvdata-league-row">
        <td><input type="text" name="league_slug[]" class="regular-text" required pattern="[a-z0-9\-]+" title="<?php esc_attr_e('Lowercase letters, numbers, and hyphens only', 'afvdata'); ?>"></td>
        <td><input type="text" name="league_label[]" class="regular-text"></td>
        <td><input type="text" name="league_code[]" class="small-text" required></td>
        <td><input type="text" name="league_team_name[]" class="regular-text" placeholder="<?php esc_attr_e('e.g. Wetterau Bulls', 'afvdata'); ?>"></td>
        <td><input type="checkbox" name="league_active[{{INDEX}}]" checked></td>
        <td><button type="button" class="button afvdata-remove-league"><?php esc_html_e('Remove', 'afvdata'); ?></button></td>
    </tr>
</script>
