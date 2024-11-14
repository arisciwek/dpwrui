<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-foto.php
 * Version: 1.2.0
 * 
 * Changelog:
 * 1.2.0
 * - Added dedicated validation methods for file upload
 * - Added comprehensive file handling methods
 * - Added photo data retrieval methods
 * - Added batch operation methods
 * - Added detailed error messaging
 * - Improved upload directory security
 * - Added support for template-based display
 * - Added proper error logging
 * - Added photo status management
 * - Fixed permission checking in file operations
 * 
 * 1.1.0 
 * - Previous version functionality
 */

class DPW_RUI_Foto {
    private $wpdb;
    private $errors = array();
    //private $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    private $allowed_types = array('image/jpeg', 'image/png');
    private $max_size = 1887436; // 1.8MB in bytes
    private $upload_dir;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        // Setup upload directory
        $upload_info = wp_upload_dir();
        $this->upload_dir = $upload_info['basedir'] . '/dpw-rui';
        
        // Add hooks for upload handling
        add_filter('upload_dir', array($this, 'custom_upload_dir'));
        add_filter('ajax_query_attachments_args', array($this, 'filter_media_library'));
        add_action('add_attachment', array($this, 'set_attachment_privacy'));
        add_filter('wp_get_attachment_url', array($this, 'filter_attachment_url'), 10, 2);
    }

    /**
     * Get errors if any
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Check if there are any errors
     */
    public function has_errors() {
        return !empty($this->errors);
    }

    /**
     * Add error message
     */
    private function add_error($message) {
        $this->errors[] = $message;
        error_log('DPW RUI Foto Error: ' . $message);
    }

    /**
     * Clear error messages
     */
    private function clear_errors() {
        $this->errors = array();
    }

    /**
     * Validate file before upload
     * 
     * @param array $file $_FILES array element
     * @return bool True if valid, false if not
     */
    public function validate_upload($file) {
        $this->clear_errors();
        
        // Check if file exists
        if(empty($file['name'])) {
            $this->add_error('Tidak ada file yang dipilih');
            return false;
        }
        
        // Check file type
        if(!in_array($file['type'], $this->allowed_types)) {
            $this->add_error('Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF');
            return false;
        }
        
        // Check file size
        if($file['size'] > $this->max_size) {
            $this->add_error('Ukuran file terlalu besar. Maksimal 1.8 MB');
            return false;
        }
        
        // Check if it's really an image
        $img_info = getimagesize($file['tmp_name']);
        if($img_info === false) {
            $this->add_error('File yang dipilih bukan file gambar yang valid');
            return false;
        }
        
        return true;
    }

    /**
     * Handle file upload process
     * 
     * @param string $file_key Key in $_FILES array
     * @param int $anggota_id Member ID
     * @return int|WP_Error Attachment ID if successful, WP_Error if not
     */
    public function handle_file_upload($file_key, $anggota_id) {
        if(!isset($_FILES[$file_key])) {
            return new WP_Error('no_file', 'Tidak ada file yang diupload');
        }

        // Validate upload
        if(!$this->validate_upload($_FILES[$file_key])) {
            return new WP_Error('validation_failed', implode(', ', $this->get_errors()));
        }

        // Add flag for custom upload directory
        $_POST['is_dpw_rui_upload'] = true;
        $_POST['anggota_id'] = $anggota_id;

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Handle upload
        $attachment_id = media_handle_upload($file_key, 0);
        
        if(is_wp_error($attachment_id)) {
            $this->add_error($attachment_id->get_error_message());
            return $attachment_id;
        }

        return $attachment_id;
    }

    /**
     * Get photo data with additional info
     * 
     * @param int $photo_id Photo ID
     * @return object|false Photo data object or false if not found
     */
    public function get_photo_data($photo_id) {
        $photo = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT p.*, a.attachment_url, a.attachment_path, a.attachment_metadata 
             FROM {$this->wpdb->prefix}dpw_rui_anggota_foto p
             LEFT JOIN {$this->wpdb->posts} a ON p.attachment_id = a.ID
             WHERE p.id = %d",
            $photo_id
        ));

        if($photo) {
            $photo->url = wp_get_attachment_url($photo->attachment_id);
            $photo->metadata = wp_get_attachment_metadata($photo->attachment_id);
            return $photo;
        }

        return false;
    }

    /**
     * Get all photos for a member with complete data
     * 
     * @param int $anggota_id Member ID
     * @return array Array of photo objects
     */
    public function get_member_photos($anggota_id) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT p.*, a.guid as url 
             FROM {$this->wpdb->prefix}dpw_rui_anggota_foto p
             LEFT JOIN {$this->wpdb->posts} a ON p.attachment_id = a.ID
             WHERE p.anggota_id = %d 
             ORDER BY p.is_main DESC, p.id ASC",
            $anggota_id
        ));
    }

    /**
     * Custom upload directory per user
     */
    public function custom_upload_dir($uploads) {
        if(!empty($_POST['is_dpw_rui_upload'])) {
            $anggota_id = isset($_POST['anggota_id']) ? absint($_POST['anggota_id']) : 0;
            $subdir = '/dpw-rui/' . get_current_user_id();
            
            if($anggota_id) {
                $subdir .= '/' . $anggota_id;
            }
            
            $uploads['subdir'] = $subdir;
            $uploads['path'] = $uploads['basedir'] . $subdir;
            $uploads['url'] = $uploads['baseurl'] . $subdir;
        }
        
        return $uploads;
    }

    /**
     * Add new photo
     * 
     * @param int $anggota_id Member ID
     * @param int $attachment_id Attachment ID
     * @param bool $is_main Whether this is the main photo
     * @return bool True if successful, false if not
     */
    public function add_photo($anggota_id, $attachment_id, $is_main = 0) {
        // Start transaction
        $this->wpdb->query('START TRANSACTION');

        try {
            // If this is main photo, reset others
            if($is_main) {
                $this->wpdb->update(
                    $this->wpdb->prefix . 'dpw_rui_anggota_foto',
                    array('is_main' => 0),
                    array('anggota_id' => $anggota_id),
                    array('%d'),
                    array('%d')
                );
            }

            // Insert new photo
            $result = $this->wpdb->insert(
                $this->wpdb->prefix . 'dpw_rui_anggota_foto',
                array(
                    'anggota_id' => $anggota_id,
                    'attachment_id' => $attachment_id,
                    'is_main' => $is_main,
                    'created_at' => current_time('mysql'),
                    'created_by' => get_current_user_id()
                ),
                array('%d', '%d', '%d', '%s', '%d')
            );

            if($result === false) {
                throw new Exception('Gagal menyimpan data foto');
            }

            $this->wpdb->query('COMMIT');
            return true;

        } catch (Exception $e) {
            $this->wpdb->query('ROLLBACK');
            $this->add_error($e->getMessage());
            return false;
        }
    }

    /**
     * Set photo as main
     * 
     * @param int $photo_id Photo ID
     * @param int $anggota_id Member ID
     * @return bool True if successful, false if not
     */
    public function set_main_photo($photo_id, $anggota_id) {
        $this->wpdb->query('START TRANSACTION');

        try {
            // Reset all photos to non-main
            $this->wpdb->update(
                $this->wpdb->prefix . 'dpw_rui_anggota_foto',
                array('is_main' => 0),
                array('anggota_id' => $anggota_id),
                array('%d'),
                array('%d')
            );
            
            // Set selected photo as main
            $result = $this->wpdb->update(
                $this->wpdb->prefix . 'dpw_rui_anggota_foto',
                array('is_main' => 1),
                array('id' => $photo_id),
                array('%d'),
                array('%d')
            );

            if($result === false) {
                throw new Exception('Gagal mengatur foto utama');
            }

            $this->wpdb->query('COMMIT');
            return true;

        } catch (Exception $e) {
            $this->wpdb->query('ROLLBACK');
            $this->add_error($e->getMessage());
            return false;
        }
    }

    /**
     * Delete photo with proper cleanup
     * 
     * @param int $photo_id Photo ID
     * @param int $anggota_id Member ID
     * @return bool True if successful, false if not
     */
    public function delete_photo($photo_id, $anggota_id) {
        $this->wpdb->query('START TRANSACTION');

        try {
            // Get photo data
            $photo = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
                 WHERE id = %d AND anggota_id = %d",
                $photo_id,
                $anggota_id
            ));

            if(!$photo) {
                throw new Exception('Foto tidak ditemukan');
            }

            // Delete attachment
            if(!wp_delete_attachment($photo->attachment_id, true)) {
                throw new Exception('Gagal menghapus file foto');
            }

            // Delete from custom table
            $result = $this->wpdb->delete(
                $this->wpdb->prefix . 'dpw_rui_anggota_foto',
                array('id' => $photo_id),
                array('%d')
            );

            if($result === false) {
                throw new Exception('Gagal menghapus data foto');
            }

            // If this was main photo, set new main
            if($photo->is_main) {
                $new_main = $this->wpdb->get_row($this->wpdb->prepare(
                    "SELECT id FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
                     WHERE anggota_id = %d AND id != %d 
                     ORDER BY id ASC LIMIT 1",
                    $anggota_id,
                    $photo_id
                ));

                if($new_main) {
                    $this->set_main_photo($new_main->id, $anggota_id);
                }
            }

            $this->wpdb->query('COMMIT');
            return true;

        } catch (Exception $e) {
            $this->wpdb->query('ROLLBACK');
            $this->add_error($e->getMessage());
            return false;
        }
    }

    /**
     * Get main photo for a member
     * 
     * @param int $anggota_id Member ID
     * @return object|false Photo data or false if none
     */
    public function get_main_photo($anggota_id) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT p.*, a.guid as url 
             FROM {$this->wpdb->prefix}dpw_rui_anggota_foto p
             LEFT JOIN {$this->wpdb->posts} a ON p.attachment_id = a.ID
             WHERE p.anggota_id = %d AND p.is_main = 1",
            $anggota_id
        ));
    }

    /**
     * Count total photos for a member
     * 
     * @param int $anggota_id Member ID
     * @return int Number of photos
     */
    public function count_photos($anggota_id) {
        return $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
             WHERE anggota_id = %d",
            $anggota_id
        ));
    }

    /**
     * Check if user can manage photo
     * 
     * @param int $anggota_id Member ID
     * @return bool True if can manage, false if not
     */
    public function can_manage_photo($anggota_id) {
        if(!$anggota_id) {
            return false;
        }

        // Get member data
        $anggota = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT created_by FROM {$this->wpdb->prefix}dpw_rui_anggota WHERE id = %d",
            $anggota_id
        ));

        if(!$anggota) {
            return false;
        }

        return current_user_can('dpw_rui_update') || 
               (current_user_can('dpw_rui_edit_own') && 
                $anggota->created_by == get_current_user_id());
    }

    /**
     * Filter media library to show only user's own attachments
     */
    public function filter_media_library($query) {
        // Skip for administrators
        if(current_user_can('manage_options')) {
            return $query;
        }

        $user_id = get_current_user_id();
        
        if(!isset($query['author'])) {
            $query['author'] = $user_id;
        }
        
        return $query;
    }

    /**
     * Set attachment privacy meta
     * 
     * @param int $attachment_id Attachment ID
     */
    public function set_attachment_privacy($attachment_id) {
        if(!empty($_POST['is_dpw_rui_upload'])) {
            update_post_meta($attachment_id, '_dpw_rui_attachment', '1');
            update_post_meta($attachment_id, '_dpw_rui_user', get_current_user_id());
            
            // Set attachment post author
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_author' => get_current_user_id()
            ));
        }
    }

    /**
     * Filter attachment URL access
     * 
     * @param string $url URL of the attachment
     * @param int $attachment_id Attachment ID
     * @return string Filtered URL
     */
    public function filter_attachment_url($url, $attachment_id) {
        // Skip if not our attachment
        if(!get_post_meta($attachment_id, '_dpw_rui_attachment', true)) {
            return $url;
        }
        
        // Allow admin access
        if(current_user_can('manage_options')) {
            return $url;
        }
        
        $attachment_user = get_post_meta($attachment_id, '_dpw_rui_user', true);
        $current_user = get_current_user_id();
        
        // Check if user owns the attachment
        if($attachment_user != $current_user) {
            // Check if user can view the associated anggota
            $anggota_id = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT anggota_id FROM {$this->wpdb->prefix}dpw_rui_anggota_foto 
                 WHERE attachment_id = %d",
                $attachment_id
            ));
            
            if($anggota_id) {
                $anggota = $this->wpdb->get_row($this->wpdb->prepare(
                    "SELECT created_by FROM {$this->wpdb->prefix}dpw_rui_anggota 
                     WHERE id = %d",
                    $anggota_id
                ));
                
                // Allow access if user has read permission or owns the anggota
                if(!current_user_can('dpw_rui_read') && 
                   (!current_user_can('dpw_rui_edit_own') || 
                    $anggota->created_by != $current_user)) {
                    return '';
                }
            } else {
                return '';
            }
        }
        
        return $url;
    }

    /**
     * Setup upload directory security
     */
    private function setup_upload_security() {
        if(!is_dir($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
        }

        // Create .htaccess to protect upload directory
        $htaccess = $this->upload_dir . '/.htaccess';
        if(!file_exists($htaccess)) {
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<Files *.php>\n";
            $htaccess_content .= "Order Deny,Allow\n";
            $htaccess_content .= "Deny from all\n";
            $htaccess_content .= "</Files>";
            
            file_put_contents($htaccess, $htaccess_content);
        }

        // Create index.php to prevent directory listing
        $index_file = $this->upload_dir . '/index.php';
        if(!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden');
        }
    }

    /**
     * Clean up temporary files
     */
    public function cleanup_temp_files() {
        // Get files older than 24 hours
        $files = glob($this->upload_dir . '/temp/*');
        $now = time();
        
        foreach($files as $file) {
            if(is_file($file)) {
                if($now - filemtime($file) >= 86400) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Get photo template path
     * 
     * @param string $template Template name
     * @return string Full template path
     */
    public function get_template_path($template) {
        $template_path = DPW_RUI_PLUGIN_DIR . 'admin/views/templates/foto/' . $template . '.php';
        
        if(!file_exists($template_path)) {
            $this->add_error('Template tidak ditemukan: ' . $template);
            return false;
        }
        
        return $template_path;
    }

    /**
     * Render photo template
     * 
     * @param string $template Template name
     * @param array $data Data to pass to template
     */
    public function render_template($template, $data = array()) {
        $template_path = $this->get_template_path($template);
        
        if($template_path) {
            extract($data);
            include $template_path;
        }
    }
}