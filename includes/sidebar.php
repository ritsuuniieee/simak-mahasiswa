<?php
$role = currentRole();
$current = basename($_SERVER['SCRIPT_NAME']);

/** Satu item navigation drawer Material 3 (pill, dengan state aktif) */
function navItem(string $href, string $icon, string $label, string $current, string $matchFile): string {
    $active = $current === $matchFile ? ' is-active' : '';
    return '<a href="' . $href . '" class="m3-nav-item' . $active . '">'
         . '<span class="m3-icon">' . $icon . '</span>' . $label . '</a>';
}
?>
<nav class="m3-drawer" data-m3-drawer aria-label="Menu utama">
  <?php if ($role === 'operator'): ?>
    <div class="m3-drawer__section">Ringkasan</div>
    <?= navItem(BASE_URL.'/operator/dashboard.php', 'space_dashboard', 'Dashboard', $current, 'dashboard.php') ?>

    <div class="m3-drawer__section">Data induk</div>
    <?= navItem(BASE_URL.'/operator/mahasiswa.php', 'groups', 'Mahasiswa &amp; PKL', $current, 'mahasiswa.php') ?>
    <?= navItem(BASE_URL.'/operator/dosen.php', 'badge', 'Dosen', $current, 'dosen.php') ?>
    <?= navItem(BASE_URL.'/operator/users.php', 'manage_accounts', 'Akun pengguna', $current, 'users.php') ?>

    <div class="m3-drawer__section">Rekap</div>
    <?= navItem(BASE_URL.'/operator/absensi_rekap.php', 'event_available', 'Absensi', $current, 'absensi_rekap.php') ?>
    <?= navItem(BASE_URL.'/operator/kegiatan_rekap.php', 'edit_note', 'Kegiatan harian', $current, 'kegiatan_rekap.php') ?>
    <?= navItem(BASE_URL.'/operator/nilai.php', 'grade', 'Nilai', $current, 'nilai.php') ?>
    <?= navItem(BASE_URL.'/operator/sertifikat_rekap.php', 'workspace_premium', 'Sertifikat', $current, 'sertifikat_rekap.php') ?>

    <div class="m3-drawer__section">Akun</div>
    <?= navItem(BASE_URL.'/operator/profil.php', 'account_circle', 'Profil saya', $current, 'profil.php') ?>

  <?php elseif ($role === 'dosen'): ?>
    <div class="m3-drawer__section">Ringkasan</div>
    <?= navItem(BASE_URL.'/dosen/dashboard.php', 'space_dashboard', 'Dashboard', $current, 'dashboard.php') ?>

    <div class="m3-drawer__section">Bimbingan</div>
    <?= navItem(BASE_URL.'/dosen/mahasiswa.php', 'groups', 'Peserta bimbingan', $current, 'mahasiswa.php') ?>
    <?= navItem(BASE_URL.'/dosen/absensi.php', 'event_available', 'Absensi', $current, 'absensi.php') ?>
    <?= navItem(BASE_URL.'/dosen/kegiatan.php', 'edit_note', 'Kegiatan harian', $current, 'kegiatan.php') ?>

    <div class="m3-drawer__section">Akun</div>
    <?= navItem(BASE_URL.'/dosen/profil.php', 'account_circle', 'Profil saya', $current, 'profil.php') ?>
  <?php endif; ?>
</nav>
