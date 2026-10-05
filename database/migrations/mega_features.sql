-- ============================================================
-- Essential Lanka — Mega Features Migration
-- Run this ONCE in phpMyAdmin after backing up your database
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------
-- A1: OTP Verification for Job Completion
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `job_completion_otp` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` BIGINT UNSIGNED NOT NULL,
  `otp_code` VARCHAR(6) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_otp_job` (`job_id`, `used`),
  CONSTRAINT `fk_otp_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- A2: Police Clearance + Worker Tier columns
-- -----------------------------------------------
-- Add police clearance fields to worker_profiles (ignore if already exists)
ALTER TABLE `worker_profiles`
  ADD COLUMN IF NOT EXISTS `police_clearance_path` VARCHAR(255) DEFAULT NULL AFTER `nic_back`,
  ADD COLUMN IF NOT EXISTS `police_clearance_status` ENUM('not_submitted','pending','approved','rejected') DEFAULT 'not_submitted' AFTER `police_clearance_path`,
  ADD COLUMN IF NOT EXISTS `worker_tier` ENUM('new','rising','pro','top_rated') DEFAULT 'new' AFTER `police_clearance_status`,
  ADD COLUMN IF NOT EXISTS `nic_front` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `nic_back` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `is_verified` TINYINT(1) DEFAULT 0;

-- -----------------------------------------------
-- C2: Urgent / Emergency Jobs + extra job columns
-- -----------------------------------------------
-- Add columns one by one to handle existing columns gracefully
ALTER TABLE `jobs`
  ADD COLUMN IF NOT EXISTS `client_completed` BOOLEAN DEFAULT FALSE,
  ADD COLUMN IF NOT EXISTS `worker_completed` BOOLEAN DEFAULT FALSE,
  ADD COLUMN IF NOT EXISTS `accepted_bid_amount` DECIMAL(10,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `platform_fee` DECIMAL(10,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `refund_amount` DECIMAL(10,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `province` VARCHAR(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `district` VARCHAR(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `contact_number` VARCHAR(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `location` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `is_urgent` TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `urgency_expires_at` DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `completion_otp` VARCHAR(6) DEFAULT NULL;

-- -----------------------------------------------
-- D2: Voice Note & Location in Chat
-- -----------------------------------------------
ALTER TABLE `messages`
  ADD COLUMN IF NOT EXISTS `message_type` ENUM('text','voice','location','image') DEFAULT 'text' AFTER `message`,
  ADD COLUMN IF NOT EXISTS `attachment_path` VARCHAR(255) DEFAULT NULL AFTER `message_type`,
  ADD COLUMN IF NOT EXISTS `location_lat` DECIMAL(10,8) DEFAULT NULL AFTER `attachment_path`,
  ADD COLUMN IF NOT EXISTS `location_lng` DECIMAL(11,8) DEFAULT NULL AFTER `location_lat`;

-- -----------------------------------------------
-- E1: User Language Preference
-- -----------------------------------------------
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `preferred_lang` VARCHAR(5) DEFAULT 'en' AFTER `is_active`,
  ADD COLUMN IF NOT EXISTS `email` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `alt_phone` VARCHAR(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `address` TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `paypal_email` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `profile_picture` VARCHAR(255) DEFAULT NULL;

-- -----------------------------------------------
-- D1: SMS Log Table
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `sms_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone` VARCHAR(20) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('sent','failed','simulated') DEFAULT 'simulated',
  `provider_response` TEXT DEFAULT NULL,
  `sent_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_sms_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Notifications Table (in-app)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'info',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `data_json` JSON DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_notif_user` (`user_id`, `is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Payments table (if not exists)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` BIGINT UNSIGNED NOT NULL,
  `client_id` BIGINT UNSIGNED NOT NULL,
  `worker_id` BIGINT UNSIGNED DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `paypal_order_id` VARCHAR(100) DEFAULT NULL,
  `paypal_capture_id` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_job_payment` (`job_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Done! All mega feature tables and columns are ready.
