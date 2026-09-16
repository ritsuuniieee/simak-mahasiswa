<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['dosen']);

$dosenId = currentDosenId();
$pageTitle = 'Dashboard Dosen';

$totalMhs = $pdo->prepare("SELECT COUNT(*) FROM mahasiswa WHERE dosen_id = ? AND tipe='mahasiswa'");
$totalMhs->execute([$dosenId]); $totalMhs = $totalMhs->fetchColumn();

$totalPkl = $pdo->prepare("SELECT COUNT(*) FROM mahasiswa WHERE dosen_id = ? AND tipe='siswa_pkl'");
$totalPkl->execute([$dosenId]); $totalPkl = $totalPkl->fetchColumn();

$absenHariIni = $pdo->prepare("SELECT COUNT(*) FROM absensi a JOIN mahasiswa m ON m.id=a.mahasiswa_id WHERE m.dosen_id=? AND a.tanggal=CURDATE()");
$absenHariIni->execute([$dosenId]); $absenHariIni = $absenHariIni->fetchColumn();

$stmt = $pdo->prepare("
    SELECT k.tanggal, k.judul, m.nama AS nama_peserta, m.tipe
    FROM kegiatan_harian k JOIN mahasiswa m ON m.id = k.mahasiswa_id
    WHERE m.dosen_id = ? ORDER BY k.created_at DESC LIMIT 6
");
$stmt->execute([$dosenId]);
$kegiatanTerbaru = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h1 class="m3-page-title">Dashboard dosen</h1>

<div class="m3-cards-grid m3-mb-4">
  <div class="m3-stat m3-stat--primary">
    <span class="m3-stat__label">Mahasiswa bimbingan</span>
    <span class="m3-stat__value"><?= (int)$totalMhs ?></span>
    <span class="m3-icon m3-stat__icon">school</span>
  </div>
  <div class="m3-stat m3-stat--tertiary">
    <span class="m3-stat__label">Siswa PKL bimbingan</span>
    <span class="m3-stat__value"><?= (int)$totalPkl ?></span>
    <span class="m3-icon m3-stat__icon">engineering</span>
  </div>
  <div class="m3-stat m3-stat--secondary">
    <span class="m3-stat__label">Absen hari ini</span>
    <span class="m3-stat__value"><?= (int)$absenHariIni ?></span>
    <span class="m3-icon m3-stat__icon">event_available</span>
  </div>
</div>

<section class="m3-table-wrap">
  <div class="m3-card__header">Kegiatan harian terbaru</div>
  <div class="m3-table-scroll m3-mt-2">
    <table class="m3-table">
      <thead><tr><th>Tanggal</th><th>Nama</th><th>Tipe</th><th>Judul kegiatan</th></tr></thead>
      <tbody>
      <?php if (!$kegiatanTerbaru): ?>
        <tr><td colspan="4" class="m3-table__empty">Belum ada kegiatan tercatat.</td></tr>
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
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
