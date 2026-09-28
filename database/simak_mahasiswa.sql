-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 29, 2026 at 12:44 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simak_mahasiswa`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `status_masuk` enum('tepat_waktu','terlambat') DEFAULT NULL,
  `status_keluar` enum('sesuai_jadwal','pulang_cepat') DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `mahasiswa_id`, `tanggal`, `jam_masuk`, `jam_keluar`, `status_masuk`, `status_keluar`, `keterangan`, `created_at`) VALUES
(8, 25, '2026-09-21', '15:11:29', '15:11:31', 'terlambat', 'pulang_cepat', NULL, '2026-09-21 07:11:29'),
(9, 25, '2026-09-26', '07:52:22', '07:52:26', 'tepat_waktu', 'pulang_cepat', NULL, '2026-09-25 23:52:22'),
(10, 25, '2026-09-27', '20:51:06', '20:51:07', 'terlambat', 'sesuai_jadwal', NULL, '2026-09-27 12:51:06'),
(11, 25, '2026-09-29', '06:41:21', NULL, 'tepat_waktu', NULL, NULL, '2026-09-28 22:41:21');

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nidn_nidk` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `user_id`, `nidn_nidk`, `nama`, `no_hp`, `foto`, `created_at`) VALUES
(1, 3, '0012058501', 'fulan', '081234567890', NULL, '2026-09-06 16:17:16'),
(2, 6, '12345432', 'fulanah', '21312434', NULL, '2026-09-11 01:07:19'),
(3, 8, '0023069002', 'Siti Nurhaliza, S.Kom., M.T', '081234500002', NULL, '2026-09-15 10:54:32'),
(4, 9, '0034077403', 'Ahmad Fauzi, S.T., M.Eng', '081234500003', 'dosen_1790514892_10988fb8.png', '2026-09-15 10:54:32'),
(5, 10, '0045088704', 'Dr. Dewi Lestari, M.Cs', '081234500004', NULL, '2026-09-15 10:54:32'),
(6, 11, '0056099105', 'Hendra Gunawan, S.Kom., M.Kom', '081234500005', NULL, '2026-09-15 10:54:32'),
(7, 12, '0067100806', 'Ratna Sari Dewi, S.T., M.T', '081234500006', NULL, '2026-09-15 10:54:32'),
(8, 13, '0078111907', 'Yusuf Maulana, S.Kom., M.Cs', '081234500007', NULL, '2026-09-15 10:54:32'),
(9, 14, '0089123008', 'Indah Permatasari, S.Pd., M.Pd', '081234500008', NULL, '2026-09-15 10:54:32'),
(10, 15, '0015079001', 'Dr. Andi Wijaya, S.Kom., M.T', '081255660011', '', '2026-09-20 08:00:00'),
(12, 17, '0037097803', 'Citra Dewi, S.Kom., M.Kom', '081255660013', '', '2026-09-20 08:02:00'),
(13, 18, '0048108104', 'Dedi Kurniawan, S.T., M.T', '081255660014', '', '2026-09-20 08:03:00'),
(14, 19, '0059119405', 'Eka Fitriani, S.Kom., M.Cs', '081255660015', '', '2026-09-20 08:04:00'),
(15, 20, '0060120706', 'Fajar Nugroho, S.T., M.Eng', '081255660016', '', '2026-09-20 08:05:00'),
(16, 21, '0071132007', 'Gita Maharani, S.Pd., M.Pd', '081255660017', '', '2026-09-20 08:06:00'),
(17, 22, '0082143308', 'Hadi Prasetyo, S.Kom., M.T', '081255660018', '', '2026-09-20 08:07:00'),
(18, 23, '0093154609', 'Ira Kusuma, S.T., M.T', '081255660019', '', '2026-09-20 08:08:00'),
(19, 24, '0104165910', 'Joko Susilo, S.Kom., M.Kom', '081255660020', '', '2026-09-20 08:09:00'),
(20, 25, '0115176211', 'Kartika Sari, S.Pd., M.Hum', '081255660021', '', '2026-09-20 08:10:00'),
(21, 26, '0126187512', 'Lukman Hakim, S.T., M.Eng', '081255660022', '', '2026-09-20 08:11:00'),
(22, 27, '0137198813', 'Maya Anggraini, S.Kom., M.Cs', '081255660023', '', '2026-09-20 08:12:00'),
(23, 28, '0148209114', 'Naufal Rizki, S.T., M.T', '081255660024', '', '2026-09-20 08:13:00'),
(24, 29, '0159210415', 'Olivia Putri, S.Pd., M.Pd', '081255660025', '', '2026-09-20 08:14:00'),
(25, 30, '0160221716', 'Rio Saputra, S.Kom., M.T', '081255660026', '', '2026-09-20 08:15:00'),
(26, 31, '0171232017', 'Sari Indah, S.T., M.Eng', '081255660027', '', '2026-09-20 08:16:00');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan_harian`
--

CREATE TABLE `kegiatan_harian` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kegiatan_harian`
--

