(function ($) {
    'use strict';

    $(function () {

        var cfg = window.gridirontables_afvd_config;

        if ($.fn.wpColorPicker) {
            var palettes = cfg && cfg.themePalette && cfg.themePalette.length ? cfg.themePalette : true;
            $('.gridirontables_afvd_color_picker').wpColorPicker({ palettes: palettes });
        }

        $('#gridirontables_afvd_add_league').on('click', function () {
            var tbody = $('#gridirontables_afvd_leagues_body');
            var index = tbody.find('tr').length;
            var template = $('#tmpl-gridirontables_afvd_league_row').html();
            template = template.replace(/\{\{INDEX\}\}/g, index);
            tbody.append(template);
        });

        $(document).on('click', '.gridirontables_afvd_remove_league', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('click', '.gridirontables_afvd_import_league', function () {
            var btn = $(this);
            var slug = btn.data('slug');
            var statusCell = $('.gridirontables_afvd_import_status[data-slug="' + slug + '"]');

            btn.prop('disabled', true);
            statusCell.removeClass('success error').text(cfg.i18n.importing);

            $.post(cfg.ajaxUrl, {
                action: 'gridirontables_afvd_import',
                nonce: cfg.nonce,
                slug: slug
            })
            .done(function (response) {
                if (response.success) {
                    var data = response.data;
                    statusCell.addClass('success').text(
                        cfg.i18n.success + ' — ' +
                        data.standings_count + ' standings, ' +
                        data.schedule_count + ' schedule'
                    );
                    $('.gridirontables_afvd_count_standings[data-slug="' + slug + '"]').text(data.standings_count);
                    $('.gridirontables_afvd_count_schedule[data-slug="' + slug + '"]').text(data.schedule_count);
                } else {
                    statusCell.addClass('error').text(cfg.i18n.error + ': ' + response.data);
                }
            })
            .fail(function () {
                statusCell.addClass('error').text(cfg.i18n.error);
            })
            .always(function () {
                btn.prop('disabled', false);
            });
        });

        $('#gridirontables_afvd_import_all').on('click', function () {
            if (!confirm(cfg.i18n.confirm)) {
                return;
            }

            var btn = $(this);
            var statusSpan = $('#gridirontables_afvd_import_all_status');

            btn.prop('disabled', true);
            $('.gridirontables_afvd_import_league').prop('disabled', true);
            statusSpan.text(cfg.i18n.importing);

            $.post(cfg.ajaxUrl, {
                action: 'gridirontables_afvd_import_all',
                nonce: cfg.nonce
            })
            .done(function (response) {
                if (response.success) {
                    var results = response.data;
                    var ok = 0;
                    var fail = 0;

                    $.each(results, function (slug, result) {
                        var statusCell = $('.gridirontables_afvd_import_status[data-slug="' + slug + '"]');
                        if (result.error) {
                            fail++;
                            statusCell.addClass('error').text(cfg.i18n.error + ': ' + result.error);
                        } else {
                            ok++;
                            statusCell.addClass('success').text(
                                cfg.i18n.success + ' — ' +
                                result.standings_count + ' standings, ' +
                                result.schedule_count + ' schedule'
                            );
                            $('.gridirontables_afvd_count_standings[data-slug="' + slug + '"]').text(result.standings_count);
                            $('.gridirontables_afvd_count_schedule[data-slug="' + slug + '"]').text(result.schedule_count);
                        }
                    });

                    statusSpan.text(ok + ' OK, ' + fail + ' failed');
                } else {
                    statusSpan.text(cfg.i18n.error);
                }
            })
            .fail(function () {
                statusSpan.text(cfg.i18n.error);
            })
            .always(function () {
                btn.prop('disabled', false);
                $('.gridirontables_afvd_import_league').prop('disabled', false);
            });
        });

        $(document).on('click', '.gridirontables_afvd_view_raw', function () {
            var btn = $(this);
            var slug = btn.data('slug');
            var type = btn.data('type');
            var wrap = $('#gridirontables_afvd_raw_data_wrap');
            var title = $('#gridirontables_afvd_raw_data_title');
            var content = $('#gridirontables_afvd_raw_data_content');

            btn.prop('disabled', true);
            content.html('<em>' + cfg.i18n.importing + '</em>');
            title.text(slug + ' — ' + type);
            wrap.show();

            $.post(cfg.ajaxUrl, {
                action: 'gridirontables_afvd_raw_data',
                nonce: cfg.nonce,
                slug: slug,
                type: type
            })
            .done(function (response) {
                if (!response.success || !response.data.rows || !response.data.rows.length) {
                    content.html('<p>' + cfg.i18n.error + '</p>');
                    return;
                }

                var rows = response.data.rows;
                var keys = Object.keys(rows[0]);
                var html = '<table class="widefat striped"><thead><tr>';
                keys.forEach(function (k) { html += '<th>' + k + '</th>'; });
                html += '</tr></thead><tbody>';
                rows.forEach(function (row) {
                    html += '<tr>';
                    keys.forEach(function (k) {
                        html += '<td>' + (row[k] !== null ? row[k] : '') + '</td>';
                    });
                    html += '</tr>';
                });
                html += '</tbody></table>';
                content.html(html);
            })
            .fail(function () {
                content.html('<p>' + cfg.i18n.error + '</p>');
            })
            .always(function () {
                btn.prop('disabled', false);
            });
        });

    });

})(jQuery);
