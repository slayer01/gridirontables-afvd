<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
defined('ABSPATH') || exit;
?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="gridirontables_afvd_save_leagues">
    <?php wp_nonce_field('gridirontables_afvd_save_leagues', 'gridirontables_afvd_nonce'); ?>

    <p class="description">
        <?php esc_html_e('Configure the leagues to import. The slug is used in shortcodes, e.g. [gridirontables_afvd_standings league="mensteam"]. The Liga Code is the league identifier from the XML API (e.g., "olm", "mu19ol").', 'gridirontables-afvd'); ?>
    </p>

    <table class="widefat gridirontables_afvd_leagues_table" id="gridirontables_afvd_leagues_table">
        <thead>
            <tr>
                <th><?php esc_html_e('Slug', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Label', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Liga Code', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Team Name', 'gridirontables-afvd'); ?></th>
                <th><?php esc_html_e('Active', 'gridirontables-afvd'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="gridirontables_afvd_leagues_body">
            <?php if (!empty($leagues)) : ?>
                <?php foreach ($leagues as $i => $league) : ?>
                    <tr class="gridirontables_afvd_league_row">
                        <td>
                            <input type="text" name="league_slug[]"
                                   value="<?php echo esc_attr($league['slug']); ?>"
                                   class="regular-text" required pattern="[a-z0-9\-]+"
                                   title="<?php esc_attr_e('Lowercase letters, numbers, and hyphens only', 'gridirontables-afvd'); ?>">
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
                                   placeholder="<?php esc_attr_e('e.g. Wetterau Bulls', 'gridirontables-afvd'); ?>">
                        </td>
                        <td>
                            <input type="checkbox" name="league_active[<?php echo (int) $i; ?>]"
                                   <?php checked(!empty($league['active'])); ?>>
                        </td>
                        <td>
                            <button type="button" class="button gridirontables_afvd_remove_league">
                                <?php esc_html_e('Remove', 'gridirontables-afvd'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p>
        <button type="button" class="button" id="gridirontables_afvd_add_league">
            <?php esc_html_e('+ Add League', 'gridirontables-afvd'); ?>
        </button>
    </p>

    <?php submit_button(__('Save Leagues', 'gridirontables-afvd')); ?>
</form>

<script type="text/html" id="tmpl-gridirontables_afvd_league_row">
    <tr class="gridirontables_afvd_league_row">
        <td><input type="text" name="league_slug[]" class="regular-text" required pattern="[a-z0-9\-]+" title="<?php esc_attr_e('Lowercase letters, numbers, and hyphens only', 'gridirontables-afvd'); ?>"></td>
        <td><input type="text" name="league_label[]" class="regular-text"></td>
        <td><input type="text" name="league_code[]" class="small-text" required></td>
        <td><input type="text" name="league_team_name[]" class="regular-text" placeholder="<?php esc_attr_e('e.g. Wetterau Bulls', 'gridirontables-afvd'); ?>"></td>
        <td><input type="checkbox" name="league_active[{{INDEX}}]" checked></td>
        <td><button type="button" class="button gridirontables_afvd_remove_league"><?php esc_html_e('Remove', 'gridirontables-afvd'); ?></button></td>
    </tr>
</script>
