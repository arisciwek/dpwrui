<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/settings.php
 * Version: 1.0.2
 * 
 * Changelog:
 * 1.0.2
 * - Implementasi sistem routing terpusat
 * - Perbaikan duplikasi rendering header dan tab navigation
 * - Penambahan handler untuk masing-masing tab
 * - Optimasi struktur kode
 * 
 * 1.0.1 
 * - Initial version
 */

if (!defined('ABSPATH')) {
    exit;
}

class DPW_RUI_Settings {
    private $active_tab;
    private $tabs;

    public function __construct() {
        $this->active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'umum';
        
        $this->tabs = array(
            'umum' => array(
                'title' => 'Umum',
                'handler' => 'DPW_RUI_General_Settings'
            ),
            'layanan' => array(
                'title' => 'Layanan',
                'handler' => 'DPW_RUI_Services_Settings'
            ),
            'roles' => array(
                'title' => 'Role Management',
                'handler' => 'DPW_RUI_Roles_Settings'
            )
        );

        add_action('admin_init', array($this, 'init_settings'));
    }

    public function init_settings() {
        if (!is_admin()) {
            return;
        }

        // Load handler class sesuai active tab
        if (isset($this->tabs[$this->active_tab])) {
            $handler_class = $this->tabs[$this->active_tab]['handler'];
            if (!class_exists($handler_class)) {
                require_once DPW_RUI_PLUGIN_DIR . 'admin/' . strtolower(str_replace('DPW_RUI_', '', $handler_class)) . '.php';
            }
        }
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki akses ke halaman ini.'));
        }

        // Render header dan navigasi
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div style="margin: 10px 0 20px;">
                <?php foreach ($this->tabs as $tab_id => $tab) : ?>
                    <a href="?page=dpw-rui-settings&tab=<?php echo esc_attr($tab_id); ?>"
                       class="button<?php echo $this->active_tab === $tab_id ? ' button-primary' : ''; ?>"
                       style="margin-right: 10px;">
                        <?php echo esc_html($tab['title']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php
            // Tampilkan konten tab aktif
            if (isset($this->tabs[$this->active_tab])) {
                $handler_class = $this->tabs[$this->active_tab]['handler'];
                if (class_exists($handler_class)) {
                    $handler = new $handler_class();
                    $handler->render_content();  // Setiap handler harus memiliki method render_content()
                }
            }
            ?>
        </div>
        <?php
    }
}