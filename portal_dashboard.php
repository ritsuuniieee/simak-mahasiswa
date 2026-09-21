<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pesertaId = $_SESSION['portal_peserta_id'] ?? null;
if (!$pesertaId) {
    header('Location: ' . BASE_URL . '/portal.php');
    exit;
}

$stmt = $pdo->prepare('SELECT m.*, d.nama AS nama_dosen FROM mahasiswa m LEFT JOIN dosen d ON d.id = m.dosen_id WHERE m.id = ?');
$stmt->execute([$pesertaId]);
$peserta = $stmt->fetch();
if (!$peserta) {
    unset($_SESSION['portal_peserta_id']);
    header('Location: ' . BASE_URL . '/portal.php');
    exit;
}

$tipe = $peserta['tipe'];
$today = date('Y-m-d');
$flashLocal = '';
$flashType = 'success';

// ==== Aksi: Absen Masuk / Keluar ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && in_array($_POST['aksi'], ['masuk','keluar'], true)) {
    if (!csrfValid()) {
        $flashLocal = 'Sesi form kadaluarsa, coba lagi.'; $flashType = 'danger';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM absensi WHERE mahasiswa_id = ? AND tanggal = ?');
        $stmt->execute([$pesertaId, $today]);
        $absenHariIni = $stmt->fetch();
        $now = date('H:i:s');

        if ($_POST['aksi'] === 'masuk') {
            if ($absenHariIni) {
                $flashLocal = 'Anda sudah tercatat absen hari ini.'; $flashType = 'danger';
            } else {
                $status = hitungStatusMasuk($tipe, $now);
                $stmt = $pdo->prepare('INSERT INTO absensi (mahasiswa_id, tanggal, jam_masuk, status_masuk) VALUES (?,?,?,?)');
                $stmt->execute([$pesertaId, $today, $now, $status]);
                $flashLocal = 'Absen masuk tercatat pukul ' . substr($now,0,5) . ' (' . ($status === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat') . ').';
            }
        } else {
            if (!$absenHariIni || !$absenHariIni['jam_masuk']) {
                $flashLocal = 'Anda belum absen masuk hari ini.'; $flashType = 'danger';
            } elseif ($absenHariIni['jam_keluar']) {
                $flashLocal = 'Anda sudah tercatat absen keluar hari ini.'; $flashType = 'danger';
            } else {
                $statusKeluar = hitungStatusKeluar($tipe, $now);
                $stmt = $pdo->prepare('UPDATE absensi SET jam_keluar = ?, status_keluar = ? WHERE id = ?');
                $stmt->execute([$now, $statusKeluar, $absenHariIni['id']]);
                $flashLocal = 'Absen keluar tercatat pukul ' . substr($now,0,5) . '.';
            }
        }
    }
}

// ==== Aksi: Tambah Kegiatan Harian (tanggal otomatis hari ini) ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'kegiatan') {
    if (!csrfValid()) {
        $flashLocal = 'Sesi form kadaluarsa, coba lagi.'; $flashType = 'danger';
    } else {
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        if ($judul === '' || $deskripsi === '') {
            $flashLocal = 'Judul dan deskripsi kegiatan wajib diisi.'; $flashType = 'danger';
        } else {
            $stmt = $pdo->prepare('INSERT INTO kegiatan_harian (mahasiswa_id, tanggal, judul, deskripsi) VALUES (?,?,?,?)');
            $stmt->execute([$pesertaId, $today, $judul, $deskripsi]);
            $flashLocal = 'Kegiatan hari ini berhasil dicatat.';
        }
    }
}

