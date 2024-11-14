<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/templates/foto/grid-manage.php
 * Version: 1.0.0
 * 
 * Template for photo grid management
 * 
 * @param array $photos Array of photo objects
 * @param int $anggota_id Member ID
 * @param bool $can_manage Whether current user can manage photos
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="card shadow mb-4">
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
            <div class="dpw-rui-foto-grid">
                <?php foreach($photos as $photo): 
                    $photo_url = wp_get_attachment_url($photo->attachment_id);
                    if(!$photo_url) continue;
                ?>
                    <div class="dpw-rui-foto-item" id="foto-<?php echo $photo->id; ?>">
                        <div class="position-relative">
                            <div class="dpw-rui-foto-preview">
                                <img src="<?php echo esc_url($photo_url); ?>" 
                                     alt="<?php echo $photo->is_main ? 'Foto Utama' : 'Foto Tambahan'; ?>">
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

                        <div class="foto-info p-2">
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

                // Image lazy loading
                if('loading' in HTMLImageElement.prototype) {
                    document.querySelectorAll('img[src]').forEach(img => {
                        img.setAttribute('loading', 'lazy');
                    });
                }
            });
            </script>
        <?php endif; ?>
    </div>
</div>