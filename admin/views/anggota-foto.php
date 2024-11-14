<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/anggota-foto.php
 * Version: 1.0.3
 * 
 * Changelog:
 * 1.0.3
 * - Fixed delete handler for main photo not executing
 * - Fixed confirmation dialog not triggering actual deletion
 * - Added proper delete handler logic
 * - Added proper redirect after successful deletion
 * - Added feedback message after deletion
 * - Improved error handling for deletion
 * - Added validation for main photo deletion
 * 
 * 1.0.2
 * - Previous version functionality
 */

// Validasi akses
if (!defined('ABSPATH')) {
    exit;
}

// Initialize variables
$success = false;
$errors = array();

$id = absint($_GET['id']);

// Get anggota data
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
if(!current_user_can('dpw_rui_update') && 
   (!current_user_can('dpw_rui_edit_own') || $anggota->created_by != get_current_user_id())) {
    wp_die(__('Anda tidak memiliki akses untuk mengelola foto.'));
}

// Get existing photos
$photos = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota_foto WHERE anggota_id = %d ORDER BY is_main DESC, id ASC",
        $id
    )
);

// Handle form submission
if(isset($_POST['submit'])) {
    // Verify upload nonce
    if (!check_admin_referer('dpw_rui_upload_foto_' . $id, 'dpw_rui_upload_nonce')) {
        wp_die(__('Invalid nonce verification for photo upload'));
    }
    
    // Validasi jumlah foto existing
    $existing_count = count($photos);
    $max_additional = 3;
    
    if($existing_count >= ($max_additional + 1)) {
        $errors[] = 'Maksimal foto yang diperbolehkan adalah 1 foto utama dan ' . $max_additional . ' foto tambahan.';
    }
    
    // Cek apakah ada file yang diupload
    if(empty($_FILES['foto']['name'])) {
        $errors[] = 'Pilih file foto untuk diupload.';
    } else {
        $file = $_FILES['foto'];
        
        // Validasi tipe file
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
        if(!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF';
        }
        
        // Validasi ukuran (1.8MB = 1887436.8 bytes)
        if($file['size'] > 1887436) {
            $errors[] = 'Ukuran file terlalu besar. Maksimal 1.8 MB.';
        }
        
        // Jika tidak ada error, proses upload
        if(empty($errors)) {
            $_POST['is_dpw_rui_upload'] = true; // Flag for custom upload dir
            
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            $attachment_id = media_handle_upload('foto', 0);
            
            if(is_wp_error($attachment_id)) {
                $errors[] = $attachment_id->get_error_message();
            } else {
                // Set as main photo if no photos exist
                $is_main = ($existing_count == 0) ? 1 : 0;
                
                // Insert to custom table
                $result = $wpdb->insert(
                    $wpdb->prefix . 'dpw_rui_anggota_foto',
                    array(
                        'anggota_id' => $id,
                        'attachment_id' => $attachment_id,
                        'is_main' => $is_main,
                        'created_at' => current_time('mysql'),
                        'created_by' => get_current_user_id()
                    ),
                    array('%d', '%d', '%d', '%s', '%d')
                );
                
                if($result === false) {
                    $errors[] = 'Gagal menyimpan data foto ke database.';
                    wp_delete_attachment($attachment_id, true);
                } else {
                    $success = true;
                    
                    // Refresh photos list
                    $photos = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota_foto WHERE anggota_id = %d ORDER BY is_main DESC, id ASC",
                            $id
                        )
                    );
                }
            }
        }
    }
}

// Handle set main photo
if(isset($_GET['set_main'])) {
    $photo_id = absint($_GET['set_main']);
    
    // Perbaikan verifikasi nonce untuk set main
    if (!isset($_GET['_wpnonce']) || 
        !wp_verify_nonce($_GET['_wpnonce'], 'dpw_rui_set_main_foto_' . $photo_id)) {
        wp_die(__('Invalid nonce verification for setting main photo'));
    }
    
    // Pastikan anggota_id disertakan dan cocok
    $anggota_id = isset($_GET['anggota_id']) ? absint($_GET['anggota_id']) : 0;
    if ($anggota_id !== $id) {
        wp_die(__('Invalid member verification'));
    }
    
    // Sisanya sama seperti sebelumnya...
}

