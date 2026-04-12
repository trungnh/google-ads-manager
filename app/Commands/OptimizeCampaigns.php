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
                    $telegramChatId = $userSettings['telegram_chat_id'] ?? null;

                    // Kiểm tra và refresh token trước khi xử lý
                    $tokenData = $this->ensureValidToken($account['user_id']);
                    if (!$tokenData) {
                        throw new \Exception('Không thể lấy token hợp lệ');
                    }
                    $optimizeCampaignsResult = $this->optimizeCampaigns($account, $tokenData['access_token'], $mccId, $telegramChatId);
                    $processedAccounts[] = $account['id'];
                } catch (\Exception $e) {
                    $message = "Lỗi khi tối ưu tài khoản {$accountName}: " . $e->getMessage();
                    CLI::write($message, 'red');
                    log_message('error', $message);
                    //$this->sendTelegramMessage("❌ " . $message, $telegramChatId);
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
                $this->sendTelegramMessage($message, $telegramChatId);
            }
        } catch (\Exception $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            CLI::write($message, 'red');
            log_message('error', $message);
            $this->sendTelegramMessage("❌ " . $message, $telegramChatId);
        }
    }

    protected function optimizeCampaigns($account, $accessToken, $mccId = null, $telegramChatId)
    {
        $pausedCampaigns = 0;
        $increasedBudgetCampaigns = 0;
        try {
            // Kiểm tra các trường bắt buộc
            if (!isset($account['customer_id']) || !isset($account['id'])) {
                throw new \Exception('Thiếu thông tin customer_id hoặc account id');
            }

            // Lấy dữ liệu chiến dịch realtime từ Google Ads
            try {
                $campaigns = $this->googleAdsService->getCampaignsWithRealConv($account, $account['customer_id'], $accessToken, $mccId, true, date('Y-m-d'), date('Y-m-d'));
                if (empty($campaigns)) {
                    CLI::write("Không tìm thấy chiến dịch nào cho tài khoản {$account['customer_id']}", 'yellow');
                    return [
                        'paused_campaigns' => 0,
                        'increased_budget_campaigns' => 0
                    ];
                }
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), '401') !== false) {
                    CLI::write("Token không hợp lệ, đang thử refresh...", 'yellow');
                    // Thử refresh token và gọi lại API
                    $newToken = $this->ensureValidToken($account['user_id']);
                    $campaigns = $this->googleAdsService->getCampaignsWithRealConv($account, $account['customer_id'], $newToken, $mccId, true, date('Y-m-d'), date('Y-m-d'));
                } else {
                    throw $e;
                }
            }

            try {
                // Save campaign data
                $this->campaignsDataModel->saveCampaignsData($account['customer_id'], $campaigns, date('Y-m-d'));
            } catch (\Exception $e) {
                log_message('error', 'Lỗi tối ưu chiến dịch - Save Campaigns - ' . $account['customer_id'] . ': ' . $e->getMessage());
                $this->sendTelegramMessage("❌Lỗi tối ưu chiến dịch - Save Campaigns - {$account['customer_id']}: " . $e->getMessage(), $telegramChatId);
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

                $shouldPause = false;
                $shouldIncreaseBudget = false;
                $action = '';

                $realCpa = $campaign['real_cpa'] ?? 0;
                $realConversions = $campaign['real_conversions'] ?? 0;
                $realConversionValue = $campaign['real_conversion_value'] ?? 0;
                $realRoas = ($campaign['cost'] > 0) ? $realConversionValue / $campaign['cost'] : 0;
                $tmpCFLC = 0;

                // Kiểm tra chi tiêu trước
                if (isset($account['cost_threshold']) && $account['cost_threshold'] > 0) {
                    if ($campaign['cost'] <= $account['cost_threshold']) {
                        continue;
                    }
                }

                try {
                    /* ============ Bật/tắt camp ============ */
                    // TH: Không có đơn
                    if ($realConversions == 0) {
                        // Nếu chi tiêu vượt ngưỡng CPA và không có chuyển đổi thực tế
                        if ($account['cpa_threshold'] > 0 && $campaign['cost'] > $account['cpa_threshold']) {
                            $shouldPause = true;
                            $action = "Chi tiêu (" . number_format($campaign['cost'], 0, '', '.') . ") vượt ngưỡng (" . number_format($account['cpa_threshold'], 0, '', '.') . ") và không có đơn thực tế";
                        }
                    }
                    // TH: Chỉ có 1 đơn 
                    elseif ($realConversions == 1) {
                        if (isset($account['use_roas_threshold']) && $account['use_roas_threshold'] == 1) {
                            // Check theo ROAS
                            // Nếu ROAS thực tế thấp hơn ngưỡng
                            if ($account['roas_threshold'] > 0 && $realRoas < $account['roas_threshold']) {
                                $shouldPause = true;
                                $action = "ROAS thực tế (" . number_format($realRoas, 1, ',', '.') . ") thấp hơn ngưỡng (" . number_format($account['roas_threshold'], 1, ',', '.') . ")";
                            }
                        } else {
                            // Check theo CPA
                            // Nếu CPA thực tế vượt ngưỡng
                            if ($account['cpa_threshold'] > 0 && $realCpa > $account['cpa_threshold']) {
                                $shouldPause = true;
                                $action = "CPA thực tế (" . number_format($realCpa, 0, ',', '.') . ") vượt ngưỡng (" . number_format($account['cpa_threshold'], 1, ',', '.') . ")";
                            }
                        }
                    }
                    // TH: Nhiều hơn 1 đơn
                    elseif ($realConversions > 1) {
                        $extendedCpaThreshold = $account['extended_cpa_threshold'] ?? 0;
                        if ($extendedCpaThreshold == 0) {
                            // Chỉ check CPA
                            if (isset($account['use_roas_threshold']) && $account['use_roas_threshold'] == 1) {
                                // Check theo ROAS
                                // Nếu ROAS thực tế thấp hơn ngưỡng
                                if ($account['roas_threshold'] > 0 && $realRoas < $account['roas_threshold']) {
                                    $shouldPause = true;
                                    $action = "ROAS thực tế (" . number_format($realRoas, 1, ',', '.') . ") thấp hơn ngưỡng (" . number_format($account['roas_threshold'], 1, ',', '.') . ")";
                                }
                            } else {
                                // Check theo CPA
                                // Nếu CPA thực tế vượt ngưỡng
                                if ($account['cpa_threshold'] > 0 && $realCpa > $account['cpa_threshold']) {
                                    $shouldPause = true;
                                    $action = "CPA thực tế (" . number_format($realCpa, 0, ',', '.') . ") vượt ngưỡng (" . number_format($account['cpa_threshold'], 1, ',', '.') . ")";
                                }
                            }
                        } else {
                            // Check CPA giữa 2 lần chuyển đổi
                            // Lấy campaign data từ DB
                            $tmpCampaign = $this->campaignsDataModel->where('customer_id', $account['customer_id'])
                                ->where('campaign_id', $campaign['campaign_id'])
                                ->where('date', date('Y-m-d'))
                                ->first();

                            // Check tồn tại
                            $lastCostConversion = $tmpCampaign['last_cost_conversion'] ?? 0;
                            $lastCountConversion = $tmpCampaign['last_count_conversion'] ?? 0;
                            $lastCountConversionValue = $tmpCampaign['last_count_conversion_value'] ?? 0;

                            // Tính chi tiêu từ lần ra cuối cùng ra chuyển đổi
                            $costExtendFromLastConversion = $tmpCampaign['cost'] - $lastCostConversion;
                            $conversionsExtendFromLastConversion = $realConversions - $lastCountConversion;
                            $conversionValueExtendFromLastConversion = $realConversionValue - $lastCountConversionValue;
                            $tmpCFLC = $costExtendFromLastConversion;
                            if ($conversionsExtendFromLastConversion == 0) {
                                if ($costExtendFromLastConversion > $account['cpa_threshold']) {
                                    $shouldPause = true;
                                    $action = "Chi tiêu thêm (" . number_format($costExtendFromLastConversion, 0, '', '.') . ") từ lần ra đơn cuối cùng - Không có đơn thực tế";
                                }
                            } else {
                                $cpaExtendFromLastConversion = $costExtendFromLastConversion / $conversionsExtendFromLastConversion;
                                $roasExtendFromLastConversion = $conversionValueExtendFromLastConversion / $costExtendFromLastConversion;
                                if ($account['use_roas_threshold']) {
                                    // Check theo ROAS
                                    // Nếu ROAS thực tế thấp hơn ngưỡng
                                    if ($account['roas_threshold'] > 0 && $roasExtendFromLastConversion < $account['roas_threshold']) {
                                        $shouldPause = true;
                                        $action = "Chi tiêu thêm (" . number_format($costExtendFromLastConversion, 0, '', '.') . ") từ lần ra đơn cuối cùng - ROAS (" . number_format($roasExtendFromLastConversion, 1, ',', '.') . ") thấp hơn ngưỡng (" . number_format($account['roas_threshold'], 1, ',', '.') . ")";
                                    }
                                } else {
                                    // Check theo CPA
                                    // Nếu CPA thực tế vượt ngưỡng
                                    $extendedCpaThreshold = $account['extended_cpa_threshold'] ?? 0;
                                    $extendedCpaThreshold = ($extendedCpaThreshold > 0) ? $extendedCpaThreshold : $account['cpa_threshold'];
                                    if ($extendedCpaThreshold > 0 && $cpaExtendFromLastConversion > $extendedCpaThreshold) {
                                        $shouldPause = true;
                                        $action = "Chi tiêu thêm (" . number_format($costExtendFromLastConversion, 0, '', '.') . ") từ lần ra đơn cuối cùng - CPA (" . number_format($cpaExtendFromLastConversion, 1, ',', '.') . ") vượt ngưỡng (" . number_format($extendedCpaThreshold, 1, ',', '.') . ")";
                                    }
                                }
                            }

                        }
                    }
                    /* ============ Bật/tắt camp ============ */

                } catch (\Exception $e) {
                    log_message('error', 'Lỗi tối ưu chiến dịch - Rule bật/tắt - ' . $account['customer_id'] . ': ' . $e->getMessage());
                    $this->sendTelegramMessage("❌Lỗi tối ưu chiến dịch - Rule bật/tắt - {$account['customer_id']}: " . $e->getMessage(), $telegramChatId);
                }

                // Kiểm tra tăng ngân sách nếu chiến dịch không bị tạm dừng
                $budgetThresholdPercent = $account['budget_spending_threshold'] ?? 50;
                $budgetThreshold = $campaign['budget'] * ($budgetThresholdPercent / 100);

                if (
                    !$shouldPause &&
                    $realConversions > 0 &&
                    isset($account['increase_budget']) &&
                    $account['increase_budget'] > 0 &&
                    $campaign['cost'] > $budgetThreshold
                ) {
                    $shouldIncreaseBudget = true;
                    $action = "Chi tiêu (" . number_format($campaign['cost'], 0, '', '.') . ") vượt {$budgetThresholdPercent}% ngân sách (" . number_format($campaign['budget'], 0, '', '.') . ")";
                }
                if ($shouldPause || $shouldIncreaseBudget) {
                    if (in_array($campaign['campaign_id'], $excludeCampaignIds)) {

                        // Check nếu camp exclude mà đắt quá cpa cũng tắt luôn
                        if ($tmpCFLC != 0 && $tmpCFLC > $account['cpa_threshold']) {
                            $this->executeCampaignAction($account, $campaign, $shouldPause, $shouldIncreaseBudget, $action, $accessToken, $mccId, $telegramChatId);
                        } else {
                            $message = "CHÚ Ý: Chiến dịch <b>{$account['customer_name']}</b> - {$campaign['name']}[{$campaign['campaign_id']}]: {$action}";
                            $this->sendTelegramMessage("💢 " . $message, $telegramChatId);
                        }
                    } else {
                        $this->executeCampaignAction($account, $campaign, $shouldPause, $shouldIncreaseBudget, $action, $accessToken, $mccId, $telegramChatId);
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
                $this->sendTelegramMessage("❌Lỗi tối ưu chiến dịch - Save Campaigns - {$account['customer_id']}: " . $e->getMessage(), $telegramChatId);
            }

            // return true;
        } catch (\Exception $e) {
            log_message('error', 'Lỗi tối ưu chiến dịch ' . $account['customer_id'] . ': ' . $e->getMessage());
            $this->sendTelegramMessage("❌Lỗi tối ưu chiến dịch {$account['customer_id']}: " . $e->getMessage(), $telegramChatId);
        }

        return [
            'paused_campaigns' => $pausedCampaigns,
            'increased_budget_campaigns' => $increasedBudgetCampaigns
        ];
    }

    protected function executeCampaignAction($account, $campaign, $shouldPause, $shouldIncreaseBudget, $action, $accessToken, $mccId = null, $telegramChatId)
    {
        try {
            if (!isset($account['user_id']) || !isset($campaign['campaign_id']) || !isset($account['customer_id'])) {
                throw new \Exception('Thiếu thông tin user_id, customer_id hoặc campaign_id');
            }

            $accountName = $account['customer_name'] ?? $account['customer_id'] ?? '';
            $campaignName = $campaign['name'] ?? $campaign['name'] ?? '';

            if ($shouldPause) {
                if (isset($account['auto_on_off']) && $account['auto_on_off'] == 1) {
                    $this->pauseCampaign($account, $campaign, $action, $accessToken, $mccId, $telegramChatId);
                } else {
                    $message = "CHÚ Ý: Chiến dịch <b>{$account['customer_name']}</b> - {$campaign['name']}[{$campaign['campaign_id']}]: {$action}";
                    $this->sendTelegramMessage("💢 " . $message, $telegramChatId);
                }
            } elseif ($shouldIncreaseBudget && isset($account['increase_budget'])) {
                $this->increaseBudgetCampaign($account, $campaign, $action, $accessToken, $mccId, $telegramChatId);
            }
        } catch (\Exception $e) {
            $message = "Lỗi thực hiện hành động cho chiến dịch {$accountName} - {$campaignName} | {$campaign['campaign_id']}: " . $e->getMessage();
            CLI::write($message, 'red');
            log_message('error', $message);
            $this->sendTelegramMessage("❌ " . $message, $telegramChatId);
        }
    }

    protected function pauseCampaign($account, $campaign, $action, $accessToken, $mccId = null, $telegramChatId)
    {
        try {
            $message = "Đang tạm dừng chiến dịch {$campaign['campaign_id']}...";
            CLI::write($message, 'yellow');

            $result = $this->googleAdsService->toggleCampaignStatus(
                $accessToken,
                $account['customer_id'],
                $campaign['campaign_id'],
                'PAUSED',
                $mccId
            );

            if ($result === true) {
                $message = "Tạm dừng chiến dịch <b>{$account['customer_name']}</b> - {$campaign['name']}[{$campaign['campaign_id']}]: {$action}";
                CLI::write($message, 'green');
                log_message('info', $message);
                $this->sendTelegramMessage("⏸️ " . $message, $telegramChatId);

                // Lưu log
                $this->optimizeLogsModel->insert([
                    'user_id' => $account['user_id'],
                    'customer_id' => $account['customer_id'],
                    'campaign_id' => $campaign['campaign_id'],
                    'campaign_name' => $campaign['name'],
                    'action' => 'pause',
                    'details' => $action,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
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
                    $message = "Refresh token + Tạm dừng chiến dịch <b>{$account['customer_name']}</b> - {$campaign['name']}[{$campaign['campaign_id']}]: {$action}";
                    CLI::write($message, 'green');
                    log_message('info', $message);
                    $this->sendTelegramMessage("⏸️ " . $message, $telegramChatId);

                    // Lưu log
                    $this->optimizeLogsModel->insert([
                        'user_id' => $account['user_id'],
                        'customer_id' => $account['customer_id'],
                        'campaign_id' => $campaign['campaign_id'],
                        'campaign_name' => $campaign['name'],
                        'action' => 'pause',
                        'details' => $action,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    throw new \Exception("Không thể tạm dừng chiến dịch sau khi refresh token");
                }
            } else {
                throw $e;
            }
        }
    }

    protected function increaseBudgetCampaign($account, $campaign, $action, $accessToken, $mccId = null, $telegramChatId)
    {
        try {
            $newBudget = $campaign['budget'] + $account['increase_budget'];
            $message = "Đang tăng ngân sách chiến dịch {$campaign['campaign_id']}...";
            CLI::write($message, 'yellow');

            $result = $this->googleAdsService->updateCampaignBudget(
                $accessToken,
                $account['customer_id'],
                $campaign['campaign_id'],
                $newBudget,
                $mccId
            );

            if ($result === true) {
                $message = "Tăng ngân sách chiến dịch <b>{$account['customer_name']}</b> - {$campaign['name']}[{$campaign['campaign_id']}] lên " . number_format($newBudget, 0, '', '.') . ": {$action}";
                CLI::write($message, 'green');
                log_message('info', $message);
                $this->sendTelegramMessage("💰 " . $message, $telegramChatId);

                // Lưu log
                $this->optimizeLogsModel->insert([
                    'user_id' => $account['user_id'],
                    'customer_id' => $account['customer_id'],
                    'campaign_id' => $campaign['campaign_id'],
                    'campaign_name' => $campaign['name'],
                    'action' => 'increase_budget',
                    'details' => $action,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
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
                    $message = "Refresh token + Tăng ngân sách chiến dịch <b>{$account['customer_name']}</b> - {$campaign['name']}[{$campaign['campaign_id']}] lên " . number_format($newBudget, 0, '', '.') . ": {$action}";
                    CLI::write($message, 'green');
                    log_message('info', $message);
                    $this->sendTelegramMessage("💰 " . $message, $telegramChatId);

                    // Lưu log
                    $this->optimizeLogsModel->insert([
                        'user_id' => $account['user_id'],
                        'customer_id' => $account['customer_id'],
                        'campaign_id' => $campaign['campaign_id'],
                        'campaign_name' => $campaign['name'],
                        'action' => 'increase_budget',
                        'details' => $action,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    throw new \Exception("Không thể tăng ngân sách chiến dịch sau khi refresh token");
                }
            } else {
                throw $e;
            }
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

    protected function sendTelegramMessage($message, $telegramChatId)
    {
        $hour = date('H');
        if ($hour < 5 || $hour > 21) {
            return;
        }
        $this->telegramService->sendMessage($message, $telegramChatId);
    }
}