<?php

namespace App\Services;

class GoogleSheetService
{
    public function getConversionsFromCsv($csvUrl, $startDate, $endDate, $settings)
    {
        try {
            // Đọc nội dung CSV từ URL
            $csvContent = file_get_contents($csvUrl);
            if ($csvContent === false) {
                throw new \Exception("Không thể đọc dữ liệu từ Google Sheet CSV");
            }

            // Chuyển đổi CSV thành mảng
            $rows = array_map('str_getcsv', explode("\n", $csvContent));
            
            // Lấy index của các cột từ settings
            $dateColIndex = ord(strtoupper($settings['gsheet_date_col'])) - ord('A');
            $phoneColIndex = ord(strtoupper($settings['gsheet_phone_col'])) - ord('A');
            $valueColIndex = ord(strtoupper($settings['gsheet_value_col'])) - ord('A');
            $campaignColIndex = ord(strtoupper($settings['gsheet_campaign_col'])) - ord('A');

            // Validate column indexes
            $maxIndex = max($dateColIndex, $phoneColIndex, $valueColIndex, $campaignColIndex);
            
            $campaignData = [];
            foreach ($rows as $row) {
                if (count($row) <= $maxIndex) continue; // Bỏ qua các dòng không đủ cột

                // Lấy thời gian từ cột được cấu hình và chuyển đổi thành ngày
                $conversionTime = strtotime($row[$dateColIndex]);
                $conversionDate = date('Y-m-d', $conversionTime);

                // Chỉ xử lý dữ liệu trong khoảng thời gian được chọn
                if ($conversionDate >= $startDate && $conversionDate <= $endDate) {
                    $phone = trim($row[$phoneColIndex]); // Số điện thoại
                    $value = floatval(str_replace(['₫', ',', ' '], '', $row[$valueColIndex])); // Giá trị
                    $campaignId = trim($row[$campaignColIndex]); // Campaign ID

                    if (!empty($campaignId)) {
                        if (!isset($campaignData[$campaignId])) {
                            $campaignData[$campaignId] = [
                                'unique_phones' => [],
                                'total_value' => 0
                            ];
                        }

                        // Nếu số điện thoại chưa xuất hiện trong chiến dịch này
                        if (!isset($campaignData[$campaignId]['unique_phones'][$phone])) {
                            $campaignData[$campaignId]['unique_phones'][$phone] = true;
                            $campaignData[$campaignId]['total_value'] += $value;
                        }
                    }
                }
            }
            
            // Chuyển đổi dữ liệu thành định dạng cuối cùng
            $result = [];
            foreach ($campaignData as $campaignId => $data) {
                $result[$campaignId] = [
                    'conversions' => count($data['unique_phones']),
                    'conversion_value' => $data['total_value']
                ];
            }

            return $result;

        } catch (\Exception $e) {
            log_message('error', 'Error reading Google Sheet CSV: ' . $e->getMessage());
            return [];
        }
    }

    public function processRealConversions($campaigns, $gsheetUrl, $startDate, $endDate, $settings)
    {
        // Kiểm tra input
        if (empty($campaigns) || !is_array($campaigns)) {
            log_message('error', 'Invalid campaigns data: ' . json_encode($campaigns));
            return [];
        }

        if (empty($gsheetUrl)) {
            return $campaigns;
        }

        try {
            // Lấy dữ liệu chuyển đổi từ Google Sheet
            $sheetData = $this->getConversionsFromCsv($gsheetUrl, $startDate, $endDate, $settings);
            
            // Tạo mảng mới để lưu kết quả
            $processedCampaigns = [];

            // Tính toán các chỉ số thực tế cho mỗi chiến dịch
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
                if (isset($sheetData[$campaignId])) {
                    $tmpRealConversions = $processedCampaign['real_conversions'] ?? 0;
                    $tmpRealConversionValue = $processedCampaign['real_conversion_value'] ?? 0;

                    $tmpRealConversions += $sheetData[$campaignId]['conversions'];
                    $tmpRealConversionValue += $sheetData[$campaignId]['conversion_value'];

                    $processedCampaign['real_conversions'] = $tmpRealConversions;
                    $processedCampaign['real_conversion_value'] = $tmpRealConversionValue;
                    $processedCampaign['real_conversion_rate'] = isset($campaign['clicks']) && $campaign['clicks'] > 0 
                        ? ($tmpRealConversions / $campaign['clicks']) 
                        : 0;
                    $processedCampaign['real_cpa'] = $tmpRealConversions > 0 
                        ? ($campaign['cost'] ?? 0) / $tmpRealConversions
                        : 0;
                } 
                
                $processedCampaign['real_conversions'] = $processedCampaign['real_conversions'] ?? 0;
                $processedCampaign['real_conversion_value'] = $processedCampaign['real_conversion_value'] ?? 0;
                $processedCampaign['real_conversion_rate'] = $processedCampaign['real_conversion_rate'] ?? 0;
                $processedCampaign['real_cpa'] = $processedCampaign['real_cpa'] ?? 0;
                
                $processedCampaigns[] = $processedCampaign;
            }

            return $processedCampaigns;
        } catch (\Exception $e) {
            log_message('error', 'Error in processRealConversions: ' . $e->getMessage());
            return $campaigns; // Trả về dữ liệu gốc nếu có lỗi
        }
    }
} 