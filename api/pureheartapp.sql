-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2025 at 08:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pureheartapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_transaction`
--

CREATE TABLE `app_transaction` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `amount` int(255) NOT NULL,
  `reverse_code` varchar(255) NOT NULL,
  `date_time` datetime NOT NULL,
  `status` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_transaction`
--

INSERT INTO `app_transaction` (`id`, `name`, `number`, `token`, `amount`, `reverse_code`, `date_time`, `status`) VALUES
(3, 'احمد حسن', '01156565889', '%Ks4zhaZvA4AFuqhFyWYEk7SZ', 200, 'revv', '2024-11-13 21:14:02', 1),
(4, 'احمد حسن', '01156565889', '%Ks4zhaZvA4AFuqhFyWYEk7SZ', 200, 'revv', '2024-11-13 21:18:41', 1),
(5, 'احمد حسن', '01156565889', '%Ks4zhaZvA4AFuqhFyWYEk7SZ', 300, 'reverseCode', '2024-11-13 22:08:01', 1),
(6, 'احمد حسن', '01156565889', '%Ks4zhaZvA4AFuqhFyWYEk7SZ', 500, 'reverseCode', '2024-11-13 22:09:13', 1);

--
-- Triggers `app_transaction`
--
DELIMITER $$
CREATE TRIGGER `update_student_balance` AFTER UPDATE ON `app_transaction` FOR EACH ROW BEGIN
    -- Check if the status is changed to 1 (e.g., transaction completed or reversed)
    IF NEW.status = 1 THEN
        -- Update the student balance by adding the transaction amount
        UPDATE student
        SET balance = balance + NEW.amount
        WHERE token = NEW.number;  -- Assuming 'number' corresponds to the student's token
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `student_ad` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `student_ad`, `teacher_id`) VALUES
(2, 2, 7),
(3, 2, 7),
(4, 2, 7),
(5, 2, 7);

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `rate` int(11) NOT NULL,
  `created at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_steps`
--

