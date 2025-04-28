<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignSchedules extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'customer_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'action_type' => [
                'type' => 'ENUM',
                'constraint' => ['enable', 'disable'],
                'null' => false
            ],
            'execution_time' => [
                'type' => 'TIME',
                'null' => false
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
                'null' => false
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['customer_id', 'execution_time']);
        $this->forge->createTable('campaign_schedules');
    }

    public function down()
    {
        $this->forge->dropTable('campaign_schedules');
    }
}