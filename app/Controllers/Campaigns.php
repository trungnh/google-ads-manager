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
        // 1. Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Vui lòng đăng nhập để tiếp tục');
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        try {
            // 2. Kiểm tra tài khoản hiện tại
            $account = $this->adsAccountModel
                ->where('user_id', $userId)
                ->where('customer_id', $customerId)
                ->orderBy('order', 'ASC')
                ->first();

            // 3. Nếu không tìm thấy tài khoản hiện tại, tìm tài khoản đầu tiên của user
            if (!$account) {
                $firstAccount = $this->adsAccountModel
                    ->where('user_id', $userId)
                    ->first();

                if ($firstAccount) {
                    return redirect()->to('/campaigns/index/' . $firstAccount['customer_id']);
                }

                // Nếu không có tài khoản nào
                session()->setFlashdata('error', 'Bạn chưa có tài khoản Google Ads nào');
                return redirect()->to('/adsaccounts');
            }

            // 4. Lấy danh sách tất cả tài khoản của user để hiển thị trong dropdown
            $accounts = $this->adsAccountModel
                ->where('user_id', $userId)
                ->orderBy('order', 'ASC')
                ->findAll();

            // 5. Lấy dữ liệu chiến dịch từ database
            $today = date('Y-m-d');
            $showPaused = $this->request->getGet('showPaused') === 'true';
            $campaigns = $this->campaignsDataModel->getCampaignsByDate($customerId, $today, $showPaused);
            $userSettings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $userSettings['mcc_id'] ?? null;

            $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($account['id']);
            // Check trường hợp ads account thuộc nhiều user khác nhau. Chỉ check 1 setting duy nhất
            if (!$settings) {
                $tmpAccounts = $this->adsAccountModel->getAccountsByCustomerId($account['customer_id']);
                if (!empty($tmpAccounts)) {
                    foreach ($tmpAccounts as $acc) {
                        if (!empty($acc['id'])) {
                            $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($acc['id']);
                            if ($settings) {
                                break;
                            }
                        }
                    }
                }
            }

            // 6. Nếu không có dữ liệu trong database, lấy từ API
            if (empty($campaigns)) {
                $tokenData = $this->googleTokenModel->getValidToken($userId);
                if (empty($tokenData) || empty($tokenData['access_token'])) {
                    session()->setFlashdata('error', 'Bạn cần kết nối lại với Google Ads');
                    return redirect()->to('/adsaccounts');
                }

                $campaigns = $this->googleAdsService->getCampaigns(
                    $customerId, 
                    $tokenData['access_token'], 
                    $mccId, 
                    $showPaused,
                    $today,
                    $today
                );

                $gsheetUrl = $settings['gsheet1'] ?? null;
                if (!empty($campaigns) && !empty($gsheetUrl)) {
                    $campaigns = $this->googleSheetService->processRealConversions($campaigns, $gsheetUrl, $today, $today, $settings);
                }
                $gsheetUrl2 = $settings['gsheet2'] ?? null;
                if (!empty($campaigns) && !empty($gsheetUrl2)) {
                    $campaigns = $this->googleSheetService->processRealConversions($campaigns, $gsheetUrl2, $today, $today, $settings);
                }

                $this->campaignsDataModel->saveCampaignsData($customerId, $campaigns);
            }

            // 7. Render view với dữ liệu
            return view('campaigns/index', [
                'title' => 'Danh sách chiến dịch - ' . $account['customer_name'],
                'account' => $account,
                'accounts' => $accounts,
                'campaigns' => $campaigns,
                'accountSettings' => $settings ?? [],
                'mccId' => $mccId
            ]);

        } catch (Exception $e) {
            log_message('error', 'Error in Campaigns::index: ' . $e->getMessage());
            session()->setFlashdata('error', 'Lỗi khi lấy danh sách chiến dịch: ' . $e->getMessage());
            return redirect()->to('/adsaccounts');
        }
    }

    public function loadCampaigns($customerId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để tiếp tục'
            ]);
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
            if (!$account || empty($account['id'])) {
                log_message('error', 'Account not found or invalid for customer ID: ' . $customerId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không tìm thấy tài khoản Google Ads hoặc tài khoản không hợp lệ'
                ]);
            }
            $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($account['id']);
            // Check trường hợp ads account thuộc nhiều user khác nhau. Chỉ check 1 setting duy nhất
            if (!$settings) {
                $tmpAccounts = $this->adsAccountModel->getAccountsByCustomerId($account['customer_id']);
                if (!empty($tmpAccounts)) {
                    foreach ($tmpAccounts as $acc) {
                        if (!empty($acc['id'])) {
                            $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($acc['id']);
                            if ($settings) {
                                break;
                            }
                        }
                    }
                }
            }
            $gsheetUrl = $settings['gsheet1'] ?? null;
            $gsheetUrl2 = $settings['gsheet2'] ?? null;
            // Lấy access token
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            if (empty($tokenData) || empty($tokenData['access_token'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Bạn cần kết nối lại với Google Ads']);
            }

            // Lấy MCC ID từ settings
            $userSettings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $userSettings['mcc_id'] ?? null;

            // Nếu ngày bắt đầu và kết thúc là cùng ngày
            if ($startDate === $endDate) {
                // Kiểm tra xem có data trong database không và không phải force update
                if (!$forceUpdate) {
                    $campaigns = $this->campaignsDataModel->getCampaignsByDate($customerId, $startDate, $showPaused);
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
            
            // Lấy danh sách chiến dịch từ API
            $campaigns = $this->googleAdsService->getCampaigns(
                $customerId, 
                $tokenData['access_token'], 
                $mccId, 
                $showPaused,
                $startDate,
                $endDate
            );
            // Xử lý dữ liệu chuyển đổi thực tế từ Google Sheet
            if (!empty($campaigns) && !empty($gsheetUrl)) {
                $campaigns = $this->googleSheetService->processRealConversions($campaigns, $gsheetUrl, $startDate, $endDate, $settings);
            }
            if (!empty($campaigns) && !empty($gsheetUrl2)) {
                $campaigns = $this->googleSheetService->processRealConversions($campaigns, $gsheetUrl2, $startDate, $endDate, $settings);
            }
            // Chỉ lưu vào database nếu ngày bắt đầu và kết thúc là cùng ngày
            if ($startDate === $endDate) {
                $this->campaignsDataModel->saveCampaignsData($customerId, $campaigns, $startDate);
                $lastUpdateTime = date('Y-m-d H:i:s');
                $responseCampaigns = $this->campaignsDataModel->getCampaignsByDate($customerId, $startDate, $showPaused);
                if (!empty($campaigns)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'campaigns' => $responseCampaigns,
                        'lastUpdateTime' => $lastUpdateTime,
                        'isFromCache' => true
                    ]);
                }

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
            log_message('error', 'Error in Campaigns::loadCampaigns: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách chiến dịch: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleStatus($customerId, $campaignId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            // Validate input
            if (empty($customerId)) {
                log_message('error', 'Customer ID is empty in toggleStatus');
                return $this->response->setJSON(['success' => false, 'message' => 'ID tài khoản không hợp lệ']);
            }

            if (empty($campaignId)) {
                log_message('error', 'Campaign ID is empty in toggleStatus');
                return $this->response->setJSON(['success' => false, 'message' => 'ID chiến dịch không hợp lệ']);
            }

            $userId = session()->get('id');
            $status = $this->request->getPost('status');
            
            // Validate status
            if (!in_array($status, ['ENABLED', 'PAUSED'])) {
                log_message('error', 'Invalid status in toggleStatus: ' . $status);
                return $this->response->setJSON(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
            }
            
            // Lấy access token
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            if (empty($tokenData) || empty($tokenData['access_token'])) {
                log_message('error', 'No valid access token found for user: ' . $userId);
                return $this->response->setJSON(['success' => false, 'message' => 'Bạn cần kết nối lại với Google Ads']);
            }

            // Lấy MCC ID từ settings
            $settings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $settings['mcc_id'] ?? null;

            log_message('info', 'Calling toggleCampaignStatus with params: customerId=' . $customerId . ', campaignId=' . $campaignId . ', status=' . $status);

            // Gọi API để toggle status
            $result = $this->googleAdsService->toggleCampaignStatus(
                $tokenData['access_token'],
                $customerId,
                $campaignId,
                $status,
                $mccId
            );

            if ($result === true) {
                $message = $status === 'ENABLED' ? 'Đã bật chiến dịch thành công' : 'Đã tắt chiến dịch thành công';
                $this->campaignsDataModel->saveCampaignStatus($customerId, $campaignId, $status);
                
                log_message('info', 'Successfully toggled campaign status: ' . $message);
                
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => $message,
                    'newStatus' => $status
                ]);
            } else {
                log_message('error', 'Failed to toggle campaign status');
                return $this->response->setJSON(['success' => false, 'message' => 'Không thể cập nhật trạng thái chiến dịch']);
            }
        } catch (Exception $e) {
            log_message('error', 'Lỗi khi cập nhật trạng thái chiến dịch: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateTarget($customerId, $campaignId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            $userId = session()->get('id');
            $type = $this->request->getPost('type'); // 'cpa' or 'roas'
            $value = $this->request->getPost('value');
            
            // Validate input
            if (!in_array($type, ['cpa', 'roas'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Loại mục tiêu không hợp lệ']);
            }
            
            if (!is_numeric($value) || $value <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Giá trị mục tiêu không hợp lệ']);
            }
            
            // Get access token
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            if (empty($tokenData) || empty($tokenData['access_token'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Bạn cần kết nối lại với Google Ads']);
            }

            // Get MCC ID from settings
            $settings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $settings['mcc_id'] ?? null;

            // Call API to update target
            $result = $this->googleAdsService->updateCampaignTarget(
                $tokenData['access_token'],
                $customerId,
                $campaignId,
                $type,
                $value,
                $mccId
            );

            if ($result === true) {
                // Update local data
                $campaigns = $this->campaignsDataModel->getCampaignsByID($customerId, $campaignId);
                if (!empty($campaigns)) {
                    $campaign = $campaigns[0];
                    if ($type === 'cpa') {
                        $campaign['target_cpa'] = $value;
                    } else {
                        $campaign['target_roas'] = $value;
                    }
                    $this->campaignsDataModel->saveCampaignsData($customerId, [$campaign]);
                }
                
                $message = $type === 'cpa' ? 'Đã cập nhật CPA mục tiêu thành công' : 'Đã cập nhật ROAS mục tiêu thành công';
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => $message,
                    'newValue' => $value
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Không thể cập nhật mục tiêu chiến dịch']);
            }
        } catch (Exception $e) {
            log_message('error', 'Lỗi khi cập nhật mục tiêu chiến dịch: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateBudget($customerId, $campaignId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            $userId = session()->get('id');
            $newBudget = $this->request->getPost('budget');
            
            if (!$newBudget) {
                return $this->response->setJSON(['success' => false, 'message' => 'Thiếu tham số cần thiết']);
            }
            
            // Lấy access token từ database
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            if (empty($tokenData) || empty($tokenData['access_token'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Bạn cần kết nối lại với Google Ads']);
            }

            // Lấy MCC ID từ settings - sử dụng phương pháp giống như trong toggleStatus
            $settings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $settings['mcc_id'] ?? null;

            // Cập nhật ngân sách
            $result = $this->googleAdsService->updateCampaignBudget(
                $tokenData['access_token'],
                $customerId,
                $campaignId,
                $newBudget,
                $mccId
            );
            
            // Cập nhật dữ liệu trong database
            $campaigns = $this->campaignsDataModel->getCampaignsByID($customerId, $campaignId);
            if (!empty($campaigns)) {
                $campaign = $campaigns[0];
                $campaign['budget'] = $newBudget;
                $this->campaignsDataModel->saveCampaignsData($customerId, [$campaign]);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Cập nhật ngân sách thành công',
                'new_budget' => $newBudget
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Lỗi khi cập nhật ngân sách chiến dịch: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lỗi khi cập nhật ngân sách: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCFLC($customerId, $campaignId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false,'message' => 'Unauthorized']);
        }

        try {
            $this->campaignsDataModel->saveCampaignCFLC($customerId, $campaignId);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Cập nhật cflc thành công',
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Lỗi khi cập nhật CFLC: '. $e->getMessage());
            return $this->response->setJSON([
               'success' => false,
               'message' => 'Lỗi khi cập nhật CFLC: '. $e->getMessage()
            ]);
        }
    }
}