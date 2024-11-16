<?php
/**
* Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-activator.php
* Version: 2.2.1
* Timestamp: 2024-11-16 22:00:00
* 
* Changelog:
* 2.2.1 (2024-11-16)
* - Added recursive directory permissions fix
* - Added proper world-writable upload directory
* - Fixed foto table structure to match foto class
* - Added proper error handling for directory creation
* - Added proper logging for directory errors
* 
* 2.2.0
* - Previous version functionality
*/

class DPW_RUI_Activator {

   public static function activate() {
       global $wpdb;
       
       $wpdb->query('START TRANSACTION');

       try {
           // Create anggota table
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
           ) {$wpdb->get_charset_collate()}";

           $result = $wpdb->query($sql_anggota);
           if ($result === false) {
               throw new Exception('Failed to create anggota table: ' . $wpdb->last_error);
           }

           // Create foto table with proper structure for file handling
           $sql_foto = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dpw_rui_anggota_foto` (
               `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
               `anggota_id` bigint(20) UNSIGNED NOT NULL,
               `filename` varchar(255) NOT NULL,
               `file_path` varchar(255) NOT NULL,
               `file_url` varchar(255) NOT NULL,
               `file_type` varchar(50) NOT NULL,
               `file_size` int UNSIGNED NOT NULL,
               `is_main` tinyint(1) NOT NULL DEFAULT '0',
               `created_at` datetime NOT NULL,
               `created_by` bigint(20) UNSIGNED NOT NULL,
               `updated_at` datetime DEFAULT NULL,
               `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
               PRIMARY KEY (`id`),
               KEY `anggota_id` (`anggota_id`),
               KEY `is_main` (`is_main`),
               CONSTRAINT `fk_foto_anggota` FOREIGN KEY (`anggota_id`) 
               REFERENCES `{$wpdb->prefix}dpw_rui_anggota` (`id`) ON DELETE CASCADE
           ) {$wpdb->get_charset_collate()}";

           $result = $wpdb->query($sql_foto);
           if ($result === false) {
               throw new Exception('Failed to create foto table: ' . $wpdb->last_error);
           }

           $wpdb->query('COMMIT');
           update_option('dpw_rui_db_version', '2.2.4');

       } catch (Exception $e) {
           $wpdb->query('ROLLBACK');
           error_log('DPW RUI Activation Error: ' . $e->getMessage());
           set_transient('dpw_rui_activation_error', $e->getMessage(), 30);
       }

       self::setup_upload_directory();
   }
   
   private static function setup_upload_directory() {
       $upload_dir = wp_upload_dir();
       $dpw_rui_dir = $upload_dir['basedir'] . '/dpw-rui';
       
       // Create main upload directory if not exists
       if (!file_exists($dpw_rui_dir)) {
           if (!wp_mkdir_p($dpw_rui_dir)) {
               error_log('DPW RUI: Failed to create upload directory: ' . $dpw_rui_dir);
               return;
           }
           
           // Set proper permissions (world-writable for uploads)
           chmod($dpw_rui_dir, 0777);
       }
       
       // Create .htaccess
       $htaccess = $dpw_rui_dir . '/.htaccess';
       if (!file_exists($htaccess)) {
           file_put_contents($htaccess, 
               "Options -Indexes\n" .
               "<Files *.php>\n" .
               "Order Deny,Allow\n" .
               "Deny from all\n" .
               "</Files>");
           chmod($htaccess, 0644);
       }

       // Create index.php
       $index_file = $dpw_rui_dir . '/index.php';
       if (!file_exists($index_file)) {
           file_put_contents($index_file, '<?php // Silence is golden');
           chmod($index_file, 0644);
       }

       // Create test file to verify directory is writable
       $test_file = $dpw_rui_dir . '/test.txt';
       if (@file_put_contents($test_file, 'test') === false) {
           error_log('DPW RUI: Upload directory is not writable: ' . $dpw_rui_dir);
       } else {
           unlink($test_file);
       }
   }
}
