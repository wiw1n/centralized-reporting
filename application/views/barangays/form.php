<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-houses-fill"></i> <?= html_escape($page_title) ?></h3>
    <a href="<?= base_url('barangays') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?= form_open(current_url()) ?>
            <?php if ($barangay): ?>
            <div class="mb-3">
                <label class="form-label">ID</label>
                <input type="text" class="form-control" value="<?= html_escape($barangay->id) ?>" disabled>
            </div>
            <?php endif; ?>

            <?php if ($locked_municipality): ?>
            <div class="mb-3">
                <label class="form-label">Municipality / City</label>
                <input type="text" class="form-control" value="<?= html_escape($locked_municipality->name) ?>" disabled>
            </div>
            <?php else: ?>
            <div class="mb-3">
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
            <div class="mb-3">
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
            <div class="mb-3">
                <label class="form-label">Municipality / City</label>
                <select name="municipality_id" id="municipality_id" class="form-select" data-placeholder="-- Select Municipality --" required>
                    <option value="">-- Select Municipality --</option>
                    <?php foreach ($municipalities as $municipality): ?>
                        <option value="<?= $municipality->id ?>"
                            <?= set_value('municipality_id', $barangay->municipality_id ?? '') == $municipality->id ? 'selected' : '' ?>>
                            <?= html_escape($municipality->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Barangay Name</label>
                <input type="text" name="name" class="form-control" maxlength="100" required
                       value="<?= set_value('name', $barangay->name ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-control" maxlength="45"
                       value="<?= set_value('code', $barangay->code ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Prefix <small class="text-muted">(used in the Resident ID Number, e.g. SJO)</small></label>
                <input type="text" name="prefix" class="form-control" maxlength="10" style="text-transform:uppercase"
                       value="<?= set_value('prefix', $barangay->prefix ?? '') ?>">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="poblacion" id="poblacion" class="form-check-input" value="1"
                    <?= set_value('poblacion', $barangay->poblacion ?? 0) ? 'checked' : '' ?>>
                <label class="form-check-label" for="poblacion">This is the poblacion (town center)</label>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= set_value('description', $barangay->description ?? '') ?></textarea>
            </div>

            <hr>
            <h5>Nutrition Profile Facts <small class="text-muted">(used by the Nutrition Profile report)</small></h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Total Number of Puroks</label>
                    <input type="number" min="0" step="1" name="total_puroks" class="form-control" value="<?= set_value('total_puroks', $barangay->total_puroks ?? 0) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Day Care Centers <small class="text-muted">(Public)</small></label>
                    <input type="number" min="0" step="1" name="day_care_centers_public" class="form-control" value="<?= set_value('day_care_centers_public', $barangay->day_care_centers_public ?? 0) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Day Care Centers <small class="text-muted">(Private)</small></label>
                    <input type="number" min="0" step="1" name="day_care_centers_private" class="form-control" value="<?= set_value('day_care_centers_private', $barangay->day_care_centers_private ?? 0) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Elementary Schools <small class="text-muted">(Public)</small></label>
                    <input type="number" min="0" step="1" name="elementary_schools_public" class="form-control" value="<?= set_value('elementary_schools_public', $barangay->elementary_schools_public ?? 0) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Elementary Schools <small class="text-muted">(Private)</small></label>
                    <input type="number" min="0" step="1" name="elementary_schools_private" class="form-control" value="<?= set_value('elementary_schools_private', $barangay->elementary_schools_private ?? 0) ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
        <?= form_close() ?>
    </div>
</div>
