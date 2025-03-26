<?= $this->include('templates/header') ?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col">
            <h2>Cài đặt tài khoản - <?= esc($account['customer_name']) ?></h2>
            <p>ID tài khoản: <?= esc($account['customer_id']) ?></p>
        </div>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Cài đặt tối ưu hóa tự động</h5>
                </div>
                <div class="card-body">
                    <form id="settingsForm">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_optimize" name="auto_optimize" 
                                    <?= isset($settings['auto_optimize']) && $settings['auto_optimize'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="auto_optimize">Tự động tối ưu</label>
                            </div>
                            <small class="form-text text-muted">
                                Khi bật, hệ thống sẽ tự động kiểm tra và tối ưu chiến dịch mỗi 5 phút
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="cpa_threshold" class="form-label">Ngưỡng CPA (VNĐ)</label>
                            <input type="number" class="form-control" id="cpa_threshold" name="cpa_threshold" 
                                value="<?= isset($settings['cpa_threshold']) ? $settings['cpa_threshold'] : '' ?>">
                            <small class="form-text text-muted">
                                Chiến dịch sẽ bị tạm dừng nếu CPA vượt quá ngưỡng này
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="roas_threshold" class="form-label">Ngưỡng ROAS</label>
                            <input type="number" class="form-control" id="roas_threshold" name="roas_threshold" step="0.01" 
                                value="<?= isset($settings['roas_threshold']) ? $settings['roas_threshold'] : '' ?>">
                            <small class="form-text text-muted">Nhập ngưỡng ROAS để tự động tắt chiến dịch khi ROAS thực tế thấp hơn ngưỡng này</small>
                        </div>

                        <div class="mb-3">
                            <label for="increase_budget" class="form-label">Tăng ngân sách (VNĐ)</label>
                            <input type="number" class="form-control" id="increase_budget" name="increase_budget" 
                                value="<?= isset($settings['increase_budget']) ? $settings['increase_budget'] : '' ?>">
                            <small class="form-text text-muted">
                                Số tiền tăng thêm khi chiến dịch đã chi tiêu > 50% ngân sách
                            </small>
                        </div>

                        <h5 class="card-title mb-4 mt-5">Cài đặt Google Sheet (Chuyển đổi thực tế)</h5>

                        <div class="mb-3">
                            <label for="gsheet1" class="form-label">URL Google Sheet (CSV)</label>
                            <input type="text" class="form-control" id="gsheet1" name="gsheet1" 
                                value="<?= isset($settings['gsheet1']) ? $settings['gsheet1'] : '' ?>"
                                placeholder="https://docs.google.com/spreadsheets/d/.../export?format=csv">
                        </div>
                        <div class="mb-3">
                            <label for="gsheet2" class="form-label">URL Google Sheet 2 (CSV)</label>
                            <small class="form-text text-muted">
                                <i> - Đảm bảo thứ tự các cột giống nhau giữa 2 sheet</i>
                            </small>
                            <input type="text" class="form-control" id="gsheet2" name="gsheet2" 
                                value="<?= isset($settings['gsheet2']) ? $settings['gsheet2'] : '' ?>"
                                placeholder="https://docs.google.com/spreadsheets/d/.../export?format=csv">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gsheet_date_col" class="form-label">Cột ngày chuyển đổi</label>
                                    <input type="text" class="form-control" id="gsheet_date_col" name="gsheet_date_col" 
                                        value="<?= isset($settings['gsheet_date_col']) ? $settings['gsheet_date_col'] : 'A' ?>"
                                        placeholder="Ví dụ: A">
                                    <div class="form-text">Nhập chữ cái của cột (A, B, C,...)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gsheet_phone_col" class="form-label">Cột số điện thoại</label>
                                    <input type="text" class="form-control" id="gsheet_phone_col" name="gsheet_phone_col" 
                                        value="<?= isset($settings['gsheet_phone_col']) ? $settings['gsheet_phone_col'] : 'C' ?>"
                                        placeholder="Ví dụ: C">
                                    <div class="form-text">Nhập chữ cái của cột (A, B, C,...)</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gsheet_value_col" class="form-label">Cột giá trị chuyển đổi</label>
                                    <input type="text" class="form-control" id="gsheet_value_col" name="gsheet_value_col" 
                                        value="<?= isset($settings['gsheet_value_col']) ? $settings['gsheet_value_col'] : 'F' ?>"
                                        placeholder="Ví dụ: F">
                                    <div class="form-text">Nhập chữ cái của cột (A, B, C,...)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gsheet_campaign_col" class="form-label">Cột Campaign ID</label>
                                    <input type="text" class="form-control" id="gsheet_campaign_col" name="gsheet_campaign_col" 
                                        value="<?= isset($settings['gsheet_campaign_col']) ? $settings['gsheet_campaign_col'] : 'L' ?>"
                                        placeholder="Ví dụ: L">
                                    <div class="form-text">Nhập chữ cái của cột (A, B, C,...)</div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            auto_optimize: $('#auto_optimize').is(':checked'),
            cpa_threshold: $('#cpa_threshold').val(),
            roas_threshold: $('#roas_threshold').val(),
            increase_budget: $('#increase_budget').val(),
            gsheet1: $('#gsheet1').val(),
            gsheet_date_col: $('#gsheet_date_col').val().toUpperCase(),
            gsheet_phone_col: $('#gsheet_phone_col').val().toUpperCase(),
            gsheet_value_col: $('#gsheet_value_col').val().toUpperCase(),
            gsheet_campaign_col: $('#gsheet_campaign_col').val().toUpperCase(),
            gsheet2: $('#gsheet2').val()
        };
        
        $.ajax({
            url: '<?= base_url('adsaccounts/settings/update/' . $account['id']) ?>',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Cài đặt đã được lưu thành công!');
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi lưu cài đặt');
            }
        });
    });
});
</script>

<?= $this->include('templates/footer') ?> 