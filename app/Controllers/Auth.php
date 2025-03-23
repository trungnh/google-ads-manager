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
            return redirect()->to('/home');
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
        return redirect()->to('/home');
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
     * Hiển thị form đăng ký
     */
    public function register()
    {
        // Nếu đã đăng nhập rồi thì chuyển hướng đến trang chủ
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/home');
        }
        
        return view('auth/register');
    }
    
    /**
     * Xử lý đăng ký
     */
    public function attemptRegister()
    {
        $rules = [
            'email' => 'required|valid_email|is_unique[users.email]',
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        try {
            $userModel = new UserModel();
        
            $userData = [
                'email' => $this->request->getPost('email'),
                'username' => $this->request->getPost('username'),
                'password' => $this->request->getPost('password'),
            ];
            
            $userId = $userModel->createUser($userData);
        } catch(Exception $e) {
            echo $e->getMessage();die;
        }
        
        
        if (!$userId) {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.');
        }
        
        // Lấy thông tin user vừa tạo
        $user = $userModel->find($userId);
        
        // Đăng nhập luôn sau khi đăng ký
        $this->setUserSession($user);
        
        // Chuyển hướng đến trang chủ
        return redirect()->to('/home')->with('success', 'Đăng ký thành công!');
    }
    
    /**
     * Lưu thông tin user vào session
     */
    private function setUserSession($user)
    {
        $data = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'isLoggedIn' => true,
        ];
        
        session()->set($data);
    }
}