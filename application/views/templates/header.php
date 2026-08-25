<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - Centralized Reporting' : 'Centralized Reporting' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<?php
    $full_name = $current_user->full_name ?? '';
    $initials = '';
    foreach (array_slice(preg_split('/\s+/', trim($full_name)), 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
?>
<nav class="navbar app-navbar px-3">
    <button type="button" class="sidebar-toggle-btn d-lg-none me-2" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
    <span class="navbar-brand mb-0 h1">
        <i class="bi bi-diagram-3-fill"></i> Centralized Reporting
    </span>
    <div class="dropdown ms-auto">
        <button class="btn user-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <span class="avatar-circle"><?= html_escape($initials ?: '?') ?></span>
            <span><?= html_escape($full_name) ?></span>
            <span class="badge role-pill"><?= html_escape($current_user->role_label ?? '') ?></span>
            <?php if (isset($current_user) && $current_user->role_name !== 'super_admin'): ?>
                <?php if (!empty($active_municipality)): ?>
                    <span class="badge bg-light text-dark border"><i class="bi bi-geo-alt-fill"></i> <?= html_escape($active_municipality->name) ?></span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill"></i> No active municipality</span>
                <?php endif; ?>
            <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li class="dropdown-header">
                <div class="fw-semibold text-dark"><?= html_escape($full_name) ?></div>
                <div class="text-muted small"><?= html_escape($current_user->role_label ?? '') ?></div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= base_url('profile') ?>"><i class="bi bi-person-lines-fill"></i> Edit Personal Info</a></li>
            <li><a class="dropdown-item" href="<?= base_url('profile#change-password') ?>"><i class="bi bi-shield-lock"></i> Change Password</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= base_url('auth/logout') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="app-layout">
    <nav class="app-sidebar" id="appSidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= (isset($active_menu) && $active_menu === 'dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= (isset($active_menu) && $active_menu === 'residents') ? 'active' : '' ?>" href="<?= base_url('residents') ?>">
                    <i class="bi bi-person-vcard-fill"></i> Residents
                </a>
            </li>
            <li class="nav-item mt-2">
                <span class="sidebar-heading">Reports</span>
            </li>
            <!-- HEALTH REPORTS -->
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between <?= (isset($active_menu) && $active_menu === "health") ? 'active' : '' ?>"
                   href="#" data-bs-toggle="collapse" data-bs-target="#health" role="button"
                   aria-expanded="<?= (isset($active_menu) && $active_menu === "health") ? 'true' : 'false' ?>" aria-controls="health">
                    <span><i class="bi bi-heart-pulse-fill"></i> Health</span>
                    <i class="bi bi-chevron-down sidebar-caret"></i>
                </a>
                <ul class="nav flex-column sidebar-submenu collapse <?= (isset($active_menu) && $active_menu === "health") ? 'show' : '' ?>" id="health">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('health/household_report') ?>">
                            <i class="bi bi-house-heart-fill"></i> Household Report
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('health/data_survey_report') ?>">
                            <i class="bi bi-clipboard2-pulse"></i> Data Survey Tool
                        </a>
                    </li>
                </ul>
            </li>
            <!-- SOCIAL SERVICES REPORTS -->
            <li class="nav-item">
                <a class="nav-link disabled d-flex align-items-center justify-content-between"
                   href="#" role="button" aria-disabled="true" tabindex="-1">
                    <span><i class="bi bi-people-fill"></i> Social Services</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled d-flex align-items-center justify-content-between"
                   href="#" role="button" aria-disabled="true" tabindex="-1">
                    <span><i class="bi bi-journal-bookmark-fill"></i> Civil Registrar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled d-flex align-items-center justify-content-between"
                   href="#" role="button" aria-disabled="true" tabindex="-1">
                    <span><i class="bi bi-clipboard-data-fill"></i> Assessor</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled d-flex align-items-center justify-content-between"
                   href="#" role="button" aria-disabled="true" tabindex="-1">
                    <span><i class="bi bi-shop"></i> Business Sector</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled d-flex align-items-center justify-content-between"
                   href="#" role="button" aria-disabled="true" tabindex="-1">
                    <span><i class="bi bi-houses-fill"></i> Barangay</span>
                </a>
            </li>
            <li class="nav-item mt-2">
                <span class="sidebar-heading">System Configuration</span>
            </li>
            <?php if (isset($current_user) && in_array($current_user->role_name, ['super_admin'], true)): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between <?= (isset($active_menu) && $active_menu === "addresses") ? 'active' : '' ?>"
                    href="#" data-bs-toggle="collapse" data-bs-target="#addresses" role="button"
                    aria-expanded="<?= (isset($active_menu) && $active_menu === "addresses") ? 'true' : 'false' ?>" aria-controls="addresses">
                        <span><i class="bi bi-geo-alt"></i> addresses</span>
                        <i class="bi bi-chevron-down sidebar-caret"></i>
                    </a>
                    <ul class="nav flex-column sidebar-submenu collapse <?= (isset($active_menu) && $active_menu === "addresses") ? 'show' : '' ?>" id="addresses">
                        <li class="nav-item">
                            <a class="nav-link disabled aria-disabled="true" <?= (isset($active_menu) && $active_menu === 'regions') ? 'active' : '' ?>" href="<?= base_url('regions') ?>">
                                <i class="bi bi-globe-asia-australia"></i> Regions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled aria-disabled="true" <?= (isset($active_menu) && $active_menu === 'provinces') ? 'active' : '' ?>" href="<?= base_url('provinces') ?>">
                                <i class="bi bi-map"></i> Provinces
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled aria-disabled="true" <?= (isset($active_menu) && $active_menu === 'municipalities') ? 'active' : '' ?>" href="<?= base_url('municipalities') ?>">
                                <i class="bi bi-building"></i> Municipalities
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (isset($active_menu) && $active_menu === 'barangays') ? 'active' : '' ?>" href="<?= base_url('barangays') ?>">
                                <i class="bi bi-houses-fill"></i> Barangays
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($active_menu) && $active_menu === 'users') ? 'active' : '' ?>" href="<?= base_url('users') ?>">
                        <i class="bi bi-people-fill"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($active_menu) && $active_menu === 'settings') ? 'active' : '' ?>" href="<?= base_url('settings') ?>">
                        <i class="bi bi-gear-fill"></i> System Settings
                    </a>
                </li>
                
            <?php endif; ?>
        </ul>
    </nav>

    <main class="app-content">
        <?php if ($flash_success = $this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= html_escape($flash_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($flash_error = $this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= html_escape($flash_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
