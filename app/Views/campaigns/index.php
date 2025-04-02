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
$(document).ready(function() {
    let campaignsData = [];
    let accountSettings = <?= json_encode($accountSettings) ?>;
    let mccId = <?= $mccId ?>;
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
                <tr>
                    <td>${campaign.campaign_id}</td>
                    <td>${campaign.name}</td>
                    <td>
                        <span class="editable-budget" 
                              data-campaign-id="${campaign.campaign_id}"
                              data-customer-id="${campaign.customer_id}"
                              data-mcc-id="${mccId}"
                              data-original="${campaign.budget}">
                            ${number_format(parseFloat(campaign.budget), 0, ',', '.')} VND
                        </span>
                        <div class="button-group" style="display: none;">
                            <button class="btn btn-sm btn-success save-budget">✓</button>
                            <button class="btn btn-sm btn-danger cancel-budget">✗</button>
                        </div>
                    </td>
                    <td>
                        <span class="badge ${campaign.status === 'ENABLED' ? 'bg-success' : 'bg-warning'}">
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
                        ${(campaign.real_cpa > 0) ? formatNumber(campaign.real_cpa) : '-'}
                    </td>
                    <td class="text-primary">${(campaign.real_conversions > 0) ? formatNumberWithoutCurrency(campaign.real_conversions) + ' đơn' : '-'}</td>
                    <td>${formatPercent(campaign.ctr)}</td>
                    <td>${formatNumberWithoutCurrency(campaign.clicks)}</td>
                    <td>${formatNumber(campaign.average_cpc)}</td>
                    <td>${(campaign.real_conversion_value > 0) ? formatNumber(campaign.real_conversion_value) : '-'}</td>
                    <td>${(campaign.real_conversion_rate > 0) ? formatPercent(campaign.real_conversion_rate) : '-'}</td>
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
                        <button class="btn ${campaign.status === 'ENABLED' ? 'btn-danger' : 'btn-success'} btn-sm toggle-status"
                                data-customer-id="${campaign.customer_id}"
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
                <td>${formatPercent(totalCTR)}</td>
                <td>${formatNumberWithoutCurrency(totals.clicks)}</td>
                <td>${formatNumber(totalAverageCPC)}</td>
                <td>${formatNumber(totals.real_conversion_value)}</td>
                <td>${formatPercent(totalRealConversionRate)}</td>
                <td>-</td>
                <td>-</td>
            </tr>
        `;
        
        $('#campaignsBody').html(html);
        initializeToggleButtons();
        initializeEditableTargets();
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
            
            toggleStatus(customerId, campaignId, currentStatus);
        });
    }

    function toggleStatus(customerId, campaignId, currentStatus) {
        const newStatus = currentStatus === 'ENABLED' ? 'PAUSED' : 'ENABLED';
        
        $.ajax({
            url: `/campaigns/toggleStatus/${customerId}/${campaignId}`,
            method: 'POST',
            data: { status: newStatus },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message);
                    // Cập nhật UI
                    const statusCell = $(`#campaign-${campaignId} .status-cell`);
                    const newStatusText = newStatus === 'ENABLED' ? 'Đang chạy' : 'Đã tạm dừng';
                    const newStatusClass = newStatus === 'ENABLED' ? 'text-success' : 'text-danger';
                    
                    statusCell.removeClass('text-success text-danger').addClass(newStatusClass);
                    statusCell.text(newStatusText);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('Có lỗi xảy ra khi cập nhật trạng thái chiến dịch', 'error');
            }
        });
    }

    // Initialize editable target fields
    function initializeEditableTargets() {
        // Remove any existing handlers first
        $(document).off('click', '.editable-target');
        $(document).off('keydown', '.editable-target.editing');
        $(document).off('blur', '.editable-target.editing');
        $(document).off('click', '.save-target-btn, .cancel-target-btn');
        
        // Click to edit
        $(document).on('click', '.editable-target', function(e) {
            const span = $(this);
            if (span.hasClass('editing')) return;
            
            // Close any other open editors first
            $('.editable-target.editing').each(function() {
                cancelEdit($(this));
            });
            
            const type = span.data('type');
            const currentValue = span.data('current');
            const campaignId = span.data('campaign-id');
            
            // Save original content to restore if canceled
            span.data('original-text', span.html());
            span.data('original-value', currentValue);
            
            // Make editable
            span.addClass('editing').attr('contenteditable', 'true');
            
            // Add save/cancel buttons after the span
            const buttonGroup = $(`
                <span class="target-btn-group ml-2">
                    <button class="btn btn-sm btn-success save-target-btn" data-campaign-id="${campaignId}" data-type="${type}">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-sm btn-danger cancel-target-btn" data-campaign-id="${campaignId}" data-type="${type}">
                        <i class="fas fa-times"></i>
                    </button>
                </span>
            `);
            span.after(buttonGroup);
            
            // Select all text
            document.execCommand('selectAll', false, null);
            
            // Focus the element
            span.focus();
        });
        
        // Handle keyboard actions
        $(document).on('keydown', '.editable-target.editing', function(e) {
            const span = $(this);
            
            // Enter key - save
            if (e.which === 13) {
                e.preventDefault();
                const campaignId = span.data('campaign-id');
                const type = span.data('type');
                saveEdit(span, campaignId, type);
                return false;
            }
            
            // Escape key - cancel
            if (e.which === 27) {
                e.preventDefault();
                cancelEdit(span);
                return false;
            }
            
            // Only allow numbers, decimal point, and control keys
            const allowedKeys = [8, 9, 37, 38, 39, 40, 46]; // Backspace, Tab, arrow keys, delete
            const isAllowedKey = allowedKeys.indexOf(e.which) !== -1;
            const isNumber = (e.which >= 48 && e.which <= 57) || (e.which >= 96 && e.which <= 105);
            const isDecimalPoint = e.which === 190 || e.which === 110;
            
            if (!isNumber && !isAllowedKey && (!isDecimalPoint || span.text().includes('.'))) {
                e.preventDefault();
                return false;
            }
        });
        
        // Handle save button click
        $(document).on('click', '.save-target-btn', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const campaignId = button.data('campaign-id');
            const type = button.data('type');
            const span = $(`.editable-target[data-campaign-id="${campaignId}"][data-type="${type}"]`);
            
            saveEdit(span, campaignId, type);
        });
        
        // Handle cancel button click
        $(document).on('click', '.cancel-target-btn', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const campaignId = button.data('campaign-id');
            const type = button.data('type');
            const span = $(`.editable-target[data-campaign-id="${campaignId}"][data-type="${type}"]`);
            
            cancelEdit(span);
        });
        
        // Cancel edit if clicked outside
        $(document).on('click', function(e) {
            if ($(e.target).closest('.editable-target, .target-btn-group').length === 0) {
                $('.editable-target.editing').each(function() {
                    cancelEdit($(this));
                });
            }
        });
    }
    
    function saveEdit(span, campaignId, type) {
        let newValue = span.text().trim().replace(/[^0-9.]/g, '');
        
        // Convert to number
        newValue = parseFloat(newValue);
        
        // Validate
        if (isNaN(newValue) || newValue <= 0) {
            cancelEdit(span);
            showNotification('Giá trị không hợp lệ! Vui lòng nhập số lớn hơn 0.', 'error');
            return;
        }
        
        // Format display value based on type
        const displayValue = type === 'cpa' 
            ? formatNumber(newValue) 
            : formatNumberWithoutCurrency2(newValue);
        
        // End editing mode
        span.removeClass('editing').removeAttr('contenteditable');
        span.html(displayValue);
        
        // Remove buttons
        span.next('.target-btn-group').remove();
        
        // Send update to server
        updateTargetValue(<?= $account['customer_id'] ?>, campaignId, type, newValue, span);
    }
    
    function cancelEdit(span) {
        // Restore original content
        span.html(span.data('original-text'));
        span.removeClass('editing').removeAttr('contenteditable');
        
        // Remove buttons
        span.next('.target-btn-group').remove();
    }
    
    function updateTargetValue(customerId, campaignId, type, value, element) {
        $.ajax({
            url: `/campaigns/updateTarget/${customerId}/${campaignId}`,
            method: 'POST',
            data: {
                type: type,
                value: value
            },
            success: function(response) {
                if (response.success) {
                    // Update the data attribute
                    element.data('current', value);
                    showNotification(response.message);
                } else {
                    // Revert to original on error
                    element.html(element.data('original-text'));
                    showNotification(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                // Revert to original on error
                element.html(element.data('original-text'));
                showNotification('Có lỗi xảy ra khi cập nhật giá trị!', 'error');
            }
        });
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

    function formatNumberWithoutCurrency2(number) {
        return new Intl.NumberFormat('vi-VN', { 
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
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

    // Gestion de l'édition du budget
    $(document).on('click', '.editable-budget', function() {
        const $this = $(this);
        const currentValue = $this.data('original');
        const $input = $('<input type="number" class="form-control form-control-sm" value="' + currentValue + '">');
        const $buttonGroup = $this.siblings('.button-group');
        
        $this.html($input);
        $buttonGroup.show();
        
        // Focus the input and select all text
        $input.focus();
        
        // Use setTimeout to ensure select() works after the input is fully rendered
        // $input.select();
        // setTimeout(function() {
        //     $input.select();
        // }, 0);
    });

    // Add a click handler for the input field to ensure it selects all text when clicked
    $(document).on('click', '.editable-budget input', function(e) {
        // Prevent event propagation to avoid toggling edit mode repeatedly
        e.stopPropagation();
        // Select all text in the input
        // $(this).select();
    });
    
    // Fix cursor position issue by preventing the default mouseup behavior
    $(document).on('mouseup', '.editable-budget input', function(e) {
        // Prevent the default behavior that would deselect the text
        e.preventDefault();
    });
    
    $(document).on('click', '.save-budget', function() {
        const $this = $(this);
        const $editable = $this.closest('td').find('.editable-budget');
        const $input = $editable.find('input');
        const newValue = $input.val();
        const originalValue = $editable.data('original');
        
        if (newValue === originalValue) {
            cancelBudgetEdit($editable);
            return;
        }
        
        const campaignId = $editable.data('campaign-id');
        const customerId = $editable.data('customer-id');
        
        $.ajax({
            url: `/campaigns/updateBudget/${customerId}/${campaignId}`,
            method: 'POST',
            data: {
                budget: newValue
            },
            success: function(response) {
                if (response.success) {
                    $editable.data('original', newValue);
                    $editable.html(number_format(newValue, 0, ',', '.') + ' VND');
                    showNotification('Cập nhật ngân sách thành công', 'success');
                } else {
                    showNotification(response.message, 'error');
                    $editable.html(number_format(originalValue, 0, ',', '.') + ' VND');
                }
            },
            error: function() {
                showNotification('Có lỗi xảy ra khi cập nhật ngân sách', 'error');
                $editable.html(number_format(originalValue, 0, ',', '.') + ' VND');
            },
            complete: function() {
                cancelBudgetEdit($editable);
            }
        });
    });

    $(document).on('click', '.cancel-budget', function() {
        const $editable = $(this).closest('td').find('.editable-budget');
        cancelBudgetEdit($editable);
    });
    $(document).on('blur', '.editable-budget input', function() {
        const $editable = $(this).closest('td').find('.editable-budget');
        cancelBudgetEdit($editable);
    });

    function cancelBudgetEdit($editable) {
        const originalValue = $editable.data('original');
        $editable.html(number_format(originalValue, 0, ',', '.') + ' VND');
        $editable.siblings('.button-group').hide();
    }

    // Gestion des événements clavier pour l'édition du budget
    $(document).on('keydown', '.editable-budget input', function(e) {
        if (e.key === 'Enter') {
            $(this).closest('td').find('.save-budget').click();
        } else if (e.key === 'Escape') {
            $(this).closest('td').find('.cancel-budget').click();
        }
    });

    // Trigger initial load
    $('#loadCampaigns').click();
});
</script>

<?= $this->include('templates/footer') ?> 