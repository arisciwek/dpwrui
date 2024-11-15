<?php
/**
* Path: /wp-content/plugins/dpwrui/uninstall.php
* Version: 1.0.0
* 
* Uninstalls the DPW RUI plugin and removes all data
*/

// If not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

// Tables to remove
$tables = array(
    $wpdb->prefix . 'dpw_rui_anggota_foto',
    $wpdb->prefix . 'dpw_rui_anggota'
);

// Drop tables in correct order (respect foreign keys)
foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Remove options
delete_option('dpw_rui_db_version');
delete_option('dpw_rui_remove_data_on_deactivate');
delete_option('dpw_rui_alamat');

// Remove upload directory
$upload_dir = wp_upload_dir();
$dpw_rui_dir = $upload_dir['basedir'] . '/dpw-rui';

if (file_exists($dpw_rui_dir)) {
    recursive_rmdir($dpw_rui_dir);
}

// Helper function to recursively remove directories
function recursive_rmdir($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                if (is_dir("$dir/$file")) {
                    recursive_rmdir("$dir/$file");
                } else {
                    unlink("$dir/$file");
                }
            }
        }
        rmdir($dir);
    }
}

// Clear any transients
delete_transient('dpw_rui_activation_error');

// Remove user capabilities
$roles = array('administrator', 'dpw_rui_operator', 'dpw_rui_member');
$capabilities = array(
    'dpw_rui_create',
    'dpw_rui_read', 
    'dpw_rui_update',
    'dpw_rui_delete',
    'dpw_rui_edit_own',
    'dpw_rui_view_list'
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