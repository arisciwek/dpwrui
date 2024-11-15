<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/settings.php
 * Version: 2.3.3
 * 
 * Changelog:
 * 2.3.3
 * - Perbaikan kapabilitas menu untuk administrator
 * - Menambahkan filter untuk kapabilitas menu
 * - Menambahkan pengecekan role
 * 
 * 2.3.2
 * - Removed render logic (moved to general.php)
 * - Simplified to basic menu registration
 * - Added proper menu callback
 */

class DPW_RUI_Settings {
    private $validation;

    public function __construct() {
        if (!isset($this->validation)) {
            require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
            $this->validation = new DPW_RUI_Validation();
        }

        add_action('admin_menu', array($this, 'add_settings_page'));
        // Tambahkan filter untuk mengizinkan admin
        add_filter('user_has_cap', array($this, 'add_admin_capabilities'), 10, 3);
    }

    public function add_settings_page() {
        // Ubah kapabilitas yang dibutuhkan
        $capability = current_user_can('administrator') ? 'manage_options' : 'dpw_rui_view_list';
        
        add_submenu_page(
            'dpw-rui',
            'Pengaturan DPW RUI',
            'Pengaturan',
            $capability,
            'dpw-rui-settings',
            array($this, 'settings_page_callback')
        );
    }

    public function add_admin_capabilities($allcaps, $caps, $args) {
        if (isset($allcaps['administrator']) && $allcaps['administrator']) {
            // Berikan semua kapabilitas DPW RUI ke admin
            foreach ($caps as $cap) {
                if (strpos($cap, 'dpw_rui_') === 0) {
                    $allcaps[$cap] = true;
                }
            }
        }
        return $allcaps;
    }

    public function settings_page_callback() {
        require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
        DPW_RUI_General_Settings::get_instance($this->validation);
    }
}