<?php

namespace App\Models;

use CodeIgniter\Model;

class GoogleTokenModel extends Model
{
    protected $table = 'google_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'access_token', 'refresh_token', 'token_type', 'expires_in', 'expires_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Lưu token vào database
     */
    public function saveToken($userId, $tokenData)
    {
        // Tính thời gian hết hạn
        $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
        
        $data = [
            'user_id' => $userId,
            'access_token' => $tokenData['access_token'],
            'token_type' => $tokenData['token_type'] ?? 'Bearer',
            'expires_in' => $tokenData['expires_in'],
            'expires_at' => $expiresAt
        ];
        
        // Thêm refresh token nếu có
        if (isset($tokenData['refresh_token'])) {
            $data['refresh_token'] = $tokenData['refresh_token'];
        }
        
        // Kiểm tra xem user đã có token chưa
        $existingToken = $this->where('user_id', $userId)->first();
        
        if ($existingToken) {
            // Cập nhật token hiện có
            $this->update($existingToken['id'], $data);
            return $existingToken['id'];
        } else {
            // Tạo mới token
            return $this->insert($data);
        }
    }
    
    /**
     * Lấy token hợp lệ cho user
     */
    public function getValidToken($userId)
    {
        $token = $this->where('user_id', $userId)->first();
        
        if (!$token) {
            return null;
        }
        
        // Kiểm tra token đã hết hạn chưa
        if (strtotime($token['expires_at']) <= time()) {
            // Token đã hết hạn, cần refresh
            if (!empty($token['refresh_token'])) {
                $googleAuth = new \App\Controllers\GoogleAuth();
                $newTokenData = $googleAuth->refreshToken($token['refresh_token']);
                
                if ($newTokenData && !isset($newTokenData['error'])) {
                    // Giữ lại refresh_token cũ vì Google không trả về refresh_token mới
                    $newTokenData['refresh_token'] = $token['refresh_token'];
                    
                    // Lưu token mới vào database
                    $this->saveToken($userId, $newTokenData);
                    
                    // Lấy lại token mới từ database
                    return $this->where('user_id', $userId)->first();
                }
            }
            return null;
        }
        
        return $token;
    }
}