<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['dosen']);

$pageTitle = 'Profil Saya';
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$hasFotoCol = hasDosenFotoColumn($pdo);
$dosen = null;
if ($hasFotoCol) {
    $stmt = $pdo->prepare('SELECT * FROM dosen WHERE user_id = ?');
    $stmt->execute([$userId]);
    $dosen = $stmt->fetch();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $errors[] = 'Sesi form kadaluarsa, silakan coba lagi.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $noHp = trim($_POST['no_hp'] ?? '');
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

        // Validasi foto profil dosen
        $fotoBaru = null;
        if ($hasFotoCol && !empty($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Terjadi kesalahan saat mengunggah foto.';
            } else {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png'], true)) $errors[] = 'Foto harus JPG atau PNG.';
                elseif ($_FILES['foto']['size'] > 2*1024*1024) $errors[] = 'Ukuran foto maksimal 2 MB.';
                else $fotoBaru = 'dosen_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            }
        }

        if (!$errors) {
            try {
                $pdo->beginTransaction();
                if ($fotoBaru) {
                    if (!is_dir(DOSEN_FOTO_DIR)) mkdir(DOSEN_FOTO_DIR, 0775, true);
                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], DOSEN_FOTO_DIR . $fotoBaru)) {
                        throw new Exception('Gagal menyimpan foto ke server.');
                    }
                    if ($dosen && !empty($dosen['foto'])) {
                        if (is_file(DOSEN_FOTO_DIR . $dosen['foto'])) @unlink(DOSEN_FOTO_DIR . $dosen['foto']);
                        if (is_file(FOTO_DIR . $dosen['foto'])) @unlink(FOTO_DIR . $dosen['foto']);
                    }
                    $stmt = $pdo->prepare('UPDATE dosen SET foto = ? WHERE user_id = ?');
                    $stmt->execute([$fotoBaru, $userId]);
                }
                if ($gantiPassword) {
                    $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET nama=?, email=?, password=? WHERE id=?');
                    $stmt->execute([$nama, $email ?: null, $hash, $userId]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET nama=?, email=? WHERE id=?');
                    $stmt->execute([$nama, $email ?: null, $userId]);
                }
                // Sinkronkan nama + no HP ke tabel dosen
                $stmt = $pdo->prepare('UPDATE dosen SET nama=?, no_hp=? WHERE user_id=?');
                $stmt->execute([$nama, $noHp ?: null, $userId]);
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = $e->getMessage();
            }
            if (!$errors) {
                $_SESSION['nama'] = $nama;
                setFlash('success', 'Profil berhasil diperbarui.' . ($gantiPassword ? ' Password Anda telah diganti.' : ''));
                header('Location: ' . BASE_URL . '/dosen/profil.php');
                exit;
            }
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
<h1 class="m3-page-title">Profil saya</h1>

<?php foreach ($errors as $err): ?>
  <div class="m3-banner m3-banner--error">
    <span class="m3-icon">error</span><span class="m3-grow"><?= e($err) ?></span>
  </div>
<?php endforeach; ?>

<section class="m3-card m3-card--elevated" style="max-width:840px">
  <div class="m3-card__body">
    <div class="m3-row m3-mb-3" style="gap:20px">
      <?php if ($hasFotoCol && $dosen && !empty($dosen['foto'])): ?>
        <img src="<?= fotoDosenUrl($dosen['foto']) ?>" alt="" class="m3-avatar m3-avatar--lg">
      <?php else: ?>
        <div class="m3-avatar m3-avatar--lg"><span class="m3-icon m3-icon--lg">account_circle</span></div>
      <?php endif; ?>
      <div>
        <div class="m3-title-medium"><?= e($user['username']) ?></div>
        <span class="m3-badge m3-badge--secondary"><?= e(ucfirst($user['role'])) ?></span>
        <?php if ($dosen): ?><div class="m3-body-small m3-muted">NIDN/NIDK: <?= e($dosen['nidn_nidk']) ?></div><?php endif; ?>
      </div>
    </div>

    <form method="post" enctype="multipart/form-data">
      <?= csrfField() ?>
      <?php if ($hasFotoCol): ?>
      <div class="m3-field m3-mb-2">
        <label class="m3-field__label" for="foto">Foto profil</label>
        <input id="foto" type="file" name="foto" class="m3-input" accept=".jpg,.jpeg,.png">
        <div class="m3-field__help">JPG/PNG, maks 2 MB. Kosongkan jika tidak diubah.</div>
      </div>
      <?php endif; ?>
      <div class="m3-grid">
        <div class="m3-col-6">
          <label class="m3-field__label" for="nama">Nama lengkap</label>
          <input id="nama" type="text" name="nama" class="m3-input" required value="<?= e($user['nama']) ?>">
        </div>
        <div class="m3-col-6">
          <label class="m3-field__label" for="email">Email</label>
          <input id="email" type="email" name="email" class="m3-input" value="<?= e($user['email']) ?>">
        </div>
        <div class="m3-col-6">
          <label class="m3-field__label" for="no_hp">No. HP</label>
          <input id="no_hp" type="text" name="no_hp" class="m3-input" value="<?= e($dosen['no_hp'] ?? '') ?>">
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
