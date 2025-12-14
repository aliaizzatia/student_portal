-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2025 at 03:18 AM
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
-- Database: `student_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`, `description`, `created_at`) VALUES
(1, 'ICM572', 'Gamification for Content Management', 'Learn gamification techniques for content management systems', '2025-12-13 15:06:20'),
(2, 'IMS560', 'Advanced Database Management System', 'Advanced concepts in database design and management', '2025-12-13 15:06:20'),
(3, 'IMS564', 'User Experience Design', 'Principles and practices of UX design', '2025-12-13 15:06:20'),
(4, 'IMS565', 'Information System Project Management', 'Managing IS projects effectively', '2025-12-13 15:06:20'),
(5, 'IMS566', 'Advanced Web Design Development and Content Management', 'Advanced web development with content management', '2025-12-13 15:06:20'),
(6, 'LCC501', 'English for Professional Correspondence', 'Professional English writing and communication', '2025-12-13 15:06:20'),
(7, 'TAC501', 'Introductory Arabic', 'Basic Arabic language course', '2025-12-13 15:06:20'),
(8, 'TMC501', 'Introductory Mandarin', 'Basic Mandarin language course', '2025-12-13 15:06:20');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrolled_at`) VALUES
(3, 3, 2, '2025-12-13 15:34:45'),
(4, 3, 1, '2025-12-13 15:34:49'),
(5, 3, 3, '2025-12-13 15:34:51'),
(6, 3, 4, '2025-12-13 15:34:53'),
(7, 3, 7, '2025-12-13 15:34:56'),
(8, 3, 6, '2025-12-13 15:34:58'),
(9, 3, 5, '2025-12-13 15:35:01'),
(10, 4, 8, '2025-12-13 15:36:28'),
(11, 4, 1, '2025-12-13 15:36:29'),
(12, 4, 3, '2025-12-13 15:36:32'),
(13, 4, 5, '2025-12-13 15:36:37');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `matric_id` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `semester` int(11) DEFAULT 1,
  `class` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `matric_id`, `email`, `name`, `password`, `phone`, `program`, `semester`, `class`, `photo`, `role`, `created_at`) VALUES
(1, 'ADMIN001', 'admin@portal.com', 'Administrator', '$2y$10$B5M/7vFb5z7Q8KjK8L9M0eJ8N7B6V5C4X3Z2V1B0N9M8L7K6J5H4G3F2D1S0A', NULL, NULL, 1, NULL, NULL, 'admin', '2025-12-13 15:06:20'),
(2, 'STU001', 'student@portal.com', 'John Doe', '$2y$10$C6N/8wGc6a8R9Lk9M0N1fK9O8C7W6D5Y4A3W2C1O0P9N8M7L6K5J4H3G2F1E0D', NULL, 'Computer Science', 3, 'Class A', NULL, 'student', '2025-12-13 15:06:20'),
(3, 'STU002', 'alia@portal.com', 'Nur Alia Izzati Binti Azman', '$2y$10$p1lzYbQMbKrdn8mB6VVvsO0AUibIunFCkjxr2w/3kK80NE364Bzk6', '013-3698067', 'Information System', 1, 'Class B', NULL, 'student', '2025-12-13 15:12:37'),
(4, 'STU003', 'yasmin@portal.com', 'Nurul Yasmin Bin Mohd', '$2y$10$8B5iZyKN1kukBHg3xvF7DO21jCkC2Wzm/.IbQU97Y2iVuNv5QQUMm', NULL, NULL, 1, NULL, NULL, 'student', '2025-12-13 15:36:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matric_id` (`matric_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
