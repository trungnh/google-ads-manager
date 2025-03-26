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

    public function index($adsAccountId)
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
                ->where('id', $adsAccountId)
                ->first();

            if (!$account) {
                return redirect()->to('/adsaccounts')->with('error', 'Không có quyền truy cập tài khoản này');
            }

            // Lấy settings hiện tại
            $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($adsAccountId);
            $account = $this->adsAccountModel->find($adsAccountId);
            
            if (!$settings) {
                // Tạo settings mặc định nếu chưa có
                $settings = [
                    'account_id' => $adsAccountId,
                    'auto_optimize' => 0,
                    'cpa_threshold' => 0,
                    'increase_budget' => 0,
                    'gsheet1' => '',
                    'gsheet_date_col' => 'A',
                    'gsheet_phone_col' => 'C',
                    'gsheet_value_col' => 'F',
                    'gsheet_campaign_col' => 'L',
                    'gsheet2' => '',
                    'order' => $account['order']
                ];
                log_message('info', 'Creating default settings for account: ' . $adsAccountId);
                $this->adsAccountSettingsModel->insert($settings);
                $settings = $this->adsAccountSettingsModel->getSettingsByAccountId($adsAccountId);
            }

            $data = [
                'title' => 'Cài đặt tài khoản - ' . $account['customer_name'],
                'account' => $account,
                'settings' => $settings
            ];

            return view('ads_account_settings/index', $data);

        } catch (Exception $e) {
            log_message('error', 'Error in AdsAccountSettings::index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi lấy thông tin cài đặt: ' . $e->getMessage());
        }
    }

    public function update($adsAccountId)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            log_message('info', 'Updating settings for account: ' . $adsAccountId);
            log_message('info', 'POST data: ' . json_encode($this->request->getPost()));
            $order = $this->request->getPost('order') ?? 0;
            $account = $this->adsAccountModel->find($adsAccountId);

            $settings = [
                'auto_optimize' => $this->request->getPost('auto_optimize') ? 1 : 0,
                'cpa_threshold' => $this->request->getPost('cpa_threshold'),
                'roas_threshold' => $this->request->getPost('roas_threshold'),
                'increase_budget' => $this->request->getPost('increase_budget'),
                'gsheet1' => $this->request->getPost('gsheet1'),
                'gsheet_date_col' => $this->request->getPost('gsheet_date_col'),
                'gsheet_phone_col' => $this->request->getPost('gsheet_phone_col'),
                'gsheet_value_col' => $this->request->getPost('gsheet_value_col'),
                'gsheet_campaign_col' => $this->request->getPost('gsheet_campaign_col'),
                'gsheet2' => $this->request->getPost('gsheet2')
            ];

            log_message('info', 'Processed settings: ' . json_encode($settings));

            $result = $this->adsAccountSettingsModel->saveSettings($adsAccountId, $settings);
            $this->adsAccountModel->update($adsAccountId, ['order' => $order]);
            
            if ($result) {
                log_message('info', 'Settings saved successfully for account: ' . $adsAccountId);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Cập nhật cài đặt thành công'
                ]);
            } else {
                log_message('error', 'Failed to save settings for account: ' . $adsAccountId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Lỗi khi lưu cài đặt'
                ]);
            }

        } catch (Exception $e) {
            log_message('error', 'Error in AdsAccountSettings::update: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lỗi khi cập nhật cài đặt: ' . $e->getMessage()
            ]);
        }
    }
} 