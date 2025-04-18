<!-- app/Views/ads_accounts/index.php -->
<?= $this->include('templates/header') ?>

<div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Google Ads Accounts</h2>
            <!-- <a href="<?= base_url('ads-accounts/create') ?>" class="btn btn-success me-2">Thêm Account</a> -->
            <a href="<?= base_url('syncads') ?>" class="btn btn-primary">Sync Accounts</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php if (session()->has('success')): ?>
                <div class="alert alert-success">
                    <?= session('success') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger">
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <?php if (empty($accounts)): ?>
                <div class="alert alert-info">
                    No accounts found. Please <a href="<?= base_url('syncads') ?>">sync your accounts</a> first.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Customer ID</th>
                                <th>Account Name</th>
                                <th>Currency</th>
                                <th>Time Zone</th>
                                <th>Status</th>
                                <th>Last Synced</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $account): ?>
                                <tr>
                                    <td><?= $account['customer_id'] ?></td>
                                    <td class="fw-bold"><?= $account['customer_name'] ?></td>
                                    <td><?= $account['currency_code'] ?? 'N/A' ?></td>
                                    <td><?= $account['time_zone'] ?? 'N/A' ?></td>
                                    <td>
                                        <span class="badge <?= $account['status'] === 'ACTIVE' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $account['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= $account['last_synced'] ? date('Y-m-d H:i', strtotime($account['last_synced'])) : 'Never' ?></td>
                                    <td>
                                        <a href="<?= base_url('campaigns/index/' . $account['customer_id']) ?>" class="btn btn-sm btn-info">
                                            View Campaigns
                                        </a>
                                        <?php if (in_array(session()->get('role'), ['superadmin', 'admin'])): ?>
                                        <a href="<?= base_url('adsaccounts/settings/' . $account['id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-cog"></i> Settings
                                        </a>
                                        <button onclick="deleteAccount('<?= $account['id'] ?>', '<?= $account['customer_name'] ?>')" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Xoá thật không?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Chắc chắn chưa? Chốt nhé: <span id="accountNameToDelete"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Thôi, đùa đấy!</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xoá cực mạnh!</button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteAccount(accountId, accountName) {
    document.getElementById('accountNameToDelete').textContent = accountName;
    const modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
    modal.show();
    
    document.getElementById('confirmDelete').onclick = function() {
        fetch(`<?= base_url('adsaccounts/delete') ?>/${accountId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error deleting account');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting account');
        })
        .finally(() => {
            modal.hide();
        });
    };
}
</script>

<?= $this->include('templates/footer') ?>