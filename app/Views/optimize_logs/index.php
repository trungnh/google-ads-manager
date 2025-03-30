<!-- app/Views/settings/index.php -->
<?= $this->include('templates/header') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lịch sử tối ưu chiến dịch</h3>
                </div>
                <div class="card-body">
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
                                    <th>Chiến dịch</th>
                                    <th>Hành động</th>
                                    <th>Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Không có dữ liệu</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                            <td><?= esc($log['customer_id']) ?></td>
                                            <td><?= esc($log['campaign_name']) ?></td>
                                            <td>
                                                <?php if ($log['action'] === 'pause'): ?>
                                                    <span class="badge bg-danger">Tạm dừng</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Tăng ngân sách</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($log['details']) ?></td>
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