INSERT INTO `kegiatan_harian` (`id`, `mahasiswa_id`, `tanggal`, `judul`, `deskripsi`, `created_at`) VALUES
(3, 25, '2026-09-21', 'coding', 'adfaf', '2026-09-21 10:18:27');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `tipe` enum('mahasiswa','siswa_pkl') NOT NULL DEFAULT 'mahasiswa',
  `nim` varchar(30) DEFAULT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `prodi` enum('Sistem Informasi','Teknik Komputer') DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `asal_sekolah` varchar(150) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `dosen_id` int(11) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` enum('aktif','selesai') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `user_id`, `password`, `tipe`, `nim`, `nisn`, `nama`, `prodi`, `jurusan`, `asal_sekolah`, `foto`, `dosen_id`, `no_hp`, `alamat`, `status`, `created_at`) VALUES
(25, NULL, '$2y$10$PMsVrPZFwDowB3rTYuRRCOEwOKBY/W0xCUj7UVNX77YZXG3XkFEAW', 'mahasiswa', '123123', NULL, 'Mitaka', 'Teknik Komputer', NULL, NULL, 'foto_1790514856_c47a66ca.png', 7, '081241213', '', 'aktif', '2026-09-21 00:14:32'),
(26, NULL, NULL, 'siswa_pkl', NULL, '3411344', 'Reze', 'Sistem Informasi', NULL, 'SMK 1', NULL, 6, '4124124', '', 'aktif', '2026-09-21 00:28:49'),
(27, NULL, NULL, 'mahasiswa', '2110511000', NULL, 'Budi Kurniawan', 'Sistem Informasi', NULL, NULL, NULL, NULL, '081214538996', 'Jl. Diponegoro No. 27', 'selesai', '2026-09-27 12:12:24'),
(28, NULL, NULL, 'mahasiswa', '2110511003', NULL, 'Taufik Wijaya', 'Sistem Informasi', NULL, NULL, NULL, NULL, '081266843376', 'Jl. Ahmad Yani No. 29', 'aktif', '2026-09-27 12:12:24'),
(29, NULL, NULL, 'mahasiswa', '2110511006', NULL, 'Ratna Lestari', 'Sistem Informasi', NULL, NULL, NULL, NULL, '081288788006', 'Jl. Saranani No. 6', 'aktif', '2026-09-27 12:12:24'),
(30, NULL, NULL, 'mahasiswa', '2110511007', NULL, 'Taufik Pratama', 'Sistem Informasi', NULL, NULL, NULL, NULL, '081283225115', 'Jl. Malik Raya No. 52', 'aktif', '2026-09-27 12:12:24'),
(31, NULL, NULL, 'mahasiswa', '2110511008', NULL, 'Rudi Lestari', 'Sistem Informasi', NULL, NULL, NULL, NULL, '081237028896', 'Jl. Bunga Tanjung No. 116', 'aktif', '2026-09-27 12:12:24'),
(32, NULL, NULL, 'mahasiswa', '2110511009', NULL, 'Hendra Rahma', 'Sistem Informasi', NULL, NULL, NULL, NULL, '081254403684', 'Jl. Sudirman No. 73', 'selesai', '2026-09-27 12:12:24'),
(33, NULL, NULL, 'mahasiswa', '2110511011', NULL, 'Bayu Setiawan', 'Sistem Informasi', NULL, NULL, NULL, NULL, '081238779387', 'Jl. Bunga Tanjung No. 34', 'aktif', '2026-09-27 12:12:24'),
(34, NULL, NULL, 'mahasiswa', '2110511012', NULL, 'Sari Permata', 'Sistem Informasi', NULL, NULL, NULL, NULL, '081279005656', 'Jl. Bunga Tanjung No. 9', 'aktif', '2026-09-27 12:12:24'),
(35, NULL, NULL, 'mahasiswa', '2110511013', NULL, 'Budi Santoso', 'Sistem Informasi', NULL, NULL, NULL, NULL, '081281791943', 'Jl. Bunga Tanjung No. 22', 'aktif', '2026-09-27 12:12:24'),
(36, NULL, NULL, 'siswa_pkl', NULL, '008812589', 'Wulan Oktaviani', NULL, 'Akuntansi', 'SMA Negeri 4 Kendari', NULL, NULL, '081318411135', 'Jl. Diponegoro No. 103', 'aktif', '2026-09-27 12:12:24'),
(37, NULL, NULL, 'siswa_pkl', NULL, '004862229', 'Agus Nugroho', NULL, 'Teknik Elektronika', 'SMK Negeri 1 Kendari', NULL, 26, '081350480835', 'Jl. MT Haryono No. 91', 'aktif', '2026-09-27 12:12:24'),
(38, NULL, NULL, 'siswa_pkl', NULL, '005624868', 'Lina Ramadhan', NULL, 'Teknik Elektronika', 'SMK Negeri 3 Kendari', NULL, NULL, '081321613323', 'Jl. Diponegoro No. 119', 'aktif', '2026-09-27 12:12:24'),
(39, NULL, NULL, 'siswa_pkl', NULL, '006974463', 'Eko Permata', NULL, 'Akuntansi', 'SMK Negeri 2 Kendari', NULL, NULL, '081386918310', 'Jl. Sao-Sao No. 79', 'aktif', '2026-09-27 12:12:24'),
(40, NULL, NULL, 'siswa_pkl', NULL, '007136840', 'Lestari Yulianti', NULL, 'Multimedia', 'SMK Negeri 2 Kendari', NULL, NULL, '081327769137', 'Jl. Saranani No. 74', 'aktif', '2026-09-27 12:12:24'),
(41, NULL, NULL, 'siswa_pkl', NULL, '008036359', 'Siti Permata', NULL, 'Akuntansi', 'SMK Muhammadiyah Kendari', NULL, NULL, '081339243019', 'Jl. Diponegoro No. 5', 'aktif', '2026-09-27 12:12:24'),
(42, NULL, NULL, 'siswa_pkl', NULL, '006810440', 'Rizal Nugroho', NULL, 'RPL', 'SMK Negeri 3 Kendari', NULL, NULL, '081316865714', 'Jl. Sao-Sao No. 97', 'aktif', '2026-09-27 12:12:24'),
(43, NULL, NULL, 'siswa_pkl', NULL, '008265151', 'Bayu Setiawan', NULL, 'RPL', 'SMK Negeri 1 Kendari', NULL, NULL, '081369999163', 'Jl. Saranani No. 87', 'aktif', '2026-09-27 12:12:24'),
(44, NULL, NULL, 'siswa_pkl', NULL, '005820247', 'Nur Yulianti', NULL, 'TKJ', 'SMA Negeri 4 Kendari', NULL, NULL, '081347173439', 'Jl. Ahmad Yani No. 111', 'selesai', '2026-09-27 12:12:24'),
(45, NULL, NULL, 'siswa_pkl', NULL, '003392578', 'Andi Pratama', NULL, 'RPL', 'SMK Muhammadiyah Kendari', NULL, NULL, '081396685212', 'Jl. Sao-Sao No. 51', 'aktif', '2026-09-27 12:12:24'),
(46, NULL, NULL, 'siswa_pkl', NULL, '006162746', 'Rizal Rahma', NULL, 'Multimedia', 'SMK Negeri 3 Kendari', NULL, NULL, '081389418581', 'Jl. Ahmad Yani No. 62', 'selesai', '2026-09-27 12:12:24'),
(47, NULL, NULL, 'siswa_pkl', NULL, '008802743', 'Hendra Setiawan', NULL, 'Multimedia', 'SMK Negeri 3 Kendari', NULL, NULL, '081376860312', 'Jl. Saranani No. 72', 'aktif', '2026-09-27 12:12:24'),
(48, NULL, NULL, 'siswa_pkl', NULL, '002955345', 'Rudi Ramadhan', NULL, 'Akuntansi', 'SMK Negeri 3 Kendari', NULL, NULL, '081359382473', 'Jl. Sao-Sao No. 62', 'selesai', '2026-09-27 12:12:24');

-- --------------------------------------------------------

--
-- Table structure for table `nilai`
--

CREATE TABLE `nilai` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `nilai_angka` decimal(5,2) NOT NULL DEFAULT 0.00,
  `nilai_huruf` varchar(2) NOT NULL DEFAULT '-',
  `predikat` varchar(60) NOT NULL DEFAULT '-',
  `catatan` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nilai`
