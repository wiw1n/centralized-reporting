(function () {
    'use strict';

    function initReportSearch(inputId, tableId, emptyId) {
        var input = document.getElementById(inputId);
        var table = document.getElementById(tableId);
        var emptyMsg = document.getElementById(emptyId);

        if (!input || !table) {
            return;
        }

        var groups = {};
        table.querySelectorAll('tbody tr').forEach(function (row, index) {
            var key = row.getAttribute('data-hh-group') || ('row-' + index);
            if (!groups[key]) {
                groups[key] = [];
            }
            groups[key].push(row);
        });

        input.addEventListener('input', function () {
            var term = input.value.trim().toLowerCase();
            var anyVisible = false;

            Object.keys(groups).forEach(function (key) {
                var rows = groups[key];
                var text = rows.map(function (row) { return row.textContent; }).join(' ').toLowerCase();
                var matches = term === '' || text.indexOf(term) !== -1;

                if (matches) {
                    anyVisible = true;
                }

                rows.forEach(function (row) {
                    row.classList.toggle('d-none', !matches);
                });
            });

            if (emptyMsg) {
                emptyMsg.classList.toggle('d-none', anyVisible || term === '');
            }
        });
    }

    initReportSearch('hh_search', 'household_report_table', 'hh_search_empty');
    initReportSearch('ds_search', 'data_survey_report_table', 'ds_search_empty');
})();