// ==== Aksi: Atur / Ganti password portal (hash) ====
$hasPwCol = hasMahasiswaPasswordColumn($pdo);
if ($hasPwCol && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'password') {
    if (!csrfValid()) {
        $flashLocal = 'Sesi form kadaluarsa, coba lagi.'; $flashType = 'danger';
    } else {
        $pwLama = $_POST['password_lama'] ?? '';
        $pwBaru = $_POST['password_baru'] ?? '';
        $pwKonf = $_POST['password_konfirmasi'] ?? '';
        $hashLama = $peserta['password'] ?? null;
        if (strlen($pwBaru) < 6) {
            $flashLocal = 'Password baru minimal 6 karakter.'; $flashType = 'danger';
        } elseif ($pwBaru !== $pwKonf) {
            $flashLocal = 'Konfirmasi password baru tidak cocok.'; $flashType = 'danger';
        } elseif (!empty($hashLama) && !password_verify($pwLama, $hashLama)) {
            $flashLocal = 'Password lama salah.'; $flashType = 'danger';
        } else {
            $stmt = $pdo->prepare('UPDATE mahasiswa SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($pwBaru, PASSWORD_DEFAULT), $pesertaId]);
            unset($_SESSION['portal_perlu_password']);
            $stmt = $pdo->prepare('SELECT m.*, d.nama AS nama_dosen FROM mahasiswa m LEFT JOIN dosen d ON d.id = m.dosen_id WHERE m.id = ?');
            $stmt->execute([$pesertaId]);
            $peserta = $stmt->fetch();
            $flashLocal = 'Password portal berhasil disimpan.';
        }
    }
}

// Ambil ulang data absensi hari ini (setelah kemungkinan update)
$stmt = $pdo->prepare('SELECT * FROM absensi WHERE mahasiswa_id = ? AND tanggal = ?');
$stmt->execute([$pesertaId, $today]);
$absenHariIni = $stmt->fetch();

// Rekap absensi
$rekap = $pdo->prepare("
    SELECT
      COUNT(*) AS total_hadir,
      SUM(status_masuk = 'tepat_waktu') AS total_tepat_waktu,
      SUM(status_masuk = 'terlambat') AS total_terlambat
    FROM absensi WHERE mahasiswa_id = ?
");
$rekap->execute([$pesertaId]);
$rekap = $rekap->fetch();

$stmt = $pdo->prepare('SELECT * FROM absensi WHERE mahasiswa_id = ? ORDER BY tanggal DESC LIMIT 15');
$stmt->execute([$pesertaId]);
$riwayatAbsen = $stmt->fetchAll();

// Filter & pengelompokan bulanan untuk riwayat
$bulanFilter = $_GET['bulan'] ?? '';
if (!validBulan($bulanFilter)) $bulanFilter = '';
if ($bulanFilter !== '') {
    $stmt = $pdo->prepare("SELECT * FROM absensi WHERE mahasiswa_id = ? AND DATE_FORMAT(tanggal,'%Y-%m') = ? ORDER BY tanggal DESC");
    $stmt->execute([$pesertaId, $bulanFilter]);
    $riwayatAbsen = $stmt->fetchAll();
}
$stmt = $pdo->prepare("SELECT DATE_FORMAT(tanggal,'%Y-%m') AS bulan, COUNT(*) AS total, SUM(status_masuk='tepat_waktu') AS tepat, SUM(status_masuk='terlambat') AS terlambat FROM absensi WHERE mahasiswa_id = ? GROUP BY DATE_FORMAT(tanggal,'%Y-%m') ORDER BY bulan DESC");
$stmt->execute([$pesertaId]);
$rekapBulanan = $stmt->fetchAll();

$nilaiSaya = ambilNilai($pdo, $pesertaId);

$stmt = $pdo->prepare('SELECT * FROM kegiatan_harian WHERE mahasiswa_id = ? ORDER BY tanggal DESC, created_at DESC LIMIT 10');
$stmt->execute([$pesertaId]);
$riwayatKegiatan = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM sertifikat WHERE mahasiswa_id = ? ORDER BY tanggal_upload DESC');
$stmt->execute([$pesertaId]);
$sertifikatList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#fbebe2">
<title>Portal saya | SIMAK Mahasiswa &amp; PKL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,400;8..144,500;8..144,600;8..144,700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/material3.css" rel="stylesheet">
</head>
<body>

<header class="m3-appbar">
  <a class="m3-appbar__brand" href="<?= BASE_URL ?>/portal_dashboard.php">
    <img src="<?= BASE_URL ?>/assets/images/logo-stikom.png" alt="" class="m3-appbar__logo">
    <span class="m3-appbar__title">Portal saya</span>
  </a>
  <span class="m3-appbar__spacer"></span>
  <a href="<?= BASE_URL ?>/portal_logout.php" class="m3-btn m3-btn--tonal m3-btn--sm">
    <span class="m3-icon m3-icon--sm">logout</span>Keluar
  </a>
</header>

<main class="m3-main" style="max-width:1000px;margin:0 auto;border-radius:0">

  <?php if ($flashLocal): ?>
    <div class="m3-banner m3-banner--<?= $flashType === 'danger' ? 'error' : 'success' ?>">
      <span class="m3-icon"><?= $flashType === 'danger' ? 'error' : 'check_circle' ?></span>
      <span class="m3-grow"><?= e($flashLocal) ?></span>
      <button type="button" class="m3-banner__close" data-m3-banner-close aria-label="Tutup pesan">
        <span class="m3-icon m3-icon--sm">close</span>
      </button>
    </div>
  <?php endif; ?>

  <!-- Identitas peserta -->
  <section class="m3-card m3-card--filled m3-mb-3">
    <div class="m3-card__body">
      <div class="m3-row m3-row--wrap" style="gap:20px">
        <?php if ($peserta['foto']): ?>
          <img src="<?= FOTO_URL . rawurlencode($peserta['foto']) ?>" alt="" class="m3-avatar m3-avatar--lg">
        <?php else: ?>
          <div class="m3-avatar m3-avatar--lg"><span class="m3-icon m3-icon--lg">person</span></div>
        <?php endif; ?>
        <div class="m3-grow">
          <div class="m3-row m3-gap-sm m3-mb-1" style="flex-wrap:wrap">
            <h1 class="m3-headline-small m3-mb-0"><?= e($peserta['nama']) ?></h1>
            <?= badgeTipe($peserta['tipe']) ?>
          </div>
          <p class="m3-body-medium m3-muted m3-mb-0">
            <?= e(nomorIdentitas($peserta)) ?> &middot; <?= e($peserta['prodi']) ?>
            <?php if ($peserta['tipe'] === 'siswa_pkl' && $peserta['asal_sekolah']): ?>
              &middot; <?= e($peserta['asal_sekolah']) ?>
            <?php endif; ?>
          </p>
          <p class="m3-body-medium m3-muted m3-mb-0">Pembimbing: <?= e($peserta['nama_dosen'] ?? 'Belum ditentukan') ?></p>
        </div>
      </div>
    </div>
  </section>

  <!-- Rekap kehadiran -->
  <div class="m3-cards-grid m3-mb-4">
    <div class="m3-stat m3-stat--primary">
      <span class="m3-stat__label">Total hadir</span>
      <span class="m3-stat__value"><?= (int)$rekap['total_hadir'] ?></span>
      <span class="m3-stat__meta">hari tercatat</span>
      <span class="m3-icon m3-stat__icon">event_available</span>
    </div>
    <div class="m3-stat m3-stat--success">
      <span class="m3-stat__label">Tepat waktu</span>
      <span class="m3-stat__value"><?= (int)$rekap['total_tepat_waktu'] ?></span>
      <span class="m3-icon m3-stat__icon">schedule</span>
    </div>
    <div class="m3-stat m3-stat--error">
      <span class="m3-stat__label">Terlambat</span>
      <span class="m3-stat__value"><?= (int)$rekap['total_terlambat'] ?></span>
      <span class="m3-icon m3-stat__icon">running_late</span>
    </div>
    <div class="m3-stat m3-stat--tertiary">
      <span class="m3-stat__label">Sertifikat</span>
      <span class="m3-stat__value"><?= count($sertifikatList) ?></span>
      <span class="m3-icon m3-stat__icon">workspace_premium</span>
    </div>
  </div>

  <?php if (!empty($_SESSION['portal_perlu_password']) || ($hasPwCol && empty($peserta['password']))): ?>
    <div class="m3-banner m3-banner--warning m3-mb-3">
      <span class="m3-icon">lock</span>
      <span class="m3-grow">Akun portal Anda belum punya password. Buat password sekarang agar NIM/NISN Anda tidak disalahgunakan.</span>
    </div>
  <?php endif; ?>

  <!-- Absensi hari ini -->
  <section class="m3-card m3-card--elevated m3-mb-3">
    <div class="m3-card__header">Absensi hari ini &mdash; <?= formatTanggal($today) ?></div>
    <div class="m3-card__body">
      <p class="m3-body-medium m3-muted"><?= e(keteranganPatokanJam($tipe)) ?></p>

      <div class="m3-grid m3-mb-3">
        <div class="m3-col-4">
          <div class="m3-body-small m3-muted">Jam masuk</div>
          <div class="m3-headline-small"><?= formatJam($absenHariIni['jam_masuk'] ?? null) ?></div>
          <?= badgeStatusAbsen($absenHariIni['status_masuk'] ?? null) ?>
        </div>
        <div class="m3-col-4">
          <div class="m3-body-small m3-muted">Jam keluar</div>
          <div class="m3-headline-small"><?= formatJam($absenHariIni['jam_keluar'] ?? null) ?></div>
          <?= badgeStatusAbsen($absenHariIni['status_keluar'] ?? null) ?>
        </div>
        <div class="m3-col-4">
          <div class="m3-row m3-gap-sm" style="height:100%;align-items:flex-end;flex-wrap:wrap">
            <form method="post">
              <?= csrfField() ?>
              <input type="hidden" name="aksi" value="masuk">
              <button type="submit" class="m3-btn m3-btn--filled" <?= $absenHariIni ? 'disabled' : '' ?>>
                <span class="m3-icon m3-icon--sm">login</span>Absen masuk
              </button>
            </form>
            <form method="post">
              <?= csrfField() ?>
              <input type="hidden" name="aksi" value="keluar">
              <button type="submit" class="m3-btn m3-btn--outlined"
                <?= (!$absenHariIni || !$absenHariIni['jam_masuk'] || $absenHariIni['jam_keluar']) ? 'disabled' : '' ?>>
                <span class="m3-icon m3-icon--sm">logout</span>Absen keluar
              </button>
            </form>
          </div>
        </div>
      </div>

      <h2 class="m3-title-medium m3-mt-3 m3-mb-2">Riwayat absensi terakhir</h2>
      <form method="get" class="m3-toolbar m3-mb-2">
        <input type="month" name="bulan" class="m3-input" style="width:auto" value="<?= e($bulanFilter) ?>">
        <button class="m3-btn m3-btn--tonal m3-btn--sm"><span class="m3-icon m3-icon--sm">filter_alt</span>Filter bulan</button>
        <?php if ($bulanFilter): ?><a href="portal_dashboard.php" class="m3-btn m3-btn--text m3-btn--sm">Atur ulang</a><?php endif; ?>
      </form>
      <?php if ($rekapBulanan): ?>
      <div class="m3-table-scroll m3-mb-2">
        <table class="m3-table">
          <thead><tr><th>Bulan</th><th>Hadir</th><th>Tepat waktu</th><th>Terlambat</th></tr></thead>
          <tbody>
            <?php foreach ($rekapBulanan as $rb): ?>
            <tr>
              <td><a href="portal_dashboard.php?bulan=<?= e($rb['bulan']) ?>"><?= e(formatBulanId($rb['bulan'])) ?></a></td>
              <td><?= (int)$rb['total'] ?></td><td><?= (int)$rb['tepat'] ?></td><td><?= (int)$rb['terlambat'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
      <div class="m3-table-scroll">
        <table class="m3-table">
          <thead>
            <tr><th>Tanggal</th><th>Masuk</th><th>Status</th><th>Keluar</th><th>Status</th></tr>
          </thead>
          <tbody>
          <?php if (!$riwayatAbsen): ?>
            <tr><td colspan="5" class="m3-table__empty">Belum ada riwayat absensi.</td></tr>
          <?php endif; ?>
          <?php foreach ($riwayatAbsen as $r): ?>
            <tr>
              <td><?= formatTanggal($r['tanggal']) ?></td>
              <td><?= formatJam($r['jam_masuk']) ?></td>
              <td><?= badgeStatusAbsen($r['status_masuk']) ?></td>
              <td><?= formatJam($r['jam_keluar']) ?></td>
              <td><?= badgeStatusAbsen($r['status_keluar']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Kegiatan harian -->
  <section class="m3-card m3-card--elevated m3-mb-3">
    <div class="m3-card__header">Kegiatan hari ini &mdash; <?= formatTanggal($today) ?></div>
    <div class="m3-card__body">
      <form method="post" class="m3-mb-3">
        <?= csrfField() ?>
        <input type="hidden" name="aksi" value="kegiatan">
        <div class="m3-field m3-mb-2">
          <label class="m3-field__label" for="judul">Judul kegiatan</label>
          <input id="judul" type="text" name="judul" class="m3-input" required
                 placeholder="Contoh: Menyusun laporan mingguan">
        </div>
        <div class="m3-field m3-mb-2">
          <label class="m3-field__label" for="deskripsi">Deskripsi</label>
          <textarea id="deskripsi" name="deskripsi" class="m3-textarea" rows="3" required
                    placeholder="Ceritakan apa yang dikerjakan hari ini"></textarea>
        </div>
        <button type="submit" class="m3-btn m3-btn--filled">
          <span class="m3-icon m3-icon--sm">add</span>Simpan kegiatan
        </button>
      </form>

      <h2 class="m3-title-medium m3-mt-3 m3-mb-1">Kegiatan terakhir</h2>
      <?php if (!$riwayatKegiatan): ?>
        <p class="m3-body-medium m3-muted">Belum ada kegiatan tercatat. Mulai dari form di atas.</p>
      <?php endif; ?>
      <?php foreach ($riwayatKegiatan as $k): ?>
        <div class="m3-list-row">
          <div class="m3-row--between">
            <strong class="m3-title-small"><?= e($k['judul']) ?></strong>
            <span class="m3-body-small m3-muted"><?= formatTanggal($k['tanggal']) ?></span>
          </div>
          <div class="m3-body-medium m3-muted"><?= nl2br(e($k['deskripsi'])) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Sertifikat -->
  <section class="m3-card m3-card--elevated">
    <div class="m3-card__header">Sertifikat saya<?= $nilaiSaya ? ' &middot; Nilai: ' . e($nilaiSaya['nilai_angka']) . ' (' . e($nilaiSaya['nilai_huruf']) . ' - ' . e($nilaiSaya['predikat']) . ')' : '' ?></div>
    <div class="m3-card__body">
      <?php if (!$sertifikatList): ?>
        <p class="m3-body-medium m3-muted m3-mb-0">
          Belum ada sertifikat. Sertifikat diunggah oleh operator &mdash; hubungi operator bila sertifikat Anda belum muncul.
        </p>
      <?php else: ?>
        <div class="m3-cards-grid m3-cards-grid--wide">
        <?php foreach ($sertifikatList as $s): ?>
          <div class="m3-card m3-card--outlined">
            <div class="m3-card__body m3-card__body--tight">
              <div class="m3-row" style="gap:14px">
                <span class="m3-icon m3-icon--lg" style="color:var(--m3-primary)">workspace_premium</span>
                <div class="m3-grow">
                  <div class="m3-title-small"><?= e($s['judul']) ?></div>
                  <div class="m3-body-small m3-muted"><?= formatTanggal($s['tanggal_upload']) ?></div>
                </div>
                <a href="<?= UPLOAD_URL . rawurlencode($s['file_path']) ?>" target="_blank"
                   class="m3-icon-btn m3-icon-btn--primary" title="Unduh sertifikat" aria-label="Unduh sertifikat">
                  <span class="m3-icon">download</span>
                </a>
                <a href="<?= BASE_URL ?>/cetak_sertifikat.php?peserta_id=<?= (int)$pesertaId ?>&s_id=<?= (int)$s['id'] ?>" target="_blank"
                   class="m3-icon-btn" title="Pratinjau & unduh PNG (html2canvas)" aria-label="Cetak sertifikat">
                  <span class="m3-icon">print</span>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($hasPwCol): ?>
  <!-- Keamanan portal -->
  <section class="m3-card m3-card--elevated m3-mt-3">
    <div class="m3-card__header">Password portal</div>
    <div class="m3-card__body">
      <form method="post" class="m3-grid">
        <?= csrfField() ?>
        <input type="hidden" name="aksi" value="password">
        <?php if (!empty($peserta['password'])): ?>
        <div class="m3-col-4">
          <label class="m3-field__label" for="password_lama">Password lama</label>
          <input id="password_lama" type="password" name="password_lama" class="m3-input" autocomplete="current-password">
        </div>
        <?php endif; ?>
        <div class="m3-col-4">
          <label class="m3-field__label" for="password_baru">Password baru</label>
          <input id="password_baru" type="password" name="password_baru" class="m3-input" required minlength="6" autocomplete="new-password">
        </div>
        <div class="m3-col-4">
          <label class="m3-field__label" for="password_konfirmasi">Ulangi password baru</label>
          <input id="password_konfirmasi" type="password" name="password_konfirmasi" class="m3-input" required autocomplete="new-password">
        </div>
        <div class="m3-col-12">
          <button type="submit" class="m3-btn m3-btn--filled m3-btn--sm"><span class="m3-icon m3-icon--sm">lock</span>Simpan password</button>
        </div>
      </form>
    </div>
  </section>
  <?php endif; ?>

</main>

<footer class="m3-footer">&copy; <?= date('Y') ?> STIKOM 22 Januari</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/material3.js"></script>
<script src="<?= BASE_URL ?>/assets/js/animasi.js"></script>
</body>
</html>
