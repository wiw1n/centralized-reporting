<?php
$civil_status_options = Resident_personal_model::CIVIL_STATUS_OPTIONS;
$education_options = Resident_work_education_model::EDUCATION_OPTIONS;
$blood_type_options = Resident_personal_model::BLOOD_TYPE_OPTIONS;
$type_of_resident_options = Resident_household_model::TYPE_OF_RESIDENT_OPTIONS;
$relationship_options = Resident_household_model::RELATIONSHIP_OPTIONS;
$tt_status_options = Resident_household_model::TT_STATUS_OPTIONS;
$school_level_options = Resident_household_model::SCHOOL_LEVEL_OPTIONS;
$school_type_options = Resident_household_model::SCHOOL_TYPE_OPTIONS;
$school_nutrition_options = Resident_household_model::SCHOOL_NUTRITIONAL_STATUS_OPTIONS;
$lifestage_nutrition_options = Resident_household_model::LIFESTAGE_NUTRITIONAL_STATUS_OPTIONS;
$wra_fp_method_options = Resident_household_model::WRA_FP_METHOD_OPTIONS;
$wra_fp_status_of_application_options = Resident_household_model::WRA_FP_STATUS_OF_APPLICATION_OPTIONS;
$child_immunization_status_options = Resident_household_model::CHILD_IMMUNIZATION_STATUS_OPTIONS;
$child_infant_feeding_options = Resident_household_model::CHILD_INFANT_FEEDING_OPTIONS;
$child_complementary_feeding_options = Resident_household_model::CHILD_COMPLEMENTARY_FEEDING_OPTIONS;
$immunization_status_options = Resident_data_survey_model::IMMUNIZATION_STATUS_OPTIONS;
$covid_vaccine_status_options = Resident_data_survey_model::COVID_VACCINE_STATUS_OPTIONS;
$r = $resident;
$personal = $resident_personal ?? null;
$contact = $resident_contact ?? null;
$work = $resident_work_education ?? null;
$govid = $resident_government_ids ?? null;
$flags = $resident_program_flags ?? null;
$rmk = $resident_remarks ?? null;
$hh = $resident_household ?? null;
$ds = $resident_data_survey ?? null;
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
                    <a class="list-group-item list-group-item-action" href="#section-personal"><i class="bi bi-person-badge-fill"></i> Personal Information</a>
                    <a class="list-group-item list-group-item-action" href="#section-location"><i class="bi bi-geo-alt-fill"></i> Location</a>
                    <a class="list-group-item list-group-item-action" href="#section-contact"><i class="bi bi-telephone-fill"></i> Contact &amp; Address</a>
                    <a class="list-group-item list-group-item-action" href="#section-occupation"><i class="bi bi-briefcase-fill"></i> Occupation &amp; Education</a>
                    <a class="list-group-item list-group-item-action" href="#section-gov-ids"><i class="bi bi-postcard-fill"></i> Government IDs</a>
                    <a class="list-group-item list-group-item-action" href="#section-program-flags"><i class="bi bi-flag-fill"></i> Program Flags</a>
                    <a class="list-group-item list-group-item-action" href="#section-household"><i class="bi bi-house-heart-fill"></i> Household Information</a>
                    <a class="list-group-item list-group-item-action" href="#section-data-survey"><i class="bi bi-clipboard2-pulse"></i> Data Survey Tool</a>
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
                            <label class="form-label">Resident ID Number</label>
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
                            <input type="text" name="birthplace" class="form-control" maxlength="150" value="<?= set_value('birthplace', $personal->birthplace ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Civil Status</label>
                            <select name="civil_status" class="form-select" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($civil_status_options as $opt): ?>
                                    <option value="<?= html_escape($opt) ?>" <?= set_value('civil_status', $personal->civil_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Religion</label>
                            <input type="text" name="religion" class="form-control" maxlength="50" value="<?= set_value('religion', $personal->religion ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Citizenship</label>
                            <input type="text" name="citizenship" class="form-control" maxlength="50" value="<?= set_value('citizenship', $personal->citizenship ?? 'Filipino') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Blood Type</label>
                            <select name="blood_type" class="form-select">
                                <option value="">-- Select --</option>
                                <?php foreach ($blood_type_options as $opt): ?>
                                    <option value="<?= html_escape($opt) ?>" <?= set_value('blood_type', $personal->blood_type ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
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
                            <input type="text" name="purok_sitio" class="form-control" maxlength="100" value="<?= set_value('purok_sitio', $contact->purok_sitio ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Address Line</label>
                            <input type="text" name="address_line" class="form-control" maxlength="150" value="<?= set_value('address_line', $contact->address_line ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" maxlength="20" value="<?= set_value('contact_number', $contact->contact_number ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" maxlength="100" value="<?= set_value('email', $contact->email ?? '') ?>">
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
                            <input type="text" name="occupation" class="form-control" maxlength="150" value="<?= set_value('occupation', $work->occupation ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Employer</label>
                            <input type="text" name="employer" class="form-control" maxlength="150" value="<?= set_value('employer', $work->employer ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Monthly Income</label>
                            <input type="number" step="0.01" name="monthly_income" class="form-control" value="<?= set_value('monthly_income', $work->monthly_income ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Educational Attainment</label>
                            <select name="educational_attainment" class="form-select">
                                <option value="">-- Select --</option>
                                <?php foreach ($education_options as $opt): ?>
                                    <option value="<?= html_escape($opt) ?>" <?= set_value('educational_attainment', $work->educational_attainment ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
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
                            <input type="text" name="national_id_no" class="form-control" maxlength="50" value="<?= set_value('national_id_no', $govid->national_id_no ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Voter's ID No.</label>
                            <input type="text" name="voters_id_no" class="form-control" maxlength="50" value="<?= set_value('voters_id_no', $govid->voters_id_no ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">SSS No.</label>
                            <input type="text" name="sss_no" class="form-control" maxlength="50" value="<?= set_value('sss_no', $govid->sss_no ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">GSIS No.</label>
                            <input type="text" name="gsis_no" class="form-control" maxlength="50" value="<?= set_value('gsis_no', $govid->gsis_no ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Pag-IBIG No.</label>
                            <input type="text" name="pagibig_no" class="form-control" maxlength="50" value="<?= set_value('pagibig_no', $govid->pagibig_no ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">PhilHealth No.</label>
                            <input type="text" name="philhealth_no" class="form-control" maxlength="50" value="<?= set_value('philhealth_no', $govid->philhealth_no ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">TIN No.</label>
                            <input type="text" name="tin_no" class="form-control" maxlength="50" value="<?= set_value('tin_no', $govid->tin_no ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Yakap No.</label>
                            <input type="text" name="yakap_no" class="form-control" maxlength="50" value="<?= set_value('yakap_no', $govid->yakap_no ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-program-flags" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-flag-fill"></i> Program Flags</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" name="is_pwd" value="1" class="form-check-input" id="is_pwd" <?= set_value('is_pwd', $flags->is_pwd ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_pwd">PWD</label>
                        </div>
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="is_senior_display" disabled <?= !empty($flags->is_senior_citizen) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_senior_display">Senior Citizen <small class="text-muted">(computed from birthdate)</small></label>
                        </div>
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" name="is_solo_parent" value="1" class="form-check-input" id="is_solo_parent" <?= set_value('is_solo_parent', $flags->is_solo_parent ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_solo_parent">Solo Parent</label>
                        </div>
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" name="is_4ps_beneficiary" value="1" class="form-check-input" id="is_4ps_beneficiary" <?= set_value('is_4ps_beneficiary', ($flags->is_4ps_beneficiary ?? null) !== null ? '1' : '0') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_4ps_beneficiary">4Ps Beneficiary</label>
                        </div>
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" name="is_ofw" value="1" class="form-check-input" id="is_ofw" <?= set_value('is_ofw', $flags->is_ofw ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_ofw">OFW</label>
                        </div>
                        <div class="col-auto form-check mb-2">
                            <input type="checkbox" name="is_indigenous" value="1" class="form-check-input" id="is_indigenous" <?= set_value('is_indigenous', $flags->is_indigenous ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_indigenous">Indigenous Person</label>
                        </div>
                    </div>
                    <div class="row" id="fourps_id_row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">4Ps ID Number</label>
                            <input type="text" name="fourps_id_number" class="form-control" maxlength="50" value="<?= set_value('fourps_id_number', $flags->is_4ps_beneficiary ?? '') ?>">
                        </div>
                    </div>
                    <div class="row" id="indigenous_group_row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Indigenous Group / Tribe</label>
                            <input type="text" name="indigenous_group" class="form-control" maxlength="100" value="<?= set_value('indigenous_group', $flags->indigenous_group ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-household" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-house-heart-fill"></i> Household Information</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Type of Resident</label>
                            <select name="type_of_resident" class="form-select">
                                <option value="">-- Select --</option>
                                <?php foreach ($type_of_resident_options as $opt): ?>
                                    <option value="<?= html_escape($opt) ?>" <?= set_value('type_of_resident', $hh->type_of_resident ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Household No.</label>
                            <input type="text" name="household_no" class="form-control" maxlength="30" value="<?= set_value('household_no', $hh->household_no ?? '') ?>" placeholder="e.g. HH-B1-0001">
                        </div>
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
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Medical History</div>
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
                    
                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Child</div>
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">School Level <span class="text-muted">(Day Care/Kinder/Grades 1-6)</span></label>
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
                                <label class="form-label small mb-1">Nutritional Status <span class="text-muted">(School)</span></label>
                                <select name="school_nutritional_status" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($school_nutrition_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('school_nutritional_status', $hh->school_nutritional_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Immunization Status</label>
                                <select name="child_immunization_status" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($child_immunization_status_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('child_immunization_status', $hh->child_immunization_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small mb-1">Newborn Screening</label>
                                <select name="child_newborn_screening" id="child_newborn_screening" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <option value="Yes" <?= set_value('child_newborn_screening', $hh->child_newborn_screening ?? '') === 'Yes' ? 'selected' : '' ?>>Yes</option>
                                    <option value="No" <?= set_value('child_newborn_screening', $hh->child_newborn_screening ?? '') === 'No' ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2" id="child_newborn_screening_result_row">
                                <label class="form-label small mb-1">Newborn Screening Result <span class="text-muted">(if Yes)</span></label>
                                <input type="text" name="child_newborn_screening_result" class="form-control form-control-sm" maxlength="255" value="<?= set_value('child_newborn_screening_result', $hh->child_newborn_screening_result ?? '') ?>">
                            </div>
                        </div>
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-2">
                                <label class="form-label small mb-1">Infant Feeding <span class="text-muted">(&lt; 6 mos.)</span></label>
                                <select name="child_infant_feeding" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($child_infant_feeding_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('child_infant_feeding', $hh->child_infant_feeding ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label small mb-1">Complementary Feeding <span class="text-muted">(6 mos. - 2 y/o)</span></label>
                                <select name="child_complementary_feeding" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($child_complementary_feeding_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('child_complementary_feeding', $hh->child_complementary_feeding ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row align-items-end">
                            <div class="col-12 mb-2">
                                <label class="form-label small mb-1">Micronutrient Supplementation / Deworming</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="child_mns_deworming" value="1" class="form-check-input" id="child_mns_deworming" <?= set_value('child_mns_deworming', $hh->child_mns_deworming ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="child_mns_deworming">Deworming</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="child_mns_vit_a" value="1" class="form-check-input" id="child_mns_vit_a" <?= set_value('child_mns_vit_a', $hh->child_mns_vit_a ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="child_mns_vit_a">Vitamin A supplied</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="child_mns_micronutrient_powder" value="1" class="form-check-input" id="child_mns_micronutrient_powder" <?= set_value('child_mns_micronutrient_powder', $hh->child_mns_micronutrient_powder ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="child_mns_micronutrient_powder">Micronutrient powder</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="child_mns_ferrous_sulfate" value="1" class="form-check-input" id="child_mns_ferrous_sulfate" <?= set_value('child_mns_ferrous_sulfate', $hh->child_mns_ferrous_sulfate ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="child_mns_ferrous_sulfate">Ferrous sulfate</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="child_mns_multivitamins" value="1" class="form-check-input" id="child_mns_multivitamins" <?= set_value('child_mns_multivitamins', $hh->child_mns_multivitamins ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="child_mns_multivitamins">Multiple vitamins</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Women of Reproductive Age (15 - 49 years old &mdash; women)</div>
                        <div class="row align-items-end">
                            <div class="col-auto form-check mb-2">
                                <input type="checkbox" name="is_pregnant" value="1" class="form-check-input" id="is_pregnant" <?= set_value('is_pregnant', $hh->is_pregnant ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_pregnant">Pregnant</label>
                            </div>
                            <div class="col-auto form-check mb-2">
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
                            <div class="col-12"><hr class="my-2"></div>
                            <div class="col-12 mb-2">
                                <label class="form-label small mb-1">Micronutrient Supplementation</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="wra_mns_iron_folic" value="1" class="form-check-input" id="wra_mns_iron_folic" <?= set_value('wra_mns_iron_folic', $hh->wra_mns_iron_folic ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="wra_mns_iron_folic">Iron + Folic Acid</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="wra_mns_calcium_carbonate" value="1" class="form-check-input" id="wra_mns_calcium_carbonate" <?= set_value('wra_mns_calcium_carbonate', $hh->wra_mns_calcium_carbonate ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="wra_mns_calcium_carbonate">Calcium Carbonate</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="wra_mns_mms" value="1" class="form-check-input" id="wra_mns_mms" <?= set_value('wra_mns_mms', $hh->wra_mns_mms ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="wra_mns_mms">Multiple Micro-nutrient Supplement</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Family Planning Method Used</label>
                                <select name="wra_fp_method" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($wra_fp_method_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('wra_fp_method', $hh->wra_fp_method ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Facility of Buying</label>
                                <input type="text" name="wra_fp_facility_of_buying" class="form-control form-control-sm" maxlength="150" value="<?= set_value('wra_fp_facility_of_buying', $hh->wra_fp_facility_of_buying ?? '') ?>">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">FP Status of Application</label>
                                <select name="wra_fp_status_of_application" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($wra_fp_status_of_application_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('wra_fp_status_of_application', $hh->wra_fp_status_of_application ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Nutritional Status</label>
                                <select name="wra_nutritional_status" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($lifestage_nutrition_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('wra_nutritional_status', $hh->wra_nutritional_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-auto form-check mb-2">
                                <input type="checkbox" name="wra_papsmear_done" value="1" class="form-check-input" id="wra_papsmear_done" <?= set_value('wra_papsmear_done', $hh->wra_papsmear_done ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="wra_papsmear_done">Pap Smear Done</label>
                            </div>
                        </div>
                        <div class="row align-items-end" id="wra_papsmear_result_row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small mb-1">Pap Smear Result</label>
                                <input type="text" name="wra_papsmear_result" class="form-control form-control-sm" maxlength="255" value="<?= set_value('wra_papsmear_result', $hh->wra_papsmear_result ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Adult (20 - 59 years old)</div>
                        <div class="row align-items-end">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small mb-1">Medical History</label>
                                <textarea name="adult_medical_history" class="form-control form-control-sm" rows="2" maxlength="255"><?= set_value('adult_medical_history', $hh->adult_medical_history ?? '') ?></textarea>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Nutritional Status</label>
                                <select name="adult_nutritional_status" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($lifestage_nutrition_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('adult_nutritional_status', $hh->adult_nutritional_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Adolescent</div>
                        <div class="row align-items-end">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small mb-1">Medical History</label>
                                <textarea name="adolescent_medical_history" class="form-control form-control-sm" rows="2" maxlength="255"><?= set_value('adolescent_medical_history', $hh->adolescent_medical_history ?? '') ?></textarea>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Nutritional Status</label>
                                <select name="adolescent_nutritional_status" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($lifestage_nutrition_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('adolescent_nutritional_status', $hh->adolescent_nutritional_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Senior Citizen</div>
                        <div class="row align-items-end">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small mb-1">Medical History</label>
                                <textarea name="senior_medical_history" class="form-control form-control-sm" rows="2" maxlength="255"><?= set_value('senior_medical_history', $hh->senior_medical_history ?? '') ?></textarea>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Nutritional Status</label>
                                <select name="senior_nutritional_status" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($lifestage_nutrition_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('senior_nutritional_status', $hh->senior_nutritional_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-data-survey" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Data Survey Tool <small class="text-muted">(optional)</small></h5></div>
                <div class="card-body">
                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Immun. Status</div>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <select name="immunization_status" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($immunization_status_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('immunization_status', $ds->immunization_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">COVID-19 Immun. (No. of Doses)</label>
                                <select name="covid_vaccine_status" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <?php foreach ($covid_vaccine_status_options as $opt): ?>
                                        <option value="<?= html_escape($opt) ?>" <?= set_value('covid_vaccine_status', $ds->covid_vaccine_status ?? '') === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Schisto MDA Status</div>
                        <div class="row align-items-end">
                            <div class="col-md-2 mb-2">
                                <select name="schisto_mda_status" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <option value="1" <?= set_value('schisto_mda_status', isset($ds->schisto_mda_status) ? (string) $ds->schisto_mda_status : '') === '1' ? 'selected' : '' ?>>Yes</option>
                                    <option value="0" <?= set_value('schisto_mda_status', isset($ds->schisto_mda_status) ? (string) $ds->schisto_mda_status : '') === '0' ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small mb-1">Date of Tx</label>
                                <input type="date" name="schisto_mda_date" class="form-control form-control-sm" value="<?= set_value('schisto_mda_date', $ds->schisto_mda_date ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Usual Daily Food Intake</div>
                        <div class="row">
                            <div class="col-auto form-check">
                                <input type="checkbox" name="eats_breakfast" value="1" class="form-check-input" id="eats_breakfast" <?= set_value('eats_breakfast', $ds->eats_breakfast ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="eats_breakfast">Breakfast</label>
                            </div>
                            <div class="col-auto form-check">
                                <input type="checkbox" name="eats_lunch" value="1" class="form-check-input" id="eats_lunch" <?= set_value('eats_lunch', $ds->eats_lunch ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="eats_lunch">Lunch</label>
                            </div>
                            <div class="col-auto form-check">
                                <input type="checkbox" name="eats_snacks" value="1" class="form-check-input" id="eats_snacks" <?= set_value('eats_snacks', $ds->eats_snacks ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="eats_snacks">Snacks</label>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Exercise</div>
                        <div class="row align-items-end">
                            <div class="col-md-2 mb-2">
                                <select name="exercises" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <option value="1" <?= set_value('exercises', isset($ds->exercises) ? (string) $ds->exercises : '') === '1' ? 'selected' : '' ?>>Yes</option>
                                    <option value="0" <?= set_value('exercises', isset($ds->exercises) ? (string) $ds->exercises : '') === '0' ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small mb-1">Frequency</label>
                                <input type="text" name="exercise_frequency" class="form-control form-control-sm" maxlength="50" placeholder="e.g. 3x/week" value="<?= set_value('exercise_frequency', $ds->exercise_frequency ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Recreational Activity</div>
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <select name="has_recreational_activity" class="form-select form-select-sm">
                                    <option value="">-- N/A --</option>
                                    <option value="1" <?= set_value('has_recreational_activity', isset($ds->has_recreational_activity) ? (string) $ds->has_recreational_activity : '') === '1' ? 'selected' : '' ?>>Yes</option>
                                    <option value="0" <?= set_value('has_recreational_activity', isset($ds->has_recreational_activity) ? (string) $ds->has_recreational_activity : '') === '0' ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-remarks" class="card shadow-sm form-section mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-chat-left-text-fill"></i> Remarks</h5></div>
                <div class="card-body">
                    <textarea name="remarks" class="form-control" rows="2"><?= set_value('remarks', $rmk->remarks ?? '') ?></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
            </div>
        <?= form_close() ?>
    </div>
</div>
