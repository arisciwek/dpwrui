<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-foto.php
 * Version: 2.2.0
 * 
 * Changelog:
 * 2.2.0
 * - Simplified file upload process
 * - Fixed upload directory handling
 * - Added proper mime type validation
 * - Improved error messages
 * - Fixed file path security issues
 * - Added proper cleanup on delete
 * - Fixed file size validation
 * 
 * 2.1.0
 * - Previous version functionality
 */

class DPW_RUI_Foto {
    private $wpdb;
    private $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    private $max_size = 1887436; // 1.8MB in bytes
    private $upload_dir;
    private $upload_url;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        // Set upload paths
        $wp_upload_dir = wp_upload_dir();
        $this->upload_dir = $wp_upload_dir['basedir'] . '/dpw-rui';
        $this->upload_url = $wp_upload_dir['baseurl'] . '/dpw-rui';
        
        $this->init_upload_dir();
    }

    private function init_upload_dir() {
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
            
            // Create .htaccess
            $htaccess = $this->upload_dir . '/.htaccess';
            if (!file_exists($htaccess)) {
                $content = "Options -Indexes\n";
                $content .= "<Files *.php>\n";
                $content .= "Order Deny,Allow\n";
                $content .= "Deny from all\n";
                $content .= "</Files>";
                file_put_contents($htaccess, $content);
            }
            
            // Create index.php
            $index = $this->upload_dir . '/index.php';
            if (!file_exists($index)) {
                file_put_contents($index, '<?php // Silence is golden');
            }
        }
    }

    public function handle_upload($file, $anggota_id) {
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', $this->get_upload_error_message($file['error']));
        }

        // Check file size
        if ($file['size'] > $this->max_size) {
            return new WP_Error('file_too_large', 'Ukuran file terlalu besar. Maksimal 1.8 MB');
        }

        // Validate mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $this->allowed_types)) {
            return new WP_Error('invalid_type', 'Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF');
        }

        // Create member directory
        $member_dir = $this->upload_dir . '/' . $anggota_id;
        if (!file_exists($member_dir)) {
            wp_mkdir_p($member_dir);
        }

        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = sprintf(
            '%s-%s.%s',
            $anggota_id,
            uniqid(),
            strtolower($extension)
        );
        
        $filepath = $member_dir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return new WP_Error('move_error', 'Gagal memindahkan file yang diupload');
        }

        // Add to database
        $result = $this->wpdb->insert(
            $this->wpdb->prefix . 'dpw_rui_anggota_foto',
            array(
                'anggota_id' => $anggota_id,
                'filename' => $filename,
                'is_main' => $this->count_photos($anggota_id) === 0 ? 1 : 0,
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id()
            ),
            array('%d', '%s', '%d', '%s', '%d')
        );

        if (!$result) {
            unlink($filepath);
            return new WP_Error('db_error', 'Gagal menyimpan data foto');
        }

        return array(
            'id' => $this->wpdb->insert_id,
            'filename' => $filename,
            'url' => $this->upload_url . '/' . $anggota_id . '/' . $filename
        );
    }

    public function get_photos($anggota_id) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
             WHERE anggota_id = %d 
             ORDER BY is_main DESC, id ASC",
            $anggota_id
        ));
    }

    public function get_main_photo($anggota_id) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
             WHERE anggota_id = %d AND is_main = 1 
             LIMIT 1",
            $anggota_id
        ));
    }

    public function count_photos($anggota_id) {
        return (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
             WHERE anggota_id = %d",
            $anggota_id
        ));
    }

    public function delete_photo($photo_id, $anggota_id) {
        $photo = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
             WHERE id = %d AND anggota_id = %d",
            $photo_id,
            $anggota_id
        ));

        if (!$photo) {
            return false;
        }

        // Delete file
        $filepath = $this->upload_dir . '/' . $anggota_id . '/' . $photo->filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Delete from database
        $result = $this->wpdb->delete(
            $this->wpdb->prefix . 'dpw_rui_anggota_foto',
            array('id' => $photo_id),
            array('%d')
            );

        // If deleted photo was main photo, set another photo as main
        if ($photo->is_main) {
            $new_main = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT id FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
                 WHERE anggota_id = %d AND id != %d 
                 ORDER BY created_at ASC LIMIT 1",
                $anggota_id,
                $photo_id
            ));
            
            if ($new_main) {
                $this->set_main_photo($new_main, $anggota_id);
            }
        }

        return true;
    }

    public function set_main_photo($photo_id, $anggota_id) {
        // First unset any existing main photo
        $this->wpdb->update(
            $this->wpdb->prefix . 'dpw_rui_anggota_foto',
            array('is_main' => 0),
            array('anggota_id' => $anggota_id),
            array('%d'),
            array('%d')
        );

        // Set the new main photo
        return $this->wpdb->update(
            $this->wpdb->prefix . 'dpw_rui_anggota_foto',
            array('is_main' => 1),
            array('id' => $photo_id, 'anggota_id' => $anggota_id),
            array('%d'),
            array('%d', '%d')
        );
    }

    private function get_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Ukuran file melebihi batas maksimal upload_max_filesize di PHP.INI';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Ukuran file melebihi batas maksimal yang ditentukan di form';
            case UPLOAD_ERR_PARTIAL:
                return 'File hanya terupload sebagian';
            case UPLOAD_ERR_NO_FILE:
                return 'Tidak ada file yang diupload';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Folder temporary tidak ditemukan';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Gagal menulis file ke disk';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload dihentikan oleh ekstensi PHP';
            default:
                return 'Terjadi kesalahan yang tidak diketahui saat upload';
        }
    }

    public function get_foto_url($foto) {
        if (!$foto || !$foto->filename || !$foto->anggota_id) {
            return '';
        }
        return $this->upload_url . '/' . $foto->anggota_id . '/' . $foto->filename;
    }

    public function get_foto_path($foto) {
        if (!$foto || !$foto->filename || !$foto->anggota_id) {
            return '';
        }
        
        // Build safe path
        $path = sprintf(
            '%s/%d/%s',
            rtrim($this->upload_dir, '/'),
            absint($foto->anggota_id),
            sanitize_file_name($foto->filename)
        );
        
        // Validate path is within upload directory
        $real_path = realpath($path);
        if ($real_path === false || strpos($real_path, $this->upload_dir) !== 0) {
            return '';
        }
        
        return $path;
    }

    public function clean_old_temp_files() {
        // Get all member directories
        $dirs = glob($this->upload_dir . '/*', GLOB_ONLYDIR);
        
        foreach ($dirs as $dir) {
            if (basename($dir) === '.' || basename($dir) === '..') {
                continue;
            }

            // Get all files in directory
            $files = glob($dir . '/*.*');
            $now = time();
            
            foreach ($files as $file) {
                // Skip if not a file
                if (!is_file($file)) {
                    continue;
                }
                
                // Delete files older than 24 hours that aren't in database
                $mtime = filemtime($file);
                if (($now - $mtime) > 86400) {
                    $filename = basename($file);
                    $exists = $this->wpdb->get_var($this->wpdb->prepare(
                        "SELECT COUNT(*) FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
                         WHERE filename = %s",
                        $filename
                    ));
                    
                    if (!$exists) {
                        unlink($file);
                    }
                }
            }
        }
    }

    public function validate_upload_directory() {
        // Check if upload directory exists and is writable
        if (!file_exists($this->upload_dir)) {
            return new WP_Error('upload_dir_missing', 
                'Direktori upload tidak ditemukan');
        }

        if (!is_writable($this->upload_dir)) {
            return new WP_Error('upload_dir_not_writable', 
                'Direktori upload tidak dapat ditulis');
        }

        // Check for .htaccess
        if (!file_exists($this->upload_dir . '/.htaccess')) {
            return new WP_Error('htaccess_missing', 
                'File .htaccess tidak ditemukan di direktori upload');
        }

        return true;
    }

    public function check_disk_space() {
        $free_space = disk_free_space($this->upload_dir);
        
        // Alert if less than 100MB free
        if ($free_space < 104857600) {
            return new WP_Error('low_disk_space', 
                'Ruang disk hampir penuh. Silahkan hubungi administrator.');
        }

        return true;
    }

    public function register_cleanup_schedule() {
        if (!wp_next_scheduled('dpw_rui_cleanup_temp_files')) {
            wp_schedule_event(time(), 'daily', 'dpw_rui_cleanup_temp_files');
        }
    }

    public function deregister_cleanup_schedule() {
        wp_clear_scheduled_hook('dpw_rui_cleanup_temp_files');
    }
}
