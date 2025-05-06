<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Chi tiết chiến dịch: <?= esc($campaignDetails['name']) ?></h4>
                        <div>
                            <a href="<?= base_url('campaigns/index/' . $account['customer_id']) ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại danh sách chiến dịch
                            </a>
                        </div>
                    </div>
                    
                    <div class="row mb-3 mt-3">
                        <div class="col-md-6">
                            <div>
                                <p>ID tài khoản: <?= esc($account['customer_id']) ?></p>
                                <p>ID chiến dịch: <?= esc($campaignDetails['campaign_id']) ?></p>
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
                                                <th width="40%">Tên chiến dịch</th>
                                                <td><?= esc($campaignDetails['name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Trạng thái</th>
                                                <td>
                                                    <?php if ($campaignDetails['status'] == 'ENABLED'): ?>
                                                        <span class="badge bg-success">Đang chạy</span>
                                                    <?php elseif ($campaignDetails['status'] == 'PAUSED'): ?>
                                                        <span class="badge bg-warning">Tạm dừng</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= esc($campaignDetails['status']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Loại chiến dịch</th>
                                                <td><?= esc($campaignDetails['advertising_channel_type']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Loại chiến dịch phụ</th>
                                                <td><?= esc($campaignDetails['advertising_channel_sub_type'] ?: 'Không có') ?></td>
                                            </tr>
                                            <tr>
                                                <th>Ngày bắt đầu</th>
                                                <td><?= esc($campaignDetails['start_date']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Ngày kết thúc</th>
                                                <td><?= esc($campaignDetails['end_date'] ?: 'Không giới hạn') ?></td>
                                            </tr>
                                            <tr>
                                                <th>Trạng thái phục vụ</th>
                                                <td><?= esc($campaignDetails['serving_status']) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Ngân sách và Đặt giá thầu</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th width="40%">Ngân sách</th>
                                                <td><?= number_format($campaignDetails['budget'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Phương thức phân phối ngân sách</th>
                                                <td><?= esc($campaignDetails['budget_delivery_method']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Chiến lược đặt giá thầu</th>
                                                <td><?= esc($campaignDetails['bidding_strategy_type']) ?></td>
                                            </tr>
                                            <?php if ($campaignDetails['target_cpa']): ?>
                                            <tr>
                                                <th>Target CPA</th>
                                                <td><?= number_format($campaignDetails['target_cpa'], 2) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if ($campaignDetails['target_roas']): ?>
                                            <tr>
                                                <th>Target ROAS</th>
                                                <td><?= number_format($campaignDetails['target_roas'], 2) ?></td>
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
                                                <td><?= number_format($campaignDetails['cost'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Chuyển đổi</th>
                                                <td><?= number_format($campaignDetails['conversions'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Giá trị chuyển đổi</th>
                                                <td><?= number_format($campaignDetails['conversion_value'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>CPA</th>
                                                <td><?= number_format($campaignDetails['cost_per_conversion'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tỷ lệ chuyển đổi</th>
                                                <td><?= number_format($campaignDetails['conversion_rate'] * 100, 2) ?>%</td>
                                            </tr>
                                            <tr>
                                                <th>CTR</th>
                                                <td><?= number_format($campaignDetails['ctr'] * 100, 2) ?>%</td>
                                            </tr>
                                            <tr>
                                                <th>Clicks</th>
                                                <td><?= number_format($campaignDetails['clicks']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Hiển thị</th>
                                                <td><?= number_format($campaignDetails['impressions']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>CPC trung bình</th>
                                                <td><?= number_format($campaignDetails['average_cpc'], 2) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php */?>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">Danh sách nhóm quảng cáo</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên nhóm quảng cáo</th>
                                            <th>Trạng thái</th>
                                            <th>Loại</th>
                                            <th>Chi tiêu</th>
                                            <th>Chuyển đổi</th>
                                            <th>CPA</th>
                                            <th>CTR</th>
                                            <th>Clicks</th>
                                            <th>CPC</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($adGroups)): ?>
                                        <tr>
                                            <td colspan="11" class="text-center">Không có nhóm quảng cáo nào</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($adGroups as $adGroup): ?>
                                            <tr>
                                                <td><?= esc($adGroup['ad_group_id']) ?></td>
                                                <td><?= esc($adGroup['name']) ?></td>
                                                <td>
                                                    <?php if ($adGroup['status'] == 'ENABLED'): ?>
                                                        <span class="badge bg-success">Đang chạy</span>
                                                    <?php elseif ($adGroup['status'] == 'PAUSED'): ?>
                                                        <span class="badge bg-warning">Tạm dừng</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= esc($adGroup['status']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= esc($adGroup['type']) ?></td>
                                                <td><?= number_format($adGroup['cost'], 2) ?></td>
                                                <td><?= number_format($adGroup['conversions'], 2) ?></td>
                                                <td><?= number_format($adGroup['cost_per_conversion'], 2) ?></td>
                                                <td><?= number_format($adGroup['ctr'] * 100, 2) ?>%</td>
                                                <td><?= number_format($adGroup['clicks']) ?></td>
                                                <td><?= number_format($adGroup['average_cpc'], 2) ?></td>
                                                <td>
                                                    <a href="<?= base_url('campaign-details/ad-group/' . $account['customer_id'] . '/' . $campaignDetails['campaign_id'] . '/' . $adGroup['ad_group_id']) ?>" class="btn btn-sm btn-primary">
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