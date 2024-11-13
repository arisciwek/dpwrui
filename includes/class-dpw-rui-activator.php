<?php
/**
* Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-activator.php
* Version: 2.0.0
* 
* Changelog:
* 2.0.0
* - Adjusted for new plugin class structure
* - Added proper error handling for each table creation
* - Separated table schema definitions
* - Added check for existing tables before creation
*
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
       $new_version = '2.0.0';
       
       if (version_compare($current_version, $new_version, '<')) {
           require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
           
           // Define table schemas
           $table_schemas = self::get_table_schemas($charset_collate);
           $results = array();
           
           // Create each table
           foreach ($table_schemas as $table_name => $schema) {
               // Check if table exists
               $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}$table_name'") === $wpdb->prefix . $table_name;
               
               if (!$table_exists) {
                   $result = dbDelta($schema);
                   if (!empty($wpdb->last_error)) {
                       $results[] = "Error creating table $table_name: " . $wpdb->last_error;
                   }
               }
           }

           // Check for errors
           if (empty($results)) {
               update_option('dpw_rui_db_version', $new_version);
           } else {
               error_log('DPW RUI Plugin Activation Errors: ' . print_r($results, true));
               set_transient('dpw_rui_activation_error', implode('<br>', $results), 30);
               return;
           }
       }
       
       // Create upload directory
       self::setup_upload_directory();
   }

   private static function get_table_schemas($charset_collate) {
       return array(
           'dpw_rui_anggota' => "CREATE TABLE `{$wpdb->prefix}dpw_rui_anggota` (
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
           ) $charset_collate;",

           'dpw_rui_anggota_foto' => "CREATE TABLE `{$wpdb->prefix}dpw_rui_anggota_foto` (
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
           ) $charset_collate;"
       );
   }

   private static function setup_upload_directory() {
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

       // Create index.php to prevent directory listing
       $index_file = $dpw_rui_dir . '/index.php';
       if (!file_exists($index_file)) {
           file_put_contents($index_file, '<?php // Silence is golden');
       }
   }

   public static function check_requirements() {
       global $wp_version;
       $requirements = array();

       // Check WordPress version
       if (version_compare($wp_version, '5.0', '<')) {
           $requirements[] = 'WordPress 5.0 atau lebih tinggi diperlukan';
       }

       // Check PHP version
       if (version_compare(PHP_VERSION, '7.0', '<')) {
           $requirements[] = 'PHP 7.0 atau lebih tinggi diperlukan';
       }

       // Check MySQL version
       global $wpdb;
       $mysql_version = $wpdb->db_version();
       if (version_compare($mysql_version, '5.6', '<')) {
           $requirements[] = 'MySQL 5.6 atau lebih tinggi diperlukan';
       }

       return $requirements;
   }
}