<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h3 class="mb-0"><i class="bi bi-clipboard2-pulse-fill"></i> Nutrition Profile</h3>
        <p class="text-muted mb-0">BNS Form No. 1C &mdash; Barangay Nutrition Profile, computed from household records.</p>
    </div>
    <?php if ($summary): ?>
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
            <form method="get" action="<?= base_url('nutrition_profile') ?>" class="row g-2">
                <div class="col-auto">
                    <select name="barangay_id" class="form-select">
                        <option value="">-- Select Barangay --</option>
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
            <form method="get" action="<?= base_url('nutrition_profile') ?>" class="row g-2">
                <div class="col-auto">
                    <select name="region_id" id="np_region_id" class="form-select" data-cascade-target="np_province_id" data-cascade-url="<?= base_url('provinces/by_region/') ?>" data-placeholder="-- Select Province --">
                        <option value="">-- Select Region --</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= $region->id ?>"><?= html_escape($region->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="province_id" id="np_province_id" class="form-select" data-cascade-target="np_municipality_id" data-cascade-url="<?= base_url('municipalities/by_province/') ?>" data-placeholder="-- Select Municipality --">
                        <option value="">-- Select Province --</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="municipality_id" id="np_municipality_id" class="form-select" data-cascade-target="np_barangay_id" data-cascade-url="<?= base_url('barangays/by_municipality/') ?>" data-placeholder="-- Select Barangay --">
                        <option value="">-- Select Municipality --</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="barangay_id" id="np_barangay_id" class="form-select">
                        <option value="">-- Select Barangay --</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-play-fill"></i> Generate</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!$summary): ?>
    <div class="alert alert-info no-print">Select a barangay above to generate its Nutrition Profile.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="text-center mb-3">
                <div class="fw-semibold">BNS Form No. 1C</div>
                <div class="small text-muted">Barangay Nutrition Profile</div>
                <div class="fw-bold text-uppercase mt-2">*Barangay Situational Analysis, CY <?= date('Y') ?></div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4"><strong>Barangay:</strong> <?= html_escape($barangay->name) ?></div>
                <div class="col-md-4"><strong>City/Municipality:</strong> <?= html_escape($municipality->name ?? '') ?></div>
                <div class="col-md-4"><strong>Province:</strong> <?= html_escape($province->name ?? '') ?></div>
            </div>
            <div class="mb-3"><strong>Total Number of Puroks:</strong> <?= (int) $summary->total_puroks ?></div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr><th>Indicators</th><th class="text-end" style="width: 110px;">Number</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>1. Total population</td><td class="text-end"><?= $summary->population ?></td></tr>
                        <tr><td>2. Number of households</td><td class="text-end"><?= $summary->household_count ?></td></tr>
                        <tr><td>3. Households surveyed during Family Profile Survey</td><td class="text-end"><?= $summary->households_surveyed ?></td></tr>
                        <tr><td>4. Total number of women who are:</td><td></td></tr>
                        <tr><td class="ps-4">a. Pregnant</td><td class="text-end"><?= $summary->pregnant ?></td></tr>
                        <tr><td class="ps-4">b. Lactating</td><td class="text-end"><?= $summary->lactating ?></td></tr>
                        <tr><td>5. Total number of households with preschool children aged 0-71 months old</td><td class="text-end"><?= $summary->households_with_preschoolers ?></td></tr>
                        <tr><td>6. Actual population of preschool children 0-59 months old</td><td class="text-end"><?= $summary->preschool_population ?></td></tr>
                        <tr><td>7. Total number of preschool children 0-59 months old measured during OPT Plus</td><td class="text-end"><?= $summary->preschool_measured ?></td></tr>
                        <tr><td class="ps-4">a. Percent (%) measured coverage (OPT Plus)</td><td class="text-end"><?= $summary->measured_coverage_pct ?>%</td></tr>
                        <tr>
                            <td class="ps-4" colspan="2">
                                b. Number and Percent (%) of preschool children according to Nutritional Status
                                <table class="table table-sm table-bordered mt-2 mb-0">
                                    <thead class="table-light"><tr><th></th><th class="text-end">No.</th><th class="text-end">%</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($summary->preschool_status as $i => $row): ?>
                                            <tr>
                                                <td><?= ($i + 1) ?>) <?= html_escape($row['label']) ?></td>
                                                <td class="text-end"><?= $row['count'] ?></td>
                                                <td class="text-end"><?= $row['percent'] ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr><td>8. Total number of infants 0-5 months old</td><td class="text-end"><?= $summary->age_brackets['infants_0_5'] ?></td></tr>
                        <tr><td>9. Total number of infants 6-11 months old</td><td class="text-end"><?= $summary->age_brackets['infants_6_11'] ?></td></tr>
                        <tr><td>10. Total number of preschoolers 0-23 months old</td><td class="text-end"><?= $summary->age_brackets['preschoolers_0_23'] ?></td></tr>
                        <tr><td>11. Total number of preschool children aged 12-59 months old</td><td class="text-end"><?= $summary->age_brackets['preschoolers_12_59'] ?></td></tr>
                        <tr><td>12. Total number of preschoolers 24-59 months old</td><td class="text-end"><?= $summary->age_brackets['preschoolers_24_59'] ?></td></tr>
                        <tr><td>13. Total number of families with severely wasted and wasted preschool children</td><td class="text-end"><?= $summary->families_with_wasted ?></td></tr>
                        <tr><td>14. Total number of families with stunted and severely stunted preschool children</td><td class="text-end"><?= $summary->families_with_stunted ?></td></tr>
                        <tr>
                            <td colspan="2">
                                15. Total number of Educational Institutions
                                <table class="table table-sm table-bordered mt-2 mb-0">
                                    <thead class="table-light"><tr><th></th><th class="text-end">Public</th><th class="text-end">Private</th></tr></thead>
                                    <tbody>
                                        <tr><td class="ps-4">a. Number of Day Care Centers</td><td class="text-end"><?= $summary->day_care_centers_public ?></td><td class="text-end"><?= $summary->day_care_centers_private ?></td></tr>
                                        <tr><td class="ps-4">b. Number of Elementary Schools</td><td class="text-end"><?= $summary->elementary_schools_public ?></td><td class="text-end"><?= $summary->elementary_schools_private ?></td></tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr><td>16. Total number of children enrolled in Kindergarten (DepED-supervised)</td><td class="text-end"><?= $summary->kindergarten_enrolled ?></td></tr>
                        <tr><td>17. Total number of school children (grades 1-6)</td><td class="text-end"><?= $summary->school_children ?></td></tr>
                        <tr><td>18. Total number of school children weighed at the start of the school year (K-Gr. 6)</td><td class="text-end"><?= $summary->school_children_weighed ?></td></tr>
                        <tr><td>19. Percent (%) coverage of school children measured</td><td class="text-end"><?= $summary->school_coverage_pct ?>%</td></tr>
                        <tr>
                            <td colspan="2">
                                20. Number and percent (%) of school children according to Nutritional Status
                                <table class="table table-sm table-bordered mt-2 mb-0">
                                    <thead class="table-light"><tr><th></th><th class="text-end">No.</th><th class="text-end">%</th></tr></thead>
                                    <tbody>
                                        <?php $letters = ['a', 'b', 'c', 'd', 'e']; ?>
                                        <?php foreach ($summary->school_status as $i => $row): ?>
                                            <tr>
                                                <td class="ps-4"><?= $letters[$i] ?>. <?= html_escape($row['label']) ?></td>
                                                <td class="text-end"><?= $row['count'] ?></td>
                                                <td class="text-end"><?= $row['percent'] ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mb-0">*Referred as "Barangay Nutrition Profile" in the BNS Handbook</p>
            <p class="small text-muted mb-0">**Refers to weight-for-length/height</p>
        </div>
    </div>
<?php endif; ?>
