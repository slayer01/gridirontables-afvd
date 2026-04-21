(function ($) {
    'use strict';

    // Color pickers
    $('.afvd-color-picker').wpColorPicker();

    // League management: add row
    $('#afvd-add-league').on('click', function () {
        var tbody = $('#afvd-leagues-body');
        var index = tbody.find('tr').length;
        var template = $('#tmpl-afvd-league-row').html();
        template = template.replace(/\{\{INDEX\}\}/g, index);
        tbody.append(template);
    });

    // League management: remove row
    $(document).on('click', '.afvd-remove-league', function () {
        $(this).closest('tr').remove();
    });

    // Import single league
    $(document).on('click', '.afvd-import-league', function () {
        var btn = $(this);
        var liga = btn.data('liga');
        var statusCell = $('.afvd-import-status[data-liga="' + liga + '"]');

        btn.prop('disabled', true);
        statusCell.removeClass('success error').text(afvdData.i18n.importing);

        $.post(afvdData.ajaxUrl, {
            action: 'afvd_import',
            nonce: afvdData.nonce,
            liga_code: liga
        })
        .done(function (response) {
            if (response.success) {
                var data = response.data;
                statusCell.addClass('success').text(
                    afvdData.i18n.success + ' — ' +
                    data.standings_count + ' standings, ' +
                    data.schedule_count + ' schedule'
                );
                // Update counts in table
                $('.afvd-count-standings[data-liga="' + liga + '"]').text(data.standings_count);
                $('.afvd-count-schedule[data-liga="' + liga + '"]').text(data.schedule_count);
            } else {
                statusCell.addClass('error').text(afvdData.i18n.error + ': ' + response.data);
            }
        })
        .fail(function () {
            statusCell.addClass('error').text(afvdData.i18n.error);
        })
        .always(function () {
            btn.prop('disabled', false);
        });
    });

    // Import all active leagues
    $('#afvd-import-all').on('click', function () {
        if (!confirm(afvdData.i18n.confirm)) {
            return;
        }

        var btn = $(this);
        var statusSpan = $('#afvd-import-all-status');

        btn.prop('disabled', true);
        $('.afvd-import-league').prop('disabled', true);
        statusSpan.text(afvdData.i18n.importing);

        $.post(afvdData.ajaxUrl, {
            action: 'afvd_import_all',
            nonce: afvdData.nonce
        })
        .done(function (response) {
            if (response.success) {
                var results = response.data;
                var ok = 0;
                var fail = 0;

                $.each(results, function (liga, result) {
                    var statusCell = $('.afvd-import-status[data-liga="' + liga + '"]');
                    if (result.error) {
                        fail++;
                        statusCell.addClass('error').text(afvdData.i18n.error + ': ' + result.error);
                    } else {
                        ok++;
                        statusCell.addClass('success').text(
                            afvdData.i18n.success + ' — ' +
                            result.standings_count + ' standings, ' +
                            result.schedule_count + ' schedule'
                        );
                        $('.afvd-count-standings[data-liga="' + liga + '"]').text(result.standings_count);
                        $('.afvd-count-schedule[data-liga="' + liga + '"]').text(result.schedule_count);
                    }
                });

                statusSpan.text(ok + ' OK, ' + fail + ' failed');
            } else {
                statusSpan.text(afvdData.i18n.error);
            }
        })
        .fail(function () {
            statusSpan.text(afvdData.i18n.error);
        })
        .always(function () {
            btn.prop('disabled', false);
            $('.afvd-import-league').prop('disabled', false);
        });
    });

})(jQuery);
