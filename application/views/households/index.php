<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-house-heart-fill"></i> Households</h3>
    <a href="<?= base_url('households/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Household
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if ($restricted_barangay_id !== null): ?>
            <div class="row g-2 mb-3 align-items-center">
                <div class="col-auto">
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-geo-alt-fill"></i>
                        <?= $locked_barangay ? html_escape('Barangay: ' . $locked_barangay->name) : 'No barangay assigned' ?>
                    </span>
                </div>
                <?php if ($locked_barangay): ?>
                <div class="col-auto">
                    <a href="<?= base_url('households/profile/' . $locked_barangay->id) ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-bar-chart-line"></i> View Barangay Profile
                    </a>
                </div>
                <?php endif; ?>
            </div>
        <?php elseif ($restricted_municipality_id !== null): ?>
            <div class="row g-2 mb-3">
                <div class="col-auto">
                    <select id="filter_barangay_id" class="form-select">
                        <option value="">All Barangays</option>
                        <?php foreach ($barangays as $barangay): ?>
                            <option value="<?= $barangay->id ?>"><?= html_escape($barangay->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <a href="#" id="view_profile_btn" class="btn btn-outline-secondary disabled">
                        <i class="bi bi-bar-chart-line"></i> View Barangay Profile
                    </a>
                </div>
            </div>
        <?php else: ?>
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
                    <select id="filter_province_id" class="form-select" data-cascade-target="filter_municipality_id" data-cascade-url="<?= base_url('municipalities/by_province/') ?>" data-placeholder="All Municipalities">
                        <option value="">All Provinces</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filter_municipality_id" class="form-select" data-cascade-target="filter_barangay_id" data-cascade-url="<?= base_url('barangays/by_municipality/') ?>" data-placeholder="All Barangays">
                        <option value="">All Municipalities</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filter_barangay_id" class="form-select">
                        <option value="">All Barangays</option>
                    </select>
                </div>
                <div class="col-auto">
                    <a href="#" id="view_profile_btn" class="btn btn-outline-secondary disabled">
                        <i class="bi bi-bar-chart-line"></i> View Barangay Profile
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table id="households_table" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Household No.</th>
                        <th>Barangay</th>
                        <th>Head of Household</th>
                        <th>Members</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
