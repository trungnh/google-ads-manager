<?php

namespace App\Services\Tokens;

use App\Models\GoogleTokenModel;
use App\Services\ApiClient\GoogleAdsApiClient;
use Exception;

class TokenManagerService
{
    protected $tokenModel;

    public function __construct()
    {
        $this->tokenModel = new GoogleTokenModel();
    }

    /**
     * Khởi tạo GoogleAdsApiClient cho một người dùng bằng userId.
     * Service này sẽ xử lý tự động làm mới token và lưu vào db.
     *
     * @param int $userId
     * @return GoogleAdsApiClient
     * @throws Exception
     */
    public function getApiClientForUser($userId)
    {
        $tokenData = $this->tokenModel->getValidToken($userId);
        
        if (!$tokenData) {
            throw new Exception('Không tìm thấy token hợp lệ hoặc token đã thu hồi đối với user_id: ' . $userId);
        }

        $tokenId = $tokenData['id'] ?? null;
        $accessToken = $tokenData['access_token'] ?? null;
        $refreshToken = $tokenData['refresh_token'] ?? null;

        if (!$accessToken) {
            throw new Exception('Thiếu access_token đối với user_id: ' . $userId);
        }

        return new GoogleAdsApiClient(
            $accessToken,
            $refreshToken,
            null,
            function ($newTokenData) use ($tokenId) {
                if ($tokenId) {
                    $this->tokenModel->update($tokenId, [
                        'access_token' => $newTokenData['access_token'],
                        'expires_at'   => date('Y-m-d H:i:s', time() + $newTokenData['expires_in']),
                        'updated_at'   => date('Y-m-d H:i:s')
                    ]);
                    log_message('info', "[TokenManagerService] Đã lưu thành công token mới cho record ID: {$tokenId}");
                }
            }
        );
    }
}
