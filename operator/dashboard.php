<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$pageTitle = 'Dashboard Operator';

$totalMhs = $pdo->query("SELECT COUNT(*) FROM mahasiswa WHERE tipe='mahasiswa'")->fetchColumn();
$totalPkl = $pdo->query("SELECT COUNT(*) FROM mahasiswa WHERE tipe='siswa_pkl'")->fetchColumn();
$totalDosen = $pdo->query("SELECT COUNT(*) FROM dosen")->fetchColumn();
$totalSertifikat = $pdo->query("SELECT COUNT(*) FROM sertifikat")->fetchColumn();
$absenHariIni = $pdo->query("SELECT COUNT(*) FROM absensi WHERE tanggal = CURDATE()")->fetchColumn();
$terlambatHariIni = $pdo->query("SELECT COUNT(*) FROM absensi WHERE tanggal = CURDATE() AND status_masuk='terlambat'")->fetchColumn();

// $errors = [];
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ganti_password') {
//     if (!csrfValid()) {
//         $errors[] = 'Sesi form kadaluarsa, coba lagi.';
//     } else {
//         $username = trim($_POST['username'] ?? '');
//         $nama = trim($_POST['nama'] ?? '');
//         $email = trim($_POST['email'] ?? '');
//         if ($username === '' || $nama === '') {
//             $errors[] = 'Username dan nama wajib diisi.';
//         }
//                 header('Location: ' . BASE_URL . '/operator/users.php');
//                 exit;
//             }
//         }

$stmt = $pdo->query("
  SELECT k.tanggal, k.judul, m.nama AS nama_peserta, m.tipe
  FROM kegiatan_harian k JOIN mahasiswa m ON m.id = k.mahasiswa_id
  ORDER BY k.created_at DESC LIMIT 6
");
$kegiatanTerbaru = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4 d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  Dashboard Operator
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalGantiPassword">
      <i class="bi bi-key me-1"></i>Ganti Password
    </button>
</h4>


<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card card-stat bg-brand p-3">
      <div class="small">Mahasiswa</div>
      <div class="fs-3 fw-bold"><?= (int)$totalMhs ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card card-stat p-3" style="background:#0891b2">
      <div class="small">Siswa PKL</div>
      <div class="fs-3 fw-bold"><?= (int)$totalPkl ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card card-stat p-3" style="background:#f59e0b">
      <div class="small">Absen Hari Ini (<?= (int)$terlambatHariIni ?> terlambat)</div>
      <div class="fs-3 fw-bold"><?= (int)$absenHariIni ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card card-stat p-3" style="background:#8b5cf6">
      <div class="small">Sertifikat</div>
      <div class="fs-3 fw-bold"><?= (int)$totalSertifikat ?></div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white fw-semibold">Kegiatan Harian Terbaru</div>
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead><tr><th>Tanggal</th><th>Nama</th><th>Tipe</th><th>Judul Kegiatan</th></tr></thead>
      <tbody>
      <?php if (!$kegiatanTerbaru): ?>
        <tr><td colspan="4" class="text-center text-muted py-3">Belum ada kegiatan tercatat.</td></tr>
      <?php endif; ?>
      <?php foreach ($kegiatanTerbaru as $k): ?>
        <tr>
          <td><?= formatTanggal($k['tanggal']) ?></td>
          <td><?= e($k['nama_peserta']) ?></td>
          <td><?= badgeTipe($k['tipe']) ?></td>
          <td><?= e($k['judul']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalGantiPassword" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="ganti_password">
      <div class="modal-header">
        <h5 class="modal-title">Ganti Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Password lama</label><input type="password" name="password_lama" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password baru</label><input type="password" name="password_baru" class="form-control" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success">Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
