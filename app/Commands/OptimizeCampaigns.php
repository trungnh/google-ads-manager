<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\GoogleAdsService;
use App\Services\GoogleSheetService;
use App\Models\AdsAccountSettingsModel;
use App\Models\GoogleTokenModel;
use App\Models\UserSettingsModel;

class OptimizeCampaigns extends BaseCommand
{
    protected $group       = 'Ads';
    protected $name       = 'ads:optimize';
    protected $description = 'Tối ưu chiến dịch quảng cáo tự động';

    protected $googleAdsService;
    protected $googleSheetService;
    protected $adsAccountSettingsModel;
    protected $googleTokenModel;
    protected $userSettingsModel;

    public function __construct()
    {
        $this->googleAdsService = new GoogleAdsService();
        $this->googleSheetService = new GoogleSheetService();
        $this->adsAccountSettingsModel = new AdsAccountSettingsModel();
        $this->googleTokenModel = new GoogleTokenModel();
        $this->userSettingsModel = new UserSettingsModel();
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
                // Kiểm tra các trường bắt buộc
                if (!isset($account['customer_id']) || !isset($account['user_id']) || !isset($account['id'])) {
                    CLI::write('Dữ liệu tài khoản không hợp lệ: thiếu thông tin bắt buộc' . $account['id'], 'red');
                    continue;
                }

                $accountName = $account['customer_name'] ?? $account['customer_id'] ?? 'Unknown Account';
                CLI::write("Đang tối ưu tài khoản: {$accountName}", 'green');
                
                try {
                    // Lấy MCC ID từ user settings
                    $userSettings = $this->userSettingsModel->where('user_id', $account['user_id'])->first();
                    $mccId = $userSettings['mcc_id'] ?? null;

                    // Kiểm tra và refresh token trước khi xử lý
                    $tokenData = $this->ensureValidToken($account['user_id']);
                    if (!$tokenData) {
                        throw new \Exception('Không thể lấy token hợp lệ');
                    }
                    $this->optimizeCampaigns($account, $tokenData['access_token'], $mccId);
                } catch (\Exception $e) {
                    CLI::write("Lỗi khi tối ưu tài khoản {$accountName}: " . $e->getMessage(), 'red');
                    log_message('error', "Lỗi tối ưu tài khoản {$accountName}: " . $e->getMessage());
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

    protected function optimizeCampaigns($account, $accessToken, $mccId = null)
    {
        try {
            // Kiểm tra các trường bắt buộc
            if (!isset($account['customer_id']) || !isset($account['id'])) {
                throw new \Exception('Thiếu thông tin customer_id hoặc account id');
            }

            // Lấy dữ liệu chiến dịch realtime từ Google Ads
            try {
                $campaigns = $this->googleAdsService->getTodayCampaignMetrics($account['customer_id'], $accessToken, $mccId);
                if (empty($campaigns)) {
                    CLI::write("Không tìm thấy chiến dịch nào cho tài khoản {$account['customer_id']}", 'yellow');
                    return false;
                }
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), '401') !== false) {
                    CLI::write("Token không hợp lệ, đang thử refresh...", 'yellow');
                    // Thử refresh token và gọi lại API
                    $newToken = $this->ensureValidToken($account['user_id']);
                    $campaigns = $this->googleAdsService->getTodayCampaignMetrics($account['customer_id'], $newToken['access_token'], $mccId);
                } else {
                    throw $e;
                }
            }
            
            // Lấy dữ liệu chuyển đổi thực tế từ Google Sheet
            $sheetData = [];
            if (!empty($account['gsheet1'])) {
                // Sử dụng giá trị mặc định cho cấu hình cột
                $columnConfig = [
                    'date_col' => 'A',
                    'phone_col' => 'B',
                    'value_col' => 'C',
                    'campaign_col' => 'D'
                ];

                // Nếu có cấu hình trong settings thì sử dụng
                if (isset($account['gsheet_date_col'])) $columnConfig['gsheet_date_col'] = $account['gsheet_date_col'];
                if (isset($account['gsheet_phone_col'])) $columnConfig['gsheet_phone_col'] = $account['gsheet_phone_col'];
                if (isset($account['gsheet_value_col'])) $columnConfig['gsheet_value_col'] = $account['gsheet_value_col'];
                if (isset($account['gsheet_campaign_col'])) $columnConfig['gsheet_campaign_col'] = $account['gsheet_campaign_col'];

                try {
                    $sheetData = $this->googleSheetService->getConversionsFromCsv(
                        $account['gsheet1'], 
                        date('Y-m-d'),
                        $columnConfig
                    );
                } catch (\Exception $e) {
                    CLI::write("Lỗi đọc dữ liệu Google Sheet: " . $e->getMessage(), 'yellow');
                    // Tiếp tục xử lý với sheetData rỗng
                }
            }

            foreach ($campaigns as $campaign) {
                if (!isset($campaign['campaign_id']) || !isset($campaign['cost']) || !isset($campaign['budget'])) {
                    CLI::write("Bỏ qua chiến dịch không hợp lệ: thiếu thông tin bắt buộc", 'yellow');
                    continue;
                }

                $shouldPause = false;
                $shouldIncreaseBudget = false;
                $action = '';

                // Lấy dữ liệu chuyển đổi thực tế cho chiến dịch này
                $campaignConversions = isset($sheetData[$campaign['campaign_id']]) ? $sheetData[$campaign['campaign_id']] : [
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

                // Kiểm tra ROAS thực tế trước
                if (isset($account['roas_threshold']) && $account['roas_threshold'] > 0) {
                    if ($realRoas > 0 && $realRoas < $account['roas_threshold']) {
                        $shouldPause = true;
                        $action = "ROAS thực tế ({$realRoas}) thấp hơn ngưỡng ({$account['roas_threshold']})";
                    } elseif ($realRoas == 0) {
                        // Nếu ROAS bằng 0 thì kiểm tra CPA
                        if (isset($account['cpa_threshold']) && $account['cpa_threshold'] > 0) {
                            // Nếu chi tiêu vượt ngưỡng CPA và không có chuyển đổi thực tế
                            if ($campaign['cost'] > $account['cpa_threshold'] && $campaignConversions['conversions'] == 0) {
                                $shouldPause = true;
                                $action = "ROAS = 0 và Chi tiêu ({$campaign['cost']}) vượt ngưỡng ({$account['cpa_threshold']}) và không có chuyển đổi thực tế";
                            }
                        }
                    } else {
                        // Nếu ROAS đạt yêu cầu, bỏ qua kiểm tra CPA
                        $shouldPause = false;
                    }
                }
                // Chỉ kiểm tra CPA nếu không có cấu hình ROAS hoặc ROAS không đạt
                elseif (isset($account['cpa_threshold']) && $account['cpa_threshold'] > 0) {
                    if ($realCpa > $account['cpa_threshold'] && $campaignConversions['conversions'] > 0) {
                        $shouldPause = true;
                        $action = "CPA thực tế ({$realCpa}) vượt ngưỡng ({$account['cpa_threshold']})";
                    }
                    // Kiểm tra chi tiêu và chuyển đổi thực tế
                    elseif ($campaign['cost'] > $account['cpa_threshold'] && $campaignConversions['conversions'] == 0) {
                        $shouldPause = true;
                        $action = "Chi tiêu ({$campaign['cost']}) vượt ngưỡng ({$account['cpa_threshold']}) và không có chuyển đổi thực tế";
                    }
                }

                // Kiểm tra tăng ngân sách nếu chiến dịch không bị tạm dừng
                if (!$shouldPause && isset($account['increase_budget']) && $campaign['cost'] > ($campaign['budget'] * 0.5)) {
                    $shouldIncreaseBudget = true;
                    $action = "Chi tiêu ({$campaign['cost']}) vượt 50% ngân sách ({$campaign['budget']})";
                }

                if ($shouldPause || $shouldIncreaseBudget) {
                    $this->executeCampaignAction($account, $campaign, $shouldPause, $shouldIncreaseBudget, $action, $accessToken, $mccId);
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

    protected function executeCampaignAction($account, $campaign, $shouldPause, $shouldIncreaseBudget, $action, $accessToken, $mccId = null)
    {
        try {
            if (!isset($account['user_id']) || !isset($campaign['campaign_id']) || !isset($account['customer_id'])) {
                throw new \Exception('Thiếu thông tin user_id, customer_id hoặc campaign_id');
            }
            
            if ($shouldPause) {
                try {
                    CLI::write("Đang tạm dừng chiến dịch {$campaign['campaign_id']}...", 'yellow');
                    $result = $this->googleAdsService->toggleCampaignStatus(
                        $accessToken,
                        $account['customer_id'],
                        $campaign['campaign_id'],
                        'PAUSED',
                        $mccId
                    );
                    
                    if ($result === true) {
                        CLI::write("Đã tạm dừng chiến dịch {$campaign['campaign_id']} thành công: {$action}", 'green');
                        log_message('info', "Đã tạm dừng chiến dịch {$campaign['campaign_id']}: {$action}");
                    } else {
                        throw new \Exception("Không thể tạm dừng chiến dịch");
                    }
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), '401') !== false) {
                        CLI::write("Token không hợp lệ, đang thử refresh...", 'yellow');
                        // Thử refresh token và gọi lại API
                        $newToken = $this->ensureValidToken($account['user_id']);
                        $result = $this->googleAdsService->toggleCampaignStatus(
                            $newToken['access_token'],
                            $account['customer_id'],
                            $campaign['campaign_id'],
                            'PAUSED',
                            $mccId
                        );
                        
                        if ($result === true) {
                            CLI::write("Đã tạm dừng chiến dịch {$campaign['campaign_id']} thành công sau khi refresh token: {$action}", 'green');
                            log_message('info', "Đã tạm dừng chiến dịch {$campaign['campaign_id']}: {$action}");
                        } else {
                            throw new \Exception("Không thể tạm dừng chiến dịch sau khi refresh token");
                        }
                    } else {
                        throw $e;
                    }
                }
            } elseif ($shouldIncreaseBudget && isset($account['increase_budget'])) {
                try {
                    $newBudget = $campaign['budget'] + $account['increase_budget'];
                    CLI::write("Đang tăng ngân sách chiến dịch {$campaign['campaign_id']}...", 'yellow');
                    $result = $this->googleAdsService->updateCampaignBudget(
                        $accessToken,
                        $account['customer_id'],
                        $campaign['campaign_id'],
                        $newBudget,
                        $mccId
                    );
                    
                    if ($result === true) {
                        CLI::write("Đã tăng ngân sách chiến dịch {$campaign['campaign_id']} lên {$newBudget} thành công: {$action}", 'green');
                        log_message('info', "Đã tăng ngân sách chiến dịch {$campaign['campaign_id']} lên {$newBudget}: {$action}");
                    } else {
                        throw new \Exception("Không thể tăng ngân sách chiến dịch");
                    }
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), '401') !== false) {
                        CLI::write("Token không hợp lệ, đang thử refresh...", 'yellow');
                        // Thử refresh token và gọi lại API
                        $newToken = $this->ensureValidToken($account['user_id']);
                        $newBudget = $campaign['budget'] + $account['increase_budget'];
                        $result = $this->googleAdsService->updateCampaignBudget(
                            $newToken['access_token'],
                            $account['customer_id'],
                            $campaign['campaign_id'],
                            $newBudget,
                            $mccId
                        );
                        
                        if ($result === true) {
                            CLI::write("Đã tăng ngân sách chiến dịch {$campaign['campaign_id']} lên {$newBudget} thành công sau khi refresh token: {$action}", 'green');
                            log_message('info', "Đã tăng ngân sách chiến dịch {$campaign['campaign_id']} lên {$newBudget}: {$action}");
                        } else {
                            throw new \Exception("Không thể tăng ngân sách chiến dịch sau khi refresh token");
                        }
                    } else {
                        throw $e;
                    }
                }
            }
        } catch (\Exception $e) {
            CLI::write("Lỗi thực hiện hành động cho chiến dịch {$campaign['campaign_id']}: " . $e->getMessage(), 'red');
            log_message('error', "Lỗi thực hiện hành động cho chiến dịch {$campaign['campaign_id']}: " . $e->getMessage());
        }
    }
} 