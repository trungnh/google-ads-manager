<?php
namespace App\Optimization\Rules;

class SingleConversionRule implements RuleInterface
{
    public function evaluate(array $account, array $campaign): ?array
    {
        $realConversions = $campaign['real_conversions'] ?? 0;
        
        if ($realConversions != 1) {
            return null;
        }

        $realRoas = ($campaign['cost'] > 0) ? ($campaign['real_conversion_value'] ?? 0) / $campaign['cost'] : 0;
        $realCpa = $campaign['real_cpa'] ?? 0;
        
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

        return null;
    }
}
