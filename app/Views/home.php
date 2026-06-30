<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NNHD Ads Manager - Quản lý chiến dịch quảng cáo thông minh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= base_url('assets/images/logo.ico') ?>" type="image/x-icon">
    <style>
        :root {
            --primary: #1a73e8; /* Google Blue */
            --primary-hover: #1557b0;
            --success: #34a853; /* Google Green */
            --dark: #0f172a; /* Slate 900 */
            --text: #334155; /* Slate 700 */
            --light: #f8fafc; /* Slate 50 */
            --border: #e2e8f0; /* Slate 200 */
            --font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        body {
            font-family: var(--font-family);
            color: var(--text);
            background-color: #ffffff;
            line-height: 1.6;
        }

        /* Navbar Styling */
        .navbar {
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
        }
        .navbar-brand img {
            transition: transform 0.3s ease;
        }
        .navbar-brand:hover img {
            transform: scale(1.05);
        }
        .nav-link {
            font-weight: 500;
            color: var(--text) !important;
            padding: 8px 16px !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--primary) !important;
            background-color: var(--light);
        }
        .btn-login {
            background-color: var(--primary);
            color: white !important;
            font-weight: 600;
        }
        .btn-login:hover {
            background-color: var(--primary-hover);
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            background: radial-gradient(circle at 10% 20%, rgba(26, 115, 232, 0.05) 0%, rgba(52, 168, 83, 0.03) 90%);
            padding: 120px 0 100px;
            overflow: hidden;
        }
        .hero-title {
            color: var(--dark);
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.02em;
        }
        .hero-subtitle {
            font-size: 1.15rem;
            color: #475569;
            margin-bottom: 2rem;
            max-width: 580px;
        }
        .btn-hero-primary {
            background-color: var(--primary);
            color: white;
            padding: 14px 28px;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(26, 115, 232, 0.3);
            transition: all 0.2s ease;
        }
        .btn-hero-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 115, 232, 0.4);
            color: white;
        }

        /* Feature Cards */
        .feature-card {
            border: 1px solid var(--border);
            border-radius: 16px;
            background-color: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 32px;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            border-color: rgba(26, 115, 232, 0.2);
        }
        .feature-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background-color: rgba(26, 115, 232, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        /* Google Integration Section */
        .google-integration-section {
            background-color: var(--light);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 80px 0;
        }
        .integration-badge {
            display: inline-block;
            padding: 6px 12px;
            background-color: #ffffff;
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 16px;
        }

        /* Footer */
        .footer {
            background-color: var(--dark);
            color: #94a3b8;
            padding: 60px 0 40px;
        }
        .footer h5 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .footer a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .footer a:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo" width="32" height="32" class="me-2">
                <span class="fw-bold text-dark" style="letter-spacing: -0.01em;">NNHD Ads Manager</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="/">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/privacy-policy">Chính sách bảo mật</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/terms">Điều khoản dịch vụ</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <?php if (session()->get('isLoggedIn')): ?>
                            <a class="nav-link btn btn-login text-white px-4 py-2" href="/dashboard">Dashboard</a>
                        <?php else: ?>
                            <a class="nav-link btn btn-login text-white px-4 py-2" href="/login">Đăng nhập</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center py-5">
                <div class="col-lg-7">
                    <h1 class="hero-title mb-4">Quản lý chiến dịch Google Ads thông minh</h1>
                    <p class="hero-subtitle">Tự động hóa tối ưu hóa ngân sách, theo dõi hiệu suất thời gian thực và quản lý đa tài khoản Google Ads hiệu quả chỉ trên một nền tảng duy nhất.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <?php if (session()->get('isLoggedIn')): ?>
                            <a href="/dashboard" class="btn btn-hero-primary d-inline-flex align-items-center">
                                Vào Dashboard quản trị
                            </a>
                        <?php else: ?>
                            <a href="/login" class="btn btn-hero-primary d-inline-flex align-items-center">
                                Bắt đầu trải nghiệm ngay
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <!-- Premium visual decorative elements -->
                    <div class="p-5 bg-white rounded-4 shadow-sm border border-light text-center">
                        <div class="d-inline-flex p-3 rounded-circle bg-light text-primary mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-graph-up-arrow" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.707l-5.384 5.385a.5.5 0 0 1-.707 0L7 9.207 1.354 14.854a.5.5 0 0 1-.708-.708L6.5 8.293 8.354 10.15 13.293 5.2H10.5a.5.5 0 0 1-.5-.5"/>
                            </svg>
                        </div>
                        <h4 class="fw-bold text-dark">Tối ưu hóa chiến dịch</h4>
                        <p class="text-muted small">Quản lý ngân sách, vị trí địa lý và các nhóm tiêu chí thông minh thông qua kết nối API trực tiếp của Google.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 my-4">
        <div class="container">
            <div class="text-center max-w-xl mx-auto mb-5">
                <h2 class="fw-bold text-dark mb-3">Tính năng nổi bật</h2>
                <p class="text-muted">Bộ công cụ chuyên nghiệp giúp tối ưu và giám sát hiệu quả phân phối quảng cáo của bạn.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.5 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                            </svg>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-3">Điều chỉnh ngân sách & Trạng thái</h3>
                        <p class="text-muted">Cập nhật nhanh chóng ngân sách chiến dịch, bật/tắt (enable/pause) các nhóm quảng cáo và chiến dịch dựa trên dữ liệu thống kê.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
                                <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/>
                                <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4"/>
                            </svg>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-3">Quản lý nhắm mục tiêu địa lý</h3>
                        <p class="text-muted">Giám sát và kiểm tra cài đặt vị trí địa lý (geo targeting) của từng chiến dịch, tối ưu hóa tệp khách hàng theo vùng miền.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16">
                                <path d="M5.338 1.59a61 61 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.117.724 7.21 2.518 8.72a.5.5 0 0 0 .625 0c1.794-1.51 3.072-4.603 2.518-8.72a.48.48 0 0 0-.328-.39 60 60 0 0 0-2.837-.856Zm-.086-1.09a63 63 0 0 1 3.12.94c.387.13.667.48.667.896 0 5.161-2.824 8.414-4.148 9.347a.5.5 0 0 1-.578 0C3.008 10.762 0 7.509 0 2.336c0-.417.28-.767.668-.897a63 63 0 0 1 3.12-.94h.048z"/>
                                <path d="M7.005 3.1a1 1 0 1 1-2 0 1 1 0 0 1 2 0M5 9.347l-1.39-1.4a.5.5 0 1 1 .71-.704l.79.8 2.09-2.09a.5.5 0 0 1 .71.704z"/>
                            </svg>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-3">Kiểm tra chính sách vi phạm</h3>
                        <p class="text-muted">Tích hợp bộ kiểm tra chính sách tự động từ Google Ads API nhằm phát hiện nhanh các quảng cáo bị từ chối hoặc vi phạm.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Google Integration Section -->
    <section class="google-integration-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <span class="integration-badge">Tích hợp Google Ads & Google Sheets API chính thức</span>
                    <h2 class="fw-bold text-dark mb-4">Minh bạch về kết nối và bảo vệ dữ liệu</h2>
                    <p class="text-muted mb-3">NNHD Ads Manager kết nối trực tiếp với tài khoản Google Ads và dịch vụ Google Sheets của bạn thông qua giao thức bảo mật Google OAuth 2.0. Chúng tôi cam kết tôn trọng quyền riêng tư của bạn và tuân thủ các chính sách nghiêm ngặt nhất của Google về bảo vệ dữ liệu:</p>
                    <ul class="text-muted ps-3 mb-4">
                        <li class="mb-2"><strong>Mục đích kết nối:</strong> Ứng dụng chỉ sử dụng quyền truy cập `https://www.googleapis.com/auth/adwords` (để hiển thị thống kê chiến dịch và cho phép thực hiện thao tác bật/tắt hoặc sửa ngân sách) và `https://www.googleapis.com/auth/spreadsheets.readonly` (để đọc cấu hình chuyển đổi hoặc danh sách từ khóa mẫu từ tệp trang tính do bạn cung cấp).</li>
                        <li class="mb-2"><strong>Không chia sẻ dữ liệu:</strong> Dữ liệu từ tài khoản quảng cáo và file trang tính của bạn không bao giờ được chia sẻ cho bất kỳ bên thứ ba nào, không bán và không sử dụng cho các mục đích quảng cáo khác ngoài nhu cầu sử dụng của chính bạn.</li>
                        <li class="mb-2"><strong>Không sử dụng để huấn luyện AI:</strong> Chúng tôi hoàn toàn không sử dụng dữ liệu của bạn để huấn luyện, cải tiến bất kỳ mô hình AI/ML tổng quát hoặc ngôn ngữ nào.</li>
                        <li class="mb-2"><strong>An toàn bảo mật:</strong> Token truy cập được mã hóa và lưu trữ cực kỳ bảo mật. Bạn có thể thu hồi quyền truy cập này bất cứ lúc nào trong bảng điều khiển Tài khoản Google cá nhân.</li>
                    </ul>
                    <div class="d-flex gap-3">
                        <a href="/privacy-policy" class="btn btn-outline-dark btn-sm">Xem Chính sách bảo mật</a>
                        <a href="/terms" class="btn btn-outline-dark btn-sm">Xem Điều khoản dịch vụ</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="p-4 bg-white rounded-4 border border-light shadow-sm">
                        <h4 class="fw-bold text-dark mb-3">Phạm vi quyền hạn (OAuth Scope)</h4>
                        <div class="p-3 bg-light rounded-3 mb-3 border-start border-primary border-4">
                            <code class="text-primary fw-semibold" style="word-break: break-all;">https://www.googleapis.com/auth/adwords</code>
                            <p class="small text-muted mt-2 mb-0">Cho phép quản lý các tài khoản quảng cáo và chiến dịch Google Ads của bạn một cách bảo mật.</p>
                        </div>
                        <div class="p-3 bg-light rounded-3 mb-3 border-start border-success border-4">
                            <code class="text-success fw-semibold" style="word-break: break-all;">https://www.googleapis.com/auth/spreadsheets.readonly</code>
                            <p class="small text-muted mt-2 mb-0">Cho phép ứng dụng đọc dữ liệu từ các file Google Sheets mẫu để đồng bộ các cấu hình quảng cáo và dữ liệu chuyển đổi.</p>
                        </div>
                        <p class="small text-muted mb-0">Việc phê duyệt token và cấp quyền được thực hiện trực tiếp trên máy chủ bảo mật của Google. NNHD Ads Manager không bao giờ lưu trữ mật khẩu Google của bạn.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h5 class="d-flex align-items-center">
                        <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo" width="28" height="28" class="me-2 filter-light" style="filter: brightness(0) invert(1);">
                        <span>NNHD Ads Manager</span>
                    </h5>
                    <p class="small text-slate-400">Giải pháp quản lý và tối ưu hóa hiệu quả các chiến dịch Google Ads an toàn, minh bạch và hiệu quả.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5 class="d-none d-md-block">&nbsp;</h5>
                    <p class="mb-3">
                        <a href="/privacy-policy" class="me-3 small">Chính sách bảo mật</a>
                        <a href="/terms" class="small">Điều khoản dịch vụ</a>
                    </p>
                    <p class="small text-slate-400 mb-0">&copy; <?= date('Y') ?> NNHD Ads Manager. Đã đăng ký bản quyền.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>