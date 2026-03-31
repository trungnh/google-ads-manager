<?php
namespace App\Optimization\Rules;

interface RuleInterface
{
    /**
     * @param array $account Account settings
     * @param array $campaign Campaign data
     * @return array|null Trả về array với action (pause, increase_budget) và reason, hoặc null nếu không thỏa điều kiện
     */
    public function evaluate(array $account, array $campaign): ?array;
}
