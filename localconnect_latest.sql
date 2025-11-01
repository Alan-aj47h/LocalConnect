-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 18, 2025 at 04:02 PM
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
-- Database: `localconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` text DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Accepted','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  `provider_reply` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `user_id`, `provider_id`, `service_id`, `appointment_date`, `address`, `landmark`, `message`, `status`, `provider_reply`) VALUES
(9, 5, 7, 3, '2025-09-24 06:50:36', 'Noida(H),Delhi', 'Near the church', 'Can you come between 10:am and 11:am', 'Completed', 'I will come as  you prefer'),
(10, 3, 8, 4, '2025-09-24 21:38:42', 'Nothing(H),Kochi', 'Near the bank', 'Can you come fast', 'Completed', 'I will come fast as soon as possible'),
(11, 3, 8, 4, '2025-09-29 00:10:39', 'Nothing(H),Kochi', 'Near choondy church', 'Can you come fast', 'Accepted', 'I will come as soon as possibele'),
(12, 3, 8, 4, '2025-09-29 06:51:58', 'Nothing(H),Kochi', 'Near choondy church', 'Can you come fast', 'Completed', 'I will come as soon as possible'),
(13, 3, 11, 6, '2025-10-08 03:53:29', 'Nothing(H),Kochi', 'Near choondy church', 'come fast the fan is gone', 'Accepted', 'i will come as soon as possible');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `user_id`, `provider_id`, `appointment_id`, `message`, `status`, `created_at`) VALUES
(11, 3, 8, 11, 'He dose not provide service to me.', 'pending', '2025-10-07 01:21:52');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `review` text NOT NULL,
  `reply` text DEFAULT NULL,
  `reply_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `provider_id`, `customer_id`, `review`, `reply`, `reply_at`, `created_at`) VALUES
(1, 8, 3, 'Ramesh Electronics offered good service to me with affordable price', 'Thank for you reply sir', '2025-09-28 00:40:44', '2025-09-27 15:36:08'),
(2, 8, 3, 'Ramesh provides me good service', 'Thank you sir for your support', '2025-09-29 03:48:04', '2025-09-29 03:46:21'),
(3, 8, 3, 'it was a good service it was nice working with him', 'thank you sir for your support', '2025-10-08 07:30:30', '2025-10-08 07:29:08');

-- --------------------------------------------------------

--
-- Table structure for table `service_details`
--

CREATE TABLE `service_details` (
  `service_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `category` enum('Plumber','Carpenter','Electrician','Mechanic','Painter') NOT NULL,
  `about_service` varchar(500) DEFAULT NULL,
  `price` varchar(50) NOT NULL,
  `locations` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_details`
--

INSERT INTO `service_details` (`service_id`, `provider_id`, `business_name`, `category`, `about_service`, `price`, `locations`, `created_at`, `updated_at`) VALUES
(3, 7, 'Benny\'s Workshop', 'Mechanic', 'Benny\'s workshop is a reputed car workshop for providing best solution', '350-450', 'Choondy,Ashokapuram', '2025-09-13 05:26:21', '2025-09-23 00:44:05'),
(4, 8, 'Ramesh Electronc\'s', 'Electrician', 'Ramesh Electron\'s offers good electrical works in affordable rate', '380', 'Choondy, Edathala,Kochin Bank', '2025-09-15 01:50:00', '2025-10-10 01:07:19'),
(5, 9, 'Basil  Shop', 'Electrician', 'we provide good services at reasonable range with excellent service quality', '300-400', 'Kizhakabalam,Vilangu', '2025-09-19 04:19:45', '2025-09-19 04:19:45'),
(6, 11, 'Thomas Electronics', 'Electrician', 'Thomas Electronic provide good service to customers', '300', 'Choondy, Edathala', '2025-10-04 05:14:14', '2025-10-04 05:14:14'),
(7, 12, 'Rakesh Workshop', 'Mechanic', 'Rakesh Worksop is a reputed Mehcad that provides good services to customers at affordable price', '400', 'Choondy,Kochin Bank', '2025-10-05 13:04:41', '2025-10-05 13:04:41'),
(8, 13, 'Arif Workshop', 'Mechanic', 'Arif Workshop provide good services to customers at affordable price', '380', 'Choondy', '2025-10-06 00:27:58', '2025-10-06 00:27:58'),
(9, 14, 'swathy store', 'Electrician', 'i provide best service', '300', 'Choondy', '2025-10-08 07:35:25', '2025-10-08 07:35:25'),
(10, 15, 'Moto GT Workshop', 'Mechanic', 'Moto GT offers premium services to customers at affordable price', '400', 'Choondy', '2025-10-10 01:15:34', '2025-10-10 01:15:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(60) NOT NULL,
  `password` varchar(100) NOT NULL,
  `phone` varchar(14) DEFAULT NULL,
  `role` enum('customer','provider','admin') DEFAULT 'customer',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `role`, `status`, `address`, `created_at`) VALUES
(3, 'Alan Jose', 'alan@gmail.com', '$2y$10$8111nGhx3GWa9JP9r7NKgeBSKopMzEeZeNsWKddJ9SDNxwK905VCq', '9995139624', 'customer', 'pending', 'Nothing(H),Kochi', '2025-09-11 00:35:37'),
(5, 'Libin Justin', 'libin@gmail.com', '$2y$10$KadyhfU6ty4Hf3al0Sn08uYvxo5Il1fPX44KwfXwlmIZgXtSC8yfK', '8137805729', 'customer', 'pending', 'Noida(H),Delhi', '2025-09-11 01:07:52'),
(6, 'Vincent Django', 'vincent@gmail.com', '$2y$10$Z75agwnbvTeSJqfu6AKGF.t8OZwE6DOB8Bu4oum1i79EyIUQTGWCe', NULL, 'admin', 'pending', NULL, '2025-09-12 03:25:43'),
(7, 'Benny Thomas', 'benny123@gmail.com', '$2y$10$jJQq/783d8Gl4RfnHMUna.xOSHyMCvSq0CdXQtjy1TieOp8Y9.73S', '9995139568', 'provider', 'approved', 'Chirapunji(H),Choondy,Aluva', '2025-09-12 03:31:56'),
(8, 'Ramesh Sharma', 'ramesh@gmail.com', '$2y$10$Ipw8IpG8ii2vMU4rEA5Qj.ZENHVq1oBXLJMuZDRDw480OtbaPg7KW', '8564676824', 'provider', 'approved', 'Gandhi Nagar second street, Kochi, Ernakulam', '2025-09-15 01:30:36'),
(9, 'Basil Eldhose', 'basileld@2005.gmail.com', '$2y$10$d3P7l7wTyxnM4hhCggBqTezwT8mwxyfDTsJrLSAw2VSlOfFCgV2xi', '8848763319', 'provider', 'approved', 'badajnsjujshhju(H).wsyhys', '2025-09-19 04:16:27'),
(10, 'Antony Thomas', 'antony@gmail.com', '$2y$10$WPknvLzXOWDwZSiz1l854uSEoYHI28E/iuDZFBtKWwKwpp2q6nYB2', '8137805729', 'provider', 'approved', 'Thekkara(H),Choondy,Aluva PO', '2025-10-04 03:46:31'),
(11, 'Thomas Joseph', 'thomas@gmail.com', '$2y$10$V.iUhG9qEOiSpAv2Ky803OFsNEGchqWHaGuC0llklGaCJTATNKg8m', '9995139624', 'provider', 'approved', 'Kizhak(H),Choondy,Aluva', '2025-10-04 04:52:33'),
(12, 'Rakesh Sharma', 'rakesh@gmail.com', '$2y$10$Mwld5tT6KcO6la4TQdt9uuM08w5wUROXZ1UGI8/VFqKHolEjr6wxe', '9544137205', 'provider', 'approved', 'Gandhi Nagar 2nd street,Edathala,Aluva', '2025-10-05 13:01:06'),
(13, 'Arif Husain', 'arif@gmail.com', '$2y$10$d9z1xtUTMSDMe4Up.UEKg.LF8LpCcU0CFqjHdB6oiwIl3jzzTr2tC', '9995139624', 'provider', 'approved', 'Kizhakekod(H),Choondy,Aluva PO', '2025-10-06 00:25:19'),
(14, 'Swathy suresh', 'swathy@gmail.com', '$2y$10$FPRKpiJ3GzdteGh9sX3vtONY/Zd5PCZoep1VLu/BxNezIeSJL.NQO', '9497804422', 'provider', 'approved', 'Kizhakekod(H),Choondy,Aluva PO', '2025-10-08 07:33:49'),
(15, 'Libin Justin', 'libin1@gmail.com', '$2y$10$kshhzmB95ifz3uXjqHvrYeHrTZKySVGCBh0DYOyCdjCqQYc7fGHLa', '7012308725', 'provider', 'approved', 'Kizhakekod(H),Choondy,Aluva PO', '2025-10-10 01:13:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `service_details`
--
ALTER TABLE `service_details`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `fk_provider` (`provider_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_details`
--
ALTER TABLE `service_details`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `service_details` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `service_details`
--
ALTER TABLE `service_details`
  ADD CONSTRAINT `fk_provider` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
