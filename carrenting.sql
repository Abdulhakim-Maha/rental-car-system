-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2024 at 05:39 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `carrenting`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `cardetails_view`
-- (See below for the actual view)
--
CREATE TABLE `cardetails_view` (
`id` int(11)
,`category` varchar(100)
,`registration_type` varchar(100)
,`usage_type` varchar(100)
,`make` varchar(50)
,`model` varchar(50)
,`year` int(11)
,`status` enum('Available','In Use','Under Maintenance','Decommissioned')
,`license_plate` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `carrequests`
--

CREATE TABLE `carrequests` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) DEFAULT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_date` date NOT NULL,
  `destination` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subdistrict` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departure_time` timestamp NULL DEFAULT NULL,
  `return_time` timestamp NULL DEFAULT NULL,
  `manday` int(11) DEFAULT NULL,
  `driver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oil_expense` decimal(5,2) DEFAULT NULL,
  `total_distance` decimal(6,2) DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carrequests`
--

INSERT INTO `carrequests` (`id`, `title`, `first_name`, `last_name`, `position`, `user_id`, `car_id`, `purpose`, `request_date`, `destination`, `subdistrict`, `district`, `province`, `departure_time`, `return_time`, `manday`, `driver_name`, `oil_expense`, `total_distance`, `remarks`, `status_id`, `created_at`, `updated_at`, `company`) VALUES
(1, 'ขอรถ', 'Abdulhakim', 'Maha', 'โปรแกรมเมอร์', 2, 26, 'ไปเที่ยว', '2024-09-18', 'โรงเรียนสาธิตอิสลาม', 'รูสะมีแล', 'เมือง', 'ปัตตานี', '2024-09-19 08:09:00', '2024-09-20 08:10:00', 1, 'อากิง', '150.00', '300.00', NULL, 2, '2024-09-18 08:10:06', '2024-09-19 02:47:09', NULL),
(2, 'ยืมขอเพื่อขนส่งนักศีกษา', 'Abdulhakim', 'Maha', 'เดเวลอปเปอร์', 2, 27, 'ทัศนศึกษา', '2024-09-19', 'โรงเรียนสายบุรีอิสลามวิทยา', 'สายบุรี', 'กะพ้อ', 'ปัตตานี', '2024-09-18 23:00:00', '2024-09-19 15:50:00', 3, 'ฮากิม', '500.00', '250.00', 'ไม่มี', 2, '2024-09-18 10:51:08', '2024-09-19 02:39:57', NULL),
(3, 'dfdsfdfsf', 'Abdulhakim', 'Maha', 'เดเวลอปเปอร์', 2, 23, 'dfdfdfdfdfd', '2024-09-18', NULL, 'สายบุรี', 'เมือง', 'กรุงเทพมหานคร', NULL, '2024-09-19 16:00:00', 5, 'กดดกด', NULL, NULL, NULL, 3, '2024-09-18 10:52:42', '2024-09-19 03:37:25', NULL),
(4, 'Request for Travel', 'Abdulhakim', 'Maha', 'Backend Developer', 2, 20, 'Making this request for trip', '2024-09-18', 'New York City', 'Ramkamheang', 'Bang kapi', 'Bangkok', '2024-09-19 11:00:00', '2024-09-20 01:59:00', 5, 'austiniqer', '50.00', '275.00', 'I&#039;m begging', 4, '2024-09-18 11:03:56', '2024-09-19 03:37:37', NULL),
(5, 'ขออนุญาตใช้รถยนต์ไปแข่งขัน', 'ฮากิม', 'มาหะ', 'โปรแกรมเมอร์', 2, 24, 'ไปแข่งขันตอบปัญหาไอที', '2024-09-18', 'โรงเรียนสาธิตอิสลาม', 'รูสะมีแล', 'เมือง', 'ปัตตานี', '2024-09-19 02:00:00', '2024-09-19 10:00:00', 1, 'ฮากิม', '500.00', '300.00', 'ไม่มี', 6, '2024-09-18 16:13:02', '2024-09-19 03:30:19', 'ทีมงานจำนวน 5 คน'),
(6, 'ขออนุญาตนอน', 'Abdulhakim', 'Maha', 'โปรแกรมเมอร์', 2, 21, 'ง่วงนอนมาก', '2024-09-18', 'โรงเรียนสาธิตอิสลาม', 'สายบุรี', 'กะพ้อ', 'กรุงเทพมหานคร', '2024-09-20 01:00:00', '2024-09-20 13:00:00', 1, 'ฮากิม', '200.00', '100.00', 'ไม่มี', 7, '2024-09-18 19:50:43', '2024-09-19 03:19:16', 'ทีมงานจำนวน 5 คน'),
(8, 'ขออนุญาตใช้รถยนต์ไปราชการ', 'ฮากิม', 'มาหะ', 'programmer', 2, 19, 'ขอใช้รถยนต์นะ', '2024-09-19', 'โรงเรียน', 'กะรุบี', 'กะพ้อ', 'ปัตตานี', '2024-09-20 01:00:00', '2024-09-20 10:00:00', 1, 'ฮากิม', '250.00', '100.00', 'ไม่มี', 5, '2024-09-19 03:17:22', '2024-09-19 03:36:19', 'ทีมงาน 2 คน');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `license_plate` varchar(255) DEFAULT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `status` enum('Available','In Use','Under Maintenance','Decommissioned') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category_id` int(11) NOT NULL,
  `registration_type_id` int(11) NOT NULL,
  `usage_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `license_plate`, `make`, `model`, `year`, `status`, `created_at`, `updated_at`, `category_id`, `registration_type_id`, `usage_type_id`) VALUES
