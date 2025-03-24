<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignsData extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'customer_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'campaign_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'budget' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'cost' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'conversions' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'conversion_value' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'cost_per_conversion' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'conversion_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
                'default' => 0,
            ],
            'target_cpa' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'null' => true,
            ],
            'target_roas' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'ctr' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
                'default' => 0,
            ],
            'clicks' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'average_cpc' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'real_conversions' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'real_conversion_value' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'real_conversion_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
                'default' => 0,
            ],
            'real_cpa' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'last_updated_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['customer_id', 'campaign_id', 'date']);
        $this->forge->createTable('campaigns_data');
    }

    public function down()
    {
        $this->forge->dropTable('campaigns_data');
    }
} 