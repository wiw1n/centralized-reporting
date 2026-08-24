(function ($) {
    'use strict';

    $(function () {
        var table = initServerDataTable('#provinces_table', BASE_URL + 'provinces/datatable', [
            { data: 'name' },
            { data: 'region_name' },
            { data: 'code' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ], {
            extraData: function () {
                return { region_id: $('#filter_region_id').val() };
            }
        });

        $('#filter_region_id').on('change', function () {
            table.ajax.reload();
        });
    });
})(jQuery);
