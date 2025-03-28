<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class PublicPages extends BaseController
{
    public function index()
    {
        return view('home');
    }

    public function privacyPolicy()
    {
        return view('privacy_policy', [
            'title' => 'Chính sách bảo mật'
        ]);
    }

    public function terms()
    {
        return view('terms', [
            'title' => 'Điều khoản dịch vụ'
        ]);
    }
} 