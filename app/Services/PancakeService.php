<?php

namespace App\Services;

use Exception;
class PancakeService
{
    protected $apiVersion = 'v1';
    protected $apiEnpoint = 'https://pos.pancake.vn/api/';

    /**
     * Lấy dữ liệu đơn hàng từ POS Pancake API
     * 
     * @param string $shopId ID của shop trên Pancake
     * @param string $apiKey API key để xác thực với Pancake API
     * @param string $startDateTime Thời gian bắt đầu lấy dữ liệu (Y-m-d H:i:s)
     * @param string $endDateTime Thời gian kết thúc lấy dữ liệu (Y-m-d H:i:s)
     * @return array Mảng dữ liệu đơn hàng
     */
    public function getOrders($shopId, $apiKey, $startDateTime, $endDateTime)
    {
        if (empty($shopId) || empty($apiKey)) {
            log_message('error', 'Pancake POS API: Missing shop ID or API key');
            return [];
        }

        // Chuyển đổi định dạng ngày tháng thành Unix timestamp theo yêu cầu của API
        $startTimestamp = strtotime($startDateTime);
        $endTimestamp = strtotime($endDateTime);
        
        if ($startTimestamp === false || $endTimestamp === false) {
            log_message('error', 'Pancake POS API: Invalid date format - Start: ' . $startDateTime . ', End: ' . $endDateTime);
            return [];
        }

        // Build API URL với Unix timestamp
        $url = $this->apiEnpoint . $this->apiVersion . "/shops/{$shopId}/orders?api_key={$apiKey}&option_sort=inserted_at_desc&startDateTime={$startTimestamp}&endDateTime={$endTimestamp}";

        try {
            // Initialize cURL session
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            
            // Execute cURL request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Check for errors
            if ($httpCode != 200) {
                $error = curl_error($ch);
                log_message('error', "Pancake POS API Error: HTTP Code {$httpCode}, Error: {$error}");
                curl_close($ch);
                return [];
            }
            
            curl_close($ch);
            
            // Parse JSON response
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Pancake POS API: Invalid JSON response - ' . json_last_error_msg());
                return [];
            }
            
            // Check if response contains orders
            if (!isset($data['data']) || !is_array($data['data'])) {
                log_message('info', 'Pancake POS API: No orders found in response');
                return [];
            }
            
            return $data['data'];
        } catch (\Exception $e) {
            log_message('error', 'Pancake POS API Exception: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Xử lý dữ liệu chuyển đổi thực tế từ Pancake POS
     * 
     * @param array $campaigns Dữ liệu chiến dịch
     * @param array $settings Cài đặt tài khoản
     * @param string $startDate Ngày bắt đầu (Y-m-d)
     * @param string $endDate Ngày kết thúc (Y-m-d)
     * @return array Dữ liệu chiến dịch đã được cập nhật với thông tin chuyển đổi thực tế
     */
    public function processRealConversions($campaigns, $settings, $startDate, $endDate)
    {
        // Kiểm tra input
        if (empty($campaigns) || !is_array($campaigns)) {
            log_message('error', 'Invalid campaigns data: ' . json_encode($campaigns));
            return [];
        }

        // Kiểm tra cài đặt Pancake POS
        if (empty($settings['pancake_shop_id']) || empty($settings['pancake_api_key'])) {
            log_message('error', 'Pancake POS: Missing shop ID or API key in settings');
            return $campaigns;
        }

        // Thêm thời gian vào ngày để lấy dữ liệu cả ngày
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        // Lấy danh sách đơn hàng từ Pancake POS API
        $orders = $this->getOrders(
            $settings['pancake_shop_id'],
            $settings['pancake_api_key'],
            $startDateTime,
            $endDateTime
        );

        if (empty($orders)) {
            log_message('info', 'Pancake POS: No orders found for the specified date range');
            return $campaigns;
        }

        // Khởi tạo mảng để lưu trữ dữ liệu cho mỗi chiến dịch
        $campaignData = [];
        $processedOrderIds = []; // Để đảm bảo mỗi đơn hàng chỉ được tính một lần

        // Lọc sản phẩm theo product_id nếu được cấu hình
        $productId = $settings['pancake_product_id'] ?? null;

        // Xử lý từng đơn hàng
        $count = 0;
        foreach ($orders as $order) {
            // Kiểm tra xem đơn hàng đã được xử lý chưa
            $orderId = $order['id'] ?? '';
            if (empty($orderId) || in_array($orderId, $processedOrderIds)) {
                continue;
            }

            // Lấy campaign ID từ trường p_utm_campaign
            $campaignId = $order['p_utm_campaign'] ?? '';
            if (empty($campaignId)) {
                continue;
            }

            // Kiểm tra sản phẩm nếu có cấu hình product_id
            if (!empty($productId) && !$this->orderContainsProduct($order, $productId)) {
                continue;
            }

            // Lấy thời gian tạo đơn hàng và kiểm tra xem có nằm trong khoảng thời gian cần lấy không
            $insertedAt = $order['inserted_at'] ?? null;
            if (!empty($insertedAt)) {
                // Kiểm tra nếu inserted_at là timestamp (số nguyên) thì chuyển đổi thành chuỗi datetime
                if (is_numeric($insertedAt)) {
                    $conversionTime = (int)$insertedAt;
                } else {
                    $conversionTime = strtotime($insertedAt);
                }
                
                if ($conversionTime) {
                    $conversionDate = date('Y-m-d', $conversionTime);
                    
                    // Chỉ xử lý đơn hàng trong khoảng thời gian được chọn
                    if ($conversionDate < $startDate || $conversionDate > $endDate) {
                        continue;
                    }
                }
            }

            // Lấy giá trị đơn hàng và số điện thoại
            $orderValue = $order['total_price'] ?? 0;
            $phone = $order['bill_phone_number'] ?? '';
            
            // Bỏ qua nếu không có số điện thoại
            if (empty($phone)) {
                continue;
            }

            // Khởi tạo dữ liệu chiến dịch nếu chưa có
            if (!isset($campaignData[$campaignId])) {
                $campaignData[$campaignId] = [
                    'unique_phones' => [],
                    'total_value' => 0
                ];
            }

            // Nếu số điện thoại chưa xuất hiện trong chiến dịch này
            if (!isset($campaignData[$campaignId]['unique_phones'][$phone])) {
                $campaignData[$campaignId]['unique_phones'][$phone] = true;
                $campaignData[$campaignId]['total_value'] += $orderValue;
            }

            // Đánh dấu đơn hàng đã được xử lý
            $processedOrderIds[] = $orderId;
        }

        // Tạo mảng mới để lưu kết quả
        $processedCampaigns = [];

        // Cập nhật dữ liệu chiến dịch với thông tin chuyển đổi thực tế
        foreach ($campaigns as $campaign) {
            // Đảm bảo campaign là array và có campaign_id
            if (!is_array($campaign) || !isset($campaign['campaign_id'])) {
                log_message('error', 'Invalid campaign data: ' . json_encode($campaign));
                continue;
            }
            
            // Tạo bản sao của campaign để tránh tham chiếu
            $processedCampaign = $campaign;
            $campaignId = $campaign['campaign_id'];
            
            // Nếu có dữ liệu chuyển đổi cho chiến dịch này
            if (isset($campaignData[$campaignId])) {
                // Số lượng chuyển đổi là số lượng số điện thoại duy nhất
                $processedCampaign['real_conversions'] = count($campaignData[$campaignId]['unique_phones']);
                $processedCampaign['real_conversion_value'] = $campaignData[$campaignId]['total_value'];
                $processedCampaign['real_conversion_rate'] = isset($campaign['clicks']) && $campaign['clicks'] > 0 
                    ? ($processedCampaign['real_conversions'] / $campaign['clicks']) 
                    : 0;
                $processedCampaign['real_cpa'] = $processedCampaign['real_conversions'] > 0 
                    ? ($campaign['cost'] ?? 0) / $processedCampaign['real_conversions']
                    : 0;
                // Tính ROAS thực tế
                if ($processedCampaign['real_conversion_value'] > 0 && isset($campaign['cost']) && $campaign['cost'] > 0) {
                    $processedCampaign['real_roas'] = $processedCampaign['real_conversion_value'] / $campaign['cost'];
                }
            } else {
                // Nếu không có dữ liệu chuyển đổi, đặt giá trị mặc định
                $processedCampaign['real_conversions'] = 0;
                $processedCampaign['real_conversion_value'] = 0;
                $processedCampaign['real_conversion_rate'] = 0;
                $processedCampaign['real_cpa'] = 0;
                $processedCampaign['real_roas'] = 0;
            }
            
            $processedCampaigns[] = $processedCampaign;
        }

        return $processedCampaigns;
    }

    /**
     * Kiểm tra xem đơn hàng có chứa sản phẩm cụ thể không
     * 
     * @param array $order Thông tin đơn hàng
     * @param string $productId ID sản phẩm cần kiểm tra
     * @return bool True nếu đơn hàng chứa sản phẩm, ngược lại là False
     */
    private function orderContainsProduct($order, $productId)
    {
        // Kiểm tra xem đơn hàng có chứa thông tin sản phẩm không
        if (!isset($order['items']) || !is_array($order['items'])) {
            return false;
        }

        // Kiểm tra từng sản phẩm trong đơn hàng
        foreach ($order['items'] as $item) {
            if (isset($item['product_display_id']) && $item['product_display_id'] == $productId) {
                return true;
            }
            if (isset($item['variation_info'])) {
                if (isset($item['variation_info']['product_display_id']) && $item['variation_info']['product_display_id'] == $productId) {
                    return true;
                }
            }
        }

        return false;
    }
}