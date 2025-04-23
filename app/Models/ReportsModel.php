<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportsModel extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'customer_id',
        'campaign_id',
        'date',
        'cost',
        'conversions',
        'conversion_value',
        'running',
        'paused',
        'created_at',
        'updated_at'
    ];

    public function getReportsByDate($userId, $date)
    {
        return $this->select('reports.*, ads_accounts.customer_name, ads_accounts.currency_code')
            ->join('ads_accounts', 'reports.customer_id = ads_accounts.customer_id', 'left')
            ->where('reports.user_id', $userId)
            ->where('ads_accounts.user_id', $userId)
            ->where('DATE(reports.created_at)', $date)
            ->orderBy('reports.created_at', 'DESC')
            ->findAll();
    }

    public function getReportsByDateRange($userId, $startDate, $endDate)
    {
        return $this->select('reports.*, ads_accounts.customer_name, ads_accounts.currency_code')
            ->join('ads_accounts', 'reports.customer_id = ads_accounts.customer_id', 'left')
            ->where('reports.user_id', $userId)
            ->where('ads_accounts.user_id', $userId)
            ->where('DATE(reports.created_at) >=', $startDate)
            ->where('DATE(reports.created_at) <=', $endDate)
            ->orderBy('reports.created_at', 'DESC')
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

    public function saveReport($data)
    {
        $report = [
            'user_id' => $data['user_id'],
            'customer_id' => $data['customer_id'], 
            'date' => date('Y-m-d'),
            'cost' => $data['cost'] ?? 0,
            'conversions' => $data['conversions'] ?? 0,
            'conversion_value' => $data['conversion_value'] ?? 0,
            'running' => $data['running']?? 0,
            'paused' => $data['paused']?? 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $existing = $this->where('user_id', $report['user_id'])
            ->where('customer_id', $report['customer_id'])
            ->where('date', date('Y-m-d'))
            ->first();

        if ($existing) {
            return $this->update($existing['id'], $report);
        } else {
            return $this->insert($report);
        }
    }

    public function saveReportByCampaigns($userId, $customerId, $campaigns) 
    {
        $totalCost = 0;
        $totalConversions = 0;
        $totalConversionValue = 0;
        $running = 0;
        $paused = 0;
        foreach ($campaigns as $campaign) {
            // Bỏ qua chiến dịch không hoạt động và không chi tiêu
            if ($campaign['status'] == 'PAUSED' && $campaign['cost'] == 0) {
                continue;
            }

            $totalCost += $campaign['cost'];
            $totalConversions += $campaign['real_conversions'];
            $totalConversionValue += $campaign['real_conversion_value'];
            
            if ($campaign['status'] == 'RUNNING') {
                $running++; 
            } else {
                $paused++; 
            }
        }

        $report = [
            'user_id' => $userId,
            'customer_id' => $customerId,
            'date' => date('Y-m-d'),
            'cost' => $totalCost,  
            'conversions' => $totalConversions,
            'conversion_value' => $totalConversionValue,
            'running' => $running,
            'paused' => $paused,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->saveReport($report);
    }
} 