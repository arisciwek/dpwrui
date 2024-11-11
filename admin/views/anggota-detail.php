<?php
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
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Detail Anggota</h1>
    
    <hr class="wp-header-end">

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

            <div class="mt-4">
                <a href="<?php echo admin_url('admin.php?page=dpw-rui'); ?>" 
                   class="btn btn-secondary">Kembali</a>
                
                <?php if(current_user_can('dpw_rui_update') || 
                        (current_user_can('dpw_rui_edit_own') && 
                         $anggota->created_by == get_current_user_id())): ?>
                    <a href="<?php echo admin_url('admin.php?page=dpw-rui&action=edit&id=' . $anggota->id); ?>" 
                       class="btn btn-primary">Edit</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>