<?php
// Ekspor data peserta mengikuti filter ?tipe=&q= (XLSX bila ada lib, CSV bila belum).
require_once __DIR__ . '/_spreadsheet_helper.php';
requireRole(['operator']);

$tipeFilter = $_GET['tipe'] ?? '';
$search = trim($_GET['q'] ?? '');
$sql = "SELECT m.*, d.nidn_nidk AS pembimbing_nidn FROM mahasiswa m LEFT JOIN dosen d ON d.id = m.dosen_id WHERE 1=1";
$params = [];
if ($search !== '') {
    $sql .= " AND (m.nim LIKE ? OR m.nisn LIKE ? OR m.nama LIKE ? OR m.prodi LIKE ? OR m.asal_sekolah LIKE ?)";
    $like = "%$search%";
    array_push($params, $like, $like, $like, $like, $like);
}
if (in_array($tipeFilter, ['mahasiswa','siswa_pkl'], true)) { $sql .= " AND m.tipe = ?"; $params[] = $tipeFilter; }
$sql .= " ORDER BY m.nama ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

$header = headerTemplate();
$rows = [];
foreach ($list as $m) {
    $rows[] = [$m['tipe'], $m['nim'], $m['nisn'], $m['nama'], $m['prodi'], $m['jurusan'] ?? '', $m['asal_sekolah'], $m['pembimbing_nidn'] ?? '', $m['no_hp'], $m['alamat'], $m['status']];
}

if (spreadsheetAvailable()) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('peserta');
    $sheet->fromArray([$header], null, 'A1');
    if ($rows) $sheet->fromArray($rows, null, 'A2');
    foreach (range('A', 'K') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
    $sheet->getStyle('A1:K1')->getFont()->setBold(true);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="ekspor_peserta_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
    exit;
}

kirimCsv('ekspor_peserta_' . date('Ymd_His'), $header, $rows);
