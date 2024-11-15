<?php
/**
* Path: /wp-content/plugins/dpwrui/admin/views/anggota-detail.php
* Version: 1.0.3
* Timestamp: 2024-03-16 16:00:00
* 
* Changelog:
* 1.0.3 (2024-03-16)
* - Updated photo display to use new file system
* - Removed WP attachment dependencies 
* - Added direct file URL usage
* - Improved photo layout and styling
* - Added file info display
*/

$id = absint($_GET['id']);
global $wpdb;
$anggota = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota WHERE id = %d",
        $id
    )
);

if(!$anggota) {
    wp_die(__('Data anggota tidak ditemukan.'));
}

// Cek permission
if(!current_user_can('dpw_rui_read') && 
   (!current_user_can('dpw_rui_edit_own') || $anggota->created_by != get_current_user_id())) {
    wp_die(__('Anda tidak memiliki akses untuk melihat data ini.'));
}

// Get photos
$photos = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota_foto WHERE anggota_id = %d ORDER BY is_main DESC, id ASC",
        $id
    )
);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Detail Anggota</h1>
    <hr class="wp-header-end">

    <!-- Main Content Section -->
    <div class="row">
        <!-- Left Column - Detail Anggota -->
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Anggota</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tr>
                                <th width="200">Nomor Anggota</th>
                                <td><?php echo esc_html($anggota->nomor_anggota); ?></td>
                            </tr>
                            <tr>
                                <th>Nama Perusahaan</th>
                                <td><?php echo esc_html($anggota->nama_perusahaan); ?></td>
                            </tr>
                            <tr>
                                <th>Pimpinan</th>
                                <td><?php echo esc_html($anggota->pimpinan); ?></td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td><?php echo nl2br(esc_html($anggota->alamat)); ?></td>
                            </tr>
                            <tr>
                                <th>Kabupaten</th>
                                <td><?php echo esc_html($anggota->kabupaten); ?></td>
                            </tr>
                            <tr>
                                <th>Kode Pos</th>
                                <td><?php echo esc_html($anggota->kode_pos); ?></td>
                            </tr>
                            <tr>
                                <th>Nomor Telpon</th>
                                <td><?php echo esc_html($anggota->nomor_telpon); ?></td>
                            </tr>
                            <tr>
                                <th>Bidang Usaha</th>
                                <td><?php echo esc_html($anggota->bidang_usaha); ?></td>
                            </tr>
                            <tr>
                                <th>Nomor AHU</th>
                                <td><?php echo esc_html($anggota->nomor_ahu); ?></td>
                            </tr>
                            <tr>
                                <th>Jabatan</th>
                                <td><?php echo esc_html($anggota->jabatan); ?></td>
                            </tr>
                            <tr>
                                <th>NPWP</th>
                                <td><?php echo esc_html($anggota->npwp); ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Daftar</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($anggota->created_at)); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Foto -->
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Foto Anggota</h6>
                    <?php if(current_user_can('dpw_rui_update') || 
                             (current_user_can('dpw_rui_edit_own') && 
                              $anggota->created_by == get_current_user_id())): ?>
                        <a href="<?php echo admin_url('admin.php?page=dpw-rui&action=foto&id=' . $id); ?>" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-camera mr-1"></i> Kelola Foto
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if(empty($photos)): ?>
                        <div class="alert alert-info mb-0">
                            Belum ada foto yang diunggah
                        </div>
                    <?php else: ?>
                        <?php foreach($photos as $photo): ?>
                            <div class="mb-3">
                                <div class="card">
                                    <img src="<?php echo esc_url($photo->file_url); ?>" 
                                         class="card-img-top"
                                         alt="<?php echo $photo->is_main ? 'Foto Utama' : 'Foto Tambahan'; ?>">
                                    <div class="card-footer p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <?php echo $photo->is_main ? 
                                                      '<span class="badge badge-primary">Foto Utama</span>' : 
                                                      '<span class="badge badge-secondary">Foto Tambahan</span>'; ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo human_filesize($photo->file_size); ?> &bull; 
                                                <?php echo date('d/m/Y H:i', strtotime($photo->created_at)); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <!-- Tombol Aksi -->
    <div class="mb-4">
        <a href="<?php echo admin_url('admin.php?page=dpw-rui'); ?>" 
           class="btn btn-secondary">Kembali</a>
        
        <?php if(current_user_can('dpw_rui_update') || 
                 (current_user_can('dpw_rui_edit_own') && 
                  $anggota->created_by == get_current_user_id())): ?>
            <a href="<?php echo admin_url('admin.php?page=dpw-rui&action=edit&id=' . $anggota->id); ?>" 
               class="btn btn-primary">Edit</a>
        <?php endif; ?>
    </div>

    <!-- Space untuk card tambahan -->
    <div class="row">
        <div class="col-12">
            <!-- Card tambahan akan ditambahkan di sini -->
        </div>
    </div>

</div>

<?php
// Helper function for file size formatting
function human_filesize($bytes, $decimals = 2) {
    $size = array('B','KB','MB','GB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
}
?>