CREATE TABLE `school_steps` (
  `id` int(11) NOT NULL,
  `step_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_steps`
--

INSERT INTO `school_steps` (`id`, `step_name`) VALUES
(1, 'الصف الأول الابتدائي'),
(2, 'الصف الثاني الابتدائي'),
(3, 'الصف الثالث الابتدائي'),
(4, 'الصف الرابع الابتدائي'),
(5, 'الصف الخامس الابتدائي'),
(6, 'الصف السادس الابتدائي'),
(7, 'الصف الأول المتوسط'),
(8, 'الصف الثاني المتوسط'),
(9, 'الصف الثالث المتوسط'),
(10, 'الصف الرابع الاعدادي'),
(11, 'الصف الخامس الاعدادي'),
(12, 'الصف السادس الاعدادي');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subjet` int(11) NOT NULL,
  `cost` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `teacher_id`, `student_id`, `subjet`, `cost`, `status`, `created_at`) VALUES
(1, 2, 1, 1, 100, 2, '2024-11-23 11:11:05');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `student_number` varchar(255) NOT NULL,
  `student_image` varchar(255) NOT NULL,
  `student_stage` int(11) NOT NULL,
  `rate` int(2) NOT NULL,
  `token` varchar(255) NOT NULL,
  `balance` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `student_name`, `student_number`, `student_image`, `student_stage`, `rate`, `token`, `balance`, `is_active`, `created_at`) VALUES
(1, 'احمد حسن', '01156565889', '', 3, 0, '%Ks4zhaZvA4AFuqhFyWYEk7SZ', 4100, 1, '2024-11-07'),
(2, 'asd', '123', '', 1, 0, 'ibBG6kdYAL9TBim1ur%!01rYW', 0, 0, '2024-12-10'),
(3, 'dddd', '555551111', '', 1, 0, 'l0y4a@EUQ$%5@VZRmR@RLIbbz', 0, 0, '2024-12-10'),
(4, 'dddd', '5555511112', '', 1, 0, 'e9fe7b0719efcfb86e351a7c3c05736bb34071e0', 0, 0, '2024-12-10'),
(5, 'asd', '1243', '', 2, 0, 'c6009996551a8df1b038c6c1d88fd350958c88f5', 0, 0, '2024-12-10'),
(6, 'ssssssd', '5555', '', 5, 0, '8c05b16136cf929c1a8383ced5894bb2fb9e12ba', 0, 0, '2024-12-10'),
(7, 'ssssssds', '555523', '', 7, 0, '4bebf066cde0314aa65303b074ad79246b7a80c3', 0, 0, '2024-12-10'),
(8, 'ali', '123456789', '', 4, 0, 'f830723e2f5cf64d0d920eb5d5df4c394016dbfa', 0, 0, '2024-12-11');

-- --------------------------------------------------------

--
-- Table structure for table `student_ad`
--

CREATE TABLE `student_ad` (
  `id` int(11) NOT NULL,
  `student_id` int(255) NOT NULL,
  `student_price` int(11) NOT NULL,
  `subject_id` int(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `days` char(7) DEFAULT '0000000',
  `session_date` date DEFAULT NULL,
  `time` time NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` int(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_ad`
--

INSERT INTO `student_ad` (`id`, `student_id`, `student_price`, `subject_id`, `description`, `days`, `session_date`, `time`, `created_at`, `status`) VALUES
(2, 1, 500, 1, 'اريد معلم متخصص فى مادة العلوم لشرح الباب الاول و مساعدتى فى المسائل .', '0100000', '2022-04-22', '00:00:00', '2024-12-16 13:14:59', 1),
(3, 1, 400, 3, 'اريد معلم متخصص فى مادة العلوم لشرح الباب الاول و مساعدتى فى المسائل .', '0000000', '2022-04-22', '00:00:00', '2024-12-16 13:14:59', 1),
(4, 1, 400, 3, 'اريد معلم متخصص فى مادة العلوم لشرح الباب الاول و مساعدتى فى المسائل .', '0000001', '2022-04-22', '00:00:00', '2024-12-16 13:14:59', 1),
(5, 1, 400, 3, 'اريد معلم متخصص فى مادة العلوم لشرح الباب الاول و مساعدتى فى المسائل .', '0000000', '2022-04-22', '00:00:00', '2024-12-16 13:14:59', 1),
(6, 1, 400, 3, 'اريد معلم متخصص فى مادة العلوم لشرح الباب الاول و مساعدتى فى المسائل .', '0000001', '2022-04-22', '14:30:00', '2024-12-16 13:14:59', 1),
(7, 1, 400, 3, 'اريد معلم متخصص فى مادة العلوم لشرح الباب الاول و مساعدتى فى المسائل .', '0000001', '2022-04-22', '14:30:00', '2024-12-16 13:14:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_rating`
--

CREATE TABLE `student_rating` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `rating` int(255) NOT NULL,
  `rank` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `icon`) VALUES
(1, 'اللغة العربية', 'assets/images/arabic.png'),
(2, 'اللغة الإنجليزية', 'assets/images/english.png'),
(3, 'الرياضيات', 'assets/images/math.png'),
(4, 'العلوم', 'assets/images/science.png'),
(12, 'التاريخ', 'assets/images/history.png'),
(13, 'الجغرافيا', 'assets/images/geography.png'),
(15, 'علم النفس', 'assets/images/humanScince.png'),
(16, 'الفلسفة', 'assets/images/philosofy.png');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `id` int(11) NOT NULL,
  `teacher_name` varchar(255) NOT NULL,
  `teacher_number` varchar(255) NOT NULL,
  `teacher_image` varchar(255) NOT NULL,
  `gender` tinyint(4) NOT NULL DEFAULT 1,
  `token` varchar(255) NOT NULL,
  `teacher_subject` varchar(255) NOT NULL,
  `followers` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `balance` int(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`id`, `teacher_name`, `teacher_number`, `teacher_image`, `gender`, `token`, `teacher_subject`, `followers`, `rank`, `price`, `balance`, `is_active`, `created_at`) VALUES
(2, 'احمد علي', '555558888', '', 1, 'Mymmf^Kt8btmWDVn%y$!ib$)l', '11,2,16', 0, 0, 400, 0, 1, '2024-11-07'),
(3, 'mossad', '1232332', '', 1, '7e5d6166788de36f8ff171ae37e8621af7dd020f', '1,2,3', 0, 0, 0, 0, 1, '2024-12-11'),
(4, 'mossad', '1018089212', '', 1, 'b3b168ce636689c4a74c03cac9dcecbadd2b6ba1', '1,2,3', 0, 0, 0, 0, 1, '2024-12-11'),
(5, 'mossad', '01018089212', '', 1, 'dcacb02c8715b2793b127820958ef274c5b03045', '1,2,3', 0, 0, 0, 0, 1, '2024-12-11'),
(6, 'ali haa', '12345678', '', 1, '6bc8ce636eafe03595edc508db44a787a1e2b698', '10,11', 0, 0, 0, 0, 1, '2024-12-11'),
(7, 'فوزي الحناوي', '010936996666', '', 1, 'ec5b28ece4290ea83c084ed0a84c95b9fb7c0e9a', '1,2,3', 0, 0, 0, 0, 1, '2024-12-11');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_ad`
--

CREATE TABLE `teacher_ad` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `teacher_price` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `unit_num` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_ad`
--

INSERT INTO `teacher_ad` (`id`, `teacher_id`, `teacher_price`, `subject_id`, `unit_num`, `description`, `date`, `status`, `created_at`) VALUES
(4, 2, 200, 1, 0, '', '2024-12-28 14:30:00', 1, '2024-12-28'),
(5, 2, 200, 1, 0, 'description', '2024-12-28 14:30:00', 1, '2024-12-28'),
(6, 2, 200, 1, 1, 'description', '2024-12-28 14:30:00', 1, '2025-01-17'),
(7, 7, 200, 1, 2, 'sdsdsd', '2025-01-17 16:39:00', 1, '2025-01-17'),
(8, 7, 200, 1, 2, 'sdsdsd', '2025-01-17 16:39:00', 1, '2025-01-17'),
(9, 7, 33, 1, 2, 'sdsdsd', '2025-01-17 16:39:00', 1, '2025-01-17'),
(10, 7, 33, 1, 2, 'sdsdsd', '2025-01-17 16:39:00', 1, '2025-01-17'),
(11, 7, 33, 1, 2, 'sdsdsd', '2025-01-17 16:39:00', 1, '2025-01-17');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `id` int(11) NOT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_number` varchar(255) NOT NULL,
  `reciver_name` varchar(255) NOT NULL,
  `recevier_number` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`id`, `sender_name`, `sender_number`, `reciver_name`, `recevier_number`, `amount`, `status`) VALUES
(1, 'احمد حسن', '01156565889', 'محمد محمود', '1018089212', 500, 1),
(2, 'احمد حسن', '01156565889', 'احمد علي', '555558888', 400, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_transaction`
--
ALTER TABLE `app_transaction`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_ad` (`student_ad`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `school_steps`
--
ALTER TABLE `school_steps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_stage` (`student_stage`);

--
-- Indexes for table `student_ad`
--
ALTER TABLE `student_ad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_rating`
--
ALTER TABLE `student_rating`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_rating_ibfk_2` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_teacher_number` (`teacher_number`);

--
-- Indexes for table `teacher_ad`
--
ALTER TABLE `teacher_ad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_transaction`
--
ALTER TABLE `app_transaction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_steps`
--
ALTER TABLE `school_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `student_ad`
--
ALTER TABLE `student_ad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_rating`
--
ALTER TABLE `student_rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `teacher_ad`
--
ALTER TABLE `teacher_ad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`student_ad`) REFERENCES `student_ad` (`id`),
  ADD CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher_ad` (`id`);

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sessions_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`student_stage`) REFERENCES `school_steps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_ad`
--
ALTER TABLE `student_ad`
  ADD CONSTRAINT `student_ad_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_ad_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_rating`
--
ALTER TABLE `student_rating`
  ADD CONSTRAINT `student_rating_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_rating_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`);

--
-- Constraints for table `teacher_ad`
--
ALTER TABLE `teacher_ad`
  ADD CONSTRAINT `teacher_ad_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`),
  ADD CONSTRAINT `teacher_ad_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
