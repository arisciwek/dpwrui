<?php
/**
 * Plugin Name: DPW RUI
 * Plugin URI: https://www.example.com/dpw-rui
 * Description: Plugin untuk mengelola data anggota DPW RUI
 * Version: 2.0.0
 * Author: Your Name
 * Author URI: https://www.example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: dpw-rui
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('DPW_RUI_VERSION', '2.0.0');

// Plugin directory paths
define('DPW_RUI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DPW_RUI_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load required files
 */
require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui.php';
require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-activator.php';
require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-deactivator.php';

/**
 * Activation hook
 */
function activate_dpw_rui() {
    require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-activator.php';
    DPW_RUI_Activator::activate();
}

/**
 * Deactivation hook
 */
function deactivate_dpw_rui() {
    require_once DPW_RUI_PLUGIN_DIR . 'includes/class-dpw-rui-deactivator.php';
    DPW_RUI_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_dpw_rui');
register_deactivation_hook(__FILE__, 'deactivate_dpw_rui');

/**
 * Initialize the plugin
 */
function run_dpw_rui() {
    $plugin = new DPW_RUI();
    $plugin->run();
}

run_dpw_rui();