<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['email', 'username', 'password_hash', 'role', 'status', 'last_login', 'created_by', 'deleted_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username,id,{id}]',
        'password_hash' => 'required',
    ];

    protected $validationMessages = [
        'email' => [
            'required' => 'Email là bắt buộc',
            'valid_email' => 'Email không hợp lệ',
            'is_unique' => 'Email này đã được sử dụng',
        ],
        'username' => [
            'required' => 'Tên đăng nhập là bắt buộc',
            'min_length' => 'Tên đăng nhập phải có ít nhất 3 ký tự',
            'max_length' => 'Tên đăng nhập không được vượt quá 30 ký tự',
            'is_unique' => 'Tên đăng nhập đã tồn tại',
        ],
        'password_hash' => [
            'required' => 'Mật khẩu là bắt buộc',
        ],
    ];

    public function findUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function findUserByUsername($username)
    {
        return $this->where('username', $username)->first();
    }

    public function createUser($data)
    {
        // Hash mật khẩu
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        
        // Xóa password gốc
        unset($data['password']);
        
        // Insert vào database
        return $this->insert($data);
    }

    public function updateUser($userId, $data)
    {
        // Temporarily disable validation
        $this->skipValidation = true;
        
        // Check email uniqueness manually (excluding current user)
        if (isset($data['email'])) {
            $emailExists = $this->where('email', $data['email'])
                              ->where('id !=', $userId)
                              ->countAllResults();
            if ($emailExists > 0) {
                return false; // Email already exists for another user
            }
        }
        
        // Check username uniqueness manually (excluding current user)
        if (isset($data['username'])) {
            $usernameExists = $this->where('username', $data['username'])
                                 ->where('id !=', $userId)
                                 ->countAllResults();
            if ($usernameExists > 0) {
                return false; // Username already exists for another user
            }
        }
        
        // Nếu có password mới, hash lại
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }

        return $this->update($userId, $data);
    }

    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}