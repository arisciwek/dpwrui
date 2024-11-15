/**
 * Path: /wp-content/plugins/dpwrui/admin/js/foto.js
 * Version: 2.0.0
 * 
 * Changelog:
 * 2.0.0
 * - Changed from AJAX to native PHP upload
 * - Removed WordPress media library integration
 * - Added direct form submission
 * - Improved file validation
 * - Added loading states
 * - Improved error handling
 * - Added proper preview handling
 * 
 * 1.0.1
 * - Previous version with WordPress media library
 */

jQuery(document).ready(function($) {
    var maxSize = 1887436; // 1.8MB in bytes
    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    var dropZone = $('#dropZone');
    var fileInput = $('#foto');
    var previewContainer = $('#previewContainer');
    var previewImage = $('.img-preview');
    var uploadButton = $('#uploadButton');
    var errorDiv = $('.dpw-rui-foto-error');
    var form = $('#uploadForm');

    // File input change handler
    fileInput.on('change', function(e) {
        var file = this.files[0];
        validateAndPreview(file);
    });
    
    // Drag and drop handlers
    dropZone
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
                fileInput[0].files = e.originalEvent.dataTransfer.files;
            }
        });
    
    // Validate and preview file
    function validateAndPreview(file) {
        if (!file) return;
        
        // Reset
        errorDiv.empty();
        uploadButton.prop('disabled', true);
        previewContainer.addClass('d-none');
        
        // Validate file type
        if(!allowedTypes.includes(file.type)) {
            showError('Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF');
            fileInput.val('');
            return;
        }
        
        // Validate file size
        if(file.size > maxSize) {
            showError('Ukuran file terlalu besar. Maksimal 1.8 MB.');
            fileInput.val('');
            return;
        }
        
        // Show preview
        var reader = new FileReader();
        reader.onload = function(e) {
            previewImage.attr('src', e.target.result);
            dropZone.addClass('d-none');
            previewContainer.removeClass('d-none');
            uploadButton.prop('disabled', false);
        };
        reader.readAsDataURL(file);
    }
    
    // Show error message
    function showError(message) {
        errorDiv.html(
            '<div class="alert alert-danger mb-3">' + 
            '<i class="fas fa-exclamation-circle mr-1"></i>' + 
            message + 
            '</div>'
        );
    }
    
    // Cancel preview
    $('.cancel-preview').on('click', function() {
        fileInput.val('');
        previewContainer.addClass('d-none');
        dropZone.removeClass('d-none');
        uploadButton.prop('disabled', true);
        errorDiv.empty();
    });

    // Handle form submission
    form.on('submit', function(e) {
        var file = fileInput[0].files[0];
        
        if (!file) {
            e.preventDefault();
            showError('Pilih file terlebih dahulu');
            return;
        }

        // Disable button and show loading state
        uploadButton
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengupload...');

        // Show overlay if exists
        if($('#loadingOverlay').length) {
            $('#loadingOverlay').removeClass('d-none');
        }
    });

    // Handle delete photo
    $('.delete-photo').on('click', function(e) {
        e.preventDefault();
        
        var isMain = $(this).data('is-main') === 'true';
        var message = isMain ? 
            'Ini adalah foto utama. Jika dihapus, foto lain akan otomatis dijadikan foto utama. Lanjutkan?' : 
            'Yakin ingin menghapus foto ini?';
        
        if(confirm(message)) {
            showLoading();
            window.location.href = $(this).attr('href');
        }
    });

    // Handle set main photo
    $('.set-main-photo').on('click', function(e) {
        e.preventDefault();
        
        if(confirm('Jadikan ini sebagai foto utama?')) {
            showLoading();
            window.location.href = $(this).attr('href');
        }
    });

    // Show loading overlay
    function showLoading() {
        if($('#loadingOverlay').length) {
            $('#loadingOverlay').removeClass('d-none');
        }
    }

    /*
     * mulai JS untuk grid-manage.php
     */

    var loadingOverlay = $('#loadingOverlay');
    
    // Delete photo confirmation
    $('.delete-photo').on('click', function(e) {
        e.preventDefault();
        
        var $this = $(this);
        var isMain = $this.data('is-main') === true;
        var message = isMain ? 
            'Ini adalah foto utama. Jika dihapus, foto lain akan otomatis dijadikan foto utama. Lanjutkan?' : 
            'Yakin ingin menghapus foto ini?';
        
        if(confirm(message)) {
            showLoading();
            window.location.href = $this.attr('href');
        }
    });

    // Set main photo confirmation
    $('.set-main-photo').on('click', function(e) {
        e.preventDefault();
        
        if(confirm('Jadikan ini sebagai foto utama?')) {
            showLoading();
            window.location.href = $(this).attr('href');
        }
    });

    function showLoading() {
        loadingOverlay.removeClass('d-none');
    }

    // Initialize tooltips
    $('[title]').tooltip();

    // Lazy load images
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            img.src = img.src;
        });
    }

    /*
     * mulai JS untuk message-display.php
     */

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

    /*
     * Mulai JS untuk upload-form.php
     */

    var dropZone = $('#dropZone');
    var fileInput = $('#foto');
    var previewContainer = $('#previewContainer');
    var previewImage = $('.preview-image');
    var uploadButton = $('#uploadButton');
    var errorDiv = $('.dpw-rui-foto-error');
    var form = $('#uploadForm');
    
    function handleFile(file) {
        if (!file) return;
        
        // Reset
        errorDiv.empty();
        uploadButton.prop('disabled', true);
        
        // Validate file type
        var validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            showError('Tipe file tidak didukung. Format yang diizinkan: JPG, PNG, GIF');
            return;
        }
        
        // Validate size (1.8MB)
        if (file.size > 1887436) {
            showError('Ukuran file terlalu besar. Maksimal 1.8 MB');
            return;
        }
        
        // Show preview
        var reader = new FileReader();
        reader.onload = function(e) {
            previewImage.attr('src', e.target.result);
            dropZone.addClass('d-none');
            previewContainer.removeClass('d-none');
            uploadButton.prop('disabled', false);
        };
        reader.readAsDataURL(file);
    }
    
    // File input change
    fileInput.on('change', function() {
        handleFile(this.files[0]);
    });
    
    // Drag and drop
    dropZone
        .on('dragenter dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        })
        .on('dragleave drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
            
            if (e.type === 'drop') {
                handleFile(e.originalEvent.dataTransfer.files[0]);
            }
        });
    
    // Cancel preview
    $('.cancel-preview').on('click', function() {
        fileInput.val('');
        previewContainer.addClass('d-none');
        dropZone.removeClass('d-none');
        uploadButton.prop('disabled', true);
        errorDiv.empty();
    });
    
    function showError(message) {
        errorDiv.html(
            '<div class="alert alert-danger mb-3">' + 
            '<i class="fas fa-exclamation-circle mr-1"></i>' + 
            message + 
            '</div>'
        );
        fileInput.val('');
        previewContainer.addClass('d-none');
        dropZone.removeClass('d-none');
        uploadButton.prop('disabled', true);
    }
    
    // Form submit
    form.on('submit', function() {
        uploadButton
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengupload...');
    });


});
