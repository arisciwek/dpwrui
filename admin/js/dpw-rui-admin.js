/**
 * Path: /wp-content/plugins/dpwrui/admin/js/dpw-rui-admin.js
 * Version: 1.0.0
 * JavaScript untuk fungsionalitas admin DPW RUI
 */

jQuery(document).ready(function($) {
    // Form validation
    $('form.needs-validation').on('submit', function(e) {
        if (this.checkValidity() === false) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Auto hide notifications
    setTimeout(function() {
        $('.updated, .notice-success').fadeOut();
    }, 3000);

    // Confirm delete
    $('.delete-action').on('click', function(e) {
        if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            e.preventDefault();
        }
    });
});