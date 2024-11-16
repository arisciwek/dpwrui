<?php
/**
* Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-foto.php
* Version: 1.2.0
* Timestamp: 2024-03-16 15:30:00
* 
* Changelog:
* 1.2.0 (2024-03-16)
* - Removed WordPress Media Library dependencies
* - Added direct file system operations
* - Updated file validation and security checks
* - Added upload directory management
* - Added file cleanup on delete
*/

class DPW_RUI_Foto {
    private $wpdb;
    private $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    private $max_size = 1887436; // 1.8MB in bytes
    private $upload_path;
    private $upload_url;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        $upload_dir = wp_upload_dir();
        $this->upload_path = $upload_dir['basedir'] . '/dpw-rui/';
        $this->upload_url = $upload_dir['baseurl'] . '/dpw-rui/';
        
        $this->ensure_upload_directory();
    }

    /**
     * Memastikan direktori upload ada dan aman
     */
    private function ensure_upload_directory() {
        if (!file_exists($this->upload_path)) {
            if (!wp_mkdir_p($this->upload_path)) {
                error_log('DPW RUI: Failed to create main upload directory: ' . $this->upload_path);
                return false;
            }
            
            // Set proper permissions
            @chmod($this->upload_path, 0777);
        }
        
        // Double check directory is writable
        if (!is_writable($this->upload_path)) {
            error_log('DPW RUI: Upload directory is not writable: ' . $this->upload_path);
            return false;
        }
        
        // Protect directory
        $htaccess = $this->upload_path . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, 
                "Options -Indexes\n" .
                "<Files *.php>\n" .
                "Order Deny,Allow\n" .
                "Deny from all\n" .
                "</Files>"
            );
        }
        
        return true;
    }
    
    /**
     * Get user upload directory
     */
    private function get_user_upload_dir($user_id) {
        $user_dir = $this->upload_path . $user_id . '/';
        
        if (!file_exists($user_dir)) {
            if (!wp_mkdir_p($user_dir)) {
                error_log('DPW RUI: Failed to create user upload directory: ' . $user_dir);
                return false;
            }
            // Set proper permissions for user directory
            @chmod($user_dir, 0777);
        }
        
        // Double check directory is writable
        if (!is_writable($user_dir)) {
            error_log('DPW RUI: User upload directory is not writable: ' . $user_dir);
            return false;
        }
        
        return $user_dir;
    }
    
    /**
     * Validate file upload
     */
    public function validate_upload($file) {
        $errors = array();
        
        // Check if upload directory is writable
        if (!$this->ensure_upload_directory()) {
            $errors[] = 'Direktori upload tidak dapat diakses. Hubungi administrator.';
            return $errors;
        }
        
        // Check file presence
        if (empty($file['name'])) {
            $errors[] = 'Tidak ada file yang dipilih.';
            return $errors;
        }
        
        // Check file type
        if (!in_array($file['type'], $this->allowed_types)) {
            $errors[] = 'Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF';
        }
        
        // Check file size
        if ($file['size'] > $this->max_size) {
            $errors[] = 'Ukuran file terlalu besar. Maksimal 1.8 MB.';
        }
        
        // Basic security checks
        $filename = $file['name'];
        if (preg_match('/\.(php|phtml|php3|php4|php5|phps)$/i', $filename)) {
            $errors[] = 'Tipe file tidak diizinkan karena alasan keamanan.';
        }
        
        return $errors;
    }
    
    /**
     * Handle file upload
     */
    public function handle_upload($file, $anggota_id) {
        $user_id = get_current_user_id();
        $user_dir = $this->get_user_upload_dir($user_id);
        
        if ($user_dir === false) {
            return new WP_Error('upload_error', 'Gagal membuat direktori upload.');
        }
        
        // Generate safe filename
        $filename = wp_unique_filename($user_dir, sanitize_file_name($file['name']));
        $filepath = $user_dir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            error_log('DPW RUI: Failed to move uploaded file to: ' . $filepath);
            return new WP_Error('upload_error', 'Gagal mengupload file.');
        }
        
        // Set correct file permissions
        @chmod($filepath, 0644);
        
        // Generate file URL
        $fileurl = $this->upload_url . $user_id . '/' . $filename;
        
        return array(
            'filename' => $filename,
            'filepath' => $filepath,
            'fileurl' => $fileurl,
            'filetype' => $file['type'],
            'filesize' => $file['size']
        );
    }
    
    /**
     * Save photo to database
     */
    public function save_photo($anggota_id, $file_data, $is_main = 0) {
        return $this->wpdb->insert(
            $this->wpdb->prefix . 'dpw_rui_anggota_foto',
            array(
                'anggota_id' => $anggota_id,
                'filename' => $file_data['filename'],
                'file_path' => $file_data['filepath'],
                'file_url' => $file_data['fileurl'],
                'file_type' => $file_data['filetype'],
                'file_size' => $file_data['filesize'],
                'is_main' => $is_main,
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id()
            ),
            array('%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d')
        );
    }
    
    /**
     * Get photos for anggota
     */
    public function get_photos($anggota_id) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
             WHERE anggota_id = %d 
             ORDER BY is_main DESC, id ASC",
            $anggota_id
        ));
    }
    
    /**
     * Get main photo
     */
    public function get_main_photo($anggota_id) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
             WHERE anggota_id = %d AND is_main = 1",
            $anggota_id
        ));
    }
    
    /**
     * Set photo as main
     */
    public function set_main_photo($photo_id, $anggota_id) {
        // Reset all photos to non-main
        $this->wpdb->update(
            $this->wpdb->prefix . 'dpw_rui_anggota_foto',
            array('is_main' => 0),
            array('anggota_id' => $anggota_id),
            array('%d'),
            array('%d')
        );
        
        // Set selected photo as main
        return $this->wpdb->update(
            $this->wpdb->prefix . 'dpw_rui_anggota_foto',
            array('is_main' => 1),
            array('id' => $photo_id),
            array('%d'),
            array('%d')
        );
    }
    
    /**
     * Delete photo
     */
    public function delete_photo($photo_id, $anggota_id) {
        $photo = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
             WHERE id = %d AND anggota_id = %d",
            $photo_id,
            $anggota_id
        ));
        
        if($photo) {
            // Delete physical file
            if(file_exists($photo->file_path)) {
                unlink($photo->file_path);
            }
            
            // Delete from database
            return $this->wpdb->delete(
                $this->wpdb->prefix . 'dpw_rui_anggota_foto',
                array('id' => $photo_id),
                array('%d')
            );
        }
        
        return false;
    }
    
    /**
     * Count photos
     */
    public function count_photos($anggota_id) {
        return $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
             WHERE anggota_id = %d",
            $anggota_id
        ));
    }
    
    /**
     * Clean up orphaned files
     */
    public function cleanup_orphaned_files() {
        $photos = $this->wpdb->get_results(
            "SELECT file_path FROM {$this->wpdb->prefix}dpw_rui_anggota_foto"
        );
        
        $db_files = array();
        foreach($photos as $photo) {
            $db_files[] = basename($photo->file_path);
        }
        
        // Scan upload directory
        foreach(glob($this->upload_path . '*/*.*') as $file) {
            if(!in_array(basename($file), $db_files)) {
                unlink($file); // Delete file not in database
            }
        }
    }
}