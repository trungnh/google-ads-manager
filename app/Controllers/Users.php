<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Hiển thị danh sách người dùng
     */
    public function index()
    {
        $data = [
            'title' => 'Quản lý người dùng',
            'users' => $this->userModel->findAll()
        ];
        
        return view('users/index', $data);
    }

    /**
     * Hiển thị form tạo người dùng mới
     */
    public function create()
    {
        $data = [
            'title' => 'Tạo người dùng mới',
            'validation' => null
        ];
        
        return view('users/create', $data);
    }

    /**
     * Xử lý lưu người dùng mới
     */
    public function store()
    {
        // Kiểm tra dữ liệu nhập vào
        $rules = [
            'email' => 'required|valid_email|is_unique[users.email]',
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'role' => 'required|in_list[superadmin,admin,user]'
        ];
        
        if (!$this->validate($rules)) {
            $data = [
                'title' => 'Tạo người dùng mới',
                'validation' => $this->validator
            ];
            
            return view('users/create', $data);
        }
        
        // Lấy dữ liệu từ form
        $email = $this->request->getPost('email');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $role = $this->request->getPost('role');
        
        $userData = [
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'role' => $role
        ];
        
        // Lấy ID người dùng đang đăng nhập
        $createdBy = session()->get('id');
        
        // Tạo người dùng mới
        $userId = $this->userModel->createUser($userData, $createdBy);
        
        if (!$userId) {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo người dùng: ' . implode(', ', $this->userModel->errors()));
        }
        
        // Chuyển hướng về trang danh sách người dùng
        return redirect()->to('/users')->with('success', 'Người dùng mới đã được tạo thành công.');
    }

    /**
     * Hiển thị form sửa thông tin người dùng
     */
    public function edit($id)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Không tìm thấy người dùng.');
        }
        
        $data = [
            'title' => 'Sửa thông tin người dùng',
            'user' => $user,
            'validation' => null
        ];
        
        return view('users/edit', $data);
    }

    /**
     * Xử lý cập nhật thông tin người dùng
     */
    public function update($id)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Không tìm thấy người dùng.');
        }
        
        // Kiểm tra dữ liệu nhập vào
        // $rules = [
        //     'email' => 'required|valid_email|is_unique[users.email,id,'.$id.']',
        //     'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username,id,'.$id.']',
        //     'role' => 'required|in_list[superadmin,admin,user]'
        // ];
        
        // Nếu có nhập mật khẩu mới
        if ($this->request->getPost('password')) {
            $rules['password'] = 'required|min_length[8]';
            $rules['password_confirm'] = 'required|matches[password]';
        }
        
        // if (!$this->validate($rules)) {
        //     $data = [
        //         'title' => 'Sửa thông tin người dùng',
        //         'user' => $user,
        //         'validation' => $this->validator
        //     ];
            
        //     return view('users/edit', $data);
        // }
        // Lấy dữ liệu từ form
        $userData = [
            'email' => $this->request->getPost('email'),
            'username' => $this->request->getPost('username'),
            'role' => $this->request->getPost('role')
        ];
        
        // Nếu có nhập mật khẩu mới
        if ($this->request->getPost('password')) {
            $userData['password'] = $this->request->getPost('password');
        }
        
        // Cập nhật thông tin người dùng
        $updated = $this->userModel->updateUser($id, $userData);
        
        if (!$updated) {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật thông tin người dùng: ' . implode(', ', $this->userModel->errors()));
        }
        
        // Chuyển hướng về trang danh sách người dùng
        return redirect()->to('/users')->with('success', 'Thông tin người dùng đã được cập nhật thành công.');
    }

    /**
     * Xử lý xóa người dùng
     */
    public function delete($id)
    {
        // Không cho phép xóa chính mình
        if ($id == session()->get('id')) {
            return redirect()->to('/users')->with('error', 'Bạn không thể xóa tài khoản của chính mình.');
        }
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Không tìm thấy người dùng.');
        }
        
        // Xóa người dùng
        $deleted = $this->userModel->delete($id);
        
        if (!$deleted) {
            return redirect()->to('/users')->with('error', 'Có lỗi xảy ra khi xóa người dùng.');
        }
        
        // Chuyển hướng về trang danh sách người dùng
        return redirect()->to('/users')->with('success', 'Người dùng đã được xóa thành công.');
    }
} 