<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignPerformanceLogsModel extends Model
{
    protected $table = 'campaign_performance_logs';
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
     * @param int $intervalMinutes Khoảng thời gian (5 hoặc 30)
     * @return array
     */
    public function getChartData($campaignId, $startTime, $endTime, $intervalMinutes = 5)
    {
        $builder = $this->db->table($this->table);
        $builder->select('record_time, sum(cost) as cost, sum(conversions) as conversions, sum(clicks) as clicks, sum(cpa) as cpa, sum(cpc) as cpc'); // We will refine the aggregation query if needed
        $builder->where('campaign_id', $campaignId);
        $builder->where('record_time >=', $startTime);
        $builder->where('record_time <=', $endTime);

        if ($intervalMinutes == 30) {
            // Group by 30 minutes. In MySQL: UNIX_TIMESTAMP(record_time) DIV 1800 * 1800
            $builder->select('FROM_UNIXTIME(UNIX_TIMESTAMP(record_time) DIV 1800 * 1800) as time_bucket, sum(cost) as total_cost, sum(conversions) as total_conversions, sum(clicks) as total_clicks');
            $builder->groupBy('time_bucket');
            $builder->orderBy('time_bucket', 'ASC');

            $results = $builder->get()->getResultArray();

            // Re-calculate derived metrics
            foreach ($results as &$row) {
                $row['cost'] = $row['total_cost'];
                $row['conversions'] = $row['total_conversions'];
                $row['clicks'] = $row['total_clicks'];
                $row['cpa'] = $row['total_conversions'] > 0 ? $row['total_cost'] / $row['total_conversions'] : 0;
                $row['cpc'] = $row['total_clicks'] > 0 ? $row['total_cost'] / $row['total_clicks'] : 0;
                $row['record_time'] = $row['time_bucket'];
            }
            return $results;
        }

        // Default: 5 minutes (as inserted)
        $builder->select('record_time, cost, conversions, clicks, cpa, cpc');
        $builder->orderBy('record_time', 'ASC');

        return $builder->get()->getResultArray();
    }
}
