<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorCampaignsDataJson extends Migration
{
    public function up()
    {
        // 1. Thêm cột external_metrics dạng JSON cho campaigns_data
        $this->forge->addColumn('campaigns_data', [
            'external_metrics' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'average_cpc'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('campaigns_data', 'external_metrics');
    }
}
