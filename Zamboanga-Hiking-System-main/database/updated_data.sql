








SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


;
;
;
;











CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;





INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', 'admin123', '2025-11-18 05:31:25');







CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `trail_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;





INSERT INTO `reviews` (`id`, `trail_id`, `user_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(1, 1, 4, 4, 'hsjhdjshdj', 'approved', '2025-11-18 17:06:46'),
(2, 1, 4, 4, 'jfdhjhdjsd', 'approved', '2025-11-19 00:41:48'),
(5, 1, 4, 5, 'trail was challenging, but the view at the top make it all worth it!:)', 'approved', '2025-11-19 01:36:09'),
(6, 1, 4, 1, 'shoo', 'rejected', '2025-11-19 01:46:11');







CREATE TABLE `trails` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `difficulty` enum('Easy','Moderate','Hard') DEFAULT 'Moderate',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;





INSERT INTO `trails` (`id`, `name`, `description`, `location`, `difficulty`, `image`, `created_at`) VALUES
(1, 'Abong-Abong Park Trail', 'Scenic trail with mountain views', 'Zamboanga City', 'Moderate', 'abong.jpg', '2025-11-18 05:31:46');







CREATE TABLE `trail_reviews` (
  `review_id` int(11) NOT NULL,
  `trail_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text NOT NULL,
  `review_date` datetime DEFAULT current_timestamp(),
  `is_approved` tinyint(4) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;







CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;





INSERT INTO `users` (`id`, `username`, `password`, `created_at`, `first_name`, `last_name`, `email`, `is_verified`) VALUES
(1, 'admin', '*01A6717B58FF5C7EAFFF6CB7C96F7428EA65FE4C', '2025-11-18 05:31:25', '', '', '', 0),
(4, 'lynn23', '$2y$10$eT1f4Wbsl65E8vzp09J4teu8pbx..fNEzi8uTnJsypWL8AlMIsIbO', '2025-11-18 08:08:34', 'lynn', 'rubia', 'heidilynnrubia23@gmail.com', 1);







CREATE TABLE `verification_codes` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;





INSERT INTO `verification_codes` (`id`, `email`, `code`, `created_at`, `is_used`) VALUES
(1, 'heidilynnrubia23@gmail.com', '319882', '2025-11-18 08:05:06', 0),
(2, 'heidilynnrubia23@gmail.com', '830893', '2025-11-18 08:07:46', 1),
(3, 'hezekiahsarita@gmail.com', '586380', '2025-11-18 08:12:40', 0),
(4, 'josiebanalo977@gmail.com', '994241', '2025-11-18 08:22:21', 1);








ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);




ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trail_id` (`trail_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);




ALTER TABLE `trails`
  ADD PRIMARY KEY (`id`);




ALTER TABLE `trail_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `trail_id` (`trail_id`);




ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_email` (`email`);




ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_code` (`code`);








ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;




ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;




ALTER TABLE `trails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;




ALTER TABLE `trail_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;




ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;




ALTER TABLE `verification_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;








ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`trail_id`) REFERENCES `trails` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;




ALTER TABLE `trail_reviews`
  ADD CONSTRAINT `trail_reviews_ibfk_1` FOREIGN KEY (`trail_id`) REFERENCES `trails` (`id`);
COMMIT;

;
;
;
