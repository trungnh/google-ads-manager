<?php

namespace App\Services;

use Exception;

class GoogleAdsService
{
    protected $apiVersion = 'v19';
    protected $baseUrl = 'https://googleads.googleapis.com/';
    
    public function getAccessibleAccounts($accessToken, $mccId = null)
    {
        $accounts = [];
        
        try {
            if ($mccId) {
                // Nếu có MCC ID, lấy danh sách tài khoản từ MCC
                $accounts = $this->getAccountsFromMcc($accessToken, $mccId);
            } else {
                // Nếu không có MCC ID, lấy danh sách tất cả tài khoản có thể truy cập
                $accounts = $this->getAccountsFromOwnAccess($accessToken);
            }
            
            return $accounts;
        } catch (Exception $e) {
            log_message('error', 'Lỗi khi lấy danh sách tài khoản: ' . $e->getMessage());
            throw new Exception('Lỗi khi lấy danh sách tài khoản: ' . $e->getMessage());
        }
    }
    
    protected function getAccountsFromMcc($accessToken, $mccId)
    {
        // Đảm bảo MCC ID có định dạng xxx-xxx-xxxx thành xxxxxxxxxx
        $formattedMccId = $this->formatCustomerId($mccId);
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedMccId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                customer_client.id,
                customer_client.descriptive_name,
                customer_client.currency_code,
                customer_client.time_zone,
                customer_client.status
            FROM
                customer_client
            WHERE
                customer_client.status = 'ENABLED'
        ";
        
        $data = [
            'query' => $query
        ];
        
        $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data));
        $accounts = [];
        
        // Xử lý phản hồi từ API
        if (is_array($response)) {
            foreach ($response as $batch) {
                if (isset($batch['results'])) {
                    foreach ($batch['results'] as $result) {
                        $customerClient = $result['customerClient'] ?? null;
                        
                        if ($customerClient) {
                            $accounts[] = [
                                'customer_id' => $customerClient['id'],
                                'customer_name' => $customerClient['descriptiveName'] ?? 'Unknown',
                                'currency_code' => $customerClient['currencyCode'] ?? null,
                                'time_zone' => $customerClient['timeZone'] ?? null,
                                'status' => $customerClient['status'] ?? 'ACTIVE'
                            ];
                        }
                    }
                }
            }
        }
        
        return $accounts;
    }
    
    protected function getAccountsFromOwnAccess($accessToken)
    {
        $url = $this->baseUrl . $this->apiVersion . '/customers:listAccessibleCustomers';
        
        $response = $this->makeCurlRequest($url, 'GET', $accessToken);
        
        $accounts = [];
        
        if (isset($response['resourceNames']) && !empty($response['resourceNames'])) {
            foreach ($response['resourceNames'] as $resourceName) {
                $customerId = str_replace('customers/', '', $resourceName);
                
                // Lấy thông tin chi tiết về customer
                $customerDetails = $this->getCustomerDetails($accessToken, $customerId);
                
                if ($customerDetails) {
                    $accounts[] = $customerDetails;
                }
            }
        }
        
        return $accounts;
    }
    
    protected function getCustomerDetails($accessToken, $customerId)
    {
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $customerId;
        
        try {
            $response = $this->makeCurlRequest($url, 'GET', $accessToken);
            
            if (isset($response['resourceName'])) {
                return [
                    'customer_id' => $customerId,
                    'customer_name' => $response['descriptiveName'] ?? 'Unknown',
                    'currency_code' => $response['currencyCode'] ?? null,
                    'time_zone' => $response['timeZone'] ?? null,
                    'status' => 'ACTIVE'
                ];
            }
        } catch (Exception $e) {
            log_message('error', 'Lỗi khi lấy thông tin chi tiết customer ' . $customerId . ': ' . $e->getMessage());
        }
        
        return null;
    }
    
    // Thêm hàm này để định dạng Customer ID
    protected function formatCustomerId($customerId) 
    {
        // Loại bỏ tất cả các ký tự không phải số
        $customerId = preg_replace('/[^0-9]/', '', $customerId);
        
        return $customerId;
    }

    protected function makeCurlRequest($url, $method, $accessToken, $data = null, $loginCustomerId = null)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
            'developer-token: ' . getenv('GOOGLE_ADS_DEVELOPER_TOKEN')
        ];

        // Thêm login-customer-id header nếu có
        if ($loginCustomerId) {
            $headers[] = 'login-customer-id: ' . $this->formatCustomerId($loginCustomerId);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data && $method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('cURL error: ' . $error);
        }

        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        
        // Kiểm tra và xử lý lỗi
        if ($httpCode >= 400) {
            $errorMessage = isset($decodedResponse['error']['message']) 
                ? $decodedResponse['error']['message'] 
                : 'API request failed with status ' . $httpCode . '. Response: ' . $response;
            
            log_message('error', 'Google Ads API Error: ' . $errorMessage);
            throw new Exception($errorMessage);
        }
        
        return $decodedResponse;
    }

    public function getCampaigns($customerId, $accessToken, $mccId = null, $showPaused = false, $startDate = null, $endDate = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                campaign.id,
                campaign.name,
                campaign.status,
                campaign.advertising_channel_type,
                campaign.bidding_strategy_type,
                metrics.cost_micros,
                metrics.conversions,
                metrics.conversions_value,
                metrics.cost_per_conversion,
                metrics.conversions_from_interactions_rate,
                metrics.average_cpc,
                metrics.ctr,
                metrics.clicks,
                campaign.maximize_conversions.target_cpa_micros,
                campaign.maximize_conversion_value.target_roas,
                campaign_budget.amount_micros
            FROM campaign
            WHERE campaign.status != 'REMOVED'" . 
            (!$showPaused ? " AND campaign.status = 'ENABLED'" : "") .
            ($startDate && $endDate ? " AND segments.date BETWEEN '$startDate' AND '$endDate'" : "");
        
        $data = [
            'query' => $query
        ];
        
        // Thêm MCC ID vào request header nếu có
        $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
        $campaigns = [];
        
        if (is_array($response)) {
            foreach ($response as $batch) {
                if (isset($batch['results'])) {
                    foreach ($batch['results'] as $result) {
                        $campaign = $result['campaign'];
                        $metrics = $result['metrics'];
                        $budget = $result['campaignBudget'] ?? null;
                        
                        $campaigns[] = [
                            'campaign_id' => $campaign['id'],
                            'name' => $campaign['name'],
                            'status' => $campaign['status'],
                            'budget' => $budget ? $this->microToStandard($budget['amountMicros']) : 0,
                            'cost' => isset($metrics['costMicros']) ? $this->microToStandard($metrics['costMicros']) : 0,
                            'conversions' => $metrics['conversions'] ?? 0,
                            'conversion_value' => $metrics['conversionsValue'] ?? 0,
                            'cost_per_conversion' => isset($metrics['costMicros'], $metrics['conversions']) && $metrics['conversions'] > 0 
                                ? $this->microToStandard($metrics['costMicros']) / $metrics['conversions'] 
                                : 0,
                            'conversion_rate' => $metrics['conversionsFromInteractionsRate'] ?? 0,
                            'target_cpa' => isset($campaign['maximizeConversions']['targetCpaMicros']) ? $this->microToStandard($campaign['maximizeConversions']['targetCpaMicros']) : null,
                            'target_roas' => isset($campaign['maximizeConversionValue']['targetRoas']) ? $campaign['maximizeConversionValue']['targetRoas'] : null,
                            'ctr' => $metrics['ctr'] ?? 0,
                            'clicks' => $metrics['clicks'] ?? 0,
                            'average_cpc' => isset($metrics['averageCpc']) ? $this->microToStandard($metrics['averageCpc']) : 0
                        ];
                    }
                }
            }
        }
        
        return $campaigns;
    }

    public function toggleCampaignStatus($customerId, $campaignId, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        // Đầu tiên lấy trạng thái hiện tại của chiến dịch
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                campaign.id,
                campaign.status
            FROM campaign
            WHERE campaign.id = " . $campaignId;
        
        $data = [
            'query' => $query
        ];
        
        $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
        
        if (!isset($response[0]['results'][0]['campaign'])) {
            throw new Exception('Không tìm thấy chiến dịch');
        }
        
        $currentStatus = $response[0]['results'][0]['campaign']['status'];
        $newStatus = $currentStatus === 'ENABLED' ? 'PAUSED' : 'ENABLED';
        
        // Thực hiện update status
        $updateUrl = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/campaigns/' . $campaignId . ':mutate';
        
        $updateData = [
            'updateMask' => 'status',
            'campaign' => [
                'resourceName' => 'customers/' . $formattedCustomerId . '/campaigns/' . $campaignId,
                'status' => $newStatus
            ]
        ];
        
        return $this->makeCurlRequest($updateUrl, 'POST', $accessToken, json_encode($updateData), $mccId);
    }

    protected function microToStandard($microAmount)
    {
        return $microAmount / 1000000;
    }

    public function getTodayCampaignMetrics($customerId, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        $today = date('Y-m-d');
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                campaign.id,
                campaign.name,
                campaign.status,
                metrics.cost_micros,
                metrics.conversions,
                metrics.cost_per_conversion,
                campaign_budget.amount_micros
            FROM campaign
            WHERE campaign.status = 'ENABLED'
            AND segments.date = '$today'";
        
        $data = [
            'query' => $query
        ];
        
        $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
        $campaigns = [];
        
        if (is_array($response)) {
            foreach ($response as $batch) {
                if (isset($batch['results'])) {
                    foreach ($batch['results'] as $result) {
                        $campaign = $result['campaign'];
                        $metrics = $result['metrics'];
                        $budget = $result['campaignBudget'] ?? null;
                        
                        $campaigns[] = [
                            'campaign_id' => $campaign['id'],
                            'name' => $campaign['name'],
                            'status' => $campaign['status'],
                            'budget' => $budget ? $this->microToStandard($budget['amountMicros']) : 0,
                            'cost' => isset($metrics['costMicros']) ? $this->microToStandard($metrics['costMicros']) : 0,
                            'conversions' => $metrics['conversions'] ?? 0,
                            'cost_per_conversion' => isset($metrics['costMicros'], $metrics['conversions']) && $metrics['conversions'] > 0 
                                ? $this->microToStandard($metrics['costMicros']) / $metrics['conversions'] 
                                : 0
                        ];
                    }
                }
            }
        }
        
        return $campaigns;
    }

    public function updateCampaignBudget($customerId, $campaignId, $newBudget, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        // Đầu tiên lấy thông tin budget hiện tại
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                campaign.id,
                campaign_budget.id,
                campaign_budget.amount_micros
            FROM campaign
            WHERE campaign.id = " . $campaignId;
        
        $data = [
            'query' => $query
        ];
        
        $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
        
        if (!isset($response[0]['results'][0]['campaignBudget'])) {
            throw new Exception('Không tìm thấy budget của chiến dịch');
        }
        
        $budgetId = $response[0]['results'][0]['campaignBudget']['id'];
        
        // Thực hiện update budget
        $updateUrl = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/campaignBudgets/' . $budgetId . ':mutate';
        
        $updateData = [
            'updateMask' => 'amountMicros',
            'campaignBudget' => [
                'resourceName' => 'customers/' . $formattedCustomerId . '/campaignBudgets/' . $budgetId,
                'amountMicros' => $newBudget * 1000000
            ]
        ];
        
        return $this->makeCurlRequest($updateUrl, 'POST', $accessToken, json_encode($updateData), $mccId);
    }
}