<?php
/**
* Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-deactivator.php
* Version: 2.0.1
* 
* Changelog:
* 2.0.1
* - Fixed wp_clear_scheduled_hooks() undefined error
* - Added proper WordPress core file inclusion
* - Added checks for WordPress load state
* - Improved cleanup sequence
* - Added error handling for file operations
* 
* 2.0.0
* - Previous version functionality
*/

class DPW_RUI_Deactivator {

   public static function deactivate() {
       // Ensure WordPress core is loaded
       if (!function_exists('wp_clear_scheduled_hooks')) {
           require_once(ABSPATH . 'wp-includes/functions.php');
       }
       
       // Clean up transients
       delete_transient('dpw_rui_activation_error');
       
       // Get cleanup settings
       $remove_data = get_option('dpw_rui_remove_data_on_deactivate', false);
       
       if ($remove_data) {
           self::remove_plugin_data();
       }

       // Clear scheduled hooks safely
       if (function_exists('wp_clear_scheduled_hooks')) {
           wp_clear_scheduled_hooks('dpw_rui_cleanup_temp_files');
       }
   }

   private static function remove_plugin_data() {
       global $wpdb;
       
       // Drop tables
       $tables = array(
           $wpdb->prefix . 'dpw_rui_anggota',
           $wpdb->prefix . 'dpw_rui_anggota_foto'
       );

       foreach ($tables as $table) {
           $wpdb->query("DROP TABLE IF EXISTS $table");
       }

       // Remove plugin options
       $options = array(
           'dpw_rui_db_version',
           'dpw_rui_remove_data_on_deactivate',
           'dpw_rui_alamat'
       );

       foreach ($options as $option) {
           delete_option($option);
       }

       // Remove upload directory
       self::remove_upload_directory();
       
       // Remove attachment posts and meta
       $attachments = get_posts(array(
           'post_type' => 'attachment',
           'meta_key' => '_dpw_rui_attachment',
           'posts_per_page' => -1,
           'fields' => 'ids'
       ));

       if (!empty($attachments) && !is_wp_error($attachments)) {
           foreach ($attachments as $attachment_id) {
               wp_delete_attachment($attachment_id, true);
           }
       }

       // Remove user capabilities
       self::remove_capabilities();
   }

   private static function remove_upload_directory() {
       $upload_dir = wp_upload_dir();
       $dpw_rui_dir = $upload_dir['basedir'] . '/dpw-rui';
       
       if (file_exists($dpw_rui_dir)) {
           self::recursive_remove_directory($dpw_rui_dir);
       }
   }

   private static function recursive_remove_directory($directory) {
       if (is_dir($directory)) {
           $objects = @scandir($directory);
           if ($objects !== false) {
               foreach ($objects as $object) {
                   if ($object != "." && $object != "..") {
                       $path = $directory . DIRECTORY_SEPARATOR . $object;
                       if (is_dir($path)) {
                           self::recursive_remove_directory($path);
                       } else {
                           @unlink($path);
                       }
                   }
               }
           }
           @rmdir($directory);
       }
   }

   private static function remove_capabilities() {
       $roles = array('administrator', 'dpw_rui_operator', 'dpw_rui_member');
       $capabilities = array(
           'dpw_rui_create',
           'dpw_rui_read',
           'dpw_rui_update',
           'dpw_rui_delete',
           'dpw_rui_edit_own',
           'dpw_rui_view_list',
           'dpw_rui_manage_all'
       );

       foreach ($roles as $role_name) {
           $role = get_role($role_name);
           if ($role) {
               foreach ($capabilities as $cap) {
                   $role->remove_cap($cap);
               }
           }
       }

       // Remove custom roles
       remove_role('dpw_rui_operator');
       remove_role('dpw_rui_member');
   }

   /**
    * Run cleanup before plugin files are deleted
    */
   public static function pre_uninstall_cleanup() {
       // Ensure WordPress is loaded
       if (!function_exists('get_role')) {
           require_once(ABSPATH . 'wp-includes/pluggable.php');
       }
       
       self::remove_plugin_data();

       // Remove any additional plugin data
       $meta_keys = array(
           '_dpw_rui_attachment',
           '_dpw_rui_user'
       );

       global $wpdb;
       foreach ($meta_keys as $meta_key) {
           $wpdb->delete($wpdb->postmeta, array('meta_key' => $meta_key));
       }
   }
}