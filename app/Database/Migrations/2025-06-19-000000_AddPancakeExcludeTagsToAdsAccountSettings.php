<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPancakeExcludeTagsToAdsAccountSettings extends Migration
{
    public function up()
    {
        // Thêm cột pancake_exclude_tags vào bảng ads_account_settings
        $fields = [
            'pancake_exclude_tags' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'pancake_product_id'
            ]
        ];

        $this->forge->addColumn('ads_account_settings', $fields);
    }

    public function down()
    {
        // Xóa cột pancake_exclude_tags khỏi bảng ads_account_settings
        $this->forge->dropColumn('ads_account_settings', [
            'pancake_exclude_tags'
        ]);
    }
}