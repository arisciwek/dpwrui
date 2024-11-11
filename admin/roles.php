<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/roles.php
 * Version: 1.0.7
 *
 * Changelog:
 * 1.0.7
 * - Restrukturisasi kelas untuk bekerja dengan sistem settings terpusat
 * - Penambahan method render_content() untuk integrasi dengan settings.php
 * - Penghapusan duplikasi header dan navigasi
 * - Perbaikan pengelolaan capabilities
 * - Penambahan validasi dan sanitasi
 *
 * 1.0.6
 * - Fixed duplicate wrapper div
 * - Fixed duplicate heading
 * - Fixed duplicate tab navigation
 * 
 * 1.0.5
 * - Initial release
 */

if (!defined('ABSPATH')) {
    exit;
}

class DPW_RUI_Roles_Settings {
    private $capabilities;
    private $roles;
    
    public function __construct() {
        $this->capabilities = array(
            'create' => 'Tambah Anggota',
            'read' => 'Lihat Detail Anggota',
            'update' => 'Edit Anggota', 
            'delete' => 'Hapus Anggota',
            'edit_own' => 'Edit Data Sendiri',
            'view_list' => 'Lihat Daftar Anggota'
        );

        $this->roles = array(
            'administrator' => 'Administrator',
            'dpw_rui_operator' => 'Operator',
            'dpw_rui_member' => 'Member'
        );

        add_action('admin_init', array($this, 'ensure_admin_capabilities'));
    }

    public function ensure_admin_capabilities() {
        $admin_role = get_role('administrator');
        if ($admin_role) {
            foreach ($this->capabilities as $cap_id => $cap_name) {
                $admin_role->add_cap('dpw_rui_' . $cap_id);
            }
            $admin_role->add_cap('dpw_rui_manage_all');
        }
    }

    private function save_capabilities() {
        if (!current_user_can('manage_options')) {
            return false;
        }

        check_admin_referer('dpw_rui_save_capabilities');

        $capabilities = isset($_POST['capabilities']) ? 
                       map_deep($_POST['capabilities'], 'sanitize_key') : 
                       array();

        foreach($this->roles as $role_id => $role_name) {
            if($role_id === 'administrator') continue;

            $role = get_role($role_id);
            if(!$role) continue;
            
            // Reset capabilities
            foreach($this->capabilities as $cap_id => $cap_name) {
                $role->remove_cap('dpw_rui_' . $cap_id);
            }

            // Set new capabilities
            if(isset($capabilities[$role_id])) {
                foreach($capabilities[$role_id] as $cap) {
                    $role->add_cap('dpw_rui_' . $cap);
                }
            }
        }

        $this->ensure_admin_capabilities();
        return true;
    }

    public function render_content() {
        // Handle form submission
        if (isset($_POST['submit'])) {
            if ($this->save_capabilities()) {
                add_settings_error(
                    'dpw_rui_messages',
                    'dpw_rui_message',
                    __('Pengaturan role berhasil disimpan'),
                    'updated'
                );
            }
        }

        ?>
        <div class="card" style="max-width: 100%; background: #fff; border: 1px solid #ddd; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-top: 20px; padding: 20px;">
            <?php settings_errors('dpw_rui_messages'); ?>

            <h2 style="color: #2271b1; font-size: 16px; margin: 0 0 20px;">
                <span class="dashicons dashicons-admin-users" style="font-size: 20px; margin-right: 5px;"></span>
                Pengaturan Role dan Capability
            </h2>

            <div style="background: #f0f6fc; border-left: 4px solid #72aee6; padding: 12px; margin-bottom: 20px;">
                <p style="margin: 0;">Administrator secara otomatis memiliki akses penuh ke semua fitur.</p>
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('dpw_rui_save_capabilities'); ?>
                
                <table class="widefat" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Capability</th>
                            <?php foreach($this->roles as $role_id => $role_name): ?>
                                <?php if ($role_id !== 'administrator'): ?>
                                    <th style="text-align: center; width: 150px;">
                                        <?php echo esc_html($role_name); ?>
                                    </th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($this->capabilities as $cap_id => $cap_name): ?>
                            <tr>
                                <td>
                                    <?php echo esc_html($cap_name); ?>
                                </td>
                                <?php foreach($this->roles as $role_id => $role_name): 
                                    if ($role_id === 'administrator') continue;
                                    
                                    $role = get_role($role_id);
                                    $checked = $role && $role->has_cap('dpw_rui_' . $cap_id);
                                    ?>
                                    <td style="text-align: center;">
                                        <input type="checkbox" 
                                               name="capabilities[<?php echo esc_attr($role_id); ?>][]" 
                                               value="<?php echo esc_attr($cap_id); ?>"
                                               <?php checked($checked); ?>>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p class="submit">
                    <input type="submit" 
                           name="submit" 
                           class="button button-primary" 
                           value="Simpan Pengaturan">
                </p>
            </form>
        </div>
        <?php
    }
}