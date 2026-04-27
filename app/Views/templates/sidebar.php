<?php
$currentUrl = str_replace('/index.php', '', current_url());
$isActive = static function (array $urls) use ($currentUrl): string {
    foreach ($urls as $url) {
        $fullUrl = base_url($url);
        if ($currentUrl === $fullUrl || strpos($currentUrl, rtrim($fullUrl, '/') . '/') === 0) {
            return 'active';
        }
    }

    return '';
};
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="d-flex flex-column h-100">
        <div class="sidebar-header p-3"></div>

        <div class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Tổng quan</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('dashboard') ?>" class="nav-link <?= $isActive(['dashboard']) ?>">
                            <i class="fas fa-house"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-divider"></div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Quản lý</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('adsaccounts') ?>" class="nav-link <?= $isActive(['adsaccounts']) ?>">
                            <i class="fas fa-table-columns"></i>
                            <span>Tài khoản Ads</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= base_url('campaigns/index') ?>" class="nav-link <?= $isActive(['campaigns']) ?>">
                            <i class="fas fa-bullhorn"></i>
                            <span>Chiến dịch</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= base_url('campaignschedules') ?>"
                            class="nav-link <?= $isActive(['campaignschedules']) ?>">
                            <i class="fas fa-clock"></i>
                            <span>Lập lịch</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= base_url('optimize-logs') ?>" class="nav-link <?= $isActive(['optimize-logs']) ?>">
                            <i class="fas fa-file-lines"></i>
                            <span>Lịch sử tối ưu</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-divider"></div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Thống Kê & Báo cáo</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('revenue_reports/overview') ?>"
                            class="nav-link <?= $isActive(['revenue_reports/overview']) ?>">
                            <i class="fas fa-chart-pie"></i>
                            <span>Tổng quan doanh thu</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('revenue_reports') ?>"
                            class="nav-link <?= $isActive(['revenue_reports', 'revenue_reports/index']) ?>">
                            <i class="fas fa-chart-column"></i>
                            <span>Thống kê doanh thu</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= base_url('products') ?>" class="nav-link <?= $isActive(['products']) ?>">
                            <i class="fas fa-box"></i>
                            <span>Sản phẩm</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-divider"></div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Kết nối</div>

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('syncads') ?>" class="nav-link <?= $isActive(['syncads']) ?>">
                            <i class="fas fa-wave-square"></i>
                            <span>Đồng bộ tài khoản Ads</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= base_url('google/oauth') ?>" class="nav-link <?= $isActive(['google/oauth']) ?>">
                            <i class="fas fa-link"></i>
                            <span>Kết nối Google Ads</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-divider"></div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Hệ thống</div>
                <ul class="nav flex-column">

                    <li class="nav-item">
                        <a href="<?= base_url('profile') ?>" class="nav-link <?= $isActive(['profile']) ?>">
                            <i class="fas fa-id-badge"></i>
                            <span>Thông tin cá nhân</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('settings') ?>" class="nav-link <?= $isActive(['settings']) ?>">
                            <i class="fas fa-gear"></i>
                            <span>User Setting</span>
                        </a>
                    </li>
                    <?php if (session()->get('role') === 'superadmin'): ?>
                        <li class="nav-item">
                            <a href="<?= base_url('users') ?>" class="nav-link <?= $isActive(['users']) ?>">
                                <i class="fas fa-users-cog"></i>
                                <span>Quản lý Users</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="<?= base_url('guide') ?>" class="nav-link <?= $isActive(['guide']) ?>">
                            <i class="fas fa-circle-question"></i>
                            <span>Hướng dẫn</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>