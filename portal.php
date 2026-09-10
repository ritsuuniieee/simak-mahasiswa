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
      </div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <?= csrfField() ?>
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
</body>
</html>
