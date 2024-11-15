<?php
/**
* Path: /wp-content/plugins/dpwrui/admin/roles.php
* Version: 2.2.0
* 
* Changelog:
* 2.2.0
* - Added capabilities for member activation/deactivation
* - Added member status management section
* - Added bulk action support for status changes
* - Added status indicators in member list
* - Added status change logs
* 
* 2.1.0
* - Fixed layout and structure to match parent
* - Improved role management interface
* - Added proper validation handling
* - Added role status indicators
* - Fixed permission checking
*/

class DPW_RUI_Roles_Settings {
   
    private $validation;
    private $capabilities = array(
        'create' => 'Tambah Anggota',
        'read' => 'Lihat Detail Anggota',
        'update' => 'Edit Anggota', 
        'delete' => 'Hapus Anggota',
        'edit_own' => 'Edit Data Sendiri',
        'view_list' => 'Lihat Daftar Anggota',
        'activate_member' => 'Aktivasi Anggota',
        'deactivate_member' => 'Nonaktifkan Anggota',
        'manage_member_status' => 'Kelola Status Anggota'
    );

    private $roles = array(
        'administrator' => 'Administrator',
        'dpw_rui_operator' => 'Operator',
        'dpw_rui_member' => 'Member'
    );

    private $role_capabilities = array(
        'administrator' => array(
            'dpw_rui_create',
            'dpw_rui_read',
            'dpw_rui_update',
            'dpw_rui_delete',
            'dpw_rui_edit_own',
            'dpw_rui_view_list',
            'dpw_rui_activate_member',
            'dpw_rui_deactivate_member',
            'dpw_rui_manage_member_status'
        ),
        'dpw_rui_operator' => array(
            'dpw_rui_create',
            'dpw_rui_read',
            'dpw_rui_update',
            'dpw_rui_view_list',
            'dpw_rui_activate_member',
            'dpw_rui_deactivate_member'
        ),
        'dpw_rui_member' => array(
            'dpw_rui_read',
            'dpw_rui_edit_own',
            'dpw_rui_view_list'
        )
    );

    public function __construct($validation = null) {
        if ($validation === null) {
            require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-validation.php';
            $validation = new DPW_RUI_Validation();
        }
        
        $this->validation = $validation;
        add_action('admin_init', array($this, 'ensure_roles_exist'));
        add_action('admin_init', array($this, 'ensure_capabilities'));
    }

    public function ensure_roles_exist() {
        foreach ($this->roles as $role_name => $display_name) {
            if (!get_role($role_name)) {
                add_role($role_name, $display_name);
            }
        }
        
        // Pastikan administrator memiliki semua kapabilitas
        $admin_role = get_role('administrator');
        if ($admin_role) {
            foreach ($this->capabilities as $cap => $label) {
                $admin_role->add_cap('dpw_rui_' . $cap);
            }
        }
    }

