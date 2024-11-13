<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-menu.php
 * Version: 1.0.0
 * 
 * Changelog:
 * 1.0.0
 * - Extracted menu management from class-dpw-rui.php
 * - Handles admin menu registration and permissions
 */

class DPW_RUI_Menu {
    private $plugin_name;

    public function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'DPW RUI',
            'DPW RUI',
            'read',
            'dpw-rui',
            array($this, 'display_anggota_page'),
            'dashicons-groups',
            6
        );

        add_submenu_page(
            'dpw-rui',
            'Daftar Anggota',
            'Daftar Anggota',
            'read',
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
        if(!current_user_can('dpw_rui_view_list')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        global $dpw_rui_anggota;
        $dpw_rui_anggota->handle_page_actions();
    }

    public function display_add_anggota_page() {
        if(!current_user_can('dpw_rui_create')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        global $dpw_rui_anggota;
        $dpw_rui_anggota->display_add_form();
    }

    public function display_settings_page() {
        if(!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }
        
        global $dpw_rui_settings;
        $dpw_rui_settings->render_page();
    }
}