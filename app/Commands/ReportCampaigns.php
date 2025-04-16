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
        } catch (\Exception $e) {
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
        try {
            $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($account['id']);
            // Check trường hợp ads account thuộc nhiều user khác nhau. Chỉ check 1 setting duy nhất
            if (!$settings) {
                $tmpAccounts = $this->adsAccountModel->getAccountsByCustomerId($account['customer_id']);
                foreach ($tmpAccounts as $acc) {
                    $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($acc['id']);
                    if ($settings) {
                        break;
                    }
                }
            }
            // Lấy dữ liệu chuyển đổi thực tế từ Google Sheet
            $gsheetUrl = $settings['gsheet1'] ?? null;
            $gsheetUrl2 = $settings['gsheet2'] ?? null;
            if (empty($gsheetUrl) && empty($gsheetUrl2)) {
                return;
            }   
            if (!empty($campaigns) && !empty($gsheetUrl)) {
                $campaigns = $this->googleSheetService->processRealConversions($campaigns, $gsheetUrl, date('Y-m-d'), date('Y-m-d'), $settings);
            }
            if (!empty($campaigns) && !empty($gsheetUrl2)) {
                $campaigns = $this->googleSheetService->processRealConversions($campaigns, $gsheetUrl2, date('Y-m-d'), date('Y-m-d'), $settings);
            }
        } catch (\Exception $e) {
            log_message('error', 'Lỗi tính toán real conversions: ' . $account['customer_id'] . ' - ' . $e->getMessage());
            foreach($telegramChatIds as $telegramChatId){
                $this->telegramService->sendMessage("❌ Lỗi tính toán real conversions - " . $account['customer_id'], $telegramChatId);
            }
            return;
        }

        try {
            $reportMessage = "====== {$account['customer_name']} =======\n";
            $totalConversions = 0;
            $totalConversionValue = 0;
            $totalCost = 0;
            foreach ($campaigns as $campaign) {
                if (!isset($campaign['campaign_id']) || !isset($campaign['cost']) || !isset($campaign['budget'])) {
                    CLI::write("Bỏ qua chiến dịch không hợp lệ: thiếu thông tin bắt buộc", 'yellow');
                    foreach($telegramChatIds as $telegramChatId){
                        $this->telegramService->sendMessage("❌ Bỏ qua chiến dịch không hợp lệ: thiếu thông tin bắt buộc", $telegramChatId);
                    }   
                    continue;
                }
                // Lấy dữ liệu chuyển đổi thực tế cho chiến dịch này
                if (isset($campaign['real_conversions'])) {
                    $realConversions = $campaign['real_conversions']?? 0;
                } else {
                    $realConversions = 0; 
                }
                if (isset($campaign['real_conversion_value'])) {
                    $realConversionValue = $campaign['real_conversion_value']?? 0; 
                } else {
                    $realConversionValue = 0;
                }

                $totalConversions += $realConversions;
                $totalConversionValue += $realConversionValue;
                $totalCost += $campaign['cost'];
            }

            // Save campaign data
            $this->campaignsDataModel->saveCampaignsData($account['customer_id'], $campaigns, date('Y-m-d'));

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
        } catch (\Exception $e) {
            log_message('error', 'Lỗi report tổng conversions: ' . $account['customer_id'] . ' - ' . $e->getMessage());
            foreach($telegramChatIds as $telegramChatId){
                $this->telegramService->sendMessage("❌ Lỗi report tổng conversions - " . $account['customer_id'], $telegramChatId);
            }
            return;
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
