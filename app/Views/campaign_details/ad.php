<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Chi tiết quảng cáo: <?= esc($adDetails['name']) ?></h4>
                        <div>
                            <a href="<?= base_url('campaign-details/ad-group/' . $account['customer_id'] . '/' . $campaignDetails['campaign_id'] . '/' . $adGroupDetails['ad_group_id']) ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại nhóm quảng cáo
                            </a>
                        </div>
                    </div>
                    
                    <div class="row mb-3 mt-3">
                        <div class="col-md-6">
                            <div>
                                <p>ID tài khoản: <?= esc($account['customer_id']) ?></p>
                                <p>ID chiến dịch: <?= esc($campaignDetails['campaign_id']) ?></p>
                                <p>ID nhóm quảng cáo: <?= esc($adGroupDetails['ad_group_id']) ?></p>
                                <p>ID quảng cáo: <?= esc($adDetails['ad_id']) ?></p>
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
                                                <th width="40%">Tên quảng cáo</th>
                                                <td><?= esc($adDetails['name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Loại quảng cáo</th>
                                                <td><?= esc($adDetails['type']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Trạng thái</th>
                                                <td>
                                                    <?php if ($adDetails['status'] == 'ENABLED'): ?>
                                                        <span class="badge bg-success">Đang chạy</span>
                                                    <?php elseif ($adDetails['status'] == 'PAUSED'): ?>
                                                        <span class="badge bg-warning">Tạm dừng</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= esc($adDetails['status']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Nhóm quảng cáo</th>
                                                <td><?= esc($adGroupDetails['name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Chiến dịch</th>
                                                <td><?= esc($campaignDetails['name']) ?></td>
                                            </tr>
                                            <?php if (!empty($adDetails['final_urls'])): ?>
                                            <tr>
                                                <th>Final URLs</th>
                                                <td>
                                                    <ul class="list-unstyled">
                                                        <?php foreach ($adDetails['final_urls'] as $url): ?>
                                                            <li><a href="<?= esc($url) ?>" target="_blank"><?= esc($url) ?></a></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($adDetails['display_url'])): ?>
                                            <tr>
                                                <th>Display URL</th>
                                                <td><?= esc($adDetails['display_url']) ?></td>
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
                                                <td><?= number_format($adDetails['cost'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Chuyển đổi</th>
                                                <td><?= number_format($adDetails['conversions'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Giá trị chuyển đổi</th>
                                                <td><?= number_format($adDetails['conversion_value'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>CPA</th>
                                                <td><?= number_format($adDetails['cost_per_conversion'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tỷ lệ chuyển đổi</th>
                                                <td><?= number_format($adDetails['conversion_rate'] * 100, 2) ?>%</td>
                                            </tr>
                                            <tr>
                                                <th>CTR</th>
                                                <td><?= number_format($adDetails['ctr'] * 100, 2) ?>%</td>
                                            </tr>
                                            <tr>
                                                <th>Clicks</th>
                                                <td><?= number_format($adDetails['clicks']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Hiển thị</th>
                                                <td><?= number_format($adDetails['impressions']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>CPC trung bình</th>
                                                <td><?= number_format($adDetails['average_cpc'], 2) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php */ ?>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">Nội dung quảng cáo</h5>
                        </div>
                        <div class="card-body">
                            <!-- Hiển thị xem trước quảng cáo nếu là loại quảng cáo cơ bản -->
                            <?php if ($adDetails['type'] == 'TEXT_AD'): ?>
                                <div class="ad-preview text-ad mb-4">
                                    <h5 class="ad-headline"><?= esc($adDetails['headline']) ?></h5>
                                    <div class="ad-display-url"><?= esc($adDetails['display_url']) ?></div>
                                    <div class="ad-description"><?= esc($adDetails['description1']) ?></div>
                                    <?php if (!empty($adDetails['description2'])): ?>
                                    <div class="ad-description"><?= esc($adDetails['description2']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($adDetails['type'] == 'EXPANDED_TEXT_AD'): ?>
                                <div class="ad-preview expanded-text-ad mb-4">
                                    <h5 class="ad-headline"><?= esc($adDetails['headline_part1']) ?> - <?= esc($adDetails['headline_part2']) ?></h5>
                                    <?php if (!empty($adDetails['headline_part3'])): ?>
                                    <h6 class="ad-headline-part3"><?= esc($adDetails['headline_part3']) ?></h6>
                                    <?php endif; ?>
                                    <div class="ad-display-url"><?= esc($adDetails['display_url']) ?></div>
                                    <div class="ad-description"><?= esc($adDetails['description']) ?></div>
                                    <?php if (!empty($adDetails['description2'])): ?>
                                    <div class="ad-description"><?= esc($adDetails['description2']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs" id="adContentTabs" role="tablist">
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
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="urls-tab" data-bs-toggle="tab" data-bs-target="#urls" type="button" role="tab">URLs</button>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="adContentTabsContent">
                                <!-- Tab Tiêu đề -->
                                <div class="tab-pane fade show active" id="headlines" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="80%">Nội dung</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($adDetails['type'] == 'TEXT_AD'): ?>
                                                    <tr>
                                                        <td><?= esc($adDetails['headline']) ?></td>
                                                        <td><span class="badge bg-success">Chính</span></td>
                                                    </tr>
                                                <?php elseif ($adDetails['type'] == 'EXPANDED_TEXT_AD'): ?>
                                                    <tr>
                                                        <td><?= esc($adDetails['headline_part1']) ?></td>
                                                        <td><span class="badge bg-success">Phần 1</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= esc($adDetails['headline_part2']) ?></td>
                                                        <td><span class="badge bg-success">Phần 2</span></td>
                                                    </tr>
                                                    <?php if (!empty($adDetails['headline_part3'])): ?>
                                                    <tr>
                                                        <td><?= esc($adDetails['headline_part3']) ?></td>
                                                        <td><span class="badge bg-success">Phần 3</span></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                <?php elseif ($adDetails['type'] == 'RESPONSIVE_SEARCH_AD' && isset($adDetails['headlines']) && is_array($adDetails['headlines'])): ?>
                                                    <?php foreach ($adDetails['headlines'] as $headline): ?>
                                                        <tr>
                                                            <td><?= esc($headline['text'] ?? '') ?></td>
                                                            <td>
                                                                <?php if (isset($headline['pinned']) && $headline['pinned']): ?>
                                                                    <span class="badge bg-info">Vị trí <?= esc($headline['pinned']) ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">Linh hoạt</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php elseif ($adDetails['type'] == 'DEMAND_GEN_VIDEO_RESPONSIVE_AD' && isset($adDetails['headlines']) && is_array($adDetails['headlines'])): ?>
                                                    <?php foreach ($adDetails['headlines'] as $headline): ?>
                                                        <tr>
                                                            <td><?= esc($headline['text'] ?? '') ?></td>
                                                            <td>
                                                                <?php if (isset($headline['pinned']) && $headline['pinned']): ?>
                                                                    <span class="badge bg-info">Vị trí <?= esc($headline['pinned']) ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">Linh hoạt</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="2" class="text-center">Không có thông tin tiêu đề cho loại quảng cáo này</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Tab Mô tả -->
                                <div class="tab-pane fade" id="descriptions" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="80%">Nội dung</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($adDetails['type'] == 'TEXT_AD'): ?>
                                                    <tr>
                                                        <td><?= esc($adDetails['description1']) ?></td>
                                                        <td><span class="badge bg-success">Mô tả 1</span></td>
                                                    </tr>
                                                    <?php if (!empty($adDetails['description2'])): ?>
                                                    <tr>
                                                        <td><?= esc($adDetails['description2']) ?></td>
                                                        <td><span class="badge bg-success">Mô tả 2</span></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                <?php elseif ($adDetails['type'] == 'EXPANDED_TEXT_AD'): ?>
                                                    <tr>
                                                        <td><?= esc($adDetails['description']) ?></td>
                                                        <td><span class="badge bg-success">Chính</span></td>
                                                    </tr>
                                                    <?php if (!empty($adDetails['description2'])): ?>
                                                    <tr>
                                                        <td><?= esc($adDetails['description2']) ?></td>
                                                        <td><span class="badge bg-success">Phụ</span></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                <?php elseif ($adDetails['type'] == 'RESPONSIVE_SEARCH_AD' && isset($adDetails['descriptions']) && is_array($adDetails['descriptions'])): ?>
                                                    <?php foreach ($adDetails['descriptions'] as $description): ?>
                                                        <tr>
                                                            <td><?= esc($description['text'] ?? '') ?></td>
                                                            <td>
                                                                <?php if (isset($description['pinned']) && $description['pinned']): ?>
                                                                    <span class="badge bg-info">Vị trí <?= esc($description['pinned']) ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">Linh hoạt</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php elseif ($adDetails['type'] == 'DEMAND_GEN_VIDEO_RESPONSIVE_AD' && isset($adDetails['descriptions']) && is_array($adDetails['descriptions'])): ?>
                                                    <?php foreach ($adDetails['descriptions'] as $description): ?>
                                                        <tr>
                                                            <td><?= esc($description['text'] ?? '') ?></td>
                                                            <td>
                                                                <?php if (isset($description['pinned']) && $description['pinned']): ?>
                                                                    <span class="badge bg-info">Vị trí <?= esc($description['pinned']) ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">Linh hoạt</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="2" class="text-center">Không có thông tin mô tả cho loại quảng cáo này</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Tab Hình ảnh -->
                                <div class="tab-pane fade" id="images" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Hình ảnh</th>
                                                    <th>Loại</th>
                                                    <th>Kích thước</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (isset($adDetails['images']) && is_array($adDetails['images']) && !empty($adDetails['images'])): ?>
                                                    <?php foreach ($adDetails['images'] as $image): ?>
                                                        <tr>
                                                            <td>
                                                                <?php if (isset($image['image_url'])): ?>
                                                                    <a href="<?= esc($image['image_url']) ?>" target="_blank">
                                                                        <img src="<?= esc($image['image_url']) ?>" alt="Ad Image" style="max-width: 200px; max-height: 150px;">
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Không có URL hình ảnh</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= esc($image['type'] ?? 'Không xác định') ?></td>
                                                            <td>
                                                                <?php if (isset($image['width']) && isset($image['height'])): ?>
                                                                    <?= esc($image['width']) ?> x <?= esc($image['height']) ?>
                                                                <?php else: ?>
                                                                    Không xác định
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php elseif (isset($adDetails['image_url']) && !empty($adDetails['image_url'])): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="<?= esc($adDetails['image_url']) ?>" target="_blank">
                                                                <img src="<?= esc($adDetails['image_url']) ?>" alt="Ad Image" style="max-width: 200px; max-height: 150px;">
                                                            </a>
                                                        </td>
                                                        <td>Chính</td>
                                                        <td>
                                                            <?php if (isset($adDetails['image_width']) && isset($adDetails['image_height'])): ?>
                                                                <?= esc($adDetails['image_width']) ?> x <?= esc($adDetails['image_height']) ?>
                                                            <?php else: ?>
                                                                Không xác định
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">Không có hình ảnh nào</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Tab Video -->
                                <div class="tab-pane fade" id="videos" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Video</th>
                                                    <th>ID</th>
                                                    <!-- <th>Loại</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (isset($adDetails['video_assets']) && is_array($adDetails['video_assets']) && !empty($adDetails['video_assets'])): ?>
                                                    <?php foreach ($adDetails['video_assets'] as $video): ?>
                                                        <tr>
                                                            <td>
                                                                <?php if (isset($video['url'])): ?>
                                                                    <div class="embed-responsive embed-responsive-16by9">
                                                                        <iframe class="embed-responsive-item" src="<?= esc($video['url']) ?>" allowfullscreen style="width: 200px; height: 150px;"></iframe>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Không có ID video</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= esc($video['id'] ?? 'Không có ID') ?></td>
                                                            <!-- <td><?= esc($video['type'] ?? 'Không xác định') ?></td> -->
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php elseif (isset($adDetails['youtube_video_id']) && !empty($adDetails['youtube_video_id'])): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="embed-responsive embed-responsive-16by9">
                                                                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?= esc($adDetails['youtube_video_id']) ?>" allowfullscreen style="width: 200px; height: 150px;"></iframe>
                                                            </div>
                                                        </td>
                                                        <td><?= esc($adDetails['youtube_video_id'] ?? 'Không có ID') ?></td>
                                                        <!-- <td>Chính</td> -->
                                                    </tr>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">Không có video nào</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Tab URLs -->
                                <div class="tab-pane fade" id="urls" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="20%">Loại</th>
                                                    <th>URL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($adDetails['final_urls'])): ?>
                                                    <?php foreach ($adDetails['final_urls'] as $index => $url): ?>
                                                        <tr>
                                                            <th>Final URL <?= $index + 1 ?></th>
                                                            <td><a href="<?= esc($url) ?>" target="_blank"><?= esc($url) ?></a></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($adDetails['tracking_url_template'])): ?>
                                                    <tr>
                                                        <th>Tracking URL Template</th>
                                                        <td><?= esc($adDetails['tracking_url_template']) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($adDetails['final_url_suffix'])): ?>
                                                    <tr>
                                                        <th>Final URL Suffix</th>
                                                        <td><?= esc($adDetails['final_url_suffix']) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($adDetails['display_url'])): ?>
                                                    <tr>
                                                        <th>Display URL</th>
                                                        <td><?= esc($adDetails['display_url']) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                
                                                <?php if (empty($adDetails['final_urls']) && empty($adDetails['tracking_url_template']) && empty($adDetails['final_url_suffix']) && empty($adDetails['display_url'])): ?>
                                                    <tr>
                                                        <td colspan="2" class="text-center">Không có thông tin URL nào</td>
                                                    </tr>
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

<style>
.ad-preview {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    background-color: #f9f9f9;
    max-width: 600px;
    margin: 0 auto;
}

.ad-headline {
    color: #1a0dab;
    font-size: 18px;
    margin-bottom: 5px;
    font-weight: bold;
}

.ad-headline-part3 {
    color: #1a0dab;
    font-size: 16px;
    margin-bottom: 5px;
}

.ad-display-url {
    color: #006621;
    font-size: 14px;
    margin-bottom: 5px;
}

.ad-description {
    color: #545454;
    font-size: 14px;
    line-height: 1.4;
}
</style>

<?= $this->include('templates/footer') ?>