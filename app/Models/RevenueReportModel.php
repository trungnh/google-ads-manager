<?php

namespace App\Models;

use CodeIgniter\Model;

class RevenueReportModel extends Model
{
    protected $table = 'revenue_reports';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'product_id',
        'user_id',
        'month',
        'year',
        'name',
        'return_rate',
        'import_price',
        'shipping_fee',
        'income_tax',
        'ads_tax',
        'payment_fee'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getReportsByUser($userId)
    {
        $builder = $this->db->table($this->table);
        $builder->select('revenue_reports.*, products.name as product_name');
        $builder->join('products', 'products.id = revenue_reports.product_id', 'left');
        $builder->where('revenue_reports.user_id', $userId);
        $builder->orderBy('revenue_reports.year', 'DESC');
        $builder->orderBy('revenue_reports.month', 'DESC');
        return $builder->get()->getResultArray();
    }
}
