<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPancakeFieldsToAdsAccountSettings extends Migration
{
    public function up()
    {
        // Thêm các cột Pancake vào bảng ads_account_settings
        $fields = [
            'use_pancake' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => 0,
                'after' => 'gsheet2'
            ],
            'pancake_shop_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'use_pancake'
            ],
            'pancake_api_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'pancake_shop_id'
            ],
            'pancake_product_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'pancake_api_key'
            ]
        ];

        $this->forge->addColumn('ads_account_settings', $fields);
    }

    public function down()
    {
        // Xóa các cột Pancake khỏi bảng ads_account_settings
        $this->forge->dropColumn('ads_account_settings', [
            'use_pancake',
            'pancake_shop_id',
            'pancake_api_key',
            'pancake_product_id'
        ]);
    }
}