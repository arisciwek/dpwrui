<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/templates/foto/upload-form.php
 * Version: 1.0.0
 * 
 * Template for photo upload form
 * 
 * @param int $anggota_id Member ID
 * @param int $existing_count Number of existing photos
 * @param int $max_photos Maximum allowed photos
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Upload Foto</h6>
    </div>
    <div class="card-body">
        <?php if($existing_count >= $max_photos): ?>
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Jumlah foto maksimal sudah tercapai (1 foto utama + <?php echo $max_photos - 1; ?> foto tambahan).
            </div>
        <?php else: ?>
            <form method="post" enctype="multipart/form-data" class="dpw-rui-foto-upload-form">
                <?php wp_nonce_field('dpw_rui_upload_foto_' . $anggota_id, 'dpw_rui_upload_nonce'); ?>
                
                <div class="dpw-rui-foto-upload mb-3" id="dropZone">
                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                    <p class="mb-2">Pilih foto atau tarik dan lepas file di sini</p>
                    <small class="text-muted d-block">Format yang didukung: JPG, PNG, GIF. Maksimal 1.8 MB.</small>
                    <?php if($existing_count === 0): ?>
                        <small class="text-primary d-block mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Foto pertama yang diupload akan menjadi foto utama.
                        </small>
                    <?php endif; ?>

                    <input type="file" 
                           name="foto" 
                           id="foto" 
                           class="dpw-rui-foto-input"
                           accept="image/jpeg,image/png,image/gif" 
                           required>
                </div>

                <div class="dpw-rui-foto-preview mb-3" style="display: none;">
                    <img src="" alt="Preview" id="fotoPreview">
                    <button type="button" class="btn btn-sm btn-danger cancel-upload">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="dpw-rui-foto-error-message"></div>

                <button type="submit" 
                        name="submit" 
                        class="btn btn-primary"
                        id="uploadButton"
                        disabled>
                    <i class="fas fa-upload mr-1"></i> Upload Foto
                </button>

                <div class="progress mt-3" style="display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         aria-valuenow="0" 
                         aria-valuemin="0" 
                         aria-valuemax="100"></div>
                </div>
            </form>

            <script>
            jQuery(document).ready(function($) {
                var dropZone = $('#dropZone');
                var fileInput = $('#foto');
                var preview = $('.dpw-rui-foto-preview');
                var previewImg = $('#fotoPreview');
                var uploadButton = $('#uploadButton');
                var progressBar = $('.progress');
                var errorMessage = $('.dpw-rui-foto-error-message');
                
                // File input change handler
                fileInput.on('change', function(e) {
                    handleFileSelect(this.files[0]);
                });
                
                // Drag and drop handlers
                dropZone
                    .on('dragenter dragover', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).addClass('dragover');
                    })
                    .on('dragleave drop', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).removeClass('dragover');
                        
                        if(e.type === 'drop') {
                            handleFileSelect(e.originalEvent.dataTransfer.files[0]);
                        }
                    });
                
                // Cancel upload button
                $('.cancel-upload').on('click', function() {
                    resetUpload();
                });
                
                function handleFileSelect(file) {
                    errorMessage.empty();
                    uploadButton.prop('disabled', true);
                    
                    // Validate file type
                    if(!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
                        showError('Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF');
                        return;
                    }
                    
                    // Validate file size (1.8MB = 1887436.8 bytes)
                    if(file.size > 1887436) {
                        showError('Ukuran file terlalu besar. Maksimal 1.8 MB');
                        return;
                    }
                    
                    // Show preview
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.attr('src', e.target.result);
                        preview.show();
                        dropZone.hide();
                        uploadButton.prop('disabled', false);
                    };
                    reader.readAsDataURL(file);
                }
                
                function showError(message) {
                    errorMessage
                        .html('<div class="alert alert-danger mb-0">' + message + '</div>')
                        .show();
                    resetUpload();
                }
                
                function resetUpload() {
                    fileInput.val('');
                    preview.hide();
                    previewImg.attr('src', '');
                    dropZone.show();
                    uploadButton.prop('disabled', true);
                    progressBar.hide();
                }
                
                // Form submit handler
                $('.dpw-rui-foto-upload-form').on('submit', function() {
                    uploadButton
                        .prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengupload...');
                });
            });
            </script>
        <?php endif; ?>
    </div>
</div>