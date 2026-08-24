<?php
$civil_status_options = Resident_model::CIVIL_STATUS_OPTIONS;
$education_options = Resident_model::EDUCATION_OPTIONS;
$blood_type_options = Resident_model::BLOOD_TYPE_OPTIONS;
$relationship_options = Resident_household_model::RELATIONSHIP_OPTIONS;
$tt_status_options = Resident_household_model::TT_STATUS_OPTIONS;
$weight_age_options = Resident_household_model::NUTRITIONAL_STATUS_WEIGHT_AGE_OPTIONS;
$height_age_options = Resident_household_model::NUTRITIONAL_STATUS_HEIGHT_AGE_OPTIONS;
$weight_height_options = Resident_household_model::NUTRITIONAL_STATUS_WEIGHT_HEIGHT_OPTIONS;
$school_level_options = Resident_household_model::SCHOOL_LEVEL_OPTIONS;
$school_type_options = Resident_household_model::SCHOOL_TYPE_OPTIONS;
$school_nutrition_options = Resident_household_model::SCHOOL_NUTRITIONAL_STATUS_OPTIONS;
$r = $resident;
$hh = $resident_household ?? null;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-person-vcard-fill"></i> <?= html_escape($page_title) ?></h3>
    <a href="<?= base_url('residents') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm form-section-nav-card" id="form_section_nav">
            <div class="card-body p-2">
                <div class="small text-uppercase text-muted fw-semibold px-2 pt-1 pb-2">Sections</div>
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action" href="#section-location"><i class="bi bi-geo-alt-fill"></i> Location</a>
                    <a class="list-group-item list-group-item-action" href="#section-personal"><i class="bi bi-person-badge-fill"></i> Personal Information</a>
                    <a class="list-group-item list-group-item-action" href="#section-contact"><i class="bi bi-telephone-fill"></i> Contact &amp; Address</a>
                    <a class="list-group-item list-group-item-action" href="#section-occupation"><i class="bi bi-briefcase-fill"></i> Occupation &amp; Education</a>
                    <a class="list-group-item list-group-item-action" href="#section-gov-ids"><i class="bi bi-postcard-fill"></i> Government IDs</a>
                    <a class="list-group-item list-group-item-action" href="#section-program-flags"><i class="bi bi-flag-fill"></i> Program Flags</a>
                    <a class="list-group-item list-group-item-action" href="#section-household"><i class="bi bi-house-heart-fill"></i> Household Information</a>
                    <a class="list-group-item list-group-item-action" href="#section-remarks"><i class="bi bi-chat-left-text-fill"></i> Remarks</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?= form_open(current_url()) ?>

            <?php if ($r): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label">ID</label>
                            <input type="text" class="form-control" value="<?= html_escape($r->id) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Resident No.</label>
                            <input type="text" class="form-control" value="<?= html_escape($r->resident_no) ?>" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div id="section-location" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-geo-alt-fill"></i> Location</h5></div>
                <div class="card-body">
                    <?php if ($locked_barangay): ?>
                    <div class="mb-3">
                        <label class="form-label">Barangay</label>
                        <input type="text" class="form-control" value="<?= html_escape($locked_barangay->name) ?>" disabled>
                    </div>
                    <?php elseif ($locked_municipality): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Municipality / City</label>
                            <input type="text" class="form-control" value="<?= html_escape($locked_municipality->name) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Barangay</label>
                            <select name="barangay_id" class="form-select" required>
                                <option value="">-- Select Barangay --</option>
                                <?php foreach ($barangays as $barangay): ?>
                                    <option value="<?= $barangay->id ?>"
                                        <?= set_value('barangay_id', $r->barangay_id ?? '') == $barangay->id ? 'selected' : '' ?>>
                                        <?= html_escape($barangay->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Region</label>
                            <select name="region_id" id="form_region_id" class="form-select" data-cascade-target="form_province_id" data-cascade-url="<?= base_url('provinces/by_region/') ?>">
                                <option value="">-- Select Region --</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region->id ?>"
                                        <?= set_value('region_id', $current_region_id ?? '') == $region->id ? 'selected' : '' ?>>
                                        <?= html_escape($region->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Province</label>
                            <select name="province_id" id="form_province_id" class="form-select" data-cascade-target="municipality_id" data-cascade-url="<?= base_url('municipalities/by_province/') ?>" data-placeholder="-- Select Province --" required>
                                <option value="">-- Select Province --</option>
                                <?php foreach ($provinces as $province): ?>
                                    <option value="<?= $province->id ?>"
                                        <?= set_value('province_id', $current_province_id ?? '') == $province->id ? 'selected' : '' ?>>
                                        <?= html_escape($province->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Municipality / City</label>
                            <select name="municipality_id" id="municipality_id" class="form-select" data-cascade-target="barangay_id" data-cascade-url="<?= base_url('barangays/by_municipality/') ?>" data-placeholder="-- Select Municipality --" required>
                                <option value="">-- Select Municipality --</option>
                                <?php foreach ($municipalities as $municipality): ?>
                                    <option value="<?= $municipality->id ?>"
                                        <?= set_value('municipality_id', $current_municipality_id ?? '') == $municipality->id ? 'selected' : '' ?>>
                                        <?= html_escape($municipality->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Barangay</label>
                            <select name="barangay_id" id="barangay_id" class="form-select" data-placeholder="-- Select Barangay --" required>
                                <option value="">-- Select Barangay --</option>
                                <?php foreach ($barangays as $barangay): ?>
                                    <option value="<?= $barangay->id ?>"
                                        <?= set_value('barangay_id', $r->barangay_id ?? '') == $barangay->id ? 'selected' : '' ?>>
                                        <?= html_escape($barangay->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="section-personal" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-person-badge-fill"></i> Personal Information</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" maxlength="100" value="<?= set_value('last_name', $r->last_name ?? '') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" maxlength="100" value="<?= set_value('first_name', $r->first_name ?? '') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" maxlength="100" value="<?= set_value('middle_name', $r->middle_name ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Suffix</label>
                            <input type="text" name="suffix" class="form-control" maxlength="20" value="<?= set_value('suffix', $r->suffix ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sex</label>
                            <select name="sex" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option value="Male" <?= set_value('sex', $r->sex ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= set_value('sex', $r->sex ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Birthdate</label>
                            <input type="date" name="birthdate" id="resident_birthdate" class="form-control" value="<?= set_value('birthdate', $r->birthdate ?? '') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Birthplace</label>
                            <input type="text" name="birthplace" class="form-control" maxlength="150" value="<?= set_value('birthplace', $r->birthplace ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Civil Status</label>
                            <select name="civil_status" class="form-select" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($civil_status_options as $opt): ?>
                                    <option value="<?= html_escape($opt) ?>" <?= set_value('civil_status', $r->civil_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Religion</label>
                            <input type="text" name="religion" class="form-control" maxlength="50" value="<?= set_value('religion', $r->religion ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Citizenship</label>
                            <input type="text" name="citizenship" class="form-control" maxlength="50" value="<?= set_value('citizenship', $r->citizenship ?? 'Filipino') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Blood Type</label>
                            <select name="blood_type" class="form-select">
                                <option value="">-- Select --</option>
                                <?php foreach ($blood_type_options as $opt): ?>
                                    <option value="<?= html_escape($opt) ?>" <?= set_value('blood_type', $r->blood_type ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-contact" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-telephone-fill"></i> Contact &amp; Address</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Purok / Sitio</label>
                            <input type="text" name="purok_sitio" class="form-control" maxlength="100" value="<?= set_value('purok_sitio', $r->purok_sitio ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Address Line</label>
                            <input type="text" name="address_line" class="form-control" maxlength="150" value="<?= set_value('address_line', $r->address_line ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" maxlength="20" value="<?= set_value('contact_number', $r->contact_number ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" maxlength="100" value="<?= set_value('email', $r->email ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-occupation" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-briefcase-fill"></i> Occupation &amp; Education</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Occupation</label>
                            <input type="text" name="occupation" class="form-control" maxlength="150" value="<?= set_value('occupation', $r->occupation ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Employer</label>
                            <input type="text" name="employer" class="form-control" maxlength="150" value="<?= set_value('employer', $r->employer ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Monthly Income</label>
                            <input type="number" step="0.01" name="monthly_income" class="form-control" value="<?= set_value('monthly_income', $r->monthly_income ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Educational Attainment</label>
                            <select name="educational_attainment" class="form-select">
                                <option value="">-- Select --</option>
                                <?php foreach ($education_options as $opt): ?>
                                    <option value="<?= html_escape($opt) ?>" <?= set_value('educational_attainment', $r->educational_attainment ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-gov-ids" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-postcard-fill"></i> Government IDs <small class="text-muted">(optional)</small></h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">National ID (PhilSys) No.</label>
                            <input type="text" name="national_id_no" class="form-control" maxlength="50" value="<?= set_value('national_id_no', $r->national_id_no ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Voter's ID No.</label>
                            <input type="text" name="voters_id_no" class="form-control" maxlength="50" value="<?= set_value('voters_id_no', $r->voters_id_no ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">SSS No.</label>
                            <input type="text" name="sss_no" class="form-control" maxlength="50" value="<?= set_value('sss_no', $r->sss_no ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">GSIS No.</label>
                            <input type="text" name="gsis_no" class="form-control" maxlength="50" value="<?= set_value('gsis_no', $r->gsis_no ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pag-IBIG No.</label>
                            <input type="text" name="pagibig_no" class="form-control" maxlength="50" value="<?= set_value('pagibig_no', $r->pagibig_no ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">PhilHealth No.</label>
                            <input type="text" name="philhealth_no" class="form-control" maxlength="50" value="<?= set_value('philhealth_no', $r->philhealth_no ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">TIN No.</label>
                            <input type="text" name="tin_no" class="form-control" maxlength="50" value="<?= set_value('tin_no', $r->tin_no ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-program-flags" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-flag-fill"></i> Program Flags</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" name="is_pwd" value="1" class="form-check-input" id="is_pwd" <?= set_value('is_pwd', $r->is_pwd ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_pwd">PWD</label>
                        </div>
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="is_senior_display" disabled <?= !empty($r->is_senior_citizen) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_senior_display">Senior Citizen <small class="text-muted">(computed from birthdate)</small></label>
                        </div>
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" name="is_solo_parent" value="1" class="form-check-input" id="is_solo_parent" <?= set_value('is_solo_parent', $r->is_solo_parent ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_solo_parent">Solo Parent</label>
                        </div>
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" name="is_4ps_beneficiary" value="1" class="form-check-input" id="is_4ps_beneficiary" <?= set_value('is_4ps_beneficiary', $r->is_4ps_beneficiary ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_4ps_beneficiary">4Ps Beneficiary</label>
                        </div>
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" name="is_ofw" value="1" class="form-check-input" id="is_ofw" <?= set_value('is_ofw', $r->is_ofw ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_ofw">OFW</label>
                        </div>
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" name="is_indigenous" value="1" class="form-check-input" id="is_indigenous" <?= set_value('is_indigenous', $r->is_indigenous ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_indigenous">Indigenous Person</label>
                        </div>
                    </div>
                    <div class="row" id="indigenous_group_row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Indigenous Group / Tribe</label>
                            <input type="text" name="indigenous_group" class="form-control" maxlength="100" value="<?= set_value('indigenous_group', $r->indigenous_group ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-household" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-house-heart-fill"></i> Household Information</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Relationship to Head</label>
                            <select name="relationship_to_head" class="form-select">
                                <option value="">-- Select --</option>
                                <?php foreach ($relationship_options as $opt): ?>
                                    <option value="<?= html_escape($opt) ?>" <?= set_value('relationship_to_head', $hh->relationship_to_head ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ord. Position</label>
                            <input type="number" name="ordinal_position" class="form-control" min="1" max="99" value="<?= set_value('ordinal_position', $hh->ordinal_position ?? '') ?>">
                        </div>
                        <div class="col-auto form-check mt-4 mb-3">
                            <input type="checkbox" name="is_surveyed" value="1" class="form-check-input" id="is_surveyed" <?= set_value('is_surveyed', $hh->is_surveyed ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_surveyed">Covered by the Family Profile Survey</label>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Present Illness</div>
                        <div class="row align-items-end">
                            <div class="col-auto form-check">
                                <input type="checkbox" name="has_hypertension" value="1" class="form-check-input" id="has_hypertension" <?= set_value('has_hypertension', $hh->has_hypertension ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="has_hypertension">Hypertension (HPN)</label>
                            </div>
                            <div class="col-auto form-check">
                                <input type="checkbox" name="has_diabetes" value="1" class="form-check-input" id="has_diabetes" <?= set_value('has_diabetes', $hh->has_diabetes ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="has_diabetes">Diabetes Mellitus (DM)</label>
                            </div>
                            <div class="col-auto form-check">
                                <input type="checkbox" name="has_asthma" value="1" class="form-check-input" id="has_asthma" <?= set_value('has_asthma', $hh->has_asthma ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="has_asthma">Asthma</label>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Other (Etc.)</label>
                                <input type="text" name="other_illness" class="form-control form-control-sm" maxlength="150" value="<?= set_value('other_illness', $hh->other_illness ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-auto form-check">
                            <input type="checkbox" name="is_pregnant" value="1" class="form-check-input" id="is_pregnant" <?= set_value('is_pregnant', $hh->is_pregnant ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_pregnant">Pregnant</label>
                        </div>
                        <div class="col-auto form-check">
                            <input type="checkbox" name="is_lactating" value="1" class="form-check-input" id="is_lactating" <?= set_value('is_lactating', $hh->is_lactating ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_lactating">Lactating</label>
                        </div>
                        <div class="col-md-1 mb-2">
                            <label class="form-label small mb-1">Gravida (G)</label>
                            <input type="number" name="gravida" class="form-control form-control-sm" min="0" max="30" value="<?= set_value('gravida', $hh->gravida ?? '') ?>">
                        </div>
                        <div class="col-md-1 mb-2">
                            <label class="form-label small mb-1">Para (P)</label>
                            <input type="number" name="para" class="form-control form-control-sm" min="0" max="30" value="<?= set_value('para', $hh->para ?? '') ?>">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label small mb-1">LMP</label>
                            <input type="date" name="lmp_date" class="form-control form-control-sm" value="<?= set_value('lmp_date', $hh->lmp_date ?? '') ?>">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label small mb-1">EDC</label>
                            <input type="date" name="edc_date" class="form-control form-control-sm" value="<?= set_value('edc_date', $hh->edc_date ?? '') ?>">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label small mb-1">TT Status</label>
                            <select name="tt_status" class="form-select form-select-sm">
                                <option value="">-- N/A --</option>
                                <?php foreach ($tt_status_options as $opt): ?>
                                    <option value="<?= html_escape($opt) ?>" <?= set_value('tt_status', $hh->tt_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Nutrition Profile &mdash; Preschool (0-59 mos.)</div>
                        <div class="row align-items-end">
                            <div class="col-auto form-check">
                                <input type="checkbox" name="opt_plus_measured" value="1" class="form-check-input" id="opt_plus_measured" <?= set_value('opt_plus_measured', $hh->opt_plus_measured ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="opt_plus_measured">Measured during OPT Plus <small class="text-muted">(preschool child, 0-59 mos.)</small></label>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Weight-for-Age</label>
                                <select name="nutritional_status_weight_age" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($weight_age_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('nutritional_status_weight_age', $hh->nutritional_status_weight_age ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Height-for-Age</label>
                                <select name="nutritional_status_height_age" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($height_age_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('nutritional_status_height_age', $hh->nutritional_status_height_age ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Weight-for-Height/Length</label>
                                <select name="nutritional_status_weight_height" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($weight_height_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('nutritional_status_weight_height', $hh->nutritional_status_weight_height ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Nutrition Profile &mdash; School (Day Care/Kinder/Grades 1-6)</div>
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">School Level</label>
                                <select name="school_level" class="form-select form-select-sm">
                                    <option value="">-- Not Enrolled --</option>
                                    <?php foreach ($school_level_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('school_level', $hh->school_level ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small mb-1">School Type</label>
                                <select name="school_type" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($school_type_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('school_type', $hh->school_type ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-auto form-check mb-2">
                                <input type="checkbox" name="school_weighed" value="1" class="form-check-input" id="school_weighed" <?= set_value('school_weighed', $hh->school_weighed ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="school_weighed">Weighed at start of school year</label>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Nutritional Status</label>
                                <select name="school_nutritional_status" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($school_nutrition_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('school_nutritional_status', $hh->school_nutritional_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-remarks" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-chat-left-text-fill"></i> Remarks</h5></div>
                <div class="card-body">
                    <textarea name="remarks" class="form-control" rows="2"><?= set_value('remarks', $r->remarks ?? '') ?></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
            </div>
        <?= form_close() ?>
    </div>
</div>
