-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2025 at 04:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `pelanggan_id` int(11) DEFAULT NULL,
  `waktu` varchar(20) DEFAULT NULL,
  `tanggal` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id`, `pelanggan_id`, `waktu`, `tanggal`, `status`) VALUES
(51, 12, '13:18', '2025-08-01', 'Menunggu'),
(52, 22, '13:21', '2025-08-02', 'Menunggu'),
(53, 12, '15:25', '2025-08-02', 'Menunggu');

-- --------------------------------------------------------

--
-- Table structure for table `booking_layanan`
--

CREATE TABLE `booking_layanan` (
  `booking_id` int(11) NOT NULL,
  `layanan_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_layanan`
--

INSERT INTO `booking_layanan` (`booking_id`, `layanan_id`) VALUES
(51, 1),
(51, 4),
(52, 1),
(52, 6),
(53, 2),
(53, 4);

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `product_used` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `layanan`
--

INSERT INTO `layanan` (`id`, `nama`, `description`, `product_used`, `price`, `is_active`, `created_at`, `updated_at`, `image`) VALUES
(1, 'Cuci Eksterior', 'Membersihkan bagian luar mobil ', 'Shampoo Mobil (Car Wash Soap)', 35000, 1, '2025-07-24 07:48:08', '2025-08-01 02:52:55', 'img/services/service_6881e5380773d8.70033252.webp'),
(2, 'Cuci Interior', 'Vakum, pembersihan dashboard, jok, dan karpet mobil', 'Vacuum Cleaner', 35000, 1, '2025-07-24 07:48:44', '2025-07-30 06:18:00', 'img/services/service_6881e55c85d184.57850505.webp'),
(3, 'Detailing', 'Paket premium untuk merawat dan menjaga kilau cat ', 'APC + Vacuum', 25000, 1, '2025-07-24 07:49:29', '2025-07-30 06:18:03', 'img/services/service_6881e5897143e4.67377624.webp'),
(4, 'Cuci Mobil', 'Pembersihan menyeluruh bagian luar dan dalam mobil', 'Glass Cleaner ', 50000, 1, '2025-07-24 07:50:12', '2025-07-30 06:13:56', 'img/services/service_6881e5b4348e05.06814462.webp'),
(5, 'Salon Mobil Kaca', 'Kaca depan dan samping mobil dibersihkan menyeluruh', 'Snow Foam', 25000, 0, '2025-07-24 07:51:20', '2025-08-01 02:53:23', 'img/services/service_6881e5f8691295.93116846.webp'),
(6, 'Perbaiki Mesin', 'Periksa dan perbaiki mesin mobil ', 'Dongkrak & jack stand', 30000, 0, '2025-07-24 07:51:50', '2025-08-01 02:53:30', 'img/services/service_6881e616b1a989.14854613.webp');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_card`
--

CREATE TABLE `loyalty_card` (
  `id` int(11) NOT NULL,
  `pelanggan_id` int(11) NOT NULL,
  `terakhir_cuci` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reward_claims`
--

CREATE TABLE `reward_claims` (
  `id` int(11) NOT NULL,
  `loyalty_card_id` int(11) DEFAULT NULL,
  `claimed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'Klaim Dibuat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 12, 5, 'Pelayanan yang sangat memuaskan, saya bisa menjadi membership dan mendapat gratis cuci mobil', '2025-07-25 02:19:13'),
(2, 22, 5, 'Harga cuci mobil di Go Wash sangat terjangkau, tapi kualitasnya premium! Mobil saya selalu terlihat seperti baru keluar dari toko. ', '2025-07-25 02:28:59'),
(7, 19, 4, 'Mobil saya jadi kinclong banget setelah dicuci di sini! Area interior dan eksterior dibersihkan dengan detail, bahkan noda membandel juga hilang. Recommended!', '2025-07-30 02:23:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `nama_lengkap` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `gender` enum('Pria','Wanita') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama_lengkap`, `foto`, `gender`) VALUES
(12, 'zahra', '$2y$10$hoCQq57gXGzFfXcfM31A6uwLRHtZcHt8QCtyMKujetaIUcUq20gIq', 'user', 'Zahra wahar', NULL, 'Wanita'),
(14, 'fadilah', '$2y$10$zWhtm9twxnQD3i8O1XA7FuYrusZAqaU5lrFqzcmdVjP8Ik64KEXye', 'admin', 'Nur Fadillah', '6881e040755b1.jpg', 'Wanita'),
(15, 'baim', '$2y$10$EWJKoRLt8H8.YO4BxbM.5ORpM2Yw8Ah2.X4A4KW.VlbTNhvJWTXBW', 'user', NULL, NULL, NULL),
(18, 'dini', '$2y$10$ak8nGHvipxL5GkUzZdJczujqlmEbOUBG9aykd2NRyNlK4wsycPx0S', 'admin', 'Aisyah Ardini', NULL, 'Wanita'),
(19, 'indah', '$2y$10$sNxtg67c.DRIcNt5HJKGdOXPBnmmwbB68NBWQJt2eNny/V/R0Eajq', 'user', 'indah desintia', '688982c126e70.jpg', 'Wanita'),
(22, 'dilla', '$2y$10$LHhn0fAN70mFzlwWOan61eUMa3Z8Yd5S/tu3YQmWo5hpSlW/bzdOu', 'user', 'NurFadillah', '6881dff0c3254.jpg', 'Wanita');

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

CREATE TABLE `voucher` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kode_voucher` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Aktif',
  `dibuat_pada` datetime NOT NULL DEFAULT current_timestamp(),
  `reward_claim_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pelanggan_id` (`pelanggan_id`);

--
-- Indexes for table `booking_layanan`
--
ALTER TABLE `booking_layanan`
  ADD PRIMARY KEY (`booking_id`,`layanan_id`),
  ADD KEY `layanan_id` (`layanan_id`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loyalty_card`
--
ALTER TABLE `loyalty_card`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelanggan_id` (`pelanggan_id`);

--
-- Indexes for table `reward_claims`
--
ALTER TABLE `reward_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loyalty_card_id` (`loyalty_card_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_voucher` (`kode_voucher`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reward_claim_id` (`reward_claim_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `loyalty_card`
--
ALTER TABLE `loyalty_card`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reward_claims`
--
ALTER TABLE `reward_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_layanan`
--
ALTER TABLE `booking_layanan`
  ADD CONSTRAINT `booking_layanan_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_layanan_ibfk_2` FOREIGN KEY (`layanan_id`) REFERENCES `layanan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reward_claims`
--
ALTER TABLE `reward_claims`
  ADD CONSTRAINT `reward_claims_ibfk_1` FOREIGN KEY (`loyalty_card_id`) REFERENCES `loyalty_card` (`id`);

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `voucher`
--
ALTER TABLE `voucher`
  ADD CONSTRAINT `voucher_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `voucher_ibfk_2` FOREIGN KEY (`reward_claim_id`) REFERENCES `reward_claims` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
