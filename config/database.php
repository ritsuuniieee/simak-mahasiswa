<?php
/**
 * Konfigurasi koneksi database.
 * Sesuaikan DB_HOST, DB_NAME, DB_USER, DB_PASS dengan environment Anda.
 */

// Zona waktu aplikasi. Diset di kode (bukan hanya php.ini) supaya jam
// absensi & tanggal tercatat benar meski setting php.ini server berbeda.
// Pilihan lain: 'Asia/Makassar' (WITA), 'Asia/Jayapura' (WIT)
date_default_timezone_set('Asia/Makassar');

define('DB_HOST', 'localhost');
define('DB_NAME', 'simak_mahasiswa');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL aplikasi (tanpa trailing slash). Sesuaikan jika di dalam subfolder.
define('BASE_URL', '/simak-mahasiswa');

// Folder tempat menyimpan file sertifikat yang diunggah
define('UPLOAD_DIR', __DIR__ . '/../uploads/sertifikat/');
define('UPLOAD_URL', BASE_URL . '/uploads/sertifikat/');

// Folder tempat menyimpan foto mahasiswa/siswa PKL
define('FOTO_DIR', __DIR__ . '/../uploads/foto/');
define('FOTO_URL', BASE_URL . '/uploads/foto/');

// Folder tempat menyimpan foto profil dosen
define('DOSEN_FOTO_DIR', __DIR__ . '/../uploads/foto_dosen/');
define('DOSEN_FOTO_URL', BASE_URL . '/uploads/foto_dosen/');

// ------------------------------------------------------------
// Patokan jam kehadiran (dipakai untuk menandai "Tepat Waktu" / "Terlambat")
// Format 24 jam HH:MM:SS
// ------------------------------------------------------------
define('JAM_MASUK_MAHASISWA', '08:00:00');
define('JAM_MASUK_SISWA_PKL', '07:10:00');
define('JAM_PULANG_STANDAR', '16:00:00'); // 16:00 = jam 4 sore, berlaku untuk kedua jenis peserta

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}
