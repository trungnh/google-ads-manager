<?= $this->include('templates/header') ?>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Premium Dashboard Styles (Sharp Geometry & Solid Borders - Purple Ban Compliant) */
    :root {
        --kpi-teal: #10b981;
        --kpi-orange: #fb6340;
        --kpi-blue: #2563eb;
        --kpi-red: #ef4444;
        --kpi-slate: #475569;
        --border-color: rgba(0, 0, 0, 0.08);
    }

    .dashboard-wrapper {
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Sharp Card Design */
    .premium-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 4px !important; /* Sharp technical edges */
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease;
        position: relative;
        overflow: hidden;
    }

    .premium-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }

    /* KPI Border Accent */
    .kpi-accent {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
    }
    
    .accent-spend { background-color: var(--kpi-blue); }
    .accent-discrepancy { background-color: var(--kpi-orange); }
    .accent-cpa { background-color: var(--kpi-red); }
    .accent-profit { background-color: var(--kpi-teal); }

    /* Pulsing status dots */
    .status-pulse-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .pulse-active {
        background-color: var(--kpi-teal);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5);
        animation: pulseGreen 1.8s infinite;
    }

    .pulse-inactive {
        background-color: var(--kpi-slate);
    }

    @keyframes pulseGreen {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* Timeline Styling */
    .custom-timeline {
        position: relative;
        padding-left: 20px;
        border-left: 1px dashed rgba(0, 0, 0, 0.08);
    }

    .timeline-element {
        position: relative;
        padding-bottom: 1.25rem;
    }

    .timeline-element:last-child {
        padding-bottom: 0;
    }

    .timeline-badge {
        position: absolute;
        left: -25px;
        top: 4px;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background-color: #fff;
        border: 2px solid var(--kpi-blue);
    }

    .timeline-badge.badge-success {
        border-color: var(--kpi-teal);
    }

    .timeline-badge.badge-warning {
        border-color: var(--kpi-orange);
    }

    /* Custom Scrollbar for Clean UI */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.02);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 3px;
    }

    .table-leaderboard th {
        font-size: 0.68rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: var(--kpi-slate);
        border-bottom: 1px solid var(--border-color);
    }

    .table-leaderboard td {
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
    }
</style>

