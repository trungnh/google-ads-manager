<?php

namespace App\Controllers;

use App\Models\ReportsModel;
use App\Models\UserModel;
use App\Models\AdsAccountModel;

class Reports extends BaseController
{
    protected $reportsModel;
    protected $userModel;
    protected $adsAccountModel;

    public function __construct()
    {
        $this->reportsModel = new ReportsModel();
        $this->userModel = new UserModel();
        $this->adsAccountModel = new AdsAccountModel();
    }

    public function view($userId)
    {
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Vui lòng đăng nhập để tiếp tục');
            return redirect()->to('/login');
        }

        // 1. Lấy danh sách tất cả user để hiển thị trong dropdown
        // 4. Lấy danh sách tất cả tài khoản của user để hiển thị trong dropdown
        // 1. Lấy danh sách tất cả user để hiển thị trong dropdown
        $users = $this->userModel->getAllActiveUsers();

        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $startDate = $this->request->getGet('startDate');
        $endDate = $this->request->getGet('endDate');

        $dates = $this->reportsModel->getDistinctDates($userId);
        
        if ($startDate && $endDate) {
            $reports = $this->reportsModel->getReportsByDateRange($userId, $startDate, $endDate);
        } else {
            $reports = $this->reportsModel->getReportsByDate($userId, $date);
        }

        return view('reports/view', [
            'title' => 'Reports',
            'users' => $users,
            'userId' => $userId,
            'reports' => $reports,
            'dates' => $dates,
            'currentDate' => $date,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
} 