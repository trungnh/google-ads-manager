<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NNHD Ads Manager</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
    <link rel="icon" href="<?= base_url('assets/images/logo.ico') ?>" type="image/x-icon">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Đợi DOM content loaded rồi mới thực thi
        document.addEventListener('DOMContentLoaded', function() {
            // Kiểm tra sidebar state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }
        });
        
        // Toastr configuration
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000",
            "extendedTimeOut": "1000",
            "preventDuplicates": true,
            "showDuration": "300",
            "hideDuration": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut",
            "background-color": "var(--bg-dark-3)",
            "color": "var(--text-light)"
        };

        function showNotification(message, type = 'success') {
            switch(type) {
                case 'success':
                    toastr.success(message);
                    break;
                case 'error':
                    toastr.error(message);
                    break;
                case 'warning':
                    toastr.warning(message);
                    break;
                case 'info':
                    toastr.info(message);
                    break;
            }
        }
        
        $(document).ready(function() {
            // Remove page-loading and add page-ready class when DOM is ready
            document.body.classList.remove('page-loading');
            document.body.classList.add('page-ready');
            
            // Toggle sidebar trên mobile và desktop
            $('#sidebarToggle').on('click', function(e) {
                e.preventDefault();
                if (window.innerWidth < 992) {
                    $('.sidebar').toggleClass('show');
                } else {
                    $('body').toggleClass('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', $('body').hasClass('sidebar-collapsed'));
                }
            });

            // Close sidebar when clicking outside of it on mobile
            $(document).on('click', function(e) {
                if (window.innerWidth < 992) {
                    if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('#sidebarToggle').length) {
                        $('.sidebar').removeClass('show');
                    }
                }
            });

            // Initialize dropdowns
            var dropdownTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            dropdownTriggerList.map(function(dropdownTriggerEl) {
                return new bootstrap.Dropdown(dropdownTriggerEl);
            });

            // Add smooth transitions
            $('.nav-link, .btn').addClass('transition-all');
        });
    </script>
</head>
<body class="page-loading">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary fixed-top">
        <div class="container-fluid">
            <?php if (session()->get('isLoggedIn')): ?>
            <button id="sidebarToggle" class="btn btn-link text-light">
                <i class="fas fa-bars"></i>
            </button>
            <?php endif; ?>
            
            <a class="navbar-brand d-flex align-items-center" href="<?= base_url('dashboard') ?>">
                <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo" class="me-2">
                <span>NNHD Ads Manager</span>
            </a>
            
            <?php if (session()->get('isLoggedIn')): ?>
                <a href="<?= base_url('logout') ?>" class="btn btn-link text-light d-lg-none">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                
                <!-- User dropdown (chỉ hiện trên desktop) -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav align-items-center ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link pr-0" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <span class="mb-0 text-sm font-weight-bold"><?= session()->get('username') ?></span>
                                    </div>
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                </a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a class="nav-link" href="<?= base_url('login') ?>">
                    <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Wrapper -->
    <div class="wrapper">
        <?php if (session()->get('isLoggedIn')): ?>
            <?= view('templates/sidebar') ?>
        <?php endif; ?>
        
        <!-- Main content -->
        <div class="content-wrapper">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                    <span class="alert-text"><?= session()->getFlashdata('success') ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                    <span class="alert-text"><?= session()->getFlashdata('error') ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php 
            $errors = session()->getFlashdata('errors');
            if (!empty($errors) && is_array($errors)): 
            ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                    <span class="alert-text">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>