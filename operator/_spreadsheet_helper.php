<?php
/**
 * Template + ekspor data peserta.
 * Memakai PhpSpreadsheet bila tersedia (vendor/autoload.php),
 * fallback ke CSV bila library belum di-install (`composer install`).
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

function spreadsheetAvailable(): bool {
    if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) return true;
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
        return class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet');
    }
    return false;
}

function kirimCsv(string $namaFile, array $header, array $rows): void {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $namaFile . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM agar Excel baca UTF-8
    fputcsv($out, $header, ';');
    foreach ($rows as $r) fputcsv($out, $r, ';');
    fclose($out);
    exit;
}

function headerTemplate(): array {
    return ['tipe', 'nim', 'nisn', 'nama', 'prodi', 'jurusan', 'asal_sekolah', 'pembimbing_nidn', 'no_hp', 'alamat', 'status'];
}

function contohTemplate(): array {
    return [
        ['mahasiswa', '2110511001', '', 'Andi Saputra', 'Sistem Informasi', '', '', '', '081200000001', 'Jl. Sudirman No. 10', 'aktif'],
        ['siswa_pkl', '', '0051234567', 'Siti Rahma', '', 'TKJ', 'SMK Negeri 1 Kendari', '0012058501', '081200000002', 'Jl. Ahmad Yani No. 29', 'aktif'],
    ];
}
