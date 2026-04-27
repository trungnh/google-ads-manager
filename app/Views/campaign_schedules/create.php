<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Schedule</h3>
                    <div class="card-tools">
                        <a href="<?= base_url("campaignschedules/{$customerId}") ?>" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->has('error')): ?>
                        <div class="alert alert-danger">
                            <?= session('error') ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url("campaignschedules/{$customerId}/create") ?>" method="post">
                        <div class="form-group mt-2">
                            <label class="font-weight-bold" for="name">Tên Schedule</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên schedule (tùy chọn)">
                        </div>

                        <div class="form-group mt-2">
                            <label class="font-weight-bold" for="action_type">Hành động</label>
                            <select name="action_type" id="action_type" class="form-control" required onchange="toggleBudgets()">
                                <option value="">==== Chọn hành động ====</option>
                                <option value="enable">Bật</option>
                                <option value="disable">Tắt</option>
                                <option value="increase_budget">Tăng ngân sách</option>
                                <option value="decrease_budget">Giảm ngân sách</option>
                            </select>
                        </div>

                        <div id="budget_fields" style="display: none;">
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold" for="budget_type">Loại thay đổi</label>
                                        <select name="budget_type" id="budget_type" class="form-control">
                                            <option value="percentage">Theo % ngân sách hiện tại</option>
                                            <option value="absolute">Đến 1 số ngân sách nhập vào</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold" for="budget_value">Giá trị (Nhập số)</label>
                                        <input type="number" step="0.01" name="budget_value" id="budget_value" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <label class="font-weight-bold" for="execution_time">Thời gian</label>
                            <select name="execution_time" id="execution_time" class="form-control" required>
                                <option value="">==== Chọn thời gian ====</option>
                                <?php 
                                for ($hour = 0; $hour < 24; $hour++) {
                                    for ($minute = 0; $minute < 60; $minute += 30) {
                                        $time = sprintf('%02d:%02d', $hour, $minute);
                                        echo "<option value='{$time}'>{$time}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label class="font-weight-bold">Select Campaigns</label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50px">
                                                <input type="checkbox" id="select-all">
                                            </th>
                                            <th>Campaign ID</th>
                                            <th>Campaign Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($campaigns as $campaign): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="campaign_ids[]" 
                                                           value="<?= $campaign['campaign_id'] ?>" 
                                                           class="campaign-checkbox">
                                                </td>
                                                <td><?= $campaign['campaign_id'] ?></td>
                                                <td><?= $campaign['name'] ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $campaign['status'] === 'ENABLED' ? 'success' : 'warning' ?>">
                                                        <?= $campaign['status'] == 'ENABLED' ? 'Đang chạy' : 'Tạm dừng'?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Create Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.campaign-checkbox').forEach(function(checkbox) {
        checkbox.checked = this.checked;
    }, this);
});

function toggleBudgets() {
    const actionType = document.getElementById('action_type').value;
    const budgetFields = document.getElementById('budget_fields');
    if (actionType === 'increase_budget' || actionType === 'decrease_budget') {
        budgetFields.style.display = 'block';
        document.getElementById('budget_value').setAttribute('required', 'required');
    } else {
        budgetFields.style.display = 'none';
        document.getElementById('budget_value').removeAttribute('required');
    }
}
</script>

<?= $this->include('templates/footer') ?>