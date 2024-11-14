<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/settings.php
 * Version: 2.3.2
 * 
 * Changelog:
 * 2.3.2
 * - Removed render logic (moved to general.php)
 * - Simplified to basic menu registration
 * - Added proper menu callback
 * - Removed unused dependencies
 * - Removed redundant hooks
 * 
 * 2.3.1
 * - Previous version functionality
 */

class DPW_RUI_Settings {
    private $validation;

    public function __construct() {
        if (!isset($this->validation)) {
            require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
            $this->validation = new DPW_RUI_Validation();
        }

        add_action('admin_menu', array($this, 'add_settings_page'));
    }

    public function add_settings_page() {
        add_submenu_page(
            'dpw-rui', // Parent menu slug
            'Pengaturan DPW RUI', // Page title
            'Pengaturan', // Menu title
            'manage_options', // Capability required
            'dpw-rui-settings', // Menu slug
            array($this, 'settings_page_callback') // Callback function
        );
    }

    public function settings_page_callback() {
        // Just load the general settings class which now handles all rendering
        require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
        DPW_RUI_General_Settings::get_instance($this->validation);
    }
}