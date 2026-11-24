-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2026 at 08:17 AM
-- Server version: 10.11.19-MariaDB
-- PHP Version: 8.3.37

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coastal_villa_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `booking_status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
  `booking_date` timestamp NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `room_id`, `check_in`, `check_out`, `total_amount`, `booking_status`, `booking_date`) VALUES
(1, 1, 1, '2026-11-23', '2026-11-24', 15000.00, 'Cancelled', '2026-11-22 15:42:29'),
(2, 1, 1, '2026-11-23', '2026-11-24', 15000.00, 'Cancelled', '2026-11-22 16:09:20'),
(3, 2, 1, '2026-11-24', '2026-11-25', 15000.00, 'Confirmed', '2026-11-23 18:23:52'),
(4, 1, 1, '2026-11-25', '2026-11-26', 15000.00, 'Confirmed', '2026-11-23 18:34:04');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `facilities` text DEFAULT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `max_guests` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `room_status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `room_type`, `description`, `facilities`, `price_per_night`, `max_guests`, `image`, `room_status`, `created_at`) VALUES
(1, 'Deluxe Ocean View Room', 'Deluxe', 'Enjoy a comfortable stay with a beautiful ocean view.', 'Air Conditioning, Free Wi-Fi, TV, Private Bathroom', 15000.00, 2, 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800', 'Active', '2026-11-22 09:44:46'),
(2, 'Family Garden Villa', 'Family', 'A spacious villa suitable for families and small groups.', 'Air Conditioning, Free Wi-Fi, Garden View, TV', 22000.00, 4, 'https://images.unsplash.com/photo-1613977257363-707ba9348227?w=800', 'Active', '2026-11-22 09:44:46'),
(3, 'Luxury Beach Suite', 'Luxury', 'Experience a relaxing stay in our luxury beach suite.', 'Ocean View, Air Conditioning, Free Wi-Fi, Mini Bar', 28000.00, 3, 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800', 'Active', '2026-11-22 09:44:46'),
(5, 'Standard Garden Room', 'Standard', 'A comfortable room with a beautiful garden view.', 'WiFi, Air Conditioning, Garden View', 12000.00, 2, 'assets/images/standard-garden-room.png', 'Active', '2026-11-23 19:51:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guest','admin') DEFAULT 'guest',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `contact_number`, `password`, `role`, `created_at`) VALUES
(1, 'Nimal Perera', 'nimal@gmail.com', '0771234567', '$2y$10$zcrIxOqD2MZPcHm5evCgcejGk3ZZ9xsKRjm.ur8LzhJc/gf8dG4WK', 'guest', '2026-11-22 09:27:05'),
(2, 'Sawanya Nanayakkara', 'sawanyananayakkara1103@gmail.com', '0710424113', '$2y$10$qSI68G80YAI2Mne5tL8CxOwfnEpeTLnj1oypx1DdJ2i/24Ax93XCm', 'admin', '2026-11-23 17:59:58'),
(3, 'Gayathri Nanayakkara', 'gayathrinanayakkara@gmail.com', '0777894561', '$2y$10$BG7GcUMHCGZaSxXC.2HRR.3hmY1d.alc7aeeQFLY3Em.4l3y53XNq', 'guest', '2026-11-23 19:03:29'),
(4, 'Ashoka Jayawardana', 'ashokajay@gmail.com', '0711234567', '$2y$10$2qTlni1jOdbjjQMrwRMGXukzTiUB6yjZbZMNh5G8glBbNB.Z.7sDq', 'guest', '2026-11-23 19:17:19'),
(5, 'Thilak Nanayakkara', 'thilaknanayakkara@gmail.com', '0775015514', '$2y$10$IheI3LPZL1CAq2KOp6wUjOLFMm9psX65QW80AGasd/6ekXwbeBVT2', 'guest', '2026-11-23 19:27:30'),
(6, 'Imasha Silva', 'ima@gmail.com', '0771234891', '$2y$10$kpqja2t.8qpf/IeAlwU7BOj/DeLBebbSNDcCybzepMYwjcZ27nRDO', 'guest', '2026-11-24 06:49:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_booking_user` (`user_id`),
  ADD KEY `fk_booking_room` (`room_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`),
  ADD CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
