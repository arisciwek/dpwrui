<?php
/**
* Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui.php
* Version: 2.1.0
* 
* Changelog:
* 2.1.0
* - Added DPW_RUI_Post_Handler to dependencies
* - Added proper component initialization order
* - Fixed class loading sequence
* - Improved error handling for missing components
* - Added version checking for dependencies
* 
* 2.0.0
* - Previous version functionality
*/

class DPW_RUI {
    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $settings;
    protected $assets;
    protected $menu;
    protected $validation;
    protected $anggota;
    protected $foto;
    protected $post_handler;

    public function __construct() {
        $this->version = DPW_RUI_VERSION;
        $this->plugin_name = 'dpw-rui';
        
        $this->load_dependencies();
        $this->init_components();
        $this->define_hooks();
    }

    private function load_dependencies() {
        // Core components
        require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-admin-core.php';
        require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-assets.php';
        require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-menu.php';
        
        // Data handling
        require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
        require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-anggota.php';
        require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-foto.php';
        require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-post-handler.php'; // Added this line
        
        // Admin components
        require_once DPW_RUI_PLUGIN_DIR . 'admin/settings.php';
        require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
        require_once DPW_RUI_PLUGIN_DIR . 'admin/services.php';
        require_once DPW_RUI_PLUGIN_DIR . 'admin/roles.php';

        // Check if all required files exist
        $required_files = array(
            'class-dpw-rui-admin-core.php',
            'class-dpw-rui-assets.php',
            'class-dpw-rui-menu.php',
            'class-dpw-rui-validation.php',
            'class-dpw-rui-anggota.php',
            'class-dpw-rui-foto.php',
            'class-dpw-rui-post-handler.php'
        );

        foreach ($required_files as $file) {
            if (!file_exists(DPW_RUI_PLUGIN_DIR . 'includes/' . $file)) {
                // Log error and display admin notice
                error_log('DPW RUI Error: Required file ' . $file . ' not found');
                add_action('admin_notices', function() use ($file) {
                    ?>
                    <div class="notice notice-error">
                        <p><?php echo sprintf('DPW RUI Error: Required file %s not found. Plugin may not function correctly.', $file); ?></p>
                    </div>
                    <?php
                });
            }
        }
    }

    private function init_components() {
        try {
            // Initialize core components first
            $this->validation = new DPW_RUI_Validation();
            $this->settings = new DPW_RUI_Settings();
            $this->assets = new DPW_RUI_Assets($this->plugin_name, $this->version);
            
            // Initialize data handling components
            $this->anggota = new DPW_RUI_Anggota($this->validation);
            $this->foto = new DPW_RUI_Foto();
            $this->post_handler = new DPW_RUI_Post_Handler($this->anggota, $this->validation);
            
            // Initialize menu last as it depends on other components
            $this->menu = new DPW_RUI_Menu($this->plugin_name);

            // Make components available globally
            global $dpw_rui_settings, $dpw_rui_anggota;
            $dpw_rui_settings = $this->settings;
            $dpw_rui_anggota = $this->anggota;

        } catch (Exception $e) {
            error_log('DPW RUI Error: ' . $e->getMessage());
            add_action('admin_notices', function() use ($e) {
                ?>
                <div class="notice notice-error">
                    <p>Error initializing DPW RUI components: <?php echo esc_html($e->getMessage()); ?></p>
                </div>
                <?php
            });
        }
    }

    private function define_hooks() {
        add_action('admin_notices', array($this, 'activation_notice'));
        
        // Add version check
        add_action('admin_init', array($this, 'check_version'));
    }

    public function activation_notice() {
        if ($error = get_transient('dpw_rui_activation_error')) {
            ?>
            <div class="notice notice-error">
                <p>Error saat aktivasi plugin DPW RUI:</p>
                <p><?php echo wp_kses_post($error); ?></p>
            </div>
            <?php
            delete_transient('dpw_rui_activation_error');
        }
    }

    public function check_version() {
        if (version_compare($this->version, get_option('dpw_rui_version', '0'), '>')) {
            // Version has been updated, perform any necessary upgrades
            update_option('dpw_rui_version', $this->version);
        }
    }

    public function run() {
        do_action('dpw_rui_before_run');
        
        // Plugin is now fully initialized and running
        do_action('dpw_rui_after_run');
    }
}