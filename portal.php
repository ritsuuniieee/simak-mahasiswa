<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . dashboardUrlForRole(currentRole()));
    exit;
}

$error = '';
$hasPwCol = hasMahasiswaPasswordColumn($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $error = 'Sesi form kadaluarsa, silakan coba lagi.';
    } else {
        $identitas = trim($_POST['identitas'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($identitas === '') {
            $error = 'Masukkan NIM (mahasiswa) atau NISN (siswa PKL) Anda.';
        } else {
            $cols = $hasPwCol ? 'id, password' : 'id';
            $stmt = $pdo->prepare("SELECT $cols FROM mahasiswa WHERE nim = ? OR nisn = ? LIMIT 1");
            $stmt->execute([$identitas, $identitas]);
            $row = $stmt->fetch();
            if (!$row) {
                $error = 'NIM/NISN tidak ditemukan. Periksa kembali atau hubungi operator.';
            } elseif ($hasPwCol && !empty($row['password'])) {
                // Akun sudah punya password -> wajib verifikasi
                if ($password === '' || !password_verify($password, $row['password'])) {
                    $error = 'Password salah. Hubungi operator jika lupa password.';
                } else {
                    $_SESSION['portal_peserta_id'] = (int)$row['id'];
                    header('Location: ' . BASE_URL . '/portal_dashboard.php');
                    exit;
                }
            } else {
                // Belum ada password (data lama / kolom belum diisi): izinkan masuk,
                // peserta diminta membuat password di dashboard.
                $_SESSION['portal_peserta_id'] = (int)$row['id'];
                if ($hasPwCol && empty($row['password'])) $_SESSION['portal_perlu_password'] = true;
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
      </div>
    </div>

    <?php if ($error): ?>
      <div class="m3-banner m3-banner--error">
        <span class="m3-icon">error</span>
        <span class="m3-grow"><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="post">
      <?= csrfField() ?>
      <div class="m3-field m3-mb-2">
        <label class="m3-field__label" for="identitas">NIM atau NISN</label>
        <input id="identitas" type="text" name="identitas" class="m3-input" required autofocus
               placeholder="Contoh: 2110511001">
        <div class="m3-field__help">Mahasiswa memakai NIM, siswa PKL memakai NISN.</div>
      </div>
      <?php if ($hasPwCol): ?>
      <div class="m3-field m3-mb-3">
        <label class="m3-field__label" for="password">Password</label>
        <input id="password" type="password" name="password" class="m3-input" autocomplete="current-password"
               placeholder="Password portal Anda">
        <div class="m3-field__help">Akun lama yang belum punya password: kosongkan lalu buat password di dalam portal.</div>
      </div>
      <?php endif; ?>
      <button type="submit" class="m3-btn m3-btn--filled m3-btn--block">
        <span class="m3-icon">login</span>Buka portal saya
      </button>
    </form>

    <div class="m3-auth__divider">Lihat tanpa masuk?</div>
    <a href="<?= BASE_URL ?>/publik/" class="m3-btn m3-btn--tonal m3-btn--block">
      <span class="m3-icon">public</span>Portal publik (rekapan &amp; sertifikat)
    </a>

    <div class="m3-auth__divider">Operator atau dosen?</div>

    <a href="<?= BASE_URL ?>/login.php" class="m3-btn m3-btn--outlined m3-btn--block">
      Masuk dengan akun
    </a>
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/material3.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/animasi.js"></script>
</body>
</html>
