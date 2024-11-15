<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-foto.php
 * Version: 1.1.0
 * 
 * Changelog:
 * 1.1.0
 * - Merged photo handling logic from class-dpw-rui.php
 * - Added upload directory customization
 * - Added media library filtering
 * - Added attachment privacy handling
 * - Improved error handling
 * - Added attachment URL filtering
 * - Added validation for file types and sizes
 * 
 * 1.0.0 
 * - Initial release with basic photo management
 */

class DPW_RUI_Foto {
    private $wpdb;
    private $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    private $max_size = 1887436; // 1.8MB in bytes
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;

        // Add hooks for upload handling
        add_filter('upload_dir', array($this, 'custom_upload_dir'));
        add_filter('ajax_query_attachments_args', array($this, 'filter_media_library'));
        add_action('add_attachment', array($this, 'set_attachment_privacy'));
        add_filter('wp_get_attachment_url', array($this, 'filter_attachment_url'), 10, 2);
    }

    /**
     * Custom upload directory per user
     */
    public function custom_upload_dir($uploads) {
        // Only modify for our plugin uploads
        if(!empty($_POST['is_dpw_rui_upload'])) {
            $user_id = get_current_user_id();
            $subdir = '/dpw-rui/' . $user_id;
            
            $uploads['subdir'] = $subdir;
            $uploads['path'] = $uploads['basedir'] . $subdir;
            $uploads['url'] = $uploads['baseurl'] . $subdir;
        }
        
        return $uploads;
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
                "SELECT anggota_id FROM {$this->wpdb->prefix}dpw_rui_anggota_foto WHERE attachment_id = %d",
                $attachment_id
            ));
            
            if($anggota_id) {
                $anggota = $this->wpdb->get_row($this->wpdb->prepare(
                    "SELECT created_by FROM {$this->wpdb->prefix}dpw_rui_anggota WHERE id = %d",
                    $anggota_id
                ));
                
                // Allow access if user has read permission or owns the anggota
                if(!current_user_can('dpw_rui_read') && 
                   (!current_user_can('dpw_rui_edit_own') || $anggota->created_by != $current_user)) {
                    return '';
                }
            } else {
                return '';
            }
        }
        
        return $url;
    }
    
    /**
     * Get all photos for an anggota
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
     * Handle photo upload
     */
    public function handle_upload($file_key, $anggota_id) {
        // Add flag for custom upload directory
        $_POST['is_dpw_rui_upload'] = true;

        // Validate upload
        if(empty($_FILES[$file_key]['name'])) {
            return new WP_Error('no_file', 'No file uploaded');
        }

        $file = $_FILES[$file_key];
        $validation_errors = $this->validate_upload($file);
        if(!empty($validation_errors)) {
            return new WP_Error('validation_failed', implode(', ', $validation_errors));
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        // Upload file
        $attachment_id = media_handle_upload($file_key, 0);
        
        if(is_wp_error($attachment_id)) {
            return $attachment_id;
        }
        
        return $attachment_id;
    }
    
    /**
     * Add new photo
     */
    public function add_photo($anggota_id, $attachment_id, $is_main = 0) {
        return $this->wpdb->insert(
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
            // Delete from media library
            wp_delete_attachment($photo->attachment_id, true);
            
            // Delete from custom table
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
     * Validate photo upload
     */
    public function validate_upload($file) {
        $errors = array();
        
        // Check file type
        if(!in_array($file['type'], $this->allowed_types)) {
            $errors[] = 'Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF';
        }
        
        // Check file size
        if($file['size'] > $this->max_size) {
            $errors[] = 'Ukuran file terlalu besar. Maksimal 1.8 MB.';
        }
        
        return $errors;
    }
}