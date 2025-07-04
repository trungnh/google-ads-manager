<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRealConversionFieldsToCampaignsData extends Migration
{
    public function up()
    {
        $this->forge->addColumn('campaigns_data', [
            'real_conversions_total' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
                'after' => 'real_conversions'
            ],
            'real_conversions_pending' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
                'after' => 'real_conversions_total'
            ],
            'real_conversions_success' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
                'after' => 'real_conversions_pending'
            ],
            'real_conversion_value_total' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
                'after' => 'real_conversion_value'
            ],
            'real_conversion_value_success' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
                'after' => 'real_conversion_value_total'
            ],
            'real_cpa_total' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
                'after' => 'real_cpa'
            ],
            'real_cpa_success' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
                'after' => 'real_cpa_total'
            ],
            'real_roas_total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
                'default' => 0,
                'after' => 'real_cpa_success'
            ],
            'real_roas_success' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
                'default' => 0,
                'after' => 'real_roas_total'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('campaigns_data', 'real_conversions_total');
        $this->forge->dropColumn('campaigns_data', 'real_conversions_pending');
        $this->forge->dropColumn('campaigns_data', 'real_conversions_success');
        $this->forge->dropColumn('campaigns_data', 'real_conversion_value_total');
        $this->forge->dropColumn('campaigns_data', 'real_conversion_value_success');
        $this->forge->dropColumn('campaigns_data', 'real_cpa_total');
        $this->forge->dropColumn('campaigns_data', 'real_cpa_success');
        $this->forge->dropColumn('campaigns_data', 'real_roas_total');
        $this->forge->dropColumn('campaigns_data', 'real_roas_success');
    }
}