<?php
// Impor data peserta dari XLSX/CSV (converter PhpSpreadsheet + fallback CSV).
require_once __DIR__ . '/_spreadsheet_helper.php';
requireRole(['operator']);

$pageTitle = 'Impor Data Peserta';
$hasil = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $errors[] = 'Sesi form kadaluarsa, silakan coba lagi.';
    } elseif (empty($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Pilih berkas template yang sudah diisi (XLSX atau CSV).';
    } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Terjadi kesalahan saat mengunggah berkas.';
    } else {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $rows = [];
        try {
            if ($ext === 'csv') {
                $h = fopen($_FILES['file']['tmp_name'], 'r');
                if (!$h) throw new Exception('Berkas tidak dapat dibaca.');
                // Lewati BOM
                $first = fgets($h);
                $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
                $head = str_getcsv($first, ';');
                if (count($head) < 2) { rewind($h); $head = null; }
                else {
                    // dukung koma maupun titik-koma
                    if (count($head) <= 1) { rewind($h); $head = null; }
                }
                if ($head === null) {
                    // coba baca ulang dengan koma
                    $h2 = fopen($_FILES['file']['tmp_name'], 'r');
                    $l = preg_replace('/^\xEF\xBB\xBF/', '', fgets($h2));
                    $head = str_getcsv($l, ',');
                    if (count($head) > 1) { fclose($h); $h = $h2; $delim = ','; }
                    else { fclose($h2); rewind($h); $delim = ';'; }
                } else {
                    $delim = substr_count($first, ';') >= substr_count($first, ',') ? ';' : ',';
                    // head sudah dibaca dengan ';' — bila delim koma, baca ulang
                    if ($delim === ',') { fclose($h); $h = fopen($_FILES['file']['tmp_name'], 'r'); $head = str_getcsv(preg_replace('/^\xEF\xBB\xBF/', '', fgets($h)), ','); }
                }
                while (($line = fgetcsv($h, 0, $delim)) !== false) {
                    if (count(array_filter($line, fn($v) => trim((string)$v) !== '')) === 0) continue;
                    $rows[] = $line;
                }
                fclose($h);
            } elseif (in_array($ext, ['xlsx', 'xls'], true)) {
                if (!spreadsheetAvailable()) {
                    throw new Exception('File XLSX butuh PhpSpreadsheet. Jalankan `composer install` di folder aplikasi, atau unggah versi CSV.');
                }
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($ext === 'xls' ? 'Xls' : 'Xlsx');
                $reader->setReadDataOnly(true);
                $ss = $reader->load($_FILES['file']['tmp_name']);
                $data = $ss->getActiveSheet()->toArray(null, true, true, false);
                array_shift($data); // buang header
                foreach ($data as $line) {
                    if (count(array_filter($line, fn($v) => trim((string)$v) !== '')) === 0) continue;
                    $rows[] = array_values($line);
                }
            } else {
                throw new Exception('Format berkas harus .xlsx atau .csv (unduh template dulu).');
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            // peta dosen by nidn
            $dosenMap = [];
            foreach ($pdo->query('SELECT id, nidn_nidk FROM dosen')->fetchAll() as $d) {
                $dosenMap[trim((string)$d['nidn_nidk'])] = $d['id'];
            }
            $ok = 0; $skip = 0; $fail = [];
            foreach ($rows as $i => $c) {
                $no = $i + 2;
                $c = array_pad($c, 11, '');
                [$tipe, $nim, $nisn, $nama, $prodi, $jurusan, $asal, $nidn, $hp, $alamat, $status] = array_map(fn($v) => trim((string)$v), $c);
                if (!in_array($tipe, ['mahasiswa', 'siswa_pkl'], true)) { $fail[] = "Baris $no: tipe harus mahasiswa/siswa_pkl."; continue; }
                if ($nama === '') { $fail[] = "Baris $no: nama wajib diisi."; continue; }
                $status = in_array($status, ['aktif','cuti','lulus','selesai','nonaktif'], true) ? $status : 'aktif';
                $dosenId = ($nidn !== '' && isset($dosenMap[$nidn])) ? $dosenMap[$nidn] : null;
                try {
                    if ($tipe === 'mahasiswa') {
                        if ($nim === '') throw new Exception("Baris $no: NIM wajib diisi.");
                        if ($prodi === '') $prodi = 'Sistem Informasi';
                        if (!in_array($prodi, ['Sistem Informasi', 'Teknik Informatika'], true)) $prodi = 'Sistem Informasi';
                        $stmt = $pdo->prepare('SELECT id FROM mahasiswa WHERE nim = ?');
                        $stmt->execute([$nim]);
                        if ($ex = $stmt->fetchColumn()) {
                            $pdo->prepare('UPDATE mahasiswa SET nama=?, prodi=?, dosen_id=COALESCE(?,dosen_id), no_hp=?, alamat=?, status=? WHERE id=?')
                                ->execute([$nama, $prodi, $dosenId, $hp ?: null, $alamat ?: null, $status, $ex]);
                        } else {
                            $cols = hasMahasiswaPasswordColumn($pdo)
                                ? 'INSERT INTO mahasiswa (password, tipe, nim, nama, prodi, dosen_id, no_hp, alamat, status) VALUES (NULL,?,?,?,?,?,?,?,?)'
                                : 'INSERT INTO mahasiswa (tipe, nim, nama, prodi, dosen_id, no_hp, alamat, status) VALUES (?,?,?,?,?,?,?,?)';
                            $pdo->prepare($cols)->execute([$tipe, $nim, $nama, $prodi, $dosenId, $hp ?: null, $alamat ?: null, $status]);
                        }
                    } else {
                        if ($nisn === '') throw new Exception("Baris $no: NISN wajib diisi.");
                        if ($asal === '') throw new Exception("Baris $no: asal_sekolah wajib diisi.");
                        $stmt = $pdo->prepare('SELECT id FROM mahasiswa WHERE nisn = ?');
                        $stmt->execute([$nisn]);
                        if ($ex = $stmt->fetchColumn()) {
                            $pdo->prepare('UPDATE mahasiswa SET nama=?, jurusan=?, asal_sekolah=?, dosen_id=COALESCE(?,dosen_id), no_hp=?, alamat=?, status=? WHERE id=?')
                                ->execute([$nama, $jurusan ?: null, $asal, $dosenId, $hp ?: null, $alamat ?: null, $status, $ex]);
                        } else {
                            $pdo->prepare('INSERT INTO mahasiswa (tipe, nisn, nama, jurusan, asal_sekolah, dosen_id, no_hp, alamat, status) VALUES (?,?,?,?,?,?,?,?,?)')
                                ->execute([$tipe, $nisn, $nama, $jurusan ?: null, $asal, $dosenId, $hp ?: null, $alamat ?: null, $status]);
                        }
                    }
                    $ok++;
                } catch (Exception $e) {
                    $fail[] = $e->getMessage();
                }
            }
            $hasil = ['ok' => $ok, 'skip' => $skip, 'fail' => $fail];
            if ($ok) setFlash('success', "Impor selesai: $ok baris berhasil" . ($fail ? ', ' . count($fail) . ' baris gagal.' : '.'));
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h1 class="m3-page-title">Impor data peserta</h1>

<div class="m3-banner m3-banner--info m3-mb-3">
  <span class="m3-icon">info</span>
  <span class="m3-grow">Langkah: 1) <a href="<?= BASE_URL ?>/operator/mahasiswa_template.php"><strong>unduh template</strong></a>
  (<?= spreadsheetAvailable() ? 'XLSX siap dipakai' : 'mode CSV — jalankan <code>composer install</code> untuk XLSX' ?>),
  2) isi sesuai petunjuk, 3) unggah di bawah. Data dengan NIM/NISN yang sama akan <strong>diperbarui</strong>, bukan diduplikasi.</span>
