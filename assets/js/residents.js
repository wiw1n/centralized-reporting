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
            { data: 'household_no' },
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

        var $fourpsCheckbox = $('#is_4ps_beneficiary');
        var $fourpsRow = $('#fourps_id_row');
        if ($fourpsCheckbox.length && $fourpsRow.length) {
            function toggleFourpsRow() {
                $fourpsRow.toggle($fourpsCheckbox.is(':checked'));
            }
            $fourpsCheckbox.on('change', toggleFourpsRow);
            toggleFourpsRow();
        }

        var $papsmearCheckbox = $('#wra_papsmear_done');
        var $papsmearRow = $('#wra_papsmear_result_row');
        if ($papsmearCheckbox.length && $papsmearRow.length) {
            function togglePapsmearRow() {
                $papsmearRow.toggle($papsmearCheckbox.is(':checked'));
            }
            $papsmearCheckbox.on('change', togglePapsmearRow);
            togglePapsmearRow();
        }

        var $newbornScreening = $('#child_newborn_screening');
        var $newbornScreeningRow = $('#child_newborn_screening_result_row');
        if ($newbornScreening.length && $newbornScreeningRow.length) {
            function toggleNewbornScreeningRow() {
                $newbornScreeningRow.toggle($newbornScreening.val() === 'Yes');
            }
            $newbornScreening.on('change', toggleNewbornScreeningRow);
            toggleNewbornScreeningRow();
        }

        // Sidebar section nav: show one section card at a time. Every
        // .form-section is hidden except the one whose nav link was clicked.
        var $nav = $('#form_section_nav');
        if ($nav.length) {
            var $navLinks = $nav.find('.list-group-item[href^="#section-"]');
            var $sections = $('.form-section');
            var form = $sections.first().closest('form').get(0);

            function showSection(id) {
                var $target = $('#' + id);
                if (!$target.hasClass('form-section')) {
                    return;
                }
                $sections.addClass('d-none');
                $target.removeClass('d-none');
                $navLinks.removeClass('active').filter('[href="#' + id + '"]').addClass('active');
            }

            $navLinks.on('click', function (e) {
                e.preventDefault();
                showSection(this.getAttribute('href').slice(1));
            });

            // Native validation cannot focus a control inside a hidden
            // section, so reveal the section holding the first invalid field.
            if (form) {
                form.addEventListener('invalid', function (e) {
                    if (form.querySelector(':invalid') !== e.target) {
                        return;
                    }
                    var section = e.target.closest('.form-section');
                    if (section) {
                        showSection(section.id);
                    }
                }, true);
            }

            // Show Personal Information by default; an explicit hash wins.
            $sections.addClass('d-none');
            var initial = (window.location.hash || '').replace('#', '');
            showSection(initial && $('#' + initial).hasClass('form-section') ? initial : 'section-personal');
        }
    });
})(jQuery);
