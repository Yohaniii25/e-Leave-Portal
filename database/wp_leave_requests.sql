-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2025 at 06:57 AM
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
-- Database: `wp_leave_requests`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_leave_approvals`
--

CREATE TABLE `wp_leave_approvals` (
  `approval_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `hod_id` int(11) NOT NULL,
  `approval_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wp_leave_notifications`
--

CREATE TABLE `wp_leave_notifications` (
  `notification_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `notification_message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `number_of_days` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `substitute` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wp_pradeshiya_sabha_users`
--

CREATE TABLE `wp_pradeshiya_sabha_users` (
  `ID` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `designation` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `sub_office` varchar(255) NOT NULL,
  `date_of_joining` date NOT NULL,
  `user_role` enum('Employee','HOD','Admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_pradeshiya_sabha_users`
--

INSERT INTO `wp_pradeshiya_sabha_users` (`ID`, `username`, `password`, `first_name`, `last_name`, `email`, `phone_number`, `designation`, `department`, `sub_office`, `date_of_joining`, `user_role`, `created_at`, `updated_at`) VALUES
(3, 'admin', '$2y$10$BsBiRu.Qo6pMS/hJQVFcXOZcyXcj7NR8JoTFIz90rWr30ifCXO5TK', 'Admin', 'User', 'yohanii725@gmail.com', '0778439871', 'Administrator', 'Administration', 'Head Office', '2023-01-01', 'Admin', '2025-03-27 08:28:36', '2025-03-27 08:28:36'),
(5, 'admin', '$2y$10$xadovtwGX9KMtbTD5hipReR1pvFeXDkB5zLS6/V/0MqNNAicM0f.u', 'Admin', 'User', 'yohanii725@gmail.com', '0778439871', 'Administrator', 'Administration', 'Head Office', '2023-01-01', 'Admin', '2025-03-27 08:36:30', '2025-03-27 08:36:30'),
(6, 'admin', '$2y$10$xPERKcwxTMx6xeFVOL6H3.1b48cTwJvjyJG2JfGe1PRGkhGLDYWQi', 'Admin', 'User', 'yohanii725@gmail.com', '0778439871', 'Administrator', 'Administration', 'Head Office', '2023-01-01', 'Admin', '2025-03-27 08:36:30', '2025-03-27 08:36:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wp_leave_approvals`
--
ALTER TABLE `wp_leave_approvals`
  ADD PRIMARY KEY (`approval_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `hod_id` (`hod_id`);

--
-- Indexes for table `wp_leave_notifications`
--
ALTER TABLE `wp_leave_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `wp_leave_request`
--
ALTER TABLE `wp_leave_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wp_pradeshiya_sabha_users`
--
ALTER TABLE `wp_pradeshiya_sabha_users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wp_leave_approvals`
--
ALTER TABLE `wp_leave_approvals`
  MODIFY `approval_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_leave_notifications`
--
ALTER TABLE `wp_leave_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_leave_request`
--
ALTER TABLE `wp_leave_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_pradeshiya_sabha_users`
--
ALTER TABLE `wp_pradeshiya_sabha_users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `wp_leave_approvals`
--
ALTER TABLE `wp_leave_approvals`
  ADD CONSTRAINT `wp_leave_approvals_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `wp_leave_request` (`request_id`),
  ADD CONSTRAINT `wp_leave_approvals_ibfk_2` FOREIGN KEY (`hod_id`) REFERENCES `wp_pradeshiya_sabha_users` (`ID`);

--
-- Constraints for table `wp_leave_notifications`
--
ALTER TABLE `wp_leave_notifications`
  ADD CONSTRAINT `wp_leave_notifications_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `wp_leave_request` (`request_id`);

--
-- Constraints for table `wp_leave_request`
--
ALTER TABLE `wp_leave_request`
  ADD CONSTRAINT `wp_leave_request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `wp_pradeshiya_sabha_users` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
