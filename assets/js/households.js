(function ($) {
    'use strict';

    // Households list (index.php)
    $(function () {
        var $table = $('#households_table');
        if (!$table.length) {
            return;
        }

        var table = initServerDataTable('#households_table', BASE_URL + 'households/datatable', [
            { data: 'household_no' },
            { data: 'barangay_name' },
            { data: 'head_name' },
            { data: 'member_count' },
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

        var $barangayFilter = $('#filter_barangay_id');
        var $profileBtn = $('#view_profile_btn');
        if ($barangayFilter.length && $profileBtn.length) {
            $barangayFilter.on('change', function () {
                var id = $(this).val();
                if (id) {
                    $profileBtn.attr('href', BASE_URL + 'households/profile/' + id).removeClass('disabled');
                } else {
                    $profileBtn.attr('href', '#').addClass('disabled');
                }
            });
        }
    });

    // Household form (form.php): dynamic member repeater.
    $(function () {
        var $container = $('#members_container');
        if (!$container.length) {
            return;
        }

        var nextMemberIndex = parseInt($container.data('next-index'), 10) || 0;

        $('#add_member_btn').on('click', function () {
            var template = document.getElementById('member_row_template');
            var $clone = $(template.content.cloneNode(true));
            var index = nextMemberIndex++;
            $clone.find('[name]').each(function () {
                this.name = this.name.replace('__INDEX__', index);
            });
            $container.append($clone);
        });

        $container.on('click', '.remove-member-btn', function () {
            if ($container.find('.member-row').length <= 1) {
                alert('A household must have at least one member.');
                return;
            }
            $(this).closest('.member-row').remove();
        });

        // Resident lookup: as the encoder types a member's name, search the
        // residents registry so an already-profiled person can be linked
        // instead of re-typed as a brand new individual.
        var residentSearchTimer = null;

        function currentBarangayId() {
            return $('#barangay_id').val() || '';
        }

        function searchResidentsForRow($row) {
            var last = $.trim($row.find('.member-last-name').val());
            var first = $.trim($row.find('.member-first-name').val());
            var $results = $row.find('.resident-match-results');

            if (last.length < 2 && first.length < 2) {
                $results.empty().addClass('d-none');
                return;
            }

            $.getJSON(BASE_URL + 'residents/search', {
                q: $.trim(last + ' ' + first),
                barangay_id: currentBarangayId()
            }, function (data) {
                renderResidentMatches($row, data);
            });
        }

        function renderResidentMatches($row, matches) {
            var $results = $row.find('.resident-match-results');
            $results.empty();

            if (!matches || !matches.length) {
                $results.addClass('d-none');
                return;
            }

            matches.forEach(function (resident) {
                $('<button type="button" class="list-group-item list-group-item-action py-1 px-2 small"></button>')
                    .text(resident.label)
                    .on('click', function () {
                        applyResidentToRow($row, resident);
                    })
                    .appendTo($results);
            });
            $results.removeClass('d-none');
        }

        function applyResidentToRow($row, resident) {
            $row.find('.member-last-name').val(resident.last_name);
            $row.find('.member-first-name').val(resident.first_name);
            $row.find('[name$="[middle_name]"]').val(resident.middle_name || '');
            $row.find('[name$="[suffix]"]').val(resident.suffix || '');
            $row.find('[name$="[sex]"]').val(resident.sex);
            $row.find('.member-birthdate').val(resident.birthdate).trigger('change');
            $row.find('[name$="[civil_status]"]').val(resident.civil_status || '');
            $row.find('[name$="[religion]"]').val(resident.religion || '');
            $row.find('[name$="[occupation]"]').val(resident.occupation || '');
            $row.find('[name$="[educational_attainment]"]').val(resident.educational_attainment || '');
            $row.find('[name$="[contact_number]"]').val(resident.contact_number || '');
            $row.find('.member-resident-id').val(resident.id);
            $row.find('.resident-link-label').text('Linked to resident ' + resident.resident_no);
            $row.find('.resident-link-badge').removeClass('d-none');
            $row.find('.resident-match-results').empty().addClass('d-none');
        }

        $container.on('input', '.member-last-name, .member-first-name', function () {
            var $row = $(this).closest('.member-row');

            if ($row.find('.member-resident-id').val()) {
                $row.find('.member-resident-id').val('');
                $row.find('.resident-link-badge').addClass('d-none');
            }

            clearTimeout(residentSearchTimer);
            residentSearchTimer = setTimeout(function () { searchResidentsForRow($row); }, 350);
        });

        $container.on('blur', '.member-last-name, .member-first-name', function () {
            var $row = $(this).closest('.member-row');
            setTimeout(function () { $row.find('.resident-match-results').addClass('d-none'); }, 200);
        });

        $container.on('click', '.resident-unlink-btn', function () {
            var $row = $(this).closest('.member-row');
            $row.find('.member-resident-id').val('');
            $row.find('.resident-link-badge').addClass('d-none');
        });

        // Cosmetic only: the senior-citizen checkbox is disabled and never
        // submitted -- the server derives it from birthdate on every save.
        $container.on('change', '.member-birthdate', function () {
            var $row = $(this).closest('.member-row');
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
            $row.find('.member-senior-checkbox').prop('checked', age >= 60);
        });

        $container.closest('form').on('submit', function (e) {
            var $rows = $container.find('.member-row');
            if ($rows.length === 0) {
                alert('A household must have at least one member.');
                e.preventDefault();
                return;
            }

            var headCount = 0;
            var missingRequired = false;
            $rows.each(function () {
                var $row = $(this);
                if ($row.find('.member-relationship').val() === 'Head') {
                    headCount++;
                }
                $row.find('[required]').each(function () {
                    if (!this.value) {
                        missingRequired = true;
                    }
                });
            });

            if (missingRequired) {
                alert('Please fill in all required member fields.');
                e.preventDefault();
                return;
            }
            if (headCount === 0) {
                alert('A household must have exactly one member marked as Head.');
                e.preventDefault();
                return;
            }
            if (headCount > 1) {
                alert('Only one member may be marked as Head.');
                e.preventDefault();
            }
        });
    });
})(jQuery);
