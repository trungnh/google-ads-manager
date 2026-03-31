<?php
namespace App\Optimization\Rules;

use App\Models\CampaignsDataModel;

class MultipleConversionsRule implements RuleInterface
{
    protected $campaignsDataModel;

    public function __construct()
    {
        $this->campaignsDataModel = new CampaignsDataModel();
    }

    public function evaluate(array $account, array $campaign): ?array
    {
        $realConversions = $campaign['real_conversions'] ?? 0;
        
        if ($realConversions <= 1) {
            return null;
        }

        $extendedCpaThreshold = $account['extended_cpa_threshold'] ?? 0;
        $realRoas = ($campaign['cost'] > 0) ? ($campaign['real_conversion_value'] ?? 0) / $campaign['cost'] : 0;
        $realCpa = $campaign['real_cpa'] ?? 0;

        if ($extendedCpaThreshold == 0) {
            // Giống SingleConversionRule
            if (isset($account['use_roas_threshold']) && $account['use_roas_threshold'] == 1) {
                $roasThreshold = $account['roas_threshold'] ?? 0;
                if ($roasThreshold > 0 && $realRoas < $roasThreshold) {
                    return [
                        'action' => 'pause',
                        'reason' => "ROAS thực tế (" . number_format($realRoas, 1, ',', '.') . ") thấp hơn ngưỡng (" . number_format($roasThreshold, 1, ',', '.') . ")"
                    ];
                }
            } else {
                $cpaThreshold = $account['cpa_threshold'] ?? 0;
                if ($cpaThreshold > 0 && $realCpa > $cpaThreshold) {
                    return [
                        'action' => 'pause',
                        'reason' => "CPA thực tế (" . number_format($realCpa, 0, ',', '.') . ") vượt ngưỡng (" . number_format($cpaThreshold, 1, ',', '.') . ")"
                    ];
                }
            }
        } else {
            // Check khoảng chuyển đổi
            $tmpCampaign = $this->campaignsDataModel->where('customer_id', $account['customer_id'])
                ->where('campaign_id', $campaign['campaign_id'])
                ->where('date', date('Y-m-d'))
                ->first();

            if (!$tmpCampaign) {
                return null;
            }

            $lastCostConversion = $tmpCampaign['last_cost_conversion'] ?? 0;
            $lastCountConversion = $tmpCampaign['last_count_conversion'] ?? 0;
            $lastCountConversionValue = $tmpCampaign['last_count_conversion_value'] ?? 0;

            $costExtendFromLastConversion = $tmpCampaign['cost'] - $lastCostConversion;
            $conversionsExtendFromLastConversion = $realConversions - $lastCountConversion;
            $conversionValueExtendFromLastConversion = ($campaign['real_conversion_value'] ?? 0) - $lastCountConversionValue;

            $cpaThreshold = $account['cpa_threshold'] ?? 0;

            if ($conversionsExtendFromLastConversion == 0) {
                if ($cpaThreshold > 0 && $costExtendFromLastConversion > $cpaThreshold) {
                    return [
                        'action' => 'pause',
                        'tmp_cflc' => $costExtendFromLastConversion, // required to pass back to command logic for exclude check
                        'reason' => "Chi tiêu thêm (" . number_format($costExtendFromLastConversion, 0, '', '.') . ") từ lần ra đơn cuối cùng - Không có đơn thực tế"
                    ];
                }
            } else {
                $cpaExtendFromLastConversion = $costExtendFromLastConversion / $conversionsExtendFromLastConversion;
                $roasExtendFromLastConversion = ($costExtendFromLastConversion > 0) ? $conversionValueExtendFromLastConversion / $costExtendFromLastConversion : 0;
                
                if (isset($account['use_roas_threshold']) && $account['use_roas_threshold']) {
                    $roasThreshold = $account['roas_threshold'] ?? 0;
                    if ($roasThreshold > 0 && $roasExtendFromLastConversion < $roasThreshold) {
                        return [
                            'action' => 'pause',
                            'tmp_cflc' => $costExtendFromLastConversion,
                            'reason' => "Chi tiêu thêm (" . number_format($costExtendFromLastConversion, 0, '', '.') . ") từ lần ra đơn cuối cùng - ROAS (" . number_format($roasExtendFromLastConversion, 1, ',', '.') . ") thấp hơn ngưỡng (" . number_format($roasThreshold, 1, ',', '.') . ")"
                        ];
                    }
                } else {
                    $dynamicThreshold = ($extendedCpaThreshold > 0) ? $extendedCpaThreshold : $cpaThreshold;
                    if ($dynamicThreshold > 0 && $cpaExtendFromLastConversion > $dynamicThreshold) {
                        return [
                            'action' => 'pause',
                            'tmp_cflc' => $costExtendFromLastConversion,
                            'reason' => "Chi tiêu thêm (" . number_format($costExtendFromLastConversion, 0, '', '.') . ") từ lần ra đơn cuối cùng - CPA (" . number_format($cpaExtendFromLastConversion, 1, ',', '.') . ") vượt ngưỡng (" . number_format($dynamicThreshold, 1, ',', '.') . ")"
                        ];
                    }
                }
            }
        }

        return null;
    }
}
