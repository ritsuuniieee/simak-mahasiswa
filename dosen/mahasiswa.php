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
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h4 class="mb-0">Mahasiswa &amp; Siswa PKL Bimbingan Saya</h4>
  <a href="<?= BASE_URL ?>/dosen/mahasiswa_form.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Tambah Peserta</a>
</div>

<form class="row g-2 mb-3" method="get">
  <div class="col-auto flex-grow-1"><input type="text" name="q" class="form-control" placeholder="Cari NIM/NISN / nama..." value="<?= e($search) ?>"></div>
  <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Cari</button></div>
</form>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead><tr><th>Foto</th><th>Tipe</th><th>No. ID</th><th>Nama</th><th>Prodi/Sekolah</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      <?php if (!$list): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada peserta bimbingan.</td></tr>
      <?php endif; ?>
      <?php foreach ($list as $m): ?>
        <tr>
          <td>
            <?php if ($m['foto']): ?><img src="<?= FOTO_URL . rawurlencode($m['foto']) ?>" class="foto-peserta">
            <?php else: ?><div class="foto-peserta d-flex align-items-center justify-content-center bg-light text-muted"><i class="bi bi-person"></i></div><?php endif; ?>
          </td>
          <td><?= badgeTipe($m['tipe']) ?></td>
          <td><?= e(nomorIdentitas($m)) ?></td>
          <td><?= e($m['nama']) ?></td>
          <td><?= e($m['tipe']==='siswa_pkl' ? $m['asal_sekolah'] : $m['prodi']) ?></td>
          <td><span class="badge text-bg-light text-dark border"><?= e($m['status']) ?></span></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/dosen/mahasiswa_form.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
            <a href="<?= BASE_URL ?>/dosen/absensi.php?mahasiswa_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Absensi"><i class="bi bi-calendar-check"></i></a>
            <a href="<?= BASE_URL ?>/dosen/kegiatan.php?mahasiswa_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Kegiatan"><i class="bi bi-journal-text"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<p class="text-muted small mt-2">Untuk menghapus data atau memindahkan pembimbing, hubungi Operator.</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
