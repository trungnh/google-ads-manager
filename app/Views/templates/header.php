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
            
            // Toggle sidebar
            $('#sidebarToggle').on('click', function() {
                $('body').toggleClass('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', $('body').hasClass('sidebar-collapsed'));
            });

            // Toggle sidebar on mobile
            $('.navbar-toggler').on('click', function() {
                $('.sidebar').toggleClass('show');
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
        <div class="container-fluid px-0">
            <!-- Sidebar Toggle Button -->
            <button id="sidebarToggle" class="btn btn-link text-light me-3">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Toggler -->
            <button class="navbar-toggler ms-n3 bg-transparent border-0" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center mx-3" href="<?= base_url() ?>">
                <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo" width="30" height="30" class="me-2">
                <span>NNHD Ads Manager</span>
            </a>

            <!-- Right navbar -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav align-items-center ms-auto">
                    <?php if (session()->get('isLoggedIn')): ?>
                        <!-- User -->
                        <li class="nav-item dropdown">
                            <a class="nav-link pr-0" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <div class="me-2 d-none d-lg-inline">
                                        <span class="mb-0 text-sm font-weight-bold"><?= session()->get('username') ?></span>
                                    </div>
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <!-- <?php if (session()->get('role') === 'superadmin'): ?>
                                    <li><a class="dropdown-item" href="<?= base_url('users') ?>">
                                        <i class="fas fa-users-cog me-2"></i>Quản lý Users
                                    </a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= base_url('profile') ?>">
                                    <i class="fas fa-user me-2"></i>User Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?= base_url('settings') ?>">
                                    <i class="fas fa-cog me-2"></i>User Settings
                                </a></li> -->
                                <!-- <li><hr class="dropdown-divider"></li> -->
                                <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('login') ?>">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
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