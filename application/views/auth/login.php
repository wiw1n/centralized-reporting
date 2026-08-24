<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Centralized Reporting</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="card auth-card shadow">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-diagram-3-fill" style="font-size: 2.5rem; color: var(--brand);"></i>
                <h4 class="mt-2 mb-0">Centralized Reporting</h4>
                <small class="text-muted">Sign in to continue</small>
            </div>

            <?php if ($error = $this->session->flashdata('error')): ?>
                <div class="alert alert-danger"><?= html_escape($error) ?></div>
            <?php endif; ?>
            <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>

            <?= form_open('auth/login') ?>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= set_value('username') ?>" autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            <?= form_close() ?>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url('assets/js/auth.js') ?>"></script>
</body>
</html>
