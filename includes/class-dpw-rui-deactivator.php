<?php
/**
* Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-deactivator.php
* Version: 2.0.0
* 
* Changelog:
* 2.0.0
* - Added proper cleanup for plugin deactivation
* - Added option to keep or remove data
* - Added cleanup for upload directory
* - Added transient cleanup
* 
* 1.0.0
* - Initial release with basic deactivation
*/

class DPW_RUI_Deactivator {

   public static function deactivate() {
       // Clean up transients
       delete_transient('dpw_rui_activation_error');
       
       // Get cleanup settings
       $remove_data = get_option('dpw_rui_remove_data_on_deactivate', false);
       
       if ($remove_data) {
           self::remove_plugin_data();
       }

       // Clear any scheduled hooks if exist
       wp_clear_scheduled_hooks('dpw_rui_cleanup_temp_files');
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

       foreach ($attachments as $attachment_id) {
           wp_delete_attachment($attachment_id, true);
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
           $objects = scandir($directory);
           foreach ($objects as $object) {
               if ($object != "." && $object != "..") {
                   if (is_dir($directory . DIRECTORY_SEPARATOR . $object)) {
                       self::recursive_remove_directory($directory . DIRECTORY_SEPARATOR . $object);
                   } else {
                       unlink($directory . DIRECTORY_SEPARATOR . $object);
                   }
               }
           }
           rmdir($directory);
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