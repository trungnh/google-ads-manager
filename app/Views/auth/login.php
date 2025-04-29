<?= $this->include('templates/header') ?>

<div class="row justify-content-md-center">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Đăng nhập</h4>
            </div>
            <div class="card-body">
                <form action="<?= base_url('login') ?>" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập hoặc Email</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= old('username') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Đăng nhập</button>
                    </div>
                </form>
                <!-- <div class="mt-3">
                    <p>Chưa có tài khoản? <a href="<?= base_url('register') ?>">Đăng ký</a></p>
                </div> -->
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>