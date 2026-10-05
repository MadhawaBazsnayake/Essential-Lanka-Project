-- ============================================================
-- Essential Lanka Project - Tool Sharing Mini-Market Schema
-- ============================================================

-- 1. Update users role enum to include 'tool_provider'
ALTER TABLE `users` MODIFY COLUMN `role` ENUM('client', 'worker', 'admin', 'tool_provider') NOT NULL DEFAULT 'client';

-- 2. Tools / Equipment Table
CREATE TABLE IF NOT EXISTS `tools` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `provider_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `daily_rate` DECIMAL(10,2) NOT NULL,
  `hourly_rate` DECIMAL(10,2) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `is_available` TINYINT(1) NOT NULL DEFAULT 1,
  `location_name` VARCHAR(150) DEFAULT NULL,
  `location_lat` DECIMAL(10,8) DEFAULT NULL,
  `location_lng` DECIMAL(11,8) DEFAULT NULL,
  `contact_phone` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_provider` (`provider_id`),
  INDEX `idx_available` (`is_available`),
  INDEX `idx_category` (`category`),
  CONSTRAINT `fk_tools_provider` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tool Rental Bookings Table
CREATE TABLE IF NOT EXISTS `tool_rentals` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tool_id` BIGINT UNSIGNED NOT NULL,
  `renter_id` BIGINT UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `days_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `total_price` DECIMAL(10,2) NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_rental_tool` (`tool_id`),
  INDEX `idx_rental_renter` (`renter_id`),
  INDEX `idx_rental_status` (`status`),
  CONSTRAINT `fk_rentals_tool` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rentals_renter` FOREIGN KEY (`renter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
