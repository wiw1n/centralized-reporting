(function ($) {
    'use strict';

    $(function () {
        var $form = $('#report_form');
        if (!$form.length) {
            return;
        }

        var $resultsCard = $('#report_results_card');
        var $resultsTable = $('#report_results_table');
        var $resultsSummary = $('#report_results_summary');
        var $templateSelect = $('#template_select');
        var $deleteTemplateBtn = $('#delete_template_btn');
        var saveTemplateModal = new bootstrap.Modal(document.getElementById('save_template_modal'));

        // This page can fire several AJAX POSTs (preview, export, save) without a full reload,
        // and CSRF protection regenerates the token on every POST -- so the hidden csrf field
        // baked into the page at load time goes stale after the first one, and every request
        // after that gets rejected. Re-sync it (from the cookie the server just refreshed) after
        // every request, success or failure -- see syncCsrfToken() in templates/footer.php.
        $(document).ajaxComplete(syncCsrfToken);

        // Column picker: "All" / "None" per field group.
        $(document).on('click', '.field-group-toggle', function (e) {
            e.preventDefault();
            var group = $(this).data('group');
            var check = $(this).data('check') === 1 || $(this).data('check') === '1';
            $('[data-field-group="' + group + '"] input[type=checkbox]').prop('checked', check);
        });

        function renderResults(result) {
            var $thead = $resultsTable.find('thead').empty();
            var $tbody = $resultsTable.find('tbody').empty();

            if (!result.columns.length) {
                $resultsSummary.text('');
                $thead.append('<tr><th>No columns selected</th></tr>');
                $resultsCard.removeClass('d-none');
                return;
            }

            var $headRow = $('<tr>');
            result.columns.forEach(function (col) {
                $headRow.append($('<th>').text(col.label));
            });
            $thead.append($headRow);

            if (!result.rows.length) {
                $tbody.append('<tr><td colspan="' + result.columns.length + '" class="text-muted text-center py-3">No residents match these filters.</td></tr>');
            } else {
                result.rows.forEach(function (row) {
                    var $tr = $('<tr>');
                    result.columns.forEach(function (col) {
                        var value = row[col.key];
                        if (col.type === 'boolean') {
                            // Truthy for tinyint flags ("1") and for varchar flags
                            // such as is_4ps_beneficiary that store an ID string.
                            value = (value === null || value === undefined || value === '' || value === '0' || value === 0) ? 'No' : 'Yes';
                        } else if (value === null || value === undefined) {
                            value = '';
                        }
                        $tr.append($('<td>').text(value));
                    });
                    $tbody.append($tr);
                });
            }

            var summary = result.total + (result.total === 1 ? ' resident matched' : ' residents matched');
            if (result.truncated) {
                summary += ' -- showing the first ' + result.rows.length + ' (export CSV for the full list)';
            }
            $resultsSummary.text(summary);
            $resultsCard.removeClass('d-none');
        }

        function generateReport() {
            var $btn = $('#generate_report_btn');
            $btn.prop('disabled', true);
            return $.post(BASE_URL + 'reports/preview', $form.serialize())
                .done(renderResults)
                .fail(function () {
                    alert('Could not generate the report. Please try again.');
                })
                .always(function () {
                    $btn.prop('disabled', false);
                });
        }

        $('#generate_report_btn').on('click', function () {
            generateReport();
        });

        $('#print_report_btn').on('click', function () {
            if ($resultsCard.hasClass('d-none')) {
                generateReport().done(function () {
                    window.print();
                });
            } else {
                window.print();
            }
        });

        // Export fetches the CSV over AJAX (as a blob) and saves it via a throwaway <a download>
        // instead of submitting the form as a real page/iframe navigation. A form-submit-to-
        // hidden-iframe approach was tried first but browsers are inconsistent about letting a
        // second download fire into a target frame that's already been used once.
        $('#export_csv_btn').on('click', function () {
            if (!$form.find('input[name="fields[]"]:checked').length) {
                alert('Select at least one field before exporting.');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: BASE_URL + 'reports/export_csv',
                method: 'POST',
                data: $form.serialize(),
                xhrFields: { responseType: 'blob' }
            }).done(function (blob, status, xhr) {
                var disposition = xhr.getResponseHeader('Content-Disposition') || '';
                var match = /filename="?([^"]+)"?/.exec(disposition);
                var filename = match ? match[1] : 'report.csv';

                var url = URL.createObjectURL(blob);
                var $link = $('<a>', { href: url, download: filename }).appendTo('body');
                $link[0].click();
                $link.remove();
                URL.revokeObjectURL(url);
            }).fail(function () {
                alert('Could not export the report. Please try again.');
            }).always(function () {
                $btn.prop('disabled', false);
            });
        });

        $form.on('reset', function () {
            $resultsCard.addClass('d-none');
            $templateSelect.val('');
            $deleteTemplateBtn.addClass('d-none');
        });

        // Save current field/filter selection as a named, reusable report.
        $('#save_template_btn').on('click', function () {
            $('#save_template_name').val('');
            $('#save_template_error').addClass('d-none');
            saveTemplateModal.show();
        });

        $('#save_template_confirm_btn').on('click', function () {
            var name = $('#save_template_name').val().trim();
            var $error = $('#save_template_error');
            if (!name) {
                $error.text('Please enter a name.').removeClass('d-none');
                return;
            }

            $.post(BASE_URL + 'reports/save_template', $form.serialize() + '&name=' + encodeURIComponent(name))
                .done(function (data) {
                    $templateSelect.append($('<option>', { value: data.id, text: data.name, selected: true }));
                    $deleteTemplateBtn.removeClass('d-none').attr('href', BASE_URL + 'reports/delete_template/' + data.id);
                    saveTemplateModal.hide();
                })
                .fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.error) || 'Could not save this report.';
                    $error.text(message).removeClass('d-none');
                });
        });

        // Load a saved report's field/filter selection back into the form.
        $templateSelect.on('change', function () {
            var id = $(this).val();
            if (!id) {
                $deleteTemplateBtn.addClass('d-none');
                return;
            }
            $deleteTemplateBtn.removeClass('d-none').attr('href', BASE_URL + 'reports/delete_template/' + id);

            $.getJSON(BASE_URL + 'reports/load_template/' + id, function (data) {
                $form.find('input[name="fields[]"]').prop('checked', false);
                (data.fields || []).forEach(function (key) {
                    $form.find('#field_' + key).prop('checked', true);
                });

                $form.find('[name^="filters"]').each(function () {
                    var $el = $(this);
                    if ($el.attr('type') === 'checkbox') {
                        $el.prop('checked', false);
                    } else {
                        $el.val('');
                    }
                });

                $.each(data.filters || {}, function (key, value) {
                    if (Array.isArray(value)) {
                        value.forEach(function (v) {
                            $form.find('[name="filters[' + key + '][]"][value="' + v + '"]').prop('checked', true);
                        });
                    } else if (value && typeof value === 'object') {
                        $.each(value, function (subKey, subValue) {
                            $form.find('[name="filters[' + key + '][' + subKey + ']"]').val(subValue);
                        });
                    } else if (value) {
                        $form.find('[name="filters[' + key + ']"]').prop('checked', true);
                    }
                });
            }).fail(function () {
                alert('Could not load this saved report.');
            });
        });
    });
})(jQuery);
