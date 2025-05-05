<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignsDataModel extends Model
{
    protected $table = 'campaigns_data';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'customer_id',
        'campaign_id',
        'date',
        'name',
        'status',
        'budget',
        'cost',
        'conversions',
        'conversion_value',
        'cost_per_conversion',
        'conversion_rate',
        'target_cpa',
        'target_roas',
        'ctr',
        'clicks',
        'average_cpc',
        'real_conversions',
        'real_conversion_value',
        'real_conversion_rate',
        'real_cpa',
        'last_cost_conversion',
        'last_count_conversion',
        'last_count_conversion_value',
        'last_updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getCampaignsByDate($customerId, $date, $showPaused = false)
    {
        $where = [
            'customer_id' => $customerId,
            'date' => $date
        ];

        if (!$showPaused) {
            $where['status'] = 'ENABLED';
        }

        return $this->where($where)->findAll();
    }

    public function getLastUpdateTime($customerId, $date)
    {
        $result = $this->select('last_updated_at')
            ->where([
                'customer_id' => $customerId,
                'date' => $date
            ])
            ->orderBy('last_updated_at', 'DESC')
            ->first();

        return $result ? $result['last_updated_at'] : null;
    }

    public function saveCampaignsData($customerId, $campaignsData, $date = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('campaigns_data');
        $date = $date ?? date('Y-m-d');
        $campaignReturnData = [];
        foreach ($campaignsData as $campaign) {
            $realConversions = $campaign['real_conversions']?? 0;
            $realConversionValue = $campaign['real_conversion_value']?? 0;
            $realConversionRate = $campaign['real_conversion_rate']?? 0;
            $data = [
                'customer_id' => $customerId,
                'campaign_id' => $campaign['campaign_id'],
                'date' => $date,
                'name' => $campaign['name'],
                'status' => $campaign['status'],
                'budget' => $campaign['budget'],
                'cost' => $campaign['cost'],
                'conversions' => $campaign['conversions'] ?? 0,
                'conversion_value' => $campaign['conversion_value'] ?? 0,
                'cost_per_conversion' => $campaign['cost_per_conversion'] ?? 0,
                'conversion_rate' => $campaign['conversion_rate'] ?? 0,
                'target_cpa' => $campaign['target_cpa'] ?? null,
                'target_roas' => $campaign['target_roas'] ?? null,
                'ctr' => $campaign['ctr'] ?? 0,
                'clicks' => $campaign['clicks'] ?? 0,
                'average_cpc' => $campaign['average_cpc'] ?? 0,
                'real_conversions' => $realConversions,
                'real_conversion_value' => $realConversionValue,
                'real_conversion_rate' => $realConversionRate,
                'real_cpa' => $campaign['real_cpa'] ?? 0,
                'last_updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Kiểm tra xem dữ liệu đã tồn tại chưa
            $exists = $builder->where('customer_id', $customerId)
                            ->where('campaign_id', $campaign['campaign_id'])
                            ->where('date', $date)
                            ->countAllResults();
            
            if ($exists) {
                // Nếu có thêm conversions mới thì cập nhật last_cost_conversion, last_count_conversion, last_count_conversion_value
                $tmpCampaign = $this->where('customer_id', $customerId)
                        ->where('campaign_id', $campaign['campaign_id'])
                        ->where('date', date('Y-m-d'))
                        ->first();
                if (isset($tmpCampaign['real_conversions']) && $tmpCampaign['real_conversions'] < $realConversions) {
                    $data['last_cost_conversion'] = $campaign['cost'];
                    $data['last_count_conversion'] = $realConversions;
                    $data['last_count_conversion_value'] = $realConversionValue;
                }
                // Cập nhật dữ liệu nếu đã tồn tại
                $builder->where('customer_id', $customerId)
                       ->where('campaign_id', $campaign['campaign_id'])
                       ->where('date', $date)
                       ->update($data);
            } else {
                // Thêm dữ liệu mới nếu chưa tồn tại
                // Nếu chưa có chuyển đổi thì đặt giá trị là 0, ngược lại là giá trị hiện tại
                if ($realConversions > 0) {
                    $data['last_cost_conversion'] = $campaign['cost'];
                    $data['last_count_conversion'] = $realConversions;
                    $data['last_count_conversion_value'] = $realConversionValue;
                }
                $builder->insert($data);
            }
            $campaignReturnData[] = $data;
        }
        
        return $campaignReturnData;
        // return true;
    }

    public function saveCampaignStatus($customerId, $campaignId, $status)
    {
        $data = [
            'customer_id' => $customerId,
            'campaign_id' => $campaignId,
            'status' => $status 
        ];

        $this->db->table('campaigns_data')
                ->where('customer_id', $customerId)
                ->where('campaign_id', $campaignId)
                ->update($data);    
    }

    public function saveCampaignCFLC($customerId, $campaignId)
    {
        $campaign = $this->where('customer_id', $customerId)
                        ->where('campaign_id', $campaignId)
                        ->where('date', date('Y-m-d'))
                        ->first();

        $data = [
            'customer_id' => $customerId,
            'campaign_id' => $campaignId,
            'last_cost_conversion' => $campaign['cost'],
            'last_count_conversion' => $campaign['real_conversions'],
            'last_count_conversion_value' => $campaign['real_conversion_value'],
        ];

        $this->db->table('campaigns_data')
                ->where('customer_id', $customerId)
                ->where('campaign_id', $campaignId)
                ->update($data);    
    }
    
    public function getCampaignsByID($customerId, $campaignId)
    {
        return $this->where([
            'customer_id' => $customerId,
            'campaign_id' => $campaignId
        ])->orderBy('date', 'DESC')->findAll(1);
    }
} 