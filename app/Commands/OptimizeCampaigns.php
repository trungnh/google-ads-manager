<?php

namespace App\Commands;

use App\Models\AdsAccountSettingsModel;
use App\Models\GoogleTokenModel;
use App\Services\GoogleAdsService;
use App\Services\GoogleSheetService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Commands;

class OptimizeCampaigns extends BaseCommand
{
    protected $group       = 'Ads';
    protected $name       = 'ads:optimize';
    protected $description = 'Tối ưu chiến dịch quảng cáo tự động';

    protected $googleAdsService;
    protected $googleSheetService;
    protected $adsAccountSettingsModel;
    protected $googleTokenModel;

    public function __construct(Commands $commands)
    {
        parent::__construct($commands);
        $this->googleAdsService = new GoogleAdsService();
        $this->googleSheetService = new GoogleSheetService();
        $this->adsAccountSettingsModel = new AdsAccountSettingsModel();
        $this->googleTokenModel = new GoogleTokenModel();
    }

    public function run(array $params)
    {
        try {
            // Lấy danh sách tài khoản cần tối ưu
            $accounts = $this->adsAccountSettingsModel->getAccountsForOptimization();
            
            if (empty($accounts)) {
                CLI::write('Không có tài khoản nào cần tối ưu.', 'yellow');
                return;
            }

            foreach ($accounts as $account) {
                CLI::write("Đang tối ưu tài khoản: {$account['name']}", 'green');
                
                try {
                    // Kiểm tra và refresh token trước khi xử lý
                    $this->ensureValidToken($account['user_id']);
                    $this->optimizeCampaigns($account);
                } catch (\Exception $e) {
                    CLI::write("Lỗi khi tối ưu tài khoản {$account['name']}: " . $e->getMessage(), 'red');
                    log_message('error', "Lỗi tối ưu tài khoản {$account['name']}: " . $e->getMessage());
                }
            }

            CLI::write('Hoàn thành tối ưu chiến dịch.', 'green');
        } catch (\Exception $e) {
            CLI::write('Lỗi: ' . $e->getMessage(), 'red');
            log_message('error', 'Lỗi tối ưu chiến dịch: ' . $e->getMessage());
        }
    }

    protected function ensureValidToken($userId)
    {
        try {
            // Lấy token hiện tại
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            
            if (empty($tokenData)) {
                throw new \Exception('Không tìm thấy token cho user');
            }

            // Kiểm tra token có sắp hết hạn không (ít hơn 5 phút)
            $expiresIn = strtotime($tokenData['expires_at']) - time();
            if ($expiresIn < 300) { // 5 phút = 300 giây
                CLI::write("Token sắp hết hạn, đang refresh...", 'yellow');
                
                // Refresh token
                $newToken = $this->googleAdsService->refreshToken($tokenData['refresh_token']);
                if (!$newToken) {
                    throw new \Exception('Không thể refresh token');
                }

                // Cập nhật token mới vào database
                $this->googleTokenModel->update($tokenData['id'], [
                    'access_token' => $newToken['access_token'],
                    'expires_at' => date('Y-m-d H:i:s', time() + $newToken['expires_in']),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                CLI::write("Đã refresh token thành công", 'green');
            }
        } catch (\Exception $e) {
            log_message('error', "Lỗi refresh token cho user {$userId}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function optimizeCampaigns($account)
    {
        try {
            // Lấy dữ liệu chiến dịch realtime từ Google Ads
            $campaigns = $this->googleAdsService->getTodayCampaignMetrics($account['customer_id']);
            
            // Lấy dữ liệu chuyển đổi thực tế từ Google Sheet
            $sheetData = $this->googleSheetService->getConversionsFromCsv(
                $account['gsheet1'], 
                date('Y-m-d'),
                [
                    'date_col' => $account['gsheet_date_col'],
                    'phone_col' => $account['gsheet_phone_col'],
                    'value_col' => $account['gsheet_value_col'],
                    'campaign_col' => $account['gsheet_campaign_col']
                ]
            );

            foreach ($campaigns as $campaign) {
                $shouldPause = false;
                $shouldIncreaseBudget = false;
                $action = '';

                // Lấy dữ liệu chuyển đổi thực tế cho chiến dịch này
                $campaignConversions = isset($sheetData[$campaign['campaign_id']]) ? $sheetData[$campaign['campaign_id']] : [
                    'conversions' => 0,
                    'value' => 0
                ];

                // Tính CPA thực tế
                $realCpa = $campaignConversions['conversions'] > 0 
                    ? $campaign['cost'] / $campaignConversions['conversions'] 
                    : 0;

                // Kiểm tra CPA thực tế
                if ($realCpa > $account['cpa_threshold']) {
                    $shouldPause = true;
                    $action = "CPA thực tế ({$realCpa}) vượt ngưỡng ({$account['cpa_threshold']})";
                }
                // Kiểm tra chi tiêu và chuyển đổi thực tế
                elseif ($campaign['cost'] > $account['cpa_threshold'] && $campaignConversions['conversions'] == 0) {
                    $shouldPause = true;
                    $action = "Chi tiêu ({$campaign['cost']}) vượt ngưỡng ({$account['cpa_threshold']}) và không có chuyển đổi thực tế";
                }
                // Kiểm tra tăng ngân sách
                elseif ($campaign['cost'] > ($campaign['budget'] * 0.5)) {
                    $shouldIncreaseBudget = true;
                    $action = "Chi tiêu ({$campaign['cost']}) vượt 50% ngân sách ({$campaign['budget']})";
                }

                if ($shouldPause || $shouldIncreaseBudget) {
                    $this->executeCampaignAction($account, $campaign, $shouldPause, $shouldIncreaseBudget, $action);
                }
            }

            // Cập nhật thời gian chạy cuối cùng
            $this->adsAccountSettingsModel->update($account['id'], [
                'last_optimize_run' => date('Y-m-d H:i:s')
            ]);

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Lỗi tối ưu chiến dịch: ' . $e->getMessage());
            return false;
        }
    }

    protected function executeCampaignAction($account, $campaign, $shouldPause, $shouldIncreaseBudget, $action)
    {
        try {
            // Đảm bảo token hợp lệ trước khi thực hiện hành động
            $this->ensureValidToken($account['user_id']);
            
            $accessToken = $this->googleAdsService->getAccessToken($account);
            if (!$accessToken) {
                throw new \Exception('Không thể lấy access token');
            }

            if ($shouldPause) {
                $this->googleAdsService->pauseCampaign($accessToken, $campaign['campaign_id']);
                log_message('info', "Đã tạm dừng chiến dịch {$campaign['campaign_id']}: {$action}");
            } elseif ($shouldIncreaseBudget) {
                $newBudget = $campaign['budget'] + $account['increase_budget'];
                $this->googleAdsService->updateCampaignBudget($accessToken, $campaign['campaign_id'], $newBudget);
                log_message('info', "Đã tăng ngân sách chiến dịch {$campaign['campaign_id']} lên {$newBudget}: {$action}");
            }
        } catch (\Exception $e) {
            log_message('error', "Lỗi thực hiện hành động cho chiến dịch {$campaign['campaign_id']}: " . $e->getMessage());
        }
    }
} 