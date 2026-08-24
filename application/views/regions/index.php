<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-globe-asia-australia"></i> Regions</h3>
    <a href="<?= base_url('regions/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Region
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="regions_table" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
