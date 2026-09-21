-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 15, 2026 at 02:57 PM
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
(6, 5, '2026-09-15', '19:04:56', '19:04:59', 'terlambat', 'sesuai_jadwal', NULL, '2026-09-15 11:04:56');

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

INSERT INTO `dosen` (`id`, `user_id`, `nidn_nidk`, `nama`, `no_hp`, `created_at`) VALUES
(1, 3, '0012058501', 'fulan', '081234567890', '2026-09-06 16:17:16'),
(2, 6, '12345432', 'fulanah', '21312434', '2026-09-11 01:07:19'),
(3, 8, '0023069002', 'Siti Nurhaliza, S.Kom., M.T', '081234500002', '2026-09-15 10:54:32'),
(4, 9, '0034077403', 'Ahmad Fauzi, S.T., M.Eng', '081234500003', '2026-09-15 10:54:32'),
(5, 10, '0045088704', 'Dr. Dewi Lestari, M.Cs', '081234500004', '2026-09-15 10:54:32'),
(6, 11, '0056099105', 'Hendra Gunawan, S.Kom., M.Kom', '081234500005', '2026-09-15 10:54:32'),
(7, 12, '0067100806', 'Ratna Sari Dewi, S.T., M.T', '081234500006', '2026-09-15 10:54:32'),
(8, 13, '0078111907', 'Yusuf Maulana, S.Kom., M.Cs', '081234500007', '2026-09-15 10:54:32'),
(9, 14, '0089123008', 'Indah Permatasari, S.Pd., M.Pd', '081234500008', '2026-09-15 10:54:32');

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
  `prodi` enum('Sistem Informasi','Teknik Informatika') DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `asal_sekolah` varchar(150) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `dosen_id` int(11) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` enum('aktif','cuti','lulus','selesai','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `user_id`, `tipe`, `nim`, `nisn`, `nama`, `prodi`, `jurusan`, `asal_sekolah`, `foto`, `dosen_id`, `no_hp`, `alamat`, `status`, `created_at`) VALUES
(4, NULL, 'mahasiswa', '123456', NULL, 'spp fulan', 'Teknik Informatika', NULL, NULL, 'foto_1789089725_ac2a195c.png', 2, '0831434', '', 'aktif', '2026-09-11 01:22:05'),
(5, NULL, 'mahasiswa', '2110511001', NULL, 'Andi Saputra', 'Sistem Informasi', NULL, NULL, 'foto_dummy_01.png', 1, '081295822412', 'Jl. Sudirman No. 10, Kendari', 'aktif', '2026-09-15 10:54:32'),
(6, NULL, 'mahasiswa', '2110511002', NULL, 'Muhammad Rizky Pratama', 'Teknik Informatika', NULL, NULL, 'foto_dummy_02.png', 3, '081213356886', 'Jl. Ahmad Yani No. 32, Kendari', 'aktif', '2026-09-15 10:54:32'),
(7, NULL, 'mahasiswa', '2110511003', NULL, 'Nur Aisyah', 'Sistem Informasi', NULL, NULL, 'foto_dummy_03.png', 4, '081246913810', 'Jl. Sudirman No. 22, Kendari', 'aktif', '2026-09-15 10:54:32'),
(8, NULL, 'mahasiswa', '2110511004', NULL, 'Fajar Ramadhan', 'Teknik Informatika', NULL, NULL, 'foto_dummy_04.png', 5, '081239958838', 'Jl. Sudirman No. 13, Kendari', 'aktif', '2026-09-15 10:54:32'),
(9, NULL, 'mahasiswa', '2110511005', NULL, 'Putri Wulandari', 'Sistem Informasi', NULL, NULL, 'foto_dummy_05.png', NULL, '081223756669', 'Jl. Ahmad Yani No. 26, Kendari', 'aktif', '2026-09-15 10:54:32'),
(10, NULL, 'mahasiswa', '2110511006', NULL, 'Rian Hidayat', 'Teknik Informatika', NULL, NULL, 'foto_dummy_06.png', 7, '081283197857', 'Jl. Sudirman No. 7, Kendari', 'aktif', '2026-09-15 10:54:32'),
(11, NULL, 'mahasiswa', '2110511007', NULL, 'Dinda Ayu Lestari', 'Sistem Informasi', NULL, NULL, 'foto_dummy_07.png', 8, '081289254563', 'Jl. Ahmad Yani No. 2, Kendari', 'aktif', '2026-09-15 10:54:32'),
(12, NULL, 'mahasiswa', '2110511008', NULL, 'Bagas Wicaksono', 'Teknik Informatika', NULL, NULL, 'foto_dummy_08.png', 9, '081214265799', 'Jl. Sudirman No. 1, Kendari', 'aktif', '2026-09-15 10:54:32'),
(13, NULL, 'mahasiswa', '2110511009', NULL, 'Salsabila Azzahra', 'Sistem Informasi', NULL, NULL, 'foto_dummy_09.png', 1, '081222575562', 'Jl. Sudirman No. 19, Kendari', 'aktif', '2026-09-15 10:54:32'),
(14, NULL, 'mahasiswa', '2110511010', NULL, 'Fikri Ardiansyah', 'Teknik Informatika', NULL, NULL, 'foto_dummy_10.png', NULL, '081241227216', 'Jl. Ahmad Yani No. 11, Kendari', 'cuti', '2026-09-15 10:54:32'),
(15, NULL, 'mahasiswa', '2110511011', NULL, 'Melati Kusuma', 'Sistem Informasi', NULL, NULL, 'foto_dummy_11.png', 4, '081290801586', 'Jl. Sudirman No. 1, Kendari', 'lulus', '2026-09-15 10:54:32'),
(16, NULL, 'mahasiswa', '2110511012', NULL, 'Reza Firmansyah', 'Teknik Informatika', NULL, NULL, 'foto_dummy_12.png', 5, '081285329037', 'Jl. Sudirman No. 19, Kendari', 'selesai', '2026-09-15 10:54:32'),
(17, NULL, 'siswa_pkl', NULL, '0051234567', 'Siti Rahma', 'Sistem Informasi', NULL, 'SMK Negeri 1 Kendari', 'foto_dummy_13.png', 5, '081397226012', 'Jl. Ahmad Yani No. 29, Kendari', 'aktif', '2026-09-15 10:54:32'),
(18, NULL, 'siswa_pkl', NULL, '0051234678', 'Dimas Prasetyo', 'Teknik Informatika', NULL, 'SMK Negeri 2 Kendari', 'foto_dummy_14.png', 6, '081383140807', 'Jl. Ahmad Yani No. 2, Kendari', 'aktif', '2026-09-15 10:54:32'),
(19, NULL, 'siswa_pkl', NULL, '0051234789', 'Anisa Fitriani', 'Sistem Informasi', NULL, 'SMK Negeri 4 Kendari', 'foto_dummy_15.png', 7, '081339587039', 'Jl. Ahmad Yani No. 5, Kendari', 'aktif', '2026-09-15 10:54:32'),
(20, NULL, 'siswa_pkl', NULL, '0051234900', 'Wahyu Setiawan', 'Teknik Informatika', NULL, 'SMK Telkom Sulawesi Tenggara', 'foto_dummy_16.png', 8, '081389089901', 'Jl. Sudirman No. 25, Kendari', 'aktif', '2026-09-15 10:54:32'),
(21, NULL, 'siswa_pkl', NULL, '0051235011', 'Kirana Maharani', 'Sistem Informasi', NULL, 'SMK Muhammadiyah Kendari', 'foto_dummy_17.png', 9, '081310872248', 'Jl. Ahmad Yani No. 35, Kendari', 'aktif', '2026-09-15 10:54:32'),
(22, NULL, 'siswa_pkl', NULL, '0051235122', 'Iqbal Maulana', 'Teknik Informatika', NULL, 'SMK Negeri 1 Kendari', 'foto_dummy_18.png', NULL, '081331429110', 'Jl. Ahmad Yani No. 29, Kendari', 'aktif', '2026-09-15 10:54:32'),
(23, NULL, 'siswa_pkl', NULL, '0051235233', 'Zahra Amelia', 'Sistem Informasi', NULL, 'SMK Negeri 2 Kendari', 'foto_dummy_19.png', 3, '081366722344', 'Jl. Sudirman No. 31, Kendari', 'selesai', '2026-09-15 10:54:32'),
(24, NULL, 'siswa_pkl', NULL, '0051235344', 'Bima Sakti', 'Teknik Informatika', NULL, 'SMK Negeri 4 Kendari', 'foto_dummy_20.png', 4, '081347295260', 'Jl. Sudirman No. 13, Kendari', 'selesai', '2026-09-15 10:54:32');

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

--
-- Dumping data for table `sertifikat`
--

INSERT INTO `sertifikat` (`id`, `mahasiswa_id`, `judul`, `file_path`, `diupload_oleh`, `tanggal_upload`) VALUES
(2, 24, 'Sertifikat PKL Semester Genap', 'sertifikat_dummy_25.pdf', 'operator', '2026-01-26 01:00:00'),
(3, 19, 'Sertifikat Pelatihan Web Development', 'sertifikat_dummy_16.pdf', 'operator', '2026-02-22 01:00:00'),
(4, 12, 'Sertifikat Workshop UI/UX Design', 'sertifikat_dummy_07.pdf', 'operator', '2026-02-28 01:00:00'),
(5, 21, 'Sertifikat Webinar Keamanan Siber', 'sertifikat_dummy_20.pdf', 'operator', '2026-03-07 01:00:00'),
(6, 13, 'Sertifikat Seminar Nasional Teknologi Informasi', 'sertifikat_dummy_08.pdf', 'operator', '2026-03-09 01:00:00'),
(7, 22, 'Sertifikat Pelatihan Public Speaking', 'sertifikat_dummy_23.pdf', 'operator', '2026-03-09 01:00:00'),
(8, 20, 'Sertifikat Bootcamp Data Science', 'sertifikat_dummy_19.pdf', 'operator', '2026-03-20 01:00:00'),
(9, 11, 'Sertifikat Kegiatan Volunteer Sosial Kampus', 'sertifikat_dummy_05.pdf', 'operator', '2026-03-26 01:00:00'),
(10, 21, 'Sertifikat Kegiatan Volunteer Sosial Kampus', 'sertifikat_dummy_22.pdf', 'operator', '2026-04-03 01:00:00'),
(11, 5, 'Sertifikat Pelatihan Public Speaking', 'sertifikat_dummy_01.pdf', 'operator', '2026-04-06 01:00:00'),
(12, 17, 'Sertifikat Workshop UI/UX Design', 'sertifikat_dummy_13.pdf', 'operator', '2026-04-10 01:00:00'),
(13, 15, 'Sertifikat Pelatihan Web Development', 'sertifikat_dummy_11.pdf', 'operator', '2026-04-17 01:00:00'),
(14, 20, 'Sertifikat Magang PT Telkom Indonesia', 'sertifikat_dummy_18.pdf', 'operator', '2026-04-17 01:00:00'),
(15, 19, 'Sertifikat Pelatihan Jaringan Komputer (Cisco)', 'sertifikat_dummy_17.pdf', 'operator', '2026-05-26 01:00:00'),
(16, 12, 'Sertifikat Pelatihan Jaringan Komputer (Cisco)', 'sertifikat_dummy_06.pdf', 'operator', '2026-06-06 01:00:00'),
(17, 19, 'Sertifikat Webinar Keamanan Siber', 'sertifikat_dummy_15.pdf', 'operator', '2026-06-21 01:00:00'),
(18, 21, 'Sertifikat Kompetensi Junior Web Developer (BNSP)', 'sertifikat_dummy_21.pdf', 'operator', '2026-07-04 01:00:00'),
(19, 18, 'Sertifikat PKL Semester Genap', 'sertifikat_dummy_14.pdf', 'operator', '2026-07-08 01:00:00'),
(20, 13, 'Sertifikat Webinar Keamanan Siber', 'sertifikat_dummy_09.pdf', 'operator', '2026-07-26 01:00:00'),
(21, 8, 'Sertifikat PKL Semester Genap', 'sertifikat_dummy_03.pdf', 'operator', '2026-08-04 01:00:00'),
(22, 22, 'Sertifikat Seminar Nasional Teknologi Informasi', 'sertifikat_dummy_24.pdf', 'operator', '2026-08-08 01:00:00'),
(23, 16, 'Sertifikat Webinar Keamanan Siber', 'sertifikat_dummy_12.pdf', 'operator', '2026-08-11 01:00:00'),
(24, 7, 'Sertifikat Workshop UI/UX Design', 'sertifikat_dummy_02.pdf', 'operator', '2026-08-14 01:00:00'),
(25, 14, 'Sertifikat Pelatihan Web Development', 'sertifikat_dummy_10.pdf', 'operator', '2026-08-16 01:00:00'),
(26, 10, 'Sertifikat Pelatihan Web Development', 'sertifikat_dummy_04.pdf', 'operator', '2026-09-03 01:00:00');

--
-- Table structure for table `nilai`
--

CREATE TABLE IF NOT EXISTS `nilai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int(11) NOT NULL,
  `nilai_angka` decimal(5,2) NOT NULL DEFAULT 0,
  `nilai_huruf` varchar(2) NOT NULL DEFAULT '-',
  `predikat` varchar(60) NOT NULL DEFAULT '-',
  `catatan` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_nilai_mhs` (`mahasiswa_id`)
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
(2, 'operator1', '$2y$10$adviRIdcUEASvwrodnTfAOGWihHpwYSZ7NtvpkIeNiKp8OqAaqYNW', 'dan', 'operator@kampus.ac.id', 'operator', 'aktif', '2026-09-06 16:17:16'),
(3, 'dosen1', '$2y$10$B.oJC/sn7ZIzpdc73dPSJu3HbtQJpxcIkj1.96GoYC4lYWgC4qXvq', 'budi', 'fulan@kampus.ac.id', 'dosen', 'aktif', '2026-09-06 16:17:16'),
(6, 'dosen2', '$2y$10$69/NLHAzWVLfx6Ck9eDvyOgr.2w0ShyW2woDOq1EJ3O4Gz/h7gwa2', 'fulanah', NULL, 'dosen', 'aktif', '2026-09-11 01:07:19'),
(7, 'dsn_budi', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Dr. Budi Santoso, M.Kom', 'dsn_budi@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(8, 'dsn_siti', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Siti Nurhaliza, S.Kom., M.T', 'dsn_siti@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(9, 'dsn_ahmad', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Ahmad Fauzi, S.T., M.Eng', 'dsn_ahmad@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(10, 'dsn_dewi', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Dr. Dewi Lestari, M.Cs', 'dsn_dewi@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(11, 'dsn_hendra', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Hendra Gunawan, S.Kom., M.Kom', 'dsn_hendra@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(12, 'dsn_ratna', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Ratna Sari Dewi, S.T., M.T', 'dsn_ratna@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(13, 'dsn_yusuf', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Yusuf Maulana, S.Kom., M.Cs', 'dsn_yusuf@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32'),
(14, 'dsn_indah', '$6$dosenSeedSalt1$39ozRj/u8Ud23A5AbVl1hX/Xr4QB3rfQhaKM49J/j9YWbPrTOfBS3hpO3c3Ldia613r.5ronBfxxQVjQnBHVy1', 'Indah Permatasari, S.Pd., M.Pd', 'dsn_indah@stikom22januari.ac.id', 'dosen', 'aktif', '2026-09-15 10:54:32');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `kegiatan_harian`
--
ALTER TABLE `kegiatan_harian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `sertifikat`
--
ALTER TABLE `sertifikat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
-- Constraints for table `sertifikat`
--
ALTER TABLE `sertifikat`
  ADD CONSTRAINT `sertifikat_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nilai`
--
ALTER TABLE `nilai`
  ADD CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
