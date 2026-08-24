<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-person-lines-fill"></i> My Profile</h3>
</div>

<?= validation_errors('<div class="alert alert-danger">', '</div>') ?>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab !== 'password' ? 'active' : '' ?>" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-pane" type="button" role="tab">
                    <i class="bi bi-person-vcard"></i> Personal Info
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'password' ? 'active' : '' ?>" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-pane" type="button" role="tab">
                    <i class="bi bi-shield-lock"></i> Change Password
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="profileTabsContent">
            <div class="tab-pane fade <?= $active_tab !== 'password' ? 'show active' : '' ?>" id="info-pane" role="tabpanel">
                <?= form_open(base_url('profile/update_info')) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" maxlength="100" required
                                   value="<?= set_value('first_name', $profile_user->first_name) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" maxlength="100" required
                                   value="<?= set_value('last_name', $profile_user->last_name) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" maxlength="50" required
                                   value="<?= set_value('username', $profile_user->username) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" maxlength="100" required
                                   value="<?= set_value('email', $profile_user->email) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?= html_escape($profile_user->role_label) ?>" disabled>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
                <?= form_close() ?>
            </div>

            <div class="tab-pane fade <?= $active_tab === 'password' ? 'show active' : '' ?>" id="password-pane" role="tabpanel">
                <?= form_open(base_url('profile/update_password')) ?>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirm" class="form-control" minlength="8" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-shield-check"></i> Update Password</button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.location.hash === '#change-password') {
            var passwordTabBtn = document.getElementById('password-tab');
            if (passwordTabBtn) {
                new bootstrap.Tab(passwordTabBtn).show();
            }
        }
    });
</script>