</div>

<?php foreach ($errors as $err): ?>
  <div class="m3-banner m3-banner--error"><span class="m3-icon">error</span><span class="m3-grow"><?= e($err) ?></span></div>
<?php endforeach; ?>

<?php if ($hasil && $hasil['fail']): ?>
  <div class="m3-banner m3-banner--error m3-mb-3"><span class="m3-icon">error</span>
    <span class="m3-grow"><?= count($hasil['fail']) ?> baris gagal:<br><?= implode('<br>', array_map('e', array_slice($hasil['fail'], 0, 20))) ?></span>
  </div>
<?php endif; ?>

<section class="m3-card m3-card--elevated" style="max-width:720px">
  <div class="m3-card__body">
    <form method="post" enctype="multipart/form-data">
      <?= csrfField() ?>
      <label class="m3-field__label" for="file">Berkas template terisi (.xlsx / .csv)</label>
      <input id="file" type="file" name="file" class="m3-input" accept=".xlsx,.xls,.csv" required>
      <div class="m3-row m3-gap-sm m3-mt-3">
        <button type="submit" class="m3-btn m3-btn--filled"><span class="m3-icon m3-icon--sm">upload</span>Proses impor</button>
        <a href="<?= BASE_URL ?>/operator/mahasiswa.php" class="m3-btn m3-btn--text">Kembali</a>
      </div>
    </form>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
