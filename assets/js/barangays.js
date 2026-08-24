(function ($) {
    'use strict';

    $(function () {
        var table = initServerDataTable('#barangays_table', BASE_URL + 'barangays/datatable', [
            { data: 'name' },
            { data: 'municipality_name' },
            { data: 'province_name' },
            { data: 'poblacion', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ], {
            extraData: function () {
                return {
                    region_id: $('#filter_region_id').val(),
                    province_id: $('#filter_province_id').val(),
                    municipality_id: $('#filter_municipality_id').val()
                };
            }
        });

        $('#filter_region_id, #filter_province_id, #filter_municipality_id').on('change', function () {
            table.ajax.reload();
        });
    });
})(jQuery);
