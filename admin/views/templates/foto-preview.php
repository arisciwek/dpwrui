<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/templates/foto-preview.php
 * Version: 1.0.0
 */
?>

<script type="text/template" id="tmpl-dpw-rui-foto-preview">
    <div class="dpw-rui-foto-item">
        <div class="dpw-rui-foto-preview">
            <img src="{{ data.url }}" alt="{{ data.title }}">
        </div>
        <div class="actions">
            <# if (!data.is_main) { #>
                <a href="{{ data.set_main_url }}" 
                   class="btn btn-primary btn-sm set-main-photo"
                   title="Jadikan Foto Utama">
                    <i class="fas fa-star"></i>
                </a>
            <# } #>
            <# if (!data.is_main || data.total_photos > 1) { #>
                <a href="{{ data.delete_url }}"
                   class="btn btn-danger btn-sm delete-photo"
                   title="Hapus Foto">
                    <i class="fas fa-trash"></i>
                </a>
            <# } #>
        </div>
        <# if (data.is_main) { #>
            <div class="badge badge-primary position-absolute" 
                 style="top: 10px; left: 10px;">
                Foto Utama
            </div>
        <# } #>
    </div>
</script>