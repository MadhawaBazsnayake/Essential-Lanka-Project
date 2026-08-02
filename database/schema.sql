-- පද්ධතියේ භාෂා ගැටලු මඟහරවා ගැනීමට UTF-8 (Sinhala/English) සහාය ලබා දීම
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table (ගිණුම් පවත්වාගෙන යාම)
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(20) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('client', 'worker', 'admin') NOT NULL,
  `profile_pic` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_role` (`role`),          -- Role එකෙන් Search කිරීම වේගවත් කිරීමට
  INDEX `idx_phone` (`phone`)         -- Login වීම වේගවත් කිරීමට
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Categories Table (සේවා වර්ග)
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_en` VARCHAR(100) NOT NULL,
  `name_si` VARCHAR(100) NOT NULL,
  `icon_class` VARCHAR(50) DEFAULT NULL,
  `status` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Worker Profiles Table (සේවා සපයන්නන්ගේ විශේෂිත දත්ත)
-- Users table එකේ බර අඩු කර පද්ධතිය Scale කිරීමට මෙලෙස වෙනම Table එකක් සෑදීම ඉතා වැදගත් වේ.
CREATE TABLE `worker_profiles` (
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `nic_number` VARCHAR(20) UNIQUE,
  `verification_status` ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
  `bio` TEXT,
  `rating` DECIMAL(3,2) DEFAULT 0.00,
  `total_reviews` INT DEFAULT 0,
  `location_lat` DECIMAL(10, 8) DEFAULT NULL,
  `location_lng` DECIMAL(11, 8) DEFAULT NULL,
  PRIMARY KEY (`worker_id`),
  INDEX `idx_location` (`location_lat`, `location_lng`), -- ළඟම ඉන්න අය සෙවීමට GPS ඛණ්ඩාංක Index කිරීම
  FOREIGN KEY (`worker_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Jobs / Requests Table (පාරිභෝගික අවශ්‍යතා)
CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` BIGINT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `budget` DECIMAL(10,2) DEFAULT NULL,
  `location_lat` DECIMAL(10, 8) NOT NULL,
  `location_lng` DECIMAL(11, 8) NOT NULL,
  `status` ENUM('open', 'in_progress', 'completed', 'cancelled') DEFAULT 'open',
  `assigned_worker_id` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_client_jobs` (`client_id`, `status`),
  FOREIGN KEY (`client_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`),
  FOREIGN KEY (`assigned_worker_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Job Images Table (එක් අවශ්‍යතාවයකට පින්තූර කිහිපයක් දැමීමට)
CREATE TABLE `job_images` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` BIGINT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Bids Table (සේවකයින් විසින් ලබාදෙන මිල ගණන්)
CREATE TABLE `bids` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` BIGINT UNSIGNED NOT NULL,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `proposal` TEXT,
  `status` ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_job_bids` (`job_id`, `status`),
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`worker_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Portfolios Table (සේවකයාගේ පෙර වැඩ ඡායාරූප)
CREATE TABLE `portfolios` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`worker_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Reviews Table (පාරිභෝගික අදහස් සහ තරු රේටින්ග්ස්)
CREATE TABLE `reviews` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` BIGINT UNSIGNED NOT NULL,
  `reviewer_id` BIGINT UNSIGNED NOT NULL,
  `worker_id` BIGINT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `comment` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`worker_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Categories (ආරම්භක සේවා වර්ග දත්ත ගබඩාවට ඇතුලත් කිරීම)
INSERT INTO `categories` (`name_en`, `name_si`, `icon_class`) VALUES
('Electrical & Plumbing', 'විදුලි සහ නල පද්ධති', 'icon-electrical'),
('Masonry & Carpentry', 'පෙදරේරු සහ වඩු වැඩ', 'icon-masonry'),
('Cleaning & Painting', 'පිරිසිදු කිරීම් සහ තීන්ත ගෑම', 'icon-cleaning');

SET FOREIGN_KEY_CHECKS = 1;