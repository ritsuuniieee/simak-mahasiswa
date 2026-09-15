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
<<<<<<< HEAD
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
=======
<h1 class="m3-page-title">Rekap kegiatan harian</h1>

<form class="m3-toolbar" method="get">
  <div class="m3-toolbar__search">
    <input type="text" name="q" class="m3-input" value="<?= e($search) ?>"
           placeholder="Cari nama, NIM, NISN, atau judul kegiatan">
  </div>
  <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">search</span>Cari</button>
</form>

<?php if (!$kegiatanList): ?>
  <div class="m3-card m3-card--filled">
    <div class="m3-card__body m3-text-center">
      <span class="m3-icon m3-icon--xl m3-muted">edit_note</span>
      <p class="m3-body-large m3-muted m3-mb-0">Belum ada kegiatan tercatat.</p>
    </div>
  </div>
<?php endif; ?>

<div class="m3-cards-grid m3-cards-grid--wide">
<?php foreach ($kegiatanList as $k): ?>
  <article class="m3-card m3-card--elevated">
    <div class="m3-card__body">
      <div class="m3-row--between m3-mb-1">
        <h2 class="m3-title-medium m3-mb-0"><?= e($k['judul']) ?></h2>
        <span class="m3-body-small m3-muted"><?= formatTanggal($k['tanggal']) ?></span>
      </div>
      <div class="m3-row m3-gap-sm m3-mb-2">
        <span class="m3-body-small m3-muted"><?= e($k['nama_peserta']) ?></span>
        <?= badgeTipe($k['tipe']) ?>
      </div>
      <p class="m3-body-medium m3-mb-0"><?= nl2br(e($k['deskripsi'])) ?></p>
    </div>
  </article>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
<?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
