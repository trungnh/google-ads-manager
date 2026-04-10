<!-- app/Views/ads_accounts/index.php -->
<?= $this->include('templates/header') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-bold">Quản lý Sản phẩm</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProductModal">
                <i class="fas fa-plus me-1"></i> Thêm Sản phẩm
            </button>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success bg-gradient-success border-0 alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger bg-gradient-danger border-0 alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mã
                                        SP</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Tên sản phẩm</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-end">
                                        Giá nhập (VNĐ)</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-end">
                                        Giá bán (VNĐ)</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-end">
                                        Ship (VNĐ)</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">
                                        % Hoàn</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Tài khoản Ads</th>
                                    <th class="text-secondary opacity-7 text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">Chưa có sản phẩm nào.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <span class="text-sm font-weight-bold"
                                                    style="">
                                                    <?= esc($p['product_code']) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">
                                                <?= esc($p['name']) ?>
                                            </p>
                                        </td>
                                        <td class="align-middle text-end">
                                            <span class="text-sm font-weight-bold">
                                                <?= number_format($p['import_price'], 0, ',', '.') ?> đ
                                            </span>
                                        </td>
                                        <td class="align-middle text-end">
                                            <span class="text-sm font-weight-bold">
                                                <?= number_format($p['selling_price'], 0, ',', '.') ?> đ
                                            </span>
                                        </td>
                                        <td class="align-middle text-end">
                                            <span class="text-sm">
                                                <?= number_format($p['shipping_fee'], 0, ',', '.') ?> đ
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-sm font-weight-bold"
                                                style="color: #FF5E5E!important">
                                                <?= rtrim(rtrim(number_format($p['return_rate'] * 100, 2), '0'), '.') ?>%
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <?php
                                            $mappedAccounts = $p['ads_accounts'] ? explode(',', $p['ads_accounts']) : [];
                                            if (!empty($mappedAccounts)) {
                                                foreach ($mappedAccounts as $acc) {
                                                    $name = isset($adsAccountMap[$acc]) ? $adsAccountMap[$acc] : $acc;
                                                    echo '<span class="badge badge-success badge-sm bg-gradient-info me-1 mb-1">' . $name . '</span>';
                                                }
                                            } else {
                                                echo '<span class="text-xs text-secondary">Chưa map tài khoản</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="align-middle text-end px-4">
                                            <a href="javascript:;" class="text-info font-weight-bold text-xs me-3"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editProductModal<?= $p['id'] ?>">Sửa</a>
                                            <a href="<?= base_url('products/delete/' . $p['id']) ?>"
                                                class="text-danger font-weight-bold text-xs"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal for this product -->
                                    <div class="modal fade" id="editProductModal<?= $p['id'] ?>" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content"
                                                style="background-color: #1e1e2d; border: 1px solid #2b2b40;">
                                                <div class="modal-header border-bottom-0">
                                                    <h5 class="modal-title text-white">Sửa Sản phẩm</h5>
                                                    <button type="button" class="btn-close text-white"
                                                        data-bs-dismiss="modal" aria-label="Close"
                                                        style="background: none; opacity: 1;"><i
                                                            class="fas fa-times"></i></button>
                                                </div>
                                                <form action="<?= base_url('products/update/' . $p['id']) ?>" method="POST">
                                                    <div class="modal-body pb-0">
                                                        <div class="mb-3">
                                                            <label class="form-label text-white">Mã SP (Dùng để
                                                                mapping)</label>
                                                            <input type="text" name="product_code" class="form-control"
                                                                style="background-color: #151521; border-color: #2b2b40; color: white;"
                                                                value="<?= esc($p['product_code']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-white">Tên SP</label>
                                                            <input type="text" name="name" class="form-control"
                                                                style="background-color: #151521; border-color: #2b2b40; color: white;"
                                                                value="<?= esc($p['name']) ?>" required>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label text-white">Giá nhập (VNĐ)</label>
                                                                <input type="number" name="import_price"
                                                                    class="form-control"
                                                                    style="background-color: #151521; border-color: #2b2b40; color: white;"
                                                                    value="<?= rtrim(rtrim($p['import_price'], '0'), '.') ?>"
                                                                    required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label text-white">Giá bán (VNĐ)</label>
                                                                <input type="number" name="selling_price"
                                                                    class="form-control"
                                                                    style="background-color: #151521; border-color: #2b2b40; color: white;"
                                                                    value="<?= rtrim(rtrim($p['selling_price'], '0'), '.') ?>"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label text-white">Phí Ship
                                                                    (VNĐ/đơn)</label>
                                                                <input type="number" name="shipping_fee"
                                                                    class="form-control"
                                                                    style="background-color: #151521; border-color: #2b2b40; color: white;"
                                                                    value="<?= rtrim(rtrim($p['shipping_fee'], '0'), '.') ?>"
                                                                    required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label text-white">Tỉ lệ hoàn (số thập
                                                                    phân, VD: 0.15)</label>
                                                                <input type="number" step="0.001" name="return_rate"
                                                                    class="form-control"
                                                                    style="background-color: #151521; border-color: #2b2b40; color: white;"
                                                                    value="<?= rtrim(rtrim($p['return_rate'], '0'), '.') ?>"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-white">Keyword Campaign (Lọc campaign theo từ khoá)</label>
                                                            <input type="text" name="keyword_campaign" class="form-control"
                                                                style="background-color: #151521; border-color: #2b2b40; color: white;"
                                                                value="<?= esc($p['keyword_campaign']) ?>">
                                                            <small class="text-muted">Dùng để lọc các chiến dịch có tên chứa từ khoá này</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-white">Mapping Tài Khoản
                                                                Ads</label>
                                                            <p class="text-xs text-muted mb-1">Giữ Ctrl / Cmd để chọn nhiều
                                                            </p>
                                                            <select name="customer_ids[]" class="form-control" multiple
                                                                style="background-color: #151521; border-color: #2b2b40; color: white; height: 120px;">
                                                                <?php foreach ($adsAccounts as $acc): ?>
                                                                    <option value="<?= $acc['customer_id'] ?>"
                                                                        <?= in_array($acc['customer_id'], $mappedAccounts) ? 'selected' : '' ?>>
                                                                        <?= esc($acc['customer_name']) ?> (
                                                                        <?= $acc['customer_id'] ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top-0 pt-0">
                                                        <button type="submit" class="btn btn-primary w-100">Lưu thay
                                                            đổi</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
<div class="modal fade" id="createProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #1e1e2d; border: 1px solid #2b2b40;">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title text-white">Thêm Sản phẩm Mới</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"
                    style="background: none; opacity: 1;"><i class="fas fa-times"></i></button>
            </div>
            <form action="<?= base_url('products/create') ?>" method="POST">
                <div class="modal-body pb-0">
                    <div class="mb-3">
                        <label class="form-label text-white">Mã SP (Dùng để mapping)</label>
                        <input type="text" name="product_code" class="form-control"
                            style="background-color: #151521; border-color: #2b2b40; color: white;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white">Tên SP</label>
                        <input type="text" name="name" class="form-control"
                            style="background-color: #151521; border-color: #2b2b40; color: white;" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Giá nhập (VNĐ)</label>
                            <input type="number" name="import_price" class="form-control"
                                style="background-color: #151521; border-color: #2b2b40; color: white;" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Giá bán (VNĐ)</label>
                            <input type="number" name="selling_price" class="form-control"
                                style="background-color: #151521; border-color: #2b2b40; color: white;" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Phí Ship (VNĐ/đơn)</label>
                            <input type="number" name="shipping_fee" class="form-control"
                                style="background-color: #151521; border-color: #2b2b40; color: white;" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-white">Tỉ lệ hoàn (0.15 = 15%)</label>
                            <input type="number" step="0.001" name="return_rate" class="form-control"
                                style="background-color: #151521; border-color: #2b2b40; color: white;"
                                placeholder="0.000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white">Keyword Campaign (Lọc campaign theo từ khoá)</label>
                        <input type="text" name="keyword_campaign" class="form-control"
                            style="background-color: #151521; border-color: #2b2b40; color: white;">
                        <small class="text-muted">Dùng để lọc các chiến dịch có tên chứa từ khoá này</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white">Mapping Tài Khoản Ads</label>
                        <p class="text-xs text-muted mb-1">Giữ Ctrl / Cmd để chọn nhiều</p>
                        <select name="customer_ids[]" class="form-control" multiple
                            style="background-color: #151521; border-color: #2b2b40; color: white; height: 120px;">
                            <?php if (!empty($adsAccounts))
                                foreach ($adsAccounts as $acc): ?>
                                    <option value="<?= $acc['customer_id'] ?>">
                                        <?= esc($acc['customer_name']) ?> (
                                        <?= $acc['customer_id'] ?>)
                                    </option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="submit" class="btn btn-primary w-100">Thêm SP</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->include('templates/footer') ?>