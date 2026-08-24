(function ($) {
    'use strict';

    // Residents list (index.php)
    $(function () {
        var $table = $('#residents_table');
        if (!$table.length) {
            return;
        }

        var table = initServerDataTable('#residents_table', BASE_URL + 'residents/datatable', [
            { data: 'resident_no' },
            { data: 'full_name' },
            { data: 'barangay_name' },
            { data: 'sex' },
            { data: 'age' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ], {
            extraData: function () {
                return {
                    region_id: $('#filter_region_id').val(),
                    province_id: $('#filter_province_id').val(),
                    municipality_id: $('#filter_municipality_id').val(),
                    barangay_id: $('#filter_barangay_id').val()
                };
            }
        });

        $('#filter_region_id, #filter_province_id, #filter_municipality_id, #filter_barangay_id').on('change', function () {
            table.ajax.reload();
        });
    });

    // Resident form (form.php)
    $(function () {
        var $birthdate = $('#resident_birthdate');
        var $seniorDisplay = $('#is_senior_display');
        if ($birthdate.length && $seniorDisplay.length) {
            $birthdate.on('change', function () {
                var birthdate = new Date(this.value);
                if (isNaN(birthdate.getTime())) {
                    return;
                }
                var today = new Date();
                var age = today.getFullYear() - birthdate.getFullYear();
                var hasHadBirthdayThisYear = (today.getMonth() > birthdate.getMonth())
                    || (today.getMonth() === birthdate.getMonth() && today.getDate() >= birthdate.getDate());
                if (!hasHadBirthdayThisYear) {
                    age -= 1;
                }
                $seniorDisplay.prop('checked', age >= 60);
            });
        }

        var $indigenousCheckbox = $('#is_indigenous');
        var $indigenousRow = $('#indigenous_group_row');
        if ($indigenousCheckbox.length && $indigenousRow.length) {
            function toggleIndigenousRow() {
                $indigenousRow.toggle($indigenousCheckbox.is(':checked'));
            }
            $indigenousCheckbox.on('change', toggleIndigenousRow);
            toggleIndigenousRow();
        }

        // Sidebar section nav: highlight the section currently in view as the
        // long form is scrolled, using Bootstrap's ScrollSpy against <body>.
        if ($('#form_section_nav').length && window.bootstrap && window.bootstrap.ScrollSpy) {
            new bootstrap.ScrollSpy(document.body, {
                target: '#form_section_nav',
                rootMargin: '0px 0px -70%',
                smoothScroll: true
            });
        }
    });
})(jQuery);
