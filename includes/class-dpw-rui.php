<?php
/**
* Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui.php
* Version: 2.0.0
* 
* Changelog:
* 2.0.0
* - Refactored into smaller classes
* - Now acts as main plugin bootstrap
* - Handles loading dependencies and initialization
* 
* 1.0.0
* - Initial monolithic version
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

   public function __construct() {
       $this->version = DPW_RUI_VERSION;
       $this->plugin_name = 'dpw-rui';
       
       $this->load_dependencies();
       $this->init_components();
       $this->define_hooks();
   }

   private function load_dependencies() {
       require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-admin-core.php';
       require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-assets.php';
       require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-menu.php';
       require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
       require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-anggota.php';
       require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-foto.php';
       require_once DPW_RUI_PLUGIN_DIR . 'admin/settings.php';
       require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
       require_once DPW_RUI_PLUGIN_DIR . 'admin/services.php';
       require_once DPW_RUI_PLUGIN_DIR . 'admin/roles.php';
   }

   private function init_components() {
       $this->settings = new DPW_RUI_Settings();
       $this->assets = new DPW_RUI_Assets($this->plugin_name, $this->version);
       $this->menu = new DPW_RUI_Menu($this->plugin_name);
       $this->validation = new DPW_RUI_Validation();
       $this->anggota = new DPW_RUI_Anggota($this->validation);
       $this->foto = new DPW_RUI_Foto();

       // Make components available globally
       global $dpw_rui_settings, $dpw_rui_anggota;
       $dpw_rui_settings = $this->settings;
       $dpw_rui_anggota = $this->anggota;
   }

   private function define_hooks() {
       add_action('admin_notices', array($this, 'activation_notice'));
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

   public function run() {
       // Plugin is now initialized and running
   }
}