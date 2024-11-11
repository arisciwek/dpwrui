<?php
/**
 * Class for core admin functionality
 */
class DPW_RUI_Admin_Core {
    
    /**
     * Load admin functions and resources
     */
    public static function load_admin_functions() {
        // Register admin scripts and styles
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
        
        // Add admin notices handler
        add_action('admin_notices', array(__CLASS__, 'handle_admin_notices'));
    }

    /**
     * Enqueue admin-specific scripts and styles
     */
    public static function enqueue_admin_assets() {
        $screen = get_current_screen();
        
        // Only load on plugin pages
        if (strpos($screen->id, 'dpw-rui') !== false) {
            // Bootstrap
            wp_enqueue_style(
                'bootstrap',
                'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css',
                array(),
                '4.6.0'
            );
            
            wp_enqueue_script(
                'bootstrap',
                'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js',
                array('jquery'),
                '4.6.0',
                true
            );

            // Custom admin styles
            wp_enqueue_style(
                'dpw-rui-admin',
                DPW_RUI_PLUGIN_URL . 'admin/css/admin.css',
                array(),
                DPW_RUI_VERSION
            );
        }
    }

    /**
     * Handle admin notices
     */
    public static function handle_admin_notices() {
        settings_errors('dpw_rui_messages');
    }

    /**
     * Format date to Indonesian format
     */
    public static function format_tanggal($date) {
        return date('d/m/Y', strtotime($date));
    }

    /**
     * Format datetime to Indonesian format
     */
    public static function format_datetime($datetime) {
        return date('d/m/Y H:i', strtotime($datetime));
    }

    /**
     * Validate required fields
     */
    public static function validate_required_fields($fields, $data) {
        $errors = array();
        
        foreach ($fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = sprintf(__('%s wajib diisi.'), $label);
            }
        }
        
        return $errors;
    }

    /**
     * Generate unique member number
     */
    public static function generate_member_number() {
        global $wpdb;
        
        $prefix = date('dmY');
        
        $last_number = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(CAST(SUBSTRING_INDEX(nomor_anggota, '-', -1) AS UNSIGNED)) 
             FROM {$wpdb->prefix}dpw_rui_anggota 
             WHERE nomor_anggota LIKE %s",
            $prefix . '-%'
        ));

        return $prefix . '-' . str_pad(($last_number + 1), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Sanitize and validate phone number
     */
    public static function sanitize_phone($phone) {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Check if user has permission to edit record
     */
    public static function can_edit_record($created_by) {
        return current_user_can('dpw_rui_update') || 
               (current_user_can('dpw_rui_edit_own') && $created_by == get_current_user_id());
    }

    /**
     * Get pagination info
     */
    public static function get_pagination_info($total_items, $per_page, $current_page) {
        return array(
            'total_pages' => ceil($total_items / $per_page),
            'total_items' => $total_items,
            'per_page' => $per_page,
            'current_page' => $current_page
        );
    }

    /**
     * Generate pagination links
     */
    public static function pagination_links($args) {
        return paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => $args['total_pages'],
            'current' => $args['current_page']
        ));
    }
}