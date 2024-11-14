<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/anggota-form.php
 * Version: 1.0.6
 * 
 * Changelog:
 * 1.0.6
 * - Improved layout with 2 columns
 * - Added card-footer with submit buttons
 * - Fixed form action and nonce
 * - Improved error display
 * - Fixed validation feedback
 * 
 * 1.0.5
 * - Previous version functionality
 */

// Cek jika mode edit
$is_edit = isset($_GET['action']) && $_GET['action'] == 'edit';
$anggota = null;

if($is_edit) {
    global $wpdb;
    $id = absint($_GET['id']);
    $anggota = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota WHERE id = %d",
            $id
        )
    );

    // Cek permission
    if(!current_user_can('dpw_rui_update') && 
       (!current_user_can('dpw_rui_edit_own') || $anggota->created_by != get_current_user_id())) {
        wp_die(__('Anda tidak memiliki akses untuk mengubah data ini.'));
    }
}

// Get error messages if any
$error_messages = get_settings_errors('dpw_rui_messages');
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $is_edit ? 'Edit Anggota' : 'Tambah Anggota Baru'; ?>
    </h1>
    
    <hr class="wp-header-end">

    <?php if (!empty($error_messages)): ?>
        <?php foreach($error_messages as $error): ?>
            <div class="notice notice-<?php echo $error['type']; ?> is-dismissible">
                <p><?php echo $error['message']; ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php echo $is_edit ? 'Form Edit Anggota' : 'Form Tambah Anggota'; ?>
                    </h6>
                </div>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="needs-validation" novalidate>
                    <div class="card-body">
                        <input type="hidden" name="action" value="dpw_rui_save_anggota">
                        <?php wp_nonce_field('dpw_rui_save_anggota', 'dpw_rui_nonce'); ?>
                        <input type="hidden" name="form_source" value="<?php echo $is_edit ? 'edit' : 'add'; ?>">
                        <?php if($is_edit): ?>
                            <input type="hidden" name="id" value="<?php echo $anggota->id; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Nama Perusahaan <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="nama_perusahaan" 
                                           class="form-control" 
                                           maxlength="100"
                                           required
                                           value="<?php echo $is_edit ? esc_attr($anggota->nama_perusahaan) : ''; ?>">
                                    <div class="invalid-feedback">
                                        Nama perusahaan wajib diisi
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Pimpinan <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="pimpinan" 
                                           class="form-control" 
                                           maxlength="100"
                                           required
                                           value="<?php echo $is_edit ? esc_attr($anggota->pimpinan) : ''; ?>">
                                    <div class="invalid-feedback">
                                        Nama pimpinan wajib diisi
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Alamat <span class="text-danger">*</span></label>
                                    <textarea name="alamat" 
                                              class="form-control" 
                                              rows="3" 
                                              maxlength="255"
                                              required><?php echo $is_edit ? esc_textarea($anggota->alamat) : ''; ?></textarea>
                                    <div class="invalid-feedback">
                                        Alamat wajib diisi
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Kabupaten <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="kabupaten" 
                                           class="form-control" 
                                           maxlength="50"
                                           required
                                           value="<?php echo $is_edit ? esc_attr($anggota->kabupaten) : ''; ?>">
                                    <div class="invalid-feedback">
                                        Kabupaten wajib diisi
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Kode Pos</label>
                                    <input type="text" 
                                           name="kode_pos" 
                                           class="form-control"
                                           maxlength="10" 
                                           value="<?php echo $is_edit ? esc_attr($anggota->kode_pos) : ''; ?>">
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Nomor Telpon <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="nomor_telpon" 
                                           class="form-control" 
                                           maxlength="20"
                                           required
                                           value="<?php echo $is_edit ? esc_attr($anggota->nomor_telpon) : ''; ?>">
                                    <div class="invalid-feedback">
                                        Nomor telpon wajib diisi
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Bidang Usaha <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="bidang_usaha" 
                                           class="form-control" 
                                           maxlength="100"
                                           required
                                           value="<?php echo $is_edit ? esc_attr($anggota->bidang_usaha) : ''; ?>">
                                    <div class="invalid-feedback">
                                        Bidang usaha wajib diisi
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Nomor AHU <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="nomor_ahu" 
                                           class="form-control" 
                                           maxlength="50"
                                           required
                                           value="<?php echo $is_edit ? esc_attr($anggota->nomor_ahu) : ''; ?>">
                                    <div class="invalid-feedback">
                                        Nomor AHU wajib diisi
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Jabatan</label>
                                    <input type="text" 
                                           name="jabatan" 
                                           class="form-control"
                                           maxlength="50" 
                                           value="<?php echo $is_edit ? esc_attr($anggota->jabatan) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label>NPWP <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="npwp" 
                                           class="form-control" 
                                           maxlength="30"
                                           required
                                           value="<?php echo $is_edit ? esc_attr($anggota->npwp) : ''; ?>">
                                    <div class="invalid-feedback">
                                        NPWP wajib diisi
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" name="submit" class="btn btn-primary">
                            <?php echo $is_edit ? 'Update' : 'Simpan'; ?>
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=dpw-rui'); ?>" 
                           class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>