<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/general.php
 * Version: 1.0.3 
 *
 * Changelog:
 * 1.0.3
 * - Restrukturisasi kelas untuk bekerja dengan sistem settings terpusat
 * - Penambahan method render_content() untuk integrasi dengan settings.php
 * - Penghapusan duplikasi header dan navigasi
 * - Pemindahan logika settings ke constructor
 * - Perbaikan sanitasi dan validasi
 *
 * 1.0.2
 * - Redesigned UI to match modern WordPress style
 * - Added proper form handling
 * - Improved settings registration 
 * 
 * 1.0.1
 * - Initial release
 */

if (!defined('ABSPATH')) {
    exit;
}

class DPW_RUI_General_Settings {
    private $settings_group;
    private $settings_section;
    private $options;

    public function __construct() {
        $this->settings_group = 'dpw_rui_general_options';
        $this->settings_section = 'dpw_rui_general_section';
        
        $this->options = array(
            'dpw_rui_alamat' => array(
                'title' => 'Alamat Kantor DPW RUI',
                'type' => 'textarea',
                'description' => 'Alamat lengkap kantor DPW RUI'
            ),
            'dpw_rui_email' => array(
                'title' => 'Email',
                'type' => 'text',
                'description' => 'Email kontak DPW RUI'
            ),
            'dpw_rui_telpon' => array(
                'title' => 'Nomor Telepon',
                'type' => 'text',
                'description' => 'Nomor telepon yang dapat dihubungi'
            )
        );

        $this->init_settings();
    }

    private function init_settings() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        // Register setting group
        register_setting(
            $this->settings_group,
            'dpw_rui_alamat',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default' => ''
            )
        );

        register_setting(
            $this->settings_group,
            'dpw_rui_email',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_email',
                'default' => ''
            )
        );

        register_setting(
            $this->settings_group,
            'dpw_rui_telpon',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add settings section
        add_settings_section(
            $this->settings_section,
            'Pengaturan Umum',
            array($this, 'section_callback'),
            $this->settings_group
        );

        // Add settings fields
        foreach ($this->options as $id => $option) {
            add_settings_field(
                $id,
                $option['title'],
                array($this, 'render_field'),
                $this->settings_group,
                $this->settings_section,
                array(
                    'id' => $id,
                    'type' => $option['type'],
                    'description' => $option['description']
                )
            );
        }
    }

    public function section_callback() {
        echo '<p>Pengaturan dasar untuk DPW RUI. Semua pengaturan akan diterapkan ke seluruh sistem.</p>';
    }

    public function render_field($args) {
        $id = $args['id'];
        $type = $args['type'];
        $value = get_option($id);
        
        switch ($type) {
            case 'textarea':
                printf(
                    '<textarea name="%1$s" id="%1$s" rows="4" class="regular-text">%2$s</textarea>',
                    esc_attr($id),
                    esc_textarea($value)
                );
                break;
                
            case 'text':
            default:
                printf(
                    '<input type="text" name="%1$s" id="%1$s" value="%2$s" class="regular-text">',
                    esc_attr($id),
                    esc_attr($value)
                );
                break;
        }

        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    public function render_content() {
        // Check if settings were saved
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            add_settings_error(
                'dpw_rui_messages',
                'dpw_rui_message',
                __('Pengaturan berhasil disimpan.'),
                'updated'
            );
        }
        
        ?>
        <div class="card" style="max-width: 100%; background: #fff; border: 1px solid #ddd; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-top: 20px; padding: 20px;">
            <?php settings_errors('dpw_rui_messages'); ?>

            <h2 style="color: #2271b1; font-size: 16px; margin: 0 0 20px;">
                <span class="dashicons dashicons-admin-settings" style="font-size: 20px; margin-right: 5px;"></span>
                Pengaturan Umum DPW RUI
            </h2>

            <form method="post" action="options.php">
                <?php
                settings_fields($this->settings_group);
                do_settings_sections($this->settings_group);
                submit_button('Simpan Pengaturan');
                ?>
            </form>
        </div>
        <?php
    }
}