--

INSERT INTO `nilai` (`id`, `mahasiswa_id`, `nilai_angka`, `nilai_huruf`, `predikat`, `catatan`, `updated_at`) VALUES
(1, 25, 100.00, 'A', 'Sangat Baik', 'Anjay', '2026-09-21 10:27:21');

-- --------------------------------------------------------

--
-- Table structure for table `sertifikat`
--

CREATE TABLE `sertifikat` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `nomor` varchar(60) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `diupload_oleh` enum('operator','dosen','mahasiswa') NOT NULL DEFAULT 'operator',
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('operator','dosen','mahasiswa') NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `email`, `role`, `status`, `created_at`) VALUES
(2, 'dan', '$2y$10$adviRIdcUEASvwrodnTfAOGWihHpwYSZ7NtvpkIeNiKp8OqAaqYNW', 'dan', 'operator@kampus.ac.id', 'operator', 'aktif', '2026-09-06 16:17:16'),
(3, 'dosen1', '$2y$10$B.oJC/sn7ZIzpdc73dPSJu3HbtQJpxcIkj1.96GoYC4lYWgC4qXvq', 'budi', 'fulan@kampus.ac.id', 'dosen', 'aktif', '2026-09-06 16:17:16'),
(6, 'dosen2', '$2y$10$69/NLHAzWVLfx6Ck9eDvyOgr.2w0ShyW2woDOq1EJ3O4Gz/h7gwa2', 'fulanah', NULL, 'dosen', 'aktif', '2026-09-11 01:07:19'),
(7, 'dsn_budi', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Dr. Budi Santoso, M.Kom', 'dsn_budi@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(8, 'dsn_siti', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Siti Nurhaliza, S.Kom., M.T', 'dsn_siti@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(9, 'dsn_ahmad', '$2y$10$xVt8Alpmm84KVLU50llSAOg1lJhBLmPeaBVxQdPGT9XITr1FkxV1a', 'Ahmad Fauzi, S.T., M.Eng', 'dsn_ahmad@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(10, 'dsn_dewi', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Dr. Dewi Lestari, M.Cs', 'dsn_dewi@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(11, 'dsn_hendra', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Hendra Gunawan, S.Kom., M.Kom', 'dsn_hendra@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(12, 'dsn_ratna', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Ratna Sari Dewi, S.T., M.T', 'dsn_ratna@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(13, 'dsn_yusuf', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Yusuf Maulana, S.Kom., M.Cs', 'dsn_yusuf@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(14, 'dsn_indah', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Indah Permatasari, S.Pd., M.Pd', 'dsn_indah@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(15, 'dsn_andi', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Dr. Andi Wijaya, S.Kom., M.T', 'dsn_andi@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:00:00'),
(17, 'dsn_citra', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Citra Dewi, S.Kom., M.Kom', 'dsn_citra@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:02:00'),
(18, 'dsn_dedi', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Dedi Kurniawan, S.T., M.T', 'dsn_dedi@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:03:00'),
(19, 'dsn_eka', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Eka Fitriani, S.Kom., M.Cs', 'dsn_eka@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:04:00'),
(20, 'dsn_fajar', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Fajar Nugroho, S.T., M.Eng', 'dsn_fajar@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:05:00'),
(21, 'dsn_gita', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Gita Maharani, S.Pd., M.Pd', 'dsn_gita@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:06:00'),
(22, 'dsn_hadi', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Hadi Prasetyo, S.Kom., M.T', 'dsn_hadi@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:07:00'),
(23, 'dsn_ira', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Ira Kusuma, S.T., M.T', 'dsn_ira@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:08:00'),
(24, 'dsn_joko', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Joko Susilo, S.Kom., M.Kom', 'dsn_joko@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:09:00'),
(25, 'dsn_kartika', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Kartika Sari, S.Pd., M.Hum', 'dsn_kartika@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:10:00'),
(26, 'dsn_lukman', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Lukman Hakim, S.T., M.Eng', 'dsn_lukman@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:11:00'),
(27, 'dsn_maya', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Maya Anggraini, S.Kom., M.Cs', 'dsn_maya@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:12:00'),
(28, 'dsn_naufal', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Naufal Rizki, S.T., M.T', 'dsn_naufal@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:13:00'),
(29, 'dsn_olivia', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Olivia Putri, S.Pd., M.Pd', 'dsn_olivia@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:14:00'),
(30, 'dsn_rio', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Rio Saputra, S.Kom., M.T', 'dsn_rio@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:15:00'),
(31, 'dsn_sari', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Sari Indah, S.T., M.Eng', 'dsn_sari@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:16:00'),
(32, 'dsn_teguh', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Teguh Firmansyah, S.Kom., M.Kom', 'dsn_teguh@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:17:00'),
(33, 'dsn_umi', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Umi Kalsum, S.Pd., M.Hum', 'dsn_umi@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:18:00'),
(34, 'dsn_viktor', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Viktor Manurung, S.T., M.T', 'dsn_viktor@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:19:00'),
(35, 'dsn_wulan', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Wulan Septiani, S.Kom., M.Cs', 'dsn_wulan@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:20:00'),
(36, 'dsn_yoga', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Yoga Pratama, S.T., M.Eng', 'dsn_yoga@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:21:00'),
(37, 'dsn_zaki', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Zaki Alfarizi, S.Kom., M.T', 'dsn_zaki@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:22:00'),
(38, 'dsn_ari', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Ari Wibowo, S.T., M.T', 'dsn_ari@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:23:00'),
(39, 'dsn_bunga', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Bunga Citra Lestari, S.Pd., M.Pd', 'dsn_bunga@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:24:00'),
(40, 'dsn_arkan', '$2y$10$dummyHashedPasswordForSeedingPurposes000000000000000000000', 'Arkananta Devara, S.Kom., M.Kom', 'dsn_arkan@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-20 08:25:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_absen_harian` (`mahasiswa_id`,`tanggal`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nidn_nidk` (`nidn_nidk`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `kegiatan_harian`
--
ALTER TABLE `kegiatan_harian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_nim` (`nim`),
  ADD UNIQUE KEY `uniq_nisn` (`nisn`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `dosen_id` (`dosen_id`);

--
-- Indexes for table `nilai`
--
ALTER TABLE `nilai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_nilai_mhs` (`mahasiswa_id`);

--
-- Indexes for table `sertifikat`
--
ALTER TABLE `sertifikat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `kegiatan_harian`
--
ALTER TABLE `kegiatan_harian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `nilai`
--
ALTER TABLE `nilai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sertifikat`
--
ALTER TABLE `sertifikat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dosen`
--
ALTER TABLE `dosen`
  ADD CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kegiatan_harian`
--
ALTER TABLE `kegiatan_harian`
  ADD CONSTRAINT `kegiatan_harian_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `nilai`
--
ALTER TABLE `nilai`
  ADD CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sertifikat`
--
ALTER TABLE `sertifikat`
  ADD CONSTRAINT `sertifikat_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
