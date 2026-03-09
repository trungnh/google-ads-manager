<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductAdsAccountModel extends Model
{
    protected $table = 'product_ads_accounts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['product_id', 'customer_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function updateMappings($productId, $customerIds)
    {
        $this->where('product_id', $productId)->delete();
        if (!empty($customerIds) && is_array($customerIds)) {
            $data = [];
            foreach ($customerIds as $customerId) {
                $data[] = [
                    'product_id' => $productId,
                    'customer_id' => $customerId
                ];
            }
            $this->insertBatch($data);
        }
    }
}
