<?php

namespace App\Services;

class GoogleSheetService
{
    public function getConversionsFromCsv($csvUrl, $date, $settings)
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

                // Chỉ xử lý dữ liệu của ngày được chọn
                if ($conversionDate === $date) {
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
} 