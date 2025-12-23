-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2025 at 08:32 AM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pannalaps-leave`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_leave_request`
--

CREATE TABLE `wp_leave_request` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `leave_start_date` date NOT NULL,
  `leave_end_date` date NOT NULL,
  `number_of_days` decimal(3,1) NOT NULL,
  `reason` text DEFAULT NULL,
  `substitute` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL COMMENT '1-pending, 2-accept, 3-reject',
  `step_1_approver_id` int(11) DEFAULT NULL,
  `step_1_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `step_1_date` datetime DEFAULT NULL,
  `step_2_approver_id` int(11) DEFAULT NULL,
  `step_2_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `step_2_date` datetime DEFAULT NULL,
  `step_3_approver_id` int(11) DEFAULT NULL,
  `step_3_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `step_3_date` datetime DEFAULT NULL,
  `final_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sub_office` varchar(255) NOT NULL,
  `office_type` enum('head','sub') DEFAULT 'head',
  `rejection_remark` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_leave_request`
--

INSERT INTO `wp_leave_request` (`request_id`, `user_id`, `leave_type`, `leave_start_date`, `leave_end_date`, `number_of_days`, `reason`, `substitute`, `status`, `step_1_approver_id`, `step_1_status`, `step_1_date`, `step_2_approver_id`, `step_2_status`, `step_2_date`, `step_3_approver_id`, `step_3_status`, `step_3_date`, `final_status`, `created_at`, `updated_at`, `sub_office`, `office_type`, `rejection_remark`, `department_id`) VALUES
(168, 19, 'Duty Leave', '2025-12-23', '2025-12-25', '3.0', 'hi', 'Head of Pradeshiya Sabha (PS)', 1, 136, 'approved', NULL, 137, 'approved', NULL, 136, 'pending', NULL, 'pending', '2025-12-23 06:35:36', '2025-12-23 07:28:43', 'Head Office', 'head', NULL, NULL),
(169, 19, 'Duty Leave', '2025-12-23', '2025-12-26', '4.0', 'HI', 'Head of Pradeshiya Sabha (PS)', 1, 135, 'pending', NULL, NULL, 'approved', NULL, 137, 'pending', NULL, 'pending', '2025-12-23 07:22:45', '2025-12-23 07:28:45', 'Head Office', 'head', NULL, NULL),
(170, 19, 'Duty Leave', '2025-12-24', '2025-12-26', '3.0', 'HI', 'Head of Pradeshiya Sabha (PS)', NULL, NULL, 'pending', NULL, 137, 'pending', NULL, 136, 'pending', NULL, 'pending', '2025-12-23 07:32:14', '2025-12-23 07:32:14', 'Head Office', 'head', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wp_leave_request`
--
ALTER TABLE `wp_leave_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wp_leave_request`
--
ALTER TABLE `wp_leave_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `wp_leave_request`
--
ALTER TABLE `wp_leave_request`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `wp_pradeshiya_sabha_users` (`ID`),
  ADD CONSTRAINT `wp_leave_request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `wp_pradeshiya_sabha_users` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
