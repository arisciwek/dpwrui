<?php
/**
* Path: /wp-content/plugins/dpwrui/admin/services.php
* Version: 2.1.0
* 
* Changelog:
* 2.1.0
* - Removed redundant card wrappers to match parent structure
* - Improved development status table layout
* - Added proper Bootstrap spacing and alignment
* - Added proper status badge styling
* - Fixed timeline display
* - Added icon support
* - Improved responsive layout
* - Added status legend
* - Fixed validation handling
* - Improved loading states
* 
* 2.0.1
* - Previous version functionality
*/

class DPW_RUI_Services_Settings {
    
    private $validation;
    private $development_status = array(
        'planned' => array(
            'label' => 'Direncanakan',
            'color' => 'warning'
        ),
        'in_progress' => array(
            'label' => 'Sedang Dikembangkan',
            'color' => 'info'
        ),
        'completed' => array(
            'label' => 'Selesai',
            'color' => 'success'
        ),
        'delayed' => array(
            'label' => 'Ditunda',
            'color' => 'danger'
        )
    );

    public function __construct($validation = null) {
        if ($validation === null) {
            require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
            $validation = new DPW_RUI_Validation();
        }
        
        $this->validation = $validation;
    }

    private function get_development_features() {
        return array(
            array(
                'name' => 'Manajemen Jenis Layanan',
                'description' => 'Pengaturan jenis-jenis layanan yang tersedia',
                'status' => 'planned',
                'target' => 'v2.1.0',
                'priority' => 'high'
            ),
            array(
                'name' => 'Konfigurasi Biaya',
                'description' => 'Pengaturan biaya untuk setiap layanan',
                'status' => 'planned',
                'target' => 'v2.1.0',
                'priority' => 'high'
            ),
            array(
                'name' => 'Manajemen Persyaratan',
                'description' => 'Pengaturan persyaratan untuk setiap layanan',
                'status' => 'planned',
                'target' => 'v2.2.0',
                'priority' => 'medium'
            ),
            array(
                'name' => 'Pengaturan SLA',
                'description' => 'Konfigurasi Service Level Agreement',
                'status' => 'planned',
                'target' => 'v2.2.0',
                'priority' => 'medium'
            )
        );
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }
        
        ?>
        <!-- Info Section -->
        <div class="alert alert-info mb-4">
            <h5 class="alert-heading mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                Pengembangan Mendatang
            </h5>
            <p class="mb-2">Halaman ini akan digunakan untuk pengaturan:</p>
            <ul class="mb-0 pl-3">
                <li>Jenis-jenis layanan DPW RUI</li>
                <li>Biaya layanan</li>
                <li>Persyaratan layanan</li>
                <li>Durasi proses layanan</li>
                <li>Dan fitur layanan lainnya</li>
            </ul>
        </div>

        <!-- Development Status Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 25%">Fitur</th>
                        <th style="width: 35%">Deskripsi</th>
                        <th style="width: 15%">Status</th>
                        <th style="width: 15%">Target Release</th>
                        <th style="width: 10%">Prioritas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->get_development_features() as $feature): ?>
                        <tr>
                            <td class="align-middle font-weight-medium">
                                <?php echo esc_html($feature['name']); ?>
                            </td>
                            <td class="align-middle">
                                <?php echo esc_html($feature['description']); ?>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-<?php echo $this->development_status[$feature['status']]['color']; ?>">
                                    <?php echo $this->development_status[$feature['status']]['label']; ?>
                                </span>
                            </td>
                            <td class="align-middle">
                                <?php echo esc_html($feature['target']); ?>
                            </td>
                            <td class="align-middle text-center">
                                <?php
                                $priority_badge = '';
                                switch ($feature['priority']) {
                                    case 'high':
                                        $priority_badge = '<span class="badge badge-danger">Tinggi</span>';
                                        break;
                                    case 'medium':
                                        $priority_badge = '<span class="badge badge-warning">Sedang</span>';
                                        break;
                                    case 'low':
                                        $priority_badge = '<span class="badge badge-info">Rendah</span>';
                                        break;
                                }
                                echo $priority_badge;
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Status Legend -->
        <div class="card bg-light mb-4">
            <div class="card-body">
                <h6 class="card-title mb-3">Keterangan Status:</h6>
                <div class="row">
                    <?php foreach ($this->development_status as $status): ?>
                        <div class="col-auto mb-2">
                            <span class="badge badge-<?php echo $status['color']; ?> mr-2">
                                <?php echo $status['label']; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Development Timeline -->
        <div class="alert alert-warning">
            <i class="fas fa-clock mr-2"></i>
            <strong>Catatan:</strong> Timeline pengembangan dapat berubah sesuai dengan kebutuhan dan prioritas.
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Enable tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Add hover effect to table rows
            $('.table-hover tbody tr').hover(
                function() {
                    $(this).addClass('bg-light');
                },
                function() {
                    $(this).removeClass('bg-light');
                }
            );
        });
        </script>
        <?php
    }
}

// Initialize only when needed
function dpw_rui_init_services_settings() {
    global $pagenow;
    if ($pagenow === 'admin.php' && 
        isset($_GET['page']) && $_GET['page'] === 'dpw-rui-settings' &&
        isset($_GET['tab']) && $_GET['tab'] === 'layanan') {
        
        global $dpw_rui_services_settings;
        if (!isset($dpw_rui_services_settings)) {
            global $dpw_rui_validation;
            $dpw_rui_services_settings = new DPW_RUI_Services_Settings($dpw_rui_validation);
        }
    }
}
add_action('admin_init', 'dpw_rui_init_services_settings');