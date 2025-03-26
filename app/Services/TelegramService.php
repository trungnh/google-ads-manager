<?php

namespace App\Services;

class TelegramService
{
    private $botToken;

    public function __construct()
    {
        $this->botToken = getenv('TELEGRAM_BOT_TOKEN');
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
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', 'Failed to send Telegram message: ' . $result);
            return false;
        }

        return true;
    }
} 