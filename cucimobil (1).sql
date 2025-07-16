-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Jul 2025 pada 05.46
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cucimobil`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `mencuci`
--

CREATE TABLE `mencuci` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `nama_lengkap` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `gender` enum('Pria','Wanita') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mencuci`
--

INSERT INTO `mencuci` (`id`, `username`, `password`, `role`, `nama_lengkap`, `foto`, `gender`) VALUES
(1, 'fadillah', '$2y$10$7ppWKk1J9mVOH2senJVhQ.ckg1y6wB0ljipLBH50QsIICsFzrpQjG', 'admin', 'Nur Fadillah', NULL, 'Wanita'),
(2, 'emsit', '$2y$10$1/zo./lkOzHJxWCzSSdn4ONXJr8EiJyxKOSMzo.hfeVx77Lyystz.', 'user', 'Emsit Pattyradja', NULL, 'Wanita'),
(3, 'fathul', '$2y$10$.FE4p2nznBnsZHEU2p7Gy.LyQZ1TGi3P3uUPhN50z6xFNG3YOGmiu', 'user', NULL, NULL, NULL),
(12, 'zahra', '$2y$10$oJkpcKtECq7dNR4mjpydY.fPZ01c3PwfwZFcddmzstSCs9SaKfQya', 'user', 'zahra wahar', NULL, 'Wanita');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `mencuci`
--
ALTER TABLE `mencuci`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `mencuci`
--
ALTER TABLE `mencuci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
