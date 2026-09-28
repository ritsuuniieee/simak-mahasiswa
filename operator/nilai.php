<?php
// Kelola nilai peserta (dipakai sebagai komponen sertifikat html2canvas).
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['operator']);

$pageTitle = 'Data Nilai';
$errors = [];

if (!hasNilaiTable($pdo)) {
    $pageTitle = 'Data Nilai';
    include __DIR__ . '/../includes/header.php';
    echo '<h1 class="m3-page-title">Data nilai</h1>';
    echo '<div class="m3-banner m3-banner--warning"><span class="m3-icon">warning</span><span class="m3-grow">Tabel <code>nilai</code> belum ada. Jalankan <code>database/migrasi_revisi.sql</code> di database Anda, lalu muat ulang halaman ini.</span></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $errors[] = 'Sesi form kadaluarsa, coba lagi.';
    } else {
        $pesertaId = (int)($_POST['mahasiswa_id'] ?? 0);
        $angka = (float)($_POST['nilai_angka'] ?? -1);
        $catatan = trim($_POST['catatan'] ?? '');
        if (!$pesertaId) $errors[] = 'Pilih peserta.';
        elseif ($angka < 0 || $angka > 100) $errors[] = 'Nilai angka harus 0–100.';
        if (!$errors) {
            [$huruf, $predikat] = nilaiHurufPredikat($angka);
            $stmt = $pdo->prepare('INSERT INTO nilai (mahasiswa_id, nilai_angka, nilai_huruf, predikat, catatan) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE nilai_angka=VALUES(nilai_angka), nilai_huruf=VALUES(nilai_huruf), predikat=VALUES(predikat), catatan=VALUES(catatan)');
            $stmt->execute([$pesertaId, $angka, $huruf, $predikat, $catatan ?: null]);
            setFlash('success', "Nilai tersimpan ($angka / $huruf - $predikat).");
            header('Location: ' . BASE_URL . '/operator/nilai.php');
            exit;
        }
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM nilai WHERE mahasiswa_id = ?')->execute([(int)$_GET['delete']]);
    setFlash('success', 'Nilai berhasil dihapus.');
    header('Location: ' . BASE_URL . '/operator/nilai.php');
    exit;
}

$pesertaList = $pdo->query("SELECT id, nama, tipe, nim, nisn FROM mahasiswa ORDER BY nama")->fetchAll();
$search = trim($_GET['q'] ?? '');
$sql = "SELECT n.*, m.nama, m.nim, m.nisn, m.tipe, m.prodi FROM nilai n JOIN mahasiswa m ON m.id = n.mahasiswa_id";
$params = [];
if ($search !== '') {
    $sql .= " WHERE m.nama LIKE ? OR m.nim LIKE ? OR m.nisn LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY m.nama ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h1 class="m3-page-title">Data nilai</h1>

<?php foreach ($errors as $err): ?>
  <div class="m3-banner m3-banner--error"><span class="m3-icon">error</span><span class="m3-grow"><?= e($err) ?></span></div>
<?php endforeach; ?>

<section class="m3-card m3-card--filled m3-mb-4">
  <div class="m3-card__header">Input / perbarui nilai</div>
  <div class="m3-card__body">
    <p class="m3-body-medium m3-muted">Nilai ini otomatis tampil pada <strong>cetak sertifikat (html2canvas)</strong> bersama rekapan kehadiran.</p>
    <form method="post">
      <?= csrfField() ?>
      <div class="m3-grid">
        <div class="m3-col-4">
          <label class="m3-field__label" for="mahasiswa_id">Peserta</label>
          <select id="mahasiswa_id" name="mahasiswa_id" class="m3-select" required>
            <option value="">Pilih peserta</option>
            <?php foreach ($pesertaList as $p): ?>
              <option value="<?= $p['id'] ?>"><?= e($p['nama']) ?> (<?= e($p['tipe'] === 'siswa_pkl' ? $p['nisn'] : $p['nim']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="m3-col-4">
          <label class="m3-field__label" for="nilai_angka">Nilai angka (0–100)</label>
          <input id="nilai_angka" type="number" name="nilai_angka" class="m3-input" min="0" max="100" step="0.01" required>
          <div class="m3-field__help">Huruf &amp; predikat dihitung otomatis.</div>
        </div>
        <div class="m3-col-4">
          <label class="m3-field__label" for="catatan">Catatan (opsional)</label>
          <input id="catatan" type="text" name="catatan" class="m3-input" placeholder="Contoh: Lulus dengan baik">
        </div>
      </div>
      <button type="submit" class="m3-btn m3-btn--filled m3-mt-3"><span class="m3-icon m3-icon--sm">save</span>Simpan nilai</button>
    </form>
  </div>
</section>

<form class="m3-toolbar" method="get">
  <div class="m3-toolbar__search">
    <input type="text" name="q" class="m3-input" value="<?= e($search) ?>" placeholder="Cari nama, NIM, NISN">
  </div>
  <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">search</span>Cari</button>
</form>

<section class="m3-table-wrap">
  <div class="m3-table-scroll">
    <table class="m3-table">
      <thead><tr><th>Nama</th><th>Tipe</th><th>No. ID</th><th>Angka</th><th>Huruf</th><th>Predikat</th><th>Catatan</th><th class="m3-td-actions">Aksi</th></tr></thead>
      <tbody>
      <?php if (!$list): ?><tr><td colspan="8" class="m3-table__empty">Belum ada nilai.</td></tr><?php endif; ?>
      <?php foreach ($list as $n): ?>
        <tr>
          <td><?= e($n['nama']) ?></td>
          <td><?= badgeTipe($n['tipe']) ?></td>
          <td><?= e($n['tipe'] === 'siswa_pkl' ? $n['nisn'] : $n['nim']) ?></td>
          <td><?= e($n['nilai_angka']) ?></td>
          <td><span class="m3-badge m3-badge--primary"><?= e($n['nilai_huruf']) ?></span></td>
          <td><?= e($n['predikat']) ?></td>
          <td><?= e($n['catatan'] ?: '-') ?></td>
          <td class="m3-td-actions">
            <a href="<?= BASE_URL ?>/cetak_sertifikat.php?peserta_id=<?= (int)$n['mahasiswa_id'] ?>" target="_blank" class="m3-icon-btn m3-icon-btn--success" title="Pratinjau sertifikat"><span class="m3-icon m3-icon--sm">print</span></a>
            <a href="?delete=<?= (int)$n['mahasiswa_id'] ?>" class="m3-icon-btn m3-icon-btn--danger" onclick="return confirm('Hapus nilai ini?');"><span class="m3-icon m3-icon--sm">delete</span></a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
