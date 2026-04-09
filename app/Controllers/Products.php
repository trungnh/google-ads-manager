<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductAdsAccountModel;
use App\Models\AdsAccountModel;

class Products extends BaseController
{
    protected $productModel;
    protected $productAdsAccountModel;
    protected $adsAccountModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->productAdsAccountModel = new ProductAdsAccountModel();
        $this->adsAccountModel = new AdsAccountModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        $products = $this->productModel->getProductWithAdsAccounts($userId);

        // Mảng map customer_id -> name để hiên thị
        $adsAccounts = $this->adsAccountModel->where('user_id', $userId)->findAll();
        $adsAccountMap = [];
        foreach ($adsAccounts as $acc) {
            $adsAccountMap[$acc['customer_id']] = $acc['customer_name'] . ' (' . $acc['customer_id'] . ')';
        }

        return view('products/index', [
            'title' => 'Quản lý Sản phẩm',
            'products' => $products,
            'adsAccounts' => $adsAccounts,
            'adsAccountMap' => $adsAccountMap
        ]);
    }

    public function create()
    {
        $userId = session()->get('user_id');
        $data = [
            'product_code' => $this->request->getPost('product_code'),
            'name' => $this->request->getPost('name'),
            'shipping_fee' => $this->request->getPost('shipping_fee') ? str_replace(',', '', $this->request->getPost('shipping_fee')) : 0,
            'import_price' => $this->request->getPost('import_price') ? str_replace(',', '', $this->request->getPost('import_price')) : 0,
            'selling_price' => $this->request->getPost('selling_price') ? str_replace(',', '', $this->request->getPost('selling_price')) : 0,
            'return_rate' => $this->request->getPost('return_rate') ?: 0,
            'keyword_campaign' => $this->request->getPost('keyword_campaign'),
            'user_id' => $userId
        ];

        $customerIds = $this->request->getPost('customer_ids'); // array

        if ($this->productModel->insert($data)) {
            $productId = $this->productModel->getInsertID();
            $this->productAdsAccountModel->updateMappings($productId, $customerIds);
            return redirect()->to('/products')->with('success', 'Thêm sản phẩm thành công');
        } else {
            return redirect()->to('/products')->with('error', 'Có lỗi xảy ra khi thêm sản phẩm');
        }
    }

    public function update($id)
    {
        $userId = session()->get('user_id');
        $data = [
            'product_code' => $this->request->getPost('product_code'),
            'name' => $this->request->getPost('name'),
            'shipping_fee' => $this->request->getPost('shipping_fee') ? str_replace(',', '', $this->request->getPost('shipping_fee')) : 0,
            'import_price' => $this->request->getPost('import_price') ? str_replace(',', '', $this->request->getPost('import_price')) : 0,
            'selling_price' => $this->request->getPost('selling_price') ? str_replace(',', '', $this->request->getPost('selling_price')) : 0,
            'return_rate' => $this->request->getPost('return_rate') ?: 0,
            'keyword_campaign' => $this->request->getPost('keyword_campaign'),
        ];

        $customerIds = $this->request->getPost('customer_ids'); // array

        if ($this->productModel->update($id, $data)) {
            $this->productAdsAccountModel->updateMappings($id, $customerIds);
            return redirect()->to('/products')->with('success', 'Cập nhật sản phẩm thành công');
        } else {
            return redirect()->to('/products')->with('error', 'Có lỗi xảy ra khi cập nhật');
        }
    }

    public function delete($id)
    {
        if ($this->productModel->delete($id)) {
            $this->productAdsAccountModel->where('product_id', $id)->delete();
            return redirect()->to('/products')->with('success', 'Xóa sản phẩm thành công');
        } else {
            return redirect()->to('/products')->with('error', 'Có lỗi xảy ra khi xóa');
        }
    }
}
