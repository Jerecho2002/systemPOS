-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 01, 2026 at 05:11 AM
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
(16, 'Case', 'case', 'pc_part', '', '2025-10-09 22:12:12', '2026-02-26 02:58:04', 0, 0),
(20, 'Mouse', 'mouse', 'accessory', '', '2026-01-27 19:23:00', '2026-02-28 17:51:38', 0, 0),
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
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `barcode`, `item_name`, `description`, `category_id`, `supplier_id`, `cost_price`, `selling_price`, `quantity`, `min_stock`, `image`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, '8806095114060', 'Intel Core i7-13700K', '13th Gen Desktop Processor, 16 Cores, up to 5.4 GHz', 10, 1, 18500.00, 22999.00, 9, 5, '', '2026-01-31 06:54:31', '2026-02-28 12:41:18', 0),
(2, '4719331309038', 'ASUS ROG Strix Z790-E Gaming', 'LGA 1700 ATX Motherboard, WiFi 6E', 13, 2, 16750.00, 19999.00, 17, 3, '', '2026-01-31 06:54:31', '2026-02-28 12:41:18', 0),
(3, '7406172930494', 'NVIDIA RTX 4070 Ti 12GB', 'GDDR6X, PCIe 4.0, DLSS 3', 11, 3, 45500.00, 52999.00, 2, 2, '', '2026-01-31 06:54:31', '2026-02-28 12:41:18', 0),
(4, '8969832606721', 'Corsair Vengeance RGB 32GB', 'DDR5 6000MHz CL36 (2x16GB)', 12, 4, 6500.00, 8499.00, 21, 10, '', '2026-01-31 06:54:31', '2026-02-28 12:41:18', 0),
(5, '5610494622186', 'Samsung 980 Pro 2TB NVMe SSD', 'PCIe 4.0, M.2, Read 7000MB/s', 14, 5, 8500.00, 10999.00, 6, 8, '', '2026-01-31 06:54:31', '2026-02-28 12:41:18', 0),
(6, '3287460913547', 'Seagate Barracuda 4TB HDD', '7200 RPM, SATA 6Gb/s, 256MB Cache', 14, 6, 5200.00, 6999.00, 2, 5, '', '2026-01-31 06:54:31', '2026-02-28 07:35:09', 0),
(7, '6940553418623', 'Corsair RM850x 80+ Gold', '850W Fully Modular PSU', 15, 4, 6500.00, 8499.00, 14, 4, '', '2026-01-31 06:54:31', '2026-02-28 12:41:18', 0),
(8, '1752398046139', 'NZXT H7 Flow Black', 'Mid-Tower ATX Case, Tempered Glass', 16, 7, 5500.00, 8499.00, 3, 3, '', '2026-01-31 06:54:31', '2026-02-28 12:41:18', 0),
(9, '9420175863042', 'Cooler Master Hyper 212', 'CPU Air Cooler, 4 Heat Pipes', 10, 8, 1800.00, 2499.00, 29, 15, '', '2026-01-31 06:54:31', '2026-02-23 16:39:27', 0),
(10, '4638291570641', 'Logitech G Pro X Superlight', 'Wireless Gaming Mouse, 25K DPI', 20, 9, 4500.00, 5999.00, 29, 8, '', '2026-01-31 06:54:31', '2026-02-28 12:41:18', 0),
(11, '8192346507328', 'Razer BlackWidow V3', 'Mechanical Keyboard, Green Switches', 20, 10, 6200.00, 7999.00, 32, 6, '', '2026-01-31 06:54:31', '2026-02-26 09:57:07', 0),
(12, '5073418962153', 'ASUS TUF Gaming VG27AQ', '27\" 1440p 165Hz IPS Monitor', 16, 2, 18500.00, 22999.00, 32, 3, '', '2026-01-31 06:54:31', '2026-02-09 17:41:54', 0),
(13, '6829134507826', 'AMD Ryzen 9 7900X', '12-Core, 24-Threads, up to 5.6 GHz', 10, 11, 23500.00, 28999.00, 24, 2, '', '2026-01-31 06:54:31', '2026-02-28 07:46:09', 0),
(14, '3948571026357', 'Gigabyte B650 AORUS Elite', 'AM5 Motherboard, DDR5, PCIe 5.0', 13, 12, 12500.00, 15999.00, 11, 5, '', '2026-01-31 06:54:31', '2026-01-31 06:54:31', 0),
(15, '7264913085491', 'AMD Radeon RX 7800 XT 16GB', 'Navi 32, 64MB Infinity Cache', 11, 11, 36500.00, 44999.00, 0, 2, '', '2026-01-31 06:54:31', '2026-02-25 20:38:58', 0),
(16, '1538792640357', 'Kingston Fury Beast 16GB', 'DDR4 3200MHz CL16 (2x8GB)', 12, 13, 3200.00, 4299.00, 54, 15, '', '2026-01-31 06:54:31', '2026-02-28 07:46:09', 0),
(17, '8402635914872', 'WD Blue SN570 1TB NVMe SSD', 'PCIe 3.0, M.2, Read 3500MB/s', 14, 14, 3800.00, 4999.00, 20, 10, '', '2026-01-31 06:54:31', '2026-02-23 20:32:37', 0),
(18, '5917264083592', 'MSI MAG A850GL 850W', '80+ Gold, Fully Modular, ATX 3.0', 15, 15, 5500.00, 7499.00, 8, 4, '', '2026-01-31 06:54:31', '2026-01-31 06:54:31', 0),
(19, '3084926175439', 'Lian Li Lancool 216', 'Mid Tower, Mesh Front Panel', 16, 16, 4800.00, 6499.00, 13, 5, '', '2026-01-31 06:54:31', '2026-01-31 06:54:31', 0),
(20, '9647312850367', 'Noctua NH-D15 Chromax', 'Dual Tower CPU Cooler, 2x140mm Fans', 10, 17, 6200.00, 7999.00, 23, 3, '', '2026-01-31 06:54:31', '2026-02-26 03:04:29', 0),
(36, '214453', 'Dahua Indoors', 'Sample Description', 23, 14, 400.00, 600.00, 0, 3, '', '2026-01-31 09:16:45', '2026-02-28 07:32:42', 0),
(39, '21313', 'Random Item', 'ASD', 11, 1, 1000.00, 2000.00, 20, 5, '', '2026-02-26 04:59:33', '2026-02-28 07:59:22', 0),
(40, '988753243', 'Intel i5-10400', 'Intel Core i5 New Version of Intel Core Processor', 10, 16, 5000.00, 10000.00, 15, 5, 'intel-i5.jpg', '2026-02-26 17:30:57', '2026-02-28 11:44:01', 0),
(41, '7416542', 'Intel i7-10700', 'Intel Core i7 New Version of Intel Core Processor', 10, 13, 8000.00, 12000.00, 29, 5, 'icon.png', '2026-02-26 17:32:54', '2026-02-28 10:03:13', 0),
(42, '54465465', 'sampleItem', 'asdad', 10, 14, 1000.00, 2000.00, 10, 5, 'icon1.png', '2026-02-28 09:11:51', '2026-02-28 10:02:12', 1);

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
(30, 6, 0, 1, 'Found Item', 8, '2026-02-25 20:16:23'),
(31, 15, 1, -1, 'Broken Items', 8, '2026-02-25 20:17:14'),
(32, 15, -1, 1, 'Found Items', 8, '2026-02-25 20:33:58'),
(33, 15, 1, 0, 'dsa', 8, '2026-02-25 20:36:42'),
(34, 15, 0, 2, 'Found', 8, '2026-02-25 20:37:57'),
(35, 15, 2, 0, 'lost', 8, '2026-02-25 20:38:58'),
(36, 5, 8, 7, 'Broken item', 8, '2026-02-26 06:00:53'),
(37, 36, 1, 0, 'Damaged', 8, '2026-02-28 07:32:42'),
(38, 6, 0, 2, 'Found', 8, '2026-02-28 07:35:09');

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
(18, 'Sample Quotation', 10, '2026-02-25 21:12:51', '2026-02-25 21:12:51', 0),
(19, 'Gaming PC-1', 8, '2026-02-26 06:04:20', '2026-02-26 06:04:20', 0),
(20, 'Sample PC-2', 8, '2026-02-26 06:20:20', '2026-02-26 06:20:20', 0),
(21, 'Sample PC-3', 8, '2026-02-26 09:57:51', '2026-02-26 09:57:51', 0),
(25, 'asdasd', 8, '2026-02-27 21:48:44', '2026-02-27 21:48:44', 0),
(27, 'So good pc', 8, '2026-02-27 21:52:14', '2026-02-27 21:52:14', 0),
(28, 'hahah', 8, '2026-02-28 16:31:46', '2026-02-28 16:31:46', 0),
(29, 'hehe', 8, '2026-02-28 16:32:12', '2026-02-28 16:32:12', 0),
(30, 'Latest Gaming Sheesh', 8, '2026-02-28 16:55:31', '2026-02-28 16:55:31', 0),
(31, 'asdasdasasdasda', 8, '2026-02-28 16:57:02', '2026-02-28 16:57:02', 0);

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
(96, 18, 16, 8, 1, '2026-02-25 21:12:51'),
(97, 18, 23, 36, 1, '2026-02-25 21:12:51'),
(98, 18, 10, 1, 1, '2026-02-25 21:12:51'),
(99, 18, 11, 3, 1, '2026-02-25 21:12:51'),
(100, 18, 13, 2, 1, '2026-02-25 21:12:51'),
(101, 18, 20, 11, 1, '2026-02-25 21:12:51'),
(102, 18, 15, 7, 1, '2026-02-25 21:12:51'),
(103, 18, 12, 4, 1, '2026-02-25 21:12:51'),
(104, 18, 14, 6, 1, '2026-02-25 21:12:51'),
(105, 19, 16, 8, 1, '2026-02-26 06:04:20'),
(106, 19, 10, 9, 1, '2026-02-26 06:04:20'),
(107, 19, 11, 39, 1, '2026-02-26 06:04:20'),
(108, 19, 13, 2, 1, '2026-02-26 06:04:20'),
(109, 19, 20, 10, 1, '2026-02-26 06:04:20'),
(110, 19, 15, 7, 1, '2026-02-26 06:04:20'),
(111, 19, 12, 4, 1, '2026-02-26 06:04:20'),
(112, 19, 14, 5, 1, '2026-02-26 06:04:20'),
(113, 20, 16, 8, 1, '2026-02-26 06:20:20'),
(114, 20, 10, 1, 1, '2026-02-26 06:20:20'),
(115, 20, 11, 3, 1, '2026-02-26 06:20:20'),
(116, 20, 13, 2, 1, '2026-02-26 06:20:20'),
(117, 20, 20, 10, 1, '2026-02-26 06:20:20'),
(118, 20, 15, 7, 1, '2026-02-26 06:20:20'),
(119, 20, 12, 4, 1, '2026-02-26 06:20:20'),
(120, 20, 14, 5, 1, '2026-02-26 06:20:20'),
(121, 21, 16, 8, 1, '2026-02-26 09:57:51'),
(122, 21, 10, 9, 1, '2026-02-26 09:57:51'),
(123, 21, 11, 15, 1, '2026-02-26 09:57:51'),
(124, 21, 13, 2, 1, '2026-02-26 09:57:51'),
(125, 21, 20, 10, 1, '2026-02-26 09:57:51'),
(126, 21, 15, 7, 1, '2026-02-26 09:57:51'),
(127, 21, 12, 4, 1, '2026-02-26 09:57:51'),
(128, 21, 14, 5, 1, '2026-02-26 09:57:51'),
(132, 25, 10, 41, 1, '2026-02-27 21:48:44'),
(133, 25, 23, 36, 1, '2026-02-27 21:48:44'),
(134, 25, 12, 4, 1, '2026-02-27 21:48:44'),
(139, 27, 10, 41, 1, '2026-02-27 21:52:14'),
(140, 27, 23, 36, 1, '2026-02-27 21:52:14'),
(141, 27, 12, 4, 1, '2026-02-27 21:52:14'),
(142, 27, 16, 8, 1, '2026-02-27 21:52:14'),
(143, 27, 15, 7, 3, '2026-02-27 21:52:14'),
(144, 29, 16, 19, 1, '2026-02-28 16:32:12'),
(145, 29, 23, 36, 1, '2026-02-28 16:32:12'),
(146, 29, 10, 1, 1, '2026-02-28 16:32:12'),
(147, 29, 11, 3, 1, '2026-02-28 16:32:12'),
(148, 29, 13, 2, 1, '2026-02-28 16:32:12'),
(149, 29, 15, 7, 1, '2026-02-28 16:32:12'),
(150, 29, 12, 4, 1, '2026-02-28 16:32:12'),
(151, 29, 14, 5, 1, '2026-02-28 16:32:12'),
(152, 30, 16, 12, 1, '2026-02-28 16:55:31'),
(153, 30, 10, 40, 1, '2026-02-28 16:55:31'),
(154, 30, 11, 39, 1, '2026-02-28 16:55:31'),
(155, 30, 13, 14, 1, '2026-02-28 16:55:31'),
(156, 30, 20, 11, 1, '2026-02-28 16:55:31'),
(157, 30, 15, 7, 1, '2026-02-28 16:55:31'),
(158, 30, 12, 16, 1, '2026-02-28 16:55:31'),
(159, 30, 14, 6, 1, '2026-02-28 16:55:31'),
(160, 31, 16, 19, 1, '2026-02-28 16:57:02'),
(161, 31, 23, 36, 1, '2026-02-28 16:57:02'),
(162, 31, 10, 13, 1, '2026-02-28 16:57:02'),
(163, 31, 11, 3, 1, '2026-02-28 16:57:02'),
(164, 31, 13, 14, 1, '2026-02-28 16:57:02'),
(165, 31, 15, 18, 1, '2026-02-28 16:57:02'),
(166, 31, 12, 16, 100, '2026-02-28 16:57:02'),
(167, 31, 14, 17, 1, '2026-02-28 16:57:02');

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
(47, 'PO-26-02-6488', 3, 459000.00, 'Received', '2026-02-26 11:03:57', 8, 0),
(48, 'PO-26-02-6051', 1, 179000.00, 'Cancelled', '2026-02-26 11:04:19', 8, 0),
(49, 'PO-26-02-2016', 4, 107000.00, 'Ordered', '2026-02-26 11:04:52', 8, 0),
(50, 'PO-26-02-8682', 7, 185000.00, 'Ordered', '2026-02-26 13:00:57', 8, 0),
(51, 'PO-26-02-5355', 4, 115000.00, 'Ordered', '2026-02-26 14:02:12', 8, 0),
(52, 'PO-26-02-9986', 4, 124000.00, 'Cancelled', '2026-02-26 14:19:16', 8, 0),
(53, 'PO-26-02-4876', 7, 107000.00, 'Received', '2026-02-26 17:56:54', 8, 0),
(54, 'PO-26-02-2042', 2, 267000.00, 'Received', '2026-02-28 15:45:24', 8, 0);

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
(66, 47, 2, 20, 16750.00, 335000.00),
(67, 47, 20, 20, 6200.00, 124000.00),
(68, 48, 6, 10, 5200.00, 52000.00),
(69, 48, 11, 10, 6200.00, 62000.00),
(70, 48, 7, 10, 6500.00, 65000.00),
(71, 49, 11, 10, 6200.00, 62000.00),
(72, 49, 10, 10, 4500.00, 45000.00),
(73, 50, 12, 10, 18500.00, 185000.00),
(74, 51, 10, 5, 4500.00, 22500.00),
(75, 51, 12, 5, 18500.00, 92500.00),
(76, 52, 11, 10, 6200.00, 62000.00),
(77, 52, 11, 10, 6200.00, 62000.00),
(78, 53, 10, 10, 4500.00, 45000.00),
(79, 53, 11, 10, 6200.00, 62000.00),
(80, 54, 13, 10, 23500.00, 235000.00),
(81, 54, 16, 10, 3200.00, 32000.00);

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
  `ref_number` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL,
  `sold_by` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `transaction_id`, `customer_name`, `grand_total`, `cash_received`, `cash_change`, `payment_method`, `ref_number`, `date`, `sold_by`, `is_deleted`) VALUES
