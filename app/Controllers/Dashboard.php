<?php

namespace App\Controllers;

use App\Models\GoogleTokenModel;
use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        // Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $userId = session()->get('id');
        $googleTokenModel = new GoogleTokenModel();
        $token = $googleTokenModel->getValidToken($userId);
        
        $data = [
            'hasGoogleToken' => !empty($token),
        ];
        
        return view('dashboard/index', $data);
    }
}