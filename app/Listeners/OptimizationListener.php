<?php

namespace App\Listeners;

use App\Models\OptimizeLogsModel;
use App\Services\TelegramService;

class OptimizationListener
{
    /**
     * @param array $data ['account', 'campaign', 'action' => 'pause'/'increase_budget', 'reason', 'chat_ids']
     */
    public static function onOptimizationAction(array $data)
    {
        $logModel = new OptimizeLogsModel();
        $telegramService = new TelegramService();

        $account = $data['account'];
        $campaign = $data['campaign'];
        $action = $data['action'];
        $reason = $data['reason'];
        $chatIds = $data['chat_ids'] ?? [];

        // 1. Lưu DB
        $logModel->insert([
            'customer_id' => $account['customer_id'],
            'customer_name' => $account['customer_name'],
            'campaign_id' => $campaign['campaign_id'],
            'campaign_name' => $campaign['name'],
            'action' => $action,
            'details' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Gửi Telegram
        $actionVi = ($action === 'pause') ? 'Tạm dừng' : 'Tăng NS';
        $icon = ($action === 'pause') ? '⏸️' : '📈';
        
        $message = "{$icon} <b>{$actionVi} chiến dịch</b>\n";
        $message .= "Khách hàng: <b>{$account['customer_name']}</b>\n";
        $message .= "Chiến dịch: {$campaign['name']}\n";
        $message .= "Lý do: {$reason}\n";
        
        $cflcTime = null;
        if (isset($campaign['real_conversions']) && $campaign['real_conversions'] > 1) {
            $cflcTime = "Chuyển đổi > 1 (Tính C-F-LC)";
        }
        if ($cflcTime) {
            $message .= "Time: {$cflcTime}";
        }

        self::sendTelegramBroadcast($telegramService, $message, $chatIds);
    }

    public static function onOptimizationError(array $data)
    {
        $telegramService = new TelegramService();
        $message = $data['message'];
        $chatIds = $data['chat_ids'] ?? [];
        
        log_message('error', $message);
        self::sendTelegramBroadcast($telegramService, "❌ " . $message, $chatIds);
    }

    public static function onInfoMessage(array $data)
    {
        $telegramService = new TelegramService();
        $message = $data['message'];
        $chatIds = $data['chat_ids'] ?? [];
        
        self::sendTelegramBroadcast($telegramService, "💢 " . $message, $chatIds);
    }

    protected static function sendTelegramBroadcast(TelegramService $service, $message, array $chatIds)
    {
        $hour = date('H');
        // Không gửi thông báo từ nửa đêm đến 5h sáng
        if ($hour < 5 || $hour > 21) {
            return;
        }
        foreach ($chatIds as $id) {
            $service->sendMessage($message, $id);
        }
    }
}
