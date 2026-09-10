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
<h4 class="mb-4"><?= e($pageTitle) ?></h4>

<?php foreach ($errors as $err): ?>
  <div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="post" novalidate>
      <?= csrfField() ?>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">NIDN/NIDK</label>
          <input type="text" name="nidn_nidk" class="form-control" required value="<?= e($data['nidn_nidk']) ?>">
        </div>
        <div class="col-md-8">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama" class="form-control" required value="<?= e($data['nama']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">No. HP</label>
          <input type="text" name="no_hp" class="form-control" value="<?= e($data['no_hp']) ?>">
        </div>
        <?php if (!$isEdit): ?>
        <div class="col-md-6">
          <label class="form-label">Username Login</label>
          <input type="text" name="username" class="form-control" value="<?= e($data['username']) ?>">
          <div class="form-text">Password default: <code>password123</code></div>
        </div>
        <?php endif; ?>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Simpan</button>
        <a href="<?= BASE_URL ?>/operator/dosen.php" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
