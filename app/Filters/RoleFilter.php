<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Role filter - kiểm tra quyền người dùng dựa trên role
 */
class RoleFilter implements FilterInterface
{
    /**
     * Kiểm tra role người dùng trước khi cho phép truy cập
     *
     * @param RequestInterface $request
     * @param array|null       $arguments Các role được phép truy cập, phân cách bởi dấu phẩy
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Lấy role của người dùng từ session
        $userRole = session()->get('role');

        // Nếu không có role hoặc không có arguments
        if (!$userRole || empty($arguments)) {
            return redirect()->to('/dashboard')->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        // Kiểm tra role có trong danh sách cho phép không
        // $allowedRoles = explode(',', $arguments[0]);
        $allowedRoles = $arguments;
        if (!in_array($userRole, $allowedRoles)) {
            // Log hành động truy cập trái phép
            log_message('notice', '[Access Control] User ID ' . (session()->get('id') ?? 'unknown') . 
                       ' with role ' . $userRole . ' attempted to access a restricted area.');

            // Hiển thị thông báo lỗi và chuyển hướng
            return redirect()->to('/dashboard')->with('error', 'Bạn không có quyền truy cập trang này.');
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Không làm gì sau khi xử lý request
    }
} 