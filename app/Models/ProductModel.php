<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'product_code',
        'name',
        'shipping_fee',
        'import_price',
        'selling_price',
        'return_rate',
        'keyword_campaign',
        'user_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Liên kết với bảng quảng cáo
    public function getProductWithAdsAccounts($userId = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select('products.*, GROUP_CONCAT(product_ads_accounts.customer_id) as ads_accounts');
        $builder->join('product_ads_accounts', 'product_ads_accounts.product_id = products.id', 'left');
        if ($userId) {
            $builder->where('products.user_id', $userId);
        }
        $builder->groupBy('products.id');
        $builder->orderBy('products.id', 'DESC');
        return $builder->get()->getResultArray();
    }

    public function getKeywordByCustomerId($customerId)
    {
        $builder = $this->db->table('product_ads_accounts');
        $builder->select('products.keyword_campaign');
        $builder->join('products', 'products.id = product_ads_accounts.product_id');
        $builder->where('product_ads_accounts.customer_id', $customerId);
        $result = $builder->get()->getRowArray();
        return $result['keyword_campaign'] ?? null;
    }
}
