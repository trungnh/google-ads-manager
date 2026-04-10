<?php

namespace App\Controllers;

use App\Models\RevenueReportModel;
use App\Models\RevenueReportDailyModel;
use App\Models\ProductModel;
use App\Models\ProductAdsAccountModel;
use App\Models\CampaignsDataModel;
use App\Models\GoogleTokenModel;
use App\Models\UserSettingsModel;
use App\Services\GoogleAdsService;

class RevenueReports extends BaseController
{
    protected $reportModel;
    protected $dailyModel;
    protected $productModel;
    protected $productAdsAccountModel;
    protected $campaignsDataModel;
    protected $googleTokenModel;
    protected $userSettingsModel;
    protected $googleAdsService;
    protected $adsAccountSettingsModel;
    protected $pancakeService;

    public function __construct()
    {
        $this->reportModel = new RevenueReportModel();
        $this->dailyModel = new RevenueReportDailyModel();
        $this->productModel = new ProductModel();
        $this->productAdsAccountModel = new ProductAdsAccountModel();
        $this->campaignsDataModel = new CampaignsDataModel();
        $this->googleTokenModel = new GoogleTokenModel();
        $this->userSettingsModel = new UserSettingsModel();
        $this->googleAdsService = new GoogleAdsService();
        $this->adsAccountSettingsModel = new \App\Models\AdsAccountSettingsModel();
        $this->pancakeService = new \App\Services\PancakeService();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        $products = $this->productModel->where('user_id', $userId)->findAll();

        $filterMonth = $this->request->getGet('month');
        $filterProductId = $this->request->getGet('product_id');
        $reportByTime = $this->request->getGet('report_by_time') === 'true';
        $filterStartMonth = $this->request->getGet('start_month');
        $filterEndMonth = $this->request->getGet('end_month');

        $reports = $this->reportModel->getReportsByUser($userId, $filterMonth, $filterProductId, $reportByTime, $filterStartMonth, $filterEndMonth);

        return view('revenue_reports/index', [
            'title' => 'Báo cáo doanh thu',
            'reports' => $reports,
            'products' => $products,
            'filterMonth' => $filterMonth,
            'filterProductId' => $filterProductId,
            'reportByTime' => $reportByTime,
            'filterStartMonth' => $filterStartMonth,
            'filterEndMonth' => $filterEndMonth,
        ]);
    }

    public function overview()
    {
        $userId = session()->get('user_id');
        
        // Get month and year from request, default to current
        $monthYear = $this->request->getGet('month_year') ?: date('m-Y');
        list($month, $year) = explode('-', $monthYear);
        $month = (int)$month;
        $year = (int)$year;

        // Get reports for current month
        $reports = $this->reportModel->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->findAll();
        
        $reportIds = array_column($reports, 'id');
        $dailyData = $this->dailyModel->getAggregatedDailyDataByReports($reportIds);

        // Calculate totals for current month
        $totals = [
            'orders' => 0,
            'revenue' => 0,
            'ads_cost' => 0,
            'profit' => 0
        ];

        foreach ($dailyData as $day) {
            $totals['orders'] += $day['orders'];
            $totals['revenue'] += $day['revenue'];
            $totals['ads_cost'] += $day['ads_cost'];
            $totals['profit'] += $day['profit'];
        }

        // Calculate totals for previous month for comparison
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth == 0) {
            $prevMonth = 12;
            $prevYear--;
        }

        $prevReports = $this->reportModel->where('user_id', $userId)
            ->where('month', $prevMonth)
            ->where('year', $prevYear)
            ->findAll();
        
        $prevReportIds = array_column($prevReports, 'id');
        $prevDailyData = $this->dailyModel->getAggregatedDailyDataByReports($prevReportIds);

        $prevTotals = [
            'orders' => 0,
            'revenue' => 0,
            'ads_cost' => 0,
            'profit' => 0
        ];

