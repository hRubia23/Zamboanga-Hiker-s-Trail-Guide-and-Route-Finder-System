CREATE TABLE IF NOT EXISTS `trail_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trail_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_trail_views_trail_date` (`trail_id`, `viewed_at`),
  KEY `idx_trail_views_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
