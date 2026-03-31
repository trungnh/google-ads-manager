<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MigrateDatabaseJson extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'migrate:json_data';
    protected $description = 'Chuyển đổi dữ liệu các cột cứng sang định dạng JSON cho ads_account_settings và campaigns_data';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        // ---------------------------------------------------------
        // 1. MIGRATE ADS_ACCOUNT_SETTINGS
        // ---------------------------------------------------------
        CLI::write("Bắt đầu di chuyển dữ liệu JSON cho bảng ads_account_settings...", "green");
        $builder = $db->table('ads_account_settings');
        // Set chunk size để tránh quá tải RAM
        $chunkSize = 100;
        $offset = 0;
        $totalMigratedAds = 0;

        while (true) {
            $settings = $builder->limit($chunkSize, $offset)->get()->getResultArray();
            if (empty($settings)) break;

            foreach ($settings as $row) {
                // Đóng gói các setting thành JSON
                $jsonArray = [
                    'gsheet1' => $row['gsheet1'] ?? null,
                    'gsheet_date_col' => $row['gsheet_date_col'] ?? null,
                    'gsheet_phone_col' => $row['gsheet_phone_col'] ?? null,
                    'gsheet_value_col' => $row['gsheet_value_col'] ?? null,
                    'gsheet_campaign_col' => $row['gsheet_campaign_col'] ?? null,
                    'gsheet2' => $row['gsheet2'] ?? null,
                    'use_ggsheet_api' => $row['use_ggsheet_api'] ?? null,
                    'ggsheet_id' => $row['ggsheet_id'] ?? null,
                    'ggsheet_name' => $row['ggsheet_name'] ?? null,
                    'ggsheet2_id' => $row['ggsheet2_id'] ?? null,
                    'ggsheet2_name' => $row['ggsheet2_name'] ?? null,
                    
                    'use_pancake' => $row['use_pancake'] ?? null,
                    'pancake_shop_id' => $row['pancake_shop_id'] ?? null,
                    'pancake_api_key' => $row['pancake_api_key'] ?? null,
                    'pancake_product_id' => $row['pancake_product_id'] ?? null,
                    'pancake_exclude_tags' => $row['pancake_exclude_tags'] ?? null,
                    'pancake_use_usd' => $row['pancake_use_usd'] ?? null,
                    'pancake_usd_rate' => $row['pancake_usd_rate'] ?? null
                ];

                // Nếu có cột json mới thì chèn vào
                if (array_key_exists('integration_settings', $row)) {
                    $builder->where('id', $row['id'])->update([
                        'integration_settings' => json_encode($jsonArray)
                    ]);
                    $totalMigratedAds++;
                }
            }

            $offset += $chunkSize;
            CLI::write("Đã xử lý " . $totalMigratedAds . " bản ghi ads_account_settings...");
        }

        // ---------------------------------------------------------
        // 2. MIGRATE CAMPAIGNS_DATA
        // ---------------------------------------------------------
        CLI::write("Bắt đầu di chuyển dữ liệu JSON cho bảng campaigns_data...", "green");
        $builderCamp = $db->table('campaigns_data');
        $chunkSize = 1000;
        $offset = 0;
        $totalMigratedCamps = 0;

        while (true) {
            $campaigns = $builderCamp->limit($chunkSize, $offset)->get()->getResultArray();
            if (empty($campaigns)) break;

            foreach ($campaigns as $camp) {
                $extMetrics = [
                    'real_conversions' => $camp['real_conversions'] ?? null,
                    'real_conversions_total' => $camp['real_conversions_total'] ?? null,
                    'real_conversions_pending' => $camp['real_conversions_pending'] ?? null,
                    'real_conversions_success' => $camp['real_conversions_success'] ?? null,
                    'real_conversion_value' => $camp['real_conversion_value'] ?? null,
                    'real_conversion_value_total' => $camp['real_conversion_value_total'] ?? null,
                    'real_conversion_value_success' => $camp['real_conversion_value_success'] ?? null,
                    'real_conversion_rate' => $camp['real_conversion_rate'] ?? null,
                    'real_cpa' => $camp['real_cpa'] ?? null,
                    'real_cpa_total' => $camp['real_cpa_total'] ?? null,
                    'real_cpa_success' => $camp['real_cpa_success'] ?? null,
                    'real_roas_total' => $camp['real_roas_total'] ?? null,
                    'real_roas_success' => $camp['real_roas_success'] ?? null,
                ];

                // Check để đảm bảo cột tồn tại
                if (array_key_exists('external_metrics', $camp)) {
                    $builderCamp->where('id', $camp['id'])->update([
                        'external_metrics' => json_encode($extMetrics)
                    ]);
                    $totalMigratedCamps++;
                }
            }

            $offset += $chunkSize;
            CLI::write("Đã xử lý " . $totalMigratedCamps . " bản ghi campaigns_data...");
        }

        CLI::write("HOÀN THÀNH: Đã migrate dữ liệu JSON ({$totalMigratedAds} ads_accounts, {$totalMigratedCamps} campaigns)!", "green");
        CLI::write("LƯU Ý: Nếu muốn gỡ bỏ hoàn toàn cột thừa, hãy tạo Migration Drop Column sau khi đã test kỹ hệ thống.", "yellow");
    }
}
