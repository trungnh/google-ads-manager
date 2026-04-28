<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPancakeShowOtherOrdersToAdsAccountSettings extends Migration
{
    public function up()
    {
        $fields = [
            'pancake_show_other_orders' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'pancake_usd_rate'
            ],
        ];
        $this->forge->addColumn('ads_account_settings', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', 'pancake_show_other_orders');
    }
}
