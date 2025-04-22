<?php

namespace App\Controllers;

use App\Models\OptimizeLogsModel;
use App\Models\UserModel;

class OptimizeLogs extends BaseController
{
    protected $optimizeLogsModel;
    protected $userModel;

    public function __construct()
    {
        $this->optimizeLogsModel = new OptimizeLogsModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Vui lòng đăng nhập để tiếp tục');
            return redirect()->to('/login');
        }

        $userId = session()->get('id');
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $startDate = $this->request->getGet('startDate');
        $endDate = $this->request->getGet('endDate');

        $dates = $this->optimizeLogsModel->getDistinctDates($userId);
        
        if ($startDate && $endDate) {
            $logs = $this->optimizeLogsModel->getLogsByDateRange($userId, $startDate, $endDate);
        } else {
            $logs = $this->optimizeLogsModel->getLogsByDate($userId, $date);
        }

        return view('optimize_logs/index', [
            'title' => 'Lịch sử tối ưu chiến dịch',
            'logs' => $logs,
            'dates' => $dates,
            'currentDate' => $date,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function view($uId)
    {
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Vui lòng đăng nhập để tiếp tục');
            return redirect()->to('/login');
        }

        // 1. Lấy danh sách tất cả user để hiển thị trong dropdown
        $users = $this->userModel->getAllActiveUsers();

        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $startDate = $this->request->getGet('startDate');
        $endDate = $this->request->getGet('endDate');

        $dates = $this->optimizeLogsModel->getDistinctDates($uId);
        
        if ($startDate && $endDate) {
            $logs = $this->optimizeLogsModel->getLogsByDateRange($uId, $startDate, $endDate);
        } else {
            $logs = $this->optimizeLogsModel->getLogsByDate($uId, $date);
        }

        return view('optimize_logs/view', [
            'title' => 'Lịch sử tối ưu chiến dịch',
            'uId' => $uId,
            'users' => $users,
            'logs' => $logs,
            'dates' => $dates,
            'currentDate' => $date,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
} 