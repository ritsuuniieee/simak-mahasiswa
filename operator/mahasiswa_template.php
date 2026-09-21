<?php
// Unduh TEMPLATE impor data peserta (XLSX bila PhpSpreadsheet ada, CSV bila belum).
require_once __DIR__ . '/_spreadsheet_helper.php';
requireRole(['operator']);

$header = headerTemplate();
$contoh = contohTemplate();

if (spreadsheetAvailable()) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('peserta');
    $sheet->fromArray([$header], null, 'A1');
    $sheet->fromArray($contoh, null, 'A2');
    // Petunjuk di baris bawah
    $noteRow = count($contoh) + 3;
    $sheet->setCellValue("A{$noteRow}", 'Petunjuk: tipe = mahasiswa | siswa_pkl. Mahasiswa wajib: nim, nama, prodi (Sistem Informasi / Teknik Informatika). Siswa wajib: nisn, nama, asal_sekolah. pembimbing_nidn opsional (dikosongkan bila belum ada). status: aktif/cuti/lulus/selesai/nonaktif.');
    foreach (range('A', 'K') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
    $sheet->getStyle('A1:K1')->getFont()->setBold(true);
    // Validasi tipe
    $valid = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
    $valid->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
    $valid->setFormula1('"mahasiswa,siswa_pkl"');
    $valid->setShowDropDown(true);
    for ($r = 2; $r <= 500; $r++) { $sheet->getCell("A{$r}")->setDataValidation(clone $valid); }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_peserta.xlsx"');
    header('Cache-Control: max-age=0');
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
    exit;
}

kirimCsv('template_peserta', $header, $contoh);
