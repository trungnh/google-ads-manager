<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuToOnOffToAdsAccountSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ads_account_settings', [
            'auto_on_off' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', 'auto_on_off');
    }
} 