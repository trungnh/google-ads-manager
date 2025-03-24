<?= $this->include('templates/header') ?>

<div class="row justify-content-md-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Đăng ký</h4>
            </div>
            <div class="card-body">
                <form action="<?= base_url('ureg') ?>" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= old('username') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Đăng ký</button>
                    </div>
                </form>
                <div class="mt-3">
                    <p>Đã có tài khoản? <a href="<?= base_url('login') ?>">Đăng nhập</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>