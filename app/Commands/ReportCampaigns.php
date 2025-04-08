<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\GoogleAdsService;
use App\Services\GoogleSheetService;
use App\Services\TelegramService;
use App\Models\AdsAccountSettingsModel;
use App\Models\GoogleTokenModel;
use App\Models\UserSettingsModel;
use App\Models\AdsAccountModel;
use App\Models\OptimizeLogsModel;
use App\Models\CampaignsDataModel;

class ReportCampaigns extends BaseCommand
{
    protected $group = 'Ads';
    protected $name = 'ads:report';
    protected $description = 'Báo cáo chiến dịch quảng cáo';

    protected $googleAdsService;
    protected $googleSheetService;
    protected $adsAccountSettingsModel;
    protected $googleTokenModel;
    protected $userSettingsModel;
    protected $telegramService;
    protected $adsAccountsModel;
    protected $optimizeLogsModel;
    protected $campaignsDataModel;

    public function __construct()
    {
        $this->googleAdsService = new GoogleAdsService();
        $this->googleSheetService = new GoogleSheetService();
        $this->telegramService = new TelegramService();
        $this->adsAccountSettingsModel = new AdsAccountSettingsModel();
        $this->googleTokenModel = new GoogleTokenModel();
        $this->userSettingsModel = new UserSettingsModel();
        $this->adsAccountsModel = new AdsAccountModel();
        $this->optimizeLogsModel = new OptimizeLogsModel();
        $this->campaignsDataModel = new CampaignsDataModel();
    }

    public function run(array $params)
    {
        $hour = date('H');
        if($hour < 7 || $hour > 21){
            CLI::write("Thời gian không hợp lệ, chỉ chạy từ 7:00 đến 22:00", 'yellow');
            return;
        }
        try {
            // Lấy danh sách tài khoản cần báo cáo
            $accounts = $this->adsAccountsModel->getAccountsForReporting();  
    
            if (empty($accounts)) {
                $message = 'Không có tài khoản nào cần báo cáo.';
                CLI::write($message, 'yellow');
                return;
            }

            $accounts = $this->adsAccountsModel->getAccountsForReporting();
            $processedAccounts = [];
            foreach($accounts as $account){
                if(in_array($account['customer_id'], $processedAccounts)){
                    continue;
                }

                $userSettings = $this->userSettingsModel->where('user_id', $account['user_id'])->first();
                $mccId = $userSettings['mcc_id'] ?? null;
                // Nếu tài khoản là tài khoản MCC thì bỏ qua
                if ($account['customer_id'] == $mccId){
                    continue;
                }

                $linkedUsers = $this->adsAccountsModel->getLinkedUsers($account['customer_id']);
                $telegramChatIds = [];
                foreach($linkedUsers as $linkedUser){
                    $userSettings = $this->userSettingsModel->where('user_id', $linkedUser['user_id'])->first();
                    $telegramChatId = $userSettings['telegram_chat_id'] ?? null;
                    if($telegramChatId){
                        $telegramChatIds[] = $telegramChatId;
                    }
                }

                // Kiểm tra và refresh token trước khi xử lý
                $tokenData = $this->ensureValidToken($account['user_id']);
                if (!$tokenData) {
                    throw new \Exception('Không thể lấy token hợp lệ');
                }

                $this->reportCampaigns($account, $tokenData['access_token'], $mccId, $telegramChatIds);
                $processedAccounts[] = $account['customer_id'];
                
            }
        } catch (Exception $e) {
            log_message('error', 'Lỗi khi báo cáo chiến dịch: ' . $e->getMessage());
            foreach($telegramChatIds as $telegramChatId){
                $this->telegramService->sendMessage("❌ Lỗi khi báo cáo chiến dịch: " . $account['customer_id'], $telegramChatId);
            }
            CLI::write('Lỗi khi báo cáo chiến dịch: ' . $e->getMessage(), 'red');
        }
    }

