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
<<<<<<< HEAD
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
=======
<h1 class="m3-page-title">Profil saya</h1>

<?php foreach ($errors as $err): ?>
  <div class="m3-banner m3-banner--error">
    <span class="m3-icon">error</span><span class="m3-grow"><?= e($err) ?></span>
  </div>
<?php endforeach; ?>

<section class="m3-card m3-card--elevated" style="max-width:840px">
  <div class="m3-card__body">
    <div class="m3-row m3-mb-3" style="gap:20px">
      <div class="m3-avatar"><span class="m3-icon">account_circle</span></div>
      <div>
        <div class="m3-title-medium"><?= e($user['username']) ?></div>
        <span class="m3-badge m3-badge--secondary"><?= e(ucfirst($user['role'])) ?></span>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
      </div>
    </div>

    <form method="post">
      <?= csrfField() ?>
<<<<<<< HEAD
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
=======
      <div class="m3-grid">
        <div class="m3-col-6">
          <label class="m3-field__label" for="nama">Nama lengkap</label>
          <input id="nama" type="text" name="nama" class="m3-input" required value="<?= e($user['nama']) ?>">
        </div>
        <div class="m3-col-6">
          <label class="m3-field__label" for="email">Email</label>
          <input id="email" type="email" name="email" class="m3-input" value="<?= e($user['email']) ?>">
        </div>
      </div>

      <hr>

      <h2 class="m3-title-medium m3-mb-1">Ganti password</h2>
      <p class="m3-body-medium m3-muted m3-mb-2">Kosongkan ketiga kolom ini jika password tidak diubah.</p>

      <div class="m3-grid">
        <div class="m3-col-4">
          <label class="m3-field__label" for="password_lama">Password lama</label>
          <input id="password_lama" type="password" name="password_lama" class="m3-input" autocomplete="current-password">
        </div>
        <div class="m3-col-4">
          <label class="m3-field__label" for="password_baru">Password baru</label>
          <input id="password_baru" type="password" name="password_baru" class="m3-input" autocomplete="new-password">
          <div class="m3-field__help">Minimal 6 karakter.</div>
        </div>
        <div class="m3-col-4">
          <label class="m3-field__label" for="password_konfirmasi">Ulangi password baru</label>
          <input id="password_konfirmasi" type="password" name="password_konfirmasi" class="m3-input" autocomplete="new-password">
        </div>
      </div>

      <button type="submit" class="m3-btn m3-btn--filled m3-mt-3">
        <span class="m3-icon m3-icon--sm">save</span>Simpan perubahan
      </button>
    </form>
  </div>
</section>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)

<?php include __DIR__ . '/../includes/footer.php'; ?>
