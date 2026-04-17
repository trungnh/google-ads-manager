<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class ExpireFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Nếu người dùng đã đăng nhập
        if (session()->get('isLoggedIn')) {
            $role = session()->get('role');
            
            // Chỉ kiểm tra đối với user không phải superadmin
            if ($role !== 'superadmin') {
                $expireDate = session()->get('expire_date');
                if (!$expireDate) {
                    $userId = session()->get('id');
                    $userModel = new UserModel();
                    $user = $userModel->find($userId);
                    $expireDate = $user['expire_date'] ?? null;
                    if ($expireDate) {
                        session()->set('expire_date', $expireDate);
                    }
                }
                
                if ($expireDate) {
                    $expireTime = strtotime($expireDate);
                    $now = time();
                    
                    if ($now > $expireTime) {
                        // Nếu đã hết hạn, logout và chuyển hướng
                        $uri = $request->getUri()->getPath();
                        if ($uri !== 'login' && $uri !== 'expired') {
                            // Xóa session để user không thể tiếp tục dùng
                            session()->destroy();
                            return redirect()->to('/login')->with('error', 'Tài khoản của bạn đã hết hạn. Vui lòng liên hệ quản trị viên để gia hạn.');
                        }
                    }
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Không làm gì sau khi xử lý request
    }
}
