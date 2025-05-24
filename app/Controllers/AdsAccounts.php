<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AdsAccountModel;
use App\Models\AdsAccountSettingsModel;
use App\Models\UserModel;

class AdsAccounts extends BaseController
{
    protected $adsAccountModel;
    protected $adsAccountSettingsModel;
    protected $userModel;

    public function __construct()
    {
        $this->adsAccountModel = new AdsAccountModel();
        $this->adsAccountSettingsModel = new AdsAccountSettingsModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $userId = session()->get('id');
        
        // Lấy danh sách tài khoản ads
        $accounts = $this->adsAccountModel
            ->where('user_id', $userId)
            ->orderBy('order', 'ASC')
            ->findAll();
        
        $data = [
            'title' => 'Google Ads Accounts',
            'accounts' => $accounts
        ];
        
        return view('ads_accounts/index', $data);
    }

    public function adminList($userId)
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
        
        // Lấy danh sách tài khoản ads
        $accounts = $this->adsAccountModel
            ->where('user_id', $userId)
            ->orderBy('order', 'ASC')
            ->findAll();

        $users = $this->userModel->getAllActiveUsers();
        
        $data = [
            'title' => 'Google Ads Accounts',
            'accounts' => $accounts,
            'userId' => $userId,
            'users' => $users
        ];
        
        return view('ads_accounts/admin_view/index', $data);
    }

    public function create()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Thêm Ads Account'
        ];

        return view('ads_accounts/create', $data);
    }

    public function store()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        // Validation rules
        $rules = [
            'customer_id' => [
                'rules' => 'required|min_length[3]|is_unique[ads_accounts.customer_id]',
                'errors' => [
                    'required' => 'Customer ID không được để trống',
                    'min_length' => 'Customer ID phải có ít nhất {param} ký tự',
                    'is_unique' => 'Customer ID này đã tồn tại'
                ]
            ],
            'customer_name' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Tên account không được để trống',
                    'min_length' => 'Tên account phải có ít nhất {param} ký tự'
                ]
            ],
            'currency_code' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Mã tiền tệ không được để trống'
                ]
            ],
            'time_zone' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Múi giờ không được để trống'
                ]
            ],
            'status' => 'required|in_list[active,inactive]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Prepare data
        $data = [
            'user_id' => session()->get('id'),
            'customer_id' => $this->request->getPost('customer_id'),
            'customer_name' => $this->request->getPost('customer_name'),
            'currency_code' => $this->request->getPost('currency_code'),
            'time_zone' => $this->request->getPost('time_zone'),
            'status' => $this->request->getPost('status'),
            'order' => 0 // Default order
        ];

        // Save to database
        if ($this->adsAccountModel->insert($data)) {
            return redirect()->to('/ads-accounts')->with('success', 'Ads Account đã được thêm thành công');
        } else {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi thêm Ads Account');
        }
    }

    public function delete($id = null)
    {
        // Kiểm tra đăng nhập và quyền
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        // if (!in_array(session()->get('role'), ['superadmin', 'admin'])) {
        //     return $this->response->setJSON(['success' => false, 'message' => 'Permission denied']);
        // }

        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid account ID']);
        }

        try {
            // Xóa settings của account trước
            $this->adsAccountSettingsModel->where('account_id', $id)->delete();
            
            // Xóa account
            $this->adsAccountModel->delete($id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting account: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error deleting account'
            ]);
        }
    }
}