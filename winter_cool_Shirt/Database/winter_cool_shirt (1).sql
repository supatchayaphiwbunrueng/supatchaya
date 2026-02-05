-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 27, 2026 at 10:11 AM
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
-- Database: `winter_cool_shirt`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `bank_name` varchar(100) DEFAULT '-',
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `order_status` enum('รอชำระเงิน','กำลังเตรียมสินค้า','กำลังจัดส่ง','สำเร็จ','ยกเลิก') DEFAULT 'รอชำระเงิน',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_status` varchar(50) DEFAULT 'เตรียมจัดส่ง',
  `tracking_number` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `customer_name`, `customer_phone`, `customer_address`, `payment_method`, `bank_name`, `total_price`, `status`, `order_status`, `created_at`, `shipping_status`, `tracking_number`) VALUES
(3, 4, 'จิตรกร ขวัญมา', '0996577841', '85/5 ', 'โอนเงิน', 'ธนาคารกรุงไทย', 220.00, 'pending', '', '2026-01-27 02:56:16', 'เตรียมจัดส่ง', NULL),
(4, 4, 'สินทุ  นที', '0996577841', '85/5', 'ชำระเงินปลายทาง', '-', 220.00, 'cancelled', '', '2026-01-27 03:39:16', 'เตรียมจัดส่ง', NULL),
(5, 4, 'Pannita Sukraksa', '0996577841', '85/5 ', 'โอนเงิน', 'ธนาคารกรุงเทพ', 160.00, 'shipped', '', '2026-01-27 03:43:14', 'เตรียมจัดส่ง', NULL),
(6, 4, 'Pannita Sukraksa', '09962932165', '85/5', 'โอนเงิน', '-', 549.00, 'processing', '', '2026-01-27 03:45:14', 'เตรียมจัดส่ง', NULL),
(7, 7, 'หทัยภัทร จันทร์ชาติ', '0997413245', '23/5 บ.ดงจัรทู ต.คำสะอาด อ.สว่างแดนดิน จ.สลกนคร 47110', 'โมบายแบงก์กิ้ง', '-', 385.00, 'pending', '', '2026-01-27 06:54:05', 'เตรียมจัดส่ง', NULL),
(8, 9, 'ปลาย่าง', '0997413245', '85/8 ', 'ชำระเงินปลายทาง', '-', 285.00, 'pending', '', '2026-01-27 07:39:48', 'เตรียมจัดส่ง', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 5, 9, 5, '10/10', '2026-01-27 16:07:37'),
(3, 5, 9, 5, 'สินค้าดีมาก', '2026-01-27 16:09:40'),
(4, NULL, 9, 5, 'ร้านส่งเร็วมาก', '2026-01-27 16:10:26');

-- --------------------------------------------------------

--
-- Table structure for table `shop_reviews`
--

CREATE TABLE `shop_reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('admin','staff','user') NOT NULL DEFAULT 'staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `full_name`, `role`) VALUES
(3, 'Pannita Sukraksa', 'pannita7@gmail.com', '$2y$10$VMXLYnvI7tR96ONrP2sjj.2wZkmrslzY3wACxc6MqHvQhWB3fEdJi', '', 'user'),
(4, 'pannita  sukraksa', 'pannita@gmail.com', '$2y$10$Ignvf8zPF.OFeDy2FSbNYebQ8k/AcWcA2Cbfd/EhBZ/rEfWmJJuPu', '', 'user'),
(7, 'หทัยภัทร จันทร์ชาติ', 'hathipat@gmail.com', '$2y$10$frOZ3ZbfKU.4doxt2wf1HOlAUL4c/nu0asoVr.WlHSP.Ur.KK7l1i', '', 'user'),
(9, 'ปลาย่าง', 'Grilledfish@gmail.com', '$2y$10$4R2j3dvrFFuMpV0g4E//ieMoSZeqUozFX1ZcaWWc0azaAmijaXtWi', '', 'user'),
(11, 'ThaiChayanon', 'thaichayanon@gmail.com', '$2y$10$2xQDWV/ba0lawrONe1AiceZ6BDT8XnMr5OLdoUN.7PX84jluPzlja', 'ThaiChayanon', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- Indexes for table `shop_reviews`
--
ALTER TABLE `shop_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shop_reviews`
--
ALTER TABLE `shop_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_reviews`
--
ALTER TABLE `shop_reviews`
  ADD CONSTRAINT `shop_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
