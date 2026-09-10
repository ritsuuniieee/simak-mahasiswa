<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$pageTitle = 'Sertifikat';
$allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
$maxSize = 5 * 1024 * 1024;

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('SELECT file_path FROM sertifikat WHERE id = ?');
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();
    if ($file && is_file(UPLOAD_DIR . $file)) @unlink(UPLOAD_DIR . $file);
    $stmt = $pdo->prepare('DELETE FROM sertifikat WHERE id = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Sertifikat berhasil dihapus.');
    header('Location: ' . BASE_URL . '/operator/sertifikat_rekap.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $errors[] = 'Sesi form kadaluarsa, coba lagi.';
    } else {
        $mahasiswaId = (int)($_POST['mahasiswa_id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');

        if (!$mahasiswaId) $errors[] = 'Pilih mahasiswa/siswa PKL pemilik sertifikat.';
        if ($judul === '') $errors[] = 'Judul sertifikat wajib diisi.';

        if (empty($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'File sertifikat wajib diunggah.';
        } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi kesalahan saat mengunggah file.';
        } else {
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) $errors[] = 'Format file harus PDF, JPG, atau PNG.';
            elseif ($_FILES['file']['size'] > $maxSize) $errors[] = 'Ukuran file maksimal 5 MB.';
        }

        if (!$errors) {
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
            $safeName = 'sertifikat_' . $mahasiswaId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_DIR . $safeName)) {
                $stmt = $pdo->prepare('INSERT INTO sertifikat (mahasiswa_id, judul, file_path, diupload_oleh) VALUES (?,?,?,"operator")');
                $stmt->execute([$mahasiswaId, $judul, $safeName]);
                setFlash('success', 'Sertifikat berhasil diunggah.');
                header('Location: ' . BASE_URL . '/operator/sertifikat_rekap.php');
                exit;
            } else {
                $errors[] = 'Gagal menyimpan file ke server.';
            }
        }
    }
}

$pesertaList = $pdo->query("SELECT id, nama, tipe, nim, nisn FROM mahasiswa ORDER BY nama")->fetchAll();

$search = trim($_GET['q'] ?? '');
$sql = "SELECT s.*, m.nama AS nama_peserta, m.nim, m.nisn, m.tipe
        FROM sertifikat s JOIN mahasiswa m ON m.id = s.mahasiswa_id";
$params = [];
if ($search !== '') {
    $sql .= " WHERE m.nama LIKE ? OR m.nim LIKE ? OR m.nisn LIKE ? OR s.judul LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}
$sql .= " ORDER BY s.tanggal_upload DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sertifikatList = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3">Sertifikat Mahasiswa &amp; Siswa PKL</h4>

<?php foreach ($errors as $err): ?>
  <div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-semibold">Unggah Sertifikat Baru</div>
  <div class="card-body">
    <form method="post" enctype="multipart/form-data">
      <?= csrfField() ?>
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Peserta</label>
          <select name="mahasiswa_id" class="form-select" required>
            <option value="">-- Pilih --</option>
            <?php foreach ($pesertaList as $p): ?>
              <option value="<?= $p['id'] ?>"><?= e($p['nama']) ?> (<?= e($p['tipe']==='siswa_pkl' ? $p['nisn'] : $p['nim']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Judul Sertifikat</label>
          <input type="text" name="judul" class="form-control" required placeholder="Contoh: Sertifikat PKL Semester Genap">
        </div>
        <div class="col-md-3">
          <label class="form-label">File (PDF/JPG/PNG, maks 5MB)</label>
          <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-success w-100"><i class="bi bi-upload me-1"></i>Unggah</button>
        </div>
      </div>
    </form>
  </div>
</div>

<form class="row g-2 mb-3">
  <div class="col-auto flex-grow-1"><input type="text" name="q" class="form-control" placeholder="Cari nama / NIM / NISN / judul..." value="<?= e($search) ?>"></div>
  <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Cari</button></div>
</form>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead><tr><th>Tanggal</th><th>Tipe</th><th>No. ID</th><th>Nama</th><th>Judul Sertifikat</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      <?php if (!$sertifikatList): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada sertifikat.</td></tr>
      <?php endif; ?>
      <?php foreach ($sertifikatList as $s): ?>
        <tr>
          <td><?= formatTanggal($s['tanggal_upload']) ?></td>
          <td><?= badgeTipe($s['tipe']) ?></td>
          <td><?= e($s['tipe']==='siswa_pkl' ? $s['nisn'] : $s['nim']) ?></td>
          <td><?= e($s['nama_peserta']) ?></td>
          <td><?= e($s['judul']) ?></td>
          <td class="text-end">
            <a href="<?= UPLOAD_URL . rawurlencode($s['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i></a>
            <a href="?delete=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus sertifikat \'<?= e($s['judul']) ?>\'?');"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
