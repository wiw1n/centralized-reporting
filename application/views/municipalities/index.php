<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-building"></i> Municipalities</h3>
    <?php if ($restricted_municipality_id === null): ?>
    <a href="<?= base_url('municipalities/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Municipality
    </a>
    <?php endif; ?>
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
                <select id="filter_province_id" class="form-select">
                    <option value="">All Provinces</option>
                </select>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table id="municipalities_table" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Province</th>
                        <th>Region</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
