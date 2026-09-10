<?php
$pageTitle = $pageTitle ?? 'SIMAK Mahasiswa & PKL';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | SIMAK Mahasiswa & PKL</title>
<link href="<?= BASE_URL ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
  <div class="container-fluid">
    <img src="<?= BASE_URL ?>/assets/images/logo-stikom.png" alt="" height="40" class="navbar-brand-logo me-2">
  <a class="navbar-brand fw-semibold" href="<?= dashboardUrlForRole(currentRole() ?? '') ?>">
      <i class="bi bi-mortarboard-fill me-1"></i> SIMAK Mahasiswa &amp; PKL
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <?php if (isLoggedIn()): ?>
        <li class="nav-item me-3 text-white-50">
          <i class="bi bi-person-circle me-1"></i><?= e($_SESSION['nama']) ?>
          <span class="badge text-bg-light text-dark ms-1 text-uppercase"><?= e(currentRole()) ?></span>
        </li>
        <li class="nav-item">
          <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>/logout.php">
            <i class="bi bi-box-arrow-right me-1"></i>Keluar
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="app-wrapper">
<?php if (isLoggedIn()) include __DIR__ . '/sidebar.php'; ?>
<main class="app-content">
  <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> alert-dismissible fade show" role="alert">
      <?= e($flash['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
