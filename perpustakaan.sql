-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2025 at 10:32 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`, `created_at`) VALUES
(1, 'admin', '$2y$10$Ih2cYqTl..1cl9TWsRXS4ueX0MFuMkJ9Lb.m5JD8hDIwjCfPkQRvS', 'Administrator Sistem', '2025-10-04 07:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id` int(11) NOT NULL,
  `kode_anggota` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id`, `kode_anggota`, `nama`, `email`, `telepon`, `alamat`, `created_at`) VALUES
(1, 'AGT001', 'Ahmad Wijaya', 'ahmad@email.com', '08123456789', 'Jl. Merdeka No. 123', '2025-10-04 07:10:20'),
(2, 'AGT002', 'Siti Rahayu', 'siti@email.com', '08129876543', 'Jl. Sudirman No. 456', '2025-10-04 07:10:20'),
(3, 'AGT003', 'Budi Pratama', 'budi@email.com', '08111222333', 'Jl. Gatot Subroto No. 789', '2025-10-04 07:10:20'),
(4, 'AGT004', 'Maya Sari', 'maya@email.com', '08133445566', 'Jl. Thamrin No. 321', '2025-10-04 07:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `penerbit` varchar(100) NOT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT 'Umum',
  `jumlah_halaman` int(11) DEFAULT 0,
  `stok` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `isbn`, `kategori`, `jumlah_halaman`, `stok`, `created_at`) VALUES
(1, 'Pemrograman PHP Dasar', 'Budi Santoso', 'PT. Tekno Indonesia', 2023, '1111111111', 'Teknologi', 300, 4, '2025-10-04 07:10:20'),
(2, 'Database MySQL Untuk Pemula', 'Sari Dewi', 'PT. Ilmu Komputer', 2022, '2222222222', 'Teknologi', 250, 4, '2025-10-04 07:10:20'),
(3, 'Sejarah Indonesia Modern', 'Prof. Ahmad', 'PT. Sejahtera Abadi', 2021, '3333333333', 'Sejarah', 400, 5, '2025-10-04 07:10:20'),
(4, 'Matematika Diskrit', 'Dr. Wijaya', 'PT. Pendidikan', 2020, '4444444444', 'Pendidikan', 350, 5, '2025-10-04 07:10:20'),
(5, 'Kumpulan Cerpen', 'Diana Putri', 'PT. Sastra', 2023, '5555555555', 'Sastra', 200, 5, '2025-10-04 07:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `kode_peminjaman` varchar(20) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan','terlambat') DEFAULT 'dipinjam',
  `denda` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `kode_peminjaman`, `anggota_id`, `buku_id`, `tanggal_pinjam`, `tanggal_kembali`, `status`, `denda`, `created_at`) VALUES
(5, 'PJN20251004102013455', 1, 1, '2025-10-03', NULL, 'dipinjam', '0.00', '2025-10-04 08:20:13'),
(6, 'PJN20251004102013608', 2, 2, '2025-09-29', NULL, 'dipinjam', '0.00', '2025-10-04 08:20:13'),
(7, 'PJN20251004102013254', 1, 3, '2025-09-24', '2025-10-04', 'terlambat', '15000.00', '2025-10-04 08:20:13'),
(8, 'PJN20251004102013823', 3, 4, '2025-09-14', '2025-10-04', 'terlambat', '65000.00', '2025-10-04 08:20:13'),
(9, 'PJN20251004102013794', 2, 1, '2025-09-19', '2025-09-26', 'dikembalikan', '0.00', '2025-10-04 08:20:13'),
(10, 'PJN20251004102013584', 3, 2, '2025-09-14', '2025-09-24', 'terlambat', '15000.00', '2025-10-04 08:20:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_anggota` (`kode_anggota`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_peminjaman` (`kode_peminjaman`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
