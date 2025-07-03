<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPancakeUsdFieldsToAdsAccountSettings extends Migration
{
    public function up()
    {
        // Thêm các cột Pancake USD vào bảng ads_account_settings
        $fields = [
            'pancake_use_usd' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 0,
                'after' => 'pancake_exclude_tags'
            ],
            'pancake_usd_rate' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'default' => 23000,
                'after' => 'pancake_use_usd'
            ]
        ];

        $this->forge->addColumn('ads_account_settings', $fields);
    }

    public function down()
    {
        // Xóa các cột Pancake USD khỏi bảng ads_account_settings
        $this->forge->dropColumn('ads_account_settings', [
            'pancake_use_usd',
            'pancake_usd_rate'
        ]);
    }
}