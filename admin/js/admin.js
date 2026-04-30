(function ($) {
    'use strict';

    $(function () {

        // Color pickers with theme palette swatches
        if ($.fn.wpColorPicker) {
            var palettes = typeof footballdataConfig !== 'undefined' && footballdataConfig.themePalette && footballdataConfig.themePalette.length
                ? footballdataConfig.themePalette
                : true;
            $('.footballdata-color-picker').wpColorPicker({ palettes: palettes });
        }

        // League management: add row
        $('#footballdata-add-league').on('click', function () {
            var tbody = $('#footballdata-leagues-body');
            var index = tbody.find('tr').length;
            var template = $('#tmpl-footballdata-league-row').html();
            template = template.replace(/\{\{INDEX\}\}/g, index);
            tbody.append(template);
        });

        // League management: remove row
        $(document).on('click', '.footballdata-remove-league', function () {
            $(this).closest('tr').remove();
        });

        // Import single league
        $(document).on('click', '.footballdata-import-league', function () {
            var btn = $(this);
            var liga = btn.data('liga');
            var statusCell = $('.footballdata-import-status[data-liga="' + liga + '"]');

            btn.prop('disabled', true);
            statusCell.removeClass('success error').text(footballdataConfig.i18n.importing);

            $.post(footballdataConfig.ajaxUrl, {
                action: 'footballdata_import',
                nonce: footballdataConfig.nonce,
                liga_code: liga
            })
            .done(function (response) {
                if (response.success) {
                    var data = response.data;
                    statusCell.addClass('success').text(
                        footballdataConfig.i18n.success + ' — ' +
                        data.standings_count + ' standings, ' +
                        data.schedule_count + ' schedule'
                    );
                    // Update counts in table
                    $('.footballdata-count-standings[data-liga="' + liga + '"]').text(data.standings_count);
                    $('.footballdata-count-schedule[data-liga="' + liga + '"]').text(data.schedule_count);
                } else {
                    statusCell.addClass('error').text(footballdataConfig.i18n.error + ': ' + response.data);
                }
            })
            .fail(function () {
                statusCell.addClass('error').text(footballdataConfig.i18n.error);
            })
            .always(function () {
                btn.prop('disabled', false);
            });
        });

        // Import all active leagues
        $('#footballdata-import-all').on('click', function () {
            if (!confirm(footballdataConfig.i18n.confirm)) {
                return;
            }

            var btn = $(this);
            var statusSpan = $('#footballdata-import-all-status');

            btn.prop('disabled', true);
            $('.footballdata-import-league').prop('disabled', true);
            statusSpan.text(footballdataConfig.i18n.importing);

            $.post(footballdataConfig.ajaxUrl, {
                action: 'footballdata_import_all',
                nonce: footballdataConfig.nonce
            })
            .done(function (response) {
                if (response.success) {
                    var results = response.data;
                    var ok = 0;
                    var fail = 0;

                    $.each(results, function (liga, result) {
                        var statusCell = $('.footballdata-import-status[data-liga="' + liga + '"]');
                        if (result.error) {
                            fail++;
                            statusCell.addClass('error').text(footballdataConfig.i18n.error + ': ' + result.error);
                        } else {
                            ok++;
                            statusCell.addClass('success').text(
                                footballdataConfig.i18n.success + ' — ' +
                                result.standings_count + ' standings, ' +
                                result.schedule_count + ' schedule'
                            );
                            $('.footballdata-count-standings[data-liga="' + liga + '"]').text(result.standings_count);
                            $('.footballdata-count-schedule[data-liga="' + liga + '"]').text(result.schedule_count);
                        }
                    });

                    statusSpan.text(ok + ' OK, ' + fail + ' failed');
                } else {
                    statusSpan.text(footballdataConfig.i18n.error);
                }
            })
            .fail(function () {
                statusSpan.text(footballdataConfig.i18n.error);
            })
            .always(function () {
                btn.prop('disabled', false);
                $('.footballdata-import-league').prop('disabled', false);
            });
        });

        // View raw data
        $(document).on('click', '.footballdata-view-raw', function () {
            var btn = $(this);
            var liga = btn.data('liga');
            var type = btn.data('type');
            var wrap = $('#footballdata-raw-data-wrap');
            var title = $('#footballdata-raw-data-title');
            var content = $('#footballdata-raw-data-content');

            btn.prop('disabled', true);
            content.html('<em>' + footballdataConfig.i18n.importing + '</em>');
            title.text(liga + ' — ' + type);
            wrap.show();

            $.post(footballdataConfig.ajaxUrl, {
                action: 'footballdata_raw_data',
                nonce: footballdataConfig.nonce,
                liga_code: liga,
                type: type
            })
            .done(function (response) {
                if (!response.success || !response.data.rows || !response.data.rows.length) {
                    content.html('<p>' + footballdataConfig.i18n.error + '</p>');
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
                content.html('<p>' + footballdataConfig.i18n.error + '</p>');
            })
            .always(function () {
                btn.prop('disabled', false);
            });
        });

    });

})(jQuery);
