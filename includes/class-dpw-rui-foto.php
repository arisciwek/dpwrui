<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-foto.php
 * Version: 1.0.0
 * 
 * Class untuk mengelola logika foto anggota
 */

class DPW_RUI_Foto {
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
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
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
        if(!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF';
        }
        
        // Check file size (1.8MB = 1887436.8 bytes)
        if($file['size'] > 1887436) {
            $errors[] = 'Ukuran file terlalu besar. Maksimal 1.8 MB.';
        }
        
        return $errors;
    }
}