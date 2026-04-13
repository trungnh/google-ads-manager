<!-- app/Views/ads_accounts/index.php -->
<?= $this->include('templates/header') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-bold">Quản lý Báo cáo Doanh thu</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createReportModal">
                <i class="fas fa-plus me-1"></i> Tạo Báo cáo Mới
            </button>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success bg-gradient-success text-white border-0 alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger bg-gradient-danger text-white border-0 alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-3">Bộ lọc</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <form id="filterForm" class="p-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="filter_month" class="form-label">Tháng</label>
                                    <select class="form-control" id="filter_month" name="month">
                                        <option value="">Tất cả</option>
                                        <?php
                                        $currentYear = date('Y');
                                        $lastYear = $currentYear - 1;
                                        $options = [];
                                        for ($m = 1; $m <= 12; $m++) {
                                            $options[] = sprintf("%02d-%d", $m, $currentYear);
                                        }
                                        for ($m = 1; $m <= 12; $m++) {
                                            $options[] = sprintf("%02d-%d", $m, $lastYear);
                                        }
                                        foreach ($options as $opt):
                                            list($mo, $yr) = explode('-', $opt);
                                            ?>
                                            <option value="<?= $opt ?>" <?= (isset($filterMonth) && $filterMonth == $opt) ? 'selected' : '' ?>>
                                                Tháng <?= $mo ?> Năm <?= $yr ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="filter_product" class="form-label">Sản phẩm</label>
                                    <select class="form-control" id="filter_product" name="product_id">
                                        <option value="">Tất cả</option>
                                        <?php if (!empty($products))
                                            foreach ($products as $p): ?>
                                                <option value="<?= $p['id'] ?>" <?= (isset($filterProductId) && $filterProductId == $p['id']) ? 'selected' : '' ?>>
                                                    <?= esc($p['name']) ?> (<?= esc($p['product_code']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="report_by_time_checkbox" name="report_by_time" <?= (isset($reportByTime) && $reportByTime) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="report_by_time_checkbox">Báo cáo theo thời gian</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4" id="time_range_filter_section" style="display: <?= (isset($reportByTime) && $reportByTime) ? 'block' : 'none' ?>;">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Chọn thời gian</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <form id="timeRangeFilterForm" class="p-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_month" class="form-label">Tháng Bắt đầu</label>
                                    <select class="form-control" id="start_month" name="start_month">
                                        <?php
                                        foreach ($options as $opt):
                                            list($mo, $yr) = explode('-', $opt);
                                            ?>
                                            <option value="<?= $opt ?>" <?= (isset($filterStartMonth) && $filterStartMonth == $opt) ? 'selected' : '' ?>>
                                                Tháng <?= $mo ?> Năm <?= $yr ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_month" class="form-label">Tháng Kết thúc</label>
                                    <select class="form-control" id="end_month" name="end_month">
                                        <?php
                                        foreach ($options as $opt):
                                            list($mo, $yr) = explode('-', $opt);
                                            ?>
                                            <option value="<?= $opt ?>" <?= (isset($filterEndMonth) && $filterEndMonth == $opt) ? 'selected' : '' ?>>
                                                Tháng <?= $mo ?> Năm <?= $yr ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Xem báo cáo</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table-dark-custom thead th {
            background-color: #1c345d !important;
            color: white !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            padding: 12px 15px;
        }
        .total-row {
            background-color: #f8f9fe;
            font-weight: bold;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Danh sách báo cáo</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0 table-dark-custom">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Tên Báo Cáo</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">THÁNG</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">SẢN PHẨM</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">TỔNG ĐƠN</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">TIỀN ADS</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">LỢI NHUẬN</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">DOANH THU</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7 text-center">ROAS</th>
                                    <th class="opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reports)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">Chưa có báo cáo nào.</td>
                                    </tr>
                                <?php else: 
                                    $grandOrders = 0;
                                    $grandAdsCost = 0;
                                    $grandProfit = 0;
                                    $grandRevenue = 0;
                                    foreach ($reports as $index => $r): 
                                        $grandOrders += $r['total_orders'];
                                        $grandAdsCost += $r['total_ads_cost'];
                                        $grandProfit += $r['total_profit'];
                                        $grandRevenue += $r['total_revenue'];
                                        $roas = $r['total_ads_cost'] > 0 ? $r['total_revenue'] / $r['total_ads_cost'] : 0;
                                        $profitPercent = $r['total_revenue'] > 0 ? ($r['total_profit'] / $r['total_revenue']) * 100 : 0;
                                ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="text-xs font-weight-bold"><?= $index + 1 ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <span class="text-sm font-weight-bold">
                                                    <a href="<?= base_url('revenue_reports/edit/' . $r['id']) ?>" class="text-xs mb-0" style="text-decoration: none; color: #2dce89;">
                                                    <?= esc($r['name']) ?>
                                                    </a>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold text-primary">
                                                <?= $r['year'] . '-' . $r['month'] ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold" style="color: #5e72e4;">
                                                <?= esc($r['product_name']) ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold"><?= number_format($r['total_orders'], 0) ?></span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold text-secondary">
                                                <?= $r['total_ads_cost'] > 0 ? number_format($r['total_ads_cost'], 0, ',', '.') : '-' ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold fw-bold <?= $r['total_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= $r['total_profit'] != 0 ? number_format($r['total_profit'], 0, ',', '.') : '-' ?>
                                                <?php if ($r['total_revenue'] > 0): ?>
                                                    <small class="text-xs text-muted <?= $r['total_profit'] >= 0 ? 'text-success' : 'text-danger' ?>"">(<?= number_format($profitPercent, 1) ?>%)</small>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold fw-bold text-danger">
                                                <?= $r['total_revenue'] > 0 ? number_format($r['total_revenue'], 0, ',', '.') : '-' ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold fw-bold"><?= $roas > 0 ? number_format($roas, 2) : '-' ?></span>
                                        </td>
                                        <td class="align-middle text-end px-4">
                                            <div class="dropdown">
                                                <a href="javascript:;" class="text-secondary" id="dropdownMenuButton<?= $r['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?= $r['id'] ?>">
                                                    <li><a class="dropdown-item" href="<?= base_url('revenue_reports/edit/' . $r['id']) ?>">Xem / Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="javascript:;" onclick="if(confirm('Bạn có chắc chắn muốn xóa báo cáo này?')) window.location.href='<?= base_url('revenue_reports/delete/' . $r['id']) ?>'">Xóa</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                    <tr class="total-row">
                                        <td colspan="4" class="text-end pe-4">Tổng:</td>
                                        <td class="text-center"><?= number_format($grandOrders, 0) ?></td>
                                        <td class="text-center"><?= $grandAdsCost > 0 ? number_format($grandAdsCost, 0, ',', '.') : '-' ?></td>
                                        <td class="text-center <?= $grandProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $grandProfit != 0 ? number_format($grandProfit, 0, ',', '.') : '-' ?>
                                        </td>
                                        <td class="text-center text-danger"><?= $grandRevenue > 0 ? number_format($grandRevenue, 0, ',', '.') : '-' ?></td>
                                        <td class="text-center">
                                            <?= $grandAdsCost > 0 ? number_format($grandRevenue / $grandAdsCost, 2) : '-' ?>
                                        </td>
                                        <td></td>
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

<!-- Create Modal -->
<div class="modal fade" id="createReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #1e1e2d; border: 1px solid #2b2b40;">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title text-white">Tạo Báo Cáo Tháng Mới</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"
                    style="background: none; opacity: 1;"><i class="fas fa-times"></i></button>
            </div>
            <form action="<?= base_url('revenue_reports/create') ?>" method="POST">
                <div class="modal-body pb-0">
                    <div class="mb-3">
                        <label class="form-label text-white">Chọn Sản Phẩm</label>
                        <select name="product_id" class="form-control"
                            style="background-color: #151521; border-color: #2b2b40; color: white;" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            <?php if (!empty($products))
                                foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= esc($p['name']) ?> (
                                        <?= esc($p['product_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Chọn Tháng/Năm</label>
                        <select name="month_year" class="form-control"
                            style="background-color: #151521; border-color: #2b2b40; color: white;" required>
                            <?php
                            $currentYear = date('Y');
                            $lastYear = $currentYear - 1;
                            $options = [];
                            // 2 tháng cuối năm ngoái
                            for ($m = 11; $m <= 12; $m++) {
                                $options[] = sprintf("%02d-%d", $m, $lastYear);
                            }
                            // 12 tháng năm nay
                            for ($m = 1; $m <= 12; $m++) {
                                $options[] = sprintf("%02d-%d", $m, $currentYear);
                            }

                            $currentMonthYear = date('m-Y');
                            foreach ($options as $opt):
                                list($mo, $yr) = explode('-', $opt);
                                ?>
                                <option value="<?= $opt ?>" <?= $opt === $currentMonthYear ? 'selected' : '' ?>>
                                    Tháng
                                    <?= $mo ?> Năm
                                    <?= $yr ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="submit" class="btn btn-primary w-100">Tạo Báo Cáo</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->include('templates/footer') ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const reportByTimeCheckbox = document.getElementById('report_by_time_checkbox');
        const timeRangeFilterSection = document.getElementById('time_range_filter_section');
        const filterForm = document.getElementById('filterForm');
        const timeRangeFilterForm = document.getElementById('timeRangeFilterForm');

        // Function to update URL parameters
        function updateUrlParams(formId) {
            const form = document.getElementById(formId);
            const formData = new FormData(form);
            const params = new URLSearchParams(window.location.search);

            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.set(key, value);
                } else {
                    params.delete(key);
                }
            }

            // Handle checkbox specifically
            if (formId === 'filterForm') {
                if (reportByTimeCheckbox.checked) {
                    params.set('report_by_time', 'true');
                } else {
                    params.delete('report_by_time');
                    // Clear time range filters if checkbox is unchecked
                    params.delete('start_month');
                    params.delete('end_month');
                }
            }
            
            window.location.search = params.toString();
        }

        // Event listener for filter form (month, product, report_by_time checkbox)
        filterForm.addEventListener('change', function () {
            updateUrlParams('filterForm');
        });

        // Event listener for time range filter form (start_month, end_month, submit button)
        timeRangeFilterForm.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent default form submission
            updateUrlParams('timeRangeFilterForm');
        });
    });
</script>