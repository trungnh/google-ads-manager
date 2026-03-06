<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChartTables extends Migration
{
    public function up()
    {
        // 1. Drop old table
        $this->forge->dropTable('campaign_performance_logs', true);

        // 2. Create 5m table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'customer_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
            ],
            'campaign_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
            ],
            'record_time' => [
                'type' => 'DATETIME',
            ],
            'cost' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'conversions' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'conversion_value' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'clicks' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'cpa' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'cpc' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
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
        // Add unique constraint so ON DUPLICATE KEY UPDATE works perfectly
        $this->forge->addUniqueKey(['customer_id', 'campaign_id', 'record_time']);

        $this->forge->createTable('campaign_chart_5m');

        // 3. Create 30m table (identical structure)
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'customer_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
            ],
            'campaign_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
            ],
            'record_time' => [
                'type' => 'DATETIME',
            ],
            'cost' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'conversions' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'conversion_value' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'clicks' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'cpa' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'cpc' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
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
        $this->forge->addUniqueKey(['customer_id', 'campaign_id', 'record_time']);

        $this->forge->createTable('campaign_chart_30m');
    }

    public function down()
    {
        $this->forge->dropTable('campaign_chart_5m', true);
        $this->forge->dropTable('campaign_chart_30m', true);
    }
}