(99, 'TXN-2602-5363', 'walk in', 68497.00, 68497.00, 0.00, 'Cash', NULL, '2026-02-26 03:44:05', 10, 0),
(100, 'TXN-2602-9704', 'walk in', 10999.00, 10999.00, 0.00, 'Cash', NULL, '2026-02-26 12:35:25', 8, 0),
(101, 'TXN-2602-4614', 'walk in', 52999.00, 52999.00, 0.00, 'Cash', NULL, '2026-02-26 12:47:27', 8, 0),
(102, 'TXN-2602-9174', 'walk in', 158997.00, 158997.00, 0.00, 'Cash', NULL, '2026-02-26 13:56:58', 8, 0),
(103, 'TXN-2602-9157', 'walk in', 264995.00, 264995.00, 0.00, 'Gcash', NULL, '2026-02-26 14:16:17', 8, 0),
(104, 'TXN-2602-2328', 'Jacky', 218995.00, 218995.00, 0.00, 'Gcash', NULL, '2026-02-26 17:54:13', 8, 0),
(105, 'TXN-2602-6037', 'walk in', 12000.00, 12000.00, 0.00, 'Cash', NULL, '2026-02-27 01:35:34', 8, 0),
(106, 'TXN-2602-1254', 'walk in', 211996.00, 211996.00, 0.00, 'Gcash', NULL, '2026-02-27 02:24:18', 8, 0),
(107, 'TXN-2602-5082', 'walk in', 52999.00, 52999.00, 0.00, 'Cash', NULL, '2026-02-27 02:27:26', 8, 0),
(108, 'TXN-2602-6828', 'walk in', 8499.00, 8499.00, 0.00, 'Cash', NULL, '2026-02-27 02:52:39', 8, 0),
(109, 'TXN-2602-8907', 'walk in', 8499.00, 8499.00, 0.00, 'Credit Card', '4655324', '2026-02-27 02:52:52', 8, 0),
(110, 'TXN-2602-3747', 'walk in', 8499.00, 8500.00, 1.00, 'Cash', NULL, '2026-02-27 03:22:16', 8, 0),
(111, 'TXN-2602-4396', 'walk in', 8499.00, 8499.00, 0.00, 'Cash', NULL, '2026-02-27 04:48:07', 8, 0),
(112, 'TXN-2602-3482', 'walk in', 600.00, 600.00, 0.00, 'Cash', NULL, '2026-02-27 04:48:16', 8, 0),
(113, 'TXN-2602-9723', 'walk in', 500.00, 2000.00, 1500.00, 'Cash', NULL, '2026-02-27 05:09:25', 8, 0),
(114, 'TXN-2602-2492', 'walk in', 1000.00, 2000.00, 1000.00, 'Cash', NULL, '2026-02-27 05:09:51', 8, 0),
(115, 'TXN-2602-4096', 'walk in', 600.00, 1800.00, 1200.00, 'Cash', NULL, '2026-02-27 05:15:42', 8, 0),
(116, 'TXN-2602-3203', 'walk in', 1200.00, 1800.00, 600.00, 'Cash', NULL, '2026-02-27 05:16:04', 8, 0),
(117, 'TXN-2602-9180', 'walk in', 20000.00, 50000.00, 30000.00, 'Cash', NULL, '2026-02-27 05:16:35', 8, 0),
(118, 'TXN-2602-9958', 'walk in', 16998.00, 16998.00, 0.00, 'Cash', NULL, '2026-02-27 05:22:49', 8, 0),
(119, 'TXN-2602-6478', 'walk in', 1500.00, 1500.00, 0.00, 'Cash', NULL, '2026-02-28 05:24:43', 8, 0),
(120, 'TXN-2602-1973', 'walk in', 89997.00, 89997.00, 0.00, 'Credit Card', 'A62S5D-63A5-S5XS', '2026-02-28 18:12:12', 8, 0),
(121, 'TXN-2602-5859', 'walk in', 69997.00, 69997.00, 0.00, 'Gcash', '6654-32465-5456', '2026-02-28 19:13:56', 8, 0),
(122, 'TXN-2602-1809', 'Jerecho', 155490.00, 155490.00, 0.00, 'Cash', NULL, '2026-02-28 20:41:18', 8, 0);

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
  `custom_unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`sale_item_id`, `sale_id`, `item_id`, `quantity`, `unit_price`, `custom_unit_price`, `line_total`, `created_at`) VALUES
(105, 99, 3, 1, 52999.00, 0.00, 52999.00, '2026-02-25 19:44:05'),
(106, 99, 4, 1, 8499.00, 0.00, 8499.00, '2026-02-25 19:44:05'),
(107, 99, 6, 1, 6999.00, 0.00, 6999.00, '2026-02-25 19:44:05'),
(108, 100, 5, 1, 10999.00, 0.00, 10999.00, '2026-02-26 04:35:25'),
(109, 101, 3, 1, 52999.00, 0.00, 52999.00, '2026-02-26 04:47:27'),
(110, 102, 3, 3, 52999.00, 0.00, 158997.00, '2026-02-26 05:56:58'),
(111, 103, 3, 5, 52999.00, 0.00, 264995.00, '2026-02-26 06:16:17'),
(112, 104, 3, 4, 52999.00, 0.00, 211996.00, '2026-02-26 09:54:13'),
(113, 104, 6, 1, 6999.00, 0.00, 6999.00, '2026-02-26 09:54:13'),
(114, 105, 41, 1, 12000.00, 0.00, 12000.00, '2026-02-26 17:35:34'),
(115, 106, 3, 4, 52999.00, 0.00, 211996.00, '2026-02-26 18:24:18'),
(116, 107, 3, 1, 52999.00, 0.00, 52999.00, '2026-02-26 18:27:26'),
(117, 108, 4, 1, 8499.00, 0.00, 8499.00, '2026-02-26 18:52:39'),
(118, 109, 4, 1, 8499.00, 0.00, 8499.00, '2026-02-26 18:52:52'),
(119, 110, 4, 1, 8499.00, 0.00, 8499.00, '2026-02-26 19:22:16'),
(120, 111, 8, 1, 8499.00, 0.00, 8499.00, '2026-02-26 20:48:07'),
(121, 112, 36, 1, 600.00, 0.00, 600.00, '2026-02-26 20:48:16'),
(124, 115, 36, 1, 600.00, 0.00, 600.00, '2026-02-26 21:15:42'),
(125, 116, 36, 2, 600.00, 0.00, 1200.00, '2026-02-26 21:16:04'),
(126, 117, 40, 2, 10000.00, 0.00, 20000.00, '2026-02-26 21:16:35'),
(127, 118, 4, 2, 8499.00, 0.00, 16998.00, '2026-02-26 21:22:49'),
(129, 120, 40, 3, 10000.00, 0.00, 30000.00, '2026-02-28 10:12:12'),
(130, 120, 2, 3, 19999.00, 0.00, 59997.00, '2026-02-28 10:12:12'),
(131, 121, 4, 1, 8499.00, 0.00, 8499.00, '2026-02-28 11:13:56'),
(132, 121, 8, 1, 8499.00, 0.00, 8499.00, '2026-02-28 11:13:56'),
(133, 121, 3, 1, 52999.00, 0.00, 52999.00, '2026-02-28 11:13:56'),
(134, 122, 8, 3, 8499.00, 0.00, 25497.00, '2026-02-28 12:41:18'),
(135, 122, 1, 1, 22999.00, 0.00, 22999.00, '2026-02-28 12:41:18'),
(136, 122, 3, 1, 52999.00, 0.00, 52999.00, '2026-02-28 12:41:18'),
(137, 122, 2, 1, 19999.00, 0.00, 19999.00, '2026-02-28 12:41:18'),
(138, 122, 10, 1, 5999.00, 0.00, 5999.00, '2026-02-28 12:41:18'),
(139, 122, 7, 1, 8499.00, 0.00, 8499.00, '2026-02-28 12:41:18'),
(140, 122, 4, 1, 8499.00, 0.00, 8499.00, '2026-02-28 12:41:18'),
(141, 122, 5, 1, 10999.00, 0.00, 10999.00, '2026-02-28 12:41:18');

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
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(8, 'Admin', '$2y$10$DATSVpMePiG2zoRn4xHIY.pTiR9ZGBFaW.EaWVY.OYxnW9a9UPT.e', 'admin', 1, '2026-02-26 05:03:00', '2026-02-26 05:03:40'),
(9, 'Jacaban', '$2y$10$MHu9Nwm9N4230q/UVEK7Nuvtgy2H4lr8Ie/fCbi7gJcOw9WVRYMS6', 'staff', 1, '2026-02-26 05:03:00', '2026-02-26 05:03:40'),
(10, 'Jerecho', '$2y$10$TTux6O8rrSEk6UY8elcJLe3bhJSU2J3NK18hCJl/wapQ/2ljBikLy', 'staff', 1, '2026-02-26 05:03:00', '2026-02-26 05:03:40'),
(13, 'Jacky', '$2y$10$R.NvSo/bnN7Flot6d4s7nulHW1liZ8iSsAvL6TApVKuX5BzdI6hcC', 'staff', 1, '2026-02-26 05:03:00', '2026-02-26 05:03:40'),
(14, 'Panfilo', '$2y$10$jmXcdzZ6GyX9FDovu6qY9ee/9p0Rv72.aY5uPnMys/3y39O3asz9S', 'staff', 1, '2026-02-26 05:03:00', '2026-02-26 05:03:40'),
(15, 'Shantal', '$2y$10$KEIjXkuovqycCrTKK6.s4OyQ7d8bbE8xq..joIvcDyVOYF9U2DQ6G', 'staff', 1, '2026-02-26 05:03:00', '2026-02-26 05:03:40'),
(16, 'Boyet', '$2y$10$QnNSvl34uTuLLV3cGTWzueDc3qASI7DBiDsRj30.Cm9mRrLTcPAee', 'staff', 1, '2026-02-26 05:03:00', '2026-02-26 05:03:40'),
(17, 'Zacks', '$2y$10$LPj2/DGUymk4HdO/KSmzwe9H9w7thKrUGqHvCmmBjPQxYeq8mN856', 'staff', 0, '2026-02-26 05:04:21', '2026-02-26 09:58:47'),
(19, 'NewStaff', '$2y$10$GeRmeckTDip2TYTzoex56u.pyKw4vjV4Q.2j9eJ6iJWvhNLPMv37.', 'staff', 1, '2026-02-26 09:59:03', '2026-02-28 08:49:13');

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `item_stock_adjustment`
--
ALTER TABLE `item_stock_adjustment`
  MODIFY `item_stock_adjustment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `pc_builders`
--
ALTER TABLE `pc_builders`
  MODIFY `pc_builder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `pc_builder_items`
--
ALTER TABLE `pc_builder_items`
  MODIFY `pc_builder_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `purchase_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `purchase_order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
