-- TABLE USERS (users)
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('member','admin') NOT NULL DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
);

-- TABLE URL SHORTENER (url_mappings)
CREATE TABLE `url_mappings` (
  `id` int(11) NOT NULL,
  `short_url` varchar(255) NOT NULL,
  `original_url` varchar(255) NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
);