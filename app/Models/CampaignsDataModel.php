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
        foreach ($campaignsData as $campaign) {
            $data = [
                'customer_id' => $customerId,
                'campaign_id' => $campaign['campaign_id'],
                'date' => $date,
                'name' => $campaign['name'],
                'status' => $campaign['status'],
                'budget' => $campaign['budget'],
                'cost' => $campaign['cost'],
                'conversions' => $campaign['conversions'],
                'conversion_value' => $campaign['conversion_value'],
                'cost_per_conversion' => $campaign['cost_per_conversion'],
                'conversion_rate' => $campaign['conversion_rate'],
                'target_cpa' => $campaign['target_cpa'],
                'target_roas' => $campaign['target_roas'],
                'ctr' => $campaign['ctr'],
                'clicks' => $campaign['clicks'],
                'average_cpc' => $campaign['average_cpc'],
                'real_conversions' => $campaign['real_conversions'] ?? 0,
                'real_conversion_value' => $campaign['real_conversion_value'] ?? 0,
                'real_conversion_rate' => $campaign['real_conversion_rate'] ?? 0,
                'real_cpa' => $campaign['real_cpa'] ?? 0,
                'last_updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Kiểm tra xem dữ liệu đã tồn tại chưa
            $exists = $builder->where('customer_id', $customerId)
                            ->where('campaign_id', $campaign['campaign_id'])
                            ->where('date', $date)
                            ->countAllResults();
            
            if ($exists) {
                // Cập nhật dữ liệu nếu đã tồn tại
                $builder->where('customer_id', $customerId)
                       ->where('campaign_id', $campaign['campaign_id'])
                       ->where('date', $date)
                       ->update($data);
            } else {
                // Thêm dữ liệu mới nếu chưa tồn tại
                $builder->insert($data);
            }
        }
        
        return true;
    }
} 