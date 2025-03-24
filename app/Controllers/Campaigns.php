<?php

namespace App\Controllers;

use App\Models\AdsAccountModel;
use App\Models\GoogleTokenModel;
use App\Models\UserSettingsModel;
use App\Models\CampaignsDataModel;
use App\Services\GoogleAdsService;
use App\Models\AdsAccountSettingsModel;
use App\Services\GoogleSheetService;
use Exception;
use DateTime;

class Campaigns extends BaseController
{
    protected $adsAccountModel;
    protected $googleAdsService;
    protected $googleTokenModel;
    protected $userSettingsModel;
    protected $campaignsDataModel;
    protected $adsAccountSettingsModel;
    protected $googleSheetService;

    public function __construct()
    {
        $this->adsAccountModel = new AdsAccountModel();
        $this->googleAdsService = new GoogleAdsService();
        $this->googleTokenModel = new GoogleTokenModel();
        $this->userSettingsModel = new UserSettingsModel();
        $this->campaignsDataModel = new CampaignsDataModel();
        $this->adsAccountSettingsModel = new AdsAccountSettingsModel();
        $this->googleSheetService = new GoogleSheetService();
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

    protected function processRealConversions($campaigns, $gsheetUrl, $date, $settings)
    {
        if (empty($gsheetUrl)) {
            return $campaigns;
        }

        // Lấy dữ liệu chuyển đổi từ Google Sheet
        $sheetData = $this->googleSheetService->getConversionsFromCsv($gsheetUrl, $date, $settings);

        // Tính toán các chỉ số thực tế cho mỗi chiến dịch
        foreach ($campaigns as &$campaign) {
            // Đảm bảo campaign_id tồn tại
            if (!isset($campaign['campaign_id'])) {
                log_message('error', 'Campaign data missing campaign_id: ' . json_encode($campaign));
                continue;
            }
            
            $campaignId = $campaign['campaign_id'];
            
            // Nếu có dữ liệu chuyển đổi cho chiến dịch này
            if (isset($sheetData[$campaignId])) {
                $campaign['real_conversions'] = $sheetData[$campaignId]['conversions'];
                $campaign['real_conversion_value'] = $sheetData[$campaignId]['conversion_value'];
                $campaign['real_conversion_rate'] = $campaign['clicks'] > 0 
                    ? ($sheetData[$campaignId]['conversions'] / $campaign['clicks']) * 100 
                    : 0;
                $campaign['real_cpa'] = $sheetData[$campaignId]['conversions'] > 0 
                    ? $campaign['cost'] / $sheetData[$campaignId]['conversions']
                    : 0;
            } else {
                // Nếu không có dữ liệu chuyển đổi, set về 0
                $campaign['real_conversions'] = 0;
                $campaign['real_conversion_value'] = 0;
                $campaign['real_conversion_rate'] = 0;
                $campaign['real_cpa'] = 0;
            }
        }

        return $campaigns;
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
            $forceUpdate = $this->request->getGet('forceUpdate') === 'true';
            
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

            // Lấy account settings để đọc URL Google Sheet và cấu hình cột
            $account = $this->adsAccountModel->where('customer_id', $customerId)->first();
            $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($account['id']);
            $gsheetUrl = $settings['gsheet1'] ?? null;

            // Nếu ngày bắt đầu và kết thúc là cùng ngày
            if ($startDate === $endDate) {
                // Kiểm tra xem có data trong database không và không phải force update
                if (!$forceUpdate) {
                    $campaigns = $this->campaignsDataModel->getCampaignsByDate($customerId, $startDate);
                    $lastUpdateTime = $this->campaignsDataModel->getLastUpdateTime($customerId, $startDate);
                    
                    if (!empty($campaigns)) {
                        return $this->response->setJSON([
                            'success' => true,
                            'campaigns' => $campaigns,
                            'lastUpdateTime' => $lastUpdateTime,
                            'isFromCache' => true
                        ]);
                    }
                }
            }
            
            // Lấy access token
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            if (empty($tokenData) || empty($tokenData['access_token'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Bạn cần kết nối lại với Google Ads']);
            }

            // Lấy MCC ID từ settings
            $userSettings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $userSettings['mcc_id'] ?? null;

            // Lấy danh sách chiến dịch từ API
            $campaigns = $this->googleAdsService->getCampaigns(
                $customerId, 
                $tokenData['access_token'], 
                $mccId, 
                $showPaused,
                $startDate,
                $endDate
            );

            // Nếu ngày bắt đầu và kết thúc là cùng ngày, xử lý dữ liệu từ Google Sheet
            if ($startDate === $endDate) {
                $campaigns = $this->processRealConversions($campaigns, $gsheetUrl, $startDate, $settings);
                $this->campaignsDataModel->saveCampaignsData($customerId, $startDate, $campaigns);
                $lastUpdateTime = date('Y-m-d H:i:s');
            } else {
                $lastUpdateTime = null;
            }

            return $this->response->setJSON([
                'success' => true,
                'campaigns' => $campaigns,
                'lastUpdateTime' => $lastUpdateTime,
                'isFromCache' => false
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