    protected function reportCampaigns($account, $accessToken, $mccId, $telegramChatIds)
    {
        // Kiểm tra các trường bắt buộc
        if (!isset($account['customer_id']) || !isset($account['id'])) {
            throw new \Exception('Thiếu thông tin customer_id hoặc account id');
        }

        // Lấy dữ liệu chiến dịch realtime từ Google Ads
        try {
            $campaigns = $this->googleAdsService->getCampaigns($account['customer_id'], $accessToken, $mccId, false, date('Y-m-d'), date('Y-m-d'));
            if (empty($campaigns)) {
                CLI::write("Không tìm thấy chiến dịch nào cho tài khoản {$account['customer_id']}", 'yellow');
                return false;
            }
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '401') !== false) {
                CLI::write("Token không hợp lệ, đang thử refresh...", 'yellow');
                // Thử refresh token và gọi lại API
                $newToken = $this->ensureValidToken($account['user_id']);
                $campaigns = $this->googleAdsService->getCampaigns($account['customer_id'], $newToken['access_token'], $mccId, false, date('Y-m-d'), date('Y-m-d'));
            } else {
                log_message('error', 'Lỗi tài khoản: ' . $account['customer_id'] . ' - ' . $e->getMessage());
                foreach($telegramChatIds as $telegramChatId){
                    $this->telegramService->sendMessage("❌ Lỗi tài khoản - " . $account['customer_id'], $telegramChatId);
                }
                return;
            }
        }

        // Lấy dữ liệu chuyển đổi thực tế từ Google Sheet
        // Sử dụng giá trị mặc định cho cấu hình cột
        $columnConfig = [
            'gsheet_date_col' => 'A',
            'gsheet_date_col' => 'B',
            'gsheet_value_col' => 'C',
            'gsheet_campaign_col' => 'D'
        ];

        $accountSettings = $this->adsAccountSettingsModel->where('account_id', $account['id'])->first();
        // Nếu có cấu hình trong settings thì sử dụng
        if (isset($accountSettings['gsheet_date_col'])) $columnConfig['gsheet_date_col'] = $accountSettings['gsheet_date_col'];
        if (isset($accountSettings['gsheet_phone_col'])) $columnConfig['gsheet_phone_col'] = $accountSettings['gsheet_phone_col'];
        if (isset($accountSettings['gsheet_value_col'])) $columnConfig['gsheet_value_col'] = $accountSettings['gsheet_value_col'];
        if (isset($accountSettings['gsheet_campaign_col'])) $columnConfig['gsheet_campaign_col'] = $accountSettings['gsheet_campaign_col'];

        $sheetData = [];
        if (!empty($accountSettings['gsheet1'])) {
            try {
                $sheetData = $this->googleSheetService->getConversionsFromCsv(
                    $accountSettings['gsheet1'],
                    date('Y-m-d'),
                    date('Y-m-d'),
                    $columnConfig
                );
            } catch (\Exception $e) {
                CLI::write("Lỗi đọc dữ liệu Google Sheet: " . $e->getMessage(), 'yellow');
            }
        }
        // Lấy dữ liệu chuyển đổi thực tế từ Google Sheet 2
        $sheetData2 = [];
        if (!empty($accountSettings['gsheet2'])) {
            try {
                $sheetData2 = $this->googleSheetService->getConversionsFromCsv(
                    $accountSettings['gsheet2'],
                    date('Y-m-d'),
                    date('Y-m-d'),
                    $columnConfig
                );
            } catch (\Exception $e) {
                CLI::write("Lỗi đọc dữ liệu Google Sheet: " . $e->getMessage(), 'yellow');
            }
        }

        $totalSheetData = [];
        foreach($sheetData as $key => $value){
            $totalSheetData[$key] = $value;
            if (isset($sheetData2[$key])) {
                $totalSheetData[$key]['conversions'] += $sheetData2[$key]['conversions'];
                $totalSheetData[$key]['conversion_value'] += $sheetData2[$key]['conversion_value'];
            }
        }

        $reportMessage = "====== {$account['customer_name']} =======\n";
        $totalConversions = 0;
        $totalConversionValue = 0;
        $totalCost = 0;
        $campaignsData = [];
        foreach ($campaigns as $campaign) {
            if (!isset($campaign['campaign_id']) || !isset($campaign['cost']) || !isset($campaign['budget'])) {
                CLI::write("Bỏ qua chiến dịch không hợp lệ: thiếu thông tin bắt buộc", 'yellow');
                foreach($telegramChatIds as $telegramChatId){
                    $this->telegramService->sendMessage("❌ Bỏ qua chiến dịch không hợp lệ: thiếu thông tin bắt buộc", $telegramChatId);
                }   
                continue;
            }
            // Lấy dữ liệu chuyển đổi thực tế cho chiến dịch này
            $campaignConversions = isset($totalSheetData[$campaign['campaign_id']]) ? $totalSheetData[$campaign['campaign_id']] : [
                'conversions' => 0,
                'conversion_value' => 0
            ];
            // Tính CPA và ROAS thực tế
            $realCpa = $campaignConversions['conversions'] > 0 
                ? $campaign['cost'] / $campaignConversions['conversions'] 
                : 0;

            $realRoas = $campaign['cost'] > 0 
                ? $campaignConversions['conversion_value'] / $campaign['cost']
                : 0;

            $totalConversions += $campaignConversions['conversions'];
            $totalConversionValue += $campaignConversions['conversion_value'];
            $totalCost += $campaign['cost'];

            $saveCampaignData = $campaign;
            $saveCampaignData['real_cpa'] = $realCpa;
            $saveCampaignData['real_roas'] = $realRoas;
            $saveCampaignData['real_conversions'] = $campaignConversions['conversions'];
            $saveCampaignData['real_conversion_value'] = $campaignConversions['conversion_value'];
            $campaignsData[] = $saveCampaignData;
        }

        // Save campaign data
        $this->campaignsDataModel->saveCampaignsData($account['customer_id'], $campaignsData, date('Y-m-d'));

        $reportMessage .= "💰 Chi tiêu: " . number_format($totalCost, 0, '', '.')."đ\n";
        $reportMessage .= "🛒 Đơn: " . number_format($totalConversions, 0, '', '.')."\n";
        if($totalConversions > 0){
            $reportMessage .= "🎯 CPA: " . number_format($totalCost / $totalConversions, 0, '', '.')."đ\n";
        } else {
            $reportMessage .= "🎯 CPA: 0\n";
        }   
        if($totalCost > 0){
            $reportMessage .= "🎯 ROAS: " . number_format($totalConversionValue / $totalCost, 1, ',', '.')."\n";
        } else {
            $reportMessage .= "🎯 ROAS: 0\n";
        }
        
        $reportMessage .= "====== END ======\n";

        foreach($telegramChatIds as $telegramChatId){
            $this->telegramService->sendMessage($reportMessage, $telegramChatId);
        }
    }

    protected function ensureValidToken($userId)
    {
        try {
            if (empty($userId)) {
                throw new \Exception('User ID không hợp lệ');
            }

            // Lấy token hiện tại
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            
            if (empty($tokenData)) {
                throw new \Exception('Không tìm thấy token cho user');
            }

            if (!isset($tokenData['refresh_token']) || !isset($tokenData['expires_at']) || !isset($tokenData['access_token'])) {
                throw new \Exception('Token không hợp lệ: thiếu thông tin token');
            }

            // Kiểm tra token có sắp hết hạn không (ít hơn 5 phút)
            $expiresIn = strtotime($tokenData['expires_at']) - time();
            if ($expiresIn < 300) { // 5 phút = 300 giây
                CLI::write("Token sắp hết hạn, đang refresh...", 'yellow');
                
                // Refresh token
                $newToken = $this->googleAdsService->refreshToken($tokenData['refresh_token']);
                if (!$newToken || !isset($newToken['access_token']) || !isset($newToken['expires_in'])) {
                    throw new \Exception('Không thể refresh token: dữ liệu token không hợp lệ');
                }

                // Cập nhật token mới vào database
                $this->googleTokenModel->update($tokenData['id'], [
                    'access_token' => $newToken['access_token'],
                    'expires_at' => date('Y-m-d H:i:s', time() + $newToken['expires_in']),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                CLI::write("Đã refresh token thành công", 'green');
                return [
                    'access_token' => $newToken['access_token'],
                    'expires_at' => date('Y-m-d H:i:s', time() + $newToken['expires_in'])
                ];
            }

            return $tokenData;
        } catch (\Exception $e) {
            log_message('error', "Lỗi refresh token cho user {$userId}: " . $e->getMessage());
            throw $e;
        }
    }


}
