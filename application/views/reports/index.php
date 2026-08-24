<?php
/**
 * Every checkbox/filter control below is generated straight from
 * application/config/report_fields.php -- adding a field to that registry
 * (e.g. when a new column is added to `residents`) makes it show up here,
 * in the CSV export, and in saved templates automatically. Nothing in this
 * view needs to change for that.
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h3 class="mb-0"><i class="bi bi-file-earmark-bar-graph-fill"></i> Reports</h3>
        <p class="text-muted mb-0">Build a custom resident report: pick the columns and filters you need, then preview, print, or export it.</p>
    </div>
</div>

<?= form_open(current_url(), ['id' => 'report_form']) ?>

<div class="card shadow-sm mb-3 no-print">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-bookmark-star"></i> Saved Reports</span>
        <div class="d-flex align-items-center gap-2">
            <select id="template_select" class="form-select form-select-sm" style="min-width: 220px;">
                <option value="">-- Load a saved report --</option>
                <?php foreach ($templates as $template): ?>
                    <option value="<?= $template->id ?>"><?= html_escape($template->name) ?></option>
                <?php endforeach; ?>
            </select>
            <a href="#" id="delete_template_btn" class="btn btn-sm btn-outline-danger d-none" title="Delete selected report">
                <i class="bi bi-trash"></i>
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3 no-print">
    <div class="card-body">
        <h6 class="mb-3">Location</h6>
        <?php if ($restricted_barangay_id !== null): ?>
            <span class="badge bg-light text-dark border">
                <i class="bi bi-geo-alt-fill"></i>
                <?= $locked_barangay ? html_escape('Barangay: ' . $locked_barangay->name) : 'No barangay assigned' ?>
            </span>
        <?php elseif ($restricted_municipality_id !== null): ?>
            <div class="row g-2">
                <div class="col-auto">
                    <select name="barangay_id" id="report_barangay_id" class="form-select">
                        <option value="">All Barangays</option>
                        <?php foreach ($barangays as $barangay): ?>
                            <option value="<?= $barangay->id ?>"><?= html_escape($barangay->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-2">
                <div class="col-auto">
                    <select name="region_id" id="report_region_id" class="form-select" data-cascade-target="report_province_id" data-cascade-url="<?= base_url('provinces/by_region/') ?>" data-placeholder="All Provinces">
                        <option value="">All Regions</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= $region->id ?>"><?= html_escape($region->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="province_id" id="report_province_id" class="form-select" data-cascade-target="report_municipality_id" data-cascade-url="<?= base_url('municipalities/by_province/') ?>" data-placeholder="All Municipalities">
                        <option value="">All Provinces</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="municipality_id" id="report_municipality_id" class="form-select" data-cascade-target="report_barangay_id" data-cascade-url="<?= base_url('barangays/by_municipality/') ?>" data-placeholder="All Barangays">
                        <option value="">All Municipalities</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="barangay_id" id="report_barangay_id" class="form-select">
                        <option value="">All Barangays</option>
                    </select>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row no-print">
    <div class="col-lg-5 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><i class="bi bi-funnel-fill"></i> Filters <small class="text-muted fw-normal">(only residents matching every filter below are included)</small></div>
            <div class="card-body" style="max-height: 520px; overflow-y: auto;">
                <?php $has_filter = false; ?>
                <?php foreach ($field_groups as $group): ?>
                    <?php $filterable_fields = array_filter($group['fields'], function ($f) { return !empty($f['filterable']); }); ?>
                    <?php if (empty($filterable_fields)) continue; ?>
                    <?php $has_filter = true; ?>
                    <div class="mb-3">
                        <div class="fw-semibold small text-uppercase text-muted mb-2"><?= html_escape($group['label']) ?></div>
                        <?php foreach ($filterable_fields as $key => $field): ?>
                            <div class="mb-3">
                                <label class="form-label small mb-1"><?= html_escape($field['label']) ?></label>
                                <?php if ($field['type'] === 'enum'): ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($field['options'] as $opt): ?>
                                            <div class="form-check form-check-inline m-0">
                                                <input type="checkbox" class="form-check-input" name="filters[<?= $key ?>][]" value="<?= html_escape($opt) ?>" id="filter_<?= $key ?>_<?= md5($opt) ?>">
                                                <label class="form-check-label small" for="filter_<?= $key ?>_<?= md5($opt) ?>"><?= html_escape($opt) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php elseif ($field['type'] === 'boolean'): ?>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="filters[<?= $key ?>]" value="1" id="filter_<?= $key ?>">
                                        <label class="form-check-label small" for="filter_<?= $key ?>">Only show "<?= html_escape($field['label']) ?>" residents</label>
                                    </div>
                                <?php elseif ($field['type'] === 'number'): ?>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="number" step="any" class="form-control form-control-sm" name="filters[<?= $key ?>][min]" placeholder="Min">
                                        </div>
                                        <div class="col-6">
                                            <input type="number" step="any" class="form-control form-control-sm" name="filters[<?= $key ?>][max]" placeholder="Max">
                                        </div>
                                    </div>
                                <?php elseif ($field['type'] === 'date'): ?>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="date" class="form-control form-control-sm" name="filters[<?= $key ?>][from]">
                                        </div>
                                        <div class="col-6">
                                            <input type="date" class="form-control form-control-sm" name="filters[<?= $key ?>][to]">
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <input type="text" class="form-control form-control-sm" name="filters[<?= $key ?>][contains]" placeholder="Contains...">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$has_filter): ?>
                    <p class="text-muted small mb-0">No filterable fields configured.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><i class="bi bi-ui-checks"></i> Columns to Include</div>
            <div class="card-body" style="max-height: 520px; overflow-y: auto;">
                <?php foreach ($field_groups as $group_key => $group): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold small text-uppercase text-muted"><?= html_escape($group['label']) ?></span>
                            <span>
                                <a href="#" class="small field-group-toggle" data-group="<?= $group_key ?>" data-check="1">All</a>
                                &middot;
                                <a href="#" class="small field-group-toggle" data-group="<?= $group_key ?>" data-check="0">None</a>
                            </span>
                        </div>
                        <div class="row" data-field-group="<?= $group_key ?>">
                            <?php foreach ($group['fields'] as $key => $field): ?>
                                <div class="col-md-6 form-check mb-2">
                                    <input type="checkbox" class="form-check-input" name="fields[]" value="<?= $key ?>" id="field_<?= $key ?>" <?= !empty($field['default']) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="field_<?= $key ?>"><?= html_escape($field['label']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3 no-print">
    <button type="button" id="generate_report_btn" class="btn btn-primary"><i class="bi bi-play-fill"></i> Generate Report</button>
    <button type="button" id="save_template_btn" class="btn btn-outline-primary"><i class="bi bi-bookmark-plus"></i> Save as Report</button>
    <button type="button" id="export_csv_btn" class="btn btn-outline-success"><i class="bi bi-filetype-csv"></i> Export CSV</button>
    <button type="button" id="print_report_btn" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
    <button type="reset" class="btn btn-outline-secondary ms-auto"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
</div>

<?= form_close() ?>

<div id="report_results_card" class="card shadow-sm d-none">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table"></i> Results</span>
        <span id="report_results_summary" class="text-muted small"></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle" id="report_results_table">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade no-print" id="save_template_modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Report Name</label>
                <input type="text" id="save_template_name" class="form-control" maxlength="150" placeholder="e.g. Senior Citizens per Barangay">
                <div id="save_template_error" class="text-danger small mt-2 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="save_template_confirm_btn" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