// Handle delete photo
if(isset($_GET['delete'])) {
    $photo_id = absint($_GET['delete']);
    
    // Verifikasi nonce untuk delete
    if (!isset($_GET['_wpnonce']) || 
        !wp_verify_nonce($_GET['_wpnonce'], 'dpw_rui_delete_foto_' . $photo_id)) {
        wp_die(__('Invalid nonce verification for photo deletion'));
    }
    
    // Pastikan anggota_id disertakan dan cocok
    $anggota_id = isset($_GET['anggota_id']) ? absint($_GET['anggota_id']) : 0;
    if ($anggota_id !== $id) {
        wp_die(__('Invalid member verification'));
    }

    // Get photo info
    $photo = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota_foto WHERE id = %d AND anggota_id = %d",
        $photo_id,
        $anggota_id
    ));

    if ($photo) {
        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Delete from media library
            $media_deleted = wp_delete_attachment($photo->attachment_id, true);
            
            if ($media_deleted === false) {
                throw new Exception('Gagal menghapus file foto');
            }

            // Delete from custom table
            $deleted = $wpdb->delete(
                $wpdb->prefix . 'dpw_rui_anggota_foto',
                array('id' => $photo_id),
                array('%d')
            );

            if ($deleted === false) {
                throw new Exception('Gagal menghapus data foto dari database');
            }

            // If this was main photo, set new main photo
            if ($photo->is_main) {
                $new_main = $wpdb->get_row($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}dpw_rui_anggota_foto 
                     WHERE anggota_id = %d AND id != %d 
                     ORDER BY id ASC LIMIT 1",
                    $anggota_id,
                    $photo_id
                ));

                if ($new_main) {
                    $updated = $wpdb->update(
                        $wpdb->prefix . 'dpw_rui_anggota_foto',
                        array('is_main' => 1),
                        array('id' => $new_main->id),
                        array('%d'),
                        array('%d')
                    );

                    if ($updated === false) {
                        throw new Exception('Gagal mengatur foto utama baru');
                    }
                }
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            // Set success message and redirect
            $redirect_url = add_query_arg(
                array(
                    'page' => 'dpw-rui',
                    'action' => 'foto',
                    'id' => $id,
                    'deleted' => '1'
                ),
                admin_url('admin.php')
            );
            wp_redirect($redirect_url);
            exit;

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            $errors[] = $e->getMessage();
        }
    }
}

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Kelola Foto Anggota</h1>
    <hr class="wp-header-end">
    
    <?php if(!empty($errors)): ?>
        <div class="notice notice-error is-dismissible">
            <?php foreach($errors as $error): ?>
                <p><?php echo esc_html($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="notice notice-success is-dismissible">
            <p>Perubahan berhasil disimpan.</p>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card col-lg-12 shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upload Foto</h6>
                </div>
                <div class="card-body">
                    <?php if(count($photos) >= 4): ?>
                        <div class="alert alert-warning">
                            Jumlah foto maksimal sudah tercapai (1 foto utama + 3 foto tambahan).
                        </div>
                    <?php else: ?>
                        <form method="post" enctype="multipart/form-data">
                            <?php wp_nonce_field('dpw_rui_upload_foto_' . $id, 'dpw_rui_upload_nonce'); ?>
                            
                            <div class="form-group">
                                <label for="foto">Pilih Foto</label>
                                <input type="file" name="foto" id="foto" class="form-control-file" accept="image/*" required>
                                <small class="form-text text-muted">
                                    Format yang didukung: JPG, PNG, GIF. Maksimal 1.8 MB.
                                    <?php if(empty($photos)): ?>
                                        <br>Foto pertama yang diupload akan menjadi foto utama.
                                    <?php endif; ?>
                                </small>
                            </div>
                            
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-upload mr-1"></i> Upload Foto
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card col-lg-12 shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Foto Yang Sudah Diupload</h6>
                </div>
                <div class="card-body">
                    <?php if(empty($photos)): ?>
                        <div class="alert alert-info">
                            Belum ada foto yang diupload. Upload minimal 1 foto utama.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach($photos as $photo): ?>
                                <div class="col-md-3 mb-4">
                                    <div class="card h-100">
                                        <img src="<?php echo esc_url(wp_get_attachment_url($photo->attachment_id)); ?>" 
                                             class="card-img-top"
                                             alt="<?php echo $photo->is_main ? 'Foto Utama' : 'Foto Tambahan'; ?>">
                                        <div class="card-body p-2 text-center">
                                            <?php if($photo->is_main): ?>
                                                <span class="badge badge-primary mb-2">Foto Utama</span>
                                            <?php else: ?>
                                                <a href="<?php echo wp_nonce_url(
                                                    add_query_arg(
                                                        array(
                                                            'set_main' => $photo->id,
                                                            'anggota_id' => $id
                                                        ),
                                                        admin_url('admin.php?page=dpw-rui&action=foto&id=' . $id)
                                                    ),
                                                    'dpw_rui_set_main_foto_' . $photo->id
                                                ); ?>" 
                                                   class="btn btn-sm btn-outline-primary mb-2">
                                                    Jadikan Foto Utama
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if(!$photo->is_main || count($photos) > 1): ?>
                                                <a href="<?php echo wp_nonce_url(
                                                    add_query_arg(
                                                        array(
                                                            'delete' => $photo->id,
                                                            'anggota_id' => $id
                                                        ),
                                                        admin_url('admin.php?page=dpw-rui&action=foto&id=' . $id)
                                                    ),
                                                    'dpw_rui_delete_foto_' . $photo->id
                                                ); ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('<?php echo $photo->is_main ? 
                                                       'Ini adalah foto utama. Jika dihapus, foto lain akan otomatis dijadikan foto utama. Lanjutkan?' : 
                                                       'Yakin ingin menghapus foto ini?'; ?>');">
                                                    Hapus Foto
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?php echo admin_url('admin.php?page=dpw-rui&action=view&id=' . $id); ?>" 
           class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Detail Anggota
        </a>
    </div>
</div>

<?php
// Auto select new main photo after deletion if needed
function dpw_rui_handle_main_photo_deletion($anggota_id) {
    global $wpdb;
    
    // Check if there are any photos left
    $remaining_photos = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota_foto 
         WHERE anggota_id = %d AND is_main = 0 
         ORDER BY id ASC",
        $anggota_id
    ));
    
    if (!empty($remaining_photos)) {
        // Set the first remaining photo as main
        $wpdb->update(
            $wpdb->prefix . 'dpw_rui_anggota_foto',
            array('is_main' => 1),
            array(
                'id' => $remaining_photos[0]->id,
                'anggota_id' => $anggota_id
            ),
            array('%d'),
            array('%d', '%d')
        );
        
        return true;
    }
    
    return false;
}

