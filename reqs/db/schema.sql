-------------------------------------------------------------------------------------
--                         Author: Abderrahmane Abdelouafi                         --
--                              File Name: schema.sql                              --
--                    Creation Date: October 03, 2025 05:59 AM                     --
--                     Last Updated: October 03, 2025 06:22 PM                     --
--                            Source Language: SQL (mysql)                         -- --                                                                                 --
--                             --- Code Description ---                            --
--     Defines the core database schema for the LEETMAKERS platform. This file     --
--  includes identity management, activity logging, and user notification tables,  --
--    along with indexes and relational constraints to ensure data integrity.      --
-------------------------------------------------------------------------------------

-- Use database
USE `LEETMAKERS`;

-- =============================
-- Table: identity (users)
-- =============================
CREATE TABLE IF NOT EXISTS `identity` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `firstname` VARCHAR(32) NOT NULL,
  `lastname` VARCHAR(32) NOT NULL,
  `username` VARCHAR(16) UNIQUE DEFAULT NULL,
  `email` VARCHAR(64) NOT NULL UNIQUE,
  `gender` ENUM('male', 'female', 'unspecified') DEFAULT 'unspecified',
  `picture` VARCHAR(255) DEFAULT NULL,
  `pictureType` ENUM('default', 'uploaded', 'url') NOT NULL DEFAULT 'default',
  `pictureDirID` VARCHAR(32) UNIQUE DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `accountType` ENUM('normal', 'oauth') NOT NULL DEFAULT 'normal',
  `oauthProvider` ENUM('google', '42 intra') DEFAULT NULL,
  `accountStatus` ENUM('verified', 'unverified', 'banned') NOT NULL DEFAULT 'unverified',
  `terms` VARCHAR(10) DEFAULT 'accepted',
  `googleID` VARCHAR(64) UNIQUE DEFAULT NULL,
  `otpToken` VARCHAR(6) DEFAULT NULL,
  `otpTokenExpiry` DATETIME DEFAULT NULL,
  `resetTokenHash` VARCHAR(64) DEFAULT NULL,
  `resetTokenExpiry` DATETIME DEFAULT NULL,
  `role` ENUM('visitor', 'member', 'admin') NOT NULL DEFAULT 'visitor',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_username` (`username`),
  INDEX `idx_googleID` (`googleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `identity` AUTO_INCREMENT = 1;

-- =============================
-- Table: activity (logs)
-- =============================
CREATE TABLE IF NOT EXISTS `activity` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `identity`(`id`) ON DELETE CASCADE,
  INDEX `idx_activity_user_id` (`user_id`),
  INDEX `idx_activity_action` (`action`),
  INDEX `idx_activity_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================
-- Table: notifications
-- =============================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `type` ENUM('account', 'security', 'event', 'other') NOT NULL DEFAULT 'other',
  `subject` VARCHAR(64) NOT NULL,
  `message` TEXT NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `identity`(`id`) ON DELETE CASCADE,
  INDEX `idx_notifications_user_id` (`user_id`),
  INDEX `idx_notifications_created_at` (`created_at`),
  INDEX `idx_notifications_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

