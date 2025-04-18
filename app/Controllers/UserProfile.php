<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserProfile extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login')->with('error', 'Vui lòng đăng nhập để truy cập trang này');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        return view('profile/index', ['user' => $user]);
    }

    public function update()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login')->with('error', 'Vui lòng đăng nhập để truy cập trang này');
        }

        $userId = session()->get('user_id');

        // Validate input
        $rules = [
            'current_password' => 'required|min_length[6]',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        // Kiểm tra mật khẩu hiện tại
        $user = $this->userModel->find($userId);
        if (!$this->userModel->verifyPassword($currentPassword, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Mật khẩu hiện tại không đúng');
        }

        // Cập nhật mật khẩu
        $data = ['password' => $newPassword];
        if ($this->userModel->updateUser($userId, $data)) {
            return redirect()->to('/profile')->with('success', 'Cập nhật mật khẩu thành công');
        } else {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật mật khẩu');
        }
    }
}