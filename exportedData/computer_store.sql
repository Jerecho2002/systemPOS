-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 09:56 PM
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
-- Database: `computer_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_slug` varchar(50) DEFAULT NULL,
  `category_type` enum('pc_part','accessory') NOT NULL DEFAULT 'pc_part',
  `category_description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `supports_quantity` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_slug`, `category_type`, `category_description`, `created_at`, `updated_at`, `supports_quantity`, `is_deleted`) VALUES
(10, 'CPU', 'cpu', 'pc_part', '', '2025-10-09 22:11:00', '2026-01-27 19:21:01', 0, 0),
(11, 'GPU', 'gpu', 'pc_part', '', '2025-10-09 22:11:09', '2026-02-17 15:54:23', 0, 0),
(12, 'RAM', 'ram', 'pc_part', '', '2025-10-09 22:11:17', '2026-01-27 20:18:41', 1, 0),
(13, 'Motherboard', 'motherboard', 'pc_part', '', '2025-10-09 22:11:38', '2026-01-27 18:27:36', 0, 0),
(14, 'Storage', 'storage', 'pc_part', '', '2025-10-09 22:11:54', '2026-01-27 19:21:25', 0, 0),
(15, 'PSU', 'psu', 'pc_part', '', '2025-10-09 22:12:03', '2026-01-27 18:27:01', 0, 0),
(16, 'Case', 'case', 'pc_part', '', '2025-10-09 22:12:12', '2026-02-06 16:25:32', 0, 0),
(20, 'Mouse', 'mouse', 'accessory', '', '2026-01-27 19:23:00', '2026-01-31 06:56:55', 1, 0),
(21, 'Headset', 'headset', 'accessory', '', '2026-01-27 19:52:34', '2026-01-31 06:57:02', 1, 0),
(22, 'Cable', 'cable', 'accessory', '', '2026-01-31 08:53:13', '2026-02-09 17:24:41', 1, 0),
(23, 'CCTV', 'cctv', 'accessory', '', '2026-01-31 09:15:20', '2026-01-31 09:15:20', 1, 0),
(24, 'RAM2', 'ram2', 'pc_part', '', '2026-02-07 06:45:18', '2026-02-09 18:03:25', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `selling_price` decimal(10,2) DEFAULT 0.00,
  `quantity` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `barcode`, `item_name`, `description`, `category_id`, `supplier_id`, `cost_price`, `selling_price`, `quantity`, `min_stock`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, '8806095114060', 'Intel Core i7-13700K', '13th Gen Desktop Processor, 16 Cores, up to 5.4 GHz', 10, 1, 18500.00, 22999.00, 10, 5, '2026-01-31 06:54:31', '2026-02-23 16:39:27', 0),
