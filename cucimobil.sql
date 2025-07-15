-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 08:53 AM
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
-- Table structure for table `mencuci`
--

CREATE TABLE `mencuci` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mencuci`
--

INSERT INTO `mencuci` (`id`, `username`, `password`) VALUES
(1, 'fadillah', '$2y$10$7ppWKk1J9mVOH2senJVhQ.ckg1y6wB0ljipLBH50QsIICsFzrpQjG'),
(2, 'emsit', '$2y$10$1/zo./lkOzHJxWCzSSdn4ONXJr8EiJyxKOSMzo.hfeVx77Lyystz.'),
(3, 'fathul', '$2y$10$.FE4p2nznBnsZHEU2p7Gy.LyQZ1TGi3P3uUPhN50z6xFNG3YOGmiu');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mencuci`
--
ALTER TABLE `mencuci`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mencuci`
--
ALTER TABLE `mencuci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
