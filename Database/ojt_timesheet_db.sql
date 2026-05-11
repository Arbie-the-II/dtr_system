-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 10:26 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ojt_timesheet_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `daily_pictures`
--

CREATE TABLE `daily_pictures` (
  `id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `picture_date` date NOT NULL,
  `picture_time` time NOT NULL,
  `picture_type` enum('am_timein','am_timeout','pm_timein','pm_timeout') NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `face_captures`
--

CREATE TABLE `face_captures` (
  `id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'time_in, time_out, pause',
  `capture_time` time NOT NULL,
  `created_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interns`
--

CREATE TABLE `interns` (
  `Intern_id` int(11) NOT NULL,
  `Intern_Name` varchar(255) NOT NULL,
  `Intern_School` varchar(255) NOT NULL,
  `Intern_BirthDay` date NOT NULL,
  `Intern_Age` int(255) NOT NULL,
  `Intern_Gender` varchar(255) NOT NULL,
  `Required_Hours_Rendered` int(255) NOT NULL,
  `Face_Registered` tinyint(1) DEFAULT 0,
  `Face_Image_Path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interns`
--

INSERT INTO `interns` (`Intern_id`, `Intern_Name`, `Intern_School`, `Intern_BirthDay`, `Intern_Age`, `Intern_Gender`, `Required_Hours_Rendered`, `Face_Registered`, `Face_Image_Path`) VALUES

-- --------------------------------------------------------

--
-- Table structure for table `intern_notes`
--

CREATE TABLE `intern_notes` (
  `id` int(11) NOT NULL,
  `intern_id` varchar(50) NOT NULL,
  `note_date` date NOT NULL,
  `note_content` text NOT NULL,
  `noted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `intern_notes`
--

INSERT INTO `intern_notes` (`id`, `intern_id`, `note_date`, `note_content`, `noted`, `created_at`, `updated_at`) VALUES
(22, '16', '2025-05-30', 'OT TODAT TILL 8PM', 0, '2025-05-30 11:49:59', '2025-05-30 11:49:59');

-- --------------------------------------------------------

--
-- Table structure for table `intern_photos`
--

CREATE TABLE `intern_photos` (
  `id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `photo_data` longtext NOT NULL,
  `photo_type` enum('timein','timeout') NOT NULL,
  `photo_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pause_history`
--

CREATE TABLE `pause_history` (
  `id` int(11) NOT NULL,
  `timesheet_id` int(11) DEFAULT NULL,
  `intern_id` int(11) DEFAULT NULL,
  `pause_start` time DEFAULT NULL,
  `pause_end` time DEFAULT NULL,
  `pause_duration` time DEFAULT NULL,
  `pause_reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timesheet`
--

CREATE TABLE `timesheet` (
  `record_id` int(11) NOT NULL,
  `intern_id` int(255) NOT NULL,
  `intern_name` varchar(255) NOT NULL,
  `am_timein` time(6) NOT NULL,
  `am_timein_display` time DEFAULT NULL,
  `am_timeOut` time(6) NOT NULL,
  `pm_timein` time(6) NOT NULL,
  `pm_timeout` time(6) NOT NULL,
  `am_hours_worked` time(6) NOT NULL,
  `pm_hours_worked` time(6) NOT NULL,
  `required_hours_rendered` int(255) NOT NULL,
  `day_total_hours` time(6) NOT NULL,
  `total_hours_rendered` time(6) NOT NULL,
  `created_at` varchar(255) NOT NULL,
  `confirm_overtime` int(11) NOT NULL,
  `overtime_start` time(6) NOT NULL,
  `overtime_hours` time(6) NOT NULL,
  `overtime_end` time(6) NOT NULL,
  `overtime_manual` tinyint(1) DEFAULT 0,
  `pause_start` time DEFAULT '00:00:00',
  `pause_end` time DEFAULT '00:00:00',
  `pause_duration` time DEFAULT '00:00:00',
  `pause_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo_data` longtext DEFAULT NULL,
  `photo_timestamp` timestamp NULL DEFAULT NULL,
  `photo_type` varchar(20) DEFAULT 'timein',
  `am_timein_photo` varchar(255) DEFAULT NULL,
  `am_timeout_photo` varchar(255) DEFAULT NULL,
  `pm_timein_photo` varchar(255) DEFAULT NULL,
  `pm_timeout_photo` varchar(255) DEFAULT NULL,
  `am_timein_image` varchar(255) DEFAULT NULL,
  `am_timeout_image` varchar(255) DEFAULT NULL,
  `pm_timein_image` varchar(255) DEFAULT NULL,
  `pm_timeout_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timesheet`
--

INSERT INTO `timesheet` (`record_id`, `intern_id`, `intern_name`, `am_timein`, `am_timein_display`, `am_timeOut`, `pm_timein`, `pm_timeout`, `am_hours_worked`, `pm_hours_worked`, `required_hours_rendered`, `day_total_hours`, `total_hours_rendered`, `created_at`, `confirm_overtime`, `overtime_start`, `overtime_hours`, `overtime_end`, `overtime_manual`, `pause_start`, `pause_end`, `pause_duration`, `pause_reason`, `notes`, `photo_data`, `photo_timestamp`, `photo_type`, `am_timein_photo`, `am_timeout_photo`, `pm_timein_photo`, `pm_timeout_photo`, `am_timein_image`, `am_timeout_image`, `pm_timein_image`, `pm_timeout_image`) VALUES
(96, 11, 'Mohammad Rasheed Heding', '00:00:00.000000', '00:00:00', '00:00:00.000000', '18:10:02.000000', '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 486, '00:00:00.000000', '00:00:00.000000', '2025-05-29', 0, '17:00:00.000000', '00:00:00.000000', '00:00:00.000000', 0, '00:00:00', '00:00:00', '00:00:00', NULL, NULL, NULL, NULL, 'timein', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(99, 14, 'Salwa A. Maulod', '00:00:00.000000', '00:00:00', '00:00:00.000000', '17:07:48.000000', '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 486, '00:00:00.000000', '00:00:00.000000', '2025-05-29', 0, '17:00:00.000000', '00:00:00.000000', '00:00:00.000000', 0, '00:00:00', '00:00:00', '00:00:00', NULL, NULL, NULL, NULL, 'timein', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(100, 15, 'SALI RHAZDI BARA', '00:00:00.000000', '00:00:00', '00:00:00.000000', '17:12:46.000000', '17:13:02.000000', '00:00:00.000000', '00:00:16.000000', 720, '00:00:16.000000', '00:00:00.000000', '2025-05-29', 0, '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 0, '00:00:00', '00:00:00', '00:00:00', NULL, NULL, NULL, NULL, 'timein', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(101, 16, 'PALTINGCA,JOSHUA CIHM U.', '00:00:00.000000', '00:00:00', '00:00:00.000000', '17:38:34.000000', '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 540, '00:00:00.000000', '00:00:00.000000', '2025-05-29', 0, '17:00:00.000000', '00:00:00.000000', '00:00:00.000000', 0, '00:00:00', '00:00:00', '00:00:00', NULL, NULL, NULL, NULL, 'timein', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(102, 16, 'PALTINGCA,JOSHUA CIHM U.', '08:25:45.000000', '08:25:45', '17:49:30.000000', '00:00:00.000000', '00:00:00.000000', '09:23:45.000000', '00:00:00.000000', 540, '09:23:45.000000', '00:00:00.000000', '2025-05-30', 0, '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 0, '00:00:00', '00:00:00', '00:00:00', NULL, 'OT TODAT TILL 8PM', NULL, NULL, 'timein', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(103, 14, 'Salwa A. Maulod', '08:36:41.000000', '08:36:41', '12:05:09.000000', '00:00:00.000000', '00:00:00.000000', '03:28:28.000000', '00:00:00.000000', 486, '03:28:28.000000', '00:00:00.000000', '2025-05-30', 0, '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 0, '00:00:00', '00:00:00', '00:00:00', NULL, NULL, NULL, NULL, 'timein', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(104, 12, 'yURR', '08:44:14.000000', '08:44:14', '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 240, '00:00:00.000000', '00:00:00.000000', '2025-05-30', 0, '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 0, '00:00:00', '00:00:00', '00:00:00', NULL, NULL, NULL, NULL, 'timein', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(107, 11, 'Mohammad Rasheed Heding', '10:09:18.000000', '10:09:18', '11:23:53.000000', '00:00:00.000000', '00:00:00.000000', '01:14:35.000000', '00:00:00.000000', 486, '01:14:35.000000', '00:00:00.000000', '2025-05-30', 0, '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 0, '00:00:00', '00:00:00', '00:00:00', NULL, NULL, NULL, NULL, 'timein', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(108, 16, 'PALTINGCA,JOSHUA CIHM U.', '09:44:24.000000', '09:44:24', '12:34:10.000000', '13:00:00.000000', '00:00:00.000000', '02:49:46.000000', '00:00:00.000000', 540, '02:49:46.000000', '00:00:00.000000', '2025-06-02', 0, '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 0, '00:00:00', '00:00:00', '00:00:00', NULL, NULL, NULL, NULL, 'timein', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(109, 11, 'Mohammad Rasheed Heding', '00:00:00.000000', '00:00:00', '00:00:00.000000', '13:00:00.000000', '12:33:45.000000', '00:00:00.000000', '00:26:15.000000', 486, '00:26:15.000000', '00:00:00.000000', '2025-06-02', 0, '00:00:00.000000', '00:00:00.000000', '00:00:00.000000', 0, '00:00:00', '00:00:00', '00:00:00', NULL, NULL, NULL, NULL, 'timein', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timesheet_photos`
--

CREATE TABLE `timesheet_photos` (
  `id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `photo_path` varchar(255) NOT NULL,
  `photo_type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timesheet_photos`
--

INSERT INTO `timesheet_photos` (`id`, `intern_id`, `record_id`, `photo_path`, `photo_type`, `created_at`) VALUES
(9, 12, 97, 'uploads/faces/face_12_1748501261.png', 'afternoon_time_in', '2025-05-29 06:47:41'),
(10, 13, 98, 'uploads/faces/face_13_1748509144.png', 'afternoon_time_in', '2025-05-29 08:59:04'),
(11, 13, 98, 'uploads/faces/face_13_1748509152.png', 'afternoon_time_out', '2025-05-29 08:59:12'),
(12, 11, 96, 'uploads/faces/face_11_1748513402.png', 'afternoon_time_in', '2025-05-29 10:10:02'),
(13, 14, 103, 'uploads/faces/face_14_1748565401.png', 'morning_time_in', '2025-05-30 00:36:42'),
(14, 13, 105, 'uploads/faces/face_13_1748566286.png', 'morning_time_in', '2025-05-30 00:51:26'),
(15, 14, 103, 'uploads/faces/face_14_1748577908.png', 'afternoon_time_out', '2025-05-30 04:05:08'),
(16, 16, 102, 'uploads/faces/face_16_1748598569.png', 'afternoon_time_out', '2025-05-30 09:49:29'),
(17, 11, 109, 'uploads/faces/face_11_1748838825.png', 'afternoon_time_out', '2025-06-02 04:33:45'),
(18, 16, 108, 'uploads/faces/face_16_1748838850.png', 'afternoon_time_out', '2025-06-02 04:34:10');

-- --------------------------------------------------------

--
-- Table structure for table `time_adjustments`
--

CREATE TABLE `time_adjustments` (
  `id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `time_field` varchar(50) NOT NULL,
  `previous_value` time DEFAULT NULL,
  `new_value` time NOT NULL,
  `adjusted_by` varchar(50) DEFAULT NULL,
  `adjustment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_adjustments`
--

INSERT INTO `time_adjustments` (`id`, `record_id`, `intern_id`, `time_field`, `previous_value`, `new_value`, `adjusted_by`, `adjustment_date`) VALUES
(1, 97, 12, 'pm_timein', '13:36:14', '07:36:00', 'Supervisor', '2025-05-29 06:46:25'),
(2, 97, 12, 'pm_timein', '07:36:00', '07:36:00', 'Supervisor', '2025-05-29 06:46:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daily_pictures`
--
ALTER TABLE `daily_pictures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intern_id` (`intern_id`);

--
-- Indexes for table `face_captures`
--
ALTER TABLE `face_captures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intern_id` (`intern_id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `interns`
--
ALTER TABLE `interns`
  ADD PRIMARY KEY (`Intern_id`);

--
-- Indexes for table `intern_notes`
--
ALTER TABLE `intern_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `intern_date` (`intern_id`,`note_date`);

--
-- Indexes for table `intern_photos`
--
ALTER TABLE `intern_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intern_id` (`intern_id`);

--
-- Indexes for table `pause_history`
--
ALTER TABLE `pause_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intern_id` (`intern_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `timesheet`
--
ALTER TABLE `timesheet`
  ADD PRIMARY KEY (`record_id`);

--
-- Indexes for table `timesheet_photos`
--
ALTER TABLE `timesheet_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intern_id` (`intern_id`);

--
-- Indexes for table `time_adjustments`
--
ALTER TABLE `time_adjustments`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daily_pictures`
--
ALTER TABLE `daily_pictures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `face_captures`
--
ALTER TABLE `face_captures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interns`
--
ALTER TABLE `interns`
  MODIFY `Intern_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `intern_notes`
--
ALTER TABLE `intern_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `intern_photos`
--
ALTER TABLE `intern_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pause_history`
--
ALTER TABLE `pause_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timesheet`
--
ALTER TABLE `timesheet`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `timesheet_photos`
--
ALTER TABLE `timesheet_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `time_adjustments`
--
ALTER TABLE `time_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_pictures`
--
ALTER TABLE `daily_pictures`
  ADD CONSTRAINT `daily_pictures_ibfk_1` FOREIGN KEY (`intern_id`) REFERENCES `interns` (`Intern_id`) ON DELETE CASCADE;

--
-- Constraints for table `face_captures`
--
ALTER TABLE `face_captures`
  ADD CONSTRAINT `fk_face_captures_intern` FOREIGN KEY (`intern_id`) REFERENCES `interns` (`Intern_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_face_captures_record` FOREIGN KEY (`record_id`) REFERENCES `timesheet` (`record_id`) ON DELETE CASCADE;

--
-- Constraints for table `intern_photos`
--
ALTER TABLE `intern_photos`
  ADD CONSTRAINT `intern_photos_ibfk_1` FOREIGN KEY (`intern_id`) REFERENCES `interns` (`Intern_id`) ON DELETE CASCADE;

--
-- Constraints for table `pause_history`
--
ALTER TABLE `pause_history`
  ADD CONSTRAINT `pause_history_ibfk_1` FOREIGN KEY (`intern_id`) REFERENCES `interns` (`Intern_id`);

--
-- Constraints for table `timesheet_photos`
--
ALTER TABLE `timesheet_photos`
  ADD CONSTRAINT `timesheet_photos_ibfk_1` FOREIGN KEY (`intern_id`) REFERENCES `interns` (`Intern_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
