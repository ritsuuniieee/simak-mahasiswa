<?php

/** Simpan pesan flash untuk ditampilkan sekali di halaman berikutnya */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Ambil & hapus pesan flash */
function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Escape output HTML dengan singkat */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Format tanggal Indonesia sederhana: 2024-01-05 -> 05-01-2024 */
function formatTanggal(?string $tanggal): string {
    if (!$tanggal) return '-';
    $t = strtotime($tanggal);
    return $t ? date('d-m-Y', $t) : '-';
}

/** Format jam HH:MM:SS -> HH:MM, tampilkan strip jika kosong */
function formatJam(?string $jam): string {
    if (!$jam) return '-';
    return substr($jam, 0, 5);
}

/** Validasi CSRF token sederhana */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function csrfValid(): bool {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// ================================================================
// Helper Tipe Peserta (mahasiswa / siswa_pkl)
// ================================================================

/** Label tampilan untuk tipe peserta */
function labelTipe(string $tipe): string {
    return $tipe === 'siswa_pkl' ? 'Siswa PKL' : 'Mahasiswa';
}

<<<<<<< HEAD
/** Badge Bootstrap untuk tipe peserta */
function badgeTipe(string $tipe): string {
    return $tipe === 'siswa_pkl'
        ? '<span class="badge text-bg-info text-dark">Siswa PKL</span>'
        : '<span class="badge text-bg-secondary">Mahasiswa</span>';
=======
/** Label Material 3 untuk tipe peserta */
function badgeTipe(string $tipe): string {
    return $tipe === 'siswa_pkl'
        ? '<span class="m3-badge m3-badge--tertiary">Siswa PKL</span>'
        : '<span class="m3-badge m3-badge--secondary">Mahasiswa</span>';
}

/** Label Material 3 untuk status keaktifan peserta */
function badgeStatusPeserta(?string $status): string {
    $variant = in_array($status, ['aktif'], true) ? 'success'
             : (in_array($status, ['lulus', 'selesai'], true) ? 'primary' : 'neutral');
    return '<span class="m3-badge m3-badge--' . $variant . '">' . e(ucfirst($status ?? '-')) . '</span>';
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
}

/** Nomor identitas yang relevan (NIM untuk mahasiswa, NISN untuk siswa PKL) */
function nomorIdentitas(array $peserta): string {
    return $peserta['tipe'] === 'siswa_pkl' ? ($peserta['nisn'] ?? '-') : ($peserta['nim'] ?? '-');
}

// ================================================================
// Helper Absensi & Patokan Jam
// ================================================================

/** Keterangan patokan jam untuk ditampilkan ke peserta */
function keteranganPatokanJam(string $tipe): string {
    $jamMasuk = substr($tipe === 'siswa_pkl' ? JAM_MASUK_SISWA_PKL : JAM_MASUK_MAHASISWA, 0, 5);
    $jamPulang = substr(JAM_PULANG_STANDAR, 0, 5);
    return "Batas tepat waktu: masuk sebelum {$jamMasuk}, pulang setelah {$jamPulang}.";
}

/** Hitung status masuk ('tepat_waktu' / 'terlambat') berdasarkan tipe peserta & jam saat ini */
function hitungStatusMasuk(string $tipe, string $jamSekarang): string {
    $patokan = $tipe === 'siswa_pkl' ? JAM_MASUK_SISWA_PKL : JAM_MASUK_MAHASISWA;
    return $jamSekarang <= $patokan ? 'tepat_waktu' : 'terlambat';
}

/** Hitung status keluar ('sesuai_jadwal' / 'pulang_cepat') berdasarkan jam saat ini */
function hitungStatusKeluar(string $tipe, string $jamSekarang): string {
    return $jamSekarang >= JAM_PULANG_STANDAR ? 'sesuai_jadwal' : 'pulang_cepat';
}

<<<<<<< HEAD
/** Badge Bootstrap untuk status_masuk / status_keluar (dipakai bersama) */
function badgeStatusAbsen(?string $status): string {
    switch ($status) {
        case 'tepat_waktu':
            return '<span class="badge text-bg-success">Tepat Waktu</span>';
        case 'terlambat':
            return '<span class="badge text-bg-danger">Terlambat</span>';
        case 'sesuai_jadwal':
            return '<span class="badge text-bg-success">Sesuai Jadwal</span>';
        case 'pulang_cepat':
            return '<span class="badge text-bg-warning text-dark">Pulang Cepat</span>';
        default:
            return '<span class="badge text-bg-light text-dark border">-</span>';
=======
/** Label Material 3 untuk status_masuk / status_keluar (dipakai bersama) */
function badgeStatusAbsen(?string $status): string {
    switch ($status) {
        case 'tepat_waktu':
            return '<span class="m3-badge m3-badge--success">Tepat waktu</span>';
        case 'terlambat':
            return '<span class="m3-badge m3-badge--error">Terlambat</span>';
        case 'sesuai_jadwal':
            return '<span class="m3-badge m3-badge--success">Sesuai jadwal</span>';
        case 'pulang_cepat':
            return '<span class="m3-badge m3-badge--warning">Pulang cepat</span>';
        default:
            return '<span class="m3-badge m3-badge--neutral">Belum ada</span>';
>>>>>>> df20464 (Nambahin Dummy + Ngubah tampilan ke material)
    }
}

/** URL foto profil, fallback ke placeholder SVG inline jika belum ada foto */
function fotoUrl(?string $foto): string {
    if ($foto && is_file(FOTO_DIR . $foto)) {
        return FOTO_URL . rawurlencode($foto);
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="120" height="120" fill="%23e9e0d8"/><circle cx="60" cy="45" r="22" fill="%23613b18" fill-opacity="0.35"/><rect x="20" y="75" width="80" height="45" rx="20" fill="%23613b18" fill-opacity="0.35"/></svg>';
    return 'data:image/svg+xml,' . $svg;
}
