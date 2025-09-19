-- -----------------------------------------------------
-- Prime EMS Studios - Database Schema
-- Bu dosya, uygulamanın ihtiyaç duyduğu tüm tabloları
-- ve temel başlangıç verilerini içerir.
-- -----------------------------------------------------

CREATE DATABASE IF NOT EXISTS `prime_ems_new`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `prime_ems_new`;

-- ------------------------------
-- Yönetici ve günlük tabloları
-- ------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(150) DEFAULT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'admin',
  `last_login` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_admin_username` (`username`),
  UNIQUE KEY `uniq_admin_email` (`email`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(100) DEFAULT NULL,
  `table_name` VARCHAR(100) DEFAULT NULL,
  `record_id` BIGINT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `details` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_module` (`module`),
  KEY `idx_activity_created_at` (`created_at`),
  CONSTRAINT `fk_activity_user`
    FOREIGN KEY (`user_id`) REFERENCES `admins` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------
-- Genel ayarlar ve içerik
-- ------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` LONGTEXT DEFAULT NULL,
  `setting_type` VARCHAR(50) NOT NULL DEFAULT 'text',
  `category` VARCHAR(50) NOT NULL DEFAULT 'general',
  `description` VARCHAR(255) DEFAULT NULL,
  `is_secure` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_setting_key` (`setting_key`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hero_section` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `button1_text` VARCHAR(100) DEFAULT NULL,
  `button1_link` VARCHAR(255) DEFAULT NULL,
  `button2_text` VARCHAR(100) DEFAULT NULL,
  `button2_link` VARCHAR(255) DEFAULT NULL,
  `background_image` VARCHAR(255) DEFAULT NULL,
  `overlay_opacity` DECIMAL(3,2) NOT NULL DEFAULT 0.70,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `about_section` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `mission` TEXT DEFAULT NULL,
  `vision` TEXT DEFAULT NULL,
  `values` JSON DEFAULT NULL,
  `features` JSON DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `icon` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(255) DEFAULT NULL,
  `meta_keywords` VARCHAR(255) DEFAULT NULL,
  `template` VARCHAR(50) NOT NULL DEFAULT 'default',
  `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  `published_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_pages_slug` (`slug`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------
-- Hizmetler, paketler ve ekip
-- ------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `short_description` TEXT DEFAULT NULL,
  `long_description` LONGTEXT DEFAULT NULL,
  `duration` VARCHAR(50) DEFAULT NULL,
  `goal` VARCHAR(150) DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT NULL,
  `session_count` INT DEFAULT NULL,
  `features` JSON DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_services_slug` (`slug`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `packages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `session_count` INT NOT NULL DEFAULT 0,
  `validity_days` INT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `discount_percentage` DECIMAL(5,2) DEFAULT 0,
  `features` JSON DEFAULT NULL,
  `is_trial` TINYINT(1) NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trainers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `title` VARCHAR(150) DEFAULT NULL,
  `bio` LONGTEXT DEFAULT NULL,
  `certifications` JSON DEFAULT NULL,
  `specializations` JSON DEFAULT NULL,
  `experience_years` INT DEFAULT 0,
  `social_instagram` VARCHAR(150) DEFAULT NULL,
  `social_linkedin` VARCHAR(150) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------
-- Cihaz tabloları
-- ------------------------------
CREATE TABLE IF NOT EXISTS `devices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `features` JSON DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_devices_slug` (`slug`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ems_devices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `device_type` VARCHAR(50) NOT NULL DEFAULT 'other',
  `model` VARCHAR(100) DEFAULT NULL,
  `manufacturer` VARCHAR(150) DEFAULT NULL,
  `short_description` TEXT DEFAULT NULL,
  `long_description` LONGTEXT DEFAULT NULL,
  `features` JSON DEFAULT NULL,
  `specifications` JSON DEFAULT NULL,
  `certifications` JSON DEFAULT NULL,
  `usage_areas` JSON DEFAULT NULL,
  `benefits` JSON DEFAULT NULL,
  `exercise_programs` JSON DEFAULT NULL,
  `technical_documents` TEXT DEFAULT NULL,
  `warranty_info` VARCHAR(255) DEFAULT NULL,
  `price_range` VARCHAR(100) DEFAULT NULL,
  `main_image` VARCHAR(255) DEFAULT NULL,
  `gallery_images` JSON DEFAULT NULL,
  `capacity` INT DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(255) DEFAULT NULL,
  `seo_description` VARCHAR(255) DEFAULT NULL,
  `seo_keywords` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_ems_devices_slug` (`slug`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------
-- Üyelik yönetimi
-- ------------------------------
CREATE TABLE IF NOT EXISTS `members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_code` VARCHAR(50) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(25) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `birth_date` DATE DEFAULT NULL,
  `gender` VARCHAR(25) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `emergency_contact` VARCHAR(150) DEFAULT NULL,
  `emergency_phone` VARCHAR(25) DEFAULT NULL,
  `health_conditions` TEXT DEFAULT NULL,
  `goals` TEXT DEFAULT NULL,
  `membership_type` VARCHAR(100) DEFAULT NULL,
  `membership_start` DATE DEFAULT NULL,
  `membership_end` DATE DEFAULT NULL,
  `total_sessions` INT NOT NULL DEFAULT 0,
  `remaining_sessions` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_member_code` (`member_code`),
  KEY `idx_members_phone` (`phone`),
  KEY `idx_members_email` (`email`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `member_payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` INT UNSIGNED NOT NULL,
  `package_id` INT UNSIGNED DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `currency` VARCHAR(5) NOT NULL DEFAULT 'TRY',
  `sessions_purchased` INT NOT NULL DEFAULT 0,
  `payment_date` DATE NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `reference_code` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_member_payments_member` (`member_id`),
  KEY `idx_member_payments_date` (`payment_date`),
  CONSTRAINT `fk_member_payments_member`
    FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_member_payments_package`
    FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `member_sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` INT UNSIGNED NOT NULL,
  `session_date` DATE NOT NULL,
  `session_type` VARCHAR(100) DEFAULT NULL,
  `duration_minutes` SMALLINT DEFAULT NULL,
  `intensity_level` VARCHAR(50) DEFAULT NULL,
  `trainer_name` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_member_sessions_member` (`member_id`),
  KEY `idx_member_sessions_date` (`session_date`),
  CONSTRAINT `fk_member_sessions_member`
    FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `member_metrics` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` INT UNSIGNED NOT NULL,
  `measurement_date` DATE NOT NULL,
  `weight_kg` DECIMAL(6,2) DEFAULT NULL,
  `body_fat_percentage` DECIMAL(5,2) DEFAULT NULL,
  `muscle_mass_kg` DECIMAL(6,2) DEFAULT NULL,
  `visceral_fat` DECIMAL(5,2) DEFAULT NULL,
  `water_percentage` DECIMAL(5,2) DEFAULT NULL,
  `waist_cm` DECIMAL(5,2) DEFAULT NULL,
  `hip_cm` DECIMAL(5,2) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_member_metrics_member` (`member_id`),
  KEY `idx_member_metrics_date` (`measurement_date`),
  CONSTRAINT `fk_member_metrics_member`
    FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------
-- Randevu ve iletişim
-- ------------------------------
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` INT UNSIGNED DEFAULT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(25) NOT NULL,
  `customer_email` VARCHAR(150) DEFAULT NULL,
  `service_id` INT UNSIGNED DEFAULT NULL,
  `device_id` INT UNSIGNED DEFAULT NULL,
  `trainer_id` INT UNSIGNED DEFAULT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` TIME NOT NULL,
  `duration_minutes` INT NOT NULL DEFAULT 20,
  `status` ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` TEXT DEFAULT NULL,
  `source` VARCHAR(50) NOT NULL DEFAULT 'website',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_appointments_date` (`appointment_date`),
  KEY `idx_appointments_status` (`status`),
  CONSTRAINT `fk_appointments_member`
    FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_appointments_service`
    FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_appointments_device`
    FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_appointments_trainer`
    FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(25) DEFAULT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------
-- İçerik yönetimi
-- ------------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` TEXT NOT NULL,
  `answer` LONGTEXT NOT NULL,
  `category` VARCHAR(100) DEFAULT 'Genel',
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_title` VARCHAR(150) DEFAULT NULL,
  `content` TEXT NOT NULL,
  `rating` DECIMAL(3,2) DEFAULT NULL,
  `service_used` VARCHAR(150) DEFAULT NULL,
  `result_achieved` VARCHAR(255) DEFAULT NULL,
  `video_url` VARCHAR(255) DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `discount_text` VARCHAR(100) DEFAULT NULL,
  `badge_text` VARCHAR(50) DEFAULT NULL,
  `badge_color` VARCHAR(20) DEFAULT '#FFD700',
  `icon` VARCHAR(100) DEFAULT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `button_text` VARCHAR(100) DEFAULT NULL,
  `button_link` VARCHAR(255) DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `alt_text` VARCHAR(150) DEFAULT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `media` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) DEFAULT NULL,
  `file_size` BIGINT DEFAULT NULL,
  `alt_text` VARCHAR(150) DEFAULT NULL,
  `uploaded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_type` (`file_type`),
  CONSTRAINT `fk_media_admin`
    FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------
-- Blog sistemi
-- ------------------------------
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `color` VARCHAR(20) DEFAULT '#0d6efd',
  `icon` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_blog_categories_slug` (`slug`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_blog_tags_slug` (`slug`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `excerpt` TEXT DEFAULT NULL,
  `content` LONGTEXT NOT NULL,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `author_id` INT UNSIGNED DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(255) DEFAULT NULL,
  `meta_keywords` VARCHAR(255) DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `reading_time` INT DEFAULT 5,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `ai_generated` TINYINT(1) NOT NULL DEFAULT 0,
  `ai_prompt` TEXT DEFAULT NULL,
  `view_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_blog_posts_slug` (`slug`),
  PRIMARY KEY (`id`),
  KEY `idx_blog_posts_published` (`is_published`,`published_at`),
  CONSTRAINT `fk_blog_posts_category`
    FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_blog_posts_author`
    FOREIGN KEY (`author_id`) REFERENCES `admins` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_post_tags` (
  `post_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `idx_blog_post_tags_tag` (`tag_id`),
  CONSTRAINT `fk_blog_post_tags_post`
    FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_blog_post_tags_tag`
    FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_comments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` INT UNSIGNED NOT NULL,
  `author_name` VARCHAR(150) NOT NULL,
  `author_email` VARCHAR(150) DEFAULT NULL,
  `comment` TEXT NOT NULL,
  `status` ENUM('pending','approved','spam') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_blog_comments_status` (`status`),
  CONSTRAINT `fk_blog_comments_post`
    FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_ai_suggestions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic` VARCHAR(255) NOT NULL,
  `outline` JSON DEFAULT NULL,
  `seo_score` DECIMAL(5,2) DEFAULT 0,
  `keywords` JSON DEFAULT NULL,
  `is_used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------
-- Chatbot tabloları
-- ------------------------------
CREATE TABLE IF NOT EXISTS `chatbot_config` (
  `config_key` VARCHAR(100) NOT NULL,
  `config_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chatbot_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone` VARCHAR(25) NOT NULL,
  `message` TEXT NOT NULL,
  `response` TEXT DEFAULT NULL,
  `status` ENUM('success','error') NOT NULL DEFAULT 'success',
  `metadata` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_chatbot_logs_phone` (`phone`),
  KEY `idx_chatbot_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chatbot_appointments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(25) NOT NULL,
  `service` VARCHAR(150) DEFAULT NULL,
  `preferred_time` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------
-- İstatistik tabloları
-- ------------------------------
CREATE TABLE IF NOT EXISTS `statistics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `metric_key` VARCHAR(100) NOT NULL,
  `metric_value` DOUBLE DEFAULT 0,
  `meta` JSON DEFAULT NULL,
  `recorded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_statistics_key` (`metric_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------
-- Başlangıç verileri
-- ------------------------------
INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `role`, `is_active`)
VALUES ('admin', 'admin@example.com', '$2y$12$Ge4Y1/kkSGljLAc838JVDO5zSoDD1o5KXtWmpUsZQq55Se1CQSuTu', 'Site Yöneticisi', 'superadmin', 1)
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`)
VALUES
  ('site_name', 'Prime EMS Studios İzmir', 'text', 'general', 'Site başlığı'),
  ('site_url', 'https://primeemsstudios.com', 'text', 'general', 'Kanonik site adresi'),
  ('contact_email', 'info@primeems.com', 'text', 'contact', 'İletişim e-posta adresi'),
  ('contact_phone', '+90 232 555 66 77', 'text', 'contact', 'Telefon numarası'),
  ('contact_address', 'Balçova Marina AVM, Kat:2, No:205, Balçova / İzmir', 'textarea', 'contact', 'Fiziksel adres'),
  ('social_instagram', 'https://instagram.com/primeemsstudios', 'text', 'social', 'Instagram adresi'),
  ('primary_color', '#FFD700', 'text', 'design', 'Tema ana rengi')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

INSERT INTO `hero_section` (`id`, `title`, `subtitle`, `description`, `button1_text`, `button1_link`, `button2_text`, `button2_link`, `overlay_opacity`, `is_active`)
VALUES
  (1, 'Prime EMS Studios İzmir', '20 Dakikada Maksimum Sonuç', 'Bilimsel EMS teknolojisi ile haftada 2 seans yeter!', 'Ücretsiz Keşif Seansı Alın', 'https://wa.me/905555556677', 'Programlarımız', '/services.php', 0.70, 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `about_section` (`id`, `title`, `subtitle`, `content`, `mission`, `vision`, `values`, `features`, `image`, `is_active`)
VALUES
  (1,
   'Prime EMS Studios Hakkında',
   'Alman Teknolojisi ile Premium EMS Deneyimi',
   'Prime EMS Studios, İzmir Balçova''da EMS teknolojisini en üst seviyede sunar.',
   'Üyelerimizin hedeflerine güvenli ve bilimsel yöntemlerle ulaşmasını sağlamak.',
   'Türkiye''de EMS alanında referans stüdyo olmak.',
   JSON_ARRAY('Bilimsel Yaklaşım','Sürekli Gelişim','Kişiye Özel Programlar'),
   JSON_ARRAY('i-motion® kablosuz teknolojisi','Uzman eğitmen ekibi','3D vücut analizleri'),
   '/assets/images/about/ems-studio.jpg',
   1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `services` (`name`, `slug`, `short_description`, `long_description`, `duration`, `goal`, `icon`, `price`, `session_count`, `features`, `is_featured`, `is_active`, `sort_order`)
VALUES
  ('EMS Fit Programı', 'ems-fit-programi', '20 dakikalık tam vücut EMS seansı', 'Yağ yakımı ve kas aktivasyonu odaklı EMS protokolü.', '20 dk', 'Yağ Yakımı', 'bi-activity', 950.00, 12, JSON_ARRAY('Tam vücut kas aktivasyonu','Haftada 2 seans plan'), 1, 1, 1)
ON DUPLICATE KEY UPDATE `short_description` = VALUES(`short_description`);

INSERT INTO `packages` (`name`, `description`, `session_count`, `validity_days`, `price`, `discount_percentage`, `features`, `is_trial`, `is_featured`, `is_active`, `sort_order`)
VALUES
  ('Başlangıç Paketi', 'Yeni başlayanlar için 8 seans EMS programı', 8, 30, 5990.00, 10.00, JSON_ARRAY('Vücut analizi','Beslenme rehberi'), 0, 1, 1, 1)
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

INSERT INTO `blog_categories` (`name`, `slug`, `description`, `color`, `icon`, `sort_order`, `is_active`)
VALUES
  ('EMS Teknolojisi', 'ems-teknolojisi', 'Elektrik kas stimülasyonu üzerine içerikler', '#0d6efd', 'bi-lightning-charge', 1, 1)
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

