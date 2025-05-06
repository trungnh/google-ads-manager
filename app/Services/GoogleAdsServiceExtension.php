<?php

namespace App\Services;

use Exception;

/**
 * Class GoogleAdsServiceExtension
 * Mở rộng chức năng của GoogleAdsService để lấy thông tin chi tiết về chiến dịch, nhóm quảng cáo và quảng cáo
 */
class GoogleAdsServiceExtension extends GoogleAdsService
{
    /**
     * Lấy thông tin chi tiết về cài đặt của chiến dịch
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $campaignId ID của chiến dịch
     * @param string $accessToken Access token
     * @param string|null $mccId ID của tài khoản MCC (nếu có)
     * @return array Thông tin chi tiết về chiến dịch
     */
    public function getCampaignDetails($customerId, $campaignId, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                campaign.id,
                campaign.name,
                campaign.status,
                campaign.advertising_channel_type,
                campaign.advertising_channel_sub_type,
                campaign.bidding_strategy_type,
                campaign.target_cpa.target_cpa_micros,
                campaign.target_roas.target_roas,
                campaign.maximize_conversions.target_cpa_micros,
                campaign.maximize_conversion_value.target_roas,
                campaign.manual_cpm,
                campaign.start_date,
                campaign.end_date,
                campaign.serving_status,
                campaign.experiment_type,
                campaign.base_campaign,
                campaign.selective_optimization.conversion_actions,
                campaign_budget.amount_micros,
                campaign_budget.delivery_method,
                campaign_budget.explicitly_shared,
                campaign_budget.period,
                campaign_budget.status,
                bidding_strategy.type,
                bidding_strategy.name,
                bidding_strategy.target_cpa.target_cpa_micros,
                bidding_strategy.target_roas.target_roas,
                bidding_strategy.maximize_conversion_value.target_roas,
                metrics.cost_micros,
                metrics.conversions,
                metrics.conversions_value,
                metrics.cost_per_conversion,
                metrics.conversions_from_interactions_rate,
                metrics.average_cpc,
                metrics.ctr,
                metrics.clicks,
                metrics.impressions
            FROM campaign
            WHERE campaign.id = {$campaignId}" . 
            " AND segments.date BETWEEN '".date('Y-m-d')."' AND '".date('Y-m-d')."'";
        
        $data = [
            'query' => $query
        ];

        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            $campaignDetails = [];

            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results']) && !empty($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            if (!isset($result['campaign'])) {
                                continue;
                            }

                            $campaign = $result['campaign'];
                            $metrics = $result['metrics'] ?? [];
                            $budget = $result['campaignBudget'] ?? null;
                            $biddingStrategy = $result['biddingStrategy'] ?? null;
                            
                            // Xác định target CPA và ROAS dựa trên chiến lược đặt giá thầu
                            $targetCpa = null;
                            $targetRoas = null;
                            
                            $biddingStrategyType = $campaign['biddingStrategyType'] ?? '';
                            
                            // Lấy CPA mục tiêu từ các loại chiến lược đặt giá thầu khác nhau
                            if (isset($campaign['maximizeConversions']['targetCpaMicros']) && $campaign['maximizeConversions']['targetCpaMicros'] > 0) {
                                $targetCpa = $this->microToStandard($campaign['maximizeConversions']['targetCpaMicros']);
                            } elseif (isset($campaign['targetCpa']['targetCpaMicros']) && $campaign['targetCpa']['targetCpaMicros'] > 0) {
                                $targetCpa = $this->microToStandard($campaign['targetCpa']['targetCpaMicros']);
                            } elseif (isset($biddingStrategy['targetCpa']['targetCpaMicros']) && $biddingStrategy['targetCpa']['targetCpaMicros'] > 0) {
                                $targetCpa = $this->microToStandard($biddingStrategy['targetCpa']['targetCpaMicros']);
                            }
                            
                            // Lấy ROAS mục tiêu từ các loại chiến lược đặt giá thầu khác nhau
                            if (isset($campaign['maximizeConversionValue']['targetRoas']) && $campaign['maximizeConversionValue']['targetRoas'] > 0) {
                                $targetRoas = $campaign['maximizeConversionValue']['targetRoas'];
                            } elseif (isset($campaign['targetRoas']['targetRoas']) && $campaign['targetRoas']['targetRoas'] > 0) {
                                $targetRoas = $campaign['targetRoas']['targetRoas'];
                            } elseif (isset($biddingStrategy['targetRoas']['targetRoas']) && $biddingStrategy['targetRoas']['targetRoas'] > 0) {
                                $targetRoas = $biddingStrategy['targetRoas']['targetRoas'];
                            } elseif (isset($biddingStrategy['maximizeConversionValue']['targetRoas']) && $biddingStrategy['maximizeConversionValue']['targetRoas'] > 0) {
                                $targetRoas = $biddingStrategy['maximizeConversionValue']['targetRoas'];
                            }
                            
