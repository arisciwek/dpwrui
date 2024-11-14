<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/templates/foto/preview.php
 * Version: 1.1.0
 * 
 * Template for photo preview
 * Previously: /admin/views/templates/foto-preview.php
 * 
 * Changelog:
 * 1.1.0
 * - Moved to new location
 * - Added more detailed comments
 * - Improved template variables
 * - Added status indicators
 * - Added loading states
 * - Added error states
 * - Improved action button styling
 * - Added accessibility improvements
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<script type="text/template" id="tmpl-dpw-rui-foto-preview">
    <div class="dpw-rui-foto-item" id="foto-{{ data.id }}">
        <div class="dpw-rui-foto-preview">
            <img src="{{ data.url }}" 
                 alt="{{ data.title }}" 
                 class="foto-image"
                 loading="lazy">

            <# if (data.isLoading) { #>
                <div class="dpw-rui-foto-loading">
                    <span class="screen-reader-text">Sedang memuat...</span>
                </div>
            <# } #>
        </div>
        
        <# if (data.canManage) { #>
            <div class="actions">
                <# if (!data.is_main) { #>
                    <a href="{{ data.set_main_url }}" 
                       class="btn btn-primary btn-sm set-main-photo"
                       title="Jadikan Foto Utama"
                       aria-label="Jadikan Foto Utama">
                        <i class="fas fa-star" aria-hidden="true"></i>
                    </a>
                <# } #>
                
                <# if (!data.is_main || data.total_photos > 1) { #>
                    <a href="{{ data.delete_url }}"
                       class="btn btn-danger btn-sm delete-photo"
                       title="Hapus Foto"
                       aria-label="Hapus Foto"
                       data-is-main="{{ data.is_main }}">
                        <i class="fas fa-trash" aria-hidden="true"></i>
                    </a>
                <# } #>
            </div>
        <# } #>
        
        <# if (data.is_main) { #>
            <div class="badge badge-primary position-absolute main-photo-badge" 
                 style="top: 10px; left: 10px;">
                <i class="fas fa-check-circle mr-1" aria-hidden="true"></i>
                <span>Foto Utama</span>
            </div>
        <# } #>

        <# if (data.status) { #>
            <div class="foto-status position-absolute" style="bottom: 10px; left: 10px;">
                <span class="badge badge-{{ data.status.type }}">
                    <i class="fas fa-{{ data.status.icon }} mr-1" aria-hidden="true"></i>
                    {{ data.status.text }}
                </span>
            </div>
        <# } #>

        <# if (data.error) { #>
            <div class="dpw-rui-foto-error-message">
                {{ data.error }}
            </div>
        <# } #>
    </div>
</script>