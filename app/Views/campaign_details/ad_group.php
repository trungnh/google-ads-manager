<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Chi tiết nhóm quảng cáo: <?= esc($adGroupDetails['name']) ?></h4>
                        <div>
                            <a href="<?= base_url('campaign-details/campaign/' . $account['customer_id'] . '/' . $campaignDetails['campaign_id']) ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại chi tiết chiến dịch
                            </a>
                        </div>
                    </div>
                    
                    <div class="row mb-3 mt-3">
                        <div class="col-md-6">
                            <div>
                                <p>ID tài khoản: <?= esc($account['customer_id']) ?></p>
                                <p>ID chiến dịch: <?= esc($campaignDetails['campaign_id']) ?></p>
                                <p>ID nhóm quảng cáo: <?= esc($adGroupDetails['ad_group_id']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
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
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin cơ bản</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th width="40%">Tên nhóm quảng cáo</th>
                                                <td><?= esc($adGroupDetails['name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Trạng thái</th>
                                                <td>
                                                    <?php if ($adGroupDetails['status'] == 'ENABLED'): ?>
                                                        <span class="badge bg-success">Đang chạy</span>
                                                    <?php elseif ($adGroupDetails['status'] == 'PAUSED'): ?>
                                                        <span class="badge bg-warning">Tạm dừng</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= esc($adGroupDetails['status']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Loại</th>
                                                <td><?= esc($adGroupDetails['type']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Chiến dịch</th>
                                                <td><?= esc($campaignDetails['name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nhắm mục tiêu được tối ưu hóa</th>
                                                <td>
                                                    <?php if (isset($adGroupDetails['optimized_targeting_enabled']) && $adGroupDetails['optimized_targeting_enabled']): ?>
                                                        <span class="badge bg-success">Đang Bật</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Tắt</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Đặt giá thầu</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <?php if ($adGroupDetails['cpc_bid'] > 0): ?>
                                            <tr>
                                                <th width="40%">CPC Bid</th>
                                                <td><?= number_format($adGroupDetails['cpc_bid'], 2) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if ($adGroupDetails['cpm_bid'] > 0): ?>
                                            <tr>
                                                <th>CPM Bid</th>
                                                <td><?= number_format($adGroupDetails['cpm_bid'], 2) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if ($adGroupDetails['target_cpa'] > 0): ?>
                                            <tr>
                                                <th>Target CPA</th>
                                                <td><?= number_format($adGroupDetails['target_cpa'], 2) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if ($adGroupDetails['target_roas'] > 0): ?>
                                            <tr>
                                                <th>Target ROAS</th>
                                                <td><?= number_format($adGroupDetails['target_roas'], 2) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php /*?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Hiệu suất</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th width="40%">Chi tiêu</th>
                                                <td><?= number_format($adGroupDetails['cost'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Chuyển đổi</th>
                                                <td><?= number_format($adGroupDetails['conversions'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Giá trị chuyển đổi</th>
                                                <td><?= number_format($adGroupDetails['conversion_value'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>CPA</th>
                                                <td><?= number_format($adGroupDetails['cost_per_conversion'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tỷ lệ chuyển đổi</th>
                                                <td><?= number_format($adGroupDetails['conversion_rate'] * 100, 2) ?>%</td>
                                            </tr>
                                            <tr>
                                                <th>CTR</th>
                                                <td><?= number_format($adGroupDetails['ctr'] * 100, 2) ?>%</td>
                                            </tr>
                                            <tr>
                                                <th>Clicks</th>
                                                <td><?= number_format($adGroupDetails['clicks']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Hiển thị</th>
                                                <td><?= number_format($adGroupDetails['impressions']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>CPC trung bình</th>
                                                <td><?= number_format($adGroupDetails['average_cpc'], 2) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php */?>
                        </div>
                    </div>

                    <!-- Cấu hình nhắm mục tiêu nhóm quảng cáo (Kênh, Đối tượng) -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="card-title my-0">Cấu hình nhắm mục tiêu nhóm quảng cáo</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Kênh nhắm mục tiêu (Placements/Channels) -->
                                        <div class="col-md-6">
                                            <h6 class="fw-bold border-bottom pb-2 text-secondary"><i class="fas fa-bullseye text-primary me-2"></i> Kênh nhắm mục tiêu (Vị trí đặt/Placements)</h6>
                                            <?php if (empty($adGroupTargeting['placements'])): ?>
                                                <p class="text-muted small">Tự động tối ưu hoặc nhắm mục tiêu diện rộng</p>
                                            <?php else: ?>
                                                <ul class="list-group list-group-flush">
                                                    <?php foreach ($adGroupTargeting['placements'] as $pl): ?>
                                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 small bg-transparent">
                                                            <div>
                                                                <span class="fw-bold me-1">[<?= esc($pl['type']) ?>]</span>
                                                                <span><?= esc($pl['name']) ?></span>
                                                            </div>
                                                            <div>
                                                                <?php if ($pl['negative']): ?>
                                                                    <span class="badge bg-danger me-1">Loại trừ</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-success me-1">Nhắm mục tiêu</span>
                                                                <?php endif; ?>
                                                                <span class="badge bg-secondary"><?= esc($pl['status']) ?></span>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Đối tượng nhắm mục tiêu (Audiences) -->
                                        <div class="col-md-6">
                                            <h6 class="fw-bold border-bottom pb-2 text-secondary"><i class="fas fa-users text-success me-2"></i> Đối tượng nhắm mục tiêu (Audience segments)</h6>
                                            <?php if (empty($adGroupTargeting['audiences'])): ?>
                                                <p class="text-muted small">Không nhắm mục tiêu đối tượng cụ thể</p>
                                            <?php else: ?>
                                                <ul class="list-group list-group-flush">
                                                    <?php foreach ($adGroupTargeting['audiences'] as $aud): ?>
                                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 small bg-transparent">
                                                            <div>
                                                                <span class="fw-bold me-1">[<?= esc($aud['type']) ?>]</span>
                                                                <span><?= esc($aud['display_name']) ?></span>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <?php if ($aud['negative']): ?>
                                                                    <span class="badge bg-danger me-2">Loại trừ</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-success me-2">Nhắm mục tiêu</span>
                                                                <?php endif; ?>
                                                                <button type="button" class="btn btn-sm btn-outline-info btn-view-audience" data-audience='<?= json_encode($aud) ?>'>
                                                                    <i class="fas fa-info-circle"></i> Chi tiết
                                                                </button>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">Danh sách quảng cáo</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên quảng cáo</th>
                                            <th>Loại</th>
                                            <th>Trạng thái</th>
                                            <th>Chi tiêu</th>
                                            <th>Chuyển đổi</th>
                                            <th>CPA</th>
                                            <th>CTR</th>
                                            <th>Clicks</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($ads)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center">Không có quảng cáo nào</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($ads as $ad): ?>
                                            <tr>
                                                <td><?= esc($ad['ad_id']) ?></td>
                                                <td><?= esc($ad['name']) ?></td>
                                                <td><?= esc($ad['type']) ?></td>
                                                <td>
                                                    <?php if ($ad['status'] == 'ENABLED'): ?>
                                                        <span class="badge bg-success">Đang chạy</span>
                                                    <?php elseif ($ad['status'] == 'PAUSED'): ?>
                                                        <span class="badge bg-warning">Tạm dừng</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= esc($ad['status']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= number_format($ad['cost'], 2) ?></td>
                                                <td><?= number_format($ad['conversions'], 2) ?></td>
                                                <td><?= number_format($ad['cost_per_conversion'], 2) ?></td>
                                                <td><?= number_format($ad['ctr'] * 100, 2) ?>%</td>
                                                <td><?= number_format($ad['clicks']) ?></td>
                                                <td>
                                                    <a href="<?= base_url('campaign-details/ad/' . $account['customer_id'] . '/' . $campaignDetails['campaign_id'] . '/' . $adGroupDetails['ad_group_id'] . '/' . $ad['ad_id']) ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> Xem
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết đối tượng -->
<div class="modal fade" id="audienceDetailsModal" tabindex="-1" aria-labelledby="audienceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="audienceDetailsModalLabel"><i class="fas fa-users text-primary me-2"></i> Chi tiết đối tượng nhắm mục tiêu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="audienceModalContent">
                    <!-- Nội dung chi tiết đối tượng sẽ được render bằng JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewAudienceButtons = document.querySelectorAll('.btn-view-audience');
    viewAudienceButtons.forEach(button => {
        button.addEventListener('click', function() {
            const audience = JSON.parse(this.dataset.audience);
            const contentDiv = document.getElementById('audienceModalContent');
            
            let html = '';
            
            // Basic Info
            html += `<table class="table table-bordered">
                <tbody>
                    <tr>
                        <th width="35%" class="bg-light">Tên đối tượng</th>
                        <td><strong>${escapeHtml(audience.display_name)}</strong></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Loại đối tượng</th>
                        <td><span class="badge bg-primary">${escapeHtml(audience.type)}</span></td>
                    </tr>
                    <tr>
                        <th class="bg-light">ID Tiêu chí (Criterion ID)</th>
                        <td>${escapeHtml(audience.criterion_id)}</td>
                    </tr>`;
            
            if (audience.resource_name) {
                html += `<tr>
                    <th class="bg-light">Resource Name</th>
                    <td><code class="small text-break">${escapeHtml(audience.resource_name)}</code></td>
                </tr>`;
            }

            if (audience.status) {
                html += `<tr>
                    <th class="bg-light">Trạng thái mục tiêu</th>
                    <td><span class="badge bg-info">${escapeHtml(audience.status)}</span></td>
                </tr>`;
            }

            if (audience.description) {
                html += `<tr>
                    <th class="bg-light">Mô tả</th>
                    <td>${escapeHtml(audience.description)}</td>
                </tr>`;
            }

            html += `</tbody></table>`;

            // Type-specific detailed parameters
            if (audience.details && audience.details.details) {
                const details = audience.details.details;
                html += `<h5 class="mt-4 mb-3 text-secondary border-bottom pb-2"><i class="fas fa-cog me-1"></i> Thông số cấu hình Google Ads</h5>`;
                
                if (audience.type === 'USER_LIST') {
                    // User List specific details
                    html += `<table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th width="40%" class="bg-light">Quy mô mạng tìm kiếm (Search Size)</th>
                                <td>${formatNumber(details.sizeForSearch)}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Quy mô mạng hiển thị (Display Size)</th>
                                <td>${formatNumber(details.sizeForDisplay)}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Thời gian lưu giữ (Life Span)</th>
                                <td>${details.membershipLifeSpan ? details.membershipLifeSpan + ' ngày' : 'Không giới hạn'}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Trạng thái thành viên</th>
                                <td><span class="badge ${details.membershipStatus === 'OPEN' ? 'bg-success' : 'bg-danger'}">${details.membershipStatus || 'N/A'}</span></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Loại danh sách (User List Type)</th>
                                <td>${escapeHtml(details.type || 'N/A')}</td>
                            </tr>
                        </tbody>
                    </table>`;
                } else if (audience.type === 'CUSTOM_AUDIENCE') {
                    // Custom Audience specific details
                    html += `<table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th width="40%" class="bg-light">Trạng thái hoạt động</th>
                                <td><span class="badge bg-secondary">${escapeHtml(details.status || 'N/A')}</span></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Loại đối tượng tùy chỉnh</th>
                                <td>${escapeHtml(details.type || 'N/A')}</td>
                            </tr>
                        </tbody>
                    </table>`;
                } else if (audience.type === 'AUDIENCE') {
                    // Audience specific details
                    html += `<table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th width="40%" class="bg-light">Trạng thái đối tượng</th>
                                <td><span class="badge bg-secondary">${escapeHtml(details.status || 'N/A')}</span></td>
                            </tr>
                        </tbody>
                    </table>`;

                    if (details.dimensions && details.dimensions.length > 0) {
                        html += `<h6 class="fw-bold mt-3"><i class="fas fa-sliders-h me-1"></i> Phân khúc cấu thành (Dimensions)</h6>`;
                        html += `<ul class="list-group">`;
                        details.dimensions.forEach(dim => {
                            if (dim.ageRanges) {
                                html += `<li class="list-group-item bg-transparent">`;
                                html += `<strong>Độ tuổi</strong>: `;
                                html += dim.ageRanges.map(a => escapeHtml(a.type)).join(', ');
                                html += `</li>`;
                            } else if (dim.genders) {
                                html += `<li class="list-group-item bg-transparent">`;
                                html += `<strong>Giới tính</strong>: `;
                                html += dim.genders.map(g => escapeHtml(g.type)).join(', ');
                                html += `</li>`;
                            } else if (dim.householdIncome) {
                                html += `<li class="list-group-item bg-transparent">`;
                                html += `<strong>Thu nhập hộ gia đình</strong>: `;
                                html += dim.householdIncome.map(h => escapeHtml(h.type)).join(', ');
                                html += `</li>`;
                            } else if (dim.parentalStatus) {
                                html += `<li class="list-group-item bg-transparent">`;
                                html += `<strong>Trạng thái phụ huynh</strong>: `;
                                html += dim.parentalStatus.map(p => escapeHtml(p.type)).join(', ');
                                html += `</li>`;
                            } else if (dim.userLists) {
                                html += `<li class="list-group-item bg-transparent">`;
                                html += `<strong>Tệp khách hàng/Remarketing</strong>: `;
                                html += dim.userLists.map(u => `<code class="small">${escapeHtml(u.userList)}</code>`).join(', ');
                                html += `</li>`;
                            } else if (dim.audienceSegments && dim.audienceSegments.segments) {
                                dim.audienceSegments.segments.forEach(seg => {
                                    let segType = '';
                                    let resourceName = '';
                                    let hasDetailButton = false;

                                    if (seg.userList) {
                                        segType = 'Tệp khách hàng (User List)';
                                        resourceName = seg.userList.userList;
                                    } else if (seg.customAudience) {
                                        segType = 'Phân khúc tùy chỉnh (Custom Segment)';
                                        resourceName = seg.customAudience.customAudience;
                                        hasDetailButton = true;
                                    } else if (seg.detailedDemographic) {
                                        segType = 'Nhân khẩu học chi tiết (Detailed Demographic)';
                                        resourceName = seg.detailedDemographic.detailedDemographic;
                                    } else if (seg.affinityGroup) {
                                        segType = 'Mối quan tâm (Affinity)';
                                        resourceName = seg.affinityGroup.affinityGroup;
                                    } else if (seg.inMarket) {
                                        segType = 'Mối quan tâm (In Market)';
                                        resourceName = seg.inMarket.inMarket;
                                    }

                                    if (segType) {
                                        const shortName = getResourceId(resourceName);
                                        html += `<li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-2 px-3 small">`;
                                        html += `<div>`;
                                        html += `<strong>${segType}</strong>: <span class="resolved-segment-name fw-bold" data-resource-name="${escapeHtml(resourceName)}">${escapeHtml(shortName)}</span>`;
                                        html += `</div>`;
                                        if (hasDetailButton) {
                                            html += `<button type="button" class="btn btn-xs btn-outline-info py-0 px-2 btn-view-custom-segment font-monospace" data-resource-name="${escapeHtml(resourceName)}" style="font-size: 0.75rem; border-radius: 2px;">`;
                                            html += `<i class="fas fa-eye me-1"></i>Chi tiết`;
                                            html += `</button>`;
                                        }
                                        html += `</li>`;
                                    }
                                });
                            } else {
                                html += `<li class="list-group-item bg-transparent text-muted small">${JSON.stringify(dim)}</li>`;
                            }
                        });
                        html += `</ul>`;
                    }
                }
            }
            
            contentDiv.innerHTML = html;
            
            // Resolve friendly names dynamically via AJAX
            setTimeout(() => {
                document.querySelectorAll('.resolved-segment-name').forEach(span => {
                    const resName = span.dataset.resourceName;
                    $.ajax({
                        url: '/campaign-details/ajax-audience-details',
                        type: 'GET',
                        data: {
                            customer_id: '<?= esc($account['customer_id']) ?>',
                            resource_name: resName
                        },
                        success: function(data) {
                            if (data && data.name) {
                                span.textContent = data.name;
                                if (data.description) {
                                    span.title = data.description;
                                }
                            }
                        }
                    });
                });
            }, 100);

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('audienceDetailsModal'));
            modal.show();
        });
    });

    // Handle nested Custom Segment Details click
    $(document).on('click', '.btn-view-custom-segment', function(e) {
        e.preventDefault();
        const resName = $(this).data('resource-name');
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
        
        $.ajax({
            url: '/campaign-details/ajax-audience-details',
            type: 'GET',
            data: {
                customer_id: '<?= esc($account['customer_id']) ?>',
                resource_name: resName
            },
            success: function(data) {
                btn.prop('disabled', false).html(originalText);
                if (data && data.details) {
                    const details = data.details;
                    
                    let membersHtml = '';
                    if (details.members && details.members.length > 0) {
                        const keywords = [];
                        const urls = [];
                        const apps = [];

                        details.members.forEach(member => {
                            const type = member.memberType || '';
                            const val = member.value || '';
                            if (type === 'KEYWORD') {
                                keywords.push(val);
                            } else if (type === 'URL') {
                                urls.push(val);
                            } else if (type === 'APP') {
                                apps.push(val);
                            }
                        });

                        if (keywords.length > 0) {
                            membersHtml += `
                                <div class="mt-3">
                                    <h6 class="fw-bold text-secondary small"><i class="fas fa-keyboard me-1"></i>Từ khóa đã lưu (${keywords.length})</h6>
                                    <div class="d-flex flex-wrap gap-1 mt-1" style="max-height: 150px; overflow-y: auto;">
                                        ${keywords.map(kw => `<span class="badge bg-light text-dark border px-2 py-1 small" style="font-weight: normal; font-size: 0.75rem;">${escapeHtml(kw)}</span>`).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        if (urls.length > 0) {
                            membersHtml += `
                                <div class="mt-3">
                                    <h6 class="fw-bold text-secondary small"><i class="fas fa-link me-1"></i>Trang web tương tự (${urls.length})</h6>
                                    <ul class="list-group list-group-flush border-top border-bottom py-1 ps-3 mb-0" style="max-height: 120px; overflow-y: auto;">
                                        ${urls.map(url => `<li class="py-1 small text-break" style="list-style-type: disc;"><a href="${escapeHtml(url)}" target="_blank" class="text-decoration-none text-info">${escapeHtml(url)}</a></li>`).join('')}
                                    </ul>
                                </div>
                            `;
                        }

                        if (apps.length > 0) {
                            membersHtml += `
                                <div class="mt-3">
                                    <h6 class="fw-bold text-secondary small"><i class="fas fa-mobile-alt me-1"></i>Ứng dụng liên quan (${apps.length})</h6>
                                    <div class="d-flex flex-wrap gap-1 mt-1" style="max-height: 120px; overflow-y: auto;">
                                        ${apps.map(app => `<span class="badge bg-light text-secondary border px-2 py-1 small" style="font-weight: normal; font-size: 0.75rem;"><i class="fab fa-android text-success me-1"></i>${escapeHtml(app)}</span>`).join('')}
                                    </div>
                                </div>
                            `;
                        }
                    } else {
                        membersHtml = `<div class="mt-3 text-muted small italic"><i class="fas fa-info-circle me-1"></i>Không có từ khóa hay trang web nào được thiết lập.</div>`;
                    }

                    let modalHtml = `
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th width="35%" class="bg-light">Tên phân khúc</th>
                                    <td><strong>${escapeHtml(data.name)}</strong></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">ID</th>
                                    <td>${escapeHtml(data.id)}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Loại phân khúc</th>
                                    <td><span class="badge bg-primary">${escapeHtml(details.type || 'N/A')}</span></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Trạng thái</th>
                                    <td><span class="badge bg-secondary">${escapeHtml(details.status || 'N/A')}</span></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Mô tả</th>
                                    <td>${escapeHtml(details.description || 'Không có mô tả')}</td>
                                </tr>
                            </tbody>
                        </table>
                        ${membersHtml}
                    `;
                    
                    let customModal = $('#customSegmentDetailsModal');
                    if (customModal.length === 0) {
                        $('body').append(`
                            <div class="modal fade" id="customSegmentDetailsModal" tabindex="-1" aria-labelledby="customSegmentDetailsModalLabel" aria-hidden="true" style="z-index: 1060;">
                                <div class="modal-dialog modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fs-6 fw-bold" id="customSegmentDetailsModalLabel"><i class="fas fa-bullseye text-info me-2"></i>Phân khúc tùy chỉnh</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body" id="customSegmentModalBody"></div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                        customModal = $('#customSegmentDetailsModal');
                    }
                    
                    $('#customSegmentModalBody').html(modalHtml);
                    const modalObj = new bootstrap.Modal(document.getElementById('customSegmentDetailsModal'));
                    modalObj.show();
                } else {
                    alert('Không thể lấy thông tin chi tiết của phân khúc tùy chỉnh.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                alert('Có lỗi xảy ra khi lấy thông tin phân khúc.');
            }
        });
    });

    function getResourceId(resourceName) {
        if (!resourceName) return 'N/A';
        const parts = resourceName.split('/');
        return parts[parts.length - 1];
    }

    function escapeHtml(str) {
        if (!str) return 'N/A';
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatNumber(val) {
        let num = parseFloat(val);
        if (isNaN(num)) return 'Không xác định';
        return num.toLocaleString('en-US');
    }
});
</script>

<?= $this->include('templates/footer') ?>