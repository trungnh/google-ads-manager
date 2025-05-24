<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Chiến dịch - <?= esc($account['customer_name']) ?></h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div>
                                <p>ID tài khoản: <?= esc($account['customer_id']) ?></p>
                                <a href="<?= base_url('adsaccounts/settings/admin_view/' . $account['customer_id']) ?>" class="btn btn-sm btn-primary mb-2 mb-md-0">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="text-end">
                                <select class="form-select" id="accountSelector" style="width: 300px;" onchange="window.location.href=this.value">
                                    <?php foreach ($accounts as $acc): ?>
                                        <option value="<?= base_url('campaigns/admin_view/' . $acc['customer_id']) ?>" <?= $acc['customer_id'] == $account['customer_id'] ? 'selected' : '' ?>>
                                        [<?= esc($acc['username']) ?>] - <?= esc($acc['customer_name']) ?> - <?= esc($acc['customer_id']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="form-check form-check-inline mb-2">
                                <input class="form-check-input" type="checkbox" id="showPaused" <?php echo (isset($accountSettings['default_paused_campaigns']) && $accountSettings['default_paused_campaigns'] == 1) ? 'checked' : '';?>>
                                <label class="form-check-label" for="showPaused">
                                    Hiển thị chiến dịch đã tạm dừng
                                </label>
                            </div>
                            <div class="form-check form-check-inline mb-2">
                                <input class="form-check-input" type="checkbox" id="showPausedAndCost">
                                <label class="form-check-label" for="showPausedAndCost">
                                    Hiển thị chiến dịch không chi tiêu
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-8">
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
                </div>
                <div class="card-body">
                    <div id="campaignsTable" class="table-responsive" style="display: none;">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="" data-sort="campaign_id">ID</th>
                                    <th class="sortable" data-sort="name">Tên chiến dịch</th>
                                    <th class="sortable" data-sort="budget">Ngân sách</th>
                                    <th class="" data-sort="status">Trạng thái</th>
                                    <th class="sortable" data-sort="cost">Chi tiêu</th>
                                    <th class="sortable" data-sort="roas">ROAS</th>
                                    <th class="sortable" data-sort="cost_per_conversion">CPA</th>
                                    <th class="sortable" data-sort="conversions">Conv</th>
                                    <th class="sortable" data-sort="ctr">CTR</th>
                                    <th class="sortable" data-sort="clicks">Clicks</th>
                                    <th class="sortable" data-sort="average_cpc">CPC</th>
                                    <th class="sortable" data-sort="conversion_value">Conv value</th>
                                    <th class="sortable" data-sort="conversion_rate">Conv rate</th>
                                    <?php if (in_array(session()->get('role'), ['superadmin', 'admin'])): ?>
                                    <th class="sortable" data-toggle="tooltip" data-placement="top" title="Chi tiêu thêm từ lần ra đơn gần nhất">CFLC</th>
                                    <?php endif;?>
                                    <th class="" data-sort="bidding_strategy">Chiến lược</th>
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
            </div>
        </div>
    </div>
</div>

<style>
.sortable {
    cursor: pointer;
    position: relative;
    padding-right: 20px !important;
}

.sortable:after {
    content: '↕';
    position: absolute;
    right: 5px;
    opacity: 0.3;
}

.sortable.sort-asc:after {
    content: '↑';
    opacity: 1;
}

.sortable.sort-desc:after {
    content: '↓';
    opacity: 1;
}

.sortable:hover:after {
    opacity: 1;
}

/* Datepicker styles */
.datepicker {
    z-index: 1060 !important;
}

/* Editable target styles */
.editable-target {
    cursor: pointer;
    padding: 2px 5px;
    border-radius: 3px;
    background-color: #f8f9fa;
    border: 1px solid transparent;
}

.editable-target:hover {
    border-color: #ddd;
    background-color: #fff;
}

.editable-target.editing {
    background-color: #fff;
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.target-btn-group {
    margin-left: 5px;
    display: inline-flex;
}

.target-btn-group .btn {
    padding: 0px 5px;
    font-size: 0.7rem;
    line-height: 1.2;
    margin-left: 2px;
    border-radius: 3px;
}

.editable-budget {
    cursor: pointer;
    padding: 2px 5px;
    border-radius: 3px;
}

.editable-budget:hover {
    background-color: #f8f9fa;
}

.editable-budget input {
    width: 120px;
    padding: 2px 5px;
    border: 1px solid #ced4da;
    border-radius: 3px;
}

.button-group {
    display: inline-block;
    margin-left: 5px;
}

.button-group button {
    padding: 2px 5px;
    font-size: 12px;
    line-height: 1;
    margin: 0 1px;
}
</style>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Add Bootstrap Datepicker CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<!-- Add Bootstrap Datepicker JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.vi.min.js"></script>
<!-- Add Bootstrap Notify for notifications -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-notify/0.2.0/js/bootstrap-notify.min.js"></script>

<script>
$(function () {
    $('[data-toggle="tooltip"]').tooltip()
});
$(document).ready(function() {
    let campaignsData = [];
    let accountSettings = <?= json_encode($accountSettings) ?>;
    let mccId = '<?= $mccId ?>';
    let currentCustomerId = '<?= $account['customer_id'] ?>';
    let account = <?= json_encode($account) ?>;
    let currentSort = {
        column: 'cost',
        direction: 'desc'
    };

    // Thêm hàm number_format
    function number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + (Math.round(n * k) / k)
                    .toFixed(prec);
            };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
            .split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '')
            .length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1)
                .join('0');
        }
        return s.join(dec);
    }

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
        const showPausedAndCost = $('#showPausedAndCost').is(':checked');
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
            url: '<?= base_url('campaigns/admin_view/load/' . $account['customer_id']) ?>',
            method: 'GET',
            data: { 
                showPaused: showPaused,
                showPausedAndCost: showPausedAndCost,
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
        const totalRealConversionRate = totals.clicks > 0 ? (totals.real_conversions / totals.clicks) : 0;
        const totalROAS = totals.cost > 0 ? totals.real_conversion_value / totals.cost : 0;

        // Sắp xếp dữ liệu nếu có
        if (currentSort.column) {
            sortedCampaigns.sort((a, b) => {
                let aValue, bValue;
                
                switch(currentSort.column) {
                    case 'campaign_id':
                        aValue = a.campaign_id;
                        bValue = b.campaign_id;
                        break;
                    case 'name':
                        aValue = a.name;
                        bValue = b.name;
                        break;
                    case 'status':
                        aValue = a.status;
                        bValue = b.status;
                        break;
                    case 'budget':
                        aValue = parseFloat(a.budget) || 0;
                        bValue = parseFloat(b.budget) || 0;
                        break;
                    case 'cost':
                        aValue = parseFloat(a.cost) || 0;
                        bValue = parseFloat(b.cost) || 0;
                        break;
                    case 'ctr':
                        aValue = parseFloat(a.ctr) || 0;
                        bValue = parseFloat(b.ctr) || 0;
                        break;
                    case 'clicks':
                        aValue = parseInt(a.clicks) || 0;
                        bValue = parseInt(b.clicks) || 0;
                        break;
                    case 'average_cpc':
                        aValue = parseFloat(a.average_cpc) || 0;
                        bValue = parseFloat(b.average_cpc) || 0;
                        break;
                    case 'conversions':
                        aValue = parseFloat(a.real_conversions) || 0;
                        bValue = parseFloat(b.real_conversions) || 0;
                        break;
                    case 'conversion_value':
                        aValue = parseFloat(a.real_conversion_value) || 0;
                        bValue = parseFloat(b.real_conversion_value) || 0;
                        break;
                    case 'cost_per_conversion':
                        aValue = parseFloat(a.real_cpa) || 0;
                        bValue = parseFloat(b.real_cpa) || 0;
                        break;
                    case 'roas':
                        aValue = a.cost > 0 ? parseFloat(a.real_conversion_value / a.cost) || 0 : 0;
                        bValue = b.cost > 0 ? parseFloat(b.real_conversion_value / b.cost) || 0 : 0;
                        break;
                    case 'conversion_rate':
                        aValue = parseFloat(a.real_conversion_rate) || 0;
                        bValue = parseFloat(b.real_conversion_rate) || 0;
                        break;
                    case 'bidding_strategy':
                        aValue = a.bidding_strategy || '-';
                        bValue = b.bidding_strategy || '-';
                        break;
                    default:
                        return 0;
                }
                
                if (currentSort.direction === 'asc') {
                    return aValue > bValue ? 1 : -1;
                } else {
                    return aValue < bValue ? 1 : -1;
                }
            });
        }

        let html = '';
        
        // Render các chiến dịch
        sortedCampaigns.forEach(campaign => {
            let tmpRoas = (campaign.cost > 0) ? campaign.real_conversion_value / campaign.cost : 0;
            html += `
                <tr id="campaign-${campaign.campaign_id}">
                    <td>${campaign.campaign_id}</td>
                    <td>${campaign.name}</td>
                    <td>
                        <span class="editable-budget" 
                              data-campaign-id="${campaign.campaign_id}"
                              data-customer-id="${campaign.customer_id}"
                              data-mcc-id="${mccId}"
                              data-original="${campaign.budget}">
                            ${formatNumber(campaign.budget)}
                        </span>
                    </td>
                    <td>
                        <span class="badge ${campaign.status === 'ENABLED' ? 'bg-success' : 'bg-warning'} status-badge">
                            ${campaign.status === 'ENABLED' ? 'Đang chạy' : 'Tạm dừng'}
                        </span>
                    </td>
                    <td class="text-primary">
                        ${formatNumber(campaign.cost)}
                    </td>
                    <td>
                        <span class="fw-bold ${(tmpRoas > accountSettings.roas_threshold) ? 'text-success' : 'text-danger'}">
                            ${tmpRoas > 0 ? formatNumberWithoutCurrency2(tmpRoas) : '-'}
                        </span>
                    </td>
                    <td class="text-${(campaign.real_cpa > accountSettings.cpa_threshold) || campaign.real_cpa == 0 ? 'danger' : 'primary'}">
                        ${(campaign.real_cpa > 0) ? formatNumber(campaign.real_cpa): '-'}
                    </td>
                    <td class="text-primary">${(campaign.real_conversions > 0) ? formatNumberWithoutCurrency(campaign.real_conversions) + ' đơn' : '-'}</td>
                    <td>${formatPercent(campaign.ctr)}</td>
                    <td>${formatNumberWithoutCurrency(campaign.clicks)}</td>
                    <td>${formatNumber(campaign.average_cpc)}</td>
                    <td>${(campaign.real_conversion_value > 0) ? formatNumber(campaign.real_conversion_value): '-'}</td>
                    <td>${(campaign.real_conversion_rate > 0) ? formatPercent(campaign.real_conversion_rate) : '-'}</td>
                    <?php if (in_array(session()->get('role'), ['superadmin', 'admin'])): ?>
                    <td class="cflc-value">${(campaign.last_cost_conversion > 0) ? formatNumber(campaign.cost - campaign.last_cost_conversion) : '-'}</td>
                    <?php endif;?>
                    <td class="small text-muted">
                        ${campaign.bidding_strategy || '-'}
                        ${campaign.target_cpa ? 
                            `<br>CPA: <span class="editable-target" 
                                data-type="cpa" 
                                data-campaign-id="${campaign.campaign_id}" 
                                data-current="${campaign.target_cpa}"
                                title="Click to edit">${formatNumber(campaign.target_cpa)}</span>` : 
                            ''
                        }
                        ${campaign.target_roas ? 
                            `<br>ROAS: <span class="editable-target" 
                                data-type="roas" 
                                data-campaign-id="${campaign.campaign_id}" 
                                data-current="${campaign.target_roas}"
                                title="Click to edit">${formatNumberWithoutCurrency2(campaign.target_roas)}</span>` : 
                            ''
                        }
                    </td>
                    <td>
                        <?php if (in_array(session()->get('role'), ['superadmin', 'admin'])): ?>
                        <a href="<?= base_url('campaign-details/campaign/') ?>${campaign.customer_id}/${campaign.campaign_id}" class="btn btn-sm btn-info m-1">
                            <i class="fas fa-eye"></i> Chi tiết
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            `;
        });

        // Thêm dòng total
        html += `
            <tr class="table-primary fw-bold">
                <td colspan="2">Tổng cộng</td>
                <td>${formatNumber(totals.budget)}</td>
                <td>-</td>
                <td class="text-primary">${formatNumber(totals.cost)}</td>
                <td>
                    <span class="${totalROAS > 2 ? 'text-success' : 'text-danger'}">
                        ${formatNumberWithoutCurrency2(totalROAS)}
                    </span>
                </td>
                <td class="text-primary">${formatNumber(totalRealCPA)}</td>
                <td class="text-primary">${(totals.real_conversions > 0) ? formatNumberWithoutCurrency(totals.real_conversions) + ' đơn' : '-'}</td>
                <td>-</td>
                <td>${formatNumberWithoutCurrency(totals.clicks)}</td>
                <td>${formatNumber(totalAverageCPC)}</td>
                <td>${formatNumber(totals.real_conversion_value)}</td>
                <td>${formatPercent(totalRealConversionRate)}</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
        `;
        
        $('#campaignsBody').html(html);
    }
    
    function showNotification(message, type = 'success') {
        if ($.notify) {
            $.notify({
                message: message
            }, {
                type: type,
                placement: {
                    from: 'top',
                    align: 'center'
                },
                z_index: 9999,
                timer: 2000
            });
        } else {
            alert(message);
        }
    }

    function formatNumber(number) {
        if (!number || isNaN(number)) return '0';
        if (account.currency_code === 'USD') {
            const formattedNumber = number_format(parseFloat(number), 2, ',', '.');
            return '$ ' + formattedNumber;
        }
        
        const formattedNumber = number_format(parseFloat(number), 0, ',', '.');
        return formattedNumber + ' ₫';
    }

    function formatNumberWithoutCurrency(number) {
        if (!number || isNaN(number)) return '0';
        return number_format(parseFloat(number), 0, ',', '.');
    }

    function formatNumberWithoutCurrency2(number) {
        if (!number || isNaN(number)) return '0';
        return number_format(parseFloat(number), 2, ',', '.');
    }

    function formatPercent(number) {
        if (!number || isNaN(number)) return '0%';
        return number_format(parseFloat(number*100), 2, ',', '.') + '%';
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