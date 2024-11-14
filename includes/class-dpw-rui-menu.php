<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-menu.php
 * Version: 1.0.3
 * 
 * Changelog:
 * 1.0.3
 * - Fixed menu registration priority
 * - Force main menu to always show member list
 * - Added menu registration priority handling
 * - Fixed settings menu callback
 * - Added menu registration sequence fix
 * 
 * 1.0.2
 * - Previous version functionality
 */

class DPW_RUI_Menu {
    private $plugin_name;
    private $anggota;

    public function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
        
        // Ensure anggota component is loaded
        global $dpw_rui_anggota;
        if (!isset($dpw_rui_anggota)) {
            require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-anggota.php';
            require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
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
        // Unregister existing menus if any
        remove_menu_page('dpw-rui');
        remove_menu_page('dpw-rui-settings');
        
        // Menu utama HARUS mengarah ke daftar anggota
        add_menu_page(
            'DPW RUI',
            'DPW RUI',
            'dpw_rui_view_list',  // Changed permission level
            'dpw-rui', // Force this to be member list
            array($this, 'display_anggota_page'),
            'dashicons-groups',
            6
        );

        // Submenu Daftar Anggota (sama dengan menu utama)
        add_submenu_page(
            'dpw-rui',
            'Daftar Anggota',
            'Daftar Anggota',
            'dpw_rui_view_list',  // Changed permission level
            'dpw-rui', // Harus sama dengan menu utama
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

        // Settings harus ditambahkan terakhir
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

        require_once DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-form.php';
    }

    public function display_settings_page() {
        if(!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }
        
        require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
        $settings = DPW_RUI_General_Settings::get_instance();
        $settings->render_page();
    }
}