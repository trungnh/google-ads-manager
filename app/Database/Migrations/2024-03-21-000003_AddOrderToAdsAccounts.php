<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderToAdsAccounts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ads_accounts', [
            'order' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => true,
                'default' => 0
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_accounts', 'order');
    }
} 