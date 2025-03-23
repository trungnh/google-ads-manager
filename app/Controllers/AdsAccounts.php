<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AdsAccountModel;

class AdsAccounts extends BaseController
{
    protected $adsAccountModel;

    public function __construct()
    {
        $this->adsAccountModel = new AdsAccountModel();
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
            ->orderBy('last_synced', 'DESC')
            ->findAll();
        
        $data = [
            'title' => 'Google Ads Accounts',
            'accounts' => $accounts
        ];
        
        return view('ads_accounts/index', $data);
    }
}