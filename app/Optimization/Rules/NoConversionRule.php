<?php
namespace App\Optimization\Rules;

class NoConversionRule implements RuleInterface
{
    public function evaluate(array $account, array $campaign): ?array
    {
        $realConversions = $campaign['real_conversions'] ?? 0;
        $cpaThreshold = $account['cpa_threshold'] ?? 0;
        $cost = $campaign['cost'] ?? 0;

        if ($realConversions == 0) {
            if ($cpaThreshold > 0 && $cost > $cpaThreshold) {
                return [
                    'action' => 'pause',
                    'reason' => "Chi tiêu (" . number_format($cost, 0, '', '.') . ") vượt ngưỡng (" . number_format($cpaThreshold, 0, '', '.') . ") và không có đơn thực tế",
                ];
            }
        }

        return null;
    }
}
