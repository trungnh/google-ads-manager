<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBudgetSpendingThresholdToAdsAccountSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ads_account_settings', [
            'budget_spending_threshold' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 50.00,
                'after' => 'increase_budget'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', 'budget_spending_threshold');
    }
}
