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
        $identitas = trim($_POST['identitas'] ?? '');
        if ($identitas === '') {
            $error = 'Masukkan NIM (mahasiswa) atau NISN (siswa PKL) Anda.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM mahasiswa WHERE nim = ? OR nisn = ? LIMIT 1');
            $stmt->execute([$identitas, $identitas]);
            $id = $stmt->fetchColumn();
            if (!$id) {
                $error = 'NIM/NISN tidak ditemukan. Periksa kembali atau hubungi operator.';
            } else {
                $_SESSION['portal_peserta_id'] = (int)$id;
                header('Location: ' . BASE_URL . '/portal_dashboard.php');
                exit;
            }
        }
    }
}

$totalMhs = $pdo->query("SELECT COUNT(*) FROM mahasiswa WHERE tipe='mahasiswa'")->fetchColumn();
$totalPkl = $pdo->query("SELECT COUNT(*) FROM mahasiswa WHERE tipe='siswa_pkl'")->fetchColumn();
$totalSertifikat = $pdo->query("SELECT COUNT(*) FROM sertifikat")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
<title>Portal Publik | SIMAK Mahasiswa & PKL</title>
<link href="<?= BASE_URL ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-wrapper login-page">
  <div class="card login-card p-4" style="max-width:480px">
    <div class="text-center mb-3">
      <img src="<?= BASE_URL ?>/assets/images/logo-stikom.png" alt="" height="60" class="mb-2">
      <h4 class="mt-2 mb-0 fw-bold">Portal Mahasiswa &amp; Siswa PKL</h4>
      <small class="text-muted">Absensi &middot; Kegiatan Harian &middot; Sertifikat</small>
    </div>

    <div class="row text-center g-2 mb-3">
      <div class="col-4">
        <div class="border rounded p-2">
          <div class="fw-bold fs-5"><?= (int)$totalMhs ?></div>
          <div class="small text-muted">Mahasiswa</div>
        </div>
      </div>
      <div class="col-4">
        <div class="border rounded p-2">
          <div class="fw-bold fs-5"><?= (int)$totalPkl ?></div>
          <div class="small text-muted">Siswa PKL</div>
        </div>
      </div>
      <div class="col-4">
        <div class="border rounded p-2">
          <div class="fw-bold fs-5"><?= (int)$totalSertifikat ?></div>
          <div class="small text-muted">Sertifikat</div>
        </div>
=======
<meta name="theme-color" content="#fbebe2">
<title>Portal Peserta | SIMAK Mahasiswa &amp; PKL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,400;8..144,500;8..144,600;8..144,700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/material3.css" rel="stylesheet">
</head>
<body class="m3-auth">
  <main class="m3-auth__card m3-auth__card--wide">
    <div class="m3-auth__head">
      <img src="<?= BASE_URL ?>/assets/images/logo-stikom.png" alt="" class="m3-auth__logo">
      <h1 class="m3-auth__title">Portal peserta</h1>
      <p class="m3-auth__subtitle">Isi absensi, catat kegiatan harian, dan unduh sertifikat</p>
    </div>

    <div class="m3-metrics">
      <div class="m3-metric">
        <div class="m3-metric__value"><?= (int)$totalMhs ?></div>
        <div class="m3-metric__label">Mahasiswa</div>
      </div>
      <div class="m3-metric">
        <div class="m3-metric__value"><?= (int)$totalPkl ?></div>
        <div class="m3-metric__label">Siswa PKL</div>
      </div>
      <div class="m3-metric">
        <div class="m3-metric__value"><?= (int)$totalSertifikat ?></div>
        <div class="m3-metric__label">Sertifikat</div>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
      </div>
    </div>

    <?php if ($error): ?>
<<<<<<< HEAD
      <div class="alert alert-danger py-2"><?= e($error) ?></div>
=======
      <div class="m3-banner m3-banner--error">
        <span class="m3-icon">error</span>
        <span class="m3-grow"><?= e($error) ?></span>
      </div>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
    <?php endif; ?>

    <form method="post">
      <?= csrfField() ?>
<<<<<<< HEAD
      <label class="form-label">Masukkan NIM atau NISN Anda</label>
      <input type="text" name="identitas" class="form-control mb-3" required autofocus>
      <button type="submit" class="btn btn-success w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk ke Portal
      </button>
    </form>

    <p class="text-center mt-3 mb-0">
      <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-primary w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i>Login sebagai Operator / Dosen
      </a>
    </p>
  </div>
=======
      <div class="m3-field m3-mb-3">
        <label class="m3-field__label" for="identitas">NIM atau NISN</label>
        <input id="identitas" type="text" name="identitas" class="m3-input" required autofocus
               placeholder="Contoh: 2110511001">
        <div class="m3-field__help">Mahasiswa memakai NIM, siswa PKL memakai NISN. Tidak perlu password.</div>
      </div>
      <button type="submit" class="m3-btn m3-btn--filled m3-btn--block">
        <span class="m3-icon">login</span>Buka portal saya
      </button>
    </form>

    <div class="m3-auth__divider">Operator atau dosen?</div>

    <a href="<?= BASE_URL ?>/login.php" class="m3-btn m3-btn--outlined m3-btn--block">
      Masuk dengan akun
    </a>
  </main>

  <script src="<?= BASE_URL ?>/assets/js/material3.js"></script>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
</body>
</html>
