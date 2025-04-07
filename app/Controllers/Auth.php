<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Auth extends Controller
{
    /**
     * Hiển thị form đăng nhập
     */
    public function login()
    {
        // Nếu đã đăng nhập rồi thì chuyển hướng đến trang chủ
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        
        return view('auth/login');
    }
    
    /**
     * Xử lý đăng nhập
     */
    public function attemptLogin()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[30]',
            'password' => 'required|min_length[8]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        
        $userModel = new UserModel();
        
        // Tìm user theo username
        $user = $userModel->findUserByUsername($username);
        
        // Nếu không tìm thấy, thử tìm theo email
        if (!$user) {
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $user = $userModel->findUserByEmail($username);
            }
        }
        
        // Nếu không tìm thấy user hoặc mật khẩu không đúng
        if (!$user || !$userModel->verifyPassword($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Username/Email hoặc mật khẩu không đúng');
        }
        
        // Đăng nhập thành công, lưu thông tin vào session
        $this->setUserSession($user);
        
        // Chuyển hướng đến trang chủ
        return redirect()->to('/dashboard');
    }
    
    /**
     * Đăng xuất
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
    
    /**
     * Lưu thông tin user vào session
     */
    private function setUserSession($user)
    {
        $data = [
            'id' => $user['id'],
            'user_id' => $user['id'], // Thêm user_id để tương thích với mã cũ
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'isLoggedIn' => true,
        ];
        
        session()->set($data);
        
        // Log thông tin đăng nhập
        log_message('info', 'User logged in: ' . $user['username'] . ' (ID: ' . $user['id'] . ', Role: ' . $user['role'] . ')');
    }
}