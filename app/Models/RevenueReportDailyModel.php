<?php

namespace App\Models;

use CodeIgniter\Model;

class RevenueReportDailyModel extends Model
{
    protected $table = 'revenue_report_daily';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'report_id',
        'date',
        'orders',
        'quantity',
        'ads_cost',
        'revenue',
        'goods_cost',
        'ship_cost',
        'return_cost',
        'total_cost',
        'profit'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getDailyData($reportId)
    {
        return $this->where('report_id', $reportId)
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    public function getAggregatedDailyDataByReports($reportIds)
    {
        if (empty($reportIds)) {
            return [];
        }

        return $this->select('date, 
                             SUM(orders) as orders, 
                             SUM(quantity) as quantity, 
                             SUM(ads_cost) as ads_cost, 
                             SUM(revenue) as revenue, 
                             SUM(goods_cost) as goods_cost, 
                             SUM(ship_cost) as ship_cost, 
                             SUM(return_cost) as return_cost, 
                             SUM(total_cost) as total_cost, 
                             SUM(profit) as profit')
            ->whereIn('report_id', $reportIds)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    // Nhận mảng dữ liệu nhiều ngày gửi lên từ client và upsert
    public function upsertDailyData($reportId, $dailyData)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        foreach ($dailyData as $data) {
            $existing = $this->where('report_id', $reportId)
                ->where('date', $data['date'])
                ->first();

            $saveData = [
                'report_id' => $reportId,
                'date' => $data['date'],
                'orders' => isset($data['orders']) ? (int) $data['orders'] : 0,
                'quantity' => isset($data['quantity']) ? (int) $data['quantity'] : 0,
                'ads_cost' => isset($data['ads_cost']) ? (float) $data['ads_cost'] : 0,
                'revenue' => isset($data['revenue']) ? (float) $data['revenue'] : 0,
                'goods_cost' => isset($data['goods_cost']) ? (float) $data['goods_cost'] : 0,
                'ship_cost' => isset($data['ship_cost']) ? (float) $data['ship_cost'] : 0,
                'return_cost' => isset($data['return_cost']) ? (float) $data['return_cost'] : 0,
                'total_cost' => isset($data['total_cost']) ? (float) $data['total_cost'] : 0,
                'profit' => isset($data['profit']) ? (float) $data['profit'] : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                $builder->where('id', $existing['id'])->update($saveData);
            } else {
                $saveData['created_at'] = date('Y-m-d H:i:s');
                $builder->insert($saveData);
            }
        }
    }
}
