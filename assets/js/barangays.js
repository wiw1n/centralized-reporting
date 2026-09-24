(function ($) {
    'use strict';

    $(function () {
        var table = initServerDataTable('#barangays_table', BASE_URL + 'barangays/datatable', [
            { data: 'name' },
            { data: 'prefix' },
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

        if ($('#barangay_map').length) {
            var picker = window.initLocationPicker({
                mapId: 'barangay_map',
                latInputId: 'latitude',
                lonInputId: 'longitude'
            });

            function recenterOnMunicipality(lat, lon) {
                if (!picker || isNaN(lat) || isNaN(lon)) {
                    return;
                }
                picker.recenter(lat, lon, 13);
            }

            // Encoders have a locked municipality (no dropdown); center on it
            // once, but only if the barangay itself has no coordinates yet.
            var $lockedLat = $('#locked_municipality_lat');
            var hasOwnCoordinates = !!$('#latitude').val();
            if ($lockedLat.length && !hasOwnCoordinates) {
                recenterOnMunicipality(parseFloat($lockedLat.val()), parseFloat($('#locked_municipality_lon').val()));
            }

            // Super admins pick the municipality via cascading dropdowns; recenter
            // the view (not the marker) whenever the selection changes, and once
            // on load when editing a barangay that has no coordinates of its own yet.
            var $municipalitySelect = $('#municipality_id');
            if ($municipalitySelect.length && !hasOwnCoordinates) {
                var $preselected = $municipalitySelect.find('option:selected');
                recenterOnMunicipality(parseFloat($preselected.attr('data-lat')), parseFloat($preselected.attr('data-lon')));
            }
            $municipalitySelect.on('change', function () {
                var $selected = $(this).find('option:selected');
                recenterOnMunicipality(parseFloat($selected.attr('data-lat')), parseFloat($selected.attr('data-lon')));
            });
        }
    });
})(jQuery);
