-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 21, 2026 at 09:00 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `banksampah_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_setoran`
--

CREATE TABLE `detail_setoran` (
  `no_transaksi` varchar(10) NOT NULL,
  `id_kategori_sampah` varchar(10) NOT NULL,
  `berat_kg` int DEFAULT NULL,
  `subtotal_harga` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_sampah`
--

CREATE TABLE `kategori_sampah` (
  `id_kategori_sampah` varchar(10) NOT NULL,
  `nama_sampah` varchar(50) NOT NULL,
  `harga_beli_per_kg` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nasabah`
--

CREATE TABLE `nasabah` (
  `id_nasabah` varchar(10) NOT NULL,
  `nama_nasabah` varchar(100) NOT NULL,
  `nomor_rekening_sampah` varchar(50) NOT NULL,
  `alamat` text,
  `saldo_rupiah` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `petugas_id` varchar(10) NOT NULL,
  `nama_petugas` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `no_transaksi` varchar(10) NOT NULL,
  `tanggal_transaksi` date DEFAULT NULL,
  `jenis_transaksi` varchar(50) DEFAULT NULL,
  `petugas_id` varchar(10) DEFAULT NULL,
  `id_nasabah` varchar(10) DEFAULT NULL,
  `total_nominal` int DEFAULT NULL,
  `metode_penarikan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_setoran`
--
ALTER TABLE `detail_setoran`
  ADD PRIMARY KEY (`no_transaksi`,`id_kategori_sampah`),
  ADD KEY `id_kategori_sampah` (`id_kategori_sampah`);

--
-- Indexes for table `kategori_sampah`
--
ALTER TABLE `kategori_sampah`
  ADD PRIMARY KEY (`id_kategori_sampah`);

--
-- Indexes for table `nasabah`
--
ALTER TABLE `nasabah`
  ADD PRIMARY KEY (`id_nasabah`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`petugas_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`no_transaksi`),
  ADD KEY `petugas_id` (`petugas_id`),
  ADD KEY `id_nasabah` (`id_nasabah`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_setoran`
--
ALTER TABLE `detail_setoran`
  ADD CONSTRAINT `detail_setoran_ibfk_1` FOREIGN KEY (`no_transaksi`) REFERENCES `transaksi` (`no_transaksi`),
  ADD CONSTRAINT `detail_setoran_ibfk_2` FOREIGN KEY (`id_kategori_sampah`) REFERENCES `kategori_sampah` (`id_kategori_sampah`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`petugas_id`) REFERENCES `petugas` (`petugas_id`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_nasabah`) REFERENCES `nasabah` (`id_nasabah`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
