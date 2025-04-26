<!-- app/Views/settings/index.php -->
<?= $this->include('templates/header') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reports</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-end">
                                    <select class="form-select" id="accountSelector" style="width: 300px;" onchange="window.location.href=this.value">
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= base_url('reports/view/' . $user['id']) ?>" <?= $user['id'] == $userId ? 'selected' : '' ?>>
                                                <?= esc($user['username']) ?> - <?= esc($user['id']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <a href="<?= base_url('optimize-logs/view/' . $userId) ?>" class="btn btn-sm btn-info">
                                View Logs
                            </a>
                        </div>
                    </div>
                    <!-- Date Range Filter -->
                    <form method="get" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Từ ngày</label>
                                    <input type="date" name="startDate" class="form-control" value="<?= $startDate ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Đến ngày</label>
                                    <input type="date" name="endDate" class="form-control" value="<?= $endDate ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">Lọc</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Quick Date Selection -->
                    <div class="mb-4">
                        <h5>Chọn nhanh ngày:</h5>
                        <div class="btn-group">
                            <?php foreach ($dates as $dateItem): ?>
                                <a href="?date=<?= $dateItem['date'] ?>" 
                                   class="btn btn-outline-primary <?= $currentDate === $dateItem['date'] ? 'active' : '' ?>">
                                    <?= date('d/m/Y', strtotime($dateItem['date'])) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Logs Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Tài khoản</th>
                                    <th>Tên tài khoản</th>
                                    <th>Cost</th>
                                    <th>ROAS</th>
                                    <th>CPA</th>
                                    <th>Conv</th>
                                    <th>Conv Value</th>
                                    <th>Running</th>
                                    <th>Pause</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reports)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center">Không có dữ liệu</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reports as $report): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s', strtotime($report['created_at'])) ?></td>
                                            <td><?= esc($report['customer_id']) ?></td>
                                            <td><?= esc($report['customer_name']) ?></td>
                                            <td>
                                                <?php 
                                                if ($report['currency_code'] == 'VND') {
                                                    echo number_format($report['cost'], 0, '.', '.') . ' ₫';    
                                                } else {
                                                    echo '$  '.number_format($report['cost'], 2);
                                                }
                                                ?>
                                            </td>
                                            <td><?= $report['cost'] > 0 ? number_format($report['conversion_value']/$report['cost'], 2) : 0; ?></td>
                                            <td>
                                                <?php 
                                                $cpa = $report['conversions'] > 0 ? $report['cost']/$report['conversions'] : 0;
                                                if ($report['currency_code'] == 'VND') {
                                                    echo number_format($cpa, 0, '.', '.') . ' ₫';    
                                                } else {
                                                    echo '$  '.number_format($cpa, 2);
                                                }
                                                ?>
                                            </td>
                                            <td><?= number_format($report['conversions'], 2)?></td>
                                            <td>
                                                <?php 
                                                $cpa = $report['conversions'] > 0 ? $report['cost']/$report['conversions'] : 0;
                                                if ($report['currency_code'] == 'VND') {
                                                    echo number_format($report['conversion_value'], 0, '.', '.') . ' ₫';    
                                                } else {
                                                    echo '$  '.number_format($report['conversion_value'], 2);
                                                }
                                                ?>
                                            </td>
                                            <td><?= number_format($report['running'], 0)?></td>
                                            <td><?= number_format($report['paused'], 0)?></td>
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
<?= $this->include('templates/footer') ?>