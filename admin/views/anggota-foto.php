<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/anggota-foto.php
 * Version: 1.3.0
 * 
 * Changelog:
 * 1.3.0
 * - Simplified file upload handling
 * - Fixed form submission process
 * - Removed duplicate upload handling
 * - Added proper nonce verification
 * - Added explicit error handling
 * - Fixed integration with DPW_RUI_Foto class
 * 
 * 1.2.0 
 * - Previous version functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

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
$can_manage = current_user_can('dpw_rui_update') || 
              (current_user_can('dpw_rui_edit_own') && $anggota->created_by == get_current_user_id());

if (!$can_manage) {
    wp_die(__('Anda tidak memiliki akses untuk mengelola foto.'));
}

// Get existing photos
$photos = $dpw_rui_foto->get_photos($anggota_id);
$existing_count = count($photos);

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Verify nonce
    check_admin_referer('dpw_rui_upload_foto_' . $anggota_id);

    // Handle file upload
    if (isset($_FILES['foto']) && !empty($_FILES['foto']['name'])) {
        try {
            // Upload file
            $upload_result = $dpw_rui_foto->handle_upload($_FILES['foto'], $anggota_id);
            
            if (is_wp_error($upload_result)) {
                throw new Exception($upload_result->get_error_message());
            }

            $success = true;
            $success_message = 'Foto berhasil diupload.';
            
            // Refresh photos list
            $photos = $dpw_rui_foto->get_photos($anggota_id);
            $existing_count = count($photos);
            
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    } else {
        $errors[] = 'Tidak ada file yang dipilih.';
    }
}

// Handle GET actions (delete, set main)
$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action) {
    switch($action) {
        case 'delete':
            if (isset($_GET['delete']) && check_admin_referer('dpw_rui_delete_foto_' . $_GET['delete'])) {
                $photo_id = absint($_GET['delete']);
                
                if ($dpw_rui_foto->delete_photo($photo_id, $anggota_id)) {
                    $success = true;
                    $success_message = 'Foto berhasil dihapus.';
                    
                    // Refresh photos
                    $photos = $dpw_rui_foto->get_photos($anggota_id);
                    $existing_count = count($photos);
                } else {
                    $errors[] = 'Gagal menghapus foto.';
                }
            }
            break;

        case 'set_main':
            if (isset($_GET['set_main']) && check_admin_referer('dpw_rui_set_main_foto_' . $_GET['set_main'])) {
                $photo_id = absint($_GET['set_main']);
                
                if ($dpw_rui_foto->set_main_photo($photo_id, $anggota_id)) {
                    $success = true;
                    $success_message = 'Foto utama berhasil diubah.';
                    
                    // Refresh photos
                    $photos = $dpw_rui_foto->get_photos($anggota_id);
                } else {
                    $errors[] = 'Gagal mengubah foto utama.';
                }
            }
            break;
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
    require_once DPW_RUI_PLUGIN_DIR . 'admin/views/templates/foto/message-display.php';
    ?>

    <div class="row">
        <div class="col-lg-4">
            <?php
            // Display upload form
            $upload_data = array(
                'anggota_id' => $anggota_id,
                'existing_count' => $existing_count,
                'max_photos' => 4,
                'main_photo' => $dpw_rui_foto->get_main_photo($anggota_id)
            );
            require_once DPW_RUI_PLUGIN_DIR . 'admin/views/templates/foto/upload-form.php';
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
            require_once DPW_RUI_PLUGIN_DIR . 'admin/views/templates/foto/grid-manage.php';
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