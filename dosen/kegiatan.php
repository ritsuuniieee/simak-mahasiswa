<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['dosen']);

$dosenId = currentDosenId();
$pageTitle = 'Kegiatan Harian Bimbingan';

$mahasiswaFilter = isset($_GET['mahasiswa_id']) ? (int)$_GET['mahasiswa_id'] : null;

$sql = "SELECT k.*, m.nama AS nama_peserta, m.tipe
        FROM kegiatan_harian k JOIN mahasiswa m ON m.id = k.mahasiswa_id
        WHERE m.dosen_id = ?";
$params = [$dosenId];
if ($mahasiswaFilter) {
    $sql .= " AND m.id = ?";
    $params[] = $mahasiswaFilter;
}
$sql .= " ORDER BY k.tanggal DESC, k.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$kegiatanList = $stmt->fetchAll();

$mhsList = $pdo->prepare('SELECT id, nama FROM mahasiswa WHERE dosen_id = ? ORDER BY nama');
$mhsList->execute([$dosenId]);
$mhsList = $mhsList->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3">Kegiatan Harian Peserta Bimbingan</h4>

<form class="row g-2 mb-3">
  <div class="col-auto">
    <select name="mahasiswa_id" class="form-select">
      <option value="">-- Semua Peserta --</option>
      <?php foreach ($mhsList as $m): ?>
        <option value="<?= $m['id'] ?>" <?= $mahasiswaFilter === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['nama']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Filter</button>
  </div>
</form>

<div class="row g-3">
<?php if (!$kegiatanList): ?>
  <p class="text-muted text-center py-4">Belum ada kegiatan tercatat.</p>
<?php endif; ?>
<?php foreach ($kegiatanList as $k): ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <h6 class="card-title mb-1"><?= e($k['judul']) ?></h6>
          <small class="text-muted"><?= formatTanggal($k['tanggal']) ?></small>
        </div>
        <div class="small text-muted mb-2"><?= e($k['nama_peserta']) ?> &middot; <?= badgeTipe($k['tipe']) ?></div>
        <p class="card-text mb-0"><?= nl2br(e($k['deskripsi'])) ?></p>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
