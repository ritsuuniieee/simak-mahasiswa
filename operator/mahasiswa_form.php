<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = $id !== null;
$pageTitle = $isEdit ? 'Edit Data Peserta' : 'Tambah Peserta';

$data = [
    'tipe' => 'mahasiswa', 'nim' => '', 'nisn' => '', 'nama' => '', 'prodi' => '',
    'asal_sekolah' => '', 'jurusan' => '', 'dosen_id' => '', 'no_hp' => '', 'alamat' => '',
    'status' => 'aktif', 'foto' => '',
];
$errors = [];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM mahasiswa WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        setFlash('error', 'Data tidak ditemukan.');
        header('Location: ' . BASE_URL . '/operator/mahasiswa.php');
        exit;
    }
    $data = array_merge($data, $row);
}

$dosenList = $pdo->query('SELECT id, nama FROM dosen ORDER BY nama')->fetchAll();
$allowedExtFoto = ['jpg', 'jpeg', 'png'];
$maxSizeFoto = 2 * 1024 * 1024; // 2 MB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $errors[] = 'Sesi form kadaluarsa, silakan coba lagi.';
    } else {
        $data['tipe'] = in_array($_POST['tipe'] ?? '', ['mahasiswa','siswa_pkl'], true) ? $_POST['tipe'] : 'mahasiswa';
        $data['nim'] = trim($_POST['nim'] ?? '');
        $data['nisn'] = trim($_POST['nisn'] ?? '');
        $data['nama'] = trim($_POST['nama'] ?? '');
        $data['prodi'] = $_POST['prodi'] ?? '';
        $data['jurusan'] = trim($_POST['jurusan'] ?? '');
        $data['asal_sekolah'] = trim($_POST['asal_sekolah'] ?? '');
        $data['dosen_id'] = $_POST['dosen_id'] !== '' ? (int)$_POST['dosen_id'] : null;
        $data['no_hp'] = trim($_POST['no_hp'] ?? '');
        $data['alamat'] = trim($_POST['alamat'] ?? '');
        $data['status'] = $_POST['status'] ?? 'aktif';

        if ($data['nama'] === '' || $data['prodi'] === '') {
            $errors[] = 'Nama dan prodi/bidang wajib diisi.';
        }
        if ($data['tipe'] === 'mahasiswa' && $data['nim'] === '') {
            $errors[] = 'NIM wajib diisi untuk tipe Mahasiswa.';
        }
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
        // Validasi foto (opsional)
        $fotoBaru = null;
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Terjadi kesalahan saat mengunggah foto.';
            } else {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExtFoto, true)) {
                    $errors[] = 'Foto harus berformat JPG atau PNG.';
                } elseif ($_FILES['foto']['size'] > $maxSizeFoto) {
                    $errors[] = 'Ukuran foto maksimal 2 MB.';
                } else {
                    $fotoBaru = 'foto_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                }
            }
        }

        if (!$errors) {
            try {
                $pdo->beginTransaction();
                $userId = $isEdit ? ($data['user_id'] ?? null) : null;

                // Pindahkan file foto baru setelah validasi lolos
                if ($fotoBaru) {
                    if (!is_dir(FOTO_DIR)) mkdir(FOTO_DIR, 0775, true);
                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], FOTO_DIR . $fotoBaru)) {
                        throw new Exception('Gagal menyimpan foto ke server.');
                    }
                    // hapus foto lama jika ada (mode edit)
                    if ($isEdit && !empty($data['foto']) && is_file(FOTO_DIR . $data['foto'])) {
                        @unlink(FOTO_DIR . $data['foto']);
                    }
                }
                $fotoFinal = $fotoBaru ?? ($data['foto'] ?: null);

                $nimFinal = $data['tipe'] === 'mahasiswa' ? $data['nim'] : null;
                $nisnFinal = $data['tipe'] === 'siswa_pkl' ? $data['nisn'] : null;
                $asalSekolahFinal = $data['tipe'] === 'siswa_pkl' ? $data['asal_sekolah'] : null;

                if ($isEdit) {
                    $stmt = $pdo->prepare('UPDATE mahasiswa SET tipe=?, nim=?, nisn=?, nama=?, prodi=?, asal_sekolah=?, dosen_id=?, no_hp=?, alamat=?, status=?, foto=? WHERE id=?');
                    $stmt->execute([$data['tipe'], $nimFinal, $nisnFinal, $data['nama'], $data['prodi'], $asalSekolahFinal, $data['dosen_id'], $data['no_hp'], $data['alamat'], $data['status'], $fotoFinal, $id]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO mahasiswa (user_id, tipe, nim, nisn, nama, prodi, asal_sekolah, dosen_id, no_hp, alamat, status, foto) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
                    $stmt->execute([$userId, $data['tipe'], $nimFinal, $nisnFinal, $data['nama'], $data['prodi'], $asalSekolahFinal, $data['dosen_id'], $data['no_hp'], $data['alamat'], $data['status'], $fotoFinal]);
                }

                $pdo->commit();
                setFlash('success', $isEdit ? 'Data berhasil diperbarui.' : 'Peserta baru berhasil ditambahkan. Peserta dapat langsung mengakses Portal Publik dengan NIM/NISN mereka.');
                header('Location: ' . BASE_URL . '/operator/mahasiswa.php');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h1 class="m3-page-title"><?= e($pageTitle) ?></h1>

<?php foreach ($errors as $err): ?>
  <div class="m3-banner m3-banner--error">
    <span class="m3-icon">error</span><span class="m3-grow"><?= e($err) ?></span>
  </div>
<?php endforeach; ?>

<section class="m3-card m3-card--elevated" style="max-width:960px">
  <div class="m3-card__body">
    <form method="post" enctype="multipart/form-data" novalidate>
      <?= csrfField() ?>

      <label class="m3-field__label">Tipe peserta</label>
      <div class="m3-segmented m3-mb-3">
        <input type="radio" name="tipe" id="tipeMhs" value="mahasiswa"
               <?= $data['tipe'] === 'mahasiswa' ? 'checked' : '' ?> onchange="toggleTipe()">
        <label for="tipeMhs"><span class="m3-icon m3-icon--sm">school</span>Mahasiswa</label>
        <input type="radio" name="tipe" id="tipePkl" value="siswa_pkl"
               <?= $data['tipe'] === 'siswa_pkl' ? 'checked' : '' ?> onchange="toggleTipe()">
        <label for="tipePkl"><span class="m3-icon m3-icon--sm">engineering</span>Siswa PKL</label>
      </div>

      <div class="m3-grid">
        <!-- Foto -->
        <div class="m3-col-4">
          <label class="m3-field__label">Foto peserta</label>
          <img id="fotoPreview" alt="Pratinjau foto" class="m3-avatar m3-avatar--xl m3-mb-2"
               src="<?= $data['foto'] ? FOTO_URL . rawurlencode($data['foto']) : '' ?>"
               style="<?= $data['foto'] ? '' : 'display:none' ?>">
          <input type="file" name="foto" class="m3-input" accept=".jpg,.jpeg,.png" onchange="previewFoto(this)">
          <div class="m3-field__help">JPG atau PNG, maksimal 2 MB.</div>
        </div>

        <!-- Identitas -->
        <div class="m3-col-8">
          <div class="m3-grid">
            <div class="m3-col-6" id="wrapNim">
              <label class="m3-field__label" for="nim">NIM</label>
              <input id="nim" type="text" name="nim" class="m3-input" value="<?= e($data['nim']) ?>">
            </div>
            <div class="m3-col-6" id="wrapNisn" style="display:none">
              <label class="m3-field__label" for="nisn">NISN</label>
              <input id="nisn" type="text" name="nisn" class="m3-input" value="<?= e($data['nisn']) ?>">
            </div>
            <div class="m3-col-12">
              <label class="m3-field__label" for="nama">Nama lengkap</label>
              <input id="nama" type="text" name="nama" class="m3-input" required value="<?= e($data['nama']) ?>">
            </div>
            <div class="m3-col-6" id="wrapProdi">
              <label class="m3-field__label" for="prodi">Program studi</label>
              <select id="prodi" name="prodi" class="m3-select">
                <?php foreach (['Sistem Informasi','Teknik Informatika'] as $st): ?>
                  <option value="<?= $st ?>" <?= $data['prodi'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="m3-col-12" id="wrapJurusan">
              <label class="m3-field__label" for="jurusan">Jurusan</label>
              <input id="jurusan" type="text" name="jurusan" class="m3-input" value="<?= e($data['jurusan']) ?>">
            </div>
            <div class="m3-col-6" id="wrapAsalSekolah" style="display:none">
              <label class="m3-field__label" for="asal_sekolah">Asal sekolah</label>
              <input id="asal_sekolah" type="text" name="asal_sekolah" class="m3-input" value="<?= e($data['asal_sekolah']) ?>">
            </div>
            <div class="m3-col-6">
              <label class="m3-field__label" for="status">Status</label>
              <select id="status" name="status" class="m3-select">
                <?php foreach (['aktif','cuti','lulus','selesai','nonaktif'] as $st): ?>
                  <option value="<?= $st ?>" <?= $data['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Kontak & pembimbing -->
        <div class="m3-col-6">
          <label class="m3-field__label" for="dosen_id">Pembimbing</label>
          <select id="dosen_id" name="dosen_id" class="m3-select">
            <option value="">Belum ditentukan</option>
            <?php foreach ($dosenList as $d): ?>
              <option value="<?= $d['id'] ?>" <?= (string)$data['dosen_id'] === (string)$d['id'] ? 'selected' : '' ?>>
                <?= e($d['nama']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="m3-col-6">
          <label class="m3-field__label" for="no_hp">No. HP</label>
          <input id="no_hp" type="text" name="no_hp" class="m3-input" value="<?= e($data['no_hp']) ?>">
        </div>
        <div class="m3-col-12">
          <label class="m3-field__label" for="alamat">Alamat</label>
          <textarea id="alamat" name="alamat" class="m3-textarea" rows="2"><?= e($data['alamat']) ?></textarea>
        </div>
      </div>

      <div class="m3-banner m3-banner--info m3-mt-3">
        <span class="m3-icon">info</span>
        <span class="m3-grow">
          Peserta tidak memerlukan akun terpisah. Setelah tersimpan, mereka dapat membuka
          <strong>portal peserta</strong> menggunakan NIM atau NISN untuk mengisi absensi,
          mencatat kegiatan, dan mengunduh sertifikat.
        </span>
      </div>

      <div class="m3-row m3-gap-sm m3-mt-3">
        <button type="submit" class="m3-btn m3-btn--filled">
          <span class="m3-icon m3-icon--sm">save</span>Simpan
        </button>
        <a href="<?= BASE_URL ?>/operator/mahasiswa.php" class="m3-btn m3-btn--text">Batal</a>
      </div>
    </form>
  </div>
</section>

<script>
function toggleTipe() {
  var isPkl = document.getElementById('tipePkl').checked;
  document.getElementById('wrapNim').style.display = isPkl ? 'none' : 'block';
  document.getElementById('wrapNisn').style.display = isPkl ? 'block' : 'none';
  document.getElementById('wrapAsalSekolah').style.display = isPkl ? 'block' : 'none';
  document.getElementById('wrapProdi').style.display = isPkl ? 'none' : 'block';
  document.getElementById('wrapJurusan').style.display = isPkl ? 'block' : 'none';
}
function previewFoto(input) {
  var preview = document.getElementById('fotoPreview');
  if (input.files && input.files[0]) {
    preview.src = URL.createObjectURL(input.files[0]);
    preview.style.display = 'block';
  }
}
toggleTipe();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
