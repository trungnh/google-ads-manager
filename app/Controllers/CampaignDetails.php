<?php

namespace App\Controllers;

use App\Models\AdsAccountModel;
use App\Models\GoogleTokenModel;
use App\Models\UserSettingsModel;
use App\Services\GoogleAdsServiceExtension;
use Exception;

/**
 * Class CampaignDetails
 * Controller để hiển thị thông tin chi tiết về chiến dịch, nhóm quảng cáo và quảng cáo
 */
class CampaignDetails extends BaseController
{
    protected $adsAccountModel;
    protected $googleAdsService;
    protected $googleTokenModel;
    protected $userSettingsModel;

    public function __construct()
    {
        $this->adsAccountModel = new AdsAccountModel();
        $this->googleAdsService = new GoogleAdsServiceExtension();
        $this->googleTokenModel = new GoogleTokenModel();
        $this->userSettingsModel = new UserSettingsModel();
    }

    /**
     * Hiển thị thông tin chi tiết về chiến dịch
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $campaignId ID của chiến dịch
     * @return mixed
     */
    public function campaign($customerId, $campaignId)
    {
        // 1. Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Vui lòng đăng nhập để tiếp tục');
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        try {
            // 2. Kiểm tra tài khoản hiện tại
            $account = $this->adsAccountModel
                ->where('user_id', $userId)
                ->where('customer_id', $customerId)
                ->first();

            // 3. Nếu không tìm thấy tài khoản hiện tại, tìm tài khoản đầu tiên của user
            if (!$account) {
                $firstAccount = $this->adsAccountModel
                    ->where('user_id', $userId)
                    ->first();

                if ($firstAccount) {
                    return redirect()->to('/campaigns/index/' . $firstAccount['customer_id']);
                }

                // Nếu không có tài khoản nào
                session()->setFlashdata('error', 'Bạn chưa có tài khoản Google Ads nào');
                return redirect()->to('/adsaccounts');
            }

            // 4. Lấy danh sách tất cả tài khoản của user để hiển thị trong dropdown
            $accounts = $this->adsAccountModel
                ->where('user_id', $userId)
                ->findAll();

            // 5. Lấy thông tin chi tiết về chiến dịch
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            $userSettings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $userSettings['mcc_id'] ?? null;

            $campaignDetails = $this->googleAdsService->getCampaignDetails(
                $customerId,
                $campaignId,
                $tokenData['access_token'],
                $mccId
            );

            // 6. Lấy danh sách nhóm quảng cáo hoặc asset groups tùy thuộc vào loại chiến dịch
            $isPerformanceMax = $campaignDetails['is_performance_max'] ?? false;
            
            if ($isPerformanceMax) {
                $assetGroups = $this->googleAdsService->getAssetGroups(
                    $customerId,
                    $campaignId,
                    $tokenData['access_token'],
                    $mccId
                );
                
                return view('campaign_details/performance_max_campaign', [
                    'account' => $account,
                    'accounts' => $accounts,
                    'campaignDetails' => $campaignDetails,
                    'assetGroups' => $assetGroups
                ]);
            } else {
                $adGroups = $this->googleAdsService->getAdGroups(
                    $customerId,
                    $campaignId,
                    $tokenData['access_token'],
                    $mccId
                );
                
                return view('campaign_details/campaign', [
                    'account' => $account,
                    'accounts' => $accounts,
                    'campaignDetails' => $campaignDetails,
                    'adGroups' => $adGroups
                ]);
            }
        } catch (Exception $e) {
            session()->setFlashdata('error', 'Lỗi khi lấy thông tin chiến dịch: ' . $e->getMessage());
            return redirect()->to('/campaigns/index/' . $customerId);
        }
    }

