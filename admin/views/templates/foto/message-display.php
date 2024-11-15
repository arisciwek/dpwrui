<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/templates/foto/message-display.php
 * Version: 1.1.1
 * 
 * Changelog:
 * 1.1.1
 * - Menghapus semua CSS dan JavaScript internal
 * - Memindahkan CSS ke foto.css
 * - Memindahkan JavaScript ke foto.js  
 * - Tidak mengubah struktur HTML dan PHP
 * - Memastikan class dan ID tetap sesuai dengan CSS & JS
 * 
 * 1.1.0
 * - Previous functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

$message_types = array(
    'error' => array(
        'class' => 'notice-error',
        'icon' => 'fas fa-exclamation-circle'
    ),
    'success' => array(
        'class' => 'notice-success',
        'icon' => 'fas fa-check-circle'
    ),
    'warning' => array(
        'class' => 'notice-warning',
        'icon' => 'fas fa-exclamation-triangle'
    ),
    'info' => array(
        'class' => 'notice-info',
        'icon' => 'fas fa-info-circle'
    )
);

?>
<div id="dpw-rui-messages">
    <?php if (!empty($errors)): ?>
        <div class="notice <?php echo $message_types['error']['class']; ?> is-dismissible">
            <?php foreach ($errors as $error): ?>
                <p>
                    <i class="<?php echo $message_types['error']['icon']; ?> mr-2"></i>
                    <?php echo esc_html($error); ?>
                </p>
            <?php endforeach; ?>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Tutup notifikasi.</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="notice <?php echo $message_types['success']['class']; ?> is-dismissible">
            <p>
                <i class="<?php echo $message_types['success']['icon']; ?> mr-2"></i>
                <?php echo esc_html($success_message); ?>
            </p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Tutup notifikasi.</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($notices)): ?>
        <?php foreach ($notices as $notice_type => $notice_messages): 
            if (!isset($message_types[$notice_type])) continue;
        ?>
            <div class="notice <?php echo $message_types[$notice_type]['class']; ?> is-dismissible">
                <?php foreach ($notice_messages as $message): ?>
                    <p>
                        <i class="<?php echo $message_types[$notice_type]['icon']; ?> mr-2"></i>
                        <?php echo esc_html($message); ?>
                    </p>
                <?php endforeach; ?>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Tutup notifikasi.</span>
                </button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
