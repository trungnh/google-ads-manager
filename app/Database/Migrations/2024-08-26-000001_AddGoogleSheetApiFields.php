<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleSheetApiFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ads_account_settings', [
            'use_ggsheet_api' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'gsheet2'
            ],
            'ggsheet_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'use_ggsheet_api'
            ],
            'ggsheet_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'ggsheet_id'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', 'use_ggsheet_api');
        $this->forge->dropColumn('ads_account_settings', 'ggsheet_id');
        $this->forge->dropColumn('ads_account_settings', 'ggsheet_name');
    }
}