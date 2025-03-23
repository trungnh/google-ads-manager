<?php

namespace App\Models;

use CodeIgniter\Model;

class AdsAccountModel extends Model
{
    protected $table = 'ads_accounts';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'user_id', 
        'customer_id', 
        'customer_name', 
        'currency_code', 
        'time_zone', 
        'status', 
        'last_synced',
        'created_at',
        'updated_at'
    ];
    
    protected $useTimestamps = false;
    
    protected $validationRules = [
        'user_id' => 'required|numeric',
        'customer_id' => 'required|string',
        'customer_name' => 'required|string'
    ];
}