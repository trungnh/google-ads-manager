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
use App\Models\UserModel;
use App\Models\AdsAccountModel;
use App\Models\OptimizeLogsModel;
use App\Models\CampaignsDataModel;
use App\Models\CampaignChart30mModel;
use App\Models\CampaignChart5mModel;

class OptimizeCampaigns extends BaseCommand
{
    protected $group = 'Ads';
    protected $name = 'ads:optimize';
    protected $description = 'Tối ưu chiến dịch quảng cáo tự động';

    protected $googleAdsService;
    protected $googleSheetService;
    protected $adsAccountSettingsModel;
    protected $googleTokenModel;
    protected $userSettingsModel;
    protected $user;
    protected $telegramService;
    protected $adsAccountsModel;
    protected $optimizeLogsModel;
    protected $campaignsDataModel;
    protected $pancakeService;
    protected $campaignChart5mModel;
    protected $campaignChart30mModel;

    public function __construct()
    {
        $this->googleAdsService = new GoogleAdsService();
        $this->googleSheetService = new GoogleSheetService();
        $this->adsAccountSettingsModel = new AdsAccountSettingsModel();
        $this->googleTokenModel = new GoogleTokenModel();
        $this->userSettingsModel = new UserSettingsModel();
        $this->user = new UserModel();
        $this->telegramService = new TelegramService();
        $this->adsAccountsModel = new AdsAccountModel();
        $this->optimizeLogsModel = new OptimizeLogsModel();
        $this->campaignsDataModel = new CampaignsDataModel();
        $this->pancakeService = new PancakeService();
        $this->campaignChart5mModel = new CampaignChart5mModel();
        $this->campaignChart30mModel = new CampaignChart30mModel();
    }

    public function run(array $params)
    {
        // Thời gian chạy 00h - 22h
        $hour = date('H');
        if ($hour > 21) {
            //CLI::write("Thời gian không hợp lệ, chỉ chạy từ 0:00 đến 22:00", 'yellow');
            return;
        }
        try {
            // Lấy danh sách tài khoản cần tối ưu
            $accounts = $this->adsAccountSettingsModel->getAccountsForOptimization();

            if (empty($accounts)) {
                $message = 'Không có tài khoản nào cần tối ưu.';
                CLI::write($message, 'yellow');
                return;
            }

            $message = "🔄 Bắt đầu tối ưu chiến dịch cho " . count($accounts) . " tài khoản";
            CLI::write($message, 'green');

            $totalErrors = 0;

            $optimizeCampaignsResult = [
                'paused_campaigns' => 0,
                'increased_budget_campaigns' => 0
            ];

            $processedAccounts = [];
            foreach ($accounts as $account) {
                // Nếu tài khoản đã được xử lý thì bỏ qua
                if (in_array($account['id'], $processedAccounts)) {
                    continue;
                }

                // Kiểm tra các trường bắt buộc
                if (!isset($account['customer_id']) || !isset($account['user_id']) || !isset($account['id'])) {
                    $message = 'Dữ liệu tài khoản không hợp lệ: thiếu thông tin bắt buộc' . $account['id'];
                    CLI::write($message, 'red');
                    continue;
                }

                $accountName = $account['customer_name'] ?? $account['customer_id'] ?? 'Unknown Account';
                $message = "Đang tối ưu tài khoản: {$accountName}";
                CLI::write($message, 'green');

                try {
                    $userInstance = $this->user->where('id', $account['user_id'])->first();
                    if ($userInstance['status'] != 'active') {
                        continue;
                    }

                    // Lấy MCC ID từ user settings
                    $userSettings = $this->userSettingsModel->where('user_id', $account['user_id'])->first();
                    $mccId = $userSettings['mcc_id'] ?? null;

                    $this->telegramService->loadProxySettings($account['user_id']);
                    $linkedUsers = $this->adsAccountsModel->getLinkedUsers($account['customer_id']);
                    $telegramChatIds = [];
                    foreach ($linkedUsers as $linkedUser) {
                        $userSettings = $this->userSettingsModel->where('user_id', $linkedUser['user_id'])->first();
                        $telegramChatId = $userSettings['telegram_chat_id'] ?? null;
                        if ($telegramChatId) {
                            $telegramChatIds[] = $telegramChatId;
                        }
                    }

                    // Kiểm tra và refresh token trước khi xử lý
                    $tokenData = $this->ensureValidToken($account['user_id']);
                    if (!$tokenData) {
                        throw new \Exception('Không thể lấy token hợp lệ');
                    }
                    $optimizeCampaignsResult = $this->optimizeCampaigns($account, $tokenData, $mccId, $telegramChatIds);
                    $processedAccounts[] = $account['id'];
                } catch (\Exception $e) {
                    $message = "Lỗi khi tối ưu tài khoản {$accountName}: " . $e->getMessage();
                    CLI::write($message, 'red');
                    log_message('error', $message);
                    //$this->sendTelegramMessage("❌ " . $message, $telegramChatIds);
                    $totalErrors++;
                }
            }

            $message = "✅ Hoàn thành tối ưu chiến dịch.\n";
            $message .= "📊 Tổng kết:\n";
            $message .= "- Tổng số tài khoản: " . count($accounts) . "\n";
            $message .= "- Tổng số chiến dịch tạm dừng: " . $optimizeCampaignsResult['paused_campaigns'] . "\n";
            $message .= "- Tổng số chiến dịch tăng ngân sách: " . $optimizeCampaignsResult['increased_budget_campaigns'] . "\n";
            $message .= "- Số lỗi: {$totalErrors}";

            CLI::write($message, 'green');
            // Chỉ gửi khi có  chiến dịch tạm dừng hoặc tăng ngân sách
            if ($optimizeCampaignsResult['paused_campaigns'] > 0 || $optimizeCampaignsResult['increased_budget_campaigns'] > 0) {
                $this->sendTelegramMessage($message, $telegramChatIds);
            }
        } catch (\Exception $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            CLI::write($message, 'red');
            log_message('error', $message);
            $this->sendTelegramMessage("❌ " . $message, $telegramChatIds);
        }
    }

