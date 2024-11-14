<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/anggota-foto.php
 * Version: 1.1.0
 * 
 * Changelog:
 * 1.1.0
 * - Refactored to use new template structure
 * - Improved error handling and validation
 * - Added proper template loading
 * - Improved security checks
 * - Added constants for configuration
 * - Added proper action handling
 * - Improved file upload process
 * - Added proper transaction handling
 * - Added better feedback messages
 * 
 * 1.0.3
 * - Previous version functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

// Configuration
define('DPW_RUI_MAX_PHOTOS', 4); // 1 main + 3 additional
define('DPW_RUI_MAX_FILESIZE', 1887436); // 1.8MB in bytes

// Initialize variables
$errors = array();
$notices = array();
$success = false;
$success_message = '';

// Get anggota ID and validate
$anggota_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
if (!$anggota_id) {
    wp_die(__('ID Anggota tidak valid.'));
}

// Get DPW_RUI_Foto instance
global $dpw_rui_foto;
if (!isset($dpw_rui_foto)) {
    require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-foto.php';
    $dpw_rui_foto = new DPW_RUI_Foto();
}

// Get anggota data
global $wpdb;
$anggota = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota WHERE id = %d",
    $anggota_id
));

if (!$anggota) {
    wp_die(__('Data anggota tidak ditemukan.'));
}

// Check permissions
$can_manage = $dpw_rui_foto->can_manage_photo($anggota_id);
if (!$can_manage) {
    wp_die(__('Anda tidak memiliki akses untuk mengelola foto.'));
}

// Get existing photos
$photos = $dpw_rui_foto->get_member_photos($anggota_id);
$existing_count = count($photos);

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action) {
    switch($action) {
        case 'delete':
            if (isset($_GET['delete']) && isset($_GET['_wpnonce'])) {
                $photo_id = absint($_GET['delete']);
                
                // Verify nonce
                if (!wp_verify_nonce($_GET['_wpnonce'], 'dpw_rui_delete_foto_' . $photo_id)) {
                    $errors[] = 'Invalid security token.';
                    break;
                }
                
                // Delete photo
                if ($dpw_rui_foto->delete_photo($photo_id, $anggota_id)) {
                    $success = true;
                    $success_message = 'Foto berhasil dihapus.';
                    
                    // Refresh photos
                    $photos = $dpw_rui_foto->get_member_photos($anggota_id);
                    $existing_count = count($photos);
                } else {
                    $errors = array_merge($errors, $dpw_rui_foto->get_errors());
                }
            }
            break;

        case 'set_main':
            if (isset($_GET['set_main']) && isset($_GET['_wpnonce'])) {
                $photo_id = absint($_GET['set_main']);
                
                // Verify nonce
                if (!wp_verify_nonce($_GET['_wpnonce'], 'dpw_rui_set_main_foto_' . $photo_id)) {
                    $errors[] = 'Invalid security token.';
                    break;
                }
                
                // Set as main photo
                if ($dpw_rui_foto->set_main_photo($photo_id, $anggota_id)) {
                    $success = true;
                    $success_message = 'Foto utama berhasil diubah.';
                    
                    // Refresh photos
                    $photos = $dpw_rui_foto->get_member_photos($anggota_id);
                } else {
                    $errors = array_merge($errors, $dpw_rui_foto->get_errors());
                }
            }
            break;
    }
}

// Handle photo upload
if (isset($_POST['submit'])) {
    // Verify nonce
    if (!check_admin_referer('dpw_rui_upload_foto_' . $anggota_id, 'dpw_rui_upload_nonce')) {
        wp_die(__('Invalid security token.'));
    }
    
    // Check max photos limit
    if ($existing_count >= DPW_RUI_MAX_PHOTOS) {
        $errors[] = sprintf(
            'Maksimal foto yang diperbolehkan adalah 1 foto utama dan %d foto tambahan.',
            DPW_RUI_MAX_PHOTOS - 1
        );
    } else {
        // Handle file upload
        $attachment_id = $dpw_rui_foto->handle_file_upload('foto', $anggota_id);
        
        if (is_wp_error($attachment_id)) {
            $errors[] = $attachment_id->get_error_message();
        } else {
            // Set as main if no photos exist
            $is_main = ($existing_count == 0) ? 1 : 0;
            
            // Add photo record
            if ($dpw_rui_foto->add_photo($anggota_id, $attachment_id, $is_main)) {
                $success = true;
                $success_message = 'Foto berhasil diupload.';
                
                // Refresh photos
                $photos = $dpw_rui_foto->get_member_photos($anggota_id);
                $existing_count = count($photos);
            } else {
                $errors = array_merge($errors, $dpw_rui_foto->get_errors());
                
                // Clean up attachment if failed
                wp_delete_attachment($attachment_id, true);
            }
        }
    }
}

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Kelola Foto Anggota</h1>
    <hr class="wp-header-end">

    <?php
    // Display messages
    $message_data = array(
        'errors' => $errors,
        'success' => $success,
        'success_message' => $success_message,
        'notices' => $notices
    );
    $dpw_rui_foto->render_template('message-display', $message_data);
    ?>

    <div class="row">
        <div class="col-lg-8">
            <?php
            // Display upload form
            $upload_data = array(
                'anggota_id' => $anggota_id,
                'existing_count' => $existing_count,
                'max_photos' => DPW_RUI_MAX_PHOTOS
            );
            $dpw_rui_foto->render_template('upload-form', $upload_data);

            // Display photo grid
            $grid_data = array(
                'photos' => $photos,
                'anggota_id' => $anggota_id,
                'can_manage' => $can_manage
            );
            $dpw_rui_foto->render_template('grid-manage', $grid_data);
            ?>
        </div>

        <div class="col-lg-4">
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
                            <li>Maksimal <?php echo DPW_RUI_MAX_PHOTOS; ?> foto (1 utama + <?php echo DPW_RUI_MAX_PHOTOS - 1; ?> tambahan)</li>
                            <li>Format: JPG, PNG, GIF</li>
                            <li>Maksimal ukuran: 1.8 MB</li>
                            <li>Foto pertama otomatis jadi foto utama</li>
                        </ul>
                    </div>

                    <div class="foto-stats mb-3">
                        <h6 class="font-weight-bold mb-2">Status Foto:</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>Total Foto</td>
                                <td class="text-right"><?php echo $existing_count; ?> / <?php echo DPW_RUI_MAX_PHOTOS; ?></td>
                            </tr>
                            <tr>
                                <td>Foto Utama</td>
                                <td class="text-right">
                                    <?php echo $dpw_rui_foto->get_main_photo($anggota_id) ? 
                                        '<span class="text-success"><i class="fas fa-check-circle"></i> Ada</span>' : 
                                        '<span class="text-danger"><i class="fas fa-times-circle"></i> Belum Ada</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Sisa Slot</td>
                                <td class="text-right"><?php echo DPW_RUI_MAX_PHOTOS - $existing_count; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?php echo admin_url('admin.php?page=dpw-rui&action=view&id=' . $anggota_id); ?>" 
           class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Detail Anggota
        </a>
    </div>
</div>