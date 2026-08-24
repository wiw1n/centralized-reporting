<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-globe-asia-australia"></i> <?= html_escape($page_title) ?></h3>
    <a href="<?= base_url('regions') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?= form_open(current_url()) ?>
            <div class="mb-3">
                <label class="form-label">Region Name</label>
                <input type="text" name="name" class="form-control" maxlength="45" required
                       value="<?= set_value('name', $region->name ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-control" maxlength="45"
                       value="<?= set_value('code', $region->code ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= set_value('description', $region->description ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
        <?= form_close() ?>
    </div>
</div>
