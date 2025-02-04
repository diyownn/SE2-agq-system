-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2025 at 03:02 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agq_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_archive`
--

CREATE TABLE `tbl_archive` (
  `archive_id` int(11) NOT NULL,
  `archive_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_company`
--

CREATE TABLE `tbl_company` (
  `CompanyID` int(11) NOT NULL,
  `Company_name` int(11) NOT NULL,
  `Company_picture` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_document`
--

CREATE TABLE `tbl_document` (
  `approved` tinyint(1) NOT NULL,
  `doc_type` varchar(25) NOT NULL,
  `comment` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_expbrk`
--

CREATE TABLE `tbl_expbrk` (
  `exportB_refNum` int(11) NOT NULL,
  `loading` int(11) NOT NULL,
  `arr_wha` int(11) NOT NULL,
  `white_co` int(11) NOT NULL,
  `form_d` int(11) NOT NULL,
  `pcci` int(11) NOT NULL,
  `trucking` int(11) NOT NULL,
  `others_chg` int(11) NOT NULL,
  `others` varchar(25) NOT NULL,
  `summary` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_expfwd`
--

CREATE TABLE `tbl_expfwd` (
  `exportF_refNum` int(11) NOT NULL,
  `ship_line` int(11) NOT NULL,
  `others_chg` int(11) NOT NULL,
  `others` varchar(25) NOT NULL,
  `summary` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_impbrk`
--

CREATE TABLE `tbl_impbrk` (
  `importB_refNum` int(11) NOT NULL,
  `arr_wha` int(11) NOT NULL,
  `form_stamp` int(11) NOT NULL,
  `pcopy_notary` int(11) NOT NULL,
  `e2m_lodge` int(11) NOT NULL,
  `stuffing` int(11) NOT NULL,
  `icco` int(11) NOT NULL,
  `trucking` int(11) NOT NULL,
  `handling` int(11) NOT NULL,
  `loading` int(11) NOT NULL,
  `pcci` int(11) NOT NULL,
  `others_chg` int(11) NOT NULL,
  `others` varchar(25) NOT NULL,
  `summary` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_impfwd`
--

CREATE TABLE `tbl_impfwd` (
  `importF_refNum` int(11) NOT NULL,
  `shipment` varchar(50) NOT NULL,
  `exp_arr_wha` int(11) NOT NULL,
  `exp_white_co` int(11) NOT NULL,
  `exp_form_d` int(11) NOT NULL,
  `exp_trucking` int(11) NOT NULL,
  `exp_pcci` int(11) NOT NULL,
  `exp_loading` int(11) NOT NULL,
  `exp_shipline` int(11) NOT NULL,
  `exp_others` int(11) NOT NULL,
  `local_charge` int(11) NOT NULL,
  `cont_deposit` int(11) NOT NULL,
  `others_chg` int(11) NOT NULL,
  `others` varchar(25) NOT NULL,
  `summary` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transaction`
--

CREATE TABLE `tbl_transaction` (
  `TransactionID` varchar(20) NOT NULL,
  `Shipper` varchar(50) NOT NULL,
  `Date` date NOT NULL,
  `Department` varchar(50) NOT NULL,
  `Vessel` varchar(50) NOT NULL,
  `BLNum` int(11) NOT NULL,
  `DestinationOrigin` varchar(50) NOT NULL,
  `NatureOfGoods` varchar(50) NOT NULL,
  `Volume` varchar(20) NOT NULL,
  `ER` int(11) NOT NULL,
  `EstTimeArrival` datetime NOT NULL,
  `PackageCount` int(11) NOT NULL,
  `PackageWeight` int(11) NOT NULL,
  `PackageMeasurement` int(11) NOT NULL,
  `RefNum` varchar(20) NOT NULL,
  `IsArchived` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `User_id` varchar(20) NOT NULL,
  `Name` varchar(25) NOT NULL,
  `Email` varchar(25) NOT NULL,
  `Password` varchar(25) NOT NULL,
  `Department` varchar(25) NOT NULL,
  `Otp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_archive`
--
ALTER TABLE `tbl_archive`
  ADD PRIMARY KEY (`archive_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_archive`
--
ALTER TABLE `tbl_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
