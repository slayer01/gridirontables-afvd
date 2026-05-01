<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- variables scoped to included view
defined('ABSPATH') || exit;
?>
<div style="max-width:700px;">

    <div style="text-align:center;margin:30px 0;">
        <img src="<?php echo esc_url(GRIDIRONTABLES_AFVD_PLUGIN_URL . 'admin/img/logo.png'); ?>" alt="Gridirontables AFVD" style="height:128px;">
        <h2 style="margin-top:12px;">Gridirontables AFVD &ndash; League tables &amp; schedules - data provided by AFVD</h2>
        <p style="color:#666;">Version <?php echo esc_html(GRIDIRONTABLES_AFVD_VERSION); ?></p>
    </div>

    <hr>

    <h3><?php esc_html_e('Disclaimer', 'gridirontables-afvd'); ?></h3>
    <p>
        <?php esc_html_e('This plugin is an independent project and is not affiliated with, endorsed by, or in any way officially connected to the AFVD (American Football Verband Deutschland) or any of its member associations.', 'gridirontables-afvd'); ?>
    </p>
    <p>
        <?php
        printf(
            /* translators: %s: URL to the data source */
            esc_html__('It uses the publicly accessible XML data export provided at %s to display league standings and game schedules.', 'gridirontables-afvd'),
            '<code>vereine.football-verband.de</code>'
        );
        ?>
    </p>

    <hr>

    <h3><?php esc_html_e('Contact', 'gridirontables-afvd'); ?></h3>
    <table class="form-table">
        <tr>
            <th scope="row"><?php esc_html_e('Developer', 'gridirontables-afvd'); ?></th>
            <td>Daniel Schmidt-Richert</td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Email', 'gridirontables-afvd'); ?></th>
            <td><a href="mailto:afvdata@foo.boo">afvdata@foo.boo</a></td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Source Code', 'gridirontables-afvd'); ?></th>
            <td><a href="https://github.com/slayer01/gridirontables-afvd" target="_blank" rel="noopener">github.com/slayer01/gridirontables-afvd</a></td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('License', 'gridirontables-afvd'); ?></th>
            <td>GPL v2 or later</td>
        </tr>
    </table>

</div>
