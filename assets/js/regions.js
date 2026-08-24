(function ($) {
    'use strict';

    $(function () {
        initServerDataTable('#regions_table', BASE_URL + 'regions/datatable', [
            { data: 'name' },
            { data: 'code' },
            { data: 'description' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ]);
    });
})(jQuery);
