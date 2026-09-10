<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['dosen']);

$pageTitle = 'Profil Saya';
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $errors[] = 'Sesi form kadaluarsa, silakan coba lagi.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $passwordLama = $_POST['password_lama'] ?? '';
        $passwordBaru = $_POST['password_baru'] ?? '';
        $passwordKonfirmasi = $_POST['password_konfirmasi'] ?? '';

        if ($nama === '') {
            $errors[] = 'Nama tidak boleh kosong.';
        }

        $gantiPassword = $passwordLama !== '' || $passwordBaru !== '' || $passwordKonfirmasi !== '';
        if ($gantiPassword) {
            if (!password_verify($passwordLama, $user['password'])) {
                $errors[] = 'Password lama yang Anda masukkan salah.';
            } elseif (strlen($passwordBaru) < 6) {
                $errors[] = 'Password baru minimal 6 karakter.';
            } elseif ($passwordBaru !== $passwordKonfirmasi) {
                $errors[] = 'Konfirmasi password baru tidak cocok.';
            }
        }

        if (!$errors) {
            if ($gantiPassword) {
                $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET nama=?, email=?, password=? WHERE id=?');
                $stmt->execute([$nama, $email ?: null, $hash, $userId]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET nama=?, email=? WHERE id=?');
                $stmt->execute([$nama, $email ?: null, $userId]);
            }
            $_SESSION['nama'] = $nama;
            setFlash('success', 'Profil berhasil diperbarui.' . ($gantiPassword ? ' Password Anda telah diganti.' : ''));
            header('Location: ' . BASE_URL . '/dosen/profil.php');
            exit;
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        $user['nama'] = $nama;
        $user['email'] = $email;
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3">Profil Saya</h4>

<?php foreach ($errors as $err): ?>
  <div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="row mb-4">
      <div class="col-md-6">
        <p class="mb-1 text-muted small">Username</p>
        <p class="fw-semibold"><?= e($user['username']) ?></p>
      </div>
      <div class="col-md-6">
        <p class="mb-1 text-muted small">Role</p>
        <p class="fw-semibold text-uppercase"><?= e($user['role']) ?></p>
      </div>
    </div>

    <form method="post">
      <?= csrfField() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama" class="form-control" required value="<?= e($user['nama']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>">
        </div>
      </div>

      <hr class="my-4">
      <h6 class="fw-semibold mb-3">Ganti Password</h6>
      <p class="text-muted small mb-3">Kosongkan ketiga kolom di bawah jika tidak ingin mengganti password.</p>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Password Lama</label>
          <input type="password" name="password_lama" class="form-control" autocomplete="current-password">
        </div>
        <div class="col-md-4">
          <label class="form-label">Password Baru</label>
          <input type="password" name="password_baru" class="form-control" autocomplete="new-password">
        </div>
        <div class="col-md-4">
          <label class="form-label">Konfirmasi Password Baru</label>
          <input type="password" name="password_konfirmasi" class="form-control" autocomplete="new-password">
        </div>
      </div>

      <button type="submit" class="btn btn-success mt-4"><i class="bi bi-check-lg me-1"></i>Simpan Perubahan</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
