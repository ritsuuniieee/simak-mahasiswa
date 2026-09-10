<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . dashboardUrlForRole(currentRole()));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $error = 'Sesi form kadaluarsa, silakan coba lagi.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Username dan password wajib diisi.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Username atau password salah.';
            } elseif ($user['status'] !== 'aktif') {
                $error = 'Akun Anda tidak aktif. Hubungi operator.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                header('Location: ' . dashboardUrlForRole($user['role']));
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | SIMAK Mahasiswa & PKL</title>
<link href="<?= BASE_URL ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-wrapper login-page">
  <div class="card login-card p-4">
    <div class="text-center mb-3">
      <img src="<?= BASE_URL ?>/assets/images/logo-stikom.png" alt="" height="60" class="mb-2">
      <h4 class="mt-2 mb-0 fw-bold">SIMAK Mahasiswa &amp; PKL</h4>
      <small class="text-muted">Sistem Informasi Manajemen Kehadiran &amp; Kegiatan</small>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" novalidate>
      <?= csrfField() ?>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success w-100">Masuk</button>
    </form>
    <p class="text-center mt-2 mb-0">
      <a href="<?= BASE_URL ?>/portal.php" class="btn btn-outline-secondary w-100">
        <i class="bi bi-eye me-1"></i>Lihat Portal Publik (tanpa login) &raquo;
      </a>
    </p>
  </div>
</body>
</html>
