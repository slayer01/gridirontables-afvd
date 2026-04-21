<?php
defined('ABSPATH') || exit;
?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="afvd_save_leagues">
    <?php wp_nonce_field('afvd_save_leagues', 'afvd_nonce'); ?>

    <p class="description">
        <?php esc_html_e('Configure the leagues to import. The slug is used in shortcodes, e.g. [afvd_standings league="mensteam"]. The Liga Code is the AFVD identifier (e.g., "olm", "mu19ol").', 'afvd-data'); ?>
    </p>

    <table class="widefat afvd-leagues-table" id="afvd-leagues-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Slug', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Label', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Liga Code', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Groups', 'afvd-data'); ?></th>
                <th><?php esc_html_e('Active', 'afvd-data'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="afvd-leagues-body">
            <?php if (!empty($leagues)) : ?>
                <?php foreach ($leagues as $i => $league) : ?>
                    <tr class="afvd-league-row">
                        <td>
                            <input type="text" name="league_slug[]"
                                   value="<?php echo esc_attr($league['slug']); ?>"
                                   class="regular-text" required pattern="[a-z0-9\-]+"
                                   title="<?php esc_attr_e('Lowercase letters, numbers, and hyphens only', 'afvd-data'); ?>">
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
                            <input type="text" name="league_groups[]"
                                   value="<?php echo esc_attr($league['groups']); ?>"
                                   class="small-text" placeholder="A,B,C">
                        </td>
                        <td>
                            <input type="checkbox" name="league_active[<?php echo (int) $i; ?>]"
                                   <?php checked(!empty($league['active'])); ?>>
                        </td>
                        <td>
                            <button type="button" class="button afvd-remove-league">
                                <?php esc_html_e('Remove', 'afvd-data'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p>
        <button type="button" class="button" id="afvd-add-league">
            <?php esc_html_e('+ Add League', 'afvd-data'); ?>
        </button>
    </p>

    <?php submit_button(__('Save Leagues', 'afvd-data')); ?>
</form>

<script type="text/html" id="tmpl-afvd-league-row">
    <tr class="afvd-league-row">
        <td><input type="text" name="league_slug[]" class="regular-text" required pattern="[a-z0-9\-]+" title="<?php esc_attr_e('Lowercase letters, numbers, and hyphens only', 'afvd-data'); ?>"></td>
        <td><input type="text" name="league_label[]" class="regular-text"></td>
        <td><input type="text" name="league_code[]" class="small-text" required></td>
        <td><input type="text" name="league_groups[]" class="small-text" placeholder="A,B,C"></td>
        <td><input type="checkbox" name="league_active[{{INDEX}}]" checked></td>
        <td><button type="button" class="button afvd-remove-league"><?php esc_html_e('Remove', 'afvd-data'); ?></button></td>
    </tr>
</script>
