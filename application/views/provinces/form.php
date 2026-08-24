<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-map"></i> <?= html_escape($page_title) ?></h3>
    <a href="<?= base_url('provinces') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?= form_open(current_url()) ?>
            <?php if ($province): ?>
            <div class="mb-3">
                <label class="form-label">ID</label>
                <input type="text" class="form-control" value="<?= html_escape($province->id) ?>" disabled>
            </div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Region</label>
                <select name="region_id" class="form-select" required>
                    <option value="">-- Select Region --</option>
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= $region->id ?>"
                            <?= set_value('region_id', $province->region_id ?? '') == $region->id ? 'selected' : '' ?>>
                            <?= html_escape($region->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Province Name</label>
                <input type="text" name="name" class="form-control" maxlength="100" required
                       value="<?= set_value('name', $province->name ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-control" maxlength="45"
                       value="<?= set_value('code', $province->code ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= set_value('description', $province->description ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
        <?= form_close() ?>
    </div>
</div>
