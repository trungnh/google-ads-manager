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

<?= $this->include('templates/footer') ?>