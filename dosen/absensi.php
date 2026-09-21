<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['dosen']);

$dosenId = currentDosenId();
$pageTitle = 'Absensi Bimbingan';

$mahasiswaFilter = isset($_GET['mahasiswa_id']) ? (int)$_GET['mahasiswa_id'] : null;
$tanggal = $_GET['tanggal'] ?? '';
$bulan = $_GET['bulan'] ?? '';
if (!validBulan($bulan)) $bulan = '';

$sql = "SELECT a.*, m.nama AS nama_peserta, m.nim, m.nisn, m.tipe
        FROM absensi a JOIN mahasiswa m ON m.id = a.mahasiswa_id WHERE m.dosen_id = ?";
$params = [$dosenId];
if ($mahasiswaFilter) { $sql .= " AND m.id = ?"; $params[] = $mahasiswaFilter; }
if ($bulan !== '') { $sql .= " AND DATE_FORMAT(a.tanggal,'%Y-%m') = ?"; $params[] = $bulan; }
if ($tanggal !== '') { $sql .= " AND a.tanggal = ?"; $params[] = $tanggal; }
$sql .= " ORDER BY a.tanggal DESC, m.nama ASC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$absensiList = $stmt->fetchAll();

$sqlBulan = "SELECT DATE_FORMAT(a.tanggal,'%Y-%m') AS bulan, COUNT(*) AS total,
    SUM(a.status_masuk='tepat_waktu') AS tepat, SUM(a.status_masuk='terlambat') AS terlambat
    FROM absensi a JOIN mahasiswa m ON m.id = a.mahasiswa_id WHERE m.dosen_id = ?";
$paramsBulan = [$dosenId];
if ($mahasiswaFilter) { $sqlBulan .= " AND m.id = ?"; $paramsBulan[] = $mahasiswaFilter; }
$sqlBulan .= " GROUP BY DATE_FORMAT(a.tanggal,'%Y-%m') ORDER BY bulan DESC LIMIT 24";
$stmt = $pdo->prepare($sqlBulan);
$stmt->execute($paramsBulan);
$rekapBulanan = $stmt->fetchAll();

$mhsList = $pdo->prepare('SELECT id, nama FROM mahasiswa WHERE dosen_id = ? ORDER BY nama');
$mhsList->execute([$dosenId]);
$mhsList = $mhsList->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h1 class="m3-page-title">Absensi peserta bimbingan</h1>

<form class="m3-toolbar">
  <select name="mahasiswa_id" class="m3-select" style="width:auto;min-width:220px">
    <option value="">Semua peserta</option>
    <?php foreach ($mhsList as $m): ?>
      <option value="<?= $m['id'] ?>" <?= $mahasiswaFilter === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['nama']) ?></option>
    <?php endforeach; ?>
  </select>
  <input type="month" name="bulan" class="m3-input" style="width:auto" value="<?= e($bulan) ?>" title="Filter bulan">
  <input type="date" name="tanggal" class="m3-input" style="width:auto" value="<?= e($tanggal) ?>">
  <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">filter_alt</span>Terapkan</button>
  <a href="absensi.php" class="m3-btn m3-btn--text">Atur ulang</a>
</form>

<?php if ($rekapBulanan): ?>
<section class="m3-card m3-card--filled m3-mb-3">
  <div class="m3-card__header">Pengelompokan per bulan</div>
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead><tr><th>Bulan</th><th>Hadir</th><th>Tepat waktu</th><th>Terlambat</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rekapBulanan as $rb): ?>
        <tr>
          <td><?= e(formatBulanId($rb['bulan'])) ?></td>
          <td><?= (int)$rb['total'] ?></td><td><?= (int)$rb['tepat'] ?></td><td><?= (int)$rb['terlambat'] ?></td>
          <td><a class="m3-btn m3-btn--text m3-btn--sm" href="?mahasiswa_id=<?= (int)$mahasiswaFilter ?>&bulan=<?= e($rb['bulan']) ?>">Lihat</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php endif; ?>

<section class="m3-table-wrap">
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead>
        <tr><th>Tanggal</th><th>Tipe</th><th>Nama</th><th>Masuk</th><th>Status</th><th>Keluar</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php if (!$absensiList): ?>
        <tr><td colspan="7" class="m3-table__empty">Tidak ada data absensi untuk filter ini.</td></tr>
      <?php endif; ?>
      <?php foreach ($absensiList as $a): ?>
        <tr>
          <td><?= formatTanggal($a['tanggal']) ?></td>
          <td><?= badgeTipe($a['tipe']) ?></td>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
