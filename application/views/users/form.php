<?php
$seed = array_map(function ($a) {
    $areaId = null;
    switch ($a->scope_type) {
        case 'region': $areaId = $a->region_id; break;
        case 'province': $areaId = $a->province_id; break;
        case 'municipality': $areaId = $a->municipality_id; break;
        case 'barangay': $areaId = $a->barangay_id; break;
    }
    return ['scope_type' => $a->scope_type, 'area_id' => $areaId, 'label' => $a->area_label];
}, $assigned_areas);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-people-fill"></i> <?= html_escape($page_title) ?></h3>
    <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?= form_open(current_url()) ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required
                           value="<?= set_value('username', $user->username ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= set_value('email', $user->email ?? '') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" required
                           value="<?= set_value('first_name', $user->first_name ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required
                           value="<?= set_value('last_name', $user->last_name ?? '') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <?= $user ? '<small class="text-muted">(leave blank to keep current)</small>' : '' ?></label>
                    <input type="password" name="password" class="form-control" <?= $user ? '' : 'required' ?>>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirm" class="form-control" <?= $user ? '' : 'required' ?>>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role</label>
                    <select name="role_id" id="role_id" class="form-select" required>
                        <option value="">-- Select Role --</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role->id ?>" data-role-name="<?= html_escape($role->name) ?>" data-role-description="<?= html_escape($role->description) ?>"
                                <?= set_value('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' ?>>
                                <?= html_escape($role->label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="role_description" class="form-text"></div>
                </div>
                <?php if (!$user || (int) $user->id !== (int) $current_user->id): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" name="status" id="status" class="form-check-input" value="1"
                            <?= set_value('status', $user->status ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div id="area_assignment_panel" style="display:none;">
                <hr>
                <h5><i class="bi bi-geo-alt-fill"></i> Assigned Areas</h5>
                <p class="text-muted">Encoders are restricted to the region(s), province(s), municipality(ies), or barangay(s) assigned here.</p>

                <div id="area_assignment_builder" class="border rounded p-3 bg-light">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Scope Level</label>
                            <select id="scope_type" class="form-select">
                                <option value="region">Region</option>
                                <option value="province">Province</option>
                                <option value="municipality" selected>Municipality</option>
                                <option value="barangay">Barangay</option>
                            </select>
                        </div>
                        <div class="col-md-3" data-area-level="region">
                            <label class="form-label">Region</label>
                            <select id="area_region_id" class="form-select" data-cascade-target="area_province_id" data-cascade-url="<?= base_url('provinces/by_region/') ?>" data-placeholder="-- Select Province --">
                                <option value="">-- Select Region --</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region->id ?>"><?= html_escape($region->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3" data-area-level="province">
                            <label class="form-label">Province</label>
                            <select id="area_province_id" class="form-select" data-cascade-target="area_municipality_id" data-cascade-url="<?= base_url('municipalities/by_province/') ?>" data-placeholder="-- Select Municipality --">
                                <option value="">-- Select Province --</option>
                            </select>
                        </div>
                        <div class="col-md-3" data-area-level="municipality">
                            <label class="form-label">Municipality</label>
                            <select id="area_municipality_id" class="form-select" data-cascade-target="area_barangay_id" data-cascade-url="<?= base_url('barangays/by_municipality/') ?>" data-placeholder="-- Select Barangay --">
                                <option value="">-- Select Municipality --</option>
                            </select>
                        </div>
                        <div class="col-md-3" data-area-level="barangay">
                            <label class="form-label">Barangay</label>
                            <select id="area_barangay_id" class="form-select">
                                <option value="">-- Select Barangay --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="add_area_btn" class="btn btn-outline-primary w-100">
                                <i class="bi bi-plus-lg"></i> Add Area
                            </button>
                        </div>
                    </div>
                </div>

                <ul id="assigned_areas_list" class="list-group mt-3"></ul>
                <p id="assigned_areas_empty" class="text-muted mt-2">No areas assigned yet.</p>
                <div id="assignments_inputs"></div>
                <script type="application/json" id="assigned_areas_seed"><?= json_encode($seed) ?></script>
            </div>

            <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-save"></i> Save</button>
        <?= form_close() ?>
    </div>
</div>
