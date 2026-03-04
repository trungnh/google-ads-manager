<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignChart5mModel extends Model
{
    protected $table = 'campaign_chart_5m';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'customer_id',
        'campaign_id',
        'record_time',
        'cost',
        'conversions',
        'conversion_value',
        'clicks',
        'cpa',
        'cpc'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Lấy dữ liệu biểu đồ cho campaign theo khoảng thời gian
     *
     * @param string $campaignId ID chiến dịch
     * @param string $startTime Thời gian bắt đầu (Y-m-d H:i:s)
     * @param string $endTime Thời gian kết thúc (Y-m-d H:i:s)
     * @return array
     */
    public function getChartData($campaignId, $startTime, $endTime)
    {
        $builder = $this->db->table($this->table);
        $builder->select('record_time, cost, conversions, clicks, cpa, cpc');
        $builder->where('campaign_id', $campaignId);
        $builder->where('record_time >=', $startTime);
        $builder->where('record_time <=', $endTime);
        $builder->orderBy('record_time', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Upsert dữ liệu biểu đồ 5 phút. 
     * Nếu đã có record cùng record_time, cộng dồn cost, conversions, ticks và tính lại cpa cpc.
     */
    public function upsertData($data)
    {
        $db = \Config\Database::connect();

        $sql = "INSERT INTO {$this->table} (customer_id, campaign_id, record_time, cost, conversions, conversion_value, clicks, cpa, cpc, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                cost = cost + VALUES(cost),
                conversions = conversions + VALUES(conversions),
                conversion_value = conversion_value + VALUES(conversion_value),
                clicks = clicks + VALUES(clicks),
                cpa = IF((conversions + VALUES(conversions)) > 0, (cost + VALUES(cost)) / (conversions + VALUES(conversions)), 0),
                cpc = IF((clicks + VALUES(clicks)) > 0, (cost + VALUES(cost)) / (clicks + VALUES(clicks)), 0),
                updated_at = NOW()";

        $db->query($sql, [
            $data['customer_id'],
            $data['campaign_id'],
            $data['record_time'],
            $data['cost'] ?? 0,
            $data['conversions'] ?? 0,
            $data['conversion_value'] ?? 0,
            $data['clicks'] ?? 0,
            $data['cpa'] ?? 0,
            $data['cpc'] ?? 0
        ]);

        return $db->affectedRows() > 0;
    }
}
