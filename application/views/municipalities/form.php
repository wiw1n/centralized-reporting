<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-building"></i> <?= html_escape($page_title) ?></h3>
    <a href="<?= base_url('municipalities') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?= form_open(current_url()) ?>
            <?php if ($municipality): ?>
            <div class="mb-3">
                <label class="form-label">ID</label>
                <input type="text" class="form-control" value="<?= html_escape($municipality->id) ?>" disabled>
            </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Region</label>
                <select name="region_id" id="form_region_id" class="form-select" data-cascade-target="province_id" data-cascade-url="<?= base_url('provinces/by_region/') ?>">
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
                <select name="province_id" id="province_id" class="form-select" data-placeholder="-- Select Province --" required>
                    <option value="">-- Select Province --</option>
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?= $province->id ?>"
                            <?= set_value('province_id', $municipality->province_id ?? '') == $province->id ? 'selected' : '' ?>>
                            <?= html_escape($province->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Municipality / City Name</label>
                <input type="text" name="name" class="form-control" maxlength="100" required
                       value="<?= set_value('name', $municipality->name ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-control" maxlength="45"
                       value="<?= set_value('code', $municipality->code ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Prefix <small class="text-muted">(used in the Resident ID Number, e.g. PAL)</small></label>
                <input type="text" name="prefix" class="form-control" maxlength="10" style="text-transform:uppercase"
                       value="<?= set_value('prefix', $municipality->prefix ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= set_value('description', $municipality->description ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
        <?= form_close() ?>
    </div>
</div>
