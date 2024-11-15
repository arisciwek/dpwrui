<?php
/**
* Path: /wp-content/plugins/dpwrui/admin/general.php
* Version: 2.1.6
* 
* Changelog:
* 2.1.6
* - Moved settings render logic from settings.php
* - Added complete parent card structure
* - Added proper header and tab navigation
* - Improved layout consistency
* - Fixed initialization and render flow
* 
* 2.1.5
* - Previous version functionality
*/ 

class DPW_RUI_General_Settings {
    private $validation;
    private static $instance = null;
    private $active_tab;
    private $tabs = array(
        'umum' => 'Umum',
        'layanan' => 'Layanan',
        'roles' => 'Role Management'
    );

    public static function get_instance($validation = null) {
        if (null === self::$instance) {
            self::$instance = new self($validation);
        }
        return self::$instance;
    }

    private function __construct($validation = null) {
        if ($validation === null) {
            require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
            $validation = new DPW_RUI_Validation();
        }
        
        $this->validation = $validation;
        $this->active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'umum';
        $this->register_settings();
    }

    protected function register_settings() {
        // Alamat settings
        register_setting(
            'dpw_rui_general_options',
            'dpw_rui_alamat',
            array(
                'type' => 'string',
                'description' => 'Alamat kantor DPW RUI',
                'sanitize_callback' => array($this, 'sanitize_alamat'),
                'show_in_rest' => false,
                'default' => '',
            )
        );

        // Cleanup settings
        register_setting(
            'dpw_rui_general_options',
            'dpw_rui_remove_data_on_deactivate',
            array(
                'type' => 'boolean',
                'description' => 'Hapus data saat deaktivasi',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'show_in_rest' => false,
                'default' => false,
            )
        );

        // Alamat section
        add_settings_section(
            'dpw_rui_general_section',
            'Informasi Dasar',
            array($this, 'section_callback'),
            'dpw_rui_general'
        );

        // Cleanup section
        add_settings_section(
            'dpw_rui_cleanup_section',
            'Pembersihan Data',
            array($this, 'cleanup_section_callback'),
            'dpw_rui_general'
        );

        // Alamat field
        add_settings_field(
            'dpw_rui_alamat',
            'Alamat Kantor DPW RUI',
            array($this, 'alamat_callback'),
            'dpw_rui_general',
            'dpw_rui_general_section',
            array(
                'label_for' => 'dpw_rui_alamat',
                'class' => 'form-group'
            )
        );

        // Cleanup field
        add_settings_field(
            'dpw_rui_remove_data_on_deactivate',
            'Pengaturan Pembersihan',
            array($this, 'cleanup_callback'),
            'dpw_rui_general',
            'dpw_rui_cleanup_section',
            array(
                'label_for' => 'dpw_rui_remove_data_on_deactivate',
                'class' => 'form-group'
            )
        );
    }

    public function section_callback($args) {
        ?>
        <div class="alert alert-info mb-4">
            <h5 class="alert-heading mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                Pengaturan Umum DPW RUI
            </h5>
            <p class="mb-0">
                Silakan atur informasi dasar untuk DPW RUI di bawah ini.
            </p>
        </div>
        <?php
    }

    public function cleanup_section_callback($args) {
        ?>
        <div class="alert alert-warning mb-4">
            <h5 class="alert-heading mb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Pengaturan Pembersihan Data
            </h5>
            <p class="mb-0">
                Mengatur bagaimana data plugin akan ditangani saat plugin dinonaktifkan.
            </p>
        </div>
        <?php
    }

    public function alamat_callback($args) {
        $value = get_option('dpw_rui_alamat');
        ?>
        <div class="form-group">
            <textarea 
                name="dpw_rui_alamat" 
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

    public function cleanup_callback($args) {
        $value = get_option('dpw_rui_remove_data_on_deactivate');
        ?>
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" 
                       class="custom-control-input" 
                       id="dpw_rui_remove_data_on_deactivate"
                       name="dpw_rui_remove_data_on_deactivate" 
                       value="1" 
                       <?php checked($value); ?>>
                <label class="custom-control-label" for="dpw_rui_remove_data_on_deactivate">
                    Hapus semua data saat plugin dinonaktifkan
                </label>
            </div>
            <div class="mt-2">
                <small class="form-text text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Data yang sudah dihapus tidak dapat dikembalikan.
                </small>
            </div>
        </div>
        <?php
    }

    public function sanitize_alamat($input) {
        if(empty($input)) {
            add_settings_error(
                'dpw_rui_messages',
                'dpw_rui_alamat_error',
                'Alamat tidak boleh kosong',
                'error'
            );
            return get_option('dpw_rui_alamat');
        }
        
        return sanitize_textarea_field($input);
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
            <hr class="wp-header-end">

            <?php settings_errors('dpw_rui_messages'); ?>

            <div class="card shadow mb-4">
                <div class="card-header">
                    <h2 class="nav-tab-wrapper wp-clearfix">
                        <?php foreach ($this->tabs as $tab_key => $tab_label): ?>
                            <a href="<?php echo esc_url(add_query_arg(array(
                                'page' => 'dpw-rui-settings',
                                'tab' => $tab_key
                            ), admin_url('admin.php'))); ?>" 
                               class="nav-tab <?php echo $this->active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                                <?php echo esc_html($tab_label); ?>
                            </a>
                        <?php endforeach; ?>
                    </h2>
                </div>
                <div class="card-body">
                    <?php if ($this->active_tab === 'umum'): ?>
                        <form method="post" action="options.php" class="needs-validation" novalidate>
                            <?php
                                settings_fields('dpw_rui_general_options');
                                do_settings_sections('dpw_rui_general');
                            ?>
                            <div class="form-actions mt-4">
                                <?php submit_button('Simpan Pengaturan', 'primary', 'submit', false); ?>
                                <button type="reset" class="button button-secondary">Reset</button>
                            </div>
                        </form>
                    <?php elseif ($this->active_tab === 'layanan'): 
                        require_once DPW_RUI_PLUGIN_DIR . 'admin/services.php';
                        $services = new DPW_RUI_Services_Settings($this->validation);
                        $services->render_page();
                    ?>
                    <?php elseif ($this->active_tab === 'roles'): 
                        require_once DPW_RUI_PLUGIN_DIR . 'admin/roles.php';
                        $roles = new DPW_RUI_Roles_Settings($this->validation);
                        $roles->render_page();
                    ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

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

            // Tab switching confirmation if form is dirty
            $('.nav-tab').on('click', function(e) {
                var isDirty = $('form').serialize() !== $('form').data('serialized');
                if (isDirty) {
                    if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin pindah tab?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            });

            // Store initial form state
            $('form').each(function() {
                $(this).data('serialized', $(this).serialize());
            });
        });
        </script>
        <?php
    }
}

// Initialize when needed
add_action('admin_init', function() {
    global $pagenow;
    if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'dpw-rui-settings') {
        DPW_RUI_General_Settings::get_instance();
    }
});