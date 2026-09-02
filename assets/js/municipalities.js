(function ($) {
    'use strict';

    $(function () {
        var table = initServerDataTable('#municipalities_table', BASE_URL + 'municipalities/datatable', [
            { data: 'name' },
            { data: 'prefix' },
            { data: 'province_name' },
            { data: 'region_name' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ], {
            extraData: function () {
                return {
                    region_id: $('#filter_region_id').val(),
                    province_id: $('#filter_province_id').val()
                };
            }
        });

        $('#filter_region_id, #filter_province_id').on('change', function () {
            table.ajax.reload();
        });
    });
})(jQuery);
