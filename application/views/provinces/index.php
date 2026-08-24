<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-map"></i> Provinces</h3>
    <a href="<?= base_url('provinces/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Province
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-auto">
                <select id="filter_region_id" class="form-select">
                    <option value="">All Regions</option>
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region->id ?>"><?= html_escape($region->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="provinces_table" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Region</th>
                        <th>Code</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
