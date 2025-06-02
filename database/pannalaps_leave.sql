-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 11:35 AM
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
-- Database: `pannalaps_leave`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_departments`
--

CREATE TABLE `wp_departments` (
  `department_id` int(11) NOT NULL,
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
(5, 'Institutions Division');

-- --------------------------------------------------------

--
-- Table structure for table `wp_designations`
--

CREATE TABLE `wp_designations` (
  `designation_id` int(11) NOT NULL,
  `designation_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_designations`
--

INSERT INTO `wp_designations` (`designation_id`, `designation_name`) VALUES
(1, 'HOD'),
(2, 'Employee'),
(3, 'Head of PS'),
(4, 'Head of suboffice'),
(5, 'Head office Authorized Officer'),
(6, 'Sub office Authorized Officer'),
(7, 'Admin'),
(8, 'Leave Officer');

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
  `status` int(11) DEFAULT NULL COMMENT '1-pending, 2-accept, 3-reject',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sub_office` varchar(255) NOT NULL,
  `rejection_remark` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_leave_request`
--

INSERT INTO `wp_leave_request` (`request_id`, `user_id`, `leave_type`, `leave_start_date`, `leave_end_date`, `number_of_days`, `reason`, `substitute`, `status`, `created_at`, `updated_at`, `sub_office`, `rejection_remark`, `department_id`) VALUES
(43, 14, 'Duty Leave', '2025-05-28', '2025-05-30', 3, 'Go to matara for semina', 'A.P. Jayatissa (53)', 3, '2025-05-28 11:01:06', '2025-05-28 11:25:22', '0', 'no', NULL),
(44, 91, 'Casual Leave', '2025-04-01', '2025-04-03', 3, 'go home gota', '', 2, '2025-05-29 04:53:46', '2025-05-29 04:53:46', 'Head Office', NULL, NULL),
(45, 19, 'Casual Leave', '2025-05-29', '2025-05-30', 2, 'hi', '', 2, '2025-05-29 05:09:54', '2025-05-29 05:09:54', 'Head Office', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wp_manual_leave_logs`
--

