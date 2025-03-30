<?php

namespace App\Models;

use CodeIgniter\Model;

class OptimizeLogsModel extends Model
{
    protected $table = 'optimize_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'customer_id',
        'campaign_id',
        'campaign_name',
        'action',
        'details',
        'created_at'
    ];

    public function getLogsByDate($userId, $date)
    {
        return $this->where('user_id', $userId)
            ->where('DATE(created_at)', $date)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getLogsByDateRange($userId, $startDate, $endDate)
    {
        return $this->where('user_id', $userId)
            ->where('DATE(created_at) >=', $startDate)
            ->where('DATE(created_at) <=', $endDate)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getDistinctDates($userId)
    {
        return $this->select('DATE(created_at) as date')
            ->where('user_id', $userId)
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'DESC')
            ->limit(5)
            ->findAll();
    }
} 