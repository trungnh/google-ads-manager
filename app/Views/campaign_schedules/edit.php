<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Schedule</h3>
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

                    <form action="<?= base_url("campaignschedules/{$customerId}/edit/{$schedule['id']}") ?>" method="post">
                        <div class="form-group mt-2">
                            <label class="font-weight-bold" for="action_type">Hành động</label>
                            <select name="action_type" id="action_type" class="form-control" required>
                                <option value="">==== Chọn hành động ====</option>
                                <option value="enable" <?= $schedule['action_type'] === 'enable' ? 'selected' : '' ?>>Bật</option>
                                <option value="disable" <?= $schedule['action_type'] === 'disable' ? 'selected' : '' ?>>Tắt</option>
                            </select>
                        </div>

                        <div class="form-group mt-2">
                            <label class="font-weight-bold" for="execution_time">Thời gian</label>
                            <select name="execution_time" id="execution_time" class="form-control" required>
                                <option value="">==== Chọn thời gian ====</option>
                                <?php 
                                $scheduleTime = date('H:i', strtotime($schedule['execution_time']));
                                for ($hour = 0; $hour < 24; $hour++) {
                                    for ($minute = 0; $minute < 60; $minute += 30) {
                                        $time = sprintf('%02d:%02d', $hour, $minute);
                                        $selected = ($time === $scheduleTime) ? 'selected' : '';
                                        echo "<option value='{$time}' {$selected}>{$time}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group mt-2">
                            <label class="font-weight-bold" for="status">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="active" <?= $schedule['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $schedule['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
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
                                        <?php 
                                        $scheduledCampaignIds = array_column($scheduledCampaigns, 'campaign_id');
                                        foreach ($campaigns as $campaign): 
                                        ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="campaign_ids[]" 
                                                           value="<?= $campaign['campaign_id'] ?>" 
                                                           class="campaign-checkbox"
                                                           <?= in_array($campaign['campaign_id'], $scheduledCampaignIds) ? 'checked' : '' ?>>
                                                </td>
                                                <td><?= $campaign['campaign_id'] ?></td>
                                                <td><?= $campaign['name'] ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $campaign['status'] === 'ENABLED' ? 'success' : 'warning' ?>">
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
                            <button type="submit" class="btn btn-primary">Update Schedule</button>
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