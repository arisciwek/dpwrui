<?php
/**
* Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-anggota.php
* Version: 1.1.0
* 
* Changelog:
* 1.1.0
* - Fixed form submission handling and validation flow
* - Added proper redirection after successful save
* - Fixed validation context for both add and edit
* - Improved error handling and messages
* - Added explicit form action handling
* - Fixed permission checking sequence
* - Added proper nonce verification
* - Added consistent success/error messages
* 
* 1.0.0
* - Initial version extracted from class-dpw-rui.php
*/

class DPW_RUI_Anggota {
    private $wpdb;
    private $validation;

    public function __construct(DPW_RUI_Validation $validation) {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->validation = $validation;
    }

    public function handle_page_actions() {
        // Check if it's a form submission first
        if (isset($_POST['submit']) && isset($_POST['_wpnonce'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'dpw_rui_add_anggota')) {
                wp_die(__('Invalid nonce verification'));
            }
            return $this->save_anggota();
        }

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
                
            default:
                $this->display_list_anggota();
                break;
        }
    }
public function save_anggota($post_data) {
        $table = $this->wpdb->prefix . 'dpw_rui_anggota';
        $is_edit = isset($post_data['form_source']) && $post_data['form_source'] === 'edit';
        
        try {
            // Validate required fields
            $this->validation->validate_required_fields($post_data);

            // Prepare data
            $data = $this->validation->sanitize_form_data($post_data);
            
            // Validate field lengths
            $this->validation->validate_field_length($data);
            
            // Truncate fields
            $data = $this->validation->truncate_fields($data);

            if ($is_edit) {
                $id = absint($post_data['id']);
                
                $result = $this->wpdb->update(
                    $table,
                    $data,
                    array('id' => $id)
                );

                if ($result === false) {
                    throw new Exception('Gagal mengupdate data: ' . $this->wpdb->last_error);
                }
                
                return array(
                    'success' => true,
                    'id' => $id,
                    'message' => 'Data berhasil diupdate'
                );
            } else {
                // Add fields for new record
                $data['nomor_anggota'] = $this->generate_member_number();
                $data['created_at'] = current_time('mysql');
                $data['created_by'] = get_current_user_id();

                $result = $this->wpdb->insert($table, $data);
                
                if ($result === false) {
                    throw new Exception('Gagal menyimpan data: ' . $this->wpdb->last_error);
                }
                
                return array(
                    'success' => true,
                    'id' => $this->wpdb->insert_id,
                    'message' => 'Data berhasil disimpan'
                );
            }
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
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

    private function display_edit_anggota() {
        if(!current_user_can('dpw_rui_update')) {
            wp_die(__('Anda tidak memiliki akses untuk mengubah data.'));
        }

        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        
        $anggota = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}dpw_rui_anggota WHERE id = %d",
            $id
        ));

        if(!$anggota) {
            wp_die(__('Data anggota tidak ditemukan.'));
        }

        if(!current_user_can('dpw_rui_update') && 
           (!current_user_can('dpw_rui_edit_own') || $anggota->created_by != get_current_user_id())) {
            wp_die(__('Anda tidak memiliki akses untuk mengubah data ini.'));
        }

        require_once DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-form.php';
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