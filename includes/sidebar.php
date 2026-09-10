<?php
$role = currentRole();
$current = basename($_SERVER['SCRIPT_NAME']);

function navItem(string $href, string $icon, string $label, string $current, string $matchFile): string {
    $active = $current === $matchFile ? 'active' : '';
    return '<a href="' . $href . '" class="sidebar-link ' . $active . '"><i class="bi ' . $icon . ' me-2"></i>' . $label . '</a>';
}
?>
<aside class="app-sidebar">
  <nav class="d-flex flex-column p-2">
  <?php if ($role === 'operator'): ?>
    <?= navItem(BASE_URL.'/operator/dashboard.php', 'bi-speedometer2', 'Dashboard', $current, 'dashboard.php') ?>
    <?= navItem(BASE_URL.'/operator/mahasiswa.php', 'bi-people-fill', 'Data Mahasiswa/PKL', $current, 'mahasiswa.php') ?>
    <?= navItem(BASE_URL.'/operator/dosen.php', 'bi-person-badge-fill', 'Data Dosen', $current, 'dosen.php') ?>
    <?= navItem(BASE_URL.'/operator/users.php', 'bi-people', 'Akun Pengguna', $current, 'users.php') ?>
    <?= navItem(BASE_URL.'/operator/absensi_rekap.php', 'bi-calendar-check', 'Rekap Absensi', $current, 'absensi_rekap.php') ?>
    <?= navItem(BASE_URL.'/operator/kegiatan_rekap.php', 'bi-journal-text', 'Rekap Kegiatan', $current, 'kegiatan_rekap.php') ?>
    <?= navItem(BASE_URL.'/operator/sertifikat_rekap.php', 'bi-award', 'Sertifikat', $current, 'sertifikat_rekap.php') ?>
    <?= navItem(BASE_URL.'/operator/profil.php', 'bi-person-lines-fill', 'Profil Saya', $current, 'profil.php') ?>
  <?php elseif ($role === 'dosen'): ?>
    <?= navItem(BASE_URL.'/dosen/dashboard.php', 'bi-speedometer2', 'Dashboard', $current, 'dashboard.php') ?>
    <?= navItem(BASE_URL.'/dosen/mahasiswa.php', 'bi-people-fill', 'Bimbingan Saya', $current, 'mahasiswa.php') ?>
    <?= navItem(BASE_URL.'/dosen/absensi.php', 'bi-calendar-check', 'Absensi Bimbingan', $current, 'absensi.php') ?>
    <?= navItem(BASE_URL.'/dosen/kegiatan.php', 'bi-journal-text', 'Kegiatan Harian', $current, 'kegiatan.php') ?>
    <?= navItem(BASE_URL.'/dosen/profil.php', 'bi-person-lines-fill', 'Profil Saya', $current, 'profil.php') ?>
  <?php elseif ($role === 'mahasiswa'): ?>
    <?= navItem(BASE_URL.'/mahasiswa/dashboard.php', 'bi-speedometer2', 'Dashboard', $current, 'dashboard.php') ?>
    <?= navItem(BASE_URL.'/mahasiswa/absensi.php', 'bi-calendar-check', 'Absensi Saya', $current, 'absensi.php') ?>
    <?= navItem(BASE_URL.'/mahasiswa/kegiatan.php', 'bi-journal-text', 'Kegiatan Harian', $current, 'kegiatan.php') ?>
    <?= navItem(BASE_URL.'/mahasiswa/sertifikat.php', 'bi-award', 'Sertifikat Saya', $current, 'sertifikat.php') ?>
    <?= navItem(BASE_URL.'/mahasiswa/profil.php', 'bi-person-lines-fill', 'Profil Saya', $current, 'profil.php') ?>
  <?php endif; ?>
  </nav>
</aside>
