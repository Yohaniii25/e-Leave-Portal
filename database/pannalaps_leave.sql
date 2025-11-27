-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 26, 2025 at 07:47 AM
-- Server version: 8.0.44
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pannalaps_leave`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_departments`
--

CREATE TABLE `wp_departments` (
  `department_id` int NOT NULL,
  `department_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_departments`
--

INSERT INTO `wp_departments` (`department_id`, `department_name`) VALUES
(1, 'Industry Division'),
(2, 'Revenue Division'),
(3, 'Community Development Division'),
(4, 'Accounts Division'),
(5, 'Institutions Division'),
(6, 'Leave Management'),
(7, 'Pradeshiya Sabha Division\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `wp_designations`
--

CREATE TABLE `wp_designations` (
  `designation_id` int NOT NULL,
  `designation_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_designations`
--

INSERT INTO `wp_designations` (`designation_id`, `designation_name`) VALUES
(1, 'Head Of Department'),
(2, 'Employee'),
(3, 'Head of Pradeshiya Sabha'),
(5, 'Head office Authorized Officer'),
(6, 'Sub office Authorized Officer'),
(7, 'Admin'),
(8, 'Leave Officer'),
(9, 'Head of SubOffice'),
(10, 'SubOffice Leave Officer\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `wp_leave_notifications`
--

CREATE TABLE `wp_leave_notifications` (
  `notification_id` int NOT NULL,
  `request_id` int NOT NULL,
  `notification_message` text,
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wp_leave_request`
--

CREATE TABLE `wp_leave_request` (
  `request_id` int NOT NULL,
  `user_id` int NOT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `leave_start_date` date NOT NULL,
  `leave_end_date` date NOT NULL,
  `number_of_days` decimal(3,1) NOT NULL,
  `reason` text,
  `substitute` varchar(255) DEFAULT NULL,
  `status` int DEFAULT NULL COMMENT '1-pending, 2-accept, 3-reject',
  `step_1_approver_id` int DEFAULT NULL,
  `step_1_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `step_1_date` datetime DEFAULT NULL,
  `step_2_approver_id` int DEFAULT NULL,
  `step_2_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `step_2_date` datetime DEFAULT NULL,
  `step_3_approver_id` int DEFAULT NULL,
  `step_3_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `step_3_date` datetime DEFAULT NULL,
  `final_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sub_office` varchar(255) NOT NULL,
  `office_type` enum('head','sub') DEFAULT 'head',
  `rejection_remark` text,
  `department_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_leave_request`
--

INSERT INTO `wp_leave_request` (`request_id`, `user_id`, `leave_type`, `leave_start_date`, `leave_end_date`, `number_of_days`, `reason`, `substitute`, `status`, `step_1_approver_id`, `step_1_status`, `step_1_date`, `step_2_approver_id`, `step_2_status`, `step_2_date`, `step_3_approver_id`, `step_3_status`, `step_3_date`, `final_status`, `created_at`, `updated_at`, `sub_office`, `office_type`, `rejection_remark`, `department_id`) VALUES
(144, 29, 'Sick Leave', '2025-11-18', '2025-11-18', 0.5, 'fever', 'Chinthaka Saman Kumara', 2, 130, 'approved', '2025-11-17 09:26:08', 136, 'approved', '2025-11-17 09:27:30', NULL, 'approved', NULL, 'approved', '2025-11-17 14:25:01', '2025-11-17 14:28:08', 'Head Office', 'head', NULL, NULL),
(145, 19, 'Casual Leave', '2025-11-20', '2025-11-23', 4.0, 'wedding', 'Head of Pradeshiya Sabha (PS)', 2, NULL, 'approved', NULL, 135, 'approved', '2025-11-17 09:41:32', NULL, 'approved', NULL, 'approved', '2025-11-17 14:40:23', '2025-11-17 14:43:15', 'Head Office', 'head', NULL, NULL),
(146, 25, 'Duty Leave', '2025-11-18', '2025-11-18', 1.0, 'event managenent', 'Iresha Abeywardena', NULL, 134, 'approved', '2025-11-19 04:24:52', 136, 'approved', '2025-11-19 04:27:06', NULL, 'pending', NULL, 'pending', '2025-11-19 09:22:20', '2025-11-19 09:27:06', 'Head Office', 'head', NULL, NULL),
(147, 25, 'Duty Leave', '2025-11-14', '2025-11-14', 1.0, 'DAVE Unit training', 'S.M.B.M. Sundarapperuma', NULL, NULL, 'pending', NULL, NULL, 'pending', NULL, NULL, 'pending', NULL, 'pending', '2025-11-19 09:34:50', '2025-11-19 09:34:50', 'Head Office', 'head', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wp_manual_leave_logs`
--

CREATE TABLE `wp_manual_leave_logs` (
  `log_id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `number_of_days` decimal(3,1) NOT NULL DEFAULT '0.0',
  `reason` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_manual_leave_logs`
--

INSERT INTO `wp_manual_leave_logs` (`log_id`, `admin_id`, `user_id`, `leave_type`, `number_of_days`, `reason`, `created_at`) VALUES
(33, 3, 19, 'Casual Leave', 0.5, 'personal', '2025-11-04 04:42:00'),
(34, 3, 19, 'Casual Leave', 3.0, 'personal', '2025-11-04 04:53:06'),
(35, 3, 19, 'Sick Leave', 0.5, 'fever', '2025-11-04 04:55:03'),
(36, 3, 20, 'Casual Leave', 5.0, 'w', '2025-11-04 05:00:53'),
(37, 3, 19, 'Casual Leave', 4.0, 'rrr', '2025-11-04 05:30:53'),
(38, 3, 19, 'Sick Leave', 0.5, 'fever', '2025-11-04 05:37:31'),
(39, 3, 19, 'Duty Leave', 2.0, 'meeting', '2025-11-04 05:38:05');

-- --------------------------------------------------------

--
-- Table structure for table `wp_pradeshiya_sabha_users`
--

CREATE TABLE `wp_pradeshiya_sabha_users` (
  `ID` int NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `NIC` varchar(12) NOT NULL,
  `service_number` varchar(50) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `designation` varchar(255) NOT NULL,
  `sub_office` varchar(255) NOT NULL,
  `date_of_joining` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `leave_balance` decimal(3,1) NOT NULL DEFAULT '20.0',
  `duty_leave_count` int DEFAULT '0',
  `casual_leave_balance` decimal(3,1) NOT NULL DEFAULT '21.0',
  `sick_leave_balance` decimal(3,1) NOT NULL DEFAULT '24.0',
  `department_id` int DEFAULT NULL,
  `designation_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_pradeshiya_sabha_users`
--

INSERT INTO `wp_pradeshiya_sabha_users` (`ID`, `username`, `password`, `first_name`, `last_name`, `gender`, `email`, `address`, `NIC`, `service_number`, `phone_number`, `designation`, `sub_office`, `date_of_joining`, `created_at`, `updated_at`, `leave_balance`, `duty_leave_count`, `casual_leave_balance`, `sick_leave_balance`, `department_id`, `designation_id`) VALUES
(3, 'admin', '$2y$10$HKmp9EJLq8JSu.dnE3skKumofYSkE18hcRsEbDGio4ddu8iNqggOy', 'Admin', 'User', 'Female', 'admin@gmail.com', 'Pannala Pradeshiya Sabha', '324534534534', 'PS01', '', 'Admin', 'Head Office', '2025-04-01', '2025-03-27 08:28:36', '2025-10-03 07:42:23', 45.0, 0, 21.0, 24.0, 7, 7),
(7, 'sub-office-admin', '$2y$10$dUZmuLxI/3FJio9l6.jA1.svjauAPUJlzxB.SzN4Ot9J.wXPxH/lC', 'Sub Office', 'Admin', 'Female', 'suboffice@pannala.ps.gov.lk', '730/2, Madinndagoda, Rajagiriya', '923456789v', 'PS02', '0711111111', 'Sub-Office Admin', 'Pannala Sub-Office', '0000-00-00', '2025-03-28 06:07:20', '2025-11-03 09:55:36', 45.0, 0, 21.0, 24.0, 7, 7),
(8, 'makandura', '$2y$10$lKnb5/0sKwmf6NG.SGuN7OT1mk7NQO9VY.PFsgvLkCEUY6bhnwV0K', 'Makandura', 'User', 'Male', 'makandura@pannalaps.com', '', '', 'PS03', '0722222222', 'Officer', 'Makandura Sub-Office', '2022-06-15', '2025-03-28 06:07:20', '2025-10-03 07:42:23', 45.0, 0, 21.0, 24.0, 7, 7),
(9, 'yakkwila', '$2y$10$zjm8ikYevIZqKr/RR00cxOKgNWVaPR.MRJ.W/jJUk.advhVIicdXm', 'Yakkwila', 'User', 'Male', 'yakkwila@pannalaps.com', '', '', 'PS04', '0733333333', 'Supervisor', 'Yakkwila Sub-Office', '2021-03-10', '2025-03-28 06:07:20', '2025-10-03 07:42:23', 45.0, 0, 21.0, 24.0, 7, 7),
(10, 'hamangalla', '$2y$10$QgZ6K03jSU17zvPQLJX1oOppdiYy50rOFJHkuXM2JfHFj.nr4emMW', 'Hamangalla', 'User', 'Male', 'hamangalla@pannalaps.com', '', '', 'PS05', '0744444444', 'Manager', 'Hamangalla Sub-Office', '2020-11-20', '2025-03-28 06:07:20', '2025-10-03 07:42:23', 45.0, 0, 21.0, 24.0, 7, 7),
(19, 'jayasinghe', '$2y$10$QL47WVYIG/2BX0c4xoFHw.LFAseszrG.8JVJXJwixRAmdkpv4Anti', 'J.A.S.', 'Jayasinghe', 'Female', 'secretary@pannalaps.lk', '', '', '1', '779082143', 'Secretary', 'Head Office', '2025-04-21', '2025-04-21 10:00:40', '2025-11-17 13:00:16', 45.0, 0, 21.0, 24.0, 6, 2),
(21, 'ilangkoon', '$2y$10$U0K8axK5XogoGSQQ4CpjwO93ba9eleU7V3JOC6brfzFyoL84lW5xy', 'I.M.G.P.', 'Ilangkoon', 'Female', 'ilangkoon@pannalaps.lk', '', '', '3', '707570827', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:10:43', '2025-11-16 09:11:33', 45.0, 0, 21.0, 24.0, 3, NULL),
(22, 'gunathilaka', '$2y$10$Vycami23TBBmpeAXSlXfDe96HvM33QLfoFtiX2KDOPdOVT0WMgNMm', 'P.A.H.', 'Gunathilaka', 'Female', 'gunathilaka@pannalaps.lk', '', '', '4', '772202568', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:12:14', '2025-11-16 09:11:33', 45.0, 0, 21.0, 24.0, 3, NULL),
(23, 'subhasinghe', '$2y$10$3k5C68E9meWWwLR84tkmmeSPtcEv980DzFSIvq2lIL0x9YQZOUIFu', 'S.A.D.M.', 'Subhasinghe', 'Female', 'subhasinghe@pannalaps.lk', '', '', '5', '760206424', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:13:43', '2025-11-16 09:11:33', 45.0, 0, 21.0, 24.0, 3, NULL),
(24, 'ratnamalala', '$2y$10$6t7VW71.YMTYbtH2X2P4EOizgTiAzFa/v4FwVLpRLFOvmYQ4GmP.y', 'D.M.I.U.', 'Ratnamalala', 'Female', 'ratnamalala@pannalaps.lk', '', '', '6', '769093976', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:15:10', '2025-11-16 09:26:36', 45.0, 0, 21.0, 24.0, 5, NULL),
(25, 'anuruddhika', '$2y$10$PsiPZ3nEJwlamTk1XMK/AelU2uY6mqQzI6UCuE7vYgeQNxMk.aDwK', 'W.A.K.S.', 'Anuruddhika', 'Female', 'anuruddhika@pannalaps.lk', '', '', '7', '726555116', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:16:38', '2025-11-16 09:26:36', 45.0, 0, 21.0, 24.0, 5, NULL),
(26, 'wijeratne', '$2y$10$na37958LTHSzHdyW4WUfzeuGQCWPgDxiytcFdw/MDnLK.IG76oCIq', 'A.M.N.D.', 'Wijeratne', 'Female', 'wijeratne@pannalaps.lk', '', '', '8', '775539644', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:17:52', '2025-11-16 09:26:36', 45.0, 0, 21.0, 24.0, 5, NULL),
(27, 'dassanayake', '$2y$10$Uj1iOdidXzKofJI4.vpdLuWTxNbZV.o9AunqFYdOvADZpI14haWjy', 'M.D.M.A.P.K.', 'Dassanayake', 'Female', 'dassanayake@pannalaps.lk', '', '', '9', '711960486', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:19:19', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(28, 'basnayake', '$2y$10$JeDxVNRiywKZSYQbnz8rNuEWuY9Z4Sh6KDK2VLGztjr7n/DhYrpEW', 'B.M.S.S.', 'Basnayake', 'Female', 'basnayake@pannalaps.lk', '', '', '10', '772169923', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:21:18', '2025-11-16 09:19:51', 42.0, 0, 21.0, 24.0, 4, NULL),
(29, 'wanniarachchi', '$2y$10$KNUjN9Ufxg/NXnKctYtHyOO5PFt/mBdp0kxMXVzwtX.jqktibMrFW', 'W.A.D.P.', 'Wanniarachchi', 'Female', 'wanniarachchi@pannalaps.lk', '', '', '11', '703872749', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:25:38', '2025-11-16 08:16:31', 45.0, 0, 21.0, 24.0, 1, NULL),
(31, 'heneyaka', '$2y$10$.RAmkrsmOw.5bVO9PoyuvO54WO7p.s1D/pLUtL/FRsAHUCgyJpVMy', 'H.A.M.N.', 'Heneyaka', 'Female', 'heneyaka@pannalaps.lk', '', '', '13', '771024383', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:28:18', '2025-11-04 05:00:25', 45.0, 0, 21.0, 24.0, 7, NULL),
(32, 'abeysinghe', '$2y$10$B5YGEpOnhhAvH9WDv4bkau8l3YKxbm5OEdvaIwLzT3n.JthZCKKEW', 'A.M.D.', 'Abeysinghe', 'Female', 'abeysinghe@pannalaps.lk', '', '', '15', '771231830', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:30:29', '2025-11-16 08:52:41', 45.0, 0, 21.0, 24.0, 2, NULL),
(33, 'susantha', '$2y$10$YJ/P8M1oE8ionu2.I.LGnu2ZUujvW/Fh7AWooCDewYMlSxWnlGe9e', 'A.D.', 'Susantha', 'Female', 'susantha@pannalaps.lk', '', '', '16', '777991066', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:31:50', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(35, 'hemamali', '$2y$10$1bAEHAgRwk6T8esU1SeoKu6NsDxvj8bgdIlRyaHL3QQswP1FMKgdW', 'A.M.W.', 'Hemamali', 'Female', 'hemamali@pannalaps.lk', '', '', '18', '716016490', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:36:12', '2025-11-16 09:11:33', 45.0, 0, 21.0, 24.0, 3, NULL),
(36, 'padmakumari', '$2y$10$Q1djxYu3fT1m7O9J8c4SYuKjk2LefyR9a2NbvlDuTibDjyKJG2gSu', 'N.', 'Padmakumari', 'Female', 'padmakumari@pannalaps.lk', '', '', '19', '778801594', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:37:14', '2025-11-04 05:02:25', 45.0, 0, 21.0, 24.0, 7, NULL),
(37, 'mapa', '$2y$10$eeDbjUMwxuzCiqPgk4W/M.LzV4ddaFpSHwDWtRKrIkwBOVYpgnCti', 'M.M.I.Thushara', 'Mapa', 'Male', 'mapa@pannalaps.lk', '', '', '20', '774008514', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:39:04', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(38, 'jayamaha', '$2y$10$XbL2HMBHmjKrDeHgpRCXM.NaHnPGf2wNTxX.TVE76RJU50C68lUV.', 'Chethana Sudheera', 'Jayamaha', 'Male', 'jayamaha@pannalaps.lk', '', '', '21', '706799800', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:43:31', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(39, 'kumari', '$2y$10$dW9tKn.SjqeVRVD9IzLMgOXa6Mj8wHl1Jeiu0Omri4NFX2vbYyOtu', 'J.A.T.P.', 'Kumari', 'Female', 'kumari@pannalaps.lk', '', '', '22', '778752279', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:44:29', '2025-11-16 09:11:33', 45.0, 0, 21.0, 24.0, 3, NULL),
(40, 'sanjeevani', '$2y$10$Lfj5ZQM/xLTumt2vzFgVpuqX9MXzNiWPDHuqZdRrUbojA3e5B3r8C', 'Inoka', 'Sanjeevani', 'Female', 'sanjeevani@pannalaps.lk', '', '', '23', '710875047', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:45:30', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(41, 'kumara', '$2y$10$FczI8m/8wMeXCxISoArKMOxRls2C8G//B1N/zYG.M.3m0.GemtMO.', 'Chinthaka Saman', 'Kumara', 'Male', 'kumara@pannalaps.lk', '', '', '24', '741595462', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:46:49', '2025-11-16 12:47:29', 45.0, 0, 21.0, 24.0, 1, NULL),
(43, 'ishani', '$2y$10$oISEWmwDydA.XnozvdIb4u/8oXGD3cHDLQNPureXtIoApGyzs/QsC', 'P.M.N. Ishani', 'Pathiraja', 'Female', 'ishani@pannalaps.lk', '', '', '28', '741885491', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:54:34', '2025-11-16 08:16:31', 45.0, 0, 21.0, 24.0, 1, NULL),
(44, 'ranasinghe', '$2y$10$hXE5zoHXM/nxZmXPTt/BSez.ZY6BJL2RhZdPa6VtT3je5y/JiCGGK', 'P.S.', 'Ranasinghe', 'Female', 'ranasinghe@pannalaps.lk', '', '', '29', '714235543', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:56:03', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(45, 'chinthaka', '$2y$10$kceGXe.gTfLfMWK3KP.NF.sz46GoczK.Awg3x3n1dH9yovR5KJqzu', 'S.A.U.V.', 'Chinthaka', 'Male', 'chinthaka@pannalaps.lk', '', '', '30', '775323401', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 10:58:03', '2025-11-16 08:16:31', 45.0, 0, 21.0, 24.0, 1, NULL),
(46, 'deepika', 'a0247c9866', 'R.D.R.', 'Deepika', 'Female', 'deepika@pannalaps.lk', '', '', '522', '0778420522', '', 'Head Office', '2025-04-21', '2025-04-21 10:59:56', '2025-11-04 03:55:21', 45.0, 0, 21.0, 24.0, 7, NULL),
(47, 'abeywardena', '$2y$10$Aw0unaItfgHV1kwcVfeFieV70ScDqnEOTjjqWZxYzrEwYk24BjWOy', 'Iresha', 'Abeywardena', 'Female', 'abeywardena@pannalaps.lk', '', '', '31', '741678893', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:01:01', '2025-11-16 09:26:36', 45.0, 0, 21.0, 24.0, 5, NULL),
(48, 'karunanayake', '$2y$10$VQXjTN9NDOb1XGYmfXk8GOC5TvdmCN42bQJhdf7cf14PTIjm0M9bS', 'K.A.C.', 'Karunanayake', 'Female', 'karunanayake@pannalaps.lk', '', '', '516', '0764420320', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:01:44', '2025-11-04 05:07:56', 45.0, 0, 21.0, 24.0, 7, NULL),
(49, 'ratnayake', '$2y$10$08mb/hNjPtuiLEVQLCA8CeYpiFV.AbbG7u0s1jLrzC5wD5rZ1751q', 'R.M.T.M.', 'Ratnayake', 'Female', 'ratnayake@pannalaps.lk', '', '', '32', '773108541', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:02:16', '2025-11-16 09:26:36', 45.0, 0, 21.0, 24.0, 5, NULL),
(50, 'rasika', '$2y$10$u/gwbQoLYpvAWn4QHK2QZexzMtEBLQfHoO2W2TdC.8wAy73g4njdu', 'Harshani', 'Rasika', 'Female', 'rasika@pannalaps.lk', '', '', '33', '768624785', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:03:46', '2025-11-16 08:52:41', 45.0, 0, 21.0, 24.0, 2, NULL),
(51, 'rajapaksa', '$2y$10$EAfgNTYXzrYJhbTBXUaEFun7HTrA43t.mGQLMx56DcsI/YydMCp.u', 'R.M.S.D.', 'Rajapaksa', 'Female', 'rajapaksa@pannalaps.lk', '', '', '35', '717180099', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:04:44', '2025-11-16 08:16:31', 45.0, 0, 21.0, 24.0, 1, NULL),
(52, 'janakasiri', '$2y$10$0SjKHCm6U3q/gc2r6w/UZ.FaEo6w/4Co/MRqLC9GgBeN37UcRf/Bq', 'T.M.N.', 'Janakasiri', 'Female', 'janakasiri@pannalaps.lk', '', '', '45', '764578843', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:05:37', '2025-11-16 09:26:36', 45.0, 0, 21.0, 24.0, 5, NULL),
(53, 'senaratne', '$2y$10$cDUOGPwq4sgoMFl2QDCRue3jqH8gB5tAraIrwQWWioyCg1rqw5PmS', 'N.G.S.', 'Senaratne', 'Female', 'senaratne@pannalaps.lk', '', '', '46', '774548773', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:07:07', '2025-11-16 09:26:36', 45.0, 0, 21.0, 24.0, 5, NULL),
(54, 'ayesha', '$2y$10$ctGazVb0TqZH7UYyg75I7OoRnk6QP/fEiwFOsjdprxwv9m7mDAeOm', 'Chathurie', 'Ayesha', 'Female', 'ayesha@pannalaps.lk', '', '', '47', '762612195', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:08:20', '2025-11-16 09:26:36', 45.0, 0, 21.0, 24.0, 5, NULL),
(55, 'wanigasuriya', '$2y$10$tl/wOGO4cOjhLZLCR5cFpuRVI/l/jqLFxW2vVbxcMTz/pHqLWV.l.', 'Nandana', 'Wanigasuriya', 'Female', 'wanigasuriya@pannalaps.lk', '', '', '48', '762092831', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:09:24', '2025-11-16 09:26:36', 45.0, 0, 21.0, 24.0, 5, NULL),
(56, 'jayasuriya', '$2y$10$heC/SvadwbvIl5b0ptwMp.tuzBC2J0ifEeHbTxfyYWeVmHuBz7Y5G', 'K.', 'Jayasuriya', 'Female', 'jayasuriya@pannalaps.lk', '', '', '49', '773788973', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:15:36', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(57, 'chandrasiri', '$2y$10$K0yeo63P1usOsMc.KewXA.rG.CZgjaWGP/tF/OBdzhglvKdgPgUIS', 'A.P.S.', 'Chandrasiri', 'Male', 'chandrasiri@pannalaps.lk', '', '', '50', '769108091', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:16:51', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(58, 'dharmasiri', '$2y$10$mXdlYSqgRTMAmGAtipiTBuIlVZHy06sZUFVHDhjmyewKOc84pHM/i', 'Lalith', 'Dharmasiri', 'Male', 'dharmasiri@pannalaps.lk', '', '', '51', '762736090', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:18:02', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(59, 'pathiratne', '$2y$10$01Tsk8pOyBL8GX0etyKdoOtMJQv3RgvvaGZpYD560Cb8GcDvncrvC', 'G.P.I.J.', 'Pathiratne', 'Male', 'pathiratne@pannalaps.lk', '', '', '52', '776002965', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:20:25', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(60, 'jayatissa', '$2y$10$UQthKywBZ8kzDzScaHKpJeqBZhU8PNevVPskRiWkvCzfXyvOlfc3K', 'A.P.', 'Jayatissa', 'Male', 'jayatissa@pannalaps.lk', '', '', '53', '773441725', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:21:55', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(61, 'anjana', '$2y$10$qdgzzPFN5iw7ze3NHB7t..Mi/NjHNfAVzAAF0WvXRsXhWnZG5AeYS', 'U.V.Dhammi', 'Anjana', 'Female', 'anjana@pannalaps.lk', '', '', '524', '0784322183', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:22:55', '2025-11-04 05:15:54', 45.0, 0, 21.0, 24.0, 7, NULL),
(62, 'samaraweera', '$2y$10$PwrerIhMYTOAFdW9IAFkw.m24EVjcbwNxo7LPCvj6q./8CfmwihuG', 'W.A.C.Y.', 'Samaraweera', 'Male', 'samaraweera@pannalaps.lk', '', '', '515', '0776059073', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:24:01', '2025-11-04 05:19:58', 45.0, 0, 21.0, 24.0, 7, NULL),
(63, 'ajith', '$2y$10$XITNvcGY6DRMUHKzORl1qOAw/IsjwZK8D6k/D0ge9GY/S/rRMkYXa', 'R.G.Ajith', 'Kumara', 'Male', 'ajith@pannalaps.lk', '', '', '54', '772739050', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:25:00', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(64, 'perera', '$2y$10$1mlNMM8U58b9hBcJt1b8L.cSxwODSR4IJRXQcvUAG0IF0dg7w3Hfm', 'W.I.A.', 'Perera', 'Male', 'perera@pannalaps.lk', '', '', '509', '', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:25:07', '2025-11-04 05:21:23', 45.0, 0, 21.0, 24.0, 7, NULL),
(65, 'dmg', '$2y$10$tgTwYwHdJYnYgGWBgQrq0uwnRhld2jLUo80OnEErXwJVZD8KTw5W2', 'D.M.G.', 'Dassanayake', 'Male', 'dmg@pannalaps.lk', '', '', '504', '0704918440', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:26:39', '2025-11-04 05:21:47', 45.0, 0, 21.0, 24.0, 7, NULL),
(66, 'fernando', '$2y$10$i9E16veYdLQGuVeF71ktyeb88iO5akchK0s.EJZQ2ckSNVgcJHgES', 'K.N.J.', 'Fernando', 'Male', 'fernando@pannalaps.lk', '', '', '300', '', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:27:20', '2025-11-04 05:22:00', 45.0, 0, 21.0, 24.0, 7, NULL),
(67, 'arunasiri', '$2y$10$k38ngfr1TpzxT9NrLAycKendOfGmLqpGh3YWwhBvZsFMP.YcViDtC', 'Charith', 'Arunasiri', 'Male', 'arunasiri@pannalaps.lk', '', '', '55', '765394563', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:27:29', '2025-11-16 09:26:36', 45.0, 0, 21.0, 24.0, 5, NULL),
(68, 'mahesh', '$2y$10$Lh4hyAk6WD6KosRWqWfQce/Kxnlzq5yRoeJucAs5J23pZdySj6BsK', 'Thilina Mahesh', 'Kumara', 'Male', 'mahesh@pannalaps.lk', '', '', '56', '778017604', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:29:42', '2025-11-04 05:22:48', 45.0, 0, 21.0, 24.0, 7, NULL),
(69, 'priyantha', '$2y$10$z5c9ZVBVJA3OxTPoDebxuOWqODarLnb1STOtCBi/acBDlzCljwNga', 'H.M. Lakmini', 'Priyantha', 'Male', 'priyantha@pannalaps.lk', '', '', '57', '778665376', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:32:33', '2025-11-16 08:16:31', 45.0, 0, 21.0, 24.0, 1, NULL),
(70, 'adhikari', '$2y$10$O9xU7fgoGhYbyGFmETSeJuj53n3huYnl6jJ4vamPbpwNE698k1oE2', 'A.M.G.D.', 'Adhikari', 'Male', 'adhikari@pannalaps.lk', '', '', '402', '0761616928', 'Employee', 'Head Office', '2025-04-28', '2025-04-21 11:33:44', '2025-11-04 05:23:59', 45.0, 0, 21.0, 24.0, 7, NULL),
(71, 'lansakkara', '$2y$10$XBR9/f0p7EJx2iJR5lOCZ.XjDqCxgiy0IZ766yyQRoP7JTsNNQZyO', 'L.M.P.M.', 'Lansakkara', 'Male', 'lansakkara@pannalaps.lk', '', '', '401', '0761886729', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:34:53', '2025-11-04 05:24:12', 45.0, 0, 21.0, 24.0, 7, NULL),
(72, 'ranaweera', '$2y$10$nI9cBCp.CFZdWyrUbg6e2.eEoW308Djp2wewNa/bjubIm/aj02yoe', 'M.G.', 'Ranaweera', 'Male', 'ranaweera@pannalaps.lk', '', '', '5', '773406006', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:36:10', '2025-11-04 05:25:04', 45.0, 0, 21.0, 24.0, 7, NULL),
(73, 'dilshan', '$2y$10$0KNpjqJRAbKauayHCZnis.NBW9sunsuH5I7yubU2Eay1za./tHhB2', 'R.M.P.S.', 'Dilshan', 'Male', 'dilshan@pannalaps.lk', '', '', '', '0779603381', 'Employee', 'Head Office', '2025-04-21', '2025-04-21 11:52:36', '2025-11-04 05:26:45', 45.0, 0, 21.0, 24.0, 7, NULL),
(75, 'indika', '$2y$10$wAGsTmY7hxd3np9KZ4mNR.jYnjHKDfqF9hkNtdF52SGmguDjeboVO', 'Mohan', 'Indika', 'Male', 'indika@pannalaps.lk', '', '', '58', '754413021', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:52:37', '2025-11-16 08:16:31', 45.0, 0, 21.0, 24.0, 1, NULL),
(76, 'kumuditha', '$2y$10$Ipm/8han2NtT41JfOW1ZwuMKUaU4aIaFOrlJafD1l8OtmboqQH.sm', 'R.D.S.S.', 'Kumuditha', 'Male', 'kumuditha@pannalaps.lk', '', '', '', '075-9533429', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:53:23', '2025-11-04 05:27:57', 45.0, 0, 21.0, 24.0, 7, NULL),
(77, 'wijesinghe', '$2y$10$kj64HdzvkWFYg.f4/DAxtunwozRCXjJL31qqTT2LtvWb6JvcRA7Yu', 'K.A. Sarath', 'Wijesinghe', 'Male', 'wijesinghe@pannalaps.lk', '', '', '59', '760026828', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:53:43', '2025-11-16 08:16:31', 45.0, 0, 21.0, 24.0, 1, NULL),
(78, 'liyanarachchi', '$2y$10$/8yDQoj.2b5SiLw1PomE1uc4DoQosgFrwczIuYPidX4VYcKJ8kDGa', 'L.A.S.', 'Liyanarachchi', 'Male', 'liyanarachchi@pannalaps.lk', '', '', '600', '0766845853', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:54:15', '2025-11-04 05:27:03', 45.0, 0, 21.0, 24.0, 7, NULL),
(79, 'samaratunga', '$2y$10$mY4/NH6B.cxHQYuf3LIWY.YJnBfOUXY8iZGIgFxDd.pYpD9ljDcuq', 'Charaka Shyan', 'Samaratunga', 'Male', 'samaratunga@pannalaps.lk', '', '', '60', '779217537', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:54:38', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(80, 'sinhapruthivi', '$2y$10$2.HykEDb1qKT8J3GlmjW8OUjHXwJCQ.zTaYQzcmaBPb/5bw6R3tGi', 'S.A.W.C.', 'Sinhapruthivi', 'Male', 'sinhapruthivi@pannalaps.lk', '', '', '601', '0768088341', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:55:08', '2025-11-04 05:42:28', 45.0, 0, 21.0, 24.0, 7, NULL),
(81, 'sanjaya', '$2y$10$7RzfhO0JVoIdNcwgt4Pj4e8gsM7kpR0HZVXERi3Yb8nRl4pq2xXsm', 'D.M. Prabath', 'Sanjaya', 'Male', 'sanjaya@pannalaps.lk', '', '', '61', '778827067', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:55:49', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(82, 'bpps', '$2y$10$NZS9IxOjT3qhfUr92nIaHeXeCpIo3/PgdkKExOgbDJryIycR676hC', 'B.P.P.S.', 'Kumara', 'Male', 'bpps@pannalaps.lk', '', '', '602', '0786915155', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:56:34', '2025-11-04 05:43:57', 45.0, 0, 21.0, 24.0, 7, NULL),
(83, 'sarath', '$2y$10$DP5EICkE90E7w8M4Tv.wd.ZAX9H2Bp5zaQNrf33jkDzh4ZMGWgYS6', 'Mahinda Sarath', 'Kumara', 'Male', 'sarath@pannalaps.lk', '', '', '62', '774064143', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:57:17', '2025-11-16 09:19:51', 45.0, 0, 21.0, 24.0, 4, NULL),
(84, 'smpv', '$2y$10$UJay9NGIBCjGdAs90zQEQ.gDzmoHMN.PjJL8QGahTesYL/5X1kYYi', 'S.M.P.V.', 'Subhasinghe', 'Male', 'smpv@pannalaps.lk', '', '', '102', '0771477613', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:58:12', '2025-11-04 05:46:19', 45.0, 0, 21.0, 24.0, 7, NULL),
(85, 'thilakashanthi', '$2y$10$uI8uo1g2BF0SE7rLYc5Fh.quaWHY7xhIv9BTUeG2ddHDcEMOdO1k2', 'R.K.M. Enoka', 'Thilakashanthi', 'Male', 'thilakashanthi@pannalaps.lk', '', '', '38', '742067244', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:58:17', '2025-11-04 05:46:48', 45.0, 0, 21.0, 24.0, 7, NULL),
(86, 'gonavila', '$2y$10$pdVLWVwjeYUkxoEuSWE96.pXOOFPb7kEMAasp76U8Qx/YhS3/2OlC', 'G.A.A.D.P.', 'Gonavila', 'Male', 'gonavila@pannalaps.lk', '', '', '209', '0702428186', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:59:11', '2025-11-04 05:47:06', 45.0, 0, 21.0, 24.0, 7, NULL),
(87, 'seneviratne', '$2y$10$OgOcztmToV4SmXnojg0S/ul3sITvOfbyGdXwLrdoLIusYNyMi.laS', 'S.P.', 'Seneviratne', 'Male', 'seneviratne@pannalaps.lk', '', '', '49', '778375868', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 04:59:25', '2025-11-04 05:48:06', 45.0, 0, 21.0, 24.0, 7, NULL),
(88, 'janaki', '$2y$10$A7wxNZjKxY7LC.hXOQqv9uf.KQEs/aurfN3ENlBAb0Le9RabFzJh6', 'P.A.', 'Janaki', 'Female', 'janaki@pannalaps.lk', '', '', '', '0740411491', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:00:19', '2025-11-04 05:48:30', 45.0, 0, 21.0, 24.0, 7, NULL),
(89, 'smgr', '$2y$10$xIrF/AHwiM3Br4wUNTazau01pNdkWMZb0PHrSReMVShRARJ9kpUCO', 'S.M.G.R.', 'Ratnayake', 'Male', 'smgr@pannalaps.lk', '', '', '66', '763548218', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:02:07', '2025-11-04 05:49:01', 45.0, 0, 21.0, 24.0, 7, NULL),
(90, 'tharushi', '$2y$10$0QbUXQ3q4bQpTXRZIH.7YezA/c2oy5cvnr0aL/8oz8F1.RIOb6vdS', 'D.P. Tharushi Niwarthana', 'Dassanayake', 'Female', 'tharushi@pannalaps.lk', '', '', '67', '', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:11:40', '2025-11-04 05:49:30', 45.0, 0, 21.0, 24.0, 7, NULL),
(91, 'jayakody', '$2y$10$CyTb0MwGSx6qdmifaWgzw.KCDIMRZ99XNb52S0XoEzg7Nq7.u8oRC', 'J.A.R.D.', 'Jayakody', 'Male', 'jayakody@pannalaps.lk', '', '', '39', '770552675', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:12:50', '2025-11-04 05:49:47', 42.0, 0, 18.0, 24.0, 7, NULL),
(92, 'dissanayake', '$2y$10$xXn1blJAgh0wJSIaGbTvJ.sIpEyGGpx6IeqsMOzl8FZ9NeMsJRXme', 'D.M. Sujeewa Priyanka', 'Dissanayake', 'Male', 'dissanayake@pannalaps.lk', '', '', '205', '774086732', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:13:10', '2025-11-04 05:50:29', 45.0, 0, 21.0, 24.0, 7, NULL),
(93, 'eranga', '$2y$10$PlfsX.B6DJzc4IjAFr7Yf.ZhtuACatQPqGCLDYnVXNBaGr8M7KS3e', 'S.A. Shanaka', 'Eranga', 'Male', 'eranga@pannalaps.lk', '', '', '701', '779139280', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:14:06', '2025-11-04 05:50:29', 45.0, 0, 21.0, 24.0, 7, NULL),
(94, 'abeysekera', '$2y$10$Htm2JLFntZoLeWLbSmaSTOlR9aHpBdQEvUkeDzS9YYB05LQE9hUnm', 'A.W.A. Deepani', 'Abeysekera', 'Female', 'abeysekera@pannalaps.lk', '', '', '38', '778667480', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:14:16', '2025-11-04 05:50:48', 45.0, 0, 21.0, 24.0, 7, NULL),
(95, 'jayasundara', '$2y$10$Jtos5f3WczfMookOhIOBme4tjFqpE2KiWKyKS.dT7Otk31y9pB5Qi', 'J.M.Y. Chandrani', 'Jayasundara', 'Female', 'jayasundara@pannalaps.lk', '', '', '204', '787708969', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:16:18', '2025-11-04 05:51:53', 45.0, 0, 21.0, 24.0, 7, NULL),
(96, 'muthukumarana', '$2y$10$0gStk9dMseBgJAVJrllrMeE5SX5K/uxsUZbM9CaBqyWr3zY2WpcDK', 'D.P.N.', 'Muthukumarana', 'Male', 'muthukumarana@pannalaps.lk', '', '', '500', '729590649', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:17:04', '2025-11-04 05:52:08', 45.0, 0, 21.0, 24.0, 7, NULL),
(97, 'liyanage', '$2y$10$sAmBIXP9V.Eb6Gw/T9CPaOigNSyLL7NHEg8R3ACQH7DQSsO8iojWy', 'L.P. Anoma', 'Liyanage', 'Female', 'liyanage@pannalaps.lk', '', '', '203', '776625378', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:17:13', '2025-11-04 05:53:04', 45.0, 0, 21.0, 24.0, 7, NULL),
(98, 'senarath', '$2y$10$BOn3zlL1ULj/9Xmfa.waw.ZCEpEWjSNX9cEa7/p9fk/R73XN1MoO2', 'S.A.P.', 'Senarath', 'Male', 'senarath@pannalaps.lk', '', '', '83', '773983327', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:18:27', '2025-11-04 05:53:28', 45.0, 0, 21.0, 24.0, 7, NULL),
(99, 'tmnd', '$2y$10$VowDCerQtHE8PaPpDr6Pmuyp3uPsF3SddUOfNp1wymWbhLhTAhnSW', 'T.M.N.D.', 'Kumari', 'Female', 'tmnd@pannalaps.lk', '', '', '503', '713092733', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:19:30', '2025-11-04 05:54:01', 45.0, 0, 21.0, 24.0, 7, NULL),
(100, 'ruwanmali', '$2y$10$Ay2uUqNE0i12NmywsAaPVOwvgeiomrSlqWnQJk6k7h8QRRVxA8k7K', 'M.P.', 'Ruwanmali', 'Female', 'ruwanmali@pannalaps.lk', '', '', '202', '774926139', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:20:06', '2025-11-04 05:54:28', 45.0, 0, 21.0, 24.0, 7, NULL),
(101, 'hms', '$2y$10$cVhNv1sBNSsZrxSjYg1p2OClQN3uTyeAJO.iCGiQYy0cCUEhXLtZi', 'H.M.S.', 'Kumari', 'Female', 'hms@pannalaps.lk', '', '', '506', '774779491', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:21:51', '2025-11-04 05:54:59', 45.0, 0, 21.0, 24.0, 7, NULL),
(102, 'nalani', '$2y$10$Jq3/QKyxLmhS5lz70YylRuNo2y.HZJkcLxn7ljuUOJ7aGKZQwaBkq', 'D.L. Nalani', 'Wanigasuriya', 'Female', 'nalani@pannalaps.lk', '', '', '201', '776106079', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:22:06', '2025-11-04 05:55:22', 45.0, 0, 21.0, 24.0, 7, NULL),
(103, 'harischandra', '$2y$10$T8R.Y1..JXnnXGnS0AcMo.h7mEOAbw1RMH1yNnlx/nR16EzNTN6hu', 'K.P.', 'Harischandra', 'Male', 'harischandra@pannalaps.lk', '', '', '73', '761032264', 'Employee', 'Head Office', '2025-04-23', '2025-04-22 05:22:59', '2025-11-04 05:55:50', 45.0, 0, 21.0, 24.0, 7, NULL),
(104, 'lakshika', '$2y$10$uzGyaRBujDqsKT8IWogBlO30aBy07I7Es0eJ3.Q/L2GKbKDQ.iBXy', 'K.M.M.M.', 'Lakshika', 'Female', 'lakshika@pannalaps.lk', '', '', '513', '764448404', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:23:06', '2025-11-04 05:56:30', 45.0, 0, 21.0, 24.0, 7, NULL),
(105, 'padmasiri', '$2y$10$u7z5LiDvNEiS.vX9g9STw.Xfv.MoTV9vGl5jOJm67x5J1VEhZZFhK', 'W.P. Janadara', 'Padmasiri', 'Male', 'padmasiri@pannalaps.lk', '', '', '77', '761918149', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:24:01', '2025-11-04 05:56:50', 45.0, 0, 21.0, 24.0, 7, NULL),
(106, 'mallawasuriya', '$2y$10$9kujdQBKz2vVOek84vm4duSAwjFUAz30R8x.wUEPDKjQPn5t7z1YW', 'M.A.S.P.', 'Mallawasuriya', 'Male', 'mallawasuriya@pannalaps.lk', '', '', '7', '712349297', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:26:00', '2025-11-04 05:57:26', 45.0, 0, 21.0, 24.0, 7, NULL),
(107, 'silva', '$2y$10$Wu3xSsAGi7L76fJoyb1mxun1PCS3krDoZOwFDiQlTTPMudblzkSF6', 'M.M.T.N.', 'Silva', 'Female', 'silva@pannalaps.lk', '', '', '90', '771567019', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:27:45', '2025-11-04 05:57:52', 45.0, 0, 21.0, 24.0, 7, NULL),
(108, 'herath', '$2y$10$nSTWqMSIsGCVnRPPvGOBwedGKIYPu6zo.YmINj34j2Es4SnSnO5Fy', 'H.M.S.D.', 'Herath', 'Male', 'herath@pannalaps.lk', '', '', '514', '716697745', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:28:05', '2025-11-04 06:00:44', 45.0, 0, 21.0, 24.0, 7, NULL),
(109, 'pathirana', '$2y$10$mdWfzPqpoVLGHtZX3P7L9.agr/SxOsv.34ezWUTDUUcSi77ccGG2O', 'P.K.C.', 'Pathirana', 'Male', 'pathirana@pannalaps.lk', '', '', '75', '765657733', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:28:43', '2025-11-04 06:01:40', 45.0, 0, 21.0, 24.0, 7, NULL),
(110, 'athawada', '$2y$10$ecDjS0dQWNRHsfhn7qDRIeUzNDEfW1Yho4QlkgfVTFqqfPY3gXUw.', 'A.M.S.', 'Athawada', 'Male', 'athawada@pannalaps.lk', '', '', '82', '773301288', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:29:47', '2025-11-04 06:02:09', 45.0, 0, 21.0, 24.0, 7, NULL),
(111, 'karunasekera', '$2y$10$xOz6cVrYoUDCRiqxJUD04epj29tnFDpxBNtXJm1nJJbHnB6Pc7BIO', 'R.M.U.S.', 'Karunasekera', 'Male', 'karunasekera@pannalaps.lk', '', '', '59', '770793022', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:30:42', '2025-11-04 06:03:16', 45.0, 0, 21.0, 24.0, 7, NULL),
(112, 'anuruddha', '$2y$10$lNxH8ZC7nioVfZuq09/54OWlvJ7X367A.XX1.oXMp3r90DWkHyEsS', 'S.Suranga', 'Anuruddha', 'Male', 'anuruddha@pannalaps.lk', '', '', '112', '774104746', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:31:46', '2025-11-04 06:03:28', 45.0, 0, 21.0, 24.0, 7, NULL),
(113, 'samiththa', '$2y$10$o9P/J70zf8lgEqmtLYuRXOkRc/iWo9.Vwh.XSchH0Zg7EdnBD1lR6', 'N.G.Samiththa Prasad', 'Dharmasiri', 'Male', 'samiththa@pannalaps.lk', '', '', '73', '775991239', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:33:31', '2025-11-04 06:04:13', 45.0, 0, 21.0, 24.0, 7, NULL),
(114, 'attanayake', '$2y$10$PtIN.jO8/OkXjGzh93HIZOLUXNhIUMyuiiYXGIKaPUhViaQovx94O', 'A.M.N.Dashini', 'Attanayake', 'Female', 'attanayake@pannalaps.lk', '', '', '75', '778284553', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:34:26', '2025-11-04 06:04:30', 45.0, 0, 21.0, 24.0, 7, NULL),
(115, 'nayanga', '$2y$10$1XPpcTHZvMgzhGl1ZSwVP.2GqJD0MEWD6AC5R2UT1nwWXJHxqOb/a', 'A.K.Rimani', 'Nayanga', 'Female', 'nayanga@pannalaps.lk', '', '', '', '717166311', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:35:23', '2025-11-04 06:04:49', 45.0, 0, 21.0, 24.0, 7, NULL),
(116, 'jeevani', '$2y$10$jiPYhgXxe1lmth3UMCeFzeKelD.b9YMatafjfGNldHJE8o2NxUT2e', 'W.R.Sandhya Jeevani', 'Kumari', 'Female', 'jeevani@pannalaps.lk', '', '', '120', '772164033', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:37:27', '2025-11-04 06:05:11', 45.0, 0, 21.0, 24.0, 7, NULL),
(117, 'udayamali', '$2y$10$1iTYp5SOTdN6wGnkGRk15u4pEMN.F3srAt7j5WcoldsgCiIRUL.cO', 'H.A.A.A.T.', 'Udayamali', 'Female', 'udayamali@pannalaps.lk', '', '', '70', '702402240', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:38:31', '2025-11-04 06:05:24', 45.0, 0, 21.0, 24.0, 7, NULL),
(118, 'sunil', '$2y$10$wlDUZMUxl/5oQfvWYOXTD.z9MEu7yoEnoRawpX/KP0Zu7y.ReuM52', 'Sunil', 'Shantha', 'Male', 'sunil@pannalaps.lk', '', '', '69', '0779977235', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:39:51', '2025-11-04 06:05:43', 45.0, 0, 21.0, 24.0, 7, NULL),
(119, 'manchanayake', '$2y$10$GOLme4ifXOpzFHK4sruu6eJle7l7PgT9mkmOa96pEU3DYmuU5floy', 'M.A.Yutapeethake', 'Manchanayake', 'Male', 'manchanayake@pannalaps.lk', '', '', '93', '775044993', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:40:45', '2025-11-04 06:06:00', 45.0, 0, 21.0, 24.0, 7, NULL),
(120, 'marasinghe', '$2y$10$Vkmu2VFZ9n0/QlNGUnQVwukTJM3vX2ZX8Ek8GYPcXpcHrHhdQTgCi', 'M.M.N.C.', 'Marasinghe', 'Male', 'marasinghe@pannalaps.lk', '', '', '114', '712668850', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:41:37', '2025-11-04 06:06:44', 45.0, 0, 21.0, 24.0, 7, NULL),
(121, 'madhubhashini', '$2y$10$tQgXzrsiWveLqcYgM4vm4umR1BgMtYa0RPbhlSDrgfUm3.FI.HbXy', 'Jayani', 'Madhubhashini', 'Female', 'madhubhashini@pannalaps.lk', '', '', '36', '770823141', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:42:51', '2025-11-16 08:16:31', 45.0, 0, 21.0, 24.0, 1, NULL),
(122, 'jayakodi', '$2y$10$pv6cwfPxhyVa.7pSGAo3mudC.4Sro6jC1n56vTsu5gieo6fdDAB1e', 'J.A.W.P.K.', 'Jayakodi', 'Male', 'jayakodi@pannalaps.lk', '', '', '520', '765415570', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:43:15', '2025-11-04 06:07:20', 45.0, 0, 21.0, 24.0, 7, NULL),
(123, 'munasinghe', '$2y$10$U6SNUJ/9jJvzIlMeUzO7/ef0Lqec8rnEA/Z8jf0pyXvl5iSexjvcy', 'M.M.T.A.', 'Munasinghe', 'Male', 'munasinghe@pannalaps.lk', '', '', '68', '702311160', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:43:37', '2025-11-04 06:07:54', 45.0, 0, 21.0, 24.0, 7, NULL),
(124, 'sankalpana', '$2y$10$pYcvjtpYdfaAvM0o7tvBZ.eOAIvCpUVd83LPK73ZnhHBP6MJ5Bdn6', 'A.Dinujaya', 'Sankalpana', 'Male', 'sankalpana@pannalaps.lk', '', '', '839', '779630313', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:44:15', '2025-11-04 06:08:15', 45.0, 0, 21.0, 24.0, 7, NULL),
(125, 'jayalath', '$2y$10$iOXYtT2hn4u7m0Uhz/tGKuzgbIb4mjgnPP7p19t48Has6DlpULsSa', 'J.A.S.T.', 'Jayalath', 'Male', 'jayalath@pannalaps.lk', '', '', '66', '710922481', 'Employee', 'Head Office', '2025-04-22', '2025-04-22 05:44:16', '2025-11-04 06:08:34', 45.0, 0, 21.0, 24.0, 7, NULL),
(130, 'industryhead', '$2y$10$6M4AtWCJUARScGYggY4hKON60bWvci3VAMZz88cn.p3yji7hXk/Uq', 'A.M.S.', 'Athawada', 'Male', 'industry.head@pannalaps.lk', '', '', '', '773301288', 'Head Of Department', 'Head Office', '0000-00-00', '2025-06-04 08:22:20', '2025-11-16 12:10:43', 45.0, 0, 21.0, 24.0, 1, 1),
(131, 'revenuehead', '$2y$10$VVMlp7GiErA09WzMmVLmkOHPkEoxvvCLoPOEDl7bBw.v0TBaJWMD2', 'P.M.A.S.', 'Anushka Pathiraja', 'Female', 'revenue.head@pannalaps.lk', '', '', '', '762939926', 'Head Of Department', 'Head Office', '0000-00-00', '2025-06-04 08:23:10', '2025-11-16 12:07:48', 45.0, 0, 21.0, 24.0, 2, 1),
(132, 'communityhead', '$2y$10$4urIh6j43hIP/aDJtNDk7uHwOe7bcvxGCcDaUqxjgcmIelDdOZ.62', 'Kasun', 'Bandara', 'Male', 'community.head@pannalaps.lk', '', '', '', '713539013', 'Head Of Department', 'Head Office', '0000-00-00', '2025-06-04 08:23:49', '2025-11-16 12:09:26', 45.0, 0, 21.0, 24.0, 3, 1),
(133, 'accountshead', '$2y$10$lb0vjyJmdacOY.GzTs4r2e5IV.Kw.U7D1rb6anoW73AoEDnmHHRGW', 'H.M.S', 'Shantha', 'Male', 'accounts.head@pannalaps.lk', '', '', '', '775408321', 'Head Of Department', 'Head Office', '0000-00-00', '2025-06-04 08:24:23', '2025-11-16 11:02:16', 45.0, 0, 21.0, 24.0, 4, 1),
(134, 'institutionshead', '$2y$10$FyaUtlq9ParMzTsXVeGuf.NtNdFNKREE16/hd.ZVu5Az7zbVfvwJi', 'L.B.C.S', 'Jayasekara', 'Male', 'institutions.head@pannalaps.lk', '', '', '', '710130836', 'Head Of Department', 'Head Office', '0000-00-00', '2025-06-04 08:25:04', '2025-11-16 11:03:15', 45.0, 0, 21.0, 24.0, 5, 1),
(135, 'headps', '$2y$10$hikXuhXMw1ChSK9McolhXOxeoHbuKVW6y2eRujI.VnE8yxEvQjYuy', 'Head of', 'Pradeshiya Sabha (PS)', 'Female', 'head.ps@pannala.ps.gov.lk', '', '', '', '0', '', 'Head Office', '0000-00-00', '2025-06-05 05:20:07', '2025-10-03 08:07:08', 45.0, 0, 21.0, 24.0, 6, 3),
(136, 'headauth', '$2y$10$qnWlK.HG54d79HcYwrxYHOBxOdiFppleKXfTcQLAGA5igeDYU0UUi', 'Authorized', 'Officer', 'Male', 'head.auth@pannala.ps.gov.lk', '', '', '', '0', '', 'Head Office', '0000-00-00', '2025-06-05 05:21:17', '2025-10-03 08:08:09', 45.0, 0, 21.0, 24.0, 6, 5),
(137, 'leaveofficer', '$2y$10$1T15ZX2MMhpk2txaSbuaT.wiQmfAj2Ak45xRExRGPMAe5wCMWJQEK', 'Leave Officer', 'Head Office', '', 'leave.officer@pannala.ps.gov.lk', '', '', '', '0', '', 'Head Office', '0000-00-00', '2025-06-05 06:08:55', '2025-10-03 08:08:18', 45.0, 0, 21.0, 24.0, 6, 8),
(142, 'headsuboffice', '$2y$10$TDbNMUOeZcMufW7kaD8lmeUvXmtx.bQkdmeVcW5JY.b3WFSL39i9m', 'Head of', 'Suboffice', '', 'head.suboffice@pannala.ps.gov.lk', '', '', '', '0', '', 'Pannala Sub-Office', '2025-06-01', '2025-06-12 11:16:17', '2025-10-03 08:10:08', 45.0, 0, 21.0, 24.0, 6, 9),
(143, 'authsuboffice', '$2y$10$kf8aYxlDqL8wPEhrBw.VruVKSiDwJFdFQD.8kfOT6yfcKzzCCDuxG', 'Sub Office', 'Authorized Officer', '', 'auth.suboffice@pannala.ps.gov.lk', '', '', '', '0', '', 'Pannala Sub-Office', '2025-06-01', '2025-06-12 11:17:26', '2025-10-03 08:10:12', 45.0, 0, 21.0, 24.0, 6, 6),
(144, 'leaveofficersub', '$2y$10$zuxOyKVXizpksZGcE8n8ZurcRCGBos4CGaVAIJ4dicDJMojqzk8lG', 'Leave Officer', 'Sub Office', 'Male', 'leave.officersub@pannala.ps.gov.lk', '', '', '', '0', 'SubOffice Leave Officer\r\n', 'Pannala Sub-Office', '0000-00-00', '2025-06-12 11:18:04', '2025-10-03 08:10:16', 45.0, 0, 21.0, 24.0, 6, 10),
(152, 'dept', '$2y$10$gITThuy99XdWO/Yx2l8UgOYirx8GY50vCx2EyQZiUWCdk25qPasz.', 'Department PS', 'PS', 'Male', 'dept@pannalaps.lk', '', '', '', '0', '', 'Head Office', '2025-10-03', '2025-10-03 07:59:05', '2025-10-03 07:59:05', 45.0, 0, 21.0, 24.0, 7, 1),
(155, 'testusr', '$2y$10$se9D.xGgV8Wx4JvtR6.rD.olXd455FMxGdLjjvX2y2Nngq5MJqhu.', 'test', 'user', 'Male', 'testusr@pannalaps.lk', '', '', '901', '0', '', 'Head Office', '2025-10-03', '2025-10-03 08:27:59', '2025-10-03 08:37:47', 45.0, 0, 21.0, 24.0, 7, 2),
(156, 'newuser', '$2y$10$mdMqz/ZtbJl9Nvvp1a17J.f6eZ9ydkzo.GBNgjR0eWn/4ZACDO6Z2', 'new', 'user', 'Male', 'newuser@pannalaps.lk', '730/2\r\nMadinnagoda', '', '', '778439871', 'Employee', 'Head Office', '2025-10-03', '2025-10-03 08:58:49', '2025-10-03 09:02:25', 45.0, 0, 21.0, 24.0, 7, 2),
(157, 'somathilaka', '$2y$10$WiFI1BOla.rNjVPQijPHeuIkPoANP0mZNSpyMk4v8BC7jVkrAjFUa', 'U.P.I.M.', 'Somathilaka', 'Male', 'somathilaka@pannalaps.lk', '', '', '37', '740839402', 'Employee', 'Head Office', '0000-00-00', '2025-11-16 08:12:03', '2025-11-16 08:45:48', 45.0, 0, 21.0, 24.0, 1, 2),
(158, 'gunawardana', '$2y$10$L1cVB9aMViqFql4t2eP4Zu6btvjXgTihzLGh8NnlwGeSFtPTjoDai', 'S.M.S.U.', 'Gunawardana', 'Male', 'gunawardana@pannalaps.lk', '', '', '41', '767986451', 'Employee', 'Head Office', '2025-04-21', '2025-11-16 08:44:54', '2025-11-16 08:44:54', 45.0, 0, 21.0, 24.0, 2, 2),
(159, 'swarnamali', '$2y$10$9LiSKvkofLd5pLYNLf7vL.FstgHF0UiMSd4z9QzjVrNW3ZDSlNS4a', 'A.M.J.', 'Swarnamali', 'Female', 'swarnamali@pannalaps.lk', '', '', '38', '704137253', 'Employee', 'Head Office', '2025-04-21', '2025-11-16 08:44:54', '2025-11-16 08:44:54', 45.0, 0, 21.0, 24.0, 2, 2),
(160, 'senanayaka', '$2y$10$uZ8XPr5rj4F0yujvHJ4xU.iwbPlbowAHhlvPHm4/Wcyy0dgaDF5JW', 'S.M.H.C.', 'Senanayaka', 'Male', 'senanayaka@pannalaps.lk', '', '', '', '716577576', 'Employee', 'Head Office', '2025-04-21', '2025-11-16 08:44:54', '2025-11-16 08:44:54', 45.0, 0, 21.0, 24.0, 2, 2),
(161, 'wathsala', '$2y$10$39BTQ7SG0Y2x1AISNLWneOTeIb7OJn58sA.TXgaitI5T54GKo.vN2', 'S.M.A.', 'Wathsala Sewwandi', 'Female', 'wathsala@pannalaps.lk', '', '', '', '762992981', 'Employee', 'Head Office', '2025-04-21', '2025-11-16 08:44:54', '2025-11-16 08:44:54', 45.0, 0, 21.0, 24.0, 2, 2),
(162, 'basnayaka', '$2y$10$NRMxGN/kol.deMr7cvpzjeDxWwAt.Wf3/jZlEDCtUvFm8d89ztXAK', 'B.M.R.N.', 'Basnayaka', 'Male', 'basnayaka@pannalaps.lk', '', '', '40', '712821259', 'Employee', 'Head Office', '2025-04-21', '2025-11-16 09:17:53', '2025-11-16 09:17:53', 45.0, 0, 21.0, 24.0, 4, 2),
(163, 'dangolla', '$2y$10$IXj/TCwhKtAojTJltlM7q.vwpAEx84J9FDiNtlBs1QXN0Q1wJwOEy', 'P.D.M.G.', 'Dangolla', 'Male', 'dangolla@pannalaps.lk', '', '', '42', '776379009', 'Employee', 'Head Office', '2025-04-21', '2025-11-16 09:17:53', '2025-11-16 09:17:53', 45.0, 0, 21.0, 24.0, 4, 2),
(164, 'karunarathna', '$2y$10$FbF8jtnvGWZnpm4g6iCEeusuBBBDkahNX5n78trox45V5pJ2w.mfu', 'K.K.U.D.', 'Karunarathna', 'Male', 'karunarathna@pannalaps.lk', '', '', '36', '767923968', 'Employee', 'Head Office', '2025-04-21', '2025-11-16 09:17:53', '2025-11-16 09:17:53', 45.0, 0, 21.0, 24.0, 4, 2),
(165, 'sundarapperuma', '$2y$10$E.M/hCGU14opwJVlM0zaEuYcCEIxwzY7sCu2Dg/coVPIYopKElB86', 'S.M.B.M.', 'Sundarapperuma', 'Male', 'sundarapperuma@pannalaps.lk', '', '', '39', '775678585', '', 'Head Office', '0000-00-00', '2025-11-16 09:25:05', '2025-11-16 09:25:05', 45.0, 0, 21.0, 24.0, 5, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wp_departments`
--
ALTER TABLE `wp_departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `wp_designations`
--
ALTER TABLE `wp_designations`
  ADD PRIMARY KEY (`designation_id`);

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
-- Indexes for table `wp_manual_leave_logs`
--
ALTER TABLE `wp_manual_leave_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `wp_pradeshiya_sabha_users`
--
ALTER TABLE `wp_pradeshiya_sabha_users`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wp_departments`
--
ALTER TABLE `wp_departments`
  MODIFY `department_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `wp_designations`
--
ALTER TABLE `wp_designations`
  MODIFY `designation_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `wp_leave_notifications`
--
ALTER TABLE `wp_leave_notifications`
  MODIFY `notification_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_leave_request`
--
ALTER TABLE `wp_leave_request`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `wp_manual_leave_logs`
--
ALTER TABLE `wp_manual_leave_logs`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `wp_pradeshiya_sabha_users`
--
ALTER TABLE `wp_pradeshiya_sabha_users`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- Constraints for dumped tables
--

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

--
-- Constraints for table `wp_pradeshiya_sabha_users`
--
ALTER TABLE `wp_pradeshiya_sabha_users`
  ADD CONSTRAINT `wp_pradeshiya_sabha_users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `wp_departments` (`department_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
