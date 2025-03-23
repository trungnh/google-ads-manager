<?php

namespace App\Controllers;

use App\Models\GoogleTokenModel;
use CodeIgniter\Controller;

class GoogleAuth extends Controller
{
    /**
     * Hiển thị trang OAuth để kết nối với Google Ads
     */
    public function oauth()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục');
        }
        
        // Lấy các thông tin cấu hình Google API
        $clientId = getenv('GOOGLE_CLIENT_ID');
        $redirectUri = getenv('GOOGLE_REDIRECT_URI');
        $scopes = getenv('GOOGLE_AUTH_SCOPES');
        
        // Tạo OAuth URL
        $googleOAuthUrl = $this->buildGoogleOAuthUrl($clientId, $redirectUri, $scopes);
        
        $data = [
            'googleOAuthUrl' => $googleOAuthUrl
        ];
        
        return view('google/oauth', $data);
    }
    
    /**
     * Xử lý callback từ Google OAuth
     */
    public function callback()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục');
        }
        
        // Lấy authorization code từ URL
        $code = $this->request->getGet('code');
        
        if (empty($code)) {
            return redirect()->to('/google/oauth')->with('error', 'Authorization code không hợp lệ');
        }
        
        // Lấy thông tin cấu hình
        $clientId = getenv('GOOGLE_CLIENT_ID');
        $clientSecret = getenv('GOOGLE_CLIENT_SECRET');
        $redirectUri = getenv('GOOGLE_REDIRECT_URI');
        
        // Đổi code lấy token
        $tokenData = $this->exchangeCodeForToken($code, $clientId, $clientSecret, $redirectUri);
        
        if (!$tokenData || isset($tokenData['error'])) {
            $errorMessage = isset($tokenData['error_description']) ? $tokenData['error_description'] : 'Không thể lấy access token từ Google';
            return redirect()->to('/google/oauth')->with('error', $errorMessage);
        }
        
        // Lưu token vào database
        $googleTokenModel = new GoogleTokenModel();
        $userId = session()->get('id');
        $googleTokenModel->saveToken($userId, $tokenData);
        
        return redirect()->to('/home')->with('success', 'Kết nối với Google Ads thành công!');
    }
    
    /**
     * Xây dựng URL OAuth để kết nối với Google
     */
    private function buildGoogleOAuthUrl($clientId, $redirectUri, $scope)
    {
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'access_type' => 'offline', // Để lấy refresh_token
            'response_type' => 'code',
            'prompt' => 'consent', // Luôn hiển thị màn hình đồng ý để lấy refresh_token mới
            'include_granted_scopes' => 'true',
        ];
        
        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    }
    
    /**
     * Đổi authorization code lấy access token
     */
    private function exchangeCodeForToken($code, $clientId, $clientSecret, $redirectUri)
    {
        $url = 'https://oauth2.googleapis.com/token';
        
        $params = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($httpCode != 200) {
            log_message('error', 'Google OAuth Error: ' . $response);
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Làm mới access token bằng refresh token
     */
    public function refreshToken($refreshToken)
    {
        $clientId = getenv('GOOGLE_CLIENT_ID');
        $clientSecret = getenv('GOOGLE_CLIENT_SECRET');
        
        $url = 'https://oauth2.googleapis.com/token';
        
        $params = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($httpCode != 200) {
            log_message('error', 'Google Token Refresh Error: ' . $response);
            return null;
        }
        
        return json_decode($response, true);
    }
}