<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKeywordCampaignToProducts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('products', [
            'keyword_campaign' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'return_rate'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('products', 'keyword_campaign');
    }
}
