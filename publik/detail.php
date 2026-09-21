<?php
/** Detail peserta versi publik: biodata ringkas + rekap bulanan + sertifikat. */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/publik/'); exit; }

$stmt = $pdo->prepare('SELECT m.*, d.nama AS nama_dosen FROM mahasiswa m LEFT JOIN dosen d ON d.id = m.dosen_id WHERE m.id = ?');
$stmt->execute([$id]);
$peserta = $stmt->fetch();
if (!$peserta) { http_response_code(404); exit('Peserta tidak ditemukan.'); }

$bulan = $_GET['bulan'] ?? '';
if (!validBulan($bulan)) $bulan = '';

$rekap = rekapKehadiran($pdo, $id);
$nilai = ambilNilai($pdo, $id);

$stmt = $pdo->prepare("SELECT DATE_FORMAT(tanggal,'%Y-%m') AS bulan, COUNT(*) AS total, SUM(status_masuk='tepat_waktu') AS tepat, SUM(status_masuk='terlambat') AS terlambat FROM absensi WHERE mahasiswa_id = ? GROUP BY DATE_FORMAT(tanggal,'%Y-%m') ORDER BY bulan DESC");
$stmt->execute([$id]);
$bulanan = $stmt->fetchAll();

if ($bulan !== '') {
    $stmt = $pdo->prepare('SELECT * FROM absensi WHERE mahasiswa_id = ? AND DATE_FORMAT(tanggal,"%Y-%m") = ? ORDER BY tanggal DESC');
    $stmt->execute([$id, $bulan]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM absensi WHERE mahasiswa_id = ? ORDER BY tanggal DESC LIMIT 30');
    $stmt->execute([$id]);
}
$riwayat = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM sertifikat WHERE mahasiswa_id = ? ORDER BY tanggal_upload DESC');
$stmt->execute([$id]);
$sertifikat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($peserta['nama']) ?> | Portal Publik</title>
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
  <a href="<?= BASE_URL ?>/publik/" class="m3-btn m3-btn--text m3-btn--sm"><span class="m3-icon m3-icon--sm">arrow_back</span>Beranda</a>
</header>

<main class="m3-main" style="max-width:1000px;margin:0 auto">
  <section class="m3-card m3-card--filled m3-mb-3">
    <div class="m3-card__body">
      <div class="m3-row" style="gap:20px">
        <?php if ($peserta['foto']): ?><img src="<?= FOTO_URL . rawurlencode($peserta['foto']) ?>" alt="" class="m3-avatar m3-avatar--lg">
        <?php else: ?><div class="m3-avatar m3-avatar--lg"><span class="m3-icon m3-icon--lg">person</span></div><?php endif; ?>
        <div class="m3-grow">
          <h1 class="m3-headline-small m3-mb-0"><?= e($peserta['nama']) ?> <?= badgeTipe($peserta['tipe']) ?></h1>
          <p class="m3-body-medium m3-muted m3-mb-0">
            <?= e(nomorIdentitas($peserta)) ?> &middot;
            <?= e($peserta['tipe'] === 'siswa_pkl' ? (($peserta['jurusan'] ? $peserta['jurusan'].' — ' : '').($peserta['asal_sekolah'] ?? '')) : ($peserta['prodi'] ?? '')) ?>
          </p>
          <p class="m3-body-medium m3-muted m3-mb-0">Pembimbing: <?= e($peserta['nama_dosen'] ?? 'Belum ditentukan') ?> &middot; Status: <?= e(ucfirst($peserta['status'])) ?></p>
          <?php if ($nilai): ?>
            <p class="m3-body-medium m3-mb-0">Nilai: <strong><?= e($nilai['nilai_angka']) ?> (<?= e($nilai['nilai_huruf']) ?> — <?= e($nilai['predikat']) ?>)</strong></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <div class="m3-cards-grid m3-mb-3">
    <div class="m3-stat m3-stat--primary"><span class="m3-stat__label">Total hadir</span><span class="m3-stat__value"><?= (int)$rekap['total'] ?></span><span class="m3-icon m3-stat__icon">event_available</span></div>
    <div class="m3-stat m3-stat--success"><span class="m3-stat__label">Tepat waktu</span><span class="m3-stat__value"><?= (int)$rekap['tepat'] ?></span><span class="m3-stat__meta"><?= e($rekap['persen_tepat']) ?>%</span><span class="m3-icon m3-stat__icon">schedule</span></div>
    <div class="m3-stat m3-stat--error"><span class="m3-stat__label">Terlambat</span><span class="m3-stat__value"><?= (int)$rekap['terlambat'] ?></span><span class="m3-icon m3-stat__icon">running_late</span></div>
    <div class="m3-stat m3-stat--tertiary"><span class="m3-stat__label">Sertifikat</span><span class="m3-stat__value"><?= count($sertifikat) ?></span><span class="m3-icon m3-stat__icon">workspace_premium</span></div>
  </div>

  <section class="m3-card m3-card--elevated m3-mb-3">
    <div class="m3-card__header">Rekapan absensi<?= $bulan ? ' &middot; ' . e(formatBulanId($bulan)) : '' ?></div>
    <div class="m3-card__body">
      <form method="get" class="m3-toolbar m3-mb-2">
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <input type="month" name="bulan" class="m3-input" style="width:auto" value="<?= e($bulan) ?>">
        <button class="m3-btn m3-btn--tonal m3-btn--sm">Filter bulan</button>
        <?php if ($bulan): ?><a href="?id=<?= (int)$id ?>" class="m3-btn m3-btn--text m3-btn--sm">Atur ulang</a><?php endif; ?>
      </form>
      <?php if ($bulanan): ?>
      <div class="m3-chipset m3-mb-2">
        <?php foreach ($bulanan as $b): ?>
          <a class="m3-chip <?= $bulan === $b['bulan'] ? 'is-selected' : '' ?>" href="?id=<?= (int)$id ?>&bulan=<?= e($b['bulan']) ?>">
            <?= e(formatBulanId($b['bulan'])) ?> (<?= (int)$b['total'] ?>)
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="m3-table-scroll">
        <table class="m3-table">
          <thead><tr><th>Tanggal</th><th>Masuk</th><th>Status</th><th>Keluar</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (!$riwayat): ?><tr><td colspan="5" class="m3-table__empty">Belum ada data absensi.</td></tr><?php endif; ?>
          <?php foreach ($riwayat as $r): ?>
            <tr><td><?= formatTanggal($r['tanggal']) ?></td><td><?= formatJam($r['jam_masuk']) ?></td><td><?= badgeStatusAbsen($r['status_masuk']) ?></td><td><?= formatJam($r['jam_keluar']) ?></td><td><?= badgeStatusAbsen($r['status_keluar']) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <section class="m3-card m3-card--elevated">
    <div class="m3-card__header">Sertifikat (<?= count($sertifikat) ?>)</div>
    <div class="m3-card__body">
      <?php if (!$sertifikat): ?>
        <p class="m3-body-medium m3-muted m3-mb-0">Belum ada sertifikat untuk peserta ini.</p>
      <?php else: ?>
        <div class="m3-cards-grid m3-cards-grid--wide">
        <?php foreach ($sertifikat as $s): ?>
          <div class="m3-card m3-card--outlined">
            <div class="m3-card__body m3-card__body--tight">
              <div class="m3-row" style="gap:14px">
                <span class="m3-icon m3-icon--lg" style="color:var(--m3-primary)">workspace_premium</span>
                <div class="m3-grow">
                  <div class="m3-title-small"><?= e($s['judul']) ?></div>
                  <div class="m3-body-small m3-muted"><?= formatTanggal($s['tanggal_upload']) ?><?= !empty($s['nomor']) ? ' &middot; ' . e($s['nomor']) : '' ?></div>
                </div>
                <a href="<?= UPLOAD_URL . rawurlencode($s['file_path']) ?>" target="_blank" class="m3-icon-btn m3-icon-btn--primary" title="Unduh berkas"><span class="m3-icon">download</span></a>
                <a href="<?= BASE_URL ?>/cetak_sertifikat.php?peserta_id=<?= (int)$id ?>&s_id=<?= (int)$s['id'] ?>" target="_blank" class="m3-icon-btn" title="Cetak desain (html2canvas)"><span class="m3-icon">print</span></a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>
<footer class="m3-footer">&copy; <?= date('Y') ?> STIKOM 22 Januari &middot; Portal Publik</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/material3.js"></script>
<script src="<?= BASE_URL ?>/assets/js/animasi.js"></script>
</body>
</html>
