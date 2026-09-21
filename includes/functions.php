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

// ================================================================
// Helper Kompatibilitas Skema (localhost boleh belum/di-upgrade)
// ================================================================

/** Cek apakah sebuah kolom ada di tabel (cache per-request). */
function tableHasColumn(PDO $pdo, string $table, string $column): bool {
    static $cache = [];
    $key = $table . '.' . $column;
    if (!array_key_exists($key, $cache)) {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
            $stmt->execute([$table, $column]);
            $cache[$key] = ((int)$stmt->fetchColumn()) > 0;
        } catch (Throwable $e) {
            // Fallback: anggap tidak ada agar query tetap aman
            $cache[$key] = false;
        }
    }
    return $cache[$key];
}

function hasMahasiswaPasswordColumn(PDO $pdo): bool {
    return tableHasColumn($pdo, 'mahasiswa', 'password');
}

function hasDosenFotoColumn(PDO $pdo): bool {
    return tableHasColumn($pdo, 'dosen', 'foto');
}

function hasNilaiTable(PDO $pdo): bool {
    static $cache = null;
    if ($cache === null) {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
            $stmt->execute(['nilai']);
            $cache = ((int)$stmt->fetchColumn()) > 0;
        } catch (Throwable $e) {
            $cache = false;
        }
    }
    return $cache;
}

/** URL foto dosen: dukung kolom baru `dosen.foto` + fallback placeholder. */
function fotoDosenUrl(?string $foto): string {
    if ($foto) {
        if (defined('DOSEN_FOTO_DIR') && is_file(DOSEN_FOTO_DIR . $foto)) {
            return DOSEN_FOTO_URL . rawurlencode($foto);
        }
        if (is_file(FOTO_DIR . $foto)) {
            return FOTO_URL . rawurlencode($foto);
        }
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="120" height="120" fill="%23dfe7f5"/><circle cx="60" cy="45" r="22" fill="%231c3d6e" fill-opacity="0.35"/><rect x="20" y="75" width="80" height="45" rx="20" fill="%231c3d6e" fill-opacity="0.35"/></svg>';
    return 'data:image/svg+xml,' . $svg;
}

// ================================================================
// Helper Bulan / Nilai / Sertifikat
// ================================================================

/** Validasi format bulan YYYY-MM. */
function validBulan(?string $bulan): bool {
    return is_string($bulan) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan) === 1;
}

/** Format YYYY-MM -> "September 2026". */
function formatBulanId(?string $bulan): string {
    if (!validBulan($bulan)) return '-';
    static $nama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    [$y, $m] = explode('-', $bulan);
    return $nama[(int)$m] . ' ' . $y;
}

/** Konversi nilai angka -> huruf + predikat (skala umum). */
function nilaiHurufPredikat(float $angka): array {
    if ($angka >= 85) return ['A', 'Sangat Baik'];
    if ($angka >= 75) return ['B', 'Baik'];
    if ($angka >= 65) return ['C', 'Cukup'];
    if ($angka >= 55) return ['D', 'Kurang'];
    return ['E', 'Sangat Kurang'];
}

/** Rekap kehadiran satu peserta (dipakai sertifikat html2canvas & publik). */
function rekapKehadiran(PDO $pdo, int $pesertaId): array {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total, SUM(status_masuk='tepat_waktu') AS tepat, SUM(status_masuk='terlambat') AS terlambat, SUM(status_keluar='sesuai_jadwal') AS sesuai FROM absensi WHERE mahasiswa_id = ?");
    $stmt->execute([$pesertaId]);
    $r = $stmt->fetch() ?: [];
    $total = (int)($r['total'] ?? 0);
    $tepat = (int)($r['tepat'] ?? 0);
    $terlambat = (int)($r['terlambat'] ?? 0);
    $persen = $total > 0 ? round($tepat / $total * 100, 1) : 0;
    return ['total' => $total, 'tepat' => $tepat, 'terlambat' => $terlambat, 'sesuai' => (int)($r['sesuai'] ?? 0), 'persen_tepat' => $persen];
}

/** Ambil nilai satu peserta; null bila tabel/belum ada nilai. */
function ambilNilai(PDO $pdo, int $pesertaId): ?array {
    if (!hasNilaiTable($pdo)) return null;
    $stmt = $pdo->prepare('SELECT * FROM nilai WHERE mahasiswa_id = ? LIMIT 1');
    $stmt->execute([$pesertaId]);
    $row = $stmt->fetch();
    return $row ?: null;
}
