-- UGHI dummy MySQL database export generated from database/database.sqlite
-- Import into an empty or disposable MySQL database.
CREATE DATABASE IF NOT EXISTS `ughi_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ughi_db`;
SET NAMES utf8mb4;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `performance_targets`;
DROP TABLE IF EXISTS `performance_indicators`;
DROP TABLE IF EXISTS `results_framework_items`;
DROP TABLE IF EXISTS `user_invitation_departments`;
DROP TABLE IF EXISTS `user_invitations`;
DROP TABLE IF EXISTS `performance_rating_scales`;
DROP TABLE IF EXISTS `performance_results`;
DROP TABLE IF EXISTS `reporting_periods`;
DROP TABLE IF EXISTS `project_risks`;
DROP TABLE IF EXISTS `project_milestones`;
DROP TABLE IF EXISTS `project_activities`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `finance_audit_logs`;
DROP TABLE IF EXISTS `cash_accounts`;
DROP TABLE IF EXISTS `bank_accounts`;
DROP TABLE IF EXISTS `accounts`;
DROP TABLE IF EXISTS `financial_years`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `department_activity_logs`;
DROP TABLE IF EXISTS `approval_actions`;
DROP TABLE IF EXISTS `approval_requests`;
DROP TABLE IF EXISTS `approval_workflow_steps`;
DROP TABLE IF EXISTS `approval_workflows`;
DROP TABLE IF EXISTS `department_task_attachments`;
DROP TABLE IF EXISTS `department_task_comments`;
DROP TABLE IF EXISTS `department_tasks`;
DROP TABLE IF EXISTS `user_departments`;
DROP TABLE IF EXISTS `department_leadership`;
DROP TABLE IF EXISTS `donations`;
DROP TABLE IF EXISTS `involvement_applications`;
DROP TABLE IF EXISTS `editorial_items`;
DROP TABLE IF EXISTS `media_assets`;
DROP TABLE IF EXISTS `partners`;
DROP TABLE IF EXISTS `content_pages`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `department_project`;
DROP TABLE IF EXISTS `department_programme`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `team_members`;
DROP TABLE IF EXISTS `positions`;
DROP TABLE IF EXISTS `impact_statistics`;
DROP TABLE IF EXISTS `project_images`;
DROP TABLE IF EXISTS `districts`;
DROP TABLE IF EXISTS `regions`;
DROP TABLE IF EXISTS `countries`;
DROP TABLE IF EXISTS `programmes`;
DROP TABLE IF EXISTS `site_settings`;
DROP TABLE IF EXISTS `role_has_permissions`;
DROP TABLE IF EXISTS `model_has_roles`;
DROP TABLE IF EXISTS `model_has_permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `migrations`;

CREATE TABLE IF NOT EXISTS `migrations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `migration` varchar(255) not null,
    `batch` BIGINT UNSIGNED not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email` varchar(255) not null,
    `token` varchar(255) not null,
    `created_at` datetime,
    primary key (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` varchar(255) not null,
    `user_id` BIGINT UNSIGNED,
    `ip_address` varchar(255),
    `user_agent` text,
    `payload` text not null,
    `last_activity` BIGINT UNSIGNED not null,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `cache` (
    `key` varchar(255) not null,
    `value` text not null,
    `expiration` BIGINT UNSIGNED not null,
    primary key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `cache_locks` (
    `key` varchar(255) not null,
    `owner` varchar(255) not null,
    `expiration` BIGINT UNSIGNED not null,
    primary key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `queue` varchar(255) not null,
    `payload` text not null,
    `attempts` BIGINT UNSIGNED not null,
    `reserved_at` BIGINT UNSIGNED,
    `available_at` BIGINT UNSIGNED not null,
    `created_at` BIGINT UNSIGNED not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `job_batches` (
    `id` varchar(255) not null,
    `name` varchar(255) not null,
    `total_jobs` BIGINT UNSIGNED not null,
    `pending_jobs` BIGINT UNSIGNED not null,
    `failed_jobs` BIGINT UNSIGNED not null,
    `failed_job_ids` text not null,
    `options` text,
    `cancelled_at` BIGINT UNSIGNED,
    `created_at` BIGINT UNSIGNED not null,
    `finished_at` BIGINT UNSIGNED,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` varchar(255) not null,
    `connection` text not null,
    `queue` text not null,
    `payload` text not null,
    `exception` text not null,
    `failed_at` datetime not null default CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) not null,
    `guard_name` varchar(255) not null,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `roles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) not null,
    `guard_name` varchar(255) not null,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
    `permission_id` BIGINT UNSIGNED not null,
    `model_type` varchar(255) not null,
    `model_id` BIGINT UNSIGNED not null,
    primary key (`permission_id`, `model_id`, `model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
    `role_id` BIGINT UNSIGNED not null,
    `model_type` varchar(255) not null,
    `model_id` BIGINT UNSIGNED not null,
    primary key (`role_id`, `model_id`, `model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
    `permission_id` BIGINT UNSIGNED not null,
    `role_id` BIGINT UNSIGNED not null,
    primary key (`permission_id`, `role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `key` varchar(255) not null,
    `data` text not null,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `programmes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `slug` varchar(255) not null,
    `title` text not null,
    `short_description` text not null,
    `description` text,
    `image` varchar(255),
    `image_alt` varchar(255),
    `icon` varchar(255) not null default 'heart',
    `display_order` BIGINT UNSIGNED not null default '0',
    `is_active` tinyint(1) not null default '1',
    `meta_title` varchar(255),
    `meta_description` text,
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `countries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) not null,
    `code` varchar(255) not null,
    `slug` varchar(255) not null,
    `is_active` tinyint(1) not null default '1',
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `regions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `country_id` BIGINT UNSIGNED not null,
    `name` varchar(255) not null,
    `slug` varchar(255) not null,
    `description` text,
    `display_order` BIGINT UNSIGNED not null default '0',
    `is_active` tinyint(1) not null default '1',
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `districts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `region_id` BIGINT UNSIGNED not null,
    `name` varchar(255) not null,
    `slug` varchar(255) not null,
    `display_order` BIGINT UNSIGNED not null default '0',
    `is_active` tinyint(1) not null default '1',
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `project_images` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED not null,
    `image` varchar(255) not null,
    `alt_text` varchar(255) not null,
    `caption` varchar(255),
    `display_order` BIGINT UNSIGNED not null default '0',
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `impact_statistics` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `programme_id` BIGINT UNSIGNED,
    `project_id` BIGINT UNSIGNED,
    `metric` varchar(255) not null,
    `label` text not null,
    `value` BIGINT UNSIGNED not null default '0',
    `suffix` varchar(255) not null default '',
    `icon` varchar(255) not null default 'users',
    `as_of_date` date,
    `verification_source` text,
    `verified_at` datetime,
    `verified_by` BIGINT UNSIGNED,
    `display_order` BIGINT UNSIGNED not null default '0',
    `is_active` tinyint(1) not null default '0',
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `positions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `department_id` BIGINT UNSIGNED not null,
    `reports_to_position_id` BIGINT UNSIGNED,
    `title` varchar(255) not null,
    `code` varchar(255),
    `description` text,
    `level` varchar(255),
    `display_order` BIGINT UNSIGNED not null default '0',
    `active` tinyint(1) not null default '1',
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `team_members` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `department_id` BIGINT UNSIGNED,
    `position_id` BIGINT UNSIGNED,
    `user_id` BIGINT UNSIGNED,
    `name` varchar(255) not null,
    `slug` varchar(255) not null,
    `email` varchar(255),
    `phone` varchar(255),
    `short_bio` text,
    `bio` text,
    `photo` varchar(255),
    `photo_alt` varchar(255),
    `employment_type` varchar(255),
    `display_order` BIGINT UNSIGNED not null default '0',
    `show_on_website` tinyint(1) not null default '1',
    `active` tinyint(1) not null default '1',
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime,
    `is_leader` tinyint(1) not null default '0',
    `leadership_role` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `departments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `parent_id` BIGINT UNSIGNED,
    `head_staff_id` BIGINT UNSIGNED,
    `code` varchar(255) not null,
    `name` varchar(255) not null,
    `slug` varchar(255) not null,
    `short_description` varchar(255),
    `description` text,
    `email` varchar(255),
    `phone` varchar(255),
    `office_location` varchar(255),
    `icon` varchar(255),
    `featured_image` varchar(255),
    `display_order` BIGINT UNSIGNED not null DEFAULT '0',
    `show_on_website` tinyint(1) not null DEFAULT '1',
    `accepts_public_messages` tinyint(1) not null DEFAULT '1',
    `active` tinyint(1) not null DEFAULT '1',
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `department_programme` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `department_id` BIGINT UNSIGNED not null,
    `programme_id` BIGINT UNSIGNED not null,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `department_project` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `department_id` BIGINT UNSIGNED not null,
    `project_id` BIGINT UNSIGNED not null,
    `is_lead` tinyint(1) not null default '0',
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `department_id` BIGINT UNSIGNED,
    `assigned_to_user_id` BIGINT UNSIGNED,
    `name` varchar(255) not null,
    `email` varchar(255) not null,
    `phone` varchar(255),
    `subject` varchar(255),
    `message` text not null,
    `status` varchar(255) not null default 'NEW',
    `resolved_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `content_pages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `path` varchar(255) not null,
    `eyebrow` varchar(255),
    `title` text not null,
    `description` text,
    `body` text,
    `hero_image` varchar(255),
    `hero_image_alt` varchar(255),
    `meta_title` varchar(255),
    `meta_description` text,
    `og_image` varchar(255),
    `is_published` tinyint(1) not null default '1',
    `display_order` BIGINT UNSIGNED not null default '0',
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `partners` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) not null,
    `slug` varchar(255) not null,
    `partner_type` varchar(255),
    `short_description` text,
    `description` text,
    `website` varchar(255),
    `email` varchar(255),
    `logo` varchar(255),
    `logo_alt` varchar(255),
    `featured_image` varchar(255),
    `image_alt` varchar(255),
    `is_featured` tinyint(1) not null default '0',
    `show_on_website` tinyint(1) not null default '1',
    `active` tinyint(1) not null default '1',
    `display_order` BIGINT UNSIGNED not null default '0',
    `meta_title` varchar(255),
    `meta_description` text,
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `media_assets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uploaded_by` BIGINT UNSIGNED,
    `name` varchar(255) not null,
    `path` varchar(255) not null,
    `alt_text` varchar(255),
    `caption` varchar(255),
    `collection` varchar(255) not null default 'general',
    `is_public` tinyint(1) not null default '1',
    `created_at` datetime,
    `updated_at` datetime,
    `deleted_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `editorial_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type` varchar(255) not null,
    `title` varchar(255) not null,
    `slug` varchar(255) not null,
    `summary` text,
    `body` text,
    `image` varchar(255),
    `image_alt` varchar(255),
    `document` varchar(255),
    `document_label` varchar(255),
    `external_url` varchar(255),
    `event_starts_at` datetime,
    `event_ends_at` datetime,
    `event_location` varchar(255),
    `is_published` tinyint(1) not null default '0',
    `published_at` datetime,
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `involvement_applications` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type` varchar(255) not null,
    `name` varchar(255) not null,
    `email` varchar(255) not null,
    `phone` varchar(255),
    `organization` varchar(255),
    `interest_area` varchar(255),
    `availability` varchar(255),
    `message` text,
    `status` varchar(255) not null default 'NEW',
    `assigned_to_user_id` BIGINT UNSIGNED,
    `internal_notes` text,
    `submitted_at` datetime not null,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `donations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `donor_name` varchar(255) not null,
    `email` varchar(255) not null,
    `phone` varchar(255),
    `amount` numeric not null,
    `currency` varchar(255) not null DEFAULT 'TZS',
    `frequency` varchar(255) not null DEFAULT 'one_time',
    `purpose` varchar(255) not null DEFAULT 'where_needed',
    `payment_method` varchar(255) not null DEFAULT 'contact_me',
    `payment_reference` varchar(255),
    `status` varchar(255) not null DEFAULT 'PENDING',
    `received_at` datetime,
    `internal_notes` text,
    `created_at` datetime,
    `updated_at` datetime,
    `programme_id` BIGINT UNSIGNED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `department_leadership` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `department_id` BIGINT UNSIGNED not null,
    `team_member_id` BIGINT UNSIGNED not null,
    `leadership_role` varchar(255) not null,
    `is_primary` tinyint(1) not null default '0',
    `start_date` date,
    `end_date` date,
    `appointment_status` varchar(255) not null default 'ACTIVE',
    `can_approve` tinyint(1) not null default '0',
    `can_assign_tasks` tinyint(1) not null default '0',
    `can_manage_staff` tinyint(1) not null default '0',
    `can_manage_projects` tinyint(1) not null default '0',
    `can_view_reports` tinyint(1) not null default '1',
    `active` tinyint(1) not null default '1',
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `user_departments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED not null,
    `department_id` BIGINT UNSIGNED not null,
    `is_primary` tinyint(1) not null default '0',
    `department_role` varchar(255),
    `active` tinyint(1) not null default '1',
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `department_tasks` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `department_id` BIGINT UNSIGNED not null,
    `title` varchar(255) not null,
    `description` text,
    `assigned_by_user_id` BIGINT UNSIGNED not null,
    `assigned_to_team_member_id` BIGINT UNSIGNED,
    `assigned_to_user_id` BIGINT UNSIGNED,
    `priority` varchar(255) not null default 'MEDIUM',
    `status` varchar(255) not null default 'PENDING',
    `start_date` date,
    `due_date` date,
    `completed_at` datetime,
    `completion_note` text,
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `department_task_comments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `task_id` BIGINT UNSIGNED not null,
    `user_id` BIGINT UNSIGNED not null,
    `comment` text not null,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `department_task_attachments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `task_id` BIGINT UNSIGNED not null,
    `uploaded_by_user_id` BIGINT UNSIGNED not null,
    `disk` varchar(255) not null default 'local',
    `path` varchar(255) not null,
    `original_name` varchar(255) not null,
    `mime_type` varchar(255) not null,
    `size` BIGINT UNSIGNED not null,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `approval_workflows` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) not null,
    `code` varchar(255) not null,
    `module` varchar(255) not null,
    `description` text,
    `active` tinyint(1) not null default '1',
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `approval_workflow_steps` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` BIGINT UNSIGNED not null,
    `step_order` BIGINT UNSIGNED not null,
    `name` varchar(255) not null,
    `approver_type` varchar(255) not null,
    `required_permission` varchar(255),
    `required_leadership_role` varchar(255),
    `specific_user_id` BIGINT UNSIGNED,
    `department_scoped` tinyint(1) not null default '1',
    `can_return` tinyint(1) not null default '1',
    `can_reject` tinyint(1) not null default '1',
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `approval_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` BIGINT UNSIGNED not null,
    `approvable_type` varchar(255) not null,
    `approvable_id` BIGINT UNSIGNED not null,
    `department_id` BIGINT UNSIGNED,
    `requested_by_user_id` BIGINT UNSIGNED not null,
    `current_step` BIGINT UNSIGNED not null default '1',
    `status` varchar(255) not null default 'DRAFT',
    `submitted_at` datetime,
    `completed_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `approval_actions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `approval_request_id` BIGINT UNSIGNED not null,
    `workflow_step_id` BIGINT UNSIGNED,
    `user_id` BIGINT UNSIGNED not null,
    `action` varchar(255) not null,
    `comment` text,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `department_activity_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `department_id` BIGINT UNSIGNED,
    `user_id` BIGINT UNSIGNED,
    `event` varchar(255) not null,
    `subject_type` varchar(255),
    `subject_id` BIGINT UNSIGNED,
    `properties` text,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` varchar(255) not null,
    `type` varchar(255) not null,
    `notifiable_type` varchar(255) not null,
    `notifiable_id` BIGINT UNSIGNED not null,
    `data` text not null,
    `read_at` datetime,
    `created_at` datetime,
    `updated_at` datetime,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `financial_years` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) not null,
    `start_date` date not null,
    `end_date` date not null,
    `status` varchar(255) not null default 'OPEN',
    `closed_at` datetime,
    `closed_by_user_id` BIGINT UNSIGNED,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `accounts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `parent_id` BIGINT UNSIGNED,
    `code` varchar(255) not null,
    `name` varchar(255) not null,
    `type` varchar(255) not null,
    `normal_balance` varchar(255) not null,
    `description` text,
    `allow_transactions` tinyint(1) not null default '1',
    `active` tinyint(1) not null default '1',
    `display_order` BIGINT UNSIGNED not null default '0',
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `bank_accounts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `account_id` BIGINT UNSIGNED not null,
    `bank_name` varchar(255) not null,
    `account_name` varchar(255) not null,
    `masked_account_number` varchar(255),
    `currency` varchar(255) not null default 'TZS',
    `account_type` varchar(255),
    `opening_balance` numeric not null default '0',
    `active` tinyint(1) not null default '1',
    `notes` text,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `cash_accounts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `account_id` BIGINT UNSIGNED not null,
    `name` varchar(255) not null,
    `department_id` BIGINT UNSIGNED,
    `currency` varchar(255) not null default 'TZS',
    `opening_balance` numeric not null default '0',
    `active` tinyint(1) not null default '1',
    `custodian_team_member_id` BIGINT UNSIGNED,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `finance_audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED,
    `event` varchar(255) not null,
    `subject_type` varchar(255),
    `subject_id` BIGINT UNSIGNED,
    `properties` text,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) not null,
    `email` varchar(255) not null,
    `email_verified_at` datetime,
    `password` varchar(255) not null,
    `remember_token` varchar(255),
    `created_at` datetime,
    `updated_at` datetime,
    `is_active` tinyint(1) not null DEFAULT '1',
    `must_change_password` tinyint(1) not null DEFAULT '1',
    `last_login_at` datetime,
    `first_name` varchar(255),
    `middle_name` varchar(255),
    `last_name` varchar(255),
    `username` varchar(255),
    `phone` varchar(255),
    `profile_photo` varchar(255),
    `team_member_id` BIGINT UNSIGNED,
    `status` varchar(255) not null default 'ACTIVE',
    `phone_verified_at` datetime,
    `last_login_ip` varchar(255),
    `failed_login_attempts` BIGINT UNSIGNED not null default '0',
    `locked_until` datetime,
    `password_changed_at` datetime,
    `locale` varchar(255) not null default 'en',
    `timezone` varchar(255) not null default 'Africa/Dar_es_Salaam',
    `notification_preferences` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `projects` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `programme_id` BIGINT UNSIGNED not null,
    `region_id` BIGINT UNSIGNED,
    `district_id` BIGINT UNSIGNED,
    `title` text not null,
    `slug` varchar(255) not null,
    `short_description` text not null,
    `description` text not null,
    `challenge` text,
    `objectives` text,
    `activities` text,
    `target_groups` text,
    `featured_image` varchar(255),
    `image_alt` varchar(255),
    `start_date` date,
    `end_date` date,
    `status` varchar(255) not null DEFAULT 'PLANNED',
    `location` varchar(255),
    `funding_partner` varchar(255),
    `project_budget` numeric,
    `people_reached` BIGINT UNSIGNED,
    `is_featured` tinyint(1) not null DEFAULT '0',
    `is_published` tinyint(1) not null DEFAULT '0',
    `published_at` datetime,
    `meta_title` varchar(255),
    `meta_description` text,
    `og_image` varchar(255),
    `deleted_at` datetime,
    `created_at` datetime,
    `updated_at` datetime,
    `code` varchar(255),
    `project_manager_team_member_id` BIGINT UNSIGNED,
    `grant_id` BIGINT UNSIGNED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `project_activities` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED not null,
    `department_id` BIGINT UNSIGNED,
    `title` varchar(255) not null,
    `description` text,
    `activity_type` varchar(255),
    `start_date` date,
    `end_date` date,
    `status` varchar(255) not null default 'PLANNED',
    `responsible_team_member_id` BIGINT UNSIGNED,
    `budget_line_id` BIGINT UNSIGNED,
    `location` varchar(255),
    `target_participants` BIGINT UNSIGNED,
    `actual_participants` BIGINT UNSIGNED,
    `completion_percentage` BIGINT UNSIGNED not null default '0',
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `project_milestones` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED not null,
    `title` varchar(255) not null,
    `description` text,
    `due_date` date,
    `completed_at` datetime,
    `status` varchar(255) not null default 'UPCOMING',
    `responsible_user_id` BIGINT UNSIGNED,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `project_risks` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED not null,
    `title` varchar(255) not null,
    `description` text,
    `likelihood` BIGINT UNSIGNED not null default '1',
    `impact` BIGINT UNSIGNED not null default '1',
    `risk_level` varchar(255) not null default 'LOW',
    `mitigation` text,
    `owner_user_id` BIGINT UNSIGNED,
    `status` varchar(255) not null default 'OPEN',
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `reporting_periods` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) not null,
    `period_type` varchar(255) not null,
    `financial_year_id` BIGINT UNSIGNED,
    `start_date` date not null,
    `end_date` date not null,
    `submission_deadline` date,
    `status` varchar(255) not null default 'UPCOMING',
    `created_at` datetime,
    `updated_at` datetime,
    `data_entry_start` date,
    `data_entry_deadline` date,
    `verification_deadline` date
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `performance_results` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `performance_indicator_id` BIGINT UNSIGNED not null,
    `performance_target_id` BIGINT UNSIGNED,
    `reporting_period_id` BIGINT UNSIGNED not null,
    `department_id` BIGINT UNSIGNED,
    `programme_id` BIGINT UNSIGNED,
    `project_id` BIGINT UNSIGNED,
    `team_member_id` BIGINT UNSIGNED,
    `actual_value` numeric not null,
    `achievement_percentage` numeric,
    `score` numeric,
    `status` varchar(255) not null default 'DRAFT',
    `source_type` varchar(255) not null default 'MANUAL',
    `source_id` BIGINT UNSIGNED,
    `entered_by_user_id` BIGINT UNSIGNED,
    `verified_by_user_id` BIGINT UNSIGNED,
    `verified_at` datetime,
    `comments` text,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `performance_rating_scales` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) not null default 'UGHI Performance Rating',
    `minimum_score` numeric not null,
    `maximum_score` numeric not null,
    `label` varchar(255) not null,
    `description` text,
    `display_order` BIGINT UNSIGNED not null default '0',
    `active` tinyint(1) not null default '1',
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `user_invitations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `first_name` varchar(255) not null,
    `last_name` varchar(255) not null,
    `email` varchar(255) not null,
    `team_member_id` BIGINT UNSIGNED,
    `invited_by_user_id` BIGINT UNSIGNED not null,
    `role_id` BIGINT UNSIGNED,
    `primary_department_id` BIGINT UNSIGNED,
    `token_hash` varchar(255) not null,
    `status` varchar(255) not null default 'PENDING',
    `expires_at` datetime not null,
    `accepted_at` datetime,
    `revoked_at` datetime,
    `created_at` datetime,
    `updated_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `user_invitation_departments` (
    `user_invitation_id` BIGINT UNSIGNED not null,
    `department_id` BIGINT UNSIGNED not null,
    primary key (`user_invitation_id`, `department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `results_framework_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `programme_id` BIGINT UNSIGNED,
    `project_id` BIGINT UNSIGNED,
    `parent_id` BIGINT UNSIGNED,
    `level` varchar(255) not null,
    `code` varchar(255),
    `title` varchar(255) not null,
    `description` text,
    `start_date` date,
    `end_date` date,
    `display_order` BIGINT UNSIGNED not null default '0',
    `active` tinyint(1) not null default '1',
    `created_at` datetime,
    `updated_at` datetime,
    `deleted_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `performance_indicators` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code` varchar(255) not null,
    `name` varchar(255) not null,
    `description` text,
    `level` varchar(255) not null,
    `department_id` BIGINT UNSIGNED,
    `programme_id` BIGINT UNSIGNED,
    `project_id` BIGINT UNSIGNED,
    `indicator_id` BIGINT UNSIGNED,
    `measurement_type` varchar(255) not null DEFAULT 'NUMBER',
    `unit` varchar(255),
    `direction` varchar(255) not null DEFAULT 'HIGHER_IS_BETTER',
    `weight` numeric not null DEFAULT '0',
    `target_value` numeric,
    `minimum_value` numeric,
    `maximum_value` numeric,
    `reporting_frequency` varchar(255) not null DEFAULT 'QUARTERLY',
    `data_source` varchar(255) not null DEFAULT 'MANUAL',
    `active` tinyint(1) not null DEFAULT '1',
    `created_at` datetime,
    `updated_at` datetime,
    `deleted_at` datetime,
    `short_name` varchar(255),
    `results_framework_item_id` BIGINT UNSIGNED,
    `indicator_type` varchar(255),
    `calculation_type` varchar(255),
    `calculation_description` text,
    `numerator_definition` text,
    `denominator_definition` text,
    `collection_method` varchar(255),
    `responsible_user_id` BIGINT UNSIGNED,
    `verification_method` text,
    `baseline_value` numeric,
    `baseline_date` date,
    `definition_notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `performance_targets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `performance_indicator_id` BIGINT UNSIGNED not null,
    `reporting_period_id` BIGINT UNSIGNED not null,
    `department_id` BIGINT UNSIGNED,
    `programme_id` BIGINT UNSIGNED,
    `project_id` BIGINT UNSIGNED,
    `team_member_id` BIGINT UNSIGNED,
    `target_value` numeric not null,
    `weight_override` numeric,
    `notes` text,
    `created_at` datetime,
    `updated_at` datetime,
    `district_id` BIGINT UNSIGNED,
    `created_by_user_id` BIGINT UNSIGNED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data
INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_10_17_000000_create_permission_tables',1);
INSERT INTO migrations VALUES(5,'2026_10_17_000001_create_phase_one_tables',1);
INSERT INTO migrations VALUES(6,'2026_10_17_000002_create_projects_impact_and_locations',1);
INSERT INTO migrations VALUES(7,'2026_10_18_000003_create_departments_positions_and_team',1);
INSERT INTO migrations VALUES(8,'2026_10_18_000004_create_content_partners_media',1);
INSERT INTO migrations VALUES(9,'2026_10_19_000001_add_leadership_fields_to_team_members',1);
INSERT INTO migrations VALUES(10,'2026_10_19_000002_create_donations_table',1);
INSERT INTO migrations VALUES(11,'2026_10_19_000003_create_editorial_items_table',1);
INSERT INTO migrations VALUES(12,'2026_10_19_000004_add_last_login_to_users_table',1);
INSERT INTO migrations VALUES(13,'2026_10_19_000005_create_involvement_applications_table',1);
INSERT INTO migrations VALUES(14,'2026_10_19_000006_grant_new_cms_module_permissions',1);
INSERT INTO migrations VALUES(15,'2026_10_19_000007_add_programme_to_donations_table',1);
INSERT INTO migrations VALUES(16,'2026_10_19_000008_create_department_management_tables',1);
INSERT INTO migrations VALUES(17,'2026_10_19_000009_add_department_workflow_permissions',1);
INSERT INTO migrations VALUES(18,'2026_10_19_000010_create_notifications_table',1);
INSERT INTO migrations VALUES(19,'2026_10_19_000011_create_finance_foundation_tables',1);
INSERT INTO migrations VALUES(20,'2026_10_19_000012_add_finance_foundation_permissions',1);
INSERT INTO migrations VALUES(21,'2026_10_19_000012_extend_user_account_details',1);
INSERT INTO migrations VALUES(22,'2026_10_20_000001_add_project_operations',1);
INSERT INTO migrations VALUES(23,'2026_10_20_000002_create_reporting_performance_foundation',1);
INSERT INTO migrations VALUES(24,'2026_10_20_000003_add_reporting_performance_permissions',1);
INSERT INTO migrations VALUES(25,'2026_10_20_000004_keep_staff_appraisal_access_separate',1);
INSERT INTO migrations VALUES(26,'2026_10_20_000005_create_user_invitations_table',1);
INSERT INTO migrations VALUES(27,'2026_10_20_000006_add_user_invitation_permissions',1);
INSERT INTO migrations VALUES(28,'2026_10_20_000007_extend_performance_foundation_for_mel_m1',1);
INSERT INTO migrations VALUES(29,'2026_10_20_000008_add_mel_m1_permissions',1);
INSERT INTO migrations VALUES(30,'2026_10_20_000009_restrict_administrator_mel_writes',1);
INSERT INTO permissions VALUES(1,'organization.full_access','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(2,'department.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(3,'department.manage_leadership','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(4,'department.assign_task','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(5,'department.view_reports','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(6,'department.manage_projects','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(7,'department.manage_programmes','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(8,'department.approve','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(9,'department.view_budget','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(10,'department.manage_documents','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(11,'department.manage_messages','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(12,'department.view_activity','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(13,'admin.access','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(14,'finance.dashboard.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(15,'finance.year.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(16,'finance.year.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(17,'finance.account.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(18,'finance.account.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(19,'finance.bank.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(20,'finance.bank.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(21,'finance.cash.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(22,'finance.cash.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(23,'report.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(24,'report.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(25,'report.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(26,'report.submit','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(27,'report.review','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(28,'report.approve','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(29,'report.return','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(30,'report.reject','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(31,'report.export','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(32,'report.manage_types','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(33,'performance.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(34,'performance.view_organization','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(35,'performance.view_department','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(36,'performance.view_programme','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(37,'performance.view_project','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(38,'performance.manage_kpi','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(39,'performance.manage_target','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(40,'performance.enter_result','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(41,'performance.verify_result','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(42,'performance.manage_scale','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(43,'performance.export','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(44,'staff_performance.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(45,'staff_performance.review','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(46,'staff_performance.approve','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(47,'user.invite','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(48,'user.invitation.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(49,'user.invitation.resend','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(50,'user.invitation.revoke','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(51,'user.password_reset.send','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(52,'mel.dashboard.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(53,'mel.results_framework.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(54,'mel.results_framework.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(55,'mel.indicator.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(56,'mel.indicator.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(57,'mel.indicator.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(58,'mel.indicator.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(59,'mel.target.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(60,'mel.target.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(61,'homepage.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(62,'settings.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(63,'user.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(64,'user.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(65,'user.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(66,'user.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(67,'user.disable','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(68,'user.activate','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(69,'user.assign_role','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(70,'user.assign_department','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(71,'user.reset_password','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(72,'user.view_activity','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(73,'user.manage_security','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(74,'user.sessions.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(75,'user.sessions.revoke','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(76,'role.manage','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(77,'member.approve','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(78,'safeguarding.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(79,'safeguarding.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(80,'news.publish','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(81,'department.assign_programme','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(82,'department.assign_project','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(83,'department.manage_staff','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(84,'programme.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(85,'programme.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(86,'programme.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(87,'programme.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(88,'programme.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(89,'project.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(90,'project.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(91,'project.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(92,'project.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(93,'project.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(94,'department.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(95,'department.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(96,'department.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(97,'department.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(98,'department.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(99,'position.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(100,'position.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(101,'position.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(102,'position.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(103,'position.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(104,'news.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(105,'news.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(106,'news.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(107,'news.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(108,'news.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(109,'event.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(110,'event.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(111,'event.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(112,'event.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(113,'event.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(114,'story.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(115,'story.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(116,'story.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(117,'story.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(118,'story.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(119,'publication.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(120,'publication.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(121,'publication.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(122,'publication.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(123,'publication.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(124,'gallery.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(125,'gallery.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(126,'gallery.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(127,'gallery.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(128,'gallery.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(129,'team.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(130,'team.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(131,'team.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(132,'team.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(133,'team.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(134,'partner.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(135,'partner.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(136,'partner.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(137,'partner.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(138,'partner.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(139,'member.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(140,'member.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(141,'member.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(142,'member.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(143,'member.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(144,'volunteer.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(145,'volunteer.create','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(146,'volunteer.update','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(147,'volunteer.delete','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(148,'volunteer.restore','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(149,'membership.view','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO permissions VALUES(150,'membership.create','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(151,'membership.update','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(152,'membership.delete','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(153,'membership.restore','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(154,'contact.view','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(155,'contact.create','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(156,'contact.update','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(157,'contact.delete','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(158,'contact.restore','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(159,'partnership.view','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(160,'partnership.create','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(161,'partnership.update','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(162,'partnership.delete','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(163,'partnership.restore','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(164,'newsletter.view','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(165,'newsletter.create','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(166,'newsletter.update','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(167,'newsletter.delete','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(168,'newsletter.restore','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(169,'donation.view','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(170,'donation.create','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(171,'donation.update','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(172,'donation.delete','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(173,'donation.restore','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(174,'campaign.view','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(175,'campaign.create','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(176,'campaign.update','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(177,'campaign.delete','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(178,'campaign.restore','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(179,'impact.view','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(180,'impact.create','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(181,'impact.update','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(182,'impact.delete','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(183,'impact.restore','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(184,'location.view','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(185,'location.create','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(186,'location.update','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(187,'location.delete','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(188,'location.restore','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(189,'page.view','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(190,'page.create','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(191,'page.update','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(192,'page.delete','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(193,'page.restore','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(194,'media.view','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(195,'media.create','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(196,'media.update','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(197,'media.delete','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(198,'media.restore','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(199,'project.publish','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(200,'impact.verify','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO permissions VALUES(201,'impact.publish','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO roles VALUES(1,'Finance Officer','web','2026-10-20 21:42:04','2026-10-20 21:42:04');
INSERT INTO roles VALUES(2,'Super Admin','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO roles VALUES(3,'Administrator','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO roles VALUES(4,'Executive Secretary','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO roles VALUES(5,'Content Manager','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO roles VALUES(6,'Programme Officer','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO roles VALUES(7,'M&E Officer','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO roles VALUES(8,'Communications Officer','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO roles VALUES(9,'Membership Officer','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO roles VALUES(10,'Safeguarding Officer','web','2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO model_has_roles VALUES(2,'App\\Models\\User',1);
INSERT INTO role_has_permissions VALUES(13,1);
INSERT INTO role_has_permissions VALUES(14,1);
INSERT INTO role_has_permissions VALUES(15,1);
INSERT INTO role_has_permissions VALUES(16,1);
INSERT INTO role_has_permissions VALUES(17,1);
INSERT INTO role_has_permissions VALUES(18,1);
INSERT INTO role_has_permissions VALUES(19,1);
INSERT INTO role_has_permissions VALUES(20,1);
INSERT INTO role_has_permissions VALUES(21,1);
INSERT INTO role_has_permissions VALUES(22,1);
INSERT INTO role_has_permissions VALUES(13,2);
INSERT INTO role_has_permissions VALUES(61,2);
INSERT INTO role_has_permissions VALUES(62,2);
INSERT INTO role_has_permissions VALUES(63,2);
INSERT INTO role_has_permissions VALUES(64,2);
INSERT INTO role_has_permissions VALUES(65,2);
INSERT INTO role_has_permissions VALUES(66,2);
INSERT INTO role_has_permissions VALUES(67,2);
INSERT INTO role_has_permissions VALUES(68,2);
INSERT INTO role_has_permissions VALUES(69,2);
INSERT INTO role_has_permissions VALUES(70,2);
INSERT INTO role_has_permissions VALUES(71,2);
INSERT INTO role_has_permissions VALUES(72,2);
INSERT INTO role_has_permissions VALUES(73,2);
INSERT INTO role_has_permissions VALUES(74,2);
INSERT INTO role_has_permissions VALUES(75,2);
INSERT INTO role_has_permissions VALUES(47,2);
INSERT INTO role_has_permissions VALUES(48,2);
INSERT INTO role_has_permissions VALUES(49,2);
INSERT INTO role_has_permissions VALUES(50,2);
INSERT INTO role_has_permissions VALUES(51,2);
INSERT INTO role_has_permissions VALUES(76,2);
INSERT INTO role_has_permissions VALUES(77,2);
INSERT INTO role_has_permissions VALUES(78,2);
INSERT INTO role_has_permissions VALUES(79,2);
INSERT INTO role_has_permissions VALUES(80,2);
INSERT INTO role_has_permissions VALUES(1,2);
INSERT INTO role_has_permissions VALUES(81,2);
INSERT INTO role_has_permissions VALUES(82,2);
INSERT INTO role_has_permissions VALUES(83,2);
INSERT INTO role_has_permissions VALUES(2,2);
INSERT INTO role_has_permissions VALUES(3,2);
INSERT INTO role_has_permissions VALUES(4,2);
INSERT INTO role_has_permissions VALUES(5,2);
INSERT INTO role_has_permissions VALUES(6,2);
INSERT INTO role_has_permissions VALUES(7,2);
INSERT INTO role_has_permissions VALUES(8,2);
INSERT INTO role_has_permissions VALUES(9,2);
INSERT INTO role_has_permissions VALUES(10,2);
INSERT INTO role_has_permissions VALUES(11,2);
INSERT INTO role_has_permissions VALUES(12,2);
INSERT INTO role_has_permissions VALUES(23,2);
INSERT INTO role_has_permissions VALUES(24,2);
INSERT INTO role_has_permissions VALUES(25,2);
INSERT INTO role_has_permissions VALUES(26,2);
INSERT INTO role_has_permissions VALUES(27,2);
INSERT INTO role_has_permissions VALUES(28,2);
INSERT INTO role_has_permissions VALUES(29,2);
INSERT INTO role_has_permissions VALUES(30,2);
INSERT INTO role_has_permissions VALUES(31,2);
INSERT INTO role_has_permissions VALUES(32,2);
INSERT INTO role_has_permissions VALUES(33,2);
INSERT INTO role_has_permissions VALUES(34,2);
INSERT INTO role_has_permissions VALUES(35,2);
INSERT INTO role_has_permissions VALUES(36,2);
INSERT INTO role_has_permissions VALUES(37,2);
INSERT INTO role_has_permissions VALUES(38,2);
INSERT INTO role_has_permissions VALUES(39,2);
INSERT INTO role_has_permissions VALUES(40,2);
INSERT INTO role_has_permissions VALUES(41,2);
INSERT INTO role_has_permissions VALUES(42,2);
INSERT INTO role_has_permissions VALUES(43,2);
INSERT INTO role_has_permissions VALUES(52,2);
INSERT INTO role_has_permissions VALUES(53,2);
INSERT INTO role_has_permissions VALUES(54,2);
INSERT INTO role_has_permissions VALUES(55,2);
INSERT INTO role_has_permissions VALUES(56,2);
INSERT INTO role_has_permissions VALUES(57,2);
INSERT INTO role_has_permissions VALUES(58,2);
INSERT INTO role_has_permissions VALUES(59,2);
INSERT INTO role_has_permissions VALUES(60,2);
INSERT INTO role_has_permissions VALUES(84,2);
INSERT INTO role_has_permissions VALUES(85,2);
INSERT INTO role_has_permissions VALUES(86,2);
INSERT INTO role_has_permissions VALUES(87,2);
INSERT INTO role_has_permissions VALUES(88,2);
INSERT INTO role_has_permissions VALUES(89,2);
INSERT INTO role_has_permissions VALUES(90,2);
INSERT INTO role_has_permissions VALUES(91,2);
INSERT INTO role_has_permissions VALUES(92,2);
INSERT INTO role_has_permissions VALUES(93,2);
INSERT INTO role_has_permissions VALUES(94,2);
INSERT INTO role_has_permissions VALUES(95,2);
INSERT INTO role_has_permissions VALUES(96,2);
INSERT INTO role_has_permissions VALUES(97,2);
INSERT INTO role_has_permissions VALUES(98,2);
INSERT INTO role_has_permissions VALUES(99,2);
INSERT INTO role_has_permissions VALUES(100,2);
INSERT INTO role_has_permissions VALUES(101,2);
INSERT INTO role_has_permissions VALUES(102,2);
INSERT INTO role_has_permissions VALUES(103,2);
INSERT INTO role_has_permissions VALUES(104,2);
INSERT INTO role_has_permissions VALUES(105,2);
INSERT INTO role_has_permissions VALUES(106,2);
INSERT INTO role_has_permissions VALUES(107,2);
INSERT INTO role_has_permissions VALUES(108,2);
INSERT INTO role_has_permissions VALUES(109,2);
INSERT INTO role_has_permissions VALUES(110,2);
INSERT INTO role_has_permissions VALUES(111,2);
INSERT INTO role_has_permissions VALUES(112,2);
INSERT INTO role_has_permissions VALUES(113,2);
INSERT INTO role_has_permissions VALUES(114,2);
INSERT INTO role_has_permissions VALUES(115,2);
INSERT INTO role_has_permissions VALUES(116,2);
INSERT INTO role_has_permissions VALUES(117,2);
INSERT INTO role_has_permissions VALUES(118,2);
INSERT INTO role_has_permissions VALUES(119,2);
INSERT INTO role_has_permissions VALUES(120,2);
INSERT INTO role_has_permissions VALUES(121,2);
INSERT INTO role_has_permissions VALUES(122,2);
INSERT INTO role_has_permissions VALUES(123,2);
INSERT INTO role_has_permissions VALUES(124,2);
INSERT INTO role_has_permissions VALUES(125,2);
INSERT INTO role_has_permissions VALUES(126,2);
INSERT INTO role_has_permissions VALUES(127,2);
INSERT INTO role_has_permissions VALUES(128,2);
INSERT INTO role_has_permissions VALUES(129,2);
INSERT INTO role_has_permissions VALUES(130,2);
INSERT INTO role_has_permissions VALUES(131,2);
INSERT INTO role_has_permissions VALUES(132,2);
INSERT INTO role_has_permissions VALUES(133,2);
INSERT INTO role_has_permissions VALUES(134,2);
INSERT INTO role_has_permissions VALUES(135,2);
INSERT INTO role_has_permissions VALUES(136,2);
INSERT INTO role_has_permissions VALUES(137,2);
INSERT INTO role_has_permissions VALUES(138,2);
INSERT INTO role_has_permissions VALUES(139,2);
INSERT INTO role_has_permissions VALUES(140,2);
INSERT INTO role_has_permissions VALUES(141,2);
INSERT INTO role_has_permissions VALUES(142,2);
INSERT INTO role_has_permissions VALUES(143,2);
INSERT INTO role_has_permissions VALUES(144,2);
INSERT INTO role_has_permissions VALUES(145,2);
INSERT INTO role_has_permissions VALUES(146,2);
INSERT INTO role_has_permissions VALUES(147,2);
INSERT INTO role_has_permissions VALUES(148,2);
INSERT INTO role_has_permissions VALUES(149,2);
INSERT INTO role_has_permissions VALUES(150,2);
INSERT INTO role_has_permissions VALUES(151,2);
INSERT INTO role_has_permissions VALUES(152,2);
INSERT INTO role_has_permissions VALUES(153,2);
INSERT INTO role_has_permissions VALUES(154,2);
INSERT INTO role_has_permissions VALUES(155,2);
INSERT INTO role_has_permissions VALUES(156,2);
INSERT INTO role_has_permissions VALUES(157,2);
INSERT INTO role_has_permissions VALUES(158,2);
INSERT INTO role_has_permissions VALUES(159,2);
INSERT INTO role_has_permissions VALUES(160,2);
INSERT INTO role_has_permissions VALUES(161,2);
INSERT INTO role_has_permissions VALUES(162,2);
INSERT INTO role_has_permissions VALUES(163,2);
INSERT INTO role_has_permissions VALUES(164,2);
INSERT INTO role_has_permissions VALUES(165,2);
INSERT INTO role_has_permissions VALUES(166,2);
INSERT INTO role_has_permissions VALUES(167,2);
INSERT INTO role_has_permissions VALUES(168,2);
INSERT INTO role_has_permissions VALUES(169,2);
INSERT INTO role_has_permissions VALUES(170,2);
INSERT INTO role_has_permissions VALUES(171,2);
INSERT INTO role_has_permissions VALUES(172,2);
INSERT INTO role_has_permissions VALUES(173,2);
INSERT INTO role_has_permissions VALUES(174,2);
INSERT INTO role_has_permissions VALUES(175,2);
INSERT INTO role_has_permissions VALUES(176,2);
INSERT INTO role_has_permissions VALUES(177,2);
INSERT INTO role_has_permissions VALUES(178,2);
INSERT INTO role_has_permissions VALUES(179,2);
INSERT INTO role_has_permissions VALUES(180,2);
INSERT INTO role_has_permissions VALUES(181,2);
INSERT INTO role_has_permissions VALUES(182,2);
INSERT INTO role_has_permissions VALUES(183,2);
INSERT INTO role_has_permissions VALUES(184,2);
INSERT INTO role_has_permissions VALUES(185,2);
INSERT INTO role_has_permissions VALUES(186,2);
INSERT INTO role_has_permissions VALUES(187,2);
INSERT INTO role_has_permissions VALUES(188,2);
INSERT INTO role_has_permissions VALUES(189,2);
INSERT INTO role_has_permissions VALUES(190,2);
INSERT INTO role_has_permissions VALUES(191,2);
INSERT INTO role_has_permissions VALUES(192,2);
INSERT INTO role_has_permissions VALUES(193,2);
INSERT INTO role_has_permissions VALUES(194,2);
INSERT INTO role_has_permissions VALUES(195,2);
INSERT INTO role_has_permissions VALUES(196,2);
INSERT INTO role_has_permissions VALUES(197,2);
INSERT INTO role_has_permissions VALUES(198,2);
INSERT INTO role_has_permissions VALUES(13,3);
INSERT INTO role_has_permissions VALUES(61,3);
INSERT INTO role_has_permissions VALUES(62,3);
INSERT INTO role_has_permissions VALUES(63,3);
INSERT INTO role_has_permissions VALUES(64,3);
INSERT INTO role_has_permissions VALUES(65,3);
INSERT INTO role_has_permissions VALUES(66,3);
INSERT INTO role_has_permissions VALUES(67,3);
INSERT INTO role_has_permissions VALUES(68,3);
INSERT INTO role_has_permissions VALUES(69,3);
INSERT INTO role_has_permissions VALUES(70,3);
INSERT INTO role_has_permissions VALUES(71,3);
INSERT INTO role_has_permissions VALUES(72,3);
INSERT INTO role_has_permissions VALUES(73,3);
INSERT INTO role_has_permissions VALUES(74,3);
INSERT INTO role_has_permissions VALUES(75,3);
INSERT INTO role_has_permissions VALUES(47,3);
INSERT INTO role_has_permissions VALUES(48,3);
INSERT INTO role_has_permissions VALUES(49,3);
INSERT INTO role_has_permissions VALUES(50,3);
INSERT INTO role_has_permissions VALUES(51,3);
INSERT INTO role_has_permissions VALUES(77,3);
INSERT INTO role_has_permissions VALUES(80,3);
INSERT INTO role_has_permissions VALUES(1,3);
INSERT INTO role_has_permissions VALUES(81,3);
INSERT INTO role_has_permissions VALUES(82,3);
INSERT INTO role_has_permissions VALUES(83,3);
INSERT INTO role_has_permissions VALUES(2,3);
INSERT INTO role_has_permissions VALUES(3,3);
INSERT INTO role_has_permissions VALUES(4,3);
INSERT INTO role_has_permissions VALUES(5,3);
INSERT INTO role_has_permissions VALUES(6,3);
INSERT INTO role_has_permissions VALUES(7,3);
INSERT INTO role_has_permissions VALUES(8,3);
INSERT INTO role_has_permissions VALUES(9,3);
INSERT INTO role_has_permissions VALUES(10,3);
INSERT INTO role_has_permissions VALUES(11,3);
INSERT INTO role_has_permissions VALUES(12,3);
INSERT INTO role_has_permissions VALUES(23,3);
INSERT INTO role_has_permissions VALUES(24,3);
INSERT INTO role_has_permissions VALUES(25,3);
INSERT INTO role_has_permissions VALUES(26,3);
INSERT INTO role_has_permissions VALUES(27,3);
INSERT INTO role_has_permissions VALUES(28,3);
INSERT INTO role_has_permissions VALUES(29,3);
INSERT INTO role_has_permissions VALUES(30,3);
INSERT INTO role_has_permissions VALUES(31,3);
INSERT INTO role_has_permissions VALUES(32,3);
INSERT INTO role_has_permissions VALUES(33,3);
INSERT INTO role_has_permissions VALUES(34,3);
INSERT INTO role_has_permissions VALUES(35,3);
INSERT INTO role_has_permissions VALUES(36,3);
INSERT INTO role_has_permissions VALUES(37,3);
INSERT INTO role_has_permissions VALUES(42,3);
INSERT INTO role_has_permissions VALUES(43,3);
INSERT INTO role_has_permissions VALUES(84,3);
INSERT INTO role_has_permissions VALUES(85,3);
INSERT INTO role_has_permissions VALUES(86,3);
INSERT INTO role_has_permissions VALUES(87,3);
INSERT INTO role_has_permissions VALUES(88,3);
INSERT INTO role_has_permissions VALUES(89,3);
INSERT INTO role_has_permissions VALUES(90,3);
INSERT INTO role_has_permissions VALUES(91,3);
INSERT INTO role_has_permissions VALUES(92,3);
INSERT INTO role_has_permissions VALUES(93,3);
INSERT INTO role_has_permissions VALUES(94,3);
INSERT INTO role_has_permissions VALUES(95,3);
INSERT INTO role_has_permissions VALUES(96,3);
INSERT INTO role_has_permissions VALUES(97,3);
INSERT INTO role_has_permissions VALUES(98,3);
INSERT INTO role_has_permissions VALUES(99,3);
INSERT INTO role_has_permissions VALUES(100,3);
INSERT INTO role_has_permissions VALUES(101,3);
INSERT INTO role_has_permissions VALUES(102,3);
INSERT INTO role_has_permissions VALUES(103,3);
INSERT INTO role_has_permissions VALUES(104,3);
INSERT INTO role_has_permissions VALUES(105,3);
INSERT INTO role_has_permissions VALUES(106,3);
INSERT INTO role_has_permissions VALUES(107,3);
INSERT INTO role_has_permissions VALUES(108,3);
INSERT INTO role_has_permissions VALUES(109,3);
INSERT INTO role_has_permissions VALUES(110,3);
INSERT INTO role_has_permissions VALUES(111,3);
INSERT INTO role_has_permissions VALUES(112,3);
INSERT INTO role_has_permissions VALUES(113,3);
INSERT INTO role_has_permissions VALUES(114,3);
INSERT INTO role_has_permissions VALUES(115,3);
INSERT INTO role_has_permissions VALUES(116,3);
INSERT INTO role_has_permissions VALUES(117,3);
INSERT INTO role_has_permissions VALUES(118,3);
INSERT INTO role_has_permissions VALUES(119,3);
INSERT INTO role_has_permissions VALUES(120,3);
INSERT INTO role_has_permissions VALUES(121,3);
INSERT INTO role_has_permissions VALUES(122,3);
INSERT INTO role_has_permissions VALUES(123,3);
INSERT INTO role_has_permissions VALUES(124,3);
INSERT INTO role_has_permissions VALUES(125,3);
INSERT INTO role_has_permissions VALUES(126,3);
INSERT INTO role_has_permissions VALUES(127,3);
INSERT INTO role_has_permissions VALUES(128,3);
INSERT INTO role_has_permissions VALUES(129,3);
INSERT INTO role_has_permissions VALUES(130,3);
INSERT INTO role_has_permissions VALUES(131,3);
INSERT INTO role_has_permissions VALUES(132,3);
INSERT INTO role_has_permissions VALUES(133,3);
INSERT INTO role_has_permissions VALUES(134,3);
INSERT INTO role_has_permissions VALUES(135,3);
INSERT INTO role_has_permissions VALUES(136,3);
INSERT INTO role_has_permissions VALUES(137,3);
INSERT INTO role_has_permissions VALUES(138,3);
INSERT INTO role_has_permissions VALUES(139,3);
INSERT INTO role_has_permissions VALUES(140,3);
INSERT INTO role_has_permissions VALUES(141,3);
INSERT INTO role_has_permissions VALUES(142,3);
INSERT INTO role_has_permissions VALUES(143,3);
INSERT INTO role_has_permissions VALUES(144,3);
INSERT INTO role_has_permissions VALUES(145,3);
INSERT INTO role_has_permissions VALUES(146,3);
INSERT INTO role_has_permissions VALUES(147,3);
INSERT INTO role_has_permissions VALUES(148,3);
INSERT INTO role_has_permissions VALUES(149,3);
INSERT INTO role_has_permissions VALUES(150,3);
INSERT INTO role_has_permissions VALUES(151,3);
INSERT INTO role_has_permissions VALUES(152,3);
INSERT INTO role_has_permissions VALUES(153,3);
INSERT INTO role_has_permissions VALUES(154,3);
INSERT INTO role_has_permissions VALUES(155,3);
INSERT INTO role_has_permissions VALUES(156,3);
INSERT INTO role_has_permissions VALUES(157,3);
INSERT INTO role_has_permissions VALUES(158,3);
INSERT INTO role_has_permissions VALUES(159,3);
INSERT INTO role_has_permissions VALUES(160,3);
INSERT INTO role_has_permissions VALUES(161,3);
INSERT INTO role_has_permissions VALUES(162,3);
INSERT INTO role_has_permissions VALUES(163,3);
INSERT INTO role_has_permissions VALUES(164,3);
INSERT INTO role_has_permissions VALUES(165,3);
INSERT INTO role_has_permissions VALUES(166,3);
INSERT INTO role_has_permissions VALUES(167,3);
INSERT INTO role_has_permissions VALUES(168,3);
INSERT INTO role_has_permissions VALUES(169,3);
INSERT INTO role_has_permissions VALUES(170,3);
INSERT INTO role_has_permissions VALUES(171,3);
INSERT INTO role_has_permissions VALUES(172,3);
INSERT INTO role_has_permissions VALUES(173,3);
INSERT INTO role_has_permissions VALUES(174,3);
INSERT INTO role_has_permissions VALUES(175,3);
INSERT INTO role_has_permissions VALUES(176,3);
INSERT INTO role_has_permissions VALUES(177,3);
INSERT INTO role_has_permissions VALUES(178,3);
INSERT INTO role_has_permissions VALUES(179,3);
INSERT INTO role_has_permissions VALUES(180,3);
INSERT INTO role_has_permissions VALUES(181,3);
INSERT INTO role_has_permissions VALUES(182,3);
INSERT INTO role_has_permissions VALUES(183,3);
INSERT INTO role_has_permissions VALUES(184,3);
INSERT INTO role_has_permissions VALUES(185,3);
INSERT INTO role_has_permissions VALUES(186,3);
INSERT INTO role_has_permissions VALUES(187,3);
INSERT INTO role_has_permissions VALUES(188,3);
INSERT INTO role_has_permissions VALUES(189,3);
INSERT INTO role_has_permissions VALUES(190,3);
INSERT INTO role_has_permissions VALUES(191,3);
INSERT INTO role_has_permissions VALUES(192,3);
INSERT INTO role_has_permissions VALUES(193,3);
INSERT INTO role_has_permissions VALUES(194,3);
INSERT INTO role_has_permissions VALUES(195,3);
INSERT INTO role_has_permissions VALUES(196,3);
INSERT INTO role_has_permissions VALUES(197,3);
INSERT INTO role_has_permissions VALUES(198,3);
INSERT INTO role_has_permissions VALUES(13,4);
INSERT INTO role_has_permissions VALUES(1,4);
INSERT INTO role_has_permissions VALUES(94,4);
INSERT INTO role_has_permissions VALUES(5,4);
INSERT INTO role_has_permissions VALUES(8,4);
INSERT INTO role_has_permissions VALUES(89,4);
INSERT INTO role_has_permissions VALUES(84,4);
INSERT INTO role_has_permissions VALUES(33,4);
INSERT INTO role_has_permissions VALUES(34,4);
INSERT INTO role_has_permissions VALUES(52,4);
INSERT INTO role_has_permissions VALUES(53,4);
INSERT INTO role_has_permissions VALUES(55,4);
INSERT INTO role_has_permissions VALUES(59,4);
INSERT INTO role_has_permissions VALUES(23,4);
INSERT INTO role_has_permissions VALUES(31,4);
INSERT INTO role_has_permissions VALUES(13,5);
INSERT INTO role_has_permissions VALUES(61,5);
INSERT INTO role_has_permissions VALUES(84,5);
INSERT INTO role_has_permissions VALUES(85,5);
INSERT INTO role_has_permissions VALUES(86,5);
INSERT INTO role_has_permissions VALUES(87,5);
INSERT INTO role_has_permissions VALUES(88,5);
INSERT INTO role_has_permissions VALUES(189,5);
INSERT INTO role_has_permissions VALUES(190,5);
INSERT INTO role_has_permissions VALUES(191,5);
INSERT INTO role_has_permissions VALUES(104,5);
INSERT INTO role_has_permissions VALUES(105,5);
INSERT INTO role_has_permissions VALUES(106,5);
INSERT INTO role_has_permissions VALUES(107,5);
INSERT INTO role_has_permissions VALUES(109,5);
INSERT INTO role_has_permissions VALUES(110,5);
INSERT INTO role_has_permissions VALUES(111,5);
INSERT INTO role_has_permissions VALUES(112,5);
INSERT INTO role_has_permissions VALUES(119,5);
INSERT INTO role_has_permissions VALUES(120,5);
INSERT INTO role_has_permissions VALUES(121,5);
INSERT INTO role_has_permissions VALUES(122,5);
INSERT INTO role_has_permissions VALUES(124,5);
INSERT INTO role_has_permissions VALUES(125,5);
INSERT INTO role_has_permissions VALUES(126,5);
INSERT INTO role_has_permissions VALUES(127,5);
INSERT INTO role_has_permissions VALUES(134,5);
INSERT INTO role_has_permissions VALUES(135,5);
INSERT INTO role_has_permissions VALUES(136,5);
INSERT INTO role_has_permissions VALUES(194,5);
INSERT INTO role_has_permissions VALUES(195,5);
INSERT INTO role_has_permissions VALUES(196,5);
INSERT INTO role_has_permissions VALUES(13,6);
INSERT INTO role_has_permissions VALUES(84,6);
INSERT INTO role_has_permissions VALUES(86,6);
INSERT INTO role_has_permissions VALUES(89,6);
INSERT INTO role_has_permissions VALUES(90,6);
INSERT INTO role_has_permissions VALUES(91,6);
INSERT INTO role_has_permissions VALUES(94,6);
INSERT INTO role_has_permissions VALUES(99,6);
INSERT INTO role_has_permissions VALUES(81,6);
INSERT INTO role_has_permissions VALUES(82,6);
INSERT INTO role_has_permissions VALUES(4,6);
INSERT INTO role_has_permissions VALUES(33,6);
INSERT INTO role_has_permissions VALUES(36,6);
INSERT INTO role_has_permissions VALUES(37,6);
INSERT INTO role_has_permissions VALUES(52,6);
INSERT INTO role_has_permissions VALUES(53,6);
INSERT INTO role_has_permissions VALUES(55,6);
INSERT INTO role_has_permissions VALUES(59,6);
INSERT INTO role_has_permissions VALUES(23,6);
INSERT INTO role_has_permissions VALUES(24,6);
INSERT INTO role_has_permissions VALUES(26,6);
INSERT INTO role_has_permissions VALUES(13,7);
INSERT INTO role_has_permissions VALUES(179,7);
INSERT INTO role_has_permissions VALUES(180,7);
INSERT INTO role_has_permissions VALUES(181,7);
INSERT INTO role_has_permissions VALUES(89,7);
INSERT INTO role_has_permissions VALUES(94,7);
INSERT INTO role_has_permissions VALUES(33,7);
INSERT INTO role_has_permissions VALUES(35,7);
INSERT INTO role_has_permissions VALUES(36,7);
INSERT INTO role_has_permissions VALUES(37,7);
INSERT INTO role_has_permissions VALUES(38,7);
INSERT INTO role_has_permissions VALUES(39,7);
INSERT INTO role_has_permissions VALUES(40,7);
INSERT INTO role_has_permissions VALUES(41,7);
INSERT INTO role_has_permissions VALUES(43,7);
INSERT INTO role_has_permissions VALUES(52,7);
INSERT INTO role_has_permissions VALUES(53,7);
INSERT INTO role_has_permissions VALUES(54,7);
INSERT INTO role_has_permissions VALUES(55,7);
INSERT INTO role_has_permissions VALUES(56,7);
INSERT INTO role_has_permissions VALUES(57,7);
INSERT INTO role_has_permissions VALUES(59,7);
INSERT INTO role_has_permissions VALUES(60,7);
INSERT INTO role_has_permissions VALUES(23,7);
INSERT INTO role_has_permissions VALUES(24,7);
INSERT INTO role_has_permissions VALUES(26,7);
INSERT INTO role_has_permissions VALUES(27,7);
INSERT INTO role_has_permissions VALUES(31,7);
INSERT INTO role_has_permissions VALUES(13,8);
INSERT INTO role_has_permissions VALUES(104,8);
INSERT INTO role_has_permissions VALUES(105,8);
INSERT INTO role_has_permissions VALUES(106,8);
INSERT INTO role_has_permissions VALUES(80,8);
INSERT INTO role_has_permissions VALUES(109,8);
INSERT INTO role_has_permissions VALUES(110,8);
INSERT INTO role_has_permissions VALUES(111,8);
INSERT INTO role_has_permissions VALUES(119,8);
INSERT INTO role_has_permissions VALUES(120,8);
INSERT INTO role_has_permissions VALUES(121,8);
INSERT INTO role_has_permissions VALUES(124,8);
INSERT INTO role_has_permissions VALUES(125,8);
INSERT INTO role_has_permissions VALUES(126,8);
INSERT INTO role_has_permissions VALUES(159,8);
INSERT INTO role_has_permissions VALUES(161,8);
INSERT INTO role_has_permissions VALUES(114,8);
INSERT INTO role_has_permissions VALUES(115,8);
INSERT INTO role_has_permissions VALUES(116,8);
INSERT INTO role_has_permissions VALUES(189,8);
INSERT INTO role_has_permissions VALUES(191,8);
INSERT INTO role_has_permissions VALUES(134,8);
INSERT INTO role_has_permissions VALUES(135,8);
INSERT INTO role_has_permissions VALUES(136,8);
INSERT INTO role_has_permissions VALUES(194,8);
INSERT INTO role_has_permissions VALUES(195,8);
INSERT INTO role_has_permissions VALUES(196,8);
INSERT INTO role_has_permissions VALUES(13,9);
INSERT INTO role_has_permissions VALUES(139,9);
INSERT INTO role_has_permissions VALUES(141,9);
INSERT INTO role_has_permissions VALUES(77,9);
INSERT INTO role_has_permissions VALUES(149,9);
INSERT INTO role_has_permissions VALUES(151,9);
INSERT INTO role_has_permissions VALUES(144,9);
INSERT INTO role_has_permissions VALUES(146,9);
INSERT INTO role_has_permissions VALUES(13,10);
INSERT INTO role_has_permissions VALUES(78,10);
INSERT INTO role_has_permissions VALUES(79,10);
INSERT INTO role_has_permissions VALUES(199,2);
INSERT INTO role_has_permissions VALUES(200,2);
INSERT INTO role_has_permissions VALUES(201,2);
INSERT INTO role_has_permissions VALUES(199,3);
INSERT INTO role_has_permissions VALUES(200,3);
INSERT INTO role_has_permissions VALUES(201,3);
INSERT INTO role_has_permissions VALUES(200,7);
INSERT INTO role_has_permissions VALUES(89,5);
INSERT INTO role_has_permissions VALUES(90,5);
INSERT INTO role_has_permissions VALUES(91,5);
INSERT INTO role_has_permissions VALUES(99,7);
INSERT INTO site_settings VALUES(1,'global','{"organization_name":"Ustawi Global Health Initiative","abbreviation":"UGHI","tagline":"Communities leading, sustaining and thriving in health, locally and globally.","hero_eyebrow":"COMMUNITY-LED HEALTH","hero_title":"Communities Leading,\\nSustaining and Thriving\\nin Health.","hero_description":"We work with communities to improve health, strengthen health systems and create sustainable change across Tanzania.","hero_primary_label":"Explore Our Work","hero_primary_url":"\\/programmes","hero_secondary_label":"Support Our Mission","hero_secondary_url":"\\/get-involved","hero_image_alt":"A healthcare professional in conversation with a patient","hero_image_caption":"Illustrative photography \\u00b7 Community at the heart of care","about_eyebrow":"ROOTED IN COMMUNITY","about_title":"Improving Health Through Community Leadership","about_description":"Ustawi Global Health Initiative is a Tanzanian non-governmental, non-profit, non-partisan and independent health organization. We put communities at the centre of lasting change.","about_image_alt":"Volunteers organizing supplies for community support","mission":"To improve the health and well-being of mothers, children, adolescents and communities through community-led, evidence-based health interventions, strengthened health systems, youth empowerment and sustainable approaches to lasting change.","vision":"Communities leading, sustaining and thriving in health, locally and globally.","goal":"To contribute to improved health, well-being and resilience of individuals and communities through equitable access to quality health services, community engagement, youth leadership and strengthened health systems.","programmes_eyebrow":"OUR FOCUS AREAS","programmes_title":"Better health. Lasting change.","programmes_description":"Seven connected areas of work. One shared purpose: healthier, more resilient communities.","location_title":"Local roots. A shared future.","location_description":"We believe sustainable health begins with listening to the people who know their communities best. Explore the communities and operating areas that shape our work.","cta_title":"A healthier future starts with all of us.","cta_description":"Bring your time, expertise or partnership. Together, we can support community-led change.","footer_about":"Community-led. Evidence-based. Together for better health.","footer_links":[{"heading":"ABOUT","links":[{"label":"Who We Are","url":"\\/about"},{"label":"Departments","url":"\\/about\\/departments"},{"label":"Partners","url":"\\/about\\/partners"},{"label":"Leadership","url":"\\/about\\/leadership"},{"label":"Governance","url":"\\/about\\/governance"},{"label":"Where We Work","url":"\\/where-we-work"}]},{"heading":"OUR WORK","links":[{"label":"Programmes","url":"\\/programmes"},{"label":"Projects","url":"\\/projects"},{"label":"Impact","url":"\\/impact"},{"label":"Research","url":"\\/resources\\/publications"}]},{"heading":"GET INVOLVED","links":[{"label":"Volunteer","url":"\\/get-involved\\/volunteer"},{"label":"Membership","url":"\\/get-involved\\/membership"},{"label":"Partnership","url":"\\/get-involved\\/partner"},{"label":"Donate","url":"\\/donate"}]},{"heading":"RESOURCES","links":[{"label":"News","url":"\\/news"},{"label":"Events","url":"\\/events"},{"label":"Publications","url":"\\/resources\\/publications"},{"label":"Gallery","url":"\\/gallery"}]}],"footer_legal_links":[{"label":"Privacy Policy","url":"\\/privacy-policy"},{"label":"Safeguarding","url":"\\/safeguarding"},{"label":"Terms","url":"\\/terms"}],"default_meta_title":"Community-led health in Tanzania","default_meta_description":"Ustawi Global Health Initiative works with communities in Tanzania to improve health, strengthen health systems and create sustainable change.","email":null,"phone":null,"whatsapp":null,"physical_address":null,"postal_address":null,"facebook":null,"instagram":null,"linkedin":null,"youtube":null,"x":null,"logo":null,"logo_alt":null,"favicon":null,"favicon_alt":null,"hero_image":null,"hero_image_2":null,"hero_image_2_alt":"Community volunteers working together","hero_image_2_caption":"Community outreach for healthier futures","hero_image_3":null,"hero_image_3_alt":"Map of Tanzania regions where UGHI works","hero_image_3_caption":"Health education for lasting change","about_image":null,"default_og_image":null,"performance_score_cap":100,"performance_on_track_threshold":80,"performance_critical_threshold":60,"performance_enforce_100_percent_weights":false}','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO programmes VALUES(1,'maternal-newborn-child-and-adolescent-health','{"en":"Maternal, Newborn, Child and Adolescent Health"}','{"en":"Supporting healthier lives at every stage, from pregnancy and birth through childhood and adolescence."}','{"en":"Supporting healthier lives at every stage, from pregnancy and birth through childhood and adolescence."}',NULL,NULL,'heart',1,1,NULL,NULL,NULL,'2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO programmes VALUES(2,'hiv-prevention-care-treatment-and-client-retention','{"en":"HIV Prevention, Care, Treatment and Client Retention"}','{"en":"Community-led prevention, access to care and sustained support for people living with HIV."}','{"en":"Community-led prevention, access to care and sustained support for people living with HIV."}',NULL,NULL,'shield',2,1,NULL,NULL,NULL,'2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO programmes VALUES(3,'cancer-prevention-screening-and-non-communicable-diseases','{"en":"Cancer Prevention, Screening and Non-Communicable Diseases"}','{"en":"Building awareness and strengthening access to prevention, early detection and continuing care."}','{"en":"Building awareness and strengthening access to prevention, early detection and continuing care."}',NULL,NULL,'activity',3,1,NULL,NULL,NULL,'2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO programmes VALUES(4,'youth-sexual-and-reproductive-health-rights-and-empowerment','{"en":"Youth Sexual and Reproductive Health, Rights and Empowerment"}','{"en":"Supporting young people with knowledge, choice and opportunities to lead."}','{"en":"Supporting young people with knowledge, choice and opportunities to lead."}',NULL,NULL,'users',4,1,NULL,NULL,NULL,'2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO programmes VALUES(5,'mental-health-and-psychosocial-support','{"en":"Mental Health and Psychosocial Support"}','{"en":"Promoting mental well-being, reducing stigma and supporting compassionate community care."}','{"en":"Promoting mental well-being, reducing stigma and supporting compassionate community care."}',NULL,NULL,'sun',5,1,NULL,NULL,NULL,'2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO programmes VALUES(6,'nutrition-water-sanitation-hygiene-and-environmental-health','{"en":"Nutrition, Water, Sanitation, Hygiene and Environmental Health"}','{"en":"Connecting healthy nutrition and safe environments with healthier communities."}','{"en":"Connecting healthy nutrition and safe environments with healthier communities."}',NULL,NULL,'droplet',6,1,NULL,NULL,NULL,'2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO programmes VALUES(7,'health-systems-strengthening-capacity-building-and-research','{"en":"Health Systems Strengthening, Capacity Building and Research"}','{"en":"Strengthening people, systems and evidence for equitable, sustainable health services."}','{"en":"Strengthening people, systems and evidence for equitable, sustainable health services."}',NULL,NULL,'book',7,1,NULL,NULL,NULL,'2026-10-20 21:42:05','2026-10-20 21:42:05');
INSERT INTO countries VALUES(1,'Tanzania','TZ','tanzania',1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO regions VALUES(1,1,'Ruvuma','ruvuma',NULL,0,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO districts VALUES(1,1,'Namtumbo','namtumbo',0,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO impact_statistics VALUES(1,NULL,NULL,'people_reached','{"en":"People reached"}',0,'','users',NULL,NULL,NULL,NULL,0,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO impact_statistics VALUES(2,NULL,NULL,'communities_supported','{"en":"Communities supported"}',0,'','users',NULL,NULL,NULL,NULL,0,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO impact_statistics VALUES(3,NULL,NULL,'health_workers_trained','{"en":"Health workers trained"}',0,'','users',NULL,NULL,NULL,NULL,0,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO impact_statistics VALUES(4,NULL,NULL,'projects','{"en":"Projects"}',0,'','users',NULL,NULL,NULL,NULL,0,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO impact_statistics VALUES(5,NULL,NULL,'partners','{"en":"Partners"}',0,'','users',NULL,NULL,NULL,NULL,0,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO departments VALUES(1,NULL,NULL,'SEC','Secretariat','secretariat','The coordinating administrative office of UGHI.',NULL,NULL,NULL,NULL,NULL,NULL,0,1,1,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO departments VALUES(2,1,NULL,'EXEC','Executive Office','executive-office',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO departments VALUES(3,1,NULL,'PROG','Programmes','programmes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,1,1,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO departments VALUES(4,1,NULL,'MEL','Monitoring, Evaluation & Learning','monitoring-evaluation-learning',NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,1,1,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO departments VALUES(5,1,NULL,'RKM','Research & Knowledge Management','research-knowledge-management',NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,1,1,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO departments VALUES(6,1,NULL,'FIN','Finance & Administration','finance-administration',NULL,NULL,NULL,NULL,NULL,NULL,NULL,5,1,1,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO departments VALUES(7,1,NULL,'HR','Human Resources','human-resources',NULL,NULL,NULL,NULL,NULL,NULL,NULL,6,1,1,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO departments VALUES(8,1,NULL,'COMMS','Communications & Partnerships','communications-partnerships',NULL,NULL,NULL,NULL,NULL,NULL,NULL,7,1,1,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO departments VALUES(9,1,NULL,'ICT','ICT / Digital Systems','ict-digital-systems',NULL,NULL,NULL,NULL,NULL,NULL,NULL,8,1,1,1,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(1,'news','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"News"}','{"en":"Updates from UGHI and our communities."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(2,'about/leadership','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"Leadership"}','{"en":"Accountable leadership. A shared commitment."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(3,'about/governance','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"Governance"}','{"en":"Integrity, oversight and accountability."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(4,'get-involved','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"Get Involved"}','{"en":"A healthier future starts with all of us."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(5,'get-involved/volunteer','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"Volunteer with UGHI"}','{"en":"Share your time and skills for community health."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(6,'get-involved/membership','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"Membership"}','{"en":"Join a community committed to lasting change."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(7,'get-involved/partner','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"Partner with UGHI"}','{"en":"Connect expertise, resources and shared purpose."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(8,'contact','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"Contact UGHI"}','{"en":"Let\\u2019s connect for healthier communities."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(9,'safeguarding','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"Safeguarding"}','{"en":"Dignity, safety and respect for everyone."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(10,'privacy-policy','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"Privacy Policy"}','{"en":"Respecting your privacy."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO content_pages VALUES(11,'terms','USTAWI GLOBAL HEALTH INITIATIVE','{"en":"Terms"}','{"en":"Using this website."}','{"en":""}',NULL,NULL,NULL,NULL,NULL,1,0,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflows VALUES(1,'Project Proposal (Default)','PROJECT_PROPOSAL','project','Default review route: department manager, department head, Executive Secretary.',1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflows VALUES(2,'Department Report (Default)','DEPARTMENT_REPORT','department_report','Default review route for department reports.',1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflows VALUES(3,'Procurement Request (Default)','PROCUREMENT_REQUEST','procurement','Default review route for procurement requests.',1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflows VALUES(4,'Publication (Default)','PUBLICATION','publication','Default publication review route.',1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflows VALUES(5,'Programme Activity (Default)','PROGRAMME_ACTIVITY','programme_activity','Default programme activity review route.',1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflow_steps VALUES(1,1,1,'Programme Manager','DEPARTMENT_MANAGER',NULL,'MANAGER',NULL,1,1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflow_steps VALUES(2,1,2,'Department Head','DEPARTMENT_HEAD',NULL,'HEAD',NULL,1,1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflow_steps VALUES(3,1,3,'Executive Secretary','EXECUTIVE_SECRETARY',NULL,NULL,NULL,0,1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflow_steps VALUES(4,2,1,'Department Head','DEPARTMENT_HEAD',NULL,'HEAD',NULL,1,1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflow_steps VALUES(5,2,2,'Executive Secretary','EXECUTIVE_SECRETARY',NULL,NULL,NULL,0,1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflow_steps VALUES(6,3,1,'Department Head','DEPARTMENT_HEAD',NULL,'HEAD',NULL,1,1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflow_steps VALUES(7,3,2,'Executive Secretary','EXECUTIVE_SECRETARY',NULL,NULL,NULL,0,1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflow_steps VALUES(8,4,1,'Communications Reviewer','PERMISSION','news.publish',NULL,NULL,1,1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflow_steps VALUES(9,5,1,'Department Manager','DEPARTMENT_MANAGER',NULL,'MANAGER',NULL,1,1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO approval_workflow_steps VALUES(10,5,2,'Department Head','DEPARTMENT_HEAD',NULL,'HEAD',NULL,1,1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO department_activity_logs VALUES(1,NULL,NULL,'performance.rating_scale_created','App\\Models\\PerformanceRatingScale',1,'[]','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO department_activity_logs VALUES(2,NULL,NULL,'performance.rating_scale_created','App\\Models\\PerformanceRatingScale',2,'[]','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO department_activity_logs VALUES(3,NULL,NULL,'performance.rating_scale_created','App\\Models\\PerformanceRatingScale',3,'[]','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO department_activity_logs VALUES(4,NULL,NULL,'performance.rating_scale_created','App\\Models\\PerformanceRatingScale',4,'[]','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO department_activity_logs VALUES(5,NULL,NULL,'performance.rating_scale_created','App\\Models\\PerformanceRatingScale',5,'[]','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO financial_years VALUES(1,'2026','2026-01-01 00:00:00','2026-12-31 00:00:00','OPEN',NULL,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO accounts VALUES(1,NULL,'1000','Assets','ASSET','DEBIT',NULL,0,1,10,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO accounts VALUES(2,1,'1100','Bank Accounts','ASSET','DEBIT',NULL,1,1,20,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO accounts VALUES(3,1,'1200','Cash Accounts','ASSET','DEBIT',NULL,1,1,30,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO accounts VALUES(4,NULL,'2000','Liabilities','LIABILITY','CREDIT',NULL,0,1,40,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO accounts VALUES(5,NULL,'3000','Equity','EQUITY','CREDIT',NULL,0,1,50,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO accounts VALUES(6,NULL,'4000','Income','INCOME','CREDIT',NULL,0,1,60,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO accounts VALUES(7,NULL,'5000','Expenses','EXPENSE','DEBIT',NULL,0,1,70,NULL,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO finance_audit_logs VALUES(1,NULL,'FinancialYear.created','App\\Models\\FinancialYear',1,'{"name":"2026","status":"OPEN"}','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO finance_audit_logs VALUES(2,NULL,'Account.created','App\\Models\\Account',1,'{"code":"1000","name":"Assets","type":"ASSET"}','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO finance_audit_logs VALUES(3,NULL,'Account.created','App\\Models\\Account',2,'{"code":"1100","name":"Bank Accounts","type":"ASSET"}','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO finance_audit_logs VALUES(4,NULL,'Account.created','App\\Models\\Account',3,'{"code":"1200","name":"Cash Accounts","type":"ASSET"}','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO finance_audit_logs VALUES(5,NULL,'Account.created','App\\Models\\Account',4,'{"code":"2000","name":"Liabilities","type":"LIABILITY"}','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO finance_audit_logs VALUES(6,NULL,'Account.created','App\\Models\\Account',5,'{"code":"3000","name":"Equity","type":"EQUITY"}','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO finance_audit_logs VALUES(7,NULL,'Account.created','App\\Models\\Account',6,'{"code":"4000","name":"Income","type":"INCOME"}','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO finance_audit_logs VALUES(8,NULL,'Account.created','App\\Models\\Account',7,'{"code":"5000","name":"Expenses","type":"EXPENSE"}','2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO users VALUES(1,'UGHI Administrator','admin@ughi.local',NULL,'$2y$12$.urkQJMw/mjXiKPZaVLERuoZWy5qYEbToNIMVXsAfBLWbOoJKJiCa',NULL,'2026-10-20 21:42:05','2026-10-20 21:42:05',1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ACTIVE',NULL,NULL,0,NULL,'2026-10-20 21:42:05','en','Africa/Dar_es_Salaam',NULL);
INSERT INTO performance_rating_scales VALUES(1,'UGHI Performance Rating',90,100,'Excellent','Starter configuration. Edit these thresholds to match UGHI policy.',1,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO performance_rating_scales VALUES(2,'UGHI Performance Rating',80,89.98999999999999489,'Very Good','Starter configuration. Edit these thresholds to match UGHI policy.',2,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO performance_rating_scales VALUES(3,'UGHI Performance Rating',70,79.98999999999999489,'Good','Starter configuration. Edit these thresholds to match UGHI policy.',3,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO performance_rating_scales VALUES(4,'UGHI Performance Rating',60,69.98999999999999489,'Needs Attention','Starter configuration. Edit these thresholds to match UGHI policy.',4,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO performance_rating_scales VALUES(5,'UGHI Performance Rating',0,59.99000000000000198,'Critical','Starter configuration. Edit these thresholds to match UGHI policy.',5,1,'2026-10-20 21:42:06','2026-10-20 21:42:06');
INSERT INTO sqlite_sequence VALUES('migrations',30);
INSERT INTO sqlite_sequence VALUES('departments',9);
INSERT INTO sqlite_sequence VALUES('donations',0);
INSERT INTO sqlite_sequence VALUES('permissions',201);
INSERT INTO sqlite_sequence VALUES('roles',10);
INSERT INTO sqlite_sequence VALUES('users',1);
INSERT INTO sqlite_sequence VALUES('projects',0);
INSERT INTO sqlite_sequence VALUES('performance_indicators',0);
INSERT INTO sqlite_sequence VALUES('performance_targets',0);
INSERT INTO sqlite_sequence VALUES('programmes',7);
INSERT INTO sqlite_sequence VALUES('countries',1);
INSERT INTO sqlite_sequence VALUES('regions',1);
INSERT INTO sqlite_sequence VALUES('districts',1);
INSERT INTO sqlite_sequence VALUES('impact_statistics',5);
INSERT INTO sqlite_sequence VALUES('content_pages',11);
INSERT INTO sqlite_sequence VALUES('approval_workflows',5);
INSERT INTO sqlite_sequence VALUES('approval_workflow_steps',10);
INSERT INTO sqlite_sequence VALUES('financial_years',1);
INSERT INTO sqlite_sequence VALUES('finance_audit_logs',8);
INSERT INTO sqlite_sequence VALUES('accounts',7);
INSERT INTO sqlite_sequence VALUES('performance_rating_scales',5);
INSERT INTO sqlite_sequence VALUES('department_activity_logs',5);
INSERT INTO sqlite_sequence VALUES('site_settings',1);

-- Foreign keys
ALTER TABLE `model_has_permissions` ADD foreign key(`permission_id`) references `permissions`(`id`) on delete cascade;
ALTER TABLE `model_has_roles` ADD foreign key(`role_id`) references `roles`(`id`) on delete cascade;
ALTER TABLE `role_has_permissions` ADD foreign key(`permission_id`) references `permissions`(`id`) on delete cascade;
ALTER TABLE `role_has_permissions` ADD foreign key(`role_id`) references `roles`(`id`) on delete cascade;
ALTER TABLE `regions` ADD foreign key(`country_id`) references `countries`(`id`) on delete restrict;
ALTER TABLE `districts` ADD foreign key(`region_id`) references `regions`(`id`) on delete restrict;
ALTER TABLE `project_images` ADD foreign key(`project_id`) references `projects`(`id`) on delete cascade;
ALTER TABLE `impact_statistics` ADD foreign key(`programme_id`) references `programmes`(`id`) on delete restrict;
ALTER TABLE `impact_statistics` ADD foreign key(`project_id`) references `projects`(`id`) on delete restrict;
ALTER TABLE `impact_statistics` ADD foreign key(`verified_by`) references `users`(`id`) on delete set null;
ALTER TABLE `positions` ADD foreign key(`department_id`) references `departments`(`id`) on delete restrict;
ALTER TABLE `positions` ADD foreign key(`reports_to_position_id`) references `positions`(`id`) on delete set null;
ALTER TABLE `team_members` ADD foreign key(`department_id`) references `departments`(`id`) on delete set null;
ALTER TABLE `team_members` ADD foreign key(`position_id`) references `positions`(`id`) on delete set null;
ALTER TABLE `team_members` ADD foreign key(`user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `departments` ADD foreign key(`parent_id`) references departments(`id`) on delete set null on update no action;
ALTER TABLE `departments` ADD foreign key(`head_staff_id`) references `team_members`(`id`) on delete set null;
ALTER TABLE `department_programme` ADD foreign key(`department_id`) references `departments`(`id`) on delete cascade;
ALTER TABLE `department_programme` ADD foreign key(`programme_id`) references `programmes`(`id`) on delete cascade;
ALTER TABLE `department_project` ADD foreign key(`department_id`) references `departments`(`id`) on delete cascade;
ALTER TABLE `department_project` ADD foreign key(`project_id`) references `projects`(`id`) on delete cascade;
ALTER TABLE `contact_messages` ADD foreign key(`department_id`) references `departments`(`id`) on delete set null;
ALTER TABLE `contact_messages` ADD foreign key(`assigned_to_user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `media_assets` ADD foreign key(`uploaded_by`) references `users`(`id`) on delete set null;
ALTER TABLE `involvement_applications` ADD foreign key(`assigned_to_user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `donations` ADD foreign key(`programme_id`) references `programmes`(`id`) on delete set null;
ALTER TABLE `department_leadership` ADD foreign key(`department_id`) references `departments`(`id`) on delete cascade;
ALTER TABLE `department_leadership` ADD foreign key(`team_member_id`) references `team_members`(`id`) on delete cascade;
ALTER TABLE `user_departments` ADD foreign key(`user_id`) references `users`(`id`) on delete cascade;
ALTER TABLE `user_departments` ADD foreign key(`department_id`) references `departments`(`id`) on delete cascade;
ALTER TABLE `department_tasks` ADD foreign key(`department_id`) references `departments`(`id`) on delete restrict;
ALTER TABLE `department_tasks` ADD foreign key(`assigned_by_user_id`) references `users`(`id`) on delete restrict;
ALTER TABLE `department_tasks` ADD foreign key(`assigned_to_team_member_id`) references `team_members`(`id`) on delete set null;
ALTER TABLE `department_tasks` ADD foreign key(`assigned_to_user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `department_task_comments` ADD foreign key(`task_id`) references `department_tasks`(`id`) on delete cascade;
ALTER TABLE `department_task_comments` ADD foreign key(`user_id`) references `users`(`id`) on delete restrict;
ALTER TABLE `department_task_attachments` ADD foreign key(`task_id`) references `department_tasks`(`id`) on delete cascade;
ALTER TABLE `department_task_attachments` ADD foreign key(`uploaded_by_user_id`) references `users`(`id`) on delete restrict;
ALTER TABLE `approval_workflow_steps` ADD foreign key(`workflow_id`) references `approval_workflows`(`id`) on delete cascade;
ALTER TABLE `approval_workflow_steps` ADD foreign key(`specific_user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `approval_requests` ADD foreign key(`workflow_id`) references `approval_workflows`(`id`) on delete restrict;
ALTER TABLE `approval_requests` ADD foreign key(`department_id`) references `departments`(`id`) on delete set null;
ALTER TABLE `approval_requests` ADD foreign key(`requested_by_user_id`) references `users`(`id`) on delete restrict;
ALTER TABLE `approval_actions` ADD foreign key(`approval_request_id`) references `approval_requests`(`id`) on delete cascade;
ALTER TABLE `approval_actions` ADD foreign key(`workflow_step_id`) references `approval_workflow_steps`(`id`) on delete set null;
ALTER TABLE `approval_actions` ADD foreign key(`user_id`) references `users`(`id`) on delete restrict;
ALTER TABLE `department_activity_logs` ADD foreign key(`department_id`) references `departments`(`id`) on delete set null;
ALTER TABLE `department_activity_logs` ADD foreign key(`user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `financial_years` ADD foreign key(`closed_by_user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `accounts` ADD foreign key(`parent_id`) references `accounts`(`id`) on delete set null;
ALTER TABLE `bank_accounts` ADD foreign key(`account_id`) references `accounts`(`id`) on delete restrict;
ALTER TABLE `cash_accounts` ADD foreign key(`account_id`) references `accounts`(`id`) on delete restrict;
ALTER TABLE `cash_accounts` ADD foreign key(`department_id`) references `departments`(`id`) on delete set null;
ALTER TABLE `cash_accounts` ADD foreign key(`custodian_team_member_id`) references `team_members`(`id`) on delete set null;
ALTER TABLE `finance_audit_logs` ADD foreign key(`user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `users` ADD foreign key(`team_member_id`) references `team_members`(`id`) on delete set null;
ALTER TABLE `projects` ADD foreign key(`district_id`) references districts(`id`) on delete restrict on update no action;
ALTER TABLE `projects` ADD foreign key(`region_id`) references regions(`id`) on delete restrict on update no action;
ALTER TABLE `projects` ADD foreign key(`programme_id`) references programmes(`id`) on delete restrict on update no action;
ALTER TABLE `projects` ADD foreign key(`project_manager_team_member_id`) references `team_members`(`id`) on delete set null;
ALTER TABLE `project_activities` ADD foreign key(`project_id`) references `projects`(`id`) on delete cascade;
ALTER TABLE `project_activities` ADD foreign key(`department_id`) references `departments`(`id`) on delete set null;
ALTER TABLE `project_activities` ADD foreign key(`responsible_team_member_id`) references `team_members`(`id`) on delete set null;
ALTER TABLE `project_milestones` ADD foreign key(`project_id`) references `projects`(`id`) on delete cascade;
ALTER TABLE `project_milestones` ADD foreign key(`responsible_user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `project_risks` ADD foreign key(`project_id`) references `projects`(`id`) on delete cascade;
ALTER TABLE `project_risks` ADD foreign key(`owner_user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `reporting_periods` ADD foreign key(`financial_year_id`) references `financial_years`(`id`) on delete set null;
ALTER TABLE `performance_results` ADD foreign key(`performance_indicator_id`) references `performance_indicators`(`id`) on delete cascade;
ALTER TABLE `performance_results` ADD foreign key(`performance_target_id`) references `performance_targets`(`id`) on delete set null;
ALTER TABLE `performance_results` ADD foreign key(`reporting_period_id`) references `reporting_periods`(`id`) on delete cascade;
ALTER TABLE `performance_results` ADD foreign key(`department_id`) references `departments`(`id`) on delete set null;
ALTER TABLE `performance_results` ADD foreign key(`programme_id`) references `programmes`(`id`) on delete set null;
ALTER TABLE `performance_results` ADD foreign key(`project_id`) references `projects`(`id`) on delete set null;
ALTER TABLE `performance_results` ADD foreign key(`team_member_id`) references `team_members`(`id`) on delete set null;
ALTER TABLE `performance_results` ADD foreign key(`entered_by_user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `performance_results` ADD foreign key(`verified_by_user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `user_invitations` ADD foreign key(`team_member_id`) references `team_members`(`id`) on delete set null;
ALTER TABLE `user_invitations` ADD foreign key(`invited_by_user_id`) references `users`(`id`) on delete restrict;
ALTER TABLE `user_invitations` ADD foreign key(`role_id`) references `roles`(`id`) on delete set null;
ALTER TABLE `user_invitations` ADD foreign key(`primary_department_id`) references `departments`(`id`) on delete set null;
ALTER TABLE `user_invitation_departments` ADD foreign key(`user_invitation_id`) references `user_invitations`(`id`) on delete cascade;
ALTER TABLE `user_invitation_departments` ADD foreign key(`department_id`) references `departments`(`id`) on delete cascade;
ALTER TABLE `results_framework_items` ADD foreign key(`programme_id`) references `programmes`(`id`) on delete set null;
ALTER TABLE `results_framework_items` ADD foreign key(`project_id`) references `projects`(`id`) on delete set null;
ALTER TABLE `results_framework_items` ADD foreign key(`parent_id`) references `results_framework_items`(`id`) on delete set null;
ALTER TABLE `performance_indicators` ADD foreign key(`project_id`) references projects(`id`) on delete set null on update no action;
ALTER TABLE `performance_indicators` ADD foreign key(`programme_id`) references programmes(`id`) on delete set null on update no action;
ALTER TABLE `performance_indicators` ADD foreign key(`department_id`) references departments(`id`) on delete set null on update no action;
ALTER TABLE `performance_indicators` ADD foreign key(`results_framework_item_id`) references `results_framework_items`(`id`) on delete set null;
ALTER TABLE `performance_indicators` ADD foreign key(`responsible_user_id`) references `users`(`id`) on delete set null;
ALTER TABLE `performance_targets` ADD foreign key(`district_id`) references districts(`id`) on delete set null on update no action;
ALTER TABLE `performance_targets` ADD foreign key(`performance_indicator_id`) references performance_indicators(`id`) on delete cascade on update no action;
ALTER TABLE `performance_targets` ADD foreign key(`reporting_period_id`) references reporting_periods(`id`) on delete cascade on update no action;
ALTER TABLE `performance_targets` ADD foreign key(`department_id`) references departments(`id`) on delete set null on update no action;
ALTER TABLE `performance_targets` ADD foreign key(`programme_id`) references programmes(`id`) on delete set null on update no action;
ALTER TABLE `performance_targets` ADD foreign key(`project_id`) references projects(`id`) on delete set null on update no action;
ALTER TABLE `performance_targets` ADD foreign key(`team_member_id`) references team_members(`id`) on delete set null on update no action;
ALTER TABLE `performance_targets` ADD foreign key(`created_by_user_id`) references `users`(`id`) on delete set null;

-- Indexes
CREATE INDEX `sessions_user_id_index` on `sessions` (`user_id`);
CREATE INDEX `sessions_last_activity_index` on `sessions` (`last_activity`);
CREATE INDEX `cache_expiration_index` on `cache` (`expiration`);
CREATE INDEX `cache_locks_expiration_index` on `cache_locks` (`expiration`);
CREATE INDEX `jobs_queue_index` on `jobs` (`queue`);
CREATE UNIQUE INDEX `failed_jobs_uuid_unique` on `failed_jobs` (`uuid`);
CREATE UNIQUE INDEX `permissions_name_guard_name_unique` on `permissions` (`name`, `guard_name`);
CREATE UNIQUE INDEX `roles_name_guard_name_unique` on `roles` (`name`, `guard_name`);
CREATE INDEX `model_has_permissions_model_id_model_type_index` on `model_has_permissions` (`model_id`, `model_type`);
CREATE INDEX `model_has_roles_model_id_model_type_index` on `model_has_roles` (`model_id`, `model_type`);
CREATE UNIQUE INDEX `site_settings_key_unique` on `site_settings` (`key`);
CREATE UNIQUE INDEX `programmes_slug_unique` on `programmes` (`slug`);
CREATE INDEX `programmes_display_order_index` on `programmes` (`display_order`);
CREATE INDEX `programmes_is_active_index` on `programmes` (`is_active`);
CREATE UNIQUE INDEX `countries_code_unique` on `countries` (`code`);
CREATE UNIQUE INDEX `countries_slug_unique` on `countries` (`slug`);
CREATE INDEX `countries_is_active_index` on `countries` (`is_active`);
CREATE UNIQUE INDEX `regions_country_id_name_unique` on `regions` (`country_id`, `name`);
CREATE UNIQUE INDEX `regions_slug_unique` on `regions` (`slug`);
CREATE INDEX `regions_is_active_index` on `regions` (`is_active`);
CREATE UNIQUE INDEX `districts_region_id_name_unique` on `districts` (`region_id`, `name`);
CREATE UNIQUE INDEX `districts_slug_unique` on `districts` (`slug`);
CREATE INDEX `districts_is_active_index` on `districts` (`is_active`);
CREATE INDEX `impact_statistics_is_active_verified_at_index` on `impact_statistics` (`is_active`, `verified_at`);
CREATE INDEX `impact_statistics_metric_index` on `impact_statistics` (`metric`);
CREATE INDEX `impact_statistics_as_of_date_index` on `impact_statistics` (`as_of_date`);
CREATE INDEX `positions_department_id_active_display_order_index` on `positions` (`department_id`, `active`, `display_order`);
CREATE INDEX `positions_display_order_index` on `positions` (`display_order`);
CREATE INDEX `positions_active_index` on `positions` (`active`);
CREATE INDEX `team_members_department_id_active_display_order_index` on `team_members` (`department_id`, `active`, `display_order`);
CREATE UNIQUE INDEX `team_members_slug_unique` on `team_members` (`slug`);
CREATE INDEX `team_members_display_order_index` on `team_members` (`display_order`);
CREATE INDEX `team_members_show_on_website_index` on `team_members` (`show_on_website`);
CREATE INDEX `team_members_active_index` on `team_members` (`active`);
CREATE INDEX `departments_accepts_public_messages_index` on `departments` (`accepts_public_messages`);
CREATE INDEX `departments_active_index` on `departments` (`active`);
CREATE UNIQUE INDEX `departments_code_unique` on `departments` (`code`);
CREATE INDEX `departments_display_order_index` on `departments` (`display_order`);
CREATE INDEX `departments_parent_id_active_display_order_index` on `departments` (`parent_id`, `active`, `display_order`);
CREATE INDEX `departments_show_on_website_index` on `departments` (`show_on_website`);
CREATE UNIQUE INDEX `departments_slug_unique` on `departments` (`slug`);
CREATE UNIQUE INDEX `department_programme_department_id_programme_id_unique` on `department_programme` (`department_id`, `programme_id`);
CREATE UNIQUE INDEX `department_project_department_id_project_id_unique` on `department_project` (`department_id`, `project_id`);
CREATE INDEX `department_project_project_id_is_lead_index` on `department_project` (`project_id`, `is_lead`);
CREATE INDEX `contact_messages_department_id_status_index` on `contact_messages` (`department_id`, `status`);
CREATE INDEX `contact_messages_status_index` on `contact_messages` (`status`);
CREATE UNIQUE INDEX `content_pages_path_unique` on `content_pages` (`path`);
CREATE INDEX `content_pages_is_published_index` on `content_pages` (`is_published`);
CREATE INDEX `content_pages_display_order_index` on `content_pages` (`display_order`);
CREATE UNIQUE INDEX `partners_slug_unique` on `partners` (`slug`);
CREATE INDEX `partners_is_featured_index` on `partners` (`is_featured`);
CREATE INDEX `partners_show_on_website_index` on `partners` (`show_on_website`);
CREATE INDEX `partners_active_index` on `partners` (`active`);
CREATE INDEX `partners_display_order_index` on `partners` (`display_order`);
CREATE INDEX `media_assets_collection_index` on `media_assets` (`collection`);
CREATE INDEX `media_assets_is_public_index` on `media_assets` (`is_public`);
CREATE INDEX `team_members_is_leader_index` on `team_members` (`is_leader`);
CREATE INDEX `editorial_items_type_index` on `editorial_items` (`type`);
CREATE UNIQUE INDEX `editorial_items_slug_unique` on `editorial_items` (`slug`);
CREATE INDEX `editorial_items_is_published_index` on `editorial_items` (`is_published`);
CREATE INDEX `editorial_items_published_at_index` on `editorial_items` (`published_at`);
CREATE INDEX `involvement_applications_type_index` on `involvement_applications` (`type`);
CREATE INDEX `involvement_applications_status_index` on `involvement_applications` (`status`);
CREATE INDEX `involvement_applications_submitted_at_index` on `involvement_applications` (`submitted_at`);
CREATE INDEX `donations_frequency_index` on `donations` (`frequency`);
CREATE INDEX `donations_purpose_index` on `donations` (`purpose`);
CREATE INDEX `donations_status_index` on `donations` (`status`);
CREATE INDEX `department_leadership_department_id_leadership_role_active_index` on `department_leadership` (`department_id`, `leadership_role`, `active`);
CREATE INDEX `department_leadership_team_member_id_active_index` on `department_leadership` (`team_member_id`, `active`);
CREATE UNIQUE INDEX `user_departments_user_id_department_id_unique` on `user_departments` (`user_id`, `department_id`);
CREATE INDEX `user_departments_department_id_active_index` on `user_departments` (`department_id`, `active`);
CREATE INDEX `department_tasks_department_id_status_due_date_index` on `department_tasks` (`department_id`, `status`, `due_date`);
CREATE INDEX `department_tasks_assigned_to_user_id_status_index` on `department_tasks` (`assigned_to_user_id`, `status`);
CREATE INDEX `department_tasks_due_date_index` on `department_tasks` (`due_date`);
CREATE UNIQUE INDEX `approval_workflows_code_unique` on `approval_workflows` (`code`);
CREATE INDEX `approval_workflows_module_index` on `approval_workflows` (`module`);
CREATE UNIQUE INDEX `approval_workflow_steps_workflow_id_step_order_unique` on `approval_workflow_steps` (`workflow_id`, `step_order`);
CREATE INDEX `approval_requests_approvable_type_approvable_id_index` on `approval_requests` (`approvable_type`, `approvable_id`);
CREATE INDEX `approval_requests_department_id_status_index` on `approval_requests` (`department_id`, `status`);
CREATE INDEX `approval_requests_status_index` on `approval_requests` (`status`);
CREATE INDEX `approval_actions_approval_request_id_created_at_index` on `approval_actions` (`approval_request_id`, `created_at`);
CREATE INDEX `department_activity_logs_subject_type_subject_id_index` on `department_activity_logs` (`subject_type`, `subject_id`);
CREATE INDEX `department_activity_logs_department_id_created_at_index` on `department_activity_logs` (`department_id`, `created_at`);
CREATE INDEX `department_activity_logs_event_index` on `department_activity_logs` (`event`);
CREATE INDEX `notifications_notifiable_type_notifiable_id_index` on `notifications` (`notifiable_type`, `notifiable_id`);
CREATE INDEX `financial_years_start_date_end_date_index` on `financial_years` (`start_date`, `end_date`);
CREATE UNIQUE INDEX `financial_years_name_unique` on `financial_years` (`name`);
CREATE INDEX `financial_years_status_index` on `financial_years` (`status`);
CREATE INDEX `accounts_parent_id_active_display_order_index` on `accounts` (`parent_id`, `active`, `display_order`);
CREATE UNIQUE INDEX `accounts_code_unique` on `accounts` (`code`);
CREATE INDEX `accounts_type_index` on `accounts` (`type`);
CREATE INDEX `accounts_active_index` on `accounts` (`active`);
CREATE UNIQUE INDEX `bank_accounts_account_id_unique` on `bank_accounts` (`account_id`);
CREATE INDEX `bank_accounts_currency_index` on `bank_accounts` (`currency`);
CREATE INDEX `bank_accounts_active_index` on `bank_accounts` (`active`);
CREATE INDEX `cash_accounts_department_id_active_index` on `cash_accounts` (`department_id`, `active`);
CREATE UNIQUE INDEX `cash_accounts_account_id_unique` on `cash_accounts` (`account_id`);
CREATE INDEX `cash_accounts_currency_index` on `cash_accounts` (`currency`);
CREATE INDEX `cash_accounts_active_index` on `cash_accounts` (`active`);
CREATE INDEX `finance_audit_logs_subject_type_subject_id_index` on `finance_audit_logs` (`subject_type`, `subject_id`);
CREATE INDEX `finance_audit_logs_created_at_event_index` on `finance_audit_logs` (`created_at`, `event`);
CREATE INDEX `finance_audit_logs_event_index` on `finance_audit_logs` (`event`);
CREATE UNIQUE INDEX `users_email_unique` on `users` (`email`);
CREATE INDEX `users_last_login_at_index` on `users` (`last_login_at`);
CREATE UNIQUE INDEX `users_username_unique` on `users` (`username`);
CREATE UNIQUE INDEX `users_team_member_id_unique` on `users` (`team_member_id`);
CREATE INDEX `users_status_index` on `users` (`status`);
CREATE INDEX `projects_is_featured_index` on `projects` (`is_featured`);
CREATE INDEX `projects_is_published_published_at_index` on `projects` (`is_published`, `published_at`);
CREATE UNIQUE INDEX `projects_slug_unique` on `projects` (`slug`);
CREATE INDEX `projects_start_date_index` on `projects` (`start_date`);
CREATE INDEX `projects_status_index` on `projects` (`status`);
CREATE UNIQUE INDEX `projects_code_unique` on `projects` (`code`);
CREATE INDEX `projects_grant_id_index` on `projects` (`grant_id`);
CREATE INDEX `project_activities_project_id_start_date_index` on `project_activities` (`project_id`, `start_date`);
CREATE INDEX `project_activities_status_index` on `project_activities` (`status`);
CREATE INDEX `project_activities_budget_line_id_index` on `project_activities` (`budget_line_id`);
CREATE INDEX `project_milestones_due_date_index` on `project_milestones` (`due_date`);
CREATE INDEX `project_milestones_status_index` on `project_milestones` (`status`);
CREATE INDEX `project_risks_risk_level_index` on `project_risks` (`risk_level`);
CREATE INDEX `project_risks_status_index` on `project_risks` (`status`);
CREATE INDEX `reporting_periods_start_date_end_date_index` on `reporting_periods` (`start_date`, `end_date`);
CREATE INDEX `reporting_periods_period_type_index` on `reporting_periods` (`period_type`);
CREATE INDEX `reporting_periods_status_index` on `reporting_periods` (`status`);
CREATE INDEX `perf_results_period_dept_status_idx` on `performance_results` (`reporting_period_id`, `department_id`, `status`);
CREATE INDEX `perf_results_period_prog_status_idx` on `performance_results` (`reporting_period_id`, `programme_id`, `status`);
CREATE INDEX `perf_results_period_project_status_idx` on `performance_results` (`reporting_period_id`, `project_id`, `status`);
CREATE INDEX `perf_results_kpi_period_status_idx` on `performance_results` (`performance_indicator_id`, `reporting_period_id`, `status`);
CREATE INDEX `perf_results_member_period_status_idx` on `performance_results` (`team_member_id`, `reporting_period_id`, `status`);
CREATE INDEX `performance_results_status_index` on `performance_results` (`status`);
CREATE INDEX `performance_rating_scales_minimum_score_maximum_score_index` on `performance_rating_scales` (`minimum_score`, `maximum_score`);
CREATE INDEX `performance_rating_scales_active_index` on `performance_rating_scales` (`active`);
CREATE INDEX `user_invitations_email_status_index` on `user_invitations` (`email`, `status`);
CREATE INDEX `user_invitations_email_index` on `user_invitations` (`email`);
CREATE INDEX `user_invitations_status_index` on `user_invitations` (`status`);
CREATE INDEX `user_invitations_expires_at_index` on `user_invitations` (`expires_at`);
CREATE INDEX `rf_items_scope_level_active_idx` on `results_framework_items` (`programme_id`, `project_id`, `level`, `active`);
CREATE INDEX `results_framework_items_level_index` on `results_framework_items` (`level`);
CREATE INDEX `results_framework_items_code_index` on `results_framework_items` (`code`);
CREATE INDEX `results_framework_items_active_index` on `results_framework_items` (`active`);
CREATE INDEX `performance_indicators_active_index` on `performance_indicators` (`active`);
CREATE UNIQUE INDEX `performance_indicators_code_unique` on `performance_indicators` (`code`);
CREATE INDEX `performance_indicators_data_source_index` on `performance_indicators` (`data_source`);
CREATE INDEX `performance_indicators_indicator_id_index` on `performance_indicators` (`indicator_id`);
CREATE INDEX `performance_indicators_level_department_id_active_index` on `performance_indicators` (`level`, `department_id`, `active`);
CREATE INDEX `performance_indicators_level_index` on `performance_indicators` (`level`);
CREATE INDEX `performance_indicators_level_programme_id_active_index` on `performance_indicators` (`level`, `programme_id`, `active`);
CREATE INDEX `performance_indicators_level_project_id_active_index` on `performance_indicators` (`level`, `project_id`, `active`);
CREATE INDEX `performance_indicators_indicator_type_index` on `performance_indicators` (`indicator_type`);
CREATE INDEX `perf_targets_kpi_period_idx` on `performance_targets` (`performance_indicator_id`, `reporting_period_id`);
CREATE INDEX `performance_targets_reporting_period_id_department_id_index` on `performance_targets` (`reporting_period_id`, `department_id`);
CREATE INDEX `performance_targets_reporting_period_id_programme_id_index` on `performance_targets` (`reporting_period_id`, `programme_id`);
CREATE INDEX `performance_targets_reporting_period_id_project_id_index` on `performance_targets` (`reporting_period_id`, `project_id`);

SET FOREIGN_KEY_CHECKS = 1;
