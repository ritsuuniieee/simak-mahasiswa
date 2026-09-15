<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['dosen']);

$dosenId = currentDosenId();
$pageTitle = 'Absensi Bimbingan';

$mahasiswaFilter = isset($_GET['mahasiswa_id']) ? (int)$_GET['mahasiswa_id'] : null;
$tanggal = $_GET['tanggal'] ?? '';

$sql = "SELECT a.*, m.nama AS nama_peserta, m.nim, m.nisn, m.tipe
        FROM absensi a JOIN mahasiswa m ON m.id = a.mahasiswa_id WHERE m.dosen_id = ?";
$params = [$dosenId];
if ($mahasiswaFilter) { $sql .= " AND m.id = ?"; $params[] = $mahasiswaFilter; }
if ($tanggal !== '') { $sql .= " AND a.tanggal = ?"; $params[] = $tanggal; }
$sql .= " ORDER BY a.tanggal DESC, m.nama ASC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$absensiList = $stmt->fetchAll();

$mhsList = $pdo->prepare('SELECT id, nama FROM mahasiswa WHERE dosen_id = ? ORDER BY nama');
$mhsList->execute([$dosenId]);
$mhsList = $mhsList->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<<<<<<< HEAD
<h4 class="mb-3">Absensi Peserta Bimbingan</h4>

<form class="row g-2 mb-3">
  <div class="col-auto">
    <select name="mahasiswa_id" class="form-select">
      <option value="">-- Semua Peserta --</option>
      <?php foreach ($mhsList as $m): ?>
        <option value="<?= $m['id'] ?>" <?= $mahasiswaFilter===(int)$m['id']?'selected':'' ?>><?= e($m['nama']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto"><input type="date" name="tanggal" class="form-control" value="<?= e($tanggal) ?>"></div>
  <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Filter</button> <a href="absensi.php" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></a></div>
</form>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead><tr><th>Tanggal</th><th>Tipe</th><th>Nama</th><th>Masuk</th><th>Status</th><th>Keluar</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (!$absensiList): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data absensi.</td></tr>
=======
<h1 class="m3-page-title">Absensi peserta bimbingan</h1>

<form class="m3-toolbar">
  <select name="mahasiswa_id" class="m3-select" style="width:auto;min-width:220px">
    <option value="">Semua peserta</option>
    <?php foreach ($mhsList as $m): ?>
      <option value="<?= $m['id'] ?>" <?= $mahasiswaFilter === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['nama']) ?></option>
    <?php endforeach; ?>
  </select>
  <input type="date" name="tanggal" class="m3-input" style="width:auto" value="<?= e($tanggal) ?>">
  <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">filter_alt</span>Terapkan</button>
  <a href="absensi.php" class="m3-btn m3-btn--text">Atur ulang</a>
</form>

<section class="m3-table-wrap">
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead>
        <tr><th>Tanggal</th><th>Tipe</th><th>Nama</th><th>Masuk</th><th>Status</th><th>Keluar</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php if (!$absensiList): ?>
        <tr><td colspan="7" class="m3-table__empty">Tidak ada data absensi untuk filter ini.</td></tr>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
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
<<<<<<< HEAD
</div>
=======
</section>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)

<?php include __DIR__ . '/../includes/footer.php'; ?>
