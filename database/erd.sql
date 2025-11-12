CREATE TABLE `users` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255),
  `email` varchar(255) UNIQUE,
  `password` varchar(255),
  `remember_token` varchar(255),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `profiles` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint,
  `full_name` varchar(255),
  `phone` varchar(255),
  `address` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `categories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255),
  `description` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `items` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `category_id` bigint,
  `name` varchar(255),
  `description` text,
  `photo_url` varchar(255),
  `status` varchar(255),
  `price_per_period` decimal(12,2),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `rentals` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint,
  `total_price` decimal(12,2),
  `start_date` date,
  `end_date` date,
  `status` varchar(255),
  `payment_method` varchar(255),
  `payment_status` varchar(255),
  `payment_gateway_response` json,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `rental_items` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `rental_id` bigint,
  `item_id` bigint,
  `quantity` int,
  `item_status` varchar(255)
);

CREATE TABLE `activity_logs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint,
  `action` varchar(255),
  `target_table` varchar(255),
  `target_id` bigint,
  `description` text,
  `ip_address` varchar(255),
  `user_agent` text,
  `created_at` timestamp
);

CREATE TABLE `notifications` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint,
  `type` varchar(255),
  `message` text,
  `read_at` timestamp,
  `created_at` timestamp,
  `updated_at` timestamp
);

ALTER TABLE `profiles` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `items` ADD FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

ALTER TABLE `rentals` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `rental_items` ADD FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`);

ALTER TABLE `rental_items` ADD FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

ALTER TABLE `activity_logs` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `notifications` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
