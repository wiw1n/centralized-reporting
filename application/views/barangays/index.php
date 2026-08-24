<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-houses-fill"></i> Barangays</h3>
    <a href="<?= base_url('barangays/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Barangay
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if ($restricted_municipality_id === null): ?>
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
                <select id="filter_municipality_id" class="form-select">
                    <option value="">All Municipalities</option>
                </select>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table id="barangays_table" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Municipality</th>
                        <th>Province</th>
                        <th>Poblacion</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
