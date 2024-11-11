(function($) {
    "use strict";

    // Toggle the side navigation
    $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
        $("body").toggleClass("sidebar-toggled");
        $(".sidebar").toggleClass("toggled");
        if ($(".sidebar").hasClass("toggled")) {
            $('.sidebar .collapse').collapse('hide');
        };
    });

    // Close any open menu accordions when window is resized below 768px
    $(window).resize(function() {
        if ($(window).width() < 768) {
            $('.sidebar .collapse').collapse('hide');
        };
        
        // Toggle the side navigation when window is resized below 480px
        if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
            $("body").addClass("sidebar-toggled");
            $(".sidebar").addClass("toggled");
            $('.sidebar .collapse').collapse('hide');
        };
    });

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
        if ($(window).width() > 768) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;
            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
            e.preventDefault();
        }
    });

    // Scroll to top button appear
    $(document).on('scroll', function() {
        var scrollDistance = $(this).scrollTop();
        if (scrollDistance > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function(e) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top)
        }, 1000, 'easeInOutExpo');
        e.preventDefault();
    });

    // Prevent the dropdown menus from closing when clicking inside 
    $('.dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });

    // Initialize tooltips everywhere
    $('[data-toggle="tooltip"]').tooltip();

    // Initialize popovers everywhere
    $('[data-toggle="popover"]').popover();

    // Dismiss popovers on next click
    $('.popover-dismiss').popover({
        trigger: 'focus'
    });

    // Toggle the side navigation
    $('#sidebarToggle').on('click', function(e) {
        e.preventDefault();
        $('body').toggleClass('sb-sidenav-toggled');
    });

    // Add active state to sidbar nav links
    var path = window.location.href;
    $("#layoutSidenav_nav .sb-sidenav a.nav-link").each(function() {
        if (this.href === path) {
            $(this).addClass("active");
        }
    });

    // Custom form validation styles
    window.addEventListener('load', function() {
        // Fetch all forms we want to apply custom validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);

    // DataTables initialization
    if($.fn.dataTable) {
        $('.dataTable').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari...",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Data tidak ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir", 
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });
    }

    // Chart.js initialization
    if(typeof Chart !== 'undefined') {
        // Set default Chart.js colors
        Chart.defaults.global.defaultFontFamily = 'Nunito';
        Chart.defaults.global.defaultFontColor = '#858796';
        
        // Add number formatting
        Chart.Tooltip.prototype.beforeTitle = function() {
            return '';
        };
        
        Chart.Tooltip.prototype.labelColor = function(tooltipItem, chart) {
            return {
                borderColor: chart.data.datasets[tooltipItem.datasetIndex].borderColor,
                backgroundColor: chart.data.datasets[tooltipItem.datasetIndex].borderColor
            };
        };
    }

    // Bootstrap Custom File Input
    $('.custom-file input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Responsive table wrapper
    $('.table-responsive').on('show.bs.dropdown', function() {
        $('.table-responsive').css("overflow", "inherit");
    });

    $('.table-responsive').on('hide.bs.dropdown', function() {
        $('.table-responsive').css("overflow", "auto");
    });

    // Handle dismissible alerts
    $('.alert-dismissible .close').on('click', function() {
        $(this).parent().alert('close');
    });

    // Initialize Bootstrap Select
    if($.fn.selectpicker) {
        $('.selectpicker').selectpicker();
    }

    // Initialize Date Picker
    if($.fn.datepicker) {
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true,
            language: 'id'
        });
    }

    // Initialize Masked Input
    if($.fn.mask) {
        $('.date-mask').mask('00/00/0000');
        $('.time-mask').mask('00:00:00');
        $('.phone-mask').mask('0000-0000-0000');
        $('.npwp-mask').mask('00.000.000.0-000.000');
    }

    // Fix header when scrolled
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.sticky-top').addClass('shadow-sm');
        } else {
            $('.sticky-top').removeClass('shadow-sm');
        }
    });

    // Custom file upload preview
    $('input[type="file"]').change(function(e) {
        var fileName = e.target.files[0].name;
        $('.custom-file-label').html(fileName);
    });

    // Print functionality
    $('.btn-print').on('click', function() {
        window.print();
        return false;
    });

    // Back to previous page
    $('.btn-back').on('click', function() {
        window.history.back();
        return false;
    });

    // Confirm delete
    $('form.delete-form').on('submit', function() {
        return confirm('Apakah Anda yakin ingin menghapus data ini?');
    });

    // Auto hide success messages
    window.setTimeout(function() {
        $(".alert-success").fadeTo(500, 0).slideUp(500, function() {
            $(this).remove();
        });
    }, 3000);

})(jQuery); // End of use strict