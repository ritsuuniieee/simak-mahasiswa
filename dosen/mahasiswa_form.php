<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['dosen']);

$dosenId = currentDosenId();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = $id !== null;
$pageTitle = $isEdit ? 'Edit Peserta' : 'Tambah Peserta';

$data = ['tipe' => 'mahasiswa', 'nim' => '', 'nisn' => '', 'nama' => '', 'prodi' => '', 'asal_sekolah' => '', 'no_hp' => '', 'alamat' => '', 'status' => 'aktif', 'foto' => ''];
$errors = [];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM mahasiswa WHERE id = ? AND dosen_id = ?');
    $stmt->execute([$id, $dosenId]);
    $row = $stmt->fetch();
    if (!$row) {
        setFlash('error', 'Data tidak ditemukan atau bukan bimbingan Anda.');
        header('Location: ' . BASE_URL . '/dosen/mahasiswa.php');
        exit;
    }
    $data = array_merge($data, $row);
}

$allowedExtFoto = ['jpg', 'jpeg', 'png'];
$maxSizeFoto = 2 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $errors[] = 'Sesi form kadaluarsa, coba lagi.';
    } else {
        $data['tipe'] = in_array($_POST['tipe'] ?? '', ['mahasiswa','siswa_pkl'], true) ? $_POST['tipe'] : 'mahasiswa';
        $data['nim'] = trim($_POST['nim'] ?? '');
        $data['nisn'] = trim($_POST['nisn'] ?? '');
        $data['nama'] = trim($_POST['nama'] ?? '');
        $data['prodi'] = $_POST['prodi'] ?? '';
        $data['jurusan'] = trim($_POST['jurusan'] ?? '');
        $data['asal_sekolah'] = trim($_POST['asal_sekolah'] ?? '');
        $data['no_hp'] = trim($_POST['no_hp'] ?? '');
        $data['alamat'] = trim($_POST['alamat'] ?? '');
        $data['status'] = $_POST['status'] ?? 'aktif';

        if ($data['nama'] === '' || $data['prodi'] === '') $errors[] = 'Nama dan prodi/bidang wajib diisi.';
        if ($data['tipe'] === 'mahasiswa' && $data['nim'] === '') $errors[] = 'NIM wajib diisi untuk tipe Mahasiswa.';
        if ($data['tipe'] === 'siswa_pkl') {
            if ($data['nisn'] === '') $errors[] = 'NISN wajib diisi untuk tipe Siswa PKL.';
            if ($data['asal_sekolah'] === '') $errors[] = 'Asal sekolah wajib diisi untuk tipe Siswa PKL.';
        }
        if (!$errors && $data['nim'] !== '') {
            $stmt = $pdo->prepare('SELECT id FROM mahasiswa WHERE nim = ? AND id != ?');
            $stmt->execute([$data['nim'], $id ?? 0]);
            if ($stmt->fetch()) $errors[] = 'NIM sudah digunakan.';
        }
        if (!$errors && $data['nisn'] !== '') {
            $stmt = $pdo->prepare('SELECT id FROM mahasiswa WHERE nisn = ? AND id != ?');
            $stmt->execute([$data['nisn'], $id ?? 0]);
            if ($stmt->fetch()) $errors[] = 'NISN sudah digunakan.';
        }

        $fotoBaru = null;
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Terjadi kesalahan saat mengunggah foto.';
            } else {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExtFoto, true)) $errors[] = 'Foto harus JPG/PNG.';
                elseif ($_FILES['foto']['size'] > $maxSizeFoto) $errors[] = 'Ukuran foto maksimal 2 MB.';
                else $fotoBaru = 'foto_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            }
        }

        if (!$errors) {
            if ($fotoBaru) {
                if (!is_dir(FOTO_DIR)) mkdir(FOTO_DIR, 0775, true);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], FOTO_DIR . $fotoBaru)) {
                    if ($isEdit && !empty($data['foto']) && is_file(FOTO_DIR . $data['foto'])) @unlink(FOTO_DIR . $data['foto']);
                } else {
                    $errors[] = 'Gagal menyimpan foto ke server.';
                }
            }
        }

        if (!$errors) {
            $fotoFinal = $fotoBaru ?? ($data['foto'] ?: null);
            $nimFinal = $data['tipe'] === 'mahasiswa' ? $data['nim'] : null;
            $nisnFinal = $data['tipe'] === 'siswa_pkl' ? $data['nisn'] : null;
            $asalFinal = $data['tipe'] === 'siswa_pkl' ? $data['asal_sekolah'] : null;

            if ($isEdit) {
                $stmt = $pdo->prepare('UPDATE mahasiswa SET tipe=?, nim=?, nisn=?, nama=?, prodi=?, asal_sekolah=?, no_hp=?, alamat=?, status=?, foto=? WHERE id=? AND dosen_id=?');
                $stmt->execute([$data['tipe'], $nimFinal, $nisnFinal, $data['nama'], $data['prodi'], $asalFinal, $data['no_hp'], $data['alamat'], $data['status'], $fotoFinal, $id, $dosenId]);
                setFlash('success', 'Data berhasil diperbarui.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO mahasiswa (tipe, nim, nisn, nama, prodi, asal_sekolah, dosen_id, no_hp, alamat, status, foto) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$data['tipe'], $nimFinal, $nisnFinal, $data['nama'], $data['prodi'], $asalFinal, $dosenId, $data['no_hp'], $data['alamat'], $data['status'], $fotoFinal]);
                setFlash('success', 'Peserta baru berhasil ditambahkan sebagai bimbingan Anda. Untuk membuatkan akun login, hubungi operator.');
            }
            header('Location: ' . BASE_URL . '/dosen/mahasiswa.php');
            exit;
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4"><?= e($pageTitle) ?></h4>

<?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="post" enctype="multipart/form-data" novalidate>
      <?= csrfField() ?>
      <div class="mb-3">
        <label class="form-label d-block">Tipe Peserta</label>
        <div class="btn-group">
          <input type="radio" class="btn-check" name="tipe" id="tipeMhs" value="mahasiswa" <?= $data['tipe']==='mahasiswa'?'checked':'' ?> onchange="toggleTipe()">
          <label class="btn btn-outline-success" for="tipeMhs">Mahasiswa</label>
          <input type="radio" class="btn-check" name="tipe" id="tipePkl" value="siswa_pkl" <?= $data['tipe']==='siswa_pkl'?'checked':'' ?> onchange="toggleTipe()">
          <label class="btn btn-outline-success" for="tipePkl">Siswa PKL</label>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <img id="fotoPreview" src="<?= $data['foto'] ? FOTO_URL . rawurlencode($data['foto']) : '' ?>" class="foto-preview-lg mb-2" style="<?= $data['foto'] ? '' : 'display:none' ?>">
          <label class="form-label d-block">Foto</label>
          <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png" onchange="previewFoto(this)">
        </div>
        <div class="col-md-8">
          <div class="row g-3">
            <div class="col-md-6" id="wrapNim">
              <label class="form-label">NIM</label>
              <input type="text" name="nim" class="form-control" value="<?= e($data['nim']) ?>">
            </div>
            <div class="col-md-6" id="wrapNisn" style="display:none">
              <label class="form-label">NISN</label>
              <input type="text" name="nisn" class="form-control" value="<?= e($data['nisn']) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="nama" class="form-control" required value="<?= e($data['nama']) ?>">
            </div>
            <div class="col-md-6" id="wrapProdi">
              <label class="form-label" id="labelProdi">Program Studi</label>
                    <select name="prodi" class="form-select">
                        <?php foreach (['Sistem Informasi','Teknik Informatika'] as $st): ?>
                        <option value="<?= $st ?>" <?= $data['prodi'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
            </div>
            <div class="col-12" id="wrapJurusan">
              <label class="form-label">Jurusan</label>
              <input type="text" name="jurusan" class="form-control" required value="<?= e($data['jurusan']) ?>">
            </div>
            <div class="col-md-6" id="wrapAsalSekolah" style="display:none">
              <label class="form-label">Asal Sekolah</label>
              <input type="text" name="asal_sekolah" class="form-control" value="<?= e($data['asal_sekolah']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <?php foreach (['aktif','cuti','lulus','selesai','nonaktif'] as $st): ?>
                  <option value="<?= $st ?>" <?= $data['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">No. HP</label>
          <input type="text" name="no_hp" class="form-control" value="<?= e($data['no_hp']) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Alamat</label>
          <textarea name="alamat" class="form-control" rows="2"><?= e($data['alamat']) ?></textarea>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Simpan</button>
        <a href="<?= BASE_URL ?>/dosen/mahasiswa.php" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>

<script>
function toggleTipe() {
  const isPkl = document.getElementById('tipePkl').checked;
  document.getElementById('wrapNim').style.display = isPkl ? 'none' : 'block';
  document.getElementById('wrapNisn').style.display = isPkl ? 'block' : 'none';
  document.getElementById('wrapAsalSekolah').style.display = isPkl ? 'block' : 'none';
  document.getElementById('wrapProdi').style.display = isPkl ? 'none' : 'block';
  document.getElementById('wrapJurusan').style.display = isPkl ? 'block' : 'none';
}
function previewFoto(input) {
  const preview = document.getElementById('fotoPreview');
  if (input.files && input.files[0]) { preview.src = URL.createObjectURL(input.files[0]); preview.style.display='block'; }
}
toggleTipe();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
