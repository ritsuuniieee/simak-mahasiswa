<?php
/**
 * Cetak sertifikat berbasis HTML + html2canvas.
 * Data menyesuaikan rekapan kehadiran (total/tepat/terlambat) + nilai.
 * Akses: publik/operator/dosen/peserta (tanpa login khusus — sesuai portal publik).
 * Param: ?peserta_id=ID[&s_id=SERTIFIKAT_ID]
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pesertaId = (int)($_GET['peserta_id'] ?? 0);
$sertifikatId = isset($_GET['s_id']) ? (int)$_GET['s_id'] : null;
if (!$pesertaId) { http_response_code(404); exit('peserta_id wajib diisi.'); }

$stmt = $pdo->prepare('SELECT m.*, d.nama AS nama_dosen FROM mahasiswa m LEFT JOIN dosen d ON d.id = m.dosen_id WHERE m.id = ?');
$stmt->execute([$pesertaId]);
$peserta = $stmt->fetch();
if (!$peserta) { http_response_code(404); exit('Peserta tidak ditemukan.'); }

$sertifikat = null;
if ($sertifikatId) {
    $stmt = $pdo->prepare('SELECT * FROM sertifikat WHERE id = ? AND mahasiswa_id = ?');
    $stmt->execute([$sertifikatId, $pesertaId]);
    $sertifikat = $stmt->fetch();
}
if (!$sertifikat) {
    $stmt = $pdo->prepare('SELECT * FROM sertifikat WHERE mahasiswa_id = ? ORDER BY tanggal_upload DESC LIMIT 1');
    $stmt->execute([$pesertaId]);
    $sertifikat = $stmt->fetch();
}

$rekap = rekapKehadiran($pdo, $pesertaId);
$nilai = ambilNilai($pdo, $pesertaId);
$nomor = $sertifikat['nomor'] ?? ('SERT/' . date('Y') . '/' . str_pad((string)$pesertaId, 5, '0', STR_PAD_LEFT));
$judul = $sertifikat['judul'] ?? 'Sertifikat Penghargaan';
$identitas = nomorIdentitas($peserta);
$afiliasi = $peserta['tipe'] === 'siswa_pkl'
    ? (($peserta['jurusan'] ? $peserta['jurusan'] . ' — ' : '') . ($peserta['asal_sekolah'] ?? ''))
    : ($peserta['prodi'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cetak Sertifikat — <?= e($peserta['nama']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/material3.css" rel="stylesheet">
<style>
  body { background:#e8e2d9; font-family:'Inter',system-ui,sans-serif; }
  .wrap { max-width:1080px; margin:24px auto; padding:0 16px; }
  #sertifikat {
    width:100%; aspect-ratio: 1.414/1; background:#fffdf7; color:#2b2118;
    border:10px double #8a6d2f; border-radius:6px; padding:44px 56px; position:relative;
    box-sizing:border-box; overflow:hidden;
  }
  #sertifikat::before { content:''; position:absolute; inset:14px; border:2px solid #c9a94e; border-radius:4px; pointer-events:none; }
  .s-head { text-align:center; }
  .s-head img { height:64px; }
  .s-title { font-family:'Playfair Display',serif; font-size:44px; letter-spacing:6px; margin:6px 0 0; color:#6b4e12; }
  .s-sub { letter-spacing:3px; font-size:13px; color:#8a6d2f; text-transform:uppercase; }
  .s-name { font-family:'Playfair Display',serif; font-size:38px; margin:14px 0 2px; }
  .s-meta { font-size:15px; color:#5c4a2f; }
  .s-box { display:flex; gap:16px; margin-top:18px; }
  .s-stat { flex:1; border:1px solid #e3d3a6; background:#fff8e6; border-radius:10px; padding:10px 14px; text-align:center; }
  .s-stat b { font-size:22px; display:block; }
  .s-stat span { font-size:12px; color:#7a6234; }
  .s-foot { display:flex; justify-content:space-between; margin-top:26px; font-size:13px; }
  .toolbar { display:flex; gap:8px; margin:16px 0; flex-wrap:wrap; }
  @media print { .toolbar, .note { display:none; } body{background:#fff;} .wrap{margin:0;max-width:none;} }
</style>
</head>
<body>
<div class="wrap">
  <div class="toolbar">
    <button class="m3-btn m3-btn--filled" id="btnPng"><span class="m3-icon m3-icon--sm">download</span>Unduh PNG (html2canvas)</button>
    <button class="m3-btn m3-btn--outlined" onclick="window.print()"><span class="m3-icon m3-icon--sm">print</span>Cetak / PDF</button>
    <a class="m3-btn m3-btn--text" href="<?= BASE_URL ?>/publik/detail.php?id=<?= (int)$pesertaId ?>">Kembali</a>
  </div>
  <p class="note m3-body-small m3-muted">Desain menyesuaikan <strong>rekapan kehadiran</strong> (otomatis dari absensi) dan <strong>nilai</strong> (dari menu Nilai operator).</p>

  <div id="sertifikat">
    <div class="s-head">
      <img src="<?= BASE_URL ?>/assets/images/logo-stikom.png" alt="Logo" onerror="this.style.display='none'">
      <div class="s-sub">STIKOM 22 Januari &middot; <?= e($judul) ?></div>
      <h1 class="s-title">SERTIFIKAT</h1>
      <div class="s-meta">Nomor: <?= e($nomor) ?></div>
    </div>
    <p class="s-meta" style="text-align:center;margin-bottom:0">Diberikan dengan bangga kepada</p>
    <h2 class="s-name" style="text-align:center"><?= e($peserta['nama']) ?></h2>
    <p class="s-meta" style="text-align:center">
      <?= e($peserta['tipe'] === 'siswa_pkl' ? 'NISN' : 'NIM') ?>: <strong><?= e($identitas) ?></strong>
      &middot; <?= e($afiliasi) ?>
    </p>
    <div class="s-box">
      <div class="s-stat"><b><?= (int)$rekap['total'] ?></b><span>Total kehadiran (hari)</span></div>
      <div class="s-stat"><b><?= (int)$rekap['tepat'] ?></b><span>Tepat waktu (<?= e($rekap['persen_tepat']) ?>%)</span></div>
      <div class="s-stat"><b><?= (int)$rekap['terlambat'] ?></b><span>Terlambat</span></div>
      <div class="s-stat"><b><?= $nilai ? e($nilai['nilai_angka']) . ' / ' . e($nilai['nilai_huruf']) : '-' ?></b><span>Nilai<?= $nilai ? ' — ' . e($nilai['predikat']) : '' ?></span></div>
    </div>
    <div class="s-foot">
      <div>Kendari, <?= date('d-m-Y') ?><br>Pembimbing: <strong><?= e($peserta['nama_dosen'] ?? '-') ?></strong></div>
      <div style="text-align:right">Mengetahui,<br><br><br><strong>( .................................... )</strong></div>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.getElementById('btnPng').addEventListener('click', function () {
  var node = document.getElementById('sertifikat');
  html2canvas(node, { scale: 2, useCORS: true }).then(function (canvas) {
    var a = document.createElement('a');
    a.download = 'sertifikat-<?= preg_replace('/[^a-z0-9]+/i', '-', strtolower($peserta['nama'])) ?>.png';
    a.href = canvas.toDataURL('image/png');
    a.click();
  }).catch(function (err) { alert('Gagal merender: ' + err); });
});
</script>
</body>
</html>
