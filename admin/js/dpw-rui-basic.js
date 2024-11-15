/**
 * Path: /wp-content/plugins/dpwrui/admin/js/dpw-rui-basic.js
 * Version: 1.0.3
 * Date: 2024-11-16
 * Support fungsi dasar CRUD tanpa ajax
 * 
 * Changelog:
 * 1.0.3
 * - Fixed form validation on submit
 * - Added proper form handling for create/update
 * - Fixed submit button state handling
 * - Added loading indicator 
 * - Improved error message display
 */

(function($) {
    "use strict";

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                var submitButton = form.querySelector('button[type="submit"]');
                
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Show validation errors
                    var fields = form.querySelectorAll('input[required], textarea[required]');
                    fields.forEach(function(field) {
                        if (!field.validity.valid) {
                            field.classList.add('error');
                            
                            // Show error message if doesn't exist
                            if (!field.nextElementSibling?.classList.contains('invalid-feedback')) {
                                var feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = field.validationMessage || 'Field ini wajib diisi';
                                field.parentNode.insertBefore(feedback, field.nextSibling);
                            }
                        }
                    });
                } else {
                    // Show loading state
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
                    }
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    });

    // Remove error class on input
    $('input[required], textarea[required]').on('input', function() {
        $(this).removeClass('error');
        $(this).next('.invalid-feedback').remove();
    });

    // Reset form
    $('.btn-reset').on('click', function(e) {
        e.preventDefault();
        if (confirm('Reset form? Semua perubahan akan hilang.')) {
            var form = $(this).closest('form');
            form.trigger('reset');
            form.removeClass('was-validated');
            form.find('.error').removeClass('error');
            form.find('.invalid-feedback').remove();
        }
    });

})(jQuery);
