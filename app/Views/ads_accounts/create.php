<?= $this->include('templates/header') ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Thêm Ads Account</h4>
                </div>
                <div class="card-body">
                    <?php if (session()->has('success')) : ?>
                        <div class="alert alert-success">
                            <?= session('success') ?>                            
                        </div>
                    <?php endif; ?>

                    <?php if (session()->has('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session('error') ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('ads-accounts/store') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="alert alert-info mt-4">
                            <h5>Lưu ý:</h5>
                            <ul>
                                <li>Đảm bảo tài khoản Google mà bạn đã kết nối có quyền thao tác với tài khoản ads này.</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Customer ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.customer_id') ? 'is-invalid' : '' ?>" 
                                   id="customer_id" name="customer_id" value="<?= old('customer_id') ?>" required>
                            <?php if (session('errors.customer_id')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.customer_id') ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted">Nhập ID của tài khoản Google Ads không có dấu "-" (ví dụ: 1234567890)</small>
                        </div>

                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Tên Account <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.customer_name') ? 'is-invalid' : '' ?>" 
                                   id="customer_name" name="customer_name" value="<?= old('customer_name') ?>" required>
                            <?php if (session('errors.customer_name')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.customer_name') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="currency_code" class="form-label">Mã tiền tệ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.currency_code') ? 'is-invalid' : '' ?>" 
                                   id="currency_code" name="currency_code" value="<?= old('currency_code') ?>" required>
                            <?php if (session('errors.currency_code')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.currency_code') ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted">Ví dụ: USD, VND</small>
                        </div>

                        <div class="mb-3">
                            <label for="time_zone" class="form-label">Múi giờ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.time_zone') ? 'is-invalid' : '' ?>" 
                                   id="time_zone" name="time_zone" value="<?= old('time_zone') ?>" required>
                            <?php if (session('errors.time_zone')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.time_zone') ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted">Ví dụ: Asia/Saigon</small>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select <?= session('errors.status') ? 'is-invalid' : '' ?>" 
                                    id="status" name="status">
                                <option value="active" <?= old('status') == 'active' ? 'selected' : '' ?>>Hoạt động</option>
                                <option value="inactive" <?= old('status') == 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                            </select>
                            <?php if (session('errors.status')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.status') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Thêm Account</button>
                            <a href="<?= base_url('ads-accounts') ?>" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->include('templates/footer') ?>