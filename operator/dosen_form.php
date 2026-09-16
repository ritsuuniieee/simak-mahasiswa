<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = $id !== null;
$pageTitle = $isEdit ? 'Edit Dosen' : 'Tambah Dosen';

$data = ['nidn_nidk' => '', 'nama' => '', 'no_hp' => '', 'username' => ''];
$errors = [];

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

        if (!$errors) {
            try {
                $pdo->beginTransaction();
                if ($isEdit) {
                    $stmt = $pdo->prepare('UPDATE dosen SET nidn_nidk=?, nama=?, no_hp=? WHERE id=?');
                    $stmt->execute([$data['nidn_nidk'], $data['nama'], $data['no_hp'], $id]);
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

                    $stmt = $pdo->prepare('INSERT INTO dosen (user_id, nidn_nidk, nama, no_hp) VALUES (?,?,?,?)');
                    $stmt->execute([$userId, $data['nidn_nidk'], $data['nama'], $data['no_hp']]);
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
    <form method="post" novalidate>
      <?= csrfField() ?>
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

      <div class="m3-row m3-gap-sm m3-mt-3">
        <button type="submit" class="m3-btn m3-btn--filled">
          <span class="m3-icon m3-icon--sm">save</span>Simpan
        </button>
        <a href="<?= BASE_URL ?>/operator/dosen.php" class="m3-btn m3-btn--text">Batal</a>
      </div>
    </form>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
