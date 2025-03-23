<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSettingsModel extends Model
{
    protected $table = 'user_settings';
    protected $primaryKey = 'id';
    
    protected $allowedFields = ['user_id', 'mcc_id', 'created_at', 'updated_at'];
    
    protected $useTimestamps = false;
    
    protected $validationRules = [
        'user_id' => 'required|numeric',
        'mcc_id' => 'permit_empty|string',
    ];
}