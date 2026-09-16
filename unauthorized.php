<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Akses ditolak';
include __DIR__ . '/includes/header.php';
?>
<section class="m3-card m3-card--filled" style="max-width:560px;margin:40px auto">
  <div class="m3-card__body m3-text-center">
    <span class="m3-icon m3-icon--xl" style="color:var(--m3-error)">lock</span>
    <h1 class="m3-headline-small m3-mt-2">Akses ditolak</h1>
    <p class="m3-body-large m3-muted">Halaman ini tidak tersedia untuk peran akun Anda.</p>
    <?php if (isLoggedIn()): ?>
      <a href="<?= dashboardUrlForRole(currentRole()) ?>" class="m3-btn m3-btn--filled">Kembali ke dashboard</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/login.php" class="m3-btn m3-btn--filled">Ke halaman masuk</a>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
