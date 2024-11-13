<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui.php
 * Version: 1.0.5
 * 
 * Changelog:
 * 1.0.5
 * - Fixed form submission handling di display_anggota_page()
 * - Added handling POST data untuk save dan edit
 * - Fixed redirect after save/update
 * - Improved error handling pada operasi database
 * 
 * 1.0.4 
 * - Fixed save_anggota() untuk handle edit mode
 * - Fixed redirect setelah update
 * - Added proper update database query
 * - Fixed permission checking
 * 
 * 1.0.3
 * - Initial functionality
 */

class DPW_RUI {
    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $settings;

    public function __construct() {
        $this->version = DPW_RUI_VERSION;
        $this->plugin_name = 'dpw-rui';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    private function load_dependencies() {
        require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-admin-core.php';
        require_once DPW_RUI_PLUGIN_DIR . 'admin/settings.php';
        require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
        require_once DPW_RUI_PLUGIN_DIR . 'admin/services.php';
        require_once DPW_RUI_PLUGIN_DIR . 'admin/roles.php';

        $this->settings = new DPW_RUI_Settings();
    }

    private function define_admin_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            array(),
            '5.15.4'
        );

        wp_enqueue_style(
            $this->plugin_name,
            DPW_RUI_PLUGIN_URL . 'admin/css/sb-admin-2.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script('jquery');

        wp_enqueue_script(
            'bootstrap',
            'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js',
            array('jquery'),
            '4.6.0',
            true
        );

        wp_enqueue_script(
            'jquery-easing',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js',
            array('jquery'),
            '1.4.1',
            true
        );

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            DPW_RUI_PLUGIN_URL . 'admin/js/dpw-rui-admin.js',
            array('jquery'),
            $this->version,
            true
        );
    }

    public function add_admin_menu() {
        add_menu_page(
            'DPW RUI',
            'DPW RUI',
            'read',
            'dpw-rui',
            array($this, 'display_anggota_page'),
            'dashicons-groups',
            6
        );

        add_submenu_page(
            'dpw-rui',
            'Daftar Anggota',
            'Daftar Anggota',
            'read',
            'dpw-rui',
            array($this, 'display_anggota_page')
        );

        if(current_user_can('dpw_rui_create')) {
            add_submenu_page(
                'dpw-rui',
                'Tambah Anggota',
                'Tambah Anggota',
                'dpw_rui_create',
                'dpw-rui-add',
                array($this, 'display_add_anggota_page')
            );
        }

        if(current_user_can('manage_options')) {
            add_submenu_page(
                'dpw-rui',
                'Pengaturan',
                'Pengaturan', 
                'manage_options',
                'dpw-rui-settings',
                array($this, 'display_settings_page')
            );
        }
    }

    public function display_anggota_page() {
        if(!current_user_can('dpw_rui_view_list')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        // Handle POST submission
        if (isset($_POST['submit'])) {
            $this->save_anggota();
            return; // save_anggota() akan handle redirect
        }

        // Handle GET actions
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

    public function display_add_anggota_page() {
        if(!current_user_can('dpw_rui_create')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        if(isset($_POST['submit'])) {
            $this->save_anggota();
            return;
        }

        require_once DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-form.php';
    }

    // Definisikan konstanta untuk panjang maksimum field
    private $field_lengths = array(
        'nama_perusahaan' => 100,
        'pimpinan' => 100,
        'alamat' => 255,
        'kabupaten' => 50,
        'kode_pos' => 10,  // Sesuaikan dengan struktur database
        'nomor_telpon' => 20,
        'bidang_usaha' => 100,
        'nomor_ahu' => 50,
        'jabatan' => 50,
        'npwp' => 30,
        'nomor_anggota' => 20
    );

    private function validate_field_length($data) {
        $errors = array();
        
        foreach ($data as $field => $value) {
            if (isset($this->field_lengths[$field]) && strlen($value) > $this->field_lengths[$field]) {
                $errors[] = sprintf(
                    'Field %s terlalu panjang. Maksimal %d karakter.', 
                    $field, 
                    $this->field_lengths[$field]
                );
            }
        }
        
        if (!empty($errors)) {
            wp_die(implode("<br>", $errors));
        }
    }

    private function truncate_fields($data) {
        foreach ($data as $field => $value) {
            if (isset($this->field_lengths[$field])) {
                $data[$field] = substr($value, 0, $this->field_lengths[$field]);
            }
        }
        return $data;
    }
    
    private function save_anggota() {
        global $wpdb;
        
        // Validasi nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'dpw_rui_add_anggota')) {
            wp_die(__('Invalid nonce verification'));
        }

        $table = $wpdb->prefix . 'dpw_rui_anggota';
        
        // Check if edit mode
        $is_edit = isset($_POST['id']) && !empty($_POST['id']);
        
        // Validate permissions
        if ($is_edit) {
            $id = absint($_POST['id']);
            
            $existing = $wpdb->get_row($wpdb->prepare(
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
        $required_fields = array(
            'nama_perusahaan' => 'Nama Perusahaan',
            'pimpinan' => 'Pimpinan',
            'alamat' => 'Alamat',
            'kabupaten' => 'Kabupaten',
            'nomor_telpon' => 'Nomor Telpon',
            'bidang_usaha' => 'Bidang Usaha',
            'nomor_ahu' => 'Nomor AHU',
            'npwp' => 'NPWP'
        );

        foreach ($required_fields as $field => $label) {
            if (empty($_POST[$field])) {
                wp_die(sprintf(__('%s wajib diisi.'), $label));
            }
        }

        // Prepare data
        $data = array(
            'nama_perusahaan' => sanitize_text_field($_POST['nama_perusahaan']),
            'pimpinan' => sanitize_text_field($_POST['pimpinan']),
            'alamat' => sanitize_textarea_field($_POST['alamat']),
            'kabupaten' => sanitize_text_field($_POST['kabupaten']),
            'kode_pos' => sanitize_text_field($_POST['kode_pos']),
            'nomor_telpon' => sanitize_text_field($_POST['nomor_telpon']),
            'bidang_usaha' => sanitize_text_field($_POST['bidang_usaha']),
            'nomor_ahu' => sanitize_text_field($_POST['nomor_ahu']),
            'jabatan' => sanitize_text_field($_POST['jabatan']),
            'npwp' => sanitize_text_field($_POST['npwp']),
            'updated_at' => current_time('mysql'),
            'updated_by' => get_current_user_id()
        );

        // Validate field lengths
        $this->validate_field_length($data);
        
        // Truncate fields to maximum allowed length
        $data = $this->truncate_fields($data);

        if ($is_edit) {
            $result = $wpdb->update(
                $table,
                $data,
                array('id' => $id)
            );
            
            if ($result === false) {
                $error_message = "Gagal mengupdate data.\n";
                $error_message .= "SQL Error: " . $wpdb->last_error . "\n";
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

            // Log data before insert
            error_log('Attempting to insert data:');
            error_log(print_r($data, true));

            $result = $wpdb->insert($table, $data);
            
            if ($result === false) {
                $error_message = "Gagal menyimpan data baru.\n";
                $error_message .= "SQL Error: " . $wpdb->last_error . "\n";
                $error_message .= "Last Query: " . $wpdb->last_query . "\n";
                $error_message .= "Data yang coba disimpan: " . print_r($data, true);
                error_log($error_message);
                wp_die($error_message);
            }
            
            $redirect_id = $wpdb->insert_id;
            $message = 1;
        }

        // Redirect ke halaman detail anggota
        wp_redirect(add_query_arg(array(
            'page' => 'dpw-rui',
            'action' => 'view',
            'id' => $redirect_id,
            'message' => $message
        ), admin_url('admin.php')));
        exit;
    }

    private function generate_member_number() {
        global $wpdb;
        
        $prefix = date('dmY');
        
        $last_number = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(CAST(SUBSTRING_INDEX(nomor_anggota, '-', -1) AS UNSIGNED)) 
             FROM {$wpdb->prefix}dpw_rui_anggota 
             WHERE nomor_anggota LIKE %s",
            $prefix . '-%'
        ));

        $next_number = ($last_number ? intval($last_number) : 0) + 1;
        return $prefix . '-' . str_pad($next_number, 5, '0', STR_PAD_LEFT);
    }

    public function display_settings_page() {
        if(!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }
        
        $this->settings->render_page();
    }

    private function display_detail_anggota() {
        if(!current_user_can('dpw_rui_read')) {
            wp_die(__('Anda tidak memiliki akses untuk melihat detail anggota.'));
        }

        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        
        global $wpdb;
        $anggota = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota WHERE id = %d",
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
        
        global $wpdb;
        $anggota = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dpw_rui_anggota WHERE id = %d",
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

        global $wpdb;
        $table = $wpdb->prefix . 'dpw_rui_anggota';

        $where = '';
        if(!empty($search)) {
            $where = $wpdb->prepare(
                " WHERE nomor_anggota LIKE %s OR nama_perusahaan LIKE %s OR nomor_telpon LIKE %s",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table" . $where);
        $total_pages = ceil($total / $limit);

        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table" . $where . " ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );

        require_once DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-list.php';
    }

    public function run() {
        $this->define_admin_hooks();
    }
}
