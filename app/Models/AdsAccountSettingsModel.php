<?php

namespace App\Models;

use CodeIgniter\Model;

class AdsAccountSettingsModel extends Model
{
    protected $table = 'ads_account_settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'account_id',
        'auto_optimize',
        'cpa_threshold',
        'roas_threshold',
        'increase_budget',
        'gsheet1',
        'gsheet_date_col',
        'gsheet_phone_col',
        'gsheet_value_col',
        'gsheet_campaign_col',
        'gsheet2',
        'last_optimize_run',
        'cost_threshold',
        'auto_on_off'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'account_id' => 'required|integer',
        'auto_optimize' => 'required|in_list[0,1]',
        'cpa_threshold' => 'permit_empty|decimal',
        'roas_threshold' => 'permit_empty|decimal',
        'increase_budget' => 'permit_empty|decimal',
        'gsheet1' => 'permit_empty|valid_url',
        'gsheet_date_col' => 'permit_empty|alpha|max_length[1]',
        'gsheet_phone_col' => 'permit_empty|alpha|max_length[1]',
        'gsheet_value_col' => 'permit_empty|alpha|max_length[1]',
        'gsheet_campaign_col' => 'permit_empty|alpha|max_length[1]',
        'gsheet2' => 'permit_empty|valid_url',
        'cost_threshold' => 'permit_empty|decimal',
        'auto_on_off' => 'permit_empty|in_list[0,1]'
    ];

    public function getSettingsByAccountId($accountId)
    {
        return $this->where('account_id', $accountId)->first();
    }

    public function saveSettings($accountId, $data)
    {
        // Debug log
        log_message('info', 'Saving settings for account: ' . $accountId);
        log_message('info', 'Input data: ' . json_encode($data));

        // Chuẩn hóa dữ liệu
        $settings = [
            'account_id' => $accountId,
            'auto_optimize' => ($data['auto_optimize'] === 'true' || $data['auto_optimize'] === true || $data['auto_optimize'] === 1) ? 1 : 0,
            'cpa_threshold' => $data['cpa_threshold'] ?? 0,
            'roas_threshold' => $data['roas_threshold'] ?? 0,
            'increase_budget' => $data['increase_budget'] ?? 0,
            'gsheet1' => $data['gsheet1'] ?? null,
            'gsheet_date_col' => strtoupper($data['gsheet_date_col'] ?? ''),
            'gsheet_phone_col' => strtoupper($data['gsheet_phone_col'] ?? ''),
            'gsheet_value_col' => strtoupper($data['gsheet_value_col'] ?? ''),
            'gsheet_campaign_col' => strtoupper($data['gsheet_campaign_col'] ?? ''),
            'gsheet2' => $data['gsheet2'] ?? null,
            'cost_threshold' => $data['cost_threshold'] ?? 0,
            'auto_on_off' => ($data['auto_on_off'] === 'true' || $data['auto_on_off'] === true || $data['auto_on_off'] === 1) ? 1 : 0
        ];

        // Debug log
        log_message('info', 'Processed settings: ' . json_encode($settings));

        // Kiểm tra xem đã có settings chưa
        $existing = $this->where('account_id', $accountId)->first();

        if ($existing) {
            return $this->update($existing['id'], $settings);
        } else {
            return $this->insert($settings);
        }
    }

    public function getAccountsForOptimization()
    {
        return $this->select('ads_account_settings.*, ads_accounts.customer_id, ads_accounts.customer_name, ads_accounts.user_id')
                    ->join('ads_accounts', 'ads_accounts.id = ads_account_settings.account_id')
                    ->where('auto_optimize', 1)
                    ->findAll();
    }
} 