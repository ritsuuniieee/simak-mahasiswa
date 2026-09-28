<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$pageTitle = 'Rekap Absensi';

$tanggal = $_GET['tanggal'] ?? '';
$bulan = $_GET['bulan'] ?? '';
if (!validBulan($bulan)) $bulan = '';
$search = trim($_GET['q'] ?? '');
$tipeFilter = $_GET['tipe'] ?? '';

$sql = "SELECT a.*, m.nama AS nama_peserta, m.nim, m.nisn, m.tipe, m.prodi
        FROM absensi a JOIN mahasiswa m ON m.id = a.mahasiswa_id WHERE 1=1";
$params = [];
if ($bulan !== '') { $sql .= " AND DATE_FORMAT(a.tanggal,'%Y-%m') = ?"; $params[] = $bulan; }
if ($tanggal !== '') { $sql .= " AND a.tanggal = ?"; $params[] = $tanggal; }
if ($search !== '') { $sql .= " AND (m.nama LIKE ? OR m.nim LIKE ? OR m.nisn LIKE ?)"; $like="%$search%"; array_push($params,$like,$like,$like); }
if (in_array($tipeFilter, ['mahasiswa','siswa_pkl'], true)) { $sql .= " AND m.tipe = ?"; $params[] = $tipeFilter; }
$sql .= " ORDER BY a.tanggal DESC, m.nama ASC LIMIT 300";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$absensiList = $stmt->fetchAll();

// Ringkasan rekap (mengikuti filter tanggal/bulan & tipe yang sama, tanpa limit)
$sqlRekap = "SELECT
    COUNT(*) AS total,
    SUM(status_masuk='tepat_waktu') AS tepat_waktu,
    SUM(status_masuk='terlambat') AS terlambat,
    SUM(status_keluar='pulang_cepat') AS pulang_cepat
    FROM absensi a JOIN mahasiswa m ON m.id = a.mahasiswa_id WHERE 1=1";
$paramsRekap = [];
if ($bulan !== '') { $sqlRekap .= " AND DATE_FORMAT(a.tanggal,'%Y-%m') = ?"; $paramsRekap[] = $bulan; }
if ($tanggal !== '') { $sqlRekap .= " AND a.tanggal = ?"; $paramsRekap[] = $tanggal; }
if (in_array($tipeFilter, ['mahasiswa','siswa_pkl'], true)) { $sqlRekap .= " AND m.tipe = ?"; $paramsRekap[] = $tipeFilter; }
$stmt = $pdo->prepare($sqlRekap);
$stmt->execute($paramsRekap);
$rekap = $stmt->fetch();

// Pengelompokan per bulan (mengikuti filter tipe + pencarian, abaikan tanggal/bulan spesifik)
$sqlBulan = "SELECT DATE_FORMAT(a.tanggal,'%Y-%m') AS bulan,
    COUNT(*) AS total,
    SUM(a.status_masuk='tepat_waktu') AS tepat_waktu,
    SUM(a.status_masuk='terlambat') AS terlambat,
    SUM(a.status_keluar='pulang_cepat') AS pulang_cepat
    FROM absensi a JOIN mahasiswa m ON m.id = a.mahasiswa_id WHERE 1=1";
$paramsBulan = [];
if ($search !== '') { $sqlBulan .= " AND (m.nama LIKE ? OR m.nim LIKE ? OR m.nisn LIKE ?)"; $like="%$search%"; array_push($paramsBulan,$like,$like,$like); }
if (in_array($tipeFilter, ['mahasiswa','siswa_pkl'], true)) { $sqlBulan .= " AND m.tipe = ?"; $paramsBulan[] = $tipeFilter; }
$sqlBulan .= " GROUP BY DATE_FORMAT(a.tanggal,'%Y-%m') ORDER BY bulan DESC LIMIT 24";
$stmt = $pdo->prepare($sqlBulan);
$stmt->execute($paramsBulan);
$rekapBulanan = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
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
  <input type="month" name="bulan" class="m3-input" style="width:auto" value="<?= e($bulan) ?>" title="Filter bulan">
  <input type="date" name="tanggal" class="m3-input" style="width:auto" value="<?= e($tanggal) ?>" title="Filter tanggal spesifik">
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
<?php if ($bulan): ?>
  <div class="m3-banner m3-banner--info m3-mb-3">
    <span class="m3-icon">calendar_month</span>
    <span class="m3-grow">Menampilkan bulan <strong><?= e(formatBulanId($bulan)) ?></strong>.</span>
  </div>
<?php endif; ?>

<section class="m3-card m3-card--filled m3-mb-3">
  <div class="m3-card__header">Pengelompokan per bulan<?= $tipeFilter ? ' &middot; ' . e(labelTipe($tipeFilter)) : '' ?></div>
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead><tr><th>Bulan</th><th>Total</th><th>Tepat waktu</th><th>Terlambat</th><th>Pulang cepat</th><th></th></tr></thead>
      <tbody>
      <?php if (!$rekapBulanan): ?>
        <tr><td colspan="6" class="m3-table__empty">Belum ada data per bulan.</td></tr>
      <?php endif; ?>
      <?php foreach ($rekapBulanan as $rb): ?>
        <tr>
          <td><?= e(formatBulanId($rb['bulan'])) ?></td>
          <td><?= (int)$rb['total'] ?></td>
          <td><?= (int)$rb['tepat_waktu'] ?></td>
          <td><?= (int)$rb['terlambat'] ?></td>
          <td><?= (int)$rb['pulang_cepat'] ?></td>
          <td><a class="m3-btn m3-btn--text m3-btn--sm" href="?bulan=<?= e($rb['bulan']) ?>&tipe=<?= e($tipeFilter) ?>&q=<?= urlencode($search) ?>">Lihat</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="m3-table-wrap">
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead>
        <tr><th>Tanggal</th><th>Tipe</th><th>No. ID</th><th>Nama</th><th>Masuk</th><th>Status</th><th>Keluar</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php if (!$absensiList): ?>
        <tr><td colspan="8" class="m3-table__empty">Tidak ada data absensi untuk filter ini.</td></tr>
      <?php endif; ?>
      <?php foreach ($absensiList as $a): ?>
        <tr>
          <td><?= formatTanggal($a['tanggal']) ?></td>
          <td><?= badgeTipe($a['tipe']) ?></td>
          <td><?= e($a['tipe'] === 'siswa_pkl' ? $a['nisn'] : $a['nim']) ?></td>
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
</section>
<p class="m3-body-small m3-muted m3-mt-2">
  Tabel menampilkan maksimal 300 baris terbaru. Kartu ringkasan di atas menghitung seluruh data sesuai filter.
</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
