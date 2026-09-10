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
</body>
</html>
