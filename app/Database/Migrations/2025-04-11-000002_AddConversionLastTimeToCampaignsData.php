<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConversionLastTimeToCampaignsData extends Migration
{
    public function up()
    {
        $this->forge->addColumn('campaigns_data', [
            'last_cost_conversion' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
                'after' => 'real_cpa'
            ],
            'last_count_conversion' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
                'after' => 'last_cost_conversion'
            ],
            'last_count_conversion_value' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
                'after' => 'last_count_conversion'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('campaigns_data', 'last_cost_conversion');
        $this->forge->dropColumn('campaigns_data', 'last_count_conversion');
        $this->forge->dropColumn('campaigns_data', 'last_count_conversion_value');
    }
} 