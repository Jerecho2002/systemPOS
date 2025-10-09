-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2025 at 12:45 PM
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
  `category_description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_description`, `created_at`, `updated_at`) VALUES
(10, 'CPU', '', '2025-10-09 22:11:00', '2025-10-09 06:11:00'),
(11, 'GPU', '', '2025-10-09 22:11:09', '2025-10-09 06:11:09'),
(12, 'RAM', '', '2025-10-09 22:11:17', '2025-10-09 06:11:17'),
(13, 'Motherboard', '', '2025-10-09 22:11:38', '2025-10-09 06:11:38'),
(14, 'Storage', '', '2025-10-09 22:11:54', '2025-10-09 06:11:54'),
(15, 'PSU', '', '2025-10-09 22:12:03', '2025-10-09 06:12:03'),
(16, 'Case', '', '2025-10-09 22:12:12', '2025-10-09 06:12:12');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `barcode`, `item_name`, `description`, `category_id`, `supplier_id`, `cost_price`, `selling_price`, `quantity`, `min_stock`, `created_at`, `updated_at`) VALUES
(11, '1234567890123', 'Intel Core i5-12400', '6-core, 12-thread 2.5 GHz CPU from Intel Alder Lake series.', 10, 1, 5000.00, 8000.00, 33, 10, '2025-10-09 23:09:20', '2025-10-09 10:29:50'),
(12, '9876543210987', 'AMD Ryzen 5 5600X', '6-core, 12-thread 3.7 GHz CPU from AMD Zen 3 architecture.', 10, 2, 3000.00, 6000.00, 0, 10, '2025-10-09 23:09:58', '2025-10-09 10:28:18'),
(13, '1122334455667', 'NVIDIA RTX 3060', 'Mid-range gaming GPU with 12GB GDDR6 memory.', 11, 4, 6000.00, 10000.00, 25, 5, '2025-10-09 23:10:56', '2025-10-09 10:29:50'),
(14, '7766554433221', 'AMD Radeon RX 6600 XT', 'Efficient GPU for 1080p gaming, 8GB GDDR6.', 11, 5, 6000.00, 10000.00, 18, 5, '2025-10-09 23:12:01', '2025-10-09 10:28:18'),
(15, '1928374650912', 'MSI B550 Tomahawk', 'AM4 socket motherboard with PCIe 4.0 support and robust VRMs.', 13, 6, 5000.00, 9000.00, 13, 5, '2025-10-09 23:12:55', '2025-10-09 10:29:50'),
(16, '9182736455467', 'ASUS Z690 Prime', 'LGA 1700 socket motherboard for Intel 12th Gen CPUs with DDR5 support.', 13, 6, 5000.00, 9000.00, 30, 5, '2025-10-09 23:13:32', '2025-10-09 07:13:32'),
(17, '5647382910456', '16GB DDR4 3200MHz', 'Dual channel DDR4 RAM kit with 3200MHz speed.', 12, 1, 2000.00, 4000.00, 48, 10, '2025-10-09 23:14:27', '2025-10-09 10:28:18'),
(18, '6758493021567', '32GB DDR4 3600MHz', 'High-speed DDR4 RAM kit with 3600MHz frequency.', 12, 2, 3500.00, 7000.00, 35, 5, '2025-10-09 23:15:21', '2025-10-09 10:29:50'),
(19, '1029384756102', '500GB NVMe SSD', 'Fast PCIe NVMe SSD for quick boot and load times.', 14, 4, 3000.00, 4500.00, 38, 10, '2025-10-09 23:16:37', '2025-10-09 10:28:18'),
(20, '5647382910293', '1TB SATA SSD', 'Reliable SATA SSD with large storage capacity.', 14, 4, 5000.00, 7000.00, 30, 5, '2025-10-09 23:17:25', '2025-10-09 10:29:50'),
(21, '8374650912837', '650W 80+ Bronze', 'Efficient 650W power supply with 80 Plus Bronze certification.', 15, 5, 5000.00, 8000.00, 18, 5, '2025-10-09 23:18:05', '2025-10-09 10:28:18'),
(22, '9283746501928', '750W 80+ Gold', 'High-efficiency 750W PSU with 80 Plus Gold rating.', 15, 6, 7500.00, 10000.00, 25, 5, '2025-10-09 23:18:56', '2025-10-09 10:29:50'),
(23, '6758493026758', 'NZXT H510', 'Mid-tower case with clean design and good airflow.', 16, 1, 5000.00, 7000.00, 18, 5, '2025-10-09 23:19:36', '2025-10-09 10:28:18'),
(24, '8473629102847', 'Fractal Design Meshify C', 'Compact mid-tower case with mesh front panel for cooling.', 16, 2, 6000.00, 8000.00, 25, 5, '2025-10-09 23:20:17', '2025-10-09 10:29:50');

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

