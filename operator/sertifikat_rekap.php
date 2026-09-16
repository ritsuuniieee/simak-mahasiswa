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
<h1 class="m3-page-title">Sertifikat</h1>

<?php foreach ($errors as $err): ?>
  <div class="m3-banner m3-banner--error">
    <span class="m3-icon">error</span><span class="m3-grow"><?= e($err) ?></span>
  </div>
<?php endforeach; ?>

<section class="m3-card m3-card--filled m3-mb-4">
  <div class="m3-card__header">Unggah sertifikat</div>
  <div class="m3-card__body">
    <p class="m3-body-medium m3-muted">Sertifikat yang diunggah di sini langsung tampil di portal peserta yang bersangkutan.</p>
    <form method="post" enctype="multipart/form-data">
      <?= csrfField() ?>
      <div class="m3-grid">
        <div class="m3-col-4">
          <label class="m3-field__label" for="mahasiswa_id">Pemilik sertifikat</label>
          <select id="mahasiswa_id" name="mahasiswa_id" class="m3-select" required>
            <option value="">Pilih peserta</option>
            <?php foreach ($pesertaList as $p): ?>
              <option value="<?= $p['id'] ?>">
                <?= e($p['nama']) ?> (<?= e($p['tipe'] === 'siswa_pkl' ? $p['nisn'] : $p['nim']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="m3-col-4">
          <label class="m3-field__label" for="judul">Judul sertifikat</label>
          <input id="judul" type="text" name="judul" class="m3-input" required
                 placeholder="Contoh: Sertifikat PKL semester genap">
        </div>
        <div class="m3-col-4">
          <label class="m3-field__label" for="file">Berkas</label>
          <input id="file" type="file" name="file" class="m3-input" accept=".pdf,.jpg,.jpeg,.png" required>
          <div class="m3-field__help">PDF, JPG, atau PNG. Maksimal 5 MB.</div>
        </div>
      </div>
      <button type="submit" class="m3-btn m3-btn--filled m3-mt-3">
        <span class="m3-icon m3-icon--sm">upload</span>Unggah sertifikat
      </button>
    </form>
  </div>
</section>

<form class="m3-toolbar">
  <div class="m3-toolbar__search">
    <input type="text" name="q" class="m3-input" value="<?= e($search) ?>"
           placeholder="Cari nama, NIM, NISN, atau judul sertifikat">
  </div>
  <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">search</span>Cari</button>
</form>

<section class="m3-table-wrap">
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead>
        <tr><th>Tanggal</th><th>Tipe</th><th>No. ID</th><th>Nama</th><th>Judul sertifikat</th><th class="m3-td-actions">Aksi</th></tr>
      </thead>
      <tbody>
      <?php if (!$sertifikatList): ?>
        <tr><td colspan="6" class="m3-table__empty">Belum ada sertifikat diunggah.</td></tr>
      <?php endif; ?>
      <?php foreach ($sertifikatList as $s): ?>
        <tr>
          <td><?= formatTanggal($s['tanggal_upload']) ?></td>
          <td><?= badgeTipe($s['tipe']) ?></td>
          <td><?= e($s['tipe'] === 'siswa_pkl' ? $s['nisn'] : $s['nim']) ?></td>
          <td><?= e($s['nama_peserta']) ?></td>
          <td><?= e($s['judul']) ?></td>
          <td class="m3-td-actions">
            <a href="<?= UPLOAD_URL . rawurlencode($s['file_path']) ?>" target="_blank"
               class="m3-icon-btn m3-icon-btn--success" title="Unduh" aria-label="Unduh sertifikat">
              <span class="m3-icon m3-icon--sm">download</span>
            </a>
            <a href="?delete=<?= $s['id'] ?>" class="m3-icon-btn m3-icon-btn--danger"
               title="Hapus" aria-label="Hapus sertifikat"
               onclick="return confirm('Hapus sertifikat \'<?= e($s['judul']) ?>\'?');">
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
