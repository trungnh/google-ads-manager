<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSettingsModel extends Model
{
    protected $table = 'user_settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['user_id', 'mcc_id', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'mcc_id' => 'permit_empty|numeric'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be an integer'
        ],
        'mcc_id' => [
            'numeric' => 'MCC ID must be a number'
        ]
    ];

    public function getSettingsByUserId($userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    public function updateSettings($userId, $data)
    {
        return $this->where('user_id', $userId)->set($data)->update();
    }

    public function createSettings($data)
    {
        return $this->insert($data);
    }
}