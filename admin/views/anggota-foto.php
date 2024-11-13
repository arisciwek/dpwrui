<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/anggota-foto.php
 * Version: 1.0.0
 * 
 * Halaman untuk mengelola foto anggota DPW RUI
 * - Upload foto utama (required) dan tambahan (optional, max 3)  
 * - Validasi file (max 1.8MB, image only)
 * - Display foto yang sudah diupload
 * - Fungsi hapus foto
 * - Fungsi set foto utama
 * 
 * Changelog:
 * 1.0.0
 * - Initial release with core photo management functionality
 * - Upload foto dengan validasi
 * - Display foto existing
 * - Hapus foto
 * - Set foto utama
 */

// Validasi akses
if (!defined('ABSPATH')) {
    exit;
}

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
    check_admin_referer('dpw_rui_upload_foto');
    
    $errors = array();
    $success = false;
    
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
            $attachment_id = $this->handle_photo_upload('foto', $id);
            
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
                }
            }
        }
    }
}

// Handle set main photo
if(isset($_GET['set_main']) && check_admin_referer('set_main_foto')) {
    $photo_id = absint($_GET['set_main']);
    
    // Reset all photos to non-main
    $wpdb->update(
        $wpdb->prefix . 'dpw_rui_anggota_foto',
        array('is_main' => 0),
        array('anggota_id' => $id),
        array('%d'),
        array('%d')
    );
    
    // Set selected photo as main
    $wpdb->update(
        $wpdb->prefix . 'dpw_rui_anggota_foto',
        array('is_main' => 1),
        array('id' => $photo_id),
        array('%d'),
        array('%d')
    );
    
    $success = true;
}

// Handle delete photo
if(isset($_GET['delete']) && check_admin_referer('delete_foto')) {
    $photo_id = absint($_GET['delete']);
    
    // Get photo data
    $photo = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota_foto WHERE id = %d AND anggota_id = %d",
            $photo_id,
            $id
        )
    );
    
    if($photo) {
        // Delete from media library
        wp_delete_attachment($photo->attachment_id, true);
        
        // Delete from custom table
        $wpdb->delete(
            $wpdb->prefix . 'dpw_rui_anggota_foto',
            array('id' => $photo_id),
            array('%d')
        );
        
        $success = true;
    }
    
    // Refresh photos list
    $photos = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota_foto WHERE anggota_id = %d ORDER BY is_main DESC, id ASC",
            $id
        )
    );
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
                                <img src="<?php echo esc_url(wp_get_attachment_url($photo->attachment_id)); ?>" 
                                     class="card-img-top"
                                     alt="<?php echo $photo->is_main ? 'Foto Utama' : 'Foto Tambahan'; ?>">
                                <div class="card-body p-2 text-center">
                                    <?php if($photo->is_main): ?>
                                        <span class="badge badge-primary mb-2">Foto Utama</span>
                                    <?php else: ?>
                                        <a href="<?php echo wp_nonce_url(add_query_arg(array(
                                            'set_main' => $photo->id
                                        )), 'set_main_foto'); ?>" 
                                           class="btn btn-sm btn-outline-primary mb-2">
                                            Jadikan Foto Utama
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if(!$photo->is_main || count($photos) > 1): ?>
                                        <a href="<?php echo wp_nonce_url(add_query_arg(array(
                                            'delete' => $photo->id
                                        )), 'delete_foto'); ?>"
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
