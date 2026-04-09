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

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tên
                                        Báo Cáo</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Sản phẩm</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                                        Tháng/Năm</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                                        Ngày tạo</th>
                                    <th class="text-secondary opacity-7 text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reports)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">Chưa có báo cáo nào.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($reports as $r): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <span class="text-sm font-weight-bold">
                                                    <a href="<?= base_url('revenue_reports/edit/' . $r['id']) ?>" class="text-xs mb-0" style="text-decoration: none;">
                                                    <?= esc($r['name']) ?>
                                                    </a>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">
                                                <?= esc($r['product_name']) ?>
                                            </p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm">
                                                <?= str_pad($r['month'], 2, '0', STR_PAD_LEFT) . '/' . $r['year'] ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm">
                                                <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-end px-4">
                                            <a href="<?= base_url('revenue_reports/edit/' . $r['id']) ?>"
                                                class="btn btn-sm btn-info text-xs mb-0">Xem / Sửa Báo Cáo</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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