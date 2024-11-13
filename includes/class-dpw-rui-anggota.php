<?php
/**
* Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-anggota.php
* Version: 1.0.0
* 
* Changelog:
* 1.0.0
* - Extracted member CRUD operations from class-dpw-rui.php
* - Handles all member data operations
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
       // Handle form submission
       if (isset($_POST['submit'])) {
           $this->save_anggota();
           return;
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

   private function save_anggota() {
       // Validasi nonce
       $this->validation->validate_nonce($_POST['_wpnonce'], 'dpw_rui_add_anggota');

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
               wp_die(__('Data tidak ditemukan.'));
           }
           
           if (!current_user_can('dpw_rui_update') && 
               (!current_user_can('dpw_rui_edit_own') || $existing->created_by != get_current_user_id())) {
               wp_die(__('Anda tidak memiliki akses untuk mengubah data ini.'));
           }
       } else {
           if (!current_user_can('dpw_rui_create')) {
               wp_die(__('Anda tidak memiliki akses untuk menambah data.'));
           }
       }

       // Validate required fields
       $this->validation->validate_required_fields($_POST);

       // Prepare data
       $data = $this->validation->sanitize_form_data($_POST);

       // Validate field lengths
       $this->validation->validate_field_length($data);
       
       // Truncate fields to maximum allowed length
       $data = $this->validation->truncate_fields($data);

       if ($is_edit) {
           $result = $this->wpdb->update(
               $table,
               $data,
               array('id' => $id)
           );
           
           if ($result === false) {
               $error_message = "Gagal mengupdate data.\n";
               $error_message .= "SQL Error: " . $this->wpdb->last_error . "\n";
               $error_message .= "Data yang coba diupdate: " . print_r($data, true);
               error_log($error_message);
               wp_die($error_message);
           }
           
           $redirect_id = $id;
           $message = 2;
       } else {
           // Add fields for new record
           $data['nomor_anggota'] = $this->generate_member_number();
           $data['created_at'] = current_time('mysql');
           $data['created_by'] = get_current_user_id();

           $result = $this->wpdb->insert($table, $data);
           
           if ($result === false) {
               $error_message = "Gagal menyimpan data baru.\n";
               $error_message .= "SQL Error: " . $this->wpdb->last_error . "\n";
               $error_message .= "Last Query: " . $this->wpdb->last_query . "\n";
               $error_message .= "Data yang coba disimpan: " . print_r($data, true);
               error_log($error_message);
               wp_die($error_message);
           }
           
           $redirect_id = $this->wpdb->insert_id;
           $message = 1;
       }

       $redirect_url = add_query_arg(array(
           'page' => 'dpw-rui',
           'action' => 'view',
           'id' => $redirect_id,
           'message' => $message
       ), admin_url('admin.php'));

       ?>
       <div class="wrap">
           <p>Menyimpan data... Jika tidak ada redirect otomatis, silakan klik <a href="<?php echo esc_url($redirect_url); ?>">di sini</a>.</p>
           <meta http-equiv="refresh" content="0;url=<?php echo esc_url($redirect_url); ?>">
       </div>
       <?php
       exit;
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