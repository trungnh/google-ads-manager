<!-- app/Views/settings/index.php -->
<?= $this->include('templates/header') ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <h3>User Settings</h3>
                </div>
                <div class="card-body">
                    <?php if (session()->has('success')): ?>
                        <div class="alert alert-success">
                            <?= session('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->has('errors')): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach (session('errors') as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('settings/update') ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="mcc_id">Google Ads MCC ID (Optional)</label>
                            <input type="number" class="form-control" id="mcc_id" name="mcc_id" 
                                   placeholder="Example: 1234567890" 
                                   value="<?= isset($settings['mcc_id']) ? $settings['mcc_id'] : '' ?>">
                            <small class="form-text text-muted">Nhập MCC ID dưới dạng số (không có dấu -) hoặc để trống</small>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="telegram_chat_id">Telegram Chat ID (Optional)</label>
                            <input type="text" class="form-control" id="telegram_chat_id" name="telegram_chat_id" 
                                   placeholder="Example: -1001234567890" 
                                   value="<?= isset($settings['telegram_chat_id']) ? $settings['telegram_chat_id'] : '' ?>">
                            <small class="form-text text-muted">Nhập Telegram Chat ID để nhận thông báo. Để lấy Chat ID, thêm bot @your_bot vào group chat và gửi tin nhắn /chat_id</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>