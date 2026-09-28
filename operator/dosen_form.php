<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = $id !== null;
$pageTitle = $isEdit ? 'Edit Dosen' : 'Tambah Dosen';

$data = ['nidn_nidk' => '', 'nama' => '', 'no_hp' => '', 'username' => '', 'foto' => ''];
$errors = [];
$hasFotoCol = hasDosenFotoColumn($pdo);

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM dosen WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        setFlash('error', 'Data dosen tidak ditemukan.');
        header('Location: ' . BASE_URL . '/operator/dosen.php');
        exit;
    }
    $data = array_merge($data, $row);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $errors[] = 'Sesi form kadaluarsa, silakan coba lagi.';
    } else {
        $data['nidn_nidk'] = trim($_POST['nidn_nidk'] ?? '');
        $data['nama'] = trim($_POST['nama'] ?? '');
        $data['no_hp'] = trim($_POST['no_hp'] ?? '');
        $data['username'] = trim($_POST['username'] ?? '');

        if ($data['nidn_nidk'] === '' || $data['nama'] === '') {
            $errors[] = 'NIDN/NIDK dan nama wajib diisi.';
        }
        if (!$isEdit && $data['username'] === '') {
            $errors[] = 'Username akun login dosen wajib diisi.';
        }

        if (!$errors) {
            $stmt = $pdo->prepare('SELECT id FROM dosen WHERE nidn_nidk = ? AND id != ?');
            $stmt->execute([$data['nidn_nidk'], $id ?? 0]);
            if ($stmt->fetch()) {
                $errors[] = 'NIDN/NIDK sudah digunakan dosen lain.';
            }
        }

        // Validasi foto profil (opsional)
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
                    if ($isEdit && !empty($data['foto']) && is_file(DOSEN_FOTO_DIR . $data['foto'])) @unlink(DOSEN_FOTO_DIR . $data['foto']);
                    // fallback: hapus juga bila file lama tersimpan di FOTO_DIR
                    if ($isEdit && !empty($data['foto']) && is_file(FOTO_DIR . $data['foto'])) @unlink(FOTO_DIR . $data['foto']);
                }
                $fotoFinal = $fotoBaru ?? ($data['foto'] ?: null);
                if ($isEdit) {
                    if ($hasFotoCol) {
                        $stmt = $pdo->prepare('UPDATE dosen SET nidn_nidk=?, nama=?, no_hp=?, foto=? WHERE id=?');
                        $stmt->execute([$data['nidn_nidk'], $data['nama'], $data['no_hp'], $fotoFinal, $id]);
                    } else {
                        $stmt = $pdo->prepare('UPDATE dosen SET nidn_nidk=?, nama=?, no_hp=? WHERE id=?');
                        $stmt->execute([$data['nidn_nidk'], $data['nama'], $data['no_hp'], $id]);
                    }
                    $stmt = $pdo->prepare('UPDATE users SET nama=? WHERE id=?');
                    $stmt->execute([$data['nama'], $data['user_id']]);
                } else {
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
                    $stmt->execute([$data['username']]);
                    if ($stmt->fetch()) throw new Exception('Username sudah digunakan, pilih username lain.');
                    $hash = password_hash('password123', PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (username, password, nama, role) VALUES (?,?,?,"dosen")');
                    $stmt->execute([$data['username'], $hash, $data['nama']]);
                    $userId = $pdo->lastInsertId();

                    if ($hasFotoCol) {
                        $stmt = $pdo->prepare('INSERT INTO dosen (user_id, nidn_nidk, nama, no_hp, foto) VALUES (?,?,?,?,?)');
                        $stmt->execute([$userId, $data['nidn_nidk'], $data['nama'], $data['no_hp'], $fotoFinal ?? null]);
                    } else {
                        $stmt = $pdo->prepare('INSERT INTO dosen (user_id, nidn_nidk, nama, no_hp) VALUES (?,?,?,?)');
                        $stmt->execute([$userId, $data['nidn_nidk'], $data['nama'], $data['no_hp']]);
                    }
                }
                $pdo->commit();
                setFlash('success', $isEdit ? 'Data dosen berhasil diperbarui.' : 'Dosen baru berhasil ditambahkan. Password default: password123');
                header('Location: ' . BASE_URL . '/operator/dosen.php');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h1 class="m3-page-title"><?= e($pageTitle) ?></h1>

<?php foreach ($errors as $err): ?>
  <div class="m3-banner m3-banner--error">
    <span class="m3-icon">error</span><span class="m3-grow"><?= e($err) ?></span>
  </div>
<?php endforeach; ?>

<section class="m3-card m3-card--elevated" style="max-width:840px">
  <div class="m3-card__body">
    <?php if (!$hasFotoCol): ?>
      <div class="m3-banner m3-banner--warning m3-mb-3">
        <span class="m3-icon">warning</span>
        <span class="m3-grow">Kolom <code>dosen.foto</code> belum ada di database. Jalankan <code>database/migrasi_revisi.sql</code> agar foto profil tersimpan.</span>
      </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" novalidate>
      <?= csrfField() ?>
      <div class="m3-grid">
        <?php if ($hasFotoCol): ?>
        <div class="m3-col-4">
          <label class="m3-field__label">Foto profil</label>
          <img id="fotoPreview" alt="Pratinjau foto" class="m3-avatar m3-avatar--xl m3-mb-2"
               src="<?= !empty($data['foto']) ? fotoDosenUrl($data['foto']) : '' ?>"
               style="<?= !empty($data['foto']) ? '' : 'display:none' ?>">
          <input type="file" name="foto" class="m3-input" accept=".jpg,.jpeg,.png" onchange="previewFotoDosen(this)">
          <div class="m3-field__help">JPG/PNG, maks 2 MB.</div>
        </div>
        <?php endif; ?>
        <div class="m3-col-<?= $hasFotoCol ? '8' : '12' ?>">
          <div class="m3-grid">
            <div class="m3-col-4">
              <label class="m3-field__label" for="nidn_nidk">NIDN / NIDK</label>
              <input id="nidn_nidk" type="text" name="nidn_nidk" class="m3-input" required value="<?= e($data['nidn_nidk']) ?>">
            </div>
            <div class="m3-col-8">
              <label class="m3-field__label" for="nama">Nama lengkap</label>
              <input id="nama" type="text" name="nama" class="m3-input" required value="<?= e($data['nama']) ?>">
            </div>
            <div class="m3-col-6">
              <label class="m3-field__label" for="no_hp">No. HP</label>
              <input id="no_hp" type="text" name="no_hp" class="m3-input" value="<?= e($data['no_hp']) ?>">
            </div>
            <?php if (!$isEdit): ?>
            <div class="m3-col-6">
              <label class="m3-field__label" for="username">Username untuk masuk</label>
              <input id="username" type="text" name="username" class="m3-input" value="<?= e($data['username']) ?>">
              <div class="m3-field__help">Password awal diset ke <code>password123</code>.</div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="m3-row m3-gap-sm m3-mt-3">
        <button type="submit" class="m3-btn m3-btn--filled">
          <span class="m3-icon m3-icon--sm">save</span>Simpan
        </button>
        <a href="<?= BASE_URL ?>/operator/dosen.php" class="m3-btn m3-btn--text">Batal</a>
      </div>
    </form>
  </div>
</section>

<script>
function previewFotoDosen(input) {
  var p = document.getElementById('fotoPreview');
  if (input.files && input.files[0]) { p.src = URL.createObjectURL(input.files[0]); p.style.display = 'block'; }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
