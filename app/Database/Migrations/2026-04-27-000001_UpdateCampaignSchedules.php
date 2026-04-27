<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCampaignSchedules extends Migration
{
    public function up()
    {
        $fields = [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'customer_id'
            ],
            'budget_value' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'null' => true,
                'after' => 'execution_time'
            ],
            'budget_type' => [
                'type' => 'ENUM',
                'constraint' => ['percentage', 'absolute'],
                'null' => true,
                'after' => 'budget_value'
            ],
        ];
        $this->forge->addColumn('campaign_schedules', $fields);

        // Update action_type to VARCHAR to be more flexible
        $this->forge->modifyColumn('campaign_schedules', [
            'action_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('campaign_schedules', ['name', 'budget_value', 'budget_type']);
        
        // Revert action_type to ENUM
        $this->forge->modifyColumn('campaign_schedules', [
            'action_type' => [
                'type' => 'ENUM',
                'constraint' => ['enable', 'disable'],
                'null' => false
            ]
        ]);
    }
}
