/**
 * Path: /wp-content/plugins/dpwrui/includes/sql/create-foto-table.sql
 * Version: 1.0.0
 */

CREATE TABLE IF NOT EXISTS `{prefix}dpw_rui_anggota_foto` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `anggota_id` bigint(20) UNSIGNED NOT NULL,
    `attachment_id` bigint(20) UNSIGNED NOT NULL,
    `is_main` tinyint(1) NOT NULL DEFAULT '0',
    `created_at` datetime NOT NULL,
    `created_by` bigint(20) UNSIGNED NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `anggota_id` (`anggota_id`),
    KEY `attachment_id` (`attachment_id`),
    KEY `is_main` (`is_main`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;