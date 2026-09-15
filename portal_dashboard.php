<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pesertaId = $_SESSION['portal_peserta_id'] ?? null;
if (!$pesertaId) {
    header('Location: ' . BASE_URL . '/portal.php');
    exit;
}

$stmt = $pdo->prepare('SELECT m.*, d.nama AS nama_dosen FROM mahasiswa m LEFT JOIN dosen d ON d.id = m.dosen_id WHERE m.id = ?');
$stmt->execute([$pesertaId]);
$peserta = $stmt->fetch();
if (!$peserta) {
    unset($_SESSION['portal_peserta_id']);
    header('Location: ' . BASE_URL . '/portal.php');
    exit;
}

$tipe = $peserta['tipe'];
$today = date('Y-m-d');
$flashLocal = '';
$flashType = 'success';

// ==== Aksi: Absen Masuk / Keluar ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && in_array($_POST['aksi'], ['masuk','keluar'], true)) {
    if (!csrfValid()) {
        $flashLocal = 'Sesi form kadaluarsa, coba lagi.'; $flashType = 'danger';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM absensi WHERE mahasiswa_id = ? AND tanggal = ?');
        $stmt->execute([$pesertaId, $today]);
        $absenHariIni = $stmt->fetch();
        $now = date('H:i:s');

        if ($_POST['aksi'] === 'masuk') {
            if ($absenHariIni) {
                $flashLocal = 'Anda sudah tercatat absen hari ini.'; $flashType = 'danger';
            } else {
                $status = hitungStatusMasuk($tipe, $now);
                $stmt = $pdo->prepare('INSERT INTO absensi (mahasiswa_id, tanggal, jam_masuk, status_masuk) VALUES (?,?,?,?)');
                $stmt->execute([$pesertaId, $today, $now, $status]);
                $flashLocal = 'Absen masuk tercatat pukul ' . substr($now,0,5) . ' (' . ($status === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat') . ').';
            }
        } else {
            if (!$absenHariIni || !$absenHariIni['jam_masuk']) {
                $flashLocal = 'Anda belum absen masuk hari ini.'; $flashType = 'danger';
            } elseif ($absenHariIni['jam_keluar']) {
                $flashLocal = 'Anda sudah tercatat absen keluar hari ini.'; $flashType = 'danger';
            } else {
                $statusKeluar = hitungStatusKeluar($tipe, $now);
                $stmt = $pdo->prepare('UPDATE absensi SET jam_keluar = ?, status_keluar = ? WHERE id = ?');
                $stmt->execute([$now, $statusKeluar, $absenHariIni['id']]);
                $flashLocal = 'Absen keluar tercatat pukul ' . substr($now,0,5) . '.';
            }
        }
    }
}

// ==== Aksi: Tambah Kegiatan Harian (tanggal otomatis hari ini) ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'kegiatan') {
    if (!csrfValid()) {
        $flashLocal = 'Sesi form kadaluarsa, coba lagi.'; $flashType = 'danger';
    } else {
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        if ($judul === '' || $deskripsi === '') {
            $flashLocal = 'Judul dan deskripsi kegiatan wajib diisi.'; $flashType = 'danger';
        } else {
            $stmt = $pdo->prepare('INSERT INTO kegiatan_harian (mahasiswa_id, tanggal, judul, deskripsi) VALUES (?,?,?,?)');
            $stmt->execute([$pesertaId, $today, $judul, $deskripsi]);
            $flashLocal = 'Kegiatan hari ini berhasil dicatat.';
        }
    }
}

// Ambil ulang data absensi hari ini (setelah kemungkinan update)
$stmt = $pdo->prepare('SELECT * FROM absensi WHERE mahasiswa_id = ? AND tanggal = ?');
$stmt->execute([$pesertaId, $today]);
$absenHariIni = $stmt->fetch();

