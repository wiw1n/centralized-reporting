<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-speedometer2"></i> Dashboard</h3>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h5>Welcome, <?= html_escape($current_user->full_name) ?>!</h5>
        <p class="text-muted mb-0">
            You are signed in as <strong><?= html_escape($current_user->role_label) ?></strong>.
        </p>
    </div>
</div>

<?php if (isset($restricted_municipality_id) && $restricted_municipality_id !== null): ?>
<div class="card shadow-sm mt-3">
    <div class="card-header bg-white">
        <i class="bi bi-building"></i> Your Municipality
    </div>
    <div class="card-body">
        <?php if ($active_municipality): ?>
            <h5><?= html_escape($active_municipality->name) ?>, <?= html_escape($active_municipality->province_name) ?></h5>
            <p class="text-muted mb-0"><?= (int) ($municipality_barangay_count ?? 0) ?> barangay(s) on record.</p>
        <?php else: ?>
            <p class="text-warning mb-0"><i class="bi bi-exclamation-triangle-fill"></i> No municipality is currently active. Contact your Super Admin to activate one in System Settings.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (isset($town_summary)): ?>
<div class="card shadow-sm mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bar-chart-line"></i> Town-wide Resident Profile</span>
        <?php if (!empty($active_municipality)): ?>
            <a href="<?= base_url('residents') ?>" class="btn btn-outline-secondary btn-sm">View Residents</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row g-3 text-center">
            <div class="col-md-2 col-6">
                <div class="text-muted small">Population</div>
                <div class="fs-4 fw-semibold"><?= (int) $town_summary->population ?></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="text-muted small">Households</div>
                <div class="fs-4 fw-semibold"><?= (int) $town_summary->household_count ?></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="text-muted small">PWD</div>
                <div class="fs-4 fw-semibold"><?= (int) $town_summary->pwd_count ?></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="text-muted small">Senior Citizens</div>
                <div class="fs-4 fw-semibold"><?= (int) $town_summary->senior_count ?></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="text-muted small">Solo Parents</div>
                <div class="fs-4 fw-semibold"><?= (int) $town_summary->solo_parent_count ?></div>
            </div>
            <div class="col-md-2 col-6">
                <div class="text-muted small">4Ps Beneficiaries</div>
                <div class="fs-4 fw-semibold"><?= (int) $town_summary->fourps_count ?></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($assigned_areas)): ?>
<div class="card shadow-sm mt-3">
    <div class="card-header bg-white">
        <i class="bi bi-geo-alt-fill"></i> Your Assigned Areas
    </div>
    <div class="card-body">
        <?php if (empty($assigned_areas)): ?>
            <p class="text-muted mb-0">No areas have been assigned to your account yet. Please contact your administrator.</p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($assigned_areas as $area): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= html_escape($area->area_label) ?>
                        <span class="badge bg-secondary text-uppercase"><?= html_escape($area->scope_type) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
