<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CampaignScheduleModel;
use App\Models\CampaignScheduleItemModel;
use App\Models\GoogleTokenModel;
use App\Models\AdsAccountModel;
use App\Services\GoogleAdsService;
use Exception;

class ExecuteCampaignSchedules extends BaseCommand
{
    protected $group = 'Ads';
    protected $name = 'ads:execute-schedules';
    protected $description = 'Execute scheduled campaign actions';

    protected $campaignScheduleModel;
    protected $campaignScheduleItemModel;
    protected $googleTokenModel;
    protected $adsAccountModel;
    protected $googleAdsService;

    public function __construct()
    {
        $this->campaignScheduleModel = new CampaignScheduleModel();
        $this->campaignScheduleItemModel = new CampaignScheduleItemModel();
        $this->googleTokenModel = new GoogleTokenModel();
        $this->adsAccountModel = new AdsAccountModel();
        $this->googleAdsService = new GoogleAdsService();
    }

    public function run(array $params)
    {
        try {
            // Get current time rounded to nearest 30 minutes
            $currentTime = new \DateTime();
            $minutes = (int) $currentTime->format('i');
            $roundedMinutes = round($minutes / 30) * 30;
            $currentTime->setTime($currentTime->format('H'), $roundedMinutes);
            
            CLI::write('Checking schedules for time: ' . $currentTime->format('H:i'), 'yellow');

            // Get active schedules for current time that haven't been executed today
            $todayDate = date('Y-m-d');
            $schedules = $this->campaignScheduleModel
                ->where('status', 'active')
                ->where('execution_time', $currentTime->format('H:i:s'))
                ->where("(last_executed_date IS NULL OR last_executed_date < '$todayDate')")
                ->findAll();

            if (empty($schedules)) {
                CLI::write('No schedules to execute at this time.', 'yellow');
                return;
            }

            foreach ($schedules as $schedule) {
                try {
                    CLI::write("Processing schedule ID: {$schedule['id']}", 'green');

                    // Get campaigns for this schedule
                    $scheduledCampaigns = $this->campaignScheduleItemModel->getCampaignsByScheduleId($schedule['id']);
                    if (empty($scheduledCampaigns)) {
                        CLI::write("No campaigns found for schedule ID: {$schedule['id']}", 'yellow');
                        continue;
                    }

                    // Get account info
                    $account = $this->adsAccountModel->where('customer_id', $schedule['customer_id'])->first();
                    if (!$account) {
                        throw new Exception("Account not found for customer ID: {$schedule['customer_id']}");
                    }

                    // Get valid token
                    $tokenData = $this->googleTokenModel->getValidToken($account['user_id']);
                    if (empty($tokenData) || empty($tokenData['access_token'])) {
                        throw new Exception("Valid token not found for account: {$schedule['customer_id']}");
                    }

                    // Process each campaign
                    foreach ($scheduledCampaigns as $campaign) {
                        try {
                            $status = $schedule['action_type'] === 'enable' ? 'ENABLED' : 'PAUSED';
                            
                            $this->googleAdsService->toggleCampaignStatus(
                                $tokenData['access_token'],
                                $schedule['customer_id'],
                                $campaign['campaign_id'],
                                $status
                            );
                            CLI::write("Successfully {$schedule['action_type']}d campaign: {$campaign['campaign_id']}", 'green');
                        } catch (Exception $e) {
                            CLI::error("Error processing campaign {$campaign['campaign_id']}: " . $e->getMessage());
                        }
                    }

                    // Update last executed date after successful execution
                    $this->campaignScheduleModel->update($schedule['id'], [
                        'last_executed_date' => date('Y-m-d')
                    ]);

                } catch (Exception $e) {
                    CLI::error("Error processing schedule {$schedule['id']}: " . $e->getMessage());
                }
            }

            CLI::write('Schedule execution completed', 'green');

        } catch (Exception $e) {
            CLI::error($e->getMessage());
        }
    }
}