<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleSheet2ApiFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ads_account_settings', [
            'ggsheet2_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'ggsheet_name'
            ],
            'ggsheet2_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'ggsheet2_id'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', ['ggsheet2_id', 'ggsheet2_name']);
    }
}