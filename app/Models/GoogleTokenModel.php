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
                // Implement refresh token logic here
                // $newToken = $this->refreshToken($token['refresh_token']);
                // return $newToken;
                return null; // Tạm thời trả về null, sẽ implement sau
            }
            return null;
        }
        
        return $token;
    }
}