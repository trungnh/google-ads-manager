<!-- Sidebar -->
<div class="sidebar">
    <div class="d-flex flex-column h-100">
        <!-- Sidebar header -->
        <div class="sidebar-header p-3"></div>

        <!-- Nav items -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="<?= base_url('dashboard') ?>" class="nav-link <?= str_replace('/index.php', '', current_url()) == base_url('dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?= base_url('adsaccounts') ?>" class="nav-link <?= str_replace('/index.php', '', current_url()) == base_url('adsaccounts') ? 'active' : '' ?>">
                    <i class="fas fa-ad"></i>
                    <span>Ads Accounts</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= base_url('optimize-logs') ?>" class="nav-link <?= str_replace('/index.php', '', current_url()) == base_url('optimize-logs') ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Lịch sử tối ưu</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= base_url('settings') ?>" class="nav-link <?= str_replace('/index.php', '', current_url()) == base_url('settings') ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i>
                    <span>User Settings</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= base_url('profile') ?>" class="nav-link <?= str_replace('/index.php', '', current_url()) == base_url('profile') ? 'active' : '' ?>">
                    <i class="fas fa-user"></i>
                    <span>User Profile</span>
                </a>
            </li>

            <?php if (session()->get('role') === 'superadmin'): ?>
                <li class="nav-item">
                    <a href="<?= base_url('users') ?>" class="nav-link <?= str_replace('/index.php', '', current_url()) == base_url('users') ? 'active' : '' ?>">
                        <i class="fas fa-users-cog"></i>
                        <span>Quản lý Users</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a href="<?= base_url('syncads') ?>" class="nav-link <?= str_replace('/index.php', '', current_url()) == base_url('syncads') ? 'active' : '' ?>">
                    <i class="fas fa-sync"></i>
                    <span>Đồng bộ tài khoản Ads</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= base_url('google/oauth') ?>" class="nav-link <?= str_replace('/index.php', '', current_url()) == base_url('google/oauth') ? 'active' : '' ?>">
                    <i class="fab fa-google"></i>
                    <span>Kết nối Google Ads</span>
                </a>
            </li>
        </ul>

        <!-- Divider -->
        <hr class="my-3">

        <!-- Help -->
        <ul class="nav flex-column mt-auto">
            <li class="nav-item">
                <a href="<?= base_url('profile') ?>" class="nav-link">
                    <i class="fas fa-user"></i>
                    <span>User Profile</span>
                </a>
            </li>
        </ul>
    </div>
</div>