(19, 'กข 1234 กรุงเทพมหานค', 'TOYOTA', 'Frontliner', 2023, 'In Use', '2024-09-18 03:31:57', '2024-09-19 03:27:04', 1, 1, 1),
(20, 'ขก 5678 เชียงใหม่', 'TOYOTA', 'Frontliner', 2023, 'Available', '2024-09-18 03:31:57', '2024-09-19 03:09:23', 1, 2, 2),
(21, 'คง 9012 ภูเก็ต', 'TOYOTA', 'Advanced Frontliner', 2023, 'In Use', '2024-09-18 03:31:57', '2024-09-18 19:54:43', 2, 2, 3),
(22, 'งจ 3456 นครราชสีมา', 'TOYOTA', 'Frontliner', 2023, 'Available', '2024-09-18 03:31:57', '2024-09-18 18:37:02', 1, 2, 4),
(23, 'จฉ 7890 ชลบุรี', 'TOYOTA', 'Frontliner', 2023, 'In Use', '2024-09-18 03:31:57', '2024-09-18 18:37:09', 1, 2, 5),
(24, 'ฉช 1122 ขอนแก่น', 'NISSAN', 'Van', 2023, 'In Use', '2024-09-18 03:31:57', '2024-09-18 18:37:14', 3, 2, 6),
(25, 'ชซ 3344 สุราษฎร์ธานี', 'NISSAN', 'Van', 2023, 'In Use', '2024-09-18 03:31:57', '2024-09-18 19:54:14', 3, 2, 7),
(26, 'ออ 1234 ปัตตานี', 'ISUZU', 'Double Cab Pickup', 2023, 'In Use', '2024-09-18 03:31:57', '2024-09-19 02:47:09', 4, 1, 8),
(27, 'บด 5463 ยะลา', 'ISUZU', 'Utility Pickup', 2023, 'In Use', '2024-09-18 03:31:57', '2024-09-18 18:37:53', 5, 1, 9);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'รถพยาบาลแบบผู้พร้อมอุปกรณ์ช่วยชีวิต', 'Description for Category 1'),
(2, 'รถพยาบาลพร้อมอุปกรณ์ช่วยชีวิตขั้นสูง', 'Description for Category 2'),
(3, 'รถตู้โดยสาร', 'Description for Category 3'),
(4, 'รถยนต์กระบะบรรทุกแบบดับเบิ้ลแค็บ 4 ประตู', 'Description for Category 4'),
(5, 'รถกระบะสาธารณูปโภค', 'Description for Category 5');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'CREATE_CAR_REQUEST', 'Permission to create a car request', '2024-09-17 15:27:09', '2024-09-17 15:27:09'),
(2, 'VIEW_OWN_REQUESTS', 'Permission to view own car requests', '2024-09-17 15:27:09', '2024-09-17 15:27:09'),
(3, 'CANCEL_OWN_REQUEST', 'Permission to cancel own car requests', '2024-09-17 15:27:09', '2024-09-17 15:27:09'),
(4, 'PROVIDE_CAR', 'Permission to assign a car to a request', '2024-09-17 15:27:09', '2024-09-17 15:27:09'),
(5, 'APPROVE_REQUEST', 'Permission to approve a request', '2024-09-17 15:27:09', '2024-09-17 15:27:09'),
(6, 'VIEW_ALL_REQUESTS', 'Permission to view all car requests', '2024-09-17 15:27:09', '2024-09-17 15:27:09'),
(7, 'FINAL_APPROVE_REQUEST', 'Permission to perform final approval of requests', '2024-09-17 15:27:09', '2024-09-17 15:27:09'),
(8, 'CANCEL_ANY_REQUEST', 'Permission to cancel any car request', '2024-09-17 15:27:09', '2024-09-17 15:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `registrationtypes`
--

CREATE TABLE `registrationtypes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `registrationtypes`
--

INSERT INTO `registrationtypes` (`id`, `name`, `description`) VALUES
(1, 'รถยนต์บรรทุกส่วนบุคคล', 'Personal truck vehicle'),
(2, 'รถยนต์นั่งส่วนบุคคลไม่เกิน 7 คน', 'Personal passenger vehicle with no more than 7 seats');

-- --------------------------------------------------------

--
-- Table structure for table `requeststatus`
--

CREATE TABLE `requeststatus` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `requeststatus`
--

INSERT INTO `requeststatus` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Requested', 'Request is pending approval', '2024-09-17 15:34:18', '2024-09-18 08:47:05'),
(2, 'Head Assigned', 'Request approved by Head, pending Supervisor approval', '2024-09-17 15:34:18', '2024-09-18 08:43:31'),
(3, 'Supervisor Approved', 'Request approved by Supervisor, pending Director approval', '2024-09-17 15:34:18', '2024-09-18 08:43:07'),
(4, 'Director Approved', 'Request has been finally approved', '2024-09-17 15:34:18', '2024-09-18 08:43:20'),
(5, 'Approved', 'Request has been approved', '2024-09-17 15:34:18', '2024-09-18 12:41:23'),
(6, 'Rejected', 'Request has been rejected', '2024-09-18 08:54:39', '2024-09-18 08:54:53'),
(7, 'Cancelled', 'Request has been cancelled', '2024-09-19 02:00:43', '2024-09-19 02:00:43');

-- --------------------------------------------------------

--
-- Table structure for table `rolepermissions`
--

CREATE TABLE `rolepermissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `rolepermissions`
--

INSERT INTO `rolepermissions` (`role_id`, `permission_id`, `granted_at`) VALUES
(1, 1, '2024-09-17 15:32:31'),
(1, 2, '2024-09-17 15:32:31'),
(1, 3, '2024-09-17 15:32:31'),
(2, 4, '2024-09-17 15:32:59'),
(2, 5, '2024-09-17 15:32:59'),
(2, 6, '2024-09-17 15:32:59'),
(2, 8, '2024-09-17 15:32:59'),
(3, 5, '2024-09-17 15:33:14'),
(3, 6, '2024-09-17 15:33:14'),
(3, 8, '2024-09-17 15:33:14'),
(4, 6, '2024-09-17 15:33:23'),
(4, 7, '2024-09-17 15:33:23'),
(4, 8, '2024-09-17 15:33:23');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Staff', 'Staff member who can request and manage own car requests', '2024-09-17 15:26:20', '2024-09-17 15:26:20'),
(2, 'Head', 'Head responsible for providing and approving car requests', '2024-09-17 15:26:20', '2024-09-17 15:26:20'),
(3, 'Supervisor', 'Supervisor responsible for approving requests after Head', '2024-09-17 15:26:20', '2024-09-17 15:26:20'),
(4, 'Director', 'Director responsible for final approval of requests', '2024-09-17 15:26:20', '2024-09-17 15:26:20'),
(5, 'Admin', 'Administrator', '2024-09-18 09:25:07', '2024-09-18 09:25:07');

-- --------------------------------------------------------

--
-- Table structure for table `usagetypes`
--

CREATE TABLE `usagetypes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `usagetypes`
--

INSERT INTO `usagetypes` (`id`, `name`, `description`) VALUES
(1, 'รถเยี่ยมบ้าน (ทันตกรรม)', 'Home visit vehicle for dentistry'),
(2, 'รถเยี่ยมบ้าน (สหวิชาชีพ)', 'Home visit vehicle for multi-disciplinary professionals'),
(3, 'รถรีเฟอร์ คันที่ 1', 'Refrigerated vehicle number 1'),
(4, 'รถรีเฟอร์ คันที่ 2', 'Refrigerated vehicle number 2'),
(5, 'รถรีเฟอร์ คันที่ 3', 'Refrigerated vehicle number 3'),
(6, 'รถตู้ (สีเทา)', 'Gray van'),
(7, 'รถตู้ (สีทอง)', 'Gold van'),
(8, 'รถยนต์ 4 ประตู', '4-door car'),
(9, 'รถยนต์ 2 ประตู', '2-door car');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `role_id` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role_id`, `created_at`, `updated_at`, `is_active`) VALUES
(2, 'hakim', '63011075@kmitl.ac.th', '$2y$10$/Hj.974vYlvGWdEjQbDRseIqUFdLs/OBaCSYXLHjhySTBXXl.a3wu', 'Abdulhakim', 'Maha', 1, '2024-09-18 04:37:00', '2024-09-18 04:37:00', 1),
(3, 'admin', 'admin@gmail.com', '$2y$10$iaOItp2dImeK4rtRh5elf.dGjRspu9hqB6SvyBpTS.6EoQ/PfwqaW', 'admin', 'root', 5, '2024-09-18 09:23:04', '2024-09-18 09:25:21', 1),
(4, 'head', 'head@gmail.com', '$2y$10$1a68RKQukHnTHGYelrBr3uo8b.PpYDqqlj1FCFN5fnKkHFD6VKes2', 'John', 'Doe', 2, '2024-09-18 09:30:21', '2024-09-18 09:38:16', 1),
(5, 'sub', 'sub@gmail.com', '$2y$10$/3IQWOvEzP0tAzsktL6/JeHUrs0Vd72i9ju6KLJYizStJWHrL4EyW', 'super', 'visor', 3, '2024-09-18 11:00:53', '2024-09-18 11:01:23', 1),
(6, 'director', 'director@gmail.com', '$2y$10$UHoKMUft92kAMD7.Ftjk8.ljtOv8Uqo7Kf3ER/EwN.Jhfu4VPRtES', 'Direc', 'Tor', 4, '2024-09-18 12:45:38', '2024-09-18 12:47:04', 1);

