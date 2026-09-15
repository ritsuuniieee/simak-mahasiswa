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
<<<<<<< HEAD
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
=======
<h1 class="m3-page-title">Rekap absensi</h1>

<div class="m3-cards-grid m3-mb-3">
  <div class="m3-stat m3-stat--primary">
    <span class="m3-stat__label">Total rekaman</span>
    <span class="m3-stat__value"><?= (int)$rekap['total'] ?></span>
    <span class="m3-icon m3-stat__icon">fact_check</span>
  </div>
  <div class="m3-stat m3-stat--success">
    <span class="m3-stat__label">Tepat waktu</span>
    <span class="m3-stat__value"><?= (int)$rekap['tepat_waktu'] ?></span>
    <span class="m3-icon m3-stat__icon">schedule</span>
  </div>
  <div class="m3-stat m3-stat--error">
    <span class="m3-stat__label">Terlambat</span>
    <span class="m3-stat__value"><?= (int)$rekap['terlambat'] ?></span>
    <span class="m3-icon m3-stat__icon">running_late</span>
  </div>
  <div class="m3-stat m3-stat--warning">
    <span class="m3-stat__label">Pulang cepat</span>
    <span class="m3-stat__value"><?= (int)$rekap['pulang_cepat'] ?></span>
    <span class="m3-icon m3-stat__icon">logout</span>
  </div>
</div>

<form class="m3-toolbar">
  <input type="date" name="tanggal" class="m3-input" style="width:auto" value="<?= e($tanggal) ?>">
  <select name="tipe" class="m3-select" style="width:auto">
    <option value="">Semua tipe</option>
    <option value="mahasiswa" <?= $tipeFilter === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
    <option value="siswa_pkl" <?= $tipeFilter === 'siswa_pkl' ? 'selected' : '' ?>>Siswa PKL</option>
  </select>
  <div class="m3-toolbar__search">
    <input type="text" name="q" class="m3-input" value="<?= e($search) ?>" placeholder="Cari nama, NIM, atau NISN">
  </div>
  <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">filter_alt</span>Terapkan</button>
  <a href="absensi_rekap.php" class="m3-btn m3-btn--text">Atur ulang</a>
</form>

<section class="m3-table-wrap">
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead>
        <tr><th>Tanggal</th><th>Tipe</th><th>No. ID</th><th>Nama</th><th>Masuk</th><th>Status</th><th>Keluar</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php if (!$absensiList): ?>
        <tr><td colspan="8" class="m3-table__empty">Tidak ada data absensi untuk filter ini.</td></tr>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
      <?php endif; ?>
      <?php foreach ($absensiList as $a): ?>
        <tr>
          <td><?= formatTanggal($a['tanggal']) ?></td>
          <td><?= badgeTipe($a['tipe']) ?></td>
<<<<<<< HEAD
          <td><?= e($a['tipe']==='siswa_pkl' ? $a['nisn'] : $a['nim']) ?></td>
=======
          <td><?= e($a['tipe'] === 'siswa_pkl' ? $a['nisn'] : $a['nim']) ?></td>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
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
<<<<<<< HEAD
</div>
<p class="text-muted small mt-2">Tabel menampilkan maksimal 300 baris terbaru; kartu ringkasan di atas menghitung seluruh data sesuai filter.</p>
=======
</section>
<p class="m3-body-small m3-muted m3-mt-2">
  Tabel menampilkan maksimal 300 baris terbaru. Kartu ringkasan di atas menghitung seluruh data sesuai filter.
</p>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)

<?php include __DIR__ . '/../includes/footer.php'; ?>
