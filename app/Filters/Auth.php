<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Nếu người dùng chưa đăng nhập
        if (!session()->get('isLoggedIn')) {
            // Lưu URL hiện tại để chuyển hướng trở lại sau khi đăng nhập
            session()->set('redirect_url', current_url());
            
            // Chuyển hướng đến trang đăng nhập
            return redirect()->to('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Không làm gì sau khi xử lý request
    }
}