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
        'budget_spending_threshold',
        'gsheet1',
        'gsheet_date_col',
        'gsheet_phone_col',
        'gsheet_value_col',
        'gsheet_campaign_col',
        'gsheet2',
        'use_ggsheet_api',
        'ggsheet_id',
        'ggsheet_name',
        'ggsheet2_id',
        'ggsheet2_name',
        'last_optimize_run',
        'cost_threshold',
        'auto_on_off',
        'use_roas_threshold',
        'extended_cpa_threshold',
        'default_paused_campaigns',
        'exclude_campaign_ids',
        'customer_id',
        'pancake_shop_id',
        'pancake_api_key',
        'pancake_product_id',
        'pancake_exclude_tags',
        'use_pancake',
        'pancake_use_usd',
        'pancake_usd_rate',
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
        'budget_spending_threshold' => 'permit_empty|decimal',
        'gsheet1' => 'permit_empty|valid_url',
        'gsheet_date_col' => 'permit_empty|alpha|max_length[1]',
        'gsheet_phone_col' => 'permit_empty|alpha|max_length[1]',
        'gsheet_value_col' => 'permit_empty|alpha|max_length[1]',
        'gsheet_campaign_col' => 'permit_empty|alpha|max_length[1]',
        'gsheet2' => 'permit_empty|valid_url',
        'use_ggsheet_api' => 'permit_empty|in_list[0,1]',
        'ggsheet_id' => 'permit_empty|string',
        'ggsheet_name' => 'permit_empty|string',
        'ggsheet2_id' => 'permit_empty|string',
        'ggsheet2_name' => 'permit_empty|string',
        'cost_threshold' => 'permit_empty|decimal',
        'auto_on_off' => 'permit_empty|in_list[0,1]',
        'use_roas_threshold' => 'permit_empty|in_list[0,1]',
        'extended_cpa_threshold' => 'permit_empty|decimal',
        'default_paused_campaigns' => 'permit_empty|in_list[0,1]',
        'exclude_campaign_ids' => 'permit_empty|string',
        'customer_id' => 'permit_empty|string',
        'pancake_shop_id' => 'permit_empty|string',
        'pancake_api_key' => 'permit_empty|string',
        'pancake_product_id' => 'permit_empty|string',
        'pancake_exclude_tags' => 'permit_empty|string',
        'use_pancake' => 'permit_empty|in_list[0,1]',
        'pancake_use_usd' => 'permit_empty|in_list[0,1]',
        'pancake_usd_rate' => 'permit_empty|integer',
    ];

    public function getSettingsByAccountId($accountId)
    {
        return $this->where('account_id', $accountId)->first();
    }

    public function getSettingsByCustomerId($customerId)
    {
        return $this->where('customer_id', $customerId)->first();
    }

    public function saveSettings($customerId, $data)
    {
        try {
            // Debug log
            log_message('info', 'Saving settings for account: ' . $customerId);
            log_message('info', 'Input data: ' . json_encode($data));

            // Kiểm tra dữ liệu đầu vào
            if (empty($data['account_id'])) {
                log_message('error', 'Missing account_id in saveSettings');
                return false;
            }

            // Chuẩn hóa dữ liệu
            $settings = [
                'customer_id' => $customerId,
                'account_id' => $data['account_id'],
                'auto_optimize' => ($data['auto_optimize'] === 'true' || $data['auto_optimize'] === true || $data['auto_optimize'] === 1) ? 1 : 0,
                'cpa_threshold' => $data['cpa_threshold'] ?? 0,
                'roas_threshold' => $data['roas_threshold'] ?? 0,
                'increase_budget' => ($data['increase_budget'] === 'true' || $data['increase_budget'] === true || $data['increase_budget'] === 1) ? 1 : 0,
                'gsheet1' => $data['gsheet1'] ?? null,
                'gsheet_date_col' => strtoupper($data['gsheet_date_col'] ?? ''),
                'gsheet_phone_col' => strtoupper($data['gsheet_phone_col'] ?? ''),
                'gsheet_value_col' => strtoupper($data['gsheet_value_col'] ?? ''),
                'gsheet_campaign_col' => strtoupper($data['gsheet_campaign_col'] ?? ''),
                'gsheet2' => $data['gsheet2'] ?? null,
                'cost_threshold' => $data['cost_threshold'] ?? 0,
                'auto_on_off' => ($data['auto_on_off'] === 'true' || $data['auto_on_off'] === true || $data['auto_on_off'] === 1) ? 1 : 0,
                'use_roas_threshold' => ($data['use_roas_threshold'] === 'true' || $data['use_roas_threshold'] === true || $data['use_roas_threshold'] === 1) ? 1 : 0,
                'extended_cpa_threshold' => $data['extended_cpa_threshold'] ?? 0,
                'default_paused_campaigns' => ($data['default_paused_campaigns'] === 'true' || $data['default_paused_campaigns'] === true || $data['default_paused_campaigns'] === 1) ? 1 : 0,
                'exclude_campaign_ids' => $data['exclude_campaign_ids'] ?? null,
                'pancake_shop_id' => $data['pancake_shop_id'] ?? null,
                'pancake_api_key' => $data['pancake_api_key'] ?? null,
                'pancake_product_id' => $data['pancake_product_id'] ?? null,
                'pancake_exclude_tags' => $data['pancake_exclude_tags'] ?? null,
                'use_pancake' => ($data['use_pancake'] === 'true' || $data['use_pancake'] === true || $data['use_pancake'] === 1) ? 1 : 0,
                'pancake_use_usd' => ($data['pancake_use_usd'] === 'true' || $data['pancake_use_usd'] === true || $data['pancake_use_usd'] === 1) ? 1 : 0,
                'pancake_usd_rate' => $data['pancake_usd_rate'] ?? 27000
            ];

            // Kiểm tra xem đã có settings chưa
            $existing = $this->where('customer_id', $customerId)->first();
            log_message('info', 'Existing settings: ' . ($existing ? json_encode($existing) : 'None'));

            $result = false;
            if ($existing) {
                log_message('info', 'Updating existing settings with ID: ' . $existing['id']);
                $result = $this->update($existing['id'], $settings);
                log_message('info', 'Update result: ' . ($result ? 'Success' : 'Failed'));
            } else {
                log_message('info', 'Inserting new settings');
                $result = $this->insert($settings);
                log_message('info', 'Insert result: ' . ($result ? 'Success' : 'Failed'));
            }

            if (!$result) {
                log_message('error', 'Database operation failed. Last error: ' . print_r($this->db->error(), true));
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Exception in saveSettings: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    public function updateSettings($customerId, $settings)
    {
        $existing = $this->where('customer_id', $customerId)->first();
        if ($existing) {
            return $this->update($existing['id'], $settings);
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