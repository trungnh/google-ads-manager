<?php

namespace App\Controllers;

use App\Models\OptimizeLogsModel;

class OptimizeLogs extends BaseController
{
    protected $optimizeLogsModel;

    public function __construct()
    {
        $this->optimizeLogsModel = new OptimizeLogsModel();
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
} 