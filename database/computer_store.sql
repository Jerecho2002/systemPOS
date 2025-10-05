-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2025 at 01:32 AM
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
(1, 'Processors & CPUs', 'asdasd', '2025-10-05 22:34:02', '2025-10-05 22:59:41'),
(2, 'Motherboards', '', '2025-10-05 22:34:02', '2025-10-05 22:34:02'),
(3, 'Graphics Cards (GPUs)', '', '2025-10-05 22:34:02', '2025-10-05 22:34:02'),
(5, 'Memory (RAM)', '', '2025-10-05 22:42:11', '2025-10-05 22:42:11'),
(7, 'Storage Devices', '', '2025-10-05 23:01:26', '2025-10-05 23:01:26'),
(8, 'Power Supplies', '', '2025-10-05 23:01:58', '2025-10-05 23:01:58'),
(9, 'Monitors', '', '2025-10-05 23:02:17', '2025-10-05 23:02:17');

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
(2, '223456789', 'Intel i5 6th generation', 'Latest CPU in this year', 1, 1, 3000.00, 5000.00, 57, 3, '2025-09-28 02:53:13', '2025-10-05 21:40:42'),
(3, '245622135', 'Amd A8 7500k', 'The best seller in AMD', 1, 2, 3000.00, 8000.00, 42, 5, '2025-09-28 02:53:13', '2025-10-05 21:40:42'),
(7, '123456782', 'Geforce RTX 1050 Ti', 'Latest GPU in 2007', 3, 1, 1000.00, 2000.00, 34, 5, '2025-09-28 02:53:13', '2025-10-04 09:21:43'),
(8, '457986531', 'Intel i9 7th gen', 'Latest Intel in year 2009', 1, 6, 10000.00, 13000.00, 19, 5, '2025-10-01 07:00:48', '2025-10-04 08:05:09'),
(9, '546886542', 'Logitech G\'s HERO', 'These mice typically feature high-quality sensors.', 2, 2, 3000.00, 5000.00, 90, 5, '2025-10-05 02:05:19', '2025-10-04 10:31:15');

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
(8, 2, 1, -2, 'Defective Items', 2, '2025-09-28 02:05:04'),
(9, 2, -2, -1, 'Fixed item', 2, '2025-09-28 02:05:20'),
(11, 2, 3, 7, 'Found Items\r\n', 2, '2025-09-30 08:46:07');

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

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`purchase_order_id`, `po_number`, `supplier_id`, `grand_total`, `status`, `date`, `created_by`, `is_active`) VALUES
(40, 'PO-25-10-5296', 1, 6000.00, 'Received', '2025-10-04 02:57:34', 2, 1);

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
(51, 40, 2, 1, 3000.00, 3000.00),
(52, 40, 3, 1, 3000.00, 3000.00);

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
  `date` datetime NOT NULL,
  `sold_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `transaction_id`, `customer_name`, `grand_total`, `cash_received`, `cash_change`, `date`, `sold_by`) VALUES
(25, 'TXN-2510-8400', 'Walk-in', 215000.00, 215000.00, 0.00, '2025-10-04 16:05:09', 2),
(26, 'TXN-2510-4746', 'Walk-in', 50000.00, 50000.00, 0.00, '2025-10-04 17:54:36', 2),
(27, 'TXN-2510-7688', 'Walk-in', 130000.00, 130000.00, 0.00, '2025-10-04 17:55:08', 2),
(28, 'TXN-2510-1318', 'Walk-in', 50000.00, 50000.00, 0.00, '2025-10-04 18:31:15', 2);

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
(36, 25, 2, 5, 5000.00, 25000.00, '2025-10-05 00:05:09'),
(37, 25, 3, 10, 8000.00, 80000.00, '2025-10-05 00:05:09'),
(38, 25, 7, 3, 2000.00, 6000.00, '2025-10-05 00:05:09'),
(39, 25, 8, 8, 13000.00, 104000.00, '2025-10-05 00:05:09'),
(40, 26, 2, 10, 5000.00, 50000.00, '2025-10-05 01:54:36'),
(41, 27, 3, 10, 8000.00, 80000.00, '2025-10-05 01:55:08'),
(43, 28, 9, 10, 5000.00, 50000.00, '2025-10-05 02:31:15');

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `item_stock_adjustment`
--
ALTER TABLE `item_stock_adjustment`
  MODIFY `item_stock_adjustment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

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
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`purchase_order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
