<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Migrate extends Controller
{
    public function index()
    {
        die('Không có gì đâu. Bỏ qua đi');
        // Chỉ cho phép chạy trong môi trường development và local
        if (ENVIRONMENT !== 'development' && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
            die('Chỉ có thể chạy migration trong môi trường development hoặc từ localhost');
        }

        // Load migration library
        $migrate = \Config\Services::migrations();

        try {
            // Chạy tất cả migration
            $migrate->latest();
            
            echo '<h1>Migration đã chạy thành công!</h1>';
            echo '<p>Đã thêm các trường role, status và các trường khác vào bảng users.</p>';
            echo '<p>Người dùng đầu tiên đã được gán vai trò superadmin, các người dùng khác được gán vai trò admin.</p>';
            echo '<p><a href="' . base_url() . '">Về trang chủ</a></p>';
        } catch (\Exception $e) {
            echo '<h1>Migration gặp lỗi!</h1>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo '<p><a href="' . base_url() . '">Về trang chủ</a></p>';
        }
    }
} 