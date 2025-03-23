<?= $this->include('templates/header') ?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col">
            <h2>Chiến dịch - <?= esc($account['customer_name']) ?></h2>
            <p>ID tài khoản: <?= esc($account['customer_id']) ?></p>
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

    <div id="campaignsTable" class="table-responsive" style="display: none;">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Tên chiến dịch</th>
                    <th>ID</th>
                    <th class="sortable" data-sort="budget">
                        Budget
                        <i class="sort-icon"></i>
                    </th>
                    <th class="sortable" data-sort="cost">
                        Cost
                        <i class="sort-icon"></i>
                    </th>
                    <th class="sortable" data-sort="roas">
                        ROAS
                        <i class="sort-icon"></i>
                    </th>
                    <th class="sortable" data-sort="conversions">
                        Chuyển đổi
                        <i class="sort-icon"></i>
                    </th>
                    <th class="sortable" data-sort="costPerConversion">
                        CPA
                        <i class="sort-icon"></i>
                    </th>
                    <th>Conv rate</th>
                    <th>tCPA</th>
                    <th>tROAS</th>
                    <th>CTR</th>
                    <th>Clicks</th>
                    <th>CPC</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody id="campaignsBody">
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

    $('#loadCampaigns').click(function() {
        const showPaused = $('#showPaused').is(':checked');
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        const button = $(this);
        
        if (!startDate || !endDate) {
            alert('Vui lòng chọn khoảng thời gian');
            return;
        }

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
        
        $.ajax({
            url: '<?= base_url('campaigns/load/' . $account['customer_id']) ?>',
            method: 'GET',
            data: { 
                showPaused: showPaused,
                startDate: startDate,
                endDate: endDate
            },
            success: function(response) {
                if (response.success) {
                    campaignsData = response.campaigns;
                    renderCampaigns();
                    $('#campaignsTable').show();
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi tải dữ liệu');
            },
            complete: function() {
                button.prop('disabled', false).text('Load chiến dịch');
            }
        });
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
        
        if (currentSort.column) {
            sortedCampaigns.sort((a, b) => {
                let aValue, bValue;
                
                switch(currentSort.column) {
                    case 'budget':
                        aValue = a.budget;
                        bValue = b.budget;
                        break;
                    case 'cost':
                        aValue = a.cost;
                        bValue = b.cost;
                        break;
                    case 'roas':
                        aValue = a.cost > 0 ? a.conversion_value / a.cost : 0;
                        bValue = b.cost > 0 ? b.conversion_value / b.cost : 0;
                        break;
                    case 'conversions':
                        aValue = a.conversions;
                        bValue = b.conversions;
                        break;
                    case 'costPerConversion':
                        aValue = a.cost_per_conversion;
                        bValue = b.cost_per_conversion;
                        break;
                    default:
                        return 0;
                }
                
                if (currentSort.direction === 'asc') {
                    return aValue - bValue;
                } else {
                    return bValue - aValue;
                }
            });
        }

        let html = '';
        sortedCampaigns.forEach(function(campaign) {
            html += `
                <tr>
                    <td>${campaign.name}</td>
                    <td>${campaign.id}</td>
                    <td>${formatNumber(campaign.budget)}</td>
                    <td>${formatNumber(campaign.cost)}</td>
                    <td>${campaign.cost > 0 ? formatNumber(campaign.conversion_value / campaign.cost) : '0.00'}</td>
                    <td>${formatNumber(campaign.conversions)}</td>
                    <td>${formatNumber(campaign.cost_per_conversion)}</td>
                    <td>${formatNumber(campaign.conversion_rate * 100)}%</td>
                    <td>${campaign.target_cpa ? formatNumber(campaign.target_cpa) : 'N/A'}</td>
                    <td>${campaign.target_roas ? formatNumber(campaign.target_roas) : 'N/A'}</td>
                    <td>${formatNumber(campaign.ctr * 100)}%</td>
                    <td>${formatNumber(campaign.clicks)}</td>
                    <td>${formatNumber(campaign.average_cpc)}</td>
                    <td>
                        <span class="badge ${campaign.status === 'ENABLED' ? 'bg-success' : 'bg-secondary'}">
                            ${campaign.status === 'ENABLED' ? 'Đang chạy' : 'Tạm dừng'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm ${campaign.status === 'ENABLED' ? 'btn-warning' : 'btn-success'} toggle-campaign" 
                                data-campaign-id="${campaign.id}"
                                data-current-status="${campaign.status}">
                            ${campaign.status === 'ENABLED' ? 'Tạm dừng' : 'Kích hoạt'}
                        </button>
                    </td>
                </tr>
            `;
        });
        
        $('#campaignsBody').html(html);
    }

    $(document).on('click', '.toggle-campaign', function() {
        const button = $(this);
        const campaignId = button.data('campaign-id');
        
        $.ajax({
            url: '<?= base_url('campaigns/toggleStatus/' . $account['customer_id']) ?>/' + campaignId,
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    $('#loadCampaigns').click();
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi thực hiện thao tác');
            }
        });
    });

    function formatNumber(number) {
        return new Intl.NumberFormat('vi-VN', { 
            minimumFractionDigits: 2,
            maximumFractionDigits: 2 
        }).format(number);
    }

    // Trigger initial load
    $('#loadCampaigns').click();
});
</script>

<?= $this->include('templates/footer') ?> 