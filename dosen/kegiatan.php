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
<h1 class="m3-page-title">Kegiatan harian peserta bimbingan</h1>

<form class="m3-toolbar">
  <select name="mahasiswa_id" class="m3-select" style="width:auto;min-width:220px">
    <option value="">Semua peserta</option>
    <?php foreach ($mhsList as $m): ?>
      <option value="<?= $m['id'] ?>" <?= $mahasiswaFilter === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['nama']) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">filter_alt</span>Terapkan</button>
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
<?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
