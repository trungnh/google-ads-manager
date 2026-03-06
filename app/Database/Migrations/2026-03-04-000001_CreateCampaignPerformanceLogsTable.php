<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignPerformanceLogsTable extends Migration
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
                'constraint' => '50',
            ],
            'campaign_id' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'record_time' => [
                'type' => 'DATETIME',
            ],
            'cost' => [
                'type' => 'DOUBLE',
                'default' => 0,
            ],
            'conversions' => [
                'type' => 'DOUBLE',
                'default' => 0,
            ],
            'conversion_value' => [
                'type' => 'DOUBLE',
                'default' => 0,
            ],
            'clicks' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'cpa' => [
                'type' => 'DOUBLE',
                'default' => 0,
            ],
            'cpc' => [
                'type' => 'DOUBLE',
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
        // Add index to speed up filtering by campaign_id and time
        $this->forge->addKey(['campaign_id', 'record_time']);
        $this->forge->createTable('campaign_performance_logs');
    }

    public function down()
    {
        $this->forge->dropTable('campaign_performance_logs');
    }
}
