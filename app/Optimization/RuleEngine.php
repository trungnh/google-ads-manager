<?php
namespace App\Optimization;

use App\Optimization\Rules\NoConversionRule;
use App\Optimization\Rules\SingleConversionRule;
use App\Optimization\Rules\MultipleConversionsRule;
use App\Optimization\Rules\IncreaseBudgetRule;

class RuleEngine
{
    protected $pauseRules = [];
    protected $increaseRules = [];

    public function __construct()
    {
        $this->pauseRules = [
            new NoConversionRule(),
            new SingleConversionRule(),
            new MultipleConversionsRule()
        ];
        
        $this->increaseRules = [
            new IncreaseBudgetRule()
        ];
    }

    public function evaluateCampaign(array $account, array $campaign): array
    {
        // 1. Kiểm tra chi tiêu phải vượt cost_threshold
        if (isset($account['cost_threshold']) && $account['cost_threshold'] > 0) {
            if (($campaign['cost'] ?? 0) <= $account['cost_threshold']) {
                return ['action' => 'none', 'reason' => 'Cost chưa tới ngưỡng tối thiểu', 'tmp_cflc' => 0];
            }
        }
        
        // 2. Kiểm tra các rule PAUSE
        foreach ($this->pauseRules as $rule) {
            $result = $rule->evaluate($account, $campaign);
            if ($result !== null && $result['action'] === 'pause') {
                return [
                    'action' => 'pause',
                    'reason' => $result['reason'],
                    'tmp_cflc' => $result['tmp_cflc'] ?? 0
                ];
            }
        }

        // 3. Nếu không Pause, kiểm tra rule Tăng ngân sách
        foreach ($this->increaseRules as $rule) {
            $result = $rule->evaluate($account, $campaign);
            if ($result !== null && $result['action'] === 'increase_budget') {
                return [
                    'action' => 'increase_budget',
                    'reason' => $result['reason'],
                    'tmp_cflc' => 0
                ];
            }
        }

        return ['action' => 'none', 'reason' => '', 'tmp_cflc' => 0];
    }
}
