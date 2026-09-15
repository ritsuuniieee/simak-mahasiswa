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
<<<<<<< HEAD
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
=======
<meta name="theme-color" content="#fbebe2">
<title>Masuk | SIMAK Mahasiswa &amp; PKL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,400;8..144,500;8..144,600;8..144,700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/material3.css" rel="stylesheet">
</head>
<body class="m3-auth">
  <main class="m3-auth__card">
    <div class="m3-auth__head">
      <img src="<?= BASE_URL ?>/assets/images/logo-stikom.png" alt="" class="m3-auth__logo">
      <h1 class="m3-auth__title">Masuk</h1>
      <p class="m3-auth__subtitle">Akun operator dan dosen pembimbing</p>
    </div>

    <?php if ($error): ?>
      <div class="m3-banner m3-banner--error">
        <span class="m3-icon">error</span>
        <span class="m3-grow"><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <?= csrfField() ?>
      <div class="m3-field m3-mb-2">
        <label class="m3-field__label" for="username">Username</label>
        <input id="username" type="text" name="username" class="m3-input" required autofocus
               autocomplete="username" value="<?= e($_POST['username'] ?? '') ?>">
      </div>
      <div class="m3-field m3-mb-3">
        <label class="m3-field__label" for="password">Password</label>
        <input id="password" type="password" name="password" class="m3-input" required autocomplete="current-password">
      </div>
      <button type="submit" class="m3-btn m3-btn--filled m3-btn--block">Masuk</button>
    </form>

    <div class="m3-auth__divider">Mahasiswa atau siswa PKL?</div>

    <a href="<?= BASE_URL ?>/portal.php" class="m3-btn m3-btn--tonal m3-btn--block">
      <span class="m3-icon">badge</span>Buka portal dengan NIM / NISN
    </a>
  </main>

  <script src="<?= BASE_URL ?>/assets/js/material3.js"></script>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
</body>
</html>
