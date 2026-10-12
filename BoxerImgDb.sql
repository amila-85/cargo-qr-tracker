-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Oct 12, 2026 at 08:17 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `BoxerImgDb`
--

-- --------------------------------------------------------

--
-- Table structure for table `BoxShipments`
--

CREATE TABLE `BoxShipments` (
  `TrackingNumber` varchar(50) NOT NULL,
  `InvoiceNumber` varchar(50) DEFAULT NULL,
  `SenderName` varchar(100) DEFAULT NULL,
  `SenderContactNumber` varchar(50) DEFAULT NULL,
  `FromAddress` text,
  `ReceiverName` varchar(100) DEFAULT NULL,
  `ReceiverContactNumber` varchar(50) DEFAULT NULL,
  `ToAddress` text,
  `BoxSize` varchar(50) DEFAULT NULL,
  `BoxType` varchar(50) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `Status` varchar(50) DEFAULT 'Warehouse',
  `UpdatedBy` varchar(100) DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `ReceiverEmail` varchar(150) DEFAULT NULL,
  `EstimatedDeliveryDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `BoxShipments`
--

INSERT INTO `BoxShipments` (`TrackingNumber`, `InvoiceNumber`, `SenderName`, `SenderContactNumber`, `FromAddress`, `ReceiverName`, `ReceiverContactNumber`, `ToAddress`, `BoxSize`, `BoxType`, `CreatedAt`, `Status`, `UpdatedBy`, `UpdatedAt`, `ReceiverEmail`, `EstimatedDeliveryDate`) VALUES
('EASCM73533N', 'INVM-00123', 'John Doe', '0211234567', 'Auckland', 'Jane Smith', '0229876543', 'Colombo', 'Medium', 'Standard', '2026-10-06 10:49:05', '6. Customs Cleared', 'User 1', '2026-10-10 22:47:44', 'madushanka8m@gmail.com', '2026-10-10');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `UserId` int NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Role` varchar(20) DEFAULT 'Staff',
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`UserId`, `Username`, `Password`, `FullName`, `Role`, `CreatedAt`) VALUES
(1, 'admin', '123456', 'Amila Bandara', 'Admin', '2026-10-07 10:30:32'),
(2, 'User', '123456', 'User 1', 'user', '2026-10-07 10:30:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `BoxShipments`
--
ALTER TABLE `BoxShipments`
  ADD PRIMARY KEY (`TrackingNumber`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`UserId`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `UserId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
