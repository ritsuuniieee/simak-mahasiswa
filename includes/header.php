<?php
$pageTitle = $pageTitle ?? 'SIMAK Mahasiswa & PKL';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </div>
  <?php endif; ?>
