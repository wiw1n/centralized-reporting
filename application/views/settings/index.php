<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-gear-fill"></i> System Settings</h3>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h5 class="card-title">Active Municipality</h5>
        <p class="text-muted">Only one municipality can be active at a time. Admin and encoder accounts will only see data for the active municipality.</p>
        <?php if ($current_active): ?>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-success fs-6"><i class="bi bi-geo-alt-fill"></i> <?= html_escape($current_active->name) ?>, <?= html_escape($current_active->province_name) ?></span>
                <a href="<?= base_url('settings/deactivate/' . $current_active->id) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this municipality? No municipality will be active until you set a new one.');">
                    <i class="bi bi-x-circle"></i> Deactivate
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle-fill"></i> No municipality is currently active. Admin and encoder accounts will see no address data until you activate one below.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-auto">
                <select id="filter_region_id" class="form-select" data-cascade-target="filter_province_id" data-cascade-url="<?= base_url('provinces/by_region/') ?>" data-placeholder="All Provinces">
                    <option value="">All Regions</option>
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region->id ?>"><?= html_escape($region->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <select id="filter_province_id" class="form-select">
                    <option value="">All Provinces</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="settings_municipalities_table" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Province</th>
                        <th>Region</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
