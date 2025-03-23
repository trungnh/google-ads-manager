<?php

namespace App\Controllers;

use App\Models\AdsAccountModel;
use App\Models\GoogleTokenModel;
use App\Models\UserSettingsModel;
use App\Services\GoogleAdsService;
use Exception;
use DateTime;

class Campaigns extends BaseController
{
    protected $adsAccountModel;
    protected $googleAdsService;
    protected $googleTokenModel;
    protected $userSettingsModel;

    public function __construct()
    {
        $this->adsAccountModel = new AdsAccountModel();
        $this->googleAdsService = new GoogleAdsService();
        $this->googleTokenModel = new GoogleTokenModel();
        $this->userSettingsModel = new UserSettingsModel();
    }

    public function index($customerId)
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        try {
            // Kiểm tra xem user có quyền truy cập account này không
            $account = $this->adsAccountModel
                ->where('user_id', $userId)
                ->where('customer_id', $customerId)
                ->first();

            if (!$account) {
                return redirect()->to('/adsaccounts')->with('error', 'Không có quyền truy cập tài khoản này');
            }

            $data = [
                'title' => 'Danh sách chiến dịch - ' . $account['customer_name'],
                'account' => $account,
                'campaigns' => []
            ];

            return view('campaigns/index', $data);

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi lấy danh sách chiến dịch: ' . $e->getMessage());
        }
    }

    public function loadCampaigns($customerId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            $userId = session()->get('id');
            $showPaused = $this->request->getGet('showPaused') === 'true';
            $startDate = $this->request->getGet('startDate');
            $endDate = $this->request->getGet('endDate');
            
            // Convert date format from dd/mm/yyyy to yyyy-mm-dd
            $startDateObj = DateTime::createFromFormat('d/m/Y', $startDate);
            $endDateObj = DateTime::createFromFormat('d/m/Y', $endDate);
            
            if (!$startDateObj || !$endDateObj) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Định dạng ngày không hợp lệ'
                ]);
            }
            
            $startDate = $startDateObj->format('Y-m-d');
            $endDate = $endDateObj->format('Y-m-d');
            
            // Lấy access token
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            if (empty($tokenData) || empty($tokenData['access_token'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Bạn cần kết nối lại với Google Ads']);
            }

            // Lấy MCC ID từ settings
            $settings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $settings['mcc_id'] ?? null;

            // Lấy danh sách chiến dịch
            $campaigns = $this->googleAdsService->getCampaigns(
                $customerId, 
                $tokenData['access_token'], 
                $mccId, 
                $showPaused,
                $startDate,
                $endDate
            );

            return $this->response->setJSON([
                'success' => true,
                'campaigns' => $campaigns
            ]);
        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách chiến dịch: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleStatus($customerId, $campaignId)
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            $userId = session()->get('id');
            
            // Lấy access token
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            if (empty($tokenData) || empty($tokenData['access_token'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Bạn cần kết nối lại với Google Ads']);
            }

            // Lấy MCC ID từ settings
            $settings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $settings['mcc_id'] ?? null;

            $result = $this->googleAdsService->toggleCampaignStatus($customerId, $campaignId, $tokenData['access_token'], $mccId);
            return $this->response->setJSON(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        } catch (Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
} 