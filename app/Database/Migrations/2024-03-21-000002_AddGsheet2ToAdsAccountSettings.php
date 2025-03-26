<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGsheet2ToAdsAccountSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ads_account_settings', [
            'gsheet2' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', 'gsheet2');
    }
} 