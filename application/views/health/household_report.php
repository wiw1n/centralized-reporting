<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h3 class="mb-0"><i class="bi bi-house-heart-fill"></i> Household Report</h3>
        <p class="text-muted mb-0">BHW Family Profiling Form &mdash; household roster with health &amp; pregnancy details.</p>
    </div>
    <?php if (!empty($households)): ?>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    <?php endif; ?>
</div>

<div class="card shadow-sm mb-3 no-print">
    <div class="card-body">
        <h6 class="mb-3">Barangay</h6>
        <?php if ($locked_barangay): ?>
            <span class="badge bg-light text-dark border">
                <i class="bi bi-geo-alt-fill"></i>
                <?= $locked_barangay ? html_escape('Barangay: ' . $locked_barangay->name) : 'No barangay assigned' ?>
            </span>
        <?php elseif ($restricted_municipality_id !== null): ?>
            <form method="get" action="<?= base_url('health/household_report') ?>" class="row g-2">
                <div class="col-auto">
                    <select name="barangay_id" class="form-select">
                        <option value="">-- All Barangays --</option>
                        <?php foreach ($barangays as $b): ?>
                            <option value="<?= $b->id ?>" <?= (!empty($barangay) && (int) $barangay->id === (int) $b->id) ? 'selected' : '' ?>><?= html_escape($b->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-play-fill"></i> Generate</button>
                </div>
            </form>
        <?php else: ?>
            <form method="get" action="<?= base_url('health/household_report') ?>" class="row g-2">
                <div class="col-auto">
                    <select name="region_id" id="hh_region_id" class="form-select" data-cascade-target="hh_province_id" data-cascade-url="<?= base_url('provinces/by_region/') ?>" data-placeholder="-- Select Province --">
                        <option value="">-- Select Region --</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= $region->id ?>" <?= ((int) $selected_region_id === (int) $region->id) ? 'selected' : '' ?>><?= html_escape($region->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="province_id" id="hh_province_id" class="form-select" data-cascade-target="hh_municipality_id" data-cascade-url="<?= base_url('municipalities/by_province/') ?>" data-placeholder="-- Select Municipality --">
                        <option value="">-- Select Province --</option>
                        <?php foreach ($provinces as $province_opt): ?>
                            <option value="<?= $province_opt->id ?>" <?= ((int) $selected_province_id === (int) $province_opt->id) ? 'selected' : '' ?>><?= html_escape($province_opt->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="municipality_id" id="hh_municipality_id" class="form-select" data-cascade-target="hh_barangay_id" data-cascade-url="<?= base_url('barangays/by_municipality/') ?>" data-placeholder="-- All Barangays --">
                        <option value="">-- Select Municipality --</option>
                        <?php foreach ($municipalities as $municipality_opt): ?>
                            <option value="<?= $municipality_opt->id ?>" <?= ((int) $selected_municipality_id === (int) $municipality_opt->id) ? 'selected' : '' ?>><?= html_escape($municipality_opt->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="barangay_id" id="hh_barangay_id" class="form-select">
                        <option value="">-- All Barangays --</option>
                        <?php foreach ($barangays as $b): ?>
                            <option value="<?= $b->id ?>" <?= (!empty($barangay) && (int) $barangay->id === (int) $b->id) ? 'selected' : '' ?>><?= html_escape($b->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-play-fill"></i> Generate</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!$municipality): ?>
    <div class="alert alert-info no-print">
        <?= $locked_barangay === null && $restricted_barangay_id !== null
            ? 'No barangay assigned &mdash; contact an administrator.'
            : 'Select a municipality and barangay above to generate the Household Report.' ?>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="text-center mb-3">
                <div class="fw-semibold">BHW Family Profiling Form</div>
                <div class="small text-muted">Household Report</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4"><strong>Barangay:</strong> <?= $barangay ? html_escape($barangay->name) : 'All Barangays' ?></div>
                <div class="col-md-4"><strong>City/Municipality:</strong> <?= html_escape($municipality->name ?? '') ?></div>
                <div class="col-md-4"><strong>Province:</strong> <?= html_escape($province->name ?? '') ?></div>
            </div>

            <?php if (empty($households)): ?>
                <div class="alert alert-info mb-0">No households recorded for this <?= $barangay ? 'barangay' : 'municipality' ?> yet.</div>
            <?php else: ?>
                <div class="row mb-3 no-print">
                    <div class="col-md-5">
                        <input type="text" id="hh_search" class="form-control" placeholder="Search household no., name, occupation, etc.">
                    </div>
                </div>
                <div class="alert alert-info no-print d-none" id="hh_search_empty">No households match your search.</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center" id="household_report_table" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <?php if ($show_barangay_column): ?>
                                    <th rowspan="2" class="align-middle">Barangay</th>
                                <?php endif; ?>
                                <th rowspan="2" class="align-middle">HH No.</th>
                                <th rowspan="2" class="align-middle">H.H. Head's Name</th>
                                <th rowspan="2" class="align-middle">H.H. Members Name</th>
                                <th rowspan="2" class="align-middle">Sex</th>
                                <th rowspan="2" class="align-middle">Ord. Position</th>
                                <th rowspan="2" class="align-middle">DOB</th>
                                <th rowspan="2" class="align-middle">Age</th>
                                <th rowspan="2" class="align-middle">Educ. Attainment</th>
                                <th rowspan="2" class="align-middle">Occup./Income</th>
                                <th rowspan="2" class="align-middle">Religion</th>
                                <th colspan="4">Present Illness</th>
                                <th colspan="4">Pregnant</th>
                                <th rowspan="2" class="align-middle">TT Status</th>
                            </tr>
                            <tr>
                                <th>HPN</th>
                                <th>DM</th>
                                <th>Asthma</th>
                                <th>Etc.</th>
                                <th>G</th>
                                <th>P</th>
                                <th>LMP</th>
                                <th>EDC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($households as $h_index => $entry): ?>
                                <?php
                                    $members = $entry->members;
                                    $row_count = count($members) ?: 1;
                                    $head = null;
                                    foreach ($members as $m) {
                                        if ($m->relationship_to_head === 'Head') {
                                            $head = $m;
                                            break;
                                        }
                                    }
                                ?>
                                <?php if (empty($members)): ?>
                                    <tr data-hh-group="<?= $h_index ?>">
                                        <?php if ($show_barangay_column): ?>
                                            <td><?= html_escape($entry->barangay_name ?? '') ?></td>
                                        <?php endif; ?>
                                        <td><?= html_escape($entry->household_no) ?></td>
                                        <td class="text-muted">&mdash;</td>
                                        <td colspan="17" class="text-muted">No members recorded</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($members as $i => $member): ?>
                                        <tr data-hh-group="<?= $h_index ?>">
                                            <?php if ($i === 0): ?>
                                                <?php if ($show_barangay_column): ?>
                                                    <td rowspan="<?= $row_count ?>"><?= html_escape($entry->barangay_name ?? '') ?></td>
                                                <?php endif; ?>
                                                <td rowspan="<?= $row_count ?>"><?= html_escape($entry->household_no) ?></td>
                                                <td rowspan="<?= $row_count ?>"><?= $head ? html_escape($head->last_name . ', ' . $head->first_name) : '<span class="text-muted">&mdash;</span>' ?></td>
                                            <?php endif; ?>
                                            <td class="text-start"><?= html_escape($member->last_name . ', ' . $member->first_name . ($member->middle_name ? ' ' . $member->middle_name : '')) ?></td>
                                            <td><?= html_escape($member->sex) ?></td>
                                            <td><?= $member->ordinal_position !== null ? (int) $member->ordinal_position : ($i + 1) ?></td>
                                            <td><?= !empty($member->birthdate) ? date('m/d/Y', strtotime($member->birthdate)) : '' ?></td>
                                            <td><?= !empty($member->birthdate) ? (new DateTime($member->birthdate))->diff(new DateTime())->y : '' ?></td>
                                            <td class="text-start"><?= html_escape($member->educational_attainment ?? '') ?></td>
                                            <td class="text-start"><?= html_escape($member->occupation ?? '') ?></td>
                                            <td><?= html_escape($member->religion ?? '') ?></td>
                                            <td><?= !empty($member->has_hypertension) ? '&check;' : '' ?></td>
                                            <td><?= !empty($member->has_diabetes) ? '&check;' : '' ?></td>
                                            <td><?= !empty($member->has_asthma) ? '&check;' : '' ?></td>
                                            <td><?= html_escape($member->other_illness ?? '') ?></td>
                                            <td><?= $member->gravida !== null ? (int) $member->gravida : '' ?></td>
                                            <td><?= $member->para !== null ? (int) $member->para : '' ?></td>
                                            <td><?= !empty($member->lmp_date) ? date('m/d/Y', strtotime($member->lmp_date)) : '' ?></td>
                                            <td><?= !empty($member->edc_date) ? date('m/d/Y', strtotime($member->edc_date)) : '' ?></td>
                                            <td><?= html_escape($member->tt_status ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
