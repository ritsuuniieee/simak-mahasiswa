<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Akses Ditolak';
include __DIR__ . '/includes/header.php';
?>
<div class="text-center py-5">
  <i class="bi bi-shield-lock display-1 text-danger"></i>
  <h3 class="mt-3">Akses Ditolak</h3>
  <p class="text-muted">Anda tidak memiliki hak akses untuk membuka halaman ini.</p>
  <?php if (isLoggedIn()): ?>
    <a href="<?= dashboardUrlForRole(currentRole()) ?>" class="btn btn-success">Kembali ke Dashboard</a>
  <?php else: ?>
    <a href="<?= BASE_URL ?>/login.php" class="btn btn-success">Ke Halaman Login</a>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
