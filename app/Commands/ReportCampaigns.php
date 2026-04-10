<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\GoogleAdsService;
use App\Services\GoogleSheetService;
use App\Services\PancakeService;
use App\Services\TelegramService;
use App\Models\AdsAccountSettingsModel;
use App\Models\GoogleTokenModel;
use App\Models\UserSettingsModel;
use App\Models\AdsAccountModel;
use App\Models\OptimizeLogsModel;
use App\Models\CampaignsDataModel;
use App\Models\ReportsModel;

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
    protected $reportsModel;
    protected $pancakeService;

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
        $this->reportsModel = new ReportsModel();
        $this->pancakeService = new PancakeService();
    }

    public function run(array $params)
    {
        $hour = date('H');
        if($hour < 5 || $hour > 21){
            //CLI::write("Thời gian không hợp lệ, chỉ chạy từ 7:00 đến 22:00", 'yellow');
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
                
                $this->telegramService->loadProxySettings($account['user_id']);
                $telegramChatId = $userSettings['report_telegram_chat_id'] ?? null;

                // Kiểm tra và refresh token trước khi xử lý
                $tokenData = $this->ensureValidToken($account['user_id']);
                if (!$tokenData) {
                    throw new \Exception('Không thể lấy token hợp lệ');
                }

                $this->reportCampaigns($account, $tokenData['access_token'], $mccId, $telegramChatId);
                $processedAccounts[] = $account['customer_id'];
                
            }
        } catch (\Exception $e) {
            log_message('error', 'Lỗi khi báo cáo chiến dịch: ' . $e->getMessage());
            $this->telegramService->sendMessage("❌ Lỗi khi báo cáo chiến dịch: " . $account['customer_id'], $telegramChatId);
            CLI::write('Lỗi khi báo cáo chiến dịch: ' . $e->getMessage(), 'red');
        }
    }

    protected function reportCampaigns($account, $accessToken, $mccId, $telegramChatId)
    {
        // Kiểm tra các trường bắt buộc
        if (!isset($account['customer_id']) || !isset($account['id'])) {
            throw new \Exception('Thiếu thông tin customer_id hoặc account id');
        }

        // Lấy dữ liệu chiến dịch realtime từ Google Ads
        try {
            $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($account['id']);
            // Check trường hợp ads account thuộc nhiều user khác nhau. Chỉ check 1 setting duy nhất
            if (!$settings) {
                $tmpAccounts = $this->adsAccountsModel->getAccountsByCustomerId($account['customer_id']);
                foreach ($tmpAccounts as $acc) {
                    $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($acc['id']);
                    if ($settings) {
                        break;
                    }
                }
            }

            $campaigns = $this->googleAdsService->getCampaignsWithRealConv($settings, $account['customer_id'], $accessToken, $mccId, true, date('Y-m-d'), date('Y-m-d'));

            if (empty($campaigns)) {
                CLI::write("Không tìm thấy chiến dịch nào cho tài khoản {$account['customer_id']}", 'yellow');
                return false;
            }
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '401') !== false) {
                CLI::write("Token không hợp lệ, đang thử refresh...", 'yellow');
                // Thử refresh token và gọi lại API
                $newToken = $this->ensureValidToken($account['user_id']);
                $campaigns = $this->googleAdsService->getCampaignsWithRealConv($settings, $account['customer_id'], $newToken, $mccId, true, date('Y-m-d'), date('Y-m-d'));
            } else {
                log_message('error', 'Lỗi tài khoản: ' . $account['customer_id'] . ' - ' . $e->getMessage());
                $this->telegramService->sendMessage("❌ Lỗi tài khoản - " . $account['customer_id'], $telegramChatId);
                return;
            }
        }

        try {
            $reportMessage = "====== <b>{$account['customer_name']}</b> =======\n";
            $totalConversions = 0;
            $totalConversionValue = 0;
            $totalCost = 0;
            $totalCampaigns = 0;
            $runningCampaigns = 0;
            $runningCost = 0;
            $runningConversions = 0;
            $runningConversionValue = 0;
            $pausedCampaigns = 0;
            $pausedConversion = 0;
            $pausedConversionValue = 0;
            foreach ($campaigns as $campaign) {
                if (!isset($campaign['campaign_id']) || !isset($campaign['cost']) || !isset($campaign['budget'])) {
                    CLI::write("Bỏ qua chiến dịch không hợp lệ: thiếu thông tin bắt buộc", 'yellow');
                    $this->telegramService->sendMessage("❌ Bỏ qua chiến dịch không hợp lệ: thiếu thông tin bắt buộc", $telegramChatId);  
                    continue;
                }

                // Bỏ qua chiến dịch không hoạt động và không chi tiêu
                if ($campaign['status'] == 'PAUSED' && $campaign['cost'] == 0) {
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
                if ($campaign['cost'] > 0) {
                    $totalCampaigns++;
                }

                // Đếm loại chiến dịch
                if ($campaign['status'] == 'ENABLED') {
                    $runningCampaigns++;
                    $runningCost += $campaign['cost'];
                    $runningConversions += $realConversions;
                    $runningConversionValue += $realConversionValue;  
                } else {
                    $pausedCampaigns++;
                    $pausedConversion += $realConversions;
                    $pausedConversionValue += $realConversionValue;
                }
            }

            // Bỏ qua tài khoản không có chi tiêu
            if ($totalCost == 0) {
                return;
            }

            // Save campaign data
            $this->campaignsDataModel->saveCampaignsData($account['customer_id'], $campaigns, date('Y-m-d'));
            // Save campaign reports
            $this->reportsModel->saveReportByCampaigns($account['user_id'], $account['customer_id'], $campaigns);

            $currencySymbol = $account['currency_code'] == 'VND' ? '₫' : '$';

            $reportMessage .= "☀️ <b>Camp hoạt động:</b> " . number_format($runningCampaigns, 0, '', '.')."\n";
            $reportMessage .= "💰 <b>Chi tiêu:</b> " . number_format($runningCost, 0, '', '.') . " " . $currencySymbol . "\n";
            $reportMessage .= "🛒 <b>Đơn:</b> " . number_format($runningConversions, 0, '', '.')."\n";
            if($runningConversions > 0){
                $reportMessage .= "🎯 <b>CPA:</b> " . number_format($runningCost / $runningConversions, 0, '', '.') . " " . $currencySymbol ."\n";
            } else {
                $reportMessage .= "🎯 <b>CPA:</b> 0\n";
            }   
            if($runningCost > 0){
                $reportMessage .= "🎯 <b>ROAS:</b> " . number_format($runningConversionValue / $runningCost, 1, ',', '.')."\n";
            } else {
                $reportMessage .= "🎯 <b>ROAS:</b> 0\n";
            }
            if ($totalCampaigns > $runningCampaigns) {
                $reportMessage .= "====== <b>Tổng số</b> ======\n";
                $reportMessage .= "☀️ <b>Camp:</b> " . number_format($totalCampaigns, 0, '', '.')."\n";
                $reportMessage .= "💰 <b>Chi tiêu:</b> " . number_format($totalCost, 0, '', '.') . " " . $currencySymbol . "\n";
                $reportMessage .= "🛒 <b>Đơn:</b> " . number_format($totalConversions, 0, '', '.')."\n";
                if($totalConversions > 0){
                    $reportMessage .= "🎯 <b>CPA:</b> " . number_format($totalCost / $totalConversions, 0, '', '.') . " " . $currencySymbol ."\n";
                } else {
                    $reportMessage .= "🎯 <b>CPA:</b> 0\n";
                }   
                if($totalCost > 0){
                    $reportMessage .= "🎯 <b>ROAS:</b> " . number_format($totalConversionValue / $totalCost, 1, ',', '.')."\n";
                } else {
                    $reportMessage .= "🎯 <b>ROAS:</b> 0\n";
                }
            }
            
            $reportMessage .= "========== END ==========\n";

            $this->telegramService->sendMessage($reportMessage, $telegramChatId);
        } catch (\Exception $e) {
            log_message('error', 'Lỗi report tổng conversions: ' . $account['customer_id'] . ' - ' . $e->getMessage());
            $this->telegramService->sendMessage("❌ Lỗi report tổng conversions - " . $account['customer_id'] . ': ' . $e->getMessage(), $telegramChatId);
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
