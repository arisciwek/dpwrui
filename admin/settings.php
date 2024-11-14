<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/settings.php
 * Version: 2.3.3
 * 
 * Changelog:
 * 2.3.3
 * - Removed duplicate menu registration via add_settings_page()
 * - Menu registration now fully handled by DPW_RUI_Menu class
 * - Class now only handles settings logic and rendering
 * - Fixed double menu issue
 * 
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
        
        // No longer registering menu here - handled by DPW_RUI_Menu
    }

    /**
     * Callback for settings page render
     * Called from DPW_RUI_Menu class
     */
    public function render_settings_page() {
        require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
        DPW_RUI_General_Settings::get_instance($this->validation)->render_page();
    }
}