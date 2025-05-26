<!-- app/Views/settings/index.php -->
<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>User Settings</h3>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('settings/update') ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="mcc_id">Google Ads MCC ID (Optional)</label>
                            <input type="number" class="form-control" id="mcc_id" name="mcc_id" 
                                   placeholder="Example: 1234567890" 
                                   value="<?= isset($settings['mcc_id']) ? $settings['mcc_id'] : '' ?>">
                            <small class="form-text text-muted">Nhập MCC ID dưới dạng số (không có dấu -) hoặc để trống</small>
                        </div>
                        
                        <h5 class="card-title mt-4">Cài đặt Thông báo Telegram</h5>
                        <small class="form-text text-muted">
                                <i>Nhập Telegram Chat ID để nhận thông báo. Để lấy Chat ID, thêm bot <strong>@check_don_bot</strong> vào group chat và lấy Chat ID</i>
                        </small>

                        <div class="form-group mb-3 mt-4">
                            <label for="telegram_chat_id">Chat ID Optimize (Optional)</label>
                            <input type="text" class="form-control" id="telegram_chat_id" name="telegram_chat_id" 
                                   placeholder="Example: -1001234567890" 
                                   value="<?= isset($settings['telegram_chat_id']) ? $settings['telegram_chat_id'] : '' ?>">
                            <small class="form-text text-muted">
                                <i>Các thông báo bật/tắt, tăng ngân sách chiến dịch</i>
                            </small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="report_telegram_chat_id">Chat ID Report Campaigns (Optional)</label>
                            <input type="text" class="form-control" id="report_telegram_chat_id" name="report_telegram_chat_id" 
                                   placeholder="Example: -1001234567890" 
                                   value="<?= isset($settings['report_telegram_chat_id']) ? $settings['report_telegram_chat_id'] : '' ?>">
                            <small class="form-text text-muted">
                                <i>Các thông báo về chỉ số chiến dịch</i>
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telegram_proxy" class="form-label">Proxy Telegram</label>
                            <input type="text" class="form-control" id="telegram_proxy" name="telegram_proxy" 
                                    value="<?= isset($settings['telegram_proxy']) ? $settings['telegram_proxy'] : '' ?>"
                                    placeholder="IP:port:username:password">
                            <div class="form-text text-muted"><i>Định dạng proxy: IP:port:username:password</i></div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="use_telegram_proxy" name="use_telegram_proxy" 
                                    <?= (isset($settings['use_telegram_proxy']) && $settings['use_telegram_proxy']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="use_telegram_proxy">Sử dụng proxy cho Telegram</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>