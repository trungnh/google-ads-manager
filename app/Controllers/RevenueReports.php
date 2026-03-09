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
    }

    public function index()
    {
        $userId = session()->get('user_id');
        $reports = $this->reportModel->getReportsByUser($userId);
        $products = $this->productModel->where('user_id', $userId)->findAll();

        return view('revenue_reports/index', [
            'title' => 'Báo cáo doanh thu',
            'reports' => $reports,
            'products' => $products
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

    public function fetchAdsCost()
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
            $cost = $this->googleAdsService->getDailyCost($customerId, $accessToken, $date, $mccId);
            $totalCost += $cost;
        }

        return $this->response->setJSON([
            'success' => true,
            'ads_cost' => $totalCost
        ]);
    }
}
