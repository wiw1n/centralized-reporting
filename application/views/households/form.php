<?php
$relationship_options = Household_model::RELATIONSHIP_OPTIONS;
$civil_status_options = Household_model::CIVIL_STATUS_OPTIONS;
$education_options = Household_model::EDUCATION_OPTIONS;
$weight_age_options = Household_member_model::NUTRITIONAL_STATUS_WEIGHT_AGE_OPTIONS;
$height_age_options = Household_member_model::NUTRITIONAL_STATUS_HEIGHT_AGE_OPTIONS;
$weight_height_options = Household_member_model::NUTRITIONAL_STATUS_WEIGHT_HEIGHT_OPTIONS;
$school_level_options = Household_member_model::SCHOOL_LEVEL_OPTIONS;
$school_type_options = Household_member_model::SCHOOL_TYPE_OPTIONS;
$school_nutrition_options = Household_member_model::SCHOOL_NUTRITIONAL_STATUS_OPTIONS;
$tt_status_options = Household_member_model::TT_STATUS_OPTIONS;

$initial_members = $posted_members ?? array_map(function ($m) {
    return [
        'resident_id' => $m->resident_id,
        'linked_resident_no' => $m->linked_resident_no,
        'last_name' => $m->last_name,
        'first_name' => $m->first_name,
        'middle_name' => $m->middle_name,
        'suffix' => $m->suffix,
        'relationship_to_head' => $m->relationship_to_head,
        'ordinal_position' => $m->ordinal_position,
        'sex' => $m->sex,
        'birthdate' => $m->birthdate,
        'civil_status' => $m->civil_status,
        'religion' => $m->religion,
        'occupation' => $m->occupation,
        'educational_attainment' => $m->educational_attainment,
        'contact_number' => $m->contact_number,
        'is_pwd' => $m->is_pwd,
        'is_senior_citizen' => $m->is_senior_citizen,
        'is_solo_parent' => $m->is_solo_parent,
        'is_4ps_beneficiary' => $m->is_4ps_beneficiary,
        'is_pregnant' => $m->is_pregnant,
        'is_lactating' => $m->is_lactating,
        'has_hypertension' => $m->has_hypertension,
        'has_diabetes' => $m->has_diabetes,
        'has_asthma' => $m->has_asthma,
        'other_illness' => $m->other_illness,
        'gravida' => $m->gravida,
        'para' => $m->para,
        'lmp_date' => $m->lmp_date,
        'edc_date' => $m->edc_date,
        'tt_status' => $m->tt_status,
        'opt_plus_measured' => $m->opt_plus_measured,
        'nutritional_status_weight_age' => $m->nutritional_status_weight_age,
        'nutritional_status_height_age' => $m->nutritional_status_height_age,
        'nutritional_status_weight_height' => $m->nutritional_status_weight_height,
        'school_level' => $m->school_level,
        'school_type' => $m->school_type,
        'school_weighed' => $m->school_weighed,
        'school_nutritional_status' => $m->school_nutritional_status,
    ];
}, $existing_members);

if (empty($initial_members) && !$household) {
    $initial_members = [['relationship_to_head' => 'Head']];
}