(2, '4719331309038', 'ASUS ROG Strix Z790-E Gaming', 'LGA 1700 ATX Motherboard, WiFi 6E', 13, 2, 16750.00, 19999.00, 1, 3, '2026-01-31 06:54:31', '2026-02-23 20:32:37', 0),
(3, '7406172930494', 'NVIDIA RTX 4070 Ti 12GB', 'GDDR6X, PCIe 4.0, DLSS 3', 11, 3, 45500.00, 52999.00, 1, 2, '2026-01-31 06:54:31', '2026-02-23 16:39:27', 0),
(4, '8969832606721', 'Corsair Vengeance RGB 32GB', 'DDR5 6000MHz CL36 (2x16GB)', 12, 4, 6500.00, 8499.00, 29, 10, '2026-01-31 06:54:31', '2026-02-23 16:39:27', 0),
(5, '5610494622186', 'Samsung 980 Pro 2TB NVMe SSD', 'PCIe 4.0, M.2, Read 7000MB/s', 14, 5, 8500.00, 10999.00, 9, 8, '2026-01-31 06:54:31', '2026-02-23 16:39:27', 0),
(6, '3287460913547', 'Seagate Barracuda 4TB HDD', '7200 RPM, SATA 6Gb/s, 256MB Cache', 14, 6, 5200.00, 6999.00, 1, 5, '2026-01-31 06:54:31', '2026-02-23 16:39:27', 0),
(7, '6940553418623', 'Corsair RM850x 80+ Gold', '850W Fully Modular PSU', 15, 4, 6500.00, 8499.00, 15, 4, '2026-01-31 06:54:31', '2026-01-31 09:18:06', 0),
(8, '1752398046139', 'NZXT H7 Flow Black', 'Mid-Tower ATX Case, Tempered Glass', 16, 7, 5500.00, 7499.00, 9, 3, '2026-01-31 06:54:31', '2026-01-31 06:54:31', 0),
(9, '9420175863042', 'Cooler Master Hyper 212', 'CPU Air Cooler, 4 Heat Pipes', 10, 8, 1800.00, 2499.00, 29, 15, '2026-01-31 06:54:31', '2026-02-23 16:39:27', 0),
(10, '4638291570641', 'Logitech G Pro X Superlight', 'Wireless Gaming Mouse, 25K DPI', 20, 9, 4500.00, 5999.00, 20, 8, '2026-01-31 06:54:31', '2026-01-31 06:54:31', 0),
(11, '8192346507328', 'Razer BlackWidow V3', 'Mechanical Keyboard, Green Switches', 20, 10, 6200.00, 7999.00, 22, 6, '2026-01-31 06:54:31', '2026-02-09 19:51:02', 0),
(12, '5073418962153', 'ASUS TUF Gaming VG27AQ', '27\" 1440p 165Hz IPS Monitor', 16, 2, 18500.00, 22999.00, 32, 3, '2026-01-31 06:54:31', '2026-02-09 17:41:54', 0),
(13, '6829134507826', 'AMD Ryzen 9 7900X', '12-Core, 24-Threads, up to 5.6 GHz', 10, 11, 23500.00, 28999.00, 14, 2, '2026-01-31 06:54:31', '2026-02-17 15:52:30', 1),
(14, '3948571026357', 'Gigabyte B650 AORUS Elite', 'AM5 Motherboard, DDR5, PCIe 5.0', 13, 12, 12500.00, 15999.00, 11, 5, '2026-01-31 06:54:31', '2026-01-31 06:54:31', 0),
(15, '7264913085491', 'AMD Radeon RX 7800 XT 16GB', 'Navi 32, 64MB Infinity Cache', 11, 11, 36500.00, 44999.00, 16, 2, '2026-01-31 06:54:31', '2026-02-09 17:41:54', 0),
(16, '1538792640357', 'Kingston Fury Beast 16GB', 'DDR4 3200MHz CL16 (2x8GB)', 12, 13, 3200.00, 4299.00, 44, 15, '2026-01-31 06:54:31', '2026-02-23 16:39:27', 0),
(17, '8402635914872', 'WD Blue SN570 1TB NVMe SSD', 'PCIe 3.0, M.2, Read 3500MB/s', 14, 14, 3800.00, 4999.00, 20, 10, '2026-01-31 06:54:31', '2026-02-23 20:32:37', 0),
(18, '5917264083592', 'MSI MAG A850GL 850W', '80+ Gold, Fully Modular, ATX 3.0', 15, 15, 5500.00, 7499.00, 8, 4, '2026-01-31 06:54:31', '2026-01-31 06:54:31', 0),
(19, '3084926175439', 'Lian Li Lancool 216', 'Mid Tower, Mesh Front Panel', 16, 16, 4800.00, 6499.00, 13, 5, '2026-01-31 06:54:31', '2026-01-31 06:54:31', 0),
(20, '9647312850367', 'Noctua NH-D15 Chromax', 'Dual Tower CPU Cooler, 2x140mm Fans', 10, 17, 6200.00, 7999.00, 3, 3, '2026-01-31 06:54:31', '2026-02-09 19:51:02', 0),
(35, '7726281923', 'Cat-6 Indoor 20meters', 'Cable cat-6 indoor', 22, 14, 200.00, 500.00, 20, 5, '2026-01-31 08:58:43', '2026-02-04 16:33:30', 0),
(36, '214453', 'Dahua Indoor', '', 23, 14, 300.00, 600.00, 1, 3, '2026-01-31 09:16:45', '2026-02-09 19:51:02', 0);

-- --------------------------------------------------------

--
-- Table structure for table `item_stock_adjustment`
--

