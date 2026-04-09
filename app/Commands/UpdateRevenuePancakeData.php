<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\RevenueReportModel;
use App\Models\RevenueReportDailyModel;
use App\Models\ProductModel;
use App\Models\ProductAdsAccountModel;
use App\Models\AdsAccountSettingsModel;
use App\Services\PancakeService;

class UpdateRevenuePancakeData extends BaseCommand
{
    protected $group = 'Custom';
    protected $name = 'cron:update_pancake_revenue';
    protected $description = 'Lấy dữ liệu đơn hàng từ Pancake CRM và cập nhật vào báo cáo doanh thu.';

    public function run(array $params)
    {
        CLI::write("Bắt đầu chạy Cron cập nhật dữ liệu Doanh thu từ Pancake...", 'cyan');

        $reportModel = new RevenueReportModel();
        $dailyModel = new RevenueReportDailyModel();
        $productModel = new ProductModel();
        $mappingModel = new ProductAdsAccountModel();
        $settingsModel = new AdsAccountSettingsModel();
        $pancakeService = new PancakeService();

        // 1. Lấy ngày chạy (mặc định hôm qua nếu chạy tự động hằng ngày)
        $targetDate = isset($params[0]) ? $params[0] : date('Y-m-d', strtotime('-1 day'));
        CLI::write("Đang xử lý dữ liệu cho ngày: {$targetDate}", 'yellow');

        $month = date('n', strtotime($targetDate));
        $year = date('Y', strtotime($targetDate));

        // 2. Tìm các báo cáo đang có tương ứng với tháng/năm này
        $reports = $reportModel->where('month', $month)->where('year', $year)->findAll();
        if (empty($reports)) {
            CLI::write("Không có báo cáo nào trong {$month}/{$year}.", 'green');
            return;
        }

        foreach ($reports as $report) {
            CLI::write("Xử lý báo cáo #{$report['id']}: {$report['name']}...", 'cyan');

            // 3. Lấy thông tin sản phẩm và mã mapping
            $product = $productModel->find($report['product_id']);
            if (!$product) {
                CLI::write("-> Lỗi: Không tìm thấy sản phẩm #{$report['product_id']}", 'red');
                continue;
            }

            // Lấy mappings product - tài khoản quảng cáo
            $mappings = $mappingModel->where('product_id', $product['id'])->findAll();
            if (empty($mappings)) {
                CLI::write("-> Sản phẩm {$product['product_code']} chưa được mapping tài khoản Ads.", 'yellow');
                continue;
            }

            $customerIds = array_column($mappings, 'customer_id');
            // Tìm 1 cài đặt Pancake từ tập các tài khoản Ads (thường chung 1 shop)
            $pancakeShopId = null;
            $pancakeApiKey = null;

            foreach ($customerIds as $cId) {
                $settings = $settingsModel->getSettingsByCustomerId($cId);
                if (!empty($settings['pancake_shop_id']) && !empty($settings['pancake_api_key'])) {
                    $pancakeShopId = $settings['pancake_shop_id'];
                    $pancakeApiKey = $settings['pancake_api_key'];
                    break;
                }
            }

            if (!$pancakeShopId || !$pancakeApiKey) {
                CLI::write("-> Không tìm thấy Pancake ID/API Key trên tài khoản Ads cho sản phẩm {$product['product_code']}", 'yellow');
                continue;
            }

            // 4. Gọi PancakeService lấy danh sách đơn của ngày targetDate
            $startDateTime = $targetDate . ' 00:00:00';
            $endDateTime = $targetDate . ' 23:59:59';
            $pancakeProductId = $pancakeService->getPancakeProductId($pancakeShopId, $pancakeApiKey, $product['product_code']);
            $orders = $pancakeService->getOrders($pancakeShopId, $pancakeApiKey, $pancakeProductId, $startDateTime, $endDateTime);

            if (empty($orders)) {
                CLI::write("-> Không có đơn hàng nào từ CRM trong ngày.", 'yellow');
                continue;
            }

            // 5. Tính toán revenue data
            $revenueData = $pancakeService->calculateRevenueFromOrders($orders, $product['product_code']);
            CLI::write("-> Tìm thấy {$revenueData['orders']} đơn hàng hợp lệ. Doanh thu: " . number_format($revenueData['revenue']) . "đ", 'green');

            // 6. Tính toán công cụ tài chính
            // Lấy daily records có sẵn để giữ Ads Cost của ngày đó
            $existingDaily = $dailyModel->where('report_id', $report['id'])
                ->where('date', $targetDate)
                ->first();

            $adsCost = $existingDaily ? (float) $existingDaily['ads_cost'] : 0;

            // Retrieve config stats from report global cache
            $returnRate = (float) $report['return_rate'];
            $incomeTax = (float) $report['income_tax'];
            $adsTax = (float) $report['ads_tax'];
            $paymentFee = (float) $report['payment_fee'];
            // Vận chuyển cơ bản
            $reportShipFeeBase = (float) $report['shipping_fee'];

            // Base aggregated stats from CRM response
            $goodsCost = $revenueData['goods_cost'];
            $shipCost = $revenueData['ship_cost'];
            $revenue = $revenueData['revenue'];
            $ordersCount = $revenueData['orders'];

            // Tỉ lệ hoàn: ((doanh thu - giá nhập) * tỉ lệ hoàn) + (số đơn * tỉ lệ hoàn * (phí ship/2))
            $returnCost = (($revenue - $goodsCost) * $returnRate) + ($ordersCount * $returnRate * ($reportShipFeeBase / 2));
            if ($returnCost < 0)
                $returnCost = 0;

            // Tổng chi
            $totalCost = $goodsCost + $shipCost + $returnCost + $adsCost + ($adsCost * $adsTax) + ($adsCost * $paymentFee) + ($revenue * $incomeTax);

            // Lợi nhuận
            $profit = $revenue - $totalCost;

            // 7. Lưu đè / Upsert vào database daily report
            $saveData = [
                'report_id' => $report['id'],
                'date' => $targetDate,
                'orders' => $ordersCount,
                'quantity' => 0, // SL bỏ đi
                'ads_cost' => $adsCost,
                'revenue' => $revenue,
                'goods_cost' => $goodsCost,
                'ship_cost' => $shipCost,
                'return_cost' => $returnCost,
                'total_cost' => $totalCost,
                'profit' => $profit
            ];

            $dailyData = [$saveData]; // upsertDailyData expects matrix
            $dailyModel->upsertDailyData($report['id'], $dailyData);

            CLI::write("-> Cập nhật thành công cho bảng doanh thu.", 'green');
        }

        CLI::write("Chạy Cron Pancake hoàn tất!", 'cyan');
    }
}
