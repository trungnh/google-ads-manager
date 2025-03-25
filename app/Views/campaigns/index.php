<?= $this->include('templates/header') ?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Chiến dịch - <?= esc($account['customer_name']) ?></h2>
                    <p>ID tài khoản: <?= esc($account['customer_id']) ?></p>
                </div>
                <div class="text-end">
                    <select class="form-select" id="accountSelector" style="width: 300px;" onchange="window.location.href=this.value">
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= base_url('campaigns/index/' . $acc['customer_id']) ?>" <?= $acc['customer_id'] == $account['customer_id'] ? 'selected' : '' ?>>
                                <?= esc($acc['customer_name']) ?> - <?= esc($acc['customer_id']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-8">
            <div class="form-check form-check-inline mb-2">
                <input class="form-check-input" type="checkbox" id="showPaused">
                <label class="form-check-label" for="showPaused">
                    Hiển thị chiến dịch đã tạm dừng
                </label>
            </div>
            <div class="d-inline-block">
                <div class="input-group">
                    <input type="text" class="form-control" id="startDate" placeholder="Từ ngày">
                    <span class="input-group-text">đến</span>
                    <input type="text" class="form-control" id="endDate" placeholder="Đến ngày">
                    <button id="loadCampaigns" class="btn btn-primary">Load chiến dịch</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-8">
            <div id="lastUpdate" class="text-muted mt-2" style="display: none;">
                Lần cập nhật gần nhất: <span id="lastUpdateTime"></span>
            </div>
            <div class="d-inline-block">
                <div class="input-group">
                    <button id="updateData" class="btn btn-warning" style="display: none;">
                        <i class="fas fa-sync-alt"></i> Cập nhật dữ liệu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="campaignsTable" class="table-responsive" style="display: none;">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Tên chiến dịch</th>
                    <th>Trạng thái</th>
                    <th>Ngân sách</th>
                    <th>Chi tiêu</th>
                    <th>CTR</th>
                    <th>Clicks</th>
                    <th>CPC</th>
                    <th>Conv</th>
                    <th>Conv value</th>
                    <th>CPA</th>
                    <th>ROAS</th>
                    <th>Conv rate</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody id="campaignsBody">
                <tr>
                    <td colspan="14" class="text-center">Đang tải dữ liệu...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
.sortable {
    cursor: pointer;
    position: relative;
    padding-right: 20px !important;
}

.sort-icon {
    position: absolute;
    right: 5px;
    display: inline-block;
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
}

.sort-asc .sort-icon {
    border-bottom: 5px solid #333;
    top: 45%;
}

.sort-desc .sort-icon {
    border-top: 5px solid #333;
    top: 45%;
}

.sortable:not(.sort-asc):not(.sort-desc) .sort-icon {
    border-bottom: 5px solid #ccc;
    top: 45%;
}

/* Datepicker styles */
.datepicker {
    z-index: 1060 !important;
}
</style>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Add Bootstrap Datepicker CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<!-- Add Bootstrap Datepicker JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.vi.min.js"></script>

<script>
$(document).ready(function() {
    let campaignsData = [];
    let currentSort = {
        column: null,
        direction: 'asc'
    };

    // Initialize datepickers
    $('#startDate, #endDate').datepicker({
        format: 'dd/mm/yyyy',
        language: 'vi',
        autoclose: true,
        todayHighlight: true
    });

    // Set default date (today)
    const today = new Date();
    $('#startDate').datepicker('setDate', today);
    $('#endDate').datepicker('setDate', today);

    function loadCampaignsData(forceUpdate = false) {
        const showPaused = $('#showPaused').is(':checked');
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        const button = forceUpdate ? $('#updateData') : $('#loadCampaigns');
        
        if (!startDate || !endDate) {
            alert('Vui lòng chọn khoảng thời gian');
            return;
        }

        button.prop('disabled', true);
        if (forceUpdate) {
            button.html('<i class="fas fa-sync-alt fa-spin"></i> Đang cập nhật...');
        } else {
            button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
        }
        
        $.ajax({
            url: '<?= base_url('campaigns/load/' . $account['customer_id']) ?>',
            method: 'GET',
            data: { 
                showPaused: showPaused,
                startDate: startDate,
                endDate: endDate,
                forceUpdate: forceUpdate
            },
            success: function(response) {
                if (response.success) {
                    campaignsData = response.campaigns;
                    renderCampaigns();
                    $('#campaignsTable').show();

                    // Show/hide update button and last update time
                    if (startDate === endDate) {
                        $('#updateData').show();
                        if (response.lastUpdateTime) {
                            $('#lastUpdateTime').text(formatDateTime(response.lastUpdateTime));
                            $('#lastUpdate').show();
                        }
                    } else {
                        $('#updateData').hide();
                        $('#lastUpdate').hide();
                    }
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi tải dữ liệu');
            },
            complete: function() {
                if (forceUpdate) {
                    button.html('<i class="fas fa-sync-alt"></i> Cập nhật dữ liệu').prop('disabled', false);
                } else {
                    button.text('Load chiến dịch').prop('disabled', false);
                }
            }
        });
    }

    $('#loadCampaigns').click(function() {
        loadCampaignsData(false);
    });

    $('#updateData').click(function() {
        loadCampaignsData(true);
    });

    // Hide update button when date range changes
    $('#startDate, #endDate').change(function() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        if (startDate === endDate) {
            $('#updateData').show();
        } else {
            $('#updateData').hide();
            $('#lastUpdate').hide();
        }
    });

    $('.sortable').click(function() {
        const column = $(this).data('sort');
        
        // Reset other columns
        $('.sortable').not(this).removeClass('sort-asc sort-desc');
        
        // Toggle sort direction
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            $(this).toggleClass('sort-asc sort-desc');
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
            $(this).removeClass('sort-desc').addClass('sort-asc');
        }
        
        renderCampaigns();
    });

    function renderCampaigns() {
        let sortedCampaigns = [...campaignsData];
        
        // Tính toán total
        const totals = {
            budget: 0,
            cost: 0,
            clicks: 0,
            real_conversions: 0,
            real_conversion_value: 0
        };

        sortedCampaigns.forEach(campaign => {
            totals.budget += parseFloat(campaign.budget) || 0;
            totals.cost += parseFloat(campaign.cost) || 0;
            totals.clicks += parseInt(campaign.clicks) || 0;
            totals.real_conversions += parseFloat(campaign.real_conversions) || 0;
            totals.real_conversion_value += parseFloat(campaign.real_conversion_value) || 0;
        });

        // Tính các chỉ số tổng hợp
        const totalCTR = totals.clicks > 0 ? (totals.clicks / totals.clicks) * 100 : 0;
        const totalAverageCPC = totals.clicks > 0 ? totals.cost / totals.clicks : 0;
        const totalRealCPA = totals.real_conversions > 0 ? totals.cost / totals.real_conversions : 0;
        const totalRealConversionRate = totals.clicks > 0 ? (totals.real_conversions / totals.clicks) * 100 : 0;
        const totalROAS = totals.cost > 0 ? totals.real_conversion_value / totals.cost : 0;

        let html = '';
        
        // Render các chiến dịch
        sortedCampaigns.forEach(campaign => {
            html += `
                <tr>
                    <td>${campaign.campaign_id}</td>
                    <td>${campaign.name}</td>
                    <td>
                        <span class="badge ${campaign.status === 'ENABLED' ? 'bg-success' : 'bg-warning'}">
                            ${campaign.status === 'ENABLED' ? 'Đang chạy' : 'Tạm dừng'}
                        </span>
                    </td>
                    <td>${formatNumber(campaign.budget)}</td>
                    <td>${formatNumber(campaign.cost)}</td>
                    <td>${formatPercent(campaign.ctr)}</td>
                    <td>${formatNumberWithoutCurrency(campaign.clicks)}</td>
                    <td>${formatNumber(campaign.average_cpc)}</td>
                    <td>${campaign.real_conversions ? formatNumberWithoutCurrency(campaign.real_conversions) : '-'}</td>
                    <td>${campaign.real_conversion_value ? formatNumber(campaign.real_conversion_value) : '-'}</td>
                    <td>${campaign.real_cpa ? formatNumber(campaign.real_cpa) : '-'}</td>
                    <td>${campaign.cost > 0 ? formatNumberWithoutCurrency(campaign.real_conversion_value / campaign.cost) : '-'}</td>
                    <td>${campaign.real_conversion_rate ? formatPercent(campaign.real_conversion_rate) : '-'}</td>
                    <td>
                        <button class="btn ${campaign.status === 'ENABLED' ? 'btn-danger' : 'btn-success'} btn-sm toggle-status"
                                data-customer-id="<?= $account['customer_id'] ?>"
                                data-campaign-id="${campaign.campaign_id}"
                                data-status="${campaign.status}">
                            <i class="fas fa-power-off"></i>
                            ${campaign.status === 'ENABLED' ? 'Tắt' : 'Bật'}
                        </button>
                    </td>
                </tr>
            `;
        });

        // Thêm dòng total
        html += `
            <tr class="table-primary fw-bold">
                <td colspan="2">Tổng cộng</td>
                <td>-</td>
                <td>${formatNumber(totals.budget)}</td>
                <td>${formatNumber(totals.cost)}</td>
                <td>${formatPercent(totalCTR)}</td>
                <td>${formatNumberWithoutCurrency(totals.clicks)}</td>
                <td>${formatNumber(totalAverageCPC)}</td>
                <td>${formatNumberWithoutCurrency(totals.real_conversions)}</td>
                <td>${formatNumber(totals.real_conversion_value)}</td>
                <td>${formatNumber(totalRealCPA)}</td>
                <td>${formatNumberWithoutCurrency(totalROAS)}</td>
                <td>${formatPercent(totalRealConversionRate)}</td>
                <td>-</td>
            </tr>
        `;
        
        $('#campaignsBody').html(html);
        initializeToggleButtons();
    }

    // Di chuyển event handler ra function riêng
    function initializeToggleButtons() {
        $('.toggle-status').off('click').on('click', function(e) {
            e.preventDefault();
            
            const btn = $(this);
            const customerId = btn.data('customer-id');
            const campaignId = btn.data('campaign-id');
            const currentStatus = btn.data('status');
            const newStatus = currentStatus === 'ENABLED' ? 'PAUSED' : 'ENABLED';
            
            // Disable button while processing
            btn.prop('disabled', true);
            
            $.ajax({
                url: '<?= base_url('campaigns/toggleStatus') ?>/' + customerId + '/' + campaignId,
                method: 'POST',
                data: { status: newStatus },
                success: function(response) {
                    if (response.success) {
                        // Update button appearance
                        btn.data('status', response.newStatus);
                        if (response.newStatus === 'ENABLED') {
                            btn.removeClass('btn-success').addClass('btn-danger');
                            btn.html('<i class="fas fa-power-off"></i> Tắt');
                        } else {
                            btn.removeClass('btn-danger').addClass('btn-success');
                            btn.html('<i class="fas fa-power-off"></i> Bật');
                        }
                        toastr.success(response.message);
                        
                        // Reload campaigns data after successful toggle
                        loadCampaignsData(true);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('Có lỗi xảy ra khi cập nhật trạng thái chiến dịch');
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        });
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('vi-VN', { 
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
            style: 'currency',
            currency: 'VND'
        }).format(number);
    }

    function formatNumberWithoutCurrency(number) {
        return new Intl.NumberFormat('vi-VN', { 
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    }

    function formatPercent(number) {
        return new Intl.NumberFormat('vi-VN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(number * 100) + '%';
    }

    function formatDateTime(dateTimeStr) {
        const date = new Date(dateTimeStr);
        return date.toLocaleString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    // Trigger initial load
    $('#loadCampaigns').click();
});
</script>

<?= $this->include('templates/footer') ?> 