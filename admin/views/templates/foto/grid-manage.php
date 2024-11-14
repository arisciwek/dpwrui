<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/templates/foto/grid-manage.php
 * Version: 1.2.0
 * 
 * Changelog:
 * 1.2.0
 * - Fixed preview functionality to only affect new uploads
 * - Separated existing photos display from preview
 * - Added proper photo grid containment
 * - Improved photo status indicators
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="card col-lg-12 shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Foto Yang Sudah Diupload</h6>
        <span class="badge badge-primary">
            Total: <?php echo count($photos); ?> foto
        </span>
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
                    $photo_url = wp_get_attachment_url($photo->attachment_id);
                    if(!$photo_url) continue;
                ?>
                    <div class="dpw-rui-foto-item" id="foto-<?php echo $photo->id; ?>">
                        <div class="position-relative h-100">
                            <div class="dpw-rui-foto-preview existing-photo">
                                <img src="<?php echo esc_url($photo_url); ?>" 
                                     alt="<?php echo $photo->is_main ? 'Foto Utama' : 'Foto Tambahan'; ?>"
                                     class="img-fluid"
                                     loading="lazy">
                            </div>

                            <?php if($can_manage): ?>
                                <div class="actions">
                                    <?php if(!$photo->is_main): ?>
                                        <a href="<?php echo wp_nonce_url(
                                            add_query_arg(
                                                array(
                                                    'set_main' => $photo->id,
                                                    'anggota_id' => $anggota_id
                                                ),
                                                admin_url('admin.php?page=dpw-rui&action=foto&id=' . $anggota_id)
                                            ),
                                            'dpw_rui_set_main_foto_' . $photo->id
                                        ); ?>" 
                                           class="btn btn-primary btn-sm set-main-photo"
                                           title="Jadikan Foto Utama">
                                            <i class="fas fa-star"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if(!$photo->is_main || count($photos) > 1): ?>
                                        <a href="<?php echo wp_nonce_url(
                                            add_query_arg(
                                                array(
                                                    'delete' => $photo->id,
                                                    'anggota_id' => $anggota_id
                                                ),
                                                admin_url('admin.php?page=dpw-rui&action=foto&id=' . $anggota_id)
                                            ),
                                            'dpw_rui_delete_foto_' . $photo->id
                                        ); ?>"
                                           class="btn btn-danger btn-sm delete-photo"
                                           title="Hapus Foto"
                                           data-is-main="<?php echo $photo->is_main ? 'true' : 'false'; ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if($photo->is_main): ?>
                                <div class="badge badge-primary position-absolute" 
                                     style="top: 10px; left: 10px;">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Foto Utama
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="foto-info p-2 bg-light border-top">
                            <small class="text-muted d-block">
                                Diupload: <?php echo date('d/m/Y H:i', strtotime($photo->created_at)); ?>
                            </small>
                            <?php 
                            $metadata = wp_get_attachment_metadata($photo->attachment_id);
                            if($metadata): 
                            ?>
                                <small class="text-muted d-block">
                                    Ukuran: <?php echo $metadata['width']; ?> x <?php echo $metadata['height']; ?> px
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.dpw-rui-foto-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin: 0;
    max-width: 80%;
    margin: 0 auto;
}

.dpw-rui-foto-preview {
    position: relative;
    padding-top: 60%;
    overflow: hidden;
}

.dpw-rui-foto-preview.existing-photo {
    /* Specific styles for existing photos */
    background-color: #fff;
    border: 1px solid #e3e6f0;
}

.dpw-rui-foto-item {
    background: #fff;
    border-radius: 0.35rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transition: transform 0.2s ease;
    overflow: hidden;
}

.dpw-rui-foto-item:hover {
    transform: translateY(-3px);
}

.dpw-rui-foto-preview img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.dpw-rui-foto-item .actions {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    display: flex;
    gap: 0.25rem;
    opacity: 0;
    transition: opacity 0.2s ease;
    z-index: 2;
}

.dpw-rui-foto-item:hover .actions {
    opacity: 1;
}

.foto-info {
    min-height: 60px;
}

@media (max-width: 992px) {
    .dpw-rui-foto-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Delete photo confirmation
    $('.delete-photo').on('click', function(e) {
        e.preventDefault();
        
        var isMain = $(this).data('is-main') === true;
        var message = isMain ? 
            'Ini adalah foto utama. Jika dihapus, foto lain akan otomatis dijadikan foto utama. Lanjutkan?' : 
            'Yakin ingin menghapus foto ini?';
        
        if(confirm(message)) {
            window.location.href = $(this).attr('href');
        }
    });

    // Set main photo confirmation
    $('.set-main-photo').on('click', function(e) {
        e.preventDefault();
        
        if(confirm('Jadikan ini sebagai foto utama?')) {
            window.location.href = $(this).attr('href');
        }
    });

    // Initialize tooltips
    $('[title]').tooltip();
});
</script>