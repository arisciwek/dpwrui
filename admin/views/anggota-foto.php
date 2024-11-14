<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/anggota-foto.php
 * Version: 1.2.0
 * 
 * Changelog:
 * 1.2.0
 * - Restructured layout grid using col-lg-5 and col-lg-7
 * - Moved Card Informasi to upload-form.php
 * - Fixed page header and message display positions
 * - Added proper spacing between components
 * - Improved responsive behavior 
 * 
 * 1.1.0 
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

// Handle photo upload
if (isset($_POST['submit']) && check_admin_referer('dpw_rui_upload_foto_' . $anggota_id, 'dpw_rui_upload_nonce')) {
    if (!isset($_FILES['foto']) || empty($_FILES['foto']['name'])) {
        wp_die('No file uploaded');
    }

    $file = $_FILES['foto'];
    
    // Validate file type
    if (!in_array($file['type'], array('image/jpeg', 'image/png', 'image/gif'))) {
        wp_die('Invalid file type. Allowed: JPG, PNG, GIF');
    }
    
    // Validate file size (1.8MB)
    if ($file['size'] > 1887436) {
        wp_die('File too large. Max: 1.8MB');
    }

    // Get existing photos count
    $existing_photos = $dpw_rui_foto->get_member_photos($anggota_id);
    if (count($existing_photos) >= 4) {
        wp_die('Maximum photos limit reached (4)');
    }

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    // Upload file to WordPress media library
    $attachment_id = media_handle_upload('foto', 0);
    if (is_wp_error($attachment_id)) {
        wp_die($attachment_id->get_error_message());
    }

    // Add photo record
    $is_main = (count($existing_photos) == 0) ? 1 : 0;
    $result = $dpw_rui_foto->add_photo($anggota_id, $attachment_id, $is_main);
    
    if (!$result) {
        wp_delete_attachment($attachment_id, true);
        wp_die('Failed to save photo record');
    }

    wp_redirect(add_query_arg(array(
        'page' => 'dpw-rui',
        'action' => 'foto',
        'id' => $anggota_id,
        'message' => 'uploaded'
    ), admin_url('admin.php')));
    exit;
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
        <div class="col-lg-4">
            <?php
            // Display upload form with info card
            $upload_data = array(
                'anggota_id' => $anggota_id,
                'existing_count' => $existing_count,
                'max_photos' => DPW_RUI_MAX_PHOTOS,
                'main_photo' => $dpw_rui_foto->get_main_photo($anggota_id)
            );
            $dpw_rui_foto->render_template('upload-form', $upload_data);
            ?>
        </div>

        <div class="col-lg-8">
            <?php
            // Display photo grid
            $grid_data = array(
                'photos' => $photos,
                'anggota_id' => $anggota_id,
                'can_manage' => $can_manage
            );
            $dpw_rui_foto->render_template('grid-manage', $grid_data);
            ?>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?php echo admin_url('admin.php?page=dpw-rui&action=view&id=' . $anggota_id); ?>" 
           class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Detail Anggota
        </a>
    </div>
</div>
