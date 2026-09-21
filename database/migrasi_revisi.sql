-- ============================================================
-- MIGRASI REVISI (jalankan sekali di database localhost)
-- Database: simak_mahasiswa
-- Catatan: user menyebut schema.sql belum diubah tapi localhost
-- sudah diubah. File ini menyatukan keduanya: jalankan bagian
-- yang BELUM ada di localhost Anda. Setiap blok aman diulang
-- (dicek dulu via information_schema).
-- ============================================================

-- 1) Password portal untuk mahasiswa/siswa (hash password_hash)
SET @col_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mahasiswa' AND COLUMN_NAME = 'password');
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `mahasiswa` ADD COLUMN `password` VARCHAR(255) NULL DEFAULT NULL AFTER `user_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Foto profil untuk dosen
SET @col_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dosen' AND COLUMN_NAME = 'foto');
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `dosen` ADD COLUMN `foto` VARCHAR(255) NULL DEFAULT NULL AFTER `no_hp`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) Kolom jurusan (form sudah memakai, pastikan ada)
SET @col_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mahasiswa' AND COLUMN_NAME = 'jurusan');
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `mahasiswa` ADD COLUMN `jurusan` VARCHAR(100) NULL DEFAULT NULL AFTER `prodi`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4) Nomor sertifikat (dipakai template cetak html2canvas)
SET @col_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sertifikat' AND COLUMN_NAME = 'nomor');
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `sertifikat` ADD COLUMN `nomor` VARCHAR(60) NULL DEFAULT NULL AFTER `judul`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5) Tabel nilai (dipakai rekapan nilai pada sertifikat html2canvas)
CREATE TABLE IF NOT EXISTS `nilai` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` INT(11) NOT NULL,
  `nilai_angka` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `nilai_huruf` VARCHAR(2) NOT NULL DEFAULT '-',
  `predikat` VARCHAR(60) NOT NULL DEFAULT '-',
  `catatan` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_nilai_mhs` (`mahasiswa_id`),
  CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Perbaiki enum prodi bila localhost masih campur aduk
-- (abaikan jika gagal; sesuaikan manual)
-- ALTER TABLE `mahasiswa` MODIFY `prodi` ENUM('Sistem Informasi','Teknik Informatika') NULL DEFAULT NULL;
