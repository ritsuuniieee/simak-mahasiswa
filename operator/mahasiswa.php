<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$pageTitle = 'Data Mahasiswa & Siswa PKL';

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
<<<<<<< HEAD
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h4 class="mb-0">Data Mahasiswa &amp; Siswa PKL</h4>
  <a href="<?= BASE_URL ?>/operator/mahasiswa_form.php" class="btn btn-success">
    <i class="bi bi-plus-lg me-1"></i>Tambah Peserta
  </a>
</div>

<ul class="nav nav-pills mb-3">
  <?php foreach (['' => 'Semua', 'mahasiswa' => 'Mahasiswa', 'siswa_pkl' => 'Siswa PKL'] as $val => $label): ?>
    <li class="nav-item"><a class="nav-link <?= $tipeFilter === $val ? 'active' : '' ?>" href="?tipe=<?= $val ?>&q=<?= urlencode($search) ?>"><?= $label ?></a></li>
  <?php endforeach; ?>
</ul>

<form class="row g-2 mb-3" method="get">
  <input type="hidden" name="tipe" value="<?= e($tipeFilter) ?>">
  <div class="col-auto flex-grow-1">
    <input type="text" name="q" class="form-control" placeholder="Cari NIM/NISN / nama / prodi / asal sekolah..." value="<?= e($search) ?>">
  </div>
  <div class="col-auto">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Cari</button>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead>
        <tr>
          <th>Foto</th><th>Tipe</th><th>No. Identitas</th><th>Nama</th>
          <th>Prodi/Sekolah</th><th>Pembimbing</th><th>Status</th><th class="text-end">Aksi</th>
=======
<div class="m3-row--between m3-mb-3">
  <h1 class="m3-page-title m3-mb-0">Mahasiswa &amp; siswa PKL</h1>
  <a href="<?= BASE_URL ?>/operator/mahasiswa_form.php" class="m3-fab">
    <span class="m3-icon">person_add</span>Tambah peserta
  </a>
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
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
        </tr>
      </thead>
      <tbody>
      <?php if (!$list): ?>
<<<<<<< HEAD
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td></tr>
=======
        <tr><td colspan="8" class="m3-table__empty">
          Belum ada peserta. Gunakan tombol &ldquo;Tambah peserta&rdquo; untuk mulai mengisi data.
        </td></tr>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
      <?php endif; ?>
      <?php foreach ($list as $m): ?>
        <tr>
          <td>
            <?php if ($m['foto']): ?>
<<<<<<< HEAD
              <img src="<?= FOTO_URL . rawurlencode($m['foto']) ?>" class="foto-peserta">
            <?php else: ?>
              <div class="foto-peserta d-flex align-items-center justify-content-center bg-light text-muted"><i class="bi bi-person"></i></div>
=======
              <img src="<?= FOTO_URL . rawurlencode($m['foto']) ?>" alt="" class="m3-avatar">
            <?php else: ?>
              <div class="m3-avatar"><span class="m3-icon">person</span></div>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
            <?php endif; ?>
          </td>
          <td><?= badgeTipe($m['tipe']) ?></td>
          <td><?= e(nomorIdentitas($m)) ?></td>
          <td><?= e($m['nama']) ?></td>
          <td><?= e($m['tipe'] === 'siswa_pkl' ? $m['asal_sekolah'] : $m['prodi']) ?></td>
          <td><?= e($m['nama_dosen'] ?? '-') ?></td>
<<<<<<< HEAD
          <td><span class="badge text-bg-light text-dark border"><?= e($m['status']) ?></span></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/operator/mahasiswa_form.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="<?= BASE_URL ?>/operator/mahasiswa.php?delete=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger"
               onclick="return confirm('Yakin hapus data \'<?= e($m['nama']) ?>\'? Semua data absensi, kegiatan, dan sertifikat terkait juga akan terhapus.');"><i class="bi bi-trash"></i></a>
=======
          <td><?= badgeStatusPeserta($m['status']) ?></td>
          <td class="m3-td-actions">
            <a href="<?= BASE_URL ?>/operator/mahasiswa_form.php?id=<?= $m['id'] ?>"
               class="m3-icon-btn m3-icon-btn--primary" title="Ubah data" aria-label="Ubah data">
              <span class="m3-icon m3-icon--sm">edit</span>
            </a>
            <a href="<?= BASE_URL ?>/operator/mahasiswa.php?delete=<?= $m['id'] ?>"
               class="m3-icon-btn m3-icon-btn--danger" title="Hapus data" aria-label="Hapus data"
               onclick="return confirm('Hapus data \'<?= e($m['nama']) ?>\'? Absensi, kegiatan, dan sertifikat peserta ini ikut terhapus.');">
              <span class="m3-icon m3-icon--sm">delete</span>
            </a>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
          </td>
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
