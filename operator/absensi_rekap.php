<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$pageTitle = 'Rekap Absensi';

$tanggal = $_GET['tanggal'] ?? '';
$search = trim($_GET['q'] ?? '');
$tipeFilter = $_GET['tipe'] ?? '';

$sql = "SELECT a.*, m.nama AS nama_peserta, m.nim, m.nisn, m.tipe, m.prodi
        FROM absensi a JOIN mahasiswa m ON m.id = a.mahasiswa_id WHERE 1=1";
$params = [];
if ($tanggal !== '') { $sql .= " AND a.tanggal = ?"; $params[] = $tanggal; }
if ($search !== '') { $sql .= " AND (m.nama LIKE ? OR m.nim LIKE ? OR m.nisn LIKE ?)"; $like="%$search%"; array_push($params,$like,$like,$like); }
if (in_array($tipeFilter, ['mahasiswa','siswa_pkl'], true)) { $sql .= " AND m.tipe = ?"; $params[] = $tipeFilter; }
$sql .= " ORDER BY a.tanggal DESC, m.nama ASC LIMIT 300";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$absensiList = $stmt->fetchAll();

// Ringkasan rekap (mengikuti filter tanggal & tipe yang sama, tanpa limit)
$sqlRekap = "SELECT
    COUNT(*) AS total,
    SUM(status_masuk='tepat_waktu') AS tepat_waktu,
    SUM(status_masuk='terlambat') AS terlambat,
    SUM(status_keluar='pulang_cepat') AS pulang_cepat
    FROM absensi a JOIN mahasiswa m ON m.id = a.mahasiswa_id WHERE 1=1";
$paramsRekap = [];
if ($tanggal !== '') { $sqlRekap .= " AND a.tanggal = ?"; $paramsRekap[] = $tanggal; }
if (in_array($tipeFilter, ['mahasiswa','siswa_pkl'], true)) { $sqlRekap .= " AND m.tipe = ?"; $paramsRekap[] = $tipeFilter; }
$stmt = $pdo->prepare($sqlRekap);
$stmt->execute($paramsRekap);
$rekap = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3">Rekap Absensi</h4>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="card card-stat bg-brand p-3 text-center"><div class="small">Total Rekaman</div><div class="fs-4 fw-bold"><?= (int)$rekap['total'] ?></div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-stat p-3 text-center" style="background:#16a34a"><div class="small">Tepat Waktu</div><div class="fs-4 fw-bold"><?= (int)$rekap['tepat_waktu'] ?></div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-stat p-3 text-center" style="background:#dc2626"><div class="small">Terlambat</div><div class="fs-4 fw-bold"><?= (int)$rekap['terlambat'] ?></div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-stat p-3 text-center" style="background:#f59e0b"><div class="small">Pulang Cepat (PKL)</div><div class="fs-4 fw-bold"><?= (int)$rekap['pulang_cepat'] ?></div></div>
  </div>
</div>

<form class="row g-2 mb-3">
  <div class="col-auto"><input type="date" name="tanggal" class="form-control" value="<?= e($tanggal) ?>"></div>
  <div class="col-auto">
    <select name="tipe" class="form-select">
      <option value="">Semua Tipe</option>
      <option value="mahasiswa" <?= $tipeFilter==='mahasiswa'?'selected':'' ?>>Mahasiswa</option>
      <option value="siswa_pkl" <?= $tipeFilter==='siswa_pkl'?'selected':'' ?>>Siswa PKL</option>
    </select>
  </div>
  <div class="col-auto flex-grow-1"><input type="text" name="q" class="form-control" placeholder="Cari nama / NIM / NISN..." value="<?= e($search) ?>"></div>
  <div class="col-auto">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Filter</button>
    <a href="absensi_rekap.php" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></a>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead><tr><th>Tanggal</th><th>Tipe</th><th>No. ID</th><th>Nama</th><th>Masuk</th><th>Status</th><th>Keluar</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (!$absensiList): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data absensi.</td></tr>
      <?php endif; ?>
      <?php foreach ($absensiList as $a): ?>
        <tr>
          <td><?= formatTanggal($a['tanggal']) ?></td>
          <td><?= badgeTipe($a['tipe']) ?></td>
          <td><?= e($a['tipe']==='siswa_pkl' ? $a['nisn'] : $a['nim']) ?></td>
          <td><?= e($a['nama_peserta']) ?></td>
          <td><?= formatJam($a['jam_masuk']) ?></td>
          <td><?= badgeStatusAbsen($a['status_masuk']) ?></td>
          <td><?= formatJam($a['jam_keluar']) ?></td>
          <td><?= badgeStatusAbsen($a['status_keluar']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<p class="text-muted small mt-2">Tabel menampilkan maksimal 300 baris terbaru; kartu ringkasan di atas menghitung seluruh data sesuai filter.</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