    /**
     * Hiển thị thông tin chi tiết về nhóm quảng cáo và danh sách quảng cáo
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $campaignId ID của chiến dịch
     * @param string $adGroupId ID của nhóm quảng cáo
     * @return mixed
     */
    public function adGroup($customerId, $campaignId, $adGroupId)
    {
        // 1. Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Vui lòng đăng nhập để tiếp tục');
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        try {
            // 2. Kiểm tra tài khoản hiện tại
            $account = $this->adsAccountModel
                ->where('user_id', $userId)
                ->where('customer_id', $customerId)
                ->first();

            // 3. Nếu không tìm thấy tài khoản hiện tại, tìm tài khoản đầu tiên của user
            if (!$account) {
                session()->setFlashdata('error', 'Không tìm thấy tài khoản');
                return redirect()->to('/campaigns');
            }

            // 4. Lấy danh sách tất cả tài khoản của user để hiển thị trong dropdown
            $accounts = $this->adsAccountModel
                ->where('user_id', $userId)
                ->findAll();

            // 5. Lấy thông tin chi tiết về chiến dịch và nhóm quảng cáo
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            $userSettings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $userSettings['mcc_id'] ?? null;

            $campaignDetails = $this->googleAdsService->getCampaignDetails(
                $customerId,
                $campaignId,
                $tokenData['access_token'],
                $mccId
            );

            // 6. Lấy danh sách quảng cáo của nhóm quảng cáo
            $ads = $this->googleAdsService->getAds(
                $customerId,
                $adGroupId,
                $tokenData['access_token'],
                $mccId
            );

            // 7. Lấy thông tin về nhóm quảng cáo
            $adGroups = $this->googleAdsService->getAdGroups(
                $customerId,
                $campaignId,
                $tokenData['access_token'],
                $mccId
            );

            $adGroupDetails = null;
            foreach ($adGroups as $adGroup) {
                if ($adGroup['ad_group_id'] == $adGroupId) {
                    $adGroupDetails = $adGroup;
                    break;
                }
            }

            if (!$adGroupDetails) {
                session()->setFlashdata('error', 'Không tìm thấy nhóm quảng cáo');
                return redirect()->to('/campaign-details/campaign/' . $customerId . '/' . $campaignId);
            }

            return view('campaign_details/ad_group', [
                'account' => $account,
                'accounts' => $accounts,
                'campaignDetails' => $campaignDetails,
                'adGroupDetails' => $adGroupDetails,
                'ads' => $ads
            ]);
        } catch (Exception $e) {
            session()->setFlashdata('error', 'Lỗi khi lấy thông tin nhóm quảng cáo: ' . $e->getMessage());
            return redirect()->to('/campaign-details/campaign/' . $customerId . '/' . $campaignId);
        }
    }

    /**
     * Hiển thị thông tin chi tiết về asset group và danh sách assets
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $campaignId ID của chiến dịch
     * @param string $assetGroupId ID của asset group
     * @return mixed
     */
    public function assetGroup($customerId, $campaignId, $assetGroupId)
    {
        // 1. Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Vui lòng đăng nhập để tiếp tục');
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        try {
            // 2. Kiểm tra tài khoản hiện tại
            $account = $this->adsAccountModel
                ->where('user_id', $userId)
                ->where('customer_id', $customerId)
                ->first();

            // 3. Nếu không tìm thấy tài khoản hiện tại, tìm tài khoản đầu tiên của user
            if (!$account) {
                session()->setFlashdata('error', 'Không tìm thấy tài khoản');
                return redirect()->to('/campaigns');
            }

            // 4. Lấy danh sách tất cả tài khoản của user để hiển thị trong dropdown
            $accounts = $this->adsAccountModel
                ->where('user_id', $userId)
                ->findAll();

            // 5. Lấy thông tin chi tiết về chiến dịch và asset group
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            $userSettings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $userSettings['mcc_id'] ?? null;

            $campaignDetails = $this->googleAdsService->getCampaignDetails(
                $customerId,
                $campaignId,
                $tokenData['access_token'],
                $mccId
            );

            // 6. Lấy danh sách assets của asset group
            $assets = $this->googleAdsService->getAssetGroupAssets(
                $customerId,
                $assetGroupId,
                $tokenData['access_token'],
                $mccId
            );

            // 7. Lấy thông tin về asset group
            $assetGroups = $this->googleAdsService->getAssetGroups(
                $customerId,
                $campaignId,
                $tokenData['access_token'],
                $mccId
            );

            $assetGroupDetails = null;
            foreach ($assetGroups as $assetGroup) {
                if ($assetGroup['asset_group_id'] == $assetGroupId) {
                    $assetGroupDetails = $assetGroup;
                    break;
                }
            }

            if (!$assetGroupDetails) {
                session()->setFlashdata('error', 'Không tìm thấy asset group');
                return redirect()->to('/campaign-details/campaign/' . $customerId . '/' . $campaignId);
            }

            return view('campaign_details/asset_group', [
                'account' => $account,
                'accounts' => $accounts,
                'campaignDetails' => $campaignDetails,
                'assetGroupDetails' => $assetGroupDetails,
                'assets' => $assets
            ]);
        } catch (Exception $e) {
            session()->setFlashdata('error', 'Lỗi khi lấy thông tin asset group: ' . $e->getMessage());
            return redirect()->to('/campaign-details/campaign/' . $customerId . '/' . $campaignId);
        }
    }

