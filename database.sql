-- FILE: /database.sql
-- SplashCreator - Multi-Tenant SaaS Database Schema
-- Compatible with MySQL 5.7+ / MariaDB 10.2+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS splashcreator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE splashcreator;

-- ========================================
-- MULTI-TENANT CORE TABLES
-- ========================================

CREATE TABLE IF NOT EXISTS `tenants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(255) NOT NULL,
  `subdomain` VARCHAR(100) NOT NULL,
  `status` ENUM('active', 'suspended', 'cancelled') NOT NULL DEFAULT 'active',
  `owner_email` VARCHAR(255) NOT NULL,
  `settings_json` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subdomain` (`subdomain`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `price_monthly` DECIMAL(10,2) NOT NULL,
  `max_text_generations` INT NOT NULL DEFAULT 100,
  `max_image_generations` INT NOT NULL DEFAULT 50,
  `max_video_generations` INT NOT NULL DEFAULT 10,
  `max_scheduled_posts` INT NOT NULL DEFAULT 100,
  `social_accounts_limit` INT NOT NULL DEFAULT 5,
  `features_json` TEXT,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tenant_subscriptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `status` ENUM('active', 'cancelled', 'expired') NOT NULL DEFAULT 'active',
  `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tenant_usage` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `month` VARCHAR(7) NOT NULL COMMENT 'YYYY-MM format',
  `text_generations_count` INT NOT NULL DEFAULT 0,
  `image_generations_count` INT NOT NULL DEFAULT 0,
  `video_generations_count` INT NOT NULL DEFAULT 0,
  `scheduled_posts_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tenant_month` (`tenant_id`, `month`),
  KEY `idx_tenant_id` (`tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tenant_api_keys` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `api_key` VARCHAR(64) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `idx_tenant_id` (`tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'NULL for platform admins',
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `role` ENUM('platform_admin', 'tenant_admin', 'content_creator', 'social_manager', 'viewer') NOT NULL DEFAULT 'content_creator',
  `avatar_path` VARCHAR(500) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_role` (`role`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- AI GENERATION TABLES
-- ========================================

CREATE TABLE IF NOT EXISTS `ai_text_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `prompt` TEXT NOT NULL,
  `result_text` TEXT,
  `model_used` VARCHAR(100) DEFAULT 'gpt-4',
  `tokens_count` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `prompt` TEXT NOT NULL,
  `image_path` VARCHAR(500) NOT NULL,
  `style` VARCHAR(100) DEFAULT 'realistic',
  `width` INT UNSIGNED DEFAULT 1024,
  `height` INT UNSIGNED DEFAULT 1024,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_videos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `script_text` TEXT NOT NULL,
  `video_path` VARCHAR(500) NOT NULL,
  `duration_seconds` INT UNSIGNED DEFAULT 0,
  `resolution` VARCHAR(20) DEFAULT '1920x1080',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- BRAND KITS
-- ========================================

CREATE TABLE IF NOT EXISTS `brand_kits` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `colors_json` TEXT COMMENT 'Array of hex colors',
  `fonts_json` TEXT COMMENT 'Font preferences',
  `logo_path` VARCHAR(500) NULL DEFAULT NULL,
  `voice_tone` VARCHAR(100) DEFAULT 'professional' COMMENT 'professional, casual, friendly, authoritative',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SOCIAL MEDIA ACCOUNTS
-- ========================================

CREATE TABLE IF NOT EXISTS `social_accounts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `platform` ENUM('instagram', 'facebook', 'tiktok', 'twitter', 'linkedin', 'youtube') NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `access_token` TEXT COMMENT 'Simulated token',
  `status` ENUM('active', 'disconnected', 'error') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_platform` (`platform`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- CONTENT LIBRARY
-- ========================================

CREATE TABLE IF NOT EXISTS `content_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `type` ENUM('text', 'image', 'video') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content_text` TEXT,
  `media_path` VARCHAR(500) NULL DEFAULT NULL,
  `tags_json` TEXT COMMENT 'Array of tags',
  `brand_kit_id` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`brand_kit_id`) REFERENCES `brand_kits`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SCHEDULING & POSTING
-- ========================================

CREATE TABLE IF NOT EXISTS `scheduled_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `content_item_id` INT UNSIGNED NOT NULL,
  `social_account_id` INT UNSIGNED NOT NULL,
  `platform` ENUM('instagram', 'facebook', 'tiktok', 'twitter', 'linkedin', 'youtube') NOT NULL,
  `scheduled_time_utc` TIMESTAMP NOT NULL,
  `status` ENUM('scheduled', 'posted', 'failed', 'cancelled') NOT NULL DEFAULT 'scheduled',
  `log_message` TEXT,
  `posted_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_content_item_id` (`content_item_id`),
  KEY `idx_social_account_id` (`social_account_id`),
  KEY `idx_scheduled_time` (`scheduled_time_utc`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`content_item_id`) REFERENCES `content_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- ANALYTICS (SIMULATED)
-- ========================================

CREATE TABLE IF NOT EXISTS `social_metrics` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `platform` ENUM('instagram', 'facebook', 'tiktok', 'twitter', 'linkedin', 'youtube') NOT NULL,
  `content_item_id` INT UNSIGNED NULL DEFAULT NULL,
  `impressions` INT UNSIGNED DEFAULT 0,
  `likes` INT UNSIGNED DEFAULT 0,
  `comments` INT UNSIGNED DEFAULT 0,
  `shares` INT UNSIGNED DEFAULT 0,
  `clicks` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_platform` (`platform`),
  KEY `idx_content_item_id` (`content_item_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`content_item_id`) REFERENCES `content_items`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TEMPLATES
-- ========================================

CREATE TABLE IF NOT EXISTS `templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'NULL for global templates',
  `type` ENUM('text', 'image', 'video') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `prompt_template` TEXT NOT NULL,
  `category` VARCHAR(100) DEFAULT 'general',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_type` (`type`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TEAM COLLABORATION
-- ========================================

CREATE TABLE IF NOT EXISTS `content_comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `content_item_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `comment_text` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_content_item_id` (`content_item_id`),
  KEY `idx_user_id` (`user_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`content_item_id`) REFERENCES `content_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- ACTIVITY LOGS
-- ========================================

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NULL DEFAULT NULL,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `entity_type` VARCHAR(50) NOT NULL COMMENT 'textgen, imagegen, videogeneration, content, schedule, social_post',
  `entity_id` INT UNSIGNED NULL DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL COMMENT 'created, posted, scheduled, failed, etc',
  `description` TEXT,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` VARCHAR(500) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_entity_type` (`entity_type`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SEED DATA
-- ========================================

-- Insert Plans
INSERT INTO `plans` (`name`, `price_monthly`, `max_text_generations`, `max_image_generations`, `max_video_generations`, `max_scheduled_posts`, `social_accounts_limit`, `features_json`) VALUES
('Starter', 29.00, 100, 50, 10, 100, 3, '["brand_kits", "basic_analytics"]'),
('Professional', 79.00, 500, 250, 50, 500, 10, '["brand_kits", "bulk_generation", "team_collaboration", "advanced_analytics"]'),
('Agency', 199.00, 2000, 1000, 200, 2000, 50, '["brand_kits", "bulk_generation", "team_collaboration", "advanced_analytics", "white_label", "priority_support"]');

-- Insert Demo Tenant
INSERT INTO `tenants` (`company_name`, `subdomain`, `status`, `owner_email`, `settings_json`) VALUES
('Demo Marketing Agency', 'demo', 'active', 'demo@splashcreator.com', '{"timezone":"UTC"}');

-- Get tenant ID for demo
SET @demo_tenant_id = LAST_INSERT_ID();

-- Insert Tenant Subscription
INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `status`, `started_at`, `expires_at`) VALUES
(@demo_tenant_id, 2, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR));

-- Initialize Usage Tracking
INSERT INTO `tenant_usage` (`tenant_id`, `month`, `text_generations_count`, `image_generations_count`, `video_generations_count`, `scheduled_posts_count`) VALUES
(@demo_tenant_id, DATE_FORMAT(NOW(), '%Y-%m'), 15, 8, 2, 5);

-- Insert API Key for demo tenant
INSERT INTO `tenant_api_keys` (`tenant_id`, `api_key`, `name`, `is_active`) VALUES
(@demo_tenant_id, SHA2(CONCAT('demo-api-key-', UNIX_TIMESTAMP()), 256), 'Demo API Key', 1);

-- Insert Platform Admin
INSERT INTO `users` (`tenant_id`, `email`, `password_hash`, `full_name`, `role`, `is_active`) VALUES
(NULL, 'admin@splashcreator.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Platform Administrator', 'platform_admin', 1);
-- Password: password

-- Insert Demo Tenant Users
INSERT INTO `users` (`tenant_id`, `email`, `password_hash`, `full_name`, `role`, `is_active`) VALUES
(@demo_tenant_id, 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo Admin', 'tenant_admin', 1),
(@demo_tenant_id, 'creator@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Content Creator', 'content_creator', 1),
(@demo_tenant_id, 'social@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Social Manager', 'social_manager', 1);
-- All passwords: password

SET @demo_admin_id = (SELECT id FROM users WHERE email = 'admin@demo.com');
SET @demo_creator_id = (SELECT id FROM users WHERE email = 'creator@demo.com');

-- Insert Demo Brand Kit
INSERT INTO `brand_kits` (`tenant_id`, `name`, `colors_json`, `fonts_json`, `voice_tone`) VALUES
(@demo_tenant_id, 'Demo Brand', '["#FF6B6B", "#4ECDC4", "#45B7D1", "#FFA07A"]', '["Helvetica", "Arial"]', 'professional');

SET @demo_brand_kit_id = LAST_INSERT_ID();

-- Insert Social Accounts
INSERT INTO `social_accounts` (`tenant_id`, `platform`, `username`, `access_token`, `status`) VALUES
(@demo_tenant_id, 'instagram', '@demo_agency', 'simulated_instagram_token_12345', 'active'),
(@demo_tenant_id, 'facebook', 'Demo Agency Page', 'simulated_facebook_token_67890', 'active'),
(@demo_tenant_id, 'twitter', '@demo_agency', 'simulated_twitter_token_abcde', 'active'),
(@demo_tenant_id, 'linkedin', 'Demo Marketing Agency', 'simulated_linkedin_token_fghij', 'active');

SET @social_instagram_id = (SELECT id FROM social_accounts WHERE platform = 'instagram' AND tenant_id = @demo_tenant_id LIMIT 1);
SET @social_facebook_id = (SELECT id FROM social_accounts WHERE platform = 'facebook' AND tenant_id = @demo_tenant_id LIMIT 1);
SET @social_twitter_id = (SELECT id FROM social_accounts WHERE platform = 'twitter' AND tenant_id = @demo_tenant_id LIMIT 1);

-- Insert Sample AI Text Generations
INSERT INTO `ai_text_requests` (`tenant_id`, `user_id`, `prompt`, `result_text`, `model_used`, `tokens_count`) VALUES
(@demo_tenant_id, @demo_creator_id, 'Write an Instagram caption about morning coffee', '☕ Start your day right! There''s nothing quite like that first sip of perfectly brewed coffee. What''s your go-to morning brew? #MorningCoffee #CoffeeLovers #MondayMotivation', 'gpt-4', 87),
(@demo_tenant_id, @demo_creator_id, 'Create a tweet about productivity tips', '🚀 Productivity hack: Break your day into 90-minute focus blocks. Your brain works in natural cycles - work WITH them, not against them. Try it tomorrow! #ProductivityTips #WorkSmarter', 'gpt-4', 65);

-- Insert Sample AI Images
INSERT INTO `ai_images` (`tenant_id`, `user_id`, `prompt`, `image_path`, `style`, `width`, `height`) VALUES
(@demo_tenant_id, @demo_creator_id, 'Modern office workspace with laptop and coffee', 'storage/uploads/' || @demo_tenant_id || '/generated/image_001.jpg', 'realistic', 1024, 1024),
(@demo_tenant_id, @demo_creator_id, 'Abstract geometric pattern in brand colors', 'storage/uploads/' || @demo_tenant_id || '/generated/image_002.jpg', 'abstract', 1080, 1080);

-- Insert Sample AI Videos
INSERT INTO `ai_videos` (`tenant_id`, `user_id`, `script_text`, `video_path`, `duration_seconds`, `resolution`) VALUES
(@demo_tenant_id, @demo_creator_id, 'Welcome to our agency! We create amazing content that engages your audience.', 'storage/uploads/' || @demo_tenant_id || '/generated/video_001.mp4', 15, '1920x1080');

-- Insert Sample Content Items
INSERT INTO `content_items` (`tenant_id`, `user_id`, `type`, `title`, `content_text`, `media_path`, `tags_json`, `brand_kit_id`) VALUES
(@demo_tenant_id, @demo_creator_id, 'text', 'Monday Motivation Post', '🚀 Start your week strong! Success is the sum of small efforts repeated day in and day out. #MondayMotivation #Success', NULL, '["motivation", "monday", "inspiration"]', @demo_brand_kit_id),
(@demo_tenant_id, @demo_creator_id, 'image', 'Product Showcase', 'Check out our latest creation!', 'storage/uploads/' || @demo_tenant_id || '/generated/image_001.jpg', '["product", "showcase"]', @demo_brand_kit_id),
(@demo_tenant_id, @demo_creator_id, 'video', 'Agency Introduction', 'Welcome to Demo Marketing Agency', 'storage/uploads/' || @demo_tenant_id || '/generated/video_001.mp4', '["intro", "agency"]', @demo_brand_kit_id);

SET @content_item_1 = (SELECT id FROM content_items WHERE title = 'Monday Motivation Post' LIMIT 1);
SET @content_item_2 = (SELECT id FROM content_items WHERE title = 'Product Showcase' LIMIT 1);
SET @content_item_3 = (SELECT id FROM content_items WHERE title = 'Agency Introduction' LIMIT 1);

-- Insert Scheduled Posts (Past and Future)
INSERT INTO `scheduled_posts` (`tenant_id`, `content_item_id`, `social_account_id`, `platform`, `scheduled_time_utc`, `status`, `log_message`, `posted_at`) VALUES
(@demo_tenant_id, @content_item_1, @social_instagram_id, 'instagram', DATE_SUB(NOW(), INTERVAL 2 DAY), 'posted', 'Successfully posted to Instagram', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(@demo_tenant_id, @content_item_2, @social_facebook_id, 'facebook', DATE_ADD(NOW(), INTERVAL 1 DAY), 'scheduled', NULL, NULL),
(@demo_tenant_id, @content_item_3, @social_twitter_id, 'twitter', DATE_ADD(NOW(), INTERVAL 3 DAY), 'scheduled', NULL, NULL);

-- Insert Sample Analytics
INSERT INTO `social_metrics` (`tenant_id`, `platform`, `content_item_id`, `impressions`, `likes`, `comments`, `shares`, `clicks`) VALUES
(@demo_tenant_id, 'instagram', @content_item_1, 1542, 89, 12, 7, 34),
(@demo_tenant_id, 'facebook', @content_item_1, 2341, 134, 23, 18, 67),
(@demo_tenant_id, 'twitter', @content_item_1, 987, 45, 8, 12, 23);

-- Insert Global Templates
INSERT INTO `templates` (`tenant_id`, `type`, `title`, `prompt_template`, `category`) VALUES
(NULL, 'text', 'Instagram Caption - Product Launch', 'Write an engaging Instagram caption for a new product launch. Product: {product_name}. Key features: {features}. Target audience: {audience}. Tone: {tone}.', 'social_media'),
(NULL, 'text', 'Twitter Thread Starter', 'Create an attention-grabbing first tweet for a thread about: {topic}. Make it intriguing and include a hook.', 'social_media'),
(NULL, 'text', 'LinkedIn Post - Thought Leadership', 'Write a professional LinkedIn post about {topic}. Include personal insights and end with a question to drive engagement.', 'professional'),
(NULL, 'image', 'Social Media Quote Card', 'Create a visually appealing quote card with the text: "{quote}". Style: {style}. Colors: {colors}.', 'graphics'),
(NULL, 'video', 'Product Demo Script', 'Write a 30-second product demo script for {product_name}. Highlight: {key_benefits}. Call to action: {cta}.', 'video');

-- Insert Sample Comments
INSERT INTO `content_comments` (`tenant_id`, `content_item_id`, `user_id`, `comment_text`) VALUES
(@demo_tenant_id, @content_item_2, @demo_admin_id, 'Great work! Let''s schedule this for tomorrow morning.'),
(@demo_tenant_id, @content_item_3, @demo_admin_id, 'Can we adjust the color scheme to match our new brand guidelines?');

-- Insert Activity Logs
INSERT INTO `activity_logs` (`tenant_id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `ip_address`) VALUES
(@demo_tenant_id, @demo_creator_id, 'textgen', 1, 'created', 'Generated text content using AI', '127.0.0.1'),
(@demo_tenant_id, @demo_creator_id, 'imagegen', 1, 'created', 'Generated image using AI', '127.0.0.1'),
(@demo_tenant_id, @demo_creator_id, 'content', @content_item_1, 'created', 'Created new content item: Monday Motivation Post', '127.0.0.1'),
(@demo_tenant_id, @demo_admin_id, 'schedule', 1, 'scheduled', 'Scheduled post for Instagram', '127.0.0.1'),
(@demo_tenant_id, NULL, 'social_post', 1, 'posted', 'Successfully posted to Instagram', '127.0.0.1');

-- ========================================
-- END OF SEED DATA
-- ========================================