-- --------------------------------------------------------

--
-- Structure for view `cardetails_view`
--
DROP TABLE IF EXISTS `cardetails_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `cardetails_view`  AS SELECT `cars`.`id` AS `id`, `categories`.`name` AS `category`, `registrationtypes`.`name` AS `registration_type`, `usagetypes`.`name` AS `usage_type`, `cars`.`make` AS `make`, `cars`.`model` AS `model`, `cars`.`year` AS `year`, `cars`.`status` AS `status`, `cars`.`license_plate` AS `license_plate` FROM (((`cars` join `categories` on(`cars`.`category_id` = `categories`.`id`)) join `registrationtypes` on(`cars`.`registration_type_id` = `registrationtypes`.`id`)) join `usagetypes` on(`cars`.`usage_type_id` = `usagetypes`.`id`))  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carrequests`
--
ALTER TABLE `carrequests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_carrequests_user_id` (`user_id`),
  ADD KEY `fk_carrequests_car_id` (`car_id`),
  ADD KEY `fk_carrequests_status_id` (`status_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `registration_type_id` (`registration_type_id`),
  ADD KEY `usage_type_id` (`usage_type_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `registrationtypes`
--
ALTER TABLE `registrationtypes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `requeststatus`
--
ALTER TABLE `requeststatus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `rolepermissions`
--
ALTER TABLE `rolepermissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `usagetypes`
--
ALTER TABLE `usagetypes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carrequests`
--
ALTER TABLE `carrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `registrationtypes`
--
ALTER TABLE `registrationtypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `requeststatus`
--
ALTER TABLE `requeststatus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `usagetypes`
--
ALTER TABLE `usagetypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carrequests`
--
ALTER TABLE `carrequests`
  ADD CONSTRAINT `fk_carrequests_car_id` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_carrequests_status_id` FOREIGN KEY (`status_id`) REFERENCES `requeststatus` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_carrequests_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `cars_ibfk_2` FOREIGN KEY (`registration_type_id`) REFERENCES `registrationtypes` (`id`),
  ADD CONSTRAINT `cars_ibfk_3` FOREIGN KEY (`usage_type_id`) REFERENCES `usagetypes` (`id`);

--
-- Constraints for table `rolepermissions`
--
ALTER TABLE `rolepermissions`
  ADD CONSTRAINT `rolepermissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rolepermissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
