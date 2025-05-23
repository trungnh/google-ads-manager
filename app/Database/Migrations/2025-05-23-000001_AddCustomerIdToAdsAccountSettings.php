<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerIdToAdsAccountSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ads_account_settings', [
            'customer_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', 'customer_id');
    }
} 