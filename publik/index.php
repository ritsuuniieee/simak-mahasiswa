<?php
/**
 * PORTAL PUBLIK — rekapan absensi & sertifikat TANPA login / tanpa NIM-NISN.
 * Folder: /publik/
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$search = trim($_GET['q'] ?? '');
$tipeFilter = $_GET['tipe'] ?? '';
$bulan = $_GET['bulan'] ?? '';
if (!validBulan($bulan)) $bulan = '';

$sql = "SELECT m.id, m.nama, m.tipe, m.nim, m.nisn, m.prodi, m.jurusan, m.asal_sekolah, m.foto, m.status, d.nama AS nama_dosen,
  (SELECT COUNT(*) FROM absensi a WHERE a.mahasiswa_id = m.id" . ($bulan !== '' ? " AND DATE_FORMAT(a.tanggal,'%Y-%m') = " . $pdo->quote($bulan) : "") . ") AS total_hadir,
  (SELECT COUNT(*) FROM absensi a WHERE a.mahasiswa_id = m.id AND a.status_masuk='tepat_waktu'" . ($bulan !== '' ? " AND DATE_FORMAT(a.tanggal,'%Y-%m') = " . $pdo->quote($bulan) : "") . ") AS total_tepat,
  (SELECT COUNT(*) FROM absensi a WHERE a.mahasiswa_id = m.id AND a.status_masuk='terlambat'" . ($bulan !== '' ? " AND DATE_FORMAT(a.tanggal,'%Y-%m') = " . $pdo->quote($bulan) : "") . ") AS total_terlambat,
  (SELECT COUNT(*) FROM sertifikat s WHERE s.mahasiswa_id = m.id) AS total_sertifikat
  FROM mahasiswa m LEFT JOIN dosen d ON d.id = m.dosen_id WHERE 1=1";
$params = [];
if ($search !== '') {
    $sql .= " AND (m.nama LIKE ? OR m.nim LIKE ? OR m.nisn LIKE ? OR m.asal_sekolah LIKE ?)";
    $like = "%$search%";
    array_push($params, $like, $like, $like, $like);
}
if (in_array($tipeFilter, ['mahasiswa','siswa_pkl'], true)) { $sql .= " AND m.tipe = ?"; $params[] = $tipeFilter; }
$sql .= " ORDER BY m.nama ASC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

$totalMhs = $pdo->query("SELECT COUNT(*) FROM mahasiswa WHERE tipe='mahasiswa'")->fetchColumn();
$totalPkl = $pdo->query("SELECT COUNT(*) FROM mahasiswa WHERE tipe='siswa_pkl'")->fetchColumn();
$totalAbsen = $pdo->query("SELECT COUNT(*) FROM absensi")->fetchColumn();
$totalSert = $pdo->query("SELECT COUNT(*) FROM sertifikat")->fetchColumn();

// Pengelompokan absensi per bulan (global, mengikuti filter tipe+pencarian)
$sqlB = "SELECT DATE_FORMAT(a.tanggal,'%Y-%m') AS bulan, COUNT(*) AS total FROM absensi a JOIN mahasiswa m ON m.id=a.mahasiswa_id WHERE 1=1";
$pB = [];
if ($search !== '') { $sqlB .= " AND (m.nama LIKE ? OR m.nim LIKE ? OR m.nisn LIKE ?)"; $like="%$search%"; array_push($pB,$like,$like,$like); }
if (in_array($tipeFilter, ['mahasiswa','siswa_pkl'], true)) { $sqlB .= " AND m.tipe = ?"; $pB[] = $tipeFilter; }
$sqlB .= " GROUP BY DATE_FORMAT(a.tanggal,'%Y-%m') ORDER BY bulan DESC LIMIT 12";
$stmt = $pdo->prepare($sqlB);
$stmt->execute($pB);
$bulanan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portal Publik | SIMAK Mahasiswa &amp; PKL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,400;8..144,500;8..144,600;8..144,700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/material3.css" rel="stylesheet">
</head>
<body>
<header class="m3-appbar">
  <a class="m3-appbar__brand" href="<?= BASE_URL ?>/publik/">
    <img src="<?= BASE_URL ?>/assets/images/logo-stikom.png" alt="" class="m3-appbar__logo">
    <span class="m3-appbar__title">Portal publik</span>
  </a>
  <span class="m3-appbar__spacer"></span>
  <a href="<?= BASE_URL ?>/login.php" class="m3-btn m3-btn--outlined m3-btn--sm"><span class="m3-icon m3-icon--sm">key</span>Masuk</a>
  <a href="<?= BASE_URL ?>/portal.php" class="m3-btn m3-btn--tonal m3-btn--sm"><span class="m3-icon m3-icon--sm">login</span>Portal peserta</a>
</header>

<main class="m3-main" style="max-width:1100px;margin:0 auto">
  <section class="m3-card m3-card--elevated m3-mb-3">
    <div class="m3-card__body">
      <div class="m3-row--between" style="gap:16px;flex-wrap:wrap">
        <div class="m3-grow" style="min-width:240px">
          <h1 class="m3-headline-small m3-mb-0">SIMAK Mahasiswa &amp; PKL</h1>
          <p class="m3-body-medium m3-muted m3-mb-0">Rekapan absensi &amp; sertifikat terbuka untuk umum — tanpa perlu memasukkan NIM/NISN.</p>
        </div>
        <div class="m3-row m3-gap-sm" style="flex-wrap:wrap">
          <a href="<?= BASE_URL ?>/login.php" class="m3-btn m3-btn--filled m3-btn--sm"><span class="m3-icon m3-icon--sm">key</span>Masuk operator / dosen</a>
          <a href="<?= BASE_URL ?>/portal.php" class="m3-btn m3-btn--outlined m3-btn--sm"><span class="m3-icon m3-icon--sm">badge</span>Portal peserta (NIM/NISN)</a>
        </div>
      </div>
    </div>
  </section>

  <h2 class="m3-title-medium">Rekapan absensi &amp; sertifikat</h2>

  <div class="m3-cards-grid m3-mb-3">
    <div class="m3-stat m3-stat--primary"><span class="m3-stat__label">Mahasiswa</span><span class="m3-stat__value"><?= (int)$totalMhs ?></span><span class="m3-icon m3-stat__icon">school</span></div>
    <div class="m3-stat m3-stat--tertiary"><span class="m3-stat__label">Siswa PKL</span><span class="m3-stat__value"><?= (int)$totalPkl ?></span><span class="m3-icon m3-stat__icon">engineering</span></div>
    <div class="m3-stat m3-stat--success"><span class="m3-stat__label">Absensi</span><span class="m3-stat__value"><?= (int)$totalAbsen ?></span><span class="m3-icon m3-stat__icon">event_available</span></div>
    <div class="m3-stat m3-stat--secondary"><span class="m3-stat__label">Sertifikat</span><span class="m3-stat__value"><?= (int)$totalSert ?></span><span class="m3-icon m3-stat__icon">workspace_premium</span></div>
  </div>

  <form class="m3-toolbar" method="get">
    <input type="month" name="bulan" class="m3-input" style="width:auto" value="<?= e($bulan) ?>" title="Kelompokkan per bulan">
    <select name="tipe" class="m3-select" style="width:auto">
      <option value="">Semua tipe</option>
      <option value="mahasiswa" <?= $tipeFilter==='mahasiswa'?'selected':'' ?>>Mahasiswa</option>
      <option value="siswa_pkl" <?= $tipeFilter==='siswa_pkl'?'selected':'' ?>>Siswa PKL</option>
    </select>
    <div class="m3-toolbar__search">
      <input type="text" name="q" class="m3-input" value="<?= e($search) ?>" placeholder="Cari nama, NIM, NISN, sekolah">
    </div>
    <button class="m3-btn m3-btn--tonal"><span class="m3-icon m3-icon--sm">search</span>Cari</button>
    <a href="<?= BASE_URL ?>/publik/" class="m3-btn m3-btn--text">Atur ulang</a>
  </form>

  <?php if ($bulanan): ?>
  <section class="m3-card m3-card--filled m3-mb-3">
    <div class="m3-card__header">Pengelompokan absensi per bulan</div>
    <div class="m3-chipset" style="padding:0 16px 16px">
      <?php foreach ($bulanan as $b): ?>
        <a class="m3-chip <?= $bulan === $b['bulan'] ? 'is-selected' : '' ?>" href="?bulan=<?= e($b['bulan']) ?>&tipe=<?= e($tipeFilter) ?>&q=<?= urlencode($search) ?>">
          <?= e(formatBulanId($b['bulan'])) ?> &middot; <?= (int)$b['total'] ?>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($bulan): ?>
    <div class="m3-banner m3-banner--info m3-mb-3"><span class="m3-icon">calendar_month</span>
      <span class="m3-grow">Angka kehadiran di bawah dihitung khusus bulan <strong><?= e(formatBulanId($bulan)) ?></strong>.</span></div>
  <?php endif; ?>

  <section class="m3-table-wrap">
    <div class="m3-table-scroll">
      <table class="m3-table">
        <thead><tr><th>Foto</th><th>Peserta</th><th>Tipe</th><th>Hadir</th><th>Tepat</th><th>Terlambat</th><th>Sertifikat</th><th></th></tr></thead>
        <tbody>
        <?php if (!$list): ?><tr><td colspan="8" class="m3-table__empty">Tidak ada peserta ditemukan.</td></tr><?php endif; ?>
        <?php foreach ($list as $p): ?>
          <tr>
            <td>
              <?php if ($p['foto']): ?><img src="<?= FOTO_URL . rawurlencode($p['foto']) ?>" alt="" class="m3-avatar">
              <?php else: ?><div class="m3-avatar"><span class="m3-icon">person</span></div><?php endif; ?>
            </td>
            <td><strong><?= e($p['nama']) ?></strong><br><span class="m3-body-small m3-muted"><?= e(nomorIdentitas($p)) ?> &middot; <?= e($p['tipe']==='siswa_pkl' ? ($p['asal_sekolah'] ?: '-') : ($p['prodi'] ?: '-')) ?></span></td>
            <td><?= badgeTipe($p['tipe']) ?></td>
            <td><?= (int)$p['total_hadir'] ?></td>
            <td><?= (int)$p['total_tepat'] ?></td>
            <td><?= (int)$p['total_terlambat'] ?></td>
            <td><span class="m3-badge m3-badge--neutral"><?= (int)$p['total_sertifikat'] ?></span></td>
            <td><a class="m3-btn m3-btn--text m3-btn--sm" href="<?= BASE_URL ?>/publik/detail.php?id=<?= (int)$p['id'] ?>">Detail</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
  <p class="m3-body-small m3-muted m3-mt-2">Menampilkan maks. 200 peserta. Data kontak detail (alamat/No. HP) disembunyikan di portal publik.</p>
</main>
<footer class="m3-footer">&copy; <?= date('Y') ?> STIKOM 22 Januari &middot; Portal Publik</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/material3.js"></script>
<script src="<?= BASE_URL ?>/assets/js/animasi.js"></script>
</body>
</html>
