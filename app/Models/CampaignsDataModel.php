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

    public function getCampaignsByDate($customerId, $date)
    {
        return $this->where([
            'customer_id' => $customerId,
            'date' => $date
        ])->findAll();
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

    public function saveCampaignsData($customerId, $date, $campaigns)
    {
        // Begin transaction
        $this->db->transStart();

        try {
            // Delete existing data for this customer and date
            $this->where([
                'customer_id' => $customerId,
                'date' => $date
            ])->delete();

            // Prepare data for batch insert
            $data = [];
            foreach ($campaigns as $campaign) {
                $data[] = [
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
                    'real_conversions' => isset($campaign['real_conversions']) ? $campaign['real_conversions'] : 0,
                    'real_conversion_value' => isset($campaign['real_conversion_value']) ? $campaign['real_conversion_value'] : 0,
                    'real_conversion_rate' => isset($campaign['real_conversion_rate']) ? $campaign['real_conversion_rate'] : 0,
                    'real_cpa' => isset($campaign['real_cpa']) ? $campaign['real_cpa'] : 0,
                    'last_updated_at' => date('Y-m-d H:i:s')
                ];
            }

            // Insert new data
            if (!empty($data)) {
                $this->insertBatch($data);
            }

            // Commit transaction
            $this->db->transComplete();
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->transRollback();
            log_message('error', 'Error saving campaigns data: ' . $e->getMessage());
            return false;
        }
    }
} 