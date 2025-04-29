<?= $this->include('templates/header') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Campaign Schedules</h3>
                    <div class="text-end mt-2 mb-2">
                        <select class="form-select" id="accountSelector" style="width: 300px;" onchange="window.location.href=this.value">
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= base_url('campaigns/index/' . $acc['customer_id']) ?>" <?= $acc['customer_id'] == $customerId ? 'selected' : '' ?>>
                                    <?= esc($acc['customer_name']) ?> - <?= esc($acc['customer_id']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="card-tools">
                        <a href="<?= base_url("campaignschedules/{$customerId}/create") ?>" class="btn btn-sm btn-primary my-1">
                            <i class="fas fa-plus"></i> Add New Schedule
                        </a>
                        <a href="<?= base_url('campaigns/index/' . $customerId); ?>" class="btn btn-sm btn-info my-1">
                            View Campaigns
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Hành động</th>
                                    <th>Thời gian</th>
                                    <th>Status</th>
                                    <th>Campaigns</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td><?= $schedule['action_type'] == 'enable' ? 'Bật' : 'Tắt' ?></td>
                                        <td><?= date('H:i', strtotime($schedule['execution_time'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $schedule['status'] === 'active' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($schedule['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $scheduledCampaigns = model('CampaignScheduleItemModel')->getCampaignsByScheduleId($schedule['id']);
                                            echo count($scheduledCampaigns) . ' campaigns';
                                            ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url("campaignschedules/{$customerId}/edit/{$schedule['id']}") ?>" 
                                               class="btn btn-sm btn-primary m-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?= base_url("campaignschedules/{$customerId}/delete/{$schedule['id']}") ?>" 
                                               class="btn btn-sm btn-danger m-1"
                                               onclick="return confirm('Are you sure you want to delete this schedule?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->include('templates/footer') ?>