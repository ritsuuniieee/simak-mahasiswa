<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$pageTitle = 'Akun Pengguna';

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    if ($id !== (int)$_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status='aktif','nonaktif','aktif') WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Status akun berhasil diubah.');
    } else {
        setFlash('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
    }
    header('Location: ' . BASE_URL . '/operator/users.php');
    exit;
}

if (isset($_GET['reset'])) {
    $id = (int)$_GET['reset'];
    $hash = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->execute([$hash, $id]);
    setFlash('success', 'Password akun berhasil direset ke: password123');
    header('Location: ' . BASE_URL . '/operator/users.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== (int)$_SESSION['user_id']) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        setFlash('success', 'Akun berhasil dihapus.');
    } else {
        setFlash('error', 'Anda tidak bisa menghapus akun sendiri.');
    }
    header('Location: ' . BASE_URL . '/operator/users.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah_operator') {
    if (!csrfValid()) {
        $errors[] = 'Sesi form kadaluarsa, coba lagi.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($username === '' || $nama === '') {
            $errors[] = 'Username dan nama wajib diisi.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = 'Username sudah digunakan.';
            } else {
                $hash = password_hash('password123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, password, nama, email, role) VALUES (?,?,?,?,"operator")');
                $stmt->execute([$username, $hash, $nama, $email ?: null]);
                setFlash('success', 'Akun operator baru berhasil dibuat. Password default: password123');
                header('Location: ' . BASE_URL . '/operator/users.php');
                exit;
            }
        }
    }
}

$roleFilter = $_GET['role'] ?? '';
$sql = "SELECT * FROM users";
$params = [];
if (in_array($roleFilter, ['operator','dosen','mahasiswa'], true)) {
    $sql .= " WHERE role = ?";
    $params[] = $roleFilter;
}
$sql .= " ORDER BY role, nama";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<<<<<<< HEAD
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h4 class="mb-0">Akun Pengguna</h4>
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahOperator">
    <i class="bi bi-plus-lg me-1"></i>Tambah Akun Operator
=======
<div class="m3-row--between m3-mb-3">
  <h1 class="m3-page-title m3-mb-0">Akun pengguna</h1>
  <button class="m3-fab" data-m3-dialog-open="dialogTambahOperator">
    <span class="m3-icon">person_add</span>Tambah operator
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
  </button>
</div>

<?php foreach ($errors as $err): ?>
<<<<<<< HEAD
  <div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<ul class="nav nav-pills mb-3">
  <?php foreach (['' => 'Semua', 'operator' => 'Operator', 'dosen' => 'Dosen', 'mahasiswa' => 'Mahasiswa/PKL'] as $val => $label): ?>
    <li class="nav-item"><a class="nav-link <?= $roleFilter === $val ? 'active' : '' ?>" href="?role=<?= $val ?>"><?= $label ?></a></li>
  <?php endforeach; ?>
</ul>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead><tr><th>Username</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
=======
  <div class="m3-banner m3-banner--error">
    <span class="m3-icon">error</span><span class="m3-grow"><?= e($err) ?></span>
  </div>
<?php endforeach; ?>

<div class="m3-chipset">
  <?php foreach (['' => 'Semua', 'operator' => 'Operator', 'dosen' => 'Dosen', 'mahasiswa' => 'Peserta'] as $val => $label): ?>
    <a class="m3-chip <?= $roleFilter === $val ? 'is-selected' : '' ?>" href="?role=<?= $val ?>">
      <?php if ($roleFilter === $val): ?><span class="m3-icon m3-icon--sm">check</span><?php endif; ?>
      <?= $label ?>
    </a>
  <?php endforeach; ?>
</div>

<section class="m3-table-wrap">
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead>
        <tr><th>Username</th><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th class="m3-td-actions">Aksi</th></tr>
      </thead>
      <tbody>
      <?php if (!$users): ?>
        <tr><td colspan="6" class="m3-table__empty">Belum ada akun terdaftar.</td></tr>
      <?php endif; ?>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['username']) ?></td>
          <td><?= e($u['nama']) ?></td>
