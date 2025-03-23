<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['email', 'username', 'password_hash'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[users.email]',
        'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
        'password' => 'required|min_length[8]',
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
        'password' => [
            'required' => 'Mật khẩu là bắt buộc',
            'min_length' => 'Mật khẩu phải có ít nhất 8 ký tự',
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
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);
        
        return $this->insert($data);
    }

    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}