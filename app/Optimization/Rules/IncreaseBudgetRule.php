<?php
namespace App\Optimization\Rules;

class IncreaseBudgetRule implements RuleInterface
{
    public function evaluate(array $account, array $campaign): ?array
    {
        $realConversions = $campaign['real_conversions'] ?? 0;
        
        $increaseBudget = $account['increase_budget'] ?? 0;
        $cost = $campaign['cost'] ?? 0;
        $budget = $campaign['budget'] ?? 0;

        if ($realConversions > 0 && $increaseBudget > 0 && $cost > ($budget * 0.5)) {
            return [
                'action' => 'increase_budget',
                'reason' => "Chi tiêu (" . number_format($cost, 0, '', '.') . ") vượt 50% ngân sách (" . number_format($budget, 0, '', '.') . ")"
            ];
        }

        return null;
    }
}
