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

// Household map modal: shows a read-only pin for the household location behind each "Map" button.
(function ($) {
    'use strict';

    if (typeof $ === 'undefined') {
        return;
    }

    var $modal = $('#household_map_modal');
    if (!$modal.length || typeof L === 'undefined') {
        return;
    }

    var map = null;
    var marker = null;
    var pendingLat = null;
    var pendingLon = null;

    $(document).on('click', '.household-map-btn', function () {
        pendingLat = parseFloat($(this).data('lat'));
        pendingLon = parseFloat($(this).data('lon'));
        $('#household_map_modal_label').text($(this).data('label') || 'Household Location');

        var modalInstance = bootstrap.Modal.getOrCreateInstance($modal.get(0));
        modalInstance.show();
    });

    $modal.on('shown.bs.modal', function () {
        if (isNaN(pendingLat) || isNaN(pendingLon)) {
            return;
        }

        if (!map) {
            map = L.map('household_map_modal_map').setView([pendingLat, pendingLon], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);
            marker = L.marker([pendingLat, pendingLon]).addTo(map);
        } else {
            map.setView([pendingLat, pendingLon], 16);
            marker.setLatLng([pendingLat, pendingLon]);
        }

        setTimeout(function () { map.invalidateSize(); }, 0);
    });
})(window.jQuery);
