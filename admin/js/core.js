/**
 * Path: /wp-content/plugins/dpwrui/admin/js/core.js
 * Version: 1.0.0
 * Core JavaScript functionality for DPW RUI plugin
 */

jQuery(document).ready(function($) {
    // Tab handling
    function initTabs() {
        var currentTab = window.location.search.match(/[?&]tab=([^&]*)/);
        currentTab = currentTab ? currentTab[1] : 'umum';
        
        $('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
        $('.nav-tab-wrapper .nav-tab[href*="tab=' + currentTab + '"]').addClass('nav-tab-active');
    }

    // Form validation
    function initFormValidation() {
        $('form.needs-validation').on('submit', function(e) {
            if (this.checkValidity() === false) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
    }

    // Notification handling
    function initNotifications() {
        $('.notice.is-dismissible').each(function() {
            var $notice = $(this);
            setTimeout(function() {
                $notice.fadeOut();
            }, 3000);
        });
    }

    // Initialize all functionality
    function init() {
        initTabs();
        initFormValidation();
        initNotifications();
    }

    // Run initialization
    init();
});