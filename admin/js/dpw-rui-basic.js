/**
 * DPW RUI Basic Javascript
 * Version: 1.0.2
 * Support fungsi dasar CRUD tanpa ajax
 */

(function($) {
    "use strict";

    // Form validation tanpa bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Tampilkan pesan error native
                    var fields = form.querySelectorAll('input[required], textarea[required]');
                    fields.forEach(function(field) {
                        if (!field.validity.valid) {
                            field.classList.add('error');
                        }
                    });
                }
            }, false);
        });
    });

    // Konfirmasi hapus data
    $('.delete-action').on('click', function(e) {
        e.preventDefault();
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            window.location.href = this.href;
        }
    });

    // Pesan sukses auto hide
    var successMessage = document.querySelector('.updated.notice-success');
    if (successMessage) {
        setTimeout(function() {
            successMessage.style.display = 'none';
        }, 3000);
    }

    // Reset form
    $('.btn-reset').on('click', function(e) {
        e.preventDefault();
        $(this).closest('form').trigger('reset');
    });

    // Nomor anggota preview
    $('#nama_perusahaan').on('change', function() {
        var date = new Date();
        var prefix = String(date.getDate()).padStart(2,'0') + 
                    String(date.getMonth() + 1).padStart(2,'0') + 
                    date.getFullYear();
        $('#nomor_preview').text(prefix + '-XXXXX');
    });

})(jQuery);