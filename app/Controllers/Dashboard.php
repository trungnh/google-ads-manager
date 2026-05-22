<?php

namespace App\Controllers;

use App\Models\GoogleTokenModel;
use App\Models\AdsAccountModel;
use App\Models\AdsAccountSettingsModel;
use App\Models\CampaignsDataModel;
use App\Models\OptimizeLogsModel;
use App\Models\ProductModel;
use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $userId = session()->get('id');
        $googleTokenModel = new GoogleTokenModel();
        $token = $googleTokenModel->getValidToken($userId);
        
        $customerIdFilter = $this->request->getVar('customer_id') ?? 'all';
        $dateRange = $this->request->getVar('date_range') ?? 'today';

        // Lấy danh sách tài khoản active liên kết
        $adsAccountModel = new AdsAccountModel();
        $userAccounts = $adsAccountModel->where('user_id', $userId)->where('status', 'ACTIVE')->findAll();
        $customerIds = array_column($userAccounts, 'customer_id');

        // Khởi tạo các trạng thái tích hợp hệ thống
        $settingsModel = new AdsAccountSettingsModel();
        $hasPancake = false;
        $hasGSheet = false;
        $autoOptimizeCount = 0;

        if (!empty($customerIds)) {
            $settings = $settingsModel->whereIn('customer_id', $customerIds)->findAll();
            foreach ($settings as $setting) {
                if ($setting['use_pancake'] == 1 && !empty($setting['pancake_shop_id'])) {
                    $hasPancake = true;
                }
                if ($setting['use_ggsheet_api'] == 1 && !empty($setting['ggsheet_id'])) {
                    $hasGSheet = true;
                }
                if ($setting['auto_optimize'] == 1) {
                    $autoOptimizeCount++;
                }
            }
        }

        // Tìm ngày đồng bộ dữ liệu mới nhất để hiển thị làm mặc định
        $campaignsDataModel = new CampaignsDataModel();
        $latestDateRow = null;
        if (!empty($customerIds)) {
            $latestDateRow = $campaignsDataModel->select('date')
                ->whereIn('customer_id', $customerIds)
                ->orderBy('date', 'DESC')
                ->first();
        }
        $latestDate = $latestDateRow ? $latestDateRow['date'] : date('Y-m-d');

        // Định dạng thời gian truy vấn
        $db = \Config\Database::connect();
        $builder = $db->table('campaigns_data');

        if ($customerIdFilter !== 'all' && in_array($customerIdFilter, $customerIds)) {
            $builder->where('customer_id', $customerIdFilter);
        } elseif (!empty($customerIds)) {
            $builder->whereIn('customer_id', $customerIds);
        } else {
            $builder->where('1', '0'); // Không có tài khoản liên kết
        }

        if ($dateRange === 'yesterday') {
            $targetDate = date('Y-m-d', strtotime($latestDate . ' -1 day'));
            $builder->where('date', $targetDate);
        } elseif ($dateRange === '7days') {
            $startDate = date('Y-m-d', strtotime($latestDate . ' -6 days'));
            $builder->where('date >=', $startDate)->where('date <=', $latestDate);
        } elseif ($dateRange === '30days') {
            $startDate = date('Y-m-d', strtotime($latestDate . ' -29 days'));
            $builder->where('date >=', $startDate)->where('date <=', $latestDate);
        } else { // today
            $builder->where('date', $latestDate);
        }

        $campaigns = $builder->get()->getResultArray();

        // Tính các chỉ số tổng quan
        $totalSpend = 0;
        $totalBudget = 0;
        $googleConversions = 0;
        $crmConversions = 0;
        $crmRevenue = 0;

        if ($dateRange === '7days' || $dateRange === '30days') {
            // Tính ngân sách cho ngày gần nhất của mỗi chiến dịch để không bị cộng dồn nhân đôi
            $uniqueCampBudgets = [];
            foreach ($campaigns as $camp) {
                $key = $camp['campaign_id'];
                if (!isset($uniqueCampBudgets[$key]) || $camp['date'] > $uniqueCampBudgets[$key]['date']) {
                    $uniqueCampBudgets[$key] = [
                        'date' => $camp['date'],
                        'budget' => $camp['budget']
                    ];
                }
            }
            $totalBudget = array_sum(array_column($uniqueCampBudgets, 'budget'));
        } else {
            foreach ($campaigns as $camp) {
                $totalBudget += $camp['budget'];
            }
        }

        foreach ($campaigns as $camp) {
            $totalSpend += $camp['cost'];
            $googleConversions += $camp['conversions'];
            $crmConversions += $camp['real_conversions'];
            $crmRevenue += $camp['real_conversion_value'];
        }

        // Lấy sản phẩm của User để tính toán Lợi nhuận Net chính xác theo Keyword Campaign
        $productModel = new ProductModel();
        $products = $productModel->where('user_id', $userId)->findAll();

        $netProfit = 0;
        foreach ($campaigns as $camp) {
            $rev = $camp['real_conversion_value'];
            $orders = $camp['real_conversions'];
            
            // Tìm sản phẩm tương ứng bằng keyword_campaign
            $importPrice = 0;
            $sellingPrice = 0;
            $shippingFee = 30000; // Mặc định 30,000 VND
            $returnRate = 0.10;   // Mặc định 10%
            
            foreach ($products as $prod) {
                if (!empty($prod['keyword_campaign']) && stripos($camp['name'], $prod['keyword_campaign']) !== false) {
                    $importPrice = $prod['import_price'];
                    $sellingPrice = $prod['selling_price'];
                    $shippingFee = $prod['shipping_fee'];
                    $returnRate = $prod['return_rate'] / 100;
                    break;
                }
            }

            // Tính Chi phí nhập hàng (Goods Cost)
            if ($importPrice > 0 && $sellingPrice > 0) {
                $goodsCost = $orders * $importPrice;
            } else {
                $goodsCost = $rev * 0.35; // Fallback COGS trung bình 35% doanh thu
            }

            // Tính Chi phí hoàn hàng thực tế (Southeast Asia logistics formula)
            $returnCost = max(0, ($rev - $goodsCost) * $returnRate + ($orders * $returnRate * $shippingFee / 2));
            
            $adsCost = $camp['cost'];
            $adsTaxRate = 0.05;      // Thuế Ads 5% chi tiêu
            $paymentFeeRate = 0.02;  // Phí thanh toán / COD 2% doanh thu
            $incomeTaxRate = 0.015;  // Thuế thu nhập doanh nghiệp 1.5% doanh thu
            $shipCost = $orders * $shippingFee;

            $totalCost = $goodsCost 
                + $shipCost 
                + $returnCost 
                + $adsCost 
                + ($adsCost * $adsTaxRate) 
                + ($adsCost * $paymentFeeRate) 
                + ($rev * $incomeTaxRate);

            $netProfit += ($rev - $totalCost);
        }

        // Lấy 5 logs tối ưu gần nhất của User
        $optimizeLogsModel = new OptimizeLogsModel();
        $recentLogs = $optimizeLogsModel->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();

        // Lấy dữ liệu biểu đồ 7 ngày gần nhất
        $chartData = [];
        if (!empty($customerIds)) {
            $chartQuery = $db->table('campaigns_data')
                ->select('date, SUM(conversions) as google_conv, SUM(real_conversions) as crm_conv, SUM(cost) as cost, SUM(real_conversion_value) as revenue')
                ->whereIn('customer_id', $customerIds)
                ->where('date >=', date('Y-m-d', strtotime($latestDate . ' -6 days')))
                ->where('date <=', $latestDate)
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get()
                ->getResultArray();

            $dateMap = [];
            foreach ($chartQuery as $row) {
                $dateMap[$row['date']] = $row;
            }

            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime($latestDate . " -{$i} days"));
                if (isset($dateMap[$d])) {
                    $cCost = (float)$dateMap[$d]['cost'];
                    $cConv = (float)$dateMap[$d]['crm_conv'];
                    $cCpa = $cConv > 0 ? $cCost / $cConv : 0;
                    $chartData[] = [
                        'date' => date('d/m', strtotime($d)),
                        'google_conv' => (float)$dateMap[$d]['google_conv'],
                        'crm_conv' => $cConv,
                        'cost' => $cCost,
                        'revenue' => (float)$dateMap[$d]['revenue'],
                        'crm_cpa' => (float)$cCpa
                    ];
                } else {
                    $chartData[] = [
                        'date' => date('d/m', strtotime($d)),
                        'google_conv' => 0,
                        'crm_conv' => 0,
                        'cost' => 0,
                        'revenue' => 0,
                        'crm_cpa' => 0
                    ];
                }
            }
        }

        // Bảng xếp hạng chiến dịch
        $topCampaigns = [];
        $problemCampaigns = [];

        if (!empty($campaigns)) {
            // Group and sum by campaign_id in case there are multiple days
            $campGroups = [];
            foreach ($campaigns as $camp) {
                $cid = $camp['campaign_id'];
                if (!isset($campGroups[$cid])) {
                    $campGroups[$cid] = $camp;
                } else {
                    $campGroups[$cid]['cost'] += $camp['cost'];
                    $campGroups[$cid]['conversions'] += $camp['conversions'];
                    $campGroups[$cid]['real_conversions'] += $camp['real_conversions'];
                    $campGroups[$cid]['real_conversion_value'] += $camp['real_conversion_value'];
                }
            }

            // Top Performing Campaigns (Ranked by CRM real conversions)
            uasort($campGroups, function($a, $b) {
                return $b['real_conversions'] <=> $a['real_conversions'];
            });
            $topCampaigns = array_slice($campGroups, 0, 5);

            // Problem Campaigns (Zero CRM Conversions with high cost spend)
            $zeroCrmCamps = array_filter($campGroups, function($camp) {
                return $camp['real_conversions'] == 0 && $camp['cost'] > 0;
            });
            uasort($zeroCrmCamps, function($a, $b) {
                return $b['cost'] <=> $a['cost'];
            });
            $problemCampaigns = array_slice($zeroCrmCamps, 0, 5);
        }

        $data = [
            'hasGoogleToken' => !empty($token),
            'userAccounts' => $userAccounts,
            'selectedCustomerId' => $customerIdFilter,
            'selectedDateRange' => $dateRange,
            'latestDate' => $latestDate,
            'hasPancake' => $hasPancake,
            'hasGSheet' => $hasGSheet,
            'autoOptimizeCount' => $autoOptimizeCount,
            'totalSpend' => $totalSpend,
            'totalBudget' => $totalBudget,
            'googleConversions' => $googleConversions,
            'crmConversions' => $crmConversions,
            'crmRevenue' => $crmRevenue,
            'netProfit' => $netProfit,
            'recentLogs' => $recentLogs,
            'chartData' => $chartData,
            'topCampaigns' => $topCampaigns,
            'problemCampaigns' => $problemCampaigns,
        ];
        
        return view('dashboard/index', $data);
    }
}