/**
 * Path: /wp-content/plugins/dpwrui/admin/js/foto.js
 * Version: 1.0.0
 */

jQuery(document).ready(function($) {
    var maxSize = 1887436; // 1.8MB in bytes
    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    // File input change handler
    $('#foto').on('change', function(e) {
        var file = this.files[0];
        validateAndPreview(file);
    });
    
    // Drag and drop handlers
    $('.dpw-rui-foto-upload')
        .on('dragenter dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        })
        .on('dragleave drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
            
            if(e.type === 'drop') {
                var file = e.originalEvent.dataTransfer.files[0];
                validateAndPreview(file);
            }
        });
    
    // Validate and preview file
    function validateAndPreview(file) {
        var $preview = $('.dpw-rui-foto-preview');
        var $error = $('.dpw-rui-foto-error-message');
        var $submit = $('button[type="submit"]');
        
        $error.empty();
        $preview.removeClass('dpw-rui-foto-error');
        $submit.prop('disabled', false);
        
        // Validate file type
        if(!allowedTypes.includes(file.type)) {
            showError('Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF');
            return;
        }
        
        // Validate file size
        if(file.size > maxSize) {
            showError('Ukuran file terlalu besar. Maksimal 1.8 MB.');
            return;
        }
        
        // Show preview
        var reader = new FileReader();
        reader.onload = function(e) {
            $preview.html('<img src="' + e.target.result + '" alt="Preview">');
        };
        reader.readAsDataURL(file);
    }
    
    // Show error message
    function showError(message) {
        var $preview = $('.dpw-rui-foto-preview');
        var $error = $('.dpw-rui-foto-error-message');
        var $submit = $('button[type="submit"]');
        
        $preview.addClass('dpw-rui-foto-error');
        $error.html(message);
        $submit.prop('disabled', true);
        
        // Clear file input
        $('#foto').val('');
    }
    
    // Set main photo confirmation
    $('.set-main-photo').on('click', function(e) {
        if(!confirm('Jadikan ini sebagai foto utama?')) {
            e.preventDefault();
        }
    });
    
    // Delete photo confirmation
    $('.delete-photo').on('click', function(e) {
        if(!confirm('Yakin ingin menghapus foto ini?')) {
            e.preventDefault();
        }
    });
});