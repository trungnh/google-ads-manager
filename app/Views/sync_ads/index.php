<!-- app/Views/sync_ads/index.php -->
<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Đồng bộ tài khoản Google Ads</h3>
                </div>
                <div class="card-body">

                    <p>Tính năng này sẽ đồng bộ tất cả các tài khoản Google Ads mà bạn có quyền truy cập.</p>
                    
                    <?php if (!$isConnected): ?>
                        <div class="alert alert-warning">
                            <strong>Lưu ý:</strong> Bạn chưa kết nối với Google Ads.
                            <br>
                            <a href="<?= base_url('google/oauth') ?>" class="btn btn-primary mt-2">Kết nối với Google Ads</a>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($settings['mcc_id'])): ?>
                            <div class="alert alert-info">
                                <strong>Lưu ý:</strong> Bạn đã cấu hình MCC ID: <?= $settings['mcc_id'] ?>. 
                                Hệ thống sẽ đồng bộ tất cả các tài khoản có thể truy cập dưới MCC này.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <strong>Lưu ý:</strong> Bạn chưa cấu hình MCC ID. 
                                Hệ thống sẽ đồng bộ tất cả các tài khoản Google Ads mà bạn có quyền truy cập.
                                <br>
                                <a href="<?= base_url('settings') ?>">Cấu hình MCC ID</a> nếu bạn muốn truy cập nhiều tài khoản.
                            </div>
                        <?php endif; ?>

                        <form action="<?= base_url('syncads/syncaccounts') ?>" method="post">
                            <button type="submit" class="btn btn-primary mb-2 mb-md-0">Bắt đầu đồng bộ</button>
                            <a href="<?= base_url('adsaccounts') ?>" class="btn btn-secondary ml-2">Xem tài khoản hiện có</a>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>