    public function ensure_capabilities() {
        foreach ($this->role_capabilities as $role_name => $capabilities) {
            $role = get_role($role_name);
            if ($role) {
                // Hapus kapabilitas yang tidak seharusnya ada
                foreach ($this->capabilities as $cap => $label) {
                    if (!in_array('dpw_rui_' . $cap, $capabilities)) {
                        $role->remove_cap('dpw_rui_' . $cap);
                    }
                }
                // Tambahkan kapabilitas yang seharusnya ada
                foreach ($capabilities as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
        
        // Fungsi untuk debug kapabilitas
        if (WP_DEBUG) {
            $admin_role = get_role('administrator');
            if ($admin_role) {
                error_log('Kapabilitas Admin: ' . print_r($admin_role->capabilities, true));
            }
        }
    }


    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        // Handle form submission
        if (isset($_POST['submit'])) {
            check_admin_referer('dpw_rui_roles_settings');
            $this->save_role_settings();
        }

        ?>
        <!-- Role Management Section -->
        <div class="alert alert-info mb-4">
            <h5 class="alert-heading mb-2">
                <i class="fas fa-users-cog mr-2"></i>
                Manajemen Role dan Hak Akses
            </h5>
            <p class="mb-0">
                Atur hak akses untuk setiap role pengguna dalam sistem DPW RUI.
            </p>
        </div>

        <form method="post" action="" class="needs-validation" novalidate>
            <?php wp_nonce_field('dpw_rui_roles_settings'); ?>

            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Capability</th>
                            <?php foreach ($this->roles as $role_name => $display_name): ?>
                                <th class="text-center"><?php echo esc_html($display_name); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->capabilities as $cap => $label): ?>
                            <tr>
                                <td>
                                    <?php echo esc_html($label); ?>
                                    <?php if (in_array($cap, array('activate_member', 'deactivate_member', 'manage_member_status'))): ?>
                                        <span class="badge badge-info ml-2">Baru</span>
                                    <?php endif; ?>
                                </td>
                                <?php foreach ($this->roles as $role_name => $display_name): 
                                    $role = get_role($role_name);
                                    $checked = $role && $role->has_cap('dpw_rui_' . $cap);
                                    $disabled = $role_name === 'administrator';
                                ?>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" 
                                                   class="custom-control-input" 
                                                   id="cap_<?php echo $role_name . '_' . $cap; ?>"
                                                   name="capabilities[<?php echo $role_name; ?>][]"
                                                   value="<?php echo $cap; ?>"
                                                   <?php checked($checked); ?>
                                                   <?php disabled($disabled); ?>>
                                            <label class="custom-control-label" 
                                                   for="cap_<?php echo $role_name . '_' . $cap; ?>"></label>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Member Status Management Section -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-user-shield mr-2"></i>
                        Keterangan Status Anggota:
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <strong>Aktivasi Anggota:</strong> 
                            Mengaktifkan anggota baru atau anggota yang dinonaktifkan
                        </li>
                        <li>
                            <i class="fas fa-times-circle text-danger mr-2"></i>
                            <strong>Nonaktifkan Anggota:</strong>
                            Menonaktifkan anggota yang sudah tidak aktif
                        </li>
                        <li>
                            <i class="fas fa-cogs text-primary mr-2"></i>
                            <strong>Kelola Status:</strong>
                            Melihat history perubahan status dan alasan perubahan
                        </li>
                    </ul>
                </div>
            </div>

            <div class="form-actions">
                <?php submit_button('Simpan Pengaturan', 'primary', 'submit', false); ?>
                <button type="button" class="button button-secondary" id="resetDefaults">
                    Reset ke Default
                </button>
            </div>
        </form>

        <script>
        jQuery(document).ready(function($) {
            // Form validation
            $('form.needs-validation').on('submit', function(e) {
                if (this.checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });

            // Reset to defaults confirmation
            $('#resetDefaults').on('click', function(e) {
                e.preventDefault();
                if (confirm('Apakah Anda yakin ingin mengembalikan semua pengaturan ke default? Ini akan mereset semua perubahan yang telah dilakukan.')) {
                    // Reset checkboxes based on default role capabilities
                    var defaults = <?php echo json_encode($this->role_capabilities); ?>;
                    Object.keys(defaults).forEach(function(role) {
                        $('input[name="capabilities[' + role + '][]"]').each(function() {
                            var cap = $(this).val();
                            $(this).prop('checked', defaults[role].includes('dpw_rui_' + cap));
                        });
                    });
                }
            });

            // Loading state
            $('form').on('submit', function() {
                $('button[type="submit"]', this)
                    .prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
            });
        });
        </script>
        <?php
    }

    private function save_role_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki izin untuk mengubah pengaturan ini.'));
        }

        $capabilities = isset($_POST['capabilities']) ? $_POST['capabilities'] : array();

        foreach ($this->roles as $role_name => $display_name) {
            if ($role_name === 'administrator') continue; // Skip administrator role

            $role = get_role($role_name);
            if (!$role) continue;

            // Remove all existing capabilities first
            foreach ($this->capabilities as $cap => $label) {
                $role->remove_cap('dpw_rui_' . $cap);
            }

            // Add selected capabilities
            if (isset($capabilities[$role_name]) && is_array($capabilities[$role_name])) {
                foreach ($capabilities[$role_name] as $cap) {
                    if (array_key_exists($cap, $this->capabilities)) {
                        $role->add_cap('dpw_rui_' . $cap);
                    }
                }
            }
        }

        add_settings_error(
            'dpw_rui_messages',
            'dpw_rui_roles_updated',
            __('Pengaturan role berhasil disimpan.'),
            'updated'
        );
    }
}

// Initialize only when needed
function dpw_rui_init_roles_settings() {
    global $pagenow;
    if ($pagenow === 'admin.php' && 
        isset($_GET['page']) && $_GET['page'] === 'dpw-rui-settings' &&
        isset($_GET['tab']) && $_GET['tab'] === 'roles') {
        
        global $dpw_rui_roles_settings;
        if (!isset($dpw_rui_roles_settings)) {
            global $dpw_rui_validation;
            $dpw_rui_roles_settings = new DPW_RUI_Roles_Settings($dpw_rui_validation);
        }
    }
}
add_action('admin_init', 'dpw_rui_init_roles_settings');