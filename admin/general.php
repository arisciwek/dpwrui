<?php
/**
* Path: /wp-content/plugins/dpwrui/admin/general.php
* Version: 2.1.0
* 
* Changelog:
* 2.1.0
* - Removed redundant card wrappers since parent already provides card structure
* - Fixed form layout and spacing
* - Added proper form validation
* - Improved error handling and messages
* - Removed duplicate headings
* - Added proper field descriptions
* - Fixed nonce verification
* - Added loading states for form submission
* 
* 2.0.1
* - Previous version functionality
*/

class DPW_RUI_General_Settings {
   
    private $validation;

    public function __construct($validation = null) {
        if ($validation === null) {
            require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
            $validation = new DPW_RUI_Validation();
        }
        
        $this->validation = $validation;
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting(
            'dpw_rui_general_options',
            'dpw_rui_alamat',
            array(
                'type' => 'string',
                'description' => 'Alamat kantor DPW RUI',
                'sanitize_callback' => array($this, 'sanitize_alamat'),
                'show_in_rest' => false,
            )
        );

        add_settings_section(
            'dpw_rui_general_section',
            '', // Kosong karena header sudah ada di parent
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
        echo '<p class="mb-4">Pengaturan umum untuk konfigurasi dasar DPW RUI</p>';
    }

    public function alamat_callback() {
        $value = get_option('dpw_rui_alamat');
        ?>
        <div class="form-group">
            <textarea name="dpw_rui_alamat" 
                      id="dpw_rui_alamat"
                      rows="4" 
                      class="form-control"
                      aria-describedby="alamatHelp"
                      ><?php echo esc_textarea($value); ?></textarea>
            <small id="alamatHelp" class="form-text text-muted">
                Masukkan alamat lengkap kantor DPW RUI termasuk kode pos
            </small>
        </div>
        <?php
    }

    public function sanitize_alamat($input) {
        return sanitize_textarea_field($input);
    }

    public function render_page() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        // Handle form submission
        if (isset($_POST['submit'])) {
            check_admin_referer('dpw_rui_general_options-options');
            
            $alamat = isset($_POST['dpw_rui_alamat']) ? $this->sanitize_alamat($_POST['dpw_rui_alamat']) : '';
            update_option('dpw_rui_alamat', $alamat);
            
            add_settings_error(
                'dpw_rui_messages',
                'dpw_rui_message',
                __('Pengaturan berhasil disimpan.'),
                'updated'
            );
        }
        ?>
        <form method="post" action="options.php" class="needs-validation" novalidate>
            <?php
                settings_fields('dpw_rui_general_options');
                do_settings_sections('dpw_rui_general');
            ?>

            <div class="form-actions">
                <?php submit_button('Simpan Pengaturan', 'primary', 'submit', false); ?>
                
                <button type="reset" class="button button-secondary">
                    Reset
                </button>
            </div>
        </form>

        <script>
        jQuery(document).ready(function($) {
            // Form validation
            $('form.needs-validation').on('submit', function(e) {
                if (this.checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });

            // Reset form handler
            $('button[type="reset"]').on('click', function(e) {
                e.preventDefault();
                if (confirm('Apakah Anda yakin ingin mereset form ini? Perubahan yang belum disimpan akan hilang.')) {
                    $(this).closest('form').trigger('reset');
                }
            });

            // Loading state
            $('form').on('submit', function() {
                $('button[type="submit"]', this)
                    .prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
            });
        });
        </script>
        <?php
    }
}

// Initialize only when needed
function dpw_rui_init_general_settings() {
    global $pagenow;
    if ($pagenow === 'admin.php' && 
        isset($_GET['page']) && $_GET['page'] === 'dpw-rui-settings' &&
        (!isset($_GET['tab']) || $_GET['tab'] === 'umum')) {
        
        global $dpw_rui_general_settings;
        if (!isset($dpw_rui_general_settings)) {
            global $dpw_rui_validation;
            $dpw_rui_general_settings = new DPW_RUI_General_Settings($dpw_rui_validation);
        }
    }
}
add_action('admin_init', 'dpw_rui_init_general_settings');