<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/templates/foto/upload-form.php
 * Version: 2.0.3
 * 
 * Changelog:
 * 2.0.3
 * - Fixed undefined variable $main_photo
 * - Menggunakan $upload_data yang dikirim dari parent
 * - Tidak mengubah struktur dan fungsi lainnya
 * 
 * 2.0.2
 * - Previous functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

// Pastikan variabel tersedia
if (!isset($max_photos)) {
    $max_photos = defined('DPW_RUI_MAX_PHOTOS') ? DPW_RUI_MAX_PHOTOS : 4;
}

// Pastikan $upload_data tersedia dan extract variabelnya
if (!isset($upload_data) || !is_array($upload_data)) {
    $upload_data = array();
}

$anggota_id = isset($upload_data['anggota_id']) ? $upload_data['anggota_id'] : 0;
$existing_count = isset($upload_data['existing_count']) ? $upload_data['existing_count'] : 0;
$max_photos = isset($upload_data['max_photos']) ? $upload_data['max_photos'] : 4;
$main_photo = isset($upload_data['main_photo']) ? $upload_data['main_photo'] : null;
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Upload Foto</h6>
    </div>
    <div class="card-body">
            <?php if($existing_count > 0 && $existing_count >= $max_photos): ?>
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Jumlah foto maksimal sudah tercapai (<?php echo $max_photos; ?> foto).
            </div>
        <?php else: ?>
            <form method="post" enctype="multipart/form-data" id="uploadForm">
                <?php wp_nonce_field('dpw_rui_upload_foto_' . $anggota_id, 'dpw_rui_upload_nonce'); ?>
                <input type="hidden" name="anggota_id" value="<?php echo $anggota_id; ?>">
                
                <div class="dpw-rui-foto-upload mb-3" id="dropZone">
                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                    <p class="mb-2">Pilih foto atau tarik dan lepas file di sini</p>
                    <small class="text-muted d-block">Format: JPG, PNG, GIF. Maks: 1.8 MB.</small>
                    <?php if($existing_count === 0): ?>
                        <small class="text-primary d-block mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Foto pertama akan menjadi foto utama.
                        </small>
                    <?php endif; ?>
                    
                    <input type="file" 
                           name="foto" 
                           id="foto" 
                           class="dpw-rui-foto-input"
                           accept=".jpg,.jpeg,.png,.gif"
                           required>
                </div>

                <!-- Preview Container -->
                <div id="previewContainer" class="d-none mb-3">
                    <div class="preview-wrap">
                        <img src="" alt="Preview" class="preview-image">
                        <button type="button" class="btn btn-sm btn-danger cancel-preview">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="dpw-rui-foto-error"></div>

                <button type="submit" 
                        name="submit" 
                        class="btn btn-primary"
                        id="uploadButton"
                        disabled>
                    <i class="fas fa-upload mr-1"></i> Upload Foto
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Info Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Status</h6>
    </div>
    <div class="card-body">
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