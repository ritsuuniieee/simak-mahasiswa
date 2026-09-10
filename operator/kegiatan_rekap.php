<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$pageTitle = 'Rekap Kegiatan Harian';

$search = trim($_GET['q'] ?? '');
$sql = "SELECT k.*, m.nama AS nama_peserta, m.nim, m.nisn, m.tipe
        FROM kegiatan_harian k JOIN mahasiswa m ON m.id = k.mahasiswa_id";
$params = [];
if ($search !== '') {
    $sql .= " WHERE m.nama LIKE ? OR m.nim LIKE ? OR m.nisn LIKE ? OR k.judul LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}
$sql .= " ORDER BY k.tanggal DESC, k.created_at DESC LIMIT 300";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$kegiatanList = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3">Rekap Kegiatan Harian</h4>

<form class="row g-2 mb-3">
  <div class="col-auto flex-grow-1">
    <input type="text" name="q" class="form-control" placeholder="Cari nama / NIM / NISN / judul kegiatan..." value="<?= e($search) ?>">
  </div>
  <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Cari</button></div>
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
