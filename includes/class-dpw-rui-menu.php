<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-menu.php
 * Version: 1.1.0
 * 
 * Changelog:
 * 1.1.0
 * - Fixed menu initialization to properly route main menu to member list
 * - Added proper menu capability checks
 * - Improved component loading sequence
 * - Added validation for menu access
 * - Added proper menu registration priorities
 * - Fixed settings page routing
 * - Added proper dependency loading
 * 
 * 1.0.3
 * - Previous version functionality
 */

class DPW_RUI_Menu {
    private $plugin_name;
    private $anggota;
    private $validation;
    private static $instance = null;

    public static function get_instance($plugin_name = '') {
        if (null === self::$instance) {
            self::$instance = new self($plugin_name);
        }
        return self::$instance;
    }

    private function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
        
        // Initialize validation
        require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
        $this->validation = new DPW_RUI_Validation();
        
        // Load components in correct order
        $this->load_dependencies();
        
        // Register menus with proper priority
        add_action('admin_menu', array($this, 'register_menus'), 10);
    }

    private function load_dependencies() {
        // Load anggota component if not already loaded
        global $dpw_rui_anggota;
        if (!isset($dpw_rui_anggota)) {
            require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-anggota.php';
            $dpw_rui_anggota = new DPW_RUI_Anggota($this->validation);
        }
        $this->anggota = $dpw_rui_anggota;
    }

    public function register_menus() {
        // Clear existing menu registrations
        $this->clear_existing_menus();
        
        // Register main menu (Daftar Anggota)
        add_menu_page(
            'DPW RUI',
            'DPW RUI',
            'dpw_rui_view_list',
            'dpw-rui',
            array($this, 'render_anggota_list'),
            'dashicons-groups',
            6
        );

        // Register submenus
        $this->register_submenus();
    }

    private function clear_existing_menus() {
        global $submenu;
        if (isset($submenu['dpw-rui'])) {
            unset($submenu['dpw-rui']);
        }
        remove_menu_page('dpw-rui');
    }

    private function register_submenus() {
        // Daftar Anggota (same as main menu)
        add_submenu_page(
            'dpw-rui',
            'Daftar Anggota',
            'Daftar Anggota',
            'dpw_rui_view_list',
            'dpw-rui',
            array($this, 'render_anggota_list')
        );

        // Tambah Anggota
        if (current_user_can('dpw_rui_create')) {
            add_submenu_page(
                'dpw-rui',
                'Tambah Anggota',
                'Tambah Anggota',
                'dpw_rui_create',
                'dpw-rui-add',
                array($this, 'render_add_anggota')
            );
        }

        // Settings (last)
        if (current_user_can('manage_options')) {
            add_submenu_page(
                'dpw-rui',
                'Pengaturan',
                'Pengaturan',
                'manage_options',
                'dpw-rui-settings',
                array($this, 'render_settings')
            );
        }
    }

    public function render_anggota_list() {
        if (!current_user_can('dpw_rui_view_list')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        if (!isset($this->anggota)) {
            wp_die(__('Komponen anggota tidak tersedia. Silakan hubungi administrator.'));
        }

        $this->anggota->handle_page_actions();
    }

    public function render_add_anggota() {
        if (!current_user_can('dpw_rui_create')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        if (!isset($this->anggota)) {
            wp_die(__('Komponen anggota tidak tersedia. Silakan hubungi administrator.'));
        }

        require_once DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-form.php';
    }

    public function render_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
        $settings = DPW_RUI_General_Settings::get_instance($this->validation);
        $settings->render_page();
    }
}
