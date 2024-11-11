<?php
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
        // Load admin core functionality
        require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-admin-core.php';
        
        // Load settings pages
        require_once DPW_RUI_PLUGIN_DIR . 'admin/settings.php';
        require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
        require_once DPW_RUI_PLUGIN_DIR . 'admin/services.php';
        require_once DPW_RUI_PLUGIN_DIR . 'admin/roles.php';

        // Initialize settings
        $this->settings = new DPW_RUI_Settings();
    }

    private function define_admin_hooks() {
        // Menu Admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // CSS dan JS
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_styles() {
        // Font Awesome
        wp_enqueue_style(
            'fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            array(),
            '5.15.4'
        );

        // SB Admin 2 CSS
        wp_enqueue_style(
            $this->plugin_name,
            DPW_RUI_PLUGIN_URL . 'admin/css/sb-admin-2.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        // jQuery (WordPress sudah menyertakan)
        wp_enqueue_script('jquery');

        // Bootstrap Bundle (includes Popper.js)
        wp_enqueue_script(
            'bootstrap',
            'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js',
            array('jquery'),
            '4.6.0',
            true
        );

        // jQuery Easing
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
        // Menu Utama
        add_menu_page(
            'DPW RUI',
            'DPW RUI',
            'read',
            'dpw-rui',
            array($this, 'display_anggota_page'),
            'dashicons-groups',
            6
        );

        // Submenu Anggota 
        add_submenu_page(
            'dpw-rui',
            'Daftar Anggota',
            'Daftar Anggota',
            'read',
            'dpw-rui',
            array($this, 'display_anggota_page')
        );

        // Submenu Tambah Anggota
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

        // Submenu Pengaturan
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

        // Handle search
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        // Pagination
        $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $limit = 10;
        $offset = ($paged - 1) * $limit;

        global $wpdb;
        $table = $wpdb->prefix . 'dpw_rui_anggota';

        // Query with search
        $where = '';
        if(!empty($search)) {
            $where = $wpdb->prepare(
                " WHERE nomor_anggota LIKE %s OR nama_perusahaan LIKE %s OR nomor_telpon LIKE %s",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        // Total rows for pagination
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table" . $where);
        $total_pages = ceil($total / $limit);

        // Get data
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table" . $where . " ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );

        require_once DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-list.php';
    }

    public function display_add_anggota_page() {
        if(!current_user_can('dpw_rui_create')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        if(isset($_POST['submit'])) {
            $this->save_anggota();
        }

        require_once DPW_RUI_PLUGIN_DIR . 'admin/views/anggota-form.php';
    }

    public function display_settings_page() {
        if(!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }
        
        $this->settings->render_page();
    }

    private function save_anggota() {
        // Validasi nonce
        check_admin_referer('dpw_rui_add_anggota');

        // Generate nomor anggota
        $nomor_anggota = date('dmY') . '-' . $this->generate_member_number();

        global $wpdb;
        $table = $wpdb->prefix . 'dpw_rui_anggota';

        $data = array(
            'nomor_anggota' => $nomor_anggota,
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
            'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id(),
            'updated_at' => current_time('mysql'),
            'updated_by' => get_current_user_id()
        );

        $wpdb->insert($table, $data);

        wp_redirect(admin_url('admin.php?page=dpw-rui&message=1'));
        exit;
    }

    private function generate_member_number() {
        global $wpdb;
        
        $last_number = $wpdb->get_var("
            SELECT MAX(CAST(SUBSTRING_INDEX(nomor_anggota, '-', -1) AS UNSIGNED)) 
            FROM {$wpdb->prefix}dpw_rui_anggota 
            WHERE nomor_anggota LIKE '" . date('dmY') . "-%'
        ");

        return str_pad(($last_number + 1), 5, '0', STR_PAD_LEFT);
    }

    public function run() {
        $this->define_admin_hooks();
    }
}