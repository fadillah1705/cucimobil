-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Jul 2025 pada 05.29
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
-- Struktur dari tabel `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `layanan` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking`
--

INSERT INTO `booking` (`id`, `nama`, `layanan`, `waktu`) VALUES
(4, 'zahra wahar', 'Detailing', '2025-07-17 10:27:45');

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
(2, '', '$2y$10$1/zo./lkOzHJxWCzSSdn4ONXJr8EiJyxKOSMzo.hfeVx77Lyystz.', 'user', 'Emsit Pattyradja', NULL, 'Wanita'),
(3, 'fathul', '$2y$10$.FE4p2nznBnsZHEU2p7Gy.LyQZ1TGi3P3uUPhN50z6xFNG3YOGmiu', 'user', NULL, NULL, NULL),
(12, 'zahra', '$2y$10$hoCQq57gXGzFfXcfM31A6uwLRHtZcHt8QCtyMKujetaIUcUq20gIq', 'user', 'zahra wahar', NULL, 'Wanita'),
(13, 'fadil', '$2y$10$6kRdv9rAG1RfV9BBwGQ8qOy4zdA5jSoKaqRH4B.QpLD2agf0Fxx1K', 'user', NULL, NULL, NULL),
(14, 'fadilah', '$2y$10$zWhtm9twxnQD3i8O1XA7FuYrusZAqaU5lrFqzcmdVjP8Ik64KEXye', 'admin', NULL, NULL, NULL),
(15, 'baim', '$2y$10$EWJKoRLt8H8.YO4BxbM.5ORpM2Yw8Ah2.X4A4KW.VlbTNhvJWTXBW', 'admin', NULL, NULL, NULL),
(16, 'tika', '$2y$10$bZ.e4pNYgVzzzQ2ktys4OOGduk4vUiN772Pd9/C8qG/iB44K2OpTu', 'admin', NULL, NULL, NULL),
(17, 'zia', '$2y$10$tRlMviJvGVkiDCJ3O0qPfenO7lImxlVSjqQw2DaLS.oKnNTwXowdW', 'user', 'fahzia', NULL, 'Wanita'),
(18, 'dini', '$2y$10$ak8nGHvipxL5GkUzZdJczujqlmEbOUBG9aykd2NRyNlK4wsycPx0S', 'admin', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT untuk tabel `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `mencuci`
--
ALTER TABLE `mencuci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
