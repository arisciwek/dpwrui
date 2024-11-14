<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/templates/foto/message-display.php
 * Version: 1.0.0
 * 
 * Template for displaying success/error messages
 * 
 * @param array $errors Array of error messages
 * @param bool $success Whether the last operation was successful
 * @param string $success_message Custom success message (optional)
 * @param array $notices Array of notice messages (optional)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Success message
if($success): ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo !empty($success_message) ? 
                      esc_html($success_message) : 
                      'Perubahan berhasil disimpan.'; ?>
        </p>
    </div>
<?php endif; ?>

<?php 
// Error messages
if(!empty($errors)): ?>
    <div class="notice notice-error is-dismissible">
        <?php foreach($errors as $error): ?>
            <p>
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo esc_html($error); ?>
            </p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
// Notice messages
if(!empty($notices)): ?>
    <div class="notice notice-warning is-dismissible">
        <?php foreach($notices as $notice): ?>
            <p>
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?php echo esc_html($notice); ?>
            </p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
jQuery(document).ready(function($) {
    // Auto dismiss success messages after 3 seconds
    setTimeout(function() {
        $('.notice-success').slideUp(300, function() {
            $(this).remove();
        });
    }, 3000);

    // Add dismiss button functionality
    $('.notice.is-dismissible').each(function() {
        var $notice = $(this);
        
        // Add dismiss button if not exists
        if(!$notice.find('.notice-dismiss').length) {
            $notice.append('<button type="button" class="notice-dismiss">' + 
                         '<span class="screen-reader-text">Tutup pesan.</span></button>');
        }
        
        // Handle dismiss click
        $notice.on('click', '.notice-dismiss', function(e) {
            e.preventDefault();
            $notice.slideUp(300, function() {
                $notice.remove();
            });
        });
    });
    
    // Animate entrance
    $('.notice').hide().slideDown(300);
});
</script>

<style>
/* Ensure consistent spacing */
.notice {
    margin: 15px 0;
    padding: 10px 40px 10px 15px;
    position: relative;
}

.notice p {
    margin: 0.5em 0;
    padding: 2px 0;
    line-height: 1.4;
}

/* Improve dismiss button positioning */
.notice .notice-dismiss {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    padding: 3px;
    margin: 0;
    height: 26px;
    width: 26px;
    border: none;
    background: none;
    color: #787c82;
    cursor: pointer;
    text-decoration: none;
}

.notice .notice-dismiss:before {
    content: "\f153";
    display: block;
    font: normal 16px/20px dashicons;
    speak: never;
    height: 20px;
    text-align: center;
    width: 20px;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.notice .notice-dismiss:hover,
.notice .notice-dismiss:active,
.notice .notice-dismiss:focus {
    color: #d63638;
}

/* Improve icon alignment */
.notice i.fas {
    width: 16px;
    text-align: center;
}

/* Custom colors for different notice types */
.notice-success {
    border-left-color: #46b450;
}

.notice-error {
    border-left-color: #dc3232;
}

.notice-warning {
    border-left-color: #ffb900;
}
</style>