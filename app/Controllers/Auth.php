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
     * Hiển thị form đăng ký
     */
    public function register()
    {
        // Nếu đã đăng nhập rồi thì chuyển hướng đến trang chủ
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
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
        
            // Lấy dữ liệu từ form
            $email = $this->request->getPost('email');
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            
            // Kiểm tra dữ liệu có tồn tại không
            if (empty($email) || empty($username) || empty($password)) {
                return redirect()->back()->withInput()->with('error', 'Vui lòng điền đầy đủ thông tin');
            }
            
            $userData = [
                'email' => $email,
                'username' => $username,
                'password' => $password
            ];
            
            // Log dữ liệu trước khi tạo user
            log_message('debug', 'Attempting to create user with data: ' . json_encode($userData));
            
            $userId = $userModel->createUser($userData);
            
            if (!$userId) {
                // Log lỗi validation nếu có
                if ($userModel->errors()) {
                    log_message('error', 'Validation errors: ' . json_encode($userModel->errors()));
                }
                return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi đăng ký: ' . implode(', ', $userModel->errors()));
            }
            
            // Lấy thông tin user vừa tạo
            $user = $userModel->find($userId);
            
            // Đăng nhập luôn sau khi đăng ký
            $this->setUserSession($user);
            
            // Chuyển hướng đến trang chủ
            return redirect()->to('/dashboard')->with('success', 'Đăng ký thành công!');
            
        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi đăng ký: ' . $e->getMessage());
        }
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