$render_member_row = function ($member, $index) use (
    $relationship_options, $civil_status_options, $education_options,
    $weight_age_options, $height_age_options, $weight_height_options,
    $school_level_options, $school_type_options, $school_nutrition_options, $tt_status_options
) {
    $m = array_merge([
        'resident_id' => '', 'linked_resident_no' => '',
        'last_name' => '', 'first_name' => '', 'middle_name' => '', 'suffix' => '',
        'relationship_to_head' => '', 'ordinal_position' => '', 'sex' => '', 'birthdate' => '', 'civil_status' => '',
        'religion' => '', 'occupation' => '', 'educational_attainment' => '', 'contact_number' => '',
        'is_pwd' => 0, 'is_senior_citizen' => 0, 'is_solo_parent' => 0, 'is_4ps_beneficiary' => 0,
        'is_pregnant' => 0, 'is_lactating' => 0, 'opt_plus_measured' => 0,
        'has_hypertension' => 0, 'has_diabetes' => 0, 'has_asthma' => 0, 'other_illness' => '',
        'gravida' => '', 'para' => '', 'lmp_date' => '', 'edc_date' => '', 'tt_status' => '',
        'nutritional_status_weight_age' => '', 'nutritional_status_height_age' => '', 'nutritional_status_weight_height' => '',
        'school_level' => '', 'school_type' => '', 'school_weighed' => 0, 'school_nutritional_status' => '',
    ], $member);
    ?>
    <div class="member-row border rounded p-3 mb-3">
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-sm btn-outline-danger remove-member-btn"><i class="bi bi-x-lg"></i> Remove</button>
        </div>
        <p class="small text-muted mb-1"><i class="bi bi-search"></i> Start typing the name &mdash; matching residents already on file will appear below so you can link the same person instead of creating a duplicate.</p>
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="form-label">Last Name</label>
                <input type="text" name="members[<?= $index ?>][last_name]" class="form-control member-last-name" maxlength="100" value="<?= html_escape($m['last_name']) ?>" required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">First Name</label>
                <input type="text" name="members[<?= $index ?>][first_name]" class="form-control member-first-name" maxlength="100" value="<?= html_escape($m['first_name']) ?>" required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Middle Name</label>
                <input type="text" name="members[<?= $index ?>][middle_name]" class="form-control" maxlength="100" value="<?= html_escape($m['middle_name']) ?>">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Suffix</label>
                <input type="text" name="members[<?= $index ?>][suffix]" class="form-control" maxlength="20" value="<?= html_escape($m['suffix']) ?>">
            </div>
        </div>
        <input type="hidden" name="members[<?= $index ?>][resident_id]" class="member-resident-id" value="<?= html_escape($m['resident_id']) ?>">
        <div class="resident-match-results list-group mb-2 d-none" style="max-height: 220px; overflow-y: auto;"></div>
        <div class="resident-link-badge alert alert-success py-1 px-2 small d-flex justify-content-between align-items-center <?= empty($m['resident_id']) ? 'd-none' : '' ?>">
            <span><i class="bi bi-link-45deg"></i> <span class="resident-link-label"><?= !empty($m['linked_resident_no']) ? 'Linked to resident ' . html_escape($m['linked_resident_no']) : 'Linked to an existing resident' ?></span></span>
            <button type="button" class="btn btn-sm btn-outline-secondary resident-unlink-btn">Unlink</button>
        </div>
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="form-label">Relationship to Head</label>
                <select name="members[<?= $index ?>][relationship_to_head]" class="form-select member-relationship" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($relationship_options as $opt): ?>
                        <option value="<?= html_escape($opt) ?>" <?= $m['relationship_to_head'] === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Ord. Position</label>
                <input type="number" name="members[<?= $index ?>][ordinal_position]" class="form-control" min="1" max="99" value="<?= html_escape($m['ordinal_position']) ?>">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Sex</label>
                <select name="members[<?= $index ?>][sex]" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="Male" <?= $m['sex'] === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $m['sex'] === 'Female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Birthdate</label>
                <input type="date" name="members[<?= $index ?>][birthdate]" class="form-control member-birthdate" value="<?= html_escape($m['birthdate']) ?>" required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Civil Status</label>
                <select name="members[<?= $index ?>][civil_status]" class="form-select" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($civil_status_options as $opt): ?>
                        <option value="<?= html_escape($opt) ?>" <?= $m['civil_status'] === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="form-label">Religion</label>
                <input type="text" name="members[<?= $index ?>][religion]" class="form-control" maxlength="50" value="<?= html_escape($m['religion']) ?>">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Occupation / Source of Income</label>
                <input type="text" name="members[<?= $index ?>][occupation]" class="form-control" maxlength="150" value="<?= html_escape($m['occupation']) ?>">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Educational Attainment</label>
                <select name="members[<?= $index ?>][educational_attainment]" class="form-select">
                    <option value="">-- Select --</option>
                    <?php foreach ($education_options as $opt): ?>
                        <option value="<?= html_escape($opt) ?>" <?= $m['educational_attainment'] === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Contact Number</label>
                <input type="text" name="members[<?= $index ?>][contact_number]" class="form-control" maxlength="20" value="<?= html_escape($m['contact_number']) ?>">
            </div>
        </div>
        <div class="row mt-1">
            <div class="col-auto form-check">
                <input type="checkbox" name="members[<?= $index ?>][is_pwd]" value="1" class="form-check-input" <?= $m['is_pwd'] ? 'checked' : '' ?>>
                <label class="form-check-label">PWD</label>
            </div>
            <div class="col-auto form-check">
                <input type="checkbox" class="form-check-input member-senior-checkbox" disabled <?= $m['is_senior_citizen'] ? 'checked' : '' ?>>
                <label class="form-check-label">Senior Citizen <small class="text-muted">(computed from birthdate)</small></label>
            </div>
            <div class="col-auto form-check">
                <input type="checkbox" name="members[<?= $index ?>][is_solo_parent]" value="1" class="form-check-input" <?= $m['is_solo_parent'] ? 'checked' : '' ?>>
                <label class="form-check-label">Solo Parent</label>
            </div>
            <div class="col-auto form-check">
                <input type="checkbox" name="members[<?= $index ?>][is_4ps_beneficiary]" value="1" class="form-check-input" <?= $m['is_4ps_beneficiary'] ? 'checked' : '' ?>>
                <label class="form-check-label">4Ps Beneficiary</label>
            </div>
        </div>

        <div class="border-top pt-2 mt-2">
            <div class="small text-muted text-uppercase fw-semibold mb-1">Present Illness</div>
            <div class="row align-items-end">
                <div class="col-auto form-check">
                    <input type="checkbox" name="members[<?= $index ?>][has_hypertension]" value="1" class="form-check-input" <?= $m['has_hypertension'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Hypertension (HPN)</label>
                </div>
                <div class="col-auto form-check">
                    <input type="checkbox" name="members[<?= $index ?>][has_diabetes]" value="1" class="form-check-input" <?= $m['has_diabetes'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Diabetes Mellitus (DM)</label>
                </div>
                <div class="col-auto form-check">
                    <input type="checkbox" name="members[<?= $index ?>][has_asthma]" value="1" class="form-check-input" <?= $m['has_asthma'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Asthma</label>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small mb-1">Other (Etc.)</label>
                    <input type="text" name="members[<?= $index ?>][other_illness]" class="form-control form-control-sm" maxlength="150" value="<?= html_escape($m['other_illness']) ?>">
                </div>
            </div>
        </div>

        <div class="row mt-2 member-women-fields">
            <div class="col-auto form-check">
                <input type="checkbox" name="members[<?= $index ?>][is_pregnant]" value="1" class="form-check-input" <?= $m['is_pregnant'] ? 'checked' : '' ?>>
                <label class="form-check-label">Pregnant</label>
            </div>
            <div class="col-auto form-check">
                <input type="checkbox" name="members[<?= $index ?>][is_lactating]" value="1" class="form-check-input" <?= $m['is_lactating'] ? 'checked' : '' ?>>
                <label class="form-check-label">Lactating</label>
            </div>
            <div class="col-md-1 mb-2">
                <label class="form-label small mb-1">Gravida (G)</label>
                <input type="number" name="members[<?= $index ?>][gravida]" class="form-control form-control-sm" min="0" max="30" value="<?= html_escape($m['gravida']) ?>">
            </div>
            <div class="col-md-1 mb-2">
                <label class="form-label small mb-1">Para (P)</label>
                <input type="number" name="members[<?= $index ?>][para]" class="form-control form-control-sm" min="0" max="30" value="<?= html_escape($m['para']) ?>">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label small mb-1">LMP</label>
                <input type="date" name="members[<?= $index ?>][lmp_date]" class="form-control form-control-sm" value="<?= html_escape($m['lmp_date']) ?>">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label small mb-1">EDC</label>
                <input type="date" name="members[<?= $index ?>][edc_date]" class="form-control form-control-sm" value="<?= html_escape($m['edc_date']) ?>">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label small mb-1">TT Status</label>
                <select name="members[<?= $index ?>][tt_status]" class="form-select form-select-sm">
                    <option value="">-- N/A --</option>
                    <?php foreach ($tt_status_options as $opt): ?>
                        <option value="<?= html_escape($opt) ?>" <?= $m['tt_status'] === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="member-preschool-fields border-top pt-2 mt-2">
            <div class="small text-muted text-uppercase fw-semibold mb-1">Nutrition Profile &mdash; Preschool (0-59 mos.)</div>
            <div class="row align-items-end">
                <div class="col-auto form-check">
                    <input type="checkbox" name="members[<?= $index ?>][opt_plus_measured]" value="1" class="form-check-input" <?= $m['opt_plus_measured'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Measured during OPT Plus <small class="text-muted">(preschool child, 0-59 mos.)</small></label>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small mb-1">Weight-for-Age</label>
                    <select name="members[<?= $index ?>][nutritional_status_weight_age]" class="form-select form-select-sm">
                        <option value="">-- N/A --</option>
                        <?php foreach ($weight_age_options as $opt): ?>
                            <option value="<?= html_escape($opt) ?>" <?= $m['nutritional_status_weight_age'] === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small mb-1">Height-for-Age</label>
                    <select name="members[<?= $index ?>][nutritional_status_height_age]" class="form-select form-select-sm">
                        <option value="">-- N/A --</option>
                        <?php foreach ($height_age_options as $opt): ?>
                            <option value="<?= html_escape($opt) ?>" <?= $m['nutritional_status_height_age'] === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small mb-1">Weight-for-Height/Length</label>
                    <select name="members[<?= $index ?>][nutritional_status_weight_height]" class="form-select form-select-sm">
                        <option value="">-- N/A --</option>
                        <?php foreach ($weight_height_options as $opt): ?>
                            <option value="<?= html_escape($opt) ?>" <?= $m['nutritional_status_weight_height'] === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="member-school-fields border-top pt-2 mt-2">
            <div class="small text-muted text-uppercase fw-semibold mb-1">Nutrition Profile &mdash; School (Day Care/Kinder/Grades 1-6)</div>
            <div class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="form-label small mb-1">School Level</label>
                    <select name="members[<?= $index ?>][school_level]" class="form-select form-select-sm">
                        <option value="">-- Not Enrolled --</option>
                        <?php foreach ($school_level_options as $opt): ?>
                            <option value="<?= html_escape($opt) ?>" <?= $m['school_level'] === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label small mb-1">School Type</label>
                    <select name="members[<?= $index ?>][school_type]" class="form-select form-select-sm">
                        <option value="">-- N/A --</option>
                        <?php foreach ($school_type_options as $opt): ?>
                            <option value="<?= html_escape($opt) ?>" <?= $m['school_type'] === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto form-check mb-2">
                    <input type="checkbox" name="members[<?= $index ?>][school_weighed]" value="1" class="form-check-input" <?= $m['school_weighed'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Weighed at start of school year</label>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small mb-1">Nutritional Status</label>
                    <select name="members[<?= $index ?>][school_nutritional_status]" class="form-select form-select-sm">
                        <option value="">-- N/A --</option>
                        <?php foreach ($school_nutrition_options as $opt): ?>
                            <option value="<?= html_escape($opt) ?>" <?= $m['school_nutritional_status'] === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <?php
};
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-house-heart-fill"></i> <?= html_escape($page_title) ?></h3>
    <a href="<?= base_url('households') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?= form_open(current_url()) ?>

            <?php if ($household): ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">ID</label>
                    <input type="text" class="form-control" value="<?= html_escape($household->id) ?>" disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Household No.</label>
                    <input type="text" class="form-control" value="<?= html_escape($household->household_no) ?>" disabled>
                </div>
            </div>
            <?php endif; ?>

            <h5>Location</h5>
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
                    <select name="barangay_id" id="barangay_id" class="form-select" required>
                        <option value="">-- Select Barangay --</option>
                        <?php foreach ($barangays as $barangay): ?>
                            <option value="<?= $barangay->id ?>"
                                <?= set_value('barangay_id', $household->barangay_id ?? '') == $barangay->id ? 'selected' : '' ?>>
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
                                <?= set_value('barangay_id', $household->barangay_id ?? '') == $barangay->id ? 'selected' : '' ?>>
                                <?= html_escape($barangay->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Purok / Sitio</label>
                    <input type="text" name="purok_sitio" class="form-control" maxlength="100" value="<?= set_value('purok_sitio', $household->purok_sitio ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Address Line</label>
                    <input type="text" name="address_line" class="form-control" maxlength="150" value="<?= set_value('address_line', $household->address_line ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" maxlength="20" value="<?= set_value('contact_number', $household->contact_number ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control" rows="2"><?= set_value('remarks', $household->remarks ?? '') ?></textarea>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_surveyed" id="is_surveyed" value="1" class="form-check-input" <?= set_value('is_surveyed', $household->is_surveyed ?? 0) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_surveyed">Covered by the Family Profile Survey</label>
            </div>

            <hr>
            <h5>Household Members</h5>
            <p class="text-muted small">Exactly one member must be marked as <strong>Head</strong>.</p>

            <template id="member_row_template">
                <?php $render_member_row([], '__INDEX__'); ?>
            </template>

            <div id="members_container" data-next-index="<?= count($initial_members) ?>">
                <?php foreach ($initial_members as $i => $member): ?>
                    <?php $render_member_row($member, $i); ?>
                <?php endforeach; ?>
            </div>

            <button type="button" id="add_member_btn" class="btn btn-outline-primary btn-sm mb-3">
                <i class="bi bi-plus-lg"></i> Add Another Member
            </button>

            <div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
            </div>
        <?= form_close() ?>
    </div>
</div>
