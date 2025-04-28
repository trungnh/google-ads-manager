<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignScheduleModel extends Model
{
    protected $table = 'campaign_schedules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['customer_id', 'action_type', 'execution_time', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Lấy danh sách lịch của một tài khoản
     */
    public function getSchedulesByCustomerId($customerId)
    {
        return $this->where('customer_id', $customerId)
                    ->orderBy('execution_time', 'ASC')
                    ->findAll();
    }

    /**
     * Lấy các lịch cần thực thi trong khoảng thời gian
     */
    public function getActiveSchedulesForExecution($time)
    {
        return $this->where('status', 'active')
                    ->where('execution_time', $time)
                    ->findAll();
    }
}