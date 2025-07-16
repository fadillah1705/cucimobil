-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2025 at 08:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

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
  `nama` varchar(100) DEFAULT NULL,
  `layanan` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id`, `nama`, `layanan`, `waktu`) VALUES
(6, 'Emsit Patyradja', 'Salon Mobil Kaca', '2025-07-16 13:05:11'),
(7, 'fathul', 'Cuci Mobil Exterior', '2025-07-16 13:07:25');

-- --------------------------------------------------------

--
-- Table structure for table `mencuci`
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
-- Dumping data for table `mencuci`
--

INSERT INTO `mencuci` (`id`, `username`, `password`, `role`, `nama_lengkap`, `foto`, `gender`) VALUES
(1, 'fadillah', '$2y$10$7ppWKk1J9mVOH2senJVhQ.ckg1y6wB0ljipLBH50QsIICsFzrpQjG', 'admin', 'Nur Fadillah', NULL, 'Wanita'),
(2, 'emsit', '$2y$10$1/zo./lkOzHJxWCzSSdn4ONXJr8EiJyxKOSMzo.hfeVx77Lyystz.', 'user', 'Emsit Patyradja', NULL, 'Wanita'),
(3, 'fathul', '$2y$10$.FE4p2nznBnsZHEU2p7Gy.LyQZ1TGi3P3uUPhN50z6xFNG3YOGmiu', 'user', NULL, NULL, NULL),
(12, 'indah', '$2y$10$J1N5PDujRTtYkm30r48Wm.ixJ51Pxeo4J1Z6E47VtKz9WI8O6jVZ.', 'admin', NULL, NULL, NULL),
(13, 'ustazah', '$2y$10$89znLanyNOTTO8OvygeiM.AIxbOrhKIcJLYlIw7sckFE0X7Fh1BBO', 'admin', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mencuci`
--
ALTER TABLE `mencuci`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mencuci`
--
ALTER TABLE `mencuci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
