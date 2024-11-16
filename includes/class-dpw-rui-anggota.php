<?php
/**
* Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-anggota.php
* Version: 1.1.3
* Timestamp: 2024-11-16 21:00:00
* 
* Changelog:
* 1.1.3
* - Added foto action handling in handle_page_actions()
* - Added display_foto_management() method
* - Integrated foto management with existing permission system
* - Maintained previous header and form handling fixes
* 
* 1.1.2
* - Fixed "headers already sent" error by moving form handling to admin_init
* - Added proper early redirect handling
* - Improved form submission flow
* - Added proper action hooks for form processing
* - Added session-based message handling
*/

class DPW_RUI_Anggota {
    private $wpdb;
    private $validation;

    public function __construct(DPW_RUI_Validation $validation) {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->validation = $validation;

        // Add form handling at admin_init (before any output)
        add_action('admin_init', array($this, 'process_form_submission'));
    }

    public function process_form_submission() {
        // Only process on our plugin pages
        if (!isset($_GET['page']) || !in_array($_GET['page'], array('dpw-rui', 'dpw-rui-add'))) {
            return;
        }

        // Check if form was submitted
        if (isset($_POST['submit']) && isset($_POST['_wpnonce'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'dpw_rui_add_anggota')) {
                wp_die('Invalid nonce verification');
            }

            $result = $this->save_anggota();
            if (is_wp_error($result)) {
                // Store error message in transient
                set_transient('dpw_rui_form_errors', $result->get_error_message(), 30);
                
                // Redirect back to form with error
                $redirect_url = isset($_POST['id']) ? 
                    add_query_arg(array(
                        'page' => 'dpw-rui',
                        'action' => 'edit',
                        'id' => absint($_POST['id'])
                    ), admin_url('admin.php')) :
                    add_query_arg('page', 'dpw-rui-add', admin_url('admin.php'));
                
                wp_redirect($redirect_url);
                exit;
            }
        }
    }

