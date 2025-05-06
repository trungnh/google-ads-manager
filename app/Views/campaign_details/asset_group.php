<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Chi tiết nhóm thành phần: <?= esc($assetGroupDetails['name']) ?></h4>
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
                                <p>ID nhóm thành phần: <?= esc($assetGroupDetails['asset_group_id']) ?></p>
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
                                                <th width="40%">Tên nhóm thành phần</th>
                                                <td><?= esc($assetGroupDetails['name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Trạng thái</th>
                                                <td>
                                                    <?php if ($assetGroupDetails['status'] == 'ENABLED'): ?>
                                                        <span class="badge bg-success">Đang chạy</span>
                                                    <?php elseif ($assetGroupDetails['status'] == 'PAUSED'): ?>
                                                        <span class="badge bg-warning">Tạm dừng</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= esc($assetGroupDetails['status']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Chiến dịch</th>
                                                <td><?= esc($campaignDetails['name']) ?></td>
                                            </tr>
                                            <?php if (!empty($assetGroupDetails['final_urls'])): ?>
                                            <tr>
                                                <th>Final URLs</th>
                                                <td>
                                                    <ul class="list-unstyled">
                                                        <?php foreach ($assetGroupDetails['final_urls'] as $url): ?>
                                                            <li><a href="<?= esc($url) ?>" target="_blank"><?= esc($url) ?></a></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php /*?>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Hiệu suất</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th width="40%">Chi tiêu</th>
                                                <td><?= number_format($assetGroupDetails['cost'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Chuyển đổi</th>
                                                <td><?= number_format($assetGroupDetails['conversions'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Giá trị chuyển đổi</th>
                                                <td><?= number_format($assetGroupDetails['conversion_value'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>CPA</th>
                                                <td><?= number_format($assetGroupDetails['cost_per_conversion'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tỷ lệ chuyển đổi</th>
                                                <td><?= number_format($assetGroupDetails['conversion_rate'] * 100, 2) ?>%</td>
                                            </tr>
                                            <tr>
                                                <th>CTR</th>
                                                <td><?= number_format($assetGroupDetails['ctr'] * 100, 2) ?>%</td>
                                            </tr>
                                            <tr>
                                                <th>Clicks</th>
                                                <td><?= number_format($assetGroupDetails['clicks']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Hiển thị</th>
                                                <td><?= number_format($assetGroupDetails['impressions']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>CPC trung bình</th>
                                                <td><?= number_format($assetGroupDetails['average_cpc'], 2) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php */?>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">Danh sách thành phần (Assets)</h5>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="assetTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="headlines-tab" data-bs-toggle="tab" data-bs-target="#headlines" type="button" role="tab">Tiêu đề</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="descriptions-tab" data-bs-toggle="tab" data-bs-target="#descriptions" type="button" role="tab">Mô tả</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images" type="button" role="tab">Hình ảnh</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="videos-tab" data-bs-toggle="tab" data-bs-target="#videos" type="button" role="tab">Video</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="assetTabsContent">
                                <!-- Tiêu đề -->
                                <div class="tab-pane fade show active" id="headlines" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nội dung</th>
                                                    <th>Loại</th>
                                                    <th>Hiệu suất</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $headlineAssets = array_filter($assets, function($asset) {
                                                    return $asset['field_type'] == 'HEADLINE' && isset($asset['text']);
                                                });
                                                
                                                if (empty($headlineAssets)): 
                                                ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Không có tiêu đề nào</td>
                                                </tr>
                                                <?php else: ?>
                                                    <?php foreach ($headlineAssets as $asset): ?>
                                                    <tr>
                                                        <td><?= esc($asset['asset_id']) ?></td>
                                                        <td><?= esc($asset['text']) ?></td>
                                                        <td><?= esc($asset['type']) ?></td>
                                                        <td>
                                                            <?php if ($asset['performance_label'] == 'PENDING'): ?>
                                                                <span class="badge bg-secondary">Đang chờ</span>
                                                            <?php elseif ($asset['performance_label'] == 'LEARNING'): ?>
                                                                <span class="badge bg-info">Đang học</span>
                                                            <?php elseif ($asset['performance_label'] == 'LOW'): ?>
                                                                <span class="badge bg-danger">Thấp</span>
                                                            <?php elseif ($asset['performance_label'] == 'GOOD'): ?>
                                                                <span class="badge bg-success">Tốt</span>
                                                            <?php elseif ($asset['performance_label'] == 'BEST'): ?>
                                                                <span class="badge bg-primary">Tốt nhất</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary"><?= esc($asset['performance_label']) ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Mô tả -->
                                <div class="tab-pane fade" id="descriptions" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nội dung</th>
                                                    <th>Loại</th>
                                                    <th>Hiệu suất</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $descriptionAssets = array_filter($assets, function($asset) {
                                                    return $asset['field_type'] == 'DESCRIPTION' && isset($asset['text']);
                                                });
                                                
                                                if (empty($descriptionAssets)): 
                                                ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Không có mô tả nào</td>
                                                </tr>
                                                <?php else: ?>
                                                    <?php foreach ($descriptionAssets as $asset): ?>
                                                    <tr>
                                                        <td><?= esc($asset['asset_id']) ?></td>
                                                        <td><?= esc($asset['text']) ?></td>
                                                        <td><?= esc($asset['type']) ?></td>
                                                        <td>
                                                            <?php if ($asset['performance_label'] == 'PENDING'): ?>
                                                                <span class="badge bg-secondary">Đang chờ</span>
                                                            <?php elseif ($asset['performance_label'] == 'LEARNING'): ?>
                                                                <span class="badge bg-info">Đang học</span>
                                                            <?php elseif ($asset['performance_label'] == 'LOW'): ?>
                                                                <span class="badge bg-danger">Thấp</span>
                                                            <?php elseif ($asset['performance_label'] == 'GOOD'): ?>
                                                                <span class="badge bg-success">Tốt</span>
                                                            <?php elseif ($asset['performance_label'] == 'BEST'): ?>
                                                                <span class="badge bg-primary">Tốt nhất</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary"><?= esc($asset['performance_label']) ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Hình ảnh -->
                                <div class="tab-pane fade" id="images" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Hình ảnh</th>
                                                    <th>Loại</th>
                                                    <th>Kích thước</th>
                                                    <th>Hiệu suất</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $imageAssets = array_filter($assets, function($asset) {
                                                    return isset($asset['image_url']);
                                                });
                                                
                                                if (empty($imageAssets)): 
                                                ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">Không có hình ảnh nào</td>
                                                </tr>
                                                <?php else: ?>
                                                    <?php foreach ($imageAssets as $asset): ?>
                                                    <tr>
                                                        <td><?= esc($asset['asset_id']) ?></td>
                                                        <td>
                                                            <a href="<?= esc($asset['image_url']) ?>" target="_blank">
                                                                <img src="<?= esc($asset['image_url']) ?>" alt="Asset Image" style="max-width: 200px; max-height: 150px;">
                                                            </a>
                                                        </td>
                                                        <td><?= esc($asset['field_type']) ?></td>
                                                        <td><?= esc($asset['width_pixels'] ?? 0) ?> x <?= esc($asset['height_pixels'] ?? 0) ?></td>
                                                        <td>
                                                            <?php if ($asset['performance_label'] == 'PENDING'): ?>
                                                                <span class="badge bg-secondary">Đang chờ</span>
                                                            <?php elseif ($asset['performance_label'] == 'LEARNING'): ?>
                                                                <span class="badge bg-info">Đang học</span>
                                                            <?php elseif ($asset['performance_label'] == 'LOW'): ?>
                                                                <span class="badge bg-danger">Thấp</span>
                                                            <?php elseif ($asset['performance_label'] == 'GOOD'): ?>
                                                                <span class="badge bg-success">Tốt</span>
                                                            <?php elseif ($asset['performance_label'] == 'BEST'): ?>
                                                                <span class="badge bg-primary">Tốt nhất</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary"><?= esc($asset['performance_label']) ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Video -->
                                <div class="tab-pane fade" id="videos" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Video</th>
                                                    <th>Tiêu đề</th>
                                                    <th>Loại</th>
                                                    <th>Hiệu suất</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $videoAssets = array_filter($assets, function($asset) {
                                                    return isset($asset['youtube_video_id']);
                                                });
                                                
                                                if (empty($videoAssets)): 
                                                ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">Không có video nào</td>
                                                </tr>
                                                <?php else: ?>
                                                    <?php foreach ($videoAssets as $asset): ?>
                                                    <tr>
                                                        <td><?= esc($asset['asset_id']) ?></td>
                                                        <td>
                                                            <div class="embed-responsive embed-responsive-16by9">
                                                                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?= esc($asset['youtube_video_id']) ?>" allowfullscreen style="width: 200px; height: 150px;"></iframe>
                                                            </div>
                                                        </td>
                                                        <td><?= esc($asset['youtube_video_title']) ?></td>
                                                        <td><?= esc($asset['field_type']) ?></td>
                                                        <td>
                                                            <?php if ($asset['performance_label'] == 'PENDING'): ?>
                                                                <span class="badge bg-secondary">Đang chờ</span>
                                                            <?php elseif ($asset['performance_label'] == 'LEARNING'): ?>
                                                                <span class="badge bg-info">Đang học</span>
                                                            <?php elseif ($asset['performance_label'] == 'LOW'): ?>
                                                                <span class="badge bg-danger">Thấp</span>
                                                            <?php elseif ($asset['performance_label'] == 'GOOD'): ?>
                                                                <span class="badge bg-success">Tốt</span>
                                                            <?php elseif ($asset['performance_label'] == 'BEST'): ?>
                                                                <span class="badge bg-primary">Tốt nhất</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary"><?= esc($asset['performance_label']) ?></span>
                                                            <?php endif; ?>
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
    </div>
</div>

<?= $this->include('templates/footer') ?>