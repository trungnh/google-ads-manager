<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastExecutedDateToCampaignSchedules extends Migration
{
    public function up()
    {
        $this->forge->addColumn('campaign_schedules', [
            'last_executed_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'execution_time'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('campaign_schedules', 'last_executed_date');
    }
}