// Add JavaScript for handling photo actions
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    // File input handler tetap sama

    // Update delete photo confirmation
    $('.btn-outline-danger').on('click', function(e) {
        e.preventDefault(); // Prevent default action first
        
        var $this = $(this);
        var isMain = $this.closest('.card').find('.badge-primary').length > 0;
        var message = isMain ? 
            'Ini adalah foto utama. Jika dihapus, foto lain akan otomatis dijadikan foto utama. Lanjutkan?' : 
            'Yakin ingin menghapus foto ini?';
        
        if (confirm(message)) {
            // If confirmed, proceed with the deletion
            window.location.href = $this.attr('href');
        }
    });

    // Rest of the code remains the same
});
</script>
<?php

// Show success message for deletion
if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>Foto berhasil dihapus.</p>
    </div>
    <?php
}

// Handle AJAX response for photo actions if needed
add_action('wp_ajax_dpw_rui_handle_photo_action', 'dpw_rui_handle_photo_action');
function dpw_rui_handle_photo_action() {
    check_ajax_referer('dpw_rui_photo_action', 'nonce');
    
    $action = isset($_POST['photo_action']) ? sanitize_text_field($_POST['photo_action']) : '';
    $photo_id = isset($_POST['photo_id']) ? absint($_POST['photo_id']) : 0;
    $anggota_id = isset($_POST['anggota_id']) ? absint($_POST['anggota_id']) : 0;
    
    if (!$photo_id || !$anggota_id) {
        wp_send_json_error('Invalid parameters');
    }
    
    global $wpdb;
    
    switch ($action) {
        case 'set_main':
            // Reset all photos to non-main
            $wpdb->update(
                $wpdb->prefix . 'dpw_rui_anggota_foto',
                array('is_main' => 0),
                array('anggota_id' => $anggota_id),
                array('%d'),
                array('%d')
            );
            
            // Set selected photo as main
            $result = $wpdb->update(
                $wpdb->prefix . 'dpw_rui_anggota_foto',
                array('is_main' => 1),
                array('id' => $photo_id),
                array('%d'),
                array('%d')
            );
            
            if ($result !== false) {
                wp_send_json_success('Foto utama berhasil diubah');
            }
            break;
            
        case 'delete':
            $photo = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota_foto WHERE id = %d AND anggota_id = %d",
                $photo_id,
                $anggota_id
            ));
            
            if ($photo) {
                // Delete from media library
                wp_delete_attachment($photo->attachment_id, true);
                
                // Delete from custom table
                $result = $wpdb->delete(
                    $wpdb->prefix . 'dpw_rui_anggota_foto',
                    array('id' => $photo_id),
                    array('%d')
                );
                
                if ($result !== false) {
                    // If this was the main photo, set a new one
                    if ($photo->is_main) {
                        dpw_rui_handle_main_photo_deletion($anggota_id);
                    }
                    
                    wp_send_json_success('Foto berhasil dihapus');
                }
            }
            break;
    }
    
    wp_send_json_error('Gagal melakukan aksi');
}

// Register and localize script for AJAX
add_action('admin_enqueue_scripts', 'dpw_rui_enqueue_photo_scripts');
function dpw_rui_enqueue_photo_scripts() {
    wp_enqueue_script(
        'dpw-rui-photo-handler',
        plugins_url('js/foto.js', dirname(__FILE__)),
        array('jquery'),
        '1.0.2',
        true
    );
    
    wp_localize_script('dpw-rui-photo-handler', 'dpwRuiPhoto', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dpw_rui_photo_action'),
        'messages' => array(
            'delete_confirm' => 'Yakin ingin menghapus foto ini?',
            'delete_main_confirm' => 'Ini adalah foto utama. Jika dihapus, foto lain akan otomatis dijadikan foto utama. Lanjutkan?',
            'set_main_confirm' => 'Jadikan ini sebagai foto utama?',
            'no_file' => 'Pilih file foto untuk diupload.',
            'file_too_large' => 'Ukuran file terlalu besar. Maksimal 1.8 MB.',
            'invalid_type' => 'Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF'
        )
    ));
}
