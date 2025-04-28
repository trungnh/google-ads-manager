<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignScheduleItemModel extends Model
{
    protected $table = 'campaign_schedule_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['schedule_id', 'campaign_id', 'created_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Lấy danh sách campaign của một lịch
     */
    public function getCampaignsByScheduleId($scheduleId)
    {
        return $this->where('schedule_id', $scheduleId)->findAll();
    }

    /**
     * Thêm nhiều campaign vào lịch
     */
    public function addCampaignsToSchedule($scheduleId, array $campaignIds)
    {
        $data = [];
        foreach ($campaignIds as $campaignId) {
            $data[] = [
                'schedule_id' => $scheduleId,
                'campaign_id' => $campaignId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        return $this->insertBatch($data);
    }

    /**
     * Xóa campaign khỏi lịch
     */
    public function removeCampaignsFromSchedule($scheduleId, array $campaignIds)
    {
        return $this->where('schedule_id', $scheduleId)
                    ->whereIn('campaign_id', $campaignIds)
                    ->delete();
    }

    /**
     * Xóa tất cả campaign của một lịch
     */
    public function removeAllCampaignsFromSchedule($scheduleId)
    {
        return $this->where('schedule_id', $scheduleId)->delete();
    }
}