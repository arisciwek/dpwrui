<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-post-handler.php
 * Version: 1.0.0
 * 
 * Handles all admin-post.php form submissions
 */

class DPW_RUI_Post_Handler {
    private $anggota;
    private $validation;

    public function __construct(DPW_RUI_Anggota $anggota, DPW_RUI_Validation $validation) {
        $this->anggota = $anggota;
        $this->validation = $validation;

        add_action('admin_post_dpw_rui_save_anggota', array($this, 'handle_save_anggota'));
    }

    public function handle_save_anggota() {
        if (!isset($_POST['dpw_rui_nonce']) || 
            !wp_verify_nonce($_POST['dpw_rui_nonce'], 'dpw_rui_save_anggota')) {
            wp_die('Invalid nonce verification');
        }

        $is_edit = isset($_POST['form_source']) && $_POST['form_source'] === 'edit';
        $redirect_params = array('page' => 'dpw-rui');

        try {
            // Permission check
            if ($is_edit) {
                if (!current_user_can('dpw_rui_update')) {
                    throw new Exception('Anda tidak memiliki akses untuk mengubah data.');
                }
            } else {
                if (!current_user_can('dpw_rui_create')) {
                    throw new Exception('Anda tidak memiliki akses untuk menambah data.');
                }
            }

            // Save data
            $result = $this->anggota->save_anggota($_POST);

            if ($result['success']) {
                $redirect_params['action'] = 'view';
                $redirect_params['id'] = $result['id'];
                $redirect_params['message'] = $is_edit ? '2' : '1';
            } else {
                throw new Exception($result['message']);
            }

        } catch (Exception $e) {
            if ($is_edit) {
                $redirect_params['action'] = 'edit';
                $redirect_params['id'] = absint($_POST['id']);
            } else {
                $redirect_params['action'] = 'add';
            }
            
            add_settings_error(
                'dpw_rui_messages',
                'dpw_rui_error',
                $e->getMessage(),
                'error'
            );
            
            set_transient('dpw_rui_form_values', $_POST, 30);
        }

        wp_redirect(add_query_arg($redirect_params, admin_url('admin.php')));
        exit;
    }
}