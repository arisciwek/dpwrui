<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-activator.php
 * Version: 1.0.1
 * 
 * Changelog:
 * 1.0.1
 * - Added foto table creation
 * - Added version check for table updates
 * - Improved SQL error handling
 * 
 * 1.0.0
 * - Initial release
 */

class DPW_RUI_Activator {

    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $current_version = get_option('dpw_rui_db_version', '0');
        $new_version = '1.0.1'; // Increment this when adding/modifying tables
        
        if (version_compare($current_version, $new_version, '<')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            // Create anggota table if not exists
            $sql_anggota = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dpw_rui_anggota` (
                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `nomor_anggota` varchar(20) NOT NULL,
                `nama_perusahaan` varchar(100) NOT NULL,
                `pimpinan` varchar(100) NOT NULL,
                `alamat` text NOT NULL,
                `kabupaten` varchar(50) NOT NULL,
                `kode_pos` varchar(10) DEFAULT NULL,
                `nomor_telpon` varchar(20) NOT NULL,
                `bidang_usaha` varchar(100) NOT NULL,
                `nomor_ahu` varchar(50) NOT NULL,
                `jabatan` varchar(50) DEFAULT NULL,
                `npwp` varchar(30) NOT NULL,
                `created_at` datetime NOT NULL,
                `created_by` bigint(20) UNSIGNED NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `nomor_anggota` (`nomor_anggota`),
                KEY `created_by` (`created_by`),
                KEY `updated_by` (`updated_by`)
            ) $charset_collate;";
            
            // Create foto table if not exists
            $sql_foto = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dpw_rui_anggota_foto` (
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
            ) $charset_collate;";
            
            // Execute SQL with error handling
            $results = array();
            
            // Create anggota table
            $result = dbDelta($sql_anggota);
            if (!empty($wpdb->last_error)) {
                $results[] = "Error creating anggota table: " . $wpdb->last_error;
            }
            
            // Create foto table  
            $result = dbDelta($sql_foto);
            if (!empty($wpdb->last_error)) {
                $results[] = "Error creating foto table: " . $wpdb->last_error;
            }

            // Update version if no errors
            if (empty($results)) {
                update_option('dpw_rui_db_version', $new_version);
            } else {
                // Log errors
                error_log('DPW RUI Plugin Activation Errors: ' . print_r($results, true));
                
                // Show admin notice
                set_transient(
                    'dpw_rui_activation_error', 
                    implode('<br>', $results), 
                    30
                );
            }
        }
        
        // Create upload directory if not exists
        $upload_dir = wp_upload_dir();
        $dpw_rui_dir = $upload_dir['basedir'] . '/dpw-rui';
        
        if (!file_exists($dpw_rui_dir)) {
            wp_mkdir_p($dpw_rui_dir);
        }
        
        // Create .htaccess to protect upload directory
        $htaccess = $dpw_rui_dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<Files *.php>\n";
            $htaccess_content .= "Order Deny,Allow\n";
            $htaccess_content .= "Deny from all\n";
            $htaccess_content .= "</Files>";
            
            file_put_contents($htaccess, $htaccess_content);
        }
    }
}