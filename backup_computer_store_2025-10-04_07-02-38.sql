-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: computer_store
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Processors & CPUs'),(2,'Motherboards'),(3,'Graphics Cards (GPUs)');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_stock_adjustment`
--

DROP TABLE IF EXISTS `item_stock_adjustment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_stock_adjustment` (
  `item_stock_adjustment_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `previous_quantity` int(11) DEFAULT 0,
  `new_quantity` int(11) DEFAULT 0,
  `reason_adjustment` text NOT NULL,
  `adjust_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_stock_adjustment_id`),
  KEY `item_id` (`item_id`),
  KEY `adjust_by` (`adjust_by`),
  CONSTRAINT `item_stock_adjustment_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `item_stock_adjustment_ibfk_2` FOREIGN KEY (`adjust_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_stock_adjustment`
--

LOCK TABLES `item_stock_adjustment` WRITE;
/*!40000 ALTER TABLE `item_stock_adjustment` DISABLE KEYS */;
INSERT INTO `item_stock_adjustment` VALUES (6,6,0,-2,'Broken items',2,'2025-09-28 02:02:20'),(7,6,-2,-1,'Found items',2,'2025-09-28 02:02:33'),(8,2,1,-2,'Defective Items',2,'2025-09-28 02:05:04'),(9,2,-2,-1,'Fixed item',2,'2025-09-28 02:05:20'),(10,6,-2,8,'Bought somewhere',1,'2025-09-28 15:34:56'),(11,2,3,7,'Found Items\r\n',2,'2025-09-30 08:46:07'),(12,6,3,5,'Found items',1,'2025-10-01 23:24:38');
/*!40000 ALTER TABLE `item_stock_adjustment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `category_id` (`category_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  CONSTRAINT `items_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (2,'223456789','Intel i5 6th generation','Latest CPU in this year',1,1,3000.00,5000.00,75,3,'2025-09-28 02:53:13','2025-10-01 09:08:53'),(3,'245622135','Amd A8 7500k','The best seller in AMD',1,2,3000.00,8000.00,63,5,'2025-09-28 02:53:13','2025-10-01 10:01:45'),(6,'877895645','Logitech G&#39;s HERO','These mice typically feature high-quality sensors.',2,1,3000.00,5000.00,5,5,'2025-09-28 02:53:13','2025-10-01 07:24:38'),(7,'123456782','Geforce RTX 1050 Ti','Latest GPU in 2007',3,1,1000.00,2000.00,22,5,'2025-09-28 02:53:13','2025-09-28 14:34:01'),(8,'457986531','Intel i9 7th gen','Latest Intel in year 2009',1,6,10000.00,13000.00,30,5,'2025-10-01 07:00:48','2025-10-01 07:00:48');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_items` (
  `purchase_order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`purchase_order_item_id`),
  KEY `purchase_id` (`purchase_order_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`purchase_order_id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
INSERT INTO `purchase_order_items` VALUES (24,26,7,10,1000.00,10000.00),(25,26,3,5,3000.00,15000.00),(26,26,6,3,3000.00,9000.00),(27,27,2,10,3000.00,30000.00),(28,27,3,5,3000.00,15000.00),(29,27,6,10,3000.00,30000.00),(30,28,2,1,3000.00,3000.00),(31,28,3,1,3000.00,3000.00),(32,28,6,1,3000.00,3000.00),(33,29,2,5,3000.00,15000.00),(34,29,6,5,3000.00,15000.00),(35,29,7,5,1000.00,5000.00),(36,30,2,5,3000.00,15000.00),(37,30,6,5,3000.00,15000.00),(38,31,3,5,3000.00,15000.00),(39,32,3,1,3000.00,3000.00),(40,33,2,1,3000.00,3000.00),(42,35,2,5,3000.00,15000.00),(43,35,3,5,3000.00,15000.00),(44,36,7,5,1000.00,5000.00),(45,37,2,100,3000.00,300000.00),(46,37,3,200,3000.00,600000.00);
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `purchase_order_id` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(80) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `status` enum('Ordered','Received','Cancelled') DEFAULT 'Ordered',
  `date` datetime DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`purchase_order_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
INSERT INTO `purchase_orders` VALUES (26,'PO-25-09-3641',2,34000.00,'Received','2025-09-23 13:37:21',1,1),(27,'PO-25-09-9118',2,75000.00,'Received','2025-09-23 14:20:15',1,1),(28,'PO-25-09-5011',1,9000.00,'Received','2025-09-24 10:08:39',1,1),(29,'PO-25-09-2657',1,35000.00,'Received','2025-09-24 10:22:40',1,1),(30,'PO-25-09-7085',1,30000.00,'Cancelled','2025-09-24 10:32:06',1,0),(31,'PO-25-09-9277',2,15000.00,'Ordered','2025-09-24 10:42:33',1,1),(32,'PO-25-09-5033',1,3000.00,'Cancelled','2025-09-24 10:45:08',1,0),(33,'PO-25-09-4479',2,3000.00,'Cancelled','2025-09-24 10:45:14',1,0),(35,'PO-25-09-2183',4,30000.00,'Received','2025-09-27 18:33:42',2,1),(36,'PO-25-09-4367',4,5000.00,'Cancelled','2025-09-27 18:42:01',2,1),(37,'PO-25-09-5019',5,900000.00,'Received','2025-09-30 01:47:36',2,1);
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_items` (
  `sale_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `line_total` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`sale_item_id`),
  KEY `sale_id` (`sale_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (22,16,2,5,5000.00,25000.00,'2025-10-01 23:53:45'),(23,16,3,5,8000.00,40000.00,'2025-10-01 23:53:45'),(24,17,2,3,5000.00,15000.00,'2025-10-02 00:03:00'),(25,17,3,5,8000.00,40000.00,'2025-10-02 00:03:00'),(26,18,2,10,5000.00,50000.00,'2025-10-02 00:47:42'),(27,19,2,3,5000.00,15000.00,'2025-10-02 01:08:53'),(28,20,3,3,8000.00,24000.00,'2025-10-02 02:01:45');
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(80) NOT NULL,
  `customer_name` varchar(80) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `cash_received` decimal(10,2) NOT NULL,
  `cash_change` decimal(10,2) NOT NULL,
  `date` datetime NOT NULL,
  `sold_by` int(11) NOT NULL,
  PRIMARY KEY (`sale_id`),
  KEY `sold_by` (`sold_by`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`sold_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (16,'TXN-2510-8122','Walk-in',65000.00,65000.00,0.00,'2025-10-01 15:53:45',1),(17,'TXN-2510-7525','Walk-in',55000.00,55000.00,0.00,'2025-10-01 16:03:00',1),(18,'TXN-2510-2468','Walk-in',50000.00,50000.00,0.00,'2025-10-01 16:47:42',2),(19,'TXN-2510-4659','Walk-in',15000.00,15000.00,0.00,'2025-10-01 17:08:53',1),(20,'TXN-2510-9957','Walk-in',24000.00,24000.00,0.00,'2025-10-01 18:01:45',2);
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'TechLine Distributor','09557896512','TechLine_Distributors@gmail.com',1),(2,'NextGen Components Co.','09887543215','NextGen_ComponentsCo.@yahoo.com',0),(4,'PixelForge Hardware','09932554631','PixelForge_Hardware@gmail.com',1),(5,'QuantumRack Solutions','09211235465','QuantumRack_Solutions@yahoo.com',1),(6,'NovaChip Electronics','09885623154','NovaChip23_Electronics@gmail.com',1);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Jerecho','$2y$10$3U6KZ6tHo1zk3FXS3DRunejRBf3/WlXYNPjsZmFIeLxFMmTu4xEtW','staff',1),(2,'Admin','$2y$10$4tpLaZJ74qvfT.zkmEGHYuhT5q3B7rYRj0ZszObqvLUVeOK4tllvS','staff',1),(3,'Staff','$2y$10$AAoF1JxX8.N.wLGsPEJ2guN7sfbNCu4XaiM/8FCXMSUSxT27mEQbe','staff',1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-03 21:02:39
