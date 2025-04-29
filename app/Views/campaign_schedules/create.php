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
                            <label class="font-weight-bold" for="action_type">Hành động</label>
                            <select name="action_type" id="action_type" class="form-control" required>
                                <option value="">==== Chọn hành động ====</option>
                                <option value="enable">Bật</option>
                                <option value="disable">Tắt</option>
                            </select>
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
                                <table class="table table-bordered table-striped">
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
</script>

<?= $this->include('templates/footer') ?>