<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NNHD Ads Manager</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
    <link rel="icon" href="<?= base_url('assets/images/logo.ico') ?>" type="image/x-icon">
    
    <!-- jQuery (must be loaded before Bootstrap JS) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Popper.js (required for Bootstrap 5 dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <!-- Bootstrap Bundle (alternative if separate files don't work) -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Cấu hình toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // Hàm hiển thị thông báo
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
        
        // Initialize Bootstrap components when the document is ready
        $(document).ready(function() {
            // For debugging - log to make sure jQuery and Bootstrap are loaded
            console.log('jQuery version:', $.fn.jquery);
            console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
            
            // Make sure dropdown elements are properly initialized
            var dropdownTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            dropdownTriggerList.map(function(dropdownTriggerEl) {
                return new bootstrap.Dropdown(dropdownTriggerEl);
            });
            
            // Alternative method using jQuery
            $('.dropdown-toggle').on('click', function(e) {
                e.stopPropagation();
                var dropdown = new bootstrap.Dropdown(this);
                dropdown.toggle();
            });
        });
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo" width="30" height="30">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (session()->get('isLoggedIn')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard') ?>">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('adsaccounts') ?>">Ads Account</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('optimize-logs') ?>">Lịch sử tối ưu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('settings') ?>">Cài đặt</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('google/oauth') ?>">Kết nối Google Ads</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (session()->get('isLoggedIn')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= session()->get('username') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="<?= base_url('logout') ?>">Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('login') ?>">Đăng nhập</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        
        <?php 
        $errors = session()->getFlashdata('errors');
        if (!empty($errors) && is_array($errors)): 
        ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>