// Rekap absensi
$rekap = $pdo->prepare("
    SELECT
      COUNT(*) AS total_hadir,
      SUM(status_masuk = 'tepat_waktu') AS total_tepat_waktu,
      SUM(status_masuk = 'terlambat') AS total_terlambat
    FROM absensi WHERE mahasiswa_id = ?
");
$rekap->execute([$pesertaId]);
$rekap = $rekap->fetch();

$stmt = $pdo->prepare('SELECT * FROM absensi WHERE mahasiswa_id = ? ORDER BY tanggal DESC LIMIT 15');
$stmt->execute([$pesertaId]);
$riwayatAbsen = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM kegiatan_harian WHERE mahasiswa_id = ? ORDER BY tanggal DESC, created_at DESC LIMIT 10');
$stmt->execute([$pesertaId]);
$riwayatKegiatan = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM sertifikat WHERE mahasiswa_id = ? ORDER BY tanggal_upload DESC');
$stmt->execute([$pesertaId]);
$sertifikatList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
<title>Portal Saya | SIMAK Mahasiswa & PKL</title>
<link href="<?= BASE_URL ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark app-navbar">
  <div class="container-fluid">
    <span class="navbar-brand"><i class="bi bi-mortarboard-fill me-1"></i>Portal Saya</span>
    <a href="<?= BASE_URL ?>/portal_logout.php" class="btn btn-sm btn-outline-light">
      <i class="bi bi-box-arrow-right me-1"></i>Keluar Portal
    </a>
  </div>
</nav>
<main class="container py-4" style="max-width:900px">

  <?php if ($flashLocal): ?>
    <div class="alert alert-<?= $flashType ?>"><?= e($flashLocal) ?></div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap gap-3 align-items-center">
      <?php if ($peserta['foto']): ?>
        <img src="<?= FOTO_URL . rawurlencode($peserta['foto']) ?>" class="foto-preview-lg">
      <?php else: ?>
        <div class="foto-preview-lg d-flex align-items-center justify-content-center bg-light text-muted"><i class="bi bi-person fs-1"></i></div>
      <?php endif; ?>
      <div>
        <h5 class="mb-1"><?= e($peserta['nama']) ?> <?= badgeTipe($peserta['tipe']) ?></h5>
        <div class="text-muted small">
          <?= e(labelTipe($peserta['tipe'])) ?> &middot;
          <?= e(nomorIdentitas($peserta)) ?> &middot;
          <?= e($peserta['prodi']) ?>
          <?php if ($peserta['tipe'] === 'siswa_pkl'): ?> &middot; <?= e($peserta['asal_sekolah']) ?><?php endif; ?>
        </div>
        <div class="text-muted small">Pembimbing: <?= e($peserta['nama_dosen'] ?? '-') ?></div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card card-stat bg-brand p-3 text-center">
        <div class="small">Total Hadir</div>
        <div class="fs-4 fw-bold"><?= (int)$rekap['total_hadir'] ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card card-stat p-3 text-center" style="background:#16a34a">
        <div class="small">Tepat Waktu</div>
        <div class="fs-4 fw-bold"><?= (int)$rekap['total_tepat_waktu'] ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card card-stat p-3 text-center" style="background:#dc2626">
        <div class="small">Terlambat</div>
        <div class="fs-4 fw-bold"><?= (int)$rekap['total_terlambat'] ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card card-stat p-3 text-center" style="background:#8b5cf6">
        <div class="small">Sertifikat</div>
        <div class="fs-4 fw-bold"><?= count($sertifikatList) ?></div>
      </div>
    </div>
  </div>

  <!-- Absensi -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Absensi Hari Ini &mdash; <?= formatTanggal($today) ?></div>
    <div class="card-body">
      <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i><?= keteranganPatokanJam($tipe) ?></p>
      <div class="row g-3 align-items-center">
        <div class="col-md-4">
          <div class="text-muted small">Jam Masuk</div>
          <div class="fs-5 fw-bold"><?= formatJam($absenHariIni['jam_masuk'] ?? null) ?></div>
          <?= badgeStatusAbsen($absenHariIni['status_masuk'] ?? null) ?>
        </div>
        <div class="col-md-4">
          <div class="text-muted small">Jam Keluar</div>
          <div class="fs-5 fw-bold"><?= formatJam($absenHariIni['jam_keluar'] ?? null) ?></div>
          <?= badgeStatusAbsen($absenHariIni['status_keluar'] ?? null) ?>
        </div>
        <div class="col-md-4 d-flex gap-2">
          <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="aksi" value="masuk">
            <button type="submit" class="btn btn-success" <?= $absenHariIni ? 'disabled' : '' ?>>Absen Masuk</button>
          </form>
          <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="aksi" value="keluar">
            <button type="submit" class="btn btn-outline-danger"
              <?= (!$absenHariIni || !$absenHariIni['jam_masuk'] || $absenHariIni['jam_keluar']) ? 'disabled' : '' ?>>Absen Keluar</button>
          </form>
        </div>
      </div>

      <hr>
      <h6 class="fw-semibold">Riwayat Absensi Terakhir</h6>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead><tr><th>Tanggal</th><th>Masuk</th><th>Status</th><th>Keluar</th><th>Status</th></tr></thead>
          <tbody>
=======
<meta name="theme-color" content="#fbebe2">
<title>Portal saya | SIMAK Mahasiswa &amp; PKL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,400;8..144,500;8..144,600;8..144,700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/material3.css" rel="stylesheet">
</head>
<body>

<header class="m3-appbar">
  <a class="m3-appbar__brand" href="<?= BASE_URL ?>/portal_dashboard.php">
    <img src="<?= BASE_URL ?>/assets/images/logo-stikom.png" alt="" class="m3-appbar__logo">
    <span class="m3-appbar__title">Portal saya</span>
  </a>
  <span class="m3-appbar__spacer"></span>
  <a href="<?= BASE_URL ?>/portal_logout.php" class="m3-btn m3-btn--tonal m3-btn--sm">
    <span class="m3-icon m3-icon--sm">logout</span>Keluar
  </a>
</header>

<main class="m3-main" style="max-width:1000px;margin:0 auto;border-radius:0">

  <?php if ($flashLocal): ?>
    <div class="m3-banner m3-banner--<?= $flashType === 'danger' ? 'error' : 'success' ?>">
      <span class="m3-icon"><?= $flashType === 'danger' ? 'error' : 'check_circle' ?></span>
      <span class="m3-grow"><?= e($flashLocal) ?></span>
      <button type="button" class="m3-banner__close" data-m3-banner-close aria-label="Tutup pesan">
        <span class="m3-icon m3-icon--sm">close</span>
      </button>
    </div>
  <?php endif; ?>

  <!-- Identitas peserta -->
  <section class="m3-card m3-card--filled m3-mb-3">
    <div class="m3-card__body">
      <div class="m3-row m3-row--wrap" style="gap:20px">
        <?php if ($peserta['foto']): ?>
          <img src="<?= FOTO_URL . rawurlencode($peserta['foto']) ?>" alt="" class="m3-avatar m3-avatar--lg">
        <?php else: ?>
          <div class="m3-avatar m3-avatar--lg"><span class="m3-icon m3-icon--lg">person</span></div>
        <?php endif; ?>
        <div class="m3-grow">
          <div class="m3-row m3-gap-sm m3-mb-1" style="flex-wrap:wrap">
            <h1 class="m3-headline-small m3-mb-0"><?= e($peserta['nama']) ?></h1>
            <?= badgeTipe($peserta['tipe']) ?>
          </div>
          <p class="m3-body-medium m3-muted m3-mb-0">
            <?= e(nomorIdentitas($peserta)) ?> &middot; <?= e($peserta['prodi']) ?>
            <?php if ($peserta['tipe'] === 'siswa_pkl' && $peserta['asal_sekolah']): ?>
              &middot; <?= e($peserta['asal_sekolah']) ?>
            <?php endif; ?>
          </p>
          <p class="m3-body-medium m3-muted m3-mb-0">Pembimbing: <?= e($peserta['nama_dosen'] ?? 'Belum ditentukan') ?></p>
        </div>
      </div>
    </div>
  </section>

  <!-- Rekap kehadiran -->
  <div class="m3-cards-grid m3-mb-4">
    <div class="m3-stat m3-stat--primary">
      <span class="m3-stat__label">Total hadir</span>
      <span class="m3-stat__value"><?= (int)$rekap['total_hadir'] ?></span>
      <span class="m3-stat__meta">hari tercatat</span>
      <span class="m3-icon m3-stat__icon">event_available</span>
    </div>
    <div class="m3-stat m3-stat--success">
      <span class="m3-stat__label">Tepat waktu</span>
      <span class="m3-stat__value"><?= (int)$rekap['total_tepat_waktu'] ?></span>
      <span class="m3-icon m3-stat__icon">schedule</span>
    </div>
    <div class="m3-stat m3-stat--error">
      <span class="m3-stat__label">Terlambat</span>
      <span class="m3-stat__value"><?= (int)$rekap['total_terlambat'] ?></span>
      <span class="m3-icon m3-stat__icon">running_late</span>
    </div>
    <div class="m3-stat m3-stat--tertiary">
      <span class="m3-stat__label">Sertifikat</span>
      <span class="m3-stat__value"><?= count($sertifikatList) ?></span>
      <span class="m3-icon m3-stat__icon">workspace_premium</span>
    </div>
  </div>

  <!-- Absensi hari ini -->
  <section class="m3-card m3-card--elevated m3-mb-3">
    <div class="m3-card__header">Absensi hari ini &mdash; <?= formatTanggal($today) ?></div>
    <div class="m3-card__body">
      <p class="m3-body-medium m3-muted"><?= e(keteranganPatokanJam($tipe)) ?></p>

      <div class="m3-grid m3-mb-3">
        <div class="m3-col-4">
          <div class="m3-body-small m3-muted">Jam masuk</div>
          <div class="m3-headline-small"><?= formatJam($absenHariIni['jam_masuk'] ?? null) ?></div>
          <?= badgeStatusAbsen($absenHariIni['status_masuk'] ?? null) ?>
        </div>
        <div class="m3-col-4">
          <div class="m3-body-small m3-muted">Jam keluar</div>
          <div class="m3-headline-small"><?= formatJam($absenHariIni['jam_keluar'] ?? null) ?></div>
          <?= badgeStatusAbsen($absenHariIni['status_keluar'] ?? null) ?>
        </div>
        <div class="m3-col-4">
          <div class="m3-row m3-gap-sm" style="height:100%;align-items:flex-end;flex-wrap:wrap">
            <form method="post">
              <?= csrfField() ?>
              <input type="hidden" name="aksi" value="masuk">
              <button type="submit" class="m3-btn m3-btn--filled" <?= $absenHariIni ? 'disabled' : '' ?>>
                <span class="m3-icon m3-icon--sm">login</span>Absen masuk
              </button>
            </form>
            <form method="post">
              <?= csrfField() ?>
              <input type="hidden" name="aksi" value="keluar">
              <button type="submit" class="m3-btn m3-btn--outlined"
                <?= (!$absenHariIni || !$absenHariIni['jam_masuk'] || $absenHariIni['jam_keluar']) ? 'disabled' : '' ?>>
                <span class="m3-icon m3-icon--sm">logout</span>Absen keluar
              </button>
            </form>
          </div>
        </div>
      </div>

      <h2 class="m3-title-medium m3-mt-3 m3-mb-2">Riwayat absensi terakhir</h2>
      <div class="m3-table-scroll">
        <table class="m3-table">
          <thead>
            <tr><th>Tanggal</th><th>Masuk</th><th>Status</th><th>Keluar</th><th>Status</th></tr>
          </thead>
          <tbody>
          <?php if (!$riwayatAbsen): ?>
            <tr><td colspan="5" class="m3-table__empty">Belum ada riwayat absensi.</td></tr>
          <?php endif; ?>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
          <?php foreach ($riwayatAbsen as $r): ?>
            <tr>
              <td><?= formatTanggal($r['tanggal']) ?></td>
              <td><?= formatJam($r['jam_masuk']) ?></td>
              <td><?= badgeStatusAbsen($r['status_masuk']) ?></td>
              <td><?= formatJam($r['jam_keluar']) ?></td>
              <td><?= badgeStatusAbsen($r['status_keluar']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
<<<<<<< HEAD
  </div>

  <!-- Kegiatan Harian -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Catat Kegiatan Hari Ini &mdash; <?= formatTanggal($today) ?></div>
    <div class="card-body">
      <form method="post" class="mb-3">
        <?= csrfField() ?>
        <input type="hidden" name="aksi" value="kegiatan">
        <div class="mb-2">
          <label class="form-label">Judul Kegiatan</label>
          <input type="text" name="judul" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Deskripsi</label>
          <textarea name="deskripsi" class="form-control" rows="2" required></textarea>
        </div>
        <button type="submit" class="btn btn-success btn-sm">Simpan Kegiatan</button>
      </form>
      <hr>
      <h6 class="fw-semibold">Kegiatan Terakhir</h6>
      <?php foreach ($riwayatKegiatan as $k): ?>
        <div class="border-bottom py-2">
          <div class="d-flex justify-content-between">
            <strong><?= e($k['judul']) ?></strong>
            <small class="text-muted"><?= formatTanggal($k['tanggal']) ?></small>
          </div>
          <div class="small text-muted"><?= nl2br(e($k['deskripsi'])) ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (!$riwayatKegiatan): ?><p class="text-muted small mb-0">Belum ada kegiatan tercatat.</p><?php endif; ?>
    </div>
  </div>

  <!-- Sertifikat -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Sertifikat Saya</div>
    <div class="card-body">
      <p class="text-muted small">Sertifikat diunggah oleh Operator. Hubungi operator jika sertifikat Anda belum muncul di sini.</p>
      <?php if (!$sertifikatList): ?>
        <p class="text-muted">Belum ada sertifikat.</p>
      <?php endif; ?>
      <div class="row g-3">
      <?php foreach ($sertifikatList as $s): ?>
        <div class="col-md-6">
          <div class="border rounded p-3 d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold"><?= e($s['judul']) ?></div>
              <small class="text-muted"><?= formatTanggal($s['tanggal_upload']) ?></small>
            </div>
            <a href="<?= UPLOAD_URL . rawurlencode($s['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-success">
              <i class="bi bi-download"></i>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>

</main>
<script src="<?= BASE_URL ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
=======
  </section>

  <!-- Kegiatan harian -->
  <section class="m3-card m3-card--elevated m3-mb-3">
    <div class="m3-card__header">Kegiatan hari ini &mdash; <?= formatTanggal($today) ?></div>
    <div class="m3-card__body">
      <form method="post" class="m3-mb-3">
        <?= csrfField() ?>
        <input type="hidden" name="aksi" value="kegiatan">
        <div class="m3-field m3-mb-2">
          <label class="m3-field__label" for="judul">Judul kegiatan</label>
          <input id="judul" type="text" name="judul" class="m3-input" required
                 placeholder="Contoh: Menyusun laporan mingguan">
        </div>
        <div class="m3-field m3-mb-2">
          <label class="m3-field__label" for="deskripsi">Deskripsi</label>
          <textarea id="deskripsi" name="deskripsi" class="m3-textarea" rows="3" required
                    placeholder="Ceritakan apa yang dikerjakan hari ini"></textarea>
        </div>
        <button type="submit" class="m3-btn m3-btn--filled">
          <span class="m3-icon m3-icon--sm">add</span>Simpan kegiatan
        </button>
      </form>

      <h2 class="m3-title-medium m3-mt-3 m3-mb-1">Kegiatan terakhir</h2>
      <?php if (!$riwayatKegiatan): ?>
        <p class="m3-body-medium m3-muted">Belum ada kegiatan tercatat. Mulai dari form di atas.</p>
      <?php endif; ?>
      <?php foreach ($riwayatKegiatan as $k): ?>
        <div class="m3-list-row">
          <div class="m3-row--between">
            <strong class="m3-title-small"><?= e($k['judul']) ?></strong>
            <span class="m3-body-small m3-muted"><?= formatTanggal($k['tanggal']) ?></span>
          </div>
          <div class="m3-body-medium m3-muted"><?= nl2br(e($k['deskripsi'])) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Sertifikat -->
  <section class="m3-card m3-card--elevated">
    <div class="m3-card__header">Sertifikat saya</div>
    <div class="m3-card__body">
      <?php if (!$sertifikatList): ?>
        <p class="m3-body-medium m3-muted m3-mb-0">
          Belum ada sertifikat. Sertifikat diunggah oleh operator &mdash; hubungi operator bila sertifikat Anda belum muncul.
        </p>
      <?php else: ?>
        <div class="m3-cards-grid m3-cards-grid--wide">
        <?php foreach ($sertifikatList as $s): ?>
          <div class="m3-card m3-card--outlined">
            <div class="m3-card__body m3-card__body--tight">
              <div class="m3-row" style="gap:14px">
                <span class="m3-icon m3-icon--lg" style="color:var(--m3-primary)">workspace_premium</span>
                <div class="m3-grow">
                  <div class="m3-title-small"><?= e($s['judul']) ?></div>
                  <div class="m3-body-small m3-muted"><?= formatTanggal($s['tanggal_upload']) ?></div>
                </div>
                <a href="<?= UPLOAD_URL . rawurlencode($s['file_path']) ?>" target="_blank"
                   class="m3-icon-btn m3-icon-btn--primary" title="Unduh sertifikat" aria-label="Unduh sertifikat">
                  <span class="m3-icon">download</span>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

</main>

<footer class="m3-footer">&copy; <?= date('Y') ?> STIKOM 22 Januari</footer>
<script src="<?= BASE_URL ?>/assets/js/material3.js"></script>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
</body>
</html>
