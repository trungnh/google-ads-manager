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

    protected function makeAccountListRequest($url, $method, $accessToken, $data = null, $loginCustomerId = null)
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
            $formattedLoginCustomerId = $this->formatCustomerId($loginCustomerId);
            $headers[] = 'login-customer-id: ' . $formattedLoginCustomerId;
            log_message('debug', '[Account List] Using login-customer-id: ' . $formattedLoginCustomerId);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data && $method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            log_message('debug', '[Account List] Request body: ' . $data);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', '[Account List] cURL Error: ' . $error);
            throw new Exception('cURL error: ' . $error);
        }

        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMessage = isset($decodedResponse['error']['message']) 
                ? $decodedResponse['error']['message'] 
                : 'API request failed with status ' . $httpCode . '. Response: ' . $response;
            
            log_message('error', '[Account List] Google Ads API Error: ' . $errorMessage);
            throw new Exception('API request failed with status ' . $httpCode . '. Response: ' . $response);
        }
        
        return $decodedResponse;
    }

    protected function getAccountDetails($accessToken, $customerId, $mccId = null)
    {
        try {
            $formattedCustomerId = $this->formatCustomerId($customerId);
            $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
            
            $query = "
                SELECT
                    customer.id,
                    customer.descriptive_name,
                    customer.currency_code,
                    customer.time_zone,
                    customer.status
                FROM customer
                WHERE customer.id = " . $formattedCustomerId;
            
            $data = [
                'query' => $query
            ];
            
            $response = $this->makeAccountListRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            
            if (isset($response[0]['results'][0]['customer'])) {
                $customer = $response[0]['results'][0]['customer'];
                return [
                    'customer_id' => $customer['id'],
                    'customer_name' => $customer['descriptiveName'] ?? 'Unknown',
                    'currency_code' => $customer['currencyCode'] ?? null,
                    'time_zone' => $customer['timeZone'] ?? null,
                    'status' => $customer['status'] ?? 'UNKNOWN'
                ];
            }
            
            return null;
        } catch (Exception $e) {
            log_message('error', 'Error getting account details for customer ' . $customerId . ': ' . $e->getMessage());
            return null;
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
                            if ($mccId == $customerClient['id']) {
                                continue;
                            }
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
        
        try {
            $response = $this->makeCurlRequest($url, 'GET', $accessToken);
            
            $accounts = [];
            
            if (isset($response['resourceNames']) && !empty($response['resourceNames'])) {
                foreach ($response['resourceNames'] as $resourceName) {
                    $customerId = str_replace('customers/', '', $resourceName);
                    
                    // Lấy thông tin chi tiết về customer bằng cách sử dụng searchStream
                    $customerDetails = $this->getCustomerDetailsFromSearch($accessToken, $customerId);
                    if ($customerDetails) {
                        $accounts[] = $customerDetails;
                    }
                }
            }
            
            return $accounts;
        } catch (Exception $e) {
            log_message('error', 'Lỗi khi lấy danh sách tài khoản: ' . $e->getMessage());
            throw $e;
        }
    }
    
    protected function getCustomerDetailsFromSearch($accessToken, $customerId)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                customer.id,
                customer.descriptive_name,
                customer.currency_code,
                customer.time_zone,
                customer.status
            FROM
                customer
            WHERE
                customer.id = " . $formattedCustomerId;
        
        $data = [
            'query' => $query
        ];
        
        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data));
            
            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            if (isset($result['customer'])) {
                                $customer = $result['customer'];
                                return [
                                    'customer_id' => $customer['id'],
                                    'customer_name' => $customer['descriptiveName'] ?? 'Unknown',
                                    'currency_code' => $customer['currencyCode'] ?? null,
                                    'time_zone' => $customer['timeZone'] ?? null,
                                    'status' => $customer['status'] ?? 'ACTIVE'
                                ];
                            }
                        }
                    }
                }
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
            // Đảm bảo ID được định dạng đúng (không có dấu gạch ngang)
            $formattedLoginCustomerId = $this->formatCustomerId($loginCustomerId);
            $headers[] = 'login-customer-id: ' . $formattedLoginCustomerId;
            
            // Log để debug
            log_message('debug', '[CURL] Using login-customer-id header: ' . $formattedLoginCustomerId);
        } else {
            log_message('debug', '[CURL] No login-customer-id provided');
        }
        
        // Log tất cả các header để debug
        log_message('debug', '[CURL] Headers: ' . json_encode($headers));
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data && $method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            log_message('debug', '[CURL] Request body: ' . $data);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        log_message('debug', '[CURL] HTTP Status Code: ' . $httpCode);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', '[CURL] Error: ' . $error);
            throw new Exception('cURL error: ' . $error);
        }

        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        
        // Kiểm tra và xử lý lỗi
        if ($httpCode >= 400) {
            $errorMessage = isset($decodedResponse['error']['message']) 
                ? $decodedResponse['error']['message'] 
                : 'API request failed with status ' . $httpCode . '. Response: ' . $response;
            
            log_message('error', '[CURL] Google Ads API Error: ' . $errorMessage);
            throw new Exception('API request failed with status ' . $httpCode . '. Response: ' . $response);
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
                campaign.maximize_conversions.target_cpa_micros,
                campaign.target_cpa.target_cpa_micros,
                campaign.maximize_conversion_value.target_roas,
                campaign.target_roas.target_roas,
                metrics.cost_micros,
                metrics.conversions,
                metrics.conversions_value,
                metrics.cost_per_conversion,
                metrics.conversions_from_interactions_rate,
                metrics.average_cpc,
                metrics.ctr,
                metrics.clicks,
                campaign_budget.amount_micros
            FROM campaign
            WHERE campaign.status != 'REMOVED'" . 
            (!$showPaused ? " AND campaign.status = 'ENABLED'" : "") .
            ($startDate && $endDate ? " AND segments.date BETWEEN '$startDate' AND '$endDate'" : "");
        
        $data = [
            'query' => $query
        ];

        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            $campaigns = [];

            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            if (!isset($result['campaign'])) {
                                continue;
                            }

                            $campaign = $result['campaign'];
                            $metrics = $result['metrics'] ?? [];
                            $budget = $result['campaignBudget'] ?? null;
                            
                            // Xác định target CPA và ROAS dựa trên chiến lược đặt giá thầu
                            $targetCpa = null;
                            $targetRoas = null;
                            
                            $biddingStrategyType = $campaign['biddingStrategyType'] ?? '';
                            
                            // Lấy CPA mục tiêu từ các loại chiến lược đặt giá thầu khác nhau
                            if (isset($campaign['maximizeConversions']['targetCpaMicros']) && $campaign['maximizeConversions']['targetCpaMicros'] > 0) {
                                $targetCpa = $this->microToStandard($campaign['maximizeConversions']['targetCpaMicros']);
                            } elseif (isset($campaign['targetCpa']['targetCpaMicros']) && $campaign['targetCpa']['targetCpaMicros'] > 0) {
                                $targetCpa = $this->microToStandard($campaign['targetCpa']['targetCpaMicros']);
                            }
                            
                            // Lấy ROAS mục tiêu từ các loại chiến lược đặt giá thầu khác nhau
                            if (isset($campaign['maximizeConversionValue']['targetRoas']) && $campaign['maximizeConversionValue']['targetRoas'] > 0) {
                                $targetRoas = $campaign['maximizeConversionValue']['targetRoas'];
                            } elseif (isset($campaign['targetRoas']['targetRoas']) && $campaign['targetRoas']['targetRoas'] > 0) {
                                $targetRoas = $campaign['targetRoas']['targetRoas'];
                            }
                            
                            // Log để debug
                            log_message('debug', 'Campaign ID: ' . $campaign['id'] . ' - Bidding Strategy: ' . $biddingStrategyType);
                            if ($targetCpa !== null) {
                                log_message('debug', 'Target CPA: ' . $targetCpa);
                            }
                            if ($targetRoas !== null) {
                                log_message('debug', 'Target ROAS: ' . $targetRoas);
                            }
                            
                            $campaigns[] = [
                                'campaign_id' => $campaign['id'],
                                'name' => $campaign['name'],
                                'status' => $campaign['status'],
                                'budget' => isset($budget['amountMicros']) ? $this->microToStandard($budget['amountMicros']) : 0,
                                'cost' => isset($metrics['costMicros']) ? $this->microToStandard($metrics['costMicros']) : 0,
                                'conversions' => $metrics['conversions'] ?? 0,
                                'conversion_value' => $metrics['conversionsValue'] ?? 0,
                                'cost_per_conversion' => isset($metrics['costMicros'], $metrics['conversions']) && $metrics['conversions'] > 0 
                                    ? $this->microToStandard($metrics['costMicros']) / $metrics['conversions'] 
                                    : 0,
                                'conversion_rate' => $metrics['conversionsFromInteractionsRate'] ?? 0,
                                'bidding_strategy' => $biddingStrategyType,
                                'target_cpa' => $targetCpa,
                                'target_roas' => $targetRoas,
                                'ctr' => $metrics['ctr'] ?? 0,
                                'clicks' => $metrics['clicks'] ?? 0,
                                'average_cpc' => isset($metrics['averageCpc']) ? $this->microToStandard($metrics['averageCpc']) : 0,
                                'customer_id' => $customerId
                            ];
                        }
                    }
                }
            }

            return $campaigns;
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsService::getCampaigns: ' . $e->getMessage());
            throw $e;
        }
    }

    public function toggleCampaignStatus($accessToken, $customerId, $campaignId, $status, $mccId = null)
    {
        try {
            // Validate input
            if (empty($customerId)) {
                log_message('error', 'Customer ID is empty in toggleCampaignStatus');
                throw new Exception('ID tài khoản không hợp lệ');
            }

            if (empty($campaignId)) {
                log_message('error', 'Campaign ID is empty in toggleCampaignStatus');
                throw new Exception('ID chiến dịch không hợp lệ');
            }

            if (!in_array($status, ['ENABLED', 'PAUSED'])) {
                log_message('error', 'Invalid status in toggleCampaignStatus: ' . $status);
                throw new Exception('Trạng thái không hợp lệ');
            }

            // Format customer ID
            $formattedCustomerId = $this->formatCustomerId($customerId);
            
            // Prepare the request URL
            $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:mutate';
            
            // Prepare the request data
            $data = [
                'mutateOperations' => [
                    [
                        'campaignOperation' => [
                            'update' => [
                                'resourceName' => "customers/{$formattedCustomerId}/campaigns/{$campaignId}",
                                'status' => $status
                            ],
                            'updateMask' => [
                                'paths' => ['status']
                            ]
                        ]
                    ]
                ]
            ];

            log_message('info', 'Making API request to toggle campaign status: ' . json_encode($data));

            // Make the API request - loginCustomerId will be handled in makeCurlRequest header
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);

            if (isset($response['mutateOperationResponses'][0]['campaignResult'])) {
                log_message('info', 'Successfully toggled campaign status');
                return true;
            } else {
                log_message('error', 'Failed to toggle campaign status. Response: ' . json_encode($response));
                throw new Exception('Không thể cập nhật trạng thái chiến dịch');
            }
        } catch (Exception $e) {
            log_message('error', 'Error in toggleCampaignStatus: ' . $e->getMessage());
            throw $e;
        }
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
        
        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            $campaigns = [];
            
            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            // Kiểm tra tồn tại của campaign và metrics
                            if (!isset($result['campaign'])) {
                                continue;
                            }
                            
                            $campaign = $result['campaign'];
                            $metrics = $result['metrics'] ?? [];
                            $budget = $result['campaignBudget'] ?? null;
                            
                            // Chỉ thêm vào mảng kết quả nếu có đủ thông tin cần thiết
                            if (isset($campaign['id'])) {
                                $campaigns[] = [
                                    'campaign_id' => $campaign['id'],
                                    'name' => $campaign['name'] ?? 'Unknown',
                                    'status' => $campaign['status'] ?? 'UNKNOWN',
                                    'budget' => $budget ? $this->microToStandard($budget['amountMicros']) : 0,
                                    'cost' => isset($metrics['costMicros']) ? $this->microToStandard($metrics['costMicros']) : 0,
                                    'conversions' => $metrics['conversions'] ?? 0,
                                    'cost_per_conversion' => isset($metrics['costMicros'], $metrics['conversions']) && $metrics['conversions'] > 0 
                                        ? $this->microToStandard($metrics['costMicros']) / $metrics['conversions'] 
                                        : 0,
                                    'customer_id' => $customerId
                                ];
                            }
                        }
                    }
                }
            }
            
            return $campaigns;
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsService::getTodayCampaignMetrics: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function makeCampaignBudgetRequest($url, $method, $accessToken, $data, $loginCustomerId = null)
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

        if ($loginCustomerId) {
            $formattedLoginCustomerId = $this->formatCustomerId($loginCustomerId);
            $headers[] = 'login-customer-id: ' . $formattedLoginCustomerId;
            log_message('debug', '[Budget Request] Using login-customer-id: ' . $formattedLoginCustomerId);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data && $method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            log_message('debug', '[Budget Request] Request body: ' . $data);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', '[Budget Request] cURL Error: ' . $error);
            throw new Exception('cURL error: ' . $error);
        }

        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMessage = isset($decodedResponse['error']['message']) 
                ? $decodedResponse['error']['message'] 
                : 'API request failed with status ' . $httpCode . '. Response: ' . $response;
            
            log_message('error', '[Budget Request] Google Ads API Error: ' . $errorMessage);
            throw new Exception('API request failed with status ' . $httpCode . '. Response: ' . $response);
        }
        
        return $decodedResponse;
    }

    public function updateCampaignBudget($accessToken, $customerId, $campaignId, $newBudget, $mccId = null)
    {
        try {
            // Validate input
            if (empty($customerId)) {
                log_message('error', 'Customer ID is empty in updateCampaignBudget');
                throw new Exception('ID tài khoản không hợp lệ');
            }

            if (empty($campaignId)) {
                log_message('error', 'Campaign ID is empty in updateCampaignBudget');
                throw new Exception('ID chiến dịch không hợp lệ');
            }

            // Format customer ID
            $formattedCustomerId = $this->formatCustomerId($customerId);
            
            log_message('debug', "updateCampaignBudget: Starting budget update for campaign {$campaignId} with customerId {$formattedCustomerId}");
            
            // Get campaign and its current budget resource name
            $searchUrl = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
            
            $query = "
                SELECT
                    campaign.id,
                    campaign.resource_name,
                    campaign_budget.id,
                    campaign_budget.resource_name,
                    campaign_budget.amount_micros,
                    campaign_budget.type
                FROM campaign
                WHERE campaign.id = " . $campaignId;
            
            $data = [
                'query' => $query
            ];
            
            log_message('debug', "updateCampaignBudget: Searching for budget info with query: " . json_encode($data));
            
            $response = $this->makeCampaignBudgetRequest($searchUrl, 'POST', $accessToken, json_encode($data), $mccId);
            
            if (!isset($response[0]['results'][0]['campaign']['resourceName'])) {
                log_message('error', "updateCampaignBudget: Campaign not found. Response: " . json_encode($response));
                throw new Exception('Campaign not found');
            }
            
            if (!isset($response[0]['results'][0]['campaignBudget']['resourceName'])) {
                log_message('error', "updateCampaignBudget: Campaign budget not found. Response: " . json_encode($response));
                throw new Exception('Campaign budget not found');
            }
            
            $campaignResourceName = $response[0]['results'][0]['campaign']['resourceName'];
            $budgetResourceName = $response[0]['results'][0]['campaignBudget']['resourceName'];
            $budgetType = $response[0]['results'][0]['campaignBudget']['type'] ?? 'STANDARD';
            
            log_message('debug', "updateCampaignBudget: Found campaign resource: {$campaignResourceName}");
            log_message('debug', "updateCampaignBudget: Found budget resource: {$budgetResourceName}");
            
            // Convert budget to micros (1 unit = 1,000,000 micros)
            $budgetMicros = (int)($newBudget * 1000000);
            
            // Update the existing budget directly
            $updateBudgetUrl = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:mutate';
            
            $updateBudgetData = [
                'mutateOperations' => [
                    [
                        'campaignBudgetOperation' => [
                            'update' => [
                                'resourceName' => $budgetResourceName,
                                'amountMicros' => $budgetMicros,
                            ],
                            'updateMask' => [
                                'paths' => ['amount_micros']
                            ]
                        ]
                    ]
                ]
            ];
            
            log_message('debug', "updateCampaignBudget: Updating existing budget with data: " . json_encode($updateBudgetData));
            
            $updateResponse = $this->makeCampaignBudgetRequest($updateBudgetUrl, 'POST', $accessToken, json_encode($updateBudgetData), $mccId);
            
            log_message('debug', "updateCampaignBudget: Budget update response: " . json_encode($updateResponse));
            
            if (!isset($updateResponse['mutateOperationResponses'][0]['campaignBudgetResult'])) {
                log_message('error', "updateCampaignBudget: Failed to update budget. Response: " . json_encode($updateResponse));
                throw new Exception('Failed to update campaign budget');
            }
            
            log_message('debug', "updateCampaignBudget: Budget update successful");
            return true;
        } catch (Exception $e) {
            log_message('error', 'Error updating campaign budget: ' . $e->getMessage());
            throw $e;
        }
    }

    public function refreshToken($refreshToken)
    {
        try {
            $url = 'https://oauth2.googleapis.com/token';
            $data = [
                'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
                'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
                'refresh_token' => $refreshToken,
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
                log_message('error', 'Error refreshing token: ' . json_encode($error));
                throw new Exception('Error refreshing token: ' . ($error['error_description'] ?? 'Unknown error'));
            }

            $tokenData = json_decode($response, true);
            if (!isset($tokenData['access_token'])) {
                throw new Exception('Invalid response from Google OAuth server');
            }

            return [
                'access_token' => $tokenData['access_token'],
                'expires_in' => $tokenData['expires_in'],
                'token_type' => $tokenData['token_type']
            ];
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsService::refreshToken: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateCampaignTarget($accessToken, $customerId, $campaignId, $type, $value, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        try {
            // First, get campaign details to determine bidding strategy
            $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
            
            $query = "
                SELECT
                    campaign.id,
                    campaign.resource_name,
                    campaign.bidding_strategy_type,
                    campaign.maximize_conversions.target_cpa_micros,
                    campaign.target_cpa.target_cpa_micros,
                    campaign.maximize_conversion_value.target_roas,
                    campaign.target_roas.target_roas
                FROM campaign
                WHERE campaign.id = " . $campaignId;
            
            $data = [
                'query' => $query
            ];
            
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            
            if (!isset($response[0]['results'][0]['campaign'])) {
                throw new \Exception('Không tìm thấy chiến dịch');
            }
            
            $campaign = $response[0]['results'][0]['campaign'];
            $resourceName = $campaign['resourceName'];
            $biddingStrategyType = $campaign['biddingStrategyType'] ?? '';
            
            log_message('debug', 'Campaign bidding strategy: ' . $biddingStrategyType);
            
            // Determine the update structure based on bidding strategy and target type
            $updateUrl = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/campaigns:mutate';
            $campaignObject = [
                'resourceName' => $resourceName
            ];
            
            $updateMask = '';
            $valueMicros = null;
            
            if ($type === 'cpa') {
                // Convert to micro amount (multiply by 1,000,000)
                $valueMicros = (float)$value * 1000000;
                
                // Check bidding strategy to determine where to set CPA
                if ($biddingStrategyType === 'MAXIMIZE_CONVERSIONS') {
                    $campaignObject['maximizeConversions'] = [
                        'targetCpaMicros' => $valueMicros
                    ];
                    $updateMask = 'maximize_conversions.target_cpa_micros';
                } elseif ($biddingStrategyType === 'TARGET_CPA') {
                    $campaignObject['targetCpa'] = [
                        'targetCpaMicros' => $valueMicros
                    ];
                    $updateMask = 'target_cpa.target_cpa_micros';
                } else {
                    throw new \Exception('Chiến dịch không sử dụng chiến lược đặt giá thầu TARGET_CPA hoặc MAXIMIZE_CONVERSIONS');
                }
            } elseif ($type === 'roas') {
                // ROAS is a ratio value, not in micro amount
                $roas = (float)$value;
                
                // Check bidding strategy to determine where to set ROAS
                if ($biddingStrategyType === 'MAXIMIZE_CONVERSION_VALUE') {
                    $campaignObject['maximizeConversionValue'] = [
                        'targetRoas' => $roas
                    ];
                    $updateMask = 'maximize_conversion_value.target_roas';
                } elseif ($biddingStrategyType === 'TARGET_ROAS') {
                    $campaignObject['targetRoas'] = [
                        'targetRoas' => $roas
                    ];
                    $updateMask = 'target_roas.target_roas';
                } else {
                    throw new \Exception('Chiến dịch không sử dụng chiến lược đặt giá thầu TARGET_ROAS hoặc MAXIMIZE_CONVERSION_VALUE');
                }
            } else {
                throw new \Exception('Loại mục tiêu không hợp lệ. Chỉ hỗ trợ "cpa" hoặc "roas"');
            }
            
            // Create update request
            $updateData = [
                'operations' => [
                    [
                        'update' => $campaignObject,
                        'updateMask' => $updateMask
                    ]
                ],
                'validateOnly' => false
            ];
            
            // Log request data for debugging
            log_message('debug', 'Campaign target update URL: ' . $updateUrl);
            log_message('debug', 'Campaign target update request: ' . json_encode($updateData));
            
            $updateResponse = $this->makeCurlRequest($updateUrl, 'POST', $accessToken, json_encode($updateData), $mccId);
            
            // Log response for debugging
            log_message('debug', 'Campaign target update response: ' . json_encode($updateResponse));
            
            // Check response
            if (isset($updateResponse['error'])) {
                throw new \Exception('API Error: ' . json_encode($updateResponse['error']));
            }
            
            // Check mutate results
            if (!isset($updateResponse['results']) || empty($updateResponse['results'])) {
                throw new \Exception('Không nhận được kết quả từ API khi cập nhật mục tiêu');
            }
            
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Lỗi khi cập nhật mục tiêu chiến dịch: ' . $e->getMessage());
            throw $e;
        }
    }
}