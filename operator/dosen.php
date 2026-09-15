<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$pageTitle = 'Data Dosen';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM dosen WHERE id = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Data dosen berhasil dihapus.');
    header('Location: ' . BASE_URL . '/operator/dosen.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT d.*, (SELECT COUNT(*) FROM mahasiswa m WHERE m.dosen_id = d.id) AS jumlah_bimbingan FROM dosen d";
$params = [];
if ($search !== '') {
    $sql .= " WHERE d.nama LIKE ? OR d.nidn_nidk LIKE ?";
    $params = ["%$search%", "%$search%"];
}
$sql .= " ORDER BY d.nama ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dosenList = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<<<<<<< HEAD
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h4 class="mb-0">Data Dosen</h4>
  <a href="<?= BASE_URL ?>/operator/dosen_form.php" class="btn btn-success">
    <i class="bi bi-plus-lg me-1"></i>Tambah Dosen
  </a>
</div>

<form class="row g-2 mb-3" method="get">
  <div class="col-auto flex-grow-1">
    <input type="text" name="q" class="form-control" placeholder="Cari nama / NIDN/NIDK..." value="<?= e($search) ?>">
  </div>
  <div class="col-auto">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Cari</button>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead><tr><th>NIDN/NIDK</th><th>Nama</th><th>No. HP</th><th>Bimbingan</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      <?php if (!$dosenList): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data dosen.</td></tr>
=======
<div class="m3-row--between m3-mb-3">
  <h1 class="m3-page-title m3-mb-0">Dosen pembimbing</h1>
  <a href="<?= BASE_URL ?>/operator/dosen_form.php" class="m3-fab">
    <span class="m3-icon">person_add</span>Tambah dosen
  </a>
</div>

<form class="m3-toolbar" method="get">
  <div class="m3-toolbar__search">
    <input type="text" name="q" class="m3-input" value="<?= e($search) ?>" placeholder="Cari nama atau NIDN/NIDK">
  </div>
  <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">search</span>Cari</button>
</form>

<section class="m3-table-wrap">
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead>
        <tr><th>NIDN / NIDK</th><th>Nama</th><th>No. HP</th><th>Bimbingan</th><th class="m3-td-actions">Aksi</th></tr>
      </thead>
      <tbody>
      <?php if (!$dosenList): ?>
        <tr><td colspan="5" class="m3-table__empty">Belum ada dosen terdaftar.</td></tr>
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
      <?php endif; ?>
      <?php foreach ($dosenList as $d): ?>
        <tr>
          <td><?= e($d['nidn_nidk']) ?></td>
          <td><?= e($d['nama']) ?></td>
<<<<<<< HEAD
          <td><?= e($d['no_hp']) ?></td>
          <td><span class="badge text-bg-light text-dark border"><?= (int)$d['jumlah_bimbingan'] ?> peserta</span></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/operator/dosen_form.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="<?= BASE_URL ?>/operator/dosen.php?delete=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger"
               onclick="return confirm('Yakin hapus dosen \'<?= e($d['nama']) ?>\'?');"><i class="bi bi-trash"></i></a>
=======
          <td><?= e($d['no_hp'] ?: '-') ?></td>
          <td><span class="m3-badge m3-badge--neutral"><?= (int)$d['jumlah_bimbingan'] ?> peserta</span></td>
          <td class="m3-td-actions">
            <a href="<?= BASE_URL ?>/operator/dosen_form.php?id=<?= $d['id'] ?>"
               class="m3-icon-btn m3-icon-btn--primary" title="Ubah data" aria-label="Ubah data">
              <span class="m3-icon m3-icon--sm">edit</span>
            </a>
            <a href="<?= BASE_URL ?>/operator/dosen.php?delete=<?= $d['id'] ?>"
               class="m3-icon-btn m3-icon-btn--danger" title="Hapus dosen" aria-label="Hapus dosen"
               onclick="return confirm('Hapus dosen \'<?= e($d['nama']) ?>\'?');">
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
