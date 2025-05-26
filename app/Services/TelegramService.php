<?php

namespace App\Services;

use App\Models\UserSettingsModel;

class TelegramService
{
    private $botToken;
    private $proxySettings;

    public function __construct()
    {
        $this->botToken = getenv('TELEGRAM_BOT_TOKEN');
    }
    
    public function loadProxySettings($userId = null)
    {
        $this->proxySettings = null;
        if ($userId) {
            $userSettingsModel = new UserSettingsModel();
            $settings = $userSettingsModel->where('user_id', $userId)->first();
            
            if ($settings && !empty($settings['telegram_proxy']) && $settings['use_telegram_proxy']) {
                $proxyParts = explode(':', $settings['telegram_proxy']);
                
                if (count($proxyParts) >= 2) {
                    $this->proxySettings = [
                        'ip' => $proxyParts[0],
                        'port' => $proxyParts[1],
                        'username' => $proxyParts[2] ?? null,
                        'password' => $proxyParts[3] ?? null
                    ];
                }
            }
        }
    }

    public function sendMessage($message, $chatId)
    {
        if (empty($this->botToken)) {
            log_message('error', 'Telegram configuration is missing');
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Thêm cấu hình proxy nếu có
        if ($this->proxySettings) {
            $proxyAuth = '';
            if (!empty($this->proxySettings['username']) && !empty($this->proxySettings['password'])) {
                $proxyAuth = $this->proxySettings['username'] . ':' . $this->proxySettings['password'];
            }
            
            curl_setopt($ch, CURLOPT_PROXY, $this->proxySettings['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxySettings['port']);
            
            if (!empty($proxyAuth)) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyAuth);
            }
            
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            
            //log_message('error', 'Using proxy for Telegram: ' . $this->proxySettings['ip'] . ':' . $this->proxySettings['port']);
        } else {
            // Không gửi mess nếu không có proxy
            return true;
        }
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            log_message('error', 'Curl error when sending Telegram message: ' . curl_error($ch));
        }
        
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', 'Failed to send Telegram message: ' . $result);
            return false;
        }

        return true;
    }
}