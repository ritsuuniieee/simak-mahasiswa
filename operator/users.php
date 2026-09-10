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
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h4 class="mb-0">Akun Pengguna</h4>
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahOperator">
    <i class="bi bi-plus-lg me-1"></i>Tambah Akun Operator
  </button>
</div>

<?php foreach ($errors as $err): ?>
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
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['username']) ?></td>
          <td><?= e($u['nama']) ?></td>
          <td><?= e($u['email'] ?? '-') ?></td>
          <td><span class="badge text-bg-secondary text-uppercase"><?= e($u['role']) ?></span></td>
          <td><span class="badge <?= $u['status'] === 'aktif' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= e($u['status']) ?></span></td>
          <td class="text-end">
            <a href="?toggle=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Aktif/Nonaktifkan"><i class="bi bi-power"></i></a>
            <a href="?reset=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning" title="Reset Password"
               onclick="return confirm('Reset password akun \'<?= e($u['username']) ?>\' ke password123?');"><i class="bi bi-key"></i></a>
            <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus Akun"
               onclick="return confirm('Yakin hapus akun \'<?= e($u['username']) ?>\'?');"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
