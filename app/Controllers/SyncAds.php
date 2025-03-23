<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserSettingsModel;
use App\Models\AdsAccountModel;
use App\Models\GoogleTokenModel;
use App\Services\GoogleAdsService;
use Exception;

class SyncAds extends BaseController
{
    protected $userSettingsModel;
    protected $adsAccountModel;
    protected $googleTokenModel;
    protected $googleAdsService;
    protected $googleAuth;

    public function __construct()
    {
        $this->userSettingsModel = new UserSettingsModel();
        $this->adsAccountModel = new AdsAccountModel();
        $this->googleTokenModel = new GoogleTokenModel();
        $this->googleAdsService = new GoogleAdsService();
        $this->googleAuth = new GoogleAuth(); // Sử dụng GoogleAuth controller đã có
    }

    public function index()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');
        
        // Lấy settings của user
        $settings = $this->userSettingsModel->where('user_id', $userId)->first();
        
        // Kiểm tra xem đã kết nối với Google Ads chưa
        $tokenData = $this->googleTokenModel->getValidToken($userId);
        $isConnected = !empty($tokenData);
        
        $data = [
            'title' => 'Đồng bộ tài khoản Google Ads',
            'settings' => $settings,
            'isConnected' => $isConnected
        ];
        
        return view('sync_ads/index', $data);
    }

    public function syncAccounts()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục');
        }

        $userId = session()->get('id');
        
        try {
            // Lấy access token từ database
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            
            if (empty($tokenData) || empty($tokenData['access_token'])) {
                return redirect()->to('/google/oauth')->with('error', 'Bạn cần kết nối với Google Ads trước');
            }
            
            // Kiểm tra xem token đã hết hạn chưa, nếu hết hạn thì refresh
            if (strtotime($tokenData['expires_at']) <= time() && !empty($tokenData['refresh_token'])) {
                $newTokenData = $this->googleAuth->refreshToken($tokenData['refresh_token']);
                
                if (!$newTokenData || isset($newTokenData['error'])) {
                    return redirect()->to('/google/oauth')->with('error', 'Token hết hạn, vui lòng kết nối lại với Google Ads');
                }
                
                // Lưu token mới vào database
                $this->googleTokenModel->saveToken($userId, $newTokenData);
                
                // Cập nhật lại tokenData
                $tokenData['access_token'] = $newTokenData['access_token'];
            }
            
            // Lấy MCC ID từ settings (nếu có)
            $settings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $settings['mcc_id'] ?? null;
            
            // Lấy danh sách tài khoản
            $accounts = $this->googleAdsService->getAccessibleAccounts(
                $tokenData['access_token'], 
                $mccId
            );
            
            // Lưu các tài khoản vào database
            $this->saveAccountsToDatabase($userId, $accounts);
            
            return redirect()->to('/adsaccounts')->with('success', 'Đồng bộ tài khoản thành công');
        } catch (Exception $e) {
            log_message('error', 'Lỗi đồng bộ tài khoản: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi đồng bộ tài khoản: ' . $e->getMessage());
        }
    }
    
    protected function saveAccountsToDatabase($userId, $accounts)
    {
        foreach ($accounts as $account) {
            // Kiểm tra xem tài khoản đã tồn tại chưa
            $existingAccount = $this->adsAccountModel
                ->where('user_id', $userId)
                ->where('customer_id', $account['customer_id'])
                ->first();
            
            $now = date('Y-m-d H:i:s');
            
            if ($existingAccount) {
                // Update thông tin tài khoản
                $this->adsAccountModel->update($existingAccount['id'], [
                    'customer_name' => $account['customer_name'],
                    'currency_code' => $account['currency_code'],
                    'time_zone' => $account['time_zone'],
                    'status' => $account['status'] === 'ENABLED' ? 'ACTIVE' : $account['status'],
                    'last_synced' => $now,
                    'updated_at' => $now
                ]);
            } else {
                // Thêm tài khoản mới
                $this->adsAccountModel->insert([
                    'user_id' => $userId,
                    'customer_id' => $account['customer_id'],
                    'customer_name' => $account['customer_name'],
                    'currency_code' => $account['currency_code'],
                    'time_zone' => $account['time_zone'],
                    'status' => $account['status'] === 'ENABLED' ? 'ACTIVE' : $account['status'],
                    'last_synced' => $now,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }
    }
}