        foreach ($prevDailyData as $day) {
            $prevTotals['orders'] += $day['orders'];
            $prevTotals['revenue'] += $day['revenue'];
            $prevTotals['ads_cost'] += $day['ads_cost'];
            $prevTotals['profit'] += $day['profit'];
        }

        // Calculate percentage differences
        $comparison = [];
        foreach (['orders', 'revenue', 'ads_cost', 'profit'] as $key) {
            if ($prevTotals[$key] > 0) {
                $diff = $totals[$key] - $prevTotals[$key];
                $comparison[$key] = ($diff / $prevTotals[$key]) * 100;
            } else {
                $comparison[$key] = null; // Mark as NaN/null if no previous data
            }
        }

        return view('revenue_reports/overview', [
            'title' => 'Tổng quan báo cáo doanh thu',
            'month' => $month,
            'year' => $year,
            'totals' => $totals,
            'comparison' => $comparison,
            'dailyData' => $dailyData,
            'monthYear' => $monthYear
        ]);
    }

    public function create()
    {
        $userId = session()->get('user_id');
        $productId = $this->request->getPost('product_id');
        $monthYear = $this->request->getPost('month_year'); // Format: MM-YYYY

        list($month, $year) = explode('-', $monthYear);

        $product = $this->productModel->find($productId);
        if (!$product) {
            return redirect()->to('/revenue_reports')->with('error', 'Sản phẩm không hợp lệ.');
        }

        // Check duplicate
        $existing = $this->reportModel->where('product_id', $productId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
        if ($existing) {
            return redirect()->to('/revenue_reports')->with('error', 'Báo cáo tháng này của sản phẩm đã tồn tại. Vui lòng chọn sửa báo cáo cũ.');
        }

        // Lấy config gốc từ ENV hoặc constant. (Fallback to defaults)
        $incomeTax = env('TAX_INCOME', '0.015');
        $adsTax = env('TAX_ADS', '0.10');
        $paymentFee = env('FEE_PAYMENT', '0.012');

        $reportName = $product['name'] . " - Tháng " . str_pad($month, 2, '0', STR_PAD_LEFT) . "/$year";

        $data = [
            'product_id' => $productId,
            'user_id' => $userId,
            'month' => $month,
            'year' => $year,
            'name' => $reportName,
            'return_rate' => $product['return_rate'],
            'import_price' => $product['import_price'],
            'shipping_fee' => $product['shipping_fee'],
            'income_tax' => $incomeTax,
            'ads_tax' => $adsTax,
            'payment_fee' => $paymentFee
        ];

        if ($this->reportModel->insert($data)) {
            $reportId = $this->reportModel->getInsertID();

            // Init daily data
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $dailyData = [];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $i);
                $dailyData[] = [
                    'report_id' => $reportId,
                    'date' => $dateStr
                ];
            }
            $this->dailyModel->insertBatch($dailyData);

            return redirect()->to('/revenue_reports/edit/' . $reportId)->with('success', 'Đã khởi tạo Báo cáo doanh thu mới.');
        }

        return redirect()->to('/revenue_reports')->with('error', 'Lỗi khởi tạo báo cáo.');
    }

    public function edit($id)
    {
        $userId = session()->get('user_id');
        $report = $this->reportModel->find($id);

        if (!$report || $report['user_id'] != $userId) {
            return redirect()->to('/revenue_reports')->with('error', 'Báo cáo không tồn tại.');
        }

        $dailyData = $this->dailyModel->getDailyData($id);

        return view('revenue_reports/edit', [
            'title' => 'Sửa Báo cáo: ' . $report['name'],
            'report' => $report,
            'dailyData' => $dailyData
        ]);
    }

    public function update($id)
    {
        $userId = session()->get('user_id');
        $report = $this->reportModel->find($id);

        if (!$report || $report['user_id'] != $userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        // Cập nhật cấu hình metadata phía trên form
        $reportData = [
            'return_rate' => $this->request->getPost('return_rate'),
            'import_price' => $this->request->getPost('import_price'),
            'shipping_fee' => $this->request->getPost('shipping_fee'),
            'income_tax' => $this->request->getPost('income_tax'),
            'ads_tax' => $this->request->getPost('ads_tax'),
            'payment_fee' => $this->request->getPost('payment_fee')
        ];
        $this->reportModel->update($id, $reportData);

        // Cập nhật chi tiết các dòng (vòng lặp từ JSON)
        $dailyDataRaw = $this->request->getPost('daily_data'); // string JSON
        if ($dailyDataRaw) {
            $dailyArr = json_decode($dailyDataRaw, true);
            if (is_array($dailyArr)) {
                $this->dailyModel->upsertDailyData($id, $dailyArr);
            }
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Đã lưu báo cáo.']);
    }

    public function delete($id)
    {
        $userId = session()->get('user_id');
        $report = $this->reportModel->find($id);

        if (!$report || $report['user_id'] != $userId) {
            return redirect()->to('/revenue_reports')->with('error', 'Báo cáo không tồn tại.');
        }

        if ($this->reportModel->delete($id)) {
            // Delete daily data as well
            $this->dailyModel->where('report_id', $id)->delete();
            return redirect()->to('/revenue_reports')->with('success', 'Đã xóa báo cáo thành công.');
        }

        return redirect()->to('/revenue_reports')->with('error', 'Có lỗi xảy ra khi xóa báo cáo.');
    }

    public function fetchAdsCost()
    {
        $userId = session()->get('user_id');
        $reportId = $this->request->getPost('report_id');
        $date = $this->request->getPost('date');
        $keyword = null;

        if (!$reportId || !$date) {
            return $this->response->setJSON(['success' => false, 'message' => 'Thiếu dữ liệu.']);
        }

        $report = $this->reportModel->find($reportId);
        if (!$report || $report['user_id'] != $userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Báo cáo không hợp lệ.']);
        }

        $product = $this->productModel->find($report['product_id']);
        if ($product) {
            $keyword = $product['keyword_campaign'] ?? null;
        }

        // Lấy danh sách customer_id được map với sản phẩm này
        $mappings = $this->productAdsAccountModel->where('product_id', $report['product_id'])->findAll();
        if (empty($mappings)) {
            return $this->response->setJSON(['success' => true, 'ads_cost' => 0]);
        }

        $customerIds = array_column($mappings, 'customer_id');

        $userSettings = $this->userSettingsModel->where('user_id', $userId)->first();
        $mccId = $userSettings['mcc_id'] ?? null;

        $tokenData = $this->googleTokenModel->getValidToken($userId);
        if (empty($tokenData) || empty($tokenData['access_token'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Lỗi kết nối Google Ads: Vui lòng kết nối lại tài khoản.']);
        }
        $accessToken = $tokenData['access_token'];

        $totalCost = 0;
        foreach ($customerIds as $customerId) {
            $cost = $this->googleAdsService->getDailyCost($customerId, $accessToken, $date, $mccId, $keyword);
            $totalCost += $cost;
        }

        return $this->response->setJSON([
            'success' => true,
            'ads_cost' => $totalCost
        ]);
    }

    public function fetchPancakeData()
    {
        $userId = session()->get('user_id');
        $reportId = $this->request->getPost('report_id');
        $date = $this->request->getPost('date');

        if (!$reportId || !$date) {
            return $this->response->setJSON(['success' => false, 'message' => 'Thiếu dữ liệu.']);
        }

        $report = $this->reportModel->find($reportId);
        if (!$report || $report['user_id'] != $userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Báo cáo không hợp lệ.']);
        }

        // Lấy thông tin tài khoản ads và settings để có shop_id và api_key của pancake
        // Ở đây ta cần lấy shop_id và api_key từ settings của AdsAccount. 
        // Vì 1 sản phẩm có thể map với nhiều AdsAccount, ta sẽ lấy AdsAccount đầu tiên có cấu hình Pancake.
        $mappings = $this->productAdsAccountModel->where('product_id', $report['product_id'])->findAll();
        if (empty($mappings)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sản phẩm chưa được gán cho tài khoản Ads nào.']);
        }

        $pancakeSettings = null;
        foreach ($mappings as $mapping) {
            $settings = $this->adsAccountSettingsModel->getSettingsByCustomerId($mapping['customer_id']);
            if ($settings && !empty($settings['use_pancake']) && !empty($settings['pancake_shop_id']) && !empty($settings['pancake_api_key'])) {
                $pancakeSettings = $settings;
                break;
            }
        }

        if (!$pancakeSettings) {
            return $this->response->setJSON(['success' => false, 'message' => 'Không tìm thấy cấu hình Pancake API cho các tài khoản Ads liên kết.']);
        }

        // Lấy dữ liệu từ Pancake
        $startDateTime = $date . ' 00:00:00';
        $endDateTime = $date . ' 23:59:59';
        $productId = $pancakeSettings['pancake_product_id'] ?? null;

        // Lấy orders từ Pancake
        $orders = [];
        $skus = array_map('trim', explode(',', $productId));
        foreach ($skus as $sku) {
            $pancakeProductId = $this->pancakeService->getPancakeProductId($pancakeSettings['pancake_shop_id'], $pancakeSettings['pancake_api_key'], $sku);
            $tmpOrders = $this->pancakeService->getOrders(
                $pancakeSettings['pancake_shop_id'],
                $pancakeSettings['pancake_api_key'],
                $pancakeProductId,
                $startDateTime,
                $endDateTime
            );
            $orders = array_merge($orders, $tmpOrders);
        }

        if (empty($orders)) {
            return $this->response->setJSON([
                'success' => true,
                'orders' => 0,
                'revenue' => 0,
                'goods_cost' => 0
            ]);
        }

        // Lọc đơn hàng và tính toán theo logic PancakeService
        // Định nghĩa các trạng thái đơn hàng và thẻ loại trừ
        $canceledStatuses = [6, 7]; // Đã hủy, Đã xóa
        $excludeTags = $pancakeSettings['pancake_exclude_tags'] ?? '';
        
        $validOrdersCount = 0;
        $totalRevenue = 0;
        $totalGoodsCost = 0;
        $processedOrderIds = [];

        foreach ($orders as $order) {
            $orderId = $order['id'] ?? '';
            if (empty($orderId) || in_array($orderId, $processedOrderIds)) {
                continue;
            }

            // Lọc theo status và tag
            $orderStatus = isset($order['status']) ? (int) $order['status'] : null;
            if (in_array($orderStatus, $canceledStatuses)) {
                continue;
            }

            if (!empty($excludeTags)) {
                $excludeTagsArr = array_map('trim', explode(',', $excludeTags));
                $hasExcludedTag = false;
                if (isset($order['tags']) && is_array($order['tags'])) {
                    foreach ($order['tags'] as $tag) {
                        if (in_array($tag['id'], $excludeTagsArr)) {
                            $hasExcludedTag = true;
                            break;
                        }
                    }
                }
                if ($hasExcludedTag) {
                    continue;
                }
            }

            // Tính toán doanh thu và tiền hàng
            $validOrdersCount++;
            $totalRevenue += isset($order['money_to_collect']) ? (float) $order['money_to_collect'] : 0;

            if (isset($order['items']) && is_array($order['items'])) {
                foreach ($order['items'] as $item) {
                    $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 1;
                    $importPrice = 0;
                    if (isset($item['variation_info']['last_imported_price'])) {
                        $importPrice = (float) $item['variation_info']['last_imported_price'];
                    }
                    $totalGoodsCost += ($quantity * $importPrice);
                }
            }

            $processedOrderIds[] = $orderId;
        }

        return $this->response->setJSON([
            'success' => true,
            'orders' => $validOrdersCount,
            'revenue' => $totalRevenue,
            'goods_cost' => $totalGoodsCost
        ]);
    }
}
