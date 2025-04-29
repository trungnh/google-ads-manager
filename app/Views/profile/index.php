<?= $this->include('templates/header') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">User Profile</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('profile/update') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control <?= session('errors.username') ? 'is-invalid' : '' ?>" 
                                   id="username" name="username" value="<?= old('username', $user['username']) ?>" disabled readonly>
                            <?php if (session('errors.username')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.username') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" value="<?= old('email', $user['email']) ?>" disabled readonly>
                            <?php if (session('errors.email')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.email') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control <?= session('errors.current_password') ? 'is-invalid' : '' ?>" 
                                   id="current_password" name="current_password">
                            <?php if (session('errors.current_password')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.current_password') ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted">Chỉ điền nếu bạn muốn đổi mật khẩu</small>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control <?= session('errors.new_password') ? 'is-invalid' : '' ?>" 
                                   id="new_password" name="new_password">
                            <?php if (session('errors.new_password')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.new_password') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control <?= session('errors.confirm_password') ? 'is-invalid' : '' ?>" 
                                   id="confirm_password" name="confirm_password">
                            <?php if (session('errors.confirm_password')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.confirm_password') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->include('templates/footer') ?>