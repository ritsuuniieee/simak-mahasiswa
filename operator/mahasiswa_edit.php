<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    // File ini khusus EDIT. Tambah data lewat mahasiswa_form.php
    header('Location: ' . BASE_URL . '/operator/mahasiswa_form.php');
    exit;
}
$pageTitle = 'Edit Data Peserta';

$stmt = $pdo->prepare('SELECT * FROM mahasiswa WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    setFlash('error', 'Data tidak ditemukan.');
    header('Location: ' . BASE_URL . '/operator/mahasiswa.php');
    exit;
}

// Tipe DIKUNCI: tidak boleh diubah saat edit (sesuai revisi)
$tipe = $row['tipe'];
$isMhs = $tipe === 'mahasiswa';

$data = [
    'nim' => $row['nim'] ?? '', 'nisn' => $row['nisn'] ?? '', 'nama' => $row['nama'] ?? '',
    'prodi' => $row['prodi'] ?? '', 'jurusan' => $row['jurusan'] ?? '',
    'asal_sekolah' => $row['asal_sekolah'] ?? '', 'dosen_id' => $row['dosen_id'] ?? '',
    'no_hp' => $row['no_hp'] ?? '', 'alamat' => $row['alamat'] ?? '',
    'status' => $row['status'] ?? 'aktif', 'foto' => $row['foto'] ?? '',
];
$errors = [];
$hasPwCol = hasMahasiswaPasswordColumn($pdo);

$dosenList = $pdo->query('SELECT id, nama FROM dosen ORDER BY nama')->fetchAll();
$allowedExtFoto = ['jpg', 'jpeg', 'png'];
$maxSizeFoto = 2 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $errors[] = 'Sesi form kadaluarsa, silakan coba lagi.';
    } else {
        // Tipe TIDAK diambil dari POST — tetap pakai $tipe dari database
        $data['nama'] = trim($_POST['nama'] ?? '');
        $data['dosen_id'] = ($_POST['dosen_id'] ?? '') !== '' ? (int)$_POST['dosen_id'] : null;
        $data['no_hp'] = trim($_POST['no_hp'] ?? '');
        $data['alamat'] = trim($_POST['alamat'] ?? '');
        $data['status'] = $_POST['status'] ?? 'aktif';
        $passwordBaru = trim($_POST['password_baru'] ?? '');

        if ($isMhs) {
            $data['nim'] = trim($_POST['nim'] ?? '');
            $data['prodi'] = $_POST['prodi'] ?? '';
            // Pastikan field tipe lain dikosongkan
            $data['nisn'] = ''; $data['jurusan'] = ''; $data['asal_sekolah'] = '';
            if ($data['nama'] === '' || $data['prodi'] === '') $errors[] = 'Nama dan program studi wajib diisi.';
            if ($data['nim'] === '') $errors[] = 'NIM wajib diisi untuk tipe Mahasiswa.';
        } else {
            $data['nisn'] = trim($_POST['nisn'] ?? '');
            $data['jurusan'] = trim($_POST['jurusan'] ?? '');
            $data['asal_sekolah'] = trim($_POST['asal_sekolah'] ?? '');
            $data['nim'] = ''; $data['prodi'] = null;
            if ($data['nama'] === '') $errors[] = 'Nama wajib diisi.';
            if ($data['nisn'] === '') $errors[] = 'NISN wajib diisi untuk tipe Siswa PKL.';
            if ($data['asal_sekolah'] === '') $errors[] = 'Asal sekolah wajib diisi untuk tipe Siswa PKL.';
        }

        if (!in_array($data['status'], ['aktif','cuti','lulus','selesai','nonaktif'], true)) {
            $data['status'] = 'aktif';
        }
        if ($hasPwCol && $passwordBaru !== '' && strlen($passwordBaru) < 6) {
            $errors[] = 'Password baru minimal 6 karakter (kosongkan jika tidak diubah).';
        }

        if (!$errors && $data['nim'] !== '') {
            $stmt = $pdo->prepare('SELECT id FROM mahasiswa WHERE nim = ? AND id != ?');
            $stmt->execute([$data['nim'], $id]);
            if ($stmt->fetch()) $errors[] = 'NIM sudah digunakan.';
        }
        if (!$errors && $data['nisn'] !== '') {
            $stmt = $pdo->prepare('SELECT id FROM mahasiswa WHERE nisn = ? AND id != ?');
            $stmt->execute([$data['nisn'], $id]);
            if ($stmt->fetch()) $errors[] = 'NISN sudah digunakan.';
        }

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
                if ($fotoBaru) {
                    if (!is_dir(FOTO_DIR)) mkdir(FOTO_DIR, 0775, true);
                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], FOTO_DIR . $fotoBaru)) {
                        throw new Exception('Gagal menyimpan foto ke server.');
                    }
                    if (!empty($data['foto']) && is_file(FOTO_DIR . $data['foto'])) @unlink(FOTO_DIR . $data['foto']);
                }
                $fotoFinal = $fotoBaru ?? ($data['foto'] ?: null);
                $nimFinal = $isMhs ? $data['nim'] : null;
                $nisnFinal = !$isMhs ? $data['nisn'] : null;
                $prodiFinal = $isMhs ? $data['prodi'] : null;
                $jurusanFinal = !$isMhs ? ($data['jurusan'] ?: null) : null;
                $asalFinal = !$isMhs ? $data['asal_sekolah'] : null;

                $sql = 'UPDATE mahasiswa SET nim=?, nisn=?, nama=?, prodi=?, jurusan=?, asal_sekolah=?, dosen_id=?, no_hp=?, alamat=?, status=?, foto=? WHERE id=?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nimFinal, $nisnFinal, $data['nama'], $prodiFinal, $jurusanFinal, $asalFinal, $data['dosen_id'], $data['no_hp'], $data['alamat'], $data['status'], $fotoFinal, $id]);

                if ($hasPwCol && $passwordBaru !== '') {
                    $stmt = $pdo->prepare('UPDATE mahasiswa SET password=? WHERE id=?');
                    $stmt->execute([password_hash($passwordBaru, PASSWORD_DEFAULT), $id]);
                }

                $pdo->commit();
                setFlash('success', 'Data berhasil diperbarui. Tipe peserta dikunci (' . labelTipe($tipe) . ') dan tidak dapat diubah.');
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
<h1 class="m3-page-title">Edit data peserta</h1>

