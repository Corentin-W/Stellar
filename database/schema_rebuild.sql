SET FOREIGN_KEY_CHECKS = 0;

-- Core Laravel tables ------------------------------------------------------
DROP TABLE IF EXISTS `promotion_usages`;
DROP TABLE IF EXISTS `image_captures`;
DROP TABLE IF EXISTS `observation_sessions`;
DROP TABLE IF EXISTS `support_ticket_histories`;
DROP TABLE IF EXISTS `support_attachments`;
DROP TABLE IF EXISTS `support_messages`;
DROP TABLE IF EXISTS `support_templates`;
DROP TABLE IF EXISTS `support_tickets`;
DROP TABLE IF EXISTS `support_categories`;
DROP TABLE IF EXISTS `credit_transactions`;
DROP TABLE IF EXISTS `credit_packages`;
DROP TABLE IF EXISTS `promotions`;
DROP TABLE IF EXISTS `waiting_list`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- Users --------------------------------------------------------------------
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `admin` TINYINT(1) NOT NULL DEFAULT 0,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `credits_balance` INT NOT NULL DEFAULT 0,
  `total_credits_purchased` INT NOT NULL DEFAULT 0,
  `total_credits_used` INT NOT NULL DEFAULT 0,
  `stripe_customer_id` VARCHAR(255) NULL DEFAULT NULL,
  `subscription_type` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_stripe_customer_id_unique` (`stripe_customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL DEFAULT NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Credit packages & transactions ------------------------------------------