-- --------------------------------------------------------

--
-- Table structure for table `pc_builders`
--

CREATE TABLE `pc_builders` (
  `pc_builder_id` int(11) NOT NULL,
  `pc_builder_name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cpu_id` int(11) NOT NULL,
  `gpu_id` int(11) NOT NULL,
  `ram_id` int(11) NOT NULL,
  `motherboard_id` int(11) NOT NULL,
  `storage_id` int(11) NOT NULL,
  `psu_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pc_builders`
--

INSERT INTO `pc_builders` (`pc_builder_id`, `pc_builder_name`, `user_id`, `cpu_id`, `gpu_id`, `ram_id`, `motherboard_id`, `storage_id`, `psu_id`, `case_id`, `created_at`, `updated_at`) VALUES
(3, 'Average System Unit - 8000', 1, 12, 14, 17, 15, 19, 21, 23, '2025-10-09 23:48:42', '2025-10-09 07:48:42'),
(4, 'High Range Unit', 1, 11, 13, 18, 15, 20, 22, 24, '2025-10-10 00:59:30', '2025-10-09 08:59:30');

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
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `sold_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `transaction_id`, `customer_name`, `grand_total`, `cash_received`, `cash_change`, `payment_method`, `date`, `sold_by`) VALUES
(39, 'TXN-2510-4183', 'walk in', 8000.00, 8000.00, 0.00, 'Cash', '2025-10-09 16:48:24', 1),
(40, 'TXN-2510-9607', 'walk in', 9000.00, 9000.00, 0.00, 'Cash', '2025-10-09 16:48:45', 1),
(47, 'TXN-2510-2393', 'walk in', 59000.00, 59000.00, 0.00, 'Cash', '2025-10-09 17:26:24', 1),
(48, 'TXN-2510-2769', 'walk in', 59000.00, 59000.00, 0.00, 'Cash', '2025-10-09 17:27:36', 1),
(55, 'TXN-2510-5585', 'walk in', 59000.00, 59000.00, 0.00, 'Cash', '2025-10-09 17:41:36', 1),
(56, 'TXN-2510-2391', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 17:42:18', 1),
(57, 'TXN-2510-6682', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 17:43:14', 1),
(58, 'TXN-2510-2679', 'walk in', 59000.00, 59000.00, 0.00, 'Cash', '2025-10-09 17:47:45', 1),
(59, 'TXN-2510-6648', 'walk in', 48000.00, 48000.00, 0.00, 'Cash', '2025-10-09 17:49:31', 1),
(60, 'TXN-2510-6592', 'walk in', 83000.00, 83000.00, 0.00, 'Cash', '2025-10-09 17:51:18', 1),
(61, 'TXN-2510-6693', 'walk in', 126000.00, 126000.00, 0.00, '', '2025-10-09 18:10:59', 1),
(62, 'TXN-2510-5853', 'walk in', 153500.00, 153500.00, 0.00, 'Cash', '2025-10-09 18:13:31', 1),
(63, 'TXN-2510-9687', 'walk in', 105000.00, 105000.00, 0.00, 'Credit Card', '2025-10-09 18:13:45', 1),
(64, 'TXN-2510-4849', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 18:14:54', 1),
(66, 'TXN-2510-4568', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 18:17:59', 1),
(67, 'TXN-2510-4347', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 18:18:02', 1),
(68, 'TXN-2510-5663', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 18:18:06', 1),
(69, 'TXN-2510-3654', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 18:20:37', 1),
(70, 'TXN-2510-1187', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 18:20:41', 1),
(71, 'TXN-2510-2426', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 18:21:38', 1),
(72, 'TXN-2510-9436', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 18:21:50', 1),
(73, 'TXN-2510-6862', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 18:25:49', 1),
(74, 'TXN-2510-4904', 'walk in', 48500.00, 48500.00, 0.00, 'Cash', '2025-10-09 18:28:18', 1),
(75, 'TXN-2510-5697', 'walk in', 59000.00, 59000.00, 0.00, 'Cash', '2025-10-09 18:29:50', 1);

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
(54, 39, 11, 1, 8000.00, 8000.00, '2025-10-10 00:48:24'),
(55, 40, 15, 1, 9000.00, 9000.00, '2025-10-10 00:48:45'),
(56, 59, 11, 6, 8000.00, 48000.00, '2025-10-10 01:49:31'),
(57, 60, 11, 3, 8000.00, 24000.00, '2025-10-10 01:51:18'),
(58, 61, 11, 1, 8000.00, 8000.00, '2025-10-10 02:10:59'),
(59, 62, 11, 1, 8000.00, 8000.00, '2025-10-10 02:13:31'),
(60, 63, 11, 1, 8000.00, 8000.00, '2025-10-10 02:13:45');

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

--
-- Dumping data for table `sale_pc_builders`
--

INSERT INTO `sale_pc_builders` (`sale_pc_builder_id`, `sale_id`, `pc_builder_id`, `pc_builder_name`, `selling_price`, `quantity`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 47, 4, '', 59000.00, 1, 59000.00, '2025-10-10 01:26:24', '2025-10-09 09:26:24'),
(2, 48, 4, '', 59000.00, 1, 59000.00, '2025-10-10 01:27:36', '2025-10-09 09:27:36'),
(3, 55, 4, 'High Range Unit', 59000.00, 1, 59000.00, '2025-10-10 01:41:36', '2025-10-09 09:41:36'),
(4, 56, 3, 'Average System Unit - 8000', 48500.00, 1, 48500.00, '2025-10-10 01:42:18', '2025-10-09 09:42:18'),
(5, 57, 3, 'Average System Unit - 8000', 48500.00, 1, 48500.00, '2025-10-10 01:43:14', '2025-10-09 09:43:14'),
(6, 58, 4, 'High Range Unit', 59000.00, 1, 59000.00, '2025-10-10 01:47:45', '2025-10-09 09:47:45'),
(7, 60, 4, 'High Range Unit', 59000.00, 1, 59000.00, '2025-10-10 01:51:18', '2025-10-09 09:51:18'),
(8, 61, 4, 'High Range Unit', 59000.00, 2, 118000.00, '2025-10-10 02:10:59', '2025-10-09 10:10:59'),
(9, 62, 3, 'Average System Unit - 8000', 48500.00, 3, 145500.00, '2025-10-10 02:13:31', '2025-10-09 10:13:31'),
(10, 63, 3, 'Average System Unit - 8000', 48500.00, 2, 97000.00, '2025-10-10 02:13:45', '2025-10-09 10:13:45'),
(11, 64, 3, 'Average System Unit - 8000', 48500.00, 1, 48500.00, '2025-10-10 02:14:54', '2025-10-09 10:14:54'),
(12, 69, 3, 'Average System Unit - 8000', 48500.00, 1, 48500.00, '2025-10-10 02:20:37', '2025-10-09 10:20:37'),
(13, 70, 3, 'Average System Unit - 8000', 48500.00, 1, 48500.00, '2025-10-10 02:20:41', '2025-10-09 10:20:41'),
(14, 71, 3, 'Average System Unit - 8000', 48500.00, 1, 48500.00, '2025-10-10 02:21:38', '2025-10-09 10:21:38'),
(15, 72, 3, 'Average System Unit - 8000', 48500.00, 1, 48500.00, '2025-10-10 02:21:50', '2025-10-09 10:21:50'),
(16, 73, 3, 'Average System Unit - 8000', 48500.00, 1, 48500.00, '2025-10-10 02:25:49', '2025-10-09 10:25:49'),
(17, 74, 3, 'Average System Unit - 8000', 48500.00, 1, 48500.00, '2025-10-10 02:28:18', '2025-10-09 10:28:18'),
(18, 75, 4, 'High Range Unit', 59000.00, 1, 59000.00, '2025-10-10 02:29:50', '2025-10-09 10:29:50');

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

--
-- Dumping data for table `sale_pc_builder_items`
--

INSERT INTO `sale_pc_builder_items` (`sale_pc_builder_item_id`, `sale_pc_builder_id`, `item_id`, `quantity`, `created_at`) VALUES
(1, 6, 11, 1, '2025-10-10 01:47:45'),
(2, 6, 13, 1, '2025-10-10 01:47:45'),
(3, 6, 18, 1, '2025-10-10 01:47:45'),
(4, 6, 15, 1, '2025-10-10 01:47:45'),
(5, 6, 20, 1, '2025-10-10 01:47:45'),
(6, 6, 22, 1, '2025-10-10 01:47:45'),
(7, 6, 24, 1, '2025-10-10 01:47:45'),
(8, 7, 11, 1, '2025-10-10 01:51:18'),
(9, 7, 13, 1, '2025-10-10 01:51:18'),
(10, 7, 18, 1, '2025-10-10 01:51:18'),
(11, 7, 15, 1, '2025-10-10 01:51:18'),
(12, 7, 20, 1, '2025-10-10 01:51:18'),
(13, 7, 22, 1, '2025-10-10 01:51:18'),
(14, 7, 24, 1, '2025-10-10 01:51:18'),
(15, 8, 11, 2, '2025-10-10 02:10:59'),
(16, 8, 13, 2, '2025-10-10 02:10:59'),
(17, 8, 18, 2, '2025-10-10 02:10:59'),
(18, 8, 15, 2, '2025-10-10 02:10:59'),
(19, 8, 20, 2, '2025-10-10 02:10:59'),
(20, 8, 22, 2, '2025-10-10 02:10:59'),
(21, 8, 24, 2, '2025-10-10 02:10:59'),
(22, 9, 12, 3, '2025-10-10 02:13:31'),
(23, 9, 14, 3, '2025-10-10 02:13:31'),
(24, 9, 17, 3, '2025-10-10 02:13:31'),
(25, 9, 15, 3, '2025-10-10 02:13:31'),
(26, 9, 19, 3, '2025-10-10 02:13:31'),
(27, 9, 21, 3, '2025-10-10 02:13:31'),
(28, 9, 23, 3, '2025-10-10 02:13:31'),
(29, 10, 12, 2, '2025-10-10 02:13:45'),
(30, 10, 14, 2, '2025-10-10 02:13:45'),
(31, 10, 17, 2, '2025-10-10 02:13:45'),
(32, 10, 15, 2, '2025-10-10 02:13:45'),
(33, 10, 19, 2, '2025-10-10 02:13:45'),
(34, 10, 21, 2, '2025-10-10 02:13:45'),
(35, 10, 23, 2, '2025-10-10 02:13:45'),
(36, 11, 12, 1, '2025-10-10 02:14:54'),
(37, 11, 14, 1, '2025-10-10 02:14:54'),
(38, 11, 17, 1, '2025-10-10 02:14:54'),
(39, 11, 15, 1, '2025-10-10 02:14:54'),
(40, 11, 19, 1, '2025-10-10 02:14:54'),
(41, 11, 21, 1, '2025-10-10 02:14:54'),
(42, 11, 23, 1, '2025-10-10 02:14:54'),
(43, 12, 12, 1, '2025-10-10 02:20:37'),
(44, 12, 14, 1, '2025-10-10 02:20:37'),
(45, 12, 17, 1, '2025-10-10 02:20:37'),
(46, 12, 15, 1, '2025-10-10 02:20:37'),
(47, 12, 19, 1, '2025-10-10 02:20:37'),
(48, 12, 21, 1, '2025-10-10 02:20:37'),
(49, 12, 23, 1, '2025-10-10 02:20:37'),
(50, 13, 12, 1, '2025-10-10 02:20:41'),
(51, 13, 14, 1, '2025-10-10 02:20:41'),
(52, 13, 17, 1, '2025-10-10 02:20:41'),
(53, 13, 15, 1, '2025-10-10 02:20:41'),
(54, 13, 19, 1, '2025-10-10 02:20:41'),
(55, 13, 21, 1, '2025-10-10 02:20:41'),
(56, 13, 23, 1, '2025-10-10 02:20:41'),
(57, 14, 12, 1, '2025-10-10 02:21:38'),
(58, 14, 14, 1, '2025-10-10 02:21:38'),
(59, 14, 17, 1, '2025-10-10 02:21:38'),
(60, 14, 15, 1, '2025-10-10 02:21:38'),
(61, 14, 19, 1, '2025-10-10 02:21:38'),
(62, 14, 21, 1, '2025-10-10 02:21:38'),
(63, 14, 23, 1, '2025-10-10 02:21:38'),
(64, 15, 12, 1, '2025-10-10 02:21:50'),
(65, 15, 14, 1, '2025-10-10 02:21:50'),
(66, 15, 17, 1, '2025-10-10 02:21:50'),
(67, 15, 15, 1, '2025-10-10 02:21:50'),
(68, 15, 19, 1, '2025-10-10 02:21:50'),
(69, 15, 21, 1, '2025-10-10 02:21:50'),
(70, 15, 23, 1, '2025-10-10 02:21:50'),
(71, 16, 12, 1, '2025-10-10 02:25:49'),
(72, 16, 14, 1, '2025-10-10 02:25:49'),
(73, 16, 17, 1, '2025-10-10 02:25:49'),
(74, 16, 15, 1, '2025-10-10 02:25:49'),
(75, 16, 19, 1, '2025-10-10 02:25:49'),
(76, 16, 21, 1, '2025-10-10 02:25:49'),
(77, 16, 23, 1, '2025-10-10 02:25:49');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_number`, `email`, `status`) VALUES
(1, 'TechLine Distributor', '09557896512', 'TechLine_Distributors@gmail.com', 1),
(2, 'NextGen Components Co.', '09887543215', 'NextGen_ComponentsCo.@yahoo.com', 0),
(4, 'PixelForge Hardware', '09932554631', 'PixelForge_Hardware@gmail.com', 1),
(5, 'QuantumRack Solutions', '09211235465', 'QuantumRack_Solutions@yahoo.com', 1),
(6, 'NovaChip Electronics', '09885623154', 'NovaChip23_Electronics@gmail.com', 1);

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
(1, 'Jerecho', '$2y$10$3U6KZ6tHo1zk3FXS3DRunejRBf3/WlXYNPjsZmFIeLxFMmTu4xEtW', 'staff', 1),
(2, 'Admin', '$2y$10$4tpLaZJ74qvfT.zkmEGHYuhT5q3B7rYRj0ZszObqvLUVeOK4tllvS', 'staff', 1),
(3, 'Staff', '$2y$10$AAoF1JxX8.N.wLGsPEJ2guN7sfbNCu4XaiM/8FCXMSUSxT27mEQbe', 'staff', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `cpu_id` (`cpu_id`),
  ADD KEY `gpu_id` (`gpu_id`),
  ADD KEY `motherboard_id` (`motherboard_id`),
  ADD KEY `psu_id` (`psu_id`),
  ADD KEY `ram_id` (`ram_id`),
  ADD KEY `storage_id` (`storage_id`);

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `item_stock_adjustment`
--
ALTER TABLE `item_stock_adjustment`
  MODIFY `item_stock_adjustment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pc_builders`
--
ALTER TABLE `pc_builders`
  MODIFY `pc_builder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `purchase_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `purchase_order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

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
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
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
  ADD CONSTRAINT `pc_builders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pc_builders_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pc_builders_ibfk_3` FOREIGN KEY (`cpu_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pc_builders_ibfk_4` FOREIGN KEY (`gpu_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pc_builders_ibfk_5` FOREIGN KEY (`motherboard_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pc_builders_ibfk_6` FOREIGN KEY (`psu_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pc_builders_ibfk_7` FOREIGN KEY (`ram_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pc_builders_ibfk_8` FOREIGN KEY (`storage_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
