<!-- app/Views/settings/index.php -->
<?= $this->include('templates/header') ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <h3>User Settings</h3>
                </div>
                <div class="card-body">
                    <?php if (session()->has('success')): ?>
                        <div class="alert alert-success">
                            <?= session('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->has('errors')): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach (session('errors') as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('settings/update') ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="mcc_id">Google Ads MCC ID</label>
                            <input type="text" class="form-control" id="mcc_id" name="mcc_id" 
                                   placeholder="Example: 123-456-7890" 
                                   value="<?= isset($settings['mcc_id']) ? $settings['mcc_id'] : '' ?>">
                            <small class="form-text text-muted">Điền MCC ID vào đây. Bỏ hết ký tự - đi.</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>