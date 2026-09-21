<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$pageTitle = 'Data Mahasiswa & Siswa PKL';

$hasPwCol = hasMahasiswaPasswordColumn($pdo);

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('SELECT foto FROM mahasiswa WHERE id = ?');
    $stmt->execute([$id]);
    $foto = $stmt->fetchColumn();
    if ($foto && is_file(FOTO_DIR . $foto)) {
        @unlink(FOTO_DIR . $foto);
    }
    $stmt = $pdo->prepare('DELETE FROM mahasiswa WHERE id = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Data berhasil dihapus.');
    header('Location: ' . BASE_URL . '/operator/mahasiswa.php');
    exit;
}

if ($hasPwCol && isset($_GET['reset_pw'])) {
    $id = (int)$_GET['reset_pw'];
    $stmt = $pdo->prepare('UPDATE mahasiswa SET password = NULL WHERE id = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Password portal peserta dikosongkan. Peserta dapat masuk dengan NIM/NISN lalu membuat password baru.');
    header('Location: ' . BASE_URL . '/operator/mahasiswa.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
$tipeFilter = $_GET['tipe'] ?? '';

$sql = "SELECT m.*, d.nama AS nama_dosen FROM mahasiswa m LEFT JOIN dosen d ON d.id = m.dosen_id WHERE 1=1";
$params = [];
if ($search !== '') {
    $sql .= " AND (m.nim LIKE ? OR m.nisn LIKE ? OR m.nama LIKE ? OR m.prodi LIKE ? OR m.asal_sekolah LIKE ?)";
    $like = "%$search%";
    array_push($params, $like, $like, $like, $like, $like);
}
if (in_array($tipeFilter, ['mahasiswa','siswa_pkl'], true)) {
    $sql .= " AND m.tipe = ?";
    $params[] = $tipeFilter;
}
$sql .= " ORDER BY m.nama ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="m3-row--between m3-mb-3">
  <h1 class="m3-page-title m3-mb-0">Mahasiswa &amp; siswa PKL</h1>
  <div class="m3-row m3-gap-sm" style="flex-wrap:wrap">
    <a href="<?= BASE_URL ?>/operator/mahasiswa_template.php" class="m3-btn m3-btn--outlined m3-btn--sm">
      <span class="m3-icon m3-icon--sm">description</span>Template
    </a>
    <a href="<?= BASE_URL ?>/operator/mahasiswa_export.php?tipe=<?= e($tipeFilter) ?>&q=<?= urlencode($search) ?>" class="m3-btn m3-btn--outlined m3-btn--sm">
      <span class="m3-icon m3-icon--sm">download</span>Ekspor
    </a>
    <a href="<?= BASE_URL ?>/operator/mahasiswa_import.php" class="m3-btn m3-btn--tonal m3-btn--sm">
      <span class="m3-icon m3-icon--sm">upload</span>Impor
    </a>
    <a href="<?= BASE_URL ?>/operator/mahasiswa_form.php" class="m3-fab">
      <span class="m3-icon">person_add</span>Tambah peserta
    </a>
  </div>
</div>

<div class="m3-chipset">
  <?php foreach (['' => 'Semua', 'mahasiswa' => 'Mahasiswa', 'siswa_pkl' => 'Siswa PKL'] as $val => $label): ?>
    <a class="m3-chip <?= $tipeFilter === $val ? 'is-selected' : '' ?>"
       href="?tipe=<?= $val ?>&q=<?= urlencode($search) ?>">
      <?php if ($tipeFilter === $val): ?><span class="m3-icon m3-icon--sm">check</span><?php endif; ?>
      <?= $label ?>
    </a>
  <?php endforeach; ?>
</div>

<form class="m3-toolbar" method="get">
  <input type="hidden" name="tipe" value="<?= e($tipeFilter) ?>">
  <div class="m3-toolbar__search">
    <input type="text" name="q" class="m3-input" value="<?= e($search) ?>"
           placeholder="Cari NIM, NISN, nama, prodi, atau asal sekolah">
  </div>
  <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">search</span>Cari</button>
</form>

<section class="m3-table-wrap">
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead>
        <tr>
          <th>Foto</th><th>Tipe</th><th>No. identitas</th><th>Nama</th>
          <th>Prodi / sekolah</th><th>Pembimbing</th><th>Status</th><th class="m3-td-actions">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$list): ?>
        <tr><td colspan="8" class="m3-table__empty">
          Belum ada peserta. Gunakan tombol &ldquo;Tambah peserta&rdquo; untuk mulai mengisi data.
        </td></tr>
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
          <td><?= e($m['nama_dosen'] ?? '-') ?></td>
          <td><?= badgeStatusPeserta($m['status']) ?></td>
          <td class="m3-td-actions">
            <a href="<?= BASE_URL ?>/operator/mahasiswa_edit.php?id=<?= $m['id'] ?>"
               class="m3-icon-btn m3-icon-btn--primary" title="Ubah data" aria-label="Ubah data">
              <span class="m3-icon m3-icon--sm">edit</span>
            </a>
            <?php if ($hasPwCol): ?>
            <a href="<?= BASE_URL ?>/operator/mahasiswa.php?reset_pw=<?= $m['id'] ?>"
               class="m3-icon-btn" title="Kosongkan password portal (<?= !empty($m['password']) ? 'sudah ada' : 'belum ada' ?>)" aria-label="Reset password portal"
               onclick="return confirm('Kosongkan password portal \'<?= e($m['nama']) ?>\'?');">
              <span class="m3-icon m3-icon--sm">key</span>
            </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/operator/mahasiswa.php?delete=<?= $m['id'] ?>"
               class="m3-icon-btn m3-icon-btn--danger" title="Hapus data" aria-label="Hapus data"
               onclick="return confirm('Hapus data \'<?= e($m['nama']) ?>\'? Absensi, kegiatan, dan sertifikat peserta ini ikut terhapus.');">
              <span class="m3-icon m3-icon--sm">delete</span>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