CREATE TABLE `wp_manual_leave_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `number_of_days` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `action_taken_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_manual_leave_logs`
--

INSERT INTO `wp_manual_leave_logs` (`log_id`, `admin_id`, `user_id`, `leave_type`, `number_of_days`, `reason`, `action_taken_at`) VALUES
(1, 3, 91, 'Casual Leave', 3, 'go home gota', '2025-05-29 04:53:46'),
(2, 3, 19, 'Casual Leave', 2, 'hi', '2025-05-29 05:09:54');

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
  `service_number` varchar(50) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `designation` varchar(255) NOT NULL,
  `head_of_department` varchar(255) NOT NULL,
  `sub_office` varchar(255) NOT NULL,
  `date_of_joining` date NOT NULL,
  `user_role` enum('Employee','HOD','Admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `leave_balance` int(11) NOT NULL DEFAULT 20,
  `duty_leave_count` int(11) DEFAULT 0,
  `casual_leave_balance` int(11) DEFAULT 7,
  `sick_leave_balance` int(11) DEFAULT 7,
  `department_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_pradeshiya_sabha_users`
--

INSERT INTO `wp_pradeshiya_sabha_users` (`ID`, `username`, `password`, `first_name`, `last_name`, `gender`, `email`, `birthdate`, `address`, `NIC`, `service_number`, `phone_number`, `designation`, `head_of_department`, `sub_office`, `date_of_joining`, `user_role`, `created_at`, `updated_at`, `leave_balance`, `duty_leave_count`, `casual_leave_balance`, `sick_leave_balance`, `department_id`, `designation_id`) VALUES
(3, 'admin', '$2y$10$HKmp9EJLq8JSu.dnE3skKumofYSkE18hcRsEbDGio4ddu8iNqggOy', 'Admin', 'User', 'Female', 'admin@gmail.com', '2025-04-20', 'Pannala Pradeshiya Sabha', '324534534534', 'PS01', '', 'Administrator', 'J.A.S. Jayasingha', 'Head Office', '2025-04-01', 'Admin', '2025-03-27 08:28:36', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(7, 'sub-office-admin', '$2y$10$nfNBW6kvCn3roxM.3rx7Q.P1w10QrZmL0fGm5v01o79Ie36Y3nS4C', 'Sub Office', 'Admin', 'Male', 'suboffice@pannalaps.com', '0000-00-00', '', '', 'PS02', '0711111111', 'Sub-Office Admin', '', 'Pannala Sub-Office', '2023-01-01', 'Admin', '2025-03-28 06:07:20', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(8, 'makandura', '$2y$10$lKnb5/0sKwmf6NG.SGuN7OT1mk7NQO9VY.PFsgvLkCEUY6bhnwV0K', 'Makandura', 'User', 'Male', 'makandura@pannalaps.com', '0000-00-00', '', '', 'PS03', '0722222222', 'Officer', '', 'Makandura Sub-Office', '2022-06-15', 'Admin', '2025-03-28 06:07:20', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(9, 'yakkwila', '$2y$10$zjm8ikYevIZqKr/RR00cxOKgNWVaPR.MRJ.W/jJUk.advhVIicdXm', 'Yakkwila', 'User', 'Male', 'yakkwila@pannalaps.com', '0000-00-00', '', '', 'PS04', '0733333333', 'Supervisor', '', 'Yakkwila Sub-Office', '2021-03-10', 'Admin', '2025-03-28 06:07:20', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(10, 'hamangalla', '$2y$10$QgZ6K03jSU17zvPQLJX1oOppdiYy50rOFJHkuXM2JfHFj.nr4emMW', 'Hamangalla', 'User', 'Male', 'hamangalla@pannalaps.com', '0000-00-00', '', '', 'PS05', '0744444444', 'Manager', '', 'Hamangalla Sub-Office', '2020-11-20', 'Admin', '2025-03-28 06:07:20', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(14, '', '$2y$10$UsCbE25Hq1DexRXRW0QJdut4cKqeQ9ylIP2naVocXE4WGJ6Qw/Hom', 'Yohani', 'Abeykoon', 'Female', 'yohani@pannalaps.com', '1999-07-25', '730/2\r\nMadinnagoda', '199970704599', 'PS06', '0778439871', 'Team Lead', 'Danushka Gangoda', 'Head Office', '2022-08-22', 'Employee', '2025-03-31 05:04:46', '2025-05-28 11:01:06', 45, 3, 21, 24, NULL, NULL),
(19, 'jayasinghe', '$2y$10$P3Z7X3t5KwkNOuUoV9R6.uWN6iMXWlF7yoQYHjJjIlqKUKXqadYLe', 'J.A.S.', 'Jayasinghe', 'Female', 'jayasinghe@pannalaps.lk', '2025-04-21', '', '', '1', '779082143', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:00:40', '2025-05-29 05:09:54', 45, 0, 19, 24, NULL, NULL),
(20, 'jayasekara', '$2y$10$lCB1BfbDCS5m6aN6B.N5ZOGeqPmhRQA6PGCbVat5JA3VoVveqmIAO', 'L.B.C.S.', 'Jayasekara', 'Female', 'jayasekara@pannalaps.lk', '2025-04-21', '', '', '2', '710130836', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:02:04', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(21, 'ilangkoon', '$2y$10$bXOD4b60f30QbSV80SoHH.TMK4pYx2.g3r49CbsOVazaNEFC5vCGS', 'I.M.G.P.', 'Ilangkoon', 'Female', 'ilangkoon@pannalaps.lk', '2025-04-21', '', '', '3', '707570827', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:10:43', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(22, 'gunathilaka', '$2y$10$1QUs2lbdjB4JWptliM7DTO7z8p0lHBUwQSGoUhGZJ9Y2nWuIzzzN.', 'P.A.H.', 'Gunathilaka', 'Female', 'gunathilaka@pannalaps.lk', '2025-04-20', '', '', '4', '772202568', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:12:14', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(23, 'subhasinghe', '$2y$10$kkOwxtXPHXJoq5QshyCUxuMVfOQDlx1VJf5kSDxHHmtAE4y0OBWQC', 'S.A.D.M.', 'Subhasinghe', 'Female', 'subhasinghe@pannalaps.lk', '2025-04-20', '', '', '5', '760206424', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:13:43', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(24, 'ratnamalala', '$2y$10$6nmwCgdQiknR88fnos9bFOqHmwxx5XrIlXMMPk7uK12rPyudbupRy', 'D.M.I.U.', 'Ratnamalala', 'Female', 'ratnamalala@pannalaps.lk', '2025-04-20', '', '', '6', '769093976', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:15:10', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(25, 'anuruddhika', '$2y$10$ctJ..w0uBtJLnAoaKBBi8e/75LX629SLpDfp8stslCn8IPfmKbfTG', 'W.A.K.S.', 'Anuruddhika', 'Female', 'anuruddhika@pannalaps.lk', '2025-04-21', '', '', '7', '726555116', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:16:38', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(26, 'this', '$2y$10$CXLs1RHVxls6pzb2xoHdVeOXWzvHDwt01OtQZGisK6dZh7E56QtLu', 'A.M.N.D.', 'Wijeratne', 'Female', 'this@pannalaps.lk', '2025-04-21', '', '', '8', '775539644', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:17:52', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(27, 'dassanayake', '$2y$10$EGG27oIAPWlLsHxPG.zWWeaP72pdTtTNXIfk0LEyTJruH2Xzb/bIG', 'M.D.M.A.P.K.', 'Dassanayake', 'Female', 'dassanayake@pannalaps.lk', '2025-04-21', '', '', '9', '711960486', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:19:19', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(28, 'basnayake', '$2y$10$in76LthaQWsJHr0FBWKTtu/9SCZ12heMRJD1YGzsUMCkNh.wR20YO', 'B.M.S.S.', 'Basnayake', 'Female', 'basnayake@pannalaps.lk', '2025-04-21', '', '', '10', '772169923', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:21:18', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(29, 'wanniarachchi', '$2y$10$9sp1N8gkMBtLMw8mvE3LPOgOTnb4J52WYd9YL42DikXvrZpx32vPe', 'W.A.D.P.', 'Wanniarachchi', 'Female', 'wanniarachchi@pannalaps.lk', '2025-04-21', '', '', '11', '703872749', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:25:38', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(30, 'shantha', '$2y$10$07O4YJK0wYhVZKUWup5M/uCDsqGvEI/AciTnk2hNQP0DYeVh7lDQy', 'H.M.S.', 'Shantha', 'Female', 'shantha@pannalaps.lk', '2025-04-21', '', '', '12', '775408321', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:27:09', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(31, 'heneyaka', '$2y$10$TNyIDfHLYHIsxnlBBU4uBuQPnABWLcqlJKW5HLggTM0PjGnfkrNBu', 'H.A.M.N.', 'Heneyaka', 'Female', 'heneyaka@pannalaps.lk', '2025-04-21', '', '', '13', '771024383', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:28:18', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(32, 'abeysinghe', '$2y$10$bgY/dDGMEYlqphhXJgb2MezPrfxfKiF45ZURciMOyElileE.Qhng2', 'A.M.D.', 'Abeysinghe', 'Female', 'abeysinghe@pannalaps.lk', '2025-04-21', '', '', '15', '771231830', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:30:29', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(33, 'susantha', '$2y$10$AGDnDtour/B17Wh93KC0TehxC9V4l0r02.5etifyR3EY5ZwQQCZ9q', 'A.D.', 'Susantha', 'Female', 'susantha@pannalaps.lk', '2025-04-21', '', '', '16', '777991066', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:31:50', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(34, 'pathiraja', '$2y$10$SG2DUzowFhtGUT6Y7n/cg.fK46GPX1H/eIzThGIfniNXDnHd5MKyy', 'P.M.A.S.Anushka', 'Pathiraja', 'Female', 'pathiraja@pannalaps.lk', '2025-04-21', '', '', '17', '762939926', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:33:53', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(35, 'hemamali', '$2y$10$nJTvMNGfeq9NkYq41YExD.HN864L1trAFkplNhJhABakFOKZ4HC3y', 'A.M.W.', 'Hemamali', 'Female', 'hemamali@pannalaps.lk', '2025-04-21', '', '', '18', '716016490', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:36:12', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(36, 'padmakumari', '$2y$10$XBuvR6Im7yfQHRRqpW7LzOiAG.XsVQxTygc1LG20Lg57j04lbe1Ma', 'N.', 'Padmakumari', 'Female', 'padmakumari@pannalaps.lk', '2025-04-21', '', '', '19', '778801594', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:37:14', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(37, 'mapa', '$2y$10$7KKXDU.3EjCogjVFAS9YA.HavIKXbeRXn8KlDmmUQJcDe4HkyOW0C', 'M.M.I.Thushara', 'Mapa', 'Male', 'mapa@pannalaps.lk', '2025-04-21', '', '', '20', '774008514', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:39:04', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(38, 'jayamaha', '$2y$10$Asn1vL6MEsm469sjSgXsfuWvpYgHaYOIMajPutQr9kWtjrXbGqD8i', 'Chethana Sudheera', 'Jayamaha', 'Male', 'jayamaha@pannalaps.lk', '2025-04-21', '', '', '21', '706799800', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:43:31', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(39, 'kumari', '$2y$10$1G8ULeg3mPvdqpi2aGr7j.FHmFJXQ92GanT0urCkPgrlOIeJ3vg/O', 'J.A.T.P.', 'Kumari', 'Female', 'kumari@pannalaps.lk', '2025-04-21', '', '', '22', '778752279', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:44:29', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(40, 'sanjeevani', '$2y$10$P8vj5MaRh20kn5SMbfYUhuiXNFrXWZLvJ6u8BOnjq1Sq/wVX1JAJu', 'Inoka', 'Sanjeevani', 'Female', 'sanjeevani@pannalaps.lk', '2025-04-21', '', '', '23', '710875047', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:45:30', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(41, 'kumara', '$2y$10$wIyfyRGTrSHLva64Zcig3O8DWhatU7Sib7zIk1TgqU94DQRxc7fKa', 'Chinthaka Saman', 'Kumara', 'Male', 'kumara@pannalaps.lk', '2025-04-21', '', '', '24', '741595462', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:46:49', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(42, 'bandara', '$2y$10$p3V.IR9S7Rnp1F5n.M7EPucXzU9IH.KXDP92/YeRHzCZ0mv5BGHp2', 'Kasun', 'Bandara', 'Male', 'bandara@pannalaps.lk', '2025-04-21', '', '', '26', '713539013', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:51:29', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(43, 'ishani', '$2y$10$35I5QnisK2uruWFWAcODMuG33jhFCdHx.F9fgO1XVLcoqf3gYI//q', 'P.M.N. Ishani', 'Pathiraja', 'Female', 'ishani@pannalaps.lk', '2025-04-21', '', '', '28', '741885491', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:54:34', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(44, 'ranasinghe', '$2y$10$sHQ13iJByDOC/nUUjnMRYu9MLf7/6547GW6iv/BhrxTpRiKESb88W', 'P.S.', 'Ranasinghe', 'Female', 'ranasinghe@pannalaps.lk', '2025-04-21', '', '', '29', '714235543', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:56:03', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(45, 'chinthaka', '$2y$10$FpNa513RnsknI4HKx157ZeSe.6bd.rtwTvNygEC/3S95IZJQYjfFm', 'S.A.U.V.', 'Chinthaka', 'Male', 'chinthaka@pannalaps.lk', '2025-04-21', '', '', '30', '775323401', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:58:03', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(46, 'deepika', '$2y$10$WMuQFejPKik6bM03pavbce/s6z8X6xbT65HPOlDYZ9TrTOyd2Kh6m', 'R.D.R.', 'Deepika', 'Female', 'deepika@pannalaps.lk', '2025-04-21', '', '', '522', '0778420522', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 10:59:56', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(47, 'abeywardena', '$2y$10$eiHJtYnm/MaVq/uxUsmEq.U6ap8S9AO3/jM1amqEUvUW4QZA/qpiu', 'Iresha', 'Abeywardena', 'Female', 'abeywardena@pannalaps.lk', '2025-04-21', '', '', '31', '741678893', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:01:01', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(48, 'karunanayake', '$2y$10$7uNok9tBRZd3KEw81lmsUuZ.YukdX4QuCsJp9W38rqnhDl/nJIHUK', 'K.A.C.', 'Karunanayake', 'Female', 'karunanayake@pannalaps.lk', '2025-04-21', '', '', '516', '0764420320', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:01:44', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(49, 'ratnayake', '$2y$10$ANM36XEqLxsPmEyAPZ306OHsem/ML8cm8ciC37c40b4wIINmrDcbG', 'R.M.T.M.', 'Ratnayake', 'Female', 'ratnayake@pannalaps.lk', '2025-04-21', '', '', '32', '773108541', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:02:16', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(50, 'rasika', '$2y$10$1yhW0A7bg4d1v3DyFyRUBu6Hk0HTPFcPgUNoIucixcfMoPCogp.3.', 'Harshani', 'Rasika', 'Female', 'rasika@pannalaps.lk', '2025-04-21', '', '', '33', '768624785', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:03:46', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(51, 'rajapaksa', '$2y$10$Q0wZN24ubYo4esOWqBhUGuaxFKBT/IPPjAV9lJdRZjh.x/MRQ/zb.', 'R.M.S.D.', 'Rajapaksa', 'Female', 'rajapaksa@pannalaps.lk', '2025-04-21', '', '', '35', '717180099', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:04:44', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(52, 'janakasiri', '$2y$10$jvmIJSnDcKrGR0lzTWqgl.RmKJr/vOmqm5CGR7Wm92cEP.p1sBD.q', 'T.M.N.', 'Janakasiri', 'Female', 'janakasiri@pannalaps.lk', '2025-04-21', '', '', '45', '764578843', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:05:37', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(53, 'senaratne', '$2y$10$eTs.RVq4WGV2nS105Oc/9uIShyXKVtSZMdXPMqZqqBe5WlJwgTsIO', 'N.G.S.', 'Senaratne', 'Female', 'senaratne@pannalaps.lk', '2025-04-21', '', '', '46', '774548773', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:07:07', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(54, 'ayesha', '$2y$10$syqT.hLhKKrMJCScykGHJeJUQqIO6EALTkqvkX.P1/fAh9rUugTa.', 'Chathurie', 'Ayesha', 'Female', 'ayesha@pannalaps.lk', '2025-04-21', '', '', '47', '762612195', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:08:20', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(55, 'wanigasuriya', '$2y$10$KZ0UGUh4HCDGMSlJ/yZ2Se5PbceH/VF3zfCqlAU9WxXlKOaAVEtjW', 'Nandana', 'Wanigasuriya', 'Female', 'wanigasuriya@pannalaps.lk', '2025-04-21', '', '', '48', '762092831', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:09:24', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(56, 'jayasuriya', '$2y$10$BzzGzUJ01W3Gkyi5DaeeX.jMAlHS5Y3sOarxY5mXrzL8qRR5RrvKa', 'K.', 'Jayasuriya', 'Female', 'jayasuriya@pannalaps.lk', '2025-04-21', '', '', '49', '773788973', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:15:36', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(57, 'chandrasiri', '$2y$10$QhSiJqi4MkET4QWNwfzItuPB2DzB3d9OL2W5iAqloDq.zB7rXbIAm', 'A.P.S.', 'Chandrasiri', 'Male', 'chandrasiri@pannalaps.lk', '2025-04-21', '', '', '50', '769108091', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:16:51', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(58, 'dharmasiri', '$2y$10$T1Xy05LbMMycwBekZ0DLhuCP7GLGYMpzDD5MBA4MKbOcZa3LPeEIq', 'Lalith', 'Dharmasiri', 'Male', 'dharmasiri@pannalaps.lk', '2025-04-21', '', '', '51', '762736090', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:18:02', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(59, 'pathiratne', '$2y$10$GRKhaRkxRqCEjz6dL9iZGuzrslB0yWjHrynuzLbpDZaobf8vGd3/G', 'G.P.I.J.', 'Pathiratne', 'Male', 'pathiratne@pannalaps.lk', '2025-04-21', '', '', '52', '776002965', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:20:25', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(60, 'jayatissa', '$2y$10$I52AWlIkBPyTPUW3PrpUweGq2cgu5ipLrkWXfPGV8NcDQbPHuAYPK', 'A.P.', 'Jayatissa', 'Male', 'jayatissa@pannalaps.lk', '2025-04-21', '', '', '53', '773441725', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:21:55', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(61, 'anjana', '$2y$10$hk.OlHHjAVQCvBGpmGAoC.CALh4EkN4tTUX3f5iOjFBt2hS2emapy', 'U.V.Dhammi', 'Anjana', 'Female', 'anjana@pannalaps.lk', '2025-04-21', '', '', '524', '0784322183', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:22:55', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(62, 'samaraweera', '$2y$10$8BLeKbQCdKGNeHs4MBwUkeTww27kWdOZBPFu0bJoHfJV3dHTnzeq6', 'W.A.C.Y.', 'Samaraweera', 'Male', 'samaraweera@pannalaps.lk', '2025-04-21', '', '', '515', '0776059073', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:24:01', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(63, 'ajith', '$2y$10$XG.wnYNHuuvDtl6DM698p.zYKm20mfGIpL0n04/rly1Zp3M/vyf4q', 'R.G.Ajith', 'Kumara', 'Male', 'ajith@pannalaps.lk', '2025-04-21', '', '', '54', '772739050', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:25:00', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(64, 'perera', '$2y$10$hX8wTk8BdvjWeH0cGOpCh./cq0Jv8qFgSLUh.tnHJjYUPn/kdngnK', 'W.I.A.', 'Perera', 'Male', 'perera@pannalaps.lk', '2025-04-21', '', '', '509', '', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:25:07', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(65, 'dmg', '$2y$10$kMcHzzYYjb6YrYUwvzHbeOqITdOMAUarh5YRy2tnTKb6D8oEjDnhW', 'D.M.G.', 'Dassanayake', 'Male', 'dmg@pannalaps.lk', '2025-04-21', '', '', '504', '0704918440', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:26:39', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(66, 'fernando', '$2y$10$Cj/ymjut42vH2cdn7xjaou2GPS34yKdG8IkopuyiICYVQFlelACku', 'K.N.J.', 'Fernando', 'Male', 'fernando@pannalaps.lk', '2025-04-21', '', '', '300', '', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:27:20', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(67, 'arunasiri', '$2y$10$2TAQcgMwzbx8D.N1yOXYheIdTKzE7LAJp/q37Bz5nb5vfejs9ohSG', 'Charith', 'Arunasiri', 'Male', 'arunasiri@pannalaps.lk', '2025-04-21', '', '', '55', '765394563', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:27:29', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(68, 'mahesh', '$2y$10$acd29J0cFth683UmtNwFh.Aau0kJbjIHghcTFjL10vy7SF5e6RyPa', 'Thilina Mahesh', 'Kumara', 'Male', 'mahesh@pannalaps.lk', '2025-04-21', '', '', '56', '778017604', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:29:42', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(69, 'priyantha', '$2y$10$JUQ5qv9XWVp88hIHRISv9.dwxFkPTW/s0EXGxGsUo6akOjLaQ/.0a', 'H.M. Lakmini', 'Priyantha', 'Male', 'priyantha@pannalaps.lk', '2025-04-21', '', '', '57', '778665376', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:32:33', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(70, 'adhikari', '$2y$10$QNA4/oCsAGoYbtYIx4.8g.yNAp9oAJ1ViUeUoFywf6YjrEUcznzuy', 'A.M.G.D.', 'Adhikari', 'Male', 'adhikari@pannalaps.lk', '2025-04-21', '', '', '402', '0761616928', '', '', 'Head Office', '2025-04-28', 'Employee', '2025-04-21 11:33:44', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(71, 'lansakkara', '$2y$10$QM2nwA6AgmHuf6YilgCUGebDkew/JJs/8thTgAwPl7hjVd1V1Ddhe', 'L.M.P.M.', 'Lansakkara', 'Male', 'lansakkara@pannalaps.lk', '2025-04-21', '', '', '401', '0761886729', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:34:53', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(72, 'ranaweera', '$2y$10$kSx4XCi9fYcTmqqAhrcNGu9u040S3arAf6qeGAYFK7TucCpMeSlsS', 'M.G.', 'Ranaweera', 'Male', 'ranaweera@pannalaps.lk', '2025-04-21', '', '', '5', '773406006', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:36:10', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(73, 'dilshan', '$2y$10$A.24RTg.f7l9Lf.0CGSOkOSo4JmdxHOv4IBACkBJXUt7UwsN4QzDO', 'R.M.P.S.', 'Dilshan', 'Male', 'dilshan@pannalaps.lk', '2025-04-21', '', '', '', '0779603381', '', '', 'Head Office', '2025-04-21', 'Employee', '2025-04-21 11:52:36', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(74, 'rmps', '$2y$10$AGEGkNTmr536E8nPL75ELuxTTr7OnavQ12TxbPSfsbMZxXiDVpUg.', 'R.M.P.S.', 'Dilshan', 'Male', 'rmps@pannalaps.lk', '2025-04-22', '', '', '825', '0779603381', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:52:36', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(75, 'indika', '$2y$10$iQ8aHPwZSSO7BkQMiSZefekdU9anBW4E/WfRAtBwCBV6lwwS1WmmO', 'Mohan', 'Indika', 'Male', 'indika@pannalaps.lk', '2025-04-22', '', '', '58', '754413021', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:52:37', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(76, 'kumuditha', '$2y$10$9G/rmlS2lyzjS5x9amTjUeNkvXyfITKNju1.fI6nB16xBK68Jt.4S', 'R.D.S.S.', 'Kumuditha', 'Male', 'kumuditha@pannalaps.lk', '2025-04-22', '', '', '', '075-9533429', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:53:23', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(77, 'wijesinghe', '$2y$10$.TfWiAQWCsxTw9NXn5P.1O7OfeU7/hFbJpjCNRBo.RL7GHb2KG0cG', 'K.A. Sarath', 'Wijesinghe', 'Male', 'wijesinghe@pannalaps.lk', '2025-04-22', '', '', '59', '760026828', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:53:43', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(78, 'liyanarachchi', '$2y$10$8UOMAABASMtZk8TgWdKm1.bSG4fY/EGCYMPBE75nyiUAVtzdfml02', 'L.A.S.', 'Liyanarachchi', 'Male', 'liyanarachchi@pannalaps.lk', '2025-04-22', '', '', '600', '0766845853', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:54:15', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(79, 'samaratunga', '$2y$10$dH1tyNYJvMVmZBZGD.SXBu8rsLuzOs4zMIuL0D46uY85k0QBWlSfu', 'Charaka Shyan', 'Samaratunga', 'Male', 'samaratunga@pannalaps.lk', '2025-04-22', '', '', '60', '779217537', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:54:38', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(80, 'sinhapruthivi', '$2y$10$tpq6noREfw35KeBDGD1O1O5E15c0s20E5qoWMuXeqGnoEvd2sv.YG', 'S.A.W.C.', 'Sinhapruthivi', 'Male', 'sinhapruthivi@pannalaps.lk', '2025-04-22', '', '', '601', '0768088341', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:55:08', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(81, 'sanjaya', '$2y$10$OZWba7DJewVhFq9WOka6Ke.ul9L/HdPsNWr1o9IaRk2KjTXEBXImy', 'D.M. Prabath', 'Sanjaya', 'Male', 'sanjaya@pannalaps.lk', '2025-04-22', '', '', '61', '778827067', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:55:49', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(82, 'bpps', '$2y$10$aRSrIlkiaX9BP7zn1M4YhOn3aT6ewQhgq7MkKTVwNK6iKACsg4rme', 'B.P.P.S.', 'Kumara', 'Male', 'bpps@pannalaps.lk', '2025-04-22', '', '', '602', '0786915155', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:56:34', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(83, 'sarath', '$2y$10$Qr2yDRQ2zkAq/AZO.QuBFOu1yohlB0nGH7PPNJ7A7N/1EIcQMWCUK', 'Mahinda Sarath', 'Kumara', 'Male', 'sarath@pannalaps.lk', '2025-04-22', '', '', '62', '774064143', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:57:17', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(84, 'smpv', '$2y$10$DYkGe1/y.Q2BjntHKxXbjuoEq8nBRYySjLdUD4zS9NV.w1kpFzw3m', 'S.M.P.V.', 'Subhasinghe', 'Male', 'smpv@pannalaps.lk', '2025-04-22', '', '', '102', '0771477613', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:58:12', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(85, 'thilakashanthi', '$2y$10$5niGBZE13OZN7uqFcC.fHuN.EkXFrTkKVCuuyDj50KTG.IXuSP3Re', 'R.K.M. Enoka', 'Thilakashanthi', 'Male', 'thilakashanthi@pannalaps.lk', '2025-04-22', '', '', '38', '742067244', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:58:17', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(86, 'gonavila', '$2y$10$QAY1/ePOzXMwZYjMSlys5uL79FewNgk1W7oNGSid3AAHXn6UdhTxq', 'G.A.A.D.P.', 'Gonavila', 'Male', 'gonavila@pannalaps.lk', '2025-04-22', '', '', '209', '0702428186', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:59:11', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(87, 'seneviratne', '$2y$10$HsoUfR89LP9vdXayTPIUVe5ya/J.4LtS5QPW9Q8E7qB3MMQuEN2nG', 'S.P.', 'Seneviratne', 'Male', 'seneviratne@pannalaps.lk', '2025-04-22', '', '', '49', '778375868', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 04:59:25', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(88, 'janaki', '$2y$10$S01TaWBKEw.CjPx7ePGYZODm.eicyfd9e7xds1mM1JJwunpfMMHFy', 'P.A.', 'Janaki', 'Female', 'janaki@pannalaps.lk', '2025-04-22', '', '', '', '0740411491', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:00:19', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(89, 'smgr', '$2y$10$FUsCuVmj/0gKMmsjE9mau.0rbYn2f8ACTDzOHmiOPLivzh821hG8.', 'S.M.G.R.', 'Ratnayake', 'Male', 'smgr@pannalaps.lk', '2025-04-22', '', '', '66', '763548218', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:02:07', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(90, 'tharushi', '$2y$10$OQGD576Qon.K0Fk.p9ySTOGjL6JPLQXhigH8SeZ8S.MSDdfg/is4G', 'D.P. Tharushi Niwarthana', 'Dassanayake', 'Female', 'tharushi@pannalaps.lk', '2025-04-22', '', '', '67', '', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:11:40', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(91, 'jayakody', '$2y$10$NZgIWRKoRes1S03GYgPH2OdaNm/MMwuTQ8qw0f1CJIbmRPvxzSitu', 'J.A.R.D.', 'Jayakody', 'Male', 'jayakody@pannalaps.lk', '2025-04-22', '', '', '39', '770552675', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:12:50', '2025-05-29 04:53:46', 45, 0, 18, 24, NULL, NULL),
(92, 'dissanayake', '$2y$10$GpZo3NU1KxmLWUKoMljH2./UtH5rOxeXk9bv3HENBCqXtiusRza.u', 'D.M. Sujeewa Priyanka', 'Dissanayake', 'Male', 'dissanayake@pannalaps.lk', '2025-04-22', '', '', '205', '774086732', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:13:10', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(93, 'eranga', '$2y$10$CW9OVsiBB3ZxBm4YxOPzP.3PIcIDFjrhFz/hAT1SUKmhyLXIbCdfu', 'S.A. Shanaka', 'Eranga', 'Male', 'eranga@pannalaps.lk', '2025-04-22', '', '', '701', '779139280', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:14:06', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(94, 'abeysekera', '$2y$10$nw/paTgYP1goZc2WXI3wM.QBZOKz6jHpsA7rKyQCBxWVUgAQNj332', 'A.W.A. Deepani', 'Abeysekera', 'Female', 'abeysekera@pannalaps.lk', '2025-04-22', '', '', '38', '778667480', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:14:16', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(95, 'jayasundara', '$2y$10$TMw5UsUeS71PlEvfEzYO5eeK6pUk6w4hajYGYAYxhlAJzZLmxsyy2', 'J.M.Y. Chandrani', 'Jayasundara', 'Female', 'jayasundara@pannalaps.lk', '2025-04-22', '', '', '204', '787708969', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:16:18', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(96, 'muthukumarana', '$2y$10$oJ04jWatjiU5a0R4efppVeO7FH7n6NmkS3Y2wn0AN.8KU0pqI7or2', 'D.P.N.', 'Muthukumarana', 'Male', 'muthukumarana@pannalaps.lk', '2025-04-22', '', '', '500', '729590649', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:17:04', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(97, 'liyanage', '$2y$10$Z.0aviUp77MaDbb0QH1hJuJ7jr8dnv/YtidgMFf2NwoT1VQRNADx2', 'L.P. Anoma', 'Liyanage', 'Female', 'liyanage@pannalaps.lk', '2025-04-22', '', '', '203', '776625378', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:17:13', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(98, 'senarath', '$2y$10$xsVAamEMy0THfq9Yuvfa..OvXc7WsmufnLLCDQogkL9cMEMTf3KMO', 'S.A.P.', 'Senarath', 'Male', 'senarath@pannalaps.lk', '2025-04-22', '', '', '83', '773983327', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:18:27', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(99, 'tmnd', '$2y$10$XAv8buLbSB1Ci/TscJ5hpuGdUO4ZTW.yFo2ehm2hUh9Bx5YYT6o9u', 'T.M.N.D.', 'Kumari', 'Female', 'tmnd@pannalaps.lk', '2025-04-22', '', '', '503', '713092733', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:19:30', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(100, 'ruwanmali', '$2y$10$14ddNHI3FXnYQymDa2HeOeaCoHFcLK.4F86GhDsKDb5ivJo8KFSDG', 'M.P.', 'Ruwanmali', 'Female', 'ruwanmali@pannalaps.lk', '2025-04-22', '', '', '202', '774926139', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:20:06', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(101, 'hms', '$2y$10$ac92iXd5O63ORJWVWmA25O8fCqE3qJ0mAPba2rRwxqVvq1BFg.6TS', 'H.M.S.', 'Kumari', 'Female', 'hms@pannalaps.lk', '2025-04-22', '', '', '506', '774779491', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:21:51', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(102, 'nalani', '$2y$10$Du3vwu4C0qxU1VPi6foEReqve2qn2tLKOPXVu1vfsh83TiXbwBDBi', 'D.L. Nalani', 'Wanigasuriya', 'Female', 'nalani@pannalaps.lk', '2025-04-22', '', '', '201', '776106079', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:22:06', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(103, 'harischandra', '$2y$10$kvxL3lE8lC71KAH3Jq3UZOBZOaIyqyKflptwByElkqfyfjSTueVxu', 'K.P.', 'Harischandra', 'Male', 'harischandra@pannalaps.lk', '2025-04-23', '', '', '73', '761032264', '', '', 'Head Office', '2025-04-23', 'Employee', '2025-04-22 05:22:59', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(104, 'lakshika', '$2y$10$BWdD9N3AhjGj9W5LJKv.TuTQNkL695mMub7UbBrAtffK2Cymwg5Ie', 'K.M.M.M.', 'Lakshika', 'Female', 'lakshika@pannalaps.lk', '2025-04-22', '', '', '513', '764448404', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:23:06', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(105, 'padmasiri', '$2y$10$MwtzUZXcPh6rhUP2B46paeTrYtHM6uS08xhsZSgETBnTwL4P49Sra', 'W.P. Janadara', 'Padmasiri', 'Male', 'padmasiri@pannalaps.lk', '2025-04-22', '', '', '77', '761918149', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:24:01', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(106, 'mallawasuriya', '$2y$10$9gwxFxtmiLxwYzr3Q33IteUocL4kq1mU1.094nh2UzoLYAq7ibbKi', 'M.A.S.P.', 'Mallawasuriya', 'Male', 'mallawasuriya@pannalaps.lk', '2025-04-22', '', '', '7', '712349297', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:26:00', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(107, 'silva', '$2y$10$yvtm0xdQ77H3wlIMrBiXXOh0wwQjutVc8D2qBHZSJeVLsbfMUK1me', 'M.M.T.N.', 'Silva', 'Female', 'silva@pannalaps.lk', '2025-04-15', '', '', '90', '771567019', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:27:45', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(108, 'herath', '$2y$10$YQuXWCoIEddjgjy8HMauVeejAALtmeY6dYl8jAJwW2MeJ6IyIRk2W', 'H.M.S.D.', 'Herath', 'Male', 'herath@pannalaps.lk', '2025-04-22', '', '', '514', '716697745', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:28:05', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(109, 'pathirana', '$2y$10$8N/qPAKPD.9Irj61nk2kM.V5LaFlBJyRhgysFLlMZwCRH3ktTSNce', 'P.K.C.', 'Pathirana', 'Male', 'pathirana@pannalaps.lk', '2025-04-22', '', '', '75', '765657733', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:28:43', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(110, 'athawada', '$2y$10$yMFmSGkDnYD2rYLChEwPeeLzwC9E.ZTnUeCZCB5XumzbkDRANo52.', 'A.M.S.', 'Athawada', 'Male', 'athawada@pannalaps.lk', '2025-04-22', '', '', '82', '773301288', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:29:47', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(111, 'karunasekera', '$2y$10$MPHmRZ6bFxLNWL2zIjjgW.r25RhsM9qwoZknlPGKo13T0834WbKIC', 'R.M.U.S.', 'Karunasekera', 'Male', 'karunasekera@pannalaps.lk', '2025-04-22', '', '', '59', '770793022', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:30:42', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(112, 'anuruddha', '$2y$10$iCwCPWWuOh6qzutFA0VYDOUgTEFBbAU7npVvibgnPTKc5F9Dxmgrq', 'S.Suranga', 'Anuruddha', 'Male', 'anuruddha@pannalaps.lk', '2025-04-22', '', '', '112', '774104746', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:31:46', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(113, 'samiththa', '$2y$10$P3mOLRA8Mhj8nfkBThjccOghZCYOlUQRjSpC/OtOd6oEEPPJ9ktx2', 'N.G.Samiththa Prasad', 'Dharmasiri', 'Male', 'samiththa@pannalaps.lk', '2025-04-22', '', '', '73', '775991239', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:33:31', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(114, 'attanayake', '$2y$10$CPp8PPx8CI0FT9xjS/7q5e3BL0jr0GvSFuL7s15p7PzXxcimGKUku', 'A.M.N.Dashini', 'Attanayake', 'Female', 'attanayake@pannalaps.lk', '2025-04-22', '', '', '75', '778284553', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:34:26', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(115, 'nayanga', '$2y$10$Mk/jBsSrTfrGf0s9P7Pas.rhv624r4YxEo8eAxu7oXvUybX8DQ.Ma', 'A.K.Rimani', 'Nayanga', 'Female', 'nayanga@pannalaps.lk', '2025-04-22', '', '', '', '717166311', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:35:23', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(116, 'jeevani', '$2y$10$T0E5XdvuG85mKTTxG0/I/e0lmO/n8N1TdgRMUrcsOYNYQWQSHntk6', 'W.R.Sandhya Jeevani', 'Kumari', 'Female', 'jeevani@pannalaps.lk', '2025-04-22', '', '', '120', '772164033', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:37:27', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(117, 'udayamali', '$2y$10$F8K4NnPsOISO44GxXqUcquk47FbaZvUp5v5w/q5Nn7Hs4npFvVqt6', 'H.A.A.A.T.', 'Udayamali', 'Female', 'udayamali@pannalaps.lk', '2025-04-22', '', '', '70', '702402240', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:38:31', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(118, 'sunil', '$2y$10$Rnx/gdDfTHwPsiuBrjiH3u5mxzBZ452WQDy65Z0RWHFIlFuEQjQIK', 'Sunil', 'Shantha', 'Male', 'sunil@pannalaps.lk', '2025-04-22', '', '', '69', '0779977235', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:39:51', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(119, 'manchanayake', '$2y$10$hrgZC2hiGZys0a4xEnbg4OlVHnFKtMeLvPrsptD9Fc2u6PIPmAwBS', 'M.A.Yutapeethake', 'Manchanayake', 'Male', 'manchanayake@pannalaps.lk', '2025-04-22', '', '', '93', '775044993', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:40:45', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(120, 'marasinghe', '$2y$10$XVTSc4IiQWWPA2BIIXNY9eJuzNf3G4xM9hsMbeaHxkUe8AQQY2w.W', 'M.M.N.C.', 'Marasinghe', 'Male', 'marasinghe@pannalaps.lk', '2025-04-22', '', '', '114', '712668850', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:41:37', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(121, 'madhubhashini', '$2y$10$Epgz18DdmW1GnI5IhTTBsebBqS8syYKEzzWYrUGeHTVAr8YJje.uG', 'Jayani', 'Madhubhashini', 'Female', 'madhubhashini@pannalaps.lk', '2025-04-22', '', '', '36', '770823141', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:42:51', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(122, 'jayakodi', '$2y$10$a2.dp1mz4YALPCaJE.Zkpubo0M6anlXuYCZdUBYMY5u8ymPK8T9mu', 'J.A.W.P.K.', 'Jayakodi', 'Male', 'jayakodi@pannalaps.lk', '2025-04-22', '', '', '520', '765415570', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:43:15', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(123, 'munasinghe', '$2y$10$/eNHktwD7z1BaN6wZgUvUOEkSptUfGDHuAHJQgdRMcsajww0x9YMO', 'M.M.T.A.', 'Munasinghe', 'Male', 'munasinghe@pannalaps.lk', '2025-04-22', '', '', '68', '702311160', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:43:37', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(124, 'sankalpana', '$2y$10$UIOyHX/eitjbZZrCZOLwK.2qiQ1FzEfgb2A2pjBpbUrKNzpX.Cybi', 'A.Dinujaya', 'Sankalpana', 'Male', 'sankalpana@pannalaps.lk', '2025-04-22', '', '', '839', '779630313', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:44:15', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL),
(125, 'jayalath', '$2y$10$bwwJKr36SR94PsiIUMrOS.XPSWw2Hp.63FvpxsZyJdO47U0JJVpq6', 'J.A.S.T.', 'Jayalath', 'Male', 'jayalath@pannalaps.lk', '2025-04-22', '', '', '66', '710922481', '', '', 'Head Office', '2025-04-22', 'Employee', '2025-04-22 05:44:16', '2025-05-28 09:57:20', 45, 0, 21, 24, NULL, NULL);

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
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wp_designations`
--
ALTER TABLE `wp_designations`
  MODIFY `designation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `wp_leave_notifications`
--
ALTER TABLE `wp_leave_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_leave_request`
--
ALTER TABLE `wp_leave_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `wp_manual_leave_logs`
--
ALTER TABLE `wp_manual_leave_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wp_pradeshiya_sabha_users`
--
ALTER TABLE `wp_pradeshiya_sabha_users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

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
