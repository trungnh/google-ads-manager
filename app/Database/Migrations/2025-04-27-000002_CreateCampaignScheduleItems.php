<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignScheduleItems extends Migration
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
            'schedule_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false
            ],
            'campaign_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['schedule_id', 'campaign_id']);
        $this->forge->addForeignKey('schedule_id', 'campaign_schedules', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('campaign_schedule_items');
    }

    public function down()
    {
        $this->forge->dropTable('campaign_schedule_items');
    }
}