<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/services.php
 * Version: 1.0.2
 *
 * Changelog:
 * 1.0.2
 * - Restrukturisasi kelas untuk bekerja dengan sistem settings terpusat
 * - Penambahan method render_content() untuk integrasi dengan settings.php
 * - Penghapusan duplikasi header dan navigasi
 * - Penyesuaian struktur UI dengan halaman settings lainnya
 * - Penambahan placeholder untuk fitur mendatang
 *
 * 1.0.1 
 * - Initial release
 */

if (!defined('ABSPATH')) {
    exit;
}

class DPW_RUI_Services_Settings {
    
    private $future_features;

    public function __construct() {
        $this->future_features = array(
            'service_types' => array(
                'title' => 'Pengelolaan Jenis Layanan',
                'description' => 'Menambah, mengubah dan menghapus jenis layanan'
            ),
            'service_workflow' => array(
                'title' => 'Pengaturan Workflow Layanan',
                'description' => 'Mengatur alur proses setiap jenis layanan'
            ),
            'service_forms' => array(
                'title' => 'Form Builder Layanan',
                'description' => 'Membuat form kustom untuk setiap jenis layanan'
            ),
            'service_documents' => array(
                'title' => 'Manajemen Dokumen',
                'description' => 'Mengatur template dan persyaratan dokumen'
            ),
            'service_notifications' => array(
                'title' => 'Notifikasi Layanan',
                'description' => 'Mengatur notifikasi email dan sistem'
            ),
            'service_reports' => array(
                'title' => 'Laporan Layanan',
                'description' => 'Membuat dan mengatur template laporan'
            )
        );
    }

    public function render_content() {
        ?>
        <div class="card" style="max-width: 100%; background: #fff; border: 1px solid #ddd; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-top: 20px; padding: 20px;">
            <h2 style="color: #2271b1; font-size: 16px; margin: 0 0 20px;">
                <span class="dashicons dashicons-admin-generic" style="font-size: 20px; margin-right: 5px;"></span>
                Pengaturan Layanan
            </h2>

            <div class="notice notice-info" style="margin: 0 0 20px;">
                <p>Halaman ini akan digunakan untuk pengembangan fitur layanan di masa mendatang.</p>
            </div>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 4px;">
                <h3 style="margin-top: 0; color: #2271b1;">Fitur Yang Akan Datang:</h3>
                
                <div class="feature-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                    <?php foreach ($this->future_features as $key => $feature): ?>
                        <div class="feature-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                            <h4 style="margin: 0 0 10px; color: #2271b1;">
                                <span class="dashicons dashicons-yes-alt" style="color: #1cc88a;"></span>
                                <?php echo esc_html($feature['title']); ?>
                            </h4>
                            <p style="margin: 0; color: #666;">
                                <?php echo esc_html($feature['description']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <div class="notice notice-warning inline">
                    <p>
                        <strong>Catatan Pengembangan:</strong><br>
                        Fitur-fitur di atas masih dalam tahap perencanaan dan akan dikembangkan secara bertahap.
                        Prioritas pengembangan akan disesuaikan dengan kebutuhan pengguna.
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}