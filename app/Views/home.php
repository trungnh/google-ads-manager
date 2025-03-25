<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Ads Manager - Quản lý chiến dịch quảng cáo thông minh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #4285f4 0%, #34a853 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .footer {
            background: #f8f9fa;
            padding: 40px 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">Google Ads Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (session()->get('isLoggedIn')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout">Đăng xuất</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register">Đăng ký</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/privacy-policy">Chính sách bảo mật</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/terms">Điều khoản dịch vụ</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Quản lý chiến dịch Google Ads thông minh</h1>
                    <p class="lead mb-4">Tự động hóa việc tối ưu chiến dịch quảng cáo với công nghệ AI, giúp tăng hiệu quả và giảm chi phí.</p>
                    <?php if (session()->get('isLoggedIn')): ?>
                        <a href="/dashboard" class="btn btn-light btn-lg">Vào Dashboard</a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-light btn-lg">Bắt đầu ngay</a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Tính năng nổi bật</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="card-body text-center">
                            <h3 class="h5 mb-3">Tối ưu tự động</h3>
                            <p class="card-text">Tự động điều chỉnh ngân sách và trạng thái chiến dịch dựa trên hiệu suất thực tế.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="card-body text-center">
                            <h3 class="h5 mb-3">Theo dõi chuyển đổi</h3>
                            <p class="card-text">Tích hợp với Google Sheets để theo dõi chuyển đổi thực tế và tối ưu chiến dịch.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="card-body text-center">
                            <h3 class="h5 mb-3">Báo cáo chi tiết</h3>
                            <p class="card-text">Xem báo cáo chi tiết về hiệu suất chiến dịch và ROI.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Google Ads Manager</h5>
                    <p>Giải pháp quản lý chiến dịch quảng cáo thông minh</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-2">
                        <a href="/privacy-policy" class="text-decoration-none text-dark me-3">Chính sách bảo mật</a>
                        <a href="/terms" class="text-decoration-none text-dark">Điều khoản dịch vụ</a>
                    </p>
                    <p class="mb-0">&copy; <?= date('Y') ?> Google Ads Manager. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>