<div class="dashboard-wrapper container-fluid py-2">
    
    <!-- TOP TOOL BELT: Filter and Date picker -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark" style="font-size: 1.5rem;">Bảng Điều Khiển Hệ Thống</h3>
            <p class="text-muted small mb-0">
                <i class="far fa-clock me-1"></i> Dữ liệu cập nhật mới nhất lúc: 
                <strong><?= !empty($latestDate) ? date('d/m/Y H:i', strtotime($latestDate)) : date('d/m/Y H:i') ?></strong>
            </p>
        </div>
        
        <!-- Controls Form -->
        <form method="GET" action="<?= base_url('dashboard') ?>" class="d-flex align-items-center gap-2">
            <!-- Account filter -->
            <select name="customer_id" class="form-select form-select-sm premium-card border-secondary-subtle px-3 py-2 fw-medium" onchange="this.form.submit()" style="width: auto; min-width: 200px;">
                <option value="all">📊 Tất cả tài khoản</option>
                <?php foreach ($userAccounts as $acc): ?>
                    <option value="<?= $acc['customer_id'] ?>" <?= $selectedCustomerId === $acc['customer_id'] ? 'selected' : '' ?>>
                        💼 <?= esc($acc['customer_name']) ?> (<?= esc($acc['customer_id']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            
            <!-- Date range selector -->
            <select name="date_range" class="form-select form-select-sm premium-card border-secondary-subtle px-3 py-2 fw-medium" onchange="this.form.submit()" style="width: auto;">
                <option value="today" <?= $selectedDateRange === 'today' ? 'selected' : '' ?>>📅 Hôm nay</option>
                <option value="yesterday" <?= $selectedDateRange === 'yesterday' ? 'selected' : '' ?>>📅 Hôm qua</option>
                <option value="7days" <?= $selectedDateRange === '7days' ? 'selected' : '' ?>>📅 7 ngày qua</option>
                <option value="30days" <?= $selectedDateRange === '30days' ? 'selected' : '' ?>>📅 30 ngày qua</option>
            </select>
        </form>
    </div>

    <!-- INTEGRATION STATUS ROW -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <div class="premium-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="small text-muted d-block">Google Ads Link</span>
                    <strong class="text-dark small">
                        <?= $hasGoogleToken ? 'Đã kết nối OAuth' : 'Chưa kết nối' ?>
                    </strong>
                </div>
                <span class="status-pulse-dot <?= $hasGoogleToken ? 'pulse-active' : 'pulse-inactive' ?>"></span>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="premium-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="small text-muted d-block">Đồng bộ Pancake POS</span>
                    <strong class="text-dark small">
                        <?= $hasPancake ? 'Hoạt động' : 'Tắt / Chưa cấu hình' ?>
                    </strong>
                </div>
                <span class="status-pulse-dot <?= $hasPancake ? 'pulse-active' : 'pulse-inactive' ?>"></span>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="premium-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="small text-muted d-block">Đồng bộ Google Sheets</span>
                    <strong class="text-dark small">
                        <?= $hasGSheet ? 'Đang hoạt động API' : 'Tắt / Chưa cấu hình' ?>
                    </strong>
                </div>
                <span class="status-pulse-dot <?= $hasGSheet ? 'pulse-active' : 'pulse-inactive' ?>"></span>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="premium-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="small text-muted d-block">Tự động tối ưu (Rules)</span>
                    <strong class="text-dark small">
                        <?= $autoOptimizeCount > 0 ? esc($autoOptimizeCount) . ' Tài khoản bật' : 'Tất cả đang tắt' ?>
                    </strong>
                </div>
                <span class="status-pulse-dot <?= $autoOptimizeCount > 0 ? 'pulse-active' : 'pulse-inactive' ?>"></span>
            </div>
        </div>
    </div>

    <!-- MAIN KPI STATS GRID -->
    <div class="row g-3 mb-4">
        <!-- spend card -->
        <div class="col-md-3">
            <div class="premium-card p-3 h-100">
                <div class="kpi-accent accent-spend"></div>
                <div class="ps-2">
                    <span class="text-muted small fw-bold d-block text-uppercase">Chi Tiêu Ads / Ngân Sách</span>
                    <h3 class="fw-bold my-2 text-dark" style="font-size: 1.6rem;"><?= number_format($totalSpend, 0, ',', '.') ?>đ</h3>
                    
                    <?php if ($totalBudget > 0): 
                        $pct = min(100, ($totalSpend / $totalBudget) * 100); 
                    ?>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="text-muted small d-block mt-2">Đã chi <?= number_format($pct, 1) ?>% ngân sách (<?= number_format($totalBudget, 0, ',', '.') ?>đ)</span>
                    <?php else: ?>
                        <span class="text-muted small d-block mt-2">Chưa cấu hình ngân sách mục tiêu</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- discrepancies card => Đơn Hàng CRM -->
        <div class="col-md-3">
            <div class="premium-card p-3 h-100">
                <div class="kpi-accent accent-discrepancy"></div>
                <div class="ps-2">
                    <span class="text-muted small fw-bold d-block text-uppercase">Đơn Hàng CRM (Thực Tế)</span>
                    <h3 class="fw-bold my-2 text-dark" style="font-size: 1.6rem;">
                        <?= number_format($crmConversions, 0, ',', '.') ?> <span class="text-muted fs-6" style="font-size: 0.9rem; font-weight: normal;">đơn</span>
                    </h3>
                    
                    <?php 
                        $gap = $googleConversions - $crmConversions;
                        if ($googleConversions > 0):
                            $gapPct = ($gap / $googleConversions) * 100;
                    ?>
                        <?php if ($gap > 0): ?>
                            <span class="text-danger small fw-semibold"><i class="fas fa-triangle-exclamation me-1"></i> Google báo ảo +<?= number_format($gap, 1) ?> đơn (<?= number_format($gapPct, 1) ?>%)</span>
                        <?php elseif ($gap < 0): ?>
                            <span class="text-success small fw-semibold"><i class="fas fa-circle-down me-1"></i> Google hụt đơn -<?= number_format(abs($gap), 1) ?> đơn (<?= number_format(abs($gapPct), 1) ?>%)</span>
                        <?php else: ?>
                            <span class="text-secondary small fw-semibold"><i class="fas fa-check me-1"></i> Khớp 100% với Google</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted small">Google báo cáo: 0 đơn</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- CPA comparisons card => CPA/ROAS (CRM thực tế) -->
        <div class="col-md-3">
            <div class="premium-card p-3 h-100">
                <div class="kpi-accent accent-cpa"></div>
                <div class="ps-2">
                    <span class="text-muted small fw-bold d-block text-uppercase">CPA / ROAS (CRM Thực Tế)</span>
                    <?php 
                        $realCpa = $crmConversions > 0 ? $totalSpend / $crmConversions : 0;
                        $realRoas = $totalSpend > 0 ? $crmRevenue / $totalSpend : 0;
                    ?>
                    <div class="d-flex justify-content-between align-items-center my-2 pe-1">
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.72rem; font-weight: bold; text-transform: uppercase;">CPA</span>
                            <span class="fw-bold text-dark" style="font-size: 1.25rem;"><?= number_format($realCpa, 0, ',', '.') ?>đ</span>
                        </div>
                        <div class="border-start ps-3">
                            <span class="text-muted d-block" style="font-size: 0.72rem; font-weight: bold; text-transform: uppercase;">ROAS</span>
                            <span class="fw-bold text-success" style="font-size: 1.25rem;"><?= number_format($realRoas, 2, ',', '.') ?>x</span>
                        </div>
                    </div>
                    
                    <?php if ($crmConversions > 0): ?>
                        <span class="text-secondary small">Dựa trên đơn thực tế đối soát</span>
                    <?php else: ?>
                        <span class="text-muted small">Chưa có đơn hàng để tính CPA</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profit card -->
        <div class="col-md-3">
            <div class="premium-card p-3 h-100">
                <div class="kpi-accent accent-profit"></div>
                <div class="ps-2">
                    <span class="text-muted small fw-bold d-block text-uppercase">Lợi Nhuận Net Dự Tính</span>
                    <h3 class="fw-bold my-2 <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>" style="font-size: 1.6rem;">
                        <?= $netProfit >= 0 ? '+' : '' ?><?= number_format($netProfit, 0, ',', '.') ?>đ
                    </h3>
                    <span class="text-muted small d-block">Doanh thu CRM: <strong><?= number_format($crmRevenue, 0, ',', '.') ?>đ</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- GOOGLE ADS OAUTH ALERT STATE -->
    <?php if (!$hasGoogleToken): ?>
        <div class="alert alert-warning border border-warning-subtle premium-card p-3 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x text-warning me-3"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1 text-dark small-text" style="font-size: 1rem;">Chưa Kết Nối Google Ads OAuth</h5>
                    <p class="mb-0 text-muted small">Để hệ thống có thể lấy dữ liệu chiến dịch và đồng bộ các tối ưu, bạn cần kết nối tài khoản Google Ads trước.</p>
                </div>
            </div>
            <a href="<?= base_url('google/oauth') ?>" class="btn btn-sm btn-primary px-3 py-2 text-uppercase fw-semibold" style="font-size: 0.78rem;">Kết nối ngay</a>
        </div>
    <?php endif; ?>

    <!-- GRAPH & AUDIT TIMELINE ROW -->
    <div class="row g-4 mb-4">
        <!-- Trend chart.js -->
        <div class="col-md-8">
            <div class="premium-card p-3 h-100">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold text-dark m-0" style="font-size: 1.05rem;">Xu Hướng Vận Hành 7 Ngày</h5>
                        <span class="text-muted small" id="chartDesc">So sánh Doanh thu thực tế vs Chi tiêu Ads</span>
                    </div>
                    <!-- Premium Tabs Selector -->
                    <div class="btn-group btn-group-sm" role="group" aria-label="Chart metrics selector">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-2 active" id="tabCostRev" onclick="switchChartMetric('cost_rev')" style="font-size: 0.72rem; font-weight: bold; border-radius: 2px 0 0 2px;">📈 Tiền</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary px-2" id="tabOrders" onclick="switchChartMetric('orders')" style="font-size: 0.72rem; font-weight: bold; border-radius: 0;">📦 Đơn hàng</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary px-2" id="tabCpa" onclick="switchChartMetric('cpa')" style="font-size: 0.72rem; font-weight: bold; border-radius: 0 2px 2px 0;">🏷️ CPA</button>
                    </div>
                </div>
                <div style="height: 310px; width: 100%;">
                    <canvas id="discrepancyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Actions Widget -->
        <div class="col-md-4">
            <div class="premium-card p-3 h-100">
                <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">Nhật Ký Tối Ưu Tự Động</h5>
                <span class="text-muted small d-block mb-3">Các hành động tự động hóa của Rule Engine</span>
                
                <div class="custom-timeline ps-2 custom-scrollbar overflow-auto" style="max-height: 290px;">
                    <?php if (!empty($recentLogs)): ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <div class="timeline-element">
                                <span class="timeline-badge <?= stripos($log['action'], 'pause') !== false ? 'badge-warning' : 'badge-success' ?>"></span>
                                <div class="ms-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-dark small"><?= esc($log['action']) ?></strong>
                                        <span class="text-muted" style="font-size: 0.72rem;"><?= date('H:i d/m', strtotime($log['created_at'])) ?></span>
                                    </div>
                                    <p class="text-muted small mb-1 mt-1">
                                        Chiến dịch: <strong><?= esc($log['campaign_name']) ?></strong>
                                    </p>
                                    <span class="small bg-light border border-secondary-subtle px-2 py-1 d-inline-block text-dark mt-1" style="font-size: 0.75rem; border-radius: 2px;">
                                        <?= esc($log['details']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shield-cat fa-3x text-muted mb-2"></i>
                            <p class="text-muted small mb-0">Hệ thống chưa ghi nhận lần kích hoạt tối ưu tự động nào gần đây.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CAMPAIGN LEADERBOARDS ROW -->
    <?php
        $dateLabel = '';
        if ($selectedDateRange === 'today') {
            $dateLabel = 'ngày hôm nay: ' . (!empty($latestDate) ? date('d/m/Y', strtotime($latestDate)) : date('d/m/Y'));
        } elseif ($selectedDateRange === 'yesterday') {
            $prevDate = !empty($latestDate) ? date('d/m/Y', strtotime($latestDate . ' -1 day')) : date('d/m/Y', strtotime('-1 day'));
            $dateLabel = 'ngày hôm qua: ' . $prevDate;
        } elseif ($selectedDateRange === '7days') {
            $dateLabel = '7 ngày qua';
        } elseif ($selectedDateRange === '30days') {
            $dateLabel = '30 ngày qua';
        } else {
            $dateLabel = 'ngày hiện tại';
        }
    ?>
    <div class="row g-4 mb-3">
        <!-- Top performing campaigns -->
        <div class="col-md-6">
            <div class="premium-card h-100">
                <div class="card-header border-bottom p-3">
                    <h5 class="fw-bold text-dark m-0" style="font-size: 1.05rem;">
                        <i class="fas fa-trophy text-warning me-2"></i>Top Chiến Dịch Hiệu Quả Nhất
                    </h5>
                    <span class="text-muted small">Chiến dịch đem lại nhiều đơn thực tế nhất (Dữ liệu <?= $dateLabel ?>)</span>
                </div>
                <div class="table-responsive custom-scrollbar" style="max-height: 350px;">
                    <table class="table table-leaderboard table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 50%;">Chiến dịch</th>
                                <th class="text-center" style="width: 15%;">Chi Tiêu</th>
                                <th class="text-center" style="width: 15%;">CRM Đơn</th>
                                <th class="text-center" style="width: 20%;">Doanh Thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topCampaigns)): ?>
                                <?php foreach ($topCampaigns as $camp): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 240px;" title="<?= esc($camp['name']) ?>">
                                                <?= esc($camp['name']) ?>
                                            </div>
                                            <span class="text-muted" style="font-size: 0.72rem;">
												ID: <?= esc($camp['campaign_id']) ?> - 
												<span class="badge <?php echo $camp['status'] == 'ENABLED' ? 'bg-success' : 'bg-warning'?> status-badge">
													<?php echo $camp['status'] == 'ENABLED' ? 'Đang chạy' : 'Tạm dừng'?>
												</span>
											</span>
                                        </td>
                                        <td class="text-center text-secondary font-monospace fw-semibold"><?= number_format($camp['cost'], 0, ',', '.') ?>đ</td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-1">
                                                <?= number_format($camp['real_conversions'], 0, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td class="text-center text-dark font-monospace fw-bold"><?= number_format($camp['real_conversion_value'], 0, ',', '.') ?>đ</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted small">
                                        Không có chiến dịch nào được xếp hạng
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Problem/High-risk campaigns -->
        <div class="col-md-6">
            <div class="premium-card h-100">
                <div class="card-header border-bottom p-3">
                    <h5 class="fw-bold text-dark m-0" style="font-size: 1.05rem;">
                        <i class="fas fa-triangle-exclamation text-danger me-2"></i>Top Chiến Dịch Lãng Phí (Cảnh báo)
                    </h5>
                    <span class="text-muted small">Tiêu tiền nhưng 0 đơn thực tế trên CRM (Dữ liệu <?= $dateLabel ?>)</span>
                </div>
                <div class="table-responsive custom-scrollbar" style="max-height: 350px;">
                    <table class="table table-leaderboard table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 50%;">Chiến dịch</th>
                                <th class="text-center" style="width: 20%;">Chi Tiêu</th>
                                <th class="text-center" style="width: 15%;">GG Đơn Báo</th>
                                <th class="text-center" style="width: 15%;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($problemCampaigns)): ?>
                                <?php foreach ($problemCampaigns as $camp): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 240px;" title="<?= esc($camp['name']) ?>">
                                                <?= esc($camp['name']) ?> 
                                            </div>
                                            <span class="text-muted" style="font-size: 0.72rem;">
												ID: <?= esc($camp['campaign_id']) ?> - 
												<span class="badge <?php echo $camp['status'] == 'ENABLED' ? 'bg-success' : 'bg-warning'?> status-badge">
													<?php echo $camp['status'] == 'ENABLED' ? 'Đang chạy' : 'Tạm dừng'?>
												</span>
											</span>
                                        </td>
                                        <td class="text-center text-danger font-monospace fw-bold"><?= number_format($camp['cost'], 0, ',', '.') ?>đ</td>
                                        <td class="text-center text-secondary fw-semibold"><?= number_format($camp['conversions'], 1, ',', '.') ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('campaigns/index/' . $camp['customer_id']) ?>" class="btn btn-outline-danger btn-xs py-1 px-2 text-uppercase fw-semibold" style="font-size: 0.68rem; border-radius: 2px;">
                                                Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-success small">
                                        <i class="fas fa-check-circle me-1"></i> Tuyệt vời! Không có chiến dịch nào lãng phí ngân sách hôm nay.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-3">
        <div class="col-md-6">
            <div class="premium-card h-100">
                <div class="card-header border-bottom p-3">
                    <h5 class="fw-bold text-dark m-0" style="font-size: 1.05rem;">
                        <i class="fas fa-triangle-exclamation text-danger me-2"></i>Top Chiến Dịch CPA cao (Cảnh báo)
                    </h5>
                    <span class="text-muted small">Tiêu tiền nhưng kém hiệu quả (Dữ liệu <?= $dateLabel ?>)</span>
                </div>
                <div class="table-responsive custom-scrollbar" style="max-height: 350px;">
                    <table class="table table-leaderboard table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 50%;">Chiến dịch</th>
                                <th class="text-center" style="width: 20%;">Chi Tiêu</th>
                                <th class="text-center" style="width: 15%;">CPA / CRM Đơn</th>
                                <th class="text-center" style="width: 15%;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($problemCampaigns)): ?>
                                <?php foreach ($problemCampaigns as $camp): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 240px;" title="<?= esc($camp['name']) ?>">
                                                <?= esc($camp['name']) ?> 
                                            </div>
                                            <span class="text-muted" style="font-size: 0.72rem;">
												ID: <?= esc($camp['campaign_id']) ?> - 
												<span class="badge <?php echo $camp['status'] == 'ENABLED' ? 'bg-success' : 'bg-warning'?> status-badge">
													<?php echo $camp['status'] == 'ENABLED' ? 'Đang chạy' : 'Tạm dừng'?>
												</span>
											</span>
                                        </td>
                                        <td class="text-center text-danger font-monospace fw-bold"><?= number_format($camp['cost'], 0, ',', '.') ?>đ</td>
                                        <td class="text-center text-secondary fw-semibold">
                                            <?= number_format($camp['real_cpa'], 0, ',', '.') ?>đ
                                            (<?= number_format($camp['conversions'], 1, ',', '.') ?> đơn)
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('campaigns/index/' . $camp['customer_id']) ?>" class="btn btn-outline-danger btn-xs py-1 px-2 text-uppercase fw-semibold" style="font-size: 0.68rem; border-radius: 2px;">
                                                Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-success small">
                                        <i class="fas fa-check-circle me-1"></i> Tuyệt vời! Không có chiến dịch nào lãng phí ngân sách hôm nay.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Render Discrepancy Chart using Chart.js -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('discrepancyChart').getContext('2d');
    
    // Extracted server aggregates
    var chartLabels = <?= json_encode(array_column($chartData, 'date')) ?>;
    var revenueData = <?= json_encode(array_column($chartData, 'revenue')) ?>;
    var costData = <?= json_encode(array_column($chartData, 'cost')) ?>;
    var ordersData = <?= json_encode(array_column($chartData, 'crm_conv')) ?>;
    var cpaData = <?= json_encode(array_column($chartData, 'crm_cpa')) ?>;

    var currentMetric = 'cost_rev';

    // Formatter helpers
    function formatCurrency(val) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(val);
    }

    function formatTick(value) {
        if (value >= 1000000) {
            return (value / 1000000).toFixed(1) + 'M đ';
        } else if (value >= 1000) {
            return (value / 1000).toFixed(0) + 'K đ';
        }
        return value + ' đ';
    }

    var discrepancyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Doanh thu CRM (Thực Tế)',
                    data: revenueData,
                    borderColor: '#10b981', // Emerald Teal
                    backgroundColor: 'rgba(16, 185, 129, 0.04)',
                    borderWidth: 3,
                    pointBackgroundColor: '#10b981',
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'yLeft'
                },
                {
                    label: 'Chi tiêu Google Ads',
                    data: costData,
                    borderColor: '#2563eb', // Cobalt Blue
                    backgroundColor: 'rgba(37, 99, 235, 0.03)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointBackgroundColor: '#2563eb',
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'yRight'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11,
                            family: "'Open Sans', sans-serif"
                        },
                        color: '#475569'
                    }
                },
                tooltip: {
                    padding: 10,
                    backgroundColor: 'rgba(11, 21, 38, 0.95)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 3,
                    callbacks: {
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                var val = context.parsed.y;
                                if (currentMetric === 'cost_rev' || currentMetric === 'cpa') {
                                    label += formatCurrency(val);
                                } else {
                                    label += new Intl.NumberFormat('vi-VN').format(val) + ' đơn';
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 10 }
                    }
                },
                yLeft: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: 'rgba(0, 0, 0, 0.04)'
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 10 },
                        callback: formatTick
                    },
                    title: {
                        display: true,
                        text: 'Doanh Thu CRM (VND)',
                        color: '#64748b',
                        font: { size: 10, weight: 'bold' }
                    }
                },
                yRight: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 10 },
                        callback: formatTick
                    },
                    title: {
                        display: true,
                        text: 'Chi Tiêu Ads (VND)',
                        color: '#64748b',
                        font: { size: 10, weight: 'bold' }
                    }
                }
            }
        }
    });

    window.switchChartMetric = function(metric) {
        currentMetric = metric;

        // Toggle .active class on button tabs
        document.getElementById('tabCostRev').classList.remove('active');
        document.getElementById('tabOrders').classList.remove('active');
        document.getElementById('tabCpa').classList.remove('active');

        var descText = "";
        
        if (metric === 'cost_rev') {
            document.getElementById('tabCostRev').classList.add('active');
            descText = "So sánh Doanh thu thực tế vs Chi tiêu Ads";

            // Update datasets
            discrepancyChart.data.datasets = [
                {
                    label: 'Doanh thu CRM (Thực Tế)',
                    data: revenueData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.04)',
                    borderWidth: 3,
                    pointBackgroundColor: '#10b981',
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'yLeft'
                },
                {
                    label: 'Chi tiêu Google Ads',
                    data: costData,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.03)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointBackgroundColor: '#2563eb',
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'yRight'
                }
            ];

            // Show both axes
            discrepancyChart.options.scales.yLeft.display = true;
            discrepancyChart.options.scales.yLeft.title.text = 'Doanh Thu CRM (VND)';
            discrepancyChart.options.scales.yLeft.ticks.callback = formatTick;

            if (!discrepancyChart.options.scales.yRight) {
                discrepancyChart.options.scales.yRight = {};
            }
            discrepancyChart.options.scales.yRight.display = true;
            discrepancyChart.options.scales.yRight.position = 'right';
            discrepancyChart.options.scales.yRight.ticks = { callback: formatTick, color: '#64748b', font: { size: 10 } };
            discrepancyChart.options.scales.yRight.title = { display: true, text: 'Chi Tiêu Ads (VND)', color: '#64748b', font: { size: 10, weight: 'bold' } };
            discrepancyChart.options.scales.yRight.grid = { drawOnChartArea: false };

        } else if (metric === 'orders') {
            document.getElementById('tabOrders').classList.add('active');
            descText = "Xu hướng Đơn hàng CRM Thực Tế";

            // Update datasets
            discrepancyChart.data.datasets = [
                {
                    label: 'Đơn Hàng CRM',
                    data: ordersData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.04)',
                    borderWidth: 3,
                    pointBackgroundColor: '#10b981',
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'yLeft'
                }
            ];

            // Hide right axis, configure left axis for integers
            discrepancyChart.options.scales.yLeft.display = true;
            discrepancyChart.options.scales.yLeft.title.text = 'Số Lượng Đơn Hàng';
            discrepancyChart.options.scales.yLeft.ticks.callback = function(value) {
                return Number.isInteger(value) ? value : '';
            };

            if (discrepancyChart.options.scales.yRight) {
                discrepancyChart.options.scales.yRight.display = false;
            }

        } else if (metric === 'cpa') {
            document.getElementById('tabCpa').classList.add('active');
            descText = "Xu hướng CPA (CRM Thực Tế) 7 Ngày";

            // Update datasets
            discrepancyChart.data.datasets = [
                {
                    label: 'CPA Thực Tế (CRM)',
                    data: cpaData,
                    borderColor: '#fb6340', // Signal Orange
                    backgroundColor: 'rgba(251, 99, 64, 0.04)',
                    borderWidth: 3,
                    pointBackgroundColor: '#fb6340',
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'yLeft'
                }
            ];

            // Hide right axis, configure left axis for currency
            discrepancyChart.options.scales.yLeft.display = true;
            discrepancyChart.options.scales.yLeft.title.text = 'CPA (VND / Đơn)';
            discrepancyChart.options.scales.yLeft.ticks.callback = formatTick;

            if (discrepancyChart.options.scales.yRight) {
                discrepancyChart.options.scales.yRight.display = false;
            }
        }

        document.getElementById('chartDesc').innerText = descText;
        discrepancyChart.update();
    };
});
</script>

<?= $this->include('templates/footer') ?>