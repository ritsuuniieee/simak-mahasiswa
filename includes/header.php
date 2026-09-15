<?php
$pageTitle = $pageTitle ?? 'SIMAK Mahasiswa & PKL';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
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
=======
<meta name="theme-color" content="#fbebe2">
<title><?= e($pageTitle) ?> | SIMAK Mahasiswa &amp; PKL</title>

<?php /* Tipografi & ikon Material 3. Lihat README untuk cara memakai versi lokal (offline). */ ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,400;8..144,500;8..144,600;8..144,700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">

<link href="<?= BASE_URL ?>/assets/css/material3.css" rel="stylesheet">
</head>
<body>

<header class="m3-appbar">
  <button class="m3-icon-btn m3-drawer-toggle" data-m3-drawer-toggle aria-label="Buka menu navigasi">
    <span class="m3-icon">menu</span>
  </button>

  <a class="m3-appbar__brand" href="<?= dashboardUrlForRole(currentRole() ?? '') ?>">
    <img src="<?= BASE_URL ?>/assets/images/logo-stikom.png" alt="" class="m3-appbar__logo">
    <span class="m3-appbar__title">SIMAK Mahasiswa &amp; PKL</span>
  </a>

  <span class="m3-appbar__spacer"></span>

  <?php if (isLoggedIn()): ?>
    <div class="m3-appbar__user">
      <div class="m3-text-right">
        <div class="m3-appbar__user-name"><?= e($_SESSION['nama']) ?></div>
        <div class="m3-appbar__user-role"><?= e(currentRole()) ?></div>
      </div>
      <a href="<?= BASE_URL ?>/logout.php" class="m3-icon-btn m3-icon-btn--tonal" title="Keluar" aria-label="Keluar">
        <span class="m3-icon">logout</span>
      </a>
    </div>
  <?php endif; ?>
</header>

<div class="m3-scrim" data-m3-scrim></div>

<div class="m3-layout">
<?php if (isLoggedIn()) include __DIR__ . '/sidebar.php'; ?>
<main class="m3-main">
  <?php $flash = getFlash(); if ($flash): ?>
    <div class="m3-banner m3-banner--<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
      <span class="m3-icon"><?= $flash['type'] === 'error' ? 'error' : 'check_circle' ?></span>
      <span class="m3-grow"><?= e($flash['message']) ?></span>
      <button type="button" class="m3-banner__close" data-m3-banner-close aria-label="Tutup pesan">
        <span class="m3-icon m3-icon--sm">close</span>
      </button>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
    </div>
  <?php endif; ?>
