<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/settings.php
 * Version: 2.0.0
 * 
 * Changelog:
 * 2.0.0
 * - Adjusted for new plugin structure
 * - Added proper dependency management
 * - Improved routing to settings pages
 * - Added data cleanup settings
 * - Fixed tab handling
 * 
 * 1.0.1
 * - Fixed duplicate tab rendering
 * - Improved page routing
 * - Removed redundant roles.php inclusion
 */

class DPW_RUI_Settings {
    private $active_tab;
    private $validation;

    public function __construct() {
        $this->active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'umum';
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        // General Settings
        register_setting(
            'dpw_rui_general_options',
            'dpw_rui_alamat',
            array(
                'type' => 'string',
                'description' => 'Alamat kantor DPW RUI',
                'sanitize_callback' => 'sanitize_textarea_field',
                'show_in_rest' => false,
            )
        );

        // Cleanup Settings
        register_setting(
            'dpw_rui_general_options',
            'dpw_rui_remove_data_on_deactivate',
            array(
                'type' => 'boolean',
                'description' => 'Hapus semua data saat deaktivasi plugin',
                'default' => false,
                'sanitize_callback' => 'rest_sanitize_boolean',
            )
        );
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div style="margin: 10px 0 20px;">
                <a href="<?php echo esc_url(add_query_arg('tab', 'umum')); ?>" 
                   class="button<?php echo $this->active_tab === 'umum' ? ' button-primary' : ''; ?>"
                   style="margin-right: 10px;">Umum</a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'layanan')); ?>"
                   class="button<?php echo $this->active_tab === 'layanan' ? ' button-primary' : ''; ?>"
                   style="margin-right: 10px;">Layanan</a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'roles')); ?>"
                   class="button<?php echo $this->active_tab === 'roles' ? ' button-primary' : ''; ?>">Role Management</a>
            </div>

            <?php
            if (isset($_GET['settings-updated'])) {
                add_settings_error(
                    'dpw_rui_messages',
                    'dpw_rui_message',
                    __('Pengaturan berhasil disimpan.'),
                    'updated'
                );
            }
            settings_errors('dpw_rui_messages');
            ?>

            <?php 
            // Route ke file yang sesuai
            switch($this->active_tab) {
                case 'umum':
                    require_once DPW_RUI_PLUGIN_DIR . 'admin/general.php';
                    break;
                case 'layanan':
                    require_once DPW_RUI_PLUGIN_DIR . 'admin/services.php';
                    break;
                case 'roles':
                    global $dpw_rui_roles;
                    if(class_exists('DPW_RUI_Roles_Settings')) {
                        $dpw_rui_roles->render_page();
                    }
                    break;
            }
            ?>
        </div>
        <?php
    }

    public function render_cleanup_settings() {
        ?>
        <div class="card" style="max-width: 100%; background: #fff; padding: 20px; margin-top: 20px;">
            <h2 style="margin-top: 0;">Pengaturan Pembersihan Data</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Pembersihan Data</th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="dpw_rui_remove_data_on_deactivate" 
                                   value="1" 
                                   <?php checked(get_option('dpw_rui_remove_data_on_deactivate')); ?>>
                            Hapus semua data saat plugin dinonaktifkan
                        </label>
                        <p class="description">
                            Jika dicentang, semua data anggota, foto, dan pengaturan akan dihapus saat plugin dinonaktifkan.
                            <br>
                            <strong>Peringatan:</strong> Data yang sudah dihapus tidak dapat dikembalikan.
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
}