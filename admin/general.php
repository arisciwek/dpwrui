<?php
/**
* Path: /wp-content/plugins/dpwrui/admin/general.php
* Version: 2.0.0
* 
* Changelog:
* 2.0.0
* - Adjusted for new plugin structure
* - Converted to proper class structure
* - Added integration with main settings class
* - Improved form handling and validation
* 
* 1.0.2
* - Redesigned UI to match modern WordPress style
* - Added proper form handling
* - Improved settings registration
* - Fixed nonce verification
* - Added validation messages
*/

class DPW_RUI_General_Settings {
   
   private $parent_slug = 'dpw-rui-settings';
   private $validation;

   public function __construct(DPW_RUI_Validation $validation) {
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
       <textarea name="dpw_rui_alamat" 
                 id="dpw_rui_alamat"
                 rows="4" 
                 class="large-text"
                 ><?php echo esc_textarea($value); ?></textarea>
       <p class="description">Alamat lengkap kantor DPW RUI</p>
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
       <div class="wrap">
           <div style="max-width: 100%; background: #fff; border: 1px solid #ddd; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-top: 20px; padding: 20px;">
               <?php settings_errors('dpw_rui_messages'); ?>

               <h2 style="color: #2271b1; font-size: 16px; margin: 0 0 20px;">
                   Pengaturan Umum
               </h2>
               
               <form method="post" action="options.php">
                   <?php
                       settings_fields('dpw_rui_general_options');
                       do_settings_sections('dpw_rui_general');
                       submit_button('Simpan Pengaturan');
                   ?>
               </form>
           </div>

           <?php 
           // Render cleanup settings jika diperlukan
           global $dpw_rui_settings;
           if(method_exists($dpw_rui_settings, 'render_cleanup_settings')) {
               $dpw_rui_settings->render_cleanup_settings();
           }
           ?>
       </div>
       <?php
   }
}

// Initialize only when needed
function dpw_rui_init_general_settings() {
   global $pagenow, $dpw_rui_validation;
   if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'dpw-rui-settings') {
       global $dpw_rui_general_settings;
       if(!isset($dpw_rui_general_settings)) {
           $dpw_rui_general_settings = new DPW_RUI_General_Settings($dpw_rui_validation);
       }
       
       // Only render if we're on the general tab or no tab specified
       if (!isset($_GET['tab']) || $_GET['tab'] === 'umum') {
           $dpw_rui_general_settings->render_page();
       }
   }
}
add_action('admin_init', 'dpw_rui_init_general_settings');