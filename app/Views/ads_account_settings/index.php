<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Cài đặt tài khoản - <?= esc($account['customer_name']) ?></h2>
                </div>
                <div class="card-body">
                    <div class="text-end mb-3">
                        <select class="form-select" id="accountSelector" style="width: 300px;" onchange="window.location.href=this.value">
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= base_url('adsaccounts/settings/' . $acc['customer_id']) ?>" <?= $acc['id'] == $account['id'] ? 'selected' : '' ?>>
                                    <?= esc($acc['customer_name']) ?> - <?= esc($acc['customer_id']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <a href="<?= base_url('campaigns/index/' . $account['customer_id']) ?>" class="btn btn-sm btn-info">
                        View Campaigns
                    </a>
                    <?php if (session()->has('error')): ?>
                        <div class="alert alert-danger">
                            <?= session('error') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Cài đặt tối ưu hóa tự động</h5>
                </div>
                <div class="card-body">
                    <form id="settingsForm" method="post">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_optimize" name="auto_optimize" 
                                    <?= isset($settings['auto_optimize']) && $settings['auto_optimize'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="auto_optimize">Tự động tối ưu</label>
                            </div>
                            <small class="form-text text-muted">
                                <i>Khi bật, hệ thống sẽ tự động kiểm tra và tối ưu chiến dịch</i>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="cost_threshold" class="form-label">Ngưỡng chi tiêu</label>
                            <input type="number" step="0.01" class="form-control" id="cost_threshold" name="cost_threshold" 
                                value="<?= isset($settings['cost_threshold']) ? $settings['cost_threshold'] : '' ?>">
                            <small class="form-text text-muted">
                                <i>Khi chi tiêu vượt quá ngưỡng này, chiến dịch sẽ check các điều kiện để Tắt hoặc Tăng ngân sách</i>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="increase_budget" class="form-label">Tăng ngân sách</label>
                            <input type="number" step="0.01" class="form-control" id="increase_budget" name="increase_budget" 
                                value="<?= isset($settings['increase_budget']) ? $settings['increase_budget'] : '' ?>">
                            <small class="form-text text-muted">
                                <i>
                                    Số tiền tăng thêm khi chiến dịch đã chi tiêu > 50% ngân sách
                                    <br>
                                    Nếu không muốn tăng ngân sách thì để 0
                                    <br>
                                    (Nếu chiến dịch thoả mãn điều kiện ngưỡng ROAS/CPA bên dưới thì mới tăng NS)
                                </i>
                            </small>
                        </div>

                        <div class="mb-3 mt-5">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_on_off" name="auto_on_off" 
                                    <?= isset($settings['auto_on_off']) && $settings['auto_on_off'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="auto_on_off">Tự động tắt chiến dịch</label>
                            </div>
                            <small class="form-text text-muted">
                                <i>Khi bật, hệ thống sẽ tự động tắt chiến dịch dựa trên ngưỡng chi tiêu và CPA</i>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="cpa_threshold" class="form-label">Ngưỡng CPA</label>
                            <input type="number" step="0.01" class="form-control" id="cpa_threshold" name="cpa_threshold" 
                                value="<?= isset($settings['cpa_threshold']) ? $settings['cpa_threshold'] : '' ?>">
                            <small class="form-text text-muted">
                                <i>Chiến dịch sẽ bị tạm dừng nếu CPA vượt quá ngưỡng này</i>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="extended_cpa_threshold" class="form-label">Ngưỡng CPA giữa 2 lần chuyển đổi</label>
                            <input type="number" step="0.01" class="form-control" id="extended_cpa_threshold" name="extended_cpa_threshold" 
                                value="<?= isset($settings['extended_cpa_threshold']) ? $settings['extended_cpa_threshold'] : '' ?>">
                            <small class="form-text text-muted">
                                <i>Nếu chi tiêu thêm hoặc CPA từ lần ra đơn gần nhất > ngưỡng này thì tạm dừng chiến dịch 
                                    <br>Nếu để = 0 thì sẽ check theo CPA trung bình thực tế</strong>
                            </small>
                        </div>

                        <div class="mb-3 mt-5">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="use_roas_threshold" name="use_roas_threshold" 
                                    <?= isset($settings['use_roas_threshold']) && $settings['use_roas_threshold'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="use_roas_threshold">Tắt theo ROAS</label>
                            </div>
                            <small class="form-text text-muted">
                                <i>Khi bật, hệ thống sẽ tự động bật/tắt chiến dịch dựa trên ROAS thay vì CPA</i>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="roas_threshold" class="form-label">Ngưỡng ROAS</label>
                            <input type="number" step="0.01" class="form-control" id="roas_threshold" name="roas_threshold"
                                value="<?= isset($settings['roas_threshold']) ? $settings['roas_threshold'] : '' ?>">
                            <small class="form-text text-muted">
                                <i>Nhập ngưỡng ROAS để tự động tắt chiến dịch khi ROAS thực tế thấp hơn ngưỡng này</i>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="exclude_campaign_ids" class="form-label">Loại trừ chiến dịch (ID chiến dịch)</label>
                            <input type="text" class="form-control" id="exclude_campaign_ids" name="exclude_campaign_ids"
                                value="<?= isset($settings['exclude_campaign_ids']) ? $settings['exclude_campaign_ids'] : '' ?>">
                            <small class="form-text text-muted">
                                <i>Phần tự động tối ưu sẽ <strong>bỏ qua</strong> các chiến dịch này khi chạy tự động. Điền <strong>ID chiến dịch</strong>, phân cách bằng dấu ","</i>
                            </small>
                        </div>
                        <hr class="my-4">
                        <h5 class="card-title mb-4 mt-4">Cài đặt nguồn dữ liệu chuyển đổi thực tế</h5>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="use_pancake" name="use_pancake" 
                                    <?= isset($settings['use_pancake']) && $settings['use_pancake'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="use_pancake">Sử dụng Pancake POS</label>
                            </div>
                            <small class="form-text text-muted">
                                <i>Khi bật, hệ thống sẽ lấy dữ liệu chuyển đổi từ Pancake POS thay vì Google Sheet</i>
                            </small>
                        </div>
                        
                        <div id="pancake_settings" class="mb-4" style="display: <?= isset($settings['use_pancake']) && $settings['use_pancake'] ? 'block' : 'none' ?>">
                            <div class="mb-3">
                                <label for="pancake_shop_id" class="form-label">Shop ID</label>
                                <input type="text" class="form-control" id="pancake_shop_id" name="pancake_shop_id" 
                                    value="<?= isset($settings['pancake_shop_id']) ? $settings['pancake_shop_id'] : '' ?>">
                                <small class="form-text text-muted">
                                    <i>ID của cửa hàng trên Pancake POS</i>
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="pancake_api_key" class="form-label">API Key</label>
                                <input type="text" class="form-control" id="pancake_api_key" name="pancake_api_key" 
                                    value="<?= isset($settings['pancake_api_key']) ? $settings['pancake_api_key'] : '' ?>">
                                <small class="form-text text-muted">
                                    <i>API Key để truy cập Pancake POS API</i>
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="pancake_product_id" class="form-label">Mã sản phẩm (product_display_id)</label>
                                <input type="text" class="form-control" id="pancake_product_id" name="pancake_product_id" 
                                    value="<?= isset($settings['pancake_product_id']) ? $settings['pancake_product_id'] : '' ?>">
                                <small class="form-text text-muted">
                                    <i>Mã sản phẩm để mapping đơn hàng với chiến dịch quảng cáo</i>
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label style="display: block;" for="pancake_exclude_tags" class="form-label">Loại trừ thẻ đơn hàng</label>
                                <div class="input-group mb-3" style="display: none;" >
                                    <input type="text" class="form-control" id="pancake_exclude_tags" name="pancake_exclude_tags" 

                                        value="<?= isset($settings['pancake_exclude_tags']) ? $settings['pancake_exclude_tags'] : '' ?>" readonly>

                                    <!-- Thêm hidden input để đảm bảo giá trị được gửi đi -->
                                    <input type="hidden" id="pancake_exclude_tags_hidden" name="pancake_exclude_tags_hidden" 
                                        value="<?= isset($settings['pancake_exclude_tags']) ? $settings['pancake_exclude_tags'] : '' ?>">
                                    <button class="btn btn-outline-secondary" type="button" id="load_tags_button">Load các thẻ đơn hàng</button>
                                </div>
                                <small class="form-text text-muted">
                                    <i>Các thẻ đơn hàng cần loại trừ khi tính toán chuyển đổi thực tế</i><br/>
                                    <i>(Thẻ đánh dấu đơn hàng chốt KO thành công)</i>
                                </small>
                                <div id="tags_container" class="mt-2" style="display: none;">
                                    <div class="card">
                                        <div class="card-header">Danh sách thẻ</div>
                                        <div class="card-body">
                                            <div id="tags_list" class="d-flex flex-wrap gap-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="pancake_use_usd" name="pancake_use_usd" 
                                        <?= isset($settings['pancake_use_usd']) && $settings['pancake_use_usd'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="pancake_use_usd">Tính toán bằng USD</label>
                                </div>
                                <small class="form-text text-muted">
                                    <i>Khi bật, giá trị đơn hàng sẽ được quy đổi từ VND sang USD theo tỷ giá</i><br>
                                    <i>Dùng trong trường hợp tk ads là USD</i>
                                </small>
                            </div>
                            
                            <div class="mb-3" id="pancake_usd_rate_container" style="display: <?= isset($settings['pancake_use_usd']) && $settings['pancake_use_usd'] ? 'block' : 'none' ?>">
                                <label for="pancake_usd_rate" class="form-label">Tỷ giá USD/VND</label>
                                <input type="number" step="1" class="form-control" id="pancake_usd_rate" name="pancake_usd_rate" 
                                    value="<?= isset($settings['pancake_usd_rate']) ? $settings['pancake_usd_rate'] : '27000' ?>">
                                <small class="form-text text-muted">
                                    <i>Tỷ giá quy đổi từ VND sang USD (VD: 27000 VND = 1 USD)</i>
                                </small>
                            </div>
                        </div>
                        
                        <div id="gsheet_settings" class="mb-4" style="display: <?= isset($settings['use_pancake']) && $settings['use_pancake'] ? 'none' : 'block' ?>">
                            <h6 class="mb-3">Cài đặt Google Sheet</h6>
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
                        </div>

                        <hr class="my-4">
                        <h5 class="card-title mb-4 mt-4">Cài đặt tài khoản ads</h5>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="default_paused_campaigns" name="default_paused_campaigns" 
                                    <?= isset($settings['default_paused_campaigns']) && $settings['default_paused_campaigns'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="default_paused_campaigns">Default load chiến dịch đã tạm dừng</label>
                            </div>
                            <small class="form-text text-muted">
                                <i>Khi bật, ở trang dánh sách chiến dịch của tài khoản, hệ thống sẽ tự động load các chiến dịch đã tạm dừng</i>
                            </small>
                        </div>
                        <div class="mb-3">
                            <label for="order" class="form-label">Thứ tự sắp xếp</label>
                            <input type="number" class="form-control" id="order" name="order" 
                                value="<?= isset($account['order']) ? $account['order'] : '' ?>">
                            <small class="form-text text-muted">
                                Số thứ tự sắp xếp tài khoản
                            </small>
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
    // Xử lý sự kiện khi checkbox use_pancake thay đổi
    <?php if(isset($settings['pancake_shop_id']) && $settings['pancake_api_key']): ?>
        loadTags();
    <?php endif;?>
    
    // Xử lý sự kiện khi checkbox pancake_use_usd thay đổi
    $('#pancake_use_usd').change(function() {
        var isChecked = $(this).is(':checked');
        console.log('pancake_use_usd changed:', isChecked);
        if (isChecked) {
            $('#pancake_usd_rate_container').show();
        } else {
            $('#pancake_usd_rate_container').hide();
        }
    });
    
    $('#use_pancake').change(function() {
        var isChecked = $(this).is(':checked');
        console.log('use_pancake changed:', isChecked);
        
        // Hiển thị hoặc ẩn các trường Pancake dựa trên trạng thái checkbox
        if (isChecked) {
            $('#gsheet_settings').hide();
            $('#pancake_settings').show();
            
            // Nếu đã có danh sách thẻ, cập nhật giá trị
            if ($('#tags_list').children().length > 0) {
                // Đảm bảo các checkbox được gắn sự kiện change
                $('.tag-checkbox').off('change').on('change', function() {
                    console.log('Tag checkbox changed');
                    updateSelectedTags();
                });
                
                // Cập nhật giá trị
                updateSelectedTags();
                
                console.log('Tags updated after use_pancake checked');
                console.log('pancake_exclude_tags:', $('#pancake_exclude_tags').val());
                console.log('pancake_exclude_tags_hidden:', $('#pancake_exclude_tags_hidden').val());
            } else {
                console.log('No tags loaded yet, consider loading tags');
            }
        } else {
            $('#gsheet_settings').show();
            $('#pancake_settings').hide();
            // Xóa giá trị khi không sử dụng Pancake
            $('#pancake_exclude_tags').val('');
            $('#pancake_exclude_tags_hidden').val('');
            console.log('use_pancake unchecked, cleared tag values');
        }
        
        console.log('use_pancake changed to:', isChecked);
    });
    
    // Xử lý nút load thẻ đơn hàng
    $('#load_tags_button').click(function() {
        loadTags();
    });

    function loadTags() {
        var shopId = $('#pancake_shop_id').val();
        var apiKey = $('#pancake_api_key').val();
        
        if (!shopId || !apiKey) {
            alert('Vui lòng nhập Shop ID và API Key trước khi tải thẻ đơn hàng');
            return;
        }
        
        // Hiển thị thông báo đang tải
        $('#tags_list').html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div>');
        $('#tags_container').show();
        
        // Gọi API để lấy danh sách đơn hàng và trích xuất thẻ
        $.ajax({
            url: '<?= base_url('adsaccounts/settings/get_pancake_tags') ?>',
            method: 'POST',
            data: {
                shop_id: shopId,
                api_key: apiKey
            },
            success: function(response) {
                if (response.success) {
                    console.log('Tags loaded successfully:', response.tags);
                    displayTags(response.tags);
                    
                    // Đảm bảo các sự kiện được gắn kết đúng cách
                    setTimeout(function() {
                        // Gắn sự kiện cho các checkbox
                        $('.tag-checkbox').on('change', function() {
                            console.log('Tag checkbox changed');
                            updateSelectedTags();
                        });
                        
                        // Gọi updateSelectedTags để cập nhật giá trị ban đầu
                        updateSelectedTags();
                    }, 100);
                } else {
                    $('#tags_list').html('<div class="alert alert-danger">Lỗi: ' + response.message + '</div>');
                }
            },
            error: function() {
                $('#tags_list').html('<div class="alert alert-danger">Có lỗi xảy ra khi tải thẻ đơn hàng</div>');
            }
        });
    }
    
    // Hiển thị danh sách thẻ và cho phép chọn
    function displayTags(tags) {
        if (!tags || tags.length === 0) {
            $('#tags_list').html('<div class="alert alert-info">Không tìm thấy thẻ nào</div>');
            return;
        }
        
        var tagsHtml = '';
        // Lấy giá trị hiện tại của trường pancake_exclude_tags
        var currentTagsValue = $('#pancake_exclude_tags').val() || '';
        console.log('Current pancake_exclude_tags value:', currentTagsValue);
        
        // Đảm bảo giá trị không rỗng trước khi split
        var currentTags = currentTagsValue ? currentTagsValue.split(',').map(function(tag) {
            return tag.trim();
        }).filter(function(tag) {
            return tag !== '';
        }) : [];
        
        console.log('Parsed current tags:', currentTags);
        
        // Tạo các checkbox cho từng thẻ
        tags.forEach(function(tag) {
            // Kiểm tra xem tag.id có trong danh sách currentTags không
            var tagIdStr = tag.id.toString();
            var isChecked = currentTags.includes(tagIdStr);
            console.log('Tag ID:', tagIdStr, 'Is checked:', isChecked);
            
            var checkedAttr = isChecked ? 'checked="checked"' : '';
            tagsHtml += '<div class="form-check form-check-inline">' +
                        '<input class="form-check-input tag-checkbox" type="checkbox" id="tag_' + tag.id + '" ' +
                        'value="' + tag.id + '" ' + checkedAttr + '>' +
                        '<label class="form-check-label" for="tag_' + tag.id + '">' + tag.name + '</label>' +
                        '</div>';
        });
        
        $('#tags_list').html(tagsHtml);
        
        // Xử lý sự kiện khi chọn/bỏ chọn thẻ
        $('.tag-checkbox').change(function() {
            updateSelectedTags();
        });
        
        // Đảm bảo giá trị pancake_exclude_tags được cập nhật sau khi hiển thị các thẻ
        // Điều này giúp đồng bộ giá trị của trường input với các checkbox đã chọn
        setTimeout(function() {
            updateSelectedTags();
            console.log('Tags displayed and pancake_exclude_tags updated');
        }, 100);
    }
    
    // Cập nhật danh sách thẻ đã chọn vào input
    function updateSelectedTags() {
        var selectedTags = [];
        
        // Kiểm tra xem có checkbox nào không
        var checkboxes = $('.tag-checkbox:checked');
        console.log('Number of checked checkboxes:', checkboxes.length);
        
        // Thu thập giá trị từ các checkbox đã chọn
        checkboxes.each(function() {
            var tagValue = $(this).val();
            console.log('Adding checked tag:', tagValue);
            selectedTags.push(tagValue);
        });
        
        var tagsValue = selectedTags.join(',');
        
        // Cập nhật cả input thông thường và hidden input
        $('#pancake_exclude_tags').val(tagsValue);
        $('#pancake_exclude_tags_hidden').val(tagsValue);
        
        // Đảm bảo giá trị được cập nhật trong DOM
        document.getElementById('pancake_exclude_tags').setAttribute('value', tagsValue);
        document.getElementById('pancake_exclude_tags_hidden').setAttribute('value', tagsValue);
        
        console.log('updateSelectedTags called, new value:', tagsValue);
        console.log('DOM input value after update:', document.getElementById('pancake_exclude_tags').value);
        console.log('DOM hidden input value after update:', document.getElementById('pancake_exclude_tags_hidden').value);
        console.log('DOM input attribute value after update:', $('#pancake_exclude_tags').attr('value'));
        
        return tagsValue; // Trả về giá trị để có thể sử dụng ở nơi khác
    }
    
    // Xử lý sự kiện khi form được submit
    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submitted');
        
        // Cập nhật giá trị pancake_exclude_tags trước khi thu thập dữ liệu form
        if ($('#use_pancake').is(':checked')) {
            // Cập nhật lại giá trị từ các checkbox đã chọn
            var selectedTags = [];
            var checkedBoxes = $('.tag-checkbox:checked');
            
            console.log('Number of checked tag checkboxes:', checkedBoxes.length);
            
            checkedBoxes.each(function() {
                var tagValue = $(this).val();
                console.log('Adding checked tag to selection:', tagValue);
                selectedTags.push(tagValue);
            });
            
            var tagsValue = selectedTags.join(',');
            console.log('Setting pancake_exclude_tags to:', tagsValue);
            
            // Cập nhật cả hai trường input
            $('#pancake_exclude_tags').val(tagsValue);
            $('#pancake_exclude_tags_hidden').val(tagsValue);
            
            // Cập nhật thuộc tính value
            $('#pancake_exclude_tags').attr('value', tagsValue);
            $('#pancake_exclude_tags_hidden').attr('value', tagsValue);
        } else {
            // Xóa giá trị nếu không sử dụng Pancake
            $('#pancake_exclude_tags').val('');
            $('#pancake_exclude_tags_hidden').val('');
            console.log('use_pancake is not checked, clearing tag values');
        }
        
        // Kiểm tra giá trị sau khi cập nhật
        console.log('pancake_exclude_tags value before form data collection:', $('#pancake_exclude_tags').val());
        console.log('pancake_exclude_tags_hidden value before form data collection:', $('#pancake_exclude_tags_hidden').val());
        
        // Tạo một object mới để gửi dữ liệu
        var formData = {};
        
        // Thu thập tất cả các trường form
        $('#settingsForm').find('input, select, textarea').each(function() {
            var input = $(this);
            var name = input.attr('name');
            
            // Bỏ qua các trường không có name
            if (!name) return;
            
            // Xử lý checkbox
            if (input.attr('type') === 'checkbox') {
                formData[name] = input.is(':checked') ? 'true' : 'false';
            } 
            // Xử lý các trường khác
            else {
                formData[name] = input.val();
                console.log('Field:', name, 'Value:', input.val());
            }
        });
        
        // Thêm account_id vào formData
        formData.account_id = '<?= $account['id'] ?>';
        
        // Cập nhật lại giá trị của pancake_exclude_tags trước khi gửi
        var excludeTags = '';
        if ($('#use_pancake').is(':checked')) {
            // Cập nhật lại giá trị từ các checkbox đã chọn
            var checkedBoxes = $('.tag-checkbox:checked');
            console.log('Number of checked tag checkboxes:', checkedBoxes.length);
            
            var selectedTags = [];
            checkedBoxes.each(function() {
                var tagValue = $(this).val();
                console.log('Adding checked tag to selection:', tagValue);
                selectedTags.push(tagValue);
            });
            
            excludeTags = selectedTags.join(',');
            console.log('Final pancake_exclude_tags value:', excludeTags);
            
            // Cập nhật giá trị vào cả hai input
            $('#pancake_exclude_tags').val(excludeTags);
            $('#pancake_exclude_tags_hidden').val(excludeTags);
            
            // Cập nhật thuộc tính value
            $('#pancake_exclude_tags').attr('value', excludeTags);
            $('#pancake_exclude_tags_hidden').attr('value', excludeTags);
        }
        
        // Kiểm tra giá trị trong DOM sau khi cập nhật
        console.log('DOM value of pancake_exclude_tags:', $('#pancake_exclude_tags').val());
        console.log('DOM attribute value of pancake_exclude_tags:', $('#pancake_exclude_tags').attr('value'));
        console.log('DOM property value of pancake_exclude_tags:', document.getElementById('pancake_exclude_tags').value);
        
        // Tạo một object mới để gửi dữ liệu
        var dataToSend = {
            // Đảm bảo các trường checkbox được gửi đi đúng cách
            'auto_optimize': $('#auto_optimize').is(':checked') ? 'true' : 'false',
            'auto_on_off': $('#auto_on_off').is(':checked') ? 'true' : 'false',
            'use_roas_threshold': $('#use_roas_threshold').is(':checked') ? 'true' : 'false',
            'default_paused_campaigns': $('#default_paused_campaigns').is(':checked') ? 'true' : 'false',
            'use_pancake': $('#use_pancake').is(':checked') ? 'true' : 'false',
            
            // Đảm bảo trường account_id được gửi đi
            'account_id': '<?= $account['id'] ?>',
            
            // Thêm các trường khác từ form
            'cost_threshold': $('#cost_threshold').val() || '0',
            'cpa_threshold': $('#cpa_threshold').val() || '0',
            'roas_threshold': $('#roas_threshold').val() || '0',
            'extended_cpa_threshold': $('#extended_cpa_threshold').val() || '0',
            'increase_budget': $('#increase_budget').val() || '0',
            'gsheet1': $('#gsheet1').val(),
            'gsheet2': $('#gsheet2').val(),
            'gsheet_date_col': $('#gsheet_date_col').val().toUpperCase(),
            'gsheet_phone_col': $('#gsheet_phone_col').val().toUpperCase(),
            'gsheet_value_col': $('#gsheet_value_col').val().toUpperCase(),
            'gsheet_campaign_col': $('#gsheet_campaign_col').val().toUpperCase(),
            'order': $('#order').val() || '0',
            'exclude_campaign_ids': $('#exclude_campaign_ids').val(),
            'pancake_shop_id': $('#pancake_shop_id').val(),
            'pancake_api_key': $('#pancake_api_key').val(),
            'pancake_product_id': $('#pancake_product_id').val(),
            'pancake_use_usd': $('#pancake_use_usd').is(':checked') ? 'true' : 'false',
            'pancake_usd_rate': $('#pancake_usd_rate').val() || '27000'
        };
        
        // Thêm trường pancake_exclude_tags nếu use_pancake được chọn
        if ($('#use_pancake').is(':checked')) {
            dataToSend.pancake_exclude_tags = excludeTags;
        }
        
        // Log dữ liệu để kiểm tra
        console.log('Data to send:', dataToSend);
        
        // Kiểm tra lại giá trị pancake_exclude_tags trước khi gửi
        console.log('Final check before sending:');
        console.log('- use_pancake:', dataToSend.use_pancake);
        console.log('- pancake_exclude_tags:', dataToSend.pancake_exclude_tags);
        console.log('- account_id:', dataToSend.account_id);
        
        // Gửi form bằng AJAX
        $.ajax({
            url: '<?= base_url('adsaccounts/settings/update/' . $account['customer_id']) ?>',
            method: 'POST',
            data: dataToSend, // Sử dụng object JavaScript
            dataType: 'json',
            beforeSend: function(xhr) {
                console.log('Sending request to:', this.url);
                console.log('With data:', this.data);
            },
            success: function(response) {
                console.log('Response received:', response);
                if (response.success) {
                    alert('Cài đặt đã được lưu thành công!');
                    // Thêm log để xác nhận các giá trị đã được lưu
                    console.log('Settings saved successfully. Refreshing page to verify...');
                    // Tải lại trang sau 1 giây để hiển thị các giá trị đã lưu
                    // setTimeout(function() {
                    //     location.reload();
                    // }, 1000);
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                console.log('Response text:', xhr.responseText);
                // Thử phân tích phản hồi JSON nếu có
                try {
                    var jsonResponse = JSON.parse(xhr.responseText);
                    console.log('Parsed error response:', jsonResponse);
                } catch (e) {
                    console.log('Could not parse error response as JSON');
                }
                alert('Có lỗi xảy ra khi lưu cài đặt: ' + error);
            }
        });
    });
});
</script>

<?= $this->include('templates/footer') ?>