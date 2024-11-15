<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/anggota-form.php
 * Version: 1.0.6
 * Timestamp: 2024-11-16 17:30:00
 */

// [Previous PHP code remains the same...]
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $is_edit ? 'Edit Anggota' : 'Tambah Anggota Baru'; ?>
    </h1>
    
    <hr class="wp-header-end">

    <?php if ($error_message): ?>
    <div class="notice notice-error is-dismissible">
        <p><?php echo esc_html($error_message); ?></p>
    </div>
    <?php endif; ?>
    <div class="col-lg-12">
        <div class="card col-lg-10 ml-1 mr-1 shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?php echo $is_edit ? 'Form Edit Anggota' : 'Form Tambah Anggota'; ?>
                </h6>
            </div>
            <form method="post" action="<?php echo admin_url('admin.php?page=dpw-rui' . ($is_edit ? '&action=edit&id=' . $id : '')); ?>" 
                  class="dpw-rui-form needs-validation" novalidate>
                
                <div class="card-body">
                    <?php wp_nonce_field('dpw_rui_add_anggota'); ?>
                    <input type="hidden" name="form_source" value="<?php echo $is_edit ? 'edit' : 'add'; ?>">
                    <?php if($is_edit): ?>
                        <input type="hidden" name="id" value="<?php echo $anggota->id; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <!-- Kolom Kiri - Data Perusahaan -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Perusahaan <span class="required-indicator">*</span></label>
                                <input type="text" 
                                       name="nama_perusahaan" 
                                       class="form-control" 
                                       maxlength="100"
                                       required
                                       value="<?php echo $is_edit ? esc_attr($anggota->nama_perusahaan) : ''; ?>">
                                <div class="invalid-feedback">Nama perusahaan wajib diisi</div>
                            </div>

                            <div class="form-group">
                                <label>Pimpinan <span class="required-indicator">*</span></label>
                                <input type="text" 
                                       name="pimpinan" 
                                       class="form-control" 
                                       maxlength="100"
                                       required
                                       value="<?php echo $is_edit ? esc_attr($anggota->pimpinan) : ''; ?>">
                                <div class="invalid-feedback">Nama pimpinan wajib diisi</div>
                            </div>

                            <div class="form-group">
                                <label>Bidang Usaha <span class="required-indicator">*</span></label>
                                <input type="text" 
                                       name="bidang_usaha" 
                                       class="form-control" 
                                       maxlength="100"
                                       required
                                       value="<?php echo $is_edit ? esc_attr($anggota->bidang_usaha) : ''; ?>">
                                <div class="invalid-feedback">Bidang usaha wajib diisi</div>
                            </div>

                            <div class="form-group">
                                <label>Nomor AHU <span class="required-indicator">*</span></label>
                                <input type="text" 
                                       name="nomor_ahu" 
                                       class="form-control" 
                                       maxlength="50"
                                       required
                                       value="<?php echo $is_edit ? esc_attr($anggota->nomor_ahu) : ''; ?>">
                                <div class="invalid-feedback">Nomor AHU wajib diisi</div>
                            </div>

                            <div class="form-group">
                                <label>NPWP <span class="required-indicator">*</span></label>
                                <input type="text" 
                                       name="npwp" 
                                       class="form-control" 
                                       maxlength="30"
                                       required
                                       value="<?php echo $is_edit ? esc_attr($anggota->npwp) : ''; ?>">
                                <div class="invalid-feedback">NPWP wajib diisi</div>
                            </div>
                        </div>

                        <!-- Kolom Kanan - Data Kontak/Alamat -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Alamat <span class="required-indicator">*</span></label>
                                <textarea name="alamat" 
                                          class="form-control" 
                                          rows="3" 
                                          maxlength="255"
                                          required><?php echo $is_edit ? esc_textarea($anggota->alamat) : ''; ?></textarea>
                                <div class="invalid-feedback">Alamat wajib diisi</div>
                            </div>

                            <div class="form-group">
                                <label>Kabupaten <span class="required-indicator">*</span></label>
                                <input type="text" 
                                       name="kabupaten" 
                                       class="form-control" 
                                       maxlength="50"
                                       required
                                       value="<?php echo $is_edit ? esc_attr($anggota->kabupaten) : ''; ?>">
                                <div class="invalid-feedback">Kabupaten wajib diisi</div>
                            </div>

                            <div class="form-group">
                                <label>Kode Pos</label>
                                <input type="text" 
                                       name="kode_pos" 
                                       class="form-control"
                                       maxlength="10" 
                                       value="<?php echo $is_edit ? esc_attr($anggota->kode_pos) : ''; ?>">
                                <small class="form-text text-muted">Maksimal 10 karakter</small>
                            </div>

                            <div class="form-group">
                                <label>Nomor Telpon <span class="required-indicator">*</span></label>
                                <input type="text" 
                                       name="nomor_telpon" 
                                       class="form-control" 
                                       maxlength="20"
                                       required
                                       value="<?php echo $is_edit ? esc_attr($anggota->nomor_telpon) : ''; ?>">
                                <div class="invalid-feedback">Nomor telpon wajib diisi</div>
                            </div>

                            <div class="form-group">
                                <label>Jabatan</label>
                                <input type="text" 
                                       name="jabatan" 
                                       class="form-control"
                                       maxlength="50" 
                                       value="<?php echo $is_edit ? esc_attr($anggota->jabatan) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            <?php echo $is_edit ? 'Update' : 'Simpan'; ?>
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=dpw-rui'); ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i>
                            Batal
                        </a>
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
                } else {
                    var submitButton = form.querySelector('button[type="submit"]');
                    submitButton.classList.add('btn-loading');
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>