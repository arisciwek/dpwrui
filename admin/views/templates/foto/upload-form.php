<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/templates/foto/upload-form.php
 * Version: 1.3.0
 * 
 * Changelog:
 * 1.3.0
 * - Fixed preview mixing with existing photos
 * - Revised AJAX upload handling
 * - Improved error handling
 * - Added proper form submission
 * - Fixed progress tracking
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Upload Form Card -->
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

            <form method="post" enctype="multipart/form-data" class="dpw-rui-foto-upload-form" id="uploadForm">
            <?php wp_nonce_field('dpw_rui_upload_foto_' . $anggota_id, 'dpw_rui_upload_nonce'); ?>
                <input type="hidden" name="anggota_id" value="<?php echo $anggota_id; ?>">
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

                <div id="newPhotoPreview" class="mb-3 d-none">
                    <div class="preview-container position-relative" style="max-width: 300px; margin: 0 auto;">
                        <img src="" alt="Preview" class="img-fluid rounded">
                        <button type="button" class="btn btn-sm btn-danger cancel-upload position-absolute" style="top: 5px; right: 5px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="dpw-rui-foto-error-message"></div>

                <button type="submit" 
                        name="submit" 
                        class="btn btn-primary"
                        id="uploadButton"
                        disabled>
                    <i class="fas fa-upload mr-1"></i> Upload Foto
                </button>

                <div class="progress mt-3 d-none">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         aria-valuenow="0" 
                         aria-valuemin="0" 
                         aria-valuemax="100"></div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>


<!-- Info Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Informasi</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <h6 class="alert-heading mb-2">
                <i class="fas fa-info-circle mr-1"></i>
                Ketentuan Foto:
            </h6>
            <ul class="mb-0 pl-3">
                <li>Maksimal <?php echo $max_photos; ?> foto (1 utama + <?php echo $max_photos - 1; ?> tambahan)</li>
                <li>Format: JPG, PNG, GIF</li>
                <li>Maksimal ukuran: 1.8 MB</li>
                <li>Foto pertama otomatis jadi foto utama</li>
            </ul>
        </div>

        <div class="foto-stats mb-0">
            <h6 class="font-weight-bold mb-2">Status Foto:</h6>
            <table class="table table-sm mb-0">
                <tr>
                    <td>Total Foto</td>
                    <td class="text-right"><?php echo $existing_count; ?> / <?php echo $max_photos; ?></td>
                </tr>
                <tr>
                    <td>Foto Utama</td>
                    <td class="text-right">
                        <?php if($main_photo): ?>
                            <span class="text-success">
                                <i class="fas fa-check-circle"></i> Ada
                            </span>
                        <?php else: ?>
                            <span class="text-danger">
                                <i class="fas fa-times-circle"></i> Belum Ada
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Sisa Slot</td>
                    <td class="text-right"><?php echo $max_photos - $existing_count; ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>


<script>
jQuery(document).ready(function($) {
    var dropZone = $('#dropZone');
    var fileInput = $('#foto');
    var newPhotoPreview = $('#newPhotoPreview');
    var previewImg = newPhotoPreview.find('img');
    var uploadButton = $('#uploadButton');
    var progressBar = $('.progress');
    var errorMessage = $('.dpw-rui-foto-error-message');
    var uploadForm = $('#uploadForm');
    var selectedFile = null;
    
    function handleFileSelect(file) {
        if (!file) return;
        
        errorMessage.empty();
        uploadButton.prop('disabled', true);
        
        // Validate file type
        if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
            showError('Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF');
            return;
        }
        
        // Validate file size (1.8MB = 1887436.8 bytes)
        if (file.size > 1887436) {
            showError('Ukuran file terlalu besar. Maksimal 1.8 MB');
            return;
        }
        
        selectedFile = file;
        
        var reader = new FileReader();
        reader.onload = function(e) {
            previewImg.attr('src', e.target.result);
            newPhotoPreview.removeClass('d-none');
            dropZone.addClass('d-none');
            uploadButton.prop('disabled', false);
        };
        reader.readAsDataURL(file);
    }

    fileInput.on('change', function() {
        handleFileSelect(this.files[0]);
    });
    
    dropZone.on('dragenter dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    }).on('dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        
        if (e.type === 'drop') {
            handleFileSelect(e.originalEvent.dataTransfer.files[0]);
        }
    });
    
    $('.cancel-upload').on('click', resetUpload);
    
    function showError(message) {
        errorMessage.html('<div class="alert alert-danger mb-0">' + message + '</div>');
        resetUpload();
    }
    
    function resetUpload() {
        fileInput.val('');
        selectedFile = null;
        newPhotoPreview.addClass('d-none');
        previewImg.attr('src', '');
        dropZone.removeClass('d-none');
        uploadButton.prop('disabled', true);
        progressBar.addClass('d-none').find('.progress-bar').css('width', '0%');
        errorMessage.empty();
    }
    
    uploadForm.on('submit', function(e) {
        e.preventDefault();
        
        if (!selectedFile) {
            showError('Pilih file terlebih dahulu');
            return;
        }

        var formData = new FormData(this);
        formData.append('foto', selectedFile);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                uploadButton
                    .prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengupload...');
                progressBar.removeClass('d-none');
                errorMessage.empty();
            },
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percent = Math.round((e.loaded / e.total) * 100);
                        progressBar.find('.progress-bar')
                            .css('width', percent + '%')
                            .attr('aria-valuenow', percent);
                    }
                });
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showError(response.data || 'Gagal mengupload foto');
                }
            },
            error: function() {
                showError('Terjadi kesalahan. Silakan coba lagi.');
            },
            complete: function() {
                uploadButton
                    .prop('disabled', false)
                    .html('<i class="fas fa-upload mr-1"></i> Upload Foto');
            }
        });
    });
});
</script>

<style>
.preview-container {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.dragover {
    background-color: #e8f4ff !important;
    border-color: #4e73df !important;
}

.d-none {
    display: none !important;
}
</style>