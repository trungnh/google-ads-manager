<?php

namespace App\Controllers;

use App\Models\AdsAccountModel;
use App\Models\AdsAccountSettingsModel;

class AdsAccountSettings extends BaseController
{
    protected $adsAccountModel;
    protected $adsAccountSettingsModel;

    public function __construct()
    {
        $this->adsAccountModel = new AdsAccountModel();
        $this->adsAccountSettingsModel = new AdsAccountSettingsModel();
    }

    public function index($customerId)
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        try {
            // Kiểm tra xem user có quyền truy cập account này không
            $account = $this->adsAccountModel
                ->where('user_id', $userId)
                ->where('customer_id', $customerId)
                ->first();

            if (!$account) {
                return redirect()->to('/adsaccounts')->with('error', 'Không có quyền truy cập tài khoản này');
            }

            // Lấy danh sách tất cả tài khoản của user để hiển thị trong dropdown
            $accounts = $this->adsAccountModel
            ->where('user_id', $userId)
            ->orderBy('order', 'ASC')
            ->findAll();

            // Lấy settings hiện tại
            $settings = $this->adsAccountSettingsModel->getSettingsByCustomerId($customerId);
            // Check trường hợp ads account thuộc nhiều user khác nhau. Chỉ check 1 setting duy nhất
            // if (!$settings) {
            //     $tmpAccounts = $this->adsAccountModel->getAccountsByCustomerId($account['customer_id']);
            //     foreach ($tmpAccounts as $acc) {
            //         $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($acc['id']);
            //         if ($settings) {
            //             break;
            //         }
            //     }
            // }
            
            // Tạo settings mặc định nếu chưa có
            if (!$settings) {
                $settings = [
                    'account_id' => $account['id'],
                    'customer_id' => $account['customer_id'],
                    'auto_optimize' => 0,
                    'cpa_threshold' => 0,
                    'increase_budget' => 0,
                    'gsheet1' => '',
                    'gsheet_date_col' => 'A',
                    'gsheet_phone_col' => 'C',
                    'gsheet_value_col' => 'F',
                    'gsheet_campaign_col' => 'L',
                    'gsheet2' => '',
                    'cost_threshold' => 0,
                    'auto_on_off' => 0,
                    'use_roas_threshold' => 0,
                    'extended_cpa_threshold' => 0,
                    'default_paused_campaigns' => 0,
                    'exclude_campaign_ids' => null,
                    'pancake_shop_id' => '',
                    'pancake_api_key' => '',
                    'pancake_product_id' => '',
                    'use_pancake' => 0,
                    'pancake_use_usd' => 0,
                    'pancake_usd_rate' => 23000
                ];
                log_message('info', 'Creating default settings for account: ' . $customerId);
                $this->adsAccountSettingsModel->insert($settings);
                $settings = $this->adsAccountSettingsModel->getSettingsByCustomerId($customerId);
            }

            $data = [
                'title' => 'Cài đặt tài khoản - ' . $account['customer_name'],
                'account' => $account,
                'accounts' => $accounts,
                'settings' => $settings
            ];

            return view('ads_account_settings/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error in AdsAccountSettings::index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi lấy thông tin cài đặt: ' . $e->getMessage());
        }
    }

    public function viewAdmin($customerId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id'); 

        try {
            // Lấy danh sách tất cả tài khoản của user để hiển thị trong dropdown
            $accounts = $this->adsAccountModel->select('ads_accounts.*, users.username')
                ->join('users', 'users.id = ads_accounts.user_id')
                ->where('ads_accounts.user_id !=', $userId)
                ->orderBy('ads_accounts.user_id', 'ASC')
                ->findAll();

            $account = $this->adsAccountModel
                ->where('customer_id', $customerId)
                ->first();

            // Lấy settings hiện tại
            $settings = $this->adsAccountSettingsModel->getSettingsByCustomerId($customerId);
            
            // Tạo settings mặc định nếu chưa có
            if (!$settings) {
                return redirect()->to('/adsaccounts/admin_view')->with('error', 'Chưa có setting cho tài khoản này');
            }

            $data = [
                'title' => 'Cài đặt tài khoản - ' . $account['customer_name'],
                'account' => $account,
                'accounts' => $accounts,
                'settings' => $settings
            ];

            return view('ads_account_settings/admin_view/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error in AdsAccountSettings::index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi lấy thông tin cài đặt: ' . $e->getMessage());
        }
    }

    public function update($customerId)
    {
        // Kiểm tra quyền truy cập
        $userId = session()->get('id');
        $account = $this->adsAccountModel
            ->where('user_id', $userId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$account) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập tài khoản này'
            ]);
        }

        try {
            log_message('info', 'Updating settings for account: ' . $customerId);
            log_message('info', 'POST data: ' . json_encode($this->request->getPost()));
            log_message('info', 'Request method: ' . $this->request->getMethod());
            log_message('info', 'Content-Type: ' . $this->request->getHeaderLine('Content-Type'));
            
            $order = $this->request->getPost('order') ?? 0;
            $userId = session()->get('id');
            
            // Kiểm tra tài khoản
            $account = $this->adsAccountModel
                ->where('user_id', $userId)
                ->where('customer_id', $customerId)
                ->first();
                
            if (!$account) {
                log_message('error', 'Account not found for customer ID: ' . $customerId . ' and user ID: ' . $userId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không tìm thấy tài khoản'
                ]);
            }
            
            log_message('info', 'Account found: ' . json_encode($account));

            // Debug log for auto_optimize value
            $autoOptimizeValue = $this->request->getPost('auto_optimize');
            log_message('info', 'Raw auto_optimize value: ' . $autoOptimizeValue);
            log_message('info', 'auto_optimize type: ' . gettype($autoOptimizeValue));

            $settings = [
                'auto_optimize' => ($autoOptimizeValue === 'true' || $autoOptimizeValue === true) ? 1 : 0,
                'cpa_threshold' => $this->request->getPost('cpa_threshold'),
                'roas_threshold' => $this->request->getPost('roas_threshold'),
                'increase_budget' => $this->request->getPost('increase_budget'),
                'gsheet1' => $this->request->getPost('gsheet1'),
                'gsheet_date_col' => $this->request->getPost('gsheet_date_col'),
                'gsheet_phone_col' => $this->request->getPost('gsheet_phone_col'),
                'gsheet_value_col' => $this->request->getPost('gsheet_value_col'),
                'gsheet_campaign_col' => $this->request->getPost('gsheet_campaign_col'),
                'gsheet2' => $this->request->getPost('gsheet2'),
                'cost_threshold' => $this->request->getPost('cost_threshold'),
                'auto_on_off' => $this->request->getPost('auto_on_off'),
                'use_roas_threshold' => $this->request->getPost('use_roas_threshold'),
                'extended_cpa_threshold' => $this->request->getPost('extended_cpa_threshold'),
                'default_paused_campaigns' => $this->request->getPost('default_paused_campaigns'),
                'exclude_campaign_ids' => $this->request->getPost('exclude_campaign_ids'),
                'pancake_shop_id' => $this->request->getPost('pancake_shop_id'),
                'pancake_api_key' => $this->request->getPost('pancake_api_key'),
                'pancake_product_id' => $this->request->getPost('pancake_product_id'),
                'pancake_exclude_tags' => $this->request->getPost('pancake_exclude_tags'),
                'use_pancake' => $this->request->getPost('use_pancake'),
                'pancake_use_usd' => $this->request->getPost('pancake_use_usd'),
                'pancake_usd_rate' => $this->request->getPost('pancake_usd_rate'),
                'account_id' => $account['id'],
            ];

            log_message('info', 'Processed settings: ' . json_encode($settings));
            $result = $this->adsAccountSettingsModel->saveSettings($customerId, $settings);

            $this->adsAccountModel->update($account['id'], ['order' => $order]);
            
            if ($result) {
                log_message('info', 'Settings saved successfully for account: ' . $customerId);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Cập nhật cài đặt thành công'
                ]);
            } else {
                log_message('error', 'Failed to save settings for account: ' . $customerId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Lỗi khi lưu cài đặt'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in AdsAccountSettings::update: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lỗi khi cập nhật cài đặt: ' . $e->getMessage()
            ]);
        }
    }

    public function getPancakeTags()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            $shopId = $this->request->getPost('shop_id');
            $apiKey = $this->request->getPost('api_key');

            if (empty($shopId) || empty($apiKey)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Shop ID và API Key không được để trống'
                ]);
            }

            // Khởi tạo service Pancake
            $pancakeService = new \App\Services\PancakeService();
            
            // Lấy danh sách thẻ từ Pancake POS
            $tags = $pancakeService->getTags($shopId, $apiKey);
            
            if ($tags === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không thể kết nối đến Pancake POS API. Vui lòng kiểm tra Shop ID và API Key.'
                ]);
            }

            $rsTags = [];
            foreach ($tags as $tag) {
                if ($tag['is_system_tag'] == false) {
                    $rsTags[] = $tag;
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'tags' => $rsTags
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in AdsAccountSettings::getPancakeTags: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách thẻ: ' . $e->getMessage()
            ]);
        }
    }
}