<?= $this->include('templates/header') ?>

<style>
    .overview-card {
        background-color: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        height: 100%;
        position: relative;
    }
    .overview-card h6 {
        color: #8898aa;
        text-transform: uppercase;
        font-size: 0.8rem;
        margin-bottom: 5px;
        font-weight: 600;
    }
    .overview-card .value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .overview-card .comparison {
        font-size: 0.85rem;
    }
    .overview-card .comparison.up {
        color: #2dce89;
    }
    .overview-card .comparison.down {
        color: #f5365c;
    }
    .overview-card .icon-box {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        top: 20px;
        right: 20px;
        color: white;
    }
    .icon-orders { background-color: #f5365c; }
    .icon-revenue { background-color: #fb6340; }
    .icon-ads { background-color: #2dce89; }
    .icon-profit { background-color: #11cdef; }

    .table-dark-custom thead th {
        background-color: #1c345d !important;
        color: white !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
        padding: 12px 15px;
    }
</style>

<div class="container-fluid py-4" style="background-color: #5e72e4; min-height: 100vh;">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="text-white mb-0">Tổng quan</h4>
        </div>
        <div class="col-md-8">
            <form action="<?= base_url('revenue_reports/overview') ?>" method="GET" class="d-flex justify-content-end gap-2">
                <select name="month_year" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <?php
                    $currentYear = date('Y');
                    $lastYear = $currentYear - 1;
                    $options = [];
                    for ($m = 12; $m >= 1; $m--) { $options[] = sprintf("%02d-%d", $m, $currentYear); }
                    for ($m = 12; $m >= 1; $m--) { $options[] = sprintf("%02d-%d", $m, $lastYear); }
                    
                    foreach ($options as $opt):
                        list($mo, $yr) = explode('-', $opt);
                    ?>
                        <option value="<?= $opt ?>" <?= $opt === $monthYear ? 'selected' : '' ?>>
                            Tháng <?= $mo ?> Năm <?= $yr ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="overview-card">
                <h6>TỔNG ĐƠN</h6>
                <div class="value"><?= number_format($totals['orders'], 0, ',', '.') ?></div>
                <div class="icon-box icon-orders">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="comparison <?= $comparison['orders'] >= 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $comparison['orders'] >= 0 ? 'up' : 'down' ?>"></i>
                    <?= $comparison['orders'] !== null ? number_format(abs($comparison['orders']), 1) . '%' : 'NaN%' ?>
                    <span class="text-muted ms-1">So với tháng trước</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="overview-card">
                <h6>DOANH THU</h6>
                <div class="value"><?= $totals['revenue'] > 0 ? number_format($totals['revenue'], 0, ',', '.') . ' đ' : '-' ?></div>
                <div class="icon-box icon-revenue">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="comparison <?= $comparison['revenue'] >= 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $comparison['revenue'] >= 0 ? 'up' : 'down' ?>"></i>
                    <?= $comparison['revenue'] !== null ? number_format(abs($comparison['revenue']), 1) . '%' : 'NaN%' ?>
                    <span class="text-muted ms-1">So với tháng trước</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="overview-card">
                <h6>TIỀN ADS</h6>
                <div class="value"><?= $totals['ads_cost'] > 0 ? number_format($totals['ads_cost'], 0, ',', '.') . ' đ' : '-' ?></div>
                <div class="icon-box icon-ads">
                    <i class="fas fa-ad"></i>
                </div>
                <div class="comparison <?= $comparison['ads_cost'] <= 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $comparison['ads_cost'] <= 0 ? 'down' : 'up' ?>"></i>
                    <?= $comparison['ads_cost'] !== null ? number_format(abs($comparison['ads_cost']), 1) . '%' : 'NaN%' ?>
                    <span class="text-muted ms-1">So với tháng trước</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="overview-card">
                <h6>LỢI NHUẬN</h6>
                <div class="value">
                    <?= number_format($totals['profit'], 0, ',', '.') ?> đ
                    <small class="text-muted" style="font-size: 0.9rem;">
                        (<?= $totals['revenue'] > 0 ? number_format(($totals['profit'] / $totals['revenue']) * 100, 1) : 0 ?>%)
                    </small>
                </div>
                <div class="icon-box icon-profit">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="comparison <?= $comparison['profit'] >= 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $comparison['profit'] >= 0 ? 'up' : 'down' ?>"></i>
                    <?= $comparison['profit'] !== null ? number_format(abs($comparison['profit']), 1) . '%' : 'NaN%' ?>
                    <span class="text-muted ms-1">So với tháng trước</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5 class="text-white mb-3">Tổng quan theo ngày</h5>
            <div class="card shadow">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush mb-0 table-dark-custom">
                        <thead>
                            <tr>
                                <th>NGÀY</th>
                                <th class="text-center">ĐƠN</th>
                                <th class="text-center">SL</th>
                                <th class="text-end">TIỀN HÀNG</th>
                                <th class="text-end">TIỀN ADS</th>
                                <th class="text-end">VẬN CHUYỂN</th>
                                <th class="text-end">TIỀN HOÀN</th>
                                <th class="text-end">TỔNG CHI</th>
                                <th class="text-end">DOANH THU</th>
                                <th class="text-end">LỢI NHUẬN</th>
                                <th class="text-center">%ADS</th>
                                <th class="text-center">ROAS</th>
                            </tr>
                        </thead>
                        <tbody style="background-color: white;">
                            <?php if (empty($dailyData)): ?>
                                <tr>
                                    <td colspan="12" class="text-center py-4 text-muted">Không có dữ liệu cho tháng này.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dailyData as $day): 
                                    $adsPercent = $day['revenue'] > 0 ? ($day['ads_cost'] / $day['revenue']) * 100 : 0;
                                    $roas = $day['ads_cost'] > 0 ? $day['revenue'] / $day['ads_cost'] : 0;
                                ?>
                                    <tr>
                                        <td class="font-weight-bold"><?= date('d/m/Y', strtotime($day['date'])) ?></td>
                                        <td class="text-center"><?= number_format($day['orders'], 0) ?></td>
                                        <td class="text-center"><?= number_format($day['quantity'], 0) ?></td>
                                        <td class="text-end"><?= number_format($day['goods_cost'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($day['ads_cost'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($day['ship_cost'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($day['return_cost'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($day['total_cost'], 0, ',', '.') ?></td>
                                        <td class="text-end font-weight-bold fw-bold" style="color: #f5365c;"><?= number_format($day['revenue'], 0, ',', '.') ?></td>
                                        <td class="text-end font-weight-bold fw-bold" style="color: #2dce89;">
                                            <?= number_format($day['profit'], 0, ',', '.') ?>
                                            <?php if ($day['revenue'] > 0): ?>
                                                <small class="text-xs text-muted fw-bold <?= $day['profit'] >= 0 ? 'text-success' : 'text-danger' ?>"">(<?= number_format(($day['profit'] / $day['revenue']) * 100, 1) ?>%)</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= number_format($adsPercent, 1) ?>%</td>
                                        <td class="text-center font-weight-bold fw-bold"><?= number_format($roas, 2) ?></td>
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

<?= $this->include('templates/footer') ?>
