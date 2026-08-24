(function ($) {
    'use strict';

    // Users list (index.php)
    $(function () {
        var $table = $('#users_table');
        if (!$table.length) {
            return;
        }

        initServerDataTable('#users_table', BASE_URL + 'users/datatable', [
            { data: 'username' },
            { data: 'name', orderable: false },
            { data: 'email' },
            { data: 'role_label' },
            { data: 'status' },
            { data: 'last_login' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ]);
    });

    // User form (form.php): show/hide the area assignment panel based on role.
    $(function () {
        var $roleSelect = $('#role_id');
        var $panel = $('#area_assignment_panel');
        var $roleDescription = $('#role_description');

        if (!$roleSelect.length) {
            return;
        }

        function togglePanel() {
            var $selected = $roleSelect.find('option:selected');
            var roleName = $selected.data('role-name');
            $panel.toggle(roleName === 'encoder');
            $roleDescription.text($selected.data('role-description') || '');
        }

        $roleSelect.on('change', togglePanel);
        togglePanel();
    });

    // User form (form.php): area assignment builder (Encoder role scoping).
    var $builder = $('#area_assignment_builder');
    if ($builder.length) {
        var $scopeSelect = $('#scope_type');
        var $regionSelect = $('#area_region_id');
        var $provinceSelect = $('#area_province_id');
        var $municipalitySelect = $('#area_municipality_id');
        var $barangaySelect = $('#area_barangay_id');
        var $addBtn = $('#add_area_btn');
        var $listEl = $('#assigned_areas_list');
        var $inputsEl = $('#assignments_inputs');
        var $emptyMsg = $('#assigned_areas_empty');

        var levels = {
            region: [$regionSelect],
            province: [$regionSelect, $provinceSelect],
            municipality: [$regionSelect, $provinceSelect, $municipalitySelect],
            barangay: [$regionSelect, $provinceSelect, $municipalitySelect, $barangaySelect]
        };

        var assignments = [];

        function refreshVisibility() {
            var visible = levels[$scopeSelect.val()] || [];
            [$regionSelect, $provinceSelect, $municipalitySelect, $barangaySelect].forEach(function ($sel) {
                var $wrapper = $sel.closest('[data-area-level]');
                $wrapper.toggle(visible.indexOf($sel) !== -1);
            });
        }

        function currentAreaSelect() {
            var map = { region: $regionSelect, province: $provinceSelect, municipality: $municipalitySelect, barangay: $barangaySelect };
            return map[$scopeSelect.val()];
        }

        function renderList() {
            $listEl.empty();
            $inputsEl.empty();
            $emptyMsg.toggle(assignments.length === 0);

            assignments.forEach(function (a, index) {
                var $li = $('<li>', { class: 'list-group-item d-flex justify-content-between align-items-center' });
                $li.append(
                    $('<span>').text(a.label + ' ').append(
                        $('<span>', { class: 'badge bg-secondary text-uppercase', text: a.scope_type })
                    )
                );
                $li.append(
                    $('<button>', { type: 'button', class: 'btn btn-sm btn-outline-danger', 'data-remove-index': index })
                        .append('<i class="bi bi-x-lg"></i>')
                );
                $listEl.append($li);

                $inputsEl.append($('<input>', { type: 'hidden', name: 'assignments[' + index + '][scope_type]', value: a.scope_type }));
                $inputsEl.append($('<input>', { type: 'hidden', name: 'assignments[' + index + '][area_id]', value: a.area_id }));
            });
        }

        $listEl.on('click', '[data-remove-index]', function () {
            var idx = parseInt($(this).data('remove-index'), 10);
            assignments.splice(idx, 1);
            renderList();
        });

        $addBtn.on('click', function () {
            var scope = $scopeSelect.val();
            var $select = currentAreaSelect();
            if (!scope || !$select || !$select.val()) {
                alert('Please select an area before adding.');
                return;
            }

            var areaId = $select.val();
            var duplicate = assignments.some(function (a) {
                return a.scope_type === scope && String(a.area_id) === String(areaId);
            });
            if (duplicate) {
                alert('That area has already been assigned.');
                return;
            }

            assignments.push({
                scope_type: scope,
                area_id: areaId,
                label: $select.find('option:selected').text()
            });
            renderList();
        });

        $scopeSelect.on('change', refreshVisibility);
        refreshVisibility();
        renderList();

        // Preload existing assignments (edit mode), injected as a JSON script tag.
        var $seedEl = $('#assigned_areas_seed');
        if ($seedEl.length) {
            try {
                var seeded = JSON.parse($seedEl.text());
                assignments = seeded.map(function (a) {
                    return { scope_type: a.scope_type, area_id: a.area_id, label: a.label };
                });
                renderList();
            } catch (err) {
                console.error('Failed to parse seeded assignments', err);
            }
        }
    }
})(jQuery);
