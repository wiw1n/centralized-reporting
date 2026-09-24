(function ($) {
    'use strict';

    function populateSelect($select, items, placeholder) {
        $select.empty();
        $select.append($('<option>', { value: '', text: placeholder }));
        items.forEach(function (item) {
            var $option = $('<option>', { value: item.id, text: item.name });
            if (item.latitude !== undefined && item.latitude !== null && item.latitude !== '') {
                $option.attr('data-lat', item.latitude);
            }
            if (item.longitude !== undefined && item.longitude !== null && item.longitude !== '') {
                $option.attr('data-lon', item.longitude);
            }
            $select.append($option);
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
    /**
     * Wires up a Leaflet map that lets the user click/drag a marker to pick
     * coordinates, writing them into a pair of lat/lon inputs (and reading
     * them back on load if already filled in).
     *
     * @param {Object} opts { mapId, latInputId, lonInputId, defaultCenter: [lat, lon], defaultZoom, focusedZoom }
     * @return {Object|null} { recenter(lat, lon, zoom), invalidateSize() } or null if the map element/Leaflet is missing
     */
    window.initLocationPicker = function (opts) {
        var mapEl = document.getElementById(opts.mapId);
        if (!mapEl || typeof L === 'undefined') {
            return null;
        }

        var $lat = $('#' + opts.latInputId);
        var $lon = $('#' + opts.lonInputId);
        var defaultCenter = opts.defaultCenter || [11.24, 124.87];
        var defaultZoom = opts.defaultZoom || 9;
        var focusedZoom = opts.focusedZoom || 15;

        var initialLat = parseFloat($lat.val());
        var initialLon = parseFloat($lon.val());
        var hasInitial = !isNaN(initialLat) && !isNaN(initialLon);

        var map = L.map(mapEl).setView(hasInitial ? [initialLat, initialLon] : defaultCenter, hasInitial ? focusedZoom : defaultZoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        var marker = null;

        function updateInputs(lat, lon) {
            $lat.val(lat.toFixed(7)).trigger('input');
            $lon.val(lon.toFixed(7)).trigger('input');
        }

        function setMarker(lat, lon) {
            if (marker) {
                marker.setLatLng([lat, lon]);
            } else {
                marker = L.marker([lat, lon], { draggable: true }).addTo(map);
                marker.on('dragend', function () {
                    var pos = marker.getLatLng();
                    updateInputs(pos.lat, pos.lng);
                });
            }
        }

        if (hasInitial) {
            setMarker(initialLat, initialLon);
        }

        map.on('click', function (e) {
            setMarker(e.latlng.lat, e.latlng.lng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });

        $lat.add($lon).on('change', function () {
            var lat = parseFloat($lat.val());
            var lon = parseFloat($lon.val());
            if (!isNaN(lat) && !isNaN(lon)) {
                setMarker(lat, lon);
                map.setView([lat, lon], Math.max(map.getZoom(), focusedZoom));
            }
        });

        setTimeout(function () { map.invalidateSize(); }, 0);

        return {
            recenter: function (lat, lon, zoom) {
                map.setView([lat, lon], zoom || focusedZoom);
            },
            invalidateSize: function () {
                map.invalidateSize();
            }
        };
    };

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