    protected function optimizeCampaigns($account, $tokenData, $mccId = null, $telegramChatIds = [])
    {
        $pausedCampaigns = 0;
        $increasedBudgetCampaigns = 0;
        try {
            // Kiểm tra các trường bắt buộc
            if (!isset($account['customer_id']) || !isset($account['id'])) {
                throw new \Exception('Thiếu thông tin customer_id hoặc account id');
            }

            // Lấy dữ liệu chiến dịch realtime từ Google Ads
            $campaigns = $this->googleAdsService->getCampaignsWithRealConv($account, $account['customer_id'], $tokenData, $mccId, true, date('Y-m-d'), date('Y-m-d'));
            if (empty($campaigns)) {
                CLI::write("Không tìm thấy chiến dịch nào cho tài khoản {$account['customer_id']}", 'yellow');
                return [
                    'paused_campaigns' => 0,
                    'increased_budget_campaigns' => 0
                ];
            }

            try {
                // Save campaign data
                $this->campaignsDataModel->saveCampaignsData($account['customer_id'], $campaigns, date('Y-m-d'));
            } catch (\Exception $e) {
                log_message('error', 'Lỗi tối ưu chiến dịch - Save Campaigns - ' . $account['customer_id'] . ': ' . $e->getMessage());
                $this->sendTelegramMessage("❌Lỗi tối ưu chiến dịch - Save Campaigns - {$account['customer_id']}: " . $e->getMessage(), $telegramChatIds);
            }

            $excludeCampaignIds = explode(',', $account['exclude_campaign_ids']);
            $excludeCampaignIds = array_map('trim', $excludeCampaignIds);
            foreach ($campaigns as $campaign) {
                // if (in_array($campaign['campaign_id'], $excludeCampaignIds)) {
                //     CLI::write("Bỏ qua chiến dịch {$campaign['campaign_id']} vì đã được exclude", 'yellow');
                //     continue;
                // }

                if (!isset($campaign['campaign_id']) || !isset($campaign['cost']) || !isset($campaign['budget'])) {
                    CLI::write("Bỏ qua chiến dịch không hợp lệ: thiếu thông tin bắt buộc", 'yellow');
                    continue;
                }

                // Bỏ qua chiến dịch đã tạm dừng hoặc chưa có chi tiêu
                if ($campaign['cost'] == 0 || $campaign['status'] == 'PAUSED') {
                    continue;
                }


                $ruleEngine = new \App\Optimization\RuleEngine();
                try {
                    $evaluation = $ruleEngine->evaluateCampaign($account, $campaign);
                } catch (\Exception $e) {
                    log_message('error', 'Lỗi chạy RuleEngine - ' . $account['customer_id'] . ': ' . $e->getMessage());
                    $this->sendTelegramMessage("❌Lỗi chạy RuleEngine - {$account['customer_id']}: " . $e->getMessage(), $telegramChatIds);
                    $evaluation = ['action' => 'none', 'reason' => '', 'tmp_cflc' => 0];
                }

                $shouldPause = ($evaluation['action'] === 'pause');
                $shouldIncreaseBudget = ($evaluation['action'] === 'increase_budget');
                $actionReason = $evaluation['reason'];
                $tmpCFLC = $evaluation['tmp_cflc'];

                if ($shouldPause || $shouldIncreaseBudget) {
                    if (in_array($campaign['campaign_id'], $excludeCampaignIds)) {
                        // Check nếu camp exclude mà đắt quá cpa cũng tắt luôn
                        if ($tmpCFLC != 0 && $tmpCFLC > ($account['cpa_threshold'] ?? 0)) {
                            $this->executeCampaignAction($account, $campaign, $shouldPause, $shouldIncreaseBudget, $actionReason, $tokenData, $mccId, $telegramChatIds);
                        } else {
                            $message = "CHÚ Ý: Chiến dịch <b>{$account['customer_name']}</b> - {$campaign['name']}[{$campaign['campaign_id']}]: {$actionReason}";
                            $this->sendTelegramMessage("💢 " . $message, $telegramChatIds);
                        }
                    } else {
                        $this->executeCampaignAction($account, $campaign, $shouldPause, $shouldIncreaseBudget, $actionReason, $tokenData, $mccId, $telegramChatIds);
                    }
                }
                if (isset($account['auto_on_off']) && $account['auto_on_off'] == 1) {
                    $pausedCampaigns += $shouldPause ? 1 : 0;
                }
                $increasedBudgetCampaigns += $shouldIncreaseBudget ? 1 : 0;
            }

            try {
                // Cập nhật thời gian chạy cuối cùng
                $this->adsAccountSettingsModel->update($account['id'], [
                    'last_optimize_run' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Lỗi tối ưu chiến dịch - Save Campaigns - ' . $account['customer_id'] . ': ' . $e->getMessage());
                $this->sendTelegramMessage("❌Lỗi tối ưu chiến dịch - Save Campaigns - {$account['customer_id']}: " . $e->getMessage(), $telegramChatIds);
            }

            // return true;
        } catch (\Exception $e) {
            log_message('error', 'Lỗi tối ưu chiến dịch ' . $account['customer_id'] . ': ' . $e->getMessage());
            $this->sendTelegramMessage("❌Lỗi tối ưu chiến dịch {$account['customer_id']}: " . $e->getMessage(), $telegramChatIds);
        }

        return [
            'paused_campaigns' => $pausedCampaigns,
            'increased_budget_campaigns' => $increasedBudgetCampaigns
        ];
    }

    protected function executeCampaignAction($account, $campaign, $shouldPause, $shouldIncreaseBudget, $action, $tokenData, $mccId = null, $telegramChatIds = [])
    {
        try {
            if (!isset($account['user_id']) || !isset($campaign['campaign_id']) || !isset($account['customer_id'])) {
                throw new \Exception('Thiếu thông tin user_id, customer_id hoặc campaign_id');
            }

            $accountName = $account['customer_name'] ?? $account['customer_id'] ?? '';
            $campaignName = $campaign['name'] ?? $campaign['name'] ?? '';

            if ($shouldPause) {
                if (isset($account['auto_on_off']) && $account['auto_on_off'] == 1) {
                    $this->pauseCampaign($account, $campaign, $action, $tokenData, $mccId, $telegramChatIds);
                } else {
                    $message = "CHÚ Ý: Chiến dịch <b>{$account['customer_name']}</b> - {$campaign['name']}[{$campaign['campaign_id']}]: {$action}";
                    $this->sendTelegramMessage("💢 " . $message, $telegramChatIds);
                }
            } elseif ($shouldIncreaseBudget && isset($account['increase_budget'])) {
                $this->increaseBudgetCampaign($account, $campaign, $action, $tokenData, $mccId, $telegramChatIds);
            }
        } catch (\Exception $e) {
            $message = "Lỗi thực hiện hành động cho chiến dịch {$accountName} - {$campaignName} | {$campaign['campaign_id']}: " . $e->getMessage();
            CLI::write($message, 'red');
            log_message('error', $message);
            $this->sendTelegramMessage("❌ " . $message, $telegramChatIds);
        }
    }

    protected function pauseCampaign($account, $campaign, $action, $tokenData, $mccId = null, $telegramChatIds = [])
    {
        $message = "Đang tạm dừng chiến dịch {$campaign['campaign_id']}...";
        CLI::write($message, 'yellow');

        $result = $this->googleAdsService->toggleCampaignStatus(
            $tokenData,
            $account['customer_id'],
            $campaign['campaign_id'],
            'PAUSED',
            $mccId
        );

        if ($result === true) {
            $message = "Tạm dừng chiến dịch <b>{$account['customer_name']}</b> - {$campaign['name']}[{$campaign['campaign_id']}]: {$action}";
            CLI::write($message, 'green');
            log_message('info', $message);
            \CodeIgniter\Events\Events::trigger('campaign_optimized', [
                'account' => $account,
                'campaign' => $campaign,
                'action' => 'pause',
                'reason' => $action,
                'chat_ids' => $telegramChatIds
            ]);
        } else {
            throw new \Exception("Không thể tạm dừng chiến dịch");
        }
    }

    protected function increaseBudgetCampaign($account, $campaign, $action, $tokenData, $mccId = null, $telegramChatIds = [])
    {
        $newBudget = $campaign['budget'] + $account['increase_budget'];
        $message = "Đang tăng ngân sách chiến dịch {$campaign['campaign_id']}...";
        CLI::write($message, 'yellow');

        $result = $this->googleAdsService->updateCampaignBudget(
            $tokenData,
            $account['customer_id'],
            $campaign['campaign_id'],
            $newBudget,
            $mccId
        );

        if ($result === true) {
            $message = "Tăng ngân sách chiến dịch <b>{$account['customer_name']}</b> - {$campaign['name']}[{$campaign['campaign_id']}] lên " . number_format($newBudget, 0, '', '.') . ": {$action}";
            CLI::write($message, 'green');
            log_message('info', $message);
            \CodeIgniter\Events\Events::trigger('campaign_optimized', [
                'account' => $account,
                'campaign' => $campaign,
                'action' => 'increase_budget',
                'reason' => $action,
                'chat_ids' => $telegramChatIds
            ]);
        } else {
            throw new \Exception("Không thể tăng ngân sách chiến dịch");
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

    protected function sendTelegramMessage($message, $telegramChatIds = [])
    {
        \CodeIgniter\Events\Events::trigger('campaign_info_message', [
            'message' => $message,
            'chat_ids' => $telegramChatIds
        ]);
    }
}