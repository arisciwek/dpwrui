<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/anggota-form.php  
 * Version: 1.0.3
 *
 * Changelog:
 * 1.0.3
 * - Fixed form action untuk handle add/edit dengan benar
 * - Fixed redirect url handling
 * - Improved error handling untuk validasi form
 * - Fixed hidden fields untuk routing
 * 
 * 1.0.2
 * - Added form validation
 * - Added nonce field
 * 
 * 1.0.1
 * - Initial form layout
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

// Set form action URL
$form_action = $is_edit ? 
    admin_url('admin.php?page=dpw-rui&action=edit&id=' . absint($_GET['id'])) : 
    admin_url('admin.php?page=dpw-rui-add');
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $is_edit ? 'Edit Anggota' : 'Tambah Anggota Baru'; ?>
    </h1>
    
    <hr class="wp-header-end">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <?php echo $is_edit ? 'Form Edit Anggota' : 'Form Tambah Anggota'; ?>
            </h6>
        </div>
        <div class="card-body">
            <form method="post" action="<?php echo esc_url($form_action); ?>" class="needs-validation" novalidate>
                <?php wp_nonce_field('dpw_rui_add_anggota'); ?>
                
                <?php if($is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $anggota->id; ?>">
                <?php endif; ?>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" 
                               name="nama_perusahaan" 
                               class="form-control" 
                               maxlength="100"
                               required
                               value="<?php echo $is_edit ? esc_attr($anggota->nama_perusahaan) : ''; ?>">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Pimpinan <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="pimpinan" class="form-control" required
                               value="<?php echo $is_edit ? esc_attr($anggota->pimpinan) : ''; ?>">
                        <div class="invalid-feedback">
                            Nama pimpinan wajib diisi
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Alamat <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <textarea name="alamat" class="form-control" rows="3" required><?php 
                            echo $is_edit ? esc_textarea($anggota->alamat) : ''; 
                        ?></textarea>
                        <div class="invalid-feedback">
                            Alamat wajib diisi
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Kabupaten <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="kabupaten" class="form-control" required
                               value="<?php echo $is_edit ? esc_attr($anggota->kabupaten) : ''; ?>">
                        <div class="invalid-feedback">
                            Kabupaten wajib diisi
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Kode Pos</label>
                    <div class="col-sm-10">
                        <input type="text" 
                               name="kode_pos" 
                               class="form-control"
                               maxlength="10" 
                               value="<?php echo $is_edit ? esc_attr($anggota->kode_pos) : ''; ?>">
                        <small class="form-text text-muted">Maksimal 10 karakter</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Nomor Telpon <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="nomor_telpon" class="form-control" required
                               value="<?php echo $is_edit ? esc_attr($anggota->nomor_telpon) : ''; ?>">
                        <div class="invalid-feedback">
                            Nomor telpon wajib diisi
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Bidang Usaha <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="bidang_usaha" class="form-control" required
                               value="<?php echo $is_edit ? esc_attr($anggota->bidang_usaha) : ''; ?>">
                        <div class="invalid-feedback">
                            Bidang usaha wajib diisi
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Nomor AHU <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="nomor_ahu" class="form-control" required
                               value="<?php echo $is_edit ? esc_attr($anggota->nomor_ahu) : ''; ?>">
                        <div class="invalid-feedback">
                            Nomor AHU wajib diisi
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Jabatan</label>
                    <div class="col-sm-10">
                        <input type="text" name="jabatan" class="form-control"
                               value="<?php echo $is_edit ? esc_attr($anggota->jabatan) : ''; ?>">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">NPWP <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="npwp" class="form-control" required
                               value="<?php echo $is_edit ? esc_attr($anggota->npwp) : ''; ?>">
                        <div class="invalid-feedback">
                            NPWP wajib diisi
                        </div>
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-10">
                        <button type="submit" name="submit" class="btn btn-primary">
                            <?php echo $is_edit ? 'Update' : 'Simpan'; ?>
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=dpw-rui'); ?>" 
                           class="btn btn-secondary">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>