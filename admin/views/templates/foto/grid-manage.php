<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/templates/foto/grid-manage.php
 * Version: 1.3.0
 * 
 * Changelog:
 * 1.3.0
 * - Fixed photo display mechanism
 * - Added proper URL generation
 * - Improved photo information display
 * - Added proper error handling for missing files
 * - Added loading state for photo actions
 * - Added photo size information
 * - Fixed spacing and layout issues
 * 
 * 1.2.0
 * - Previous version functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

global $dpw_rui_foto;
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Foto Yang Sudah Diupload</h6>
        <div class="d-flex align-items-center">
            <span class="badge badge-primary mr-2">
                Total: <?php echo count($photos); ?> foto
            </span>
            <?php if (count($photos) === 0): ?>
                <span class="badge badge-warning">
                    Belum ada foto
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if(empty($photos)): ?>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle mr-2"></i>
                Belum ada foto yang diupload. Upload minimal 1 foto utama.
            </div>
        <?php else: ?>
            <div class="dpw-rui-foto-grid" id="existingPhotos">
                <?php foreach($photos as $photo): 
                    $photo_url = $dpw_rui_foto->get_foto_url($photo);
                    $photo_path = $dpw_rui_foto->get_foto_path($photo);
                    
                    if(!$photo_url || !file_exists($photo_path)) {
                        continue;
                    }
                    
                    $file_size = filesize($photo_path);
                    $upload_date = strtotime($photo->created_at);
                    list($width, $height) = getimagesize($photo_path);
                ?>
                    <div class="dpw-rui-foto-item" id="foto-<?php echo $photo->id; ?>">
                        <div class="position-relative">
                            <div class="dpw-rui-foto-preview">
                                <img src="<?php echo esc_url($photo_url); ?>" 
                                     alt="<?php echo $photo->is_main ? 'Foto Utama' : 'Foto Tambahan'; ?>"
                                     class="foto-image"
                                     loading="lazy">
                            </div>

                            <?php if($can_manage): ?>
                                <div class="foto-actions">
                                    <?php if(!$photo->is_main): ?>
                                        <a href="<?php echo wp_nonce_url(
                                            add_query_arg(
                                                array(
                                                    'action' => 'set_main',
                                                    'set_main' => $photo->id,
                                                    'id' => $anggota_id
                                                ),
                                                admin_url('admin.php?page=dpw-rui&action=foto')
                                            ),
                                            'dpw_rui_set_main_foto_' . $photo->id
                                        ); ?>" 
                                           class="btn btn-primary btn-sm set-main-photo"
                                           data-action="set-main"
                                           title="Jadikan Foto Utama">
                                            <i class="fas fa-star"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if(!$photo->is_main || count($photos) > 1): ?>
                                        <a href="<?php echo wp_nonce_url(
                                            add_query_arg(
                                                array(
                                                    'action' => 'delete',
                                                    'delete' => $photo->id,
                                                    'id' => $anggota_id
                                                ),
                                                admin_url('admin.php?page=dpw-rui&action=foto')
                                            ),
                                            'dpw_rui_delete_foto_' . $photo->id
                                        ); ?>"
                                           class="btn btn-danger btn-sm delete-photo"
                                           data-action="delete"
                                           data-is-main="<?php echo $photo->is_main ? 'true' : 'false'; ?>"
                                           title="Hapus Foto">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if($photo->is_main): ?>
                                <div class="foto-badge">
                                    <span class="badge badge-primary">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Foto Utama
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="foto-info">
                            <div class="info-row">
                                <span class="info-label">Tanggal Upload:</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', $upload_date); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Ukuran File:</span>
                                <span class="info-value"><?php echo size_format($file_size); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Dimensi:</span>
                                <span class="info-value"><?php echo $width; ?> x <?php echo $height; ?> px</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="loading-overlay d-none">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