                            $campaignDetails = [
                                'campaign_id' => $campaign['id'],
                                'name' => $campaign['name'],
                                'status' => $campaign['status'],
                                'advertising_channel_type' => $campaign['advertisingChannelType'] ?? '',
                                'advertising_channel_sub_type' => $campaign['advertisingChannelSubType'] ?? '',
                                'bidding_strategy_type' => $biddingStrategyType,
                                'bidding_strategy_name' => $biddingStrategy['name'] ?? '',
                                'budget' => $budget ? $this->microToStandard($budget['amountMicros']) : 0,
                                'budget_delivery_method' => $budget['deliveryMethod'] ?? '',
                                'budget_explicitly_shared' => $budget['explicitlyShared'] ?? false,
                                'budget_period' => $budget['period'] ?? '',
                                'budget_status' => $budget['status'] ?? '',
                                'start_date' => $campaign['startDate'] ?? '',
                                'end_date' => $campaign['endDate'] ?? '',
                                'serving_status' => $campaign['servingStatus'] ?? '',
                                'experiment_type' => $campaign['experimentType'] ?? '',
                                'target_cpa' => $targetCpa,
                                'target_roas' => $targetRoas,
                                'cost' => isset($metrics['costMicros']) ? $this->microToStandard($metrics['costMicros']) : 0,
                                'conversions' => $metrics['conversions'] ?? 0,
                                'conversion_value' => $metrics['conversionsValue'] ?? 0,
                                'cost_per_conversion' => isset($metrics['costPerConversion']) ? $this->microToStandard($metrics['costPerConversion']) : 0,
                                'conversion_rate' => $metrics['conversionsFromInteractionsRate'] ?? 0,
                                'ctr' => $metrics['ctr'] ?? 0,
                                'clicks' => $metrics['clicks'] ?? 0,
                                'impressions' => $metrics['impressions'] ?? 0,
                                'average_cpc' => isset($metrics['averageCpc']) ? $this->microToStandard($metrics['averageCpc']) : 0,
                                'customer_id' => $customerId,
                                'is_performance_max' => ($campaign['advertisingChannelType'] ?? '') === 'PERFORMANCE_MAX'
                            ];
                        }
                    }
                }
            }

            return $campaignDetails;
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsServiceExtension::getCampaignDetails: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy danh sách nhóm quảng cáo của một chiến dịch
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $campaignId ID của chiến dịch
     * @param string $accessToken Access token
     * @param string|null $mccId ID của tài khoản MCC (nếu có)
     * @return array Danh sách nhóm quảng cáo
     */
    public function getAdGroups($customerId, $campaignId, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                ad_group.id,
                ad_group.name,
                ad_group.status,
                ad_group.type,
                ad_group.campaign,
                ad_group.cpc_bid_micros,
                ad_group.cpm_bid_micros,
                ad_group.target_cpa_micros,
                ad_group.target_roas,
                metrics.cost_micros,
                metrics.conversions,
                metrics.conversions_value,
                metrics.cost_per_conversion,
                metrics.conversions_from_interactions_rate,
                metrics.average_cpc,
                metrics.ctr,
                metrics.clicks,
                metrics.impressions
            FROM ad_group
            WHERE ad_group.campaign = 'customers/{$formattedCustomerId}/campaigns/{$campaignId}'". 
            " AND segments.date BETWEEN '".date('Y-m-d')."' AND '".date('Y-m-d')."'";
        
        $data = [
            'query' => $query
        ];

        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            $adGroups = [];

            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            if (!isset($result['adGroup'])) {
                                continue;
                            }

                            $adGroup = $result['adGroup'];
                            $metrics = $result['metrics'] ?? [];
                            
                            $adGroups[] = [
                                'ad_group_id' => $adGroup['id'],
                                'name' => $adGroup['name'],
                                'status' => $adGroup['status'],
                                'type' => $adGroup['type'] ?? '',
                                'cpc_bid' => isset($adGroup['cpcBidMicros']) ? $this->microToStandard($adGroup['cpcBidMicros']) : 0,
                                'cpm_bid' => isset($adGroup['cpmBidMicros']) ? $this->microToStandard($adGroup['cpmBidMicros']) : 0,
                                'target_cpa' => isset($adGroup['targetCpaMicros']) ? $this->microToStandard($adGroup['targetCpaMicros']) : 0,
                                'target_roas' => $adGroup['targetRoas'] ?? 0,
                                'cost' => isset($metrics['costMicros']) ? $this->microToStandard($metrics['costMicros']) : 0,
                                'conversions' => $metrics['conversions'] ?? 0,
                                'conversion_value' => $metrics['conversionsValue'] ?? 0,
                                'cost_per_conversion' => isset($metrics['costPerConversion']) ? $this->microToStandard($metrics['costPerConversion']) : 0,
                                'conversion_rate' => $metrics['conversionsFromInteractionsRate'] ?? 0,
                                'ctr' => $metrics['ctr'] ?? 0,
                                'clicks' => $metrics['clicks'] ?? 0,
                                'impressions' => $metrics['impressions'] ?? 0,
                                'average_cpc' => isset($metrics['averageCpc']) ? $this->microToStandard($metrics['averageCpc']) : 0,
                                'campaign_id' => $campaignId,
                                'customer_id' => $customerId
                            ];
                        }
                    }
                }
            }

            return $adGroups;
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsServiceExtension::getAdGroups: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy danh sách quảng cáo của một nhóm quảng cáo
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $adGroupId ID của nhóm quảng cáo
     * @param string $accessToken Access token
     * @param string|null $mccId ID của tài khoản MCC (nếu có)
     * @return array Danh sách quảng cáo
     */
    public function getAds($customerId, $adGroupId, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                ad_group_ad.ad.id,
                ad_group_ad.ad.name,
                ad_group_ad.ad.type,
                ad_group_ad.ad.final_urls,
                ad_group_ad.ad.display_url,
                ad_group_ad.ad.text_ad.headline,
                ad_group_ad.ad.text_ad.description1,
                ad_group_ad.ad.text_ad.description2,
                ad_group_ad.ad.expanded_text_ad.headline_part1,
                ad_group_ad.ad.expanded_text_ad.headline_part2,
                ad_group_ad.ad.expanded_text_ad.headline_part3,
                ad_group_ad.ad.expanded_text_ad.description,
                ad_group_ad.ad.expanded_text_ad.description2,
                ad_group_ad.ad.responsive_search_ad.headlines,
                ad_group_ad.ad.responsive_search_ad.descriptions,
                ad_group_ad.ad.image_ad.image_url,
                ad_group_ad.ad.image_ad.pixel_width,
                ad_group_ad.ad.image_ad.pixel_height,
                ad_group_ad.ad.image_ad.mime_type,
                ad_group_ad.ad.image_ad.name,
                ad_group_ad.ad.demand_gen_video_responsive_ad.headlines,
                ad_group_ad.ad.demand_gen_video_responsive_ad.descriptions,
                ad_group_ad.ad.demand_gen_video_responsive_ad.videos,
                ad_group_ad.status,
                ad_group_ad.ad_group,
                metrics.cost_micros,
                metrics.conversions,
                metrics.conversions_value,
                metrics.cost_per_conversion,
                metrics.conversions_from_interactions_rate,
                metrics.average_cpc,
                metrics.ctr,
                metrics.clicks,
                metrics.impressions
            FROM ad_group_ad
            WHERE ad_group_ad.ad_group = 'customers/{$formattedCustomerId}/adGroups/{$adGroupId}'". 
            " AND segments.date BETWEEN '".date('Y-m-d')."' AND '".date('Y-m-d')."'";
        $data = [
            'query' => $query
        ];

        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            $ads = [];

            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            if (!isset($result['adGroupAd']) || !isset($result['adGroupAd']['ad'])) {
                                continue;
                            }

                            $adGroupAd = $result['adGroupAd'];
                            $ad = $adGroupAd['ad'];
                            $metrics = $result['metrics'] ?? [];
                            log_message('debug', 'Ad asset structure: ' . json_encode($ad));
                            log_message('debug', 'Ad metrics structure: ' . json_encode($metrics));
                            
                            // Xử lý các loại quảng cáo khác nhau
                            $adDetails = [
                                'ad_id' => $ad['id'],
                                'name' => $ad['name'] ?? '',
                                'type' => $ad['type'] ?? '',
                                'status' => $adGroupAd['status'] ?? '',
                                'final_urls' => $ad['finalUrls'] ?? [],
                                'display_url' => $ad['displayUrl'] ?? '',
                                'cost' => isset($metrics['costMicros']) ? $this->microToStandard($metrics['costMicros']) : 0,
                                'conversions' => $metrics['conversions'] ?? 0,
                                'conversion_value' => $metrics['conversionsValue'] ?? 0,
                                'cost_per_conversion' => isset($metrics['costPerConversion']) ? $this->microToStandard($metrics['costPerConversion']) : 0,
                                'conversion_rate' => $metrics['conversionsFromInteractionsRate'] ?? 0,
                                'ctr' => $metrics['ctr'] ?? 0,
                                'clicks' => $metrics['clicks'] ?? 0,
                                'impressions' => $metrics['impressions'] ?? 0,
                                'average_cpc' => isset($metrics['averageCpc']) ? $this->microToStandard($metrics['averageCpc']) : 0,
                                'ad_group_id' => $adGroupId,
                                'customer_id' => $customerId
                            ];
                            
                            // Thêm thông tin chi tiết dựa trên loại quảng cáo
                            if (isset($ad['textAd'])) {
                                $adDetails['headline'] = $ad['textAd']['headline'] ?? '';
                                $adDetails['description1'] = $ad['textAd']['description1'] ?? '';
                                $adDetails['description2'] = $ad['textAd']['description2'] ?? '';
                            } elseif (isset($ad['expandedTextAd'])) {
                                $adDetails['headline_part1'] = $ad['expandedTextAd']['headlinePart1'] ?? '';
                                $adDetails['headline_part2'] = $ad['expandedTextAd']['headlinePart2'] ?? '';
                                $adDetails['headline_part3'] = $ad['expandedTextAd']['headlinePart3'] ?? '';
                                $adDetails['description'] = $ad['expandedTextAd']['description'] ?? '';
                                $adDetails['description2'] = $ad['expandedTextAd']['description2'] ?? '';
                            } elseif (isset($ad['responsiveSearchAd'])) {
                                $adDetails['headlines'] = $ad['responsiveSearchAd']['headlines'] ?? [];
                                $adDetails['descriptions'] = $ad['responsiveSearchAd']['descriptions'] ?? [];
                            } elseif (isset($ad['imageAd'])) {
                                $adDetails['image_url'] = $ad['imageAd']['imageUrl'] ?? '';
                                $adDetails['pixel_width'] = $ad['imageAd']['pixelWidth'] ?? 0;
                                $adDetails['pixel_height'] = $ad['imageAd']['pixelHeight'] ?? 0;
                                $adDetails['mime_type'] = $ad['imageAd']['mimeType'] ?? '';
                                $adDetails['image_name'] = $ad['imageAd']['name'] ?? '';
                            } elseif (isset($ad['videoAd'])) {
                                // Chỉ lưu thông tin cơ bản về video ad vì các trường chi tiết không được hỗ trợ trong API v19
                                $adDetails['video_ad'] = true;
                                // Nếu cần thông tin chi tiết về video, cần sử dụng API khác hoặc trường được hỗ trợ
                            } elseif (isset($ad['demandGenVideoResponsiveAd'])) {
                                // Xử lý quảng cáo DEMAND_GEN_VIDEO_RESPONSIVE_AD
                                $adDetails['headlines'] = $ad['demandGenVideoResponsiveAd']['headlines'] ?? [];
                                $adDetails['descriptions'] = $ad['demandGenVideoResponsiveAd']['descriptions'] ?? [];
                                
                                // Debug: Ghi log toàn bộ cấu trúc demandGenVideoResponsiveAd để phân tích
                                log_message('debug', 'DEMAND_GEN_VIDEO_RESPONSIVE_AD structure: ' . json_encode($ad['demandGenVideoResponsiveAd']));
                                
                                // Xử lý video assets
                                $videoAssets = [];
                                log_message('debug', 'Ad asset structure: ' . json_encode($ad));
                                
                                // Xử lý videos từ nhiều cấu trúc dữ liệu có thể có
                                // Kiểm tra tất cả các cấu trúc dữ liệu có thể chứa video
                                $videos = [];
                                
                                if (isset($ad['demandGenVideoResponsiveAd']['videos']) && is_array($ad['demandGenVideoResponsiveAd']['videos'])) {
                                    // Cấu trúc thông thường
                                    $videos = $ad['demandGenVideoResponsiveAd']['videos'];
                                    log_message('debug', 'Found videos in demandGenVideoResponsiveAd.videos');
                                } elseif (isset($ad['demandGenVideoResponsiveAd']['video_assets']) && is_array($ad['demandGenVideoResponsiveAd']['video_assets'])) {
                                    // Cấu trúc thay thế có thể có
                                    $videos = $ad['demandGenVideoResponsiveAd']['video_assets'];
                                    log_message('debug', 'Found videos in demandGenVideoResponsiveAd.video_assets');
                                } elseif (isset($ad['demandGenVideoResponsiveAd']['marketing_videos']) && is_array($ad['demandGenVideoResponsiveAd']['marketing_videos'])) {
                                    // Cấu trúc thay thế khác
                                    $videos = $ad['demandGenVideoResponsiveAd']['marketing_videos'];
                                    log_message('debug', 'Found videos in demandGenVideoResponsiveAd.marketing_videos');
                                } elseif (isset($ad['demandGenVideoResponsiveAd']['marketing_images']) && is_array($ad['demandGenVideoResponsiveAd']['marketing_images'])) {
                                    // Một số trường hợp video có thể nằm trong marketing_images
                                    $videos = $ad['demandGenVideoResponsiveAd']['marketing_images'];
                                    log_message('debug', 'Found videos in demandGenVideoResponsiveAd.marketing_images');
                                } elseif (isset($ad['demandGenVideoResponsiveAd']['assets']) && is_array($ad['demandGenVideoResponsiveAd']['assets'])) {
                                    // Cấu trúc assets
                                    $videos = array_filter($ad['demandGenVideoResponsiveAd']['assets'], function($asset) {
                                        return isset($asset['type']) && ($asset['type'] === 'VIDEO' || $asset['type'] === 'YOUTUBE_VIDEO');
                                    });
                                    log_message('debug', 'Found videos in demandGenVideoResponsiveAd.assets with VIDEO type');
                                } else {
                                    // Không tìm thấy cấu trúc video nào đã biết
                                    log_message('warning', 'No recognized video structure found in DEMAND_GEN_VIDEO_RESPONSIVE_AD');
                                }
                                
                                // Ghi log cấu trúc videos để debug
                                log_message('debug', 'Videos found: ' . json_encode($videos));
                                
                                // Xử lý từng video asset
                                foreach ($videos as $video) {
                                    // Log cấu trúc dữ liệu video để debug
                                    log_message('error', 'Video asset structure: ' . json_encode($video));
                                    
                                    // Kiểm tra các trường có thể chứa URL video
                                    $videoUrl = '';
                                    $videoName = '';
                                    $videoId = '';
                                    
                                    // Kiểm tra cấu trúc asset
                                    if (isset($video['asset'])) {
                                        // Lấy ID từ resource name nếu có
                                        if (!empty($video['asset'])) {
                                            $parts = explode('/', $video['asset']);
                                            $assetId = end($parts);
                                            
                                            // Lấy thông tin chi tiết về asset từ asset ID
                                            if (!empty($assetId)) {
                                                // Gọi API để lấy thông tin chi tiết về asset
                                                $assetDetails = $this->getAssetDetails($customerId, $assetId, $accessToken, $mccId);
                                                
                                                if (!empty($assetDetails) && isset($assetDetails['youtube_video_id'])) {
                                                    $videoId = $assetDetails['youtube_video_id'];
                                                    $videoUrl = 'https://www.youtube.com/embed/' . $videoId;
                                                    $videoName = $assetDetails['name'] ?? 'Video ' . $videoId;
                                                } else {
                                                    log_message('warning', 'Could not get YouTube video ID from asset ID: ' . $assetId);
                                                }
                                            }
                                        }
                                        
                                        // Kiểm tra nếu có YouTube video asset
                                        if (isset($video['youtubeVideoAsset']) || isset($video['asset_youtube_video_asset'])) {
                                            $ytAsset = $video['youtubeVideoAsset'] ?? $video['asset_youtube_video_asset'] ?? [];
                                            if (!empty($ytAsset['youtubeVideoId'])) {
                                                $videoUrl = 'https://www.youtube.com/embed/' . $ytAsset['youtubeVideoId'];
                                                $videoName = $ytAsset['youtubeVideoTitle'] ?? '';
                                                $videoId = $ytAsset['youtubeVideoId'] ?? '';
                                            }
                                        }
                                        
                                        // Kiểm tra trường video_id nếu có
                                        if (empty($videoId) && isset($video['video_id'])) {
                                            $videoId = $video['video_id'];
                                        }
                                    } else {
                                        // Kiểm tra các trường trực tiếp trong đối tượng video
                                        if (!empty($video['youtubeVideoUrl'])) {
                                            $videoUrl = $video['youtubeVideoUrl'];
                                        } elseif (!empty($video['youtubeVideoId'])) {
                                            $videoUrl = 'https://www.youtube.com/embed/' . $video['youtubeVideoId'];
                                            $videoId = $video['youtubeVideoId'];
                                        } elseif (!empty($video['mediaUrl'])) {
                                            $videoUrl = $video['mediaUrl'];
                                        } elseif (!empty($video['videoUrl'])) {
                                            $videoUrl = $video['videoUrl'];
                                        }
                                        
                                        // Kiểm tra các trường name khác nhau
                                        if (empty($videoName)) {
                                            $videoName = $video['name'] ?? $video['videoName'] ?? $video['title'] ?? '';
                                        }
                                        
                                        // Kiểm tra các trường ID khác nhau
                                        if (empty($videoId)) {
                                            $videoId = $video['assetId'] ?? $video['id'] ?? $video['videoId'] ?? '';
                                        }
                                    }
                                    
                                    $videoAssets[] = [
                                        'name' => $videoName,
                                        'url' => $videoUrl,
                                        'id' => $videoId
                                    ];
                                }
                                
                                // Ghi log thông tin video_assets sau khi xử lý
                                log_message('debug', 'Processed video_assets: ' . json_encode($videoAssets));
                                
                                // Kiểm tra nếu không có video_assets nào được tìm thấy, thử tìm kiếm trong cấu trúc khác
                                if (empty($videoAssets) && isset($ad['demandGenVideoResponsiveAd'])) {
                                    log_message('debug', 'Trying alternative video extraction methods');
                                    // Tìm kiếm trực tiếp trong demandGenVideoResponsiveAd
                                    if (isset($ad['demandGenVideoResponsiveAd']['youtube_videos']) && is_array($ad['demandGenVideoResponsiveAd']['youtube_videos'])) {
                                        foreach ($ad['demandGenVideoResponsiveAd']['youtube_videos'] as $ytVideo) {
                                            $videoAssets[] = [
                                                'name' => $ytVideo['title'] ?? '',
                                                'url' => 'https://www.youtube.com/embed/' . ($ytVideo['id'] ?? ''),
                                                'id' => $ytVideo['id'] ?? ''
                                            ];
                                        }
                                    }
                                }
                                
                                $adDetails['video_assets'] = $videoAssets;
                                
                                // Không thể truy vấn trực tiếp images từ demand_gen_video_responsive_ad trong API v19
                                // Đặt mảng rỗng cho image_assets để tránh lỗi khi hiển thị
                                $adDetails['image_assets'] = [];
                                
                                // Ghi chú: Nếu cần lấy hình ảnh cho loại quảng cáo này, cần sử dụng
                                // Asset API riêng biệt hoặc truy vấn asset_group_asset thay vì truy vấn trực tiếp
                            }
                            
                            $ads[] = $adDetails;
                        }
                    }
                }
            }

            return $ads;
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsServiceExtension::getAds: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy thông tin chi tiết về asset từ asset ID
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $assetId ID của asset
     * @param string $accessToken Access token
     * @param string|null $mccId ID của tài khoản MCC (nếu có)
     * @return array|null Thông tin chi tiết về asset hoặc null nếu không tìm thấy
     */
    public function getAssetDetails($customerId, $assetId, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                asset.id,
                asset.name,
                asset.type,
                asset.youtube_video_asset.youtube_video_id,
                asset.youtube_video_asset.youtube_video_title
            FROM asset
            WHERE asset.id = {$assetId}";
        
        $data = [
            'query' => $query
        ];

        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            
            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results']) && !empty($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            if (!isset($result['asset'])) {
                                continue;
                            }

                            $asset = $result['asset'];
                            $assetDetails = [
                                'asset_id' => $asset['id'],
                                'name' => $asset['name'] ?? '',
                                'type' => $asset['type'] ?? ''
                            ];
                            
                            // Thêm thông tin YouTube video nếu có
                            if (isset($asset['youtubeVideoAsset'])) {
                                $assetDetails['youtube_video_id'] = $asset['youtubeVideoAsset']['youtubeVideoId'] ?? '';
                                $assetDetails['youtube_video_title'] = $asset['youtubeVideoAsset']['youtubeVideoTitle'] ?? '';
                            }
                            
                            return $assetDetails;
                        }
                    }
                }
            }

            log_message('warning', 'Asset not found with ID: ' . $assetId);
            return null;
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsServiceExtension::getAssetDetails: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách asset groups của một chiến dịch Performance Max
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $campaignId ID của chiến dịch
     * @param string $accessToken Access token
     * @param string|null $mccId ID của tài khoản MCC (nếu có)
     * @return array Danh sách asset groups
     */
    public function getAssetGroups($customerId, $campaignId, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                asset_group.id,
                asset_group.name,
                asset_group.status,
                asset_group.campaign,
                asset_group.final_urls,
                asset_group.final_mobile_urls,
                metrics.cost_micros,
                metrics.conversions,
                metrics.conversions_value,
                metrics.cost_per_conversion,
                metrics.conversions_from_interactions_rate,
                metrics.average_cpc,
                metrics.ctr,
                metrics.clicks,
                metrics.impressions
            FROM asset_group
            WHERE asset_group.campaign = 'customers/{$formattedCustomerId}/campaigns/{$campaignId}'" . 
            " AND segments.date BETWEEN '".date('Y-m-d')."' AND '".date('Y-m-d')."'";
        
        $data = [
            'query' => $query
        ];

        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            $assetGroups = [];

            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            if (!isset($result['assetGroup'])) {
                                continue;
                            }

                            $assetGroup = $result['assetGroup'];
                            $metrics = $result['metrics'] ?? [];
                            
                            $assetGroups[] = [
                                'asset_group_id' => $assetGroup['id'],
                                'name' => $assetGroup['name'],
                                'status' => $assetGroup['status'],
                                'final_urls' => $assetGroup['finalUrls'] ?? [],
                                'final_mobile_urls' => $assetGroup['finalMobileUrls'] ?? [],
                                'cost' => isset($metrics['costMicros']) ? $this->microToStandard($metrics['costMicros']) : 0,
                                'conversions' => $metrics['conversions'] ?? 0,
                                'conversion_value' => $metrics['conversionsValue'] ?? 0,
                                'cost_per_conversion' => isset($metrics['costPerConversion']) ? $metrics['costPerConversion'] : 0,
                                'conversion_rate' => $metrics['conversionsFromInteractionsRate'] ?? 0,
                                'ctr' => $metrics['ctr'] ?? 0,
                                'clicks' => $metrics['clicks'] ?? 0,
                                'impressions' => $metrics['impressions'] ?? 0,
                                'average_cpc' => isset($metrics['averageCpc']) ? $this->microToStandard($metrics['averageCpc']) : 0,
                                'campaign_id' => $campaignId,
                                'customer_id' => $customerId
                            ];
                        }
                    }
                }
            }

            return $assetGroups;
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsServiceExtension::getAssetGroups: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy danh sách assets của một asset group
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $assetGroupId ID của asset group
     * @param string $accessToken Access token
     * @param string|null $mccId ID của tài khoản MCC (nếu có)
     * @return array Danh sách assets
     */
    public function getAssetGroupAssets($customerId, $assetGroupId, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                asset_group_asset.asset_group,
                asset_group_asset.asset,
                asset_group_asset.field_type,
                asset_group_asset.performance_label,
                asset.id,
                asset.name,
                asset.type,
                asset.text_asset.text,
                asset.image_asset.full_size.url,
                asset.image_asset.full_size.width_pixels,
                asset.image_asset.full_size.height_pixels,
                asset.youtube_video_asset.youtube_video_id,
                asset.youtube_video_asset.youtube_video_title
            FROM asset_group_asset
            WHERE asset_group_asset.asset_group = 'customers/{$formattedCustomerId}/assetGroups/{$assetGroupId}'";
        
        $data = [
            'query' => $query
        ];

        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            $assets = [];

            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            if (!isset($result['assetGroupAsset']) || !isset($result['asset'])) {
                                continue;
                            }

                            $assetGroupAsset = $result['assetGroupAsset'];
                            $asset = $result['asset'];
                            
                            $assetDetails = [
                                'asset_id' => $asset['id'],
                                'name' => $asset['name'] ?? '',
                                'type' => $asset['type'] ?? '',
                                'field_type' => $assetGroupAsset['fieldType'] ?? '',
                                'performance_label' => $assetGroupAsset['performanceLabel'] ?? '',
                                'asset_group_id' => $assetGroupId,
                                'customer_id' => $customerId
                            ];
                            
                            // Thêm thông tin chi tiết dựa trên loại asset
                            if (isset($asset['textAsset'])) {
                                $assetDetails['text'] = $asset['textAsset']['text'] ?? '';
                            } elseif (isset($asset['imageAsset']) && isset($asset['imageAsset']['fullSize'])) {
                                $assetDetails['image_url'] = $asset['imageAsset']['fullSize']['url'] ?? '';
                                $assetDetails['width_pixels'] = $asset['imageAsset']['fullSize']['widthPixels'] ?? 0;
                                $assetDetails['height_pixels'] = $asset['imageAsset']['fullSize']['heightPixels'] ?? 0;
                            } elseif (isset($asset['youtubeVideoAsset'])) {
                                $assetDetails['youtube_video_id'] = $asset['youtubeVideoAsset']['youtubeVideoId'] ?? '';
                                $assetDetails['youtube_video_title'] = $asset['youtubeVideoAsset']['youtubeVideoTitle'] ?? '';
                            }
                            
                            $assets[] = $assetDetails;
                        }
                    }
                }
            }

            return $assets;
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsServiceExtension::getAssetGroupAssets: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy thông tin chi tiết về các mục tiêu của chiến dịch
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $campaignId ID của chiến dịch
     * @param string $accessToken Access token
     * @param string|null $mccId ID của tài khoản MCC (nếu có)
     * @return array Thông tin chi tiết về các mục tiêu của chiến dịch
     */
    public function getCampaignTargeting($customerId, $campaignId, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                campaign_criterion.criterion_id,
                campaign_criterion.type,
                campaign_criterion.negative,
                campaign_criterion.keyword.text,
                campaign_criterion.keyword.match_type,
                campaign_criterion.location.geo_target_constant,
                campaign_criterion.device.type,
                campaign_criterion.age_range.type,
                campaign_criterion.gender.type,
                campaign_criterion.campaign,
                geo_target_constant.name,
                geo_target_constant.country_code,
                geo_target_constant.target_type,
                geo_target_constant.canonical_name
            FROM campaign_criterion
            WHERE campaign_criterion.campaign = 'customers/{$formattedCustomerId}/campaigns/{$campaignId}'";
        
        $data = [
            'query' => $query
        ];

        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            $targeting = [
                'keywords' => [],
                'locations' => [],
                'devices' => [],
                'age_ranges' => [],
                'genders' => []
            ];

            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            if (!isset($result['campaignCriterion'])) {
                                continue;
                            }

                            $criterion = $result['campaignCriterion'];
                            $isNegative = $criterion['negative'] ?? false;
                            
                            switch ($criterion['type'] ?? '') {
                                case 'KEYWORD':
                                    if (isset($criterion['keyword'])) {
                                        $targeting['keywords'][] = [
                                            'criterion_id' => $criterion['criterionId'] ?? '',
                                            'text' => $criterion['keyword']['text'] ?? '',
                                            'match_type' => $criterion['keyword']['matchType'] ?? '',
                                            'negative' => $isNegative
                                        ];
                                    }
                                    break;
                                    
                                case 'LOCATION':
                                    if (isset($criterion['location']) && isset($result['geoTargetConstant'])) {
                                        $targeting['locations'][] = [
                                            'criterion_id' => $criterion['criterionId'] ?? '',
                                            'name' => $result['geoTargetConstant']['name'] ?? '',
                                            'country_code' => $result['geoTargetConstant']['countryCode'] ?? '',
                                            'target_type' => $result['geoTargetConstant']['targetType'] ?? '',
                                            'canonical_name' => $result['geoTargetConstant']['canonicalName'] ?? '',
                                            'negative' => $isNegative
                                        ];
                                    }
                                    break;
                                    
                                case 'DEVICE':
                                    if (isset($criterion['device'])) {
                                        $targeting['devices'][] = [
                                            'criterion_id' => $criterion['criterionId'] ?? '',
                                            'type' => $criterion['device']['type'] ?? '',
                                            'negative' => $isNegative
                                        ];
                                    }
                                    break;
                                    
                                case 'AGE_RANGE':
                                    if (isset($criterion['ageRange'])) {
                                        $targeting['age_ranges'][] = [
                                            'criterion_id' => $criterion['criterionId'] ?? '',
                                            'type' => $criterion['ageRange']['type'] ?? '',
                                            'negative' => $isNegative
                                        ];
                                    }
                                    break;
                                    
                                case 'GENDER':
                                    if (isset($criterion['gender'])) {
                                        $targeting['genders'][] = [
                                            'criterion_id' => $criterion['criterionId'] ?? '',
                                            'type' => $criterion['gender']['type'] ?? '',
                                            'negative' => $isNegative
                                        ];
                                    }
                                    break;
                            }
                        }
                    }
                }
            }

            return $targeting;
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsServiceExtension::getCampaignTargeting: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy thông tin chi tiết về các mục tiêu của nhóm quảng cáo
     * 
     * @param string $customerId ID của tài khoản quảng cáo
     * @param string $adGroupId ID của nhóm quảng cáo
     * @param string $accessToken Access token
     * @param string|null $mccId ID của tài khoản MCC (nếu có)
     * @return array Thông tin chi tiết về các mục tiêu của nhóm quảng cáo
     */
    public function getAdGroupTargeting($customerId, $adGroupId, $accessToken, $mccId = null)
    {
        $formattedCustomerId = $this->formatCustomerId($customerId);
        
        $url = $this->baseUrl . $this->apiVersion . '/customers/' . $formattedCustomerId . '/googleAds:searchStream';
        
        $query = "
            SELECT
                ad_group_criterion.criterion_id,
                ad_group_criterion.type,
                ad_group_criterion.negative,
                ad_group_criterion.keyword.text,
                ad_group_criterion.keyword.match_type,
                ad_group_criterion.listing_group,
                ad_group_criterion.age_range.type,
                ad_group_criterion.gender.type,
                ad_group_criterion.ad_group,
                metrics.cost_micros,
                metrics.conversions,
                metrics.conversions_value,
                metrics.cost_per_conversion,
                metrics.conversions_from_interactions_rate,
                metrics.average_cpc,
                metrics.ctr,
                metrics.clicks,
                metrics.impressions
            FROM ad_group_criterion
            WHERE ad_group_criterion.ad_group = 'customers/{$formattedCustomerId}/adGroups/{$adGroupId}'" . 
            " AND segments.date BETWEEN '".date('Y-m-d')."' AND '".date('Y-m-d')."'";
        
        $data = [
            'query' => $query
        ];

        try {
            $response = $this->makeCurlRequest($url, 'POST', $accessToken, json_encode($data), $mccId);
            $targeting = [
                'keywords' => [],
                'listing_groups' => [],
                'age_ranges' => [],
                'genders' => []
            ];

            if (is_array($response)) {
                foreach ($response as $batch) {
                    if (isset($batch['results'])) {
                        foreach ($batch['results'] as $result) {
                            if (!isset($result['adGroupCriterion'])) {
                                continue;
                            }

                            $criterion = $result['adGroupCriterion'];
                            $metrics = $result['metrics'] ?? [];
                            $isNegative = $criterion['negative'] ?? false;
                            
                            $criterionMetrics = [
                                'cost' => isset($metrics['costMicros']) ? $this->microToStandard($metrics['costMicros']) : 0,
                                'conversions' => $metrics['conversions'] ?? 0,
                                'conversion_value' => $metrics['conversionsValue'] ?? 0,
                                'cost_per_conversion' => isset($metrics['costPerConversion']) ? $metrics['costPerConversion'] : 0,
                                'conversion_rate' => $metrics['conversionsFromInteractionsRate'] ?? 0,
                                'ctr' => $metrics['ctr'] ?? 0,
                                'clicks' => $metrics['clicks'] ?? 0,
                                'impressions' => $metrics['impressions'] ?? 0,
                                'average_cpc' => isset($metrics['averageCpc']) ? $this->microToStandard($metrics['averageCpc']) : 0
                            ];
                            
                            switch ($criterion['type'] ?? '') {
                                case 'KEYWORD':
                                    if (isset($criterion['keyword'])) {
                                        $targeting['keywords'][] = array_merge([
                                            'criterion_id' => $criterion['criterionId'] ?? '',
                                            'text' => $criterion['keyword']['text'] ?? '',
                                            'match_type' => $criterion['keyword']['matchType'] ?? '',
                                            'negative' => $isNegative
                                        ], $criterionMetrics);
                                    }
                                    break;
                                    
                                case 'LISTING_GROUP':
                                    if (isset($criterion['listingGroup'])) {
                                        $targeting['listing_groups'][] = array_merge([
                                            'criterion_id' => $criterion['criterionId'] ?? '',
                                            'type' => $criterion['listingGroup']['type'] ?? '',
                                            'negative' => $isNegative
                                        ], $criterionMetrics);
                                    }
                                    break;
                                    
                                case 'AGE_RANGE':
                                    if (isset($criterion['ageRange'])) {
                                        $targeting['age_ranges'][] = array_merge([
                                            'criterion_id' => $criterion['criterionId'] ?? '',
                                            'type' => $criterion['ageRange']['type'] ?? '',
                                            'negative' => $isNegative
                                        ], $criterionMetrics);
                                    }
                                    break;
                                    
                                case 'GENDER':
                                    if (isset($criterion['gender'])) {
                                        $targeting['genders'][] = array_merge([
                                            'criterion_id' => $criterion['criterionId'] ?? '',
                                            'type' => $criterion['gender']['type'] ?? '',
                                            'negative' => $isNegative
                                        ], $criterionMetrics);
                                    }
                                    break;
                            }
                        }
                    }
                }
            }

            return $targeting;
        } catch (Exception $e) {
            log_message('error', 'Error in GoogleAdsServiceExtension::getAdGroupTargeting: ' . $e->getMessage());
            throw $e;
        }
    }
}