<<<<<<< HEAD
          <td><?= e($u['email'] ?? '-') ?></td>
          <td><span class="badge text-bg-secondary text-uppercase"><?= e($u['role']) ?></span></td>
          <td><span class="badge <?= $u['status'] === 'aktif' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= e($u['status']) ?></span></td>
          <td class="text-end">
            <a href="?toggle=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Aktif/Nonaktifkan"><i class="bi bi-power"></i></a>
            <a href="?reset=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning" title="Reset Password"
               onclick="return confirm('Reset password akun \'<?= e($u['username']) ?>\' ke password123?');"><i class="bi bi-key"></i></a>
            <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus Akun"
               onclick="return confirm('Yakin hapus akun \'<?= e($u['username']) ?>\'?');"><i class="bi bi-trash"></i></a>
=======
          <td><?= e($u['email'] ?: '-') ?></td>
          <td><span class="m3-badge m3-badge--secondary"><?= e(ucfirst($u['role'])) ?></span></td>
          <td>
            <span class="m3-badge m3-badge--<?= $u['status'] === 'aktif' ? 'success' : 'error' ?>">
              <?= e(ucfirst($u['status'])) ?>
            </span>
          </td>
          <td class="m3-td-actions">
            <a href="?toggle=<?= $u['id'] ?>" class="m3-icon-btn" title="Aktifkan / nonaktifkan" aria-label="Aktifkan atau nonaktifkan akun">
              <span class="m3-icon m3-icon--sm">power_settings_new</span>
            </a>
            <a href="?reset=<?= $u['id'] ?>" class="m3-icon-btn m3-icon-btn--primary" title="Setel ulang password" aria-label="Setel ulang password"
               onclick="return confirm('Setel ulang password akun \'<?= e($u['username']) ?>\' menjadi password123?');">
              <span class="m3-icon m3-icon--sm">key</span>
            </a>
            <a href="?delete=<?= $u['id'] ?>" class="m3-icon-btn m3-icon-btn--danger" title="Hapus akun" aria-label="Hapus akun"
               onclick="return confirm('Hapus akun \'<?= e($u['username']) ?>\'?');">
              <span class="m3-icon m3-icon--sm">delete</span>
            </a>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<<<<<<< HEAD
</div>

<div class="modal fade" id="modalTambahOperator" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="tambah_operator">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Akun Operator</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="nama" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
        <div class="form-text">Password default: <code>password123</code></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success">Simpan</button>
      </div>
    </form>
  </div>
</div>
=======
</section>

<dialog class="m3-dialog" id="dialogTambahOperator">
  <form method="post">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="tambah_operator">
    <h2 class="m3-dialog__title">Tambah akun operator</h2>
    <div class="m3-dialog__body">
      <div class="m3-field m3-mb-2">
        <label class="m3-field__label" for="op-username">Username</label>
        <input id="op-username" type="text" name="username" class="m3-input" required>
      </div>
      <div class="m3-field m3-mb-2">
        <label class="m3-field__label" for="op-nama">Nama lengkap</label>
        <input id="op-nama" type="text" name="nama" class="m3-input" required>
      </div>
      <div class="m3-field">
        <label class="m3-field__label" for="op-email">Email</label>
        <input id="op-email" type="email" name="email" class="m3-input">
        <div class="m3-field__help">Password awal diset ke <code>password123</code>. Minta operator baru menggantinya setelah masuk.</div>
      </div>
    </div>
    <div class="m3-dialog__actions">
      <button type="button" class="m3-btn m3-btn--text" data-m3-dialog-close>Batal</button>
      <button type="submit" class="m3-btn m3-btn--filled">Buat akun</button>
    </div>
  </form>
</dialog>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)

<?php include __DIR__ . '/../includes/footer.php'; ?>
