<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-assets.php
 * Version: 1.0.0
 * 
 * Changelog:
 * 1.0.0
 * - Extracted assets management from class-dpw-rui.php
 * - Handles enqueue styles and scripts
 */

class DPW_RUI_Assets {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            array(),
            '5.15.4'
        );

        wp_enqueue_style(
            $this->plugin_name,
            DPW_RUI_PLUGIN_URL . 'admin/css/sb-admin-2.css',
            array(),
            $this->version,
            'all'
        );
        
        wp_enqueue_style(
            'dpw-rui-anggota',
            DPW_RUI_PLUGIN_URL . 'admin/css/anggota.css',
            array(),
            $this->version
        );

        if (isset($_GET['action']) && $_GET['action'] == 'foto') {
            wp_enqueue_style(
                'dpw-rui-foto',
                DPW_RUI_PLUGIN_URL . 'admin/css/foto.css',
                array(),
                $this->version
            );
        }
    }

    public function enqueue_scripts() {
        wp_enqueue_script('jquery');

        wp_enqueue_script(
            'bootstrap',
            'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js',
            array('jquery'),
            '4.6.0',
            true
        );

        wp_enqueue_script(
            'jquery-easing',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js',
            array('jquery'),
            '1.4.1',
            true
        );

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            DPW_RUI_PLUGIN_URL . 'admin/js/dpw-rui-admin.js',
            array('jquery'),
            $this->version,
            true
        );

        if (isset($_GET['action']) && $_GET['action'] == 'foto') {
            wp_enqueue_script(
                'dpw-rui-foto',
                DPW_RUI_PLUGIN_URL . 'admin/js/foto.js',
                array('jquery'),
                $this->version,
                true
            );
        }
    }
}