    public function handle_page_actions() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        
        switch($action) {
            case 'view':
                $this->display_detail_anggota();
                break;
                
            case 'edit':
                $this->display_edit_anggota();
                break;
                
            case 'delete':
                $this->handle_delete_anggota();
                break;
            
            case 'foto':
                $this->display_foto_management();
                break;
                
            default:
                $this->display_list_anggota();
                break;
        }
    }

    private function display_foto_management() {
        if (!isset($_GET['id'])) {
            wp_die(__('ID Anggota tidak valid'));
        }

        $id = absint($_GET['id']);
        
        $anggota = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota WHERE id = %d",
            $id
        ));

        if (!$anggota) {
            wp_die(__('Data anggota tidak ditemukan.'));
        }

        if (!current_user_can('dpw_rui_update') && 
            (!current_user_can('dpw_rui_edit_own') || $anggota->created_by != get_current_user_id())) {
            wp_die(__('Anda tidak memiliki akses untuk mengelola foto.'));
        }

        require_once DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-foto.php';
    }

    private function save_anggota() {
        $table = $this->wpdb->prefix . 'dpw_rui_anggota';
        $form_source = isset($_POST['form_source']) ? $_POST['form_source'] : '';
        
        // Check if edit mode
        $is_edit = $form_source === 'edit' && isset($_POST['id']) && !empty($_POST['id']);
        
        // Validate permissions
        if ($is_edit) {
            $id = absint($_POST['id']);
            
            $existing = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d",
                $id
            ));
            
            if (!$existing) {
                return new WP_Error('not_found', __('Data tidak ditemukan.'));
            }
            
            if (!current_user_can('dpw_rui_update') && 
                (!current_user_can('dpw_rui_edit_own') || $existing->created_by != get_current_user_id())) {
                return new WP_Error('permission_denied', __('Anda tidak memiliki akses untuk mengubah data ini.'));
            }
        } else {
            if (!current_user_can('dpw_rui_create')) {
                return new WP_Error('permission_denied', __('Anda tidak memiliki akses untuk menambah data.'));
            }
        }

        // Validate required fields
        $validation_result = $this->validation->validate_required_fields($_POST);
        if (is_wp_error($validation_result)) {
            return $validation_result;
        }

        // Prepare data
        $data = $this->validation->sanitize_form_data($_POST);

        // Validate field lengths
        $length_validation = $this->validation->validate_field_length($data);
        if (is_wp_error($length_validation)) {
            return $length_validation;
        }
        
        // Truncate fields to maximum allowed length
        $data = $this->validation->truncate_fields($data);

        if ($is_edit) {
            $result = $this->wpdb->update(
                $table,
                $data,
                array('id' => $id)
            );
            
            if ($result === false) {
                $error_message = "Gagal mengupdate data: " . $this->wpdb->last_error;
                error_log($error_message);
                return new WP_Error('update_failed', $error_message);
            }
            
            $redirect_id = $id;
            $message = 2; // Update success
            
        } else {
            // Add fields for new record
            $data['nomor_anggota'] = $this->generate_member_number();
            $data['created_at'] = current_time('mysql');
            $data['created_by'] = get_current_user_id();

            $result = $this->wpdb->insert($table, $data);
            
            if ($result === false) {
                $error_message = "Gagal menyimpan data: " . $this->wpdb->last_error;
                error_log($error_message);
                return new WP_Error('insert_failed', $error_message);
            }
            
            $redirect_id = $this->wpdb->insert_id;
            $message = 1; // Create success
        }

        // Clear any existing error messages
        delete_transient('dpw_rui_form_errors');

        // Redirect to detail view
        wp_redirect(add_query_arg(array(
            'page' => 'dpw-rui',
            'action' => 'view',
            'id' => $redirect_id,
            'message' => $message
        ), admin_url('admin.php')));
        exit;
    }

    private function display_edit_anggota() {
        if (!isset($_GET['id'])) {
            wp_die(__('ID Anggota tidak valid'));
        }

        $id = absint($_GET['id']);
        
        $anggota = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota WHERE id = %d",
            $id
        ));

        if (!$anggota) {
            wp_die(__('Data anggota tidak ditemukan.'));
        }

        if (!current_user_can('dpw_rui_update') && 
            (!current_user_can('dpw_rui_edit_own') || $anggota->created_by != get_current_user_id())) {
            wp_die(__('Anda tidak memiliki akses untuk mengubah data ini.'));
        }

        require DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-form.php';
    }

    private function display_list_anggota() {
       $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
       
       $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
       $limit = 10;
       $offset = ($paged - 1) * $limit;

       $table = $this->wpdb->prefix . 'dpw_rui_anggota';

       $where = '';
       if(!empty($search)) {
           $where = $this->wpdb->prepare(
               " WHERE nomor_anggota LIKE %s OR nama_perusahaan LIKE %s OR nomor_telpon LIKE %s",
               '%' . $this->wpdb->esc_like($search) . '%',
               '%' . $this->wpdb->esc_like($search) . '%',
               '%' . $this->wpdb->esc_like($search) . '%'
           );
       }

       $total = $this->wpdb->get_var("SELECT COUNT(*) FROM $table" . $where);
       $total_pages = ceil($total / $limit);

       $items = $this->wpdb->get_results(
           $this->wpdb->prepare(
               "SELECT * FROM $table" . $where . " ORDER BY created_at DESC LIMIT %d OFFSET %d",
               $limit,
               $offset
           )
       );

       require_once DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-list.php';
    }

    private function display_detail_anggota() {
       if(!current_user_can('dpw_rui_read')) {
           wp_die(__('Anda tidak memiliki akses untuk melihat detail anggota.'));
       }

       $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
       
       $anggota = $this->wpdb->get_row($this->wpdb->prepare(
           "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota WHERE id = %d",
           $id
       ));

       if(!$anggota) {
           wp_die(__('Data anggota tidak ditemukan.'));
       }

       require_once DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-detail.php';
    }

    private function generate_member_number() {
       $prefix = date('dmY');
       
       $last_number = $this->wpdb->get_var($this->wpdb->prepare(
           "SELECT MAX(CAST(SUBSTRING_INDEX(nomor_anggota, '-', -1) AS UNSIGNED)) 
            FROM {$this->wpdb->prefix}dpw_rui_anggota 
            WHERE nomor_anggota LIKE %s",
           $prefix . '-%'
       ));

       $next_number = ($last_number ? intval($last_number) : 0) + 1;
       return $prefix . '-' . str_pad($next_number, 5, '0', STR_PAD_LEFT);
    }
}
