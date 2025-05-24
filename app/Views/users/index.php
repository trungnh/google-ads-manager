<!-- app/Views/ads_accounts/index.php -->
<?= $this->include('templates/header') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Danh sách người dùng</h4>
                    <a href="<?= site_url('users/create') ?>" class="btn btn-primary">Tạo người dùng mới</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Vai trò</th>
                                    <th>Trạng thái</th>
                                    <th>Đăng nhập lần cuối</th>
                                    <th>Ngày tạo</th>
                                    <th>Logs</th>
                                    <th>Reports</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= $user['email'] ?></td>
                                            <td><?= $user['username'] ?></td>
                                            <td>
                                                <span class="badge bg-<?= $user['role'] === 'superadmin' ? 'danger' : ($user['role'] === 'admin' ? 'warning' : 'info') ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                    <?= $user['status'] === 'active' ? 'Đang hoạt động' : 'Không hoạt động' ?>
                                                </span>
                                            </td>
                                            <td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Chưa đăng nhập' ?></td>
                                            <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <a href="<?= base_url('optimize-logs/view/' . $user['id']) ?>" class="btn btn-sm btn-info">
                                                    Logs
                                                </a>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('reports/view/' . $user['id']) ?>" class="btn btn-sm btn-info">
                                                    Reports
                                                </a>
                                                <a href="<?= base_url('adsaccounts/admin_view' . $user['id']) ?>" class="btn btn-sm btn-success">
                                                    Check
                                                </a>
                                            </td>
                                            <td>
                                                <a href="<?= site_url('users/edit/'.$user['id']) ?>" class="btn btn-sm btn-info">Sửa</a>
                                                <?php if (session()->get('id') != $user['id']): ?>
                                                    <a href="<?= site_url('users/delete/'.$user['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">Xóa</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Không có dữ liệu</td>
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
<?= $this->include('templates/footer') ?>