CREATE TABLE `item_stock_adjustment` (
  `item_stock_adjustment_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `previous_quantity` int(11) DEFAULT 0,
  `new_quantity` int(11) DEFAULT 0,
  `reason_adjustment` text NOT NULL,
  `adjust_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_stock_adjustment`
--

INSERT INTO `item_stock_adjustment` (`item_stock_adjustment_id`, `item_id`, `previous_quantity`, `new_quantity`, `reason_adjustment`, `adjust_by`, `created_at`) VALUES
(15, 15, 4, -1, 'Broken', 1, '2026-02-06 15:54:12'),
(16, 15, -1, 2, 'Found', 1, '2026-02-06 15:54:31'),
(17, 2, 4, 0, 'Lost', 1, '2026-02-06 15:54:53'),
(18, 15, 2, 4, 'Found', 1, '2026-02-06 15:57:58'),
(19, 15, 4, 6, 'Found', 1, '2026-02-06 15:58:32'),
(20, 2, 0, 1, 'asd', 1, '2026-02-09 17:40:21'),
(21, 2, 1, 0, 'dsa', 1, '2026-02-09 17:40:27'),
(22, 2, 0, 3, 'Test\r\n', 2, '2026-02-23 20:31:21');

-- --------------------------------------------------------

--
-- Table structure for table `pc_builders`
--

CREATE TABLE `pc_builders` (
  `pc_builder_id` int(11) NOT NULL,
  `pc_builder_name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pc_builders`
--

INSERT INTO `pc_builders` (`pc_builder_id`, `pc_builder_name`, `user_id`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 'Gaming Beast Pro', 1, '2026-01-31 07:05:22', '2026-01-31 07:05:22', 0),
(2, 'Budget Office PC', 1, '2026-01-31 07:05:22', '2026-01-31 07:05:22', 0),
(3, 'Streaming Workstation', 2, '2026-01-31 07:05:22', '2026-01-31 07:05:22', 0),
(4, 'Silent Editing Rig', 2, '2026-01-31 07:05:22', '2026-01-31 07:05:22', 0),
(5, 'VR Ready System', 1, '2026-01-31 07:05:22', '2026-01-31 07:05:22', 0),
(6, 'Student All-Rounder', 3, '2026-01-31 07:05:22', '2026-01-31 07:05:22', 0),
(7, 'Esports Tournament PC', 2, '2026-01-31 07:05:22', '2026-01-31 07:05:22', 0),
(8, 'Content Creation Machine', 1, '2026-01-31 07:05:22', '2026-01-31 07:05:22', 0),
(9, 'Home Server Build', 3, '2026-01-31 07:05:22', '2026-01-31 07:05:22', 0),
(10, '4K Gaming Monster', 2, '2026-01-31 07:05:22', '2026-01-31 07:05:22', 0),
(11, 'High End PC', 1, '2026-01-31 09:19:02', '2026-01-31 09:19:02', 0),
(12, 'Hey', 5, '2026-02-09 18:19:18', '2026-02-09 18:19:18', 0),
(13, 'Sample', 1, '2026-02-23 17:41:06', '2026-02-23 17:41:06', 0),
(14, 'ASDASD', 1, '2026-02-23 17:45:43', '2026-02-23 17:45:43', 0),
(15, 'gsd', 1, '2026-02-23 18:32:31', '2026-02-23 18:32:31', 0),
(16, 'Jerecho Build', 1, '2026-02-23 19:16:57', '2026-02-23 19:16:57', 0),
(17, 'hahaha', 1, '2026-02-23 19:42:26', '2026-02-23 19:42:26', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pc_builder_items`
--

CREATE TABLE `pc_builder_items` (
  `pc_builder_item_id` int(11) NOT NULL,
  `pc_builder_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pc_builder_items`
--

INSERT INTO `pc_builder_items` (`pc_builder_item_id`, `pc_builder_id`, `category_id`, `item_id`, `quantity`, `created_at`) VALUES
(1, 1, 10, 1, 1, '2026-01-31 07:06:09'),
(2, 1, 11, 3, 1, '2026-01-31 07:06:09'),
(3, 1, 12, 4, 2, '2026-01-31 07:06:09'),
(4, 1, 13, 2, 1, '2026-01-31 07:06:09'),
(5, 1, 14, 5, 1, '2026-01-31 07:06:09'),
(6, 1, 14, 6, 1, '2026-01-31 07:06:09'),
(7, 1, 15, 7, 1, '2026-01-31 07:06:09'),
(8, 1, 16, 8, 1, '2026-01-31 07:06:09'),
(9, 1, 10, 9, 1, '2026-01-31 07:06:09'),
(10, 2, 10, 13, 1, '2026-01-31 07:06:09'),
(11, 2, 11, 15, 1, '2026-01-31 07:06:09'),
(12, 2, 12, 16, 1, '2026-01-31 07:06:09'),
(13, 2, 13, 14, 1, '2026-01-31 07:06:09'),
(14, 2, 14, 17, 1, '2026-01-31 07:06:09'),
(15, 2, 15, 18, 1, '2026-01-31 07:06:09'),
(16, 2, 16, 19, 1, '2026-01-31 07:06:09'),
(17, 3, 10, 1, 1, '2026-01-31 07:06:09'),
(18, 3, 11, 3, 1, '2026-01-31 07:06:09'),
(19, 3, 12, 4, 4, '2026-01-31 07:06:09'),
(20, 3, 13, 2, 1, '2026-01-31 07:06:09'),
(21, 3, 14, 5, 2, '2026-01-31 07:06:09'),
(22, 3, 14, 6, 1, '2026-01-31 07:06:09'),
(23, 3, 15, 7, 1, '2026-01-31 07:06:09'),
(24, 3, 16, 8, 1, '2026-01-31 07:06:09'),
(25, 3, 10, 20, 1, '2026-01-31 07:06:09'),
(26, 5, 10, 13, 1, '2026-01-31 07:06:09'),
(27, 5, 11, 3, 1, '2026-01-31 07:06:09'),
(28, 5, 12, 4, 2, '2026-01-31 07:06:09'),
(29, 5, 13, 14, 1, '2026-01-31 07:06:09'),
(30, 5, 14, 5, 1, '2026-01-31 07:06:09'),
(31, 5, 15, 18, 1, '2026-01-31 07:06:09'),
(32, 5, 16, 19, 1, '2026-01-31 07:06:09'),
(33, 1, 20, 10, 1, '2026-01-31 07:06:09'),
(34, 1, 20, 11, 1, '2026-01-31 07:06:09'),
(35, 3, 20, 10, 1, '2026-01-31 07:06:09'),
(36, 5, 20, 10, 1, '2026-01-31 07:06:09'),
(37, 11, 22, 35, 1, '2026-01-31 09:19:02'),
(38, 11, 16, 8, 1, '2026-01-31 09:19:02'),
(39, 11, 23, 36, 1, '2026-01-31 09:19:02'),
(40, 11, 10, 1, 1, '2026-01-31 09:19:02'),
(41, 11, 11, 3, 1, '2026-01-31 09:19:02'),
(42, 11, 13, 2, 1, '2026-01-31 09:19:02'),
(43, 11, 20, 11, 1, '2026-01-31 09:19:02'),
(44, 11, 15, 7, 1, '2026-01-31 09:19:02'),
(45, 11, 12, 4, 1, '2026-01-31 09:19:02'),
(46, 11, 14, 5, 1, '2026-01-31 09:19:02'),
(47, 12, 22, 35, 1, '2026-02-09 18:19:18'),
(48, 12, 16, 8, 1, '2026-02-09 18:19:18'),
(49, 12, 10, 13, 1, '2026-02-09 18:19:18'),
(50, 12, 11, 15, 1, '2026-02-09 18:19:18'),
(51, 12, 13, 14, 1, '2026-02-09 18:19:18'),
(52, 12, 15, 7, 1, '2026-02-09 18:19:18'),
(53, 12, 12, 16, 1, '2026-02-09 18:19:18'),
(54, 12, 14, 5, 1, '2026-02-09 18:19:18'),
(55, 13, 22, 35, 1, '2026-02-23 17:41:06'),
(56, 13, 16, 8, 1, '2026-02-23 17:41:06'),
(57, 13, 23, 36, 1, '2026-02-23 17:41:06'),
(58, 13, 10, 1, 1, '2026-02-23 17:41:07'),
(59, 13, 11, 15, 1, '2026-02-23 17:41:07'),
(60, 13, 13, 2, 1, '2026-02-23 17:41:07'),
(61, 13, 15, 7, 1, '2026-02-23 17:41:07'),
(62, 13, 12, 16, 1, '2026-02-23 17:41:07'),
(63, 13, 14, 5, 1, '2026-02-23 17:41:07'),
(64, 14, 22, 35, 1, '2026-02-23 17:45:43'),
(65, 14, 16, 8, 1, '2026-02-23 17:45:43'),
(66, 14, 23, 36, 1, '2026-02-23 17:45:43'),
(67, 14, 10, 9, 1, '2026-02-23 17:45:43'),
(68, 14, 11, 15, 1, '2026-02-23 17:45:43'),
(69, 14, 13, 2, 1, '2026-02-23 17:45:43'),
(70, 14, 15, 7, 1, '2026-02-23 17:45:43'),
(71, 14, 12, 16, 1, '2026-02-23 17:45:43'),
(72, 14, 14, 5, 1, '2026-02-23 17:45:43'),
(73, 15, 16, 8, 1, '2026-02-23 18:32:31'),
(74, 15, 10, 9, 1, '2026-02-23 18:32:31'),
(75, 15, 11, 15, 1, '2026-02-23 18:32:31'),
(76, 15, 13, 2, 1, '2026-02-23 18:32:31'),
(77, 15, 15, 18, 1, '2026-02-23 18:32:31'),
(78, 15, 12, 16, 1, '2026-02-23 18:32:31'),
(79, 15, 14, 6, 1, '2026-02-23 18:32:31'),
(80, 16, 22, 35, 1, '2026-02-23 19:16:57'),
(81, 16, 16, 8, 1, '2026-02-23 19:16:57'),
(82, 16, 23, 36, 1, '2026-02-23 19:16:57'),
(83, 16, 10, 1, 1, '2026-02-23 19:16:57'),
(84, 16, 11, 3, 1, '2026-02-23 19:16:57'),
(85, 16, 13, 2, 1, '2026-02-23 19:16:57'),
(86, 16, 20, 10, 1, '2026-02-23 19:16:57'),
(87, 16, 15, 7, 1, '2026-02-23 19:16:57'),
(88, 16, 12, 4, 1, '2026-02-23 19:16:57'),
(89, 16, 14, 5, 1, '2026-02-23 19:16:57'),
(90, 17, 16, 8, 1, '2026-02-23 19:42:26'),
(91, 17, 10, 1, 1, '2026-02-23 19:42:26'),
(92, 17, 11, 3, 1, '2026-02-23 19:42:26'),
(93, 17, 13, 2, 1, '2026-02-23 19:42:26'),
(94, 17, 15, 7, 1, '2026-02-23 19:42:26'),
(95, 17, 12, 4, 1, '2026-02-23 19:42:26');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `purchase_order_id` int(11) NOT NULL,
  `po_number` varchar(80) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `status` enum('Ordered','Received','Cancelled') DEFAULT 'Ordered',
  `date` datetime DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`purchase_order_id`, `po_number`, `supplier_id`, `grand_total`, `status`, `date`, `created_by`, `is_deleted`) VALUES
(41, 'PO-26-01-9094', 1, 24700.00, 'Received', '2026-01-31 14:58:43', 1, 0),
(42, 'PO-26-01-2881', 3, 125000.00, 'Received', '2026-01-31 17:17:55', 1, 0),
(43, 'PO-26-02-1477', 7, 237000.00, 'Received', '2026-02-05 00:33:09', 1, 0),
(44, 'PO-26-02-8621', 5, 767000.00, 'Received', '2026-02-05 00:45:42', 1, 1),
(45, 'PO-26-02-4707', 4, 58000.00, 'Cancelled', '2026-02-05 00:46:44', 1, 0),
(46, 'PO-26-02-7488', 8, 127000.00, 'Received', '2026-02-05 00:47:01', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `purchase_order_item_id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`purchase_order_item_id`, `purchase_order_id`, `item_id`, `quantity`, `unit_cost`, `line_total`) VALUES
(53, 41, 1, 1, 18500.00, 18500.00),
(54, 41, 11, 1, 6200.00, 6200.00),
(55, 42, 7, 5, 6500.00, 32500.00),
(56, 42, 12, 5, 18500.00, 92500.00),
(57, 43, 35, 10, 200.00, 2000.00),
(58, 43, 13, 10, 23500.00, 235000.00),
(59, 44, 15, 10, 36500.00, 365000.00),
(60, 44, 16, 10, 3200.00, 32000.00),
(61, 44, 12, 20, 18500.00, 370000.00),
(62, 45, 18, 10, 5500.00, 55000.00),
(63, 45, 36, 10, 300.00, 3000.00),
(64, 46, 4, 10, 6500.00, 65000.00),
(65, 46, 11, 10, 6200.00, 62000.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `transaction_id` varchar(80) NOT NULL,
  `customer_name` varchar(80) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `cash_received` decimal(10,2) NOT NULL,
  `cash_change` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Credit Card','Gcash','') NOT NULL,
  `date` datetime NOT NULL,
  `sold_by` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `transaction_id`, `customer_name`, `grand_total`, `cash_received`, `cash_change`, `payment_method`, `date`, `sold_by`, `is_deleted`) VALUES
(79, 'TXN-2601-2044', 'walk in', 22999.00, 22999.00, 0.00, 'Cash', '2026-01-31 14:56:28', 1, 0),
(80, 'TXN-2601-7337', 'walk in', 45998.00, 45998.00, 0.00, 'Cash', '2026-01-31 17:14:10', 1, 0),
(81, 'TXN-2601-2179', 'walk in', 2400.00, 2400.00, 0.00, 'Cash', '2026-01-31 17:17:20', 1, 0),
(82, 'TXN-2602-5905', 'walk in', 19999.00, 19999.00, 0.00, 'Cash', '2026-02-04 14:19:05', 1, 0),
(83, 'TXN-2602-3514', 'walk in', 46995.00, 46995.00, 0.00, 'Cash', '2026-02-04 14:20:39', 1, 0),
(85, 'TXN-2602-9745', 'walk in', 86996.00, 86996.00, 0.00, 'Cash', '2026-02-04 15:01:56', 1, 0),
(86, 'TXN-2602-4158', 'walk in', 19999.00, 19999.00, 0.00, 'Cash', '2026-02-04 15:21:03', 1, 1),
(87, 'TXN-2602-9194', 'walk in', 6999.00, 6999.00, 0.00, 'Cash', '2026-02-04 15:21:06', 1, 0),
(88, 'TXN-2602-1778', 'walk in', 8499.00, 8499.00, 0.00, 'Cash', '2026-02-04 15:21:09', 1, 0),
(89, 'TXN-2602-4442', 'walk in', 19999.00, 19999.00, 0.00, 'Cash', '2026-02-04 15:21:12', 1, 0),
(90, 'TXN-2602-3713', 'walk in', 10999.00, 10999.00, 0.00, 'Cash', '2026-02-04 15:21:15', 1, 0),
(91, 'TXN-2602-6163', 'walk in', 22999.00, 22999.00, 0.00, 'Cash', '2026-02-04 15:21:20', 1, 0),
(92, 'TXN-2602-7489', 'walk in', 10999.00, 10999.00, 0.00, 'Cash', '2026-02-04 15:21:23', 1, 0),
(93, 'TXN-2602-4815', 'Jerecho', 52999.00, 52999.00, 0.00, 'Cash', '2026-02-10 01:45:25', 1, 0),
(94, 'TXN-2602-2257', 'walk in', 211082.00, 211082.00, 0.00, 'Cash', '2026-02-10 03:51:02', 1, 0),
(95, 'TXN-2602-2799', 'walk in', 81998.00, 81998.00, 0.00, 'Cash', '2026-02-17 23:52:30', 1, 0),
(96, 'TXN-2602-5561', 'walk in', 114292.00, 114292.00, 0.00, 'Cash', '2026-02-24 00:39:27', 1, 0),
(97, 'TXN-2602-3688', 'Jerecho', 44997.00, 44997.00, 0.00, 'Credit Card', '2026-02-24 04:32:37', 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `sale_item_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `line_total` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`sale_item_id`, `sale_id`, `item_id`, `quantity`, `unit_price`, `line_total`, `created_at`) VALUES
(64, 79, 1, 1, 22999.00, 22999.00, '2026-01-31 06:56:28'),
(65, 80, 1, 2, 22999.00, 45998.00, '2026-01-31 09:14:10'),
(66, 81, 36, 4, 600.00, 2400.00, '2026-01-31 09:17:20'),
(67, 82, 2, 1, 19999.00, 19999.00, '2026-02-04 06:19:05'),
(68, 83, 5, 3, 10999.00, 32997.00, '2026-02-04 06:20:39'),
(69, 83, 6, 2, 6999.00, 13998.00, '2026-02-04 06:20:39'),
(72, 85, 2, 1, 19999.00, 19999.00, '2026-02-04 07:01:56'),
(73, 85, 3, 1, 52999.00, 52999.00, '2026-02-04 07:01:56'),
(74, 85, 6, 2, 6999.00, 13998.00, '2026-02-04 07:01:56'),
(75, 86, 2, 1, 19999.00, 19999.00, '2026-02-04 07:21:03'),
(76, 87, 6, 1, 6999.00, 6999.00, '2026-02-04 07:21:06'),
(77, 88, 4, 1, 8499.00, 8499.00, '2026-02-04 07:21:09'),
(78, 89, 2, 1, 19999.00, 19999.00, '2026-02-04 07:21:12'),
(79, 90, 5, 1, 10999.00, 10999.00, '2026-02-04 07:21:15'),
(80, 91, 1, 1, 22999.00, 22999.00, '2026-02-04 07:21:20'),
(81, 92, 5, 1, 10999.00, 10999.00, '2026-02-04 07:21:23'),
(82, 93, 3, 1, 52999.00, 52999.00, '2026-02-09 17:45:25'),
(83, 94, 3, 1, 52999.00, 52999.00, '2026-02-09 19:51:02'),
(84, 94, 1, 1, 22999.00, 22999.00, '2026-02-09 19:51:02'),
(85, 94, 6, 4, 6999.00, 27996.00, '2026-02-09 19:51:02'),
(86, 94, 5, 3, 10999.00, 32997.00, '2026-02-09 19:51:02'),
(87, 94, 4, 3, 8499.00, 25497.00, '2026-02-09 19:51:02'),
(88, 94, 11, 3, 7999.00, 23997.00, '2026-02-09 19:51:02'),
(89, 94, 20, 3, 7999.00, 23997.00, '2026-02-09 19:51:02'),
(90, 94, 36, 1, 600.00, 600.00, '2026-02-09 19:51:02'),
(91, 95, 13, 1, 28999.00, 28999.00, '2026-02-17 15:52:30'),
(92, 95, 3, 1, 52999.00, 52999.00, '2026-02-17 15:52:30'),
(93, 96, 1, 1, 22999.00, 22999.00, '2026-02-23 16:39:27'),
(94, 96, 3, 1, 52999.00, 52999.00, '2026-02-23 16:39:27'),
(95, 96, 6, 1, 6999.00, 6999.00, '2026-02-23 16:39:27'),
(96, 96, 5, 1, 10999.00, 10999.00, '2026-02-23 16:39:27'),
(97, 96, 4, 1, 8499.00, 8499.00, '2026-02-23 16:39:27'),
(98, 96, 9, 1, 2499.00, 2499.00, '2026-02-23 16:39:27'),
(99, 96, 17, 1, 4999.00, 4999.00, '2026-02-23 16:39:27'),
(100, 96, 16, 1, 4299.00, 4299.00, '2026-02-23 16:39:27'),
(101, 97, 17, 1, 4999.00, 4999.00, '2026-02-23 20:32:37'),
(102, 97, 2, 2, 19999.00, 39998.00, '2026-02-23 20:32:37');

-- --------------------------------------------------------

--
-- Table structure for table `sale_pc_builders`
--

CREATE TABLE `sale_pc_builders` (
  `sale_pc_builder_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `pc_builder_id` int(11) NOT NULL,
  `pc_builder_name` varchar(100) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_pc_builder_items`
--

CREATE TABLE `sale_pc_builder_items` (
  `sale_pc_builder_item_id` int(11) NOT NULL,
  `sale_pc_builder_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_number`, `email`, `status`, `is_deleted`) VALUES
(1, 'Intel Philippines', '+639171234567', 'ph.sales@intel.com', 1, 0),
(2, 'ASUS Philippines', '+639172345678', 'ph.sales@asus.com', 1, 0),
(3, 'NVIDIA Philippines', '+639173456789', 'ph.sales@nvidia.com', 1, 0),
(4, 'Corsair Philippines', '+639174567890', 'ph.sales@corsair.com', 1, 0),
(5, 'Samsung Philippines', '+639175678901', 'ph.sales@samsung.com', 1, 0),
(6, 'Seagate Philippines', '+639176789012', 'ph.sales@seagate.com', 1, 0),
(7, 'NZXT Philippines', '+639177890123', 'ph.sales@nzxt.com', 1, 0),
(8, 'Cooler Master PH', '+639178901234', 'ph.sales@coolermaster.com', 1, 0),
(9, 'Logitech Philippines', '+639179012345', 'ph.sales@logitech.com', 1, 0),
(10, 'Razer Philippines', '+639180123456', 'ph.sales@razer.com', 1, 0),
(11, 'AMD Philippines', '+639181234567', 'ph.sales@amd.com', 1, 0),
(12, 'Gigabyte Philippines', '+639182345678', 'ph.sales@gigabyte.com', 1, 0),
(13, 'Kingston Philippines', '+639183456789', 'ph.sales@kingston.com', 1, 0),
(14, 'WD Philippines', '+639184567890', 'ph.sales@wdc.com', 1, 0),
(15, 'MSI Philippines', '+639185678901', 'ph.sales@msi.com', 1, 0),
(16, 'Lian Li Philippines', '+639186789012', 'ph.sales@lian-li.com', 1, 0),
(17, 'Noctua Philippines', '+639187890123', 'ph.sales@noctua.at', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `is_active`) VALUES
(1, 'Jerecho', '$2y$10$3U6KZ6tHo1zk3FXS3DRunejRBf3/WlXYNPjsZmFIeLxFMmTu4xEtW', 'admin', 1),
(2, 'Admin', '$2y$10$4tpLaZJ74qvfT.zkmEGHYuhT5q3B7rYRj0ZszObqvLUVeOK4tllvS', 'staff', 1),
(3, 'Staff', '$2y$10$AAoF1JxX8.N.wLGsPEJ2guN7sfbNCu4XaiM/8FCXMSUSxT27mEQbe', 'staff', 1),
(4, 'sample', '$2y$10$2pUChB75nRl7X9QJ7y2TBubBIfy3598CROurW.dumlVw25JEpcZBC', 'staff', 1),
(5, 'echo', '$2y$10$VCZA/LV2nog82wFJiTFlruO14iT3gjHFCV9F7Hyes5CLDb8WZ/JgG', 'staff', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_slug` (`category_slug`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `item_stock_adjustment`
--
ALTER TABLE `item_stock_adjustment`
  ADD PRIMARY KEY (`item_stock_adjustment_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `adjust_by` (`adjust_by`);

--
-- Indexes for table `pc_builders`
--
ALTER TABLE `pc_builders`
  ADD PRIMARY KEY (`pc_builder_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pc_builder_items`
--
ALTER TABLE `pc_builder_items`
  ADD PRIMARY KEY (`pc_builder_item_id`),
  ADD KEY `pc_builder_id` (`pc_builder_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`purchase_order_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`purchase_order_item_id`),
  ADD KEY `purchase_id` (`purchase_order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `sold_by` (`sold_by`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`sale_item_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `sale_pc_builders`
--
ALTER TABLE `sale_pc_builders`
  ADD PRIMARY KEY (`sale_pc_builder_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `pc_builder_id` (`pc_builder_id`);

--
-- Indexes for table `sale_pc_builder_items`
--
ALTER TABLE `sale_pc_builder_items`
  ADD PRIMARY KEY (`sale_pc_builder_item_id`),
  ADD KEY `sale_pc_builder_id` (`sale_pc_builder_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `item_stock_adjustment`
--
ALTER TABLE `item_stock_adjustment`
  MODIFY `item_stock_adjustment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `pc_builders`
--
ALTER TABLE `pc_builders`
  MODIFY `pc_builder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `pc_builder_items`
--
ALTER TABLE `pc_builder_items`
  MODIFY `pc_builder_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `purchase_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `purchase_order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `sale_pc_builders`
--
ALTER TABLE `sale_pc_builders`
  MODIFY `sale_pc_builder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `sale_pc_builder_items`
--
ALTER TABLE `sale_pc_builder_items`
  MODIFY `sale_pc_builder_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL;

--
-- Constraints for table `item_stock_adjustment`
--
ALTER TABLE `item_stock_adjustment`
  ADD CONSTRAINT `item_stock_adjustment_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_stock_adjustment_ibfk_2` FOREIGN KEY (`adjust_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pc_builders`
--
ALTER TABLE `pc_builders`
  ADD CONSTRAINT `pc_builders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pc_builder_items`
--
ALTER TABLE `pc_builder_items`
  ADD CONSTRAINT `pc_builder_items_ibfk_1` FOREIGN KEY (`pc_builder_id`) REFERENCES `pc_builders` (`pc_builder_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pc_builder_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `pc_builder_items_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`purchase_order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`sold_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sale_pc_builders`
--
ALTER TABLE `sale_pc_builders`
  ADD CONSTRAINT `sale_pc_builders_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sale_pc_builders_ibfk_2` FOREIGN KEY (`pc_builder_id`) REFERENCES `pc_builders` (`pc_builder_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sale_pc_builder_items`
--
ALTER TABLE `sale_pc_builder_items`
  ADD CONSTRAINT `sale_pc_builder_items_ibfk_1` FOREIGN KEY (`sale_pc_builder_id`) REFERENCES `sale_pc_builders` (`sale_pc_builder_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sale_pc_builder_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
