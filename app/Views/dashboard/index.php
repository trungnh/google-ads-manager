<?= $this->include('templates/header') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Bảng điều khiển</h4>
            </div>
            <div class="card-body">
                <h5>Chào mừng, <?= session()->get('username') ?>!</h5>
                
                <?php if (!$hasGoogleToken): ?>
                <div class="alert alert-warning mt-4">
                    <p><strong>Chưa kết nối với Google Ads.</strong></p>
                    <p>Để bắt đầu quản lý tài khoản Google Ads, vui lòng kết nối tài khoản Google của bạn trước.</p>
                    <a href="<?= base_url('google/oauth') ?>" class="btn btn-outline-primary">Kết nối ngay</a>
                </div>
                <?php else: ?>
                <div class="alert alert-success mt-4">
                    <p><strong>Đã kết nối với Google Ads!</strong></p>
                    <p>Bạn đã sẵn sàng để sử dụng các tính năng quản lý Google Ads.</p>
                </div>
                
                <!-- Placeholder cho các tính năng quản lý Google Ads sẽ được thêm sau -->
                <div class="row g-4 py-4">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Cài đặt</h5>
                                <p class="card-text">Thiết lập MCC ID và các cài đặt khác</p>
                                <a href="<?= base_url('settings') ?>" class="btn btn-primary">Đi đến cài đặt</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Đồng bộ tài khoản</h5>
                                <p class="card-text">Đồng bộ các tài khoản Google Ads</p>
                                <a href="<?= base_url('syncads') ?>" class="btn btn-primary">Đồng bộ ngay</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Danh sách tài khoản</h5>
                                <p class="card-text">Xem và quản lý tài khoản Google Ads</p>
                                <a href="<?= base_url('adsaccounts') ?>" class="btn btn-primary">Xem danh sách</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>