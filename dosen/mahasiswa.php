<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['dosen']);

$dosenId = currentDosenId();
$pageTitle = 'Bimbingan Saya';

$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM mahasiswa WHERE dosen_id = ?";
$params = [$dosenId];
if ($search !== '') {
    $sql .= " AND (nim LIKE ? OR nisn LIKE ? OR nama LIKE ?)";
    array_push($params, "%$search%", "%$search%", "%$search%");
}
$sql .= " ORDER BY nama ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="m3-row--between m3-mb-3">
  <h1 class="m3-page-title m3-mb-0">Peserta bimbingan saya</h1>
  <a href="<?= BASE_URL ?>/dosen/mahasiswa_form.php" class="m3-fab">
    <span class="m3-icon">person_add</span>Tambah peserta
  </a>
</div>

<form class="m3-toolbar" method="get">
  <div class="m3-toolbar__search">
    <input type="text" name="q" class="m3-input" value="<?= e($search) ?>" placeholder="Cari NIM, NISN, atau nama">
  </div>
  <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">search</span>Cari</button>
</form>

<section class="m3-table-wrap">
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead>
        <tr><th>Foto</th><th>Tipe</th><th>No. ID</th><th>Nama</th><th>Prodi / sekolah</th><th>Status</th><th class="m3-td-actions">Aksi</th></tr>
      </thead>
      <tbody>
      <?php if (!$list): ?>
        <tr><td colspan="7" class="m3-table__empty">Belum ada peserta bimbingan.</td></tr>
      <?php endif; ?>
      <?php foreach ($list as $m): ?>
        <tr>
          <td>
            <?php if ($m['foto']): ?>
              <img src="<?= FOTO_URL . rawurlencode($m['foto']) ?>" alt="" class="m3-avatar">
            <?php else: ?>
              <div class="m3-avatar"><span class="m3-icon">person</span></div>
            <?php endif; ?>
          </td>
          <td><?= badgeTipe($m['tipe']) ?></td>
          <td><?= e(nomorIdentitas($m)) ?></td>
          <td><?= e($m['nama']) ?></td>
          <td><?= e($m['tipe'] === 'siswa_pkl' ? $m['asal_sekolah'] : $m['prodi']) ?></td>
          <td><?= badgeStatusPeserta($m['status']) ?></td>
          <td class="m3-td-actions">
            <a href="<?= BASE_URL ?>/dosen/mahasiswa_form.php?id=<?= $m['id'] ?>"
               class="m3-icon-btn m3-icon-btn--primary" title="Ubah data" aria-label="Ubah data">
              <span class="m3-icon m3-icon--sm">edit</span>
            </a>
            <a href="<?= BASE_URL ?>/dosen/absensi.php?mahasiswa_id=<?= $m['id'] ?>"
               class="m3-icon-btn" title="Lihat absensi" aria-label="Lihat absensi">
              <span class="m3-icon m3-icon--sm">event_available</span>
            </a>
            <a href="<?= BASE_URL ?>/dosen/kegiatan.php?mahasiswa_id=<?= $m['id'] ?>"
               class="m3-icon-btn" title="Lihat kegiatan" aria-label="Lihat kegiatan">
              <span class="m3-icon m3-icon--sm">edit_note</span>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<p class="m3-body-small m3-muted m3-mt-2">
  Untuk menghapus data atau memindahkan pembimbing, hubungi operator.
</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
