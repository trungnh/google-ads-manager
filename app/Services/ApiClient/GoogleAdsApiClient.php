<?php

namespace App\Services\ApiClient;

use Exception;
use Config\Services;

class GoogleAdsApiClient
{
    protected $apiVersion = 'v22';
    protected $baseUrl = 'https://googleads.googleapis.com/';
    protected $accessToken;
    protected $refreshToken;
    protected $developerToken;
    protected $onTokenRefreshed;

    public function __construct($accessToken, $refreshToken = null, $developerToken = null, callable $onTokenRefreshed = null)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->developerToken = $developerToken ?? getenv('GOOGLE_ADS_DEVELOPER_TOKEN');
        $this->onTokenRefreshed = $onTokenRefreshed;
    }

    /**
     * Set the current access token
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Get the current access token
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Format customer ID to strip dashes
     */
    public function formatCustomerId($customerId)
    {
        return preg_replace('/[^0-9]/', '', $customerId);
    }

    /**
     * Make a generic cURL request to Google Ads API with automatic 401 retry
     */
    public function makeRequest($endpoint, $method = 'GET', $data = null, $loginCustomerId = null, $retryCount = 0)
    {
        $url = (strpos($endpoint, 'http') === 0) ? $endpoint : $this->baseUrl . $this->apiVersion . '/' . ltrim($endpoint, '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
            'developer-token: ' . $this->developerToken
        ];

        if ($loginCustomerId) {
            $formattedLoginCustomerId = $this->formatCustomerId($loginCustomerId);
            $headers[] = 'login-customer-id: ' . $formattedLoginCustomerId;
            log_message('debug', '[ApiClient] Using login-customer-id: ' . $formattedLoginCustomerId);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($data && $method !== 'GET') {
            $payload = is_string($data) ? $data : json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            log_message('debug', "[ApiClient] Request body: " . $payload);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        log_message('debug', "[ApiClient] HTTP Status Code: " . $httpCode);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', '[ApiClient] cURL Error: ' . $error);
            throw new Exception('cURL error: ' . $error);
        }

        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        // Handle 401 Unauthorized
        if ($httpCode === 401 && $retryCount < 1 && $this->refreshToken) {
            log_message('warning', '[ApiClient] 401 Unauthorized. Attempting to refresh token...');
            try {
                $this->refreshAccessToken();
                // Retry the request with new token
                return $this->makeRequest($endpoint, $method, $data, $loginCustomerId, $retryCount + 1);
            } catch (Exception $e) {
                log_message('error', '[ApiClient] Token refresh failed: ' . $e->getMessage());
                // Fallthrough to throw exception below
            }
        }

        if ($httpCode >= 400) {
            $errorMessage = isset($decodedResponse['error']['message'])
                ? $decodedResponse['error']['message']
                : 'API request failed with status ' . $httpCode . '. Response: ' . $response;

            log_message('error', "[ApiClient] Google Ads API Error ({$httpCode}): " . $errorMessage);
            throw new Exception($errorMessage, $httpCode);
        }

        return $decodedResponse;
    }

    /**
     * Refresh token by calling Google Auth Endpoint
     */
    protected function refreshAccessToken()
    {
        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
            'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            log_message('error', '[ApiClient] Error refreshing token: ' . json_encode($error));
            throw new Exception('Error refreshing token: ' . ($error['error_description'] ?? 'Unknown error'));
        }

        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            throw new Exception('Invalid response from Google OAuth server');
        }

        $this->accessToken = $tokenData['access_token'];

        // Trigger callback to save new token
        if (is_callable($this->onTokenRefreshed)) {
            call_user_func($this->onTokenRefreshed, [
                'access_token' => $tokenData['access_token'],
                'expires_in' => $tokenData['expires_in'],
                'token_type' => $tokenData['token_type']
            ]);
        }

        log_message('info', '[ApiClient] Successfully refreshed token.');
        return $tokenData;
    }
}
