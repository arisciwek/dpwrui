<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-menu.php
 * Version: 1.0.4
 * 
 * Changelog:
 * 1.0.4
 * - Fixed file path resolution
 * - Added path existence checking
 * - Improved error handling for missing files
 * - Fixed form display logic
 * - Proper view file loading
 * 
 * 1.0.3
 * - Previous version functionality
 */

class DPW_RUI_Menu {
    private $plugin_name;
    private $anggota;
    private $plugin_dir;

    public function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
        $this->plugin_dir = plugin_dir_path(dirname(__FILE__)); // Get correct plugin directory path
        
        // Ensure anggota component is loaded
        global $dpw_rui_anggota;
        if (!isset($dpw_rui_anggota)) {
            require_once $this->plugin_dir . 'includes/class-dpw-rui-validation.php';
            require_once $this->plugin_dir . 'includes/class-dpw-rui-anggota.php';
            $validation = new DPW_RUI_Validation();
            $dpw_rui_anggota = new DPW_RUI_Anggota($validation);
        }
        $this->anggota = $dpw_rui_anggota;
        
        // Register menu with higher priority than settings
        add_action('admin_menu', array($this, 'add_admin_menu'), 9);
        
        // Remove any existing menu registrations
        remove_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        // Menu utama HARUS mengarah ke daftar anggota
        add_menu_page(
            'DPW RUI',
            'DPW RUI',
            'dpw_rui_view_list',
            'dpw-rui',
            array($this, 'display_anggota_page'),
            'dashicons-groups',
            6
        );

        // Submenu Daftar Anggota
        add_submenu_page(
            'dpw-rui',
            'Daftar Anggota',
            'Daftar Anggota',
            'dpw_rui_view_list',
            'dpw-rui',
            array($this, 'display_anggota_page')
        );

        if(current_user_can('dpw_rui_create')) {
            add_submenu_page(
                'dpw-rui',
                'Tambah Anggota',
                'Tambah Anggota',
                'dpw_rui_create',
                'dpw-rui-add',
                array($this, 'display_add_anggota_page')
            );
        }

        if(current_user_can('manage_options')) {
            add_submenu_page(
                'dpw-rui',
                'Pengaturan',
                'Pengaturan',
                'manage_options',
                'dpw-rui-settings',
                array($this, 'display_settings_page')
            );
        }
    }

    public function display_anggota_page() {
        if(!current_user_can('dpw_rui_read')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        if (!isset($this->anggota)) {
            wp_die(__('Komponen anggota tidak tersedia. Silakan hubungi administrator.'));
        }

        $this->anggota->handle_page_actions();
    }

    public function display_add_anggota_page() {
        if(!current_user_can('dpw_rui_create')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        if (!isset($this->anggota)) {
            wp_die(__('Komponen anggota tidak tersedia. Silakan hubungi administrator.'));
        }

        $view_file = $this->plugin_dir . 'admin/views/anggota-form.php';
        if (!file_exists($view_file)) {
            wp_die(sprintf(__('File view tidak ditemukan: %s'), $view_file));
        }
        require_once $view_file;
    }

    public function display_settings_page() {
        if(!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }
        
        require_once $this->plugin_dir . 'admin/general.php';
        $settings = DPW_RUI_General_Settings::get_instance();
        $settings->render_page();
    }

    private function load_view($view_name) {
        $view_file = $this->plugin_dir . 'admin/views/' . $view_name . '.php';
        if (!file_exists($view_file)) {
            wp_die(sprintf(__('File view tidak ditemukan: %s'), $view_file));
        }
        require_once $view_file;
    }
}