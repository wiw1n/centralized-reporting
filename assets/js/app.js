(function ($) {
    'use strict';

    function populateSelect($select, items, placeholder) {
        $select.empty();
        $select.append($('<option>', { value: '', text: placeholder }));
        items.forEach(function (item) {
            $select.append($('<option>', { value: item.id, text: item.name }));
        });
    }

    // Generic cascading <select> support: any element with
    // data-cascade-target + data-cascade-url refetches its child select
    // (via jQuery AJAX) whenever it changes; the change cascades down
    // the chain because populating the target fires its own 'change'.
    $(document).on('change', '[data-cascade-target]', function () {
        var $el = $(this);
        var $target = $('#' + $el.data('cascade-target'));
        var url = $el.data('cascade-url');
        var placeholder = $target.data('placeholder') || '-- Select --';

        if (!$target.length) {
            return;
        }

        if (!$el.val()) {
            populateSelect($target, [], placeholder);
            $target.trigger('change');
            return;
        }

        $.getJSON(url + encodeURIComponent($el.val()), function (data) {
            populateSelect($target, data, placeholder);
            $target.trigger('change');
        });
    });

    /**
     * Initializes a jQuery DataTables instance backed by server-side
     * processing (see application/libraries/Datatable.php).
     *
     * @param {string} selector    table selector, e.g. '#regions_table'
     * @param {string} ajaxUrl     controller endpoint returning the DataTables JSON envelope
     * @param {Array}  columns    DataTables column defs, e.g. [{data:'name'}, {data:'actions', orderable:false}]
     * @param {Object} [options]  { order, pageLength, extraData: function() {return {...}} }
     */
    window.initServerDataTable = function (selector, ajaxUrl, columns, options) {
        options = options || {};
        return $(selector).DataTable({
            serverSide: true,
            processing: true,
            searching: true,
            ajax: {
                url: ajaxUrl,
                type: 'GET',
                data: function (d) {
                    if (typeof options.extraData === 'function') {
                        $.extend(d, options.extraData());
                    }
                }
            },
            columns: columns,
            order: options.order || [[0, 'asc']],
            pageLength: options.pageLength || 25,
            language: { emptyTable: 'No records found.' }
        });
    };
})(jQuery);