    /**
     * Hiển thị thông tin chi tiết về quảng cáo
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $adGroupId ID của nhóm quảng cáo
     * @param string $adId ID của quảng cáo
     * @return mixed
     */
    public function ad($customerId, $campaignId, $adGroupId, $adId)
    {
        // 1. Kiểm tra đăng nhập
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Vui lòng đăng nhập để tiếp tục');
            return redirect()->to('/login');
        }

        $userId = session()->get('id');

        try {
            // 2. Kiểm tra tài khoản hiện tại
            $account = $this->adsAccountModel
                ->where('user_id', $userId)
                ->where('customer_id', $customerId)
                ->first();

            // 3. Nếu không tìm thấy tài khoản hiện tại, tìm tài khoản đầu tiên của user
            if (!$account) {
                session()->setFlashdata('error', 'Không tìm thấy tài khoản');
                return redirect()->to('/campaigns');
            }

            // 4. Lấy danh sách tất cả tài khoản của user để hiển thị trong dropdown
            $accounts = $this->adsAccountModel
                ->where('user_id', $userId)
                ->findAll();

            // 5. Lấy thông tin chi tiết về chiến dịch và nhóm quảng cáo
            $tokenData = $this->googleTokenModel->getValidToken($userId);
            $userSettings = $this->userSettingsModel->where('user_id', $userId)->first();
            $mccId = $userSettings['mcc_id'] ?? null;

            $campaignDetails = $this->googleAdsService->getCampaignDetails(
                $customerId,
                $campaignId,
                $tokenData['access_token'],
                $mccId
            );

            // 6. Lấy danh sách quảng cáo của nhóm quảng cáo
            $ads = $this->googleAdsService->getAds(
                $customerId,
                $adGroupId,
                $tokenData['access_token'],
                $mccId
            );

            // 7. Lấy thông tin về nhóm quảng cáo
            $adGroups = $this->googleAdsService->getAdGroups(
                $customerId,
                $campaignId,
                $tokenData['access_token'],
                $mccId
            );

            $adGroupDetails = null;
            foreach ($adGroups as $adGroup) {
                if ($adGroup['ad_group_id'] == $adGroupId) {
                    $adGroupDetails = $adGroup;
                    break;
                }
            }

            // 8. Lấy thông tin chi tiết về quảng cáo
            $adDetails = null;
            foreach ($ads as $ad) {
                if ($ad['ad_id'] == $adId) {
                    $adDetails = $ad;
                    break;
                }
            }

            if (!$adDetails) {
                session()->setFlashdata('error', 'Không tìm thấy quảng cáo');
                return redirect()->to('/campaign-details/ad-group/' . $customerId . '/' . $campaignId . '/' . $adGroupId);
            }

            return view('campaign_details/ad', [
                'account' => $account,
                'accounts' => $accounts,
                'campaignDetails' => $campaignDetails,
                'adGroupDetails' => $adGroupDetails,
                'adDetails' => $adDetails
            ]);
        } catch (Exception $e) {
            session()->setFlashdata('error', 'Lỗi khi lấy thông tin quảng cáo: ' . $e->getMessage());
            return redirect()->to('/campaign-details/ad-group/' . $customerId . '/' . $campaignId . '/' . $adGroupId);
        }
    }
}