CREATE TABLE `credit_packages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `credits_amount` INT NOT NULL DEFAULT 0,
  `price_cents` INT NOT NULL DEFAULT 0,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'EUR',
  `stripe_price_id` VARCHAR(255) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `bonus_credits` INT NOT NULL DEFAULT 0,
  `discount_percentage` INT NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `credit_packages_stripe_price_id_unique` (`stripe_price_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `promotions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `type` VARCHAR(50) NOT NULL,
  `value` INT NOT NULL DEFAULT 0,
  `min_purchase_amount` INT NOT NULL DEFAULT 0,
  `max_uses` INT NULL DEFAULT NULL,
  `used_count` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `starts_at` TIMESTAMP NULL DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `applicable_packages` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `promotions_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `credit_transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `credits_amount` INT NOT NULL,
  `balance_before` INT NOT NULL DEFAULT 0,
  `balance_after` INT NOT NULL DEFAULT 0,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `reference_type` VARCHAR(255) NULL DEFAULT NULL,
  `reference_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `stripe_payment_intent_id` VARCHAR(255) NULL DEFAULT NULL,
  `credit_package_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `metadata` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `credit_transactions_user_id_index` (`user_id`),
  KEY `credit_transactions_reference_index` (`reference_type`, `reference_id`),
  KEY `credit_transactions_credit_package_id_index` (`credit_package_id`),
  KEY `credit_transactions_created_by_index` (`created_by`),
  KEY `credit_transactions_stripe_payment_intent_id_index` (`stripe_payment_intent_id`),
  CONSTRAINT `credit_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `credit_transactions_credit_package_id_foreign` FOREIGN KEY (`credit_package_id`) REFERENCES `credit_packages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `credit_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `promotion_usages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `promotion_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `credit_transaction_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `discount_amount` INT NOT NULL DEFAULT 0,
  `used_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promotion_usages_promotion_id_index` (`promotion_id`),
  KEY `promotion_usages_user_id_index` (`user_id`),
  KEY `promotion_usages_credit_transaction_id_index` (`credit_transaction_id`),
  CONSTRAINT `promotion_usages_promotion_id_foreign` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promotion_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promotion_usages_credit_transaction_id_foreign` FOREIGN KEY (`credit_transaction_id`) REFERENCES `credit_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Observation sessions & images -------------------------------------------
CREATE TABLE `observation_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `telescope_id` VARCHAR(100) NULL DEFAULT NULL,
  `target_name` VARCHAR(255) NULL DEFAULT NULL,
  `target_coordinates` JSON NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'scheduled',
  `credits_cost` INT NOT NULL DEFAULT 0,
  `duration_minutes` INT NULL DEFAULT NULL,
  `scheduled_at` TIMESTAMP NULL DEFAULT NULL,
  `started_at` TIMESTAMP NULL DEFAULT NULL,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  `images_captured` INT NOT NULL DEFAULT 0,
  `weather_conditions` JSON NULL,
  `session_data` JSON NULL,
  `credit_transaction_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `observation_sessions_user_id_index` (`user_id`),
  KEY `observation_sessions_status_index` (`status`),
  KEY `observation_sessions_credit_transaction_id_index` (`credit_transaction_id`),
  CONSTRAINT `observation_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `observation_sessions_credit_transaction_id_foreign` FOREIGN KEY (`credit_transaction_id`) REFERENCES `credit_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `image_captures` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` INT NOT NULL DEFAULT 0,
  `image_type` VARCHAR(50) NULL DEFAULT NULL,
  `filter_used` VARCHAR(100) NULL DEFAULT NULL,
  `exposure_time` DECIMAL(10,2) NULL DEFAULT NULL,
  `iso_value` INT NULL DEFAULT NULL,
  `credits_cost` INT NOT NULL DEFAULT 0,
  `processing_status` VARCHAR(50) NULL DEFAULT 'pending',
  `metadata` JSON NULL,
  `credit_transaction_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `image_captures_session_id_index` (`session_id`),
  KEY `image_captures_user_id_index` (`user_id`),
  KEY `image_captures_credit_transaction_id_index` (`credit_transaction_id`),
  CONSTRAINT `image_captures_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `observation_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `image_captures_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `image_captures_credit_transaction_id_foreign` FOREIGN KEY (`credit_transaction_id`) REFERENCES `credit_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support system -----------------------------------------------------------
CREATE TABLE `support_categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `color` VARCHAR(50) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_number` VARCHAR(50) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `assigned_to` BIGINT UNSIGNED NULL DEFAULT NULL,
  `category_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `priority` VARCHAR(20) NOT NULL DEFAULT 'normal',
  `status` VARCHAR(20) NOT NULL DEFAULT 'open',
  `is_internal` TINYINT(1) NOT NULL DEFAULT 0,
  `last_reply_at` TIMESTAMP NULL DEFAULT NULL,
  `last_reply_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `resolved_at` TIMESTAMP NULL DEFAULT NULL,
  `resolved_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `closed_at` TIMESTAMP NULL DEFAULT NULL,
  `closed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_tickets_ticket_number_unique` (`ticket_number`),
  KEY `support_tickets_user_id_index` (`user_id`),
  KEY `support_tickets_assigned_to_index` (`assigned_to`),
  KEY `support_tickets_category_id_index` (`category_id`),
  KEY `support_tickets_status_index` (`status`),
  KEY `support_tickets_priority_index` (`priority`),
  KEY `support_tickets_last_reply_by_index` (`last_reply_by`),
  KEY `support_tickets_resolved_by_index` (`resolved_by`),
  KEY `support_tickets_closed_by_index` (`closed_by`),
  CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `support_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_last_reply_by_foreign` FOREIGN KEY (`last_reply_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `message` LONGTEXT NOT NULL,
  `is_internal` TINYINT(1) NOT NULL DEFAULT 0,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `attachments` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_messages_ticket_id_index` (`ticket_id`),
  KEY `support_messages_user_id_index` (`user_id`),
  CONSTRAINT `support_messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_attachments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `message_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` BIGINT NOT NULL DEFAULT 0,
  `mime_type` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_attachments_ticket_id_index` (`ticket_id`),
  KEY `support_attachments_message_id_index` (`message_id`),
  KEY `support_attachments_user_id_index` (`user_id`),
  CONSTRAINT `support_attachments_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_attachments_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `support_messages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_attachments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_ticket_histories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `old_value` TEXT NULL,
  `new_value` TEXT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_ticket_histories_ticket_id_index` (`ticket_id`),
  KEY `support_ticket_histories_user_id_index` (`user_id`),
  CONSTRAINT `support_ticket_histories_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_templates` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `category_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `usage_count` INT NOT NULL DEFAULT 0,
  `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_templates_category_id_index` (`category_id`),
  KEY `support_templates_created_by_index` (`created_by`),
  CONSTRAINT `support_templates_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `support_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Waiting list --------------------------------------------------------------
CREATE TABLE `waiting_list` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `interest_level` VARCHAR(30) NOT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` TEXT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'pending',
  `confirmed_at` TIMESTAMP NULL DEFAULT NULL,
  `confirmation_token` VARCHAR(64) NULL DEFAULT NULL,
  `metadata` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `waiting_list_email_unique` (`email`),
  UNIQUE KEY `waiting_list_confirmation_token_unique` (`confirmation_token`),
  KEY `waiting_list_status_index` (`status`),
  KEY `waiting_list_interest_level_index` (`interest_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
