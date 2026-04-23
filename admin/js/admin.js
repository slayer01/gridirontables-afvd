(function ($) {
    'use strict';

    $(function () {

        // Color pickers with theme palette swatches
        if ($.fn.wpColorPicker) {
            var palettes = typeof afvdataConfig !== 'undefined' && afvdataConfig.themePalette && afvdataConfig.themePalette.length
                ? afvdataConfig.themePalette
                : true;
            $('.afvdata-color-picker').wpColorPicker({ palettes: palettes });
        }

        // League management: add row
        $('#afvdata-add-league').on('click', function () {
            var tbody = $('#afvdata-leagues-body');
            var index = tbody.find('tr').length;
            var template = $('#tmpl-afvdata-league-row').html();
            template = template.replace(/\{\{INDEX\}\}/g, index);
            tbody.append(template);
        });

        // League management: remove row
        $(document).on('click', '.afvdata-remove-league', function () {
            $(this).closest('tr').remove();
        });

        // Import single league
        $(document).on('click', '.afvdata-import-league', function () {
            var btn = $(this);
            var liga = btn.data('liga');
            var statusCell = $('.afvdata-import-status[data-liga="' + liga + '"]');

            btn.prop('disabled', true);
            statusCell.removeClass('success error').text(afvdataConfig.i18n.importing);

            $.post(afvdataConfig.ajaxUrl, {
                action: 'afvdata_import',
                nonce: afvdataConfig.nonce,
                liga_code: liga
            })
            .done(function (response) {
                if (response.success) {
                    var data = response.data;
                    statusCell.addClass('success').text(
                        afvdataConfig.i18n.success + ' — ' +
                        data.standings_count + ' standings, ' +
                        data.schedule_count + ' schedule'
                    );
                    // Update counts in table
                    $('.afvdata-count-standings[data-liga="' + liga + '"]').text(data.standings_count);
                    $('.afvdata-count-schedule[data-liga="' + liga + '"]').text(data.schedule_count);
                } else {
                    statusCell.addClass('error').text(afvdataConfig.i18n.error + ': ' + response.data);
                }
            })
            .fail(function () {
                statusCell.addClass('error').text(afvdataConfig.i18n.error);
            })
            .always(function () {
                btn.prop('disabled', false);
            });
        });

        // Import all active leagues
        $('#afvdata-import-all').on('click', function () {
            if (!confirm(afvdataConfig.i18n.confirm)) {
                return;
            }

            var btn = $(this);
            var statusSpan = $('#afvdata-import-all-status');

            btn.prop('disabled', true);
            $('.afvdata-import-league').prop('disabled', true);
            statusSpan.text(afvdataConfig.i18n.importing);

            $.post(afvdataConfig.ajaxUrl, {
                action: 'afvdata_import_all',
                nonce: afvdataConfig.nonce
            })
            .done(function (response) {
                if (response.success) {
                    var results = response.data;
                    var ok = 0;
                    var fail = 0;

                    $.each(results, function (liga, result) {
                        var statusCell = $('.afvdata-import-status[data-liga="' + liga + '"]');
                        if (result.error) {
                            fail++;
                            statusCell.addClass('error').text(afvdataConfig.i18n.error + ': ' + result.error);
                        } else {
                            ok++;
                            statusCell.addClass('success').text(
                                afvdataConfig.i18n.success + ' — ' +
                                result.standings_count + ' standings, ' +
                                result.schedule_count + ' schedule'
                            );
                            $('.afvdata-count-standings[data-liga="' + liga + '"]').text(result.standings_count);
                            $('.afvdata-count-schedule[data-liga="' + liga + '"]').text(result.schedule_count);
                        }
                    });

                    statusSpan.text(ok + ' OK, ' + fail + ' failed');
                } else {
                    statusSpan.text(afvdataConfig.i18n.error);
                }
            })
            .fail(function () {
                statusSpan.text(afvdataConfig.i18n.error);
            })
            .always(function () {
                btn.prop('disabled', false);
                $('.afvdata-import-league').prop('disabled', false);
            });
        });

        // View raw data
        $(document).on('click', '.afvdata-view-raw', function () {
            var btn = $(this);
            var liga = btn.data('liga');
            var type = btn.data('type');
            var wrap = $('#afvdata-raw-data-wrap');
            var title = $('#afvdata-raw-data-title');
            var content = $('#afvdata-raw-data-content');

            btn.prop('disabled', true);
            content.html('<em>' + afvdataConfig.i18n.importing + '</em>');
            title.text(liga + ' — ' + type);
            wrap.show();

            $.post(afvdataConfig.ajaxUrl, {
                action: 'afvdata_raw_data',
                nonce: afvdataConfig.nonce,
                liga_code: liga,
                type: type
            })
            .done(function (response) {
                if (!response.success || !response.data.rows || !response.data.rows.length) {
                    content.html('<p>' + afvdataConfig.i18n.error + '</p>');
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
                content.html('<p>' + afvdataConfig.i18n.error + '</p>');
            })
            .always(function () {
                btn.prop('disabled', false);
            });
        });

    });

})(jQuery);
