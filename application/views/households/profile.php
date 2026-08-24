<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0"><i class="bi bi-bar-chart-line"></i> Barangay Profile</h3>
        <p class="text-muted mb-0">
            Brgy. <?= html_escape($barangay->name) ?><?= $municipality ? ', ' . html_escape($municipality->name) : '' ?><?= $province ? ', ' . html_escape($province->name) : '' ?><?= $region ? ', ' . html_escape($region->name) : '' ?>
        </p>
    </div>
    <a href="<?= base_url('households') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Households</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-2 col-6">
        <div class="card shadow-sm text-center h-100">
            <div class="card-body">
                <div class="text-muted small">Households</div>
                <div class="fs-3 fw-semibold"><?= (int) $summary->household_count ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm text-center h-100">
            <div class="card-body">
                <div class="text-muted small">Population</div>
                <div class="fs-3 fw-semibold"><?= (int) $summary->population ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm text-center h-100">
            <div class="card-body">
                <div class="text-muted small">PWD</div>
                <div class="fs-3 fw-semibold"><?= (int) $summary->pwd_count ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm text-center h-100">
            <div class="card-body">
                <div class="text-muted small">Senior Citizens</div>
                <div class="fs-3 fw-semibold"><?= (int) $summary->senior_count ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm text-center h-100">
            <div class="card-body">
                <div class="text-muted small">Solo Parents</div>
                <div class="fs-3 fw-semibold"><?= (int) $summary->solo_parent_count ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card shadow-sm text-center h-100">
            <div class="card-body">
                <div class="text-muted small">4Ps Beneficiaries</div>
                <div class="fs-3 fw-semibold"><?= (int) $summary->fourps_count ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">Sex Breakdown</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <?php foreach ($summary->sex_breakdown as $sex => $count): ?>
                        <tr>
                            <td><?= html_escape($sex) ?></td>
                            <td class="text-end fw-semibold"><?= (int) $count ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">Age Bracket Breakdown</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <?php foreach ($summary->age_brackets as $bracket => $count): ?>
                        <tr>
                            <td><?= html_escape($bracket) ?></td>
                            <td class="text-end fw-semibold"><?= (int) $count ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>
