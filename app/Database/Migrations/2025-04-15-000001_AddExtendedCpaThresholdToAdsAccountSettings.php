<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExtendedCpaThresholdToAdsAccountSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ads_account_settings', [
            'extended_cpa_threshold' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', 'extended_cpa_threshold');
    }
} 