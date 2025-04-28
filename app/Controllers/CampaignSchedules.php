<?php

namespace App\Controllers;

use App\Models\CampaignScheduleModel;
use App\Models\CampaignScheduleItemModel;
use App\Models\CampaignsDataModel;
use App\Models\AdsAccountModel;
use App\Models\GoogleTokenModel;
use App\Models\UserSettingsModel;
use App\Services\GoogleAdsService;
use Exception;

class CampaignSchedules extends BaseController
{
    protected $campaignScheduleModel;
    protected $campaignScheduleItemModel;
    protected $campaignsDataModel;
    protected $adsAccountModel;
    protected $googleAdsService;
    protected $googleTokenModel;
    protected $userSettingsModel;

    public function __construct()
    {
        $this->campaignScheduleModel = new CampaignScheduleModel();
        $this->campaignScheduleItemModel = new CampaignScheduleItemModel();
        $this->campaignsDataModel = new CampaignsDataModel();
        $this->adsAccountModel = new AdsAccountModel();
        $this->googleAdsService = new GoogleAdsService();
        $this->googleTokenModel = new GoogleTokenModel();
        $this->userSettingsModel = new UserSettingsModel();
    }

    private function getCampaignsFromGoogleAds($customerId) 
    {
        try {
            $userId = session()->get('id');

            // Get access token
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            if (empty($tokenData) || empty($tokenData['access_token'])) {
                throw new Exception('Bạn cần kết nối lại với Google Ads');
            }

            // Get MCC ID from settings
            $userSettings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $userSettings['mcc_id'] ?? null;

            // Get campaigns from Google Ads API, including both active and paused
            $campaigns = $this->googleAdsService->getCampaigns(
                $customerId,
                $tokenData['access_token'],
                $mccId,
                true, // showPaused = true to get all campaigns
                date('Y-m-d'),
                date('Y-m-d')
            );

            return $campaigns;

        } catch (Exception $e) {
            log_message('error', 'Error getting campaigns from Google Ads: ' . $e->getMessage());
            throw $e;
        }
    }

    public function index($customerId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        // Lấy danh sách tất cả tài khoản của user để hiển thị trong dropdown
        $accounts = $this->adsAccountModel
                ->where('user_id', $userId)
                ->orderBy('order', 'ASC')
                ->findAll();

        try {
            $schedules = $this->campaignScheduleModel->where('customer_id', $customerId)->findAll();
            $account = $this->adsAccountModel->where('customer_id', $customerId)->first();

            if (!$account) {
                throw new Exception('Tài khoản không tồn tại');
            }

            return view('campaign_schedules/index', [
                'title' => 'Campaign Schedules - ' . $account['customer_name'],
                'schedules' => $schedules,
                'customerId' => $customerId,
                'accounts' => $accounts
            ]);

        } catch (Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/campaigns/index/' . $customerId);
        }
    }

    public function create($customerId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            try {
                // Validate input
                $rules = [
                    'action_type' => 'required|in_list[enable,disable]',
                    'execution_time' => 'required',
                    'campaign_ids' => 'required'
                ];

                if (!$this->validate($rules)) {
                    return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
                }

                // Create schedule
                $scheduleId = $this->campaignScheduleModel->insert([
                    'customer_id' => $customerId,
                    'action_type' => $this->request->getPost('action_type'),
                    'execution_time' => $this->request->getPost('execution_time'),
                    'status' => 'active'
                ]);

                // Add campaign assignments
                foreach ($this->request->getPost('campaign_ids') as $campaignId) {
                    $this->campaignScheduleItemModel->insert([
                        'schedule_id' => $scheduleId,
                        'campaign_id' => $campaignId
                    ]);
                }

                session()->setFlashdata('success', 'Schedule created successfully');
                return redirect()->to('campaignschedules/' . $customerId);

            } catch (Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        try {
            $account = $this->adsAccountModel->where('customer_id', $customerId)->first();
            if (!$account) {
                throw new Exception('Tài khoản không tồn tại');
            }

            // Get campaigns from Google Ads
            $campaigns = $this->getCampaignsFromGoogleAds($customerId);

            return view('campaign_schedules/create', [
                'title' => 'Create Schedule - ' . $account['customer_name'],
                'customerId' => $customerId,
                'campaigns' => $campaigns
            ]);

        } catch (Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('campaignschedules/' . $customerId);
        }
    }

    public function edit($customerId, $scheduleId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            try {
                // Validate input
                $rules = [
                    'action_type' => 'required|in_list[enable,disable]',
                    'execution_time' => 'required',
                    'status' => 'required|in_list[active,inactive]',
                    'campaign_ids' => 'required'
                ];

                if (!$this->validate($rules)) {
                    return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
                }

                // Update schedule
                $this->campaignScheduleModel->update($scheduleId, [
                    'action_type' => $this->request->getPost('action_type'),
                    'execution_time' => $this->request->getPost('execution_time'),
                    'status' => $this->request->getPost('status')
                ]);

                // Update campaign assignments
                $this->campaignScheduleItemModel->where('schedule_id', $scheduleId)->delete();
                foreach ($this->request->getPost('campaign_ids') as $campaignId) {
                    $this->campaignScheduleItemModel->insert([
                        'schedule_id' => $scheduleId,
                        'campaign_id' => $campaignId
                    ]);
                }

                session()->setFlashdata('success', 'Schedule updated successfully');
                return redirect()->to('campaignschedules/' . $customerId);

            } catch (Exception $e) {
                session()->setFlashdata('error', $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        try {
            $account = $this->adsAccountModel->where('customer_id', $customerId)->first();
            $schedule = $this->campaignScheduleModel->find($scheduleId);
            
            if (!$account) {
                throw new Exception('Tài khoản không tồn tại');
            }
            
            if (!$schedule || $schedule['customer_id'] != $customerId) {
                throw new Exception('Schedule không tồn tại');
            }

            // Get campaigns from Google Ads
            $campaigns = $this->getCampaignsFromGoogleAds($customerId);
            
            // Get scheduled campaigns
            $scheduledCampaigns = $this->campaignScheduleItemModel->getCampaignsByScheduleId($scheduleId);

            return view('campaign_schedules/edit', [
                'title' => 'Edit Schedule - ' . $account['customer_name'],
                'customerId' => $customerId,
                'schedule' => $schedule,
                'campaigns' => $campaigns,
                'scheduledCampaigns' => $scheduledCampaigns
            ]);

        } catch (Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('campaignschedules/' . $customerId);
        }
    }

    public function delete($customerId, $scheduleId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        try {
            $schedule = $this->campaignScheduleModel->find($scheduleId);
            
            if (!$schedule || $schedule['customer_id'] != $customerId) {
                throw new Exception('Schedule không tồn tại');
            }

            // Delete schedule and its campaign assignments
            $this->campaignScheduleModel->delete($scheduleId);
            $this->campaignScheduleItemModel->where('schedule_id', $scheduleId)->delete();

            session()->setFlashdata('success', 'Schedule deleted successfully');

        } catch (Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
        }

        return redirect()->to('campaignschedules/' . $customerId);
    }
}