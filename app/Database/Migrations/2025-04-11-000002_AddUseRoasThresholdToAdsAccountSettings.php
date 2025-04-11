<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUseRoasThresholdToAdsAccountSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ads_account_settings', [
            'use_roas_threshold' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', 'use_roas_threshold');
    }
} 