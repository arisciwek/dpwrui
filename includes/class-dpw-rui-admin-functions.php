<?php
/**
 * Class for general settings functionality
 */
class DPW_RUI_Settings_General {
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting(
            'dpw_rui_general_options',
            'dpw_rui_alamat'
        );

        add_settings_section(
            'dpw_rui_general_section',
            'Pengaturan Umum',
            array($this, 'section_callback'),
            'dpw_rui_general'
        );

        add_settings_field(
            'dpw_rui_alamat',
            'Alamat Kantor DPW RUI',
            array($this, 'alamat_callback'),
            'dpw_rui_general',
            'dpw_rui_general_section'
        );
    }

    public function section_callback() {
        echo '<p>Pengaturan umum untuk DPW RUI</p>';
    }

    public function alamat_callback() {
        $value = get_option('dpw_rui_alamat');
        ?>
        <textarea name="dpw_rui_alamat" id="dpw_rui_alamat" 
                  class="form-control" rows="3"><?php 
            echo esc_textarea($value); 
        ?></textarea>
        <?php
    }

    public function render_page() {
        // Load admin core functionality
        DPW_RUI_Admin_Core::load_admin_functions();
        ?>
        <div class="wrap">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pengaturan Umum</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('dpw_rui_general_options');
                        do_settings_sections('dpw_rui_general');
                        submit_button('Simpan Pengaturan');
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize class only if we're on the settings page
if(isset($_GET['tab']) && $_GET['tab'] == 'general') {
    $general_settings = new DPW_RUI_Settings_General();
    $general_settings->render_page();
}