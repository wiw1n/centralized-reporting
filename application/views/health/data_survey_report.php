<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h3 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Data Survey Tool</h3>
        <p class="text-muted mb-0">Immunization, COVID-19 vaccine, Schisto MDA, food intake, exercise, and recreational status per resident.</p>
    </div>
    <?php if (!empty($survey_rows)): ?>
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
            <form method="get" action="<?= base_url('health/data_survey_report') ?>" class="row g-2">
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
            <form method="get" action="<?= base_url('health/data_survey_report') ?>" class="row g-2">
                <div class="col-auto">
                    <select name="region_id" id="ds_region_id" class="form-select" data-cascade-target="ds_province_id" data-cascade-url="<?= base_url('provinces/by_region/') ?>" data-placeholder="-- Select Province --">
                        <option value="">-- Select Region --</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= $region->id ?>" <?= ((int) $selected_region_id === (int) $region->id) ? 'selected' : '' ?>><?= html_escape($region->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="province_id" id="ds_province_id" class="form-select" data-cascade-target="ds_municipality_id" data-cascade-url="<?= base_url('municipalities/by_province/') ?>" data-placeholder="-- Select Municipality --">
                        <option value="">-- Select Province --</option>
                        <?php foreach ($provinces as $province_opt): ?>
                            <option value="<?= $province_opt->id ?>" <?= ((int) $selected_province_id === (int) $province_opt->id) ? 'selected' : '' ?>><?= html_escape($province_opt->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="municipality_id" id="ds_municipality_id" class="form-select" data-cascade-target="ds_barangay_id" data-cascade-url="<?= base_url('barangays/by_municipality/') ?>" data-placeholder="-- All Barangays --">
                        <option value="">-- Select Municipality --</option>
                        <?php foreach ($municipalities as $municipality_opt): ?>
                            <option value="<?= $municipality_opt->id ?>" <?= ((int) $selected_municipality_id === (int) $municipality_opt->id) ? 'selected' : '' ?>><?= html_escape($municipality_opt->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="barangay_id" id="ds_barangay_id" class="form-select">
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
            : 'Select a municipality and barangay above to generate the Data Survey Tool report.' ?>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="text-center mb-3">
                <div class="fw-semibold">Data Survey Tool</div>
                <div class="small text-muted">Optional &mdash; Immunization, COVID-19 Immun., Schisto MDA, Food Intake, Exercise &amp; Recreational Status</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4"><strong>Barangay:</strong> <?= $barangay ? html_escape($barangay->name) : 'All Barangays' ?></div>
                <div class="col-md-4"><strong>City/Municipality:</strong> <?= html_escape($municipality->name ?? '') ?></div>
                <div class="col-md-4"><strong>Province:</strong> <?= html_escape($province->name ?? '') ?></div>
            </div>

            <?php if (empty($survey_rows)): ?>
                <div class="alert alert-info mb-0">No residents recorded for this <?= $barangay ? 'barangay' : 'municipality' ?> yet.</div>
            <?php else: ?>
                <div class="row mb-3 no-print">
                    <div class="col-md-5">
                        <input type="text" id="ds_search" class="form-control" placeholder="Search resident name...">
                    </div>
                </div>
                <div class="alert alert-info no-print d-none" id="ds_search_empty">No residents match your search.</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center" id="data_survey_report_table" style="font-size: 0.78rem;">
                        <thead class="table-light">
                            <tr>
                                <?php if ($show_barangay_column): ?>
                                    <th rowspan="2" class="align-middle">Barangay</th>
                                <?php endif; ?>
                                <th rowspan="2" class="align-middle">Name</th>
                                <th rowspan="2" class="align-middle">Sex</th>
                                <th rowspan="2" class="align-middle">Age</th>
                                <th colspan="3">Immun. Status</th>
                                <th colspan="6">COVID-19 Immun. (No. of Doses)</th>
                                <th colspan="3">Schisto MDA Status</th>
                                <th colspan="3">Usual Daily Food Intake</th>
                                <th colspan="3">Exercise</th>
                                <th colspan="2">Recreational</th>
                            </tr>
                            <tr>
                                <th>FIC</th>
                                <th>INC.</th>
                                <th>No Immun.</th>
                                <th>1st Dose</th>
                                <th>2nd Dose</th>
                                <th>Booster 1</th>
                                <th>Booster 2</th>
                                <th>Booster 3</th>
                                <th>None</th>
                                <th>Y</th>
                                <th>N</th>
                                <th>Date of Tx</th>
                                <th>B</th>
                                <th>L</th>
                                <th>S</th>
                                <th>Y</th>
                                <th>N</th>
                                <th>Freq</th>
                                <th>Y</th>
                                <th>N</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($survey_rows as $row): ?>
                                <tr>
                                    <?php if ($show_barangay_column): ?>
                                        <td><?= html_escape($row->barangay_name ?? '') ?></td>
                                    <?php endif; ?>
                                    <td class="text-start"><?= html_escape($row->last_name . ', ' . $row->first_name . ($row->middle_name ? ' ' . $row->middle_name : '')) ?></td>
                                    <td><?= html_escape($row->sex) ?></td>
                                    <td><?= !empty($row->birthdate) ? (new DateTime($row->birthdate))->diff(new DateTime())->y : '' ?></td>
                                    <td><?= $row->immunization_status === 'FIC' ? '&check;' : '' ?></td>
                                    <td><?= $row->immunization_status === 'INC' ? '&check;' : '' ?></td>
                                    <td><?= $row->immunization_status === 'No Immunization' ? '&check;' : '' ?></td>
                                    <td><?= $row->covid_vaccine_status === '1st Dose' ? '&check;' : '' ?></td>
                                    <td><?= $row->covid_vaccine_status === '2nd Dose' ? '&check;' : '' ?></td>
                                    <td><?= $row->covid_vaccine_status === 'Booster 1' ? '&check;' : '' ?></td>
                                    <td><?= $row->covid_vaccine_status === 'Booster 2' ? '&check;' : '' ?></td>
                                    <td><?= $row->covid_vaccine_status === 'Booster 3' ? '&check;' : '' ?></td>
                                    <td><?= $row->covid_vaccine_status === 'None' ? '&check;' : '' ?></td>
                                    <td><?= $row->schisto_mda_status !== null && (int) $row->schisto_mda_status === 1 ? '&check;' : '' ?></td>
                                    <td><?= $row->schisto_mda_status !== null && (int) $row->schisto_mda_status === 0 ? '&check;' : '' ?></td>
                                    <td><?= !empty($row->schisto_mda_date) ? date('m/d/Y', strtotime($row->schisto_mda_date)) : '' ?></td>
                                    <td><?= !empty($row->eats_breakfast) ? '&check;' : '' ?></td>
                                    <td><?= !empty($row->eats_lunch) ? '&check;' : '' ?></td>
                                    <td><?= !empty($row->eats_snacks) ? '&check;' : '' ?></td>
                                    <td><?= $row->exercises !== null && (int) $row->exercises === 1 ? '&check;' : '' ?></td>
                                    <td><?= $row->exercises !== null && (int) $row->exercises === 0 ? '&check;' : '' ?></td>
                                    <td><?= html_escape($row->exercise_frequency ?? '') ?></td>
                                    <td><?= $row->has_recreational_activity !== null && (int) $row->has_recreational_activity === 1 ? '&check;' : '' ?></td>
                                    <td><?= $row->has_recreational_activity !== null && (int) $row->has_recreational_activity === 0 ? '&check;' : '' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
