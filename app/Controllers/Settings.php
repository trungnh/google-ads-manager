<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserSettingsModel;

class Settings extends BaseController
{
    protected $userSettingsModel;

    public function __construct()
    {
        $this->userSettingsModel = new UserSettingsModel();
    }

    public function index()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $userId = session()->get('id');
        
        // Lấy settings hiện tại của user
        $settings = $this->userSettingsModel->where('user_id', $userId)->first();
        
        $data = [
            'title' => 'User Settings',
            'settings' => $settings
        ];
        
        return view('settings/index', $data);
    }

    public function update()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $userId = session()->get('id');
        
        // Validate input
        $rules = [
            'mcc_id' => 'permit_empty|numeric',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $mccId = $this->request->getPost('mcc_id');
        
        // Kiểm tra xem có settings cho user này chưa
        $existingSettings = $this->userSettingsModel->where('user_id', $userId)->first();
        
        if ($existingSettings) {
            // Update settings
            $this->userSettingsModel->update($existingSettings['id'], [
                'mcc_id' => $mccId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Insert new settings
            $this->userSettingsModel->insert([
                'user_id' => $userId,
                'mcc_id' => $mccId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        return redirect()->to('/settings')->with('success', 'Settings updated successfully');
    }
}