<div class="m3-banner m3-banner--info m3-mb-3">
  <span class="m3-icon">lock</span>
  <span class="m3-grow">Tipe peserta dikunci sebagai <strong><?= e(labelTipe($tipe)) ?></strong> dan tidak dapat diubah. Hanya input yang relevan dengan tipe ini yang ditampilkan.</span>
</div>

<?php foreach ($errors as $err): ?>
  <div class="m3-banner m3-banner--error">
    <span class="m3-icon">error</span><span class="m3-grow"><?= e($err) ?></span>
  </div>
<?php endforeach; ?>

<section class="m3-card m3-card--elevated" style="max-width:960px">
  <div class="m3-card__body">
    <form method="post" enctype="multipart/form-data" novalidate>
      <?= csrfField() ?>

      <div class="m3-grid">
        <div class="m3-col-4">
          <label class="m3-field__label">Foto peserta</label>
          <img id="fotoPreview" alt="Pratinjau foto" class="m3-avatar m3-avatar--xl m3-mb-2"
               src="<?= $data['foto'] ? FOTO_URL . rawurlencode($data['foto']) : '' ?>"
               style="<?= $data['foto'] ? '' : 'display:none' ?>">
          <input type="file" name="foto" class="m3-input" accept=".jpg,.jpeg,.png" onchange="previewFoto(this)">
          <div class="m3-field__help">JPG atau PNG, maksimal 2 MB.</div>
          <div class="m3-field__help m3-mt-2">Tipe: <?= badgeTipe($tipe) ?></div>
        </div>

        <div class="m3-col-8">
          <div class="m3-grid">
            <?php if ($isMhs): ?>
              <div class="m3-col-6">
                <label class="m3-field__label" for="nim">NIM</label>
                <input id="nim" type="text" name="nim" class="m3-input" required value="<?= e($data['nim']) ?>">
              </div>
              <div class="m3-col-6">
                <label class="m3-field__label" for="prodi">Program studi</label>
                <select id="prodi" name="prodi" class="m3-select">
                  <?php foreach (['Sistem Informasi','Teknik Informatika'] as $st): ?>
                    <option value="<?= $st ?>" <?= $data['prodi'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php else: ?>
              <div class="m3-col-6">
                <label class="m3-field__label" for="nisn">NISN</label>
                <input id="nisn" type="text" name="nisn" class="m3-input" required value="<?= e($data['nisn']) ?>">
              </div>
              <div class="m3-col-6">
                <label class="m3-field__label" for="jurusan">Jurusan / Bidang</label>
                <input id="jurusan" type="text" name="jurusan" class="m3-input" value="<?= e($data['jurusan']) ?>"
                       placeholder="Contoh: TKJ, RPL, Multimedia">
              </div>
              <div class="m3-col-12">
                <label class="m3-field__label" for="asal_sekolah">Asal sekolah</label>
                <input id="asal_sekolah" type="text" name="asal_sekolah" class="m3-input" required value="<?= e($data['asal_sekolah']) ?>">
              </div>
            <?php endif; ?>
            <div class="m3-col-12">
              <label class="m3-field__label" for="nama">Nama lengkap</label>
              <input id="nama" type="text" name="nama" class="m3-input" required value="<?= e($data['nama']) ?>">
            </div>
            <div class="m3-col-6">
              <label class="m3-field__label" for="status">Status</label>
              <select id="status" name="status" class="m3-select">
                <?php foreach (['aktif','cuti','lulus','selesai','nonaktif'] as $st): ?>
                  <option value="<?= $st ?>" <?= $data['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if ($hasPwCol): ?>
            <div class="m3-col-6">
              <label class="m3-field__label" for="password_baru">Password portal baru</label>
              <input id="password_baru" type="password" name="password_baru" class="m3-input" autocomplete="new-password" minlength="6">
              <div class="m3-field__help">Kosongkan jika tidak diubah. Minimal 6 karakter.</div>
            </div>
            <?php endif; ?>
          </div>
        </div>

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
function previewFoto(input) {
  var preview = document.getElementById('fotoPreview');
  if (input.files && input.files[0]) {
    preview.src = URL.createObjectURL(input.files[0]);
    preview.style.display = 'block';
  }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
