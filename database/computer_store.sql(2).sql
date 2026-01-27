-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 27, 2026 at 09:30 PM
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
  `supports_quantity` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_slug`, `category_type`, `category_description`, `created_at`, `updated_at`, `supports_quantity`) VALUES
(10, 'CPU', 'cpu', 'pc_part', '', '2025-10-09 22:11:00', '2026-01-27 19:21:01', 0),
(11, 'GPU', 'gpu', 'pc_part', '', '2025-10-09 22:11:09', '2026-01-27 18:27:01', 0),
(12, 'RAM', 'ram', 'pc_part', '', '2025-10-09 22:11:17', '2026-01-27 20:18:41', 1),
(13, 'Motherboard', 'motherboard', 'pc_part', '', '2025-10-09 22:11:38', '2026-01-27 18:27:36', 0),
(14, 'Storage', 'storage', 'pc_part', '', '2025-10-09 22:11:54', '2026-01-27 19:21:25', 0),
(15, 'PSU', 'psu', 'pc_part', '', '2025-10-09 22:12:03', '2026-01-27 18:27:01', 0),
(16, 'Case', 'case', 'pc_part', '', '2025-10-09 22:12:12', '2026-01-27 18:27:59', 0),
(20, 'Mouse', 'mouse', 'accessory', 'dsa', '2026-01-27 19:23:00', '2026-01-27 19:48:32', 1),
(21, 'Headset', 'headset', 'accessory', 'So good!', '2026-01-27 19:52:34', '2026-01-27 19:59:50', 1);

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
(26, '45632', 'Intel i5', 'asdas', 10, 1, 1000.00, 2000.00, 48, 5, '2026-01-27 17:05:32', '2026-01-27 17:24:10'),
(27, '56465', 'GPU sample', 'asad', 11, 2, 5000.00, 10000.00, 50, 5, '2026-01-27 17:11:58', '2026-01-27 17:11:58'),
(28, '9687489', 'RAM sample', 'asda', 12, 1, 3000.00, 8000.00, 50, 5, '2026-01-27 17:12:26', '2026-01-27 17:12:26'),
(29, '8574654', 'Motherboard sample', 'sdad', 13, 4, 5000.00, 10000.00, 60, 5, '2026-01-27 17:13:03', '2026-01-27 17:13:03'),
(30, '6749879', 'SSD sample', 'sadasd', 14, 5, 2000.00, 4000.00, 100, 5, '2026-01-27 17:13:32', '2026-01-27 17:13:32'),
(31, '8795435', 'PSU sample', 'asdasd', 15, 2, 8000.00, 15000.00, 50, 5, '2026-01-27 17:13:59', '2026-01-27 17:13:59'),
(32, '8976231', 'Case sample', 'asdsad', 16, 1, 5000.00, 8000.00, 100, 5, '2026-01-27 17:14:22', '2026-01-27 17:14:22'),
(33, '891111', 'Mouse', 'sdsasd', 20, 2, 400.00, 800.00, 50, 5, '2026-01-27 17:14:43', '2026-01-27 19:47:46'),
(34, '984523', 'Ram2', 'asdsa', 12, 2, 1000.00, 2000.00, 50, 5, '2026-01-27 20:18:08', '2026-01-27 20:18:08');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pc_builders`
--

INSERT INTO `pc_builders` (`pc_builder_id`, `pc_builder_name`, `user_id`, `created_at`, `updated_at`, `status`) VALUES
(28, 'Sample', 1, '2026-01-27 20:23:49', '2026-01-27 20:23:49', 'Pending');

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
(60, 28, 16, 32, 1, '2026-01-27 20:23:49'),
(61, 28, 10, 26, 1, '2026-01-27 20:23:49'),
(62, 28, 11, 27, 1, '2026-01-27 20:23:49'),
(63, 28, 13, 29, 1, '2026-01-27 20:23:49'),
(64, 28, 20, 33, 1, '2026-01-27 20:23:49'),
(65, 28, 15, 31, 1, '2026-01-27 20:23:49'),
(66, 28, 12, 28, 1, '2026-01-27 20:23:49'),
(67, 28, 14, 30, 1, '2026-01-27 20:23:49');

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
(75, 'TXN-2510-5697', 'walk in', 59000.00, 59000.00, 0.00, 'Cash', '2025-10-09 18:29:50', 1),
(76, 'TXN-2510-7376', 'walk in', 8000.00, 8000.00, 0.00, 'Credit Card', '2025-10-11 22:13:19', 2),
(77, 'TXN-2601-1985', 'walk in', 2000.00, 2000.00, 0.00, 'Cash', '2026-01-28 01:05:49', 1),
(78, 'TXN-2601-8311', 'walk in', 2000.00, 2000.00, 0.00, 'Cash', '2026-01-28 01:24:10', 1);

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
(62, 77, 26, 1, 2000.00, 2000.00, '2026-01-27 17:05:49'),
(63, 78, 26, 1, 2000.00, 2000.00, '2026-01-27 17:24:10');

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `item_stock_adjustment`
--
ALTER TABLE `item_stock_adjustment`
  MODIFY `item_stock_adjustment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pc_builders`
--
ALTER TABLE `pc_builders`
  MODIFY `pc_builder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `pc_builder_items`
--
ALTER TABLE `pc_builder_items`
  MODIFY `pc_builder_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

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
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

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
