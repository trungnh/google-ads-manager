<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AdsAccountModel;
use App\Models\AdsAccountSettingsModel;

class AdsAccounts extends BaseController
{
    protected $adsAccountModel;
    protected $adsAccountSettingsModel;

    public function __construct()
    {
        $this->adsAccountModel = new AdsAccountModel();
        $this->adsAccountSettingsModel = new AdsAccountSettingsModel();
    }

    public function index()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $userId = session()->get('id');
        
        // Lấy danh sách tài khoản ads
        $accounts = $this->adsAccountModel
            ->where('user_id', $userId)
            ->orderBy('order', 'ASC')
            ->findAll();
        
        $data = [
            'title' => 'Google Ads Accounts',
            'accounts' => $accounts
        ];
        
        return view('ads_accounts/index', $data);
    }

    public function delete($id = null)
    {
        // Kiểm tra đăng nhập và quyền
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        if (!in_array(session()->get('role'), ['superadmin', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Permission denied']);
        }

        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid account ID']);
        }

        try {
            // Xóa settings của account trước
            $this->adsAccountSettingsModel->where('account_id', $id)->delete();
            
            // Xóa account
            $this->adsAccountModel->delete($id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting account: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error deleting account'
            ]);
        }
    }
}