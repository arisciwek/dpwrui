<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/settings.php
 * Version: 2.3.0
 * 
 * Changelog:
 * 2.3.0
 * - Fixed roles tab not displaying by properly initializing roles settings
 * - Added proper roles class instantiation
 * - Fixed tab switching logic
 * - Improved error handling for roles management
 * - Added roles validation
 * - Fixed cleanup settings position when in roles tab
 * 
 * 2.2.0
 * - Previous version functionality
 */

class DPW_RUI_Settings {
    private $active_tab;
    private $validation;
    private $tabs = array(
        'umum' => 'Umum',
        'layanan' => 'Layanan',
        'roles' => 'Role Management'
    );

    public function __construct() {
        $this->active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'umum';
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Initialize components on construct
        $this->init_components();
    }

    private function init_components() {
        // Initialize validation if not exists
        if (!isset($this->validation)) {
            require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
            $this->validation = new DPW_RUI_Validation();
        }

        // Initialize roles settings
        require_once DPW_RUI_PLUGIN_DIR . 'admin/roles.php';
        global $dpw_rui_roles_settings;
        if (!isset($dpw_rui_roles_settings)) {
            $dpw_rui_roles_settings = new DPW_RUI_Roles_Settings($this->validation);
        }
    }

    public function enqueue_assets() {
        wp_enqueue_script('jquery');
        wp_enqueue_style('dpw-rui-admin');
    }

    public function register_settings() {
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

            <div class="card col-lg-8 shadow mb-4">
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
                    <?php
                    try {
                        switch($this->active_tab) {
                            case 'umum':
                                $this->render_general_tab();
                                break;
                            case 'layanan':
                                $this->render_services_tab();
                                break;
                            case 'roles':
                                $this->render_roles_tab();
                                break;
                        }
                    } catch (Exception $e) {
                        echo '<div class="notice notice-error"><p>Error: ' . esc_html($e->getMessage()) . '</p></div>';
                    }
                    ?>
                </div>
            </div>
            
            <?php if ($this->active_tab === 'umum'): ?>
                <?php $this->render_cleanup_settings(); ?>
            <?php endif; ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Form submission loading state
            $('form').on('submit', function() {
                $(this).addClass('dpw-rui-loading');
                $('button, input[type="submit"]', this).prop('disabled', true);
            });

            // Auto-hide success messages
            setTimeout(function() {
                $('.notice-success').slideUp();
            }, 3000);

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

    private function render_general_tab() {
        require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
        global $dpw_rui_general_settings;
        if (isset($dpw_rui_general_settings)) {
            $dpw_rui_general_settings->render_page();
        }
    }

    private function render_services_tab() {
        require_once DPW_RUI_PLUGIN_DIR . 'admin/services.php';
        global $dpw_rui_services_settings;
        if (isset($dpw_rui_services_settings)) {
            $dpw_rui_services_settings->render_page();
        }
    }

    private function render_roles_tab() {
        global $dpw_rui_roles_settings;
        if (isset($dpw_rui_roles_settings)) {
            $dpw_rui_roles_settings->render_page();
        } else {
            throw new Exception('Role settings component not initialized properly');
        }
    }

    public function render_cleanup_settings() {
        ?>
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Pembersihan Data</h6>
            </div>
            <div class="card-body">
                <form method="post" action="options.php" class="needs-validation" novalidate>
                    <?php settings_fields('dpw_rui_general_options'); ?>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" 
                                   class="custom-control-input" 
                                   id="dpw_rui_remove_data_on_deactivate"
                                   name="dpw_rui_remove_data_on_deactivate" 
                                   value="1" 
                                   <?php checked(get_option('dpw_rui_remove_data_on_deactivate')); ?>>
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

                    <?php submit_button('Simpan Pengaturan'); ?>
                </form>
            </div>
        </div>
        <?php
    }
}