<?php
/**
* Path: /wp-content/plugins/dpwrui/admin/services.php
* Version: 2.0.0
* 
* Changelog:
* 2.0.0
* - Converted to proper class structure
* - Added integration with main settings class
* - Added proper access control
* - Added proper initialization
* 
* 1.0.0
* - Initial basic services placeholder page
*/

class DPW_RUI_Services_Settings {
   
   private $parent_slug = 'dpw-rui-settings';
   private $validation;

   public function __construct(DPW_RUI_Validation $validation) {
       $this->validation = $validation;
   }

   public function render_page() {
       // Check permissions
       if (!current_user_can('manage_options')) {
           wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
       }
       
       ?>
       <div class="wrap">
           <div class="card shadow mb-4">
               <div class="card-header py-3">
                   <h6 class="m-0 font-weight-bold text-primary">Pengaturan Layanan</h6>
               </div>
               <div class="card-body">
                   <?php if (isset($_GET['settings-updated'])): ?>
                       <div class="notice notice-success is-dismissible">
                           <p><?php _e('Pengaturan berhasil disimpan.'); ?></p>
                       </div>
                   <?php endif; ?>

                   <div class="alert alert-info">
                       <h4 class="alert-heading mb-2">Pengembangan Mendatang</h4>
                       <p class="mb-0">
                           Halaman ini akan digunakan untuk pengaturan:
                       </p>
                       <ul class="mt-2 mb-0">
                           <li>Jenis-jenis layanan DPW RUI</li>
                           <li>Biaya layanan</li>
                           <li>Persyaratan layanan</li>
                           <li>Durasi proses layanan</li>
                           <li>Dan fitur layanan lainnya</li>
                       </ul>
                   </div>

                   <div class="alert alert-warning mt-4">
                       <p class="mb-0">
                           <i class="fas fa-info-circle mr-1"></i>
                           Fitur ini masih dalam tahap pengembangan dan akan tersedia pada versi mendatang.
                       </p>
                   </div>
               </div>
           </div>

           <div class="card shadow">
               <div class="card-header py-3">
                   <h6 class="m-0 font-weight-bold text-primary">Status Pengembangan</h6>
               </div>
               <div class="card-body">
                   <div class="table-responsive">
                       <table class="table table-bordered">
                           <thead>
                               <tr>
                                   <th>Fitur</th>
                                   <th>Status</th>
                                   <th>Target Release</th>
                               </tr>
                           </thead>
                           <tbody>
                               <tr>
                                   <td>Manajemen Jenis Layanan</td>
                                   <td><span class="badge badge-warning">Planned</span></td>
                                   <td>v2.1.0</td>
                               </tr>
                               <tr>
                                   <td>Konfigurasi Biaya</td>
                                   <td><span class="badge badge-warning">Planned</span></td>
                                   <td>v2.1.0</td>
                               </tr>
                               <tr>
                                   <td>Manajemen Persyaratan</td>
                                   <td><span class="badge badge-warning">Planned</span></td>
                                   <td>v2.2.0</td>
                               </tr>
                               <tr>
                                   <td>Pengaturan SLA</td>
                                   <td><span class="badge badge-warning">Planned</span></td>
                                   <td>v2.2.0</td>
                               </tr>
                           </tbody>
                       </table>
                   </div>
               </div>
           </div>
       </div>
       <?php
   }
}

// Initialize only when needed
function dpw_rui_init_services_settings() {
   global $pagenow, $dpw_rui_validation;
   if ($pagenow === 'admin.php' && 
       isset($_GET['page']) && $_GET['page'] === 'dpw-rui-settings' &&
       isset($_GET['tab']) && $_GET['tab'] === 'layanan') {
       
       global $dpw_rui_services_settings;
       if(!isset($dpw_rui_services_settings)) {
           $dpw_rui_services_settings = new DPW_RUI_Services_Settings($dpw_rui_validation);
       }
       
       $dpw_rui_services_settings->render_page();
   }
}
add_action('admin_init', 'dpw_rui_init_services_settings');