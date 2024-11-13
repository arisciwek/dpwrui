<?php
/**
* Path: /wp-content/plugins/dpwrui/admin/roles.php
* Version: 2.0.0
* 
* Changelog:
* 2.0.0
* - Converted to proper class structure
* - Added integration with main settings class
* - Improved capability management
* - Added role verification
* - Added proper initialization
* 
* 1.0.6
* - Fixed duplicate wrapper div
* - Fixed duplicate heading
* - Fixed duplicate tab navigation
* - Improved HTML structure
* - Cleaned up capabilities management
*/

class DPW_RUI_Roles_Settings {
   private $capabilities = array(
       'create' => 'Tambah Anggota',
       'read' => 'Lihat Detail Anggota',
       'update' => 'Edit Anggota', 
       'delete' => 'Hapus Anggota',
       'edit_own' => 'Edit Data Sendiri',
       'view_list' => 'Lihat Daftar Anggota'
   );

   private $roles = array(
       'administrator' => 'Administrator',
       'dpw_rui_operator' => 'Operator',
       'dpw_rui_member' => 'Member'
   );

   private $validation;

   public function __construct(DPW_RUI_Validation $validation) {
       $this->validation = $validation;
       add_action('admin_init', array($this, 'ensure_roles_exist'));
       add_action('admin_init', array($this, 'ensure_admin_capabilities'));
   }

   public function ensure_roles_exist() {
       // Skip administrator as it's WordPress default
       unset($this->roles['administrator']);

       foreach ($this->roles as $role_id => $role_name) {
           $role = get_role($role_id);
           if (!$role) {
               add_role($role_id, $role_name);
           }
       }
   }

   public function ensure_admin_capabilities() {
       $admin_role = get_role('administrator');
       if ($admin_role) {
           foreach ($this->capabilities as $cap_id => $cap_name) {
               $admin_role->add_cap('dpw_rui_' . $cap_id);
           }
           $admin_role->add_cap('dpw_rui_manage_all');
       }
   }

   public function render_page() {
       if (!current_user_can('manage_options')) {
           wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
       }

       if (isset($_POST['submit']) && check_admin_referer('dpw_rui_save_capabilities')) {
           $this->save_capabilities();
       }
       
       ?>
       <div class="card" style="max-width: 100%; background: #fff; border: 1px solid #ddd; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-top: 20px; padding: 20px;">
           <h2 style="color: #2271b1; font-size: 16px; margin: 0 0 20px;">
               <span class="dashicons dashicons-admin-users" style="font-size: 20px; margin-right: 5px;"></span>
               Pengaturan Role dan Capability
           </h2>

           <div style="background: #f0f6fc; border-left: 4px solid #72aee6; padding: 12px; margin-bottom: 20px;">
               <p style="margin: 0;">Administrator secara otomatis memiliki akses penuh ke semua fitur.</p>
           </div>
           
           <form method="post" action="">
               <?php wp_nonce_field('dpw_rui_save_capabilities'); ?>
               
               <table style="width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff;">
                   <thead>
                       <tr style="background: #f8f9fa;">
                           <th style="text-align: left; padding: 12px; border: 1px solid #dee2e6;">Capability</th>
                           <?php foreach($this->roles as $role_id => $role_name): ?>
                               <?php if ($role_id !== 'administrator'): ?>
                                   <th style="text-align: center; padding: 12px; border: 1px solid #dee2e6; width: 150px;">
                                       <?php echo esc_html($role_name); ?>
                                   </th>
                               <?php endif; ?>
                           <?php endforeach; ?>
                       </tr>
                   </thead>
                   <tbody>
                       <?php foreach($this->capabilities as $cap_id => $cap_name): ?>
                           <tr>
                               <td style="padding: 12px; border: 1px solid #dee2e6;">
                                   <?php echo esc_html($cap_name); ?>
                               </td>
                               <?php foreach($this->roles as $role_id => $role_name): 
                                   if ($role_id === 'administrator') continue;
                                   
                                   $role = get_role($role_id);
                                   $checked = $role && $role->has_cap('dpw_rui_' . $cap_id);
                                   ?>
                                   <td style="text-align: center; padding: 12px; border: 1px solid #dee2e6;">
                                       <input type="checkbox"
                                              name="capabilities[<?php echo esc_attr($role_id); ?>][]" 
                                              value="<?php echo esc_attr($cap_id); ?>"
                                              style="width: 16px; height: 16px;"
                                              <?php checked($checked); ?>>
                                   </td>
                               <?php endforeach; ?>
                           </tr>
                       <?php endforeach; ?>
                   </tbody>
               </table>

               <div style="margin-top: 20px;">
                   <button type="submit" name="submit" class="button button-primary">
                       Simpan Pengaturan
                   </button>
               </div>
           </form>
       </div>
       <?php
   }

   private function save_capabilities() {
       if (!current_user_can('manage_options')) {
           wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
       }

       $capabilities = isset($_POST['capabilities']) ? (array) $_POST['capabilities'] : array();

       foreach($this->roles as $role_id => $role_name) {
           if($role_id === 'administrator') continue;

           $role = get_role($role_id);
           if(!$role) continue;
           
           // Reset capabilities
           foreach($this->capabilities as $cap_id => $cap_name) {
               $role->remove_cap('dpw_rui_' . $cap_id);
           }

           // Set new capabilities
           if(isset($capabilities[$role_id])) {
               foreach($capabilities[$role_id] as $cap) {
                   $role->add_cap('dpw_rui_' . sanitize_key($cap));
               }
           }
       }

       // Ensure administrator retains all capabilities
       $this->ensure_admin_capabilities();

       add_settings_error(
           'dpw_rui_messages',
           'dpw_rui_message',
           __('Pengaturan role berhasil disimpan'),
           'updated'
       );
   }

   public function get_role_capabilities($role_id) {
       $role = get_role($role_id);
       if (!$role) {
           return array();
       }

       $capabilities = array();
       foreach ($this->capabilities as $cap_id => $cap_name) {
           if ($role->has_cap('dpw_rui_' . $cap_id)) {
               $capabilities[] = $cap_id;
           }
       }

       return $capabilities;
   }
}

// Initialize only when needed
function dpw_rui_init_roles_settings() {
   global $pagenow, $dpw_rui_validation;
   if ($pagenow === 'admin.php' && 
       isset($_GET['page']) && $_GET['page'] === 'dpw-rui-settings' &&
       isset($_GET['tab']) && $_GET['tab'] === 'roles') {
       
       global $dpw_rui_roles_settings;
       if(!isset($dpw_rui_roles_settings)) {
           $dpw_rui_roles_settings = new DPW_RUI_Roles_Settings($dpw_rui_validation);
       }
   }
}
add_action('admin_init', 'dpw_rui_init_roles_settings');