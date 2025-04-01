-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 11:55 AM
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sub_office` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_leave_request`
--

INSERT INTO `wp_leave_request` (`request_id`, `user_id`, `leave_type`, `leave_start_date`, `leave_end_date`, `number_of_days`, `reason`, `substitute`, `status`, `created_at`, `updated_at`, `sub_office`) VALUES
(1, 14, 'Sick Leave', '2025-04-02', '2025-04-16', 15, 'Aurudu', 'Lakmi Perera', 'Pending', '2025-04-01 09:20:07', '2025-04-01 09:20:07', 'Head Office'),
(2, 14, 'Casual Leave', '2025-04-02', '2025-04-19', 18, 'gggg', 'Lakmi Perera', 'Pending', '2025-04-01 09:33:32', '2025-04-01 09:33:32', 'Head Office');

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
  `gender` enum('Male','Female') NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `address` text NOT NULL,
  `NIC` varchar(12) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `designation` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `head_of_department` varchar(255) NOT NULL,
  `sub_office` varchar(255) NOT NULL,
  `date_of_joining` date NOT NULL,
  `user_role` enum('Employee','HOD','Admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `leave_balance` int(11) NOT NULL DEFAULT 20
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_pradeshiya_sabha_users`
--

INSERT INTO `wp_pradeshiya_sabha_users` (`ID`, `username`, `password`, `first_name`, `last_name`, `gender`, `email`, `birthdate`, `address`, `NIC`, `phone_number`, `designation`, `department`, `head_of_department`, `sub_office`, `date_of_joining`, `user_role`, `created_at`, `updated_at`, `leave_balance`) VALUES
(3, 'admin', '$2y$10$BsBiRu.Qo6pMS/hJQVFcXOZcyXcj7NR8JoTFIz90rWr30ifCXO5TK', 'Admin', 'User', 'Male', 'yohanii725@gmail.com', '0000-00-00', '', '', '0778439871', 'Administrator', 'Administration', '', 'Head Office', '2023-01-01', 'Admin', '2025-03-27 08:28:36', '2025-03-31 05:25:23', 28),
(7, 'sub-office-admin', '$2y$10$nfNBW6kvCn3roxM.3rx7Q.P1w10QrZmL0fGm5v01o79Ie36Y3nS4C', 'Sub Office', 'Admin', 'Male', 'suboffice@pannalaps.com', '0000-00-00', '', '', '0711111111', 'Sub-Office Admin', 'Management', '', 'Pannala Sub-Office', '2023-01-01', 'Admin', '2025-03-28 06:07:20', '2025-03-31 05:25:29', 28),
(8, 'makandura', '$2y$10$lKnb5/0sKwmf6NG.SGuN7OT1mk7NQO9VY.PFsgvLkCEUY6bhnwV0K', 'Makandura', 'User', 'Male', 'makandura@pannalaps.com', '0000-00-00', '', '', '0722222222', 'Officer', 'Operations', '', 'Makandura Sub-Office', '2022-06-15', 'Admin', '2025-03-28 06:07:20', '2025-03-31 05:25:34', 28),
(9, 'yakkwila', '$2y$10$zjm8ikYevIZqKr/RR00cxOKgNWVaPR.MRJ.W/jJUk.advhVIicdXm', 'Yakkwila', 'User', 'Male', 'yakkwila@pannalaps.com', '0000-00-00', '', '', '0733333333', 'Supervisor', 'IT', '', 'Yakkwila Sub-Office', '2021-03-10', 'Admin', '2025-03-28 06:07:20', '2025-03-31 05:25:36', 28),
(10, 'hamangalla', '$2y$10$QgZ6K03jSU17zvPQLJX1oOppdiYy50rOFJHkuXM2JfHFj.nr4emMW', 'Hamangalla', 'User', 'Male', 'hamangalla@pannalaps.com', '0000-00-00', '', '', '0744444444', 'Manager', 'Finance', '', 'Hamangalla Sub-Office', '2020-11-20', 'Admin', '2025-03-28 06:07:20', '2025-03-31 05:25:39', 28),
(14, '', '$2y$10$tUCGT8VdZ8ly9sRSphb1.u8T/EY4sG2yFYPApMc2bSziIgih/MLJu', 'Yohani', 'Abeykoon', 'Female', 'yohani@pannalaps.com', '1999-07-25', '730/2\r\nMadinnagoda', '199970704599', '0778439871', 'Team Lead', 'Web', 'Danushka Gangoda', 'Head Office', '2022-08-22', 'Employee', '2025-03-31 05:04:46', '2025-03-31 05:25:41', 28),
(15, '', '$2y$10$0Smu8zrktNSPfPJcJyjf7OH7367a63IYNaQrckswwclSVqZpl2wOe', 'Chaya', 'Abeykoon', 'Female', 'manjulajenat9974@gmail.com', '1996-03-31', '730/2, Madinndagoda, Rajagiriya', '923456789v', '0771234567', 'HR Assistant', 'HR', 'Dhananji Bandara', 'Head Office', '2024-01-01', 'HOD', '2025-04-01 09:52:42', '2025-04-01 09:52:42', 20);

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
  ADD KEY `fk_user_id` (`user_id`);

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
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wp_pradeshiya_sabha_users`
--
ALTER TABLE `wp_pradeshiya_sabha_users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `wp_pradeshiya_sabha_users` (`ID`),
  ADD CONSTRAINT `wp_leave_request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `wp_pradeshiya_sabha_users` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
