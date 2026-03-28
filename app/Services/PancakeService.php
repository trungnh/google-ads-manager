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
     * @param string $sku SKU của sản phẩm
     * @param string $startDateTime Thời gian bắt đầu lấy dữ liệu (Y-m-d H:i:s)
     * @param string $endDateTime Thời gian kết thúc lấy dữ liệu (Y-m-d H:i:s)
     * @return array Mảng dữ liệu đơn hàng
     */
    public function getOrders($shopId, $apiKey, $sku, $startDateTime, $endDateTime)
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

        $pancakeProductId = $this->getPancakeProductId($shopId, $apiKey, $sku);
        // Build API URL với Unix timestamp
        $url = $this->apiEnpoint . $this->apiVersion . "/shops/{$shopId}/orders?api_key={$apiKey}&option_sort=inserted_at_desc&startDateTime={$startTimestamp}&endDateTime={$endTimestamp}&page=1&page_size=1000";
        if ($pancakeProductId) {
            $url .= "&product_id[]={$pancakeProductId}";
        }

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
            // Nếu không có dữ liệu chuyển đổi, đặt giá trị mặc định
            foreach ($campaigns as &$tmpCampaign) {
                $tmpCampaign['real_conversions'] = 0;
                $tmpCampaign['real_conversion_value'] = 0;
                $tmpCampaign['real_conversion_rate'] = 0;
                $tmpCampaign['real_cpa'] = 0;
                $tmpCampaign['real_roas'] = 0;
                // Thêm các trường mới
                $tmpCampaign['real_conversions_total'] = 0;
                $tmpCampaign['real_conversions_pending'] = 0;
                $tmpCampaign['real_conversions_success'] = 0;
                $tmpCampaign['real_conversion_value_total'] = 0;
                $tmpCampaign['real_conversion_value_success'] = 0;
                $tmpCampaign['real_cpa_total'] = 0;
                $tmpCampaign['real_cpa_success'] = 0;
                $tmpCampaign['real_roas_total'] = 0;
                $tmpCampaign['real_roas_success'] = 0;
            }

            return $campaigns;
        }

        // Kiểm tra cài đặt quy đổi USD
        $useUsd = isset($settings['pancake_use_usd']) && $settings['pancake_use_usd'];
        $usdRate = isset($settings['pancake_usd_rate']) && is_numeric($settings['pancake_usd_rate']) ? (float) $settings['pancake_usd_rate'] : 27000;

        // Thêm thời gian vào ngày để lấy dữ liệu cả ngày
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        // Lọc sản phẩm theo product_id nếu được cấu hình
        $productId = $settings['pancake_product_id'] ?? null;

        // Lấy danh sách đơn hàng từ Pancake POS API
        $orders = $this->getOrders(
            $settings['pancake_shop_id'],
            $settings['pancake_api_key'],
            $productId,
            $startDateTime,
            $endDateTime
        );

        if (empty($orders)) {
            log_message('info', 'Pancake POS: No orders found for the specified date range');
            // Nếu không có dữ liệu chuyển đổi, đặt giá trị mặc định
            foreach ($campaigns as &$tmpCampaign) {
                $tmpCampaign['real_conversions'] = 0;
                $tmpCampaign['real_conversion_value'] = 0;
                $tmpCampaign['real_conversion_rate'] = 0;
                $tmpCampaign['real_cpa'] = 0;
                $tmpCampaign['real_roas'] = 0;
                // Thêm các trường mới
                $tmpCampaign['real_conversions_total'] = 0;
                $tmpCampaign['real_conversions_pending'] = 0;
                $tmpCampaign['real_conversions_success'] = 0;
                $tmpCampaign['real_conversion_value_total'] = 0;
                $tmpCampaign['real_conversion_value_success'] = 0;
                $tmpCampaign['real_cpa_total'] = 0;
                $tmpCampaign['real_cpa_success'] = 0;
                $tmpCampaign['real_roas_total'] = 0;
                $tmpCampaign['real_roas_success'] = 0;
            }

            return $campaigns;
        }

        // Khởi tạo mảng để lưu trữ dữ liệu cho mỗi chiến dịch
        $campaignData = [];
        // Khởi tạo mảng để lưu trữ dữ liệu đơn hàng offline (không có p_utm_campaign)
        $offlineOrderData = [
            'unique_phones' => [],
            'total_value' => 0,
            'pending_phones' => [],
            'pending_value' => 0,
            'success_phones' => [],
            'success_value' => 0
        ];
        $processedOrderIds = []; // Để đảm bảo mỗi đơn hàng chỉ được tính một lần

        // Định nghĩa các trạng thái đơn hàng
        $canceledStatuses = [6, 7]; // Đã hủy, Đã xóa
        $pendingStatuses = [0, 10, 21]; // Mới, Webcake, Storecake

        // Xử lý từng đơn hàng
        foreach ($orders as $order) {
            // Kiểm tra xem đơn hàng đã được xử lý chưa
            $orderId = $order['id'] ?? '';
            if (empty($orderId) || in_array($orderId, $processedOrderIds)) {
                continue;
            }

            if (!empty($order['ads_source']) && strtolower($order['ads_source']) == 'facebook') {
                continue;
            }
            if (!empty($order['p_utm_source']) && strtolower($order['p_utm_source']) == 'facebook') {
                continue;
            }

            // Lấy campaign ID từ trường p_utm_campaign
            $campaignId = $order['p_utm_campaign'] ?? '';

            // Kiểm tra sản phẩm nếu có cấu hình product_id
            // if (!empty($productId) && !$this->orderContainsProduct($order, $productId)) {
            //     continue;
            // }

            // Lấy trạng thái đơn hàng
            $orderStatus = isset($order['status']) ? (int) $order['status'] : null;

            // Bỏ qua đơn hàng đã hủy hoặc đã xóa
            if (in_array($orderStatus, $canceledStatuses)) {
                continue;
            }

            // Kiểm tra thẻ đơn hàng nếu có cấu hình exclude_tags
            $excludeTags = isset($settings['pancake_exclude_tags']) ? $settings['pancake_exclude_tags'] : '';
            $hasExcludedTag = !empty($excludeTags) && $this->orderContainsExcludedTag($order, $excludeTags);
            if ($hasExcludedTag) {
                continue;
            }

            // Lấy thời gian tạo đơn hàng và kiểm tra xem có nằm trong khoảng thời gian cần lấy không
            // $insertedAt = $order['inserted_at'] ?? null;
            // if (!empty($insertedAt)) {
            //     // Kiểm tra nếu inserted_at là timestamp (số nguyên) thì chuyển đổi thành chuỗi datetime
            //     if (is_numeric($insertedAt)) {
            //         $conversionTime = (int)$insertedAt;
            //     } else {
            //         $conversionTime = strtotime($insertedAt);
            //     }

            //     if ($conversionTime) {
            //         $conversionDate = date('Y-m-d', $conversionTime);

            //         // Chỉ xử lý đơn hàng trong khoảng thời gian được chọn
            //         if ($conversionDate < $startDate || $conversionDate > $endDate) {
            //             continue;
            //         }
            //     }
            // }

            // Lấy giá trị đơn hàng và số điện thoại
            $orderValue = $order['total_price'] ?? 0;
            $phone = $order['bill_phone_number'] ?? '';

            // Bỏ qua nếu không có số điện thoại
            if (empty($phone)) {
                continue;
            }

            // Xác định loại đơn hàng (đang chốt hay thành công)
            //$isPendingOrder = in_array($orderStatus, $pendingStatuses) && !$hasExcludedTag;
            $isPendingOrder = in_array($orderStatus, $pendingStatuses);

            // Xử lý đơn hàng dựa vào campaignId
            if (empty($campaignId)) {
                // Đơn hàng offline (không có p_utm_campaign)
                // Nếu số điện thoại chưa xuất hiện trong đơn hàng offline
                if (!isset($offlineOrderData['unique_phones'][$phone])) {
                    // Thêm vào tổng đơn hàng offline
                    $offlineOrderData['unique_phones'][$phone] = true;
                    $offlineOrderData['total_value'] += $orderValue;

                    // Phân loại đơn hàng offline
                    if ($isPendingOrder) {
                        $offlineOrderData['pending_phones'][$phone] = true;
                        $offlineOrderData['pending_value'] += $orderValue;
                    } else {
                        $offlineOrderData['success_phones'][$phone] = true;
                        $offlineOrderData['success_value'] += $orderValue;
                    }
                }
            } else {
                // Đơn hàng online (có p_utm_campaign)
                // Khởi tạo dữ liệu chiến dịch nếu chưa có
                if (!isset($campaignData[$campaignId])) {
                    $campaignData[$campaignId] = [
                        'unique_phones' => [],
                        'total_value' => 0,
                        'pending_phones' => [],
                        'pending_value' => 0,
                        'success_phones' => [],
                        'success_value' => 0
                    ];
                }

                // Nếu số điện thoại chưa xuất hiện trong chiến dịch này
                if (!isset($campaignData[$campaignId]['unique_phones'][$phone])) {
                    // Thêm vào tổng đơn hàng
                    $campaignData[$campaignId]['unique_phones'][$phone] = true;
                    $campaignData[$campaignId]['total_value'] += $orderValue;

                    // Phân loại đơn hàng
                    if ($isPendingOrder) {
                        $campaignData[$campaignId]['pending_phones'][$phone] = true;
                        $campaignData[$campaignId]['pending_value'] += $orderValue;
                    } else {
                        $campaignData[$campaignId]['success_phones'][$phone] = true;
                        $campaignData[$campaignId]['success_value'] += $orderValue;
                    }
                }
            }

            // Đánh dấu đơn hàng đã được xử lý
            $processedOrderIds[] = $orderId;
        }

        // Tạo mảng mới để lưu kết quả
        $processedCampaigns = [];
        $processedCampaignIds = [];

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
                // Tính toán giá trị chuyển đổi, áp dụng quy đổi USD nếu được bật
                $totalValue = $campaignData[$campaignId]['total_value'];
                $pendingValue = $campaignData[$campaignId]['pending_value'];
                $successValue = $campaignData[$campaignId]['success_value'];

                if ($useUsd && $usdRate > 0) {
                    // Quy đổi từ VND sang USD theo tỷ giá
                    $totalValue = $totalValue / $usdRate;
                    $pendingValue = $pendingValue / $usdRate;
                    $successValue = $successValue / $usdRate;
                    log_message('info', 'Converting value from VND to USD with rate: ' . $usdRate);
                }

                // Số lượng chuyển đổi theo từng loại
                $totalConversions = count($campaignData[$campaignId]['unique_phones']);
                $pendingConversions = count($campaignData[$campaignId]['pending_phones']);
                $successConversions = count($campaignData[$campaignId]['success_phones']);

                // Gán giá trị cho các trường mới
                $processedCampaign['real_conversions_total'] = $totalConversions;
                $processedCampaign['real_conversions_pending'] = $pendingConversions;
                $processedCampaign['real_conversions_success'] = $successConversions;
                $processedCampaign['real_conversion_value_total'] = $totalValue;
                $processedCampaign['real_conversion_value_success'] = $successValue;

                // Tính CPA
                $processedCampaign['real_cpa_total'] = $totalConversions > 0
                    ? ($campaign['cost'] ?? 0) / $totalConversions
                    : 0;
                $processedCampaign['real_cpa_success'] = $successConversions > 0
                    ? ($campaign['cost'] ?? 0) / $successConversions
                    : 0;

                // Tính ROAS
                $processedCampaign['real_roas_total'] = isset($campaign['cost']) && $campaign['cost'] > 0
                    ? $totalValue / $campaign['cost']
                    : 0;
                $processedCampaign['real_roas_success'] = isset($campaign['cost']) && $campaign['cost'] > 0
                    ? $successValue / $campaign['cost']
                    : 0;

                // Giữ lại các trường cũ để tương thích ngược
                $processedCampaign['real_conversions'] = $totalConversions;
                $processedCampaign['real_conversion_value'] = $totalValue;
                $processedCampaign['real_conversion_rate'] = isset($campaign['clicks']) && $campaign['clicks'] > 0
                    ? ($totalConversions / $campaign['clicks'])
                    : 0;
                $processedCampaign['real_cpa'] = $totalConversions > 0
                    ? ($campaign['cost'] ?? 0) / $totalConversions
                    : 0;
                $processedCampaign['real_roas'] = isset($campaign['cost']) && $campaign['cost'] > 0
                    ? $totalValue / $campaign['cost']
                    : 0;
            } else {
                // Nếu không có dữ liệu chuyển đổi, đặt giá trị mặc định
                $processedCampaign['real_conversions'] = 0;
                $processedCampaign['real_conversion_value'] = 0;
                $processedCampaign['real_conversion_rate'] = 0;
                $processedCampaign['real_cpa'] = 0;
                $processedCampaign['real_roas'] = 0;
                // Thêm các trường mới
                $processedCampaign['real_conversions_total'] = 0;
                $processedCampaign['real_conversions_pending'] = 0;
                $processedCampaign['real_conversions_success'] = 0;
                $processedCampaign['real_conversion_value_total'] = 0;
                $processedCampaign['real_conversion_value_success'] = 0;
                $processedCampaign['real_cpa_total'] = 0;
                $processedCampaign['real_cpa_success'] = 0;
                $processedCampaign['real_roas_total'] = 0;
                $processedCampaign['real_roas_success'] = 0;
            }

            // Lưu ID chiến dịch vào 1 mảng
            if (!in_array($campaignId, $processedCampaignIds)) {
                $processedCampaignIds[] = $campaignId;
            }

            $processedCampaigns[] = $processedCampaign;
        }

        // Xử lý đơn hàng offline và thêm vào danh sách chiến dịch
        $offlineCampaign = [];

        if (!empty($offlineOrderData['unique_phones'])) {
            // Tính toán giá trị chuyển đổi cho đơn hàng offline, áp dụng quy đổi USD nếu được bật
            $totalValue = $offlineOrderData['total_value'];
            $pendingValue = $offlineOrderData['pending_value'];
            $successValue = $offlineOrderData['success_value'];

            if ($useUsd && $usdRate > 0) {
                // Quy đổi từ VND sang USD theo tỷ giá
                $totalValue = $totalValue / $usdRate;
                $pendingValue = $pendingValue / $usdRate;
                $successValue = $successValue / $usdRate;
            }

            // Số lượng chuyển đổi theo từng loại
            $totalConversions = count($offlineOrderData['unique_phones']);
            $pendingConversions = count($offlineOrderData['pending_phones']);
            $successConversions = count($offlineOrderData['success_phones']);

            // Tạo chiến dịch mới cho đơn hàng offline
            $this->processOfflineCampaign(
                $offlineCampaign,
                $totalConversions,
                $pendingConversions,
                $successConversions,
                $totalValue,
                $successValue
            );
        }

        // Xử lý những đơn hàng có utm campaign ID nhưng không đến từ Google Ads
        foreach ($campaignData as $campID => $data) {
            if (in_array($campID, $processedCampaignIds)) {
                continue;
            }

            // Tính toán giá trị chuyển đổi, áp dụng quy đổi USD nếu được bật
            $totalValue = $data['total_value'];
            $pendingValue = $data['pending_value'];
            $successValue = $data['success_value'];

            if ($useUsd && $usdRate > 0) {
                // Quy đổi từ VND sang USD theo tỷ giá
                $totalValue = $totalValue / $usdRate;
                $pendingValue = $pendingValue / $usdRate;
                $successValue = $successValue / $usdRate;
                log_message('info', 'Converting value from VND to USD with rate: ' . $usdRate);
            }

            // Số lượng chuyển đổi theo từng loại
            $totalConversions = count($data['unique_phones']);
            $pendingConversions = count($data['pending_phones']);
            $successConversions = count($data['success_phones']);

            // Gán giá trị cho offline Campaign
            $this->processOfflineCampaign(
                $offlineCampaign,
                $totalConversions,
                $pendingConversions,
                $successConversions,
                $totalValue,
                $successValue
            );
        }

        // Thêm chiến dịch offline vào danh sách
        if (!empty($offlineCampaign)) {
            $processedCampaigns[] = $offlineCampaign;
        }

        return $processedCampaigns;
    }

    protected function processOfflineCampaign(
        &$offlineCampaign,
        $totalConversions,
        $pendingConversions,
        $successConversions,
        $totalValue,
        $successValue
    ) {
        if (empty($offlineCampaign)) {
            // Tạo chiến dịch mới cho đơn hàng offline
            $offlineCampaign = [
                'campaign_id' => 'other',
                'name' => 'Đơn hàng KHÁC',
                'status' => 'ENABLED',
                'budget' => 0,
                'cost' => 0,
                'clicks' => 0,
                'impressions' => 0,
                'ctr' => 0,
                'average_cpc' => 0,
                'conversions' => 0,
                'conversion_rate' => 0,
                'conversion_value' => 0,
                'cost_per_conversion' => 0,
                'roas' => 0,
                // Thêm các trường chuyển đổi thực tế
                'real_conversions_total' => $totalConversions,
                'real_conversions_pending' => $pendingConversions,
                'real_conversions_success' => $successConversions,
                'real_conversion_value_total' => $totalValue,
                'real_conversion_value_success' => $successValue,
                'real_cpa_total' => 0, // Không có chi phí nên CPA = 0
                'real_cpa_success' => 0, // Không có chi phí nên CPA = 0
                'real_roas_total' => 0, // Không có chi phí nên ROAS = 0
                'real_roas_success' => 0, // Không có chi phí nên ROAS = 0
                // Giữ lại các trường cũ để tương thích ngược
                'real_conversions' => $totalConversions,
                'real_conversion_value' => $totalValue,
                'real_conversion_rate' => 0, // Không có clicks nên tỷ lệ = 0
                'real_cpa' => 0, // Không có chi phí nên CPA = 0
                'real_roas' => 0 // Không có chi phí nên ROAS = 0
            ];
        } else {
            $offlineCampaign['real_conversions_total'] += $totalConversions;
            $offlineCampaign['real_conversions_pending'] += $pendingConversions;
            $offlineCampaign['real_conversions_success'] += $successConversions;
            $offlineCampaign['real_conversion_value_total'] += $totalValue;
            $offlineCampaign['real_conversion_value_success'] += $successValue;
            $offlineCampaign['real_conversions'] += $totalConversions;
            $offlineCampaign['real_conversion_value'] += $totalValue;
        }

    }

    /**
     * Parse and compute aggregate metrics for matched orders based on CRM logic
     */
    public function calculateRevenueFromOrders($orders, $productCode)
    {
        // Chỉ tính toán các đơn ở trạng thái: 0, 1, 2, 8, 9, 12, 13
        $validStatuses = [0, 1, 2, 8, 9, 12, 13];
        $totalOrders = 0;
        $totalGoodsCost = 0;
        $totalShipCost = 0;
        $totalRevenue = 0;

        if (empty($orders)) {
            return [
                'orders' => 0,
                'goods_cost' => 0,
                'ship_cost' => 0,
                'revenue' => 0
            ];
        }

        foreach ($orders as $order) {
            $status = isset($order['status']) ? (int) $order['status'] : null;

            if (!in_array($status, $validStatuses)) {
                continue;
            }

            // Lọc doanh thu của các đơn hàng chứa mã sản phẩm này
            if (!empty($productCode) && !$this->orderContainsProduct($order, $productCode)) {
                continue;
            }

            $totalOrders++;

            // Doanh thu: tổng money_to_collect của đơn hàng
            $totalRevenue += isset($order['money_to_collect']) ? (float) $order['money_to_collect'] : 0;

            // Vận chuyển: Tổng partner_fee của đơn hàng
            $totalShipCost += isset($order['partner_fee']) ? (float) $order['partner_fee'] : 0;

            // Tiền hàng: tổng (item.quantity * item.variation_info.last_imported_price)
            if (isset($order['items']) && is_array($order['items'])) {
                foreach ($order['items'] as $item) {
                    $isMatch = empty($productCode);
                    if (!$isMatch) {
                        // Check matching items corresponding to the product
                        if (isset($item['product_display_id']) && $item['product_display_id'] == $productCode) {
                            $isMatch = true;
                        } elseif (isset($item['variation_info']['product_display_id']) && $item['variation_info']['product_display_id'] == $productCode) {
                            $isMatch = true;
                        }
                    }

                    if ($isMatch) {
                        $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 1;

                        $importPrice = 0;
                        if (isset($item['variation_info']['last_imported_price'])) {
                            $importPrice = (float) $item['variation_info']['last_imported_price'];
                        }
                        $totalGoodsCost += ($quantity * $importPrice);
                    }
                }
            }
        }

        return [
            'orders' => $totalOrders,
            'goods_cost' => $totalGoodsCost,
            'ship_cost' => $totalShipCost,
            'revenue' => $totalRevenue
        ];
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
        $productId = trim($productId);
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

    /**
     * Kiểm tra xem đơn hàng có chứa thẻ cần loại trừ không
     * 
     * @param array $order Thông tin đơn hàng
     * @param string $excludeTagsString Chuỗi chứa các thẻ cần loại trừ, phân cách bằng dấu phẩy
     * @return bool True nếu đơn hàng chứa ít nhất một thẻ cần loại trừ, ngược lại là False
     */
    private function orderContainsExcludedTag($order, $excludeTagsString)
    {
        // Nếu không có thẻ cần loại trừ, trả về false
        if (empty($excludeTagsString)) {
            return false;
        }

        // Chuyển chuỗi thẻ cần loại trừ thành mảng và loại bỏ khoảng trắng
        $excludeTags = array_map('trim', explode(',', $excludeTagsString));

        // Kiểm tra xem đơn hàng có chứa thông tin khách hàng không
        if (!isset($order['tags']) || !is_array($order['tags'])) {
            return false;
        }

        // Kiểm tra xem đơn hàng có thẻ không (một số API có thể trả về thẻ ở cấp đơn hàng)
        if (isset($order['tags'])) {
            $orderTags = $order['tags'];

            foreach ($orderTags as $tag) {
                if (in_array($tag['id'], $excludeTags)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Lấy danh sách thẻ từ Pancake POS API
     * 
     * @param string $shopId ID của shop trên Pancake
     * @param string $apiKey API key để xác thực với Pancake API
     * @return array|bool Mảng danh sách thẻ hoặc false nếu có lỗi
     */
    public function getTags($shopId, $apiKey)
    {
        if (empty($shopId) || empty($apiKey)) {
            log_message('error', 'Pancake POS API: Missing shop ID or API key');
            return false;
        }

        // Build API URL để lấy danh sách thẻ
        $url = $this->apiEnpoint . $this->apiVersion . "/shops/{$shopId}/orders/tags?api_key={$apiKey}";

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

            // Check for cURL errors
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                log_message('error', 'Pancake POS API cURL Error: ' . $error);
                return false;
            }

            curl_close($ch);

            // Check HTTP response code
            if ($httpCode != 200) {
                log_message('error', 'Pancake POS API HTTP Error: ' . $httpCode . ' - Response: ' . $response);
                return false;
            }

            // Parse JSON response
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Pancake POS API JSON Error: ' . json_last_error_msg() . ' - Response: ' . $response);
                return false;
            }

            // Kiểm tra cấu trúc dữ liệu trả về
            if (!isset($data['data']) || !is_array($data['data'])) {
                log_message('error', 'Pancake POS API Invalid Response Structure: ' . $response);
                return false;
            }

            // Trả về danh sách thẻ
            return $data['data'];

        } catch (Exception $e) {
            log_message('error', 'Pancake POS API Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy Product ID từ Pancake POS API
     * 
     * @param string $shopId ID của shop trên Pancake
     * @param string $apiKey API key để xác thực với Pancake API
     * @param string $sku SKU của sản phẩm
     * @return array|bool Mảng danh sách thẻ hoặc false nếu có lỗi
     */
    public function getPancakeProductId($shopId, $apiKey, $sku)
    {
        if (empty($shopId) || empty($apiKey)) {
            log_message('error', 'Pancake POS API: Missing shop ID or API key');
            return false;
        }

        // Build API URL để lấy danh sách thẻ
        $url = $this->apiEnpoint . $this->apiVersion . "/shops/{$shopId}/products/{$sku}?api_key={$apiKey}";

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

            // Check for cURL errors
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                log_message('error', 'Pancake POS API cURL Error: ' . $error);
                return false;
            }

            curl_close($ch);

            // Check HTTP response code
            if ($httpCode != 200) {
                log_message('error', 'Pancake POS API HTTP Error: ' . $httpCode . ' - Response: ' . $response);
                return false;
            }

            // Parse JSON response
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Pancake POS API JSON Error: ' . json_last_error_msg() . ' - Response: ' . $response);
                return false;
            }

            // Kiểm tra cấu trúc dữ liệu trả về
            if (!isset($data['data']) || !is_array($data['data'])) {
                log_message('error', 'Pancake POS API Invalid Response Structure: ' . $response);
                return false;
            }

            // Trả về danh sách thẻ
            return $data['data']['id'];

        } catch (Exception $e) {
            log_message('error', 'Pancake POS API Exception: ' . $e->getMessage());
            return false;
        }
    }
}