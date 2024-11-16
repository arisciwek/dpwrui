<?php
/**
* Path: /wp-content/plugins/dpwrui/admin/views/anggota-foto.php
* Version: 1.1.2
* Timestamp: 2024-11-16 21:30:00
* 
* Changelog:
* 1.1.2 (2024-11-16 21:30:00)
* - Added proper integration with DPW_RUI_Foto class
* - Fixed file upload handling
* - Improved error handling and messages
* - Added proper file validation sequence
* - Maintained existing permission checks
* - Fixed form submission flow
* 
* 1.1.1 (2024-03-16 15:00:00)
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

// Initialize foto handler
require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-foto.php';
$foto_handler = new DPW_RUI_Foto();

// Get existing photos
$photos = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota_foto WHERE anggota_id = %d ORDER BY is_main DESC, id ASC",
        $id
    )
);

// Handle form submission
if(isset($_POST['submit'])) {
    // Verify nonce
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'dpw_rui_upload_foto')) {
        wp_die(__('Invalid nonce verification'));
    }
    
    // Validasi jumlah foto existing
    $existing_count = count($photos);
    $max_additional = 3;
    
    if($existing_count >= ($max_additional + 1)) {
        $errors[] = 'Maksimal foto yang diperbolehkan adalah 1 foto utama dan ' . $max_additional . ' foto tambahan.';
    } else if (!empty($_FILES['foto']['name'])) {
        // Validate uploaded file
        $validation_errors = $foto_handler->validate_upload($_FILES['foto']);
        
        if (empty($validation_errors)) {
            try {
                // Handle file upload
                $file_data = $foto_handler->handle_upload($_FILES['foto'], $id);
                
                if (!is_wp_error($file_data)) {
                    // Set as main photo if no photos exist
                    $is_main = ($existing_count == 0) ? 1 : 0;
                    
                    // Save to database
                    $save_result = $foto_handler->save_photo($id, $file_data, $is_main);
                    
                    if ($save_result !== false) {
                        $success = true;
                        
                        // Refresh photos list
                        $photos = $foto_handler->get_photos($id);
                    } else {
                        $errors[] = 'Gagal menyimpan data foto ke database.';
                    }
                } else {
                    $errors[] = $file_data->get_error_message();
                }
            } catch (Exception $e) {
                $errors[] = 'Error saat upload: ' . $e->getMessage();
            }
        } else {
            $errors = array_merge($errors, $validation_errors);
        }
    } else {
        $errors[] = 'Pilih file foto untuk diupload.';
    }
}

// Handle set main photo
if(isset($_GET['set_main'])) {
    if(!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'set_main_foto_' . $_GET['set_main'])) {
        wp_die(__('Invalid nonce verification'));
    }
    
    $photo_id = absint($_GET['set_main']);
    
    if($foto_handler->set_main_photo($photo_id, $id)) {
        $success = true;
        $photos = $foto_handler->get_photos($id);
    }
}

// Handle delete photo
if(isset($_GET['delete'])) {
    if(!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_foto_' . $_GET['delete'])) {
        wp_die(__('Invalid nonce verification'));
    }
    
    $photo_id = absint($_GET['delete']);
    
    if($foto_handler->delete_photo($photo_id, $id)) {
        $success = true;
        $photos = $foto_handler->get_photos($id);
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

    <div class="card shadow mb-4">
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
                    <?php wp_nonce_field('dpw_rui_upload_foto'); ?>
                    
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

    <div class="card shadow mb-4">
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
                                <img src="<?php echo esc_url($photo->file_url); ?>" 
                                     class="card-img-top"
                                     alt="<?php echo $photo->is_main ? 'Foto Utama' : 'Foto Tambahan'; ?>">
                                <div class="card-body p-2 text-center">
                                    <?php if($photo->is_main): ?>
                                        <span class="badge badge-primary mb-2">Foto Utama</span>
                                    <?php else: ?>
                                        <a href="<?php echo wp_nonce_url(add_query_arg(array(
                                            'set_main' => $photo->id
                                        )), 'set_main_foto_' . $photo->id); ?>" 
                                           class="btn btn-sm btn-outline-primary mb-2">
                                            Jadikan Foto Utama
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if(!$photo->is_main || count($photos) > 1): ?>
                                        <a href="<?php echo wp_nonce_url(add_query_arg(array(
                                            'delete' => $photo->id
                                        )), 'delete_foto_' . $photo->id); ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Yakin ingin menghapus foto ini?');">
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

    <div class="mt-4">
        <a href="<?php echo admin_url('admin.php?page=dpw-rui&action=view&id=' . $id); ?>" 
           class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Detail Anggota
        </a>
    </div>
</div>
